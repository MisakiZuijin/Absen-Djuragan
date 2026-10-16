<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\DetailSchedule;
use App\Models\Intern;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AttendanceResetTest extends TestCase
{
    use DatabaseTransactions;

    protected function getAdminUser()
    {
        $admin = User::where('role_id', 1)->first();
        if (!$admin) {
            $admin = User::create([
                'username' => 'admin_reset_' . uniqid(),
                'email' => 'admin_reset_' . uniqid() . '@example.com',
                'password' => bcrypt('password'),
                'role_id' => 1,
                'is_active' => true,
            ]);
        }
        return $admin;
    }

    public function test_reset_attendance_from_hadir_returns_to_dijadwalkan()
    {
        $admin = $this->getAdminUser();

        $user = User::create([
            'username' => 'intern_test_' . uniqid(),
            'email' => 'intern_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role_id' => 3,
            'is_active' => true,
        ]);

        $intern = Intern::create([
            'user_id' => $user->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'shift_id' => 2,
        ]);

        $schedule = Schedule::create([
            'intern_id' => $intern->id,
            'type' => 'daily',
            'start_period' => now()->toDateString(),
            'end_period' => now()->addMonths(3)->toDateString(),
            'office_id' => 1,
        ]);

        $today = now()->toDateString();

        $attd = Attendance::create([
            'intern_id' => $intern->id,
            'date' => $today,
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'total_min' => 480,
            'keterangan' => 'Hadir kerja',
        ]);

        $detail = DetailSchedule::create([
            'schedule_id' => $schedule->id,
            'attendance_id' => $attd->id,
            'shift_id' => 2,
            'office_id' => 1,
            'date' => $today,
            'attd_status_id' => 2, // Hadir
        ]);

        // Eksekusi reset menggunakan DetailSchedule ID
        $response = $this->actingAs($admin)->postJson("/admin/attendance/reset/{$detail->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['status' => true]);

        // Verifikasi DetailSchedule kembali ke status 1 (Dijadwalkan)
        $detail->refresh();
        $this->assertEquals(1, $detail->attd_status_id);

        // Verifikasi Attendance jam-nya di-null-kan
        $attd->refresh();
        $this->assertNull($attd->start_time);
        $this->assertNull($attd->end_time);
        $this->assertEquals(0, $attd->total_min);
        $this->assertNull($attd->keterangan);
    }

    public function test_reset_attendance_from_alpha_returns_to_dijadwalkan()
    {
        $admin = $this->getAdminUser();

        $user = User::create([
            'username' => 'intern_alpha_' . uniqid(),
            'email' => 'intern_alpha_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role_id' => 3,
            'is_active' => true,
        ]);

        $intern = Intern::create([
            'user_id' => $user->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'shift_id' => 2,
        ]);

        $schedule = Schedule::create([
            'intern_id' => $intern->id,
            'type' => 'daily',
            'start_period' => now()->toDateString(),
            'end_period' => now()->addMonths(3)->toDateString(),
            'office_id' => 1,
        ]);

        $today = now()->toDateString();

        $detail = DetailSchedule::create([
            'schedule_id' => $schedule->id,
            'shift_id' => 2,
            'office_id' => 1,
            'date' => $today,
            'attd_status_id' => 5, // Tidak Hadir (Alpha)
        ]);

        // Eksekusi reset
        $response = $this->actingAs($admin)->postJson("/admin/attendance/reset/{$detail->id}");

        $response->assertStatus(200);

        // Verifikasi DetailSchedule kembali ke status 1 (Dijadwalkan)
        $detail->refresh();
        $this->assertEquals(1, $detail->attd_status_id);
    }

    public function test_reset_attendance_using_attendance_id_resets_both()
    {
        $admin = $this->getAdminUser();

        $user = User::create([
            'username' => 'intern_izin_' . uniqid(),
            'email' => 'intern_izin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role_id' => 3,
            'is_active' => true,
        ]);

        $intern = Intern::create([
            'user_id' => $user->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'shift_id' => 2,
        ]);

        $schedule = Schedule::create([
            'intern_id' => $intern->id,
            'type' => 'daily',
            'start_period' => now()->toDateString(),
            'end_period' => now()->addMonths(3)->toDateString(),
            'office_id' => 1,
        ]);

        $today = now()->toDateString();

        $attd = Attendance::create([
            'intern_id' => $intern->id,
            'date' => $today,
            'permit_start' => '09:00:00',
            'permit_back' => '12:00:00',
            'total_permit_min' => 180,
            'keterangan' => 'Izin keperluan keluarga',
        ]);

        $detail = DetailSchedule::create([
            'schedule_id' => $schedule->id,
            'attendance_id' => $attd->id,
            'shift_id' => 2,
            'office_id' => 1,
            'date' => $today,
            'attd_status_id' => 3, // Izin
            'permit_reason_id' => 1,
        ]);

        // Eksekusi reset menggunakan Attendance ID (bukan DetailSchedule ID)
        $response = $this->actingAs($admin)->postJson("/admin/attendance/reset/{$attd->id}");

        $response->assertStatus(200);

        // Verifikasi DetailSchedule status kembali ke 1 (Dijadwalkan) dan permit_reason_id null
        $detail->refresh();
        $this->assertEquals(1, $detail->attd_status_id);
        $this->assertNull($detail->permit_reason_id);

        // Verifikasi Attendance ter-reset
        $attd->refresh();
        $this->assertNull($attd->permit_start);
        $this->assertNull($attd->permit_back);
        $this->assertEquals(0, $attd->total_permit_min);
    }
}
