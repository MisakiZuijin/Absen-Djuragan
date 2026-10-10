<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AutomationSetting extends Model
{
    use HasFactory;

    protected $table = 'automation_settings';

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    protected static function booted(): void
    {
        static::saved(function ($model) {
            Cache::forget("automation_setting_{$model->key}");
            Cache::forget('automation_settings_all');
        });

        static::deleted(function ($model) {
            Cache::forget("automation_setting_{$model->key}");
            Cache::forget('automation_settings_all');
        });
    }

    /**
     * Ambil nilai pengaturan berdasarkan key (cached)
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::remember("automation_setting_{$key}", 3600, function () use ($key, $default) {
                $setting = static::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            });
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Ambil nilai pengaturan sebagai integer
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $val = static::get($key, $default);
        return is_numeric($val) ? (int) $val : $default;
    }

    /**
     * Ambil nilai pengaturan sebagai boolean
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $val = static::get($key, $default);
        if (is_bool($val)) {
            return $val;
        }
        return in_array(strtolower((string) $val), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * Simpan / perbarui pengaturan (updateOrCreate & hapus cache)
     */
    public static function set(string $key, mixed $value, ?string $description = null): self
    {
        $data = ['value' => (string) $value];
        if ($description !== null) {
            $data['description'] = $description;
        }

        $setting = static::updateOrCreate(['key' => $key], $data);
        Cache::forget("automation_setting_{$key}");
        Cache::forget('automation_settings_all');

        return $setting;
    }

    /**
     * Ambil seluruh pasangan key => value dalam bentuk array (cached)
     *
     * @return array<string, string|null>
     */
    public static function getAll(): array
    {
        try {
            return Cache::remember('automation_settings_all', 3600, function () {
                return static::pluck('value', 'key')->toArray();
            });
        } catch (\Throwable $e) {
            return [];
        }
    }
}
