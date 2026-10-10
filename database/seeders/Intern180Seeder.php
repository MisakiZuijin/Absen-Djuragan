<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Intern;
use App\Models\School;
use App\Models\Division;
use App\Models\Shift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Intern180Seeder extends Seeder
{
    /**
     * Seed 180 pemagang lengkap untuk simulasi dan operasional:
     * - Status: Aktif (is_active = 1, is_confirm = 1)
     * - Data pribadi lengkap: User, Profile (nama, no hp, tgl lahir, jk, alamat), Intern (NIM, tanggal magang, titik kos)
     * - Password standar: 'password'
     * - Tersebar merata di SEMUA divisi yang ada
     * - Berasal dari sekolah/universitas yang sama (berkelompok)
     * - Sebaran wilayah: Dekat Kantor 1, Dekat Kantor 4, Dekat Kantor 2, serta Jarak Jauh / Luar Kota untuk simulasi Full WFH
     */
    public function run(): void
    {
        $this->command->info("Memulai pembuatan data seed 180 pemagang...");

        DB::transaction(function () {
            // 1. Ambil atau Buat Master Divisi
            $divisions = Division::all();
            if ($divisions->isEmpty()) {
                $defaultDivisions = [
                    'UI/UX Designer', 'Marketing / Sales', 'Social Media Specialist', 'Programmer',
                    'Marcom/Public Relation', 'Tiktok Creator', 'Desain Grafis', 'Content Writer',
                    'Host/Presenter', 'Fotografer', 'Content Planner', 'Voice Over Talent',
                    'Videografer', 'Administrasi', 'Las', 'Digital Marketing',
                    'Project Manager', 'Human Resource', 'Research & Development'
                ];
                foreach ($defaultDivisions as $dName) {
                    Division::create(['name' => $dName]);
                }
                $divisions = Division::all();
            }
            $divisionList = $divisions->values();
            $divisionCount = $divisionList->count();

            // 2. Ambil atau Konfigurasi Sekolah & Universitas
            // Pisahkan menjadi: Sekolah Lokal Jogja & Sekolah Jarak Jauh (Full Remote WFH)
            $remoteSchoolNames = [
                'Politeknik Negeri Jakarta (Remote WFH)',
                'Institut Teknologi Sepuluh Nopember (Remote WFH)',
                'Telkom University Bandung (Remote WFH)',
            ];

            $localSchoolNames = [
                'Universitas Gadjah Mada',
                'Universitas Negeri Yogyakarta',
                'Universitas Pembangunan Nasional Veteran',
                'Universitas Amikom Yogyakarta',
                'Universitas Islam Indonesia',
                'Institut Seni Indonesia Yogyakarta',
                'SMKN 2 Yogyakarta',
                'SMKN 3 Yogyakarta',
                'SMK Muhammadiyah 1 Bantul',
                'SMKN 1 Depok Sleman',
                'Politeknik Negeri Semarang',
                'Universitas Diponegoro',
            ];

            $remoteSchools = [];
            foreach ($remoteSchoolNames as $rsName) {
                $sch = School::firstOrCreate(
                    ['name' => $rsName],
                    [
                        'educational_level_id' => 3, // Perguruan Tinggi / Vokasi
                        'address' => 'Kampus Luar Kota / PJJ',
                        'is_remote_school' => true,
                    ]
                );
                $sch->is_remote_school = true;
                $sch->save();
                $remoteSchools[] = $sch;
            }

            $localSchools = [];
            foreach ($localSchoolNames as $lsName) {
                $sch = School::firstOrCreate(
                    ['name' => $lsName],
                    [
                        'educational_level_id' => 3,
                        'address' => 'DI Yogyakarta',
                        'is_remote_school' => false,
                    ]
                );
                $localSchools[] = $sch;
            }

            // Bersihkan data pemagang lama dari seeder ini jika sebelumnya pernah dijalankan (idempotent)
            $oldInterns = Intern::where('hr_placement_notes', 'Seeder Resmi 180 Pemagang Djuragan')->get();
            if ($oldInterns->isNotEmpty()) {
                $oldUserIds = $oldInterns->pluck('user_id')->filter()->all();
                Intern::whereIn('id', $oldInterns->pluck('id'))->delete();
                Profile::whereIn('user_id', $oldUserIds)->delete();
                User::whereIn('id', $oldUserIds)->delete();
            }

            // 4. Koleksi Nama Realistis Indonesia
            $maleFirstNames = [
                'Aditya', 'Bima', 'Bagas', 'Dimas', 'Eko', 'Fajar', 'Galih', 'Hendra', 'Ilham', 'Joko',
                'Kevin', 'Lukman', 'Mahendra', 'Naufal', 'Oki', 'Pandu', 'Rafi', 'Reza', 'Satria', 'Taufik',
                'Wahyu', 'Yoga', 'Zikri', 'Aldi', 'Bayu', 'Candra', 'Daffa', 'Fikri', 'Gilang', 'Hafizh',
                'Irfan', 'Kurniawan', 'Muhammad', 'Pradana', 'Rian', 'Surya', 'Tri', 'Yusuf', 'Arya', 'Danang',
                'Faisal', 'Gibran', 'Hadi', 'Iqbal', 'Latif', 'Mustofa', 'Nizar', 'Pramudya', 'Raditya', 'Syahrul'
            ];

            $femaleFirstNames = [
                'Anisa', 'Aulia', 'Bella', 'Citra', 'Dina', 'Eka', 'Fitri', 'Gita', 'Hana', 'Indah',
                'Jasmine', 'Karina', 'Lestari', 'Maya', 'Nadia', 'Octavia', 'Putri', 'Rani', 'Salsabila', 'Tiara',
                'Ulfa', 'Vina', 'Wulan', 'Yulia', 'Zahra', 'Amalia', 'Bunga', 'Cantika', 'Devi', 'Febriana',
                'Gisela', 'Hani', 'Intan', 'Khairunisa', 'Mega', 'Nurul', 'Pratiwi', 'Riska', 'Safira', 'Tari',
                'Widya', 'Anggun', 'Dwi', 'Farida', 'Hafizhah', 'Kusuma', 'Maharani', 'Nabila', 'Rahma', 'Septiani'
            ];

            $lastNames = [
                'Pratama', 'Saputra', 'Wijaya', 'Nugroho', 'Hidayat', 'Santoso', 'Utomo', 'Wibowo', 'Setiawan', 'Ramadhan',
                'Firmansyah', 'Syahputra', 'Kurniawan', 'Pranata', 'Mahendra', 'Pangestu', 'Gunawan', 'Suryono', 'Hartono', 'Fauzi',
                'Handayani', 'Lestari', 'Anggraini', 'Rahmawati', 'Puspitasari', 'Wulandari', 'Oktaviani', 'Permatasari', 'Safitri', 'Kusumawati',
                'Dewi', 'Astuti', 'Susanti', 'Pertiwi', 'Wardani', 'Cahyani', 'Arifin', 'Hakim', 'Nasution', 'Subagyo'
            ];

            $birthPlaces = ['Yogyakarta', 'Sleman', 'Bantul', 'Solo', 'Semarang', 'Magelang', 'Jakarta', 'Bandung', 'Surabaya', 'Malang', 'Purwokerto', 'Cirebon'];

            // Hash password sekali agar proses seeding super cepat (< 1 detik)
            $hashedPassword = Hash::make('password');

            $totalTarget = 180;
            $createdCount = 0;

            for ($i = 1; $i <= $totalTarget; $i++) {
                // Tentukan Jenis Kelamin & Nama
                $gender = ($i % 2 === 0) ? 'L' : 'P';
                $firstNameList = ($gender === 'L') ? $maleFirstNames : $femaleFirstNames;
                $firstName = $firstNameList[($i - 1) % count($firstNameList)];
                $lastName = $lastNames[($i * 3 + 7) % count($lastNames)];
                $fullName = "{$firstName} {$lastName}";

                // Username & Email unik dan rapi
                $cleanFirst = strtolower(preg_replace('/[^a-z0-9]/', '', $firstName));
                $username = "intern_{$cleanFirst}_{$i}";
                $email = "{$cleanFirst}.{$i}@magang.djuragan.id";

                // Pembagian Divisi: Tersebar merata di semua divisi
                $division = $divisionList[($i - 1) % $divisionCount];

                // -------------------------------------------------------------
                // Pembagian Wilayah Domisili (Dekat K1, Dekat K4, Dekat K2, & Jauh/WFH)
                // Masing-masing kelompok sekitar 45 orang
                // -------------------------------------------------------------
                if ($i <= 45) {
                    // KELOMPOK 1: Dekat Kantor 1 Pusat (Sleman Tengah / Gejayan / UGM)
                    $baseLat = -7.7828;
                    $baseLng = 110.3670;
                    $offsetLat = (mt_rand(-15, 15) / 1000);
                    $offsetLng = (mt_rand(-15, 15) / 1000);
                    $lat = $baseLat + $offsetLat;
                    $lng = $baseLng + $offsetLng;
                    $addressNotes = "Kos Dekat Kantor 1 (Gejayan / UGM No. {$i})";
                    $school = $localSchools[$i % count($localSchools)];
                    $placementType = 'auto_office';
                } elseif ($i <= 90) {
                    // KELOMPOK 2: Dekat Kantor 4 Utara (Jakal KM 9 / Ngaglik / UII)
                    $baseLat = -7.7550;
                    $baseLng = 110.3800;
                    $offsetLat = (mt_rand(-15, 15) / 1000);
                    $offsetLng = (mt_rand(-15, 15) / 1000);
                    $lat = $baseLat + $offsetLat;
                    $lng = $baseLng + $offsetLng;
                    $addressNotes = "Kos Dekat Kantor 4 (Jakal KM " . (8 + ($i % 5)) . " No. {$i})";
                    $school = $localSchools[$i % count($localSchools)];
                    $placementType = 'auto_office';
                } elseif ($i <= 135) {
                    // KELOMPOK 3: Dekat Kantor 2 Selatan (Sewon / Krapyak / ISI / Bantul)
                    $baseLat = -7.8150;
                    $baseLng = 110.3550;
                    $offsetLat = (mt_rand(-15, 15) / 1000);
                    $offsetLng = (mt_rand(-15, 15) / 1000);
                    $lat = $baseLat + $offsetLat;
                    $lng = $baseLng + $offsetLng;
                    $addressNotes = "Kos Dekat Kantor 2 (Sewon / Krapyak No. {$i})";
                    $school = $localSchools[$i % count($localSchools)];
                    $placementType = 'auto_office';
                } else {
                    // KELOMPOK 4: Jarak Jauh / Luar Kota / Full Remote WFH (45 Orang)
                    if ($i <= 160) {
                        // Sub-kluster 4A: Mahasiswa Sekolah Remote Luar Kota (Full Remote WFH Otomatis)
                        $remoteCities = [
                            ['city' => 'Jakarta', 'lat' => -6.2088, 'lng' => 106.8456],
                            ['city' => 'Surabaya', 'lat' => -7.2575, 'lng' => 112.7521],
                            ['city' => 'Bandung', 'lat' => -6.9175, 'lng' => 107.6191],
                            ['city' => 'Malang', 'lat' => -7.9839, 'lng' => 112.6214],
                        ];
                        $targetCity = $remoteCities[$i % count($remoteCities)];
                        $lat = $targetCity['lat'] + (mt_rand(-20, 20) / 1000);
                        $lng = $targetCity['lng'] + (mt_rand(-20, 20) / 1000);
                        $addressNotes = "Domisili Luar Kota ({$targetCity['city']}) - Full Remote";
                        $school = $remoteSchools[$i % count($remoteSchools)];
                        $placementType = 'auto_office';
                    } else {
                        // Sub-kluster 4B: Domisili Perbatasan Jauh DIY / Jawa Tengah (> 30 km dari kantor)
                        $farLocations = [
                            ['name' => 'Wates Kulon Progo', 'lat' => -7.8572, 'lng' => 110.1583], // ~32 km
                            ['name' => 'Wonosari Gunungkidul', 'lat' => -7.9658, 'lng' => 110.6033], // ~38 km
                            ['name' => 'Magelang Kota', 'lat' => -7.4706, 'lng' => 110.2178], // ~42 km
                            ['name' => 'Klaten Perbatasan', 'lat' => -7.7058, 'lng' => 110.6044], // ~28 km
                        ];
                        $targetFar = $farLocations[$i % count($farLocations)];
                        $lat = $targetFar['lat'] + (mt_rand(-10, 10) / 1000);
                        $lng = $targetFar['lng'] + (mt_rand(-10, 10) / 1000);
                        $addressNotes = "Rumah Asal {$targetFar['name']} (Jarak Jauh Laju / WFH)";
                        $school = $localSchools[$i % count($localSchools)];
                        $placementType = ($i % 2 === 0) ? 'wfh_only' : 'auto_office';
                    }
                }

                // Buat Akun User (langsung simpan pre-hashed password agar cepat)
                $userId = DB::table('users')->insertGetId([
                    'username' => $username,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'role_id' => 3, // Pemagang
                    'os' => ['Windows', 'MacOS', 'Linux', 'Android', 'iOS'][$i % 5],
                    'browser' => ['Chrome', 'Firefox', 'Safari', 'Edge'][$i % 4],
                    'device' => ['Laptop ASUS', 'MacBook Air', 'PC Desktop Lenovo', 'Laptop Acer', 'Laptop HP'][$i % 5],
                    'is_active' => 1,
                    'is_confirm' => 1,
                    'is_reset_token' => 0,
                    'is_gps_support' => 1,
                    'is_gps_activate' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Buat Profil Lengkap
                $birthYear = 2002 + ($i % 4);
                $birthMonth = str_pad(($i % 12) + 1, 2, '0', STR_PAD_LEFT);
                $birthDay = str_pad(($i % 27) + 1, 2, '0', STR_PAD_LEFT);
                $birthPlace = $birthPlaces[$i % count($birthPlaces)];

                Profile::create([
                    'user_id' => $userId,
                    'NIP' => 'MG-' . date('y') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'full_name' => $fullName,
                    'address' => $addressNotes,
                    'phone' => '08' . (11 + ($i % 88)) . mt_rand(1000000, 9999999),
                    'date_of_birth' => "{$birthYear}-{$birthMonth}-{$birthDay}",
                    'birth_place' => $birthPlace,
                    'gender' => $gender,
                ]);

                // Buat Data Pemagang
                $nimYear = substr($birthYear, 2, 2);
                $nimNumber = str_pad(1000 + $i, 5, '0', STR_PAD_LEFT);

                Intern::create([
                    'user_id' => $userId,
                    'school_id' => $school->id,
                    'division_id' => $division->id,
                    'nim' => "NIM/{$nimYear}/{$nimNumber}",
                    'start_date' => '2026-08-01',
                    'end_date' => '2027-01-31',
                    'home_latitude' => $lat,
                    'home_longitude' => $lng,
                    'home_address_notes' => $addressNotes,
                    'work_placement_type' => $placementType,
                    'locked_office_id' => null,
                    'blacklisted_office_id' => null,
                    'hr_placement_notes' => 'Seeder Resmi 180 Pemagang Djuragan',
                ]);

                $createdCount++;
            }

            $this->command->info("✓ Sukses membuat {$createdCount} pemagang aktif lengkap!");
        });
    }
}
