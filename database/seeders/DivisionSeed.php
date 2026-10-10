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

        $divisions = [
            ['name' => 'UI/UX Designer', 'icon' => 'pen-fancy-solid.svg'],
            ['name' => 'Marketing / Sales', 'icon' => 'bag-shopping-solid.svg'],
            ['name' => 'Social Media Specialist', 'icon' => 'thumbs-up-regular.svg'],
            ['name' => 'Programmer', 'icon' => 'code-solid.svg'],
            ['name' => 'Marcom/Public Relation', 'icon' => 'handshake-regular.svg'],
            ['name' => 'Tiktok Creator', 'icon' => 'tiktok-brands-solid.svg'],
            ['name' => 'Desain Grafis', 'icon' => 'palette-solid.svg'],
            ['name' => 'Content Writer', 'icon' => 'newspaper-regular.svg'],
            ['name' => 'Host/Presenter', 'icon' => 'microphone-solid.svg'],
            ['name' => 'Fotografer', 'icon' => 'camera-solid.svg'],
            ['name' => 'Content Planner', 'icon' => 'calendar-days-solid.svg'],
            ['name' => 'Voice Over Talent', 'icon' => 'microphone-lines-solid.svg'],
            ['name' => 'Videografer', 'icon' => 'video-solid.svg'],
            ['name' => 'Administrasi', 'icon' => 'briefcase-solid.svg'],
            ['name' => 'Las', 'icon' => 'fire-solid.svg'],
            ['name' => 'Digital Marketing', 'icon' => 'bullhorn-solid.svg'],
            ['name' => 'Project Manager', 'icon' => 'folder-open-regular.svg'],
            ['name' => 'Human Resource', 'icon' => 'users-solid.svg'],
            ['name' => 'Research & Development', 'icon' => 'magnifying-glass-solid.svg'],
            ['name' => 'Animasi', 'icon' => 'film-solid.svg'],
            ['name' => 'Customer Service', 'icon' => 'headset-solid.svg'],
        ];

        foreach ($divisions as $d) {
            Division::firstOrCreate(
                ['name' => $d['name']],
                ['icon' => $d['icon']]
            );
        }
    }
}
