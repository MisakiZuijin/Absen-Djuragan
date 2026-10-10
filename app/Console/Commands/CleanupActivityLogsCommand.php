<?php

namespace App\Console\Commands;

use App\Helper\ActivityLogger;
use App\Models\SystemActivityLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupActivityLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-logs:cleanup {--days=60 : Hapus log audit sistem yang lebih tua dari jumlah hari ini (default: 60 hari / 2 bulan)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan riwayat audit aktivitas sistem yang lebih tua dari retensi (default 60 hari)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) ($this->option('days') ?: 60);

        // Pengaman: Jangan izinkan penghapusan kurang dari 7 hari untuk keamanan audit trail
        if ($days < 7) {
            $this->error("❌ Demi keamanan audit sistem, retensi minimal adalah 7 hari.");
            return Command::FAILURE;
        }

        $cutOffDate = Carbon::now()->subDays($days);
        $this->info("🧹 Membersihkan log audit sistem yang dibuat sebelum: {$cutOffDate->toDateTimeString()} ({$days} hari yang lalu)...");

        try {
            $totalDeleted = SystemActivityLog::where('created_at', '<', $cutOffDate)->delete();

            $this->info("✅ Berhasil membersihkan {$totalDeleted} entri audit log sistem.");

            if ($totalDeleted > 0) {
                ActivityLogger::log(
                    'DELETE',
                    'Audit Log',
                    "Sistem membersihkan {$totalDeleted} entri riwayat audit log aktivitas yang lebih tua dari {$days} hari (sebelum {$cutOffDate->format('Y-m-d')}).",
                    [
                        'retention_days' => $days,
                        'cut_off_date' => $cutOffDate->toDateTimeString(),
                        'deleted_count' => $totalDeleted,
                    ]
                );
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('CleanupActivityLogsCommand error: ' . $e->getMessage());
            $this->error("❌ Gagal membersihkan log audit sistem: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
