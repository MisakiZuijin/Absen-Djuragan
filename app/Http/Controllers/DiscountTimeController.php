<?php

namespace App\Http\Controllers;

use App\Models\DiscountTime;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Services\AttendanceService;

class DiscountTimeController extends Controller
{
    /**
     * @var AttendanceService
     */
    protected $attendanceService;

    /**
     * Inject AttendanceService agar kita bisa memanggil fungsinya.
     */
    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Method ini sekarang jauh lebih sederhana dan benar.
     */
    public function store(Request $request)
    {
        try {
            // 1. Validasi input dari form
            $validated = $request->validate([
                'schedule_id' => 'required|numeric|exists:schedules,id',
                'discount_time' => 'nullable|numeric|min:0',
            ]);

            $discountValue = $validated['discount_time'] ?? 0;

            // ================== PERBAIKAN UTAMA ADA DI SINI ==================
            // Saat menyimpan, kita sekarang juga menyertakan kolom 'date'
            DiscountTime::updateOrCreate(
                ['schedule_id' => $validated['schedule_id']],
                [
                    'duration' => $discountValue,
                    'date'     => now() // Menambahkan tanggal hari ini
                ]
            );
            // ================== AKHIR DARI PERBAIKAN ==================

            // 3. Dapatkan intern_id dari schedule_id yang dikirim
            $schedule = Schedule::find($validated['schedule_id']);
            if (!$schedule) {
                return response()->json(['success' => false, 'message' => 'Schedule tidak ditemukan'], 404);
            }
            $intern_id = $schedule->intern_id;

            // 4. Panggil fungsi internTarget() dari AttendanceService.
            $internTargetResult = $this->attendanceService->internTarget($intern_id);

            // Jika service gagal, kirim pesan error
            if (!$internTargetResult->isSuccess()) {
                 return response()->json(['success' => false, 'message' => $internTargetResult->getMessage()], 500);
            }

            // 5. Kirim kembali data yang 100% konsisten dari service
            return response()->json([
                'success' => true,
                'message' => 'Diskon jam berhasil disimpan',
                'intern_target' => $internTargetResult->getData()
            ]);

        } catch (\Throwable $th) {
            // Tangani error jika terjadi
            return response()->json(['success' => false, 'message' => 'Error: ' . $th->getMessage()], 500);
        }
    }
}