<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfflineAttendance extends Model
{
    use HasFactory;

    protected $table = 'offline_attendances';

    protected $fillable = [
        'intern_id',
        'shift_id',
        'office_id',
        'date',
        'status',
        'check_time',
        'physical_checkin_time',
        'sickness_verification_type',
        'permit_is_valid',
        'late_minutes',
        'notes',
        'penalty_type',
        'penalty_minutes',
        'penalty_notes',
        'penalty_by',
        'penalty_at',
        'recorded_by',
        'admin_user_id',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_note',
    ];

    protected $casts = [
        'date' => 'date',
        'permit_is_valid' => 'boolean',
        'late_minutes' => 'integer',
        'penalty_minutes' => 'integer',
        'penalty_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Relasi ke Intern
     */
    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class);
    }

    /**
     * Relasi ke Shift
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Relasi ke Office
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Relasi ke Admin User yang mencatat
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
