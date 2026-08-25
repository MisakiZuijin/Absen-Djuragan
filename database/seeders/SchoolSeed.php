<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolSeed extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        School::create([
            "name" => "Politeknik Negeri Jember",
            "address" => "Jl. Mastrip, Jember",
            "educational_level_id" => 4
        ]);

        School::create([
            "name" => "Politeknik Negeri Semarang",
            "address" => "Jl. Raya Semarang",
            "educational_level_id" => 4
        ]);

        School::create([
            "name" => "Politeknik Negeri Malang",
            "address" => "Jl. Merdeka, Malang",
            "educational_level_id" => 4
        ]);

        School::create([
            "name" => "Politeknik Negeri Bandung",
            "address" => "Jl. Polban, Bandung",
            "educational_level_id" => 4
        ]);

        School::create([
            "name" => "Politeknik Negeri Jakarta",
            "address" => "Jl. Ir. H. Juanda, Jakarta",
            "educational_level_id" => 4
        ]);

        School::create([
            "name" => "Politeknik Manufaktur Bandung",
            "address" => "Jl. Soekarno-Hatta, Bandung",
            "educational_level_id" => 4
        ]);

        School::create([
            "name" => "Politeknik Negeri Batam",
            "address" => "Jl. Ghazali, Batam",
            "educational_level_id" => 4
        ]);

        School::create([
            "name" => "Universitas Gadjah Mada",
            "address" => "Jl. Kridosono, Yogyakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Negeri Yogyakarta",
            "address" => "Jl. Colombo, Yogyakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Islam Indonesia",
            "address" => "Jl. Kaliurang, Yogyakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Muhammadiyah Yogyakarta",
            "address" => "Jl. Ring Road Selatan, Yogyakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Atma Jaya Yogyakarta",
            "address" => "Jl. Babarsari, Yogyakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Sanata Dharma",
            "address" => "Jl. Mrican, Yogyakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Ahmad Dahlan",
            "address" => "Jl. Kapas, Yogyakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Indonesia",
            "address" => "Jl. Raya UI, Depok",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Institut Teknologi Bandung",
            "address" => "Jl. Ganesha, Bandung",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Padjadjaran",
            "address" => "Jl. Dipati Ukur, Bandung",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Brawijaya",
            "address" => "Jl. Veteran, Malang",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Airlangga",
            "address" => "Jl. Mayjen Sungkono, Surabaya",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Hasanuddin",
            "address" => "Jl. Perintis Kemerdekaan, Makassar",
            "educational_level_id" => 3
        ]);

        // SMK di Yogyakarta dan Sekitarnya
        School::create([
            "name" => "SMK Negeri 1 Yogyakarta",
            "address" => "Jl. Kemetiran Kidul, Yogyakarta",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 2 Yogyakarta",
            "address" => "Jl. A.M. Sangaji, Yogyakarta",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 3 Yogyakarta",
            "address" => "Jl. Wirobrajan, Yogyakarta",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 4 Yogyakarta",
            "address" => "Jl. Sidikan, Yogyakarta",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 5 Yogyakarta",
            "address" => "Jl. Kenari, Yogyakarta",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Muhammadiyah 1 Yogyakarta",
            "address" => "Jl. Gotong Royong, Yogyakarta",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 1 Bantul",
            "address" => "Jl. Parangtritis, Bantul",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 1 Sleman",
            "address" => "Jl. Magelang, Sleman",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 1 Wates",
            "address" => "Jl. Wates, Kulon Progo",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Piri 1 Yogyakarta",
            "address" => "Jl. Kemuning, Yogyakarta",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Piri 2 Yogyakarta",
            "address" => "Jl. Wates, Yogyakarta",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 1 Depok",
            "address" => "Jl. Ringroad Utara, Sleman",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 1 Godean",
            "address" => "Jl. Godean, Sleman",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 2 Depok",
            "address" => "Jl. Wahid Hasyim, Sleman",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Muhammadiyah 2 Yogyakarta",
            "address" => "Jl. Pramuka, Yogyakarta",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 1 Jetis",
            "address" => "Jl. Parangtritis, Bantul",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 1 Pundong",
            "address" => "Jl. Pundong, Bantul",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 1 Temon",
            "address" => "Jl. Wates-Kulon Progo, Kulon Progo",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 2 Wonosari",
            "address" => "Jl. Ki Hajar Dewantara, Gunungkidul",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Muhammadiyah 1 Bantul",
            "address" => "Jl. Parangtritis, Bantul",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Muhammadiyah 1 Sleman",
            "address" => "Jl. Magelang, Sleman",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 3 Bantul",
            "address" => "Jl. Srandakan, Bantul",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 1 Nglipar",
            "address" => "Jl. Wonosari-Semin, Gunungkidul",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 1 Playen",
            "address" => "Jl. Playen, Gunungkidul",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Negeri 1 Karangmojo",
            "address" => "Jl. Karangmojo, Gunungkidul",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "SMK Telkom Purwokerto",
            "address" => "Jl. Di Panjaitan, Banyumas",
            "educational_level_id" => 2
        ]);

        School::create([
            "name" => "Universitas Pembangunan Nasional Veteran Yogyakarta",
            "address" => "Jl. SWK 104, Yogyakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Teknologi Yogyakarta",
            "address" => "Jl. Ring Road Utara, Yogyakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Sarjanawiyata Tamansiswa",
            "address" => "Jl. Kusumanegara, Yogyakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Kristen Duta Wacana",
            "address" => "Jl. Dr. Wahidin Sudirohusodo, Yogyakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Islam Negeri Sunan Kalijaga",
            "address" => "Jl. Laksda Adisucipto, Yogyakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Mercu Buana Yogyakarta",
            "address" => "Jl. Wates, Yogyakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Janabadra",
            "address" => "Jl. Tentara Rakyat Mataram, Yogyakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Amikom Yogyakarta",
            "address" => "Jl. Ring Road Utara, Sleman",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Cokroaminoto Yogyakarta",
            "address" => "Jl. Perintis Kemerdekaan, Yogyakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Widya Mataram",
            "address" => "Jl. Kraton, Yogyakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Institut Teknologi Sepuluh Nopember",
            "address" => "Jl. Raya ITS, Surabaya",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Diponegoro",
            "address" => "Jl. Prof. Soedarto, Semarang",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Sebelas Maret",
            "address" => "Jl. Ir. Sutami, Surakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Telkom",
            "address" => "Jl. Telekomunikasi, Bandung",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Muhammadiyah Surakarta",
            "address" => "Jl. Ahmad Yani, Surakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Trisakti",
            "address" => "Jl. Kyai Tapa, Jakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Gunadarma",
            "address" => "Jl. Margonda Raya, Depok",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Bina Nusantara (Binus)",
            "address" => "Jl. Kebon Jeruk Raya, Jakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Katolik Parahyangan",
            "address" => "Jl. Ciumbuleuit, Bandung",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Surabaya",
            "address" => "Jl. Raya Kalirungkut, Surabaya",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Esa Unggul",
            "address" => "Jl. Arjuna Utara, Jakarta",
            "educational_level_id" => 3
        ]);

        School::create([
            "name" => "Universitas Multimedia Nusantara",
            "address" => "Jl. Scientia Boulevard, Tangerang",
            "educational_level_id" => 3
        ]);
    }
}
