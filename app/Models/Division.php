<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "name",
        "icon",
        "description",
        "meet_url",
    ];

    public function intern(): HasMany
    {
        return $this->hasMany(Intern::class);
    }

    public function broadcasts(): BelongsToMany
    {
        return $this->belongsToMany(Broadcast::class, 'broadcast_division', 'division_id', 'broadcast_id');
    }
}
