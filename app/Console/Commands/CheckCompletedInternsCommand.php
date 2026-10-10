<?php

namespace App\Console\Commands;

use App\Helper\ActivityLogger;
use App\Models\Intern;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckCompletedInternsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interns:check-completed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa dan non-aktifkan akun pemagang yang masa magangnya (end_date) telah berakhir';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $today = Carbon::today($timezone)->toDateString();

        $this->info("🔍 Memeriksa pemagang yang masa magangnya telah berakhir sebelum: {$today}");

        // Cari pemagang dengan end_date yang sudah lewat dan akun user-nya masih aktif
        $expiredInterns = Intern::whereNotNull('end_date')
            ->whereDate('end_date', '<', $today)
            ->whereHas('user', function ($q) {
                $q->where('is_active', true)->where('role_id', 3);
            })
            ->with(['user.profile', 'school', 'division'])
            ->get();

        if ($expiredInterns->isEmpty()) {
            $this->info("✅ Tidak ada pemagang aktif yang masa magangnya telah lewat.");
            return Command::SUCCESS;
        }

        $this->info("📋 Ditemukan {$expiredInterns->count()} pemagang yang telah menyelesaikan masa magang.");

        $deactivatedCount = 0;

        foreach ($expiredInterns as $intern) {
            DB::beginTransaction();
            try {
                $user = $intern->user;
                if ($user) {
                    $user->update(['is_active' => false]);
                }

                $fullName = $user?->profile?->full_name ?? $user?->username ?? "Intern #{$intern->id}";
                $endDate = $intern->end_date ? Carbon::parse($intern->end_date)->format('d-m-Y') : 'N/A';

                ActivityLogger::log(
                    'UPDATE',
                    'Manajemen Pemagang',
                    "Sistem menonaktifkan akun pemagang {$fullName} (NIM: {$intern->nim}) karena masa magang telah berakhir pada {$endDate}.",
                    [
                        'intern_id' => $intern->id,
                        'user_id' => $intern->user_id,
                        'end_date' => $intern->end_date,
                    ]
                );

                DB::commit();
                $deactivatedCount++;
                $this->line("   🎓 Akun dinonaktifkan: {$fullName} (Selesai: {$endDate})");
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("CheckCompletedInternsCommand error for intern ID {$intern->id}: " . $e->getMessage());
                $this->error("   ❌ Gagal memproses pemagang ID {$intern->id}: " . $e->getMessage());
            }
        }

        $this->info("📊 Total akun pemagang yang berhasil dinonaktifkan: {$deactivatedCount}");

        return Command::SUCCESS;
    }
}
