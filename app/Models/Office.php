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
        "capacity",
        "radius",
        "sop_url",
        "rules_url",
        "rules_description",
        "piket_url",
        "piket_description"
    ];


    public function coordinates(): HasMany {
        return $this->hasMany(Coordinate::class, "office_id");
    }

    public function coordinate()
    {
        return $this->hasOne(Coordinate::class);
    }

    public function getRadiusAttribute(): int
    {
        if (isset($this->attributes['radius']) && (int) $this->attributes['radius'] > 0) {
            return (int) $this->attributes['radius'];
        }

        $main = $this->coordinates?->firstWhere('is_main', 1) ?? $this->coordinates?->first();
        $bound = $this->coordinates?->firstWhere('is_main', 0) ?? $this->coordinates?->skip(1)->first();

        if ($main && $bound) {
            $latDiff = abs((float) $bound->latitude - (float) $main->latitude);
            $calcRadius = (int) round(($latDiff * (M_PI / 180)) * 6371000);
            return max(5, $calcRadius);
        }

        return 25;
    }
}
