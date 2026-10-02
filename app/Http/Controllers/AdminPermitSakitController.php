<?php

namespace App\Http\Controllers;

use App\Models\DetailSchedule;
use App\Models\PermitReason;
use App\Models\PermitCategory;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPermitSakitController extends Controller
{
    /**
     * Display a listing of sick leave permits (Izin Sakit).
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', 'all');
        $dateFilter = $request->query('date', '');
        $period = $request->query('period', 'today'); // 'today' (default) atau 'all'
        $search = $request->query('search', '');

        // Query DetailSchedule that are Sick leave
        $query = DetailSchedule::query()
            ->where('attd_status_id', 3)
            ->where(function ($q) {
                $q->whereHas('permitReason', function ($pr) {
                    $pr->whereIn('permit_category_id', [1, 2])
                       ->orWhere('description', 'like', '%sakit%');
                });
            })
            ->with([
                'schedule.intern.user.profile',
                'schedule.intern.division',
                'schedule.intern.school',
                'shift',
                'attdStatus',
                'permitReason.category',
            ]);

        // Search filter
        if (!empty($search)) {
            $query->whereHas('schedule.intern', function ($internQuery) use ($search) {
                $internQuery->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('username', 'like', "%{$search}%")
                        ->orWhereHas('profile', function ($profileQuery) use ($search) {
                            $profileQuery->where('full_name', 'like', "%{$search}%");
                        });
                })->orWhereHas('division', function ($divQuery) use ($search) {
                    $divQuery->where('name', 'like', "%{$search}%");
                })->orWhereHas('school', function ($schoolQuery) use ($search) {
                    $schoolQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Date / Period filter
        $today = Carbon::today()->toDateString();
        if (!empty($dateFilter)) {
            $query->whereDate('date', $dateFilter);
            $period = 'custom';
        } elseif ($period === 'today') {
            $query->whereDate('date', $today);
        }

        // Status Ganti Jam filter
        if ($statusFilter === 'lunas') {
            $query->where('isChangeSchedule', 1);
        } elseif ($statusFilter === 'ganti_jam') {
            $query->where('isChangeSchedule', 2);
        } elseif ($statusFilter === 'pending') {
            $query->where(function ($q) {
                $q->whereNull('isChangeSchedule')
                  ->orWhere('isChangeSchedule', 0);
            });
        }

        // Calculate summary statistics — scoped to active period/date filter
        $sickCondition = function ($q) {
            $q->where('attd_status_id', 3)
              ->whereHas('permitReason', function ($pr) {
                  $pr->whereIn('permit_category_id', [1, 2])
                     ->orWhere('description', 'like', '%sakit%');
              });
        };

        $applyDateScope = function ($q) use ($dateFilter, $period, $today) {
            if (!empty($dateFilter)) {
                $q->whereDate('date', $dateFilter);
            } elseif ($period === 'today') {
                $q->whereDate('date', $today);
            }
            // period = 'all' → no date filter
        };

        $totalSakit = DetailSchedule::where(function ($q) use ($sickCondition) {
            $sickCondition($q);
        })->where(function ($q) use ($applyDateScope) {
            $applyDateScope($q);
        })->count();

        $lunasCount = DetailSchedule::where(function ($q) use ($sickCondition) {
            $sickCondition($q);
        })->where('isChangeSchedule', 1)->where(function ($q) use ($applyDateScope) {
            $applyDateScope($q);
        })->count();

        $pendingCount = DetailSchedule::where(function ($q) use ($sickCondition) {
            $sickCondition($q);
        })->where(function ($q) {
            $q->whereNull('isChangeSchedule')
              ->orWhere('isChangeSchedule', 0);
        })->where(function ($q) use ($applyDateScope) {
            $applyDateScope($q);
        })->count();

        // Dynamic label for period context
        $periodLabel = match (true) {
            !empty($dateFilter) => Carbon::parse($dateFilter)->translatedFormat('d M Y'),
            $period === 'today' => 'Hari Ini',
            default => 'Semua Periode',
        };

        $permits = $query->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(5)
            ->withQueryString();

        return view('admin.izin-sakit', compact(
            'permits',
            'totalSakit',
            'lunasCount',
            'pendingCount',
            'statusFilter',
            'dateFilter',
            'period',
            'periodLabel',
            'search'
        ));
    }

    /**
     * Approve sick leave as Bebas Ganti Jam (Lunas).
     */
    public function approveLunas(int $id): RedirectResponse
    {
        $detailSchedule = DetailSchedule::with(['schedule.intern.user.profile', 'shift', 'attendance'])->findOrFail($id);
        $detailSchedule->update([
            'attd_status_id' => 3, // Izin
            'isChangeSchedule' => 1, // Bebas Ganti Jam (Lunas)
            'is_change_schedule_approved' => 1,
        ]);

        \App\Helper\TimeHelper::syncValidPermitAttendance($detailSchedule, $detailSchedule->shift);

        $internName = $detailSchedule->schedule?->intern?->user?->name ?? 'Pemagang';
        \App\Helper\ActivityLogger::log(
            'APPROVE',
            'Izin & Cuti',
            "Admin menyetujui Izin Sakit pemagang {$internName} sebagai Bebas Ganti Jam (Lunas)",
            ['detail_schedule_id' => $id]
        );

        return redirect()->back()->with('success', 'Izin Sakit berhasil disetujui: Bebas Ganti Jam (Lunas). Jam masuk dan pulang disesuaikan jadwal shift pemagang.');
    }

    /**
     * Set sick leave as Wajib Ganti Jam (Hutang Jam).
     */
    public function setWajibGantiJam(int $id): RedirectResponse
    {
        $detailSchedule = DetailSchedule::with('schedule.intern.user.profile')->findOrFail($id);
        $detailSchedule->update([
            'attd_status_id' => 3, // Tetap Izin Sakit (Bukan Alpha)
            'isChangeSchedule' => 2, // Wajib Ganti Jam / Hutang Jam
            'is_change_schedule_approved' => 0,
        ]);

        \App\Helper\TimeHelper::syncValidPermitAttendance($detailSchedule, $detailSchedule->shift);

        $internName = $detailSchedule->schedule?->intern?->user?->name ?? 'Pemagang';
        \App\Helper\ActivityLogger::log(
            'UPDATE',
            'Izin & Cuti',
            "Admin menetapkan Izin Sakit pemagang {$internName} sebagai Wajib Ganti Jam",
            ['detail_schedule_id' => $id]
        );

        return redirect()->back()->with('success', 'Status Izin Sakit berhasil ditetapkan sebagai Wajib Ganti Jam (Hutang Jam). Pemagang wajib mengganti jam shift.');
    }

    /**
     * Alias for backward compatibility.
     */
    public function rejectToAlpha(int $id): RedirectResponse
    {
        return $this->setWajibGantiJam($id);
    }

    /**
     * Update sick leave detail.
     */
    public function updateDetail(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'proof_url' => 'nullable|url|max:500',
            'jam_option' => 'required|in:1,2',
        ]);

        $detailSchedule = DetailSchedule::findOrFail($id);

        if ($detailSchedule->permit_reason_id && $detailSchedule->permit_reason_id > 0) {
            $permitReason = PermitReason::find($detailSchedule->permit_reason_id);
            if ($permitReason) {
                $permitReason->update([
                    'description' => $validated['description'],
                    'proof_url' => $validated['proof_url'],
                ]);
            }
        } else {
            $permitReason = PermitReason::create([
                'description' => $validated['description'],
                'proof_url' => $validated['proof_url'],
                'permit_category_id' => 1,
            ]);
            $detailSchedule->permit_reason_id = $permitReason->id;
        }

        $detailSchedule->attd_status_id = 3;
        $detailSchedule->isChangeSchedule = (int) $validated['jam_option'];
        $detailSchedule->is_change_schedule_approved = ((int) $validated['jam_option'] === 1) ? 1 : 0;
        $detailSchedule->save();

        if ((int) $detailSchedule->attd_status_id === 3) {
            \App\Helper\TimeHelper::syncValidPermitAttendance($detailSchedule, $detailSchedule->shift);
        }

        return redirect()->back()->with('success', 'Data Izin Sakit berhasil diperbarui.');
    }
}
