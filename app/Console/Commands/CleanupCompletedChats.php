<?php

namespace App\Console\Commands;

use App\Models\ChangeTimeNote;
use App\Models\ChangeTimeRegistration;
use App\Models\ChangeTimeSession;
use App\Models\HandRaise;
use App\Models\HandRaiseMessage;
use Illuminate\Console\Command;

class CleanupCompletedChats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:completed-chats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan riwayat log chat ganti jam dan bantuan yang telah selesai agar database tetap bersih';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pembersihan log chat yang sudah selesai...');

        // 1. Bersihkan ChangeTimeNote untuk sesi yang sudah disetujui atau ditolak
        $deletedSessionNotes = ChangeTimeNote::whereHas('session', function ($q) {
            $q->whereIn('status', ['approved', 'rejected']);
        })->delete();

        // 2. Bersihkan ChangeTimeNote untuk registrasi yang sudah selesai, ditolak, atau dibatalkan
        $deletedRegNotes = ChangeTimeNote::whereHas('registration', function ($q) {
            $q->whereIn('status', ['completed', 'rejected', 'cancelled']);
        })->delete();

        // 3. Bersihkan ChangeTimeNote orphan (tanpa relasi yang aktif)
        $deletedOrphanNotes = ChangeTimeNote::whereDoesntHave('session')
            ->whereDoesntHave('registration')
            ->delete();

        // 4. Bersihkan HandRaiseMessage untuk sesi bantuan yang statusnya 'done'
        $deletedHandRaiseMessages = HandRaiseMessage::whereHas('handRaise', function ($q) {
            $q->where('status', 'done');
        })->delete();

        // 5. Bersihkan HandRaiseMessage orphan (tanpa parent hand_raises)
        $deletedOrphanHrMessages = HandRaiseMessage::whereDoesntHave('handRaise')->delete();

        $totalNotes = $deletedSessionNotes + $deletedRegNotes + $deletedOrphanNotes;
        $totalMessages = $deletedHandRaiseMessages + $deletedOrphanHrMessages;

        $this->info("Pembersihan selesai: {$totalNotes} chat ganti jam dan {$totalMessages} pesan bantuan telah dibersihkan.");

        return Command::SUCCESS;
    }
}
