<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Outsider;
use App\Models\WhatsappNumber;
use App\Models\Intern;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OutsiderSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create(attributes: [
            'username' => 'outsider01',
            'email' => 'outsider@example.com',
            'password' => "outsider12345",
            'role_id' => 5,
            'is_active' => true,
            'is_confirm' => true,
            'device' => bin2hex(random_bytes(8)),
            'os' => 'Windows',
            'browser' => 'Chrome',
            'is_gps_support' => false,
            'is_gps_activate' => false,
        ]);

        Profile::create([
            "user_id" => $user->id,
            "full_name" => "outsider01",
            "phone" => "081234567890",
            "date_of_birth" => "2024-08-14",
            "birth_place" => "Yogyakarta"
        ]);

        $outsider = Outsider::create([
            'user_id' => $user->id,
            'type' => 'ortu',
            'phone_number' => '081234567890',
            'notif_enabled' => true,
        ]);

        $interns = Intern::where('user_id', $user->id)->get();

        foreach ($interns as $intern) {
            WhatsappNumber::create([
                'intern_id' => $intern->id,
                'nomor_tujuan' => $outsider->phone_number,
                'is_notification_active' => true,
            ]);
        }
    }
}
