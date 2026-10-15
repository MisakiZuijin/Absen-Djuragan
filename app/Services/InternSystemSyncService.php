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

            // Resolusi nama sekolah jika belum diberikan
            if (empty($schoolName) && !empty($userData['school_origin_id'])) {
                $school = School::find($userData['school_origin_id']);
                $schoolName = $school?->name;
            }

            $effectiveAddress = !empty($userData['address']) 
                ? $userData['address'] 
                : ($userData['effective_address'] ?? ($userData['gmaps_url'] ?? null));

            $payload = [
                'email'            => $email,
                'username'         => $userData['username'] ?? null,
                'password'         => $userData['password'] ?? null,
                'password_hash'    => $userData['password_hash'] ?? null,
                'full_name'        => $userData['full_name'] ?? ($userData['username'] ?? 'User'),
                'phone'            => $userData['phone'] ?? null,
                'gender'           => $userData['gender'] ?? null,
                'date_of_birth'    => $userData['date_of_birth'] ?? null,
                'birth_place'      => $userData['birth_place'] ?? null,
                'religion'         => $userData['religion'] ?? null,
                'address'          => $effectiveAddress,
                'school_name'      => $schoolName,
                'school_id'        => $userData['school_origin_id'] ?? null,
                'nim'              => $userData['nim'] ?? null,
                'is_gps_available' => isset($userData['is_gps_available']) ? (bool) $userData['is_gps_available'] : true,
                'home_latitude'    => $userData['home_latitude'] ?? null,
                'home_longitude'   => $userData['home_longitude'] ?? null,
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
