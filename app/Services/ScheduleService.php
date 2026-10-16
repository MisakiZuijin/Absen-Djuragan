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
            if (!$result) {
                $intern = \App\Models\Intern::find($internId);
                $result = \App\Models\Schedule::firstOrCreate(
                    ['intern_id' => $internId],
                    [
                        'office_id' => $intern?->locked_office_id ?? 1,
                        'start_period' => $intern?->start_date ?? now()->toDateString(),
                        'end_period' => $intern?->end_date ?? now()->addMonths(3)->toDateString(),
                        'type' => 'daily',
                    ]
                );
            }
            $data = [
                "schedule"  => $result,
                "schedule_data" => $result ? $result->detailSchedules : collect([])
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

            $data = (!empty($request->validator)) ? $request->validated() : $request->all();
            $internId = (int) $data['intern_id'];

            $intern = $this->internRepository->getById($internId);
            if (!$intern) {
                return new ActionResult(false, "intern not found", null);
            }

            $start_date = new DateTime($data['start_period']);
            $end_date = new DateTime($data['end_period']);
            if ($end_date <= $start_date) {
                return new ActionResult(false, 'End periode tidak boleh kurang dari atau sama dengan start periode', null);
            }

            $shiftId = (int) $data["shift_id"];
            $officeId = (int) ($data["office_id"] ?? 1);
            $category = (int) $data["type"]; // 1 = Tetap (daily), 2 = Bergantian (weekly)
            $typeString = $category == 1 ? "daily" : "weekly";

            $currentShiftObj = \App\Models\Shift::find($shiftId);

            // Shift 'none' (ID 1) atau shift yang tidak aktif untuk rolling tidak boleh dirotasi
            $isBaseShiftRollingEligible = $currentShiftObj
                && $currentShiftObj->id != 1
                && strtolower($currentShiftObj->name) !== 'none'
                && !empty($currentShiftObj->is_rolling);

            if (!$isBaseShiftRollingEligible && $category == 2) {
                $category = 1;
                $typeString = "daily";
            }

            // Ambil seluruh ID shift yang aktif dan diizinkan rolling di kantor terkait (diurutkan berdasarkan start_time)
            $officeShiftCapacities = \App\Models\OfficeShiftCapacity::where('office_id', $officeId)
                ->where('is_enabled', true)
                ->where('max_capacity', '>', 0)
                ->pluck('shift_id')
                ->toArray();

            if (!empty($officeShiftCapacities)) {
                $rollingShiftIds = \App\Models\Shift::whereIn('id', $officeShiftCapacities)
                    ->where('id', '!=', 1)
                    ->where('name', '!=', 'none')
                    ->where('is_active', 1)
                    ->where('is_rolling', 1)
                    ->orderBy('start_time', 'asc')
                    ->pluck('id')
                    ->toArray();
            } else {
                $rollingShiftIds = \App\Models\Shift::where('is_active', 1)
                    ->where('id', '!=', 1)
                    ->where('name', '!=', 'none')
                    ->where('is_rolling', 1)
                    ->orderBy('start_time', 'asc')
                    ->pluck('id')
                    ->toArray();
            }

            if (empty($rollingShiftIds)) {
                $rollingShiftIds = [$shiftId];
            }

            // KUNCI PASANGAN ROLLING SHIFT (Berdasarkan Pasangan Grup Rolling Shift):
            $hasGroupRotation = false;

            if ($category == 2 && $isBaseShiftRollingEligible) {
                // 1. Cek Pasangan Rolling dari Grup Divisi jika ada
                $rollingGroup = $intern?->division?->rollingGroup;

                // 2. Jika tidak ada di relasi divisi, cari di grup rolling umum (ShiftRollingGroup)
                if (!$rollingGroup || !$rollingGroup->is_active) {
                    $rollingGroup = \App\Models\ShiftRollingGroup::where('is_active', true)
                        ->where(function ($q) use ($shiftId) {
                            $q->where('shift_id_1', $shiftId)
                              ->orWhere('shift_id_2', $shiftId);
                        })->first();
                }

                if ($rollingGroup && $rollingGroup->is_active && $rollingGroup->shift_id_1 && $rollingGroup->shift_id_2) {
                    $s1 = (int) $rollingGroup->shift_id_1;
                    $s2 = (int) $rollingGroup->shift_id_2;

                    $shift1Obj = \App\Models\Shift::find($s1);
                    $shift2Obj = \App\Models\Shift::find($s2);

                    // Kedua shift dalam grup harus aktif dan memiliki is_rolling == 1
                    if ($s1 !== 1 && $s2 !== 1
                        && $shift1Obj && $shift2Obj
                        && !empty($shift1Obj->is_rolling) && !empty($shift2Obj->is_rolling)
                        && !empty($shift1Obj->is_active) && !empty($shift2Obj->is_active)) {

                        $partnerShiftId = ($shiftId === $s1) ? $s2 : $s1;
                        if (in_array($partnerShiftId, $rollingShiftIds) && in_array($shiftId, $rollingShiftIds)) {
                            $rollingShiftIds = [$shiftId, $partnerShiftId];
                            $hasGroupRotation = true;
                        }
                    }
                }

                // 3. Fallback: Kunci Pasangan 1-to-1 di Master Shift (rolling_partner_shift_id)
                if (!$hasGroupRotation && $currentShiftObj && $currentShiftObj->rolling_partner_shift_id) {
                    $partnerShiftId = (int) $currentShiftObj->rolling_partner_shift_id;
                    $partnerShiftObj = \App\Models\Shift::find($partnerShiftId);
                    if ($partnerShiftId !== 1 && $partnerShiftObj && !empty($partnerShiftObj->is_rolling) && !empty($partnerShiftObj->is_active)) {
                        if (in_array($partnerShiftId, $rollingShiftIds)) {
                            $rollingShiftIds = [$shiftId, $partnerShiftId];
                            $hasGroupRotation = true;
                        }
                    }
                }

                // Jika tidak ada grup pasangan rolling yang valid, jadwalkan Tetap (daily)
                if (!$hasGroupRotation) {
                    $category = 1;
                    $typeString = "daily";
                    $rollingShiftIds = [$shiftId];
                }
            }

            // Jika kantor hanya menyediakan 1 shift aktif (misal Kantor yang hanya memiliki shift Middle),
            // maka sistem shift otomatis dipaksa Tetap (daily) karena rotasi shift tidak dimungkinkan.
            if (count($rollingShiftIds) <= 1) {
                $category = 1;
                $typeString = "daily";
                $shiftId = $rollingShiftIds[0];
            }

            // Update atau buat master schedule untuk pemagang
            $existSchedul = $this->scheduleRepository->findByInternId($intern->id);
            if ($existSchedul) {
                $existSchedul->update([
                    'type' => $typeString,
                    'start_period' => $start_date->format('Y-m-d'),
                    'end_period' => $end_date->format('Y-m-d'),
                    'office_id' => $officeId,
                ]);
                $scheduleRecord = $existSchedul;
            } else {
                $scheduleRecord = $this->scheduleRepository->create([
                    'intern_id' => $internId,
                    'type' => $typeString,
                    'start_period' => $start_date->format('Y-m-d'),
                    'end_period' => $end_date->format('Y-m-d'),
                    'office_id' => $officeId,
                ]);
            }

            // Sinkronkan shift_id dan is_fixed_shift pada data pemagang
            if ($intern) {
                $intern->shift_id = $shiftId;
                $intern->is_fixed_shift = ($category == 1);
                $intern->save();
            }

            // Ambil seluruh tanggal hari libur yang terdaftar
            $holidayDates = \App\Models\Holiday::pluck('date')
                ->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())
                ->toArray();

            $current_date = clone $start_date;
            $currentShift = $shiftId;
            $currentWeek = $start_date->format('o-W');

            $scheduleDatesMap = []; // dateStr => assignedShiftId

            while ($current_date <= $end_date) {
                $dateStr = $current_date->format('Y-m-d');
                $thisWeek = $current_date->format('o-W');

                // Jika sistem bergantian (category == 2) dan berpindah minggu (berganti pada hari SENIN ISO),
                // rotasi ke shift berikutnya. Jika kantor hanya ada 1 shift, tidak perlu dirotasi.
                if ($category == 2 && count($rollingShiftIds) > 1 && $thisWeek !== $currentWeek) {
                    $currentShift = $this->isNextShift($rollingShiftIds, $currentShift);
                    $currentWeek = $thisWeek;
                }

                $isSunday = $current_date->format('N') == 7;
                $isHoliday = in_array($dateStr, $holidayDates, true);

                // Hari Minggu dan Hari Libur nasional tidak dibuatkan jadwal kerja
                if (!$isSunday && !$isHoliday) {
                    $scheduleDatesMap[$dateStr] = $currentShift;
                }

                $current_date->modify('+1 day');
            }

            if (!empty($scheduleDatesMap)) {
                $datesToProcess = array_keys($scheduleDatesMap);

                // 1. Ambil absensi yang sudah ada untuk pemagang pada rentang tanggal ini
                $existingAttendances = \App\Models\Attendance::where('intern_id', $internId)
                    ->whereIn('date', $datesToProcess)
                    ->get()
                    ->keyBy(function ($item) {
                        return \Carbon\Carbon::parse($item->date)->toDateString();
                    });

                // 2. Buat absensi placeholder baru jika belum pernah ada
                $attendancesToInsert = [];
                foreach ($datesToProcess as $dateStr) {
                    if (!$existingAttendances->has($dateStr)) {
                        $attendancesToInsert[] = [
                            "date" => $dateStr,
                            "intern_id" => $internId
                        ];
                    }
                }

                if (!empty($attendancesToInsert)) {
                    \App\Models\Attendance::insert($attendancesToInsert);

                    $newAttendances = \App\Models\Attendance::where('intern_id', $internId)
                        ->whereIn('date', array_column($attendancesToInsert, 'date'))
                        ->get()
                        ->keyBy(function ($item) {
                            return \Carbon\Carbon::parse($item->date)->toDateString();
                        });

                    $allAttendances = $existingAttendances->union($newAttendances);
                } else {
                    $allAttendances = $existingAttendances;
                }

                // 3. Ambil detail schedule yang sudah ada untuk jadwal ini pada rentang tanggal ini
                $existingDetailSchedules = \App\Models\DetailSchedule::where('schedule_id', $scheduleRecord->id)
                    ->whereIn('date', $datesToProcess)
                    ->get()
                    ->keyBy(function ($item) {
                        return \Carbon\Carbon::parse($item->date)->toDateString();
                    });

                $detailSchedulesToInsert = [];

                foreach ($scheduleDatesMap as $dateStr => $assignedShiftId) {
                    $attendance = $allAttendances->get($dateStr);
                    $attId = $attendance?->id;

                    if ($existingDetailSchedules->has($dateStr)) {
                        $detail = $existingDetailSchedules->get($dateStr);
                        // Perbarui shift dan kantor jika pemagang belum clock-in (start_time kosong)
                        if (empty($attendance?->start_time)) {
                            $detail->update([
                                'shift_id' => $assignedShiftId,
                                'office_id' => $officeId,
                            ]);
                        }
                    } else {
                        if ($attId) {
                            $detailSchedulesToInsert[] = [
                                "attendance_id" => $attId,
                                "schedule_id" => $scheduleRecord->id,
                                "shift_id" => $assignedShiftId,
                                "office_id" => $officeId,
                                "date" => $dateStr,
                                "isChangeSchedule" => 0
                            ];
                        }
                    }
                }

                // 4. Bulk insert detail schedules yang baru
                if (!empty($detailSchedulesToInsert)) {
                    \App\Models\DetailSchedule::insert($detailSchedulesToInsert);
                }
            }

            DB::commit();

            return new ActionResult(true, "Data jadwal berhasil diperbarui!", $scheduleRecord);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('createSchedule error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            captureException($th);
            return new ActionResult(false, "Gagal membuat/memperbarui jadwal: " . $th->getMessage(), null);
        }
    }


    private function isNextShift(array $indexShift, int $currentShift): int
    {
        if (empty($indexShift)) {
            return $currentShift;
        }

        // 1. Jika indexShift sudah diset sebagai pasangan 2 shift yang saling rolling, langsung tukar ke pasangan
        if (count($indexShift) === 2 && in_array($currentShift, $indexShift)) {
            return ($currentShift === $indexShift[0]) ? $indexShift[1] : $indexShift[0];
        }

        // 2. Cek apakah ada grup rolling aktif (ShiftRollingGroup)
        $rollingGroup = \App\Models\ShiftRollingGroup::where('is_active', true)
            ->where(function ($q) use ($currentShift) {
                $q->where('shift_id_1', $currentShift)
                  ->orWhere('shift_id_2', $currentShift);
            })->first();

        if ($rollingGroup) {
            $partnerId = ($currentShift === (int) $rollingGroup->shift_id_1)
                ? (int) $rollingGroup->shift_id_2
                : (int) $rollingGroup->shift_id_1;

            if (in_array($partnerId, $indexShift)) {
                return $partnerId;
            }
        }

        // 3. Cek pasangan 1-to-1 di kolom master shift
        $shiftObj = \App\Models\Shift::find($currentShift);
        if ($shiftObj && $shiftObj->is_rolling && $shiftObj->rolling_partner_shift_id) {
            $partnerId = (int) $shiftObj->rolling_partner_shift_id;
            if (in_array($partnerId, $indexShift)) {
                return $partnerId;
            }
        }

        $partnerOfThis = \App\Models\Shift::where('rolling_partner_shift_id', $currentShift)
            ->where('is_rolling', 1)
            ->first();
        if ($partnerOfThis && in_array($partnerOfThis->id, $indexShift)) {
            return (int) $partnerOfThis->id;
        }

        // 4. Default rotasi sirkuler jika lebih dari 2 shift
        $currentIndex = array_search($currentShift, $indexShift);
        if ($currentIndex !== false) {
            $nextIndex = ($currentIndex + 1) % count($indexShift);
            return $indexShift[$nextIndex];
        }

        return $currentShift;
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
            if (!$schedule) {
                $intern = \App\Models\Intern::find($internId);
                $schedule = \App\Models\Schedule::firstOrCreate(
                    ['intern_id' => $internId],
                    [
                        'office_id' => (int) ($data['office_id'] ?? ($intern?->locked_office_id ?? 1)),
                        'start_period' => $intern?->start_date ?? $date,
                        'end_period' => $intern?->end_date ?? $date,
                        'type' => 'daily',
                    ]
                );
            }
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
                $intern = \App\Models\Intern::find($internId);
                $schedule = \App\Models\Schedule::firstOrCreate(
                    ['intern_id' => $internId],
                    [
                        'office_id' => (int) ($data['office_id'] ?? ($intern?->locked_office_id ?? 1)),
                        'start_period' => $intern?->start_date ?? now()->toDateString(),
                        'end_period' => $intern?->end_date ?? now()->addMonths(3)->toDateString(),
                        'type' => 'daily',
                    ]
                );
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
