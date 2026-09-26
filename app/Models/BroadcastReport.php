<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastReport extends Model
{
    protected $fillable = [
        'broadcast_id',
        'user_id',
        'report',
    ];

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(Broadcast::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->with('profile');
    }
}
