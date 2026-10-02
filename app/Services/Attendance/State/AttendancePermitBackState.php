<?php

namespace App\Services\Attendance\State;

use App\DTO\AttendanceDTO;
use App\Helper\ActionResult;
use App\Helper\LogConsole;
use App\Repositories\Interface\AdjustableAttdRepository;
use App\Repositories\Interface\AttendanceRepository;
use App\Repositories\Interface\DetailScheduleRepository;
use App\Services\Attendance\AttendanceState;
use App\Services\WhatsappService;
use App\Utils\AttendanceStatus;
use App\Utils\DateNow;
use Illuminate\Support\Facades\DB;

class AttendancePermitBackState implements AttendanceState {
    private AttendanceRepository $attendanceRepository;
    private DetailScheduleRepository $detailScheduleRepository;
    private AdjustableAttdRepository $adjustableAttdRepository;
    private WhatsappService $whatsappService;

    public function __construct(
        AttendanceRepository $attendanceRepository,
        DetailScheduleRepository $detailScheduleRepository,
        AdjustableAttdRepository $adjustableAttdRepository,
        WhatsappService $whatsappService
    ) {
        $this->attendanceRepository = $attendanceRepository;
        $this->detailScheduleRepository = $detailScheduleRepository;
        $this->adjustableAttdRepository = $adjustableAttdRepository;
        $this->whatsappService = $whatsappService;
    }

    public function handle(AttendanceDTO $data): ActionResult {
        $timeNow = $data->getTimeNow();
        $now = DateNow::getCurrentDateYMD();

        $scheduleId = $data->getScheduleId();
        $detailScheduleId = $data->getDetailSchedule();

        $absenceHistory = $this->attendanceRepository->getById($data->getAttendanceId());

        $attData = [
            "permit_back" => $timeNow,
            "permit_back_message" => $data->getDescription(),
            "total_permit_min" => DateNow::getDifferentInMinute($absenceHistory->permit_start, $timeNow)
        ];
        $attendanceData = $this->attendanceRepository->update($data->getAttendanceId(), $attData);
        // Kirim notifikasi WhatsApp ke ortu
        $user = $attendanceData->user;
        if ($user && $user->intern) {
            $internName = $user->profile->full_name;
            $status = 'IZIN SELESAI';
            $time = $timeNow;
            $message = "Notifikasi Presensi: Anak Anda, {$internName}, telah melakukan presensi *{$status}* pada pukul {$time}. Terima kasih.";
            $this->whatsappService->sendNotificationToAllTargets($user->intern, $message);
        }
        DB::commit();
        $detailSchedule = $this->detailScheduleRepository->find($detailScheduleId);


        $adjustableTimeData = $this->adjustableAttdRepository->getByScheduleIdAndDate($detailSchedule->id, $now);
        $isTakeChangeTimeInBreak = false;


        if (!is_null($adjustableTimeData)) {
            foreach ($adjustableTimeData as $value) {
                if (!is_null($value)) {
                    if (!is_null($attendanceData)) {

                        // Pengecekan untuk start_time
                        if (!is_null($value->start_time) && $value->start_time > $attendanceData->start_time) {
                            $isTakeChangeTimeInBreak = true;
                        }
                    }

                    // Pengecekan apakah end_time adalah null
                    if (is_null($value->end_time)) {
                        $adjustableTarget = $value;
                        break; // Keluar dari loop jika ditemukan
                    }
                }
            }
        }

        if (is_null($attendanceData->break_time) && $detailSchedule->shift->break_time_in_minute > 0 && $isTakeChangeTimeInBreak == false) {
            return new ActionResult(true, "Berhasil kembali dari izin.", [
                "absenceHistory" => $attendanceData,
                "schedule_id" => $scheduleId,
                "detail_schedule_id" => $detailScheduleId,
                "stage" => AttendanceStatus::ShowBreakChangeTime
            ]);
        }

        return new ActionResult(true, "Berhasil kembali dari izin.", [
            "absenceHistory" => $attendanceData,
            "schedule_id" => $scheduleId,
            "detail_schedule_id" => $detailScheduleId,
            "stage" => AttendanceStatus::AttendanceOut
        ]);
    }
}
