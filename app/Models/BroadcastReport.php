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

    public function chats(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BroadcastReportChat::class, 'broadcast_report_id')->orderBy('created_at', 'asc')->orderBy('id', 'asc');
    }

    public function unreadAdminChatsCount(): int
    {
        return $this->chats()->where('is_from_admin', true)->where('is_read', false)->count();
    }

    public function unreadInternChatsCount(): int
    {
        return $this->chats()->where('is_from_admin', false)->where('is_read', false)->count();
    }
}
