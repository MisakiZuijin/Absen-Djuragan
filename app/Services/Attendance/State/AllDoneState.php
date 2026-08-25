<?php

namespace App\Services\Attendance\State;

use App\DTO\AttendanceDTO;
use App\Helper\ActionResult;
use App\Services\Attendance\AttendanceState;
use App\Utils\AttendanceStatus;

class AllDoneState implements AttendanceState {

    public function handle(AttendanceDTO $data): ActionResult {
        return new ActionResult(true, "presensi kamu hari ini sudah selesai", [
            "stage" => AttendanceStatus::AllDone
        ]);
    }
}
