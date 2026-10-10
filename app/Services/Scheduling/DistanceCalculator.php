<?php

namespace App\Services\Scheduling;

use App\Models\Intern;
use App\Models\Office;

class DistanceCalculator
{
    /**
     * Radius Bumi dalam kilometer.
     */
    public const EARTH_RADIUS_KM = 6371.0;

    /**
     * Hitung jarak lurus (Haversine Formula) antara dua titik koordinat GPS dalam kilometer.
     *
     * @param float $lat1 Latitude titik 1
     * @param float $lon1 Longitude titik 1
     * @param float $lat2 Latitude titik 2
     * @param float $lon2 Longitude titik 2
     * @return float Jarak dalam kilometer (dibulatkan ke 2 desimal)
     */
    public static function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // Konversi derajat ke radian
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return round($angle * self::EARTH_RADIUS_KM, 2);
    }

    /**
     * Ekstrak koordinat latitude & longitude kantor secara presisi dari model / relasi coordinates.
     *
     * @param Office $office
     * @return array|null [float $lat, float $lng]
     */
    public static function getOfficeCoordinates(Office $office): ?array
    {
        $officeLat = null;
        $officeLng = null;

        // 1. Coba dari accessor $office->latitude / $office->longitude
        if ($office->latitude !== null && $office->longitude !== null) {
            $officeLat = (float) $office->latitude;
            $officeLng = (float) $office->longitude;
        }

        // 2. Fallback: Cari langsung dari relasi coordinates
        if ($officeLat === null || $officeLng === null || ($officeLat == 0.0 && $officeLng == 0.0)) {
            $coord = null;
            if ($office->relationLoaded('coordinates')) {
                $coord = $office->coordinates->firstWhere('is_main', 1)
                      ?? $office->coordinates->firstWhere('is_main', true)
                      ?? $office->coordinates->first();
            } else {
                $coord = $office->coordinates()->where('is_main', 1)->first()
                      ?? $office->coordinates()->first();
            }

            if ($coord && !empty($coord->latitude) && !empty($coord->longitude)) {
                $officeLat = (float) str_replace(',', '.', (string) $coord->latitude);
                $officeLng = (float) str_replace(',', '.', (string) $coord->longitude);
            }
        }

        if ($officeLat === null || $officeLng === null || ($officeLat == 0.0 && $officeLng == 0.0)) {
            return null;
        }

        return [$officeLat, $officeLng];
    }

    /**
     * Ekstrak koordinat latitude & longitude kos pemagang.
     *
     * @param Intern $intern
     * @return array|null [float $lat, float $lng]
     */
    public static function getInternCoordinates(Intern $intern): ?array
    {
        $rawLat = $intern->home_latitude;
        $rawLng = $intern->home_longitude;

        if ($rawLat === null || $rawLng === null || $rawLat === '' || $rawLng === '') {
            return null;
        }

        $lat = (float) str_replace(',', '.', (string) $rawLat);
        $lng = (float) str_replace(',', '.', (string) $rawLng);

        if ($lat == 0.0 && $lng == 0.0) {
            return null;
        }

        return [$lat, $lng];
    }

    /**
     * Hitung jarak dari kos pemagang ke sebuah kantor spesifik.
     *
     * @param Intern $intern
     * @param Office $office
     * @return array [
     *     'distance_km' => float,
     *     'is_estimated' => bool,
     *     'note' => string
     * ]
     */
    public static function getInternOfficeDistance(Intern $intern, Office $office): array
    {
        // 1. Koordinat Kantor
        $officeCoords = self::getOfficeCoordinates($office);
        if (!$officeCoords) {
            return [
                'distance_km' => 999.0,
                'is_estimated' => true,
                'note' => 'Koordinat kantor belum diset di master data'
            ];
        }

        // 2. Koordinat Kos Pemagang
        $internCoords = self::getInternCoordinates($intern);
        if (!$internCoords) {
            return [
                'distance_km' => 999.0,
                'is_estimated' => true,
                'note' => 'Titik kos pemagang belum ditentukan'
            ];
        }

        $distance = self::haversineDistance($internCoords[0], $internCoords[1], $officeCoords[0], $officeCoords[1]);

        return [
            'distance_km' => $distance,
            'is_estimated' => false,
            'note' => 'Akurat via Haversine'
        ];
    }
}
