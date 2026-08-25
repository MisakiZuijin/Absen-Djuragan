<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "name",
        "icon",
        "description"

    ];

    public function intern(): HasMany {
        return $this->belongsToMany(Broadcast::class, 'broadcast_division', 'division_id', 'broadcast_id');
    }
}
