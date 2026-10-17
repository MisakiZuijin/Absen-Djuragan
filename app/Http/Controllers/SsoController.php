<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SsoController extends Controller
{
    /**
     * Redirect Admin atau Super Admin dari Absen Djuragan ke Web Intern via Single Sign-On (SSO).
     */
    public function redirectToIntern(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        // Keamanan: Role Admin (1), Super Admin (7), atau Pemagang (3)
        if (!$user || !in_array((int) $user->role_id, [1, 7, 3], true)) {
            abort(403, 'Akses terbatas untuk Pengguna Terdaftar Absen Djuragan.');
        }

        $secret = config('services.external.api_key')
            ?: config('services.intern_system.api_key')
            ?: 'djuragan_ext_140633f34edfaf504a3d6812c8c2485c';

        $roleId = (int) $user->role_id;
        $isIntern = ($roleId === 3);

        $payload = [
            'email'          => $user->email,
            'name'           => $user->profile?->full_name ?? $user->name ?? $user->username,
            'username'       => $user->username,
            'role'           => $isIntern ? 'pemagang' : 'admin',
            'role_id'        => $roleId,
            'is_super_admin' => ($roleId === 7),
            'timestamp'      => time(),
            'expires'        => time() + 300, // Token berlaku selama 5 menit (300 detik)
        ];

        // Jika pemagang, sertakan data profil dan magang untuk sinkronisasi otomatis di Web Intern
        if ($isIntern) {
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

            $payload['intern_data'] = [
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
                'is_active'     => (bool) $user->is_active,
                'status'        => $user->is_active ? 'active' : 'accepted',
            ];
        }

        $payloadJson = json_encode($payload);
        $signature = hash_hmac('sha256', $payloadJson, $secret);

        // Gunakan URL-safe base64 encoding RFC 4648 (tanpa padding '=' dan tanpa titik '.')
        $encodedPayload = rtrim(strtr(base64_encode($payloadJson), '+/', '-_'), '=');
        $token = $encodedPayload . '.' . $signature;

        $targetUrl = rtrim(config('services.intern_system.url', 'http://Internship_Management_System_SevenInc-develop.test'), '/');

        // Jika Absen Djuragan dibuka dengan HTTPS, arahkan SSO ke HTTPS Web Intern juga
        if ($request->isSecure() || str_starts_with(config('app.url'), 'https://')) {
            $targetUrl = preg_replace('/^http:\/\//i', 'https://', $targetUrl);
        }

        return redirect()->away($targetUrl . '/auth/sso?token=' . urlencode($token));
    }
}
