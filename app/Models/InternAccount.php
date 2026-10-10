<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternAccount extends Model
{
    use HasFactory;

    protected $table = 'intern_accounts';

    protected $fillable = [
        'intern_id',
        'gdrive_url',
        'spreadsheet_url',
        'enabled_platforms',
        'github_url',
        'gmail_account',
        'gmail_password',
        'figma_url',
        'social_media_links',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'gmail_password' => 'encrypted',
        'social_media_links' => 'array',
        'enabled_platforms' => 'array',
    ];

    /**
     * Helper to determine if a platform / resource is enabled for this intern
     */
    public function isPlatformEnabled(string $platform): bool
    {
        if (array_key_exists('enabled_platforms', $this->attributes) && $this->attributes['enabled_platforms'] !== null) {
            $platforms = is_array($this->enabled_platforms) ? $this->enabled_platforms : (json_decode($this->attributes['enabled_platforms'], true) ?: []);
            return in_array($platform, $platforms, true);
        }

        // Fallback untuk record lama yang belum pernah disimpan checklist-nya oleh admin:
        return match ($platform) {
            'gdrive' => !empty($this->gdrive_url),
            'spreadsheet' => !empty($this->spreadsheet_url),
            'github' => !empty($this->github_url) || !empty($this->gmail_account),
            'figma' => !empty($this->figma_url),
            'sosmed' => !empty($this->social_media_links) && is_array($this->social_media_links) && count($this->social_media_links) > 0,
            'notes' => !empty($this->notes),
            default => true,
        };
    }

    /**
     * Cek apakah pemagang memiliki checklist platform tugas mandiri (git, figma, atau sosmed).
     */
    public function hasTaskLinkPlatform(): bool
    {
        return $this->isPlatformEnabled('github')
            || $this->isPlatformEnabled('figma')
            || $this->isPlatformEnabled('sosmed');
    }

    /**
     * Dapatkan daftar platform tugas yang diaktifkan (misal: ['Git', 'Figma', 'Media Sosial']).
     *
     * @return array<string>
     */
    public function getEnabledTaskPlatformNames(): array
    {
        $names = [];
        if ($this->isPlatformEnabled('github')) {
            $names[] = 'Git';
        }
        if ($this->isPlatformEnabled('figma')) {
            $names[] = 'Figma';
        }
        if ($this->isPlatformEnabled('sosmed')) {
            $names[] = 'Media Sosial';
        }
        return $names;
    }

    /**
     * Get the intern that owns the account credentials.
     */
    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class, 'intern_id');
    }
}
