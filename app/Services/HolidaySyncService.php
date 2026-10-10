<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\DetailSchedule;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidaySyncService
{
    /**
     * URL API Resmi Hari Libur & Cuti Bersama Indonesia.
     * Sumber 1: APIHariLibur_V2 (Mencakup Libur Nasional & Cuti Bersama resmi)
     * Sumber 2: Nager.Date Global Public Holiday API (Fallback per tahun)
     */
    protected const PRIMARY_API_URL = 'https://raw.githubusercontent.com/guangrei/APIHariLibur_V2/main/holidays.json';
    protected const FALLBACK_API_URL = 'https://date.nager.at/api/v3/PublicHolidays/{YEAR}/ID';

    /**
     * Sinkronkan hari libur nasional dari API publik ke tabel database `holidays`.
     *
     * @param int|null $targetYear Tahun spesifik yang ingin disinkronkan, atau null untuk tahun ini dan tahun depan
     * @param bool $cleanEmptySchedules Bersihkan jadwal kosong pemagang pada tanggal libur tersebut
     * @return array
     */
    public function syncHolidays(?int $targetYear = null, bool $cleanEmptySchedules = true): array
    {
        $currentYear = (int) now()->year;
        $yearsToSync = $targetYear ? [$targetYear] : [$currentYear - 1, $currentYear, $currentYear + 1];

        $holidaysMap = []; // [YYYY-MM-DD => 'Nama Libur']

        // 1. Ambil dari Sumber Utama (APIHariLibur_V2)
        try {
            $response = Http::timeout(8)->get(self::PRIMARY_API_URL);
            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data)) {
                    foreach ($data as $dateStr => $val) {
                        if ($dateStr === 'info' || empty($val['summary'])) {
                            continue;
                        }

                        try {
                            $cDate = Carbon::parse($dateStr);
                            if (in_array((int)$cDate->year, $yearsToSync, true)) {
                                $holidaysMap[$cDate->toDateString()] = trim($val['summary']);
                            }
                        } catch (\Throwable $e) {
                            // Abaikan format tanggal tidak valid
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
            Log::warning("HolidaySyncService: Gagal mengambil dari sumber utama: " . $th->getMessage());
        }

        // 2. Ambil dari Sumber Fallback (Nager.Date) untuk melengkapi tahun atau data yang belum ada
        foreach ($yearsToSync as $year) {
            try {
                $fallbackUrl = str_replace('{YEAR}', (string)$year, self::FALLBACK_API_URL);
                $response = Http::timeout(6)->get($fallbackUrl);
                if ($response->successful()) {
                    $items = $response->json();
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            $d = $item['date'] ?? null;
                            $name = $item['localName'] ?? $item['name'] ?? null;
                            if ($d && $name && !isset($holidaysMap[$d])) {
                                $holidaysMap[$d] = trim($name);
                            }
                        }
                    }
                }
            } catch (\Throwable $th) {
                Log::warning("HolidaySyncService: Gagal mengambil fallback tahun {$year}: " . $th->getMessage());
            }
        }

        if (empty($holidaysMap)) {
            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke penyedia API hari libur. Periksa koneksi internet server.',
                'synced_count' => 0,
                'holidays' => [],
            ];
        }

        // 3. Simpan / Perbarui ke Database (Upsert berdasarkan Tanggal)
        $syncedCount = 0;
        $syncedList = [];

        foreach ($holidaysMap as $date => $name) {
            Holiday::updateOrCreate(
                ['date' => $date],
                ['name' => $name]
            );
            $syncedCount++;
            $syncedList[] = [
                'date' => $date,
                'name' => $name,
            ];

            // 3.1. Hapus jadwal belum terisi pada tanggal libur baru agar otomatis off/libur
            if ($cleanEmptySchedules) {
                $emptyDetailSchedules = DetailSchedule::whereDate('date', $date)
                    ->where(function ($q) {
                        $q->whereDoesntHave('attendance')
                          ->orWhereHas('attendance', fn($aq) => $aq->whereNull('start_time'));
                    })
                    ->where('attd_status_id', 1)
                    ->get();

                foreach ($emptyDetailSchedules as $ds) {
                    $attId = $ds->attendance_id;
                    $ds->delete();
                    if ($attId) {
                        Attendance::where('id', $attId)->whereNull('start_time')->delete();
                    }
                }
            }
        }

        // 4. Bersihkan cache aplikasi agar data langsung aktif di seluruh fitur
        try {
            Artisan::call('optimize:clear');
        } catch (\Throwable $th) {
            // Ignore if in restricted environment
        }

        return [
            'success' => true,
            'message' => "Berhasil menyinkronkan {$syncedCount} hari libur nasional dari API!",
            'synced_count' => $syncedCount,
            'holidays' => $syncedList,
        ];
    }
}
