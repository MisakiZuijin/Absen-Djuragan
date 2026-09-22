<?php

namespace App\Services;

use App\DTO\AttendanceDTO;
use App\DTO\DetailScheduleDTO;
use App\DTO\ScheduleDTO;
use App\Helper\ActionResult;
use App\Helper\LogConsole;
use App\Repositories\Interface\AttendanceRepository;
use App\Repositories\Interface\DetailScheduleRepository;
use App\Repositories\Interface\InternRepository;
use App\Repositories\Interface\ScheduleRepository;
use App\Repositories\Interface\UserRepository;
use App\Http\Requests\StorePermitPresenceRequest;
use App\Http\Requests\UpdatePermitPresenceRequest;
use App\Http\Requests\UpdateStatusAttendanceRequest;
use App\Http\Requests\UpdateTimeAttendanceRequest;
use App\Utils\DateNow;
use Carbon\Carbon;
use App\Models\PermitReason;
use App\Models\DetailSchedule;
use App\Models\Intern;
use App\Repositories\Interface\AdjustableAttdRepository;
use App\Repositories\Interface\CoordinateRepository;
use App\Repositories\Interface\DiscountTimeRepository;
use App\Repositories\Interface\OfficeRepository;
use App\Repositories\Interface\ShiftRepository;
use App\Services\Attendance\AttendanceContext;
use App\Services\Attendance\AttendanceState;
use App\Services\Attendance\State\AdjustableBreakBackState;
use App\Services\Attendance\State\AdjustableBreakStartState;
use App\Services\Attendance\State\AdjustableInState;
use App\Services\Attendance\State\AdjustableOutState;
use App\Services\Attendance\State\AllDone;
use App\Services\Attendance\State\AllDoneState;
use App\Services\Attendance\State\AttendanceBreakBackState;
use App\Services\Attendance\State\AttendanceBreakStartState;
use App\Services\Attendance\State\AttendanceInState;
use App\Services\Attendance\State\AttendanceOutState;
use App\Services\Attendance\State\AttendancePermitBackState;
use App\Services\Attendance\State\AttendancePermitStartState;
use App\Services\WhatsappService;
use App\Utils\AttendanceStatus;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function Sentry\captureException;

class AttendanceService
{
    protected AttendanceRepository $attendanceRepository;
    protected UserRepository $userRepository;
    protected InternRepository $internRepository;
    protected ScheduleRepository $scheduleRepository;
    protected DetailScheduleRepository $detailScheduleRepository;
    protected CoordinateRepository $coordinateRepository;
    protected OfficeRepository $officeRepository;
    protected ShiftRepository $shiftRepository;
    protected DiscountTimeRepository $discountTimeRepository;
    protected AdjustableAttdRepository $adjustableAttdRepository;
    protected LocationService $locationService;
    protected WhatsappService $whatsappService;

    public function __construct(
        UserRepository $userRepository,
        AttendanceRepository  $absenceRepo,
        InternRepository $internRepository,
        ScheduleRepository $scheduleRepository,
        DetailScheduleRepository $detailScheduleRepository,
        CoordinateRepository $coordinateRepository,
        OfficeRepository $officeRepository,
        ShiftRepository $shiftRepository,
        DiscountTimeRepository $discountTimeRepository,
        AdjustableAttdRepository $adjustableAttdRepository,
        LocationService $locationService,
        WhatsappService $whatsappService,
    ) {
        $this->attendanceRepository = $absenceRepo;
        $this->userRepository = $userRepository;
        $this->internRepository = $internRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->detailScheduleRepository = $detailScheduleRepository;
        $this->coordinateRepository = $coordinateRepository;
        $this->officeRepository = $officeRepository;
        $this->shiftRepository = $shiftRepository;
        $this->discountTimeRepository = $discountTimeRepository;
        $this->adjustableAttdRepository = $adjustableAttdRepository;
        $this->locationService = $locationService;
        $this->whatsappService = $whatsappService;
    }
    public function updateTime(UpdateTimeAttendanceRequest $updateTimeAttendanceRequest, int $id)
    {
        try {
            $data = $updateTimeAttendanceRequest->validated();
            $field = $data['field'];
            $time = $data['time'];
            $tipe = $data['tipe'];

            if ($tipe === 'adst') {
                $result = $this->attendanceRepository->updateAdjustableTime($id, $data);

                if (!$result) {
                    return new ActionResult(false, "Data tidak ditemukan di tabel AdjustableAttd", null);
                }

                return new ActionResult(true, "Data adjustable berhasil diupdate", $result);
            } else {
                $result = $this->attendanceRepository->updateTime($id, $data);

                if (!$result) {
                    return new ActionResult(false, "Data attendance tidak ditemukan", null);
                }

                return new ActionResult(true, "Data berhasil diupdate", $result);
            }
        } catch (\Throwable $th) {
            return new ActionResult(false, "Gagal update: " . $th->getMessage(), null);
        }
    }

    public function updateStatus(UpdateStatusAttendanceRequest $updateStatusAttendanceRequest, int $id)
    {
        try {
            $data = $updateStatusAttendanceRequest->validated();
            $status = $data["status"];


            $result = $this->attendanceRepository->updateStatus($id, $status);
            if (!$result) {
                return new ActionResult(false, "failed update data into attendance, data not found", $result);
            }
            return new ActionResult(true, "Successfully update data into attendance", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "Failed to update, something went wrong", null);
        }
    }

    public function attendanceStatus(int $internId): ActionResult
    {
        try {
            $now = DateNow::getCurrentDateYMD();
            $timeNow = DateNow::getCurrentTime();


            $schedule =  $this->scheduleRepository->findByInternId($internId);

            if (!$schedule && date(format: 'w') == 0) {
                return new ActionResult(true, "Today you still don't have any shift yet, please wait until our team sets your shift.", [
                    "absenceHistory" => null,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => null,
                    "detail_schedule_id" => null,
                    "stage" => AttendanceStatus::AdjustableIn
                ]);
            }

            if (!$schedule) {
                return new ActionResult(true, "Today you still don't have any shift yet, please wait until our team sets your shift.", [
                    "absenceHistory" => null,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => null,
                    "detail_schedule_id" => null,
                    "stage" => AttendanceStatus::AttendanceAndAdjustableTime
                ]);
            }


            $detailSchedule = $this->detailScheduleRepository->findByScheduleIdAndDate($schedule->id, $now);

            if (is_null($detailSchedule) && date(format: 'w') == 0) {
                return new ActionResult(true, "", [
                    "absenceHistory" => null,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => null,
                    "stage" => AttendanceStatus::AdjustableIn
                ]);
            }

            if (is_null($detailSchedule)) {
                return new ActionResult(true, "", [
                    "absenceHistory" => null,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => null,
                    "stage" => AttendanceStatus::AttendanceAndAdjustableTime
                ]);
            }


            $adjustableTimeData = $this->adjustableAttdRepository->getByScheduleIdAndDate($detailSchedule->id, $now);

            $attendanceTarget = $detailSchedule->attendance;
            $adjustableTarget = null;
            $totalAdjustable = 0;
            $isTakeChangeTimeInBreak = false;
            $isLastChangeTaken = false;

            if (!is_null($adjustableTimeData)) {
                foreach ($adjustableTimeData as $value) {
                    if (!is_null($value)) {
                        // Periksa apakah $attendanceTarget tidak null sebelum mengakses propertinya
                        if (!is_null($attendanceTarget)) {
                            // Pengecekan untuk end_time
                            if (!is_null($value->end_time) && $value->end_time > $attendanceTarget->end_time) {
                                $isLastChangeTaken = true;
                            }

                            // Pengecekan untuk start_time
                            if (!is_null($value->start_time) && $value->start_time > $attendanceTarget->start_time) {
                                $isTakeChangeTimeInBreak = true;
                            }
                        }

                        // Pengecekan apakah end_time adalah null
                        if (is_null($value->end_time)) {
                            $adjustableTarget = $value;
                            break; // Keluar dari loop jika ditemukan
                        }

                        // Increment totalAdjustable jika semua pengecekan di atas tidak memicu break
                        $totalAdjustable++;
                    }
                }
            }

            if (date(format: 'w') == 0 && $totalAdjustable > 0) {
                return new ActionResult(true, "", [
                    "absenceHistory" => $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "shift" => $detailSchedule->shift,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "stage" => AttendanceStatus::AllDone
                ]);
            }

            $isAttendanceNull = is_null($attendanceTarget) || is_null($attendanceTarget->start_time);

            if (!$isAttendanceNull && !is_null($attendanceTarget->end_time) && !$isLastChangeTaken) {
                return new ActionResult(true, "", [
                    "absenceHistory" => $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "shift" => $detailSchedule->shift,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "stage" => AttendanceStatus::AdjustableIn
                ]);
            }

            if (!$isAttendanceNull && DateNow::checkIsTimeOrNot($timeNow, $detailSchedule->shift->end_time) && $attendanceTarget->end_time == null) {
                return new ActionResult(true, "", [
                    "absenceHistory" =>  $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "shift" => $detailSchedule->shift,
                    "totalChangeTime" => $totalAdjustable,
                    "stage" => AttendanceStatus::AttendanceOut
                ]);
            }

            if (is_null($attendanceTarget) && $totalAdjustable == 1) {
                return new ActionResult(true, "", [
                    "absenceHistory" => null,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => $adjustableTarget,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => AttendanceStatus::AttendanceIn,
                ]);
            }

            if (!is_null($attendanceTarget) && is_null($attendanceTarget->start_time) && $totalAdjustable == 1) {
                return new ActionResult(true, "", [
                    "absenceHistory" => $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => $adjustableTarget,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => AttendanceStatus::AttendanceIn,
                ]);
            }


            if (!is_null($attendanceTarget) && is_null($attendanceTarget->start_time) && is_null($adjustableTarget)) {
                return new ActionResult(true, "", [
                    "absenceHistory" => $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => AttendanceStatus::AttendanceAndAdjustableTime,
                ]);
            }


            if ($isAttendanceNull && is_null($adjustableTarget)) {
                return new ActionResult(true, "", [
                    "absenceHistory" => null,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => AttendanceStatus::AttendanceAndAdjustableTime,
                ]);
            }


            if (!is_null($adjustableTarget) && !is_null($adjustableTarget->start_time) && is_null($adjustableTarget->break_time)) {
                return new ActionResult(true, "", [
                    "absenceHistory" => null,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => $adjustableTarget,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => AttendanceStatus::BreakOrBack
                ]);
            }

            if (!is_null($adjustableTarget) && !is_null($adjustableTarget->break_time) && is_null($adjustableTarget->back_time)) {
                return new ActionResult(true, "", [
                    "absenceHistory" => null,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => $adjustableTarget,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => AttendanceStatus::EndBreakAdjustable
                ]);
            }

            if (!is_null($adjustableTarget) && !is_null($adjustableTarget->start_time) && is_null($adjustableTarget->end_time))
                return new ActionResult(true, "", [
                    "absenceHistory" => null,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => $adjustableTarget,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => AttendanceStatus::AdjustableOut
                ]);



            $canTakeBreak = is_null($attendanceTarget?->break_time) && $detailSchedule->shift->break_time_in_minute > 0;
            $canTakePermit = is_null($attendanceTarget?->permit_start);

            if (!$canTakePermit && $canTakeBreak && $isTakeChangeTimeInBreak) {
                return new ActionResult(true, "", [
                    "absenceHistory" =>  $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => AttendanceStatus::ShowBreakChangeTime
                ]);
            }

            if (!$isAttendanceNull && $canTakePermit && $isTakeChangeTimeInBreak) {
                return new ActionResult(true, "", [
                    "absenceHistory" =>  $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => AttendanceStatus::StartPermit
                ]);
            }

            if (!$isAttendanceNull && $canTakeBreak && $canTakePermit) {
                return new ActionResult(true, "", [
                    "absenceHistory" =>  $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => AttendanceStatus::ShowBreakPermitChangeTime
                ]);
            }

            if (!$isAttendanceNull && !$canTakePermit && is_null($attendanceTarget->permit_back)) {
                return new ActionResult(true, "", [
                    "absenceHistory" =>  $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => AttendanceStatus::EndPermit
                ]);
            }

            if (!$isAttendanceNull && !is_null($attendanceTarget->break_time) && is_null($attendanceTarget->back_time)) {
                return new ActionResult(true, "", [
                    "absenceHistory" =>  $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => AttendanceStatus::EndBreak
                ]);
            }

            if (!$isAttendanceNull && $canTakePermit) {
                return new ActionResult(true, "", [
                    "absenceHistory" =>  $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => AttendanceStatus::StartPermit
                ]);
            }

            if (!$isAttendanceNull && $canTakeBreak && $totalAdjustable == 0) {
                return new ActionResult(true, "", [
                    "absenceHistory" =>  $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => AttendanceStatus::StartBreak
                ]);
            }

            if (!$isAttendanceNull &&  is_null($attendanceTarget->end_time)) {
                return new ActionResult(true, "", [
                    "absenceHistory" => $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => $adjustableTarget,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "stage" => AttendanceStatus::AttendanceOut,
                    "shift" => $detailSchedule->shift,
                ]);
            }

            if (!$isLastChangeTaken) {
                return new ActionResult(true, "", [
                    "absenceHistory" => $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => $adjustableTarget,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable + 1,
                    "stage" => AttendanceStatus::AdjustableIn,
                    "shift" => $detailSchedule->shift,
                ]);
            }

            return new ActionResult(true, "", [
                "absenceHistory" => $attendanceTarget,
                "all_adjustable" => $adjustableTimeData,
                "adjustableTimeHistory" => $adjustableTarget,
                "schedule_id" => $schedule->id,
                "detail_schedule_id" => $detailSchedule->id,
                "totalChangeTime" => $totalAdjustable,
                "stage" => AttendanceStatus::AllDone,
                "shift" => $detailSchedule->shift,
            ]);
        } catch (\Throwable $th) {
            captureException($th);
            LogConsole::info($th);
            return new ActionResult(false, "Something went wrong.", null);
        }
    }


    public function attendanceAction(AttendanceDTO $data): ActionResult
    {
        try {
            $state = $this->getStateForStage($data->getStage());
            $context = new AttendanceContext($state);

            // Eksekusi state saat ini
            $result = $context->execute($data);

            // Jika berhasil dan ada adjustable_id dalam response, simpan untuk state berikutnya
            if ($result->isSuccess() && $result->getData()) {
                $responseData = $result->getData();

                // Simpan adjustable_id jika ada di response
                if (isset($responseData['adjustable_id'])) {
                    $data->setAdjustableId($responseData['adjustable_id']);
                }

                // Simpan schedule_id dan detail_schedule_id jika ada
                if (isset($responseData['schedule_id'])) {
                    $data->setScheduleId($responseData['schedule_id']);
                }
                if (isset($responseData['detail_schedule_id'])) {
                    $data->setDetailScheduleId($responseData['detail_schedule_id']);
                }
            }

            return $result;
        } catch (\Throwable $th) {
            captureException($th);
            LogConsole::info($th);
            return new ActionResult(false, "Something went wrong.", null);
        }
    }



    private function getStateForStage(mixed $stage): AttendanceState
    {
        switch ($stage) {
            case AttendanceStatus::AttendanceIn->value:
                return new AttendanceInState(
                    $this->attendanceRepository,
                    $this->scheduleRepository,
                    $this->shiftRepository,
                    $this->detailScheduleRepository,
                    $this->userRepository,
                    $this->officeRepository,
                    $this->whatsappService
                );
            case AttendanceStatus::StartBreak->value:
                return new AttendanceBreakStartState(
                    $this->attendanceRepository,
                    $this->detailScheduleRepository
                );
            case AttendanceStatus::EndBreak->value:
                return new AttendanceBreakBackState(
                    $this->attendanceRepository
                );
            case AttendanceStatus::StartPermit->value:
                return new AttendancePermitStartState(
                    $this->attendanceRepository,
                    $this->whatsappService
                );
            case AttendanceStatus::EndPermit->value:
                return new AttendancePermitBackState(
                    $this->attendanceRepository,
                    $this->detailScheduleRepository,
                    $this->adjustableAttdRepository,
                    $this->whatsappService
                );
            case AttendanceStatus::AttendanceOut->value:
                return new AttendanceOutState(
                    $this->userRepository,
                    $this->attendanceRepository,
                    $this->detailScheduleRepository,
                    $this->locationService,
                );
            case AttendanceStatus::AdjustableIn->value:
                return new AdjustableInState(
                    $this->userRepository,
                    $this->shiftRepository,
                    $this->scheduleRepository,
                    $this->detailScheduleRepository,
                    $this->adjustableAttdRepository,
                    $this->locationService,
                    $this->whatsappService
                );
                // ADD THESE MISSING MAPPINGS:
            case AttendanceStatus::StartBreakAdjustable->value:
                return new AdjustableBreakStartState($this->adjustableAttdRepository);
            case AttendanceStatus::EndBreakAdjustable->value:
                return new AdjustableBreakBackState($this->adjustableAttdRepository);
            case AttendanceStatus::AdjustableOut->value:
                return new AdjustableOutState(
                    $this->adjustableAttdRepository,
                    $this->attendanceRepository,
                    $this->locationService,
                    $this->whatsappService
                );
            case AttendanceStatus::AllDone:
                return new AllDoneState();
            default:
                throw new \Exception("Unknown stage: " . $stage);
        }
    }

    public function getTotalInternAbsence(Request $request): ActionResult
    {
        try {
            $dateTarget = $request->input('date', Carbon::now()->format('Y-m-d'));
            $totalAttendance =  $this->detailScheduleRepository->countAttendance($dateTarget, 2);
            $totalAbsence =  $this->detailScheduleRepository->countAttendance($dateTarget, 3);
            $totalPermit =  $this->detailScheduleRepository->countAttendance($dateTarget, 5);

            $data = [
                "attendanceTotal" => $totalAttendance ?? 0,
                "absenceTotal" => $totalPermit ?? 0,
                "permitTotal" => $totalAbsence ?? 0
            ];

            return new ActionResult(true, "success got count", $data);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "something weird", null);
        }
    }

    public function getTotalInternAbsenceHome(): ActionResult
    {
        try {
            $dateNow = DateNow::getCurrentDateYMD();
            $totalAttendance =  $this->detailScheduleRepository->countAttendance($dateNow, 2);
            $totalAbsence =  $this->detailScheduleRepository->countAttendance($dateNow, 3);
            $totalPermit =  $this->detailScheduleRepository->countAttendance($dateNow, 5);
            $totalAttendanceOffice1 = Detailschedule::where('date', $dateNow)
                ->where('office_id', 1)
                ->where('attd_status_id', 2)
                ->count();
            $totalAttendanceOffice2 = Detailschedule::where('date', $dateNow)
                ->where('office_id', 2)
                ->where('attd_status_id', 2)
                ->count();
            $totalAttendanceOffice3 = Detailschedule::where('date', $dateNow)
                ->where('office_id', 3)
                ->where('attd_status_id', 2)
                ->count();


            $data = [
                "attendanceTotal" => $totalAttendance ?? 0,
                "absenceTotal" => $totalPermit ?? 0,
                "permitTotal" => $totalAbsence ?? 0,
                "totalAttendanceOffice1" => $totalAttendanceOffice1 ?? 0,
                "totalAttendanceOffice2" => $totalAttendanceOffice2 ?? 0,
                "totalAttendanceOffice3" => $totalAttendanceOffice3 ?? 0
            ];

            return new ActionResult(true, "success got count", $data);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "something weird", null);
        }
    }

    public function getInternAttendance(Request $request): ActionResult
    {
        try {
            $internName = $request->query("name");
            $dateTarget = $request->query("date") ?? DateNow::getCurrentDateYMD();
            $perPage = $request->query("per_page", 15); // Diubah default ke 15 sesuai frontend

            // --- PERBAIKAN UTAMA: Ambil 'page', bukan 'current_page' ---
            // Library paginasi Laravel secara default menggunakan parameter 'page'.
            $currentPage = $request->query("page", 1);

            $result = null;

            $shiftId = $request->query("shift_id");
            $officeId = $request->query("office_id");
            $statusId = $request->query("status_id");

            // --- Pastikan $currentPage diteruskan ke semua panggilan repository ---

            if ($shiftId || $officeId || $statusId) {
                $requestData = [
                    'date' => $dateTarget,
                    'shift_id' => $shiftId,
                    'office_id' => $officeId,
                    'status_id' => $statusId
                ];
                // Pastikan method findByCriteria di repository Anda menerima $currentPage
                $result = $this->detailScheduleRepository->findByCriteria($requestData, $perPage, $currentPage);
            } elseif (!$result && $internName && $dateTarget) {
                $result = $this->detailScheduleRepository->findByNameAndDate($internName, $dateTarget, $perPage, $currentPage);
            } elseif (!$result && $internName) {
                $result = $this->detailScheduleRepository->findByName($internName, $perPage, $currentPage);
            } elseif (!$result && $dateTarget) {
                $result = $this->detailScheduleRepository->findByDate($dateTarget, $perPage, $currentPage);
            } elseif (!$result) {
                $datenow = DateNow::getCurrentDateYMD();
                $result = $this->detailScheduleRepository->findByDate($datenow, $perPage, $currentPage);
            }

            if (!$result || $result->isEmpty()) {
                // Mengembalikan struktur data yang konsisten bahkan saat kosong
                return new ActionResult(true, "Attendance data is empty", [
                    'data' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => $perPage,
                        'total' => 0,
                        'last_page' => 1,
                    ]
                ]);
            }

            $finalData = [];

            foreach ($result->items() as $item) {
                $modifiedData = [];

                $modifiedData['name'] = $item->schedule->intern->user->profile->full_name;
                $modifiedData['intern_id'] = $item->schedule->intern->id;
                $modifiedData['detail_schedule_id'] = $item->id;
                $modifiedData['is_notification_sent'] = $item->is_notification_sent;
                $modifiedData['ischange_schedule'] = $item->isChangeSchedule;
                $modifiedData['permit_data'] = $item->permitReason;
                $modifiedData['attd_status'] = $item->attdStatus;
                $modifiedData['shift_id'] = $item->shift->id ?? 0;

                $isExcusedLeave = \App\Helper\TimeHelper::isApprovedExcusedLeave($item);
                if ($item->attendance || $isExcusedLeave) {
                    $attendance = $item->attendance;
                    $shift = $item->shift;
                    $workData = $shift ? \App\Helper\TimeHelper::calculateDailyWorkHours($attendance, $shift, $item) : null;
                    $attendanceData = [
                        "id" => $attendance->id ?? null,
                        "date" => $attendance ? date('d-m-Y', strtotime($attendance->date)) : ($item->date ? date('d-m-Y', strtotime($item->date)) : null),
                        "start_time" => $attendance->start_time ?? null,
                        "break_time" => $attendance->break_time ?? null,
                        "back_time" => $attendance->back_time ?? null,
                        "permit_start" => $attendance->permit_start ?? null,
                        "permit_back" => $attendance->permit_back ?? null,
                        "end_time" => $attendance->end_time ?? null,
                        "total_min" => $workData ? $workData['actual_work_minutes'] : ($attendance->total_min ?? 0),
                        "total_break_min" => $attendance->total_break_min ?? 0,
                        "total_permit_min" => $attendance->total_permit_min ?? 0,
                        "start_time_message" => $attendance->start_time_message ?? null,
                        "break_time_message" => $attendance->break_time_message ?? null,
                        "back_time_message" => $attendance->back_time_message ?? null,
                        "permit_start_message" => $attendance->permit_start_message ?? null,
                        "permit_back_message" => $attendance->permit_back_message ?? null,
                        "end_time_message" => $attendance->end_time_message ?? null,
                        "latitude_start" => $attendance->latitude_start ?? null,
                        "longitude_start" => $attendance->longitude_start ?? null,
                        "latitude_end" => $attendance->latitude_end ?? null,
                        "longitude_end" => $attendance->longitude_end ?? null,
                        "total_time" => $workData ? $workData['actual_work_formatted'] : '',
                        "target_time" => $workData ? [
                            "condition" => $workData['diff_minutes'] >= 0,
                            "value" => $workData['diff_formatted'],
                        ] : ["condition" => false, "value" => ''],
                        "total_min_format" => $workData ? $workData['actual_work_formatted'] : '',
                        "target_time_format" => $workData ? $workData['diff_formatted'] : '',
                        "shift_target_formatted" => $workData ? $workData['shift_target_formatted'] : '',
                    ];
                    $modifiedData['attendance'] = $attendanceData;
                }

                if ($item->adjustableAttendance) {
                    $adjustableResponse = [];
                    foreach ($item->adjustableAttendance as $key => $value) {
                        $adjustableData = [
                            "id"                    => $value->id,
                            "date"                  => date('d-m-Y', strtotime($value->date)),
                            "start_time"            => $value->start_time,
                            "break_time"            => $value->break_time,
                            "back_time"             => $value->back_time,
                            "permit_start"          => $value->permit_start,
                            "permit_back"           => $value->permit_back,
                            "end_time"              => $value->end_time,
                            "total_min"             => $value->total_min,
                            "total_break_min"       => $value->total_break_min,
                            "start_time_message"    => $value->start_time_message,
                            "break_time_message"    => $value->break_time_message,
                            "back_time_message"     => $value->back_time_message,
                            "permit_start_message"  => $value->permit_start_message,
                            "permit_back_message"   => $value->permit_back_message,
                            "end_time_message"      => $value->end_time_message,
                            "latitude_start"        => $value->latitude_start,
                            "longitude_start"       => $value->longitude_start,
                            "latitude_end"          => $value->latitude_end,
                            "longitude_end"         => $value->longitude_end,
                            "is_approved"           => $value->is_approved->value,
                            "total_time"            => DateNow::setToHour($value->total_min - $value->total_break_min),
                            "target_time"           => [
                                "condition"             => true,
                                "value"                 => DateNow::setToHour(0),
                            ],
                        ];
                        $adjustableResponse[] = $adjustableData;
                    }
                    $modifiedData['adjustableAttendance'] = $adjustableResponse;
                }

                $modifiedData['log_activity'] = $item->logActivity;
                array_push($finalData, $modifiedData);
            }

            $paginationData = [
                'current_page' => $result->currentPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
                'last_page' => $result->lastPage(),
            ];

            $finalValue =  [
                'data' => $finalData ?? [],
                'pagination' => $paginationData ?? [],
            ];

            return new ActionResult(true, "Successfully retrieved data", $finalValue);
        } catch (\Throwable $th) {
            captureException($th);
            LogConsole::info($th);
            return new ActionResult(false, "An error occurred", null);
        }
    }

    public function shortageOfAttendanceHours(int $intern_id)
    {
        try {

            $dateNow = DateNow::getCurrentDateYMD();
            $totalAttendance = $this->detailScheduleRepository->findByInternId($intern_id);
            $schedules = $this->scheduleRepository->findByInternId($intern_id);

            $totalWorkTime = 0;
            $admissionTotal = 0;
            $workTimeTarget = 0;
            $workTimeTotalAfterDc = 0;
            $remainingTime = 0;
            $changeTime = 0;
            $remainingTimeAfterCalculate = 0;


            foreach ($totalAttendance as $day) {
                if ($day->date > $dateNow) continue;
                if (is_null($day->attendance->end_time)) continue;

                $defaultAttendance = $day->attendance;
                $adjustableAttendance = $day->adjustableAttendance;
                $currentDay = 0;

                if ($defaultAttendance) {
                    $currentDay = $defaultAttendance->total_min;
                    $breakTime = $defaultAttendance->total_break_min;

                    $currentDay -= $breakTime;
                }

                if ($adjustableAttendance) {
                    foreach ($adjustableAttendance as $key => $value) {
                        $currentDayAdjutable = $value->total_min;
                        $breakTimeAdjustable = $value->total_break_min;

                        if ($breakTimeAdjustable) $currentDayAdjutable -= $breakTimeAdjustable;
                        $changeTime += $currentDayAdjutable;
                    }
                }

                if ($day->attd_status_id == 2) $admissionTotal++;

                $totalInMinute = $day->shift->total_time_in_minute;
                $workTimeTarget += $totalInMinute;

                $totalWorkTime += $currentDay;
            }
            $remainingCalculation = $workTimeTarget - $totalWorkTime;
            $remainingTime = $remainingCalculation < 0 ? 0 : $remainingCalculation;

            $remainingAfterAc = $remainingTime - $changeTime;
            $remainingTimeAfterCalculate = $remainingAfterAc < 0 ? 0 : $remainingAfterAc;


            if ($schedules) {
                $discountTime = $this->discountTimeRepository->findByScheduleId($schedules->id);
                if ($discountTime && $discountTime->duration) {
                    $remainingAfterACD = $remainingTimeAfterCalculate - $discountTime->duration;
                    $remainingTimeAfterCalculate = $remainingAfterACD < 0 ? 0 : $remainingAfterACD;
                }
            }


            $lackInString = DateNow::setToHour(abs($remainingTimeAfterCalculate));
            $response = ["lack" => [
                "isLess" => $remainingTimeAfterCalculate <= 0,
                "value" => $lackInString,
            ]];

            return new ActionResult(true, "success sum all data", $response);
        } catch (\Throwable $th) {
            captureException($th);
            LogConsole::info($th);
            return new ActionResult(false, "", null);
        }
    }


    public function attendanceReport(Request $request)
    {
        DB::beginTransaction();

        try {
            $today = Carbon::now(); // Menggunakan Carbon untuk tanggal hari ini
            $currentPage = (int) $request->query('page', 1);
            $pagination = (int) $request->query('perPage', 10);
            $startDate = $request->query('startDate');
            $endDate = $request->query('endDate');
            $internName = $request->query('internName');

            $startDate = $startDate ? Carbon::parse($startDate)->toDateString() : $today->startOfDay()->toDateString();
            $endDate = $endDate ? Carbon::parse($endDate)->toDateString() : $today->endOfDay()->toDateString();

            $internTotal = $this->internRepository->count();
            $totalPage = ceil($internTotal / $pagination);
            $interns = Intern::with(['schedules.detailSchedules'])
                ->when($internName, function ($query) use ($internName) {
                    $query->whereHas('user.profile', function ($query) use ($internName) {
                        $query->where('full_name', 'LIKE', '%' . $internName . '%');
                    });
                })
                ->paginate($pagination, ['*'], 'page', $currentPage);

            $internValue = [];

            foreach ($interns as $intern) {
                $attendanceData = $intern->schedules()
                    ->whereHas('detailSchedules', function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('date', [$startDate, $endDate]); // Filter data sesuai range
                    })
                    ->selectRaw('
                    SUM(CASE WHEN detail_schedules.attd_status_id = 5 AND date BETWEEN ? AND ? THEN 1 ELSE 0 END) as absence,
                    SUM(CASE WHEN (detail_schedules.attd_status_id = 2 OR detail_schedules.attd_status_id = 4) AND date BETWEEN ? AND ? THEN 1 ELSE 0 END) as submitted,
                    SUM(CASE WHEN detail_schedules.attd_status_id = 3 AND date BETWEEN ? AND ? THEN 1 ELSE 0 END) as permits
                ', [$startDate, $endDate, $startDate, $endDate, $startDate, $endDate])
                    ->join('detail_schedules', 'schedules.id', '=', 'detail_schedules.schedule_id')
                    ->first();

                $internDetails = [
                    'id' => $intern->id,
                    'name' => $intern->user->profile->full_name,
                    'nip' => $intern->user->profile->NIP,
                    'submitted' => $attendanceData->submitted ?? 0,
                    'absence' => $attendanceData->absence ?? 0,
                    'permits' => $attendanceData->permits ?? 0,
                ];

                array_push($internValue, $internDetails);
            }

            $baseUrl = url('/attendance-report');

            $previousPageUrl = $currentPage > 1 ? $baseUrl . '?page=' . ($currentPage - 1) : null;
            $nextPageUrl = $currentPage < $totalPage ? $baseUrl . '?page=' . ($currentPage + 1) : null;

            $responseData = [
                "reportData" => $internValue,
                "totalPages" => $totalPage,
                "previous_page" => $previousPageUrl,
                "next_page" => $nextPageUrl,
                'currentPage' => $currentPage,
            ];

            DB::commit();

            return new ActionResult(true, "success get all data", $responseData);
        } catch (\Throwable $th) {
            DB::rollBack();
            captureException($th);
            return new ActionResult(false, "failed get all data", null);
        }
    }


    public function detailAttendanceReport(Request $request): ActionResult
    {
        try {
            $internId = $request->query('intern_id');
            $statusId = $request->query('status_id');
            $requestPage = $request->query('page');

            Log::info('detailAttendanceReport started', [
                'intern_id' => $internId,
                'status_id' => $statusId,
                'page' => $requestPage
            ]);

            // Debug point 1: Check if scheduleRepository exists
            if (!$this->scheduleRepository) {
                Log::error('scheduleRepository is null');
                return new ActionResult(false, "scheduleRepository not initialized", null);
            }

            // Debug point 2: Try to find schedule
            Log::info('Calling scheduleRepository->findByInternId', ['intern_id' => $internId]);
            $resultSchedule = $this->scheduleRepository->findByInternId($internId);
            Log::info('Schedule found', ['schedule' => $resultSchedule ? 'exists' : 'null']);

            $pageSize = (int) $request->query('per_page', 10);
            $page = (int) $request->query('page', $requestPage ?? 1);

            if (!$resultSchedule) {
                $data = [
                    'data' => null,
                    'pagination' => null,
                ];

                return new ActionResult(true, "still dont have any attendance history", $data);
            }

            // Debug point 3: Check detailSchedules
            Log::info('Getting detailSchedules');
            $filteredSchedules = $resultSchedule->detailSchedules;
            Log::info('DetailSchedules count', ['count' => $filteredSchedules->count()]);

            if ($statusId) $filteredSchedules = $filteredSchedules->where('attd_status_id', $statusId);

            Log::info('Processing pagination');
            $paginatedDetailSchedules = $filteredSchedules->sortBy("date")->forPage($page, $pageSize);

            // Debug point 4: Check data mapping
            Log::info('Starting data mapping');
            $paginatedData = $paginatedDetailSchedules->map(function ($detailSchedule) {
                Log::info('Processing detail schedule', ['id' => $detailSchedule->id]);
                $ds_data = $detailSchedule->toArray();

                $date = new DateTime($ds_data["date"]);
                $ds_data["date"] = $date->format('d-m-Y');

                unset($ds_data['attendance']);
                unset($ds_data['log_activity_id']);
                unset($ds_data['attendance_id']);
                unset($ds_data['start_time']);
                unset($ds_data['end_time']);
                unset($ds_data['attd_status_id']);

                $dataAttendance = $detailSchedule->attendance ? $detailSchedule->attendance->toArray() : [];
                $shift = $detailSchedule->shift;
                $isExcused = \App\Helper\TimeHelper::isApprovedExcusedLeave($detailSchedule);

                if ($isExcused && $shift) {
                    $workData = \App\Helper\TimeHelper::calculateDailyWorkHours((object)$dataAttendance, $shift, $detailSchedule);
                    $dataAttendance['start_time'] = $dataAttendance['start_time'] ?? null;
                    $dataAttendance['end_time'] = $dataAttendance['end_time'] ?? null;
                    $dataAttendance['break_time'] = $dataAttendance['break_time'] ?? null;
                    $dataAttendance['back_time'] = $dataAttendance['back_time'] ?? null;
                    $dataAttendance['total_min'] = $workData['actual_work_minutes'];
                    $dataAttendance['total_min_format'] = $workData['actual_work_formatted'];
                    $dataAttendance['target_time'] = $workData['diff_minutes'];
                    $dataAttendance['target_time_format'] = $workData['diff_formatted'];
                    $dataAttendance['shift_target_formatted'] = $workData['shift_target_formatted'];
                } else if (!empty($dataAttendance) && $shift) {
                    // Gunakan TimeHelper untuk perhitungan jam kerja aktual dan selisih
                    $workData = \App\Helper\TimeHelper::calculateDailyWorkHours((object)$dataAttendance, $shift, $detailSchedule);
                    $dataAttendance['total_min_format'] = $workData['actual_work_formatted'];
                    $dataAttendance['target_time'] = $workData['diff_minutes'];
                    $dataAttendance['target_time_format'] = $workData['diff_formatted'];
                    $dataAttendance['shift_target_formatted'] = $workData['shift_target_formatted'];
                } else if (!empty($dataAttendance) && $dataAttendance["start_time"] != null && $shift) {
                    $totaltime = \App\Helper\TimeHelper::diffInMinutes($dataAttendance["start_time"], now());
                    $dataAttendance['total_min'] = $totaltime;
                    $dataAttendance['total_min_format'] = \App\Helper\TimeHelper::formatMinutesToHours($totaltime);
                    $target = $shift->total_time_in_minute - $totaltime; // Define $target here
                    $dataAttendance['target_time'] = $target;
                    $dataAttendance['target_time_format'] = \App\Helper\TimeHelper::formatDifference($target);
                    $dataAttendance['shift_target_formatted'] = \App\Helper\TimeHelper::formatMinutesToHours($shift->total_time_in_minute);
                } else if ($shift) {
                    $dataAttendance['total_min'] = 0;
                    $dataAttendance['total_min_format'] = \App\Helper\TimeHelper::formatMinutesToHours(0);
                    $target = $shift->total_time_in_minute; // Define $target here too
                    $dataAttendance['target_time'] = $target;
                    $dataAttendance['target_time_format'] = \App\Helper\TimeHelper::formatDifference($target);
                    $dataAttendance['shift_target_formatted'] = \App\Helper\TimeHelper::formatMinutesToHours($shift->total_time_in_minute);
                } else {
                    // Handle case when no shift exists
                    $dataAttendance['total_min'] = 0;
                    $dataAttendance['total_min_format'] = \App\Helper\TimeHelper::formatMinutesToHours(0);
                    $dataAttendance['target_time'] = 0;
                    $dataAttendance['target_time_format'] = \App\Helper\TimeHelper::formatDifference(0);
                    $dataAttendance['shift_target_formatted'] = '00:00';
                }

                if (!empty($detailSchedule->adjustableAttendance)) {
                    $adjustableAttendance = $detailSchedule->adjustableAttendance->toArray();
                    $filteredData = array_filter($adjustableAttendance, function ($key) {
                        return is_numeric($key);
                    }, ARRAY_FILTER_USE_KEY);
                    $result = array_map(function ($item) use ($shift) {
                        $workData = $shift ? \App\Helper\TimeHelper::calculateDailyWorkHours((object)$item, $shift) : null;
                        $item['total_min_format'] = $workData ? $workData['actual_work_formatted'] : \App\Helper\TimeHelper::formatMinutesToHours($item['total_min'] ?? 0);
                        $item['target_time'] = $workData ? $workData['diff_minutes'] : 0;
                        $item['target_time_format'] = $workData ? $workData['diff_formatted'] : \App\Helper\TimeHelper::formatDifference(0);
                        $item['shift_target_formatted'] = $workData ? $workData['shift_target_formatted'] : ($shift ? \App\Helper\TimeHelper::formatMinutesToHours($shift->total_time_in_minute) : '');
                        return $item;
                    }, $filteredData);
                    $adjustableAttendance = array_values($result);
                } else {
                    $adjustableAttendance = [];
                }

                return [
                    'schedule' => $ds_data,
                    'attendance' => $dataAttendance,
                    'adjustableAttendance' => $adjustableAttendance,
                    'log_activity' => $detailSchedule->logActivity,
                    'attd_status' => $detailSchedule->attdStatus,
                    "permit_data" => $detailSchedule->permitReason
                ];
            })->toArray();

            $paginationData = [
                'current_page' => $page,
                'per_page' => $pageSize,
                'total' => $filteredSchedules->count(),
                'last_page' => ceil($filteredSchedules->count() / $pageSize),
            ];

            $data = [
                'data' => $paginatedData,
                'pagination' => $paginationData,
            ];


            return new ActionResult(true, "success retrieve data", $data);
        } catch (\Throwable $th) {
            // Enhanced error logging
            Log::error('detailAttendanceReport exception', [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
                'intern_id' => $request->query('intern_id')
            ]);

            captureException($th);
            LogConsole::info($th);
            return new ActionResult(false, "failed retrieve data: " . $th->getMessage(), null);
        }
    }

    public function internTarget(int $intern_id)
    {
        try {
            $dateNow = new DateTime();
            $totalAttendance = $this->detailScheduleRepository->findByInternId($intern_id);
            $schedules = $this->scheduleRepository->findByInternId($intern_id);



            // Jika tidak ada data, return response kosong
            if ($totalAttendance->isEmpty()) {
                Log::warning("No attendance data found for intern_id: {$intern_id}");
                return new ActionResult(true, "No data found", [
                    "total_work_time" => "0j 0m",
                    "admission_total" => "0 Hari",
                    "target_time" => "0j 0m",
                    "time_target_remaining" => "0j 0m",
                    "change_time_total" => "0j 0m",
                    "remaing_time_after_calculate" => "0j 0m",
                    "remaing_time_after_discount" => "0j 0m",
                    "discount_time" => 0,
                    "admit_total" => 0,
                    "back_total" => 0,
                    "break_start_total" => 0,
                    "break_back_total" => 0,
                    "permit_total" => 0,
                    "permit_back_total" => 0,
                    "shifts_work" => [],
                    "start_period" => "",
                    "end_period" => "",
                    "adjustable_start_total" => 0,
                    "adjustable_end_total" => 0,
                    "adjustable_break_in" => 0,
                    "adjustable_break_out" => 0,
                    "adjustable_total" => 0,
                    "accepted_adjustable_total" => 0,
                    "time_target_remaining_seconds" => 0,
                    "remaing_time_after_calculate_seconds" => 0,
                    "remaing_time_after_discount_seconds" => 0
                ]);
            }

            // Jika tidak ada schedule, return response kosong
            if (!$schedules) {
                Log::warning("No schedule found for intern_id: {$intern_id}");
                return new ActionResult(true, "No schedule found", [
                    "total_work_time" => "0j 0m",
                    "admission_total" => "0 Hari",
                    "target_time" => "0j 0m",
                    "time_target_remaining" => "0j 0m",
                    "change_time_total" => "0j 0m",
                    "remaing_time_after_calculate" => "0j 0m",
                    "remaing_time_after_discount" => "0j 0m",
                    "discount_time" => 0,
                    "admit_total" => 0,
                    "back_total" => 0,
                    "break_start_total" => 0,
                    "break_back_total" => 0,
                    "permit_total" => 0,
                    "permit_back_total" => 0,
                    "shifts_work" => [],
                    "start_period" => "",
                    "end_period" => "",
                    "adjustable_start_total" => 0,
                    "adjustable_end_total" => 0,
                    "adjustable_break_in" => 0,
                    "adjustable_break_out" => 0,
                    "adjustable_total" => 0,
                    "accepted_adjustable_total" => 0,
                    "time_target_remaining_seconds" => 0,
                    "remaing_time_after_calculate_seconds" => 0,
                    "remaing_time_after_discount_seconds" => 0
                ]);
            }



            $totalWorkTime = 0;
            $admissionTotal = 0;
            $workTimeTarget = 0;
            $workTimeTotalAfterDc = 0;
            $remainingTime = 0;
            $changeTime = 0;
            $remainingTimeAfterCalculate = 0;

            $admitTotal = 0;
            $backTotal = 0;
            $breakStartTotal = 0;
            $breakBackTotal = 0;
            $permitTotal = 0;
            $permitBackTotal = 0;

            $ajdustableInTotal = 0;
            $ajdustableOutTotal = 0;
            $adjustableBreakIn = 0;
            $adjsutableBreakOut = 0;

            $AdjustableTotal = 0;
            $AcceptedAdjustableTotal = 0;

            $targetTotalTime = 0;

            $shifts = [];

            foreach ($totalAttendance as $day) {
                $shift = $day->shift;
                if (!$shift) {
                    continue;
                }

                $totalInMinute =  $shift->total_time_in_minute;
                $targetTotalTime += $totalInMinute;

                // Skip hari yang belum datang
                if ($day->date > $dateNow) continue;

                if (!in_array($shift, $shifts)) $shifts[] = $shift;
                $defaultAttendance = $day->attendance;
                $adjustableAttendance = $day->adjustableAttendance;

                $currentDay = 0; // Inisialisasi currentDay

                if ($defaultAttendance) {
                    // Hitung total jam kerja dari jam masuk sampai jam pulang
                    $startTime = $defaultAttendance->start_time;
                    $endTime = $defaultAttendance->end_time;
                    $breakTime = $defaultAttendance->total_break_min ?? 0;

                    // Jika ada jam masuk dan jam pulang, hitung selisihnya
                    if ($startTime && $endTime) {
                        $startTimestamp = strtotime($startTime);
                        $endTimestamp = strtotime($endTime);

                        if ($startTimestamp && $endTimestamp) {
                            // Hitung selisih dalam menit
                            $totalMinutes = ($endTimestamp - $startTimestamp) / 60;
                            $currentDay = round($totalMinutes);

                            // Kurangi waktu istirahat
                            $currentDay -= $breakTime;

                            // Pastikan tidak negatif
                            $currentDay = max(0, $currentDay);
                        } else {
                            $currentDay = 0;
                        }
                    } else {
                        // Jika tidak ada jam masuk atau pulang, gunakan total_min yang sudah ada
                        $currentDay = $defaultAttendance->total_min ?? 0;
                        $currentDay -= $breakTime;
                        $currentDay = max(0, $currentDay);
                    }

                    if (!is_null($defaultAttendance->start_time)) $admitTotal++;
                    if (!is_null($defaultAttendance->end_time)) $backTotal++;
                    if (!is_null($defaultAttendance->break_time)) $breakStartTotal++;
                    if (!is_null($defaultAttendance->back_time)) $breakBackTotal++;
                    if (!is_null($defaultAttendance->permit_start)) $permitTotal++;
                    if (!is_null($defaultAttendance->permit_back)) $permitBackTotal++;
                } else {
                    // No attendance found for this day
                }

                if ($adjustableAttendance) {
                    foreach ($adjustableAttendance as $adjst) {
                        $AdjustableTotal++;

                        if ($adjst->is_approved->value !== 1) continue; // will skipp except 1 (approved)

                        $AcceptedAdjustableTotal++;

                        // Hitung total jam kerja dari jam masuk sampai jam pulang untuk adjustable
                        $adjStartTime = $adjst->start_time;
                        $adjEndTime = $adjst->end_time;
                        $breakTimeAdjustable = $adjst->total_break_min ?? 0;

                        if ($adjStartTime && $adjEndTime) {
                            $adjStartTimestamp = strtotime($adjStartTime);
                            $adjEndTimestamp = strtotime($adjEndTime);

                            if ($adjStartTimestamp && $adjEndTimestamp) {
                                // Hitung selisih dalam menit
                                $adjTotalMinutes = ($adjEndTimestamp - $adjStartTimestamp) / 60;
                                $currentDayAdjutable = round($adjTotalMinutes);

                                // Kurangi waktu istirahat
                                $currentDayAdjutable -= $breakTimeAdjustable;

                                // Pastikan tidak negatif
                                $currentDayAdjutable = max(0, $currentDayAdjutable);
                            } else {
                                $currentDayAdjutable = 0;
                            }
                        } else {
                            // Jika tidak ada jam masuk atau pulang, gunakan total_min yang sudah ada
                            $currentDayAdjutable = $adjst->total_min ?? 0;
                            $currentDayAdjutable -= $breakTimeAdjustable;
                            $currentDayAdjutable = max(0, $currentDayAdjutable);
                        }

                        if (!is_null($adjst->start_time)) $ajdustableInTotal++;
                        if (!is_null($adjst->end_time)) $ajdustableOutTotal++;
                        if (!is_null($adjst->break_time)) $adjustableBreakIn++;
                        if (!is_null($adjst->back_time)) $adjsutableBreakOut++;

                        $changeTime += $currentDayAdjutable;
                    }
                }

                if ($day->attd_status_id == 2) $admissionTotal++;

                $totalInMinute = (int) ($day->shift->total_time_in_minute ?? 0);
                if ($totalInMinute <= 0 && $day->shift && $day->shift->start_time && $day->shift->end_time && $day->shift->start_time !== '00:00:00') {
                    $totalInMinute = max(0, \Carbon\Carbon::parse($day->shift->end_time)->diffInMinutes(\Carbon\Carbon::parse($day->shift->start_time)) - ($day->shift->break_time_in_minute ?? 0));
                }
                $workTimeTarget += $totalInMinute;

                // Jika izin disetujui / bebas ganti jam (Lunas / Izin Sakit di-ACC),
                // durasi shift dianggap terpenuhi sehingga pemagang tidak berhutang jam kerja.
                if (\App\Helper\TimeHelper::isApprovedExcusedLeave($day)) {
                    $totalWorkTime += $totalInMinute;
                } else {
                    $totalWorkTime += $currentDay;
                }
            }

            $remainingDecrement = $workTimeTarget - $totalWorkTime;
            $remainingTime = $remainingDecrement < 0 ? 0 : $remainingDecrement;

            $remainingDecrementAC = $remainingTime - $changeTime;
            $remainingTimeAfterCalculate = $remainingDecrementAC < 0 ? 0 : $remainingDecrementAC;

            $workTimeTotalAfterDc = 0;
            $discountTime = $this->discountTimeRepository->findByScheduleId($schedules->id);
            if ($discountTime && $discountTime->duration) {
                $workTimeDecrement = $remainingTimeAfterCalculate - $discountTime->duration;
                $workTimeTotalAfterDc = $workTimeDecrement < 0 ? 0 : $workTimeDecrement;
            }



            $response = [
                "total_work_time" => DateNow::setToHour($totalWorkTime),
                "admission_total" => $admissionTotal . " Hari",
                "target_time" => DateNow::setToHour($workTimeTarget),
                "time_target_remaining" => DateNow::setToHour($remainingTime),
                "change_time_total" => DateNow::setToHour($changeTime),
                "remaing_time_after_calculate" => DateNow::setToHour($remainingTimeAfterCalculate),
                "remaing_time_after_discount" => DateNow::setToHour($workTimeTotalAfterDc),
                "discount_time" => $discountTime->duration ?? 0,
                "admit_total" => $admitTotal,
                "back_total" => $backTotal,
                "break_start_total" => $breakStartTotal,
                "break_back_total" => $breakBackTotal,
                "permit_total" => $permitTotal,
                "permit_back_total" => $permitBackTotal,
                "shifts_work" => $shifts,
                "start_period" => (new DateTime($schedules->start_period))->format('d-m-Y'),
                "end_period" => (new DateTime($schedules->end_period))->format('d-m-Y'),
                "adjustable_start_total" => $ajdustableInTotal,
                "adjustable_end_total" => $ajdustableOutTotal,
                "adjustable_break_in" => $adjustableBreakIn,
                "adjustable_break_out" => $adjsutableBreakOut,
                "adjustable_total" => $AdjustableTotal,
                "accepted_adjustable_total" => $AcceptedAdjustableTotal,
                // Tambahan field hutang jam dalam detik
                "time_target_remaining_seconds" => DateNow::hourStringToSeconds(DateNow::setToHour($remainingTime)),
                "remaing_time_after_calculate_seconds" => DateNow::hourStringToSeconds(DateNow::setToHour($remainingTimeAfterCalculate)),
                "remaing_time_after_discount_seconds" => DateNow::hourStringToSeconds(DateNow::setToHour($workTimeTotalAfterDc)),
                // Field baru untuk detail perhitungan
                "total_work_time_minutes" => $totalWorkTime,
                "total_work_time_hours" => round($totalWorkTime / 60, 2),
                "average_work_time_per_day" => $admissionTotal > 0 ? round($totalWorkTime / $admissionTotal, 2) : 0,
                "work_time_target_minutes" => $workTimeTarget,
                "work_time_target_hours" => round($workTimeTarget / 60, 2),
                "efficiency_percentage" => $workTimeTarget > 0 ? round(($totalWorkTime / $workTimeTarget) * 100, 2) : 0,
                "daily_target_hours" => $admissionTotal > 0 ? round($workTimeTarget / $admissionTotal / 60, 2) : 0
            ];


            return new ActionResult(true, "success sum all data", $response);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "", null);
        }
    }


    public function setAllAttdStatusByShift(int $status, int $shiftId, string $date)
    {
        try {
            $this->attendanceRepository->updateStatusByShiftandDate($status, $shiftId, $date, now());
        } catch (\Throwable $th) {
            captureException($th);
        }
    }

    // In your attendance service
    public function getAttendanceData(mixed $attendance, mixed $shift)
    {
        if (!$attendance || !$shift) {
            return null;
        }

        $workData = \App\Helper\TimeHelper::calculateDailyWorkHours($attendance, $shift);

        return [
            "total_time" => $workData['actual_work_formatted'],
            "target_time" => [
                "condition" => $workData['diff_minutes'] >= 0,
                "value" => $workData['diff_formatted'],
            ],
            "total_min_format" => $workData['actual_work_formatted'],
            "target_time_format" => $workData['diff_formatted'],
            "shift_target_formatted" => $workData['shift_target_formatted'],
        ];
    }


    public function setToEndTime(string $date, int $shift_id, $end_time)
    {
        try {
            $shift = $this->shiftRepository->getById($shift_id);
            $data = $this->attendanceRepository->getAttendanceStillNotBack($date, $shift_id);
            // LogConsole::info($data);
            foreach ($data as $row) {
                $total_time = DateNow::getDifferentInMinute($row["start_time"], $shift->end_time);
                $updateValue = [
                    "end_time" => $shift->end_time,
                    "total_min" => $total_time,
                    'is_auto_end' => true,
                ];
                if ($row["break_time"] != null && $row["back_time"] == null) {
                    $updateValue['back_time'] = $shift->end_time;
                    $updateValue['total_break_min'] = DateNow::getDifferentInMinute($row["break_time"], $shift->end_time);
                }
                $this->attendanceRepository->update($row["id"], $updateValue);
                LogConsole::info($updateValue);
            }
        } catch (\Throwable $th) {
            LogConsole::info($th);
            captureException($th);
        }
    }

    public function createPermitPresence(StorePermitPresenceRequest $storePermitPresenceRequest): ActionResult
    {
        try {
            $validated = $storePermitPresenceRequest->validated();

            // Ambil DetailSchedule berdasarkan ID
            $detailSchedule = DetailSchedule::find($validated['id-schedule']);
            if (!$detailSchedule) {
                return new ActionResult(false, "DetailSchedule not found", null);
            }

            // Logika Permit Reason
            if ($detailSchedule->permit_reason_id != 0) {
                $permitReason = PermitReason::find($detailSchedule->permit_reason_id);
                if ($permitReason) {
                    $permitReason->update([
                        'description' => $validated['keterangan'],
                        'proof_url' => $validated['link-google-drive'],
                        'permit_category_id' => $validated['kategori-izin'],
                    ]);
                } else {
                    return new ActionResult(false, "PermitReason not found", null);
                }
            } else {
                // Jika permit_reason_id adalah 0, create PermitReason baru
                $permitReason = PermitReason::create([
                    'description' => $validated['keterangan'],
                    'proof_url' => $validated['link-google-drive'],
                    'permit_category_id' => $validated['kategori-izin'],
                ]);

                $detailSchedule->permit_reason_id = $permitReason->id;
            }

            // Update status jadwal
            $detailSchedule->attd_status_id = 3;

            $detailSchedule->isChangeSchedule = $validated['jam-option'];

            $attendance = $detailSchedule->attendance;

            // Logika untuk menentukan total_min berdasarkan jam-option dan id-shift
            if ($validated['jam-option'] == 1) {
                if ($validated['id-shift']) {
                    // Ambil total_time_in_minute dari tabel shift sesuai shift_id
                    $shiftTotalTime = \App\Models\Shift::where('id', $validated['id-shift'])->value('total_time_in_minute');

                    if ($shiftTotalTime !== null) {
                        // Jika shift-total-time ditemukan, kurangi dengan total_min saat ini
                        $totalMin = $shiftTotalTime - ($attendance->total_min ?? 0);

                        // Perbarui attendance total_min jika `is_validated` adalah true
                        if ($validated['is_validated'] ?? false) {
                            $attendance->total_min = $totalMin;
                            $attendance->save();
                        }
                    } else {
                        return new ActionResult(false, "Shift data not found for the given ID", null);
                    }
                } else {
                    return new ActionResult(false, "Shift ID is not provided", null);
                }
            } elseif ($validated['jam-option'] == 2) {
                // Jika jam-option adalah 2, set total_min ke 0
                $totalMin = 0;
                $attendance->total_min = $totalMin;
                $attendance->save();
            } else {
                // Jika jam-option tidak valid, ambil total_min dari attendance atau set ke 0 jika tidak ada
                if ($attendance) {
                    $totalMin = $attendance->total_min ?? 0;
                } else {
                    return new ActionResult(false, "Attendance not found for the given ID", null);
                }
            }

            // Perbarui total_min pada Attendance
            if ($attendance) {
                $attendance->total_min = $totalMin;

                // Simpan perubahan pada Attendance
                $updated = $attendance->save();

                if ($updated) {
                    // Update DetailSchedule
                    $detailSchedule->save();

                    // Mengembalikan hasil sukses
                    return new ActionResult(true, "Permit presence created and attendance updated successfully", $validated);
                } else {
                    return new ActionResult(false, "Failed to update attendance", null);
                }
            } else {
                return new ActionResult(false, "Attendance not found", null);
            }
        } catch (\Throwable $th) {
            captureException($th); // Melacak error
            LogConsole::info($th);
            return new ActionResult(false, "Failed to create permit presence, something went wrong", null);
        }
    }


    public function updatePermitPresence(UpdatePermitPresenceRequest $updatePermitPresenceRequest): ActionResult
    {
        try {
            $validated = $updatePermitPresenceRequest->validated();

            // Cari PermitReason berdasarkan ID
            $permitReason = PermitReason::find($validated['id']);
            if (!$permitReason) {
                return new ActionResult(false, "PermitReason not found", null);
            }

            // Perbarui PermitReason
            $permitReason->update([
                'description' => $validated['keterangan'],
                'proof_url' => $validated['link-google-drive'],
                'permit_category_id' => $validated['kategori-izin'],
            ]);

            // Cari DetailSchedule berdasarkan permit_reason_id
            $detailSchedule = DetailSchedule::where('permit_reason_id', $validated['id'])->first();
            if (!$detailSchedule) {
                return new ActionResult(false, "DetailSchedule not found", null);
            }

            // Perbarui DetailSchedule
            $detailSchedule->update([
                'isChangeSchedule' => $validated['jam-option'],
            ]);

            $attendance = $detailSchedule->attendance;

            // Logika untuk menentukan total_min berdasarkan jam-option dan id-shift
            if ($validated['jam-option'] == 1) {
                if ($validated['id-shift']) {
                    // Ambil total_time_in_minute dari tabel shift sesuai shift_id
                    $shiftTotalTime = \App\Models\Shift::where('id', $validated['id-shift'])->value('total_time_in_minute');

                    if ($shiftTotalTime !== null) {
                        // Jika shift-total-time ditemukan, kurangi dengan total_min saat ini
                        $totalMin = $shiftTotalTime - ($attendance->total_min ?? 0);

                        // Perbarui attendance total_min jika `is_validated` adalah true
                        if ($validated['is_validated'] ?? false) {
                            $attendance->total_min = $totalMin;
                            $attendance->save();
                        }
                    } else {
                        return new ActionResult(false, "Shift data not found for the given ID", null);
                    }
                } else {
                    return new ActionResult(false, "Shift ID is not provided", null);
                }
            } elseif ($validated['jam-option'] == 2) {
                // Jika jam-option adalah 2, set total_min ke 0
                $totalMin = 0;
                $attendance->total_min = $totalMin;
                $attendance->save();
            } else {
                // Jika jam-option tidak valid, ambil total_min dari attendance atau set ke 0 jika tidak ada
                if ($attendance) {
                    $totalMin = $attendance->total_min ?? 0;
                } else {
                    return new ActionResult(false, "Attendance not found for the given ID", null);
                }
            }

            if (!$attendance) {
                return new ActionResult(false, "Attendance not found", null);
            }

            $attendance->total_min = $totalMin;

            $updated = $attendance->save();

            if ($updated) {
                return new ActionResult(true, "Attendance updated successfully", null);
            } else {
                return new ActionResult(false, "Failed to update attendance", null);
            }

            // return new ActionResult(true, "Permit presence and division updated successfully", $validated);
        } catch (\Throwable $th) {
            captureException($th); // Melacak error
            return new ActionResult(false, "Failed to update permit presence and division, something went wrong", null);
        }
    }




    public function updateShift(Request $request): ActionResult
    {
        try {
            $validatedData = $request->validate([
                'scheduleId' => 'required|exists:detail_schedules,id',
                'currentShift' => 'required|numeric',
                'work_type' => 'required|string',
                'approved_change_time' => 'nullable|numeric',
                'back_first' => 'required|numeric',
                'break_first' => 'required|numeric',
                'schedule_type' => 'required|numeric',
            ]);

            $detailSchedule = DetailSchedule::findOrFail($validatedData['scheduleId']);

            $detailSchedule->shift_id = $validatedData['currentShift'];
            $detailSchedule->work_type = $validatedData['work_type'] == 0 ? 'wfo' : 'wfh';
            $detailSchedule->is_change_schedule_approved = $validatedData['approved_change_time'];
            $detailSchedule->isBackFirst = $validatedData["back_first"];
            $detailSchedule->isChangeSchedule = $validatedData["schedule_type"];
            $detailSchedule->is_break_first = $validatedData["break_first"];
            $detailSchedule->save();

            return new ActionResult(true, 'Shift updated successfully!', $detailSchedule);
        } catch (\Throwable $e) {
            captureException($e);
            return new ActionResult(false, 'Failed to update shift', null);
        }
    }


    public function storeNote(Request $request, int $id): ActionResult
    {
        try {
            $validated = $request->validate([
                'attention_message' => 'nullable|string|max:500',
            ]);

            $intern = Intern::where('user_id', $id)->first();

            if ($intern) {
                if (empty($request->attention_message)) {
                    $intern->attention_message = null;
                } else {
                    $intern->attention_message = $request->attention_message;
                }
                $intern->save();
            }

            return new ActionResult(true, "Shift updated successfully!", $intern);
        } catch (\Throwable $e) {
            // Tangkap exception dan log error
            captureException($e);

            return new ActionResult(false, "Failed to update shift", null);
        }
    }

    // public function getAllChangeTime($internId)
    // {

    //     try {
    //         $result = $this->attendanceRepository->getAllChangeTime($internId);
    //         return new ActionResult(true, "success retrive all attendance data", $result);
    //     } catch (\Throwable $th) {
    //         captureException($th);
    //         return new ActionResult(false, "failed retrive attendance data", null);
    //     }
    // }

    public function getCountAutomaticAttendance(string $name): ActionResult
    {
        try {
            $dateNow = Carbon::now();
            $startOfWeek = $dateNow->startOfWeek()->format('Y-m-d');
            $endOfWeek = $dateNow->endOfWeek()->format('Y-m-d');
            $startOfMonth = $dateNow->startOfMonth()->format('Y-m-d');
            $endOfMonth = $dateNow->endOfMonth()->format('Y-m-d');

            $totalInWeek = $this->attendanceRepository->getTotalCount($name, $startOfWeek, $endOfWeek);
            $totalInMonth = $this->attendanceRepository->getTotalCount($name, $startOfMonth, $endOfMonth);
            $totalAll = $this->attendanceRepository->getTotalCount($name);
            $data = [
                "total_in_week" => $totalInWeek ?? 0,
                "total_in_month" => $totalInMonth ?? 0,
                "total_all" => $totalAll ?? 0
            ];

            return new ActionResult(true, "Successfully retrieved data", $data);
        } catch (\Throwable $e) {
            LogConsole::info($e);
            captureException($e);
            return new ActionResult(false, "Failed to retrieve data", null);
        }
    }

    public function getCountAttendanceToday(): ActionResult
    {
        try {
            $dateNow = Carbon::now()->format('Y-m-d');
            $totalInWeek = $this->attendanceRepository->getTotalCount(date_start: $dateNow, date_end: $dateNow);

            return new ActionResult(true, "",  $totalInWeek);
        } catch (\Throwable $e) {
            return new ActionResult(
                false,
                "",
                null
            );
        }
    }

    public function shortAutomaticAttendance($page = 1, $name = null, $date = null): ActionResult
    {

        try {
            if ($name !== null && $date !== null) {
                $result = $this->attendanceRepository->getAutoEndStatusByDateAndName(
                    name: $name,
                    date: $date,
                    currentPage: $page
                );
            } else if ($name !== null && $date === null) {
                $result = $this->attendanceRepository->getAutoEndStatusByName(name: $name, currentPage: $page);
            } else if ($name === null && $date !== null) {
                $result = $this->attendanceRepository->getAutoEndStatusByDate(date: $date, currentPage: $page);
            } else {
                $result = $this->attendanceRepository->getAllAutoEnd(currentPage: $page);
            }

            $data = [];

            foreach ($result as $item) {
                $data[] = [
                    'name' => $item->detailSchedules->schedule->intern->user->profile->full_name ?? "",
                    'date' =>  Carbon::parse($item->date)->format('d/m/y'),
                    'office' => $item->detailSchedules->office->name ?? "",
                    'shift' => $item->detailSchedules->shift->name ?? "",
                    "start_time" => $item->start_time
                ];
            }

            $meta =  [
                "current_page" => $result->currentPage(),
                "total_data" => $result->total(),
                "total_page" => $result->lastPage(),
            ];

            $lastResult = [
                "data" => $data,
                "meta" => $meta,
            ];

            return new ActionResult(true, "success get the intern that presence end automaticaly", $lastResult);
        } catch (\Throwable $e) {
            captureException($e);
            LogConsole::info($e);
            return new ActionResult(true, "success get the intern that presence end automaticaly");
        }
    }

    public function markMissedSchedulesAsAlpha(): void
    {
        try {
            $today = Carbon::today()->toDateString();
            $now = Carbon::now();

            // ID Status:
            // 1 = Dijadwalkan / Terjadwal
            // 5 = Alpha (Tidak Hadir)
            $statusDijadwalkan = 1;
            $statusAlpha = 5;

            // 1. Cari semua jadwal hari ini dan hari-hari sebelumnya yang statusnya masih "Dijadwalkan"
            //    dan belum memiliki record presensi masuk (start_time).
            $missedSchedules = DetailSchedule::whereDate('date', '<=', $today)
                ->where('attd_status_id', $statusDijadwalkan)
                ->where(function ($query) {
                    $query->whereDoesntHave('attendance')
                        ->orWhereHas('attendance', function ($q) {
                            $q->whereNull('start_time')
                                ->orWhere('start_time', '');
                        });
                })
                ->with('shift')
                ->get();

            if ($missedSchedules->isEmpty()) {
                return;
            }

            foreach ($missedSchedules as $schedule) {
                $scheduleDate = Carbon::parse($schedule->date)->toDateString();

                // Jadwal sebelum hari ini (date < today) otomatis sudah terlewat (>24 jam / hari lewat) -> Alpha
                if ($scheduleDate < $today) {
                    $schedule->update(['attd_status_id' => $statusAlpha]);
                    continue;
                }

                // Jadwal hari ini (date == today): periksa apakah shift sudah berakhir
                if ($scheduleDate === $today && $schedule->shift && $schedule->shift->end_time) {
                    $shiftEndTime = Carbon::parse($schedule->shift->end_time);
                    if ($now->greaterThan($shiftEndTime)) {
                        $schedule->update(['attd_status_id' => $statusAlpha]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Gagal saat mencoba menandai jadwal terlewat sebagai Alpha: ' . $e->getMessage());
        }
    }
}
