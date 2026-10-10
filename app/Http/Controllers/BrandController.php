<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Intern;
use App\Services\ShiftService;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class BrandController extends Controller
{
    protected UserService $userService;
    protected ShiftService $shiftService;

    public function __construct(UserService $userService, ShiftService $shiftService)
    {
        $this->userService = $userService;
        $this->shiftService = $shiftService;
    }

    /**
     * Tampilkan halaman daftar kartu Brand (seperti halaman daftar sekolah).
     */
    public function adminBrandView(): View
    {
        $userData = $this->userService->getUserLoggedData();
        $brandList = Brand::withCount('interns')->orderBy('name', 'asc')->get();
        $unassignedCount = Intern::whereNull('brand_id')->count();

        $data = [
            'user' => $userData,
            'brandList' => $brandList,
            'unassignedCount' => $unassignedCount,
        ];

        return view('admin.brand')->with($data);
    }

    /**
     * Tampilkan daftar pemagang dalam Brand tertentu (seperti halaman anggota sekolah).
     */
    public function adminBrandTeamView(string $brandId): View
    {
        $userData = $this->userService->getUserLoggedData();

        $isUnassigned = ($brandId === 'unassigned' || $brandId === '0');
        $brand = null;

        if ($isUnassigned) {
            $brand = (object) [
                'id' => 0,
                'name' => 'Tanpa Brand / Belum Ditugaskan',
                'logo' => null,
                'description' => 'Daftar pemagang yang belum terafiliasi dengan Brand manapun.',
                'is_active' => true,
            ];
            $query = Intern::whereNull('brand_id');
        } else {
            $brand = Brand::findOrFail((int) $brandId);
            $query = Intern::where('brand_id', $brand->id);
        }

        $interns = $query->with([
            'user.profile',
            'division',
            'shift',
            'lockedOffice',
            'schedules.office',
            'school',
            'whatsappNumber',
        ])->get();

        // Resolusi shift cerdas (konsisten dengan SchoolController)
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
                } else {
                    $intern->resolved_shift = $intern->shift;
                    $intern->resolved_shift_id = $intern->shift_id;
                }
            }
        }

        $data = [
            'user' => $userData,
            'brand' => $brand,
            'teamData' => $interns,
        ];

        return view('admin.anggota-brand')->with($data);
    }
}
