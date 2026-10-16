<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "date",
        "name"
    ];

    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('active_holiday_dates');
            \Illuminate\Support\Facades\Cache::forget('active_holiday_dates_until_today');
        });
        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('active_holiday_dates');
            \Illuminate\Support\Facades\Cache::forget('active_holiday_dates_until_today');
        });
    }
}
