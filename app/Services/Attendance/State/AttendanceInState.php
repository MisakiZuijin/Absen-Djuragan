<?php

namespace App\Services\Attendance\State;

use App\DTO\AttendanceDTO;
use App\DTO\ScheduleDTO;
use App\Helper\ActionResult;
use App\Helper\LogConsole;
use App\Repositories\Interface\AttendanceRepository;
use App\Repositories\Interface\DetailScheduleRepository;
use App\Repositories\Interface\OfficeRepository;
use App\Repositories\Interface\ScheduleRepository;
use App\Repositories\Interface\ShiftRepository;
use App\Repositories\Interface\UserRepository;
use App\Services\Attendance\AttendanceState;
use App\Utils\AttendanceStatus;
use Illuminate\Support\Facades\Log;
use App\Utils\DateNow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\WhatsappService;
use Carbon\Carbon;

use function Sentry\captureException;

class AttendanceInState implements AttendanceState
{
    private AttendanceRepository $attendanceRepository;
    private ScheduleRepository $scheduleRepository;
    private ShiftRepository $shiftRepository;
    private DetailScheduleRepository $detailScheduleRepository;
    private UserRepository $userRepository;
    private OfficeRepository $officeRepository;
    private WhatsappService $whatsappService;

    public function __construct(
        AttendanceRepository $attendanceRepository,
        ScheduleRepository $scheduleRepository,
        ShiftRepository $shiftRepository,
        DetailScheduleRepository $detailScheduleRepository,
        UserRepository $userRepository,
        OfficeRepository $officeRepository,
        WhatsappService $whatsappService,
    ) {
        $this->attendanceRepository = $attendanceRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->shiftRepository = $shiftRepository;
        $this->detailScheduleRepository = $detailScheduleRepository;
        $this->userRepository = $userRepository;
        $this->officeRepository = $officeRepository;
        $this->whatsappService = $whatsappService;
    }

    public function handle(AttendanceDTO $data): ActionResult
    {
        try {
            DB::beginTransaction();

            $user = $this->userRepository->findById($data->getUserId());
            $now = DateNow::getCurrentDateYMD();
            $timeNow = $data->getTimeNow();

            $internId = $user->intern->id;

            $scheduleId = $data->getScheduleId();
            $detailScheduleId = $data->getDetailSchedule();

            $shift = $this->shiftRepository->getByTimeRange($timeNow);
            $mapsTrack = $this->checkIsInOfficeArea($data->getLatitude(), $data->getLongitude());

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
                $timeToCheck = $timeNow < $shift->start_time ? $shift->start_time : $timeNow;
                $attData = [
                    "date" => $now,
                    "start_time" => $timeToCheck,
                    "start_time_message" => $data->getDescription(),
                    "latitude_start" => $data->getLatitude(),
                    "longitude_start" => $data->getLongitude()
                ];
                $resultAtt = $this->attendanceRepository->create($attData);
                $newData = [
                    "schedule_id" => $scheduleId,
                    "attendance_id" => $resultAtt->id,
                    "shift_id" => $shift->id,
                    "office_id" => $mapsTrack->officeData->id,
                    "date" => $now,
                    'work_type' =>  "wfo",
                    "isChangeSchedule" => true,
                    "isBackFirst" => true,
                    "attd_status_id" => 2
                ];
                $detailSchedule = $this->detailScheduleRepository->create($newData);
                $result = $this->attendanceRepository->getById($resultAtt->id);
                DB::commit();
                return new ActionResult(true, "Berhasil menandai presensi hari ini.", [
                    "absenceHistory" => $result,
                    "shift" => $shift,
                    "schedule_id" => $scheduleId,
                    "detail_schedule_id" => $detailSchedule->id,
                    "stage" => $detailSchedule->shift->break_time_in_minute > 0 ? AttendanceStatus::ShowBreakPermitChangeTime : AttendanceStatus::StartPermit
                ]);
            }

            if ($data->getAttendanceId() < 1) {
                $detailSchedule = $this->detailScheduleRepository->find($detailScheduleId);

                if ($mapsTrack->isInArea == false) return   new ActionResult(false, "Kamu tidak di Area Kantor manapun");

                $timeToCheck = $timeNow < $shift->start_time ? $shift->start_time : $timeNow;
                $attData = [
                    "date" => $now,
                    "start_time" => $timeToCheck,
                    "start_time_message" => $data->getDescription(),
                    "latitude_start" => $data->getLatitude(),
                    "longitude_start" => $data->getLongitude()
                ];
                $resultAtt = $this->attendanceRepository->create($attData);
                $result = $this->attendanceRepository->getById($resultAtt->id);
                $detailSchedule = $this->detailScheduleRepository->update($detailScheduleId, ["attendance_id" => $resultAtt->id, "shift_id" => $shift->id, "attd_status_id" => 2]);
                DB::commit();
                return new ActionResult(true, "Berhasil menandai presensi hari ini.", [
                    "absenceHistory" => $result,
                    // "adjustableTimeHistory" => $adjustableData,
                    "schedule_id" => $scheduleId,
                    "detail_schedule_id" => $detailScheduleId,
                    "stage" => $detailSchedule->shift->break_time_in_minute > 0 ? AttendanceStatus::ShowBreakPermitChangeTime : AttendanceStatus::StartPermit
                ]);
            }


            if ($mapsTrack->isInArea == false) return new ActionResult(false, "Kamu tidak di Area Kantor manapun");

            $detailSchedule = $this->detailScheduleRepository->find($detailScheduleId);
            if ($timeNow < DateNow::getLastHour($detailSchedule->shift->start_time, 60) && $detailSchedule->isBackFirst == false) {
                return new ActionResult(false, "Belum waktunya presensi", null);
            }

            if ($timeNow > $detailSchedule->shift->end_time) {
                return new ActionResult(false, "Jadwalmu sudah terlewat", null);
            }

            $timeToCheck = $timeNow < $detailSchedule->shift->start_time ? $detailSchedule->shift->start_time : $timeNow;
            $this->detailScheduleRepository->update($detailScheduleId, ["attd_status_id" => 2]);

            $this->attendanceRepository->update($data->getAttendanceId(), [
                "start_time" => $timeToCheck,
                "start_time_message" => $data->getDescription(),
                "latitude_start" => $data->getLatitude(),
                "longitude_start" => $data->getLongitude()
            ]);
            $result = $this->attendanceRepository->getById($data->getAttendanceId());

            DB::commit();

            $intern = $user->intern;
            if ($intern) {
                $waNumberInfo = $intern->whatsappNumber;
                if ($waNumberInfo && $waNumberInfo->is_notification_active) {

                    $internName = $user->profile->full_name;
                    $status = 'MASUK';
                    $time = Carbon::parse($timeToCheck)->format('H:i');

                    $message = "*Notifikasi Presensi*\n\n" .
                        "Ananda *{$internName}* telah melakukan presensi *{$status}* pada pukul *{$time}*.\n\n" .
                        "Terima kasih.";

                    try {
                        $this->whatsappService->sendNotificationToAllTargets($intern, $message);
                    } catch (\Exception $e) {
                        Log::error('Gagal mengirim notifikasi presensi untuk intern ' . $intern->id . ': ' . $e->getMessage());
                    }
                }
            }

            return new ActionResult(true, "Berhasil menandai presensi hari ini.", [
                "absenceHistory" => $result,
                "schedule_id" => $scheduleId,
                "detail_schedule_id" => $detailScheduleId,
                "stage" => $detailSchedule->shift->break_time_in_minute > 0 ? AttendanceStatus::ShowBreakPermitChangeTime : AttendanceStatus::StartPermit
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            captureException($th);
            LogConsole::info($th);
            return new ActionResult(false, "Something went wrong.", null);
        }
    }
    // this function is duplicate, next need to make it reusable
    private function createSchedule(ScheduleDTO $data)
    {
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

    private function checkIsInOfficeArea(float $latitude, float $longitude): object
    {
        $result = new \stdClass();
        $offices = $this->officeRepository->getAll();
        $isInOfficeArea = false;

        foreach ($offices as $office) {
            if ($isInOfficeArea) break;
            $filterCoordinate = array_filter($office->coordinates->toArray(), function ($item) {
                return $item['is_main'] == 0;
            });

            $filterCoordinate = array_values($filterCoordinate);

            $lat1 = $filterCoordinate[0]['latitude'];
            $long1 = $filterCoordinate[0]['longitude'];
            $lat2 = $filterCoordinate[1]['latitude'];
            $long2 = $filterCoordinate[1]['longitude'];


            $minLat = min($lat1, $lat2);
            $maxLat = max($lat1, $lat2);
            $minLong = min($long1, $long2);
            $maxLong = max($long1, $long2);

            if (Auth::user()->is_gps_activate == 1) {
                $isInOfficeArea = ($latitude >= $minLat && $latitude <= $maxLat) && ($longitude >= $minLong && $longitude <= $maxLong);
            } else {
                $isInOfficeArea = true;
            }
            $result->officeData = $office;
        }
        $result->isInArea = $isInOfficeArea;
        return $result;
    }
}
