<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role; // Pastikan model Role di-import

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Ini akan memastikan data role ada dan tidak akan membuat duplikat jika seeder dijalankan lagi.
     */
    public function run(): void
    {
        // Data yang sudah ada diubah menjadi firstOrCreate
        Role::firstOrCreate(
            ['name' => 'Admin'],
            ['description' => 'Memiliki akses penuh ke semua fitur dan pengaturan sistem.']
        );

        Role::firstOrCreate(
            ['name' => 'Contributor'],
            ['description' => 'Dapat menambahkan dan mengedit konten tetapi memiliki akses terbatas ke pengaturan.']
        );

        Role::firstOrCreate(
            ['name' => 'Magang'],
            ['description' => 'Intern dengan akses sementara ke fitur sistem tertentu.']
        );

        Role::firstOrCreate(
            ['name' => 'Alumni'],
            ['description' => 'Anggota yang sudah tidak aktif dengan akses hanya baca ke data historis mereka.']
        );

        Role::firstOrCreate(
            ['name' => 'Outsider'],
            ['description' => 'User dari luar organisasi, seperti pembimbing kampus, dengan akses view-only ke data pemagang tertentu.']
        );

        Role::firstOrCreate(
            ['name' => 'Asisten Admin'],
            ['description' => 'Mengapprove log activity intern']
        );
    }
}
