<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Services\ShiftService;
use App\Models\Shift; // <-- PERUBAHAN 1: Tambahkan ini untuk mengakses model Shift

class SettingShiftController extends Controller
{
    protected UserService $userService;
    protected ShiftService $shiftService;

    public function __construct(UserService $userService, ShiftService $shiftService)
    {
        $this->userService = $userService;
        $this->shiftService = $shiftService;
    }

    public function adminSettingShiftView(): View
    {
        $userData = $this->userService->getUserLoggedData();
        $shift = $this->shiftService->getAllWhereId();

        $data = [
            "user" => $userData,
            "shift" => $shift
        ];

        return view('admin.pengaturan-shift')->with($data);
    }

    public function storeShift(StoreShiftRequest $storeShiftRequest)
    {
        $this->shiftService->createShift($storeShiftRequest);

        $shiftName = $storeShiftRequest->input('addNamaShift') ?? $storeShiftRequest->input('name');
        \App\Helper\ActivityLogger::log('CREATE', 'Master Data', "Admin menambahkan shift kerja baru: {$shiftName}");

        return redirect()->route('admin.pengaturan.shift')->with('success', 'Shift berhasil ditambahkan!');
    }

    public function updateShift(UpdateShiftRequest $updateShiftRequest, int $id)
    {
        $this->shiftService->updateShift($updateShiftRequest, $id);

        $shiftName = $updateShiftRequest->input('nama_Shift') ?? $updateShiftRequest->input('name');
        \App\Helper\ActivityLogger::log('UPDATE', 'Master Data', "Admin memperbarui data shift kerja: {$shiftName}");

        return redirect()->route('admin.pengaturan.shift')->with('success', 'Shift berhasil diperbarui!');
    }

    /**
     * PERBAIKAN UTAMA ADA DI METHOD INI
     */
    public function deleteShift(int $id)
    {
        // Langkah 1: Cari shift dan hitung berapa banyak jadwal yang masih menggunakannya.
        // `withCount('detailSchedules')` sangat efisien untuk ini.
        $shift = Shift::withCount('detailSchedules')->find($id);

        // Jika shift tidak ditemukan, kembalikan error.
        if (!$shift) {
            return redirect()->back()->with('error', 'Shift tidak ditemukan!');
        }

        // Langkah 2: Periksa hasil hitungan.
        // Properti `detail_schedules_count` akan otomatis ada berkat `withCount`.
        if ($shift->detail_schedules_count > 0) {
            // Langkah 3: Jika count > 0, jangan hapus. Kembalikan dengan pesan error yang jelas.
            $errorMessage = 'Gagal! Shift "' . $shift->name . '" tidak dapat dihapus karena masih digunakan oleh ' . $shift->detail_schedules_count . ' jadwal.';
            return redirect()->back()->with('error', $errorMessage);
        }

        $shiftName = $shift->name;

        // Langkah 4: Jika aman (count == 0), baru panggil service untuk menghapus.
        $this->shiftService->deleteShift($id);

        \App\Helper\ActivityLogger::log('DELETE', 'Master Data', "Admin menghapus shift kerja: {$shiftName}");

        return redirect()->back()->with('success', 'Data Shift berhasil dihapus!');
    }
}
