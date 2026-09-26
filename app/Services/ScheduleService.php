<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Http\Requests\InternScheduleRequest;
use App\Repositories\Interface\AttendanceRepository;
use App\Repositories\Interface\DetailScheduleRepository;
use App\Repositories\Interface\InternRepository;
use App\Repositories\Interface\LogActivityRepository;
use App\Repositories\Interface\ScheduleRepository;
use App\Repositories\Interface\UserRepository;
use App\Models\DetailSchedule;
use App\Utils\DateNow;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Notifications\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

use function Sentry\captureException;

class ScheduleService
{
    protected DetailScheduleRepository $detailScheduleRepository;
    protected ScheduleRepository $scheduleRepository;
    protected AttendanceRepository $attendanceRepository;
    protected LogActivityRepository $logActivityRepository;
    protected InternRepository $internRepository;
    protected UserRepository $userRepository;

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

    public function getScheduleByInternId(int $internId)
    {
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

    public function getShiftSchedule(int $internId)
    {
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

    public function createSchedule(InternScheduleRequest $request)
    {
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

            $current_date = clone $start_date;
            $currentShift = $shiftId;
            $officeId = $data["office_id"] ?? 1;

            $attendancesToInsert = [];
            $scheduleDatesMap = []; // date => shift_id

            while ($current_date <= $end_date) {
                if ($current_date->format('N') != 7) {
                    $dateStr = $current_date->format('Y-m-d');
                    $attendancesToInsert[] = [
                        "date" => $dateStr,
                        "intern_id" => $internId
                    ];

                    if ($current_date->format('N') == 1 && $category == 2) {
                        $currentShift = $this->isNextShift($shiftIdBasic, $currentShift);
                    }

                    $scheduleDatesMap[$dateStr] = $currentShift;
                }
                $current_date->modify('+1 day');
            }

            if (!empty($attendancesToInsert)) {
                // 1. Bulk insert attendances
                \App\Models\Attendance::insert($attendancesToInsert);

                // 2. Fetch created attendance IDs for this period in 1 single query
                $createdAttendances = \App\Models\Attendance::where('intern_id', $internId)
                    ->whereBetween('date', [$start_date->format('Y-m-d'), $end_date->format('Y-m-d')])
                    ->pluck('id', 'date');

                // 3. Build detail schedules bulk insert data
                $detailSchedulesToInsert = [];
                foreach ($scheduleDatesMap as $dateStr => $assignedShiftId) {
                    $attId = $createdAttendances->get($dateStr);
                    if ($attId) {
                        $detailSchedulesToInsert[] = [
                            "attendance_id" => $attId,
                            "schedule_id" => $result1->id,
                            "shift_id" => $assignedShiftId,
                            "office_id" => $officeId,
                            "date" => $dateStr,
                            "isChangeSchedule" => 0
                        ];
                    }
                }

                // 4. Bulk insert detail schedules
                if (!empty($detailSchedulesToInsert)) {
                    \App\Models\DetailSchedule::insert($detailSchedulesToInsert);
                }
            }
            DB::commit();

            return new ActionResult(true, "success added new schedule", $result1);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('createSchedule error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            captureException($th);
            return new ActionResult(false, "failed to added new schedule", null);
        }
    }


    private function isNextShift(array $indexShift, int $currentShift)
    {
        $currentIndex = array_search($currentShift, $indexShift);

        if ($currentIndex === false) {
            throw new Exception("Shift not found in array.");
        }

        $nextIndex = ($currentIndex + 1) % count($indexShift);

        $nextShift = $indexShift[$nextIndex];
        return $nextShift;
    }


    public function weekSchedules(int $internId, string $date): ActionResult
    {
        try {

            $resultSchedules = $this->detailScheduleRepository->findByInternIdAndWeek($internId, $date);


            return new ActionResult(true, "success retrive schedules data", $resultSchedules);
        } catch (Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed retrive schedules data", null);
        }
    }

    public function updateShift(int $id, int $shiftId)
    {
        try {
            $this->detailScheduleRepository->updateShift($id, $shiftId);
            return new ActionResult(true, "success update shift");
        } catch (Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed to update shift");
        }
    }


    public function updateScheduleSingleData(int $internId, string $date, array $data): ActionResult
    {
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

    public function updateScheduleMultiData(int $internId, array $data)
    {
        try {
            $schedule = $this->scheduleRepository->findByInternId($internId);
            if (!$schedule) {
                return new ActionResult(false, "schedule not found", null);
            }

            $listDate = array_map('intval', explode(', ', $data["date"]));

            $datesMap = [];
            foreach ($listDate as $date) {
                $datesMap[] = sprintf('%04d-%02d-%02d', (int)$data["year"], (int)$data["month"], (int)$date);
            }

            $existingDetailSchedules = DetailSchedule::where('schedule_id', $schedule->id)
                ->whereIn('date', $datesMap)
                ->get()
                ->keyBy(function ($item) {
                    return Carbon::parse($item->date)->format('Y-m-d');
                });

            $existingIds = $existingDetailSchedules->pluck('id')->all();
            if (!empty($existingIds)) {
                DetailSchedule::whereIn('id', $existingIds)->update([
                    "shift_id" => $data["shift_id"],
                    "office_id" => $data["office_id"],
                    'work_type' => $data["work_type"],
                    "isChangeSchedule" => $data["schedule_type"] == 0 ? false : true,
                ]);
            }

            $missingDates = array_diff($datesMap, $existingDetailSchedules->keys()->all());
            if (!empty($missingDates)) {
                $newDetailSchedules = [];
                foreach ($missingDates as $currentDate) {
                    $attData = [
                        "date" => $currentDate,
                        "intern_id" => $internId
                    ];
                    $resultAtt = $this->attendanceRepository->create($attData);
                    $newDetailSchedules[] = [
                        "attendance_id" => $resultAtt->id,
                        "schedule_id" => $schedule->id,
                        "shift_id" => $data["shift_id"],
                        "office_id" => $data["office_id"],
                        "date" => $currentDate,
                        'work_type' => $data["work_type"],
                        "isChangeSchedule" => $data["schedule_type"] == 0 ? false : true,
                    ];
                }
                if (!empty($newDetailSchedules)) {
                    DetailSchedule::insert($newDetailSchedules);
                }
            }

            return new ActionResult(true, "success update data", null);
        } catch (Throwable $th) {
            Log::error('updateSchedule error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            captureException($th);
            return new ActionResult(false, "failed update data");
        }
    }
}
