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
        $user = \Illuminate\Support\Facades\Auth::user();
        $internId = $user?->intern?->id;

        $adjustableOld = null;
        if ($adjustableId && $adjustableId > 0) {
            try {
                $adjustableOld = $this->adjustableAttdRepository->getById($adjustableId);
            } catch (\Throwable $e) {
                $adjustableOld = null;
            }
        }

        if (!$adjustableOld && $internId) {
            $adjustableOld = \App\Models\AdjustableAttd::where('intern_id', $internId)
                ->whereNotNull('start_time')
                ->whereNull('end_time')
                ->latest('id')
                ->first();
            if ($adjustableOld) {
                $adjustableId = $adjustableOld->id;
            }
        }

        if (!$adjustableOld) {
            return new ActionResult(false, "Sesi ganti jam aktif tidak ditemukan atau sudah selesai.", null);
        }

        $timeNow = $data->getTimeNow();

        $scheduleId = $data->getScheduleId();
        $detailScheduleId = $data->getDetailSchedule();

        $attData = [
            "back_time" => $timeNow,
            "back_time_message" => $data->getDescription(),
            "total_break_min" => DateNow::getDifferentInMinute($adjustableOld->break_time, $timeNow)
        ];

        $adjustableData = $this->adjustableAttdRepository->update($adjustableId, $attData);

        return new ActionResult(true, "Kamu kembali dari istirahat (ganti jam)", [
            "adjustableTimeHistory" => $adjustableData,
            "schedule_id" => $scheduleId,
            "detail_schedule_id" => $detailScheduleId,
            "stage" => AttendanceStatus::AdjustableOut
        ]);
    }
}
