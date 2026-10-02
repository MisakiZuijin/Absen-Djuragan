<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Intern extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "user_id",
        "school_id",
        "division_id",
        "brand_id",
        "shift_id",
        "nim",
        "start_date",
        "end_date"
    ];


    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, "shift_id", 'id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id", 'id');
    }

    public function getGenderAttribute(): ?string
    {
        return $this->user?->profile?->gender;
    }

    public function isMale(): bool
    {
        return $this->user ? $this->user->isMale() : false;
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, "division_id", "id");
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, "brand_id", "id");
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, "school_id", "id");
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'intern_id');
    }

    public function detailSchedules(): HasManyThrough
    {
        return $this->hasManyThrough(
            DetailSchedule::class,
            Schedule::class,
            'intern_id',
            'schedule_id',
            'id',
            'id'
        );
    }

    public function todayDetailSchedule(): HasOneThrough
    {
        return $this->hasOneThrough(
            DetailSchedule::class,
            Schedule::class,
            'intern_id',
            'schedule_id',
            'id',
            'id'
        )->whereDate('detail_schedules.date', today());
    }

    public function detailProject(): HasMany
    {
        return $this->hasMany(DetailProjects::class, "intern_id");
    }

    public function whatsappNumber(): HasOne
    {
        return $this->hasOne(WhatsappNumber::class, 'intern_id');
    }

    public function account(): HasOne
    {
        return $this->hasOne(InternAccount::class, 'intern_id');
    }
    public function outsiders()
    {
        return $this->belongsToMany(Outsider::class, 'outsider_intern');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'intern_id');
    }

    public function attendance(): HasOne
    {
        return $this->hasOne(Attendance::class, 'intern_id');
    }

    public function changeTimeSessions(): HasMany
    {
        return $this->hasMany(ChangeTimeSession::class, 'intern_id');
    }

    public function changeTimeRegistrations(): HasMany
    {
        return $this->hasMany(ChangeTimeRegistration::class, 'intern_id');
    }

    public function toiletPermits(): HasManyThrough
    {
        return $this->hasManyThrough(
            PermitLog::class,
            Attendance::class,
            'intern_id',
            'attendance_id',
            'id',
            'id'
        )->where('permit_logs.type', 'toilet');
    }

    public function prayerPermits(): HasManyThrough
    {
        return $this->hasManyThrough(
            PermitLog::class,
            Attendance::class,
            'intern_id',
            'attendance_id',
            'id',
            'id'
        )->where('permit_logs.type', 'prayer');
    }

    // =========================================================================
    // PERMIT LOG RELATIONS
    // =========================================================================

    /**
     * [BEST PRACTICE] Mendapatkan SEMUA riwayat izin milik intern ini
     * melalui tabel attendances. Kunci didefinisikan secara eksplisit.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function permitLogs(): HasManyThrough
    {
        return $this->hasManyThrough(
            PermitLog::class,   // Model tujuan akhir
            Attendance::class,  // Model perantara
            'intern_id',        // Foreign key di tabel 'attendances'
            'attendance_id',    // Foreign key di tabel 'permit_logs'
            'id',               // Local key di tabel 'interns'
            'id'                // Local key di tabel 'attendances'
        );
    }

    /**
     * [FIXED & ROBUST] Mendapatkan SATU log izin yang sedang aktif HARI INI.
     * Relasi ini dibuat lebih tahan banting untuk menangani berbagai kasus.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function activePermitLog(): HasOneThrough
    {
        return $this->hasOneThrough(
            PermitLog::class,
            Attendance::class,
            'intern_id',        // Foreign key di tabel 'attendances'
            'attendance_id',    // Foreign key di tabel 'permit_logs'
            'id',               // Local key di tabel 'interns'
            'id'                // Local key di tabel 'attendances'
        )
        // KONDISI UTAMA:
        // 1. Cari permit_logs yang belum memiliki end_time
        ->whereNull('permit_logs.end_time')
        // 2. Dan pastikan permit_logs tersebut dibuat HARI INI
        //    Ini lebih aman daripada mengecek tanggal di tabel attendance
        ->whereDate('permit_logs.start_time', today())
        // 3. Ambil yang paling baru untuk menghindari duplikasi jika ada data error.
        ->latest('permit_logs.start_time');
    }

    /**
     * Semua log izin keluar milik pemagang pada HARI INI
     */
    public function todayLeavePermits(): HasManyThrough
    {
        return $this->hasManyThrough(
            PermitLog::class,
            Attendance::class,
            'intern_id',
            'attendance_id',
            'id',
            'id'
        )
        ->where('permit_logs.type', 'leave')
        ->whereDate('permit_logs.start_time', today())
        ->orderBy('permit_logs.start_time', 'desc');
    }

    public function getTodayLeavePermitsCollectionAttribute()
    {
        return $this->relationLoaded('todayLeavePermits')
            ? $this->todayLeavePermits
            : $this->todayLeavePermits()->get();
    }

    public function getTodayActiveLeavePermitAttribute()
    {
        return $this->today_leave_permits_collection->firstWhere('end_time', null);
    }

    public function getTodayLatestLeavePermitAttribute()
    {
        return $this->today_leave_permits_collection->first();
    }

    public function getTodayTotalLeaveMinutesAttribute(): int
    {
        return (int) $this->today_leave_permits_collection->sum(function($log) {
            if ($log->end_time) {
                return (int) ($log->duration_in_minutes ?: max(0, ceil(\Carbon\Carbon::parse($log->start_time)->diffInMinutes($log->end_time))));
            } else {
                return max(0, (int) ceil(\Carbon\Carbon::parse($log->start_time)->diffInMinutes(now())));
            }
        });
    }

    public function getTodayLeaveFormattedDurationAttribute(): string
    {
        $minutes = $this->today_total_leave_minutes;
        if ($minutes <= 0) return '0 Menit';
        $hours = intdiv($minutes, 60);
        $remMin = $minutes % 60;
        if ($hours > 0 && $remMin > 0) {
            return "{$hours} Jam {$remMin} Menit";
        } elseif ($hours > 0) {
            return "{$hours} Jam";
        }
        return "{$minutes} Menit";
    }

    /**
     * Riwayat Absen Offline oleh Admin
     */
    public function offlineAttendances(): HasMany
    {
        return $this->hasMany(OfflineAttendance::class, 'intern_id');
    }
}