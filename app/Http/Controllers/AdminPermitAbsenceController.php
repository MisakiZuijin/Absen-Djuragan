<?php

namespace App\Http\Controllers;

use App\Models\DetailSchedule;
use App\Models\PermitReason;
use App\Models\PermitCategory;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPermitAbsenceController extends Controller
{
    /**
     * Display a listing of absence permits (Sakit, Izin Biasa, and Alpha).
     */
    public function index(Request $request): View
    {
        app(\App\Services\AttendanceService::class)->markMissedSchedulesAsAlpha();

        $categoryFilter = $request->query('category', 'all');
        $statusFilter = $request->query('status', 'all');
        $dateFilter = $request->query('date', '');
        $search = $request->query('search', '');

        // Base query: All detail schedules that are either Izin (3), Alpha (5), or have a permit reason
        $query = DetailSchedule::query()
            ->where(function ($q) {
                $q->whereIn('attd_status_id', [3, 5])
                  ->orWhere(function ($sub) {
                      $sub->whereNotNull('permit_reason_id')
                          ->where('permit_reason_id', '>', 0);
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

        // Search by intern name, division, or school
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

        // Filter by Date
        if (!empty($dateFilter)) {
            $query->whereDate('date', $dateFilter);
        }

        // Filter by Category
        if ($categoryFilter === 'sakit') {
            $query->where(function ($q) {
                $q->whereHas('permitReason', function ($pr) {
                    $pr->whereIn('permit_category_id', [1, 2])
                       ->orWhere('description', 'like', '%sakit%');
                });
            });
        } elseif ($categoryFilter === 'izin') {
            $query->where('attd_status_id', 3)
                ->where(function ($q) {
                    $q->whereHas('permitReason', function ($pr) {
                        $pr->whereNotIn('permit_category_id', [1, 2])
                           ->where('description', 'not like', '%sakit%');
                    })->orDoesntHave('permitReason');
                });
        } elseif ($categoryFilter === 'alpha') {
            $query->where('attd_status_id', 5);
        }

        // Filter by Status Ganti Jam
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

        // Calculate summary counts for cards
        $today = Carbon::today()->toDateString();
        $todayCount = DetailSchedule::whereDate('date', $today)
            ->where(function ($q) {
                $q->whereIn('attd_status_id', [3, 5])
                  ->orWhere('permit_reason_id', '>', 0);
            })->count();

        $sakitCount = DetailSchedule::where('attd_status_id', 3)
            ->whereHas('permitReason', function ($pr) {
                $pr->whereIn('permit_category_id', [1, 2])
                   ->orWhere('description', 'like', '%sakit%');
            })->count();

        $izinBiasaCount = DetailSchedule::where('attd_status_id', 3)
            ->where(function ($q) {
                $q->whereHas('permitReason', function ($pr) {
                    $pr->whereNotIn('permit_category_id', [1, 2])
                       ->where('description', 'not like', '%sakit%');
                })->orDoesntHave('permitReason');
            })->count();

        $alphaCount = DetailSchedule::where('attd_status_id', 5)->count();

        // Order and paginate
        $permits = $query->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $permitCategories = PermitCategory::all();

        return view('admin.izin-absen', compact(
            'permits',
            'todayCount',
            'sakitCount',
            'izinBiasaCount',
            'alphaCount',
            'categoryFilter',
            'statusFilter',
            'dateFilter',
            'search',
            'permitCategories'
        ));
    }

    /**
     * Approve permit as Lunas / Free from changing hours (e.g. Sakit with doctor note).
     */
    public function approveLunas(int $id): RedirectResponse
    {
        $detailSchedule = DetailSchedule::with('schedule.intern.user.profile')->findOrFail($id);
        $detailSchedule->update([
            'attd_status_id' => 3, // Izin
            'isChangeSchedule' => 1, // 1 = Bebas Ganti Jam / Lunas
            'is_change_schedule_approved' => 1,
        ]);

        $internName = $detailSchedule->schedule?->intern?->user?->profile?->full_name ?? 'Pemagang';
        $adminName = auth()->user()?->name ?? 'Admin';
        \App\Helper\ActivityLogger::log('APPROVE', 'Izin', "Admin {$adminName} menyetujui izin pemagang {$internName} (Bebas Ganti Jam / Lunas)", ['detail_schedule_id' => $id]);

        return redirect()->back()->with('success', 'Izin berhasil disetujui dengan status: Bebas Ganti Jam (Lunas).');
    }

    /**
     * Approve permit as Wajib Ganti Jam (e.g. Izin Biasa / Keperluan).
     */
    public function approveGantiJam(int $id): RedirectResponse
    {
        $detailSchedule = DetailSchedule::with('schedule.intern.user.profile')->findOrFail($id);
        $detailSchedule->update([
            'attd_status_id' => 3, // Izin
            'isChangeSchedule' => 2, // 2 = Wajib Ganti Jam
            'is_change_schedule_approved' => 0,
        ]);

        $internName = $detailSchedule->schedule?->intern?->user?->profile?->full_name ?? 'Pemagang';
        $adminName = auth()->user()?->name ?? 'Admin';
        \App\Helper\ActivityLogger::log('APPROVE', 'Izin', "Admin {$adminName} menetapkan izin pemagang {$internName}: Wajib Ganti Jam", ['detail_schedule_id' => $id]);

        return redirect()->back()->with('success', 'Izin berhasil disetujui dengan status: Wajib Ganti Jam.');
    }

    /**
     * Set status to Alpha / Tidak Hadir (automatically full hour debt).
     */
    public function setAlpha(int $id): RedirectResponse
    {
        $detailSchedule = DetailSchedule::with('schedule.intern.user.profile')->findOrFail($id);
        $detailSchedule->update([
            'attd_status_id' => 5, // Tidak Hadir / Alpha
            'isChangeSchedule' => 2, // Wajib Ganti Jam
            'is_change_schedule_approved' => 0,
        ]);

        $internName = $detailSchedule->schedule?->intern?->user?->profile?->full_name ?? 'Pemagang';
        $adminName = auth()->user()?->name ?? 'Admin';
        \App\Helper\ActivityLogger::log('UPDATE', 'Izin', "Admin {$adminName} menetapkan status pemagang {$internName} menjadi Alpha (Wajib Ganti Jam Penuh)", ['detail_schedule_id' => $id]);

        return redirect()->back()->with('success', 'Status berhasil diubah menjadi Alpha (Tidak Hadir). Otomatis masuk hutang jam kerja penuh.');
    }

    /**
     * Update permit details (note, proof URL, category).
     */
    public function updateDetail(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'proof_url' => [
                'nullable',
                'url',
                'max:500',
                Rule::requiredIf(in_array((int) $request->input('permit_category_id'), [3, 4], true)),
            ],
            'permit_category_id' => 'required|integer',
            'jam_option' => 'required|in:1,2',
        ], [
            'proof_url.required' => 'Link Google Drive bukti keperluan wajib diisi.',
        ]);

        $detailSchedule = DetailSchedule::with('schedule.intern.user.profile')->findOrFail($id);

        if ($detailSchedule->permit_reason_id && $detailSchedule->permit_reason_id > 0) {
            $permitReason = PermitReason::find($detailSchedule->permit_reason_id);
            if ($permitReason) {
                $permitReason->update([
                    'description' => $validated['description'],
                    'proof_url' => $validated['proof_url'],
                    'permit_category_id' => $validated['permit_category_id'],
                ]);
            }
        } else {
            $permitReason = PermitReason::create([
                'description' => $validated['description'],
                'proof_url' => $validated['proof_url'],
                'permit_category_id' => $validated['permit_category_id'],
            ]);
            $detailSchedule->permit_reason_id = $permitReason->id;
        }

        $detailSchedule->attd_status_id = 3; // Izin
        $detailSchedule->isChangeSchedule = (int) $validated['jam_option'];
        $detailSchedule->save();

        $internName = $detailSchedule->schedule?->intern?->user?->profile?->full_name ?? 'Pemagang';
        $adminName = auth()->user()?->name ?? 'Admin';
        \App\Helper\ActivityLogger::log('UPDATE', 'Izin', "Admin {$adminName} memperbarui keterangan izin pemagang {$internName}", ['detail_schedule_id' => $id]);

        return redirect()->back()->with('success', 'Data izin berhasil diperbarui.');
    }
}
