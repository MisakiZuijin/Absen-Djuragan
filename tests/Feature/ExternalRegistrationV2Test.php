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

    public function test_register_sync_creates_new_intern_with_brand_and_inactive_status()
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
                    'is_active' => false, // Kebijakan: Masuk ke kondisi belum aktif
                ],
            ]);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals(3, $user->role_id);
        // Akun harus dalam kondisi belum aktif
        $this->assertFalse((bool) $user->is_active);

        $profile = Profile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('Budi Test V2', $profile->full_name);
        // Alamat domisili dari form pendaftaran web intern TIDAK disimpan (tetap '-')
        $this->assertEquals('-', $profile->address);

        $intern = Intern::where('user_id', $user->id)->first();
        $this->assertNotNull($intern);
        $this->assertEquals('2026-11-01', $intern->start_date);
        $this->assertEquals('2027-04-30', $intern->end_date);

        // Verifikasi Brand tersimpan dengan benar
        $this->assertNotNull($intern->brand_id, 'Brand ID harus tersimpan di interns.brand_id!');
        $brand = Brand::find($intern->brand_id);
        $this->assertNotNull($brand);
        $this->assertStringContainsString('Seven Inc', $brand->name);

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
            'brand_name'    => 'Seven Inc',
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

        // 3. Admin Web Intern mengedit tanggal magang / brand & memicu re-sync dengan status active
        $payload2 = [
            'email'         => $email,
            'full_name'     => 'Andi Perlindungan (Updated)',
            'brand_name'    => 'Seven Inc',
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

        // Dan status akun tetap false (menunggu aktivasi admin absen secara manual)
        $this->assertFalse((bool) $user->fresh()->is_active);
    }

    public function test_status_exited_deactivates_user_account()
    {
        $email = 'intern_exit_' . uniqid() . '@example.com';

        // 1. Buat pemagang (otomatis non-aktif di awal agar admin tahu ada data baru)
        $payload = [
            'email'         => $email,
            'password_hash' => Hash::make('password123'),
            'full_name'     => 'Pemagang Keluar',
            'status'        => 'active',
        ];

        $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->postJson('/api/v1/external/register-sync', $payload)->assertStatus(201);

        $user = User::where('email', $email)->first();
        $this->assertFalse((bool) $user->is_active, 'Pemagang baru dari Web Intern harus berstatus nonaktif di Absen Djuragan');

        // Admin di Absen mengaktifkan akun
        $user->update(['is_active' => true]);
        $this->assertTrue((bool) $user->fresh()->is_active);

        // 2. Pemagang keluar (exited) -> menonaktifkan akun
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
    }

    public function test_verify_intern_with_valid_credentials()
    {
        $email = 'verify_intern_' . uniqid() . '@example.com';
        $user = User::create([
            'username'  => 'intern_' . uniqid(),
            'email'     => $email,
            'password'  => 'Secret123!',
            'role_id'   => 3,
            'is_active' => true,
        ]);

        Profile::create([
            'user_id'       => $user->id,
            'full_name'     => 'Siti Nurhaliza',
            'phone'         => '081234567899',
            'gender'        => 'Perempuan',
            'date_of_birth' => '2004-01-01',
            'birth_place'   => 'Yogyakarta',
        ]);

        $division = Division::firstOrCreate(['name' => 'Programmer']);
        $brand = Brand::firstOrCreate(['name' => 'SevenInc']);
        $school = School::firstOrCreate(['name' => 'SMK Negeri 2 Yogyakarta']);

        Intern::create([
            'user_id'     => $user->id,
            'division_id' => $division->id,
            'brand_id'    => $brand->id,
            'school_id'   => $school->id,
            'nim'         => '12345/TKJ/2026',
            'start_date'  => '2026-09-01',
            'end_date'    => '2026-12-31',
        ]);

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->postJson('/api/v1/external/verify-intern', [
            'login'    => $email,
            'password' => 'Secret123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Kredensial Pemagang valid.',
                'user'    => [
                    'email'         => $email,
                    'full_name'     => 'Siti Nurhaliza',
                    'role'          => 'pemagang',
                    'role_id'       => 3,
                    'division_name' => 'Programmer',
                    'school_name'   => 'SMK Negeri 2 Yogyakarta',
                    'student_id'    => '12345/TKJ/2026',
                    'is_active'     => true,
                ],
            ]);
    }

    public function test_verify_intern_with_invalid_password()
    {
        $email = 'verify_fail_' . uniqid() . '@example.com';
        User::create([
            'username'  => 'intern_' . uniqid(),
            'email'     => $email,
            'password'  => 'CorrectPassword',
            'role_id'   => 3,
            'is_active' => true,
        ]);

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->postJson('/api/v1/external/verify-intern', [
            'login'    => $email,
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Email/Username atau password salah.',
            ]);
    }

    public function test_verify_intern_rejects_admin_role()
    {
        $email = 'admin_' . uniqid() . '@example.com';
        User::create([
            'username'  => 'admin_' . uniqid(),
            'email'     => $email,
            'password'  => 'AdminPassword',
            'role_id'   => 1, // Admin
            'is_active' => true,
        ]);

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->postJson('/api/v1/external/verify-intern', [
            'login'    => $email,
            'password' => 'AdminPassword',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }
}
