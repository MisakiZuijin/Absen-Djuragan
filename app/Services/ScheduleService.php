<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Helper\LogConsole;
use App\Http\Requests\InternScheduleRequest;
use App\Repositories\Interface\AttendanceRepository;
use App\Repositories\Interface\DetailScheduleRepository;
use App\Repositories\Interface\InternRepository;
use App\Repositories\Interface\LogActivityRepository;
use App\Repositories\Interface\ScheduleRepository;
use App\Repositories\Interface\UserRepository;
use App\Utils\DateNow;
use DateTime;
use Exception;
use Illuminate\Notifications\Action;
use Illuminate\Support\Facades\DB;
use Throwable;

use function Sentry\captureException;

class ScheduleService {
    protected $detailScheduleRepository;
    protected $scheduleRepository;
    protected $attendanceRepository;
    protected $logActivityRepository;
    protected $internRepository;
    protected $userRepository;

    public function __construct(
        ScheduleRepository $scheduleRepository,
        DetailScheduleRepository $detailScheduleRepository,
        AttendanceRepository $attendanceRepository,
        LogActivityRepository $logActivityRepository,
        InternRepository $internRepository,
        UserRepository $userRepository
    ) {
        $this->scheduleRepository = $scheduleRepository;
        $this->detailScheduleRepository = $detailScheduleRepository;
        $this->attendanceRepository = $attendanceRepository;
        $this->logActivityRepository = $logActivityRepository;
        $this->internRepository = $internRepository;
        $this->userRepository = $userRepository;
    }

    public function getScheduleByInternId($internId) {
        try {
            $result = $this->scheduleRepository->findByInternId($internId);
            $data = [
                "schedule"  => $result,
                "schedule_data" => $result->detailSchedules
            ];

            return new ActionResult(true, "success get data", $data);
        } catch (Throwable $th) {
            captureException($th);
            return new ActionResult(false, "something weird |" . $th->getMessage(), null);
        }
    }

    public function getShiftSchedule($internId) {
        try {
            $date = DateNow::getCurrentDateYMD();

            $schedule =  $this->scheduleRepository->findByInternId($internId);

            $detailSchedule = $this->detailScheduleRepository->findByScheduleIdAndDate($schedule->id, $date);
            $shift = $detailSchedule->shift->name;
            return new ActionResult(true, "succes retrive shift today", $shift);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "something weird |" . $th->getMessage(), null);
        }
    }

    public function createSchedule(InternScheduleRequest $request) {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $internId = $data['intern_id'];

            $intern = $this->internRepository->getById($data['intern_id']);
            if (!$intern) {
                return new ActionResult(false, "intern not found", null);
            }

            $start_date = new DateTime($data['start_period']);
            $end_date = new DateTime($data['end_period']);
            if ($end_date <= $start_date) return new ActionResult(false, 'end periode tidak boleh kurang dari start periode', null);

            $existSchedul = $this->scheduleRepository->findByInternId($intern->id);
            if (!is_null($existSchedul)) {
                $existEndDate = new DateTime($existSchedul->end_period);
                if ($start_date <= $existEndDate) return new ActionResult(false, 'periode yang sudah ada berakhir di tanggal ' . $existEndDate->format('Y-m-d'), null);
            }

            $shiftId = $data["shift_id"];
            $shiftIdBasic = [2, 4];
            unset($data["shift_id"]);
            $category = $data["type"];
            $data["type"] = $data["type"] == 1 ? "daily" : "weekly";

            if ($existSchedul) {
                $data['start_period'] = $existSchedul->start_period;
                $result1 = $this->scheduleRepository->update($existSchedul->id, $data);
            } else {
                $result1 = $this->scheduleRepository->create($data);
            }

            $current_date = $start_date;
            $currentShift = $shiftId;
            $officeId = $data["office_id"] ?? 1;
            while ($current_date <= $end_date) {
                if ($current_date->format('N') != 7) {
                    $attData = [
                        "date" => $current_date->format('Y-m-d'),
                        "intern_id" => $internId
                    ];

                    $resultAtt = $this->attendanceRepository->create($attData);

                    if ($current_date->format('N') == 1 && $category == 2) {
                        $currentShift = $this->isNextShift($shiftIdBasic, $currentShift);
                    }

                    $data = [
                        "attendance_id" => $resultAtt->id,
                        "schedule_id" => $result1->id,
                        "shift_id" => $currentShift,
                        "office_id" => $officeId,
                        "date" => $current_date->format('Y-m-d'),
                        "isChangeSchedule" => false
                    ];

                    $this->detailScheduleRepository->create($data);
                }
                $current_date->modify('+1 day');
            }
            DB::commit();

            return new ActionResult(true, "success added new schedule", $result1);
        } catch (\Throwable $th) {
            DB::rollBack();
            captureException($th);
            LogConsole::info($th);
            return new ActionResult(false, "failed to added new schedule", null);
        }
    }


    private function isNextShift(array $indexShift, int $currentShift) {
        $currentIndex = array_search($currentShift, $indexShift);

        if ($currentIndex === false) {
            throw new Exception("Shift not found in array.");
        }

        $nextIndex = ($currentIndex + 1) % count($indexShift);

        $nextShift = $indexShift[$nextIndex];
        return $nextShift;
    }


    public function weekSchedules($internId, $date): ActionResult {
        try {

            $resultSchedules = $this->detailScheduleRepository->findByInternIdAndWeek($internId, $date);


            return new ActionResult(true, "success retrive schedules data", $resultSchedules);
        } catch (Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed retrive schedules data", null);
        }
    }

    public function updateShift($id, $shiftId) {
        try {
            $this->detailScheduleRepository->updateShift($id, $shiftId);
            return new ActionResult(true, "success update shift");
        } catch (Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed to update shift");
        }
    }


    public function updateScheduleSingleData($internId, $date, $data): ActionResult {
        try {
            $schedule = $this->scheduleRepository->findByInternId($internId);
            $existData = $this->detailScheduleRepository->findByScheduleIdAndDate($schedule->id, $date);
            if (!$existData) {
                $attData = [
                    "date" => $date,
                    "intern_id" => $internId
                ];
                $resultAtt = $this->attendanceRepository->create($attData);
                $newData = [
                    "attendance_id" => $resultAtt->id,
                    "schedule_id" => $schedule->id,
                    "shift_id" => $data["shift_id"],
                    "office_id" => $data["office_id"],
                    "date" => $date,
                    "type" => "default",
                    'work_type' =>  $data["work_type"],
                    "isChangeSchedule" => $data["schedule_type"] == 0 ? false : true,
                    "isBackFirst" => $data["back_earlier"] == 0 ? false : true
                ];
                $result = $this->detailScheduleRepository->create($newData);
                return new ActionResult(true, "success update data", $result);
            }

            $newData = [
                "shift_id" => $data["shift_id"],
                "office_id" => $data["office_id"],
                "date" => $date,
                'work_type' =>  $data["work_type"],
                "isChangeSchedule" => $data["schedule_type"] == 0 ? false : true,
                "isBackFirst" => $data["back_earlier"] == 0 ? false : true,
            ];

            $result = $this->detailScheduleRepository->update($existData->id, $newData);
            return new ActionResult(true, "success update data", $result);
        } catch (Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed update data");
        }
    }

    public function updateScheduleMultiData($internId, $data) {
        try {
            $schedule = $this->scheduleRepository->findByInternId($internId);


            $listDate =  array_map('intval', explode(', ', $data["date"]));



            foreach ($listDate as $date) {
                $currentDate = $data["year"] . "-" . $data["month"] . "-" . $date;

                $existData = $this->detailScheduleRepository->findByScheduleIdAndDate($schedule->id, $currentDate);
                if (!$existData) {
                    $attData = [
                        "date" => $currentDate,
                        "intern_id" => $internId
                    ];
                    $resultAtt = $this->attendanceRepository->create($attData);
                    $newData = [
                        "attendance_id" => $resultAtt->id,
                        "schedule_id" => $schedule->id,
                        "shift_id" => $data["shift_id"],
                        "office_id" => $data["office_id"],
                        "date" => $currentDate,
                        // "type" => "default",
                        'work_type' =>  $data["work_type"],
                        "isChangeSchedule" => $data["schedule_type"] == 0 ? false : true
                    ];
                    $this->detailScheduleRepository->create($newData);

                    continue;
                }
                $newData = [
                    "shift_id" => $data["shift_id"],
                    "office_id" => $data["office_id"],
                    "date" => $currentDate,
                    'work_type' =>  $data["work_type"],
                    "isChangeSchedule" => $data["schedule_type"] == 0 ? false : true
                ];

                $this->detailScheduleRepository->update($existData->id, $newData);
            }
            return new ActionResult(true, "success update data", null);
        } catch (Throwable $th) {
            captureException($th);
            LogConsole::info($th->getMessage());
            return new ActionResult(false, "failed update data");
        }
    }
}
