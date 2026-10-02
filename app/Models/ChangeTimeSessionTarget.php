<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeTimeSessionTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'change_time_session_id',
        'detail_schedule_id',
        'attendance_id',
        'debt_minutes',
        'paid_minutes',
        'is_fulfilled',
    ];

    protected $casts = [
        'debt_minutes' => 'integer',
        'paid_minutes' => 'integer',
        'is_fulfilled' => 'boolean',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChangeTimeSession::class, 'change_time_session_id');
    }

    public function detailSchedule(): BelongsTo
    {
        return $this->belongsTo(DetailSchedule::class, 'detail_schedule_id');
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }
}
