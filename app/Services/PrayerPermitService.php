<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Intern;
use App\Models\PermitLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PrayerPermitService
{
    /**
     * [DIPERBAIKI] Mengubah method untuk menerima parameter $perPage,
     * melakukan sorting di database, dan mengembalikan Paginator.
     *
     * @param int $perPage Jumlah item per halaman.
     * @return LengthAwarePaginator
     */
public function getTodayInternsWithPrayerPermits(int $perPage = 25): LengthAwarePaginator
{
    return Intern::query()
        ->with('user.profile', 'school', 'activePermitLog', 'division')
        ->whereHas('attendances', function ($query) {
            $query->whereDate('date', today());
        })
        ->whereHas('user', function ($query) {
            $query->where('is_active', true);
        })
        ->with(['attendances' => function ($query) {
            $query->whereDate('date', today())
                  ->with(['permitLogs' => function ($query) {
                      $query->where('type', 'prayer')
                            ->whereNull('end_time');
                  }]);
        }])
        ->orderBy(function ($query) {
            // Subquery untuk sorting
            $query->select(DB::raw('MAX(permit_logs.id IS NOT NULL)'))
                  ->from('attendances')
                  ->join('permit_logs', 'attendances.id', '=', 'permit_logs.attendance_id')
                  ->whereColumn('attendances.intern_id', 'interns.id')
                  ->whereDate('attendances.date', today())
                  ->where('permit_logs.type', 'prayer')
                  ->whereNull('permit_logs.end_time');
        }, 'desc')
        ->orderBy(function ($query) {
            // Subquery untuk sorting start_time
            $query->select('permit_logs.start_time')
                  ->from('attendances')
                  ->join('permit_logs', 'attendances.id', '=', 'permit_logs.attendance_id')
                  ->whereColumn('attendances.intern_id', 'interns.id')
                  ->whereDate('attendances.date', today())
                  ->where('permit_logs.type', 'prayer')
                  ->whereNull('permit_logs.end_time')
                  ->orderBy('permit_logs.start_time', 'asc')
                  ->limit(1);
        }, 'asc')
        ->orderBy('interns.id', 'asc')
        ->paginate($perPage);
}

    public function getPrayerDuration(PermitLog $permitLog)
    {
        Log::info('getPrayerDuration called', [
            'permit_id' => $permitLog->id,
            'type' => $permitLog->type,
            'start_time' => $permitLog->start_time,
            'end_time' => $permitLog->end_time
        ]);

        if ($permitLog->type !== 'prayer') {
            return [
                'error' => 'Not a prayer permit',
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

    public function getPrayerHistory(Intern $intern, $perPage = 15)
    {
        return PermitLog::whereHas('attendance', function ($query) use ($intern) {
                $query->where('intern_id', $intern->id);
            })
            ->where('type', 'prayer')
            ->latest('start_time')
            ->paginate($perPage);
    }
}
