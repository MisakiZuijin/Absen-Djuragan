<?php

namespace App\Services\Attendance\State;

use App\DTO\AttendanceDTO;
use App\Helper\ActionResult;
use App\Helper\LogConsole;
use App\Models\DetailSchedule;
use App\Repositories\Interface\AttendanceRepository;
use App\Repositories\Interface\DetailScheduleRepository;
use App\Services\Attendance\AttendanceState;
use App\Utils\AttendanceStatus;
use App\Utils\DateNow;
use Illuminate\Support\Facades\DB;

class AttendanceBreakStartState implements AttendanceState {
    private AttendanceRepository $attendanceRepository;
    private DetailScheduleRepository $detailScheduleRepository;

    public function __construct(AttendanceRepository $attendanceRepository, DetailScheduleRepository $detailScheduleRepository) {
        $this->attendanceRepository = $attendanceRepository;
        $this->detailScheduleRepository = $detailScheduleRepository;
    }

    public function handle(AttendanceDTO $data): ActionResult {
        $timeNow = $data->getTimeNow();
        $scheduleId = $data->getScheduleId();
        $detailScheduleId = $data->getDetailSchedule();

        $detailSchedule = $this->detailScheduleRepository->find($detailScheduleId);
        
        // Check if detailSchedule exists
        if (!$detailSchedule) {
            return new ActionResult(false, "Detail schedule tidak ditemukan", null);
        }

        // Check if shift exists
        if (!$detailSchedule->shift) {
            return new ActionResult(false, "Shift tidak ditemukan untuk jadwal ini", null);
        }

        $attendanceData = $this->attendanceRepository->getById($data->getAttendanceId());
        
        if (!$attendanceData) {
            return new ActionResult(false, "Data attendance tidak ditemukan", null);
        }

        // Validate break time only if break_first is false and shift has break time settings
        if ($detailSchedule->is_break_first == false) {
            if ($detailSchedule->shift->start_break_time && $detailSchedule->shift->start_break_time > $timeNow) {
                return new ActionResult(false, "Belum waktunya istirahat", null);
            }

            if ($detailSchedule->shift->end_break_time && $detailSchedule->shift->end_break_time <= $timeNow) {
                if (is_null($attendanceData->permit_start)) {
                    return new ActionResult(true, "Waktu jam istirahatmu sudah terlewat", [
                        "absenceHistory" => $attendanceData,
                        "schedule_id" => $scheduleId,
                        "detail_schedule_id" => $detailScheduleId,
                        "stage" => AttendanceStatus::StartPermit
                    ]);
                } else {
                    return new ActionResult(true, "Menunggu waktu pulang", [
                        "absenceHistory" => $attendanceData,
                        "schedule_id" => $scheduleId,
                        "detail_schedule_id" => $detailScheduleId,
                        "stage" => AttendanceStatus::AttendanceOut
                    ]);
                }
            }
        }

        // Determine break time based on is_break_first setting
        $breakTime = $timeNow; // Default to current time
        if ($detailSchedule->is_break_first == false && $detailSchedule->shift->start_break_time) {
            $breakTime = $detailSchedule->shift->start_break_time;
        }

        $attData = [
            "break_time" => $breakTime,
            "break_time_message" => $data->getDescription(),
        ];
        
        $attendanceData = $this->attendanceRepository->update($data->getAttendanceId(), $attData);

        return new ActionResult(true, "Berhasil mengambil istirahat", [
            "absenceHistory" => $attendanceData,
            "schedule_id" => $scheduleId,
            "detail_schedule_id" => $detailScheduleId,
            "stage" => AttendanceStatus::EndBreak
        ]);
    }
}