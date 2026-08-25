<?php

namespace Database\Seeders;

use App\Models\AttdStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttdStatusSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        AttdStatus::create([
            "name" => "Dijadwalkan",
            "description" => "pemagang belum hadir atau belum saat nya"
        ]);
        AttdStatus::create([
            "name" => "Hadir",
            "description" => "pemagang sudah hadir dan menyelesaikan jam kerja hari ini"
        ]);
        AttdStatus::create([
            "name" => "Izin",
            "description" => "pemagang izin tidak hadir di jam magang"
        ]);
        AttdStatus::create([
            "name" => "Hadir dan Mengganti Jam",
            "description" => "pemagang sudah hadir dan menyelesaikan jam kerja hari ini"
        ]);
        AttdStatus::create([
            "name" => "Tidak Hadir",
            "description" => "pemagang tidak hadir tanpa alasan apapun"
        ]);
    }
}
