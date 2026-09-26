<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Intern;
use App\Models\PermitLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LeavePermitService
{
    /**
     * Mengambil daftar pemagang yang hadir hari ini dengan data izin keluar hari ini.
     *
     * @param int $perPage Jumlah item per halaman.
     * @param string|null $statusFilter Filter status ('all', 'sedang_keluar', 'sudah_kembali', 'wajib_ganti', 'bebas_waktu')
     * @param string|null $search Pencarian nama pemagang, divisi, sekolah
     * @return LengthAwarePaginator
     */
    public function getTodayInternsWithLeavePermits(int $perPage = 25, ?string $statusFilter = null, ?string $search = null): LengthAwarePaginator
    {
        $query = Intern::query()
            ->with([
                'user.profile',
                'school',
                'division',
                'todayLeavePermits',
            ])
            ->whereHas('attendances', function ($q) {
                $q->whereDate('date', today());
            })
            ->whereHas('user', function ($q) {
                $q->where('is_active', true);
            });

        // Filter Pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhereHas('profile', function ($profQuery) use ($search) {
                            $profQuery->where('full_name', 'like', "%{$search}%");
                        });
                })
                ->orWhereHas('division', function ($divQuery) use ($search) {
                    $divQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('school', function ($schQuery) use ($search) {
                    $schQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Subquery join untuk mengidentifikasi keberadaan izin keluar hari ini
        $query->leftJoin('attendances', function ($join) {
            $join->on('interns.id', '=', 'attendances.intern_id')
                 ->whereDate('attendances.date', today());
        })
        ->leftJoin('permit_logs', function ($join) {
            $join->on('attendances.id', '=', 'permit_logs.attendance_id')
                ->where('permit_logs.type', '=', 'leave')
                ->whereDate('permit_logs.start_time', today());
        })
        ->select(
            'interns.*',
            DB::raw('MAX(CASE WHEN permit_logs.id IS NOT NULL AND permit_logs.end_time IS NULL THEN 1 ELSE 0 END) AS is_active_leave'),
            DB::raw('MAX(CASE WHEN permit_logs.id IS NOT NULL THEN 1 ELSE 0 END) AS has_leave_today'),
            DB::raw('MAX(permit_logs.start_time) AS latest_leave_time'),
            DB::raw('MAX(CASE WHEN permit_logs.is_mandatory_replace = 1 AND permit_logs.approval_status = "approved" THEN 1 ELSE 0 END) AS is_wajib_ganti'),
            DB::raw('MAX(CASE WHEN permit_logs.is_mandatory_replace = 0 AND permit_logs.approval_status = "approved" THEN 1 ELSE 0 END) AS is_bebas_waktu')
        )
        ->groupBy('interns.id');

        // Filter tab status
        if ($statusFilter === 'active' || $statusFilter === 'sedang_keluar') {
            $query->having('is_active_leave', '=', 1);
        } elseif ($statusFilter === 'completed' || $statusFilter === 'sudah_kembali') {
            $query->having('is_active_leave', '=', 0)->having('has_leave_today', '=', 1);
        } elseif ($statusFilter === 'wajib_ganti') {
            $query->having('is_wajib_ganti', '=', 1);
        } elseif ($statusFilter === 'bebas_waktu') {
            $query->having('is_bebas_waktu', '=', 1);
        } elseif ($statusFilter === 'pending') {
            $query->having('has_leave_today', '=', 1)->having('is_wajib_ganti', '=', 0)->having('is_bebas_waktu', '=', 0);
        }

        return $query
            ->orderBy('is_active_leave', 'desc')
            ->orderBy('has_leave_today', 'desc')
            ->orderBy('latest_leave_time', 'desc')
            ->orderBy('interns.id', 'asc')
            ->paginate($perPage);
    }

    /**
     * Mengambil ringkasan metrik statistik izin keluar hari ini
     *
     * @return array
     */
    public function getSummaryMetrics(): array
    {
        $todayAttendanceIds = \App\Models\Attendance::whereDate('date', today())->pluck('id');
        $leaveLogs = PermitLog::whereIn('attendance_id', $todayAttendanceIds)
            ->where('type', 'leave')
            ->whereDate('start_time', today())
            ->get();

        $activeAttendanceIds = $leaveLogs->whereNull('end_time')->pluck('attendance_id')->unique()->toArray();
        $activeCount = count($activeAttendanceIds);
        $completedCount = $leaveLogs->whereNotNull('end_time')->whereNotIn('attendance_id', $activeAttendanceIds)->pluck('attendance_id')->unique()->count();
        $wajibGantiCount = $leaveLogs->where('is_mandatory_replace', true)->where('approval_status', 'approved')->pluck('attendance_id')->unique()->count();
        $bebasWaktuCount = $leaveLogs->where('is_mandatory_replace', false)->where('approval_status', 'approved')->pluck('attendance_id')->unique()->count();
        $pendingCount = $leaveLogs->where('approval_status', 'pending')->pluck('attendance_id')->unique()->count();

        return [
            'total_active' => $activeCount,
            'total_completed' => $completedCount,
            'total_wajib_ganti' => $wajibGantiCount,
            'total_bebas_waktu' => $bebasWaktuCount,
            'total_pending' => $pendingCount,
            'total_leave_today' => $leaveLogs->pluck('attendance_id')->unique()->count(),
        ];
    }

    /**
     * Mengambil durasi real-time untuk izin keluar.
     *
     * @param PermitLog $permitLog
     * @return array
     */
    public function getLeaveDuration(PermitLog $permitLog): array
    {
        if ($permitLog->type !== 'leave') {
            return [
                'error' => 'Not a leave permit',
                'duration' => '00:00:00',
                'is_active' => false
            ];
        }

        $startedAt = Carbon::parse($permitLog->start_time);
        $endedAt = $permitLog->end_time ? Carbon::parse($permitLog->end_time) : now();
        $duration = $startedAt->diff($endedAt);
        $durationString = sprintf('%02d:%02d:%02d', $duration->h, $duration->i, $duration->s);

        return [
            'duration' => $durationString,
            'duration_minutes' => $permitLog->duration_in_minutes ?? (int) ceil($startedAt->diffInSeconds($endedAt) / 60),
            'is_active' => is_null($permitLog->end_time)
        ];
    }

    /**
     * Mengambil riwayat izin keluar seorang pemagang dengan paginasi.
     *
     * @param Intern $intern
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getLeaveHistory(Intern $intern, int $perPage = 15)
    {
        return PermitLog::whereHas('attendance', function ($query) use ($intern) {
                $query->where('intern_id', $intern->id);
            })
            ->where('type', 'leave')
            ->latest('start_time')
            ->paginate($perPage);
    }
}
