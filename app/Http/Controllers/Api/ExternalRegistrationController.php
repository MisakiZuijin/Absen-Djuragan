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
            'absen_user_id'          => 'nullable|integer',
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
            'status'                 => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            // Evaluasi status keaktifan akun pemagang di Web Absen
            // Kebijakan Cross-API: Setiap akun baru yang masuk dari Web Intern SELALU masuk ke kondisi
            // BELUM AKTIF (is_active = false) terlebih dahulu agar Admin di Web Absen dapat mengetahui
            // pemagang yang baru masuk, mengatur shift/kantor, dan mengaktifkannya secara manual.
            $status = !empty($validated['status']) ? strtolower(trim($validated['status'])) : null;

            // 1. Identifikasi User (Cari berdasarkan absen_user_id atau email)
            $user = null;
            if (!empty($validated['absen_user_id'])) {
                $user = User::find((int) $validated['absen_user_id']);
            }
            if (!$user) {
                $user = User::where('email', $validated['email'])->first();
            }
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
                    'is_active'       => false, // Selalu tidak aktif saat baru masuk dari Web Intern
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
                // Perbarui email jika berubah dan belum dipakai user lain
                if ($user->email !== $validated['email']) {
                    $emailTaken = User::where('email', $validated['email'])->where('id', '!=', $user->id)->exists();
                    if (!$emailTaken) {
                        $user->email = $validated['email'];
                    }
                }
                // Perbarui kata sandi jika diberikan
                if (!empty($validated['password_hash'])) {
                    $user->setRawAttributes(array_merge($user->getAttributes(), ['password' => $validated['password_hash']]));
                } elseif (!empty($validated['password'])) {
                    $user->password = $validated['password'];
                }
                $user->role_id = 3;
                if ($status && in_array($status, ['exited', 'completed', 'rejected', 'accepted'], true)) {
                    $user->is_active = false;
                } else {
                    // Jangan pernah mengaktifkan otomatis dari Web Intern; pertahankan status aktif yang diatur Admin Absen
                    $user->is_active = (bool) $user->is_active;
                }
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
            // Kebijakan Cross-API V2: Alamat domisili pendaftaran tidak disinkronkan otomatis.
            // Pertahankan alamat yang sudah disetting manual di Web Absen, atau '-' untuk pemagang baru.
            $effectiveAddress = $existingProfile?->address ?? '-';

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
                $school = School::whereRaw('LOWER(name) = ?', [strtolower($schoolName)])
                    ->orWhere('name', 'like', '%' . $schoolName . '%')
                    ->first();
                if (!$school) {
                    $school = School::create([
                        'name'                 => $schoolName,
                        'address'              => 'Indonesia',
                        'educational_level_id' => 3, // Default level (Perguruan Tinggi / Kejuruan)
                        'is_remote_school'     => false,
                    ]);
                }
                $schoolId = $school->id;
            } elseif (!empty($validated['school_id']) && School::where('id', $validated['school_id'])->exists()) {
                $schoolId = (int) $validated['school_id'];
            } else {
                $defaultSchool = School::first();
                $schoolId = $defaultSchool ? $defaultSchool->id : null;
            }

            // 4. Resolusi Divisi
            $divisionId = $this->resolveDivision(
                $validated['division_name'] ?? null,
                !empty($validated['division_id']) ? (int) $validated['division_id'] : null
            );

            // 5. Resolusi Brand (Prioritaskan brand_name lintas sistem sebelum fallback ke brand_id)
            $brandId = null;
            if (!empty($validated['brand_name'])) {
                $rawBrandName = trim($validated['brand_name']);
                $brandSlug = Str::slug($rawBrandName);
                $normalizedKey = strtolower(str_replace([' ', '-', '_'], '', $rawBrandName));

                // 1. Cari berdasarkan nama persis atau slug
                $brand = Brand::where('name', $rawBrandName)
                    ->orWhere('slug', $brandSlug)
                    ->first();

                // 2. Normalisasi variasi brand populer (SevenInc <-> Seven Inc)
                if (!$brand && $normalizedKey === 'seveninc') {
                    $brand = Brand::whereRaw("LOWER(REPLACE(REPLACE(name, ' ', ''), '-', '')) = 'seveninc'")->first();
                }

                // 3. Fallback pencarian LIKE
                if (!$brand) {
                    $brand = Brand::where('name', 'like', '%' . $rawBrandName . '%')->first();
                }

                // 4. Jika brand belum ada di Web Absen, buat otomatis (seperti halnya Sekolah / Kampus)
                if (!$brand) {
                    $brand = Brand::create([
                        'name'        => $rawBrandName,
                        'slug'        => $brandSlug ?: ('brand-' . time()),
                        'description' => "Unit bisnis / brand {$rawBrandName}",
                        'is_active'   => true,
                    ]);
                }

                $brandId = $brand ? $brand->id : null;
            }

            if (!$brandId && !empty($validated['brand_id']) && Brand::where('id', $validated['brand_id'])->exists()) {
                $brandId = (int) $validated['brand_id'];
            }

            // 6. Resolusi Shift & Proteksi Konfigurasi Absensi
            // KRITIS: Jika pemagang SUDAH ADA di Web Absen, PERTAHANKAN shift_id dan tipe penempatan kerja
            // yang sudah diset oleh HR / Admin di Web Absen! Dilarang menimpa ke Shift Pagi saat re-sync.
            $existingIntern = Intern::where('user_id', $user->id)->first();

            if ($existingIntern && $existingIntern->shift_id) {
                $effectiveShiftId = $existingIntern->shift_id;
            } else {
                $defaultShift = Shift::where('name', 'like', '%Pagi%')->first() ?: Shift::first();
                $effectiveShiftId = $defaultShift ? $defaultShift->id : null;
            }

            // 7. Simpan / Perbarui Data Magang (Interns)
            $startDate = !empty($validated['start_date']) ? $validated['start_date'] : ($existingIntern?->start_date ?? now()->toDateString());
            $endDate   = !empty($validated['end_date']) ? $validated['end_date'] : ($existingIntern?->end_date ?? now()->addMonths(3)->toDateString());
            $effectiveNim = !empty($validated['student_id']) ? $validated['student_id'] : ($existingIntern?->nim ?? null);
            $effectiveSchoolId = $schoolId ?: ($existingIntern?->school_id ?? null);
            $effectiveDivisionId = $divisionId ?: ($existingIntern?->division_id ?? null);
            $effectiveBrandId = $brandId ?: ($existingIntern?->brand_id ?? null);

            // Pengaturan khusus absensi yang 100% dikendalikan di Web Absen
            $effectiveWorkPlacement = $existingIntern?->work_placement_type ?? 'auto_office';
            $effectiveHybridGroup   = $existingIntern?->hybrid_group ?? 'auto';
            $effectiveLockedOffice  = $existingIntern?->locked_office_id ?? null;
            $effectiveIsFixedShift  = $existingIntern?->is_fixed_shift ?? false;

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
                    'work_placement_type' => $effectiveWorkPlacement,
                    'hybrid_group'        => $effectiveHybridGroup,
                    'locked_office_id'    => $effectiveLockedOffice,
                    'is_fixed_shift'      => $effectiveIsFixedShift,
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
                "Sinkronisasi pendaftaran pemagang via API V2: {$validated['full_name']} ({$validated['email']}) - Status: " . ($status ?: 'accepted')
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
                    'shift_id'      => $intern->shift_id,
                    'is_active'     => (bool) $user->is_active,
                    'start_date'    => $intern->start_date,
                    'end_date'      => $intern->end_date,
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
     * Verifikasi kredensial login Pemagang untuk Remote Login Fallback dari Web Intern.
     */
    public function verifyIntern(Request $request): JsonResponse
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
                'message' => 'Akun pemagang tidak ditemukan di Absen Djuragan.',
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

        // Khusus role Pemagang (3)
        if ($roleId !== 3) {
            if (in_array($roleId, [1, 7], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun ini adalah Admin/Super Admin, silakan gunakan login Admin.',
                ], 403);
            }
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Akun bukan pemagang.',
            ], 403);
        }

        $user->loadMissing(['profile', 'intern.school', 'intern.division', 'intern.brand', 'intern.whatsappNumber']);
        $profile = $user->profile;
        $intern  = $user->intern;

        $rawGender = $profile?->gender;
        $normGender = null;
        if (!empty($rawGender)) {
            $gLower = strtolower(trim($rawGender));
            if (in_array($gLower, ['p', 'perempuan', 'female', 'wanita'], true)) {
                $normGender = 'Perempuan';
            } elseif (in_array($gLower, ['l', 'laki-laki', 'male', 'pria'], true)) {
                $normGender = 'Laki-laki';
            } else {
                $normGender = $rawGender;
            }
        }

        $rawReligion = $profile?->religion;
        $normReligion = null;
        if (!empty($rawReligion)) {
            $rLower = strtolower(trim($rawReligion));
            if (in_array($rLower, ['kristen', 'kristen protestan', 'protestan'], true)) {
                $normReligion = 'Kristen Protestan';
            } elseif (in_array($rLower, ['khonghucu', 'konghucu'], true)) {
                $normReligion = 'Khonghucu';
            } elseif ($rLower === 'islam') {
                $normReligion = 'Islam';
            } elseif ($rLower === 'katolik') {
                $normReligion = 'Katolik';
            } elseif ($rLower === 'hindu') {
                $normReligion = 'Hindu';
            } elseif (in_array($rLower, ['buddha', 'budha'], true)) {
                $normReligion = 'Buddha';
            } else {
                $normReligion = trim($rawReligion);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Kredensial Pemagang valid.',
            'user'    => [
                'absen_user_id' => $user->id,
                'email'         => $user->email,
                'username'      => $user->username,
                'full_name'              => $profile?->full_name ?? $user->name ?? $user->username,
                'phone'                  => $profile?->phone ?? $intern?->whatsappNumber?->phone_number,
                'parent_whatsapp_number' => $intern?->whatsappNumber?->phone_number,
                'gender'                 => $normGender,
                'birth_place'   => $profile?->birth_place,
                'born_date'     => $profile?->date_of_birth,
                'religion'      => $normReligion,
                'address'       => $profile?->address,
                'student_id'    => $intern?->nim,
                'school_name'   => $intern?->school?->name ?? (!empty($intern?->school_id) ? \App\Models\School::find($intern->school_id)?->name : null),
                'division_name' => $intern?->division?->name ?? (!empty($intern?->division_id) ? \App\Models\Division::find($intern->division_id)?->name : null),
                'brand_name'    => $intern?->brand?->name ?? (!empty($intern?->brand_id) ? \App\Models\Brand::find($intern->brand_id)?->name : null),
                'brand_id'      => $intern?->brand_id,
                'start_date'    => $intern?->start_date,
                'end_date'      => $intern?->end_date,
                'role'          => 'pemagang',
                'role_id'       => 3,
                'is_active'     => (bool) $user->is_active,
                'status'        => $user->is_active ? 'active' : 'accepted',
            ],
        ]);
    }

    /**
     * Resolusi ID Divisi dengan pencocokan nama persis, kamus sinonim, 2-way flexible matching, dan auto-create.
     */
    protected function resolveDivision(?string $divisionName, ?int $divisionId = null): ?int
    {
        if (empty($divisionName) || trim($divisionName) === '-' || trim($divisionName) === '') {
            if (!empty($divisionId) && Division::where('id', $divisionId)->exists()) {
                return (int) $divisionId;
            }
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
