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
    ];
}