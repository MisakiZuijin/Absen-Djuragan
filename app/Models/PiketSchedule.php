<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PiketSchedule extends Model
{
    use HasFactory;

    protected $table = 'piket_schedules';

    protected $fillable = [
        'intern_id',
        'office_id',
        'shift_id',
        'detail_schedule_id',
        'date',
        'notes',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class, 'intern_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function detailSchedule(): BelongsTo
    {
        return $this->belongsTo(DetailSchedule::class, 'detail_schedule_id');
    }
}
