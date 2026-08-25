<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Models\DetailSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use App\Models\Intern;

class NotificationService
{
    protected WhatsappService $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function notifyOutsidersForAlpha(DetailSchedule $schedule): ActionResult
    {
        try {
            if ($schedule->is_notification_sent) {
                return new ActionResult(false, 'Notifikasi untuk siswa ini sudah pernah dikirim hari ini.');
            }

            $intern = $schedule->schedule->intern;
            $studentName = $intern->user->profile->full_name;

            // --- PERUBAHAN UTAMA DI SINI ---

            // Buat koleksi untuk menampung semua nomor telepon yang valid
            $validPhoneNumbers = new Collection();

            // 1. PRIORITAS: Cek dari tabel whatsapp_numbers
            $whatsappNumberRecord = $intern->whatsappNumber;
            if ($whatsappNumberRecord && $whatsappNumberRecord->is_notification_active && $whatsappNumberRecord->phone_number) {
                $validPhoneNumbers->push($whatsappNumberRecord->phone_number);
            }

            // 2. CADANGAN: Cek dari relasi outsiders
            $outsiders = $intern->outsiders()->with('user.profile')->get();
            foreach ($outsiders as $outsider) {
                if ($outsider->user && $outsider->user->profile && $outsider->user->profile->phone_number) {
                    $phoneNumber = $outsider->user->profile->phone_number;
                    // Tambahkan hanya jika nomornya belum ada di koleksi untuk menghindari duplikasi
                    if (!$validPhoneNumbers->contains($phoneNumber)) {
                        $validPhoneNumbers->push($phoneNumber);
                    }
                }
            }

            // Cek apakah setelah semua pencarian, kita punya nomor valid atau tidak
            if ($validPhoneNumbers->isEmpty()) {
                Log::warning("Tidak ada nomor telepon valid yang ditemukan untuk Intern ID: {$intern->id}, baik dari WhatsappNumber maupun Outsiders.");
                return new ActionResult(false, 'Gagal! Data orang tua/wali atau nomor telepon tidak ditemukan atau tidak aktif.');
            }

            // Kirim pesan ke semua nomor yang valid
            $date = Carbon::parse($schedule->date)->isoFormat('dddd, D MMMM YYYY');
            $message = "Yth. Bapak/Ibu Wali dari {$studentName},\n\nKami informasikan bahwa ananda belum melakukan presensi hingga batas waktu yang ditentukan pada hari ini, {$date}, dan status kehadirannya tercatat sebagai Alpha (Tidak Hadir Tanpa Keterangan).\n\nMohon konfirmasinya. Terima kasih.";

            foreach ($validPhoneNumbers as $phoneNumber) {
                $this->whatsappService->sendMessage($phoneNumber, $message);
            }

            // Tandai notifikasi sudah terkirim
            $schedule->is_notification_sent = true;
            $schedule->save();

            $sentCount = $validPhoneNumbers->count();
            return new ActionResult(true, "Notifikasi Alpha untuk {$studentName} berhasil dikirim ke {$sentCount} penerima.");

        } catch (\Exception $e) {
            Log::error("Gagal mengirim notifikasi Alpha untuk DetailSchedule ID {$schedule->id}: " . $e->getMessage());
            return new ActionResult(false, 'Terjadi kesalahan internal saat mengirim notifikasi.');
        }
    }

    public function notifyAllOutsidersForAlphaToday(string $date): ActionResult
    {
        $schedules = DetailSchedule::where('date', $date)
            ->where('attd_status_id', 5) // Status Alpha
            ->where('is_notification_sent', false)
            ->get();

        if ($schedules->isEmpty()) {
            return new ActionResult(true, 'Tidak ada siswa berstatus Alpha yang perlu dinotifikasi hari ini.');
        }

        $successCount = 0;
        $failCount = 0;
        $lastErrorMessage = '';

        foreach ($schedules as $schedule) {
            $result = $this->notifyOutsidersForAlpha($schedule);
            if ($result->isSuccess()) {
                $successCount++;
            } else {
                $failCount++;
                $lastErrorMessage = $result->getMessage(); // Simpan pesan error terakhir
            }
        }

        $message = "Pengiriman notifikasi massal selesai. Siswa berhasil dinotifikasi: {$successCount}, Siswa gagal dinotifikasi: {$failCount}.";
        if ($failCount > 0) {
            $message .= " Contoh error: " . $lastErrorMessage;
        }

        return new ActionResult(true, $message);
    }

    public function notifyOutsidersForPermit(DetailSchedule $schedule): ActionResult
    {
        try {
            if ($schedule->is_notification_sent) {
                return new ActionResult(false, 'Notifikasi untuk siswa ini sudah pernah dikirim hari ini.');
            }

            // Pastikan ada alasan izin
            if (!$schedule->permitReason) {
                return new ActionResult(false, 'Tidak ada detail izin yang ditemukan untuk jadwal ini.');
            }

            $intern = $schedule->schedule->intern;
            $studentName = $intern->user->profile->full_name;
            $permitCategory = $schedule->permitReason->category->name ?? 'Lainnya';
            $reason = $schedule->permitReason->description;

            // Panggil WhatsappService
            $this->whatsappService->sendPermitNotification($intern, $studentName, $permitCategory, $reason);

            // Tandai bahwa notifikasi telah dikirim
            $schedule->is_notification_sent = true;
            $schedule->save();

            return new ActionResult(true, "Notifikasi Izin untuk {$studentName} berhasil dikirim.");

        } catch (\Exception $e) {
            Log::error("Gagal mengirim notifikasi Izin untuk DetailSchedule ID {$schedule->id}: " . $e->getMessage());
            return new ActionResult(false, 'Terjadi kesalahan internal saat mengirim notifikasi izin.');
        }
    }

    public function notifyAllOutsidersForPermitToday(string $date): ActionResult
    {
        $schedules = DetailSchedule::where('date', $date)
            ->where('attd_status_id', 3) // Status Izin (asumsi ID 3)
            ->where('is_notification_sent', false)
            ->with('permitReason.category', 'schedule.intern.user.profile') // Eager loading
            ->get();

        if ($schedules->isEmpty()) {
            return new ActionResult(true, 'Tidak ada siswa berstatus Izin yang perlu dinotifikasi hari ini.');
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($schedules as $schedule) {
            $result = $this->notifyOutsidersForPermit($schedule);
            if ($result->isSuccess()) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        $message = "Pengiriman notifikasi Izin massal selesai. Berhasil: {$successCount}, Gagal: {$failCount}.";
        return new ActionResult(true, $message);
    }
}
