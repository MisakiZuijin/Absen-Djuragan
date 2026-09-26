<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Tambahkan ini

class Shift extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "name",
        "description",
        "start_time",
        "break_time_in_minute",
        "end_time",
        "total_time_in_minute",
        "start_break_time",
        "end_break_time",
        "adt_start_break_time",
        "adt_end_break_time",
        "is_gps_active"
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
     * --- PERUBAHAN UTAMA ADA DI SINI ---
     * 
     * Mendefinisikan relasi "one-to-many" ke model DetailSchedule.
     * Ini memberitahu Laravel bahwa satu 'Shift' bisa dimiliki oleh banyak 'DetailSchedule'.
     * Ini memungkinkan kita untuk menggunakan withCount('detailSchedules') di Controller.
     */
    public function detailSchedules(): HasMany
    {
        return $this->hasMany(DetailSchedule::class);
    }
}