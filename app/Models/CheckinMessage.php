<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckinMessage extends Model
{
    protected $fillable = ['type', 'message', 'image', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getOnTimeMessage(): ?self
    {
        return static::where('type', 'on_time')->where('is_active', true)->first();
    }

    public static function getLateMessage(): ?self
    {
        return static::where('type', 'late')->where('is_active', true)->first();
    }
}
