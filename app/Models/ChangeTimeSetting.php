<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangeTimeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'restrict_to_holidays',
        'allowed_shift_ids',
        'allowed_office_ids',
        'default_office_id',
        'intern_notice_text',
    ];

    protected $casts = [
        'restrict_to_holidays' => 'boolean',
        'allowed_shift_ids' => 'array',
        'allowed_office_ids' => 'array',
        'default_office_id' => 'integer',
    ];

    public static function getSettings(): self
    {
        $setting = static::first();
        if (!$setting) {
            $shiftIds = Shift::where('id', '>', 1)->pluck('id')->toArray();
            $officeIds = Office::pluck('id')->toArray() ?: [1];
            $setting = static::create([
                'restrict_to_holidays' => false,
                'allowed_shift_ids' => $shiftIds,
                'allowed_office_ids' => $officeIds,
                'default_office_id' => 1,
            ]);
        }
        return $setting;
    }
}
