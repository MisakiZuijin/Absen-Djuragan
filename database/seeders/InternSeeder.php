<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Intern;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InternSeeder extends Seeder
{
    public function run(): void
    {
        $interns = [
            [
                'username' => 'intern_theo',
                'email' => 'theo@intern.com',
                'password' => 'password',
                'role_id' => 3,
                'os' => 'Windows',
                'browser' => 'Chrome',
                'device' => 'Laptop',
                'is_active' => true,
                'is_confirm' => true,
                'school_id' => 1,
                'division_id' => 1,
                'nim' => 'NIM001',
                'start_date' => '2026-08-01',
                'end_date' => '2026-10-31',
            ],
            [
                'username' => 'intern_bella',
                'email' => 'bella@intern.com',
                'password' => 'password123',
                'role_id' => 3,
                'os' => 'MacOS',
                'browser' => 'Safari',
                'device' => 'MacBook',
                'is_active' => true,
                'is_confirm' => true,
                'school_id' => 2,
                'division_id' => 2,
                'nim' => 'NIM002',
                'start_date' => '2025-07-01',
                'end_date' => '2025-08-31',
            ],
            [
                'username' => 'intern_cika',
                'email' => 'cika@intern.com',
                'password' => 'password123',
                'role_id' => 3,
                'os' => 'Linux',
                'browser' => 'Firefox',
                'device' => 'PC',
                'is_active' => true,
                'is_confirm' => true,
                'school_id' => 3,
                'division_id' => 3,
                'nim' => 'NIM003',
                'start_date' => '2025-07-01',
                'end_date' => '2025-08-31',
            ],
        ];

        foreach ($interns as $i => $intern) {
            $user = User::create([
                'username' => $intern['username'],
                'email' => $intern['email'],
                'password' => $intern['password'],
                'role_id' => $intern['role_id'],
                'os' => $intern['os'],
                'browser' => $intern['browser'],
                'device' => $intern['device'],
                'is_active' => $intern['is_active'],
                'is_confirm' => $intern['is_confirm'],
                'is_reset_token' => false,
                'is_gps_support' => false,
                'is_gps_activate' => false,
            ]);

            // Tambah profile lengkap
            $user->profile()->create([
                'NIP' => null,
                'full_name' => ucfirst(str_replace('_', ' ', $intern['username'])),
                'address' => 'Jl. Dummy No. ' . ($i + 1),
                'phone' => '0812345678' . ($i + 1),
                'date_of_birth' => '2000-01-0' . (($i % 9) + 1),
                'birth_place' => 'Jakarta',
            ]);

            Intern::create([
                'user_id' => $user->id,
                'school_id' => $intern['school_id'],
                'division_id' => $intern['division_id'],
                'nim' => $intern['nim'],
                'attention_message' => null,
                'start_date' => $intern['start_date'],
                'end_date' => $intern['end_date'],
            ]);
        }
    }
}
