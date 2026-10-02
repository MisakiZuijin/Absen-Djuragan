<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model {
    use HasFactory;

    const CREATED_AT = "date";
    const UPDATED_AT = null;

    protected $casts = [
        'permit_type' => 'string',
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'adjusted_end_time' => 'datetime',
        'is_auto_end' => 'boolean',
        'auto_end_notified' => 'boolean',
        'is_debt_fulfilled' => 'boolean',
        'debt_fulfilled_at' => 'datetime',
    ];

    protected $fillable = [
        "intern_id",
        "shift_id",
        "date",
        "start_time",
        "break_time",
        "back_time",
        "permit_start",
        "permit_back",
        "end_time",
        "adjusted_end_time",
        "checkout_notes",
        "total_min",
        "total_break_min",
        "total_permit_min",
        "start_time_message",
        "break_time_message",
        "back_time_message",
        "permit_start_message",
        "permit_back_message",
        "end_time_message",
        "is_permit",
        "description",
        "latitude_start",
        "longitude_start",
        "latitude_end",
        "longitude_end",
        "keterangan",
        "permit_type",
        "authorized_by",
        "proof_link",
        "is_auto_end",
        "auto_end_note",
        "auto_end_notified",
        "is_debt_fulfilled",
        "debt_fulfilled_session_id",
        "debt_fulfilled_at",
    ];

    public function detailSchedules(): HasOne {
        return $this->hasOne(DetailSchedule::class, 'attendance_id', 'id');
    }

    public function intern(): BelongsTo {
        return $this->belongsTo(Intern::class);
    }

    public function debtFulfilledSession(): BelongsTo {
        return $this->belongsTo(ChangeTimeSession::class, 'debt_fulfilled_session_id');
    }

    public function changeTimeSessionTargets(): HasMany {
        return $this->hasMany(ChangeTimeSessionTarget::class, 'attendance_id');
    }

    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            Intern::class,
            'id',
            'id',
            'intern_id',
            'user_id'
        );
    }

    public function permitLogs()
    {
        return $this->hasMany(PermitLog::class);
    }

    public function lateAbsences(): HasMany
    {
        return $this->hasMany(LateAbsence::class);
    }

    public function latestLateAbsence(): HasOne
    {
        return $this->hasOne(LateAbsence::class)->latestOfMany();
    }

    public function isLate(): bool
    {
        if ($this->relationLoaded('lateAbsences')) {
            return $this->lateAbsences->isNotEmpty();
        }
        return $this->lateAbsences()->exists();
    }

    public function getTotalLateMinutes(): int
    {
        if ($this->relationLoaded('lateAbsences')) {
            return (int) $this->lateAbsences->sum('late_minutes');
        }
        return (int) $this->lateAbsences()->sum('late_minutes');
    }

    public function isConsideredLate(): bool
    {
        if ($this->relationLoaded('lateAbsences')) {
            return $this->lateAbsences->where('late_minutes', '>', 0)->isNotEmpty();
        }
        return $this->lateAbsences()->where('late_minutes', '>', 0)->exists();
    }

    // METHOD BARU: Cek apakah bisa checkout
    public function canCheckOut(): bool
    {
        // Jika sudah checkout, tidak bisa lagi
        if ($this->end_time) {
            return false;
        }

        $now = Carbon::now('Asia/Jakarta');

        // Gunakan adjusted_end_time jika ada, jika tidak gunakan shift end_time
        $expectedEndTime = $this->getExpectedEndTime();

        if (!$expectedEndTime) {
            return false;
        }

        try {
            // Normalisasi format jam pulang jika bertipe datetime/Carbon
            $endTimeStr = $expectedEndTime instanceof \DateTimeInterface
                ? $expectedEndTime->format('H:i:s')
                : (string) $expectedEndTime;

            // Gabungkan dengan tanggal attendance untuk perbandingan
            $attendanceDate = $this->date ? Carbon::parse($this->date)->format('Y-m-d') : today('Asia/Jakarta')->format('Y-m-d');
            $expectedDateTime = Carbon::parse($attendanceDate . ' ' . $endTimeStr, 'Asia/Jakarta');

            // Cek apakah shift melewati midnight (end_time < start_time)
            $shift = $this->detailSchedules?->shift ?? $this->intern?->shift;
            if ($shift && $shift->start_time && $shift->end_time && $shift->end_time < $shift->start_time) {
                // Shift malam melewati tengah malam: jam pulang berada di hari berikutnya (+1 day)
                $expectedDateTime = $expectedDateTime->addDay();
            }

            return $now->greaterThanOrEqualTo($expectedDateTime);
        } catch (\Exception $e) {
            Log::error('Error checking checkout time: ' . $e->getMessage());
            return false;
        }
    }


    // METHOD BARU: Dapatkan jam pulang yang diharapkan
    public function getExpectedEndTime()
    {
        // Prioritaskan adjusted_end_time dari perhitungan keterlambatan
        if ($this->adjusted_end_time) {
            return $this->adjusted_end_time;
        }

        // Fallback ke shift dari detailSchedules atau relasi intern shift
        $shift = $this->detailSchedules?->shift ?? $this->intern?->shift;
        if ($shift && $shift->end_time) {
            return $shift->end_time;
        }

        return null;
    }

    // METHOD BARU: Cek apakah ada penyesuaian jam pulang
    public function hasAdjustedEndTime(): bool
    {
        return !empty($this->adjusted_end_time);
    }

    // METHOD BARU: Hitung total menit kerja
    public function getTotalWorkMinutes(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        return $start->diffInMinutes($end);
    }
}
