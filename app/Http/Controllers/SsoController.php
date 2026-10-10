<?php

namespace App\Http\Controllers;

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
        $user = Auth::user();

        // Keamanan: Hanya Role Admin (1) atau Super Admin (7)
        if (!$user || !in_array((int) $user->role_id, [1, 7], true)) {
            abort(403, 'Akses terbatas untuk Admin dan Super Admin.');
        }

        $secret = config('services.external.api_key', 'djuragan_ext_140633f34edfaf504a3d6812c8c2485c');

        $roleId = (int) $user->role_id;

        $payload = [
            'email'          => $user->email,
            'name'           => $user->profile?->full_name ?? $user->name ?? $user->username,
            'username'       => $user->username,
            'role'           => 'admin', // Di bypass menjadi admin di Web Intern
            'role_id'        => $roleId,
            'is_super_admin' => ($roleId === 7),
            'timestamp'      => time(),
            'expires'        => time() + 60, // Token berlaku selama 60 detik
        ];

        $payloadJson = json_encode($payload);
        $signature = hash_hmac('sha256', $payloadJson, $secret);
        $token = base64_encode($payloadJson) . '.' . $signature;

        $targetUrl = rtrim(config('services.intern_system.url', 'http://Internship_Management_System_SevenInc-develop.test'), '/');

        // Jika Absen Djuragan dibuka dengan HTTPS, arahkan SSO ke HTTPS Web Intern juga
        if ($request->isSecure() || str_starts_with(config('app.url'), 'https://')) {
            $targetUrl = preg_replace('/^http:\/\//i', 'https://', $targetUrl);
        }

        return redirect()->away($targetUrl . '/auth/sso?token=' . urlencode($token));
    }
}
