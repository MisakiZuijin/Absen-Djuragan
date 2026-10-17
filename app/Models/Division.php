<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "name",
        "icon",
        "description",
        "meet_url",
        "shift_rolling_group_id",
        "max_logbook_per_day",
        "logbook_start_time",
        "logbook_end_time",
    ];

    protected $casts = [
        'max_logbook_per_day' => 'integer',
    ];

    public function rollingGroup(): BelongsTo
    {
        return $this->belongsTo(ShiftRollingGroup::class, 'shift_rolling_group_id');
    }

    public function intern(): HasMany
    {
        return $this->hasMany(Intern::class);
    }

    public function broadcasts(): BelongsToMany
    {
        return $this->belongsToMany(Broadcast::class, 'broadcast_division', 'division_id', 'broadcast_id');
    }

    public function offices(): BelongsToMany
    {
        return $this->belongsToMany(Office::class, 'office_divisions')->withPivot('is_active')->withTimestamps();
    }

    public function activeOffices(): BelongsToMany
    {
        return $this->belongsToMany(Office::class, 'office_divisions')->wherePivot('is_active', true)->withTimestamps();
    }

    public function scheduleRule(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DivisionScheduleRule::class, 'division_id');
    }
}
