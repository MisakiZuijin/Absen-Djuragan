<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HandRaiseMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'hand_raise_id',
        'user_id',
        'message',
        'is_from_admin',
    ];

    protected $casts = [
        'is_from_admin' => 'boolean',
    ];

    public function handRaise(): BelongsTo
    {
        return $this->belongsTo(HandRaise::class, 'hand_raise_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
