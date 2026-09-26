<?php

namespace App\Services;

use App\Repositories\Interface\OfficeRepository;
use Illuminate\Support\Facades\Auth;

class LocationService
{
    private OfficeRepository $officeRepository;
    public function __construct(OfficeRepository $officeRepository)
    {
        $this->officeRepository = $officeRepository;
    }
    public function checkIsInOfficeArea(?float $latitude, ?float $longitude, ?bool $isGpsRequired = null): object
    {
        $result = new \stdClass();
        $offices = $this->officeRepository->getAll();
        $result->officeData = $offices->first();
        $result->isInArea = false;

        $user = Auth::user();
        if ($isGpsRequired === null) {
            $isGpsRequired = ($user && isset($user->is_gps_activate)) ? ((int) $user->is_gps_activate === 1) : true;
        }

        if (!$isGpsRequired || is_null($latitude) || is_null($longitude)) {
            $result->isInArea = !$isGpsRequired;
            return $result;
        }

        $isInOfficeArea = false;
        foreach ($offices as $office) {
            if ($isInOfficeArea) break;
            $coordinates = $office->coordinates ? $office->coordinates->toArray() : [];
            $filterCoordinate = array_values(array_filter($coordinates, function ($item) {
                return isset($item['is_main']) && $item['is_main'] == 0;
            }));

            if (count($filterCoordinate) < 2) {
                continue;
            }

            $lat1 = (float) $filterCoordinate[0]['latitude'];
            $long1 = (float) $filterCoordinate[0]['longitude'];
            $lat2 = (float) $filterCoordinate[1]['latitude'];
            $long2 = (float) $filterCoordinate[1]['longitude'];

            $minLat = min($lat1, $lat2);
            $maxLat = max($lat1, $lat2);
            $minLong = min($long1, $long2);
            $maxLong = max($long1, $long2);

            $isInOfficeArea = ($latitude >= $minLat && $latitude <= $maxLat) && ($longitude >= $minLong && $longitude <= $maxLong);
            if ($isInOfficeArea) {
                $result->officeData = $office;
            }
        }
        $result->isInArea = $isInOfficeArea;
        return $result;
    }
}
