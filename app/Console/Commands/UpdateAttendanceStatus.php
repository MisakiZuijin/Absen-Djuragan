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
    public function handle()
    {
        Log::info('Scheduler is running: Checking for missed attendances...');
        $now = Carbon::now();

        try {
            $schedulesToUpdate = DetailSchedule::query()
                ->where('attd_status_id', 1) // HANYA targetkan yang masih 'Terjadwal'
                ->whereDate('date', '<=', $now->toDateString()) // Targetkan hari ini dan hari-hari sebelumnya yang mungkin terlewat
                ->whereDoesntHave('attendance', function ($query) {
                    $query->whereNotNull('start_time');
                })
                ->whereHas('shift', function ($query) use ($now) {
                    // Lakukan perbandingan waktu langsung di dalam query SQL
                    // Kondisi: waktu sekarang > (jam masuk shift + 60 menit)
                    $query->where(DB::raw("TIMESTAMP(detail_schedules.date, shifts.start_time)"), '<', $now->copy()->subMinutes(60));
                })
                ->get();

            if ($schedulesToUpdate->isEmpty()) {
                $this->info('No schedules found to update to Alpha.');
                Log::info('No schedules to update to Alpha.');
                return 0; // Command successful, no action needed
            }

            $updatedCount = 0;
            foreach ($schedulesToUpdate as $schedule) {
                // Ubah statusnya menjadi Alpha (ID 5)
                $schedule->attd_status_id = 5;
                $schedule->save();
                $updatedCount++;
            }

            $message = "Successfully updated {$updatedCount} schedules to Alpha.";
            $this->info($message);
            Log::info($message);

        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            Log::error('Error in UpdateAttendanceStatus command: ' . $e->getMessage());
            return 1; // Command failed
        }
        
        return 0; // Command successful
    }
}