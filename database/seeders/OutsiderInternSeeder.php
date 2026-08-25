<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Intern;
use App\Models\Outsider;

class OutsiderInternSeeder extends Seeder
{
    public function run(): void
    {
        $interns = Intern::all();
        $outsiders = Outsider::all();

        foreach ($outsiders as $outsider) {
            $randomInterns = $interns->random(rand(1, 2));

            foreach ($randomInterns as $intern) {
                DB::table('outsider_intern')->insert([
                    'outsider_id' => $outsider->id,
                    'intern_id' => $intern->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
