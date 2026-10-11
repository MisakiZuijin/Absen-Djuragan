<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Intern;
use App\Models\Shift;
use App\Models\School;
use App\Services\ShiftService;
use App\Models\DetailSchedule;
use \App\Http\Requests\UpdateShiftRequest;
use Carbon\Carbon;

class ShiftController extends Controller
{
    protected ShiftService $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    public function index($name = null)
    {
        $shiftNames = Shift::select('name')
            ->distinct()
            ->where('name', '!=', 'None')->where('name', '!=', 'none')
            ->pluck('name')
            ->mapWithKeys(fn($shiftName) => [$shiftName => ucwords(str_replace(['_', '-'], ' ', $shiftName))])
            ->toArray();

        if (empty($shiftNames)) {
            return view('admin.shift.index', [
                'interns' => collect(),
                'activeShiftName' => null,
                'shiftNames' => []
            ]);
        }

        if (!$name) {
            return redirect()->route('admin.shift.index', array_key_first($shiftNames));
        }

        if (!array_key_exists($name, $shiftNames)) {
            abort(404, 'Kategori shift tidak ditemukan.');
        }

        $currentShift = Shift::where('name', $name)->first();

        $detailSchedules = DetailSchedule::whereHas('shift', function ($query) use ($name) {
            $query->where('name', $name);
        })
            ->whereDate('date', today())
            ->with([
                'schedule.intern.user.profile',
                'schedule.intern.school',
                'schedule.intern.division',
                'office',
                'shift'
            ])
            ->get()
            ->unique('schedule.intern_id')
            ->sortBy(function ($item) {
                return $item->schedule?->intern?->user?->profile?->full_name ?? $item->schedule?->intern?->user?->username ?? '';
            });

        return view('admin.shift.index', [
            'detailSchedules' => $detailSchedules,
            'shiftNames' => $shiftNames,
            'activeShiftName' => $name,
            'currentShift' => $currentShift,
        ]);
    }

    private function getShiftNames()
    {
        return Shift::select('name')
            ->distinct()
            ->pluck('name')
            ->filter(fn($name) => strtolower($name) !== 'none')
            ->mapWithKeys(fn($name) => [$name => ucwords(str_replace(['_', '-'], ' ', $name))])
            ->toArray();
    }

    private function formatShiftName(string $name)
    {
        return ucwords(str_replace(['_', '-'], ' ', $name));
    }

    public function update(UpdateShiftRequest $request, Shift $shift)
    {
        try {
            $this->shiftService->updateShift($request, $shift->id);

            return redirect()->route('admin.shift.index', ['name' => $request->input('nama_Shift')])
                ->with('success', 'Shift berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    private function calculateTimeDifference(string $startTime, string $endTime)
    {
        $start = new \DateTime($startTime);
        $end = new \DateTime($endTime);
        $diff = $start->diff($end);
        return ($diff->h * 60) + $diff->i;
    }

    public function showBulkUpdateForm()
    {
        $schools = School::withCount(['interns' => function ($q) {
            $q->whereHas('user', function ($uq) {
                $uq->where('is_active', true);
            })->where(function ($wq) {
                $wq->where('work_placement_type', '!=', 'wfh_only')
                    ->orWhereNull('work_placement_type');
            });
        }])->orderBy('name')->get();

        $shifts = Shift::where('name', '!=', 'None')
            ->where('name', '!=', 'none')
            ->get();

        return view('admin.shift.bulk-update', compact('schools', 'shifts'));
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'school_ids' => 'required|array',
            'school_ids.*' => 'exists:schools,id',
            'intern_ids' => 'required|array',
            'intern_ids.*' => 'exists:interns,id',
            'shift_id' => 'required|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $internIds = $request->intern_ids;
            $shiftId = $request->shift_id;
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Saring pemagang yang berstatus WFH Penuh (mengikuti sekolah remote atau manual wfh_only)
            // Sesuai aturan: pemagang WFH Penuh terkunci dan tidak dapat diubah shiftnya secara massal
            $eligibleInternIds = Intern::whereIn('id', $internIds)
                ->where(function ($q) {
                    $q->where('work_placement_type', '!=', 'wfh_only')
                        ->orWhereNull('work_placement_type');
                })
                ->whereDoesntHave('school', function ($sq) {
                    $sq->where('is_remote_school', true);
                })
                ->pluck('id')
                ->toArray();

            if (empty($eligibleInternIds)) {
                return redirect()->back()->with('error', 'Semua pemagang yang dipilih berstatus WFH Penuh (Terkunci oleh aturan sekolah remote/kebijakan WFH) sehingga shiftnya tidak dapat diubah.');
            }

            // Cari dan update semua DetailSchedule untuk intern yang berhak dalam rentang tanggal secara massal
            $query = DetailSchedule::whereHas('schedule', function ($query) use ($eligibleInternIds) {
                $query->whereIn('intern_id', $eligibleInternIds);
            })->whereBetween('date', [$startDate, $endDate]);

            $updatedCount = $query->update(['shift_id' => $shiftId]);

            if ($updatedCount === 0) {
                return redirect()->back()->with('error', 'Tidak ada jadwal yang ditemukan untuk intern yang dipilih dalam rentang tanggal tersebut.');
            }

            $adminUser = auth()->user();
            $adminName = $adminUser?->profile?->full_name ?? $adminUser?->username ?? 'Admin';
            $targetShift = Shift::find($shiftId);
            \App\Helper\ActivityLogger::log('UPDATE', 'Master Data', "Admin {$adminName} memperbarui shift kerja massal ({$updatedCount} jadwal) menjadi shift {$targetShift?->name}", [
                'shift_id' => $shiftId,
                'count' => $updatedCount,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            return redirect()->back()->with('success', "Shift berhasil diperbarui untuk {$updatedCount} jadwal.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui shift: ' . $e->getMessage());
        }
    }

    /**
     * Mengambil daftar intern berdasarkan beberapa sekolah untuk dropdown dinamis.
     * Pemagang WFH Penuh (sekolah remote / wfh_only) disaring keluar agar tidak dapat diubah shiftnya.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInternsBySchools(Request $request)
    {
        $request->validate([
            'school_ids' => 'required|array',
            'school_ids.*' => 'exists:schools,id'
        ]);

        $schoolIds = $request->school_ids;

        $interns = Intern::whereIn('school_id', $schoolIds)
            ->with(['user.profile', 'division', 'school'])
            ->whereHas('user', function ($q) {
                $q->where('is_active', true);
            })
            ->get()
            ->filter(function ($intern) {
                // Kecualikan pemagang yang WFH penuh (sekolah remote atau wfh_only)
                $isRemoteSchool = (bool) ($intern->school?->is_remote_school ?? false);
                $isWfhFull = ($intern->work_placement_type === 'wfh_only');
                return !$isRemoteSchool && !$isWfhFull;
            })
            ->sortBy(function ($intern) {
                return $intern->user?->profile?->full_name ?? $intern->user?->username ?? '';
            })
            ->map(function ($intern) {
                $fullName = $intern->user?->profile?->full_name ?? $intern->user?->username ?? 'Tanpa Nama';
                $div = $intern->division ? ' • ' . $intern->division->name : '';
                return [
                    'id' => $intern->id,
                    'text' => $fullName . $div,
                    'name' => $fullName,
                    'school_id' => $intern->school_id,
                    'school_name' => $intern->school?->name ?? 'Sekolah Lain',
                    'division_name' => $intern->division?->name,
                ];
            })
            ->values();

        return response()->json($interns);
    }

    /**
     * Mengambil daftar intern berdasarkan satu sekolah (untuk kompatibilitas ke belakang)
     *
     * @param  \App\Models\School $school
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInternsBySchool(School $school)
    {
        $interns = $school->interns()
            ->with(['user.profile', 'division', 'school'])
            ->whereHas('user', function ($q) {
                $q->where('is_active', true);
            })
            ->get()
            ->filter(function ($intern) {
                // Kecualikan pemagang yang WFH penuh (sekolah remote atau wfh_only)
                $isRemoteSchool = (bool) ($intern->school?->is_remote_school ?? false);
                $isWfhFull = ($intern->work_placement_type === 'wfh_only');
                return !$isRemoteSchool && !$isWfhFull;
            })
            ->sortBy(function ($intern) {
                return $intern->user?->profile?->full_name ?? $intern->user?->username ?? '';
            })
            ->map(function ($intern) {
                $fullName = $intern->user?->profile?->full_name ?? $intern->user?->username ?? 'Tanpa Nama';
                $div = $intern->division ? ' • ' . $intern->division->name : '';
                return [
                    'id' => $intern->id,
                    'text' => $fullName . $div,
                    'name' => $fullName,
                    'school_id' => $intern->school_id,
                    'school_name' => $intern->school?->name ?? 'Sekolah Lain',
                    'division_name' => $intern->division?->name,
                ];
            })
            ->values();

        return response()->json($interns);
    }
}
