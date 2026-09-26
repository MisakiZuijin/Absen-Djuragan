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
                return $item->schedule->intern->user->profile->full_name ?? $item->schedule->intern->user->name ?? '';
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
        $schools = School::orderBy('name')->get();
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

            // Cari dan update semua DetailSchedule untuk intern yang dipilih dalam rentang tanggal secara massal
            $query = DetailSchedule::whereHas('schedule', function ($query) use ($internIds) {
                $query->whereIn('intern_id', $internIds);
            })->whereBetween('date', [$startDate, $endDate]);

            $updatedCount = $query->update(['shift_id' => $shiftId]);

            if ($updatedCount === 0) {
                return redirect()->back()->with('error', 'Tidak ada jadwal yang ditemukan untuk intern yang dipilih dalam rentang tanggal tersebut.');
            }

            $adminName = auth()->user()?->name ?? 'Admin';
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
            ->with(['user.profile', 'division'])
            ->whereHas('user', function ($q) {
                $q->where('is_active', true);
            })
            ->get()
            ->map(function ($intern) {
                $div = $intern->division ? ' • ' . $intern->division->name : '';
                return [
                    'id' => $intern->id,
                    'text' => ($intern->user->profile->full_name ?? $intern->user->name) . $div
                ];
            });

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
            ->with(['user.profile', 'division'])
            ->whereHas('user', function ($q) {
                $q->where('is_active', true);
            })
            ->get()
            ->map(function ($intern) {
                $div = $intern->division ? ' • ' . $intern->division->name : '';
                return [
                    'id' => $intern->id,
                    'text' => ($intern->user->profile->full_name ?? $intern->user->name) . $div
                ];
            });

        return response()->json($interns);
    }
}
