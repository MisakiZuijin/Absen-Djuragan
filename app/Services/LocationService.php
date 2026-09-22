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
    function checkIsInOfficeArea(float $latitude, float $longitude): object
    {
        $result = new \stdClass();
        $offices = $this->officeRepository->getAll();
        $isInOfficeArea = false;

        foreach ($offices as $office) {
            if ($isInOfficeArea) break;
            $filterCoordinate = array_filter($office->coordinates->toArray(), function ($item) {
                return $item['is_main'] == 0;
            });

            $filterCoordinate = array_values($filterCoordinate);

            $lat1 = $filterCoordinate[0]['latitude'];
            $long1 = $filterCoordinate[0]['longitude'];
            $lat2 = $filterCoordinate[1]['latitude'];
            $long2 = $filterCoordinate[1]['longitude'];


            $minLat = min($lat1, $lat2);
            $maxLat = max($lat1, $lat2);
            $minLong = min($long1, $long2);
            $maxLong = max($long1, $long2);


            if (Auth::user()->is_gps_activate == 1) {
                $isInOfficeArea = ($latitude >= $minLat && $latitude <= $maxLat) && ($longitude >= $minLong && $longitude <= $maxLong);
            } else {
                $isInOfficeArea = true;
            }
            $result->officeData = $office;
        }
        $result->isInArea = $isInOfficeArea;
        return $result;
    }
}
