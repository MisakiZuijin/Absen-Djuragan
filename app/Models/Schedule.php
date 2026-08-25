<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Shift; // Pastikan model Shift di-import

class Schedule extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "intern_id",
        "office_id",
        "shift_id",
        "start_period",
        "end_period",
        "type"
    ];

    public function intern(): BelongsTo {
        return $this->belongsTo(Intern::class, "intern_id");
    }

    public function detailSchedules(): HasMany {
        return $this->hasMany(DetailSchedule::class, "schedule_id");
    }

    public function office(): HasOne {
        return $this->hasOne(Office::class, "id", "office_id");
    }

    // =======================================================
    // KODE YANG KURANG DITAMBAHKAN DI SINI UNTUK FIX ERROR
    // =======================================================
    /**
     * Mendefinisikan relasi bahwa Schedule ini milik satu Shift.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
    // =======================================================
}