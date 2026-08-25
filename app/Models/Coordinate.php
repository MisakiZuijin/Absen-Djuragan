<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Coordinate extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "office_id",
        "latitude",
        "longitude",
        "is_main"
    ];

    public function office(): BelongsTo {
        return $this->belongsTo(Office::class, "id",  "office_id");
    }

}
