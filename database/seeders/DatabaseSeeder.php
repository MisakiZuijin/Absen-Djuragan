<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\Status;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Database\Seeders\InternSeeder;
use Database\Seeders\OutsiderSeeder;
use Database\Seeders\WhatsappNumberSeeder;



class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {

        // for ($i = 0; $i < 10; $i++) {
        //     $date = date("Y-m-d", strtotime("2024-01-07 + $i days")); // Menyesuaikan tanggal bertambah satu per iterasi
        //     $attendance = Attendance::create(["date" => $date, "is_auto_end" => true]);

        //     DetailSchedule::create([
        //         "date" => $date,
        //         "attendance_id" => $attendance->id,
        //         "office_id" => 1,
        //         "schedule_id" => 1
        //     ]);
        // }
        $this->call([
            RoleSeeder::class,
            DivisionSeed::class,
            EducationalLevelSeed::class,
            shiftSeed::class,
            SchoolSeed::class,
            OfficeSeeder::class,
            AttdStatusSeeder::class,
            PermitReasonSeed::class,
            OutsiderSeeder::class,
            InternSeeder::class,
            OutsiderInternSeeder::class,
            WhatsappNumberSeeder::class,
            AsistenAdminSeeder::class,
            UserSeeders::class,
        ]);



        $user = User::create([
            "username" => "adminSevenInc",
            "email" => "admin@gmail.com",
            "password" => "admin12345",
            "role_id" => 1,
            "os" => "Windows",
            "browser" => "Firefox",
            "device" =>  "acer-desktop",
            "is_confirm" => true,
            "is_active" => true
        ]);


        Profile::create([
            "user_id" => $user->id,
            "full_name" => "Admin Seven Inc",
            "phone" => "089867646546",
            "date_of_birth" => "2024-08-14",
            "birth_place" => "Yogyakarta"
        ]);

        Status::create([
            "name" => "In Review"
        ]);
        Status::create([
            "name" => "Accepted"
        ]);
        Status::create([
            "name" => "Rejected"
        ]);
    }
}
