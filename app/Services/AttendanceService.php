<?php

namespace App\Services;

use App\DTO\AttendanceDTO;
use App\DTO\DetailScheduleDTO;
use App\DTO\ScheduleDTO;
use App\Helper\ActionResult;
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
use App\Models\Attendance;
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

    public function attendanceStatus(int $internId, ?DetailSchedule $preloadedDetailSchedule = null): ActionResult
    {
        try {
            $now = DateNow::getCurrentDateYMD();
            $timeNow = DateNow::getCurrentTime();

            if ($preloadedDetailSchedule) {
                $detailSchedule = $preloadedDetailSchedule;
                $schedule = $detailSchedule->schedule ?? $this->scheduleRepository->findByInternId($internId);
            } else {
                $schedule =  $this->scheduleRepository->findByInternId($internId);

                $isSunday = date('w') == 0;
                $isHoliday = \App\Models\Holiday::whereDate('date', $now)->exists();

                if (!$schedule && ($isSunday || $isHoliday)) {
                    return new ActionResult(true, "Today is a holiday or day off.", [
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
            }

            // [BARU GANTI JAM V2] Cek sesi ChangeTimeSession yang sedang aktif
            $activeChangeTimeSession = \App\Models\ChangeTimeSession::where('intern_id', $internId)
                ->where('status', 'active')
                ->latest('id')
                ->first();

            if ($activeChangeTimeSession) {
                $targetScheduleId = $activeChangeTimeSession->targets()->first()?->detail_schedule_id ?? $detailSchedule?->id;
                $sessionShift = $activeChangeTimeSession->shift;
                $nowTime = Carbon::now('Asia/Jakarta')->format('H:i:s');
                
                $isBreakMissed = false;
                if (empty($activeChangeTimeSession->break_time)) {
                    if ($sessionShift && isset($sessionShift->break_time_in_minute) && (int) $sessionShift->break_time_in_minute <= 0) {
                        $isBreakMissed = true;
                    } elseif ($sessionShift && !empty($sessionShift->end_break_time) && $nowTime > $sessionShift->end_break_time) {
                        $isBreakMissed = true;
                    }
                }

                $stage = AttendanceStatus::BreakOrBack;
                if (!empty($activeChangeTimeSession->break_time) && empty($activeChangeTimeSession->back_time)) {
                    $stage = AttendanceStatus::EndBreakAdjustable;
                } elseif (!empty($activeChangeTimeSession->back_time) || $isBreakMissed) {
                    $stage = AttendanceStatus::AdjustableOut;
                }

                return new ActionResult(true, "", [
                    "absenceHistory" => null,
                    "all_adjustable" => collect([$activeChangeTimeSession]),
                    "adjustableTimeHistory" => $activeChangeTimeSession,
                    "changeTimeSession" => $activeChangeTimeSession,
                    "schedule_id" => $schedule?->id,
                    "detail_schedule_id" => $targetScheduleId,
                    "totalChangeTime" => 0,
                    "shift" => $activeChangeTimeSession->shift,
                    "stage" => $stage
                ]);
            }

            if (is_null($detailSchedule)) {
                $activeAdjustable = \App\Models\AdjustableAttd::where('intern_id', $internId)
                    ->whereNotNull('start_time')
                    ->whereNull('end_time')
                    ->latest('id')
                    ->first();

                if ($activeAdjustable) {
                    $adjustableTimeData = collect([$activeAdjustable]);
                    if (!is_null($activeAdjustable->start_time) && is_null($activeAdjustable->break_time)) {
                        return new ActionResult(true, "", [
                            "absenceHistory" => null,
                            "all_adjustable" => $adjustableTimeData,
                            "adjustableTimeHistory" => $activeAdjustable,
                            "schedule_id" => $schedule?->id,
                            "detail_schedule_id" => $activeAdjustable->detail_schedule_id,
                            "totalChangeTime" => 0,
                            "shift" => null,
                            "stage" => AttendanceStatus::BreakOrBack
                        ]);
                    }
                    if (!is_null($activeAdjustable->break_time) && is_null($activeAdjustable->back_time)) {
                        return new ActionResult(true, "", [
                            "absenceHistory" => null,
                            "all_adjustable" => $adjustableTimeData,
                            "adjustableTimeHistory" => $activeAdjustable,
                            "schedule_id" => $schedule?->id,
                            "detail_schedule_id" => $activeAdjustable->detail_schedule_id,
                            "totalChangeTime" => 0,
                            "shift" => null,
                            "stage" => AttendanceStatus::EndBreakAdjustable
                        ]);
                    }
                    if (!is_null($activeAdjustable->start_time) && is_null($activeAdjustable->end_time)) {
                        return new ActionResult(true, "", [
                            "absenceHistory" => null,
                            "all_adjustable" => $adjustableTimeData,
                            "adjustableTimeHistory" => $activeAdjustable,
                            "schedule_id" => $schedule?->id,
                            "detail_schedule_id" => $activeAdjustable->detail_schedule_id,
                            "totalChangeTime" => 0,
                            "shift" => null,
                            "stage" => AttendanceStatus::AdjustableOut
                        ]);
                    }
                }

                $isSunday = date('w') == 0;
                $isHoliday = \App\Models\Holiday::whereDate('date', $now)->exists();

                if ($isSunday || $isHoliday) {
                    return new ActionResult(true, "", [
                        "absenceHistory" => null,
                        "all_adjustable" => collect(),
                        "adjustableTimeHistory" => null,
                        "schedule_id" => $schedule?->id,
                        "detail_schedule_id" => null,
                        "stage" => AttendanceStatus::AdjustableIn
                    ]);
                }

                return new ActionResult(true, "", [
                    "absenceHistory" => null,
                    "all_adjustable" => collect(),
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule?->id,
                    "detail_schedule_id" => null,
                    "stage" => AttendanceStatus::AttendanceAndAdjustableTime
                ]);
            }


            $adjustableTimeData = \App\Models\AdjustableAttd::where('intern_id', $internId)
                ->where(function ($q) use ($detailSchedule, $now) {
                    $q->whereDate('date', $now)
                      ->orWhere('detail_schedule_id', $detailSchedule->id)
                      ->orWhereNull('end_time');
                })
                ->get();

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

            // Cari sesi ganti jam yang sedang aktif (start_time terisi dan end_time null) untuk pemagang ini
            if (is_null($adjustableTarget)) {
                $activeAdjustable = \App\Models\AdjustableAttd::where('intern_id', $internId)
                    ->whereNotNull('start_time')
                    ->whereNull('end_time')
                    ->latest('id')
                    ->first();
                if ($activeAdjustable) {
                    $adjustableTarget = $activeAdjustable;
                    if (!$adjustableTimeData->contains('id', $activeAdjustable->id)) {
                        $adjustableTimeData->push($activeAdjustable);
                    }
                }
            }

            // JIKA STATUS HARI INI ADALAH ALPHA (attd_status_id = 5)
            // Sesi presensi reguler hari ini ditutup dan dikunci sebagai AllDone (Selesai).
            if ((int) $detailSchedule->attd_status_id === 5) {
                return new ActionResult(true, "Hari ini Anda tercatat Alpha.", [
                    "absenceHistory" => $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => $adjustableTarget,
                    "schedule_id" => $schedule->id,
                    "shift" => $detailSchedule->shift,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "stage" => AttendanceStatus::AllDone
                ]);
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

            // 1. Jika presensi reguler hari ini sudah selesai (end_time terisi) dan tidak ada sesi ganti jam yang sedang berjalan
            if (!$isAttendanceNull && !is_null($attendanceTarget->end_time) && is_null($adjustableTarget)) {
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

            // 2. Jika sesi ganti jam hari ini sudah selesai (semua ada end_time) DAN presensi reguler juga sudah selesai
            // PENTING: Jangan return AllDone jika intern belum absen masuk reguler — agar bisa lanjut ke AttendanceAndAdjustableTime
            if ($totalAdjustable > 0 && is_null($adjustableTarget) && !is_null($attendanceTarget?->end_time)) {
                return new ActionResult(true, "", [
                    "absenceHistory" => $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => AttendanceStatus::AllDone,
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

            if (!is_null($attendanceTarget) && is_null($attendanceTarget->start_time) && is_null($adjustableTarget)) {
                $isSunday = date('w') == 0;
                $isHoliday = \App\Models\Holiday::whereDate('date', $now)->exists();
                $targetStage = ($isSunday || $isHoliday) ? AttendanceStatus::AdjustableIn : AttendanceStatus::AttendanceAndAdjustableTime;

                return new ActionResult(true, "", [
                    "absenceHistory" => $attendanceTarget,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => $targetStage,
                ]);
            }

            if ($isAttendanceNull && is_null($adjustableTarget)) {
                $isSunday = date('w') == 0;
                $isHoliday = \App\Models\Holiday::whereDate('date', $now)->exists();
                $targetStage = ($isSunday || $isHoliday) ? AttendanceStatus::AdjustableIn : AttendanceStatus::AttendanceAndAdjustableTime;

                return new ActionResult(true, "", [
                    "absenceHistory" => null,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => null,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => $targetStage,
                ]);
            }


            if (!is_null($adjustableTarget) && !is_null($adjustableTarget->start_time) && is_null($adjustableTarget->break_time)) {
                $adjShift = $adjustableTarget instanceof \App\Models\ChangeTimeSession 
                    ? $adjustableTarget->shift 
                    : ($detailSchedule?->shift ?? null);
                
                $isAdjBreakMissed = false;
                if ($adjShift && isset($adjShift->break_time_in_minute) && (int) $adjShift->break_time_in_minute <= 0) {
                    $isAdjBreakMissed = true;
                } elseif ($adjShift && !empty($adjShift->end_break_time) && $timeNow > $adjShift->end_break_time) {
                    $isAdjBreakMissed = true;
                }

                return new ActionResult(true, "", [
                    "absenceHistory" => null,
                    "all_adjustable" => $adjustableTimeData,
                    "adjustableTimeHistory" => $adjustableTarget,
                    "schedule_id" => $schedule->id,
                    "detail_schedule_id" => $detailSchedule->id,
                    "totalChangeTime" => $totalAdjustable,
                    "shift" => $detailSchedule->shift,
                    "stage" => $isAdjBreakMissed ? AttendanceStatus::AdjustableOut : AttendanceStatus::BreakOrBack
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



            $currentUser = auth()->user() ?? $schedule?->intern?->user;
            $scheduleDate = $schedule?->date ?? $now;
            $effectiveEndBreak = $detailSchedule->shift ? $detailSchedule->shift->getEffectiveEndBreakTime($scheduleDate, $currentUser) : null;
            $nowTime = Carbon::now('Asia/Jakarta')->format('H:i:s');

            $isRegularBreakMissed = false;
            if (empty($detailSchedule->shift?->break_time_in_minute) || (int) ($detailSchedule->shift?->break_time_in_minute ?? 0) <= 0) {
                $isRegularBreakMissed = true;
            } elseif (!empty($effectiveEndBreak) && $nowTime > $effectiveEndBreak) {
                $isRegularBreakMissed = true;
            }

            $canTakeBreak = is_null($attendanceTarget?->break_time) 
                && ($detailSchedule->shift?->break_time_in_minute ?? 0) > 0 
                && !$isRegularBreakMissed;
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
            return new ActionResult(false, "Something went wrong.", null);
        }
    }


    public function attendanceAction(AttendanceDTO $data): ActionResult
    {
        try {
            // Validasi keamanan: jika status jadwal hari ini adalah Alpha (attd_status_id = 5)
            // maka aksi reguler ditolak (kecuali ganti jam)
            if ($data->getDetailScheduleId()) {
                $detailSchedule = $this->detailScheduleRepository->find($data->getDetailScheduleId());
                if ($detailSchedule && (int) $detailSchedule->attd_status_id === 5) {
                    $adjustableStages = [
                        AttendanceStatus::AdjustableIn->value,
                        AttendanceStatus::StartBreakAdjustable->value,
                        AttendanceStatus::EndBreakAdjustable->value,
                        AttendanceStatus::AdjustableOut->value
                    ];
                    if (!in_array($data->getStage(), $adjustableStages)) {
                        return new ActionResult(false, "Presensi reguler hari ini telah ditutup oleh Admin (Status Alpha). Anda hanya dapat melakukan Ganti Jam.", null);
                    }
                }
            }

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
            Log::error('AttendanceService::attendanceAction error: ' . $th->getMessage() . ' in ' . $th->getFile() . ':' . $th->getLine());
            captureException($th);
            return new ActionResult(false, "Something went wrong: " . $th->getMessage(), null);
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
            case AttendanceStatus::AllDone->value:
                return new AllDoneState();
            default:
                throw new \Exception("Unknown stage: " . $stage);
        }
    }

    public function getTotalInternAbsence(Request $request): ActionResult
    {
        try {
            $dateTarget = $request->input('date', Carbon::now()->format('Y-m-d'));
            $counts = DetailSchedule::where('date', $dateTarget)
                ->selectRaw("
                    COUNT(CASE WHEN attd_status_id = 2 THEN 1 END) as total_attendance,
                    COUNT(CASE WHEN attd_status_id = 3 THEN 1 END) as total_absence,
                    COUNT(CASE WHEN attd_status_id = 5 THEN 1 END) as total_permit
                ")
                ->first();

            $data = [
                "attendanceTotal" => (int) ($counts->total_attendance ?? 0),
                "absenceTotal" => (int) ($counts->total_permit ?? 0),
                "permitTotal" => (int) ($counts->total_absence ?? 0)
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
            $counts = DetailSchedule::where('date', $dateNow)
                ->selectRaw("
                    COUNT(CASE WHEN attd_status_id = 2 THEN 1 END) as total_attendance,
                    COUNT(CASE WHEN attd_status_id = 3 THEN 1 END) as total_absence,
                    COUNT(CASE WHEN attd_status_id = 5 THEN 1 END) as total_permit,
                    COUNT(CASE WHEN office_id = 1 AND attd_status_id = 2 THEN 1 END) as total_office_1,
                    COUNT(CASE WHEN office_id = 2 AND attd_status_id = 2 THEN 1 END) as total_office_2,
                    COUNT(CASE WHEN office_id = 3 AND attd_status_id = 2 THEN 1 END) as total_office_3
                ")
                ->first();

            $data = [
                "attendanceTotal" => (int) ($counts->total_attendance ?? 0),
                "absenceTotal" => (int) ($counts->total_permit ?? 0),
                "permitTotal" => (int) ($counts->total_absence ?? 0),
                "totalAttendanceOffice1" => (int) ($counts->total_office_1 ?? 0),
                "totalAttendanceOffice2" => (int) ($counts->total_office_2 ?? 0),
                "totalAttendanceOffice3" => (int) ($counts->total_office_3 ?? 0)
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

            // Eager load semua relasi bertingkat untuk mencegah 150-500+ N+1 query
            $result->load([
                'schedule.intern.user.profile',
                'permitReason.category',
                'attdStatus',
                'shift',
                'attendance.permitLogs',
                'adjustableAttendance',
                'logActivity',
            ]);

            $finalData = [];

            foreach ($result->items() as $item) {
                $modifiedData = [];

                $modifiedData['name'] = $item->schedule?->intern?->user?->profile?->full_name ?? $item->schedule?->intern?->user?->name ?? 'Pemagang';
                $modifiedData['intern_id'] = $item->schedule?->intern?->id ?? 0;
                $modifiedData['detail_schedule_id'] = $item->id;
                $modifiedData['is_notification_sent'] = $item->is_notification_sent;
                $modifiedData['ischange_schedule'] = $item->isChangeSchedule;
                $modifiedData['permit_data'] = $item->permitReason;
                $modifiedData['attd_status'] = $item->attdStatus;
                $modifiedData['shift_id'] = $item->shift->id ?? 0;

                $isPermitStatus = (int)$item->attd_status_id === 3;
                $isExcusedLeave = \App\Helper\TimeHelper::isApprovedExcusedLeave($item);
                $attendance = $item->attendance;
                $shift = $item->shift;
                $extraDebt = \App\Helper\TimeHelper::mandatoryReplaceDebtMinutes($attendance);
                $workData = $shift ? \App\Helper\TimeHelper::calculateDailyWorkHours($attendance, $shift, $item, $extraDebt) : null;

                $startTime = ($isPermitStatus && $shift) ? $shift->start_time : ($attendance->start_time ?? null);
                $endTime = ($isPermitStatus && $shift) ? $shift->end_time : ($attendance->end_time ?? null);
                $breakTime = ($isPermitStatus && $shift) ? ($attendance->break_time ?? $shift->start_break_time) : ($attendance->break_time ?? null);
                $backTime = ($isPermitStatus && $shift) ? ($attendance->back_time ?? $shift->end_break_time) : ($attendance->back_time ?? null);

                $attendanceData = [
                    "id" => $attendance->id ?? ($item->attendance_id ?? null),
                    "date" => $attendance ? date('d-m-Y', strtotime($attendance->date)) : ($item->date ? date('d-m-Y', strtotime($item->date)) : null),
                    "start_time" => $startTime,
                    "break_time" => $breakTime,
                    "back_time" => $backTime,
                    "permit_start" => $attendance->permit_start ?? null,
                    "permit_back" => $attendance->permit_back ?? null,
                    "end_time" => $endTime,
                    "total_min" => $workData ? $workData['actual_work_minutes'] : ($attendance->total_min ?? 0),
                    "total_break_min" => $attendance->total_break_min ?? ($shift->break_time_in_minute ?? 0),
                    "total_permit_min" => $attendance->total_permit_min ?? 0,
                    "start_time_message" => $attendance->start_time_message ?? ($isPermitStatus ? 'Izin disetujui' : null),
                    "break_time_message" => $attendance->break_time_message ?? null,
                    "back_time_message" => $attendance->back_time_message ?? null,
                    "permit_start_message" => $attendance->permit_start_message ?? null,
                    "permit_back_message" => $attendance->permit_back_message ?? null,
                    "end_time_message" => $attendance->end_time_message ?? ($isPermitStatus ? 'Izin disetujui' : null),
                    "latitude_start" => $attendance->latitude_start ?? null,
                    "longitude_start" => $attendance->longitude_start ?? null,
                    "latitude_end" => $attendance->latitude_end ?? null,
                    "longitude_end" => $attendance->longitude_end ?? null,
                    "total_time" => $workData ? $workData['actual_work_formatted'] : '00:00',
                    "target_time" => $workData ? [
                        "condition" => $workData['diff_minutes'] >= 0,
                        "value" => $workData['diff_formatted'],
                    ] : ["condition" => false, "value" => '00:00'],
                    "total_min_format" => $workData ? $workData['actual_work_formatted'] : '00:00',
                    "target_time_format" => $workData ? $workData['diff_formatted'] : '00:00',
                    "shift_target_formatted" => $workData ? $workData['shift_target_formatted'] : '',
                    "mandatory_replace_minutes" => $workData ? $workData['mandatory_replace_minutes'] : 0,
                ];
                $modifiedData['attendance'] = $attendanceData;

                if ($item->adjustableAttendance) {
                    $adjustableResponse = [];
                    $shift = $item->shift;
                    $shiftTargetMinutes = $shift ? (int)($shift->total_time_in_minute ?? 0) : 0;
                    if ($shiftTargetMinutes <= 0 && $shift && $shift->start_time && $shift->end_time && $shift->start_time !== '00:00:00') {
                        $shiftTargetMinutes = max(0, \App\Helper\TimeHelper::diffInMinutes($shift->start_time, $shift->end_time) - (int)($shift->break_time_in_minute ?? 0));
                    }

                    foreach ($item->adjustableAttendance as $key => $value) {
                        $isApprovedVal = is_array($value->is_approved) 
                            ? ($value->is_approved['value'] ?? 0) 
                            : ($value->is_approved->value ?? $value->is_approved ?? 0);
                        $totalMin = (int) ($value->total_min ?? 0);
                        $breakMin = (int) ($value->total_break_min ?? 0);
                        $adjMinutes = max(0, $totalMin - $breakMin);
                        if ($adjMinutes <= 0 && !empty($value->start_time) && !empty($value->end_time)) {
                            $totalMins = \App\Helper\TimeHelper::diffInMinutes($value->start_time, $value->end_time);
                            $breakMins = (!empty($value->break_time) && !empty($value->back_time))
                                ? \App\Helper\TimeHelper::diffInMinutes($value->break_time, $value->back_time)
                                : 0;
                            $adjMinutes = max(0, $totalMins - $breakMins);
                        }

                        if ((int)$isApprovedVal === 2) {
                            $diffMinutes = -$shiftTargetMinutes;
                            $targetVal = \App\Helper\TimeHelper::formatDifference($diffMinutes);
                            $condition = false;
                        } else {
                            if ($shiftTargetMinutes > 0) {
                                $diffMinutes = $adjMinutes - $shiftTargetMinutes;
                                $targetVal = \App\Helper\TimeHelper::formatDifference($diffMinutes);
                                $condition = $diffMinutes >= 0;
                            } else {
                                $targetVal = \App\Helper\TimeHelper::formatDifference($adjMinutes);
                                $condition = $adjMinutes > 0;
                            }
                        }

                        $adjustableData = [
                            "id"                    => $value->id,
                            "date"                  => date('d-m-Y', strtotime($value->date)),
                            "start_time"            => $value->start_time,
                            "break_time"            => $value->break_time,
                            "back_time"             => $value->back_time,
                            "permit_start"          => $value->permit_start,
                            "permit_back"           => $value->permit_back,
                            "end_time"              => $value->end_time,
                            "total_min"             => $adjMinutes,
                            "total_break_min"       => $breakMin,
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
                            "is_approved"           => $isApprovedVal,
                            "total_time"            => \App\Helper\TimeHelper::formatMinutesToHours($adjMinutes),
                            "target_time"           => [
                                "condition"             => $condition,
                                "value"                 => $targetVal,
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

                // Izin keluar wajib ganti jam menambah hutang waktu hari tersebut
                $workTimeTarget += \App\Helper\TimeHelper::mandatoryReplaceDebtMinutes($defaultAttendance);

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
            return new ActionResult(false, "", null);
        }
    }


    public function attendanceReport(Request $request)
    {
        try {
            $today = Carbon::now();
            $currentPage = (int) $request->query('page', 1);
            $pagination = (int) $request->query('perPage', 10);
            $startDate = $request->query('startDate');
            $endDate = $request->query('endDate');
            $internName = $request->query('internName');

            $startDate = $startDate ? Carbon::parse($startDate)->toDateString() : $today->startOfDay()->toDateString();
            $endDate = $endDate ? Carbon::parse($endDate)->toDateString() : $today->endOfDay()->toDateString();

            $interns = Intern::with('user.profile')
                ->when($internName, function ($query) use ($internName) {
                    $query->whereHas('user.profile', function ($query) use ($internName) {
                        $query->where('full_name', 'LIKE', '%' . $internName . '%');
                    });
                })
                ->paginate($pagination, ['*'], 'page', $currentPage);

            $internIds = $interns->pluck('id')->toArray();

            // Hitung rekap kehadiran seluruh pemagang di halaman ini dalam 1 single query
            $attendanceSummaries = collect();
            if (!empty($internIds)) {
                $attendanceSummaries = DB::table('schedules')
                    ->join('detail_schedules', 'schedules.id', '=', 'detail_schedules.schedule_id')
                    ->whereIn('schedules.intern_id', $internIds)
                    ->whereBetween('detail_schedules.date', [$startDate, $endDate])
                    ->selectRaw('
                        schedules.intern_id,
                        SUM(CASE WHEN detail_schedules.attd_status_id = 5 THEN 1 ELSE 0 END) as absence,
                        SUM(CASE WHEN detail_schedules.attd_status_id IN (2, 4) THEN 1 ELSE 0 END) as submitted,
                        SUM(CASE WHEN detail_schedules.attd_status_id = 3 THEN 1 ELSE 0 END) as permits
                    ')
                    ->groupBy('schedules.intern_id')
                    ->get()
                    ->keyBy('intern_id');
            }

            $internValue = [];

            foreach ($interns as $intern) {
                $attData = $attendanceSummaries->get($intern->id);

                $internDetails = [
                    'id' => $intern->id,
                    'name' => $intern->user?->profile?->full_name ?? $intern->user?->name ?? 'Pemagang',
                    'nip' => $intern->user?->profile?->NIP ?? '-',
                    'submitted' => (int) ($attData->submitted ?? 0),
                    'absence' => (int) ($attData->absence ?? 0),
                    'permits' => (int) ($attData->permits ?? 0),
                ];

                $internValue[] = $internDetails;
            }

            $totalPage = $interns->lastPage();
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

            return new ActionResult(true, "success get all data", $responseData);
        } catch (\Throwable $th) {
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

            if (!$this->scheduleRepository) {
                return new ActionResult(false, "scheduleRepository not initialized", null);
            }

            $pageSize = (int) $request->query('per_page', 10);
            $page = (int) $request->query('page', $requestPage ?? 1);

            $holidayDates = \App\Models\Holiday::pluck('date')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->toArray();

            $query = DetailSchedule::with([
                'attendance.permitLogs',
                'shift',
                'adjustableAttendance',
                'logActivity',
                'attdStatus',
                'permitReason.category'
            ])
            ->whereHas('schedule', function ($q) use ($internId) {
                $q->where('intern_id', $internId);
            });

            // Sembunyikan jadwal hari libur dan Minggu yang kosong (tanpa presensi masuk, tanpa ganti jam, tanpa izin)
            if (!empty($holidayDates)) {
                $query->where(function ($q) use ($holidayDates) {
                    $q->whereNotIn(\Illuminate\Support\Facades\DB::raw('DATE(date)'), $holidayDates)
                      ->orWhereHas('attendance', fn($aq) => $aq->whereNotNull('start_time'))
                      ->orWhereHas('adjustableAttendance')
                      ->orWhere('attd_status_id', 3);
                });
            }

            $query->where(function ($q) {
                $q->whereRaw('DAYOFWEEK(date) != 1')
                  ->orWhereHas('attendance', fn($aq) => $aq->whereNotNull('start_time'))
                  ->orWhereHas('adjustableAttendance')
                  ->orWhere('attd_status_id', 3);
            });

            if ($statusId) {
                $query->where('attd_status_id', $statusId);
            }

            $total = $query->count();

            if ($total === 0) {
                $data = [
                    'data' => null,
                    'pagination' => null,
                ];

                return new ActionResult(true, "still dont have any attendance history", $data);
            }

            $paginatedDetailSchedules = $query->orderBy('date', 'asc')
                ->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get();

            $paginatedData = $paginatedDetailSchedules->map(function ($detailSchedule) {
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
                $isPermitStatus = (int)$detailSchedule->attd_status_id === 3;
                // Hutang waktu tambahan dari izin keluar wajib ganti jam
                $extraDebt = \App\Helper\TimeHelper::mandatoryReplaceDebtMinutes($detailSchedule->attendance);

                if (($isExcused || $isPermitStatus) && $shift) {
                    $workData = \App\Helper\TimeHelper::calculateDailyWorkHours((object)$dataAttendance, $shift, $detailSchedule, $extraDebt);
                    $dataAttendance['id'] = $dataAttendance['id'] ?? ($detailSchedule->attendance_id ?? 0);
                    $dataAttendance['start_time'] = $shift->start_time;
                    $dataAttendance['end_time'] = $shift->end_time;
                    $dataAttendance['break_time'] = !empty($dataAttendance['break_time']) ? $dataAttendance['break_time'] : $shift->start_break_time;
                    $dataAttendance['back_time'] = !empty($dataAttendance['back_time']) ? $dataAttendance['back_time'] : $shift->end_break_time;
                    $dataAttendance['total_min'] = $workData['actual_work_minutes'];
                    $dataAttendance['total_min_format'] = $workData['actual_work_formatted'];
                    $dataAttendance['target_time'] = $workData['diff_minutes'];
                    $dataAttendance['target_time_format'] = $workData['diff_formatted'];
                    $dataAttendance['shift_target_formatted'] = $workData['shift_target_formatted'];
                    $dataAttendance['start_time_message'] = $dataAttendance['start_time_message'] ?? 'Izin Disetujui (Sesuai Jadwal)';
                    $dataAttendance['end_time_message'] = $dataAttendance['end_time_message'] ?? 'Izin Disetujui (Sesuai Jadwal)';
                } else if (!empty($dataAttendance) && $shift) {
                    // Gunakan TimeHelper untuk perhitungan jam kerja aktual dan selisih
                    $workData = \App\Helper\TimeHelper::calculateDailyWorkHours((object)$dataAttendance, $shift, $detailSchedule, $extraDebt);
                    $dataAttendance['total_min'] = $workData['actual_work_minutes'];
                    $dataAttendance['total_min_format'] = $workData['actual_work_formatted'];
                    $dataAttendance['target_time'] = $workData['diff_minutes'];
                    $dataAttendance['target_time_format'] = $workData['diff_formatted'];
                    $dataAttendance['shift_target_formatted'] = $workData['shift_target_formatted'];
                } else if (!empty($dataAttendance) && $dataAttendance["start_time"] != null && $shift) {
                    $totaltime = \App\Helper\TimeHelper::diffInMinutes($dataAttendance["start_time"], now());
                    $dataAttendance['total_min'] = $totaltime;
                    $dataAttendance['total_min_format'] = \App\Helper\TimeHelper::formatMinutesToHours($totaltime);
                    $target = $totaltime - $shift->total_time_in_minute;
                    $dataAttendance['target_time'] = $target;
                    $dataAttendance['target_time_format'] = \App\Helper\TimeHelper::formatDifference($target);
                    $dataAttendance['shift_target_formatted'] = \App\Helper\TimeHelper::formatMinutesToHours($shift->total_time_in_minute);
                } else if ($shift) {
                    $dataAttendance['total_min'] = 0;
                    $dataAttendance['total_min_format'] = \App\Helper\TimeHelper::formatMinutesToHours(0);
                    $target = -$shift->total_time_in_minute;
                    $dataAttendance['target_time'] = $target;
                    $dataAttendance['target_time_format'] = \App\Helper\TimeHelper::formatDifference($target);
                    $dataAttendance['shift_target_formatted'] = \App\Helper\TimeHelper::formatMinutesToHours($shift->total_time_in_minute);
                } else {
                    // Handle case when no shift exists
                    $dataAttendance['total_min'] = 0;
                    $dataAttendance['total_min_format'] = '00:00';
                    $dataAttendance['target_time'] = 0;
                    $dataAttendance['target_time_format'] = '00:00';
                    $dataAttendance['shift_target_formatted'] = '00:00';
                }
                $dataAttendance['mandatory_replace_minutes'] = $extraDebt;

                if (!empty($detailSchedule->adjustableAttendance)) {
                    $adjustableAttendance = $detailSchedule->adjustableAttendance->toArray();
                    $filteredData = array_filter($adjustableAttendance, function ($key) {
                        return is_numeric($key);
                    }, ARRAY_FILTER_USE_KEY);

                    $shiftTargetMinutes = $shift ? (int)($shift->total_time_in_minute ?? 0) : 0;
                    if ($shiftTargetMinutes <= 0 && $shift && $shift->start_time && $shift->end_time && $shift->start_time !== '00:00:00') {
                        $shiftTargetMinutes = max(0, \App\Helper\TimeHelper::diffInMinutes($shift->start_time, $shift->end_time) - (int)($shift->break_time_in_minute ?? 0));
                    }

                    $result = array_map(function ($item) use ($shift, $shiftTargetMinutes) {
                        $gantiJamMinutes = (int) ($item['total_min'] ?? 0);
                        if ($gantiJamMinutes <= 0 && !empty($item['start_time']) && !empty($item['end_time'])) {
                            $totalMins = \App\Helper\TimeHelper::diffInMinutes($item['start_time'], $item['end_time']);
                            $breakMins = (!empty($item['break_time']) && !empty($item['back_time']))
                                ? \App\Helper\TimeHelper::diffInMinutes($item['break_time'], $item['back_time'])
                                : 0;
                            $gantiJamMinutes = max(0, $totalMins - $breakMins);
                        }

                        $isApprovedVal = is_array($item['is_approved'] ?? null) 
                            ? ($item['is_approved']['value'] ?? 0) 
                            : ($item['is_approved'] ?? 0);

                        $item['total_min_format'] = \App\Helper\TimeHelper::formatMinutesToHours($gantiJamMinutes);

                        if ((int)$isApprovedVal === 2) {
                            $diff = -$shiftTargetMinutes;
                            $item['target_time'] = $diff;
                            $item['target_time_format'] = \App\Helper\TimeHelper::formatDifference($diff);
                        } else {
                            if ($shiftTargetMinutes > 0) {
                                $diff = $gantiJamMinutes - $shiftTargetMinutes;
                                $item['target_time'] = $diff;
                                $item['target_time_format'] = \App\Helper\TimeHelper::formatDifference($diff);
                            } else {
                                $item['target_time'] = $gantiJamMinutes;
                                $item['target_time_format'] = \App\Helper\TimeHelper::formatDifference($gantiJamMinutes);
                            }
                        }

                        $item['shift_target_formatted'] = $shift ? \App\Helper\TimeHelper::formatMinutesToHours($shiftTargetMinutes) : '00:00';
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
                'total' => $total,
                'last_page' => (int) ceil($total / $pageSize),
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
                    "remaing_time_after_discount_seconds" => 0,
                    "mandatory_replace_minutes" => 0
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
                    "remaing_time_after_discount_seconds" => 0,
                    "mandatory_replace_minutes" => 0
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

            // Hutang waktu dari izin (keluar, sholat, toilet) wajib ganti jam (approved), per attendance
            $mandatoryReplaceByAttendance = \App\Models\PermitLog::whereIn('attendance_id', $totalAttendance->pluck('attendance_id')->filter())
                ->whereIn('type', ['leave', 'prayer', 'toilet'])
                ->where('is_mandatory_replace', true)
                ->where('approval_status', 'approved')
                ->groupBy('attendance_id')
                ->selectRaw('attendance_id, SUM(COALESCE(agreed_duration_minutes, duration_in_minutes, 0)) AS total_minutes')
                ->pluck('total_minutes', 'attendance_id');
            $mandatoryReplaceTotal = 0;

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

                // Izin keluar wajib ganti jam menambah hutang waktu hari tersebut
                if ($day->attendance_id && isset($mandatoryReplaceByAttendance[$day->attendance_id])) {
                    $extraDebt = (int) $mandatoryReplaceByAttendance[$day->attendance_id];
                    $workTimeTarget += $extraDebt;
                    $mandatoryReplaceTotal += $extraDebt;
                }

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
                "mandatory_replace_minutes" => $mandatoryReplaceTotal,
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

        $extraDebt = \App\Helper\TimeHelper::mandatoryReplaceDebtMinutes($attendance);
        $workData = \App\Helper\TimeHelper::calculateDailyWorkHours($attendance, $shift, null, $extraDebt);

        return [
            "total_time" => $workData['actual_work_formatted'],
            "target_time" => [
                "condition" => $workData['diff_minutes'] >= 0,
                "value" => $workData['diff_formatted'],
            ],
            "total_min_format" => $workData['actual_work_formatted'],
            "target_time_format" => $workData['diff_formatted'],
            "shift_target_formatted" => $workData['shift_target_formatted'],
            "mandatory_replace_minutes" => $extraDebt,
        ];
    }


    public function setToEndTime(string $date, int $shift_id, $end_time)
    {
        try {
            $shift = $this->shiftRepository->getById($shift_id);
            if (!$shift) {
                return;
            }
            $data = $this->attendanceRepository->getAttendanceStillNotBack($date, $shift_id);
            if ($data->isEmpty()) {
                return;
            }

            $casesTotalMin = [];
            $casesTotalBreakMin = [];
            $ids = [];
            $idsWithBreak = [];

            foreach ($data as $row) {
                $id = (int)$row['id'];
                $ids[] = $id;
                $total_time = (int)DateNow::getDifferentInMinute($row["start_time"], $shift->end_time);
                $casesTotalMin[] = "WHEN id = {$id} THEN {$total_time}";

                if ($row["break_time"] != null && $row["back_time"] == null) {
                    $idsWithBreak[] = $id;
                    $totalBreakMin = (int)DateNow::getDifferentInMinute($row["break_time"], $shift->end_time);
                    $casesTotalBreakMin[] = "WHEN id = {$id} THEN {$totalBreakMin}";
                }
            }

            if (!empty($ids)) {
                $rawTotalMin = "CASE " . implode(" ", $casesTotalMin) . " END";

                $updateData = [
                    "end_time" => $shift->end_time,
                    "total_min" => DB::raw($rawTotalMin),
                    'is_auto_end' => true,
                ];

                if (!empty($idsWithBreak)) {
                    $rawTotalBreakMin = "CASE " . implode(" ", $casesTotalBreakMin) . " ELSE total_break_min END";
                    $escapedEndTime = DB::getPdo()->quote($shift->end_time);
                    $updateData['back_time'] = DB::raw("CASE WHEN id IN (" . implode(",", $idsWithBreak) . ") THEN {$escapedEndTime} ELSE back_time END");
                    $updateData['total_break_min'] = DB::raw($rawTotalBreakMin);
                }

                Attendance::whereIn('id', $ids)->update($updateData);
            }
        } catch (\Throwable $th) {
            Log::error('setToEndTime error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
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
            $detailSchedule->is_change_schedule_approved = ($validated['jam-option'] == 1) ? 1 : 0;
            $detailSchedule->save();

            \App\Helper\TimeHelper::syncValidPermitAttendance($detailSchedule, $detailSchedule->shift);

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
                'is_change_schedule_approved' => ($validated['jam-option'] == 1) ? 1 : 0,
            ]);

            \App\Helper\TimeHelper::syncValidPermitAttendance($detailSchedule, $detailSchedule->shift);

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
            $workTypeInput = strtolower((string)$validatedData['work_type']);
            if ($workTypeInput === '0' || $workTypeInput === 'wfo') {
                $detailSchedule->work_type = 'wfo';
            } elseif ($workTypeInput === '1' || $workTypeInput === 'wfh') {
                $detailSchedule->work_type = 'wfh';
            } else {
                $detailSchedule->work_type = in_array($workTypeInput, ['wfo', 'wfh']) ? $workTypeInput : 'wfo';
            }
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
            $page = max(1, (int) $page);
            $nameStr = ($name !== null && trim((string)$name) !== '') ? trim((string)$name) : null;
            $dateStr = ($date !== null && trim((string)$date) !== '') ? trim((string)$date) : null;

            if ($nameStr !== null && $dateStr !== null) {
                $result = $this->attendanceRepository->getAutoEndStatusByDateAndName(
                    name: $nameStr,
                    date: $dateStr,
                    currentPage: $page
                );
            } else if ($nameStr !== null && $dateStr === null) {
                $result = $this->attendanceRepository->getAutoEndStatusByName(name: $nameStr, currentPage: $page);
            } else if ($nameStr === null && $dateStr !== null) {
                $result = $this->attendanceRepository->getAutoEndStatusByDate(date: $dateStr, currentPage: $page);
            } else {
                $result = $this->attendanceRepository->getAllAutoEnd(currentPage: $page);
            }

            $data = [];

            foreach ($result as $item) {
                $intern = $item->detailSchedules?->schedule?->intern ?? $item->intern;
                $user = $intern?->user;
                $profile = $user?->profile;
                $shift = $item->detailSchedules?->shift ?? $intern?->shift;
                $office = $item->detailSchedules?->office;

                $data[] = [
                    'id' => $item->id,
                    'intern_id' => $intern?->id,
                    'name' => $profile?->full_name ?? ($user?->name ?? "-"),
                    'division' => $intern?->division?->name ?? 'Tanpa Divisi',
                    'school' => $intern?->school?->name ?? null,
                    'nip' => $profile?->NIP ?? ($intern?->nim ?? null),
                    'date' => $item->date ? Carbon::parse($item->date)->format('d/m/Y') : '-',
                    'office' => $office?->name ?? '-',
                    'shift' => $shift?->name ?? 'Default',
                    'start_time' => $item->start_time ? Carbon::parse($item->start_time)->format('H:i') : '-',
                    'end_time' => $item->end_time ? Carbon::parse($item->end_time)->format('H:i') : '-',
                    'auto_end_note' => $item->auto_end_note,
                    'auto_end_notified' => (bool) $item->auto_end_notified,
                ];
            }

            $meta = [
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
            Log::error('shortAutomaticAttendance error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            captureException($e);
            return new ActionResult(false, "Failed to get auto attendance records: " . $e->getMessage(), [
                'data' => [],
                'meta' => ['current_page' => 1, 'total_page' => 1, 'total_data' => 0]
            ]);
        }
    }

    /**
     * Mengambil daftar pemagang yang belum melakukan presensi pulang dan sudah melewati jam pulang shift.
     */
    public function getPendingAutoEndAttendances(?string $date = null, ?string $search = null)
    {
        $date = $date ?? Carbon::today('Asia/Jakarta')->toDateString();
        $now = Carbon::now('Asia/Jakarta');

        $query = Attendance::with([
            'intern.user.profile',
            'intern.division',
            'intern.school',
            'intern.shift',
            'detailSchedules.shift',
            'detailSchedules.office'
        ])
        ->whereDate('date', $date)
        ->whereNotNull('start_time')
        ->whereNull('end_time')
        ->where('is_auto_end', false);

        if (!empty($search)) {
            $query->whereHas('intern.user.profile', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            });
        }

        $attendances = $query->orderBy('start_time', 'asc')->get();

        // Filter: Hanya tampilkan pemagang yang waktu sekarang sudah MELEWATI jam pulang dari shiftnya
        return $attendances->filter(function ($attd) use ($now, $date) {
            $shift = $attd->detailSchedules?->shift ?? $attd->intern?->shift;
            if (!$shift || !$shift->end_time) {
                return Carbon::parse($date)->lessThan($now->copy()->startOfDay());
            }

            $attdDateStr = $attd->date ? Carbon::parse($attd->date)->format('Y-m-d') : $date;
            $endTimeStr = is_string($shift->end_time) ? $shift->end_time : Carbon::parse($shift->end_time)->format('H:i:s');
            $shiftEndDateTime = Carbon::parse($attdDateStr . ' ' . $endTimeStr, 'Asia/Jakarta');

            // Shift malam melewati tengah malam (end_time < start_time)
            if ($shift->start_time && $shift->end_time && $shift->end_time < $shift->start_time) {
                $shiftEndDateTime = $shiftEndDateTime->addDay();
            }

            // Hanya tampilkan jika sudah melewati batas jam pulang shift
            return $now->greaterThanOrEqualTo($shiftEndDateTime);
        })->values();
    }

    /**
     * Menghitung total pemagang yang belum presensi pulang dan sudah melewati jam pulang shift hari ini.
     */
    public function countPendingAutoEndToday(): int
    {
        return $this->getPendingAutoEndAttendances(Carbon::today('Asia/Jakarta')->toDateString())->count();
    }

    /**
     * Konfirmasi pulang otomatis untuk 1 pemagang dengan catatan opsional.
     */
    public function confirmAutoEndAttendance(int $attendanceId, ?string $note = null, ?string $customEndTime = null): ActionResult
    {
        try {
            DB::beginTransaction();

            $attendance = Attendance::with(['detailSchedules.shift', 'intern.user.profile'])->find($attendanceId);
            if (!$attendance) {
                return new ActionResult(false, 'Data presensi tidak ditemukan.');
            }

            if ($attendance->end_time && !$attendance->is_auto_end) {
                return new ActionResult(false, 'Pemagang sudah melakukan presensi pulang.');
            }

            $shift = $attendance->detailSchedules?->shift ?? $attendance->intern?->shift;

            $endTime = $customEndTime;
            if (!$endTime) {
                $endTime = $shift?->end_time ?? Carbon::now()->format('H:i:s');
            }

            $endTimeStr = is_string($endTime) ? $endTime : Carbon::parse($endTime)->format('H:i:s');

            // Hitung total menit kerja
            $startTime = Carbon::parse($attendance->start_time);
            $attendanceDate = $attendance->date ? Carbon::parse($attendance->date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');
            $endDateTime = Carbon::parse($attendanceDate . ' ' . $endTimeStr);

            // Jika shift malam melewati tengah malam (end_time < start_time)
            if ($shift && $shift->start_time && $shift->end_time && $shift->end_time < $shift->start_time) {
                $endDateTime = $endDateTime->addDay();
            }

            $totalMin = max(0, $startTime->diffInMinutes($endDateTime));

            $updateData = [
                'end_time' => $endTimeStr,
                'is_auto_end' => true,
                'auto_end_note' => $note ?: null,
                'auto_end_notified' => false,
                'total_min' => $totalMin,
            ];

            // Jika sedang izin istirahat tapi belum kembali
            if ($attendance->break_time && !$attendance->back_time) {
                $breakStart = Carbon::parse($attendance->break_time);
                $totalBreak = max(0, $breakStart->diffInMinutes($endDateTime));
                $updateData['back_time'] = $endTimeStr;
                $updateData['total_break_min'] = $totalBreak;
                $updateData['total_min'] = max(0, $totalMin - $totalBreak);
            }

            $attendance->update($updateData);

            $internName = $attendance->intern?->user?->profile?->full_name ?? 'Pemagang';
            $adminName = auth()->user()?->name ?? 'Admin';
            \App\Helper\ActivityLogger::log(
                'UPDATE',
                'Pulang Otomatis',
                "Admin {$adminName} memulangkan otomatis pemagang {$internName}" . ($note ? " dengan catatan: '{$note}'" : ""),
                [
                    'attendance_id' => $attendance->id,
                    'intern_id' => $attendance->intern_id,
                    'note' => $note,
                    'end_time' => $endTimeStr
                ]
            );

            DB::commit();
            return new ActionResult(true, "Berhasil memulangkan {$internName} secara otomatis.", $attendance);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('confirmAutoEndAttendance error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return new ActionResult(false, 'Terjadi kesalahan saat memproses pulang otomatis: ' . $th->getMessage());
        }
    }

    /**
     * Konfirmasi pulang otomatis massal untuk banyak pemagang.
     */
    public function bulkConfirmAutoEndAttendance(array $attendanceIds, ?string $note = null): ActionResult
    {
        try {
            DB::beginTransaction();

            $successCount = 0;
            foreach ($attendanceIds as $id) {
                $res = $this->confirmAutoEndAttendance((int)$id, $note);
                if ($res->isSuccess()) {
                    $successCount++;
                }
            }

            DB::commit();
            return new ActionResult(true, "Berhasil memulangkan {$successCount} pemagang secara otomatis.");
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('bulkConfirmAutoEndAttendance error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return new ActionResult(false, 'Gagal memproses pulang otomatis massal: ' . $th->getMessage());
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

            // Ambil seluruh tanggal hari libur yang tercatat sampai hari ini
            $holidayDates = \App\Models\Holiday::whereDate('date', '<=', $today)
                ->pluck('date')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->toArray();

            $alphaIds = [];
            foreach ($missedSchedules as $schedule) {
                $scheduleDate = Carbon::parse($schedule->date)->toDateString();

                // Hari libur nasional / tanggal merah dan hari Minggu tidak boleh ditandai Alpha
                $isSunday = Carbon::parse($scheduleDate)->isSunday();
                $isHoliday = in_array($scheduleDate, $holidayDates, true);
                if ($isSunday || $isHoliday) {
                    continue;
                }

                // Jadwal sebelum hari ini (date < today) otomatis sudah terlewat (>24 jam / hari lewat) -> Alpha
                if ($scheduleDate < $today) {
                    $alphaIds[] = $schedule->id;
                    continue;
                }

                // Jadwal hari ini (date == today): periksa apakah shift sudah berakhir
                if ($scheduleDate === $today && $schedule->shift && $schedule->shift->end_time) {
                    $shiftEndTime = Carbon::parse($schedule->shift->end_time);
                    if ($now->greaterThan($shiftEndTime)) {
                        $alphaIds[] = $schedule->id;
                    }
                }
            }

            if (!empty($alphaIds)) {
                DetailSchedule::whereIn('id', $alphaIds)->update(['attd_status_id' => $statusAlpha]);
            }
        } catch (\Exception $e) {
            Log::error('Gagal saat mencoba menandai jadwal terlewat sebagai Alpha: ' . $e->getMessage());
        }
    }
}
