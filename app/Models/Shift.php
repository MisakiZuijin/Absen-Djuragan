<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shift extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "name",
        "type",
        "description",
        "start_time",
        "break_time_in_minute",
        "end_time",
        "total_time_in_minute",
        "start_break_time",
        "end_break_time",
        "adt_start_break_time",
        "adt_end_break_time",
        "is_active",
        "is_gps_active",
        "is_friday_break_active",
        "friday_start_break_time",
        "friday_end_break_time",
        "friday_break_time_in_minute",
        "is_split_shift",
        "split_wfh_start",
        "split_wfh_end",
        "split_wfo_start",
        "split_wfo_end",
        "is_rolling",
        "rolling_partner_shift_id",
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_gps_active' => 'integer',
        'is_friday_break_active' => 'boolean',
        'is_split_shift' => 'boolean',
        'is_rolling' => 'boolean',
        'rolling_partner_shift_id' => 'integer',
        'break_time_in_minute' => 'integer',
        'friday_break_time_in_minute' => 'integer',
        'total_time_in_minute' => 'integer',
    ];

    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('sidebar_distinct_shift_names');
        });
        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('sidebar_distinct_shift_names');
        });
    }

    /**
     * Mendefinisikan relasi "one-to-many" ke model DetailSchedule.
     */
    public function detailSchedules(): HasMany
    {
        return $this->hasMany(DetailSchedule::class);
    }

    /**
     * Relasi ke shift pasangan rolling (Lock Pair A <-> B).
     */
    public function rollingPartner(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'rolling_partner_shift_id');
    }

    /**
     * Cek apakah shift ini memenuhi aturan khusus istirahat hari Jumat untuk pemagang laki-laki.
     */
    public function isFridayMaleBreak(mixed $date = null, ?User $user = null): bool
    {
        // 1. Cek apakah pengaturan istirahat khusus Jumat laki-laki aktif pada shift ini
        if (!$this->is_friday_break_active) {
            return false;
        }

        // 2. Cek apakah tanggal target adalah hari Jumat
        try {
            if ($date instanceof \Carbon\Carbon) {
                $carbonDate = $date;
            } elseif ($date instanceof \DateTimeInterface) {
                $carbonDate = \Carbon\Carbon::instance($date);
            } elseif (!empty($date)) {
                $carbonDate = \Carbon\Carbon::parse($date);
            } else {
                $carbonDate = \Carbon\Carbon::now('Asia/Jakarta');
            }

            if (!$carbonDate->isFriday()) {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }

        // 3. Cek apakah target pemagang adalah laki-laki dan beragama Islam (Shalat Jumat)
        try {
            /** @var \App\Models\User|null $targetUser */
            $targetUser = $user ?: auth()->user();
            if (!$targetUser) {
                return false;
            }

            if (method_exists($targetUser, 'isMuslimMale')) {
                return $targetUser->isMuslimMale();
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Alias backward-compatible untuk isFridayMaleBreak.
     */
    public function isFridayMaleMiddle(mixed $date = null, ?User $user = null): bool
    {
        return $this->isFridayMaleBreak($date, $user);
    }

    /**
     * Mendapatkan jam mulai istirahat efektif (dengan penyesuaian khusus Jumat untuk Laki-laki jika diaktifkan).
     */
    public function getEffectiveStartBreakTime(mixed $date = null, ?User $user = null): ?string
    {
        if ($this->isFridayMaleBreak($date, $user) && !empty($this->friday_start_break_time)) {
            return $this->friday_start_break_time;
        }

        return $this->start_break_time;
    }

    /**
     * Mendapatkan jam selesai istirahat efektif (dengan penyesuaian khusus Jumat untuk Laki-laki jika diaktifkan).
     */
    public function getEffectiveEndBreakTime(mixed $date = null, ?User $user = null): ?string
    {
        if ($this->isFridayMaleBreak($date, $user) && !empty($this->friday_end_break_time)) {
            return $this->friday_end_break_time;
        }

        return $this->end_break_time;
    }

    /**
     * Mendapatkan durasi istirahat efektif dalam menit.
     */
    public function getEffectiveBreakTimeInMinute(mixed $date = null, ?User $user = null): int
    {
        if ($this->isFridayMaleBreak($date, $user) && !empty($this->friday_start_break_time) && !empty($this->friday_end_break_time)) {
            if ($this->friday_break_time_in_minute > 0) {
                return (int) $this->friday_break_time_in_minute;
            }
            try {
                $start = \Carbon\Carbon::parse($this->friday_start_break_time);
                $end = \Carbon\Carbon::parse($this->friday_end_break_time);
                return max(0, $end->diffInMinutes($start));
            } catch (\Throwable $e) {
                return (int) ($this->break_time_in_minute ?? 0);
            }
        }

        return (int) ($this->break_time_in_minute ?? 0);
    }
}
