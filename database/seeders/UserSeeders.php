<?php

namespace Database\Seeders;

use App\Models\Intern;
use App\Models\Profile;
use App\Models\User;
use App\Models\Role; // <-- PENTING: Tambahkan import untuk Role
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class UserSeeders extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $user = User::create([
        //     'username'    => "akhdanre",
        //     'email'       => "danakhdan12@gmail.com",
        //     'password'    => "superone",
        //     'role_id'     => 3,
        //     'os'          => "Windows",
        //     'browser'     => 'Chrome',
        //     'device'      => "acer",
        //     'is_confirm'  => true,
        //     'is_active'   => true,
        //     'is_reset_token' => true,
        //     "is_gps_support" => true,
        // ]);

        // Profile::create([
        //     'user_id'       => $user->id,
        //     'full_name'     => "Akhdan Robbani",
        //     'phone'         => "085746378923",
        //     'date_of_birth' => "2003-08-12",
        //     'birth_place'   => "Nganjuk",
        // ]);

        // Intern::create([
        //     "user_id" => $user->id,
        //     "school_id" => 1,
        //     "NIM" => "E412989"
        // ]);



        // $faker = Faker::create();
        // $numberOfUsers = 20;

        // for ($i = 0; $i < $numberOfUsers; $i++) {
        //     $user = User::create([
        //         'username'    => $faker->unique()->userName,
        //         'email'       => $faker->unique()->safeEmail,
        //         'password'    => bcrypt('password'),
        //         'role_id'     => 3,
        //         'os'          => $faker->randomElement(['Windows', 'Linux', 'macOS']),
        //         'browser'     => $faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
        //         'device'      => $faker->word,
        //         'is_confirm'  => false,
        //         'is_active'   => false,
        //     ]);

        //     Profile::create([
        //         'user_id'       => $user->id,
        //         'full_name'     => $faker->name,
        //         'phone'         => $faker->randomDigit(11),
        //         'date_of_birth' => $faker->date(),
        //         'birth_place'   => $faker->city,
        //     ]);

        //     Intern::create([
        //         "user_id" => $user->id,
        //         "school_id" => $faker->numberBetween(1, 14),
        //         "NIM" => "E412989"
        //     ]);
        // }
    }
}
