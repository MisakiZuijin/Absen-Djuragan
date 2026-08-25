<?php

namespace Database\Seeders;

use App\Models\Coordinate;
use App\Models\Office;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {

        $office_1 =  Office::create([
            "name" => "kantor 1",
            "address" => "banguntapan",
            "capacity" => "30"
        ]);
        Coordinate::insert([
            [
                "office_id" => $office_1->id,
                'latitude' => -7.79036383036915,
                'longitude' => 110.4092288017273,
                "is_main" => true,
            ],
            [
                "office_id" => $office_1->id,
                'latitude' => -7.789464508763231,
                'longitude' => 110.40832110265026,
                "is_main" => false,
            ],
            [
                "office_id" => $office_1->id,
                'latitude' => -7.791263151975068,
                'longitude' => 110.41013650080433,
                "is_main" => false,

            ]
        ]);

        $office_2 = Office::create([
            "name" => "kantor 4",
            "address" => "banguntapan",
            "capacity" => "30"
        ]);

        Coordinate::insert([
            [
                "office_id" => $office_2->id,
                'latitude' => -7.786882756371842,
                'longitude' => 110.40575668215752,
                'is_main' => true,
            ],
            [
                "office_id" => $office_2->id,
                'latitude' => -7.786702892050658,
                'longitude' => 110.40557514385075,
                'is_main' => false
            ],
            [
                "office_id" => $office_2->id,
                'latitude' => -7.787062620693026,
                'longitude' => 110.40593822046428,
                'is_main' => false
            ]
        ]);

        $office_4  = Office::create([
            "name" => "kantor 2",
            "address" => "banguntapan",
            "capacity" => "30"
        ]);
        Coordinate::insert([
            [
                "office_id" => $office_4->id,
                'latitude' => -7.796914369729822,
                'longitude' => 110.40871113538743,
                'is_main' => true
            ],
            [
                "office_id" => $office_4->id,
                'latitude' => -7.796734505408638,
                'longitude' => 110.40852959273124,
                'is_main' => false
            ],
            [
                "office_id" => $office_4->id,
                'latitude' => -7.797094234051006,
                'longitude' => 110.40889267804363,
                'is_main' => false
            ]
        ]);
    }
}
