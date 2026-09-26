<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'system_activity_logs';

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action',
        'module',
        'description',
        'ip_address',
        'user_agent',
        'properties',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relasi ke User pelaku aktivitas
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
