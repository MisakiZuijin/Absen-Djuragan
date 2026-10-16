<?php

namespace Tests\Feature;

use App\Models\Shift;
use App\Models\ShiftRollingGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RollingGroupSettingTest extends TestCase
{
    use DatabaseTransactions;

    protected function getAdminUser()
    {
        $admin = User::where('role_id', 1)->first();
        if (!$admin) {
            $admin = User::create([
                'username' => 'admin_test_' . uniqid(),
                'email' => 'admintest_' . uniqid() . '@example.com',
                'password' => bcrypt('password'),
                'role_id' => 1,
                'is_active' => true,
            ]);
        }
        return $admin;
    }

    public function test_cannot_create_rolling_group_with_non_rolling_shift()
    {
        $admin = $this->getAdminUser();

        // Shift 3 (Middle) is_rolling = false
        $middleShift = Shift::where('name', 'like', '%Middle%')->first();
        if ($middleShift) {
            $middleShift->update(['is_rolling' => false]);
        }

        $pagiShift = Shift::where('name', 'like', '%Pagi%')->first();

        $response = $this->actingAs($admin)->postJson('/admin/setting/shift/rolling-groups', [
            'name' => 'Middle - Pagi Group',
            'shift_id_1' => $middleShift ? $middleShift->id : 3,
            'shift_id_2' => $pagiShift ? $pagiShift->id : 2,
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['success' => false]);
    }

    public function test_can_create_and_delete_rolling_group()
    {
        $admin = $this->getAdminUser();

        $group = ShiftRollingGroup::create([
            'name' => 'Temp Group Pagi Siang',
            'shift_id_1' => 2,
            'shift_id_2' => 4,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('shift_rolling_groups', ['id' => $group->id]);

        // Test delete route
        $response = $this->actingAs($admin)->deleteJson('/admin/setting/shift/rolling-groups/' . $group->id);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('shift_rolling_groups', ['id' => $group->id]);
    }

    public function test_schedule_service_forces_daily_when_shift_is_not_rolling()
    {
        $admin = $this->getAdminUser();

        // Buat intern khusus test ini
        $user = User::create([
            'username' => 'intern_daily_' . uniqid(),
            'email' => 'intern_daily_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role_id' => 3,
            'is_active' => true,
        ]);
        $intern = \App\Models\Intern::create([
            'user_id' => $user->id,
            'start_date' => now()->startOfWeek()->toDateString(),
            'end_date' => now()->startOfWeek()->addWeeks(4)->toDateString(),
            'shift_id' => 3,
        ]);

        // Shift 3 (Middle) is_rolling = false
        $middleShift = Shift::find(3);
        $this->assertNotNull($middleShift);
        $middleShift->update(['is_rolling' => false]);

        $scheduleService = app(\App\Services\ScheduleService::class);

        // Request type 2 (weekly / rolling), tapi shift Middle is_rolling = false
        $startDate = now()->startOfWeek();
        $endDate = (clone $startDate)->addWeeks(3);

        $req = new \App\Http\Requests\InternScheduleRequest();
        $req->replace([
            'intern_id' => $intern->id,
            'type' => 2, // Weekly
            'shift_id' => 3, // Middle
            'office_id' => 1,
            'start_period' => $startDate->format('d-m-Y'),
            'end_period' => $endDate->format('d-m-Y'),
        ]);

        $scheduleService->createSchedule($req);

        $masterSchedule = \App\Models\Schedule::where('intern_id', $intern->id)->first();
        $this->assertNotNull($masterSchedule);
        // Karena Middle is_rolling = 0, jadwal dipaksa menjadi daily
        $this->assertEquals('daily', $masterSchedule->type);

        // Detail jadwal semuanya harus Shift Middle (ID 3), tidak pernah terotasi
        $details = \App\Models\DetailSchedule::where('schedule_id', $masterSchedule->id)->get();
        $this->assertNotEmpty($details);
        foreach ($details as $detail) {
            $this->assertEquals(3, $detail->shift_id);
        }
    }

    public function test_schedule_service_rotates_when_shift_is_rolling_and_grouped()
    {
        $admin = $this->getAdminUser();

        // Pastikan Shift Pagi (2) dan Siang (4) is_rolling = true
        Shift::where('id', 2)->update(['is_rolling' => true, 'is_active' => true]);
        Shift::where('id', 4)->update(['is_rolling' => true, 'is_active' => true]);

        // Pastikan grup rolling Pagi - Siang aktif
        ShiftRollingGroup::firstOrCreate(
            ['shift_id_1' => 2, 'shift_id_2' => 4],
            ['name' => 'Pagi <-> Siang', 'is_active' => true]
        );

        $user = User::create([
            'username' => 'intern_roll_' . uniqid(),
            'email' => 'intern_roll_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role_id' => 3,
            'is_active' => true,
        ]);
        $intern = \App\Models\Intern::create([
            'user_id' => $user->id,
            'start_date' => now()->startOfWeek()->toDateString(),
            'end_date' => now()->startOfWeek()->addWeeks(4)->toDateString(),
            'shift_id' => 2,
        ]);

        $scheduleService = app(\App\Services\ScheduleService::class);

        $startDate = now()->startOfWeek();
        $endDate = (clone $startDate)->addWeeks(3);

        $req = new \App\Http\Requests\InternScheduleRequest();
        $req->replace([
            'intern_id' => $intern->id,
            'type' => 2, // Weekly
            'shift_id' => 2, // Pagi
            'office_id' => 1,
            'start_period' => $startDate->format('d-m-Y'),
            'end_period' => $endDate->format('d-m-Y'),
        ]);

        $scheduleService->createSchedule($req);

        $masterSchedule = \App\Models\Schedule::where('intern_id', $intern->id)->first();
        $this->assertNotNull($masterSchedule);
        // Karena Pagi dan Siang aktif rolling, jadwal tetap weekly
        $this->assertEquals('weekly', $masterSchedule->type);

        $details = \App\Models\DetailSchedule::where('schedule_id', $masterSchedule->id)->get();
        $this->assertNotEmpty($details);

        $distinctShifts = $details->pluck('shift_id')->unique()->values()->toArray();
        sort($distinctShifts);
        // Harus memuat shift 2 dan shift 4
        $this->assertEquals([2, 4], $distinctShifts);
    }
}
