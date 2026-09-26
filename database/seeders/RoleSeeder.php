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
        // Bersihkan role Contributor dan Alumni jika masih ada di database
        $roles = [
            [
                'id' => 7,
                'name' => 'Super Admin',
                'description' => 'Memiliki hak akses tertinggi terhadap seluruh sistem, manajemen akun admin, dan audit log aktivitas.',
            ],
            [
                'id' => 1,
                'name' => 'Admin',
                'description' => 'Memiliki akses penuh ke semua fitur dan pengaturan sistem.',
            ],
            [
                'id' => 3,
                'name' => 'Magang',
                'description' => 'Intern dengan akses sementara ke fitur sistem tertentu.',
            ],
            [
                'id' => 5,
                'name' => 'Outsider',
                'description' => 'User dari luar organisasi, seperti pembimbing kampus, dengan akses view-only ke data pemagang tertentu.',
            ],
            [
                'id' => 6,
                'name' => 'Asisten Admin',
                'description' => 'Mengapprove log activity intern',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['id' => $role['id']],
                [
                    'name' => $role['name'],
                    'description' => $role['description'],
                ]
            );
        }
    }
}
