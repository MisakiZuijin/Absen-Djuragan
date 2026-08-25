<?php

namespace App\Console\Commands;

use App\Models\Intern;
use App\Services\WhatsappService;
use Illuminate\Console\Command;

class TestWhatsappNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test {intern_id} {--status=MASUK} {--time=08:00}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test WhatsApp notification system';

    /**
     * Execute the console command.
     */
    public function handle(WhatsappService $whatsappService)
    {
        $internId = $this->argument('intern_id');
        $status = $this->option('status');
        $time = $this->option('time');

        $intern = Intern::with(['user.profile', 'whatsappNumber'])->find($internId);

        if (!$intern) {
            $this->error("Intern dengan ID {$internId} tidak ditemukan!");
            return 1;
        }

        $this->info("Testing WhatsApp notification untuk:");
        $this->info("- Intern: {$intern->user->profile->full_name}");
        $this->info("- Status: {$status}");
        $this->info("- Waktu: {$time}");

        // Test notifikasi ke semua target
        $whatsappService->sendAttendanceNotificationToAllTargets(
            $intern,
            $intern->user->profile->full_name,
            $status,
            $time
        );

        $this->info("Notifikasi test telah dikirim!");

        return 0;
    }
}
