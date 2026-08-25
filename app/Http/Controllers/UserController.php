<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Utils\DateNow;
use App\Models\Schedule;
use App\Utils\Converter;
use App\Models\Broadcast;
use App\Models\HandRaise;
use App\Models\PermitLog;
use App\Helper\LogConsole;
use App\Models\Attendance;
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
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StorePermitPresenceRequest;

class UserController extends Controller
{
    protected $userService;
    protected $attendanceService;
    protected $quoteService;
    protected $scheduleService;
    protected $permitReasonService;
    protected $holidayService;

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
        ->with(['shift', 'attendance'])
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
                    ->orderBy('created_at', 'asc')
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
        $handRaiseStatus = HandRaise::where('user_id', $user->id)->value('is_raised') ?? false;
        $quotes = (now()->format('m-d') === ($birth_date ? Carbon::parse($birth_date)->format('m-d') : null))
            ? $this->quoteService->getByCategory('ultah')
            : $this->quoteService->getByCategory('quote');

        $all_broadcasts = Broadcast::with(['divisions', 'users'])->latest()->get();
        $relevant_broadcasts = $all_broadcasts->filter(function($broadcast) use ($user) {
            switch ($broadcast->broadcast_type) {
                case 'all': return true;
                case 'division':
                    return $user->intern && $user->intern->division_id && $broadcast->divisions->contains('id', $user->intern->division_id);
                case 'specific':
                    return $broadcast->users->contains('id', $user->id);
                default: return false;
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

        // Data yang dikirim ke view sekarang menggunakan variabel $schedules yang sudah difilter
        $data = [
            "hrUsers" => $hrUsers, 
            "user" => $user, 
            "schedules" => $schedules, 
            "listPermitCategory" => $listPermitCategory,
            "holiday_data" => $holidayData, 
            "date_now" => $dateNow, 
            "day_now" => DateNow::getCurrentDay(),
            "quotes" => $quotes->isSuccess() ? $quotes->getData()->pluck('quote') : [],
            "broadcast_list" => $relevant_broadcasts, "isHandRaised" => $handRaiseStatus,
            "shift" => $todaysShift, "absenceHistory" => $absenceHistory, "lack" => $lackData,
            "intern_target" => $internTargetData, "stage" => $stage, "schedule_id" => $schedule_id,
            "detail_schedule_id" => $detail_schedule_id,
            "all_adjustable" => $all_adjustable,
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

    $allSchedules = DetailSchedule::whereHas('schedule', function($q) use ($internId) {
            $q->where('intern_id', $internId);
        })
        ->whereDate('date', '<=', today())
        ->with('shift')
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
        
        $shiftMinutes = $schedule->shift->total_time_in_minute ?? 0;
        $differenceInMinutes = 0;

        // ================================================================
        // LOGIKA BARU YANG LEBIH KUAT DAN DEFensif
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
}