<?php

namespace App\Services\Attendance\State;

use App\DTO\AttendanceDTO;
use App\Helper\ActionResult;
use App\Repositories\Interface\AttendanceRepository;
use App\Repositories\Interface\UserRepository;
use App\Services\Attendance\AttendanceState;
use App\Services\WhatsappService;
use App\Utils\AttendanceStatus;
use App\Utils\DateNow;
use Illuminate\Support\Facades\DB;

class AttendancePermitStartState implements AttendanceState {
    private AttendanceRepository $attendanceRepository;
    private WhatsappService $whatsappService;

    public function __construct(
        AttendanceRepository $attendanceRepository,
        WhatsappService $whatsappService
    ) {
        $this->attendanceRepository = $attendanceRepository;
        $this->whatsappService = $whatsappService;
    }

    public function handle(AttendanceDTO $data): ActionResult {
        $timeNow = $data->getTimeNow();

        $scheduleId = $data->getScheduleId();
        $detailScheduleId = $data->getDetailSchedule();

        $attData = [
            "permit_start" => $timeNow,
            "permit_start_message" => $data->getDescription()
        ];
        $attendanceData = $this->attendanceRepository->update($data->getAttendanceId(), $attData);
        // Kirim notifikasi WhatsApp ke ortu
        $user = $attendanceData->user;
        if ($user && $user->intern) {
            $internName = $user->profile->full_name;
            $status = 'IZIN MULAI';
            $time = $timeNow;
           $message = "Notifikasi Presensi: Anak Anda, {$internName}, telah melakukan presensi *{$status}* pada pukul {$time}. Terima kasih.";
           $this->whatsappService->sendNotificationToAllTargets($user->intern, $message);
        }
        DB::commit();
        return new ActionResult(true, "Kamu mengambil waktu izin ", [
            "absenceHistory" => $attendanceData,
            // "adjustableTimeHistory" => $adjustableData,
            "schedule_id" => $scheduleId,
            "detail_schedule_id" => $detailScheduleId,
            "stage" => AttendanceStatus::EndPermit
        ]);
    }
}
