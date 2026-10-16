<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Division;
use App\Models\Intern;
use App\Models\Profile;
use App\Models\School;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExternalRegistrationV2Test extends TestCase
{
    use DatabaseTransactions;

    protected string $apiKey = 'djuragan_ext_140633f34edfaf504a3d6812c8c2485c';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.external.api_key', $this->apiKey);
    }

    public function test_health_check_endpoint()
    {
        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->getJson('/api/v1/external/health');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Absen Djuragan External API is healthy and connected.',
            ]);
    }

    public function test_register_sync_creates_new_intern_with_default_shift_and_protected_address()
    {
        $email = 'intern_v2_' . uniqid() . '@example.com';
        $payload = [
            'email'                  => $email,
            'password_hash'          => Hash::make('password123'),
            'full_name'              => 'Budi Test V2',
            'phone'                  => '081234567890',
            'parent_whatsapp_number' => '081987654321',
            'gender'                 => 'Laki-laki',
            'born_date'              => '2004-05-15',
            'birth_place'            => 'Yogyakarta',
            'religion'               => 'Islam',
            'address'                => 'Alamat Pendaftaran Yang Tidak Boleh Disimpan',
            'school_name'            => 'Universitas Gadjah Mada',
            'student_id'             => '21/475123/TK/52100',
            'division_name'          => 'Programmer',
            'brand_name'             => 'Seven Inc',
            'start_date'             => '2026-11-01',
            'end_date'               => '2027-04-30',
            'status'                 => 'accepted',
        ];

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->postJson('/api/v1/external/register-sync', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'email'     => $email,
                    'full_name' => 'Budi Test V2',
                    'is_active' => true,
                ],
            ]);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals(3, $user->role_id);
        $this->assertTrue((bool) $user->is_active);

        $profile = Profile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('Budi Test V2', $profile->full_name);
        // Alamat domisili dari form pendaftaran web intern TIDAK disimpan (tetap '-')
        $this->assertEquals('-', $profile->address);

        $intern = Intern::where('user_id', $user->id)->first();
        $this->assertNotNull($intern);
        $this->assertEquals('2026-11-01', $intern->start_date);
        $this->assertEquals('2027-04-30', $intern->end_date);
        // Default shift adalah Shift Pagi
        $pagiShift = Shift::where('name', 'like', '%Pagi%')->first();
        $this->assertEquals($pagiShift?->id, $intern->shift_id);
    }

    public function test_resync_protects_existing_shift_office_placement_and_manual_address()
    {
        $email = 'intern_protect_' . uniqid() . '@example.com';

        // 1. Sinkronisasi pertama
        $payload1 = [
            'email'         => $email,
            'password_hash' => Hash::make('password123'),
            'full_name'     => 'Andi Perlindungan',
            'division_name' => 'Programmer',
            'start_date'    => '2026-11-01',
            'end_date'      => '2027-04-30',
            'status'        => 'accepted',
        ];

        $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->postJson('/api/v1/external/register-sync', $payload1)->assertStatus(201);

        $user = User::where('email', $email)->first();
        $intern = Intern::where('user_id', $user->id)->first();
        $profile = Profile::where('user_id', $user->id)->first();

        // 2. Simulasi HR / Admin di Web Absen mengatur shift & penempatan kerja secara khusus
        // Misalnya diubah ke Shift Siang (ID 2), work_placement_type 'custom_office', dan alamat manual
        $siangShift = Shift::where('name', 'like', '%Siang%')->first();
        $this->assertNotNull($siangShift);

        $intern->update([
            'shift_id'            => $siangShift->id,
            'work_placement_type' => 'custom_office',
            'locked_office_id'    => 1,
            'is_fixed_shift'      => true,
        ]);
        $profile->update([
            'address' => 'Jl. Kaliurang KM 9, Sleman, Yogyakarta',
        ]);

        // 3. Admin Web Intern mengedit tanggal magang / brand & memicu re-sync
        $payload2 = [
            'email'         => $email,
            'full_name'     => 'Andi Perlindungan (Updated)',
            'start_date'    => '2026-12-01',
            'end_date'      => '2027-05-31',
            'status'        => 'active',
            'address'       => 'Alamat Dari Web Intern Yang Tidak Boleh Menimpa',
        ];

        $response2 = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->postJson('/api/v1/external/register-sync', $payload2);

        $response2->assertStatus(200);

        // 4. Verifikasi proteksi: Shift, Tipe Kerja, Office ID, Fixed Shift, dan Alamat TIDAK TERTIMPA!
        $internFresh = $intern->fresh();
        $profileFresh = $profile->fresh();

        $this->assertEquals($siangShift->id, $internFresh->shift_id, 'Shift ID yang sudah diatur di Absen tidak boleh tertimpa kembali ke Shift Pagi!');
        $this->assertEquals('custom_office', $internFresh->work_placement_type, 'work_placement_type tidak boleh ditimpa!');
        $this->assertEquals(1, $internFresh->locked_office_id, 'locked_office_id tidak boleh ditimpa!');
        $this->assertTrue((bool) $internFresh->is_fixed_shift, 'is_fixed_shift tidak boleh ditimpa!');
        $this->assertEquals('Jl. Kaliurang KM 9, Sleman, Yogyakarta', $profileFresh->address, 'Alamat yang diset manual di Absen tidak boleh ditimpa!');

        // Dan tanggal magang yang diupdate dari Web Intern BERHASIL terupdate
        $this->assertEquals('2026-12-01', $internFresh->start_date);
        $this->assertEquals('2027-05-31', $internFresh->end_date);
    }

    public function test_status_exited_deactivates_user_account()
    {
        $email = 'intern_exit_' . uniqid() . '@example.com';

        // 1. Buat pemagang aktif
        $payload = [
            'email'         => $email,
            'password_hash' => Hash::make('password123'),
            'full_name'     => 'Pemagang Keluar',
            'status'        => 'accepted',
        ];

        $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->postJson('/api/v1/external/register-sync', $payload)->assertStatus(201);

        $user = User::where('email', $email)->first();
        $this->assertTrue((bool) $user->is_active);

        // 2. Pemagang keluar (exited)
        $payloadExit = [
            'email'     => $email,
            'full_name' => 'Pemagang Keluar',
            'status'    => 'exited',
        ];

        $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->postJson('/api/v1/external/register-sync', $payloadExit)->assertStatus(200);

        $this->assertFalse((bool) $user->fresh()->is_active, 'User harus dinonaktifkan saat status exited!');

        // 3. Re-aktivasi jika diubah kembali ke active
        $payloadActive = [
            'email'     => $email,
            'full_name' => 'Pemagang Keluar',
            'status'    => 'active',
        ];

        $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->postJson('/api/v1/external/register-sync', $payloadActive)->assertStatus(200);

        $this->assertTrue((bool) $user->fresh()->is_active, 'User harus aktif kembali saat status active!');
    }
}
