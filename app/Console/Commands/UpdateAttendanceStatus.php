<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DetailSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateAttendanceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status of interns who missed their shift to Alpha';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\AttendanceService $attendanceService)
    {
        Log::info('Scheduler is running: Checking for missed attendances...');

        try {
            $attendanceService->markMissedSchedulesAsAlpha();
            $message = "Successfully checked and marked missed schedules as Alpha.";
            $this->info($message);
            Log::info($message);
            return 0;
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            Log::error('Error in UpdateAttendanceStatus command: ' . $e->getMessage());
            return 1;
        }
    }
}