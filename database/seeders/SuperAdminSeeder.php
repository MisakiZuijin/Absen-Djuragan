<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'username' => 'superadmin',
                'password' => 'superadmin123',
                'role_id' => 7,
                'is_active' => true,
                'is_confirm' => true,
                'os' => 'Windows',
                'browser' => 'Chrome',
                'device' => 'desktop',
                'is_gps_support' => false,
                'is_gps_activate' => false,
            ]
        );

        Profile::updateOrCreate(
            ['user_id' => $superAdmin->id],
            [
                'full_name' => 'Super Administrator',
                'phone' => '081234567890',
                'gender' => 'L',
                'date_of_birth' => '1990-01-01',
                'birth_place' => 'Yogyakarta',
            ]
        );
    }
}
