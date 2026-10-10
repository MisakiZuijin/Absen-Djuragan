<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use App\Models\School;
use App\Services\InternService;
use App\Services\OfficeService;
use App\Services\SchoolService;
use App\Services\ShiftService;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class SchoolController extends Controller
{
    protected UserService $userService;
    protected SchoolService $schoolService;
    protected InternService $internService;
    protected ShiftService $shiftService;
    protected OfficeService $officeService;

    public function __construct(UserService $userService, SchoolService $schoolService, ShiftService $shiftService, OfficeService $officeService)
    {
        $this->userService = $userService;
        $this->schoolService = $schoolService;
        $this->shiftService = $shiftService;
        $this->officeService = $officeService;
    }

    public function adminSchoolView(\Illuminate\Http\Request $request): View
    {
        $userData = $this->userService->getUserLoggedData();
        $search = $request->query('search');
        $schoolList = $this->schoolService->getCountSchool($search);

        $data = [
            "user" => $userData,
            "schoolList" => $schoolList->isSuccess() ? $schoolList->getData() : collect([]),
            "search" => $search,
        ];

        return view('admin.sekolah')->with($data);
    }

    public function adminSchoolTeamView(int $schoolId): View
    {
        $userData = $this->userService->getUserLoggedData();
        $school = School::find($schoolId);
        $teamData = $this->schoolService->getTeamBySchoolId($schoolId);
        $interns = $teamData->isSuccess() ? $teamData->getData() : collect([]);

        // Resolusi shift cerdas agar pemagang dari sekolah ini selalu tampil shift-nya
        $shiftsResult = $this->shiftService->getAllShift();
        $shifts = $shiftsResult->isSuccess() ? $shiftsResult->getData() : collect([]);
        $shiftsById = $shifts->keyBy('id');

        $internIds = $interns->pluck('id')->filter()->toArray();
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

            foreach ($interns as $intern) {
                $resolvedShiftId = $intern->shift_id
                    ?: ($todayDetailShifts->get($intern->id)
                        ?: ($latestDetailShifts->get($intern->id)
                            ?: ($intern->schedules->first()?->shift_id ?: null)));

                if ($resolvedShiftId && isset($shiftsById[$resolvedShiftId])) {
                    $intern->resolved_shift = $shiftsById[$resolvedShiftId];
                    $intern->resolved_shift_id = $resolvedShiftId;

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

        $data = [
            "user" => $userData,
            "school" => $school,
            "teamData" => $interns,
        ];

        return view('admin.anggota-sekolah')->with($data);
    }
}
