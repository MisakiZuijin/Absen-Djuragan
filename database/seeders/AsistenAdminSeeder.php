<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AsistenAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'username' => 'asistenadmin01',
            'email' => 'asistenadmin01@example.com',
            'password' => 'asistenadmin12345',
            'role_id' => 6,
            'is_active' => true,
            'is_confirm' => true,
            'device' => bin2hex(random_bytes(8)),
            'os' => 'Windows',
            'browser' => 'Chrome',
            'is_gps_support' => false,
            'is_gps_activate' => false,
        ]);

        Profile::create([
            'user_id' => $user->id,
            'full_name' => 'Asisten Admin 01',
            'phone' => '081234567891',
            'date_of_birth' => '2000-01-01',
            'birth_place' => 'Jakarta',
        ]);
    }
}
