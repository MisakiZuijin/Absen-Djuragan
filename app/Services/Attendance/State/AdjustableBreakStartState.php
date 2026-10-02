<?php

namespace App\Services\Attendance\State;

use App\Utils\DateNow;
use App\DTO\AttendanceDTO;
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

        $user = \Illuminate\Support\Facades\Auth::user();
        $internId = $user?->intern?->id;

        $adjustableRecord = null;
        if ($adjustableId && $adjustableId > 0) {
            try {
                $adjustableRecord = $this->adjustableAttdRepository->getById($adjustableId);
            } catch (\Throwable $e) {
                $adjustableRecord = null;
            }
        }

        if (!$adjustableRecord && $internId) {
            $adjustableRecord = \App\Models\AdjustableAttd::where('intern_id', $internId)
                ->whereNotNull('start_time')
                ->whereNull('end_time')
                ->latest('id')
                ->first();
            if ($adjustableRecord) {
                $adjustableId = $adjustableRecord->id;
            }
        }

        if (!$adjustableRecord) {
            return new ActionResult(false, "Sesi ganti jam aktif tidak ditemukan atau sudah selesai.", null);
        }

        // Check if already has break time
        if (!is_null($adjustableRecord->break_time)) {
            return new ActionResult(false, "Sudah melakukan break sebelumnya", null);
        }

        // Check if start_time exists (must clock in first)
        if (is_null($adjustableRecord->start_time)) {
            return new ActionResult(false, "Harus melakukan clock in terlebih dahulu", null);
        }

        // Validate that current time matches shift break configuration
        $shift = $adjustableRecord->detailSchedule?->shift ?? null;
        if ($shift) {
            if (isset($shift->break_time_in_minute) && (int) $shift->break_time_in_minute <= 0) {
                return new ActionResult(false, "Shift " . ($shift->name ?? '') . " tidak memiliki waktu istirahat.", null);
            }
            if ($shift->start_break_time && $timeNow < $shift->start_break_time) {
                return new ActionResult(false, "Belum waktunya istirahat (Waktu istirahat shift " . ($shift->name ?? '') . " mulai pukul " . substr($shift->start_break_time, 0, 5) . ")", null);
            }
            if ($shift->end_break_time && $timeNow > $shift->end_break_time) {
                return new ActionResult(false, "Waktu istirahat shift " . ($shift->name ?? '') . " sudah terlewat (" . substr($shift->start_break_time ?? '', 0, 5) . " - " . substr($shift->end_break_time, 0, 5) . ")", null);
            }
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
