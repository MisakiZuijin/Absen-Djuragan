<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeTimeNote extends Model
{
    use HasFactory;

    protected $table = 'change_time_notes';

    protected $fillable = [
        'session_id',
        'registration_id',
        'user_id',
        'message',
        'is_from_admin',
        'is_read',
    ];

    protected $casts = [
        'is_from_admin' => 'boolean',
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke ChangeTimeSession.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ChangeTimeSession::class, 'session_id');
    }

    /**
     * Relasi ke ChangeTimeRegistration.
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(ChangeTimeRegistration::class, 'registration_id');
    }

    /**
     * Relasi ke User pengirim pesan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
