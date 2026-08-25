<?php

namespace App\Console;

use App\Console\Commands\AttendanceScheduler;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

    protected $command = [
        AttendanceScheduler::class
    ];
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void {
        $schedule->command('schedule:attd')
            ->everyFiveMinutes()->sentryMonitor(
                // Specify the slug of the job monitor in case of duplicate commands or if the monitor was created in the UI
                monitorSlug: null,
                // Check-in margin in minutes
                checkInMargin: 5,
                // Max runtime in minutes
                maxRuntime: 15,
                // In case you want to configure the job monitor exclusively in the UI, you can turn off sending the monitor config with the check-in.
                // Passing a monitor-slug is required in this case.
                updateMonitorConfig: false,
            );
        $schedule->command('attendance:update-status')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
