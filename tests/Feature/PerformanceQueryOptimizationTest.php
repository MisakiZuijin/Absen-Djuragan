<?php

namespace Tests\Feature;

use App\Models\DetailSchedule;
use App\Models\Intern;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\DebtCalculationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceQueryOptimizationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_debt_calculation_batch_query_count_is_minimal()
    {
        $user = User::create([
            'username' => 'test_debt_' . uniqid(),
            'email' => 'debt_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role_id' => 3,
            'is_active' => true,
        ]);

        $intern = Intern::create([
            'user_id' => $user->id,
            'nim' => 'NIM' . rand(10000, 99999),
        ]);

        $shift = Shift::where('id', '>', 1)->first() ?? Shift::first();
        $office = \App\Models\Office::first();
        $officeId = $office ? $office->id : 1;

        $schedule = Schedule::create([
            'intern_id' => $intern->id,
            'office_id' => $officeId,
            'start_period' => now()->subDays(10)->toDateString(),
            'end_period' => now()->addDays(10)->toDateString(),
        ]);

        // Create 10 detail schedules
        for ($i = 1; $i <= 10; $i++) {
            DetailSchedule::create([
                'schedule_id' => $schedule->id,
                'office_id' => $officeId,
                'shift_id' => $shift->id,
                'attd_status_id' => 5, // Alpha
                'date' => now()->subDays($i)->toDateString(),
            ]);
        }

        $service = app(DebtCalculationService::class);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $debts = $service->getInternDebts($intern->id, true);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // 10 schedules used to execute 30-40 queries. With batch preloading, it executes <= 6 queries total!
        $this->assertGreaterThan(0, $debts->count());
        $this->assertLessThanOrEqual(6, count($queries), "Query count should be batch-optimized (<= 6 queries, got " . count($queries) . ")");
    }

    public function test_mark_missed_schedules_as_alpha_throttled()
    {
        $attendanceService = app(AttendanceService::class);

        // First call executes or uses lock
        $count1 = $attendanceService->markMissedSchedulesAsAlpha();
        $this->assertIsInt($count1);

        // Immediate second call should be throttled and return 0
        $count2 = $attendanceService->markMissedSchedulesAsAlpha();
        $this->assertEquals(0, $count2);
    }
}
