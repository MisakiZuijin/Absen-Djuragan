<?php

namespace App\Services\Attendance;

use App\DTO\AttendanceDTO;
use App\Helper\ActionResult;

interface AttendanceState {
    public function handle(AttendanceDTO $data): ActionResult;
}
