<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LateAbsence extends Model
{
    use HasFactory;

    protected $fillable = [
        'intern_id',
        'shift_id',
        'attendance_id',
        'date',
        'absen_time',
        'original_absen_time',
        'original_end_time',
        'scheduled_time',
        'late_minutes',
        'status',
        'notes',
        'reviewed_at',
        'reviewed_by',
        'type'
    ];

    protected $casts = [
        'absen_time' => 'datetime',
        'original_absen_time' => 'datetime',
        'reviewed_at' => 'datetime',
        'date' => 'date',
        'late_minutes' => 'integer',
    ];

    public function intern()
    {
        return $this->belongsTo(Intern::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Method untuk mengecek apakah waktu sudah disesuaikan
    public function isTimeAdjusted(): bool
    {
        return !empty($this->original_absen_time);
    }

    // Method untuk mendapatkan waktu absen asli
    public function getOriginalAbsenTime()
    {
        return $this->original_absen_time ?: $this->absen_time;
    }

    // METHOD BARU: Cek apakah overtime sudah diterapkan di adjusted_end_time
    public function isOvertimeApplied(): bool
    {
        if (!$this->attendance || !$this->shift) {
            return false;
        }

        return !empty($this->attendance->adjusted_end_time);
    }

    // METHOD BARU: Hitung jam pulang yang seharusnya setelah penyesuaian
    public function calculateExpectedEndTime()
    {
        if (!$this->shift || !$this->shift->end_time) {
            return null;
        }

        $normalEnd = Carbon::parse($this->shift->end_time);
        return $normalEnd->addMinutes($this->late_minutes)->format('H:i:s');
    }
}
