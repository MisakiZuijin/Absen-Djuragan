<?php

namespace App\Livewire;

use App\DTO\AttendanceDTO;
use App\Models\CheckinMessage;   // [BARU]
use App\Services\Attendance\AttendanceState;
use App\Services\AttendanceService;
use App\Utils\AttendanceStatus;
use App\Utils\AttendanceType;
use Carbon\Carbon;               // [BARU]
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class AttdStatusButton extends Component
{
    protected AttendanceService $attendanceService;
    public mixed $hrUsers = [];
    public mixed $shift = null;
    public bool $hasShift = false;
    public mixed $user = null;
    public mixed $stage = null;
    public mixed $attendanceHistory = null;
    public mixed $adjustableTimeHistory = null;
    public mixed $scheduleId = null;
    public mixed $detailScheduleId = null;
    public int $totalChangeTime = 0;

    public bool $showModal = false;

    public bool $isAdjustable = false;
    public string $notes = '';
    public bool $hasFilledLogToday = false;
    public bool $isHandRaised = false;
    public mixed $currentHandRaise = null;
    public bool $handRaiseChecked = false;
    public bool $hasActiveTasks = false;
    public int $activeTasksCount = 0;

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount($hrUsers = [], $shift = null, $hasShift = false, $user = null, mixed $stage = AttendanceStatus::AllDone, $scheduleId = null, $detailScheduleId = null, $absenceHistory = null, $adjustableTimeHistory = null, $isHandRaised = false, $hasFilledLogToday = null, $currentHandRaise = null, $hasActiveTasks = false, $activeTasksCount = 0)
    {
        $this->scheduleId = $scheduleId;
        $this->detailScheduleId = $detailScheduleId;
        $this->hrUsers = $hrUsers;
        $this->shift = $shift;
        $this->hasShift = (bool) $hasShift;
        $this->user = $user;
        $this->stage = $stage;
        $this->attendanceHistory = $absenceHistory;
        $this->adjustableTimeHistory = $adjustableTimeHistory;
        $this->hasFilledLogToday = $hasFilledLogToday !== null ? (bool) $hasFilledLogToday : false;
        $this->isHandRaised = (bool) $isHandRaised;
        $this->currentHandRaise = $currentHandRaise;
        $this->handRaiseChecked = true;
        $this->hasActiveTasks = (bool) $hasActiveTasks;
        $this->activeTasksCount = (int) $activeTasksCount;

        if (!$this->hasActiveTasks && $this->user instanceof \App\Models\User) {
            $this->activeTasksCount = $this->user->getActiveTasksCount();
            $this->hasActiveTasks = $this->activeTasksCount > 0;
        }

        if ($hasFilledLogToday === null && $this->user && $this->user->intern) {
            $this->hasFilledLogToday = \App\Models\LogActivity::whereHas('detailSchedule.schedule', function ($query) {
                $query->where('intern_id', $this->user->intern->id);
            })->whereDate('date', today())->exists();
        }
    }

    public function isGpsRequiredForStage(mixed $stage = null): bool
    {
        // 1. User level: jika user diatur GPS non-aktif (is_gps_activate = 0)
        if ($this->user && isset($this->user->is_gps_activate) && (int) $this->user->is_gps_activate === 0) {
            return false;
        }

        // 2. Schedule level: check work_type and shift
        $workType = 'wfo';
        $shiftGpsActive = 1;

        if ($this->detailScheduleId) {
            $detailSchedule = \App\Models\DetailSchedule::with('shift')->find($this->detailScheduleId);
            if ($detailSchedule) {
                $workType = strtolower($detailSchedule->work_type ?? 'wfo');
                if ($detailSchedule->shift && isset($detailSchedule->shift->is_gps_active)) {
                    $shiftGpsActive = (int) $detailSchedule->shift->is_gps_active;
                }
            }
        } elseif ($this->shift && is_string($this->shift)) {
            $shiftModel = \App\Models\Shift::where('name', $this->shift)->first();
            if ($shiftModel && isset($shiftModel->is_gps_active)) {
                $shiftGpsActive = (int) $shiftModel->is_gps_active;
            }
        }

        if ($shiftGpsActive === 0) {
            return false;
        }

        if ($workType === 'wfh') {
            return false;
        }

        return true;
    }

    public function getIsGpsRequiredProperty(): bool
    {
        return $this->isGpsRequiredForStage($this->stage);
    }

    protected $listeners = ['actionAttd'];

    public function actionAttd(mixed $stage, ?string $text = null, $latitude = null, $longitude = null, $attendanceId = null, $adjustableId = null)
    {
        if ($stage == AttendanceStatus::AllDone->value) {
            $this->dispatch('post-created', status: true, message: "Semua Aktifitas mu hari ini sudah selesai");
            return;
        }

        // Use provided IDs if available, otherwise fallback to component properties
        $attendanceIdToUse = $attendanceId ?: ($this->attendanceHistory->id ?? 0);
        $adjustableIdToUse = $adjustableId ?: ($this->adjustableTimeHistory->id ?? 0);

        $result = $this->attendanceService->attendanceAction(new AttendanceDTO(
            $this->user->id,
            $stage,
            false,
            $attendanceIdToUse,
            $adjustableIdToUse,
            $text,
            $latitude,
            $longitude,
            $this->scheduleId,
            $this->detailScheduleId,
            $this->totalChangeTime
        ));


        $this->dispatch('post-created', status: $result->isSuccess(), message: $result->getMessage());
        $data = $result->getData();
        if ($result->isSuccess()) {

            if (isset($data["shift"])) $this->shift = $data["shift"]->name;

            if (isset($data["schedule_id"])) $this->scheduleId = $data["schedule_id"];
            if (isset($data["detail_schedule_id"])) $this->detailScheduleId = $data["detail_schedule_id"];

            if (isset($data["totalChangeTime"])) $this->totalChangeTime = $data["totalChangeTime"];

            $this->stage = $data["stage"];

            if (isset($data['absenceHistory'])) {
                $this->attendanceHistory = $data['absenceHistory'];
                $isWithoutBreak = isset($data["shift"]) && $data["shift"]->break_time_in_minute <= 0;
                $this->dispatch('attd-info-refresh', attdData: $data['absenceHistory'], isWithoutBreak: $isWithoutBreak);
            }

            if (isset($data['adjustableTimeHistory'])) {
                $adjustableData = $data['adjustableTimeHistory'];
                $this->adjustableTimeHistory = $adjustableData;
                $this->dispatch('adjst-info-refresh', adjstData: $adjustableData);
            }

            // ============================================================
            // [BARU] POPUP CHECK-IN
            // Hanya stage AttendanceIn (tombol Masuk) yang memicu popup.
            // Popup TIDAK muncul untuk ganti jam/istirahat/izin/pulang.
            // ============================================================
            // Tabel `attendances` TIDAK punya kolom shift_id, jadi shift
            // harus diambil dari data service atau relasi detail schedule.
            $isCheckinStage = (int) $stage === AttendanceStatus::AttendanceIn->value;

            if ($isCheckinStage && isset($data['absenceHistory'])) {
                $shiftModel = $data['shift'] ?? null;
                $detailScheduleId = $data['detail_schedule_id'] ?? $this->detailScheduleId;
                if (!$shiftModel && $detailScheduleId) {
                    $shiftModel = \App\Models\DetailSchedule::find($detailScheduleId)?->shift;
                }

                $popup = $this->buildCheckinPopup($data['absenceHistory'], $shiftModel);
                if ($popup) {
                    $this->dispatch('show-checkin-popup', popup: $popup);
                }
            }
            // ===== AKHIR POPUP CHECK-IN =====
        }
    }

    // ============================================================
    // [BARU] Helper: bangun data popup check-in dari data presensi
    // ============================================================
    private function buildCheckinPopup(mixed $absenData, mixed $shift): ?array
    {
        try {
            if (empty($absenData)) {
                return null;
            }

            // Normalisasi: pastikan data bisa diakses sebagai objek
            if (is_array($absenData)) {
                $absenData = (object) $absenData;
            }

            if (empty($absenData->start_time) || empty($shift) || empty($shift->start_time)) {
                return null;
            }

            $timezone = config('app.timezone', 'Asia/Jakarta');

            // Waktu absen masuk
            $absenTime = Carbon::parse($absenData->start_time)->setTimezone($timezone);

            // Waktu mulai shift pada tanggal absen
            $attendanceDate = !empty($absenData->date)
                ? Carbon::parse($absenData->date)->setTimezone($timezone)
                : Carbon::now($timezone);
            $shiftStartTime = Carbon::parse(
                $attendanceDate->format('Y-m-d') . ' ' . $shift->start_time,
                $timezone
            );

            // Toleransi 5 menit (sama seperti logika lama)
            $isLate = $absenTime->greaterThan($shiftStartTime)
                && abs($absenTime->diffInMinutes($shiftStartTime)) > 5;

            // Ambil row TANPA filter is_active agar toggle admin berfungsi:
            // - row belum ada di DB        -> pakai pesan default
            // - row ada, is_active = false -> popup TIDAK ditampilkan
            $popupMsg = CheckinMessage::where('type', $isLate ? 'late' : 'on_time')->first();

            if ($popupMsg === null) {
                return [
                    'type'    => $isLate ? 'late' : 'on_time',
                    'message' => $isLate
                        ? 'Anda terlambat hari ini. Harap datang tepat waktu!'
                        : 'Selamat datang! Anda tepat waktu hari ini 🎉',
                    'image'   => null,
                ];
            }

            if (!$popupMsg->is_active) {
                return null; // admin menonaktifkan -> tidak ada popup
            }

            return [
                'type'    => $isLate ? 'late' : 'on_time',
                'message' => $popupMsg->message,
                'image'   => $popupMsg->image ? asset('checkin-images/' . $popupMsg->image) : null,
            ];
        } catch (\Throwable $e) {
            Log::error('Gagal build popup check-in: ' . $e->getMessage());
            return null;
        }
    }

    public function render()
    {
        $userId = $this->user->id ?? auth()->id();
        /** @var \App\Models\User|null $currentUser */
        $currentUser = $this->user instanceof \App\Models\User ? $this->user : auth()->user();

        if ($currentUser) {
            $activeTasksCount = $currentUser->getActiveTasksCount();
            $this->activeTasksCount = $activeTasksCount;
            $this->hasActiveTasks = $activeTasksCount > 0;
        }

        $latestHandRaises = \App\Models\HandRaise::where('user_id', $userId)->latest()->take(5)->get();
        $currentHandRaise = $latestHandRaises->first(fn($hr) => $hr->is_raised && !in_array($hr->status, ['done', 'rejected']))
            ?? $latestHandRaises->first();

        $this->isHandRaised = (bool) ($currentHandRaise?->is_raised && !in_array($currentHandRaise?->status, ['done', 'rejected']));
        $this->currentHandRaise = $currentHandRaise;

        // Data for attendance, permits, and adjustable
        $activePermit = null;
        $activeLeavePermit = null;
        $hasReachedLeaveLimit = false;
        $hasReachedPrayerLimit = false;
        $hasCheckedIn = false;
        $hasActiveAdjustable = false;

        $intern = $currentUser?->intern;
        if ($intern) {
            $internId = $intern->id;
            $todayAttendance = $this->attendanceHistory ?? null;
            if (!$todayAttendance && empty($this->detailScheduleId)) {
                $todayAttendance = \App\Models\Attendance::where('intern_id', $internId)
                    ->whereDate('date', today())
                    ->first(['id', 'start_time']);
            }

            if ($todayAttendance) {
                $startTime = is_array($todayAttendance) ? ($todayAttendance['start_time'] ?? null) : $todayAttendance->start_time;
                $todaysAttendanceId = is_array($todayAttendance) ? ($todayAttendance['id'] ?? null) : $todayAttendance->id;
                $hasCheckedIn = !is_null($startTime);

                if ($todaysAttendanceId) {
                    $todayPermits = ($todayAttendance instanceof \App\Models\Attendance && $todayAttendance->relationLoaded('permitLogs'))
                        ? $todayAttendance->permitLogs
                        : \App\Models\PermitLog::where('attendance_id', $todaysAttendanceId)->get();

                    $active = $todayPermits->firstWhere('end_time', null);
                    if ($active) {
                        $activePermit = $active;
                        if ($active->type === 'leave') {
                            $activeLeavePermit = $active;
                        }
                    }

                    $prayerCountToday = $todayPermits->where('type', 'prayer')->count();
                    $leaveCountToday = $todayPermits->where('type', 'leave')->count();

                    $permitSettings = \Illuminate\Support\Facades\Cache::remember('permit_settings_daily_limits', 3600, function () {
                        return \App\Models\PermitSetting::whereIn('type', ['prayer', 'leave'])
                            ->get(['type', 'max_daily_count'])
                            ->keyBy('type');
                    });

                    $hasReachedPrayerLimit = $prayerCountToday >= ($permitSettings->get('prayer')?->max_daily_count ?? 5);
                    $hasReachedLeaveLimit = $leaveCountToday >= ($permitSettings->get('leave')?->max_daily_count ?? 1);
                }
            }

            $hasActiveAdjustable = false;
            if (!empty($this->adjustableTimeHistory)) {
                $adjStartTime = is_array($this->adjustableTimeHistory)
                    ? ($this->adjustableTimeHistory['start_time'] ?? null)
                    : ($this->adjustableTimeHistory->start_time ?? null);
                $hasActiveAdjustable = !empty($adjStartTime);
            }
        }

        return view('livewire.attd-status-button', [
            'isHandRaised' => $this->isHandRaised,
            'currentHandRaise' => $this->currentHandRaise,
            'hrUsers' => $this->hrUsers ?? [],
            'hasActiveTasks' => $this->hasActiveTasks,
            'activeTasksCount' => $this->activeTasksCount,
            'activePermit' => $activePermit,
            'activeLeavePermit' => $activeLeavePermit,
            'hasReachedLeaveLimit' => $hasReachedLeaveLimit,
            'hasReachedPrayerLimit' => $hasReachedPrayerLimit,
            'hasCheckedIn' => $hasCheckedIn,
            'hasActiveAdjustable' => $hasActiveAdjustable,
        ]);
    }
}
