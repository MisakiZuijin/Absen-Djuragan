<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChangeTimeSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'intern_id',
        'session_date',
        'shift_id',
        'office_id',
        'start_time',
        'break_time',
        'back_time',
        'end_time',
        'total_work_minutes',
        'total_break_minutes',
        'total_target_debt_minutes',
        'status',
        'approved_by',
        'approved_at',
        'rejection_note',
        'start_time_message',
        'break_time_message',
        'back_time_message',
        'end_time_message',
        'latitude_start',
        'longitude_start',
        'latitude_end',
        'longitude_end',
    ];

    protected $casts = [
        'session_date' => 'date',
        'total_work_minutes' => 'integer',
        'total_break_minutes' => 'integer',
        'total_target_debt_minutes' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(ChangeTimeSessionTarget::class, 'change_time_session_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForIntern(Builder $query, int $internId): Builder
    {
        return $query->where('intern_id', $internId);
    }

    // Helper Methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canCheckOut(): bool
    {
        return $this->total_work_minutes >= $this->total_target_debt_minutes;
    }

    public function getRemainingDebtMinutes(): int
    {
        return max(0, $this->total_target_debt_minutes - $this->total_work_minutes);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ChangeTimeNote::class, 'session_id');
    }

    /**
     * Fallback accessor untuk mengambil pendaftaran ganti jam terkait
     */
    public function getMatchedRegistrationAttribute(): ?ChangeTimeRegistration
    {
        if (array_key_exists('matched_registration', $this->attributes)) {
            return $this->attributes['matched_registration'];
        }

        $sessionDateStr = $this->session_date ? \Carbon\Carbon::parse($this->session_date)->toDateString() : null;

        return ChangeTimeRegistration::where('intern_id', $this->intern_id)
            ->where(function ($q) use ($sessionDateStr) {
                if ($sessionDateStr) {
                    $q->whereDate('requested_date', $sessionDateStr)
                      ->orWhereDate('created_at', $sessionDateStr);
                }
            })
            ->latest('id')
            ->first();
    }

    /**
     * Akumulasi waktu kerja ganti jam maksimal 7 jam 15 menit (435 menit)
     */
    public function getTotalWorkMinutesAttribute($value): int
    {
        return min(435, max(0, (int) $value));
    }

    public function setTotalWorkMinutesAttribute($value): void
    {
        $this->attributes['total_work_minutes'] = min(435, max(0, (int) $value));
    }
}
