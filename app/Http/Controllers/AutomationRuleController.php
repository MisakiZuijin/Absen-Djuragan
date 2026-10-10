<?php

namespace App\Http\Controllers;

use App\Helper\ActivityLogger;
use App\Models\AutomationSetting;
use App\Models\Division;
use App\Models\DivisionScheduleRule;
use App\Models\Intern;
use App\Models\InternConflictRule;
use App\Models\Office;
use App\Models\OfficeDivision;
use App\Models\OfficeShiftCapacity;
use App\Models\School;
use App\Models\Shift;
use App\Services\AutoSchedulingEngine;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AutomationRuleController extends Controller
{
    protected UserService $userService;
    protected AutoSchedulingEngine $engine;

    public function __construct(UserService $userService, AutoSchedulingEngine $engine)
    {
        $this->userService = $userService;
        $this->engine = $engine;
    }

    /**
     * Tampilan Pusat Aturan & Simulator Penjadwalan Otomatis.
     */
    public function index(Request $request): View
    {
        $userData = $this->userService->getUserLoggedData();

        // 1. Ambil data kantor berserta relasi divisi dan shift
        $offices = Office::with(['coordinate', 'coordinates', 'divisions', 'shiftCapacities'])->get();

        // 2. Ambil data divisi berserta aturan jadwal
        $divisions = Division::with(['scheduleRule', 'offices'])->get();

        // Konfigurasi split-shift sepenuhnya mengikuti halaman Manage Shift (tidak lagi ditimpa paksa di sini).

        // 3. Ambil data shift
        $shifts = Shift::orderBy('start_time')->get();

        // 4. Matriks Checklist Divisi per Kantor [division_id][office_id] => bool
        $matrixMap = [];
        $rawOfficeDivisions = OfficeDivision::all();
        foreach ($rawOfficeDivisions as $od) {
            $matrixMap[$od->division_id][$od->office_id] = (bool) $od->is_active;
        }

        // 5. Kapasitas Meja Kantor per Shift [office_id][shift_id] => ['max_capacity' => X, 'is_enabled' => Y]
        $capacityMap = [];
        $rawCapacities = OfficeShiftCapacity::all();
        foreach ($rawCapacities as $cap) {
            $capacityMap[$cap->office_id][$cap->shift_id] = [
                'max_capacity' => (int) $cap->max_capacity,
                'is_enabled' => (bool) $cap->is_enabled,
            ];
        }

        // 6. Aturan Jadwal per Divisi [division_id] => DivisionScheduleRule
        $divisionRules = DivisionScheduleRule::all()->keyBy('division_id');

        // 7. Aturan Konflik Pasangan
        $conflictRules = InternConflictRule::with([
            'intern1.user.profile',
            'intern1.school',
            'intern1.division',
            'intern2.user.profile',
            'intern2.school',
            'intern2.division'
        ])->get();

        // 8. Daftar Seluruh Pemagang Aktif dengan profil, kantor terkunci, shift, dan periode magang
        $interns = Intern::with([
            'user.profile',
            'division',
            'school',
            'lockedOffice',
            'blacklistedOffice',
            'shift'
        ])
            ->whereHas('user', function ($q) {
                $q->where('is_active', 1);
            })
            ->get()
            ->sortBy(fn($i) => strtolower($i->user?->profile?->full_name ?? $i->user?->name ?? ''));

        // 9. Ambil Pengaturan Global Mesin Otomatisasi
        $automationSettings = AutomationSetting::getAll();

        // 10. Ambil Data Sekolah untuk filter pemagang
        $schools = School::orderBy('name')->get();

        return view('admin.automation-rules', [
            'user' => $userData,
            'offices' => $offices,
            'divisions' => $divisions,
            'shifts' => $shifts,
            'matrixMap' => $matrixMap,
            'capacityMap' => $capacityMap,
            'divisionRules' => $divisionRules,
            'conflictRules' => $conflictRules,
            'interns' => $interns,
            'schools' => $schools,
            'automationSettings' => $automationSettings,
        ]);
    }

    /**
     * Simpan Matriks Checklist Divisi per Kantor (Tab 1).
     */
    public function saveMatrix(Request $request): RedirectResponse|JsonResponse
    {
        $matrix = $request->input('matrix', []);
        $offices = Office::all();
        $divisions = Division::all();

        DB::beginTransaction();
        try {
            foreach ($divisions as $division) {
                foreach ($offices as $office) {
                    $isActive = isset($matrix[$division->id][$office->id]) && (bool) $matrix[$division->id][$office->id];

                    OfficeDivision::updateOrCreate(
                        [
                            'office_id' => $office->id,
                            'division_id' => $division->id,
                        ],
                        [
                            'is_active' => $isActive,
                        ]
                    );
                }
            }

            DB::commit();
            ActivityLogger::log('UPDATE', 'Otomatisasi Penjadwalan', 'Admin memperbarui matriks checklist divisi per kantor');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Matriks relasi divisi dan kantor berhasil diperbarui!',
                ]);
            }

            return redirect()->route('admin.automation-rules.index', ['tab' => 'tab-matrix'])
                ->with('success', 'Matriks divisi per kantor berhasil disimpan!');
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan matriks: ' . $th->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Terjadi kesalahan saat menyimpan matriks: ' . $th->getMessage());
        }
    }

    /**
     * Simpan Shift Aktif & Kuota Meja Kantor (Tab 2).
     */
    public function saveCapacities(Request $request): RedirectResponse|JsonResponse
    {
        $capacities = $request->input('capacities', []);
        $officeCapacities = $request->input('office_capacities', []);
        $officeHybrids = $request->input('office_hybrid', []);
        $offices = Office::all();
        $shifts = Shift::all();

        DB::beginTransaction();
        try {
            // 1. Update kapasitas total meja fisik kantor (Universal) dan izin skema 50:50
            foreach ($offices as $office) {
                $totalSeats = isset($officeCapacities[$office->id])
                    ? max(1, (int) $officeCapacities[$office->id])
                    : (int) ($office->capacity ?? 15);
                $isHybridEnabled = !empty($officeHybrids[$office->id]);

                $office->update([
                    'capacity' => $totalSeats,
                    'is_hybrid_enabled' => $isHybridEnabled,
                ]);
            }

            // 2. Sinkronkan shift aktif dan kuota kursi per shift.
            //    Kuota shift TIDAK BOLEH melebihi kapasitas meja fisik kantor (di-clamp di server).
            foreach ($offices as $office) {
                $physicalCapacity = isset($officeCapacities[$office->id])
                    ? max(1, (int) $officeCapacities[$office->id])
                    : (int) ($office->capacity ?? 15);

                foreach ($shifts as $shift) {
                    $item = $capacities[$office->id][$shift->id] ?? null;
                    $isEnabled = !empty($item['is_enabled']);

                    $requested = isset($item['max_capacity']) && $item['max_capacity'] !== ''
                        ? (int) $item['max_capacity']
                        : $physicalCapacity; // kosong => ikut kapasitas fisik kantor
                    $shiftCapacity = max(1, min($requested, $physicalCapacity));

                    OfficeShiftCapacity::updateOrCreate(
                        [
                            'office_id' => $office->id,
                            'shift_id' => $shift->id,
                        ],
                        [
                            'max_capacity' => $shiftCapacity,
                            'is_enabled' => $isEnabled,
                        ]
                    );
                }
            }

            DB::commit();
            ActivityLogger::log('UPDATE', 'Otomatisasi Penjadwalan', 'Admin memperbarui kapasitas meja universal dan shift aktif kantor');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kapasitas meja fisik per shift berhasil diperbarui!',
                ]);
            }

            return redirect()->route('admin.automation-rules.index', ['tab' => 'tab-capacities'])
                ->with('success', 'Kapasitas meja dan shift berhasil disimpan!');
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan kapasitas meja: ' . $th->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Terjadi kesalahan saat menyimpan kapasitas: ' . $th->getMessage());
        }
    }

    /**
     * Simpan Distribusi Shift & Fleksibilitas Divisi (Tab 3).
     */
    public function saveDivisionRules(Request $request): RedirectResponse|JsonResponse
    {
        $rules = $request->input('rules', []);
        $divisions = Division::all();

        DB::beginTransaction();
        try {
            foreach ($divisions as $division) {
                $item = $rules[$division->id] ?? [];

                $allowedShifts = isset($item['allowed_shift_ids']) && is_array($item['allowed_shift_ids'])
                    ? array_map('intval', $item['allowed_shift_ids'])
                    : [];

                $mode = $item['shift_distribution_mode'] ?? 'balanced_quota';
                $allowHybrid = !empty($item['allow_hybrid_balancing']);
                $allowSplit = !empty($item['allow_split_shift']);

                DivisionScheduleRule::updateOrCreate(
                    [
                        'division_id' => $division->id,
                    ],
                    [
                        'allowed_shift_ids' => $allowedShifts,
                        'shift_distribution_mode' => $mode,
                        'allow_hybrid_balancing' => $allowHybrid,
                        'allow_split_shift' => $allowSplit,
                    ]
                );
            }

            DB::commit();
            ActivityLogger::log('UPDATE', 'Otomatisasi Penjadwalan', 'Admin memperbarui aturan distribusi shift & fleksibilitas divisi');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Aturan pola kerja divisi berhasil diperbarui!',
                ]);
            }

            return redirect()->route('admin.automation-rules.index', ['tab' => 'tab-division-rules'])
                ->with('success', 'Aturan shift divisi berhasil disimpan!');
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan aturan divisi: ' . $th->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Terjadi kesalahan saat menyimpan aturan divisi: ' . $th->getMessage());
        }
    }

    /**
     * Tambah Pasangan Konflik Pemagang (Tab 3).
     */
    public function storeConflict(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'intern_id_1' => 'required|integer|exists:interns,id|different:intern_id_2',
            'intern_id_2' => 'required|integer|exists:interns,id',
            'reason' => 'nullable|string|max:255',
        ], [
            'intern_id_1.different' => 'Pemagang 1 dan Pemagang 2 tidak boleh orang yang sama.',
            'intern_id_1.required' => 'Pilih pemagang pertama.',
            'intern_id_2.required' => 'Pilih pemagang kedua.',
        ]);

        $exists = InternConflictRule::where(function ($q) use ($validated) {
            $q->where('intern_id_1', $validated['intern_id_1'])->where('intern_id_2', $validated['intern_id_2']);
        })->orWhere(function ($q) use ($validated) {
            $q->where('intern_id_1', $validated['intern_id_2'])->where('intern_id_2', $validated['intern_id_1']);
        })->first();

        if ($exists) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aturan konflik untuk pasangan pemagang ini sudah terdaftar!',
                ], 422);
            }
            return back()->with('error', 'Aturan konflik untuk pasangan pemagang ini sudah terdaftar!');
        }

        $conflict = InternConflictRule::create([
            'intern_id_1' => $validated['intern_id_1'],
            'intern_id_2' => $validated['intern_id_2'],
            'reason' => $validated['reason'] ?? 'Pemisahan kerja atas arahan HR',
            'is_active' => true,
        ]);

        ActivityLogger::log('CREATE', 'Otomatisasi Penjadwalan', "Admin menambahkan aturan pasangan konflik pemagang #{$validated['intern_id_1']} & #{$validated['intern_id_2']}");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Aturan pasangan konflik berhasil ditambahkan!',
                'data' => $conflict->load(['intern1.user', 'intern2.user']),
            ]);
        }

        return redirect()->route('admin.automation-rules.index', ['tab' => 'tab-conflicts'])
            ->with('success', 'Aturan pasangan konflik berhasil ditambahkan!');
    }

    /**
     * Hapus Pasangan Konflik Pemagang (Tab 4: Pasangan Konflik).
     */
    public function deleteConflict(int $id, Request $request): RedirectResponse|JsonResponse
    {
        $conflict = InternConflictRule::findOrFail($id);
        $conflict->delete();

        ActivityLogger::log('DELETE', 'Otomatisasi Penjadwalan', "Admin menghapus aturan pasangan konflik ID #{$id}");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Aturan pasangan konflik berhasil dihapus!',
            ]);
        }

        return redirect()->route('admin.automation-rules.index', ['tab' => 'tab-conflicts'])
            ->with('success', 'Aturan pasangan konflik berhasil dihapus!');
    }

    /**
     * Toggle status aktif pasangan konflik pemagang (Tab 3).
     */
    public function toggleConflict(int $id, Request $request): JsonResponse
    {
        $conflict = InternConflictRule::findOrFail($id);
        $conflict->is_active = !$conflict->is_active;
        $conflict->save();

        ActivityLogger::log('UPDATE', 'Otomatisasi Penjadwalan', "Admin mengubah status aktif aturan konflik ID #{$id} menjadi " . ($conflict->is_active ? 'Aktif' : 'Nonaktif'));

        return response()->json([
            'success' => true,
            'is_active' => $conflict->is_active,
            'message' => 'Status aturan konflik berhasil diubah!',
        ]);
    }

    /**
     * Simpan Pengaturan Rolling Shift & Jam Split Shift (Tab 4).
     */
    public function saveShiftRolling(Request $request): RedirectResponse|JsonResponse
    {
        $shiftsData = $request->input('shifts', []);

        DB::beginTransaction();
        try {
            foreach ($shiftsData as $shiftId => $data) {
                $shift = Shift::find($shiftId);
                if (!$shift) continue;

                $isMorning = (stripos($shift->name, 'pagi') !== false) || ($shift->start_time && $shift->start_time < '12:00:00');
                $isRolling = !empty($data['is_rolling']);
                // Shift Pagi selalu Full WFO (tidak pernah split)
                $isSplit = $isMorning ? false : !empty($data['is_split_shift']);

                $shift->update([
                    'is_rolling' => $isRolling,
                    'is_split_shift' => $isSplit,
                    'split_wfh_start' => $isSplit ? ($data['split_wfh_start'] ?? '13:00:00') : null,
                    'split_wfh_end' => $isSplit ? ($data['split_wfh_end'] ?? '16:00:00') : null,
                    'split_wfo_start' => $isSplit ? ($data['split_wfo_start'] ?? '16:00:00') : null,
                    'split_wfo_end' => $isSplit ? ($data['split_wfo_end'] ?? ($shift->end_time ?: '21:00:00')) : null,
                ]);
            }

            DB::commit();
            ActivityLogger::log('UPDATE', 'Otomatisasi Penjadwalan', 'Admin memperbarui konfigurasi rolling shift dan split shift');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pengaturan shift dan rolling berhasil diperbarui!',
                ]);
            }

            return redirect()->route('admin.automation-rules.index', ['tab' => 'tab-shift-rolling'])
                ->with('success', 'Pengaturan shift dan rolling berhasil disimpan!');
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan pengaturan shift: ' . $th->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Terjadi kesalahan saat menyimpan pengaturan shift: ' . $th->getMessage());
        }
    }

    /**
     * Simpan Kebijakan Penempatan Pemagang Massal & Periode Magang (Tab 4).
     */
    public function saveBulkInternPolicies(Request $request): RedirectResponse|JsonResponse
    {
        $internsData = $request->input('interns', []);

        DB::beginTransaction();
        try {
            // Ambil semua intern yang akan diupdate berserta relasi sekolah
            $targetInterns = Intern::with('school')
                ->whereIn('id', array_keys($internsData))
                ->get()
                ->keyBy('id');

            foreach ($internsData as $internId => $data) {
                $internModel = $targetInterns->get($internId);
                if (!$internModel) continue;

                // ATURAN MUTLAK: Jika sekolah pemagang diset sebagai Sekolah Remote / Luar Kota,
                // tipe penempatan WAJIB terkunci ke WFH Penuh (wfh_only) dan kantor terkunci dinonaktifkan.
                $isRemoteSchool = (bool) ($internModel->school?->is_remote_school ?? false);

                if ($isRemoteSchool) {
                    $workPlacement = 'wfh_only';
                    $lockedOffice = null;
                } else {
                    $workPlacement = $data['work_placement_type'] ?? 'auto_office';
                    $lockedOffice = !empty($data['locked_office_id']) ? (int) $data['locked_office_id'] : null;
                }

                $updateData = [
                    'work_placement_type' => $workPlacement,
                    'hybrid_group' => in_array($data['hybrid_group'] ?? 'auto', ['auto', 'A', 'B'], true) ? $data['hybrid_group'] : 'auto',
                    'locked_office_id' => $lockedOffice,
                    'blacklisted_office_id' => !empty($data['blacklisted_office_id']) ? (int) $data['blacklisted_office_id'] : null,
                ];

                if (array_key_exists('start_date', $data)) {
                    $updateData['start_date'] = !empty($data['start_date']) ? $data['start_date'] : null;
                }
                if (array_key_exists('end_date', $data)) {
                    $updateData['end_date'] = !empty($data['end_date']) ? $data['end_date'] : null;
                }

                $internModel->update($updateData);
            }

            DB::commit();
            ActivityLogger::log('UPDATE', 'Otomatisasi Penjadwalan', 'Admin memperbarui kebijakan penempatan dan batas masa magang massal pemagang');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kebijakan penempatan massal pemagang berhasil disimpan!',
                ]);
            }

            return redirect()->route('admin.automation-rules.index', ['tab' => 'tab-intern-policies'])
                ->with('success', 'Kebijakan pemagang massal berhasil disimpan!');
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan kebijakan pemagang: ' . $th->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Terjadi kesalahan saat menyimpan kebijakan pemagang: ' . $th->getMessage());
        }
    }

    /**
     * Bagi Rata Grup A & B per Divisi secara proporsional 50:50.
     * Mengabaikan pemagang sekolah remote / WFH penuh agar tidak mengubah status remote mereka.
     */
    public function balanceHybridGroups(Request $request): RedirectResponse|JsonResponse
    {
        $divisionId = $request->input('division_id'); // opsional

        $query = Intern::with('school')->whereHas('user', function ($q) {
            $q->where('is_active', 1);
        });

        if (!empty($divisionId)) {
            $query->where('division_id', $divisionId);
        }

        $interns = $query->get()->groupBy('division_id');

        DB::beginTransaction();
        try {
            $totalUpdated = 0;

            foreach ($interns as $divId => $divInterns) {
                // Saring pemagang yang BUKAN sekolah remote dan BUKAN WFH full
                $eligibleInterns = $divInterns->filter(function ($i) {
                    return !($i->school?->is_remote_school || $i->work_placement_type === 'wfh_only');
                })->values();

                foreach ($eligibleInterns as $index => $intern) {
                    $group = ($index % 2 === 0) ? 'A' : 'B';
                    $intern->hybrid_group = $group;
                    $intern->save();
                    $totalUpdated++;
                }
            }

            DB::commit();
            ActivityLogger::log('UPDATE', 'Otomatisasi Penjadwalan', "Admin membagi rata Grup A & B pemagang secara proporsional ({$totalUpdated} pemagang)");

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil membagi rata {$totalUpdated} pemagang ke Grup A dan B secara seimbang 50:50 per divisi!",
                    'total_updated' => $totalUpdated,
                ]);
            }

            return redirect()->route('admin.automation-rules.index', ['tab' => 'tab-intern-policies'])
                ->with('success', "Berhasil membagi rata {$totalUpdated} pemagang ke Grup A dan B secara seimbang per divisi!");
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membagi rata grup: ' . $th->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Terjadi kesalahan saat membagi grup: ' . $th->getMessage());
        }
    }

    /**
     * Simpan Pengaturan Parameter Global Mesin Otomatisasi (Tab 6).
     */
    public function saveGlobalSettings(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'rotation_anchor_date' => 'required|date',
            'include_sundays' => 'nullable',
            'skip_holidays' => 'nullable',
            'enable_tier2_split_shift' => 'nullable',
            'enable_tier3_hybrid_rolling' => 'nullable',
            'overcrowded_threshold_percent' => 'nullable|integer|min:50|max:200',
            'max_distance_km' => 'nullable|numeric|min:1|max:100',
            'default_hybrid_pattern' => 'nullable|string|in:daily_alternating,block_3days',
        ]);

        try {
            AutomationSetting::set('rotation_anchor_date', $validated['rotation_anchor_date'], 'Tanggal dasar perhitungan linier rotasi mingguan');
            AutomationSetting::set('include_sundays', $request->boolean('include_sundays') ? '1' : '0', 'Sertakan hari Minggu dalam penjadwalan');
            AutomationSetting::set('skip_holidays', $request->boolean('skip_holidays') ? '1' : '0', 'Lewati hari libur nasional');
            AutomationSetting::set('enable_tier2_split_shift', $request->boolean('enable_tier2_split_shift') ? '1' : '0', 'Aktifkan Tier 2 Split Shift saat kapasitas padat');
            AutomationSetting::set('enable_tier3_hybrid_rolling', $request->boolean('enable_tier3_hybrid_rolling') ? '1' : '0', 'Aktifkan Tier 3 Rolling Hybrid 50% saat kapasitas penuh');

            if (isset($validated['overcrowded_threshold_percent'])) {
                AutomationSetting::set('overcrowded_threshold_percent', (string) $validated['overcrowded_threshold_percent'], 'Ambang batas okupansi untuk memicu peralihan tier');
            }
            if (isset($validated['max_distance_km'])) {
                AutomationSetting::set('max_distance_km', (string) $validated['max_distance_km'], 'Batas toleransi radius jarak kantor maksimal');
            }
            if (isset($validated['default_hybrid_pattern'])) {
                AutomationSetting::set('default_hybrid_pattern', $validated['default_hybrid_pattern'], 'Pola default pembagian hybrid');
            }

            ActivityLogger::log('UPDATE', 'Otomatisasi Penjadwalan', 'Admin memperbarui parameter global mesin otomatisasi');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Parameter global mesin otomatisasi berhasil diperbarui!',
                ]);
            }

            return redirect()->route('admin.automation-rules.index', ['tab' => 'tab-global-settings'])
                ->with('success', 'Parameter global otomatisasi berhasil disimpan!');
        } catch (\Throwable $th) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan parameter global: ' . $th->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Terjadi kesalahan saat menyimpan parameter global: ' . $th->getMessage());
        }
    }

    /**
     * Hitung & Berikan Rekomendasi Pengaturan Otomatisasi Berdasarkan Rasio Beban.
     */
    public function getRecommendation(Request $request): JsonResponse
    {
        $divisions = Division::with(['offices', 'scheduleRule'])->get();
        $offices = Office::all()->keyBy('id');
        $officeDivisions = OfficeDivision::where('is_active', true)->get();

        $divisionOffices = [];
        foreach ($officeDivisions as $od) {
            $divisionOffices[$od->division_id][] = (int) $od->office_id;
        }

        $activeInternCounts = Intern::whereHas('user', function ($q) {
            $q->where('is_active', 1);
        })->select('division_id', DB::raw('count(*) as total'))
          ->groupBy('division_id')
          ->pluck('total', 'division_id');

        $recommendations = [];

        $totalInterns = $activeInternCounts->sum();
        $totalSeats = $offices->sum(fn($o) => (int) ($o->capacity ?? 15));
        $overallRatioPct = $totalSeats > 0 ? round(($totalInterns / $totalSeats) * 100, 1) : 0;
        $isOverloaded = $totalInterns > $totalSeats;

        foreach ($divisions as $division) {
            $internCount = (int) ($activeInternCounts[$division->id] ?? 0);
            $connectedOfficeIds = $divisionOffices[$division->id] ?? $offices->keys()->toArray();

            $totalCapacity = 0;
            foreach ($connectedOfficeIds as $oId) {
                if (isset($offices[$oId])) {
                    $totalCapacity += (int) ($offices[$oId]->capacity ?? 15);
                }
            }

            $loadRatio = $totalCapacity > 0 ? round($internCount / $totalCapacity, 2) : 99.0;

            if ($loadRatio <= 1.0) {
                $recommendedTier = 1;
                $tierName = 'WFO Prioritas (Kapasitas Aman)';
                $suggestedLabel = 'WFO Aman';
                $suggestedMode = 'wfo_only';
                $recommendedMode = 'balanced_quota';
                $recommendedHybrid = false;
                $recommendedSplit = false;
                $advice = "Kapasitas kantor ({$totalCapacity} kursi) mencukupi untuk {$internCount} pemagang. Rekomendasi WFO penuh tanpa perlu pembagian hybrid.";
            } elseif ($loadRatio <= 1.5) {
                $recommendedTier = 2;
                $tierName = 'Split-Shift (WFH 3 Jam)';
                $suggestedLabel = 'Split-Shift';
                $suggestedMode = 'hybrid';
                $recommendedMode = 'weekly_rotation';
                $recommendedHybrid = false;
                $recommendedSplit = true;
                $advice = "Beban pemagang ({$internCount}) melebihi kuota ({$totalCapacity} kursi). Disarankan aktifkan rotasi mingguan shift dan split-shift siang (WFH 3 jam) untuk memaksimalkan kursi.";
            } elseif ($loadRatio <= 2.0) {
                $recommendedTier = 3;
                $tierName = 'Bergilir WFO/WFH (Grup A & B)';
                $suggestedLabel = 'Bergilir WFO/WFH';
                $suggestedMode = 'hybrid';
                $recommendedMode = 'weekly_rotation';
                $recommendedHybrid = true;
                $recommendedSplit = true;
                $advice = "Beban pemagang ({$internCount}) mendekati 2x kapasitas kantor ({$totalCapacity} kursi). Disarankan mengaktifkan Bergilir WFO/WFH (Grup A & B bertukar Senin WFO/WFH).";
            } else {
                $recommendedTier = 4;
                $tierName = 'Bergilir Multi-Kantor Cabang';
                $suggestedLabel = 'Alih Cabang';
                $suggestedMode = 'hybrid';
                $recommendedMode = 'weekly_rotation';
                $recommendedHybrid = true;
                $recommendedSplit = true;
                $advice = "Beban pemagang ({$internCount}) sangat tinggi (>2x kapasitas {$totalCapacity} kursi). Wajib aktifkan Bergilir WFO/WFH dan hubungkan kantor cabang tambahan pada matriks divisi.";
            }

            $recommendations[] = [
                'division_id' => $division->id,
                'division_name' => $division->name,
                'intern_count' => $internCount,
                'office_capacity' => $totalCapacity,
                'load_ratio' => $loadRatio,
                'recommended_tier' => $recommendedTier,
                'tier_name' => $tierName,
                'suggested_label' => $suggestedLabel,
                'suggested_mode' => $suggestedMode,
                'shift_distribution_mode' => $recommendedMode,
                'allow_hybrid_balancing' => $recommendedHybrid,
                'allow_split_shift' => $recommendedSplit,
                'reason' => $advice,
                'advice' => $advice,
            ];
        }

        return response()->json([
            'success' => true,
            'summary' => [
                'total_interns' => $totalInterns,
                'total_seats' => $totalSeats,
                'overall_ratio_pct' => $overallRatioPct,
                'is_overloaded' => $isOverloaded,
            ],
            'recommendations' => array_values($recommendations),
        ]);
    }

    /**
     * Jalankan Simulasi Penjadwalan Cerdas (Tab Simulator - Dry Run Preview).
     */
    public function simulate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'division_ids' => 'nullable|array',
            'division_ids.*' => 'integer|exists:divisions,id',
            'intern_ids' => 'nullable|array',
            'intern_ids.*' => 'integer|exists:interns,id',
            'target_office_id' => 'nullable|integer|exists:offices,id',
            'include_sundays' => 'nullable',
            'skip_holidays' => 'nullable',
            'enable_tier2_split_shift' => 'nullable',
            'enable_tier3_hybrid_rolling' => 'nullable',
        ]);

        $options = [
            'intern_ids' => !empty($validated['intern_ids']) ? array_map('intval', $validated['intern_ids']) : null,
            'target_office_id' => !empty($validated['target_office_id']) ? (int) $validated['target_office_id'] : null,
            'include_sundays' => $request->has('include_sundays') ? $request->boolean('include_sundays') : null,
            'skip_holidays' => $request->has('skip_holidays') ? $request->boolean('skip_holidays') : null,
            'enable_tier2_split_shift' => $request->has('enable_tier2_split_shift') ? $request->boolean('enable_tier2_split_shift') : null,
            'enable_tier3_hybrid_rolling' => $request->has('enable_tier3_hybrid_rolling') ? $request->boolean('enable_tier3_hybrid_rolling') : null,
        ];
        $options = array_filter($options, fn($val) => $val !== null);

        $result = $this->engine->simulate(
            $validated['start_date'],
            $validated['end_date'],
            $validated['division_ids'] ?? [],
            $options
        );

        return response()->json($result);
    }

    /**
     * Terapkan Hasil Penjadwalan Cerdas ke Database (Tab Simulator - Final Deploy).
     */
    public function deploy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'division_ids' => 'nullable|array',
            'division_ids.*' => 'integer|exists:divisions,id',
            'intern_ids' => 'nullable|array',
            'intern_ids.*' => 'integer|exists:interns,id',
            'target_office_id' => 'nullable|integer|exists:offices,id',
            'include_sundays' => 'nullable',
            'skip_holidays' => 'nullable',
            'enable_tier2_split_shift' => 'nullable',
            'enable_tier3_hybrid_rolling' => 'nullable',
        ]);

        $options = [
            'intern_ids' => !empty($validated['intern_ids']) ? array_map('intval', $validated['intern_ids']) : null,
            'target_office_id' => !empty($validated['target_office_id']) ? (int) $validated['target_office_id'] : null,
            'include_sundays' => $request->has('include_sundays') ? $request->boolean('include_sundays') : null,
            'skip_holidays' => $request->has('skip_holidays') ? $request->boolean('skip_holidays') : null,
            'enable_tier2_split_shift' => $request->has('enable_tier2_split_shift') ? $request->boolean('enable_tier2_split_shift') : null,
            'enable_tier3_hybrid_rolling' => $request->has('enable_tier3_hybrid_rolling') ? $request->boolean('enable_tier3_hybrid_rolling') : null,
        ];
        $options = array_filter($options, fn($val) => $val !== null);

        $result = $this->engine->deploy(
            $validated['start_date'],
            $validated['end_date'],
            $validated['division_ids'] ?? [],
            $options
        );

        if ($result['success']) {
            ActivityLogger::log(
                'CREATE',
                'Otomatisasi Penjadwalan',
                "Admin menerapkan jadwal otomatis periode {$validated['start_date']} s/d {$validated['end_date']} (" . count($result['allocations'] ?? []) . " entri presensi)",
                ['period' => [$validated['start_date'], $validated['end_date']], 'allocations_count' => count($result['allocations'] ?? [])]
            );
        }

        return response()->json($result);
    }
}
