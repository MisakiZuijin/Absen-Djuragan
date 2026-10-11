<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Services\ShiftService;
use App\Models\Shift;
use App\Models\DetailSchedule;
use App\Models\OfficeShiftCapacity;
use App\Models\Intern;
use App\Models\DivisionScheduleRule;

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
     * Hapus shift secara aman dengan opsi pembersihan jadwal terkait dan pemutusan relasi foreign key
     */
    public function deleteShift(Request $request, int $id)
    {
        $shift = Shift::withCount('detailSchedules')->find($id);

        if (!$shift) {
            return redirect()->back()->with('error', 'Shift tidak ditemukan!');
        }

        // Shift 'none' (ID 1) tidak boleh dihapus karena merupakan default sistem
        if ($shift->id === 1 || strtolower(trim($shift->name)) === 'none') {
            return redirect()->back()->with('error', 'Shift sistem ("none") tidak dapat dihapus.');
        }

        $forceDelete = $request->boolean('force_delete') || $request->input('force_delete') == '1';

        // Jika shift masih memiliki jadwal dan admin belum mencentang konfirmasi hapus paksa
        if ($shift->detail_schedules_count > 0 && !$forceDelete) {
            $errorMessage = 'Gagal! Shift "' . $shift->name . '" masih digunakan oleh ' . $shift->detail_schedules_count . ' jadwal. Centang opsi "Hapus beserta jadwal terkait" pada konfirmasi jika ingin tetap menghapusnya.';
            return redirect()->back()->with('error', $errorMessage);
        }

        $shiftName = $shift->name;

        try {
            DB::transaction(function () use ($shift, $id, $forceDelete) {
                // 1. Bersihkan detail schedules terkait jika forceDelete disetujui
                if ($shift->detail_schedules_count > 0 && $forceDelete) {
                    DetailSchedule::where('shift_id', $id)->delete();
                }

                // 2. Hapus referensi kapasitas kantor (menghindari SQL foreign key constraint error)
                OfficeShiftCapacity::where('shift_id', $id)->delete();

                // 3. Lepaskan penugasan shift di data pemagang
                Intern::where('shift_id', $id)->update(['shift_id' => null]);

                // 4. Bersihkan ID shift dari aturan divisi (allowed_shift_ids)
                $divisionRules = DivisionScheduleRule::all();
                foreach ($divisionRules as $rule) {
                    $allowed = $rule->allowed_shift_ids ?? [];
                    if (is_array($allowed) && in_array($id, $allowed)) {
                        $rule->allowed_shift_ids = array_values(array_diff($allowed, [$id]));
                        $rule->save();
                    }
                }

                // 5. Bersihkan riwayat pra-pendaftaran ganti jam jika ada
                if (class_exists(\App\Models\ChangeTimeRegistration::class) && Schema::hasTable('change_time_registrations')) {
                    \App\Models\ChangeTimeRegistration::where('shift_id', $id)->delete();
                }

                // 5b. Bersihkan ID shift dari pengaturan Ganti Jam (allowed_shift_ids)
                if (class_exists(\App\Models\ChangeTimeSetting::class) && Schema::hasTable('change_time_settings')) {
                    foreach (\App\Models\ChangeTimeSetting::all() as $setting) {
                        $allowed = $setting->allowed_shift_ids ?? [];
                        if (is_array($allowed) && in_array($id, $allowed)) {
                            $setting->allowed_shift_ids = array_values(array_diff($allowed, [$id]));
                            $setting->save();
                        }
                    }
                }

                // 5c. Bersihkan referensi jadwal piket
                if (class_exists(\App\Models\PiketSchedule::class) && Schema::hasTable('piket_schedules')) {
                    \App\Models\PiketSchedule::where('shift_id', $id)->delete();
                }

                // 5d. Bersihkan sesi ganti jam
                if (class_exists(\App\Models\ChangeTimeSession::class) && Schema::hasTable('change_time_sessions')) {
                    \App\Models\ChangeTimeSession::where('shift_id', $id)->delete();
                }

                // 5e. Putuskan relasi offline attendances
                if (Schema::hasTable('offline_attendances')) {
                    DB::table('offline_attendances')->where('shift_id', $id)->update(['shift_id' => null]);
                }

                // 5f. Putuskan relasi late absences
                if (Schema::hasTable('late_absences')) {
                    DB::table('late_absences')->where('shift_id', $id)->update(['shift_id' => null]);
                }

                // 5g. Bersihkan pivot broadcast shift
                if (Schema::hasTable('broadcast_shift')) {
                    DB::table('broadcast_shift')->where('shift_id', $id)->delete();
                }

                // 6. Hapus master shift
                $this->shiftService->deleteShift($id);
            });

            \App\Helper\ActivityLogger::log('DELETE', 'Master Data', "Admin menghapus shift kerja: {$shiftName}");

            return redirect()->route('admin.pengaturan.shift')->with('success', "Data Shift \"{$shiftName}\" berhasil dihapus!");
        } catch (\Throwable $th) {
            Log::error('Error deleteShift: ' . $th->getMessage(), [
                'id' => $id,
                'trace' => $th->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Gagal menghapus shift: ' . $th->getMessage());
        }
    }
}
