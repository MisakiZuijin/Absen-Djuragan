<?php

namespace App\Services\Attendance;

use App\DTO\AttendanceDTO;
use App\Helper\ActionResult;
use App\Helper\LogConsole;
use App\Utils\DateNow;

class AttendanceContext {
    private $state;

    public function __construct(AttendanceState $state) {
        $this->state = $state;
    }

    public function setState(AttendanceState $state) {
        $this->state = $state;
    }

    public function execute(AttendanceDTO $data): ActionResult {
        $timeNow = DateNow::getCurrentTime();
        $data->setTimeNow($timeNow);
        // $data->setTimeNow("12:00:00");
        return $this->state->handle($data);
    }
}
