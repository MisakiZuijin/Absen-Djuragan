<?php

namespace App\Services\Attendance\State;

use App\DTO\AttendanceDTO;
use App\DTO\ScheduleDTO;
use App\Helper\ActionResult;
use App\Helper\LogConsole;
use App\Repositories\Interface\AdjustableAttdRepository;
use App\Repositories\Interface\DetailScheduleRepository;
use App\Repositories\Interface\OfficeRepository;
use App\Repositories\Interface\ScheduleRepository;
use App\Repositories\Interface\ShiftRepository;
use App\Repositories\Interface\UserRepository;
use App\Services\Attendance\AttendanceState;
use App\Services\LocationService;
use App\Services\WhatsappService;
use App\Utils\AttendanceStatus;
use App\Utils\DateNow;
use Illuminate\Support\Facades\DB;

use function Sentry\captureException;

class AdjustableInState implements AttendanceState {
    private UserRepository $userRepository;
    private ShiftRepository $shiftRepository;
    private ScheduleRepository $scheduleRepository;
    private DetailScheduleRepository $detailScheduleRepository;
    private AdjustableAttdRepository $adjustableAttdRepository;
    private LocationService $locationService;
    private WhatsappService $whatsappService;

    public function __construct(
        UserRepository $userRepository,
        ShiftRepository $shiftRepository,
        ScheduleRepository $scheduleRepository,
        DetailScheduleRepository $detailscheduleRepository,
        AdjustableAttdRepository $adjustableAttdRepository,
        LocationService $locationService,
        WhatsappService $whatsappService
    ) {
        $this->userRepository = $userRepository;
        $this->shiftRepository = $shiftRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->detailScheduleRepository = $detailscheduleRepository;
        $this->adjustableAttdRepository = $adjustableAttdRepository;
        $this->locationService = $locationService;
        $this->whatsappService = $whatsappService;
    }

    public function handle(AttendanceDTO $data): ActionResult {
        DB::beginTransaction();

        $user = $this->userRepository->findById($data->getUserId());
        $now = DateNow::getCurrentDateYMD();
        $timeNow = $data->getTimeNow();
        $internId = $user->intern->id;

        $scheduleId = $data->getScheduleId();
        $detailScheduleId = $data->getDetailSchedule();

        $shift = $this->shiftRepository->getByTimeRange($timeNow);
        $mapsTrack = $this->locationService->checkIsInOfficeArea($data->getLatitude(), $data->getLongitude());

        if ($mapsTrack->isInArea == false) return new ActionResult(false, "Kamu tidak di Area Kantor manapun");

        if (!$scheduleId) {
            $scheduleId = $this->createSchedule(new ScheduleDTO(
                $internId,
                $mapsTrack->officeData->id,
                $shift->id,
                $now,
                $now,
                "daily"
            ))->id;
        }

        if (!$detailScheduleId) {
            $newData = [
                "schedule_id" => $scheduleId,
                "office_id" => $mapsTrack->officeData->id,
                "date" => $now,
                'work_type' =>  "wfo",
                "isChangeSchedule" => true,
                "isBackFirst" => true
            ];
            $detailScheduleId =  $this->detailScheduleRepository->create($newData)->id;
        }

        $detailSchedule = $this->detailScheduleRepository->find($detailScheduleId);
        $attendanceData = $detailSchedule->attendance;

        $isBreakChangeTime = !is_null($attendanceData) && $attendanceData->start_time != null && $attendanceData->end_time == null;

        if ($isBreakChangeTime && $detailSchedule->shift->start_break_time > $timeNow && $detailSchedule->is_break_first == false) {
            return new ActionResult(false, "Belum waktunya istirahat", null);
        }
        if ($isBreakChangeTime && $detailSchedule->shift->end_break_time <= $timeNow  && $detailSchedule->is_break_first == false) {
            if (is_null($attendanceData->permit_start)) {
                return new ActionResult(true, "Waktu jam istirahatmu sudah terlewat", [
                    "absenceHistory" => $attendanceData,
                    "schedule_id" => $scheduleId,
                    "detail_schedule_id" => $detailScheduleId,
                    "stage" => AttendanceStatus::StartPermit
                ]);
            } else {
                return new ActionResult(true, "Sekarang sudah waktunya pulang", [
                    "absenceHistory" => $attendanceData,
                    "schedule_id" => $scheduleId,
                    "detail_schedule_id" => $detailScheduleId,
                    "stage" => AttendanceStatus::AttendanceOut
                ]);
            }
        }

        $originalStartTime = $detailSchedule->shift->start_time ?? $timeNow;
        $originalEndTime = $detailSchedule->shift->end_time ?? $timeNow;

        $attData = [
            "intern_id" => $internId,
            "date" =>  $now,
            "detail_schedule_id" => $detailScheduleId,
            "start_time" => $timeNow,
            "latitude_start" => $data->getLatitude(),
            "longitude_start" => $data->getLongitude(),
            "start_time_message" => $data->getDescription(),
            "original_start_time" => $originalStartTime,
            "original_end_time" => $originalEndTime,
            "adjusted_start_time" => $timeNow,
            "adjusted_end_time" => $timeNow,
            "total_min" => 0,
            "total_break_min" => 0,
            "is_approved" => 0,
        ];

        $adjustableAttd = $this->adjustableAttdRepository->store($attData);
        $adjustableId = $adjustableAttd->id;

        LogConsole::info("Created adjustable record with ID: " . $adjustableId);

        $adjustableData = $this->adjustableAttdRepository->getById($adjustableId);
        LogConsole::info("Retrieved adjustable data ID: " . $adjustableData->id);

        DB::commit();

        if ($user->intern) {
            $internName = $user->profile->full_name;
            $status = 'GANTI JAM MASUK';
            $time = $timeNow;
            $this->whatsappService->sendAttendanceNotificationToAllTargets($user->intern, $internName, $status, $time);
        }

        return new ActionResult(true, "Berhasil mengambil ganti jam.", [
            "adjustableTimeHistory" => $adjustableData,
            "schedule_id" => $scheduleId,
            "detail_schedule_id" => $detailScheduleId,
            "adjustable_id" => $adjustableId,
            "stage" => AttendanceStatus::BreakOrBack,
        ]);
    }

    private function createSchedule(ScheduleDTO $data) {
        try {
            $data = [
                "intern_id" => $data->getInternId(),
                "office_id" => $data->getOfficeId(),
                "start_period" => $data->getStartPeriod(),
                "end_period" => $data->getEndPeriod(),
                "type" => $data->getType()
            ];
            return $this->scheduleRepository->create($data);
        } catch (\Throwable $th) {
            captureException($th);
            return null;
        }
    }
}
