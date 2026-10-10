<?php

namespace App\Console\Commands;

use App\Services\HolidaySyncService;
use Illuminate\Console\Command;

class SyncHolidaysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:sync {year? : Tahun yang ingin disinkronkan (default: tahun berjalan & tahun depan)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan daftar hari libur nasional dan cuti bersama Indonesia dari API publik ke database';

    /**
     * Execute the console command.
     */
    public function handle(HolidaySyncService $syncService)
    {
        $yearInput = $this->argument('year');
        $targetYear = $yearInput ? (int) $yearInput : null;

        $this->info("Memulai sinkronisasi hari libur nasional Indonesia" . ($targetYear ? " untuk tahun {$targetYear}..." : "..."));

        $result = $syncService->syncHolidays($targetYear);

        if ($result['success']) {
            $this->info("✅ {$result['message']}");
            if (!empty($result['holidays'])) {
                $rows = array_map(function ($item) {
                    return [$item['date'], $item['name']];
                }, array_slice($result['holidays'], 0, 15));

                $this->table(['Tanggal', 'Nama Hari Libur'], $rows);
                if (count($result['holidays']) > 15) {
                    $remaining = count($result['holidays']) - 15;
                    $this->comment("...dan {$remaining} hari libur lainnya tersimpan di database.");
                }
            }
            return Command::SUCCESS;
        }

        $this->error("❌ {$result['message']}");
        return Command::FAILURE;
    }
}
