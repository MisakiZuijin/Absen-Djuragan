<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    use HasFactory;

    protected $table = 'app_settings';

    protected $fillable = [
        'app_name',
        'logo',
        'favicon',
        'intern_banner',
        'banner_slides',
        'login_background',
    ];

    protected $casts = [
        'banner_slides' => 'array',
    ];

    /**
     * Ambil pengaturan aplikasi singleton dengan cache.
     */
    public static function getSettings(): self
    {
        return Cache::rememberForever('app_settings_global', function () {
            return static::first() ?? static::create([
                'app_name' => 'Absen Djuragan',
                'logo' => null,
                'favicon' => null,
                'intern_banner' => null,
                'banner_slides' => null,
                'login_background' => null,
            ]);
        });
    }

    /**
     * Bersihkan cache pengaturan aplikasi.
     */
    public static function clearSettingsCache(): void
    {
        Cache::forget('app_settings_global');
    }

    /**
     * Accessor URL Logo Aplikasi dengan fallback default.
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo && file_exists(public_path('uploads/app-settings/' . $this->logo))) {
            return asset('uploads/app-settings/' . $this->logo);
        }
        return asset('img/logo.svg');
    }

    /**
     * Accessor URL Favicon dengan fallback default.
     */
    public function getFaviconUrlAttribute(): string
    {
        if ($this->favicon && file_exists(public_path('uploads/app-settings/' . $this->favicon))) {
            return asset('uploads/app-settings/' . $this->favicon);
        }
        return asset('favicon.ico');
    }

    /**
     * Accessor URL Banner Atas Pemagang dengan fallback default.
     */
    public function getInternBannerUrlAttribute(): string
    {
        if ($this->intern_banner && file_exists(public_path('uploads/app-settings/' . $this->intern_banner))) {
            return asset('uploads/app-settings/' . $this->intern_banner);
        }
        return asset('img/bg.jpg');
    }

    /**
     * Accessor daftar URL semua slide banner pemagang (carousel).
     *
     * @return array<int, string>
     */
    public function getBannerSlidesUrlsAttribute(): array
    {
        $urls = [];

        if (!empty($this->banner_slides) && is_array($this->banner_slides)) {
            foreach ($this->banner_slides as $slide) {
                if ($slide && file_exists(public_path('uploads/app-settings/' . $slide))) {
                    $urls[] = asset('uploads/app-settings/' . $slide);
                }
            }
        }

        // Jika ada intern_banner dan belum masuk ke $urls
        if ($this->intern_banner && file_exists(public_path('uploads/app-settings/' . $this->intern_banner))) {
            $mainBannerUrl = asset('uploads/app-settings/' . $this->intern_banner);
            if (!in_array($mainBannerUrl, $urls)) {
                array_unshift($urls, $mainBannerUrl);
            }
        }

        // Fallback jika kosong sama sekali
        if (empty($urls)) {
            $urls[] = asset('img/bg.jpg');
        }

        return $urls;
    }

    /**
     * Accessor URL Background Halaman Login (null jika tidak ada custom background).
     */
    public function getLoginBackgroundUrlAttribute(): ?string
    {
        if ($this->login_background && file_exists(public_path('uploads/app-settings/' . $this->login_background))) {
            return asset('uploads/app-settings/' . $this->login_background);
        }
        return null;
    }
}
