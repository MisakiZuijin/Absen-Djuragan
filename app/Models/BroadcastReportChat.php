<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastReportChat extends Model
{
    protected $fillable = [
        'broadcast_report_id',
        'user_id',
        'message',
        'is_from_admin',
        'is_read',
    ];

    protected $casts = [
        'is_from_admin' => 'boolean',
        'is_read' => 'boolean',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(BroadcastReport::class, 'broadcast_report_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->with('profile');
    }
}
