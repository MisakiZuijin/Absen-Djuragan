<?php

namespace App\Services\Attendance\State;

use App\DTO\AttendanceDTO;
use App\Helper\ActionResult;
use App\Helper\LogConsole;
use App\Repositories\Interface\AdjustableAttdRepository;
use App\Repositories\Interface\AttendanceRepository;
use App\Services\Attendance\AttendanceState;
use App\Services\LocationService;
use App\Services\WhatsappService;
use App\Utils\AttendanceStatus;
use App\Utils\DateNow;

class AdjustableOutState implements AttendanceState {
    private AdjustableAttdRepository $adjustableAttdRepository;
    private AttendanceRepository $attendanceRepository;
    private LocationService $locationService;
    private WhatsappService $whatsappService;

    public function __construct(AdjustableAttdRepository $adjustableAttdRepository, AttendanceRepository $attendanceRepository, LocationService $locationService, WhatsappService $whatsappService) {
        $this->adjustableAttdRepository = $adjustableAttdRepository;
        $this->attendanceRepository = $attendanceRepository;
        $this->locationService = $locationService;
        $this->whatsappService = $whatsappService;
    }

    public function handle(AttendanceDTO $data): ActionResult {
        try {
             $adjustableId = $data->getAdjustableId();

            if (!$adjustableId || $adjustableId <= 0) {
                return new ActionResult(false, "ID adjustable tidak valid", null);
            }

            $timeNow = $data->getTimeNow();
            $scheduleId = $data->getScheduleId();
            $detailScheduleId = $data->getDetailSchedule();

            $adjustableOld = $this->adjustableAttdRepository->getByid($data->getAdjustableId());
            if (!$adjustableOld) {
                LogConsole::info("Adjustable attendance data not found for ID: " . $data->getAdjustableId());
                return new ActionResult(false, "Data adjustable attendance tidak ditemukan");
            }

            $allAdjustable = $this->adjustableAttdRepository->getAll();

            $mapsTrack = $this->locationService->checkIsInOfficeArea($data->getLatitude(), $data->getLongitude());

            if ($mapsTrack->isInArea == false) {
                return new ActionResult(false, "Kamu tidak di Area Kantor manapun");
            }

            // Calculate total minutes, accounting for break time
            $totalMinutes = DateNow::getDifferentInMinute($adjustableOld->start_time, $timeNow);
            $breakMinutes = 0;

            // If there's break time, subtract it from total
            if ($adjustableOld->break_time && $adjustableOld->back_time) {
                $breakMinutes = DateNow::getDifferentInMinute($adjustableOld->break_time, $adjustableOld->back_time);
                $totalMinutes -= $breakMinutes;
            }

            $attData = [
                "end_time" => $timeNow,
                "total_min" => max(0, $totalMinutes), // Ensure non-negative
                "end_time_message" => $data->getDescription(),
                "latitude_end" => $data->getLatitude(),
                "longitude_end" => $data->getLongitude(),
            ];

            $adjustableData = $this->adjustableAttdRepository->update($data->getAdjustableId(), $attData);

            // Handle attendance data - it might be null or have ID 0
            $attendanceData = null;
            $attendanceId = $data->getAttendanceId();

            if ($attendanceId && $attendanceId > 0) {
                $attendanceData = $this->attendanceRepository->getById($attendanceId);
            }

            $isLastChangeTime = false;
            $totalChangeTime = $data->getTotalChangeTime();

            // Check if this is the last change time only if attendance data exists
            if ($attendanceData && $attendanceData->end_time) {
                foreach ($allAdjustable as $item) {
                    if ($item->start_time > $attendanceData->end_time) {
                        $isLastChangeTime = true;
                        break;
                    }
                }
            }

            // Determine next stage
            $nextStage = AttendanceStatus::AllDone;

            // Logic for determining next stage
            if ($totalChangeTime >= 3 || date('N') == 7 || $isLastChangeTime) {
                $nextStage = AttendanceStatus::AllDone;
            } else if ($attendanceData && $attendanceData->start_time && !$attendanceData->permit_start) {
                $nextStage = AttendanceStatus::StartPermit;
                $totalChangeTime = 2;
            } else if ($attendanceData && $attendanceData->start_time && !$attendanceData->end_time) {
                $nextStage = AttendanceStatus::AttendanceOut;
            } else if ($totalChangeTime < 1) {
                $nextStage = AttendanceStatus::AttendanceIn;
            }

            // Send WhatsApp notification - Get user from adjustable data or detail schedule
            $user = null;
            if ($adjustableData && isset($adjustableData->detailSchedule)) {
                $user = $adjustableData->detailSchedule->schedule->intern->user ?? null;
            }

            if ($user && $user->intern) {
                $internName = $user->profile->full_name ?? 'Unknown';
                $status = 'GANTI JAM PULANG';
                $time = $timeNow;
                $this->whatsappService->sendAttendanceNotificationToAllTargets($user->intern, $internName, $status, $time);
            }

            return new ActionResult(true, "Berhasil mengambil ganti jam.",  [
                "adjustableTimeHistory" => $adjustableData,
                "schedule_id" => $scheduleId,
                "detail_schedule_id" => $detailScheduleId,
                "stage" => $nextStage,
                "totalChangeTime" => $totalChangeTime
            ]);
        } catch (\Throwable $e) {
            LogConsole::info("Error on AdjustableOutState: " . $e->getMessage());
            return new ActionResult(false, "Gagal mengambil ganti jam.", null);
        }
    }
}
