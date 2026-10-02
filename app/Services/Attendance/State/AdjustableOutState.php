<?php

namespace App\Services\Attendance\State;

use App\DTO\AttendanceDTO;
use App\Helper\ActionResult;
use App\Repositories\Interface\AdjustableAttdRepository;
use App\Repositories\Interface\AttendanceRepository;
use App\Services\Attendance\AttendanceState;
use App\Services\LocationService;
use App\Services\WhatsappService;
use App\Utils\AttendanceStatus;
use App\Utils\DateNow;
use Illuminate\Support\Facades\Log;

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

            $timeNow = $data->getTimeNow() ?: DateNow::getCurrentTime();
            $scheduleId = $data->getScheduleId() ?: ($adjustableOld->detailSchedule?->schedule_id ?? null);
            $detailScheduleId = $data->getDetailSchedule() ?: $adjustableOld->detail_schedule_id;

            $user = \Illuminate\Support\Facades\Auth::user();
            $shift = $adjustableOld->detailSchedule?->shift ?? null;
            $isWfhSchedule = $adjustableOld->detailSchedule && strtolower($adjustableOld->detailSchedule->work_type ?? '') === 'wfh';
            $isGpsRequired = ($user && $user->is_gps_activate == 1) && (!$shift || $shift->is_gps_active == 1) && !$isWfhSchedule;

            $latitude = $data->getLatitude();
            $longitude = $data->getLongitude();

            if ($isGpsRequired) {
                if ($latitude === null || $longitude === null) {
                    return new ActionResult(false, "Gagal mendapatkan lokasi GPS. Pastikan GPS aktif dan izinkan akses lokasi.");
                }
                $mapsTrack = $this->locationService->checkIsInOfficeArea((float) $latitude, (float) $longitude, true);

                if ($mapsTrack->isInArea == false) {
                    return new ActionResult(false, "Kamu tidak di Area Kantor manapun");
                }
            }

            // Target Detail Schedule (Jadwal target hutang yang diganti)
            $targetDetailSchedule = null;
            $targetDebtMinutes = 0;
            if ($adjustableOld->detail_schedule_id) {
                $targetDetailSchedule = \App\Models\DetailSchedule::with(['shift', 'attendance.permitLogs', 'permitReason.category', 'schedule'])->find($adjustableOld->detail_schedule_id);
                if ($targetDetailSchedule) {
                    $targetDebtMinutes = \App\Helper\TimeHelper::getScheduleTargetDebtMinutes($targetDetailSchedule, $adjustableOld->id);
                }
            }

            // Calculate total minutes, accounting for break time
            $totalMinutes = DateNow::getDifferentInMinute($adjustableOld->start_time, $timeNow);
            $breakMinutes = 0;

            // If there's break time, subtract it from total
            if ($adjustableOld->break_time && $adjustableOld->back_time) {
                $breakMinutes = DateNow::getDifferentInMinute($adjustableOld->break_time, $adjustableOld->back_time);
                $totalMinutes -= $breakMinutes;
            }
            $totalMinutes = max(0, $totalMinutes);


            $attData = [
                "end_time" => $timeNow,
                "total_min" => $totalMinutes,
                "total_break_min" => $breakMinutes,
                "end_time_message" => $data->getDescription(),
                "latitude_end" => $data->getLatitude(),
                "longitude_end" => $data->getLongitude(),
            ];

            $adjustableData = $this->adjustableAttdRepository->update($adjustableId, $attData);

            // Jika hutang di target shift telah terselesaikan (lunas):
            // Set waktu masuk dan pulang pada shift tersebut sesuai jam shift
            if ($targetDetailSchedule && ($targetDebtMinutes <= 0 || $totalMinutes >= $targetDebtMinutes)) {
                \App\Helper\TimeHelper::fulfillDebtScheduleAttendance($targetDetailSchedule, $adjustableOld->intern_id);
            }

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
                // Query all adjustable records for this detail schedule and date
                $allAdjustable = $this->adjustableAttdRepository->getByScheduleIdAndDate(
                    $adjustableOld->detail_schedule_id,
                    $adjustableOld->date ?? date('Y-m-d')
                );
                if ($allAdjustable) {
                    foreach ($allAdjustable as $item) {
                        if ($item->start_time > $attendanceData->end_time) {
                            $isLastChangeTime = true;
                            break;
                        }
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

            // Send WhatsApp notification - Get user from Auth or adjustable data
            $user = \Illuminate\Support\Facades\Auth::user() ?: ($adjustableData?->detailSchedule?->schedule?->intern?->user ?? null);

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
            Log::error("Error on AdjustableOutState: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return new ActionResult(false, "Gagal mengambil ganti jam.", null);
        }
    }
}
