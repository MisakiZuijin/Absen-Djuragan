<?php

namespace App\Services\Attendance\State;

use App\Utils\DateNow;
use App\DTO\AttendanceDTO;
use App\Helper\LogConsole;
use App\Helper\ActionResult;
use App\Utils\AttendanceStatus;
use App\Services\Attendance\AttendanceState;
use App\Repositories\Interface\AdjustableAttdRepository;

class AdjustableBreakStartState implements AttendanceState {
    private AdjustableAttdRepository $adjustableAttdRepository;

    public function __construct(AdjustableAttdRepository $adjustableAttdRepository) {
        $this->adjustableAttdRepository = $adjustableAttdRepository;
    }

    public function handle(AttendanceDTO $data): ActionResult {
        $timeNow = $data->getTimeNow();
        $scheduleId = $data->getScheduleId();
        $detailScheduleId = $data->getDetailSchedule();
        $adjustableId = $data->getAdjustableId();

        LogConsole::info("=== AdjustableBreakStartState Debug ===");
        LogConsole::info("Adjustable ID: " . ($adjustableId ?? 'NULL'));
        LogConsole::info("Schedule ID: " . ($scheduleId ?? 'NULL'));
        LogConsole::info("Detail Schedule ID: " . ($detailScheduleId ?? 'NULL'));
        LogConsole::info("Time Now: " . $timeNow);
        LogConsole::info("===============================");

        // Validate adjustable ID
        if (!$adjustableId || $adjustableId <= 0) {
            LogConsole::info("ERROR: Invalid adjustable ID: " . $adjustableId);
            return new ActionResult(false, "ID adjustable tidak valid", null);
        }

        // Verify adjustable record exists
        try {
            $adjustableRecord = $this->adjustableAttdRepository->getById($adjustableId);
            LogConsole::info("Found adjustable record ID: " . $adjustableRecord->id);
        } catch (\Exception $e) {
            LogConsole::info("Adjustable record not found: " . $e->getMessage());
            return new ActionResult(false, "Data ganti jam tidak ditemukan", null);
        }

        // Check if already has break time
        if (!is_null($adjustableRecord->break_time)) {
            return new ActionResult(false, "Sudah melakukan break sebelumnya", null);
        }

        // Check if start_time exists (must clock in first)
        if (is_null($adjustableRecord->start_time)) {
            return new ActionResult(false, "Harus melakukan clock in terlebih dahulu", null);
        }

        $attData = [
            "break_time" => $timeNow,
            "break_time_message" => $data->getDescription()
        ];

        $adjustableData = $this->adjustableAttdRepository->update($adjustableId, $attData);

        return new ActionResult(true, "Berhasil mengambil istirahat (ganti jam)", [
            "adjustableTimeHistory" => $adjustableData,
            "schedule_id" => $scheduleId,
            "detail_schedule_id" => $detailScheduleId,
            "adjustable_id" => $adjustableId,
            "stage" => AttendanceStatus::EndBreakAdjustable
        ]);
    }
}
