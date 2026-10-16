<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftRollingGroup extends Model
{
    use HasFactory;

    protected $table = 'shift_rolling_groups';

    protected $fillable = [
        'name',
        'shift_id_1',
        'shift_id_2',
        'description',
        'is_active',
    ];

    protected $casts = [
        'shift_id_1' => 'integer',
        'shift_id_2' => 'integer',
        'is_active' => 'boolean',
    ];

    public function shift1(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id_1');
    }

    public function shift2(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id_2');
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class, 'shift_rolling_group_id');
    }

    /**
     * Dapatkan shift pasangan dari shift yang diberikan dalam grup ini.
     */
    public function getPartnerOf(int $shiftId): ?int
    {
        if ($this->shift_id_1 === $shiftId) {
            return $this->shift_id_2;
        }

        if ($this->shift_id_2 === $shiftId) {
            return $this->shift_id_1;
        }

        return null;
    }
}
