<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class shiftSeed extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        Shift::create([
            "name" => 'none',
            'start_time' => '00:00',
            'end_time' => '00:00',
            'start_break_time' => '00:00',
            'end_break_time' => '00:00',
            'total_time_in_minute' => 0,
        ]);

        Shift::create([
            'name' => 'Pagi',
            'description' => 'Shift pagi dari pukul 06:30 hingga 13:00.',
            'start_time' => '06:30',
            'end_time' => '13:00',
            'start_break_time' => '00:00',
            'end_break_time' => '00:00',
            'total_time_in_minute' => 390,
        ]);

        Shift::create([
            'name' => 'Middle',
            'description' => 'Shift tengah hari dari pukul 09:00 hingga 17:00.',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'start_break_time' => '12:15',
            'end_break_time' => '13:00',
            'break_time_in_minute' => 45,
            'total_time_in_minute' => 435
        ]);

        Shift::create([
            'name' => 'Siang',
            'description' => 'Shift siang dari pukul 13:00 hingga 21:00 dengan waktu istirahat 60 menit.',
            'start_time' => '13:00',
            'end_time' => '21:00',
            'start_break_time' => '18:00',
            'end_break_time' => '19:00',
            'break_time_in_minute' => 60,
            'total_time_in_minute' => 420,
        ]);
    }
}
