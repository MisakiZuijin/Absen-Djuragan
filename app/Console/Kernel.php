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
        $schedule->command('attendance:update-status')->everyMinute()->withoutOverlapping();
        $schedule->command('cleanup:completed-chats')->dailyAt('02:00');

        // Otomatisasi Eksekusi Pulang Otomatis & Penutupan Sesi Ganti Jam (Pukul 23:59:00)
        $schedule->command('attendance:auto-end')->dailyAt('23:59')->withoutOverlapping();

        // Otomatisasi Pemindaian & Sinkronisasi Keterlambatan Bersama Jam Pulang (Pukul 23:59:00)
        $schedule->command('attendance:check-late --auto-create')->dailyAt('23:59')->withoutOverlapping();

        // Otomatisasi Deaktivasi Pemagang yang Telah Selesai Masa Magang (Pukul 00:01:00)
        $schedule->command('interns:check-completed')->dailyAt('00:01')->withoutOverlapping();

        // Otomatisasi Sinkronisasi Hari Libur Nasional dari API Publik (Tanggal 1 Setiap Bulan, Pukul 01:00)
        $schedule->command('holidays:sync')->monthlyOn(1, '01:00')->withoutOverlapping();

        // Otomatisasi Pembersihan Audit Log Aktivitas yang Melewati 2 Bulan / 60 Hari (Setiap Minggu, Pukul 02:30)
        $schedule->command('activity-logs:cleanup --days=60')->weeklyOn(0, '02:30')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
