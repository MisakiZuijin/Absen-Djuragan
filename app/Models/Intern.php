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

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, "division_id", "id");
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

        public function toiletPermits()
    {
        return $this->hasMany(PermitLog::class, 'intern_id')
                    ->where('permit_type', 'toilet');
    }

    public function prayerPermits()
    {
        return $this->hasMany(PermitLog::class, 'intern_id')
                    ->where('permit_type', 'prayer');
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
}