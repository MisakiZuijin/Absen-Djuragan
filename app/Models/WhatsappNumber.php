<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappNumber extends Model {
    use HasFactory;

    protected $fillable = [
        'intern_id',
        'phone_number',
        'is_notification_active',
    ];

    public function intern(): BelongsTo {
        return $this->belongsTo(Intern::class, 'intern_id');
    }
}
