<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "name",
        "address",
        "capacity"
    ];


    public function coordinates(): HasMany {
        return $this->hasMany(Coordinate::class, "office_id");
    }

    public function coordinate()
    {
        return $this->hasOne(Coordinate::class);
    }
}
