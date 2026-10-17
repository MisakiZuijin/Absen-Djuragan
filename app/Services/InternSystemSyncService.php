<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InternSystemSyncService
{
    /**
     * Sinkronkan data pendaftaran pemagang dari Absen Djuragan ke Internship Management System.
     *
     * @param array $userData Data input pendaftaran pemagang
     * @param string|null $schoolName Nama sekolah / kampus pemagang
     * @return bool
     */
    public static function syncRegistration(array $userData, ?string $schoolName = null): bool
    {
        try {
            $email = $userData['email'] ?? null;
            if (empty($email)) {
                Log::warning('Sinkronisasi ke Web Intern dibatalkan: Email kosong.');
                return false;
            }

            // Resolusi nama sekolah / universitas jika belum diberikan
            if (empty($schoolName)) {
                $schoolName = $userData['school_name'] ?? null;
            }
            if (empty($schoolName) && !empty($userData['school_id'])) {
                $school = School::find($userData['school_id']);
                $schoolName = $school?->name;
            }
            if (empty($schoolName) && !empty($userData['school_origin_id'])) {
                $school = School::find($userData['school_origin_id']);
                $schoolName = $school?->name;
            }
            if (empty($schoolName)) {
                $existingIntern = \App\Models\Intern::whereHas('user', fn($q) => $q->where('email', $email))->with('school')->first();
                $schoolName = $existingIntern?->school?->name;
            }

            // Resolusi nama divisi jika belum diberikan
            $divisionName = $userData['division_name'] ?? null;
            if (empty($divisionName) && !empty($userData['division_id'])) {
                $divisionName = \App\Models\Division::find($userData['division_id'])?->name;
            }
            if (empty($divisionName)) {
                $existingIntern = \App\Models\Intern::whereHas('user', fn($q) => $q->where('email', $email))->with('division')->first();
                $divisionName = $existingIntern?->division?->name;
            }

            // Resolusi nama brand jika belum diberikan
            $brandName = $userData['brand_name'] ?? null;
            if (empty($brandName) && !empty($userData['brand_id'])) {
                $brandName = \App\Models\Brand::find($userData['brand_id'])?->name;
            }
            if (empty($brandName)) {
                $existingIntern = \App\Models\Intern::whereHas('user', fn($q) => $q->where('email', $email))->with('brand')->first();
                $brandName = $existingIntern?->brand?->name;
            }

            // Resolusi jenis kelamin jika belum diberikan
            $gender = $userData['gender'] ?? null;
            if (empty($gender)) {
                $existingProfile = \App\Models\Profile::whereHas('user', fn($q) => $q->where('email', $email))->first();
                $gender = $existingProfile?->gender;
            }
            if (empty($gender)) {
                $existingIntern = \App\Models\Intern::whereHas('user', fn($q) => $q->where('email', $email))->with('user.profile')->first();
                $gender = $existingIntern?->user?->profile?->gender ?? null;
            }
            if (!empty($gender)) {
                $normGender = strtoupper(trim((string)$gender));
                if (in_array($normGender, ['L', 'LAKI-LAKI', 'LAKI', 'MALE', 'PRIA'], true)) {
                    $gender = 'Laki-laki';
                } elseif (in_array($normGender, ['P', 'PEREMPUAN', 'WANITA', 'FEMALE'], true)) {
                    $gender = 'Perempuan';
                }
            }

            $effectiveAddress = !empty($userData['address']) 
                ? $userData['address'] 
                : ($userData['effective_address'] ?? ($userData['gmaps_url'] ?? null));

            $payload = [
                'email'                  => $email,
                'username'               => $userData['username'] ?? null,
                'password'               => $userData['password'] ?? null,
                'password_hash'          => $userData['password_hash'] ?? null,
                'full_name'              => $userData['full_name'] ?? ($userData['username'] ?? 'User'),
                'phone'                  => $userData['phone'] ?? null,
                'parent_whatsapp_number' => $userData['parent_whatsapp_number'] ?? null,
                'gender'                 => $gender,
                'date_of_birth'          => $userData['date_of_birth'] ?? ($userData['birth_date'] ?? ($userData['born_date'] ?? null)),
                'birth_place'            => $userData['birth_place'] ?? null,
                'religion'               => $userData['religion'] ?? null,
                'address'                => $effectiveAddress,
                'school_name'            => $schoolName,
                'school_id'              => $userData['school_origin_id'] ?? ($userData['school_id'] ?? null),
                'nim'                    => $userData['nim'] ?? null,
                'division_name'          => $divisionName,
                'division_id'            => $userData['division_id'] ?? null,
                'brand_name'             => $brandName,
                'brand_id'               => $userData['brand_id'] ?? null,
                'start_date'             => $userData['start_date'] ?? ($userData['in_date'] ?? null),
                'end_date'               => $userData['end_date'] ?? ($userData['out_date'] ?? null),
                'is_gps_available'       => isset($userData['is_gps_available']) ? (bool) $userData['is_gps_available'] : true,
                'home_latitude'          => $userData['home_latitude'] ?? null,
                'home_longitude'         => $userData['home_longitude'] ?? null,
                'is_active'              => isset($userData['is_active']) ? (bool) $userData['is_active'] : true,
                'status'                 => $userData['status'] ?? 'active',
            ];

            $primaryUrl = rtrim(config('services.intern_system.url', 'http://Internship_Management_System_SevenInc-develop.test'), '/');
            $apiKey     = config('services.intern_system.api_key', 'djuragan_ext_140633f34edfaf504a3d6812c8c2485c');

            $candidateUrls = [];

            // Prioritaskan HTTPS jika request atau APP_URL menggunakan HTTPS
            if (request()->isSecure() || str_starts_with(config('app.url'), 'https://') || str_starts_with($primaryUrl, 'https://')) {
                $candidateUrls[] = preg_replace('/^http:\/\//i', 'https://', $primaryUrl) . '/api/v1/external/register-from-absen';
            }
            $candidateUrls[] = $primaryUrl . '/api/v1/external/register-from-absen';

            $candidateUrls = array_values(array_unique($candidateUrls));

            foreach ($candidateUrls as $url) {
                try {
                    $response = Http::timeout(2)->connectTimeout(2)->withoutVerifying()->withHeaders([
                        'X-API-KEY' => $apiKey,
                        'Accept'    => 'application/json',
                    ])->post($url, $payload);

                    if ($response->successful()) {
                        Log::info("Sinkronisasi pendaftaran {$email} ke Internship Management System berhasil via {$url}.");
                        return true;
                    } else {
                        Log::warning("Sinkronisasi pendaftaran {$email} ke {$url} gagal (HTTP {$response->status()}): " . $response->body());
                    }
                } catch (\Throwable $err) {
                    Log::warning("Koneksi sinkronisasi ke {$url} gagal: " . $err->getMessage());
                }
            }

            return false;
        } catch (\Throwable $e) {
            Log::error("Error sinkronisasi pendaftaran ke Internship Management System: " . $e->getMessage());
            return false;
        }
    }
}
