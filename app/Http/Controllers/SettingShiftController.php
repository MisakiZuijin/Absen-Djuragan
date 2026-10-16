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
        $rollingGroups = \App\Models\ShiftRollingGroup::with(['shift1', 'shift2', 'divisions.intern'])->orderBy('id')->get();
        $divisions = \App\Models\Division::with(['rollingGroup', 'intern.shift'])->orderBy('name')->get();

        $data = [
            "user" => $userData,
            "shift" => $shift,
            "rollingGroups" => $rollingGroups,
            "divisions" => $divisions,
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

        // Sinkronisasi 2 arah pasangan rolling shift jika ada input
        $partnerInput = $updateShiftRequest->input('edit_rolling_partner_shift_id') ?? $updateShiftRequest->input('rolling_partner_shift_id');
        $isRolling = $updateShiftRequest->boolean('edit_is_rolling') || $updateShiftRequest->boolean('is_rolling');

        if ($updateShiftRequest->has('edit_rolling_partner_shift_id') || $updateShiftRequest->has('rolling_partner_shift_id')) {
            $partnerId = ($partnerInput && $partnerInput != $id) ? (int) $partnerInput : null;
            $currentShift = Shift::find($id);

            if ($currentShift) {
                $oldPartnerId = $currentShift->rolling_partner_shift_id;

                if ($partnerId && $isRolling) {
                    // Putuskan relasi partner lama dari kedua sisi
                    if ($oldPartnerId && $oldPartnerId !== $partnerId) {
                        Shift::where('id', $oldPartnerId)->update(['rolling_partner_shift_id' => null, 'is_rolling' => false]);
                    }
                    Shift::where('rolling_partner_shift_id', $partnerId)
                        ->whereNotIn('id', [$id])
                        ->update(['rolling_partner_shift_id' => null, 'is_rolling' => false]);

                    // Pasangkan 2 arah
                    $currentShift->update(['rolling_partner_shift_id' => $partnerId, 'is_rolling' => true]);
                    Shift::where('id', $partnerId)->update(['rolling_partner_shift_id' => $id, 'is_rolling' => true]);
                } else {
                    // Lepas pasangan
                    if ($oldPartnerId) {
                        Shift::where('id', $oldPartnerId)->update(['rolling_partner_shift_id' => null, 'is_rolling' => false]);
                    }
                    $currentShift->update(['rolling_partner_shift_id' => null, 'is_rolling' => false]);
                }
            }
        }

        // Jika shift diset tidak rolling, non-aktifkan grup rolling yang memuat shift ini
        if (!$isRolling) {
            \App\Models\ShiftRollingGroup::where('shift_id_1', $id)
                ->orWhere('shift_id_2', $id)
                ->update(['is_active' => false]);
        }

        $shiftName = $updateShiftRequest->input('nama_Shift') ?? $updateShiftRequest->input('name');
        \App\Helper\ActivityLogger::log('UPDATE', 'Master Data', "Admin memperbarui data shift kerja: {$shiftName}");

        return redirect()->route('admin.pengaturan.shift')->with('success', 'Shift berhasil diperbarui!');
    }

    /**
     * Simpan pengaturan pasangan lock rolling shift secara 2 arah (A <-> B, C <-> D).
     */
    public function saveLockRolling(Request $request)
    {
        $pairs = $request->input('pairs', []); // [shift_id => partner_shift_id]

        DB::beginTransaction();
        try {
            $allActiveShifts = Shift::where('id', '!=', 1)->get()->keyBy('id');
            $processed = [];

            foreach ($allActiveShifts as $sId => $shift) {
                if (in_array($sId, $processed)) {
                    continue;
                }

                $partnerId = isset($pairs[$sId]) && $pairs[$sId] !== '' ? (int) $pairs[$sId] : null;

                if ($partnerId && $partnerId !== $sId && isset($allActiveShifts[$partnerId])) {
                    // Cek jika partnerId sebelumnya punya pasangan lain, putuskan dulu
                    Shift::where('rolling_partner_shift_id', $sId)
                        ->whereNotIn('id', [$partnerId])
                        ->update(['rolling_partner_shift_id' => null, 'is_rolling' => false]);

                    Shift::where('rolling_partner_shift_id', $partnerId)
                        ->whereNotIn('id', [$sId])
                        ->update(['rolling_partner_shift_id' => null, 'is_rolling' => false]);

                    // Pasangkan 2 arah
                    $shift->update([
                        'rolling_partner_shift_id' => $partnerId,
                        'is_rolling' => true,
                    ]);

                    $allActiveShifts[$partnerId]->update([
                        'rolling_partner_shift_id' => $sId,
                        'is_rolling' => true,
                    ]);

                    $processed[] = $sId;
                    $processed[] = $partnerId;
                } else {
                    // Lepas pasangan jika ada
                    $currentPartnerId = $shift->rolling_partner_shift_id;
                    if ($currentPartnerId && isset($allActiveShifts[$currentPartnerId])) {
                        $allActiveShifts[$currentPartnerId]->update([
                            'rolling_partner_shift_id' => null,
                            'is_rolling' => false,
                        ]);
                    }

                    $shift->update([
                        'rolling_partner_shift_id' => null,
                        'is_rolling' => false,
                    ]);

                    $processed[] = $sId;
                }
            }

            DB::commit();

            \App\Helper\ActivityLogger::log('UPDATE', 'Master Data', 'Admin memperbarui konfigurasi pasangan lock rolling shift');

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pengaturan pasangan lock rolling shift berhasil disimpan!',
                ]);
            }

            return redirect()->route('admin.pengaturan.shift', ['tab' => 'tab-lock-rolling'])
                ->with('success', 'Pengaturan pasangan lock rolling shift berhasil disimpan!');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error saveLockRolling: ' . $th->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan pengaturan pasangan rolling: ' . $th->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan pasangan rolling: ' . $th->getMessage());
        }
    }

    /**
     * Simpan pemetaan grup rolling shift ke divisi (Opsi 1: Grup Rolling per Divisi)
     */
    public function saveDivisionRollingGroups(Request $request)
    {
        $mappings = $request->input('division_groups', []); // [division_id => shift_rolling_group_id]

        DB::beginTransaction();
        try {
            foreach ($mappings as $divisionId => $groupId) {
                $targetGroupId = !empty($groupId) ? (int) $groupId : null;
                \App\Models\Division::where('id', $divisionId)->update([
                    'shift_rolling_group_id' => $targetGroupId
                ]);
            }

            DB::commit();
            \App\Helper\ActivityLogger::log('UPDATE', 'Master Data', 'Admin memperbarui pemetaan grup rolling shift per divisi');

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pemetaan grup rolling shift divisi berhasil diperbarui!',
                ]);
            }

            return redirect()->route('admin.pengaturan.shift', ['tab' => 'tab-lock-rolling'])
                ->with('success', 'Pemetaan grup rolling shift divisi berhasil disimpan!');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error saveDivisionRollingGroups: ' . $th->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan pemetaan divisi: ' . $th->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan pemetaan divisi: ' . $th->getMessage());
        }
    }

    /**
     * Tambah Grup Pasangan Rolling Shift Baru
     */
    public function storeRollingGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'shift_id_1' => 'required|integer|exists:shifts,id|different:shift_id_2',
            'shift_id_2' => 'required|integer|exists:shifts,id',
            'description' => 'nullable|string|max:255',
        ], [
            'shift_id_1.different' => 'Shift 1 dan Shift 2 tidak boleh shift yang sama.',
            'name.required' => 'Nama grup wajib diisi.',
            'shift_id_1.required' => 'Pilih shift pertama.',
            'shift_id_2.required' => 'Pilih shift kedua.',
        ]);

        $shift1 = Shift::find($validated['shift_id_1']);
        $shift2 = Shift::find($validated['shift_id_2']);

        // Cegah shift none (ID 1)
        if ($validated['shift_id_1'] == 1 || $validated['shift_id_2'] == 1) {
            $msg = 'Shift sistem ("none") tidak dapat dimasukkan dalam grup rolling.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        }

        // VALIDASI: Hanya shift yang opsi rolling-nya aktif (is_rolling = 1) yang boleh dijadikan pasangan rolling!
        if (!$shift1 || empty($shift1->is_rolling)) {
            $msg = "Shift '{$shift1?->name}' saat ini tidak aktif untuk rolling. Aktifkan opsi \"Dapat Digunakan untuk Rolling\" pada shift tersebut terlebih dahulu.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        }

        if (!$shift2 || empty($shift2->is_rolling)) {
            $msg = "Shift '{$shift2?->name}' saat ini tidak aktif untuk rolling. Aktifkan opsi \"Dapat Digunakan untuk Rolling\" pada shift tersebut terlebih dahulu.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        }

        try {
            $group = \App\Models\ShiftRollingGroup::create([
                'name' => $validated['name'],
                'shift_id_1' => $validated['shift_id_1'],
                'shift_id_2' => $validated['shift_id_2'],
                'description' => $validated['description'] ?? null,
                'is_active' => true,
            ]);

            \App\Helper\ActivityLogger::log('CREATE', 'Master Data', "Admin membuat grup rolling shift baru: {$group->name}");

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Grup rolling \"{$group->name}\" berhasil ditambahkan!",
                    'data' => $group->load(['shift1', 'shift2']),
                ]);
            }

            return redirect()->route('admin.pengaturan.shift', ['tab' => 'tab-lock-rolling'])
                ->with('success', "Grup rolling \"{$group->name}\" berhasil ditambahkan!");
        } catch (\Throwable $th) {
            Log::error('Error storeRollingGroup: ' . $th->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan grup: ' . $th->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menambahkan grup rolling: ' . $th->getMessage());
        }
    }

    /**
     * Hapus Grup Pasangan Rolling Shift
     */
    public function deleteRollingGroup(Request $request, $id = null)
    {
        $targetId = (int) ($id ?: $request->route('id'));

        try {
            $group = \App\Models\ShiftRollingGroup::findOrFail($targetId);
            $name = $group->name;
            $group->delete(); // divisi yang terhubung otomatis nullOnDelete

            \App\Helper\ActivityLogger::log('DELETE', 'Master Data', "Admin menghapus grup rolling shift: {$name}");

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Grup rolling \"{$name}\" berhasil dihapus!",
                ]);
            }

            return redirect()->route('admin.pengaturan.shift', ['tab' => 'tab-lock-rolling'])
                ->with('success', "Grup rolling \"{$name}\" berhasil dihapus!");
        } catch (\Throwable $th) {
            Log::error('Error deleteRollingGroup: ' . $th->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus grup: ' . $th->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menghapus grup rolling: ' . $th->getMessage());
        }
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
