<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SsoRedirectTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_redirects_to_intern_with_valid_sso_token()
    {
        $admin = User::where('role_id', 1)->first();
        if (!$admin) {
            $admin = User::create([
                'username' => 'admin_sso_' . uniqid(),
                'email' => 'admin_sso_' . uniqid() . '@example.com',
                'password' => bcrypt('password'),
                'role_id' => 1,
                'is_active' => true,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.sso.to-intern'));

        $response->assertStatus(302);
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('/auth/sso?token=', $redirectUrl);

        // Parse token dari URL
        parse_str(parse_url($redirectUrl, PHP_URL_QUERY), $queryParams);
        $token = $queryParams['token'] ?? null;
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('.', $token);

        [$encodedPayload, $signature] = explode('.', $token, 2);
        $normalized = strtr($encodedPayload, '-_', '+/');
        $pad = strlen($normalized) % 4;
        if ($pad > 0) {
            $normalized .= str_repeat('=', 4 - $pad);
        }
        $json = base64_decode($normalized);
        $payload = json_decode($json, true);

        $this->assertEquals($admin->email, $payload['email']);
        $this->assertEquals('admin', $payload['role']);

        $secret = config('services.external.api_key')
            ?: config('services.intern_system.api_key')
            ?: 'djuragan_ext_140633f34edfaf504a3d6812c8c2485c';

        $expectedSignature = hash_hmac('sha256', $json, $secret);
        $this->assertEquals($expectedSignature, $signature);
    }

    public function test_non_admin_cannot_access_sso_to_intern()
    {
        $internUser = User::where('role_id', 3)->first();
        if (!$internUser) {
            $internUser = User::create([
                'username' => 'intern_user_' . uniqid(),
                'email' => 'intern_' . uniqid() . '@example.com',
                'password' => bcrypt('password'),
                'role_id' => 3,
                'is_active' => true,
            ]);
        }

        $response = $this->actingAs($internUser)->get(route('admin.sso.to-intern'));
        // Middleware menolak dan redirect ke /
        $response->assertRedirect('/');
    }
}
