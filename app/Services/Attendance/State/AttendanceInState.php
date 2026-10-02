<?php

namespace App\Services\Attendance\State;

use App\DTO\AttendanceDTO;
use App\DTO\ScheduleDTO;
use App\Helper\ActionResult;
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

            $existingDetailSchedule = null;
            if ($detailScheduleId) {
                $existingDetailSchedule = $this->detailScheduleRepository->find($detailScheduleId);
            } elseif ($user->intern) {
                $existingDetailSchedule = \App\Models\DetailSchedule::whereHas('schedule', function ($q) use ($internId) {
                    $q->where('intern_id', $internId);
                })->whereDate('date', $now)->first();

                if ($existingDetailSchedule) {
                    $detailScheduleId = $existingDetailSchedule->id;
                    $scheduleId = $scheduleId ?: $existingDetailSchedule->schedule_id;
                }
            }

            $shift = ($existingDetailSchedule && $existingDetailSchedule->shift) ? $existingDetailSchedule->shift : $this->shiftRepository->getByTimeRange($timeNow);
            $isWfhSchedule = $existingDetailSchedule && strtolower($existingDetailSchedule->work_type ?? '') === 'wfh';

            // Validasi jadwal masuk (dapat absen mulai 30 menit sebelum jam shift dimulai)
            if ($shift) {
                if ($timeNow < DateNow::getLastHour($shift->start_time, 30) && ($existingDetailSchedule?->isBackFirst == false || !$existingDetailSchedule)) {
                    return new ActionResult(false, "Belum waktunya presensi", null);
                }

                if ($timeNow > $shift->end_time) {
                    return new ActionResult(false, "Jadwalmu sudah terlewat", null);
                }
            }

            // Validasi apakah GPS diwajibkan (User WFO & Shift mengharuskan GPS & Jadwal bukan WFH)
            $isGpsRequired = ($user->is_gps_activate == 1) && (!$shift || $shift->is_gps_active == 1) && !$isWfhSchedule;

            $latitude = $data->getLatitude();
            $longitude = $data->getLongitude();

            if ($isGpsRequired) {
                if (is_null($latitude) || is_null($longitude)) {
                    return new ActionResult(false, "Gagal mendapatkan lokasi GPS. Pastikan GPS aktif dan izinkan akses lokasi.", null);
                }

                $mapsTrack = $this->checkIsInOfficeArea((float) $latitude, (float) $longitude, true);

                // Validasi area kantor HARUS dilakukan jika GPS aktif
                if ($mapsTrack->isInArea == false) {
                    return new ActionResult(false, "Kamu tidak di Area Kantor manapun");
                }
            } else {
                $mapsTrack = $this->checkIsInOfficeArea(
                    is_null($latitude) ? null : (float) $latitude,
                    is_null($longitude) ? null : (float) $longitude,
                    false
                );
            }

            if (!$scheduleId && $user->intern) {
                $existingSchedule = \App\Models\Schedule::where('intern_id', $internId)->orderByDesc('id')->first();
                if ($existingSchedule) {
                    $scheduleId = $existingSchedule->id;
                }
            }

            if (!$scheduleId) {
                $scheduleId = $this->createSchedule(new ScheduleDTO(
                    $internId,
                    $mapsTrack->officeData?->id,
                    $shift->id,
                    $now,
                    $now,
                    "daily"
                ))->id;
            }

            if (!$detailScheduleId) {
                $timeToCheck = $timeNow < $shift->start_time ? $shift->start_time : $timeNow;
                $attData = [
                    "intern_id" => $internId,
                    "date" => $now,
                    "start_time" => $timeToCheck,
                    "start_time_message" => $data->getDescription(),
                    "latitude_start" => $latitude,
                    "longitude_start" => $longitude
                ];
                $resultAtt = $this->attendanceRepository->create($attData);
                $newData = [
                    "schedule_id" => $scheduleId,
                    "attendance_id" => $resultAtt->id,
                    "shift_id" => $shift->id,
                    "office_id" => $mapsTrack->officeData?->id,
                    "date" => $now,
                    'work_type' =>  $isGpsRequired ? "wfo" : "wfh",
                    "isChangeSchedule" => true,
                    "isBackFirst" => true,
                    "attd_status_id" => 2
                ];
                $detailSchedule = $this->detailScheduleRepository->create($newData);
                $result = $resultAtt;
                DB::commit();
                \App\Helper\ActivityLogger::log('CREATE', 'Presensi', 'Pemagang ' . (Auth::user()?->profile?->full_name ?? Auth::user()?->username ?? 'User') . ' melakukan Absen Masuk.');

                $this->sendCheckinWaNotification($user, $timeToCheck);

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

                $timeToCheck = $timeNow < $shift->start_time ? $shift->start_time : $timeNow;
                $attData = [
                    "intern_id" => $internId,
                    "date" => $now,
                    "start_time" => $timeToCheck,
                    "start_time_message" => $data->getDescription(),
                    "latitude_start" => $latitude,
                    "longitude_start" => $longitude
                ];
                $resultAtt = $this->attendanceRepository->create($attData);
                $result = $resultAtt;
                $detailSchedule = $this->detailScheduleRepository->update($detailScheduleId, ["attendance_id" => $resultAtt->id, "shift_id" => $shift->id, "attd_status_id" => 2]);
                DB::commit();
                \App\Helper\ActivityLogger::log('CREATE', 'Presensi', 'Pemagang ' . (Auth::user()?->profile?->full_name ?? Auth::user()?->username ?? 'User') . ' melakukan Absen Masuk.');

                $this->sendCheckinWaNotification($user, $timeToCheck);

                return new ActionResult(true, "Berhasil menandai presensi hari ini.", [
                    "absenceHistory" => $result,
                    // "adjustableTimeHistory" => $adjustableData,
                    "schedule_id" => $scheduleId,
                    "detail_schedule_id" => $detailScheduleId,
                    "stage" => $detailSchedule->shift->break_time_in_minute > 0 ? AttendanceStatus::ShowBreakPermitChangeTime : AttendanceStatus::StartPermit
                ]);
            }


            $detailSchedule = $this->detailScheduleRepository->find($detailScheduleId);
            if ($timeNow < DateNow::getLastHour($detailSchedule->shift->start_time, 30) && $detailSchedule->isBackFirst == false) {
                return new ActionResult(false, "Belum waktunya presensi", null);
            }

            if ($timeNow > $detailSchedule->shift->end_time) {
                return new ActionResult(false, "Jadwalmu sudah terlewat", null);
            }

            $timeToCheck = $timeNow < $detailSchedule->shift->start_time ? $detailSchedule->shift->start_time : $timeNow;
            $this->detailScheduleRepository->update($detailScheduleId, ["attd_status_id" => 2]);

            $result = $this->attendanceRepository->update($data->getAttendanceId(), [
                "start_time" => $timeToCheck,
                "start_time_message" => $data->getDescription(),
                "latitude_start" => $data->getLatitude(),
                "longitude_start" => $data->getLongitude()
            ]);

            DB::commit();

            $this->sendCheckinWaNotification($user, $timeToCheck);

            return new ActionResult(true, "Berhasil menandai presensi hari ini.", [
                "absenceHistory" => $result,
                "schedule_id" => $scheduleId,
                "detail_schedule_id" => $detailScheduleId,
                "stage" => $detailSchedule->shift->break_time_in_minute > 0 ? AttendanceStatus::ShowBreakPermitChangeTime : AttendanceStatus::StartPermit
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AttendanceInState error: ' . $th->getMessage() . ' in ' . $th->getFile() . ':' . $th->getLine());
            Log::error($th->getTraceAsString());
            captureException($th);
            return new ActionResult(false, "Something went wrong: " . $th->getMessage(), null);
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

    private function checkIsInOfficeArea(?float $latitude, ?float $longitude, bool $isGpsRequired = true): object
    {
        $result = new \stdClass();
        $offices = $this->officeRepository->getAll();
        $result->officeData = $offices->first();
        $result->isInArea = false;

        if (!$isGpsRequired || is_null($latitude) || is_null($longitude)) {
            $result->isInArea = !$isGpsRequired;
            return $result;
        }

        $isInOfficeArea = false;
        foreach ($offices as $office) {
            if ($isInOfficeArea) break;
            $coordinates = $office->coordinates ? $office->coordinates->toArray() : [];
            $filterCoordinate = array_values(array_filter($coordinates, function ($item) {
                return isset($item['is_main']) && $item['is_main'] == 0;
            }));

            if (count($filterCoordinate) < 2) {
                continue;
            }

            $lat1 = (float) $filterCoordinate[0]['latitude'];
            $long1 = (float) $filterCoordinate[0]['longitude'];
            $lat2 = (float) $filterCoordinate[1]['latitude'];
            $long2 = (float) $filterCoordinate[1]['longitude'];

            $minLat = min($lat1, $lat2);
            $maxLat = max($lat1, $lat2);
            $minLong = min($long1, $long2);
            $maxLong = max($long1, $long2);

            $isInOfficeArea = ($latitude >= $minLat && $latitude <= $maxLat) && ($longitude >= $minLong && $longitude <= $maxLong);
            if ($isInOfficeArea) {
                $result->officeData = $office;
            }
        }
        $result->isInArea = $isInOfficeArea;
        return $result;
    }

    private function sendCheckinWaNotification(mixed $user, string $timeToCheck): void
    {
        try {
            $intern = $user?->intern;
            if ($intern) {
                $waNumberInfo = $intern->whatsappNumber;
                if ($waNumberInfo && $waNumberInfo->is_notification_active) {
                    $internName = $user->profile?->full_name ?? $user->username;
                    $status = 'MASUK';
                    $time = Carbon::parse($timeToCheck)->format('H:i');

                    $message = "*Notifikasi Presensi*\n\n" .
                        "Ananda *{$internName}* telah melakukan presensi *{$status}* pada pukul *{$time}*.\n\n" .
                        "Terima kasih.";

                    $this->whatsappService->sendNotificationToAllTargets($intern, $message);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim notifikasi presensi untuk user ' . $user?->id . ': ' . $e->getMessage());
        }
    }
}
