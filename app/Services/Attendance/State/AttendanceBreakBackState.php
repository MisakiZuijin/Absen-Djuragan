<?php

namespace App\Services\Attendance\State;

use App\DTO\AttendanceDTO;
use App\Helper\ActionResult;
use App\Helper\LogConsole;
use App\Repositories\Interface\AttendanceRepository;
use App\Services\Attendance\AttendanceState;
use App\Utils\AttendanceStatus;
use App\Utils\DateNow;
use Illuminate\Support\Facades\DB;

class AttendanceBreakBackState implements AttendanceState {
    private AttendanceRepository $attendanceRepository;
    public function __construct(AttendanceRepository $attendanceRepository) {
        $this->attendanceRepository = $attendanceRepository;
    }

    public function handle(AttendanceDTO $data): ActionResult {
        $timeNow = $data->getTimeNow();

        $scheduleId = $data->getScheduleId();
        $detailScheduleId = $data->getDetailSchedule();

        $absenceHistory = $this->attendanceRepository->getById($data->getAttendanceId());

        $attData = [
            "back_time" => $timeNow,
            "back_time_message" => $data->getDescription(),
            "total_break_min" => DateNow::getDifferentInMinute($absenceHistory->break_time, $timeNow)
        ];

        if ($absenceHistory->detailSchedules->shift->end_break_time > $timeNow) {
            $attData["back_time"] =  $absenceHistory->detailSchedules->shift->end_break_time;
        }

        $attendanceData = $this->attendanceRepository->update($data->getAttendanceId(), $attData);
        DB::commit();
        if (is_null($attendanceData->permit_start)) {

            return new ActionResult(true, "Kamu Kembali dari istirahat", [
                "absenceHistory" => $attendanceData,
                // "adjustableTimeHistory" => $adjustableData,
                "schedule_id" => $scheduleId,
                "detail_schedule_id" => $detailScheduleId,
                "stage" => AttendanceStatus::StartPermit
            ]);
        }

        return new ActionResult(true, "Kamu Kembali dari istirahat", [
            "absenceHistory" => $attendanceData,
            // "adjustableTimeHistory" => $adjustableData,
            "schedule_id" => $scheduleId,
            "detail_schedule_id" => $detailScheduleId,
            "stage" => AttendanceStatus::AttendanceOut
        ]);
    }
}
