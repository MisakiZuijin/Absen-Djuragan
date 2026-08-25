<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisionSeed extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {

        Division::create([
            'name' => 'UI/UX Designer',
            'icon' => 'pen-fancy-solid.svg',
        ]);

        Division::create([
            'name' => 'Marketing / Sales',
            'icon' => 'bag-shopping-solid.svg',
        ]);

        Division::create([
            'name' => 'Social Media Specialist',
            'icon' => 'thumbs-up-regular.svg',
        ]);

        Division::create([
            'name' => 'Programmer',
            'icon' => 'code-solid.svg',
        ]);

        Division::create([
            'name' => 'Marcom/Public Relation',
            'icon' => 'handshake-regular.svg',
        ]);

        Division::create([
            'name' => 'Tiktok Creator',
            'icon' => 'tiktok-brands-solid.svg',
        ]);

        Division::create([
            'name' => 'Desain Grafis',
            'icon' => 'palette-solid.svg',
        ]);

        Division::create([
            'name' => 'Content Writter',
            'icon' => 'newspaper-regular.svg',
        ]);

        Division::create([
            'name' => 'Host/Presenter',
            'icon' => 'microphone-solid.svg',
        ]);

        Division::create([
            'name' => 'Fotografer',
            'icon' => 'camera-solid.svg',
        ]);

        Division::create([
            'name' => 'Content Planner',
            'icon' => 'calendar-days-solid.svg',
        ]);

        Division::create([
            'name' => 'Voice Over Talent',
            'icon' => 'microphone-lines-solid.svg',
        ]);

        Division::create([
            'name' => 'Videografer',
            'icon' => 'video-solid.svg',
        ]);

        Division::create([
            'name' => 'Administrasi',
            'icon' => 'briefcase-solid.svg',
        ]);

        Division::create([
            'name' => 'Las',
            'icon' => 'fire-solid.svg',
        ]);

        Division::create([
            'name' => 'Digital Marketing',
            'icon' => 'bullhorn-solid.svg',
        ]);

        Division::create([
            'name' => 'Project Manager',
            'icon' => 'folder-open-regular.svg',
        ]);

        Division::create([
            'name' => 'Human Resource',
            'icon' => 'users-solid.svg',
        ]);

        Division::create([
            'name' => 'Research & Development',
            'icon' => 'magnifying-glass-solid.svg',
        ]);
    }
}
