<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LogActivity extends Model {
    use HasFactory;

    const CREATED_AT = "date";
    const UPDATED_AT = null;

    protected $fillable = [
        "detail_schedule_id",
        "attendance_id",
        "status_id",
        "date",
        "time",
        "activity",
        "is_approved"
    ];

    protected $casts = [
        'is_approved' => 'boolean'
    ];

    public function status(): BelongsTo {
        return $this->belongsTo(Status::class, "status_id", "id");
    }

    public function detailSchedule(): BelongsTo {
        return $this->belongsTo(DetailSchedule::class, "detail_schedule_id");
    }

    public function legacyDetailSchedule(): HasOne {
        return $this->hasOne(DetailSchedule::class, "log_activity_id");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
