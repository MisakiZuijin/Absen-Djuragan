<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\InternScheduleRequest;
use App\Models\Division;
use App\Models\Intern;
use App\Models\School;
use App\Services\HolidayService;
use App\Services\OfficeService;
use App\Services\ScheduleService;
use App\Services\ShiftService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    protected ScheduleService $scheduleService;
    protected ShiftService $shiftService;
    protected OfficeService $officeService;
    protected HolidayService $holidayService;
    protected UserService $userService;

    public function __construct(
        ScheduleService $scheduleService,
        ShiftService $shiftService,
        OfficeService $officeService,
        HolidayService $holidayService,
        UserService $userService
    ) {
        $this->scheduleService = $scheduleService;
        $this->shiftService = $shiftService;
        $this->officeService = $officeService;
        $this->holidayService = $holidayService;
        $this->userService = $userService;
    }

    public function scheduleListView(Request $request)
    {
        $userData = $this->userService->getUserLoggedData();

        $query = Intern::with([
            'user.profile',
            'division',
            'school',
            'shift',
            'lockedOffice',
            'schedules.office',
            'whatsappNumber',
        ]);

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->filled('office_id')) {
            $officeId = $request->office_id;
            $query->where(function ($q) use ($officeId) {
                $q->where('locked_office_id', $officeId)
                    ->orWhereHas('schedules', function ($sq) use ($officeId) {
                        $sq->where('office_id', $officeId);
                    });
            });
        }

        if ($request->filled('system_type')) {
            $type = $request->system_type;
            if ($type === 'weekly') {
                $query->whereHas('schedules', function ($sq) {
                    $sq->where('type', 'weekly');
                });
            } elseif ($type === 'daily') {
                $query->whereHas('schedules', function ($sq) {
                    $sq->where('type', 'daily');
                });
            } elseif ($type === 'none') {
                $query->doesntHave('schedules');
            }
        }

        if ($request->filled('schedule_remaining')) {
            $today = \Carbon\Carbon::today();
            $todayStr = $today->toDateString();
            $filter = $request->schedule_remaining;

            if ($filter === 'today') {
                $query->whereHas('schedules', function ($sq) use ($todayStr) {
                    $sq->whereDate('start_period', '<=', $todayStr)
                        ->whereDate('end_period', '=', $todayStr);
                });
            } elseif ($filter === 'soon_3') {
                $plus3Str = $today->copy()->addDays(3)->toDateString();
                $query->whereHas('schedules', function ($sq) use ($todayStr, $plus3Str) {
                    $sq->whereDate('start_period', '<=', $todayStr)
                        ->whereDate('end_period', '>=', $todayStr)
                        ->whereDate('end_period', '<=', $plus3Str);
                });
            } elseif ($filter === 'soon_7') {
                $plus7Str = $today->copy()->addDays(7)->toDateString();
                $query->whereHas('schedules', function ($sq) use ($todayStr, $plus7Str) {
                    $sq->whereDate('start_period', '<=', $todayStr)
                        ->whereDate('end_period', '>=', $todayStr)
                        ->whereDate('end_period', '<=', $plus7Str);
                });
            } elseif ($filter === 'active') {
                $plus7Str = $today->copy()->addDays(7)->toDateString();
                $query->whereHas('schedules', function ($sq) use ($todayStr, $plus7Str) {
                    $sq->whereDate('start_period', '<=', $todayStr)
                        ->whereDate('end_period', '>', $plus7Str);
                });
            } elseif ($filter === 'upcoming') {
                $query->whereHas('schedules', function ($sq) use ($todayStr) {
                    $sq->whereNotNull('start_period')
                        ->whereDate('start_period', '>', $todayStr);
                });
            } elseif ($filter === 'expired') {
                $query->whereHas('schedules', function ($sq) use ($todayStr) {
                    $sq->whereNotNull('end_period')
                        ->whereDate('end_period', '<', $todayStr);
                })->whereDoesntHave('schedules', function ($sq) use ($todayStr) {
                    $sq->whereDate('end_period', '>=', $todayStr);
                });
            } elseif ($filter === 'unset') {
                $query->doesntHave('schedules');
            }
        }

        if ($request->filled('intern_remaining')) {
            $today = \Carbon\Carbon::today();
            $todayStr = $today->toDateString();
            $filter = $request->intern_remaining;

            if ($filter === 'today') {
                $query->whereDate('start_date', '<=', $todayStr)
                    ->whereDate('end_date', '=', $todayStr);
            } elseif ($filter === 'soon_7') {
                $plus7Str = $today->copy()->addDays(7)->toDateString();
                $query->whereDate('start_date', '<=', $todayStr)
                    ->whereDate('end_date', '>=', $todayStr)
                    ->whereDate('end_date', '<=', $plus7Str);
            } elseif ($filter === 'soon_30') {
                $plus30Str = $today->copy()->addDays(30)->toDateString();
                $query->whereDate('start_date', '<=', $todayStr)
                    ->whereDate('end_date', '>=', $todayStr)
                    ->whereDate('end_date', '<=', $plus30Str);
            } elseif ($filter === 'active') {
                $plus30Str = $today->copy()->addDays(30)->toDateString();
                $query->whereDate('start_date', '<=', $todayStr)
                    ->whereDate('end_date', '>', $plus30Str);
            } elseif ($filter === 'upcoming') {
                $query->whereNotNull('start_date')
                    ->whereDate('start_date', '>', $todayStr);
            } elseif ($filter === 'expired') {
                $query->whereNotNull('end_date')
                    ->whereDate('end_date', '<', $todayStr);
            } elseif ($filter === 'unset') {
                $query->where(function ($q) {
                    $q->whereNull('start_date')->orWhereNull('end_date');
                });
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('user.profile', function ($pq) use ($search) {
                    $pq->where('full_name', 'like', "%{$search}%");
                })->orWhere('nim', 'like', "%{$search}%")
                    ->orWhereHas('school', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Dibatasi maksimal 50 pemagang per halaman
        $interns = $query->paginate(50)->appends($request->query());

        $shiftRes = $this->shiftService->getAllShift();
        $shifts = $shiftRes->isSuccess() ? $shiftRes->getData() : collect([]);
        $offices = \App\Models\Office::with('shiftCapacities')->orderBy('name')->get();
        $schools = School::orderBy('name')->get();
        $divisions = Division::orderBy('name')->get();

        // Resolusi shift cerdas dari detail_schedules & master schedule
        // agar pemagang yang dijadwalkan via otomatisasi atau sistem jadwal shiftnya tampil 100%
        $internIds = collect($interns->items())->pluck('id')->filter()->toArray();

        if (!empty($internIds)) {
            $todayDetailShifts = DB::table('detail_schedules')
                ->join('schedules', 'detail_schedules.schedule_id', '=', 'schedules.id')
                ->whereIn('schedules.intern_id', $internIds)
                ->whereDate('detail_schedules.date', today())
                ->whereNotNull('detail_schedules.shift_id')
                ->pluck('detail_schedules.shift_id', 'schedules.intern_id');

            $latestDetailShifts = DB::table('detail_schedules')
                ->join('schedules', 'detail_schedules.schedule_id', '=', 'schedules.id')
                ->whereIn('schedules.intern_id', $internIds)
                ->whereNotNull('detail_schedules.shift_id')
                ->select('schedules.intern_id', 'detail_schedules.shift_id')
                ->orderBy('detail_schedules.date', 'desc')
                ->get()
                ->unique('intern_id')
                ->pluck('shift_id', 'intern_id');

            $shiftsById = $shifts->keyBy('id');

            foreach ($interns as $intern) {
                $resolvedShiftId = $intern->shift_id
                    ?: ($todayDetailShifts->get($intern->id)
                        ?: ($latestDetailShifts->get($intern->id)
                            ?: ($intern->schedules->first()?->shift_id ?: null)));

                if ($resolvedShiftId && isset($shiftsById[$resolvedShiftId])) {
                    $intern->resolved_shift = $shiftsById[$resolvedShiftId];
                    $intern->resolved_shift_id = $resolvedShiftId;

                    // Sinkronkan ke database interns.shift_id jika belum terisi agar konsisten
                    if (!$intern->shift_id) {
                        Intern::where('id', $intern->id)->update(['shift_id' => $resolvedShiftId]);
                        $intern->shift_id = $resolvedShiftId;
                    }
                } else {
                    $intern->resolved_shift = $intern->shift;
                    $intern->resolved_shift_id = $intern->shift_id;
                }
            }
        }

        // Hitung statistik global untuk kartu ringkasan
        $totalInterns = Intern::count();
        $totalRolling = Intern::whereHas('schedules', fn($q) => $q->where('type', 'weekly'))->count();
        $totalFixed = Intern::whereHas('schedules', fn($q) => $q->where('type', '!=', 'weekly'))->count();
        $totalUnset = Intern::doesntHave('schedules')->count();

        $data = [
            'user' => $userData,
            'interns' => $interns,
            'shifts' => $shifts,
            'offices' => $offices,
            'schools' => $schools,
            'divisions' => $divisions,
            'filterSchool' => $request->school_id,
            'filterDivision' => $request->division_id,
            'filterOffice' => $request->office_id,
            'filterSystemType' => $request->system_type,
            'search' => $request->search,
            'totalInterns' => $totalInterns,
            'totalRolling' => $totalRolling,
            'totalFixed' => $totalFixed,
            'totalUnset' => $totalUnset,
        ];

        return view('admin.penjadwalan', $data);
    }

    public function createSchedule(InternScheduleRequest $request)
    {
        $data = $this->scheduleService->createSchedule($request);

        if ($data->isSuccess()) return back()->with('success', 'Data Anggota berhasil diperbarui!');

        return back()->with('error', $data->getMessage());
    }



    public function updateSchedule(Request $request, int $id)
    {
        $validatedData = $request->validate([
            'shift_id' => 'required|integer',
        ]);


        $result = $this->scheduleService->updateShift($id, $validatedData['shift_id']);

        if ($result->isSuccess()) {
            return ResponseHelper::jsonResponse(true, 'Shift berhasil di perbarui', null);
        }
        return ResponseHelper::jsonResponse(false, 'shift gagal di perbarui', null, 400);
    }


    public function scheduleUpdateView(Request $request, int $internId)
    {
        $workType = ["wfo", "wfh"];
        $shiftData  = $this->shiftService->getAllShift();
        $officeData = $this->officeService->getAll();
        $holidayData = $this->holidayService->getAll();
        $schedulData = $this->scheduleService->getScheduleByInternId($internId);

        $schedule = $schedulData->isSuccess() ? ($schedulData->getData()['schedule'] ?? null) : null;
        $fullName = $schedule?->intern?->user?->profile?->full_name;
        if (!$fullName) {
            $intern = Intern::with('user.profile')->find($internId);
            $fullName = $intern?->user?->profile?->full_name ?? 'Nama tidak tersedia';
        }

        $data = [
            "name" => $fullName,
            "holiday_data" => $holidayData->isSuccess() ? $holidayData->getData() : [],
            "intern_id" => $internId,
            "workTypes" => $workType,
            "shifts" => $shiftData->isSuccess() ? $shiftData->getData() : collect([]),
            "offices" => $officeData->isSuccess() ? $officeData->getData() : collect([]),
            "schedule_data" => $schedulData->isSuccess() ? $schedulData->getData() : []
        ];
        return view('admin.shift_schedule.edit_shift_schedule', $data);
    }


    public function scheduleUpdateSingle(Request $request, int $internId)
    {
        $requestData = $request->validate([
            'date' => 'required',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900|max:2100',
            'shift_id' => 'required|integer',
            'work_type' => 'required|string|in:wfo,wfh',
            'office_id' => 'required|integer',
            'schedule_type' => 'required|integer',
            'back_earlier' => 'required|integer'
        ]);

        $date = sprintf('%04d-%02d-%02d', (int)$requestData["year"], (int)$requestData["month"], (int)$requestData["date"]);

        $result = $this->scheduleService->updateScheduleSingleData($internId, $date, $requestData);

        if (!$result->isSuccess()) {
            return response()->json(['message' => $result->getMessage() ?: 'Gagal memperbarui jadwal.'], 400);
        }

        return response()->json(['message' => 'Data Schedule berhasil di perbarui!'], 200);
    }


    public function scheduleUpdateMulti(Request $request, int $internId)
    {
        $requestData = $request->validate([
            'date' => 'required|string',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900|max:2100',
            'shift_id' => 'required|integer',
            'work_type' => 'required|string|in:wfo,wfh',
            'office_id' => 'required|integer',
            'schedule_type' => 'required|integer'
        ]);

        $result = $this->scheduleService->updateScheduleMultiData($internId, $requestData);

        if (!$result->isSuccess()) {
            return response()->json(['message' => $result->getMessage() ?: 'Gagal memperbarui jadwal massal.'], 400);
        }

        return response()->json(['message' => 'Data Schedule berhasil di perbarui!'], 200);
    }
}
