<?php

namespace Database\Seeders;

use App\Models\Intern;
use App\Models\Outsider;
use App\Models\WhatsappNumber;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WhatsappNumberSeeder extends Seeder
{
    public function run(): void
    {
        $pivotData = DB::table('outsider_intern')->get();

        foreach ($pivotData as $relation) {
            $internId = $relation->intern_id;
            $outsiderId = $relation->outsider_id;

            $outsider = Outsider::find($outsiderId);

            if ($outsider && $outsider->phone_number) {
                WhatsappNumber::create([
                    'intern_id' => $internId,
                    'phone_number' => $outsider->phone_number,
                    'is_notification_active' => true,
                ]);
            }
        }
    }
}
