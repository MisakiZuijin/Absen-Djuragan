<?php

namespace App\Services\Attendance\State;

use App\DTO\AttendanceDTO;
use App\Helper\ActionResult;
use App\Repositories\Interface\AdjustableAttdRepository;
use App\Services\Attendance\AttendanceState;
use App\Utils\AttendanceStatus;
use App\Utils\DateNow;

class AdjustableBreakBackState implements AttendanceState {
    private AdjustableAttdRepository $adjustableAttdRepository;

    public function __construct(AdjustableAttdRepository $adjustableAttdRepository) {
        $this->adjustableAttdRepository = $adjustableAttdRepository;
    }

    public function handle(AttendanceDTO $data): ActionResult {
         $adjustableId = $data->getAdjustableId();

        if (!$adjustableId || $adjustableId <= 0) {
            return new ActionResult(false, "ID adjustable tidak valid", null);
        }

        $timeNow = $data->getTimeNow();

        $scheduleId = $data->getScheduleId();
        $detailScheduleId = $data->getDetailSchedule();
        $adjustableOld = $this->adjustableAttdRepository->getById($data->getAdjustableId());

        $attData = [
            "back_time" => $timeNow,
            "back_time_message" => $data->getDescription(),
            "total_break_min" => DateNow::getDifferentInMinute($adjustableOld->break_time, $timeNow)
        ];

        $adjustableData = $this->adjustableAttdRepository->update($data->getAdjustableId(), $attData);

        return new ActionResult(true, "Kamu kembali dari istirahat (ganti jam)", [
            "adjustableTimeHistory" => $adjustableData,
            "schedule_id" => $scheduleId,
            "detail_schedule_id" => $detailScheduleId,
            "stage" => AttendanceStatus::AdjustableOut
        ]);
    }
}
