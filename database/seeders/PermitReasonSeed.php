<?php

namespace Database\Seeders;

use App\Models\PermitCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermitReasonSeed extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        PermitCategory::insert([
            ["name" => "Sakit dengan surat dokter"],
            ["name" => "Sakit tanpa surat dokter"],
            ["name" => "Keperluan sekolah / kampus"],
            ["name" => "Keperluan lain"],
        ]);
    }
}
