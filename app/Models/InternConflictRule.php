<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternConflictRule extends Model
{
    use HasFactory;

    protected $table = 'intern_conflict_rules';

    protected $fillable = [
        'intern_id_1',
        'intern_id_2',
        'reason',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function intern1(): BelongsTo
    {
        return $this->belongsTo(Intern::class, 'intern_id_1');
    }

    public function intern2(): BelongsTo
    {
        return $this->belongsTo(Intern::class, 'intern_id_2');
    }

    /**
     * Check if two interns are in conflict
     */
    public static function hasConflict(int $internId1, int $internId2): bool
    {
        return static::where('is_active', true)
            ->where(function ($query) use ($internId1, $internId2) {
                $query->where(function ($q) use ($internId1, $internId2) {
                    $q->where('intern_id_1', $internId1)->where('intern_id_2', $internId2);
                })->orWhere(function ($q) use ($internId1, $internId2) {
                    $q->where('intern_id_1', $internId2)->where('intern_id_2', $internId1);
                });
            })
            ->exists();
    }
}
