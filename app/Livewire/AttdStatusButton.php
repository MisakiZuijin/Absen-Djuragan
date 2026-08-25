<?php

namespace App\Livewire;

use App\DTO\AttendanceDTO;
use App\Helper\LogConsole;
use App\Services\Attendance\AttendanceState;
use App\Services\AttendanceService;
use App\Utils\AttendanceStatus;
use App\Utils\AttendanceType;
use Livewire\Component;

use function Laravel\Prompts\text;

class AttdStatusButton extends Component {
    protected AttendanceService $attendanceService;
    public $hrUsers;
    public $shift;
    public $hasShift = false;
    public $user;
    public $stage;
    public $attendanceHistory;
    public $adjustableTimeHistory;
    public $scheduleId;
    public $detailScheduleId;
    public $totalChangeTime = 0;

    public $showModal = false;

    public $isAdjustable = false;
    public $notes = '';

    public function boot(AttendanceService $attendanceService) {
        $this->attendanceService = $attendanceService;
    }

    public function mount($hrUsers = [], $shift, $hasShift = false, $user, $stage = AttendanceStatus::AllDone, $scheduleId = null, $detailScheduleId = null, $absenceHistory = null, $adjustableTimeHistory = null) {
        $this->scheduleId = $scheduleId;
        $this->detailScheduleId = $detailScheduleId;
        $this->hrUsers = $hrUsers;
        $this->shift = $shift;
        $this->hasShift = (bool) $hasShift;
        $this->user = $user;
        $this->stage = $stage;
        $this->attendanceHistory = $absenceHistory;
        $this->adjustableTimeHistory = $adjustableTimeHistory;

        // Debug log
        \Log::info('AttdStatusButton mount - adjustableTimeHistory: ' . ($adjustableTimeHistory ? 'ID: ' . $adjustableTimeHistory->id : 'null'));
        \Log::info('AttdStatusButton mount - stage: ' . ($stage ? $stage->value : 'null'));
    }

    protected $listeners = ['actionAttd'];

    public function actionAttd($stage, $text = null, $latitude = null, $longitude = null, $attendanceId = null, $adjustableId = null) {
        if ($stage == AttendanceStatus::AllDone->value) {
            $this->dispatch('post-created', status: true, message: "Semua Aktifitas mu hari ini sudah selesai");
            return;
        }

        // Use provided IDs if available, otherwise fallback to component properties
        $attendanceIdToUse = $attendanceId ?: ($this->attendanceHistory->id ?? 0);
        $adjustableIdToUse = $adjustableId ?: ($this->adjustableTimeHistory->id ?? 0);

        \Log::info('AttdStatusButton actionAttd - attendanceIdToUse: ' . $attendanceIdToUse);
        \Log::info('AttdStatusButton actionAttd - adjustableIdToUse: ' . $adjustableIdToUse);
        \Log::info('AttdStatusButton actionAttd - stage: ' . $stage);

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
                \Log::info('AttdStatusButton actionAttd - Updated adjustableTimeHistory to ID: ' . ($adjustableData ? $adjustableData->id : 'null'));
                $this->dispatch('adjst-info-refresh', adjstData: $adjustableData);
            }
        }
    }

    public function render() {
        return view('livewire.attd-status-button');
    }
}
