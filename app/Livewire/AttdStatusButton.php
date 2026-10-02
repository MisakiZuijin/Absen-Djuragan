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

    public bool $showStatusBantuanModal = false;
    public bool $presentationDoneStep = false;
    public bool $showOutcomeConfirmModal = false;
    public string $pendingOutcome = '';
    public ?int $pendingHandRaiseId = null;
    public ?int $pendingProjectId = null;
    public string $pendingProjectName = '';
    public string $taskLinkInput = '';
    public string $taskLinkType = 'general';
    public string $taskLinkLabel = 'Link Pengumpulan / Drive Tugas';
    public string $taskLinkPlaceholder = 'https://drive.google.com/...';
    public bool $showAdminResponseModal = false;
    public ?array $adminResponseData = null;
    public int $mountTimestamp = 0;
    public string $followUpQuestionText = '';
    public bool $isAskingFollowUp = false;

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount($hrUsers = [], $shift = null, $hasShift = false, $user = null, mixed $stage = AttendanceStatus::AllDone, $scheduleId = null, $detailScheduleId = null, $absenceHistory = null, $adjustableTimeHistory = null, $isHandRaised = false, $hasFilledLogToday = null, $currentHandRaise = null, $hasActiveTasks = false, $activeTasksCount = 0)
    {
        $this->mountTimestamp = now()->timestamp;
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

    public function isChangeTimeAllowed(): bool
    {
        $internId = $this->user?->intern?->id ?? auth()->user()?->intern?->id;
        if (!$internId) {
            return false;
        }

        $today = \Carbon\Carbon::today('Asia/Jakarta')->toDateString();

        // 1. Jika pemagang sedang dalam sesi ganti jam yang aktif (V2 atau legacy), wajib selalu diizinkan
        $changeTimeService = app(\App\Services\ChangeTimeService::class);
        $activeChangeTimeSession = $changeTimeService->getActiveSession($internId);
        if ($activeChangeTimeSession) {
            return true;
        }

        $activeLegacy = \App\Models\AdjustableAttd::where('intern_id', $internId)
            ->whereNotNull('start_time')
            ->whereNull('end_time')
            ->whereDate('date', $today)
            ->exists();
        if ($activeLegacy) {
            return true;
        }

        // 2. Pemagang harus memiliki catatan hutang jam kerja yang perlu diganti
        $debtService = app(\App\Services\DebtCalculationService::class);
        $debts = $debtService->getInternDebts($internId, true);
        if ($debts->isEmpty()) {
            return false;
        }

        // 3. Cek pengaturan kebijakan jadwal hari ganti jam dari Admin
        $setting = \App\Models\ChangeTimeSetting::getSettings();
        $isSunday = \Carbon\Carbon::now('Asia/Jakarta')->isSunday();
        $isHoliday = \App\Models\Holiday::whereDate('date', $today)->exists();
        $isOffDay = $isSunday || $isHoliday;

        // Jika dibatasi hanya hari libur (Minggu / Tanggal Merah)
        if ($setting->restrict_to_holidays) {
            // 3a. Hari ini adalah hari libur atau Minggu -> Langsung diizinkan
            if ($isOffDay) {
                return true;
            }

            // 3b. Hari kerja biasa: HANYA jika ada pendaftaran yang SUDAH DISETUJUI (approved) oleh Admin KHUSUS hari ini
            $hasApprovedRegistrationToday = \App\Models\ChangeTimeRegistration::where('intern_id', $internId)
                ->where('status', 'approved')
                ->where(function ($q) use ($today) {
                    $q->whereNull('requested_date')
                      ->orWhereDate('requested_date', $today);
                })
                ->exists();

            return (bool) $hasApprovedRegistrationToday;
        }

        // Jika mode "Setiap Hari" (tidak dibatasi hanya hari libur): diizinkan
        return true;
    }

    public function getIsGpsRequiredProperty(): bool
    {
        return $this->isGpsRequiredForStage($this->stage);
    }

    protected $listeners = [
        'actionAttd',
        'open-status-bantuan-modal' => 'openStatusBantuanModal',
    ];

    public function actionAttd(
        mixed $stage,
        ?string $text = null,
        $latitude = null,
        $longitude = null,
        $attendanceId = null,
        $adjustableId = null,
        $shiftId = null,
        $officeId = null,
        $detailScheduleId = null
    ) {
        $stageInt = is_object($stage) && enum_exists(get_class($stage)) ? (int)$stage->value : (int)$stage;

        if ($stageInt == AttendanceStatus::AllDone->value) {
            $this->dispatch('post-created', status: true, message: "Semua Aktifitas mu hari ini sudah selesai");
            return;
        }

        // ============================================================
        // [BARU GANTI JAM V2] Penanganan aksi ganti jam via ChangeTimeService
        // ============================================================
        $isGantiJamStage = in_array($stageInt, [
            AttendanceStatus::AdjustableIn->value,
            AttendanceStatus::StartBreakAdjustable->value,
            AttendanceStatus::EndBreakAdjustable->value,
            AttendanceStatus::AdjustableOut->value
        ]);

        if ($isGantiJamStage) {
            $changeTimeService = app(\App\Services\ChangeTimeService::class);
            $userId = $this->user->id ?? auth()->id();
            $internId = $this->user->intern?->id ?? auth()->user()?->intern?->id;

            if ($stageInt === AttendanceStatus::AdjustableIn->value) {
                $todayDate = \Carbon\Carbon::today('Asia/Jakarta')->toDateString();
                $activeReg = \App\Models\ChangeTimeRegistration::where('intern_id', $internId)
                    ->whereIn('status', ['pending', 'approved'])
                    ->where(function ($q) use ($todayDate) {
                        $q->whereNull('requested_date')
                          ->orWhereDate('requested_date', $todayDate);
                    })
                    ->latest('id')
                    ->first();

                $regTargetScheduleIds = [];
                if ($activeReg && !empty($activeReg->target_schedule_ids)) {
                    $regTargetScheduleIds = is_array($activeReg->target_schedule_ids)
                        ? $activeReg->target_schedule_ids
                        : (json_decode($activeReg->target_schedule_ids, true) ?? []);
                }

                $targetScheduleIds = !empty($detailScheduleId)
                    ? (is_array($detailScheduleId)
                        ? $detailScheduleId
                        : (is_numeric($detailScheduleId)
                            ? [(int) $detailScheduleId]
                            : (is_string($detailScheduleId) && !empty($detailScheduleId)
                                ? array_map('intval', explode(',', $detailScheduleId))
                                : [])))
                    : $regTargetScheduleIds;

                $finalShiftId = (int) ($shiftId ?: ($activeReg?->shift_id ?: 2));
                $finalOfficeId = (int) ($officeId ?: ($activeReg?->office_id ?: 1));

                $result = $changeTimeService->startSession(
                    userId: $userId,
                    targetScheduleIds: $targetScheduleIds,
                    shiftId: $finalShiftId,
                    officeId: $finalOfficeId,
                    latitude: $latitude,
                    longitude: $longitude,
                    message: $text
                );

                $this->dispatch('post-created', status: $result->isSuccess(), message: $result->getMessage());

                if ($result->isSuccess()) {
                    $session = $result->getData();
                    $this->adjustableTimeHistory = $session;
                    $this->isAdjustable = true;
                    $this->stage = AttendanceStatus::BreakOrBack;
                    $this->dispatch('change-time-refresh', sessionData: ['id' => $session->id]);
                    $this->dispatch('attd-info-ajdst', isAdjustable: true);
                    $this->dispatch('ganti-jam-started', message: $result->getMessage());
                }
                return;
            }

            // Untuk Istirahat, Kembali, Pulang: Cari sesi aktif
            $activeSession = $changeTimeService->getActiveSession($internId);
            if (!$activeSession) {
                $this->dispatch('post-created', status: false, message: "Sesi ganti jam aktif tidak ditemukan.");
                return;
            }

            if ($stageInt === AttendanceStatus::StartBreakAdjustable->value) {
                $result = $changeTimeService->startBreak($activeSession->id, $text);
                $this->dispatch('post-created', status: $result->isSuccess(), message: $result->getMessage());
                if ($result->isSuccess()) {
                    $this->stage = AttendanceStatus::EndBreakAdjustable;
                    $this->dispatch('change-time-refresh', sessionData: ['id' => $activeSession->id]);
                }
                return;
            }

            if ($stageInt === AttendanceStatus::EndBreakAdjustable->value) {
                $result = $changeTimeService->endBreak($activeSession->id, $text);
                $this->dispatch('post-created', status: $result->isSuccess(), message: $result->getMessage());
                if ($result->isSuccess()) {
                    $this->stage = AttendanceStatus::AdjustableOut;
                    $this->dispatch('change-time-refresh', sessionData: ['id' => $activeSession->id]);
                }
                return;
            }

            if ($stageInt === AttendanceStatus::AdjustableOut->value) {
                $result = $changeTimeService->endSession($activeSession->id, $latitude, $longitude, $text);
                $this->dispatch('post-created', status: $result->isSuccess(), message: $result->getMessage());
                if ($result->isSuccess()) {
                    $this->stage = AttendanceStatus::AllDone;
                    $this->isAdjustable = false;
                    $this->dispatch('change-time-refresh', sessionData: ['id' => $activeSession->id]);
                    $this->dispatch('attd-info-ajdst', isAdjustable: false);
                    $this->dispatch('ganti-jam-ended', message: $result->getMessage());

                    // Update ChangeTimeRegistration status to completed if exists
                    \App\Models\ChangeTimeRegistration::where('intern_id', $internId)
                        ->where('status', 'approved')
                        ->whereDate('requested_date', \Carbon\Carbon::today('Asia/Jakarta')->toDateString())
                        ->update(['status' => 'completed']);
                }
                return;
            }
        }

        // Use provided IDs if available, otherwise fallback to component properties
        $attendanceIdToUse = $attendanceId ?: (is_array($this->attendanceHistory) ? ($this->attendanceHistory['id'] ?? 0) : ($this->attendanceHistory->id ?? 0));
        
        $adjHistId = is_array($this->adjustableTimeHistory) ? ($this->adjustableTimeHistory['id'] ?? null) : ($this->adjustableTimeHistory->id ?? null);
        if (!$adjHistId && !empty($this->user->intern?->id)) {
            $adjHistId = \App\Models\AdjustableAttd::where('intern_id', $this->user->intern->id)->whereNotNull('start_time')->whereNull('end_time')->latest('id')->value('id');
        }
        $adjustableIdToUse = $adjustableId ?: ($adjHistId ?: 0);
        $detailScheduleIdToUse = $detailScheduleId ?: $this->detailScheduleId;

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
            $detailScheduleIdToUse,
            $this->totalChangeTime,
            null,
            $shiftId,
            $officeId
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
                $this->dispatch('attd-info-ajdst', isAdjustable: false);
            }

            if (isset($data['adjustableTimeHistory'])) {
                $adjustableData = $data['adjustableTimeHistory'];
                $this->adjustableTimeHistory = $adjustableData;
                $this->dispatch('adjst-info-refresh', adjstData: $adjustableData);
                $this->dispatch('attd-info-ajdst', isAdjustable: true);
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

        // Cek notifikasi real-time untuk tanggapan admin & tugas baru
        $this->checkRealtimeNotifications();

        $latestHandRaises = \App\Models\HandRaise::with(['messages.user.profile', 'resolver.profile'])
            ->where('user_id', $userId)->latest()->take(5)->get();
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
            if (!$todayAttendance) {
                $todayAttendance = \App\Models\Attendance::where('intern_id', $internId)
                    ->whereDate('date', today())
                    ->first(['id', 'start_time', 'end_time', 'break_time', 'back_time']);
            }

            if ($todayAttendance) {
                $startTime = is_array($todayAttendance) ? ($todayAttendance['start_time'] ?? null) : $todayAttendance->start_time;
                $endTime = is_array($todayAttendance) ? ($todayAttendance['end_time'] ?? null) : $todayAttendance->end_time;
                $hasCheckedIn = !is_null($startTime);
                $isWorkingShiftToday = !empty($startTime) && empty($endTime);
            }

            // Cari izin aktif milik pemagang (berlaku baik saat shift reguler maupun ganti jam)
            $activePermit = \App\Models\PermitLog::whereHas('attendance', function ($q) use ($internId) {
                $q->where('intern_id', $internId);
            })
                ->whereNull('end_time')
                ->latest('start_time')
                ->first();

            if ($activePermit && $activePermit->type === 'leave') {
                $activeLeavePermit = $activePermit;
            }

            // Hitung jumlah izin hari ini untuk cek batas harian
            $todayPermitLogs = \App\Models\PermitLog::whereHas('attendance', function ($q) use ($internId) {
                $q->where('intern_id', $internId);
            })
                ->whereDate('start_time', today())
                ->get();

            $prayerCountToday = $todayPermitLogs->where('type', 'prayer')->count();
            $leaveCountToday = $todayPermitLogs->where('type', 'leave')->count();

            $permitSettings = \Illuminate\Support\Facades\Cache::remember('permit_settings_daily_limits', 3600, function () {
                return \App\Models\PermitSetting::whereIn('type', ['prayer', 'leave', 'toilet'])
                    ->get(['type', 'max_daily_count'])
                    ->keyBy('type');
            });

            $prayerLimit = (int) ($permitSettings->get('prayer')?->max_daily_count ?? 5);
            $hasReachedPrayerLimit = $prayerLimit > 0 && ($prayerCountToday >= $prayerLimit);

            $leaveLimit = (int) ($permitSettings->get('leave')?->max_daily_count ?? 1);
            $hasReachedLeaveLimit = $leaveLimit > 0 && ($leaveCountToday >= $leaveLimit);

            $hasActiveAdjustable = false;
            $activeAdjustableModel = null;

            // [BARU GANTI JAM V2] Cek sesi ChangeTimeSession aktif
            $changeTimeService = app(\App\Services\ChangeTimeService::class);
            $activeChangeTimeSession = $internId ? $changeTimeService->getActiveSession($internId) : null;

            if ($activeChangeTimeSession) {
                $hasActiveAdjustable = true;
                $activeAdjustableModel = $activeChangeTimeSession;
                $this->adjustableTimeHistory = $activeChangeTimeSession;
                $this->isAdjustable = true;
            } else {
                if ($this->adjustableTimeHistory instanceof \App\Models\ChangeTimeSession) {
                    $this->adjustableTimeHistory = null;
                    $this->isAdjustable = false;
                }

                if (!empty($this->adjustableTimeHistory)) {
                    $adjStartTime = is_array($this->adjustableTimeHistory)
                        ? ($this->adjustableTimeHistory['start_time'] ?? null)
                        : ($this->adjustableTimeHistory->start_time ?? null);
                    $adjEndTime = is_array($this->adjustableTimeHistory)
                        ? ($this->adjustableTimeHistory['end_time'] ?? null)
                        : ($this->adjustableTimeHistory->end_time ?? null);
                    $hasActiveAdjustable = !empty($adjStartTime) && empty($adjEndTime);
                    $activeAdjustableModel = $this->adjustableTimeHistory;
                }
            }

            if (!$hasActiveAdjustable && !empty($internId)) {
                $activeAdjustableModel = \App\Models\AdjustableAttd::where('intern_id', $internId)
                    ->whereNotNull('start_time')
                    ->whereNull('end_time')
                    ->latest('id')
                    ->first();
                if ($activeAdjustableModel) {
                    $hasActiveAdjustable = true;
                    $this->adjustableTimeHistory = $activeAdjustableModel;
                }
            }
        }

        $targetDebtMinutes = 0;
        $gantiJamWorkedMinutes = 0;
        $canClockOutGantiJam = true;
        $remainingGantiJamMinutes = 0;

        if ($hasActiveAdjustable && !empty($activeAdjustableModel)) {
            if ($activeAdjustableModel instanceof \App\Models\ChangeTimeSession) {
                $targetDebtMinutes = (int) $activeAdjustableModel->total_target_debt_minutes;
                $sessionDateStr = $activeAdjustableModel->session_date->format('Y-m-d');
                $startDateTime = \Carbon\Carbon::parse($sessionDateStr . ' ' . $activeAdjustableModel->start_time, 'Asia/Jakarta');

                if (!empty($activeAdjustableModel->break_time) && empty($activeAdjustableModel->back_time)) {
                    // Sedang istirahat: durasi kerja di-pause pada saat istirahat dimulai
                    $breakDateTime = \Carbon\Carbon::parse($sessionDateStr . ' ' . $activeAdjustableModel->break_time, 'Asia/Jakarta');
                    $gantiJamWorkedMinutes = max(0, $breakDateTime->diffInMinutes($startDateTime));
                } else {
                    $now = \Carbon\Carbon::now('Asia/Jakarta');
                    $grossMins = max(0, $now->diffInMinutes($startDateTime));
                    $gantiJamWorkedMinutes = max(0, $grossMins - (int) $activeAdjustableModel->total_break_minutes);
                }

                $canClockOutGantiJam = true;
                $remainingGantiJamMinutes = max(0, $targetDebtMinutes - $gantiJamWorkedMinutes);
                $adjId = $activeAdjustableModel->id;
            } else {
                $detailSchedId = is_array($activeAdjustableModel) ? ($activeAdjustableModel['detail_schedule_id'] ?? null) : ($activeAdjustableModel->detail_schedule_id ?? null);
                $adjId = is_array($activeAdjustableModel) ? ($activeAdjustableModel['id'] ?? null) : ($activeAdjustableModel->id ?? null);
                $adjStartTime = is_array($activeAdjustableModel) ? ($activeAdjustableModel['start_time'] ?? null) : ($activeAdjustableModel->start_time ?? null);
                $adjBreakTime = is_array($activeAdjustableModel) ? ($activeAdjustableModel['break_time'] ?? null) : ($activeAdjustableModel->break_time ?? null);
                $adjBackTime = is_array($activeAdjustableModel) ? ($activeAdjustableModel['back_time'] ?? null) : ($activeAdjustableModel->back_time ?? null);

                if ($detailSchedId) {
                    $targetDebtMinutes = \App\Helper\TimeHelper::getScheduleTargetDebtMinutes($detailSchedId, $adjId);
                }

                if ($adjStartTime) {
                    if ($adjBreakTime && empty($adjBackTime)) {
                        $gantiJamWorkedMinutes = max(0, \Carbon\Carbon::parse($adjBreakTime)->diffInMinutes(\Carbon\Carbon::parse($adjStartTime)));
                    } else {
                        $breakMins = 0;
                        if ($adjBreakTime && $adjBackTime) {
                            $breakMins = \Carbon\Carbon::parse($adjBackTime)->diffInMinutes(\Carbon\Carbon::parse($adjBreakTime));
                        }
                        $gantiJamWorkedMinutes = max(0, \Carbon\Carbon::now()->diffInMinutes(\Carbon\Carbon::parse($adjStartTime)) - $breakMins);
                    }

                    $canClockOutGantiJam = true;
                    $remainingGantiJamMinutes = max(0, $targetDebtMinutes - $gantiJamWorkedMinutes);
                }
            }
        }

        $activeAdjustableId = $adjId ?? (is_array($activeAdjustableModel) ? ($activeAdjustableModel['id'] ?? 0) : ($activeAdjustableModel->id ?? 0));

        $isGantiJamBreakMissed = false;
        if ($hasActiveAdjustable && !empty($activeAdjustableModel)) {
            $breakTimeVal = is_array($activeAdjustableModel) ? ($activeAdjustableModel['break_time'] ?? null) : $activeAdjustableModel->break_time;
            if (empty($breakTimeVal)) {
                $adjShift = null;
                if ($activeAdjustableModel instanceof \App\Models\ChangeTimeSession) {
                    $adjShift = $activeAdjustableModel->shift;
                } elseif ($this->detailScheduleId) {
                    $adjShift = \App\Models\DetailSchedule::find($this->detailScheduleId)?->shift;
                }

                if ($adjShift) {
                    if (isset($adjShift->break_time_in_minute) && (int) $adjShift->break_time_in_minute <= 0) {
                        $isGantiJamBreakMissed = true;
                    } elseif (!empty($adjShift->end_break_time)) {
                        $nowTime = \Carbon\Carbon::now('Asia/Jakarta')->format('H:i:s');
                        if ($nowTime > $adjShift->end_break_time) {
                            $isGantiJamBreakMissed = true;
                        }
                    }
                }
            }
        }

        $isRegularBreakMissed = false;
        $regularBreakTime = is_array($todayAttendance) ? ($todayAttendance['break_time'] ?? null) : ($todayAttendance->break_time ?? null);
        if ($hasCheckedIn && empty($regularBreakTime)) {
            $shiftModel = null;
            if ($this->detailScheduleId) {
                $shiftModel = \App\Models\DetailSchedule::find($this->detailScheduleId)?->shift;
            } elseif ($this->shift && is_string($this->shift)) {
                $shiftModel = \App\Models\Shift::where('name', $this->shift)->first();
            } elseif ($this->shift instanceof \App\Models\Shift) {
                $shiftModel = $this->shift;
            }

            if ($shiftModel) {
                if (isset($shiftModel->break_time_in_minute) && (int) $shiftModel->break_time_in_minute <= 0) {
                    $isRegularBreakMissed = true;
                } else {
                    $dateToday = \Carbon\Carbon::now('Asia/Jakarta');
                    $effectiveEnd = $shiftModel->getEffectiveEndBreakTime($dateToday, $currentUser);
                    if (!empty($effectiveEnd)) {
                        $nowTime = $dateToday->format('H:i:s');
                        if ($nowTime > $effectiveEnd) {
                            $isRegularBreakMissed = true;
                        }
                    }
                }
            }
        }

        $isSundayToday = \Carbon\Carbon::now('Asia/Jakarta')->isSunday();
        $isHolidayDate = \App\Models\Holiday::whereDate('date', \Carbon\Carbon::now('Asia/Jakarta')->toDateString())->exists();
        $isHolidayToday = $isSundayToday || $isHolidayDate;

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
            'isWorkingShiftToday' => $isWorkingShiftToday ?? false,
            'hasActiveAdjustable' => $hasActiveAdjustable,
            'activeAdjustableModel' => $activeAdjustableModel,
            'activeAdjustableId' => $activeAdjustableId,
            'isGantiJamBreakMissed' => $isGantiJamBreakMissed,
            'isRegularBreakMissed' => $isRegularBreakMissed,
            'canClockOutGantiJam' => $canClockOutGantiJam,
            'targetDebtMinutes' => $targetDebtMinutes,
            'gantiJamWorkedMinutes' => $gantiJamWorkedMinutes,
            'remainingGantiJamMinutes' => $remainingGantiJamMinutes,
            'isHolidayToday' => $isHolidayToday,
            'isSundayToday' => $isSundayToday,
            'pollInterval' => \App\Models\PopupSetting::getInterval('attd_status_button', 5),
        ]);
    }

    /**
     * Memeriksa dan memicu popup notifikasi real-time Livewire untuk tanggapan admin & tugas baru.
     */
    public function checkRealtimeNotifications(): void
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = $this->user instanceof \App\Models\User ? $this->user : auth()->user();
        if (!$currentUser) {
            return;
        }

        $userId = $currentUser->id;

        // 1. Cek Pesan Chat Baru dari Admin / Mentor pada sesi bantuan aktif
        $latestAdminMsg = \App\Models\HandRaiseMessage::whereHas('handRaise', function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->where('is_raised', true)
              ->where('status', '!=', 'done');
        })
        ->where('is_from_admin', true)
        ->latest('id')
        ->first();

        if ($latestAdminMsg) {
            $msgSeenKey = 'seen_hr_msg_' . $latestAdminMsg->id;
            $msgCacheKey = 'user_seen_hr_msg_' . $userId . '_' . $latestAdminMsg->id;

            // Jika pesan ini belum pernah dilihat oleh pemagang (dalam session dan cache)
            if (!session()->has($msgSeenKey) && !\Illuminate\Support\Facades\Cache::has($msgCacheKey)) {
                session([$msgSeenKey => true]);
                \Illuminate\Support\Facades\Cache::put($msgCacheKey, true, now()->addDays(7));

                // Buka langsung modal Status Bantuan Aktif & bunyikan suara notif
                $this->showAdminResponseModal = false;
                $this->adminResponseData = null;
                $this->showStatusBantuanModal = true;
                $this->dispatch('play-chat-notification');
                return;
            }
        }

        // 2. Cek Tanggapan / Status Baru Admin pada Raise Hand
        // PENTING: Notifikasi popup HANYA muncul jika aksi dilakukan oleh Admin/Mentor (bukan oleh pemagang sendiri)
        $latestResponse = \App\Models\HandRaise::with(['resolver.profile'])
            ->where('user_id', $userId)
            ->whereIn('status', ['accepted', 'rescheduled', 'rejected', 'responded', 'in_progress', 'ready', 'needs_revision', 'done'])
            ->where(function ($q) use ($userId) {
                $q->whereNull('resolved_by')
                  ->orWhere('resolved_by', '!=', $userId);
            })
            ->where(function ($q) {
                $q->whereNull('notification_seen_at')
                  ->orWhereColumn('updated_at', '>', 'notification_seen_at');
            })
            ->where('updated_at', '>=', now()->subDays(7))
            ->latest('updated_at')
            ->first();

        if ($latestResponse && (int) ($latestResponse->resolved_by ?? 0) !== (int) $userId) {
            $responseTimestamp = $latestResponse->updated_at?->timestamp ?? 0;

            $seenKey = 'seen_raise_response_' . $latestResponse->id . '_' . $responseTimestamp;
            $cacheKey = 'user_seen_raise_response_' . $userId . '_' . $latestResponse->id . '_' . $responseTimestamp;

            if (!session()->has($seenKey) && !\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                $hrType = $latestResponse->type;

                // Khusus pertanyaan/kendala (question atau kosong): langsung buka modal Status Bantuan Aktif
                if ($hrType === 'question' || empty($hrType)) {
                    $this->markRaiseHandSeen($latestResponse->id, $responseTimestamp);
                    $this->showAdminResponseModal = false;
                    $this->adminResponseData = null;
                    $this->showStatusBantuanModal = true;
                    $this->dispatch('play-chat-notification');
                    return;
                }

                $statusLabel = match ($latestResponse->status) {
                    'responded' => 'Tanggapan Bantuan',
                    'in_progress' => 'Tugas Baru Diberikan',
                    'accepted' => 'Jadwal Presentasi Diterima',
                    'rescheduled' => 'Jadwal Presentasi Diubah',
                    'rejected' => 'Pengajuan Presentasi Ditolak',
                    'needs_revision' => 'Presentasi Ada Catatan Revisi',
                    'ready' => 'Presentasi Disahkan Valid',
                    'done' => 'Selesai Dievaluasi',
                    default => 'Tanggapan Admin'
                };

                $typeLabel = match ($latestResponse->type) {
                    'question' => 'Pertanyaan / Kendala',
                    'new_task' => 'Permintaan Tugas Baru',
                    'presentation' => 'Jadwal Presentasi',
                    default => 'Raise Hand'
                };

                $resolverName = $latestResponse->resolver?->profile?->full_name
                    ?? $latestResponse->resolver?->name
                    ?? 'Admin / Pembimbing';

                $responseMessage = $latestResponse->admin_response;
                if (empty($responseMessage)) {
                    $responseMessage = match ($latestResponse->status) {
                        'accepted' => 'Pengajuan jadwal presentasi Anda telah diterima dan disetujui oleh pembimbing.',
                        'rescheduled' => 'Jadwal presentasi Anda telah dijadwalkan ulang oleh pembimbing.',
                        'rejected' => 'Pengajuan jadwal presentasi Anda ditolak oleh pembimbing.',
                        'ready' => 'Presentasi Anda telah disahkan dan dinyatakan lulus valid.',
                        'needs_revision' => 'Presentasi Anda telah dievaluasi dengan catatan revisi.',
                        'in_progress' => 'Tugas baru telah diberikan oleh pembimbing.',
                        default => 'Tanggapan dari pembimbing telah diperbarui.'
                    };
                }

                $this->adminResponseData = [
                    'id' => $latestResponse->id,
                    'type' => $latestResponse->type ?? 'question',
                    'type_label' => $typeLabel,
                    'status' => $latestResponse->status,
                    'status_label' => $statusLabel,
                    'response' => $responseMessage,
                    'notes' => $latestResponse->notes ?: $latestResponse->reason,
                    'presentation_date' => $latestResponse->presentation_date?->format('d M Y'),
                    'scheduled_time' => $latestResponse->scheduled_time ? date('H:i', strtotime($latestResponse->scheduled_time)) : null,
                    'resolver' => $resolverName,
                    'timestamp' => $responseTimestamp,
                ];
                $this->showAdminResponseModal = true;
                $this->dispatch('play-chat-notification');
            }
        }
    }

    /**
     * Buka modal Status Bantuan Aktif
     */
    public function openStatusBantuanModal(): void
    {
        $this->presentationDoneStep = false;
        $this->showStatusBantuanModal = true;

        if ($this->currentHandRaise) {
            $latestAdminMsg = \App\Models\HandRaiseMessage::where('hand_raise_id', $this->currentHandRaise->id)
                ->where('is_from_admin', true)
                ->latest('id')
                ->first();
            if ($latestAdminMsg) {
                session(['seen_hr_msg_' . $latestAdminMsg->id => true]);
                $userId = $this->user->id ?? auth()->id();
                if ($userId) {
                    \Illuminate\Support\Facades\Cache::put('user_seen_hr_msg_' . $userId . '_' . $latestAdminMsg->id, true, now()->addDays(7));
                }
            }
        }
    }

    /**
     * Tutup modal Status Bantuan Aktif
     */
    public function closeStatusBantuanModal(): void
    {
        $this->presentationDoneStep = false;
        $this->showOutcomeConfirmModal = false;
        $this->pendingOutcome = '';
        $this->pendingHandRaiseId = null;
        $this->pendingProjectId = null;
        $this->pendingProjectName = '';
        $this->taskLinkInput = '';
        $this->isAskingFollowUp = false;
        $this->followUpQuestionText = '';
        $this->resetErrorBag();
        $this->showStatusBantuanModal = false;

        if ($this->currentHandRaise) {
            $latestAdminMsg = \App\Models\HandRaiseMessage::where('hand_raise_id', $this->currentHandRaise->id)
                ->where('is_from_admin', true)
                ->latest('id')
                ->first();
            if ($latestAdminMsg) {
                session(['seen_hr_msg_' . $latestAdminMsg->id => true]);
                $userId = $this->user->id ?? auth()->id();
                if ($userId) {
                    \Illuminate\Support\Facades\Cache::put('user_seen_hr_msg_' . $userId . '_' . $latestAdminMsg->id, true, now()->addDays(7));
                }
            }
        }
    }

    /**
     * Buka modal Status Bantuan Aktif langsung dari popup notifikasi tanggapan admin
     */
    public function openStatusBantuanModalFromResponse(int $id, int $timestamp): void
    {
        $this->dismissAdminResponseModal($id, $timestamp);
        $this->presentationDoneStep = false;
        $this->showOutcomeConfirmModal = false;
        $this->pendingOutcome = '';
        $this->pendingHandRaiseId = null;
        $this->pendingProjectId = null;
        $this->pendingProjectName = '';
        $this->taskLinkInput = '';
        $this->resetErrorBag();
        $this->showStatusBantuanModal = true;
    }

    /**
     * Konfirmasi pemagang sudah melaksanakan sesi presentasi
     */
    public function confirmPresentationDone(): void
    {
        $this->presentationDoneStep = true;
    }

    /**
     * Kembali dari pemilihan hasil evaluasi presentasi
     */
    public function resetPresentationDone(): void
    {
        $this->presentationDoneStep = false;
        $this->showOutcomeConfirmModal = false;
        $this->pendingOutcome = '';
        $this->pendingHandRaiseId = null;
        $this->pendingProjectId = null;
        $this->pendingProjectName = '';
        $this->taskLinkInput = '';
        $this->resetErrorBag();
    }

    /**
     * Tampilkan modal popup konfirmasi evaluasi (Lulus / Revisi)
     */
    public function promptOutcomeConfirm(int $handRaiseId, string $outcome): void
    {
        $this->resetErrorBag();
        $this->pendingHandRaiseId = $handRaiseId;
        $this->pendingOutcome = $outcome;

        if ($outcome === 'passed') {
            /** @var \App\Models\User|null $currentUser */
            $currentUser = $this->user instanceof \App\Models\User ? $this->user : auth()->user();
            $handRaise = \App\Models\HandRaise::with('project.nameProject')->find($handRaiseId);

            $project = $handRaise?->project;
            if (!$project && $currentUser?->intern) {
                $project = $currentUser->intern->detailProject()
                    ->whereHas('project', function ($q) {
                        $q->where('status', '!=', 'done');
                    })
                    ->with('project.nameProject')
                    ->latest('id')
                    ->first()?->project
                    ?? $currentUser->intern->detailProject()->with('project.nameProject')->latest('id')->first()?->project;
            }
            $this->pendingProjectId = $project?->id;
            $this->pendingProjectName = $project?->nameProject?->name ?? 'Tugas / Project Aktif';

            $divisionId = (int) ($currentUser?->intern?->division_id ?? 0);
            $divisionName = strtolower($currentUser?->intern?->division?->name ?? '');

            $isProg = ($divisionId === 4)
                || str_contains($divisionName, 'programmer')
                || str_contains($divisionName, 'program');
            $isUiUx = ($divisionId === 1)
                || str_contains($divisionName, 'ui/ux')
                || str_contains($divisionName, 'ui / ux')
                || (str_contains($divisionName, 'ui') && str_contains($divisionName, 'ux'));
            $isSosmed = in_array($divisionId, [2, 3, 5, 6, 8, 9, 11, 12, 16])
                || str_contains($divisionName, 'social')
                || str_contains($divisionName, 'tiktok')
                || str_contains($divisionName, 'marketing')
                || str_contains($divisionName, 'marcom')
                || str_contains($divisionName, 'content')
                || str_contains($divisionName, 'talent')
                || str_contains($divisionName, 'presenter');

            if ($isProg) {
                $this->taskLinkType = 'programmer';
                $this->taskLinkLabel = 'Link Repository GitHub Tugas';
                $this->taskLinkPlaceholder = 'https://github.com/username/project-repo';
            } elseif ($isUiUx) {
                $this->taskLinkType = 'uiux';
                $this->taskLinkLabel = 'Link Project / File Figma Tugas';
                $this->taskLinkPlaceholder = 'https://www.figma.com/design/...';
            } elseif ($isSosmed) {
                $this->taskLinkType = 'media';
                $this->taskLinkLabel = 'Link Media / Google Drive Tugas';
                $this->taskLinkPlaceholder = 'https://drive.google.com/...';
            } else {
                $this->taskLinkType = 'general';
                $this->taskLinkLabel = 'Link Pengumpulan / Drive Tugas';
                $this->taskLinkPlaceholder = 'https://drive.google.com/...';
            }

            // Periksa langsung ke repository_url pada project tugas tersebut (bukan kredensial akun)
            $this->taskLinkInput = $project?->repository_url ?: '';
        } else {
            $this->taskLinkInput = '';
            $this->pendingProjectName = '';
        }

        $this->showOutcomeConfirmModal = true;
    }

    /**
     * Batalkan modal popup konfirmasi evaluasi
     */
    public function cancelOutcomeConfirm(): void
    {
        $this->showOutcomeConfirmModal = false;
        $this->pendingOutcome = '';
        $this->pendingHandRaiseId = null;
        $this->pendingProjectId = null;
        $this->pendingProjectName = '';
        $this->taskLinkInput = '';
        $this->resetErrorBag();
    }

    /**
     * Eksekusi konfirmasi evaluasi (Lulus / Revisi)
     */
    public function executeOutcomeConfirm(): void
    {
        if ($this->pendingHandRaiseId && in_array($this->pendingOutcome, ['passed', 'revision'])) {
            $id = $this->pendingHandRaiseId;
            $outcome = $this->pendingOutcome;

            if ($outcome === 'passed') {
                $this->validate([
                    'taskLinkInput' => 'required|url|max:500',
                ], [
                    'taskLinkInput.required' => 'Link tugas wajib diisi terlebih dahulu sebelum menyelesaikan presentasi.',
                    'taskLinkInput.url' => 'Format link tugas harus berupa URL valid (contoh: https://...).',
                    'taskLinkInput.max' => 'Panjang link maksimal 500 karakter.',
                ]);

                $linkUrl = trim($this->taskLinkInput);

                // Simpan tautan secara khusus ke kolom repository_url pada project/tugas bersangkutan
                if ($this->pendingProjectId) {
                    $proj = \App\Models\Projects::find($this->pendingProjectId);
                    if ($proj) {
                        $proj->update(['repository_url' => $linkUrl]);
                    }
                }
            }

            $this->showOutcomeConfirmModal = false;
            $this->pendingOutcome = '';
            $this->pendingHandRaiseId = null;
            $this->pendingProjectId = null;
            $this->pendingProjectName = '';
            $this->taskLinkInput = '';
            $this->resetErrorBag();
            $this->completePresentation($id, $outcome);
        }
    }

    /**
     * Selesaikan sesi presentasi oleh pemagang (Lulus Tanpa Revisi vs Ada Catatan Revisi)
     */
    public function completePresentation(int $handRaiseId, string $outcome): void
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = $this->user instanceof \App\Models\User ? $this->user : auth()->user();
        if (!$currentUser) {
            return;
        }

        $handRaise = \App\Models\HandRaise::where('id', $handRaiseId)
            ->where('user_id', $currentUser->id)
            ->first();

        if (!$handRaise) {
            $this->dispatch('post-created', status: false, message: 'Data pengajuan presentasi tidak ditemukan.');
            return;
        }

        if (!in_array($outcome, ['passed', 'revision'])) {
            $this->dispatch('post-created', status: false, message: 'Pilihan hasil presentasi tidak valid.');
            return;
        }

        $userName = $currentUser->profile?->full_name ?? $currentUser->name ?? $currentUser->username;
        $intern = $currentUser->intern;

        // Cari project terkait jika ada
        $project = $handRaise->project;
        if (!$project && $intern) {
            $project = $intern->detailProject?->where('project.status', '!=', 'done')->last()?->project
                ?? $intern->detailProject?->last()?->project;
            if ($project) {
                $handRaise->update(['project_id' => $project->id]);
            }
        }

        if ($outcome === 'passed') {
            $handRaise->update([
                'status' => 'done',
                'is_raised' => false,
                'resolved_at' => now(),
                'resolved_by' => $currentUser->id,
            ]);
            $this->markRaiseHandSeen($handRaise->id, $handRaise->updated_at?->timestamp);

            if ($project) {
                $project->update(['status' => 'done']);

                \App\Models\HandRaise::where('user_id', $currentUser->id)
                    ->where('project_id', $project->id)
                    ->where('type', 'new_task')
                    ->update([
                        'status' => 'done',
                        'is_raised' => false,
                        'resolved_at' => now(),
                        'resolved_by' => $currentUser->id,
                    ]);
            }

            \App\Helper\ActivityLogger::log(
                'RESOLVE',
                'Raise Hand',
                "Pemagang {$userName} menyelesaikan sesi presentasi: Lulus Tanpa Revisi (Selesai Valid)",
                [
                    'hand_raise_id' => $handRaise->id,
                    'user_id' => $currentUser->id,
                    'outcome' => 'passed',
                    'status' => 'done',
                ]
            );

            $this->presentationDoneStep = false;
            $this->showStatusBantuanModal = false;
            $this->isHandRaised = false;
            $this->currentHandRaise = null;
            $this->dispatch('post-created', status: true, message: 'Selamat! Sesi presentasi berhasil diselesaikan dengan status Lulus Tanpa Revisi.');
        } else {
            // Ada Catatan Revisi Mentor
            $handRaise->update([
                'status' => 'needs_revision',
                'is_raised' => false,
                'resolved_at' => now(),
                'resolved_by' => $currentUser->id,
            ]);
            $this->markRaiseHandSeen($handRaise->id, $handRaise->updated_at?->timestamp);

            if ($project) {
                $project->update(['status' => 'progress']);
            }

            \App\Helper\ActivityLogger::log(
                'RESOLVE',
                'Raise Hand',
                "Pemagang {$userName} menyelesaikan sesi presentasi: Ada Catatan Revisi Mentor",
                [
                    'hand_raise_id' => $handRaise->id,
                    'user_id' => $currentUser->id,
                    'outcome' => 'revision',
                    'status' => 'needs_revision',
                ]
            );

            $this->presentationDoneStep = false;
            $this->showStatusBantuanModal = false;
            $this->isHandRaised = false;
            $this->currentHandRaise = null;
            $this->dispatch('post-created', status: true, message: 'Sesi presentasi selesai dengan catatan revisi. Silakan isi detail catatan revisi di halaman Tugas.');
        }
    }

    /**
     * Selesaikan pertanyaan / bantuan tanya jawab setelah mentor merespon
     */
    public function completeQuestion(int $handRaiseId): void
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = $this->user instanceof \App\Models\User ? $this->user : auth()->user();
        if (!$currentUser) {
            return;
        }

        $handRaise = \App\Models\HandRaise::where('id', $handRaiseId)
            ->where('user_id', $currentUser->id)
            ->first();

        if (!$handRaise) {
            $this->dispatch('post-created', status: false, message: 'Data pertanyaan tidak ditemukan.');
            return;
        }

        $handRaise->update([
            'is_raised' => false,
            'status' => 'done',
            'resolved_at' => now(),
            'resolved_by' => $currentUser->id,
        ]);
        $this->markRaiseHandSeen($handRaise->id, $handRaise->updated_at?->timestamp);

        // Bersihkan log chat bantuan yang sudah selesai
        \App\Models\HandRaiseMessage::where('hand_raise_id', $handRaise->id)->delete();

        $userName = $currentUser->profile?->full_name ?? $currentUser->name ?? $currentUser->username;
        \App\Helper\ActivityLogger::log(
            'RESOLVE',
            'Raise Hand',
            "Pemagang {$userName} telah memahami arahan dan menyelesaikan sesi pertanyaan",
            [
                'hand_raise_id' => $handRaise->id,
                'user_id' => $currentUser->id,
                'type' => 'question',
                'status' => 'done',
            ]
        );

        $this->showStatusBantuanModal = false;
        $this->isHandRaised = false;
        $this->currentHandRaise = null;
        $this->isAskingFollowUp = false;
        $this->followUpQuestionText = '';
        $this->dispatch('post-created', status: true, message: 'Sesi pertanyaan / bantuan telah berhasil diselesaikan.');
    }

    /**
     * Buka / tutup input formulir pertanyaan lanjutan (Tanya Lagi)
     */
    public function toggleAskFollowUp(): void
    {
        $this->isAskingFollowUp = !$this->isAskingFollowUp;
        if (!$this->isAskingFollowUp) {
            $this->followUpQuestionText = '';
        }
    }

    /**
     * Kirim pertanyaan lanjutan (Follow-up Question) pada sesi bantuan aktif
     */
    public function askFollowUp(int $handRaiseId): void
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = $this->user instanceof \App\Models\User ? $this->user : auth()->user();
        if (!$currentUser) {
            return;
        }

        $message = trim($this->followUpQuestionText);
        if (empty($message)) {
            $this->dispatch('post-created', status: false, message: 'Pertanyaan lanjutan tidak boleh kosong.');
            return;
        }

        $handRaise = \App\Models\HandRaise::where('id', $handRaiseId)
            ->where('user_id', $currentUser->id)
            ->first();

        if (!$handRaise) {
            $this->dispatch('post-created', status: false, message: 'Data bantuan tidak ditemukan.');
            return;
        }

        // Backfill pertanyaan awal jika tabel messages masih kosong
        if ($handRaise->messages()->count() === 0) {
            $initNote = trim($handRaise->notes ?? $handRaise->reason ?? '');
            if (!empty($initNote)) {
                \App\Models\HandRaiseMessage::create([
                    'hand_raise_id' => $handRaise->id,
                    'user_id' => $currentUser->id,
                    'message' => $initNote,
                    'is_from_admin' => false,
                    'created_at' => $handRaise->created_at ?? now(),
                ]);
            }
            if (!empty($handRaise->admin_response)) {
                \App\Models\HandRaiseMessage::create([
                    'hand_raise_id' => $handRaise->id,
                    'user_id' => $handRaise->resolved_by ?? 1,
                    'message' => $handRaise->admin_response,
                    'is_from_admin' => true,
                    'created_at' => $handRaise->updated_at ?? now(),
                ]);
            }
        }

        // Simpan pesan pertanyaan lanjutan pemagang
        \App\Models\HandRaiseMessage::create([
            'hand_raise_id' => $handRaise->id,
            'user_id' => $currentUser->id,
            'message' => $message,
            'is_from_admin' => false,
        ]);

        // Kembalikan status ke pending dan is_raised = true agar mentor mendapat notifikasi
        $handRaise->update([
            'status' => 'pending',
            'is_raised' => true,
        ]);

        $this->markRaiseHandSeen($handRaise->id, $handRaise->updated_at?->timestamp);

        $userName = $currentUser->profile?->full_name ?? $currentUser->name ?? $currentUser->username;
        \App\Helper\ActivityLogger::log(
            'UPDATE',
            'Raise Hand',
            "Pemagang {$userName} mengirimkan pertanyaan lanjutan pada sesi bantuan",
            [
                'hand_raise_id' => $handRaise->id,
                'user_id' => $currentUser->id,
                'type' => 'question',
                'status' => 'pending',
            ]
        );

        $this->followUpQuestionText = '';
        $this->isAskingFollowUp = false;

        // Refresh model instance
        $this->currentHandRaise = $handRaise->fresh(['messages.user.profile', 'resolver.profile']);
        $this->isHandRaised = true;

        $this->dispatch('post-created', status: true, message: 'Pertanyaan lanjutan berhasil dikirim ke mentor.');
    }

    /**
     * Selesaikan pengajuan permintaan tugas baru setelah tugas diberikan oleh mentor
     */
    public function completeNewTaskRaiseHand(int $handRaiseId): void
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = $this->user instanceof \App\Models\User ? $this->user : auth()->user();
        if (!$currentUser) {
            return;
        }

        $handRaise = \App\Models\HandRaise::where('id', $handRaiseId)
            ->where('user_id', $currentUser->id)
            ->first();

        if (!$handRaise) {
            $this->dispatch('post-created', status: false, message: 'Data pengajuan tugas tidak ditemukan.');
            return;
        }

        $handRaise->update([
            'is_raised' => false,
            'status' => 'done',
            'resolved_at' => now(),
            'resolved_by' => $currentUser->id,
        ]);
        $this->markRaiseHandSeen($handRaise->id, $handRaise->updated_at?->timestamp);

        // Bersihkan log chat bantuan yang sudah selesai
        \App\Models\HandRaiseMessage::where('hand_raise_id', $handRaise->id)->delete();

        $userName = $currentUser->profile?->full_name ?? $currentUser->name ?? $currentUser->username;
        \App\Helper\ActivityLogger::log(
            'RESOLVE',
            'Raise Hand',
            "Pemagang {$userName} telah menerima tugas dan menyelesaikan sesi pengajuan tugas baru",
            [
                'hand_raise_id' => $handRaise->id,
                'user_id' => $currentUser->id,
                'type' => 'new_task',
                'status' => 'done',
            ]
        );

        $this->showStatusBantuanModal = false;
        $this->isHandRaised = false;
        $this->currentHandRaise = null;
        $this->dispatch('post-created', status: true, message: 'Pengajuan tugas baru berhasil diselesaikan dan masuk ke riwayat bantuan.');
    }

    /**
     * Turunkan tangan (Hanya untuk pending atau ditolak)
     */
    public function lowerHand(int $handRaiseId): void
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = $this->user instanceof \App\Models\User ? $this->user : auth()->user();
        if (!$currentUser) {
            return;
        }

        $handRaise = \App\Models\HandRaise::where('id', $handRaiseId)
            ->where('user_id', $currentUser->id)
            ->first();

        if (!$handRaise) {
            $this->dispatch('post-created', status: false, message: 'Data bantuan tidak ditemukan.');
            return;
        }

        // Proteksi: Jika sudah diterima/dijadwalkan/sedang dikerjakan, tidak boleh langsung turun tangan
        if (!in_array($handRaise->status, ['pending', 'rejected'])) {
            $this->dispatch('post-created', status: false, message: 'Pengajuan telah ditanggapi oleh mentor/admin dan tidak dapat diturunkan langsung. Silakan ikuti prosedur penyelesaian.');
            return;
        }

        $statusToUpdate = $handRaise->status === 'rejected' ? 'rejected' : 'done';
        $handRaise->update([
            'is_raised' => false,
            'status' => $statusToUpdate,
            'resolved_at' => now(),
        ]);
        $this->markRaiseHandSeen($handRaise->id, $handRaise->updated_at?->timestamp);

        // Bersihkan log chat bantuan
        \App\Models\HandRaiseMessage::where('hand_raise_id', $handRaise->id)->delete();

        $userName = $currentUser->profile?->full_name ?? $currentUser->name ?? $currentUser->username;
        \App\Helper\ActivityLogger::log(
            'RESOLVE',
            'Raise Hand',
            "Pemagang {$userName} menurunkan / menutup sesi Raise Hand [{$handRaise->type}]",
            [
                'hand_raise_id' => $handRaise->id,
                'user_id' => $currentUser->id,
                'type' => $handRaise->type,
                'status' => $statusToUpdate,
            ]
        );

        $this->showStatusBantuanModal = false;
        $this->isHandRaised = false;
        $this->currentHandRaise = null;
        $this->dispatch('post-created', status: true, message: 'Status bantuan berhasil ditutup / diturunkan.');
    }

    /**
     * Tandai notifikasi respon raise hand sebagai telah dilihat (database + session + persistent cache)
     */
    private function markRaiseHandSeen(?int $id, ?int $timestamp = null): void
    {
        if (!$id) return;
        \App\Models\HandRaise::where('id', $id)->update([
            'notification_seen_at' => now(),
        ]);
        $ts = $timestamp ?? now()->timestamp;
        session(['seen_raise_response_' . $id . '_' . $ts => true]);
        $userId = $this->user->id ?? auth()->id();
        if ($userId) {
            \Illuminate\Support\Facades\Cache::put('user_seen_raise_response_' . $userId . '_' . $id . '_' . $ts, true, now()->addDays(30));
        }
    }

    /**
     * Tutup modal tanggapan admin dan tandai sebagai telah dilihat
     */
    public function dismissAdminResponseModal(int $id, int $timestamp): void
    {
        $this->markRaiseHandSeen($id, $timestamp);
        $this->showAdminResponseModal = false;
        $this->adminResponseData = null;
    }
}
