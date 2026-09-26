<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class HandRaise extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'type',
        'presentation_mode',
        'meet_url',
        'presentation_date',
        'scheduled_time',
        'status',
        'notes',
        'performance_rating',
        'performance_notes',
        'admin_response',
        'reason',
        'resolved_at',
        'resolved_by',
        'is_raised',
    ];

    protected $casts = [
        'is_raised' => 'boolean',
        'presentation_date' => 'date',
        'resolved_at' => 'datetime',
        'performance_rating' => 'float',
    ];

    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('navbar_raise_hand_count');
        });
        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('navbar_raise_hand_count');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Dapatkan model Shift yang berlaku untuk pemagang pada pengajuan ini.
     */
    public function getEffectiveShiftAttribute()
    {
        $intern = $this->user?->intern;
        if (!$intern) {
            return null;
        }

        // 1. Shift dari jadwal hari ini (todayDetailSchedule)
        if ($intern->relationLoaded('todayDetailSchedule') && $intern->todayDetailSchedule?->shift) {
            return $intern->todayDetailSchedule->shift;
        }

        // 2. Shift master langsung pada intern
        if ($intern->relationLoaded('shift') && $intern->shift) {
            return $intern->shift;
        }

        // 3. Shift dari master schedule intern
        if ($intern->relationLoaded('schedules') && $intern->schedules->first()?->shift) {
            return $intern->schedules->first()->shift;
        }

        // 4. Jika ada presentation_date yang bukan hari ini, cari di schedules yang sudah di-load
        if ($this->presentation_date && !$this->presentation_date->isToday()) {
            if ($intern->relationLoaded('schedules') && $intern->schedules->isNotEmpty()) {
                foreach ($intern->schedules as $sched) {
                    if ($sched->relationLoaded('detailSchedules')) {
                        $match = $sched->detailSchedules->firstWhere('date', $this->presentation_date->toDateString());
                        if ($match && $match->shift) {
                            return $match->shift;
                        }
                    }
                }
            }

            // Hanya query jika relasi belum di-load sama sekali
            if (!$intern->relationLoaded('schedules') && !$intern->relationLoaded('todayDetailSchedule')) {
                $pDateShift = DetailSchedule::whereHas('schedule', function ($q) use ($intern) {
                    $q->where('intern_id', $intern->id);
                })->whereDate('date', $this->presentation_date)->with('shift')->first()?->shift;

                if ($pDateShift) {
                    return $pDateShift;
                }
            }
        }

        // 5. Fallback hanya jika relasi belum di-eager-load sama sekali (single record)
        if (!$intern->relationLoaded('todayDetailSchedule') && !$intern->relationLoaded('shift') && !$intern->relationLoaded('schedules')) {
            return $intern->todayDetailSchedule?->shift
                ?? $intern->shift
                ?? $intern->schedules()->with('shift')->first()?->shift;
        }

        return null;
    }

    /**
     * Dapatkan teks waktu shift pemagang yang siap ditampilkan (misal: "Middle (09:00 - 17:00)").
     */
    public function getShiftTextAttribute(): ?string
    {
        $shift = $this->effective_shift;
        if (!$shift) {
            return null;
        }

        $name = $shift->name;
        $start = $shift->start_time ? \Carbon\Carbon::parse($shift->start_time)->format('H:i') : null;
        $end = $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('H:i') : null;

        if ($start && $end) {
            return "{$name} ({$start} - {$end})";
        }

        return $name;
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_raised', true);
    }

    public function scopeQuestions(Builder $query)
    {
        return $query->where('type', 'question');
    }

    public function scopeNewTasks(Builder $query)
    {
        return $query->where('type', 'new_task');
    }

    public function scopePresentations(Builder $query)
    {
        return $query->where('type', 'presentation');
    }

    public function scopeDone(Builder $query)
    {
        return $query->where('status', 'done')
            ->orWhere(function ($q) {
                $q->where('is_raised', false)->whereNotNull('resolved_at');
            });
    }
}
