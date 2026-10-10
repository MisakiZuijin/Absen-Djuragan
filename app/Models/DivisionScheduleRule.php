<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DivisionScheduleRule extends Model
{
    use HasFactory;

    protected $table = 'division_schedule_rules';

    protected $fillable = [
        'division_id',
        'allowed_shift_ids',
        'shift_distribution_mode',
        'allow_hybrid_balancing',
        'allow_split_shift',
    ];

    protected $casts = [
        'allowed_shift_ids' => 'array',
        'allow_hybrid_balancing' => 'boolean',
        'allow_split_shift' => 'boolean',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    /**
     * Helper to get allowed shifts as models collection
     */
    public function getAllowedShiftsAttribute()
    {
        if (empty($this->allowed_shift_ids)) {
            return collect();
        }

        return Shift::whereIn('id', $this->allowed_shift_ids)->get();
    }
}
