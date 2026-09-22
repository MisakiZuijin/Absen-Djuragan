<?php

namespace App\Http\Controllers;

use App\Models\DetailSchedule;
use App\Models\PermitReason;
use App\Models\PermitCategory;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPermitKeperluanController extends Controller
{
    /**
     * Display a listing of non-medical absence permits (Izin Biasa / Keperluan) and Alpha records.
     */
    public function index(Request $request): View
    {
        app(\App\Services\AttendanceService::class)->markMissedSchedulesAsAlpha();

        $typeFilter = $request->query('type', 'all'); // 'all', 'izin', 'alpha'
        $statusFilter = $request->query('status', 'all'); // 'all', 'ganti_jam', 'lunas', 'pending'
        $dateFilter = $request->query('date', '');
        $period = $request->query('period', 'today'); // 'today' (default) atau 'all'
        $search = $request->query('search', '');

        // Query: Records that are either general permission (not sick) or alpha
        $query = DetailSchedule::query()
            ->where(function ($q) {
                // Alpha records
                $q->where('attd_status_id', 5)
                    // Or non-medical izin
                    ->orWhere(function ($sub) {
                        $sub->where('attd_status_id', 3)
                            ->where(function ($notSick) {
                                $notSick->whereHas('permitReason', function ($pr) {
                                    $pr->whereNotIn('permit_category_id', [1, 2])
                                        ->where('description', 'not like', '%sakit%');
                                })->orDoesntHave('permitReason');
                            });
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
                    $userQuery->where('name', 'like', "%{$search}%")
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

        // Type filter
        if ($typeFilter === 'izin') {
            $query->where('attd_status_id', 3);
        } elseif ($typeFilter === 'alpha') {
            $query->where('attd_status_id', 5);
        }

        // Status Ganti Jam filter
        if ($statusFilter === 'ganti_jam') {
            $query->where('isChangeSchedule', 2);
        } elseif ($statusFilter === 'lunas') {
            $query->where('isChangeSchedule', 1);
        } elseif ($statusFilter === 'pending') {
            $query->where('attd_status_id', 3)
                ->where(function ($q) {
                    $q->whereNull('isChangeSchedule')
                        ->orWhere('isChangeSchedule', 0);
                });
        }

        // Calculate statistics
        $today = Carbon::today()->toDateString();
        $totalIzin = DetailSchedule::where('attd_status_id', 3)
            ->where(function ($q) {
                $q->whereHas('permitReason', function ($pr) {
                    $pr->whereNotIn('permit_category_id', [1, 2])
                        ->where('description', 'not like', '%sakit%');
                })->orDoesntHave('permitReason');
            })->count();

        $todayCount = DetailSchedule::whereDate('date', $today)
            ->where(function ($q) {
                $q->where('attd_status_id', 5)
                    ->orWhere(function ($sub) {
                        $sub->where('attd_status_id', 3)
                            ->where(function ($notSick) {
                                $notSick->whereHas('permitReason', function ($pr) {
                                    $pr->whereNotIn('permit_category_id', [1, 2])
                                        ->where('description', 'not like', '%sakit%');
                                })->orDoesntHave('permitReason');
                            });
                    });
            })->count();

        $gantiJamCount = DetailSchedule::where('attd_status_id', 3)
            ->where('isChangeSchedule', 2)
            ->where(function ($q) {
                $q->whereHas('permitReason', function ($pr) {
                    $pr->whereNotIn('permit_category_id', [1, 2])
                        ->where('description', 'not like', '%sakit%');
                })->orDoesntHave('permitReason');
            })->count();

        $alphaCount = DetailSchedule::where('attd_status_id', 5)->count();

        $permits = $query->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(5)
            ->withQueryString();

        return view('admin.izin-keperluan', compact(
            'permits',
            'totalIzin',
            'todayCount',
            'gantiJamCount',
            'alphaCount',
            'typeFilter',
            'statusFilter',
            'dateFilter',
            'period',
            'search'
        ));
    }

    /**
     * Approve permit as Wajib Ganti Jam.
     */
    public function approveGantiJam(int $id): RedirectResponse
    {
        $detailSchedule = DetailSchedule::findOrFail($id);
        $detailSchedule->update([
            'attd_status_id' => 3, // Izin
            'isChangeSchedule' => 2, // Wajib Ganti Jam
            'is_change_schedule_approved' => 0,
        ]);

        return redirect()->back()->with('success', 'Izin Keperluan disetujui dengan status: Wajib Ganti Jam.');
    }

    /**
     * Grant special dispensation as Bebas Ganti Jam (Lunas).
     */
    public function approveLunas(int $id): RedirectResponse
    {
        $detailSchedule = DetailSchedule::findOrFail($id);
        $detailSchedule->update([
            'attd_status_id' => 3, // Izin
            'isChangeSchedule' => 1, // Bebas Ganti Jam (Lunas)
            'is_change_schedule_approved' => 1,
        ]);

        return redirect()->back()->with('success', 'Dispensasi berhasil: Izin diberikan Bebas Ganti Jam (Lunas).');
    }

    /**
     * Set/Confirm status as Alpha (full debt).
     */
    public function setAlpha(int $id): RedirectResponse
    {
        $detailSchedule = DetailSchedule::findOrFail($id);
        $detailSchedule->update([
            'attd_status_id' => 5, // Tidak Hadir / Alpha
            'isChangeSchedule' => 2, // Wajib Ganti Jam
            'is_change_schedule_approved' => 0,
        ]);

        return redirect()->back()->with('success', 'Status ditetapkan sebagai Alpha (Tidak Hadir). Otomatis masuk hutang jam kerja penuh.');
    }

    /**
     * Update detail.
     */
    public function updateDetail(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'proof_url' => 'required|url|max:500',
            'jam_option' => 'required|in:1,2',
            'status_id' => 'nullable|in:3,5',
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
                'permit_category_id' => 3, // Keperluan
            ]);
            $detailSchedule->permit_reason_id = $permitReason->id;
        }

        if (!empty($validated['status_id'])) {
            $detailSchedule->attd_status_id = (int) $validated['status_id'];
        }
        $detailSchedule->isChangeSchedule = (int) $validated['jam_option'];
        $detailSchedule->is_change_schedule_approved = ((int) $validated['jam_option'] === 1) ? 1 : 0;
        $detailSchedule->save();

        return redirect()->back()->with('success', 'Data Izin Tidak Hadir berhasil diperbarui dengan rincian opsi yang dipilih.');
    }
}
