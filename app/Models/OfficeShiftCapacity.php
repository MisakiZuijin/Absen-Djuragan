<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeShiftCapacity extends Model
{
    use HasFactory;

    protected $table = 'office_shift_capacities';

    protected $fillable = [
        'office_id',
        'shift_id',
        'max_capacity',
        'is_enabled',
    ];

    protected $casts = [
        'max_capacity' => 'integer',
        'is_enabled' => 'boolean',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
