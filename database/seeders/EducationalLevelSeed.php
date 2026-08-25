<?php

namespace Database\Seeders;

use App\Models\EducationalLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EducationalLevelSeed extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        EducationalLevel::create([
            "name" => "SMA"
        ]);
        EducationalLevel::create([
            "name" => "SMK"
        ]);
        EducationalLevel::create([
            "name" => "Universitas"
        ]);
        EducationalLevel::create([
            "name" => "Politeknik"
        ]);
        EducationalLevel::create([
            "name" => "Institude"
        ]);
    }
}
