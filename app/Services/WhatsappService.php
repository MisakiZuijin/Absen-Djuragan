<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Intern;
use Illuminate\Support\Collection;

class WhatsappService
{
    protected $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.url');
    }

    /**
     * Method utama dan generik untuk mengirim pesan WhatsApp.
     * Inilah yang akan dipanggil oleh service lain.
     */
    public function sendMessage(string $number, string $message): void
    {
        if (empty($this->apiUrl)) {
            Log::warning('WhatsApp Service: API URL is not configured in .env file.');
            return;
        }

        // --- PENAMBAHAN LOGIKA PEMBERSIHAN NOMOR TELEPON ---
        // 1. Hapus semua karakter non-numerik (+, -, spasi, dll)
        $sanitizedNumber = preg_replace('/[^0-9]/', '', $number);

        // 2. Jika nomor diawali dengan 0, ganti dengan 62
        if (substr($sanitizedNumber, 0, 1) === '0') {
            $sanitizedNumber = '62' . substr($sanitizedNumber, 1);
        }
        // Pastikan nomor yang sudah benar (diawali 62) tidak diubah
        elseif (substr($sanitizedNumber, 0, 2) !== '62') {
             // Jika format lain (misal langsung 8xx), tambahkan 62 di depan jika perlu
             // Asumsi nomor Indonesia
            if (strlen($sanitizedNumber) > 9) { // Cek panjang minimal nomor seluler
                 $sanitizedNumber = '62' . $sanitizedNumber;
            }
        }
        // --- AKHIR DARI PENAMBAHAN LOGIKA ---

        try {
            // Gunakan nomor yang sudah dibersihkan
            $response = Http::timeout(15)->post($this->apiUrl . '/send-message', [
                'number' => $sanitizedNumber,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp message successfully sent to {$sanitizedNumber}. Message: \"{$message}\"");
            } else {
                Log::error("WhatsApp API returned an error for number {$sanitizedNumber}. Status: " . $response->status() . " Body: " . $response->body());
            }
        } catch (\Exception $e) {
            // Ini biasanya error koneksi (timeout, connection refused, dll)
            Log::error("Failed to send WhatsApp message to {$sanitizedNumber}. Connection error: " . $e->getMessage());
        }
    }

    public function sendAttendanceNotification(string $number, string $internName, string $status, string $time): void
    {
        $message = "Notifikasi Presensi: Anak Anda, {$internName}, telah melakukan presensi *{$status}* pada pukul {$time}. Terima kasih.";
        $this->sendMessage($number, $message);
    }

    /**
     * Send attendance notification to all targets (parents/guardians)
     */
    public function sendAttendanceNotificationToAllTargets(Intern $intern, string $internName, string $status, string $time): void
    {
        $message = "Notifikasi Presensi: Anak Anda, {$internName}, telah melakukan presensi *{$status}* pada pukul {$time}. Terima kasih.";
        $this->sendNotificationToAllTargets($intern, $message);
    }

    public function sendNotificationToAllTargets(Intern $intern, string $message): void
    {
        // 1. Kumpulkan semua nomor telepon target
        $validPhoneNumbers = new Collection();

        $whatsappNumberRecord = $intern->whatsappNumber;
        if ($whatsappNumberRecord && $whatsappNumberRecord->is_notification_active && $whatsappNumberRecord->phone_number) {
            $validPhoneNumbers->push($whatsappNumberRecord->phone_number);
        }

        $outsiders = $intern->outsiders()->with('user.profile')->get();
        foreach ($outsiders as $outsider) {
            if ($outsider->user && $outsider->user->profile && $outsider->user->profile->phone_number) {
                $phoneNumber = $outsider->user->profile->phone_number;
                if (!$validPhoneNumbers->contains($phoneNumber)) {
                    $validPhoneNumbers->push($phoneNumber);
                }
            }
        }
        
        // 2. Kirim pesan ke setiap nomor yang valid
        if ($validPhoneNumbers->isEmpty()) {
            Log::warning("Tidak ada nomor wali/guru yang bisa dinotifikasi untuk Intern ID: {$intern->id}");
            return;
        }
        
        foreach ($validPhoneNumbers as $phoneNumber) {
            $this->sendMessage($phoneNumber, $message);
        }
    }

    public function sendPermitNotification(Intern $intern, string $studentName, string $permitCategory, string $reason): void
    {
        $message = "*Notifikasi Pengajuan Izin*\n" .
                   "Siswa a/n *{$studentName}* telah mengajukan izin dengan rincian:\n" .
                   "Kategori: *{$permitCategory}*\n" .
                   "Alasan: {$reason}";
        
        $this->sendNotificationToAllTargets($intern, $message);
    }
}