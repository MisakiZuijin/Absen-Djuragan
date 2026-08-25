<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Intern;
use App\Models\PermitLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LeavePermitService
{
    /**
     * [DIPERBAIKI] Mengubah join untuk mencocokkan struktur database yang benar:
     * Intern -> Attendance -> PermitLog.
     *
     * @param int $perPage Jumlah item per halaman.
     * @return LengthAwarePaginator
     */
public function getTodayInternsWithLeavePermits(int $perPage = 25): LengthAwarePaginator
{
    return Intern::query()
        ->with('user.profile', 'school', 'activePermitLog', 'division')
        ->whereHas('attendances', function ($query) {
            $query->whereDate('date', today());
        })
        ->whereHas('user', function ($query) {
            $query->where('is_active', true);
        })
        ->leftJoin('attendances', function ($join) {
            $join->on('interns.id', '=', 'attendances.intern_id')
                 ->whereDate('attendances.date', today());
        })
        ->leftJoin('permit_logs', function ($join) {
            $join->on('attendances.id', '=', 'permit_logs.attendance_id')
                ->where('permit_logs.type', '=', 'leave')
                ->whereNull('permit_logs.end_time');
        })
        // Select all interns columns plus the permit_logs columns needed for ordering
        ->select('interns.*',
            DB::raw('permit_logs.id as permit_log_id'),
            DB::raw('permit_logs.start_time as permit_start_time'),
            DB::raw('permit_logs.id IS NOT NULL as has_active_leave') // Add this for proper ordering
        )
        ->distinct()
        // Order by the aliased columns
        ->orderBy('has_active_leave', 'desc')
        ->orderBy('permit_start_time', 'asc')
        ->orderBy('interns.id', 'asc')
        ->paginate($perPage);
}
    public function getLeaveDuration(PermitLog $permitLog): array
    {
        Log::info('getLeaveDuration called', [
            'permit_id' => $permitLog->id,
            'type' => $permitLog->type,
            'start_time' => $permitLog->start_time,
            'end_time' => $permitLog->end_time
        ]);

        if ($permitLog->type !== 'leave') {
            return [
                'error' => 'Not a leave permit',
                'duration' => '00:00:00',
                'is_active' => false
            ];
        }

        if ($permitLog->end_time) {
            return [
                'duration' => '00:00:00',
                'is_active' => false
            ];
        }

        $startedAt = Carbon::parse($permitLog->start_time);
        $duration = $startedAt->diff(now());
        $durationString = sprintf('%02d:%02d:%02d', $duration->h, $duration->i, $duration->s);

        return [
            'duration' => $durationString,
            'is_active' => true
        ];
    }

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
