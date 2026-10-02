<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PopupSetting extends Model
{
    use HasFactory;

    protected $table = 'popup_settings';

    protected $fillable = [
        'key',
        'label',
        'group',
        'is_enabled',
        'interval_seconds',
        'default_interval',
        'description',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'interval_seconds' => 'integer',
        'default_interval' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function ($model) {
            Cache::forget("popup_setting_{$model->key}");
            Cache::forget('popup_settings_all');
        });

        static::deleted(function ($model) {
            Cache::forget("popup_setting_{$model->key}");
            Cache::forget('popup_settings_all');
        });
    }

    /**
     * Get a specific setting by key (cached).
     */
    public static function getSetting(string $key): ?self
    {
        return Cache::remember("popup_setting_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });
    }

    /**
     * Get all settings (cached).
     */
    public static function getAllSettings()
    {
        return Cache::remember('popup_settings_all', 3600, function () {
            return static::orderBy('id')->get();
        });
    }

    /**
     * Get interval in seconds. Returns 0 if disabled.
     */
    public static function getInterval(string $key, int $default = 10): int
    {
        try {
            $setting = static::getSetting($key);
            if (!$setting) {
                return $default;
            }
            if (!$setting->is_enabled) {
                return 0; // 0 indicates disabled polling
            }
            return max(2, (int) $setting->interval_seconds);
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Check if a component's polling is enabled.
     */
    public static function isEnabled(string $key, bool $default = true): bool
    {
        try {
            $setting = static::getSetting($key);
            if (!$setting) {
                return $default;
            }
            return (bool) $setting->is_enabled;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Clear all cached settings.
     */
    public static function clearAllCache(): void
    {
        Cache::forget('popup_settings_all');
        $keys = [
            'attd_status_button',
            'broadcast_popup',
            'change_time_info',
            'raise_hand_manager',
            'toilet_monitor',
            'raise_hand_notification',
        ];
        foreach ($keys as $k) {
            Cache::forget("popup_setting_{$k}");
        }
    }
}
