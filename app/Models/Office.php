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
        "is_hybrid_enabled",
        "radius",
        "sop_url",
        "rules_url",
        "rules_description",
        "piket_url",
        "piket_description",
        "piket_max_per_week"
    ];

    protected $casts = [
        'is_hybrid_enabled' => 'boolean',
        'piket_max_per_week' => 'integer',
    ];

    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('offices_all');
        });
        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('offices_all');
        });
    }


    public function coordinates(): HasMany {
        return $this->hasMany(Coordinate::class, "office_id");
    }

    public function coordinate()
    {
        return $this->hasOne(Coordinate::class);
    }

    public function divisions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Division::class, 'office_divisions')->withPivot('is_active')->withTimestamps();
    }

    public function activeDivisions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Division::class, 'office_divisions')->wherePivot('is_active', true)->withTimestamps();
    }

    public function shiftCapacities(): HasMany
    {
        return $this->hasMany(OfficeShiftCapacity::class, 'office_id');
    }

    public function officeDivisions(): HasMany
    {
        return $this->hasMany(OfficeDivision::class, 'office_id');
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

    /**
     * Dapatkan titik koordinat utama kantor (is_main = 1)
     */
    public function getMainCoordinateAttribute(): ?Coordinate
    {
        if ($this->relationLoaded('coordinates')) {
            return $this->coordinates->firstWhere('is_main', 1) 
                ?? $this->coordinates->firstWhere('is_main', true) 
                ?? $this->coordinates->first();
        }

        return $this->coordinates()->where('is_main', 1)->first() 
            ?? $this->coordinates()->first();
    }

    /**
     * Accessor latitude kantor dari tabel coordinates
     */
    public function getLatitudeAttribute(): ?float
    {
        if (isset($this->attributes['latitude']) && $this->attributes['latitude'] !== null && $this->attributes['latitude'] !== '') {
            return (float) str_replace(',', '.', (string) $this->attributes['latitude']);
        }

        $coord = $this->main_coordinate;
        if ($coord && !empty($coord->latitude)) {
            return (float) str_replace(',', '.', (string) $coord->latitude);
        }

        return null;
    }

    /**
     * Accessor longitude kantor dari tabel coordinates
     */
    public function getLongitudeAttribute(): ?float
    {
        if (isset($this->attributes['longitude']) && $this->attributes['longitude'] !== null && $this->attributes['longitude'] !== '') {
            return (float) str_replace(',', '.', (string) $this->attributes['longitude']);
        }

        $coord = $this->main_coordinate;
        if ($coord && !empty($coord->longitude)) {
            return (float) str_replace(',', '.', (string) $coord->longitude);
        }

        return null;
    }
}
