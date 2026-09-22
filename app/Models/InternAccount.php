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
    ];

    /**
     * Get the intern that owns the account credentials.
     */
    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class, 'intern_id');
    }
}
