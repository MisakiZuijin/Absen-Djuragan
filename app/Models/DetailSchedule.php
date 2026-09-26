<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DetailSchedule extends Model {
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        "schedule_id", "attendance_id", "adjustable_attd_id", "attd_status_id",
        "office_id", "log_activity_id", "shift_id", "date", "type",
        "start_time", "end_time", "work_type", "isChangeSchedule",
        "isBackFirst", "is_change_schedule_approved", "permit_reason_id"
    ];

    public function shift(): BelongsTo {
        return $this->belongsTo(Shift::class, "shift_id");
    }
    public function schedule(): BelongsTo {
        return $this->belongsTo(Schedule::class, "schedule_id", 'id');
    }
    public function attendance(): BelongsTo {
        return $this->belongsTo(Attendance::class, 'attendance_id', 'id');
    }
    public function adjustableAttendance(): HasMany {
        return $this->hasMany(AdjustableAttd::class, "detail_schedule_id", "id");
    }
    public function logActivity(): BelongsTo {
        return $this->belongsTo(LogActivity::class, 'log_activity_id');
    }
    public function attdStatus(): BelongsTo {
        return $this->belongsTo(AttdStatus::class, "attd_status_id");
    }
    public function permitReason(): BelongsTo {
        return $this->belongsTo(PermitReason::class, "permit_reason_id");
    }
    public function office(): BelongsTo {
        return $this->belongsTo(Office::class, "office_id");
    }
}