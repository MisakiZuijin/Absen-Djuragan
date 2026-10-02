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
use App\Helper\ActivityLogger;
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

        $user->loadMissing(['profile', 'intern.account', 'intern.division', 'intern.detailProject.project.nameProject']);

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

        // Ambil jadwal hari ini langsung dari koleksi jadwal mingguan (mencegah duplikasi query DetailSchedule, Shift, Attendance)
        $todaysDetailSchedule = $schedules->first(function ($schedule) {
            return Carbon::parse($schedule->date)->isToday();
        });

        // Fallback jika hari ini tidak ada di jadwal mingguan (misal hari Minggu atau tanggal khusus)
        if (!$todaysDetailSchedule) {
            $todaysDetailSchedule = DetailSchedule::whereHas('schedule.intern', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->whereDate('date', today())
                ->with(['shift', 'attendance.permitLogs', 'schedule'])
                ->first();
        }

        $todaysShift = $todaysDetailSchedule?->shift;
        $absenceHistory = $todaysDetailSchedule?->attendance;

        // Cek pendaftaran pra-ganti jam pemagang
        $approvedRegistrationToday = $user->intern ? \App\Models\ChangeTimeRegistration::where('intern_id', $user->intern->id)
            ->where('status', 'approved')
            ->whereDate('requested_date', today())
            ->with(['shift', 'office'])
            ->first() : null;

        $activeRegistration = $user->intern ? \App\Models\ChangeTimeRegistration::where('intern_id', $user->intern->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) {
                $q->whereNull('requested_date')
                  ->orWhereDate('requested_date', '>=', today());
            })
            ->with(['shift', 'office', 'approver.profile', 'notes.user.profile'])
            ->orderByRaw("CASE WHEN status = 'approved' THEN 1 ELSE 2 END")
            ->latest('id')
            ->first() : null;

        if ($approvedRegistrationToday && !$todaysShift) {
            $todaysShift = $approvedRegistrationToday->shift;
        }

        $permitCategoriesResult = $this->permitReasonService->getAllPermitCategory();
        $listPermitCategory = $permitCategoriesResult->isSuccess() ? $permitCategoriesResult->getData() : [];

        $stageResult = $this->attendanceService->attendanceStatus($user->intern->id, $todaysDetailSchedule);
        $stage = $stageResult->isSuccess() ? $stageResult->getData()['stage'] : null;
        $schedule_id = $todaysDetailSchedule?->schedule_id;
        $detail_schedule_id = $todaysDetailSchedule?->id ?? 0;

        if (!$schedule_id) {
            $schedule_id = Schedule::where('intern_id', $user->intern->id)
                ->orderByDesc('id')
                ->value('id');
        }

        $all_adjustable = collect();
        if ($stageResult->isSuccess()) {
            $stageData = $stageResult->getData();
            if (isset($stageData['all_adjustable']) && !empty($stageData['all_adjustable'])) {
                $all_adjustable = collect($stageData['all_adjustable']);
            }
        }

        if ($all_adjustable->isEmpty() && $user->intern) {
            $activeChangeTime = \App\Models\ChangeTimeSession::where('intern_id', $user->intern->id)
                ->where('status', 'active')
                ->first();
            if ($activeChangeTime) {
                $all_adjustable = collect([$activeChangeTime]);
            } else {
                $all_adjustable = AdjustableAttd::where('intern_id', $user->intern->id)
                    ->where(function ($q) {
                        $q->whereDate('date', today())
                          ->orWhereNull('end_time');
                    })
                    ->get();
            }
        }

        $hasActiveAdjustable = $all_adjustable->whereNull('end_time')->isNotEmpty();
        $hasActiveRegular = $absenceHistory && !is_null($absenceHistory->start_time) && is_null($absenceHistory->end_time);

        $isAdjustable = $hasActiveAdjustable || (!$hasActiveRegular && $all_adjustable->isNotEmpty() && (!$absenceHistory || is_null($absenceHistory->start_time)));

        $birth_date = $user->profile->date_of_birth ?? null;
        $latestHandRaises = HandRaise::where('user_id', $user->id)->latest()->take(5)->get();
        $currentHandRaise = $latestHandRaises->first(fn($hr) => $hr->is_raised && !in_array($hr->status, ['done', 'rejected']))
            ?? $latestHandRaises->first();
        $handRaiseStatus = (bool) ($currentHandRaise?->is_raised && !in_array($currentHandRaise?->status, ['done', 'rejected']));
        $quotes = (now()->format('m-d') === ($birth_date ? Carbon::parse($birth_date)->format('m-d') : null))
            ? $this->quoteService->getByCategory('ultah')
            : $this->quoteService->getByCategory('quote');

        // Ambil HANYA Pengumuman (category = 'announcement') untuk riwayat dan popup pengumuman pemagang
        $all_announcements = Broadcast::announcements()
            ->with(['divisions', 'users', 'shifts', 'offices', 'images'])
            ->latest()
            ->take(20)
            ->get();
        $todaysShiftId = $todaysDetailSchedule?->shift_id;
        $todaysOfficeId = $todaysDetailSchedule?->office_id;
        $relevant_announcements = $all_announcements->filter(function ($announcement) use ($user, $todaysShiftId, $todaysOfficeId) {
            // Pengumuman baru terlihat setelah waktunya tiba
            if (!$announcement->isDue()) {
                return false;
            }
            switch ($announcement->broadcast_type) {
                case 'all':
                    return true;
                case 'division':
                    return $user->intern && $user->intern->division_id && $announcement->divisions->contains('id', $user->intern->division_id);
                case 'specific':
                    return $announcement->users->contains('id', $user->id);
                case 'shift':
                    return $todaysShiftId && $announcement->shifts->contains('id', $todaysShiftId);
                case 'office':
                    return $todaysOfficeId && $announcement->offices->contains('id', $todaysOfficeId);
                default:
                    return false;
            }
        });

        // Popup untuk pengumuman biasa (non-Livewire, muncul sekali per sesi saat pertama kali membuka dashboard)
        if (!Session::has('announcement_shown') && $relevant_announcements->isNotEmpty()) {
            Session::put('announcement_shown', true);
            Session::flash('firstAnnouncement', $relevant_announcements->first());
        }

        // Broadcast popup kini ditangani secara real-time dan terpisah oleh komponen Livewire BroadcastPopup

        $lackInSecondsToday = 0;
        if (is_object($todaysShift) && is_object($absenceHistory)) {
            // Hutang tambahan dari izin keluar wajib ganti jam (approved)
            $extraLeaveDebtSeconds = \App\Helper\TimeHelper::mandatoryReplaceDebtMinutes($absenceHistory) * 60;
            $hasClockedOut = !is_null($absenceHistory->end_time);
            if ($hasClockedOut) {
                $shiftMinutes = $todaysShift->total_time_in_minute ?? 0;
                $workingMinutes = ($absenceHistory->total_min ?? 0) - ($absenceHistory->total_break_min ?? 0);
                $differenceInMinutes = $shiftMinutes - $workingMinutes;
                $lackInSecondsToday = max(0, $differenceInMinutes * 60) + $extraLeaveDebtSeconds;
            } elseif (!is_null($absenceHistory->start_time)) {
                $scheduleStart = Carbon::parse($todaysShift->start_time);
                $checkInTime = Carbon::parse($absenceHistory->start_time);
                if ($checkInTime->gt($scheduleStart)) {
                    $lackInSecondsToday = $checkInTime->diffInSeconds($scheduleStart);
                }
                $lackInSecondsToday += $extraLeaveDebtSeconds;
            }
        }

        $lackData = ['isLess' => false];
        $internTargetData = ['change_time_total' => '00:00:00', 'total_lack_in_seconds' => 0];
        if ($lackInSecondsToday > 0) {
            $lackData['isLess'] = true;
            $hours = floor($lackInSecondsToday / 3600);
            $minutes = floor(($lackInSecondsToday % 3600) / 60);
            $seconds = $lackInSecondsToday % 60;
            // Format positif tanpa tanda minus
            $internTargetData['change_time_total'] = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
            $internTargetData['total_lack_in_seconds'] = $lackInSecondsToday;
        }

        // ====================================================================
        // [BARU GANTI JAM V2] DAFTAR ITEM HUTANG JAM & SESI GANTI JAM AKTIF
        // ====================================================================
        $debtCalcService = app(\App\Services\DebtCalculationService::class);
        $changeTimeService = app(\App\Services\ChangeTimeService::class);

        $internId = $user->intern?->id;
        $internDebts = $internId ? $debtCalcService->getInternDebts($internId, true) : collect();
        $totalDebtMinutes = (int) $internDebts->sum('debt_minutes');
        $totalDebtSeconds = $totalDebtMinutes * 60;
        $totalDebtHours = round($totalDebtMinutes / 60, 1);
        $totalDebtTimeFormatted = sprintf("%02d:%02d:00", floor($totalDebtMinutes / 60), $totalDebtMinutes % 60);

        $normalDebts = $internDebts->values();
        $smallDebts = $internDebts->values();

        $activeChangeTimeSession = $internId ? $changeTimeService->getActiveSession($internId) : null;
        $pendingChangeTimeSession = ($internId && !$activeChangeTimeSession) ? $changeTimeService->getPendingSession($internId) : null;

        $isAdjustable = !empty($activeChangeTimeSession);

        // Kandidat pemberi izin keluar: Admin (1) & Asisten Admin (6),
        // plus pemagang divisi Human Resource (18) jika ada.
        $hrUsers = \Illuminate\Support\Facades\Cache::remember('hr_approver_users_list', 1800, function () {
            return User::with('profile')
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereIn('role_id', [1, 6])
                        ->orWhereHas('intern', function ($q) {
                            $q->where('division_id', 18);
                        });
                })
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->profile->full_name ?? $user->username,
                    ];
                })
                ->toArray();
        });

        $allOffices = \Illuminate\Support\Facades\Cache::remember('offices_all', 3600, fn() => Office::all());
        $userOffice = null;
        if ($todaysDetailSchedule && $todaysDetailSchedule->office_id) {
            $userOffice = $allOffices->firstWhere('id', $todaysDetailSchedule->office_id);
        }
        if (!$userOffice && $user->intern) {
            $userOffice = $allOffices->firstWhere('id', $user->intern->office_id);
        }
        if (!$userOffice) {
            $userOffice = $allOffices->first();
        }

        // Logbook Harian data
        $hasFilledLogToday = !empty($todaysDetailSchedule?->log_activity_id);
        $todaysLogActivity = $hasFilledLogToday ? $todaysDetailSchedule?->logActivity : null;
        $logActivityHistory = collect();

        $activeTasksCount = $user->getActiveTasksCount();
        $hasActiveTasks = $activeTasksCount > 0;

        // Cek popup pulang otomatis yang belum dilihat (muncul 1x saat lupa absen pulang)
        $autoEndPopup = Attendance::with(['detailSchedules.shift'])
            ->where('intern_id', $user->intern->id)
            ->where('is_auto_end', true)
            ->where('auto_end_notified', false)
            ->orderByDesc('date')
            ->first();

        // Konfigurasi & Opsi untuk Sesi Ganti Jam
        $changeTimeSetting = \App\Models\ChangeTimeSetting::getSettings();
        $allowedShiftIds = $changeTimeSetting->allowed_shift_ids ?: [];
        $shiftsForChangeTime = \App\Models\Shift::where('id', '>', 1)
            ->when(!empty($allowedShiftIds), fn($q) => $q->whereIn('id', $allowedShiftIds))
            ->orderBy('start_time')->get();

        $allowedOfficeIds = $changeTimeSetting->allowed_office_ids ?: [];
        $officesForChangeTime = \App\Models\Office::when(!empty($allowedOfficeIds), fn($q) => $q->whereIn('id', $allowedOfficeIds))
            ->orderBy('name')->get();
        if ($officesForChangeTime->isEmpty()) {
            $officesForChangeTime = $allOffices;
        }

        $defaultOfficeIdForChangeTime = $changeTimeSetting->default_office_id ?: ($officesForChangeTime->first()?->id ?: 1);
        $isChangeTimeAllowedToday = !empty($approvedRegistrationToday) || !empty($activeChangeTimeSession);

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
            "broadcast_list" => $relevant_announcements,
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
            "todaysDetailSchedule" => $todaysDetailSchedule,
            "activeProjects" => $activeProjects,
            "hasActiveTasks" => $hasActiveTasks,
            "activeTasksCount" => $activeTasksCount,
            "autoEndPopup" => $autoEndPopup,
            "changeTimeSetting" => $changeTimeSetting,
            "shiftsForChangeTime" => $shiftsForChangeTime,
            "officesForChangeTime" => $officesForChangeTime,
            "defaultOfficeIdForChangeTime" => $defaultOfficeIdForChangeTime,
            "isChangeTimeAllowedToday" => $isChangeTimeAllowedToday,
            "approvedRegistrationToday" => $approvedRegistrationToday,
            "activeRegistration" => $activeRegistration,
            "internDebts" => $internDebts,
            "normalDebts" => $normalDebts,
            "smallDebts" => $smallDebts,
            "activeChangeTimeSession" => $activeChangeTimeSession,
            "pendingChangeTimeSession" => $pendingChangeTimeSession,
            "totalDebtSeconds" => $totalDebtSeconds,
            "totalDebtHours" => $totalDebtHours,
            "totalDebtTimeFormatted" => $totalDebtTimeFormatted,
            "isAdjustable" => $isAdjustable,
        ];

        return view("users.index")->with($data);
    }

    /**
     * Menandai popup notifikasi pulang otomatis sebagai telah dilihat oleh pemagang (popup 1x).
     */
    public function dismissAutoEndPopup(int $id)
    {
        $user = auth()->user();
        if (!$user || !$user->intern) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $attendance = Attendance::where('id', $id)
            ->where('intern_id', $user->intern->id)
            ->first();

        if ($attendance) {
            $attendance->update(['auto_end_notified' => true]);
            return response()->json(['success' => true, 'message' => 'Notifikasi pulang otomatis berhasil ditutup.']);
        }

        return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
    }

    /**
     * Menyimpan laporan pemagang untuk broadcast yang mewajibkan laporan.
     * Setelah laporan tersimpan, popup wajib laporan tidak muncul lagi.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Broadcast $broadcast
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitBroadcastReport(Request $request, Broadcast $broadcast)
    {
        abort_unless($broadcast->requires_report && $broadcast->isDue(), 404);

        $validated = $request->validate([
            'report' => 'required|string|min:10|max:2000',
        ], [
            'report.required' => 'Laporan wajib diisi sebelum menutup pengumuman ini.',
            'report.min' => 'Laporan minimal 10 karakter.',
            'report.max' => 'Laporan maksimal 2000 karakter.',
        ]);

        \App\Models\BroadcastReport::firstOrCreate(
            ['broadcast_id' => $broadcast->id, 'user_id' => $request->user()->id],
            ['report' => $validated['report']]
        );

        $userName = $request->user()->name ?? $request->user()->username ?? 'User';
        ActivityLogger::log('CREATE', 'Broadcast', "Pemagang {$userName} mengirim tanggapan/laporan pengumuman: {$broadcast->title}", [
            'broadcast_id' => $broadcast->id
        ], $request->user());

        return redirect()->route('user.home')->with('success', 'Laporan berhasil dikirim. Terima kasih!');
    }


    public function startPermit(Request $request)
    {
        $request->validate([
            'type' => 'required|in:leave,toilet,prayer',
            'keterangan' => 'nullable|string|max:255',
            'authorized_by' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        if (!$user || !$user->intern) {
            return redirect()->back()->with('error', 'Data pemagang tidak ditemukan.');
        }

        $internId = $user->intern->id;
        $permitType = $request->input('type');

        // Cek apakah ada absensi reguler hari ini
        $attendance = Attendance::where('intern_id', $internId)
            ->whereDate('date', today())
            ->first();

        $hasCheckedIn = false;
        if ($attendance) {
            $hasCheckedIn = !is_null($attendance->start_time);
        }

        // Cek apakah ada sesi ganti jam aktif (baik ChangeTimeSession maupun AdjustableAttd)
        $hasActiveAdjustable = \App\Models\ChangeTimeSession::where('intern_id', $internId)
            ->where('status', 'active')
            ->exists()
            || \App\Models\AdjustableAttd::where('intern_id', $internId)
            ->whereNotNull('start_time')
            ->whereNull('end_time')
            ->exists();

        if (!$hasCheckedIn && !$hasActiveAdjustable) {
            return redirect()->back()->with('error', 'Anda harus presensi masuk terlebih dahulu atau sedang dalam sesi ganti jam untuk memulai izin.');
        }

        // Jika belum ada record attendance hari ini (misal ganti jam di hari libur/minggu), buatkan record shell
        if (!$attendance) {
            $attendance = Attendance::firstOrCreate(
                ['intern_id' => $internId, 'date' => today()->toDateString()]
            );
        }

        // Cek apakah ada izin lain yang sedang aktif
        $activePermit = PermitLog::whereHas('attendance', function ($q) use ($internId) {
            $q->where('intern_id', $internId);
        })
            ->whereNull('end_time')
            ->exists();

        if ($activePermit) {
            return redirect()->back()->with('error', 'Anda sudah memiliki izin lain yang sedang aktif. Selesaikan terlebih dahulu.');
        }

        $limitSetting = PermitSetting::where('type', $permitType)->first();

        // Hanya cek batas harian jika max_daily_count > 0 (0 = unlimited / tidak dibatasi)
        if ($limitSetting && (int) $limitSetting->max_daily_count > 0) {
            $todaysPermitCount = PermitLog::whereHas('attendance', function ($q) use ($internId) {
                $q->where('intern_id', $internId);
            })
                ->where('type', $permitType)
                ->whereDate('start_time', today())
                ->count();

            if ($todaysPermitCount >= (int) $limitSetting->max_daily_count) {
                return redirect()->back()->with('error', 'Anda telah mencapai batas maksimal untuk Izin ' . ucfirst($permitType) . ' hari ini (' . $limitSetting->max_daily_count . ' kali).');
            }
        }

        PermitLog::create([
            'attendance_id' => $attendance->id,
            'type' => $permitType,
            'description' => $request->input('keterangan'),
            'authorized_by' => $request->input('authorized_by'),
            'start_time' => now(),
            'approval_status' => $permitType === 'leave' ? 'pending' : 'approved',
            'is_mandatory_replace' => false,
            'agreed_duration_minutes' => 0,
        ]);

        $internName = $user->profile->full_name ?? $user->name ?? $user->username;
        $typeName = match ($permitType) {
            'leave' => 'Keluar Kantor',
            'prayer' => 'Shalat',
            'toilet' => 'Toilet',
            default => ucfirst($permitType)
        };
        ActivityLogger::log('CREATE', 'Izin', "Pemagang {$internName} memulai Izin {$typeName}" . ($request->filled('keterangan') ? " ({$request->input('keterangan')})" : ''), [
            'type' => $permitType,
            'authorized_by' => $request->input('authorized_by')
        ], $user);

        return redirect()->back()->with('success', 'Izin ' . str_replace('_', ' ', $permitType) . ' telah dimulai.');
    }

    public function endPermit()
    {
        $user = Auth::user();
        if (!$user || !$user->intern) {
            return redirect()->back()->with('error', 'Data pemagang tidak ditemukan.');
        }

        $internId = $user->intern->id;

        $activePermit = PermitLog::whereHas('attendance', function ($q) use ($internId) {
            $q->where('intern_id', $internId);
        })
            ->whereNull('end_time')
            ->latest('start_time')
            ->first();

        if (!$activePermit) {
            return redirect()->back()->with('error', 'Tidak ada izin aktif yang ditemukan untuk diselesaikan.');
        }

        $startTime = Carbon::parse($activePermit->start_time);
        $endTime = now();

        $durationInMinutes = (int) ceil($startTime->diffInSeconds($endTime) / 60);

        // Ambil konfigurasi batas durasi
        $limitSetting = PermitSetting::where('type', $activePermit->type)->first();
        $maxDuration = $limitSetting ? (int) $limitSetting->max_duration_minutes : 0;

        $isOverdue = false;
        $overdueMinutes = 0;
        $updateData = [
            'end_time' => $endTime->format('Y-m-d H:i:s'),
            'duration_in_minutes' => $durationInMinutes,
        ];

        // Konsekuensi jika izin Sholat atau Toilet melebihi batas waktu:
        // Kelebihan waktu otomatis dimasukkan ke hutang jam (wajib ganti jam)
        if (in_array($activePermit->type, ['prayer', 'toilet']) && $maxDuration > 0 && $durationInMinutes > $maxDuration) {
            $isOverdue = true;
            $overdueMinutes = $durationInMinutes - $maxDuration;

            $updateData['is_mandatory_replace'] = true;
            $updateData['agreed_duration_minutes'] = $overdueMinutes;
            $updateData['approval_status'] = 'approved';

            $overdueNote = "Melebihi batas waktu ({$durationInMinutes}m / batas {$maxDuration}m, kelebihan {$overdueMinutes}m masuk hutang jam)";
            $updateData['description'] = $activePermit->description 
                ? ($activePermit->description . ' | ' . $overdueNote) 
                : $overdueNote;
        }

        $activePermit->update($updateData);

        $attendance = $activePermit->attendance;
        if ($attendance) {
            $totalPermitMinutesToday = PermitLog::where('attendance_id', $attendance->id)->sum('duration_in_minutes');
            $attendance->total_permit_min = $totalPermitMinutesToday;
            if ($activePermit->type === 'leave' && $attendance->permit_start && !$attendance->permit_back) {
                $attendance->permit_back = $endTime;
            }
            $attendance->save();
        }

        $internName = $user->profile->full_name ?? $user->name ?? $user->username;
        $typeName = match ($activePermit->type) {
            'leave' => 'Keluar Kantor',
            'prayer' => 'Shalat',
            'toilet' => 'Toilet',
            default => ucfirst($activePermit->type)
        };

        $logDesc = "Pemagang {$internName} menyelesaikan Izin {$typeName} (Durasi: {$durationInMinutes} menit)";
        if ($isOverdue) {
            $logDesc .= " - MELEBIHI BATAS {$maxDuration}m (Kelebihan {$overdueMinutes}m masuk Hutang Jam)";
        }

        ActivityLogger::log('UPDATE', 'Izin', $logDesc, [
            'type' => $activePermit->type,
            'duration_minutes' => $durationInMinutes,
            'max_duration_minutes' => $maxDuration,
            'overdue_minutes' => $overdueMinutes,
            'is_mandatory_replace' => $isOverdue,
        ], $user);

        $successMsg = 'Izin ' . str_replace('_', ' ', $activePermit->type) . " telah selesai (Durasi: {$durationInMinutes} menit).";
        if ($isOverdue) {
            $successMsg .= " Anda melebihi batas waktu ({$maxDuration} menit), kelebihan {$overdueMinutes} menit secara otomatis dimasukkan ke target Hutang Jam Anda.";
        }

        return redirect()->back()->with('success', $successMsg);
    }

    // Di dalam file UserController.php

    // Di dalam file UserController.php

    public function attendanceChangeView()
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if ($user instanceof \App\Models\User) {
            $user->loadMissing(['profile', 'intern']);
        }
        $date_now = DateNow::getCurrentDate();
        $day_now = DateNow::getCurrentDay();
        $birth_date = optional($user?->profile)->date_of_birth;
        $quotesResult = (now()->format('m-d') === ($birth_date ? Carbon::parse($birth_date)->format('m-d') : null))
            ? $this->quoteService->getByCategory('ultah')
            : $this->quoteService->getByCategory('quote');
        $quotes = $quotesResult->isSuccess() ? $quotesResult->getData()->pluck('quote') : [];

        if (!$user || !$user->intern) {
            return view('users.change-time', ['schedules' => collect(), 'user' => $user, 'day_now' => $day_now, 'date_now' => $date_now, 'quotes' => $quotes]);
        }

        $internId = $user->intern->id;
        $schedulesWithDeficit = $this->calculateInternScheduleDeficits($internId, true);

        return view('users.change-time', [
            'schedules' => $schedulesWithDeficit->sortByDesc('date'),
            'user' => $user,
            'day_now' => $day_now,
            'date_now' => $date_now,
            'quotes' => $quotes
        ]);
    }

    /**
     * Menghitung daftar jadwal yang memiliki kekurangan/hutang jam kerja,
     * setelah memperhitungkan jam ganti (adjustable attendance) yang telah dikerjakan.
     *
     * @param int $internId
     * @param bool $includeToday
     * @return \Illuminate\Support\Collection
     */
    private function calculateInternScheduleDeficits(int $internId, bool $includeToday = false): \Illuminate\Support\Collection
    {
        $dateLimit = $includeToday ? DateNow::getCurrentDateYMD() : Carbon::yesterday()->format('Y-m-d');

        $schedules = DetailSchedule::whereHas('schedule', function ($q) use ($internId) {
            $q->where('intern_id', $internId);
        })
            ->whereDate('date', '<=', $dateLimit)
            ->with(['shift', 'attendance.permitLogs', 'permitReason.category', 'adjustableAttendance'])
            ->orderBy('date', 'asc') // Urutan kronologis (FIFO) agar hutang terlama terlunasi terlebih dahulu
            ->get();

        // Pre-fetch data attendance berdasarkan tanggal sebagai fallback jika attendance_id di detail_schedules null
        $attendancesByDate = \App\Models\Attendance::where('intern_id', $internId)
            ->whereDate('date', '<=', $dateLimit)
            ->with('permitLogs')
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->date)->format('Y-m-d'));

        // Pre-fetch daftar tanggal libur nasional
        $holidayDates = \App\Models\Holiday::whereDate('date', '<=', $dateLimit)
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // Ambil semua sesi ganti jam pemagang yang valid (bukan ditolak / is_approved != 2)
        $allAdjustables = \App\Models\AdjustableAttd::where('intern_id', $internId)
            ->where(function ($q) {
                $q->whereNull('is_approved')
                  ->orWhere('is_approved', '!=', 2);
            })
            ->get();

        // Hitung total pool menit ganti jam dari seluruh sesi ganti jam yang valid
        $totalGantiJamPool = 0;
        foreach ($allAdjustables as $adj) {
            $isApprovedVal = is_array($adj->is_approved) 
                ? ($adj->is_approved['value'] ?? 0) 
                : ($adj->is_approved->value ?? $adj->is_approved ?? 0);
            if ((int)$isApprovedVal !== 2) {
                $totalMin = (int) ($adj->total_min ?? 0);
                $breakMin = (int) ($adj->total_break_min ?? 0);
                $adjMins = max(0, $totalMin - $breakMin);
                if ($adjMins <= 0 && !empty($adj->start_time) && !empty($adj->end_time)) {
                    $adjMins = max(0, Carbon::parse($adj->end_time)->diffInMinutes(Carbon::parse($adj->start_time)) - $breakMin);
                }
                $totalGantiJamPool += $adjMins;
            }
        }

        $deficitList = collect();

        foreach ($schedules as $schedule) {
            if (!$schedule->shift) {
                continue;
            }

            $scheduleDate = Carbon::parse($schedule->date);
            $dateKey = $scheduleDate->format('Y-m-d');
            if (!$schedule->attendance) {
                $schedule->attendance = $attendancesByDate->get($dateKey);
            }

            // Hari libur / Minggu tanpa presensi masuk tidak menghasilkan hutang jam
            $isSunday = $scheduleDate->isSunday();
            $isHoliday = in_array($dateKey, $holidayDates, true);
            $hasCheckIn = $schedule->attendance && !empty($schedule->attendance->start_time);
            if (($isSunday || $isHoliday) && !$hasCheckIn && (int)$schedule->attd_status_id !== 3) {
                continue;
            }

            $shiftMinutes = (int) ($schedule->shift->total_time_in_minute ?? 0);
            if ($shiftMinutes <= 0 && $schedule->shift && $schedule->shift->start_time && $schedule->shift->end_time && $schedule->shift->start_time !== '00:00:00') {
                $start = Carbon::parse($schedule->shift->start_time);
                $end = Carbon::parse($schedule->shift->end_time);
                $break = (int) ($schedule->shift->break_time_in_minute ?? 0);
                $shiftMinutes = max(0, $end->diffInMinutes($start) - $break);
            }

            $permitReason = $schedule->permitReason;
            $categoryId = $permitReason?->permit_category_id;
            $description = strtolower($permitReason?->description ?? '');
            $proofUrl = $permitReason?->proof_url;
            $hasProof = !empty($proofUrl);
            $isSakit = ($categoryId == 1 || $categoryId == 2 || str_contains($description, 'sakit'));

            $isDeficit = false;
            $baseDeficitMinutes = 0;
            $categoryName = '';
            $badgeClass = '';
            $type = '';

            // 1. Skenario Izin Bebas Ganti Jam (Lunas / Bebas Jam yang disetujui) -> Tidak berhutang
            if ($schedule->attd_status_id == 3 && ($schedule->isChangeSchedule == 1 || $schedule->is_change_schedule_approved == 1)) {
                continue;
            }

            // 2. Skenario Izin Sakit
            if ($schedule->attd_status_id == 3 && $isSakit) {
                if ($schedule->isChangeSchedule == 2 || !$hasProof || $categoryId == 2) {
                    $isDeficit = true;
                    $baseDeficitMinutes = $shiftMinutes;
                    $categoryName = "Ganti Jam (Tanpa Bukti Surat)";
                    $badgeClass = "bg-amber-100 text-amber-800 border-amber-300";
                    $type = "sakit_wajib_ganti";
                } elseif ($hasProof && ($schedule->isChangeSchedule === null || $schedule->isChangeSchedule == 0)) {
                    continue; // Menunggu keputusan admin
                }
            }

            // 3. Skenario Izin Keperluan
            elseif ($schedule->attd_status_id == 3) {
                if ($schedule->isChangeSchedule == 2 || !$hasProof) {
                    $isDeficit = true;
                    $baseDeficitMinutes = $shiftMinutes;
                    $categoryName = "Ganti Jam (Tanpa Bukti Surat)";
                    $badgeClass = "bg-orange-100 text-orange-800 border-orange-300";
                    $type = "izin_wajib_ganti";
                } elseif ($hasProof && ($schedule->isChangeSchedule === null || $schedule->isChangeSchedule == 0)) {
                    continue; // Menunggu keputusan admin
                }
            }

            // 4. Skenario Alpha (Tidak Hadir)
            elseif ($schedule->attd_status_id == 5) {
                $isDeficit = true;
                $baseDeficitMinutes = $shiftMinutes;
                $categoryName = "Alpha (Tidak Hadir)";
                $badgeClass = "bg-rose-100 text-rose-800 border-rose-300";
                $type = "alpha";
            }

            // 5. Skenario Presensi Reguler (Hadir / Belum Absen / Pulang Awal / Lupa Absen)
            elseif ($schedule->attd_status_id == 1 || $schedule->attd_status_id == 2) {
                $workingMinutes = 0;
                if ($schedule->attendance && $schedule->attendance->start_time && $schedule->attendance->end_time) {
                    $startTime = Carbon::parse($schedule->attendance->start_time);
                    $endTime = Carbon::parse($schedule->attendance->end_time);
                    $totalDuration = $endTime->diffInMinutes($startTime);
                    $breakDuration = $schedule->attendance->total_break_min ?? 0;
                    $workingMinutes = max(0, $totalDuration - $breakDuration);
                }

                $differenceInMinutes = $shiftMinutes - $workingMinutes;

                // Hari ini yang belum absen pulang: hitung dari keterlambatan masuk jika ada
                if ($scheduleDate->isToday() && $schedule->attendance && $schedule->attendance->start_time && !$schedule->attendance->end_time) {
                    $scheduledStartTime = Carbon::parse($schedule->date . ' ' . $schedule->shift->start_time);
                    $actualStartTime = Carbon::parse($schedule->attendance->start_time);
                    if ($actualStartTime->isAfter($scheduledStartTime)) {
                        $differenceInMinutes = $actualStartTime->diffInMinutes($scheduledStartTime);
                    } else {
                        $differenceInMinutes = 0;
                    }
                }

                $extraLeaveDebt = \App\Helper\TimeHelper::mandatoryReplaceDebtMinutes($schedule->attendance);
                $differenceInMinutes += $extraLeaveDebt;

                if ($differenceInMinutes > 1) { // Toleransi 1 menit
                    $isDeficit = true;
                    $baseDeficitMinutes = $differenceInMinutes;
                    $categoryName = "Kekurangan Jam Reguler";
                    $badgeClass = "bg-slate-100 text-slate-800 border-slate-300";
                    $type = "regular";
                }
            }

            if (!$isDeficit || $baseDeficitMinutes <= 1) {
                continue;
            }

            // Alokasikan jam ganti jam yang tersedia untuk melunasi kekurangan jadwal ini
            $coveredByGantiJam = min($baseDeficitMinutes, $totalGantiJamPool);
            $totalGantiJamPool -= $coveredByGantiJam;
            $remainingDeficit = $baseDeficitMinutes - $coveredByGantiJam;

            // Jika sudah tercover lunas (sisa <= 1 menit), jangan tampilkan di daftar hutang
            if ($remainingDeficit <= 1) {
                continue;
            }

            $remHours = floor($remainingDeficit / 60);
            $remMins = $remainingDeficit % 60;
            $origHours = floor($baseDeficitMinutes / 60);
            $origMins = $baseDeficitMinutes % 60;

            if ($coveredByGantiJam > 0) {
                $coveredH = floor($coveredByGantiJam / 60);
                $coveredM = $coveredByGantiJam % 60;
                $descText = "Kekurangan jam kerja {$remHours} Jam {$remMins} Menit (Sisa dari {$origHours} Jam {$origMins} Menit, telah diganti {$coveredH} Jam {$coveredM} Menit)";
            } else {
                $descText = "Kekurangan jam kerja {$remHours} Jam {$remMins} Menit";
            }

            $schedule->kategori = $categoryName;
            $schedule->kategori_badge = $badgeClass;
            $schedule->keterangan = $descText;
            $schedule->status = 'Belum Lunas';
            $schedule->is_paid_off = false;
            $schedule->base_deficit_minutes = $baseDeficitMinutes;
            $schedule->covered_ganti_jam_minutes = $coveredByGantiJam;
            $schedule->remaining_deficit_minutes = $remainingDeficit;
            $schedule->deficit_type = $type;

            $deficitList->push($schedule);
        }

        return $deficitList;
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
            'gmail_password' => 'nullable|string|max:255',
            'figma_url' => 'nullable|url|max:500',
            'spreadsheet_url' => 'nullable|url|max:500',
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
        $dataToSave = [];

        // Update fields only for platforms allowed/enabled by admin
        if (!$account || $account->isPlatformEnabled('github')) {
            $dataToSave['github_url'] = $validated['github_url'] ?? null;
            $dataToSave['gmail_account'] = $validated['gmail_account'] ?? null;
            if (array_key_exists('gmail_password', $validated)) {
                $dataToSave['gmail_password'] = $validated['gmail_password'];
            }
        }
        if (!$account || $account->isPlatformEnabled('figma')) {
            $dataToSave['figma_url'] = $validated['figma_url'] ?? null;
        }
        if (!$account || $account->isPlatformEnabled('sosmed')) {
            $dataToSave['social_media_links'] = $filteredLinks;
        }
        $dataToSave['notes'] = $validated['notes'] ?? null;

        if ($account) {
            $account->update($dataToSave);
        } else {
            $dataToSave['intern_id'] = $intern->id;
            \App\Models\InternAccount::create($dataToSave);
        }

        $internName = $user->profile?->full_name ?? $user->name ?? $user->username;
        ActivityLogger::log('UPDATE', 'User Management', "Pemagang {$internName} memperbarui tautan portofolio & akun divisi", [], $user);

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
        $doneProjectIds = [];
        foreach ($assignedProjects as $p) {
            $pReview = $latestPresentationReviews->get($p->id);
            if ($pReview && in_array($pReview->status, ['ready', 'done']) && $pReview->status !== 'needs_revision') {
                if ($p->status !== 'done') {
                    $doneProjectIds[] = $p->id;
                    $p->status = 'done';
                }
            }
        }
        if (!empty($doneProjectIds)) {
            Projects::whereIn('id', $doneProjectIds)->update(['status' => 'done']);
        }

        // Sinkronkan mentorTasks terkait jika project sudah done agar tugas mentor juga otomatis selesai
        $doneTaskIds = [];
        foreach ($mentorTasks as $mTask) {
            if ($mTask->project && $mTask->project->status === 'done' && $mTask->status !== 'done') {
                $doneTaskIds[] = $mTask->id;
                $mTask->status = 'done';
                $mTask->is_raised = false;
            }
        }
        if (!empty($doneTaskIds)) {
            HandRaise::whereIn('id', $doneTaskIds)->update([
                'status' => 'done',
                'is_raised' => false,
                'resolved_at' => now(),
            ]);
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
        $quotesResult = (now()->format('m-d') === ($birth_date ? Carbon::parse($birth_date)->format('m-d') : null))
            ? $this->quoteService->getByCategory('ultah')
            : $this->quoteService->getByCategory('quote');
        $quotes = $quotesResult->isSuccess() ? $quotesResult->getData()->pluck('quote') : [];

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
