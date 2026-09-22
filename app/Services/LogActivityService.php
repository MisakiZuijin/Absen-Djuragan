<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Helper\LogConsole;
use App\Http\Requests\LogActivityRequest;
use App\Repositories\Interface\DetailScheduleRepository;
use App\Repositories\Interface\LogActivityRepository;
use App\Repositories\Interface\ScheduleRepository;
use App\Repositories\Interface\UserRepository;
use App\Utils\DateNow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function Sentry\captureException;

class LogActivityService
{
    protected LogActivityRepository $logActivityRepository;
    protected UserRepository $userRepository;
    protected ScheduleRepository $scheduleRepository;
    protected DetailScheduleRepository $detailScheduleRepository;

    public function __construct(
        LogActivityRepository $logActivityRepository,
        UserRepository $userRepository,
        ScheduleRepository $scheduleRepository,
        DetailScheduleRepository $detailScheduleRepository
    ) {
        $this->logActivityRepository = $logActivityRepository;
        $this->userRepository = $userRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->detailScheduleRepository = $detailScheduleRepository;
    }

    public function create(LogActivityRequest $request): ActionResult
    {
        try {
            $data = $request->validated();
            $user = $this->userRepository->findById($data["user_id"]);
            $internId = $user->intern->id;

            $currentDate = DateNow::getCurrentDate();
            $currentDateYMD = DateNow::getCurrentDateYMD();

            DB::beginTransaction();

            $schedule = $this->scheduleRepository->findByInternId($internId);

            if (!$schedule) {
                return new ActionResult(true, "Kamu masih belum punya jadwal", [
                    "absenceHistory" => null,
                    "stage" => 1
                ]);
            }

            $detailSchedule = $this->detailScheduleRepository->findByScheduleIdAndDate($schedule->id, $currentDateYMD);

            if (!$detailSchedule) {
                return new ActionResult(false, "Jadwal hari ini belum ditemukan. Harap hubungi admin untuk konfirmasi jadwal.", null);
            }

            if (!is_null($detailSchedule->log_activity_id)) {
                return new ActionResult(false, "Kamu sudah membuat Logbook hari ini!", [
                    "absenceHistory" => null,
                    "stage" => 1
                ]);
            }

            $data['date'] = $currentDateYMD;
            $resultLA = $this->logActivityRepository->store($data);

            $updateData = [
                'log_activity_id' => $resultLA->id
            ];

            $this->detailScheduleRepository->update($detailSchedule->id, $updateData);

            DB::commit();

            return new ActionResult(true, "Log Activity berhasil ditambahkan!", null);
        } catch (\Throwable $th) {
            DB::rollBack();
            captureException($th);
            LogConsole::info($th);
            return new ActionResult(false, "something went wrong", null);
        }
    }


    public function getLogHistory(): ActionResult
    {
        try {
            $user = $this->userRepository->getAuthenticatedUser();
            $internId = $user->intern->id;

            $data = $this->logActivityRepository->findByInternId($internId);
            if (!$data) {

                return new ActionResult(false, "you dont have any log activityf", null);
            }
            return new ActionResult(true, "success retrive log activity", $data);
        } catch (\Exception $e) {
            captureException($e);
            return new ActionResult(false, "somthing wrong", null);
        }
    }

    public function updateLogActivity(array $data)
    {
        try {

            $logActId = $data["id"];
            $data["status_id"] = 1;

            $data = $this->logActivityRepository->update($logActId, $data);
            if (!$data) {
                return new ActionResult(false, "failed update log activity", null);
            }
            return new ActionResult(true, "success update log activity", $data);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "somthing wrong", null);
        }
    }

    public function UpdateLGActivity(Request $request, int $id)
    {
        try {
            $validatedData = $request->validate([
                "status" => "required|numeric"
            ]);

            $status_id = $validatedData['status'];

            $result = $this->logActivityRepository->updateStatus($id, $status_id);

            if (!$result) {
                return new ActionResult(false, "Failed to update data in attendance, data not found", $result);
            }

            return new ActionResult(true, "Successfully updated data in attendance", $result);
        } catch (\Exception $e) {
            captureException($e);
            return new ActionResult(false, "Failed to update, something went wrong", null);
        }
    }
}
