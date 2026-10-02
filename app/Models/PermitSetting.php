<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermitSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'max_daily_count',
        'max_duration_minutes',
    ];

    protected $casts = [
        'max_daily_count' => 'integer',
        'max_duration_minutes' => 'integer',
    ];
}