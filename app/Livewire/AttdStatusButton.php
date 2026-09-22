<?php

namespace App\Livewire;

use App\DTO\AttendanceDTO;
use App\Helper\LogConsole;
use App\Services\Attendance\AttendanceState;
use App\Services\AttendanceService;
use App\Utils\AttendanceStatus;
use App\Utils\AttendanceType;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\text;

class AttdStatusButton extends Component
{
    protected AttendanceService $attendanceService;
    public mixed $hrUsers = [];
    public mixed $shift;
    public bool $hasShift = false;
    public mixed $user;
    public mixed $stage;
    public mixed $attendanceHistory;
    public mixed $adjustableTimeHistory;
    public mixed $scheduleId;
    public mixed $detailScheduleId;
    public int $totalChangeTime = 0;

    public bool $showModal = false;

    public bool $isAdjustable = false;
    public string $notes = '';
    public bool $hasFilledLogToday = false;
    public bool $isHandRaised = false;
    public mixed $currentHandRaise = null;
    public bool $hasActiveTasks = false;
    public int $activeTasksCount = 0;

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount($hrUsers = [], $shift = null, $hasShift = false, $user = null, mixed $stage = AttendanceStatus::AllDone, $scheduleId = null, $detailScheduleId = null, $absenceHistory = null, $adjustableTimeHistory = null, $isHandRaised = false, $hasFilledLogToday = false, $currentHandRaise = null, $hasActiveTasks = false, $activeTasksCount = 0)
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
        $this->hasFilledLogToday = (bool) $hasFilledLogToday;
        $this->isHandRaised = (bool) $isHandRaised;
        $this->currentHandRaise = $currentHandRaise;
        $this->hasActiveTasks = (bool) $hasActiveTasks;
        $this->activeTasksCount = (int) $activeTasksCount;

        if (!$this->hasActiveTasks && $this->user instanceof \App\Models\User) {
            $this->hasActiveTasks = $this->user->hasActiveTasks();
            $this->activeTasksCount = $this->user->getActiveTasksCount();
        }

        if (!$this->hasFilledLogToday && $this->user && $this->user->intern) {
            $this->hasFilledLogToday = \App\Models\LogActivity::whereHas('detailSchedule.schedule', function ($query) {
                $query->where('intern_id', $this->user->intern->id);
            })->whereDate('date', today())->exists();
        }

        // Debug log
        Log::info('AttdStatusButton mount - adjustableTimeHistory: ' . ($adjustableTimeHistory ? 'ID: ' . $adjustableTimeHistory->id : 'null'));
        Log::info('AttdStatusButton mount - stage: ' . ($stage ? $stage->value : 'null'));
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

        Log::info('AttdStatusButton actionAttd - attendanceIdToUse: ' . $attendanceIdToUse);
        Log::info('AttdStatusButton actionAttd - adjustableIdToUse: ' . $adjustableIdToUse);
        Log::info('AttdStatusButton actionAttd - stage: ' . $stage);

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
                Log::info('AttdStatusButton actionAttd - Updated adjustableTimeHistory to ID: ' . ($adjustableData ? $adjustableData->id : 'null'));
                $this->dispatch('adjst-info-refresh', adjstData: $adjustableData);
            }
        }
    }

    public function render()
    {
        $userId = $this->user->id ?? auth()->id();
        /** @var \App\Models\User|null $currentUser */
        $currentUser = $this->user instanceof \App\Models\User ? $this->user : auth()->user();

        if ($currentUser) {
            $this->hasActiveTasks = $currentUser->hasActiveTasks();
            $this->activeTasksCount = $currentUser->getActiveTasksCount();
        }

        $currentHandRaise = \App\Models\HandRaise::where('user_id', $userId)
            ->where('is_raised', true)
            ->where('status', '!=', 'done')
            ->latest()
            ->first()
            ?? \App\Models\HandRaise::where('user_id', $userId)->latest()->first();

        $this->isHandRaised = (bool) ($currentHandRaise?->is_raised && $currentHandRaise?->status !== 'done');
        $this->currentHandRaise = $currentHandRaise;

        return view('livewire.attd-status-button', [
            'isHandRaised' => $this->isHandRaised,
            'currentHandRaise' => $this->currentHandRaise,
            'hrUsers' => $this->hrUsers ?? [],
            'hasActiveTasks' => $this->hasActiveTasks,
            'activeTasksCount' => $this->activeTasksCount,
        ]);
    }
}
