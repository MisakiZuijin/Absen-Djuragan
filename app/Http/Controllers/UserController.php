<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Utils\DateNow;
use App\Models\Schedule;
use App\Utils\Converter;
use App\Models\Broadcast;
use App\Models\HandRaise;
use App\Models\Projects;
use App\Models\NameProjects;
use App\Models\DetailProjects;
use App\Models\PermitLog;
use App\Helper\LogConsole;
use App\Models\Attendance;
use App\Models\Office;
use App\Models\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PermitSetting;
use App\Models\AdjustableAttd;
use App\Services\UserService;
use App\Models\DetailSchedule;
use App\Models\PermitCategory;
use App\Services\QuotesService;
use App\Services\HolidayService;
use App\Services\ScheduleService;
use App\Services\WhatsappService;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\PermitReasonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StorePermitPresenceRequest;

class UserController extends Controller
{
    protected UserService $userService;
    protected AttendanceService $attendanceService;
    protected QuotesService $quoteService;
    protected ScheduleService $scheduleService;
    protected PermitReasonService $permitReasonService;
    protected HolidayService $holidayService;

    public function __construct(
        UserService $userService,
        AttendanceService $attendanceService,
        QuotesService $quoteService,
        ScheduleService $scheduleService,
        PermitReasonService $permitReasonService,
        HolidayService $holidayService
    ) {
        $this->userService = $userService;
        $this->attendanceService = $attendanceService;
        $this->quoteService = $quoteService;
        $this->scheduleService = $scheduleService;
        $this->permitReasonService = $permitReasonService;
        $this->holidayService = $holidayService;
    }

    public function userView()
    {
        $dateNow = DateNow::getCurrentDate();
        $user = $this->userService->getUserLoggedData();

        if (!$user->intern) {
            abort(403, 'User tidak memiliki data intern.');
        }

        $user->intern->loadMissing(['account', 'division', 'detailProject.project.nameProject']);

        $detailProjects = $user->intern?->detailProject ?? collect();
        $assignedProjects = $detailProjects->map(function ($dp) {
            return $dp->project;
        })->filter()->unique('id');

        $activeProjects = $assignedProjects->filter(function ($p) {
            return $p && $p->status !== 'done';
        });

        // ========================================================================
        // AWAL BAGIAN YANG DIPERBAIKI
        // ========================================================================

        // 1. Ambil semua data hari libur dari service.
        $holidayDataResult = $this->holidayService->getAll();
        $holidayData = $holidayDataResult->isSuccess() ? $holidayDataResult->getData() : [];

        // 2. Buat sebuah array yang hanya berisi tanggal libur dalam format Y-m-d untuk perbandingan.
        $holidayDates = collect($holidayData)->pluck('date')->map(function ($date) {
            // Gunakan Carbon untuk memastikan format tanggal konsisten (Y-m-d)
            return Carbon::parse($date)->format('Y-m-d');
        })->toArray();

        // 3. Ambil data jadwal mingguan seperti biasa.
        $schedulesResult = $this->scheduleService->weekSchedules($user->intern->id, $dateNow);
        $schedules = $schedulesResult->isSuccess() ? $schedulesResult->getData() : collect();

        // 4. [LANGKAH KUNCI] Filter koleksi jadwal.
        // Hanya jadwal yang tanggalnya TIDAK ADA di dalam array $holidayDates yang akan dipertahankan.
        $schedules = $schedules->filter(function ($schedule) use ($holidayDates) {
            $scheduleDate = Carbon::parse($schedule->date)->format('Y-m-d');
            return !in_array($scheduleDate, $holidayDates);
        });

        // ========================================================================
        // AKHIR BAGIAN YANG DIPERBAIKI
        // ========================================================================

        // Sisa logika untuk mengambil data lain tetap berjalan seperti semula.
        $todaysDetailSchedule = DetailSchedule::whereHas('schedule.intern', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->whereDate('date', today())
            ->with(['shift', 'attendance', 'logActivity.status'])
            ->first();

        $todaysShift = $todaysDetailSchedule?->shift;
        $absenceHistory = $todaysDetailSchedule?->attendance;

        $permitCategoriesResult = $this->permitReasonService->getAllPermitCategory();
        $listPermitCategory = $permitCategoriesResult->isSuccess() ? $permitCategoriesResult->getData() : [];

        $stageResult = $this->attendanceService->attendanceStatus($user->intern->id);
        $stage = $stageResult->isSuccess() ? $stageResult->getData()['stage'] : null;
        $schedule_id = $todaysDetailSchedule?->schedule_id;
        $detail_schedule_id = $todaysDetailSchedule?->id ?? 0;

        if (!$schedule_id) {
            $schedule_id = Schedule::where('intern_id', $user->intern->id)
                ->orderByDesc('id')
                ->value('id');
        }

        $all_adjustable = [];
        if ($detail_schedule_id) {
            try {
                $all_adjustable = \App\Models\AdjustableAttd::where('detail_schedule_id', $detail_schedule_id)
                    ->where('date', now()->format('Y-m-d'))
                    ->orderBy('id', 'asc')
                    ->get();

                Log::info('UserController - Direct query all_adjustable count: ' . count($all_adjustable));
                Log::info('UserController - detail_schedule_id: ' . $detail_schedule_id);
                Log::info('UserController - date: ' . now()->format('Y-m-d'));

                foreach ($all_adjustable as $adj) {
                    Log::info('UserController - Found adjustable ID: ' . $adj->id . ' with end_time: ' . $adj->end_time);
                }
            } catch (\Exception $e) {
                Log::error('UserController - Error querying adjustable: ' . $e->getMessage());
                $all_adjustable = collect();
            }
        }

        if ($stageResult->isSuccess()) {
            $stageData = $stageResult->getData();
            if (isset($stageData['all_adjustable']) && !empty($stageData['all_adjustable'])) {
                $stageAllAdjustable = collect($stageData['all_adjustable']);

                if ($all_adjustable->isEmpty()) {
                    $all_adjustable = $stageAllAdjustable;
                    Log::info('UserController - Using stageResult all_adjustable count: ' . count($all_adjustable));
                } else {
                    $directIds = $all_adjustable->pluck('id')->toArray();
                    $stageIds = $stageAllAdjustable->pluck('id')->toArray();
                    $diff = array_diff($stageIds, $directIds);
                    if (!empty($diff)) {
                        Log::warning('UserController - Difference in adjustable data between direct query and stageResult: ' . implode(',', $diff));
                    }
                }
            }
        }

        $birth_date = $user->profile->date_of_birth ?? null;
        $currentHandRaise = HandRaise::where('user_id', $user->id)
            ->where('is_raised', true)
            ->where('status', '!=', 'done')
            ->latest()
            ->first()
            ?? HandRaise::where('user_id', $user->id)->latest()->first();
        $handRaiseStatus = (bool) ($currentHandRaise?->is_raised && $currentHandRaise?->status !== 'done');
        $quotes = (now()->format('m-d') === ($birth_date ? Carbon::parse($birth_date)->format('m-d') : null))
            ? $this->quoteService->getByCategory('ultah')
            : $this->quoteService->getByCategory('quote');

        $all_broadcasts = Broadcast::with(['divisions', 'users'])->latest()->get();
        $relevant_broadcasts = $all_broadcasts->filter(function ($broadcast) use ($user) {
            switch ($broadcast->broadcast_type) {
                case 'all':
                    return true;
                case 'division':
                    return $user->intern && $user->intern->division_id && $broadcast->divisions->contains('id', $user->intern->division_id);
                case 'specific':
                    return $broadcast->users->contains('id', $user->id);
                default:
                    return false;
            }
        });
        if (!Session::has('broadcast_shown') && $relevant_broadcasts->isNotEmpty()) {
            Session::put('broadcast_shown', true);
            Session::flash('firstBroadcast', $relevant_broadcasts->first());
        }

        $lackInSecondsToday = 0;
        if (is_object($todaysShift) && is_object($absenceHistory)) {
            $hasClockedOut = !is_null($absenceHistory->end_time);
            if ($hasClockedOut) {
                $shiftMinutes = $todaysShift->total_time_in_minute ?? 0;
                $workingMinutes = ($absenceHistory->total_min ?? 0) - ($absenceHistory->total_break_min ?? 0);
                $differenceInMinutes = $shiftMinutes - $workingMinutes;
                if ($differenceInMinutes > 0) {
                    $lackInSecondsToday = $differenceInMinutes * 60;
                }
            } elseif (!is_null($absenceHistory->start_time)) {
                $scheduleStart = Carbon::parse($todaysShift->start_time);
                $checkInTime = Carbon::parse($absenceHistory->start_time);
                if ($checkInTime->gt($scheduleStart)) {
                    $lackInSecondsToday = $checkInTime->diffInSeconds($scheduleStart);
                }
            }
        }

        $lackData = ['isLess' => false];
        $internTargetData = ['change_time_total' => '00:00:00', 'total_lack_in_seconds' => 0];
        if ($lackInSecondsToday > 0) {
            $lackData['isLess'] = true;
            $hours = floor($lackInSecondsToday / 3600);
            $minutes = floor(($lackInSecondsToday % 3600) / 60);
            $seconds = $lackInSecondsToday % 60;
            $internTargetData['change_time_total'] = sprintf("-%02d:%02d:%02d", $hours, $minutes, $seconds);
            $internTargetData['total_lack_in_seconds'] = $lackInSecondsToday;
        }

        $hrUsers = User::whereHas('intern', function ($query) {
            $query->where('division_id', 18);
        })
            ->with('profile')
            ->where('is_active', true)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->profile->full_name ?? $user->name,
                ];
            });

        $userOffice = $todaysDetailSchedule?->office
            ?? Schedule::where('intern_id', $user->intern->id)->latest('id')->first()?->office
            ?? Office::first();
        $allOffices = Office::all();

        // Logbook Harian data
        $logActivityHistory = LogActivity::whereHas('detailSchedule.schedule', function ($query) use ($user) {
            $query->where('intern_id', $user->intern->id);
        })->with('status')->latest('date')->get();

        $todaysLogActivity = $todaysDetailSchedule?->logActivity ?? $logActivityHistory->first(function ($log) {
            return \Carbon\Carbon::parse($log->date)->isToday();
        });
        $hasFilledLogToday = !is_null($todaysLogActivity);

        // Data yang dikirim ke view sekarang menggunakan variabel $schedules yang sudah difilter
        $data = [
            "hrUsers" => $hrUsers,
            "user" => $user,
            "userOffice" => $userOffice,
            "allOffices" => $allOffices,
            "schedules" => $schedules,
            "listPermitCategory" => $listPermitCategory,
            "holiday_data" => $holidayData,
            "date_now" => $dateNow,
            "day_now" => DateNow::getCurrentDay(),
            "quotes" => $quotes->isSuccess() ? $quotes->getData()->pluck('quote') : [],
            "broadcast_list" => $relevant_broadcasts,
            "isHandRaised" => $handRaiseStatus,
            "currentHandRaise" => $currentHandRaise,
            "shift" => $todaysShift,
            "absenceHistory" => $absenceHistory,
            "lack" => $lackData,
            "intern_target" => $internTargetData,
            "stage" => $stage,
            "schedule_id" => $schedule_id,
            "detail_schedule_id" => $detail_schedule_id,
            "all_adjustable" => $all_adjustable,
            "logActivityHistory" => $logActivityHistory,
            "todaysLogActivity" => $todaysLogActivity,
            "hasFilledLogToday" => $hasFilledLogToday,
            "activeProjects" => $activeProjects,
            "hasActiveTasks" => $user->hasActiveTasks(),
            "activeTasksCount" => $user->getActiveTasksCount(),
        ];

        return view("users.index")->with($data);
    }


    public function startPermit(Request $request)
    {
        $request->validate([
            'type' => 'required|in:leave,toilet,prayer',
            'keterangan' => 'nullable|string|max:255',
            'authorized_by' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $permitType = $request->input('type');

        $attendance = Attendance::where('intern_id', $user->intern->id)
            ->whereDate('date', today())
            ->first();

        $hasCheckedIn = false;
        if ($attendance) {
            $hasCheckedIn = !is_null($attendance->start_time);
        }
        $hasActiveAdjustable = AdjustableAttd::where('intern_id', $user->intern->id)
            ->whereDate('date', today())
            ->whereNotNull('start_time')
            ->whereNull('end_time')
            ->exists();

        if (!$attendance) {
            $attendance = Attendance::create([
                'intern_id' => $user->intern->id,
                'date' => today()->toDateString(),
            ]);
        }

        if (!$hasCheckedIn && !$hasActiveAdjustable) {
            return redirect()->back()->with('error', 'Anda harus presensi masuk terlebih dahulu atau sedang dalam sesi ganti jam untuk memulai izin.');
        }

        $activePermit = PermitLog::where('attendance_id', $attendance->id)
            ->whereNull('end_time')
            ->exists();

        if ($activePermit) {
            return redirect()->back()->with('error', 'Anda sudah memiliki izin lain yang sedang aktif. Selesaikan terlebih dahulu.');
        }

        $limitSetting = PermitSetting::where('type', $permitType)->first();

        if ($limitSetting) {
            $todaysPermitCount = PermitLog::where('attendance_id', $attendance->id)
                ->where('type', $permitType)
                ->whereDate('start_time', today())
                ->count();

            if ($todaysPermitCount >= $limitSetting->max_daily_count) {
                return redirect()->back()->with('error', 'Anda telah mencapai batas maksimal untuk Izin ' . ucfirst($permitType) . ' hari ini (' . $limitSetting->max_daily_count . ' kali).');
            }
        }

        PermitLog::create([
            'attendance_id' => $attendance->id,
            'type' => $permitType,
            'description' => $request->input('keterangan'),
            'authorized_by' => $request->input('authorized_by'),
            'start_time' => now(),
        ]);

        return redirect()->back()->with('success', 'Izin ' . str_replace('_', ' ', $permitType) . ' telah dimulai.');
    }

    public function endPermit()
    {
        $user = Auth::user();

        $attendance = Attendance::where('intern_id', $user->intern->id)
            ->whereDate('date', today())
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', 'Data presensi hari ini tidak ditemukan.');
        }

        $activePermit = PermitLog::where('attendance_id', $attendance->id)
            ->whereNull('end_time')
            ->first();

        if (!$activePermit) {
            return redirect()->back()->with('error', 'Tidak ada izin aktif yang ditemukan untuk diselesaikan.');
        }

        $startTime = Carbon::parse($activePermit->start_time);
        $endTime = now();

        $durationInMinutes = ceil($startTime->diffInSeconds($endTime) / 60);

        $activePermit->update([
            'end_time' => $endTime->format('Y-m-d H:i:s'),
            'duration_in_minutes' => $durationInMinutes,
        ]);

        $totalPermitMinutesToday = PermitLog::where('attendance_id', $attendance->id)->sum('duration_in_minutes');
        $attendance->total_permit_min = $totalPermitMinutesToday;
        $attendance->save();

        return redirect()->back()->with('success', 'Izin ' . str_replace('_', ' ', $activePermit->type) . ' telah selesai.');
    }

    // Di dalam file UserController.php

    // Di dalam file UserController.php

    public function attendanceChangeView()
    {
        $user = auth()->user();
        $date_now = DateNow::getCurrentDate();
        $day_now = DateNow::getCurrentDay();
        $birth_date = optional($user->profile)->date_of_birth;
        $quotesResult = (now()->format('m-d') === ($birth_date ? Carbon::parse($birth_date)->format('m-d') : null))
            ? $this->quoteService->getByCategory('ultah')
            : $this->quoteService->getByCategory('quote');
        $quotes = $quotesResult->isSuccess() ? $quotesResult->getData()->pluck('quote') : [];

        if (!$user || !$user->intern) {
            return view('users.change-time', ['schedules' => collect(), 'user' => $user, 'day_now' => $day_now, 'date_now' => $date_now, 'quotes' => $quotes]);
        }

        $internId = $user->intern->id;

        $allSchedules = DetailSchedule::whereHas('schedule', function ($q) use ($internId) {
            $q->where('intern_id', $internId);
        })
            ->whereDate('date', '<=', today())
            ->with(['shift', 'permitReason.category'])
            ->get();

        $schedulesWithDeficit = collect();

        foreach ($allSchedules as $schedule) {
            if (!$schedule->shift) {
                continue;
            }

            $scheduleDate = Carbon::parse($schedule->date);
            $startOfDay = $scheduleDate->copy()->startOfDay();
            $endOfDay = $scheduleDate->copy()->endOfDay();

            $attendanceRecord = \App\Models\Attendance::where('intern_id', $internId)
                ->whereBetween('date', [$startOfDay, $endOfDay])
                ->first();
            $schedule->attendance = $attendanceRecord;

            $shiftMinutes = (int) ($schedule->shift->total_time_in_minute ?? 0);
            if ($shiftMinutes <= 0 && $schedule->shift && $schedule->shift->start_time && $schedule->shift->end_time && $schedule->shift->start_time !== '00:00:00') {
                $start = Carbon::parse($schedule->shift->start_time);
                $end = Carbon::parse($schedule->shift->end_time);
                $break = (int) ($schedule->shift->break_time_in_minute ?? 0);
                $shiftMinutes = max(0, $end->diffInMinutes($start) - $break);
            }
            $differenceInMinutes = 0;

            $permitReason = $schedule->permitReason;
            $categoryId = $permitReason?->permit_category_id;
            $description = strtolower($permitReason?->description ?? '');
            $proofUrl = $permitReason?->proof_url;
            $hasProof = !empty($proofUrl);
            $isSakit = ($categoryId == 1 || $categoryId == 2 || str_contains($description, 'sakit'));

            // 1. Skenario Izin Bebas Ganti Jam (Lunas / Bebas Jam yang disetujui) -> Tidak berhutang jam
            if ($schedule->attd_status_id == 3 && ($schedule->isChangeSchedule == 1 || $schedule->is_change_schedule_approved == 1)) {
                continue;
            }

            // 2. Skenario Izin Sakit:
            if ($schedule->attd_status_id == 3 && $isSakit) {
                $hours = floor($shiftMinutes / 60);
                $minutes = $shiftMinutes % 60;

                // Jika admin menetapkan Wajib Ganti Jam (isChangeSchedule == 2) atau tanpa bukti:
                if ($schedule->isChangeSchedule == 2 || !$hasProof || $categoryId == 2) {
                    $schedule->kategori = "Ganti Jam (Tanpa Bukti Surat)";
                    $schedule->kategori_badge = "bg-amber-100 text-amber-800 border-amber-300";
                    $schedule->keterangan = "Izin Sakit - Wajib Ganti Jam Tanpa Bukti Surat ({$hours} Jam {$minutes} Menit)";
                    $schedule->status = 'Belum Lunas';
                    $schedule->is_paid_off = false;
                    $schedulesWithDeficit->push($schedule);
                    continue;
                } elseif ($hasProof && ($schedule->isChangeSchedule === null || $schedule->isChangeSchedule == 0)) {
                    // Ada bukti surat dokter dan belum/menunggu keputusan admin -> tidak dihitung sebagai hutang jam dulu
                    continue;
                }
            }

            // 3. Skenario Izin Keperluan:
            if ($schedule->attd_status_id == 3) {
                $hours = floor($shiftMinutes / 60);
                $minutes = $shiftMinutes % 60;

                // Jika admin menetapkan Wajib Ganti Jam (isChangeSchedule == 2) atau tanpa bukti:
                if ($schedule->isChangeSchedule == 2 || !$hasProof) {
                    $schedule->kategori = "Ganti Jam (Tanpa Bukti Surat)";
                    $schedule->kategori_badge = "bg-orange-100 text-orange-800 border-orange-300";
                    $schedule->keterangan = "Izin Keperluan - Wajib Ganti Jam Tanpa Bukti Surat ({$hours} Jam {$minutes} Menit)";
                    $schedule->status = 'Belum Lunas';
                    $schedule->is_paid_off = false;
                    $schedulesWithDeficit->push($schedule);
                    continue;
                } elseif ($hasProof && ($schedule->isChangeSchedule === null || $schedule->isChangeSchedule == 0)) {
                    // Ada bukti surat izin dan masih menunggu keputusan admin -> tidak dihitung sebagai hutang jam dulu
                    continue;
                }
            }

            // 4. Skenario Alpha (Tidak Hadir) -> Otomatis berhutang jam kerja penuh sebesar shift
            if ($schedule->attd_status_id == 5) {
                $hours = floor($shiftMinutes / 60);
                $minutes = $shiftMinutes % 60;
                $schedule->kategori = "Alpha (Tidak Hadir)";
                $schedule->kategori_badge = "bg-rose-100 text-rose-800 border-rose-300";
                $schedule->keterangan = "Alpha (Tidak Hadir) - Wajib Ganti Jam {$hours} Jam {$minutes} Menit";
                $schedule->status = 'Belum Lunas';
                $schedule->is_paid_off = false;
                $schedulesWithDeficit->push($schedule);
                continue;
            }

            // Jika bukan kehadiran reguler (1 atau 2), lewati agar tidak salah hitung sebagai kekurangan jam reguler
            if ($schedule->attd_status_id != 1 && $schedule->attd_status_id != 2) {
                continue;
            }

            // ================================================================
            // LOGIKA PRESENSI REGULER (TERLAMBAT / PULANG AWAL)
            // ================================================================
            $workingMinutes = 0;
            if ($schedule->attendance && $schedule->attendance->start_time && $schedule->attendance->end_time) {
                // Skenario 1: SUDAH ABSEN MASUK DAN PULANG
                // Hitung ulang durasi dari timestamp mentah untuk akurasi maksimal
                $startTime = Carbon::parse($schedule->attendance->start_time);
                $endTime = Carbon::parse($schedule->attendance->end_time);
                $totalDuration = $endTime->diffInMinutes($startTime);
                $breakDuration = $schedule->attendance->total_break_min ?? 0;
                $workingMinutes = $totalDuration - $breakDuration;
            }

            // Perhitungan final: Total shift - jam kerja yang berhasil dihitung (bisa 0)
            $differenceInMinutes = $shiftMinutes - $workingMinutes;

            // Logika tambahan untuk hari ini: jika belum pulang, hitung berdasarkan keterlambatan saja
            if ($scheduleDate->isToday() && $schedule->attendance && $schedule->attendance->start_time && !$schedule->attendance->end_time) {
                $scheduledStartTime = Carbon::parse($schedule->date . ' ' . $schedule->shift->start_time);
                $actualStartTime = Carbon::parse($schedule->attendance->start_time);

                if ($actualStartTime->isAfter($scheduledStartTime)) {
                    $differenceInMinutes = $actualStartTime->diffInMinutes($scheduledStartTime);
                } else {
                    $differenceInMinutes = 0; // Jika masuk tepat waktu, belum ada kekurangan
                }
            }


            if ($differenceInMinutes > 1) { // Toleransi 1 menit
                $hours = floor($differenceInMinutes / 60);
                $minutes = $differenceInMinutes % 60;

                $schedule->kategori = "Kekurangan Jam Reguler";
                $schedule->kategori_badge = "bg-slate-100 text-slate-800 border-slate-300";
                $schedule->keterangan = "Kekurangan jam kerja {$hours} Jam {$minutes} Menit";
                $schedule->status = 'Belum Lunas';
                $schedule->is_paid_off = false;

                $schedulesWithDeficit->push($schedule);
            }
        }

        return view('users.change-time', [
            'schedules' => $schedulesWithDeficit->sortByDesc('date'),
            'user' => $user,
            'day_now' => $day_now,
            'date_now' => $date_now,
            'quotes' => $quotes
        ]);
    }


    public function addPermitPresence(StorePermitPresenceRequest $storePermitPresenceRequest)
    {
        $this->userService->createPermitPresence($storePermitPresenceRequest);
        return redirect()->back()->with('success', 'Data keterangan izin berhasil disimpan!');
    }

    public function toggleWhatsappNotification(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->intern && $user->intern->whatsappNumber) {
            $waNumber = $user->intern->whatsappNumber;
            $waNumber->is_notification_active = !$waNumber->is_notification_active;
            $waNumber->save();
            $status = $waNumber->is_notification_active ? 'diaktifkan' : 'dimatikan';
            return redirect()->back()->with('success', 'Notifikasi WhatsApp untuk orang tua berhasil ' . $status . '.');
        }

        return redirect()->back()->with('error', 'Nomor WhatsApp orang tua belum diatur. Harap hubungi admin.');
    }

    public function updateAccountLinks(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'github_url' => 'nullable|url|max:255',
            'gmail_account' => 'nullable|email|max:255',
            'figma_url' => 'nullable|url|max:500',
            'notes' => 'nullable|string|max:1000',
            'social_media_links' => 'nullable|array',
            'social_media_links.*.platform' => 'nullable|string|max:50',
            'social_media_links.*.username' => 'nullable|string|max:100',
            'social_media_links.*.url' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        if (!$user || !$user->intern) {
            return redirect()->back()->with('error', 'Data pemagang tidak ditemukan.');
        }

        $intern = $user->intern;

        // Clean up social media links
        $filteredLinks = null;
        if (isset($validated['social_media_links']) && is_array($validated['social_media_links'])) {
            $cleaned = array_values(array_filter($validated['social_media_links'], function ($item) {
                return !empty($item['platform']) || !empty($item['username']) || !empty($item['url']);
            }));
            $filteredLinks = !empty($cleaned) ? $cleaned : null;
        }

        $account = $intern->account;
        $dataToSave = [
            'github_url' => $validated['github_url'] ?? null,
            'gmail_account' => $validated['gmail_account'] ?? null,
            'figma_url' => $validated['figma_url'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'social_media_links' => $filteredLinks,
        ];

        if ($account) {
            $account->update($dataToSave);
        } else {
            $dataToSave['intern_id'] = $intern->id;
            \App\Models\InternAccount::create($dataToSave);
        }

        return redirect()->back()->with('success', 'Akun & portofolio divisi berhasil diperbarui!');
    }

    /**
     * Tampilkan halaman khusus Tugas & Akun Divisi pemagang
     */
    public function taskDivisionView(Request $request): View|RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login.view');
        }

        $user->load([
            'profile',
            'intern.division',
            'intern.school',
            'intern.account',
            'intern.detailProject.project.nameProject',
            'intern.detailProject.project.members.intern.user.profile',
        ]);

        // Tugas dan instruksi yang diberikan oleh admin/mentor kepada pemagang
        $mentorTasks = HandRaise::with(['resolver.profile', 'project.nameProject'])
            ->where('user_id', $user->id)
            ->where('type', 'new_task')
            ->latest()
            ->get();

        // Sinkronisasi tugas baru agar otomatis masuk ke tabel projects & detail_projects (tugas utama)
        $hasNewLinkedProject = false;
        foreach ($mentorTasks as $mTask) {
            // Hanya buat project jika admin sudah memberikan instruksi / respon tugas
            if (!$mTask->project_id && $user->intern && !empty($mTask->admin_response)) {
                $intern = $user->intern;
                $divName = $intern->division->name ?? 'Divisi';
                $taskTitle = !empty($mTask->notes) ? ('Project ' . $divName . ' - ' . $mTask->notes) : ('Project ' . $divName);

                $nameProject = NameProjects::firstOrCreate(
                    ['name' => $taskTitle],
                    ['name' => $taskTitle]
                );

                $newProj = Projects::create([
                    'name_project_id' => $nameProject->id,
                    'team' => $divName,
                    'description' => $mTask->admin_response ?: 'Tugas baru dari pembimbing',
                    'status' => $mTask->status === 'done' ? 'done' : 'progress',
                ]);

                DetailProjects::firstOrCreate([
                    'project_id' => $newProj->id,
                    'intern_id' => $intern->id,
                ]);

                $mTask->update(['project_id' => $newProj->id]);
                $hasNewLinkedProject = true;
            }
        }

        if ($hasNewLinkedProject) {
            $user->unsetRelation('intern');
            $user->load([
                'intern.division',
                'intern.school',
                'intern.account',
                'intern.detailProject.project.nameProject',
                'intern.detailProject.project.members.intern.user.profile',
            ]);
        }

        // Review presentasi / revisi terbaru untuk setiap project pemagang (revisi terbaru menimpa revisi lama)
        $latestPresentationReviews = HandRaise::with(['resolver.profile'])
            ->where('user_id', $user->id)
            ->where('type', 'presentation')
            ->latest('created_at')
            ->get()
            ->unique('project_id')
            ->keyBy('project_id');

        // Daftar project/tugas yang ditugaskan ke pemagang
        $detailProjects = $user->intern?->detailProject ?? collect();
        $assignedProjects = $detailProjects->map(function ($dp) {
            return $dp->project;
        })->filter()->unique('id');

        // Jika project memiliki status presentasi 'ready' atau 'done' (lulus tanpa revisi), pastikan statusnya 'done'
        foreach ($assignedProjects as $p) {
            $pReview = $latestPresentationReviews->get($p->id);
            if ($pReview && in_array($pReview->status, ['ready', 'done']) && $pReview->status !== 'needs_revision') {
                if ($p->status !== 'done') {
                    $p->update(['status' => 'done']);
                    $p->status = 'done';
                }
            }
        }

        // Sinkronkan mentorTasks terkait jika project sudah done agar tugas mentor juga otomatis selesai
        foreach ($mentorTasks as $mTask) {
            if ($mTask->project && $mTask->project->status === 'done' && $mTask->status !== 'done') {
                $mTask->update([
                    'status' => 'done',
                    'is_raised' => false,
                    'resolved_at' => $mTask->resolved_at ?? now(),
                ]);
                $mTask->status = 'done';
                $mTask->is_raised = false;
            }
        }

        // Pisahkan Project Aktif vs Selesai
        $activeProjects = $assignedProjects->filter(function ($p) {
            return $p->status !== 'done';
        });
        $completedProjects = $assignedProjects->filter(function ($p) {
            return $p->status === 'done';
        });

        // Cek apakah divisi pemagang adalah Programmer (Divisi 4)
        $divisionName = strtolower($user->intern?->division?->name ?? '');
        $divisionId = (int) ($user->intern?->division_id ?? 0);
        $isProgrammer = ($divisionId === 4)
            || str_contains($divisionName, 'programmer')
            || str_contains($divisionName, 'program');

        // Cek apakah divisi pemagang adalah UI/UX Designer (Divisi 1)
        $isUiUx = ($divisionId === 1)
            || str_contains($divisionName, 'ui/ux')
            || str_contains($divisionName, 'ui / ux')
            || (str_contains($divisionName, 'ui') && str_contains($divisionName, 'ux'));

        // Akun divisi pemagang
        $internAccount = $user->intern?->account;
        $userSocialLinks = $internAccount?->social_media_links ?? [];

        // Quotes untuk konsistensi layout user
        $birth_date = $user->profile->date_of_birth ?? null;
        $quotes = (now()->format('m-d') === ($birth_date ? Carbon::parse($birth_date)->format('m-d') : null))
            ? $this->quoteService->getByCategory('ultah')
            : $this->quoteService->getByCategory('quote');

        // Tugas aktif pembimbing: hanya tampilkan jika belum terwakili di kartu activeProjects dan project belum selesai
        $activeMentorTasks = $mentorTasks->filter(function ($t) use ($activeProjects, $completedProjects) {
            if ($t->status === 'done') {
                return false;
            }
            if ($t->project_id && $completedProjects->contains('id', $t->project_id)) {
                return false;
            }
            if ($t->project && $t->project->status === 'done') {
                return false;
            }
            $isActive = ($t->status === 'in_progress' || ($t->is_raised && $t->status !== 'done'));
            return $isActive && (!$t->project_id || !$activeProjects->contains('id', $t->project_id));
        });

        // Tugas selesai: tugas yang berstatus 'done', ATAU project-nya sudah berstatus 'done'
        $completedMentorTasks = $mentorTasks->filter(function ($t) use ($completedProjects) {
            $isDone = ($t->status === 'done') || ($t->project && $t->project->status === 'done');
            return $isDone && (!$t->project_id || !$completedProjects->contains('id', $t->project_id));
        });

        $date_now = DateNow::getCurrentDate();
        $day_now = DateNow::getCurrentDay();

        return view('users.tasks', compact(
            'user',
            'assignedProjects',
            'activeProjects',
            'completedProjects',
            'activeMentorTasks',
            'completedMentorTasks',
            'latestPresentationReviews',
            'isProgrammer',
            'isUiUx',
            'mentorTasks',
            'internAccount',
            'userSocialLinks',
            'quotes',
            'date_now',
            'day_now'
        ));
    }

    /**
     * Update link repository git project oleh pemagang
     */
    public function updateProjectRepository(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->intern) {
            return redirect()->route('login.view');
        }

        // Pastikan divisi pemagang adalah Programmer atau UI/UX
        $divisionName = strtolower($user->intern->division?->name ?? '');
        $divisionId = (int) ($user->intern->division_id ?? 0);
        $isProgrammer = ($divisionId === 4)
            || str_contains($divisionName, 'programmer')
            || str_contains($divisionName, 'program');
        $isUiUx = ($divisionId === 1)
            || str_contains($divisionName, 'ui/ux')
            || str_contains($divisionName, 'ui / ux')
            || (str_contains($divisionName, 'ui') && str_contains($divisionName, 'ux'));

        if (!$isProgrammer && !$isUiUx) {
            return redirect()->back()->with('error', 'Divisi Anda tidak memerlukan tautan repository/Figma pada tugas.');
        }

        // Pastikan project ini memang ditugaskan ke pemagang bersangkutan
        $isAssigned = $user->intern->detailProject()
            ->where('project_id', $id)
            ->exists();

        if (!$isAssigned) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk mengubah project ini.');
        }

        $validated = $request->validate([
            'repository_url' => 'required|url|max:500',
        ], [
            'repository_url.required' => 'Link hasil karya / repository wajib diisi.',
            'repository_url.url' => 'Format link harus berupa URL valid (contoh: https://github.com/... atau https://www.figma.com/...).',
            'repository_url.max' => 'Panjang link maksimal 500 karakter.',
        ]);

        $project = \App\Models\Projects::findOrFail($id);
        $project->update([
            'repository_url' => $validated['repository_url'],
        ]);

        return redirect()->back()->with('success', 'Link hasil karya project berhasil disimpan!');
    }

    /**
     * Update catatan pengerjaan revisi project oleh pemagang
     */
    public function updateProjectRevisionNote(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->intern) {
            return redirect()->route('login.view');
        }

        // Pastikan project ini memang ditugaskan ke pemagang bersangkutan
        $isAssigned = $user->intern->detailProject()
            ->where('project_id', $id)
            ->exists();

        if (!$isAssigned) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk mengubah project ini.');
        }

        $validated = $request->validate([
            'intern_revision_notes' => 'required|string|max:2000',
        ], [
            'intern_revision_notes.required' => 'Catatan revisi wajib diisi.',
            'intern_revision_notes.max' => 'Catatan revisi maksimal 2000 karakter.',
        ]);

        // Simpan catatan revisi pemagang ke review presentasi terbaru
        $review = HandRaise::where('user_id', $user->id)
            ->where('type', 'presentation')
            ->where(function ($q) use ($id) {
                $q->where('project_id', $id)
                    ->orWhereNull('project_id');
            })
            ->latest('created_at')
            ->first();

        if ($review) {
            $review->update([
                'project_id' => $id,
                'performance_notes' => $validated['intern_revision_notes']
            ]);
        }

        return redirect()->back()->with('success', 'Catatan revisi berhasil disimpan!');
    }
}
