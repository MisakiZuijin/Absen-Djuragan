<?php

namespace App\Models;

use App\Utils\AdjustableStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AdjustableAttd extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'is_approved' => AdjustableStatus::class,
    ];

    protected $fillable = [
        "intern_id",
        "detail_schedule_id",
        "date",
        "start_time",
        "end_time",
        "break_time",
        "back_time",
        "total_min",
        "total_break_min",
        "is_approved",
        "start_time_message",
        "end_time_message",
        "break_time_message",
        "back_time_message",
        "original_start_time",
        "original_end_time",
        "adjusted_start_time",
        "adjusted_end_time",
        "latitude_start",
        "longitude_start",
        "latitude_end",
        "longitude_end"
    ];

    function detailSchedule(): BelongsTo {
        return $this->belongsTo(DetailSchedule::class, "detail_schedule_id"); // Fixed relationship key
    }
}
