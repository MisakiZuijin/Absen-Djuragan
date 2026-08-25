<?php

namespace App\Utils;

class Converter
{
    /**
     * Mengubah total detik menjadi array berisi jam, menit, dan detik.
     * @param int $totalSeconds Total detik yang akan diubah.
     * @return array Mengembalikan array dengan kunci 'hours', 'minutes', 'seconds'.
     */
    public static function convertToHourMinuteSecond($totalSeconds)
    {
        // Jika input bukan angka, kembalikan nilai nol untuk mencegah error.
        if (!is_numeric($totalSeconds)) {
            return ['hours' => 0, 'minutes' => 0, 'seconds' => 0];
        }

        // Pastikan kita bekerja dengan nilai absolut untuk perhitungan
        $seconds = abs((int)$totalSeconds);

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return [
            'hours' => (int) $hours,
            'minutes' => (int) $minutes,
            'seconds' => (int) $seconds,
        ];
    }

    // Anda bisa menambahkan fungsi konversi lain di sini di masa depan
    // contoh: public static function formatRupiah($number) { ... }
}