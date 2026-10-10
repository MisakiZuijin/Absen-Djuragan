<?php

namespace App\Http\Controllers\Api;

use App\Helper\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Division;
use App\Models\Intern;
use App\Models\InternAccount;
use App\Models\Profile;
use App\Models\School;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExternalRegistrationController extends Controller
{
    /**
     * Health check endpoint untuk memastikan konektivitas dan autentikasi API.
     */
    public function healthCheck(): JsonResponse
    {
        return response()->json([
            'success'     => true,
            'message'     => 'Absen Djuragan External API is healthy and connected.',
            'server_time' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Endpoint untuk sinkronisasi pendaftaran pemagang dari sistem eksternal (Internship Management System).
     */
    public function registerSync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'                  => 'required|email|max:100',
            'password'               => 'nullable|string|min:6',
            'password_hash'          => 'nullable|string',
            'full_name'              => 'required|string|max:100',
            'phone'                  => 'nullable|string|max:20',
            'parent_whatsapp_number' => 'nullable|string|max:20',
            'gender'                 => 'nullable|string|max:20',
            'born_date'              => 'nullable|date',
            'birth_place'            => 'nullable|string|max:50',
            'religion'               => 'nullable|string|max:50',
            'address'                => 'nullable|string|max:255',
            'school_name'            => 'nullable|string|max:255',
            'student_id'             => 'nullable|string|max:50',
            'division_name'          => 'nullable|string|max:100',
            'division_id'            => 'nullable|integer',
            'brand_name'             => 'nullable|string|max:100',
            'brand_id'               => 'nullable|integer',
            'start_date'             => 'nullable|date',
            'end_date'               => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            // 1. Identifikasi User (Cari berdasarkan email)
            $user = User::where('email', $validated['email'])->first();
            $isNewUser = false;

            if (!$user) {
                $isNewUser = true;

                if (empty($validated['password']) && empty($validated['password_hash'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Password atau password_hash wajib disertakan untuk pembuatan akun baru di Absen Djuragan.',
                    ], 422);
                }

                // Generate username unik dari nama lengkap
                $cleanName = preg_replace('/[^a-zA-Z0-9]/', '.', strtolower(trim($validated['full_name'])));
                $cleanName = trim(preg_replace('/\.+/', '.', (string) $cleanName), '.');
                if (empty($cleanName)) {
                    $cleanName = explode('@', $validated['email'])[0];
                }

                $baseUsername = substr($cleanName, 0, 24);
                $candidateUsername = $baseUsername;
                $counter = 1;
                while (User::where('username', $candidateUsername)->exists()) {
                    $candidateUsername = substr($baseUsername, 0, 20) . $counter;
                    $counter++;
                }

                $user = new User([
                    'username'        => $candidateUsername,
                    'email'           => $validated['email'],
                    'role_id'         => 3, // Role Pemagang
                    'is_active'       => true, // Diaktifkan karena telah disetujui / di-ACC dari IMS
                    'is_confirm'      => true, // Dikonfirmasi
                    'is_gps_support'  => false,
                    'is_gps_activate' => false,
                    'os'              => 'Windows',
                    'browser'         => 'Chrome',
                    'device'          => bin2hex(random_bytes(8)),
                ]);

                if (!empty($validated['password_hash'])) {
                    $user->setRawAttributes(array_merge($user->getAttributes(), ['password' => $validated['password_hash']]));
                } else {
                    $user->password = $validated['password'];
                }

                $user->save();
            } else {
                // Perbarui kata sandi jika diberikan
                if (!empty($validated['password_hash'])) {
                    $user->setRawAttributes(array_merge($user->getAttributes(), ['password' => $validated['password_hash']]));
                } elseif (!empty($validated['password'])) {
                    $user->password = $validated['password'];
                }
                $user->role_id = 3;
                $user->is_active = true;
                $user->is_confirm = true;
                $user->save();
            }

            // 2. Pemetaan & Simpan Profil Pengguna
            $gender = null;
            if (!empty($validated['gender'])) {
                $gLower = strtolower($validated['gender']);
                if (str_contains($gLower, 'perempuan') || $gLower === 'p' || $gLower === 'female') {
                    $gender = 'Perempuan';
                } elseif (str_contains($gLower, 'laki') || $gLower === 'l' || $gLower === 'male') {
                    $gender = 'Laki-laki';
                } else {
                    $gender = $validated['gender'];
                }
            }

            $existingProfile = Profile::where('user_id', $user->id)->first();
            $effectiveAddress = !empty($validated['address']) && $validated['address'] !== '-'
                ? $validated['address']
                : ($existingProfile?->address ?? '-');

            $profile = Profile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name'     => $validated['full_name'],
                    'phone'         => !empty($validated['phone']) && $validated['phone'] !== '-' ? preg_replace('/[\s\-]+/', '', $validated['phone']) : ($existingProfile?->phone ?? '-'),
                    'date_of_birth' => !empty($validated['born_date']) ? $validated['born_date'] : ($existingProfile?->date_of_birth ?? now()->subYears(18)->toDateString()),
                    'birth_place'   => !empty($validated['birth_place']) && $validated['birth_place'] !== '-' ? $validated['birth_place'] : ($existingProfile?->birth_place ?? '-'),
                    'gender'        => $gender ?? ($existingProfile?->gender ?? null),
                    'religion'      => !empty($validated['religion']) ? $validated['religion'] : ($existingProfile?->religion ?? null),
                    'address'       => $effectiveAddress,
                ]
            );

            // 3. Resolusi Sekolah / Kampus
            $schoolId = null;
            if (!empty($validated['school_name'])) {
                $schoolName = trim($validated['school_name']);
                $school = School::where('name', 'like', '%' . $schoolName . '%')->first();
                if (!$school) {
                    $school = School::create([
                        'name'                 => $schoolName,
                        'address'              => $validated['address'] ?? 'Indonesia',
                        'educational_level_id' => 3, // Default level (Perguruan Tinggi / Kejuruan)
                        'is_remote_school'     => false,
                    ]);
                }
                $schoolId = $school->id;
            } else {
                $defaultSchool = School::first();
                $schoolId = $defaultSchool ? $defaultSchool->id : null;
            }

            // 4. Resolusi Divisi
            $divisionId = $this->resolveDivision(
                $validated['division_name'] ?? null,
                !empty($validated['division_id']) ? (int) $validated['division_id'] : null
            );

            // 5. Resolusi Brand
            $brandId = null;
            if (!empty($validated['brand_id']) && Brand::where('id', $validated['brand_id'])->exists()) {
                $brandId = (int) $validated['brand_id'];
            } elseif (!empty($validated['brand_name'])) {
                $brand = Brand::where('name', 'like', '%' . trim($validated['brand_name']) . '%')->first();
                $brandId = $brand ? $brand->id : null;
            }

            // 6. Resolusi Shift (Default Shift Pagi)
            $shift = Shift::where('name', 'like', '%Pagi%')->first() ?: Shift::first();
            $shiftId = $shift ? $shift->id : null;

            // 7. Simpan / Perbarui Data Magang (Interns)
            $existingIntern = Intern::where('user_id', $user->id)->first();
            $startDate = !empty($validated['start_date']) ? $validated['start_date'] : ($existingIntern?->start_date ?? now()->toDateString());
            $endDate   = !empty($validated['end_date']) ? $validated['end_date'] : ($existingIntern?->end_date ?? now()->addMonths(3)->toDateString());
            $effectiveNim = !empty($validated['student_id']) ? $validated['student_id'] : ($existingIntern?->nim ?? null);
            $effectiveSchoolId = $schoolId ?: ($existingIntern?->school_id ?? null);
            $effectiveDivisionId = $divisionId ?: ($existingIntern?->division_id ?? null);
            $effectiveBrandId = $brandId ?: ($existingIntern?->brand_id ?? null);
            $effectiveShiftId = $shiftId ?: ($existingIntern?->shift_id ?? null);

            $intern = Intern::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'school_id'           => $effectiveSchoolId,
                    'division_id'         => $effectiveDivisionId,
                    'brand_id'            => $effectiveBrandId,
                    'shift_id'            => $effectiveShiftId,
                    'nim'                 => $effectiveNim,
                    'start_date'          => $startDate,
                    'end_date'            => $endDate,
                    'work_placement_type' => $existingIntern?->work_placement_type ?? 'auto_office',
                    'hybrid_group'        => $existingIntern?->hybrid_group ?? 'auto',
                ]
            );

            // Inisialisasi Akun Pendukung
            InternAccount::firstOrCreate(['intern_id' => $intern->id]);

            // 8. Simpan Nomor WA Orang Tua (Jika Ada)
            if (!empty($validated['parent_whatsapp_number'])) {
                $cleanParentWa = preg_replace('/[\s\-\.\(\)]+/', '', trim($validated['parent_whatsapp_number']));
                if (str_starts_with($cleanParentWa, '+')) {
                    $cleanParentWa = substr($cleanParentWa, 1);
                }
                $intern->whatsappNumber()->updateOrCreate(
                    ['intern_id' => $intern->id],
                    ['phone_number' => $cleanParentWa]
                );
            }

            DB::commit();

            ActivityLogger::log(
                'CREATE',
                'API External',
                "Sinkronisasi pendaftaran pemagang via API: {$validated['full_name']} ({$validated['email']})"
            );

            return response()->json([
                'success' => true,
                'message' => $isNewUser
                    ? 'Akun dan data pemagang baru berhasil disinkronkan ke Absen Djuragan.'
                    : 'Akun pemagang yang sudah ada berhasil diperbarui di Absen Djuragan.',
                'data'    => [
                    'absen_user_id' => $user->id,
                    'username'      => $user->username,
                    'email'         => $user->email,
                    'full_name'     => $profile->full_name,
                    'intern_id'     => $intern->id,
                    'division_id'   => $intern->division_id,
                    'brand_id'      => $intern->brand_id,
                    'school_id'     => $intern->school_id,
                    'is_active'     => (bool) $user->is_active,
                ],
            ], $isNewUser ? 201 : 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('External API Register Sync Error: ' . $e->getMessage(), [
                'trace'   => $e->getTraceAsString(),
                'payload' => $request->except(['password']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses sinkronisasi pendaftaran di Absen Djuragan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verifikasi kredensial login Admin / Super Admin untuk Remote Login Fallback dari Web Intern.
     */
    public function verifyAdmin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($validated['login']);
        $user = User::where('email', $loginInput)
            ->orWhere('username', $loginInput)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak ditemukan di Absen Djuragan.',
            ], 404);
        }

        $passwordValid = \Illuminate\Support\Facades\Hash::check($validated['password'], $user->password)
            || ($user->password === $validated['password']);

        if (!$passwordValid) {
            return response()->json([
                'success' => false,
                'message' => 'Email/Username atau password salah.',
            ], 401);
        }

        $roleId = (int) $user->role_id;

        // Keamanan ketat: Hanya izinkan role Admin (1) atau Super Admin (7)
        if (!in_array($roleId, [1, 7], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Akun bukan Admin atau Super Admin.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kredensial Admin/Super Admin valid.',
            'user'    => [
                'email'          => $user->email,
                'username'       => $user->username,
                'name'           => $user->profile?->full_name ?? $user->name ?? $user->username,
                'role'           => 'admin', // Bypass: Super Admin maupun Admin dijadikan 'admin' di Web Intern
                'role_id'        => $roleId,
                'is_super_admin' => ($roleId === 7),
            ],
        ]);
    }

    /**
     * Resolusi ID Divisi dengan pencocokan nama persis, kamus sinonim, 2-way flexible matching, dan auto-create.
     */
    protected function resolveDivision(?string $divisionName, ?int $divisionId = null): ?int
    {
        // 1. Cek division_id langsung jika valid
        if (!empty($divisionId) && Division::where('id', $divisionId)->exists()) {
            return (int) $divisionId;
        }

        if (empty($divisionName) || trim($divisionName) === '-' || trim($divisionName) === '') {
            $defaultDiv = Division::first();
            return $defaultDiv ? $defaultDiv->id : null;
        }

        $rawName = trim($divisionName);
        $clean   = strtolower($rawName);

        // 2. Cek kecocokan nama persis (case-insensitive)
        $div = Division::whereRaw('LOWER(name) = ?', [$clean])->first();
        if ($div) return $div->id;

        // 3. Kamus sinonim & variasi penamaan
        $synonyms = [
            'programmer (front end / backend)'  => 'Programmer',
            'programmer (frontend / backend)'   => 'Programmer',
            'web programmer'                    => 'Programmer',
            'developer'                         => 'Programmer',
            'frontend'                          => 'Programmer',
            'backend'                           => 'Programmer',
            'administration'                    => 'Administrasi',
            'admin'                             => 'Administrasi',
            'adm'                               => 'Administrasi',
            'human resources (hr)'              => 'Human Resource',
            'human resources'                   => 'Human Resource',
            'hrd'                               => 'Human Resource',
            'hr'                                => 'Human Resource',
            'ui/ux'                             => 'UI/UX Designer',
            'uiux'                              => 'UI/UX Designer',
            'ui-ux'                             => 'UI/UX Designer',
            'photographer'                      => 'Fotografer',
            'fotografi'                         => 'Fotografer',
            'videographer'                      => 'Videografer',
            'videografi'                        => 'Videografer',
            'graphic designer (konten kreatif)' => 'Desain Grafis',
            'graphic designer'                  => 'Desain Grafis',
            'desainer grafis'                   => 'Desain Grafis',
            'social media'                      => 'Social Media Specialist',
            'smm'                               => 'Social Media Specialist',
            'content writer'                    => 'Content Writer',
            'content writter'                   => 'Content Writer',
            'penulis konten'                    => 'Content Writer',
            'sales & marketing'                 => 'Marketing / Sales',
            'sales and marketing'               => 'Marketing / Sales',
            'marketing'                         => 'Marketing / Sales',
            'sales'                             => 'Marketing / Sales',
            'public relations (marcomm)'        => 'Marcom/Public Relation',
            'public relations'                  => 'Marcom/Public Relation',
            'marcomm'                           => 'Marcom/Public Relation',
            'marcom'                            => 'Marcom/Public Relation',
            'pr'                                => 'Marcom/Public Relation',
            'tiktok'                            => 'Tiktok Creator',
            'kreator tiktok'                    => 'Tiktok Creator',
            'pengelasan'                        => 'Las',
            'welder'                            => 'Las',
            'welding'                           => 'Las',
            'animation'                         => 'Animasi',
            'animator'                          => 'Animasi',
            'cs'                                => 'Customer Service',
            'layanan pelanggan'                 => 'Customer Service',
            'host'                              => 'Host/Presenter',
            'presenter'                         => 'Host/Presenter',
            'voice over'                        => 'Voice Over Talent',
            'pengisi suara'                     => 'Voice Over Talent',
            'rnd'                               => 'Research & Development',
            'r&d'                               => 'Research & Development',
        ];

        if (isset($synonyms[$clean])) {
            $mapped = Division::where('name', $synonyms[$clean])->first();
            if ($mapped) return $mapped->id;
        }

        // 4. Flexible 2-way search (nama mengandung query ATAU query mengandung nama)
        $flexible = Division::where(function ($q) use ($clean) {
            $q->whereRaw('LOWER(name) LIKE ?', ["%{$clean}%"])
              ->orWhereRaw('? LIKE CONCAT("%", LOWER(name), "%")', [$clean]);
        })->first();

        if ($flexible) return $flexible->id;

        // 5. Jika tidak ditemukan, jangan default ke UI/UX Designer! Buat divisi baru agar data tidak hilang/salah.
        $newDiv = Division::create([
            'name' => ucwords($clean),
            'icon' => 'folder-open-regular.svg',
        ]);

        return $newDiv->id;
    }
}
