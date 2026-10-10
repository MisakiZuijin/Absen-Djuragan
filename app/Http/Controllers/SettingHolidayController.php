<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Services\HolidayService;
use App\Models\Holiday;
use App\Models\Office;
use App\Models\Shift;
use Carbon\Carbon;
use App\Services\HolidaySyncService;
use App\Services\PiketSchedulingService;
use App\Models\PiketSchedule;
use Illuminate\Support\Facades\Artisan; // <--- GANTI Cache DENGAN Artisan

class SettingHolidayController extends Controller
{
    protected UserService $userService;
    protected HolidayService $holidayService;

    public function __construct(UserService $userService, HolidayService $holidayService)
    {
        $this->userService = $userService;
        $this->holidayService = $holidayService;
    }

    public function adminSettingHolidayView(Request $request): View
    {
        $userData = $this->userService->getUserLoggedData();
        $holidaylist = Holiday::whereYear('date', 2026)->orderBy('date', 'asc')->get()->map(function ($holiday) {
            $holiday->raw_date = $holiday->date;
            $holiday->date = Carbon::parse($holiday->date)->format('d-m-Y');
            return $holiday;
        });
        $offices = Office::all();
        $shifts = Shift::orderBy('start_time')->get();

        $defaultOfficeId = (string)($offices->first()?->id ?? '1');
        $legacyOfficeId = $request->get('office_id');
        $activeTab = $request->get('tab', 'holiday');

        // SOP Magang: default global ('all') atau spesifik jika dipilih
        $sopOfficeId = $request->get('sop_office_id', ($activeTab === 'sop' && $legacyOfficeId) ? $legacyOfficeId : 'all');

        // Peraturan Kantor: default spesifik per-kantor pertama agar tidak menimpa semua kantor
        $rulesOfficeId = $request->get('rules_office_id', ($activeTab === 'rules' && $legacyOfficeId) ? $legacyOfficeId : $defaultOfficeId);

        // Jadwal Piket: default 'all' (Semua Kantor) atau spesifik jika dipilih
        $piketOfficeId = $request->get('piket_office_id', ($activeTab === 'piket' && $legacyOfficeId) ? $legacyOfficeId : 'all');

        // Rentang Tanggal Piket Aktif: default minggu berjalan (Senin - Minggu)
        $piketStartDate = $request->get('piket_start_date', now()->startOfWeek(Carbon::MONDAY)->toDateString());
        $piketEndDate = $request->get('piket_end_date', now()->endOfWeek(Carbon::SUNDAY)->toDateString());
        // Ambil seluruh roster piket pada rentang tanggal aktif agar tab filter per kantor di dalam list jadwal piket dapat berjalan instan tanpa reload halaman
        $piketRoster = app(PiketSchedulingService::class)->getPiketRoster($piketStartDate, $piketEndDate, null);
        $globalPiketCount = $piketRoster->count();
        $officePiketCounts = $piketRoster->groupBy('office_id')->map->count()->toArray();
        $totalPiketCount = $globalPiketCount;

        $globalPiketMaxPerWeek = \App\Models\AutomationSetting::getInt('piket_max_per_week', 1);
        $currentPiketLimit = ($piketOfficeId && $piketOfficeId !== 'all')
            ? ($offices->firstWhere('id', (int)$piketOfficeId)?->piket_max_per_week ?? $globalPiketMaxPerWeek)
            : ($offices->first()?->piket_max_per_week ?? $globalPiketMaxPerWeek);
        $currentPiketLimit = max(1, min(7, (int)$currentPiketLimit));

        $data = [
            "holidaylist" => $holidaylist,
            "offices" => $offices,
            "shifts" => $shifts,
            "user" => $userData,
            "activeTab" => $activeTab,
            "sopOfficeId" => $sopOfficeId,
            "rulesOfficeId" => $rulesOfficeId,
            "piketOfficeId" => $piketOfficeId,
            "piketStartDate" => $piketStartDate,
            "piketEndDate" => $piketEndDate,
            "piketRoster" => $piketRoster,
            "totalPiketCount" => $totalPiketCount,
            "globalPiketCount" => $globalPiketCount,
            "officePiketCounts" => $officePiketCounts,
            "globalPiketMaxPerWeek" => $globalPiketMaxPerWeek,
            "currentPiketLimit" => $currentPiketLimit,
        ];

        return view('admin.pengaturan-holiday')->with($data);
    }

    /**
     * Fungsi helper untuk membersihkan semua cache secara paksa.
     */
    private function clearAllCaches()
    {
        \Illuminate\Support\Facades\Cache::forget('offices_all');
        \Illuminate\Support\Facades\Cache::flush();
        try {
            Artisan::call('optimize:clear');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Artisan optimize:clear warning: " . $e->getMessage());
        }
    }

    public function storeHoliday(Request $request)
    {
        $this->holidayService->create($request);

        // Hapus jadwal kosong yang belum terisi pada tanggal libur baru tersebut agar otomatis menjadi libur (seperti Minggu)
        $holidayDate = $request->input('date') ? Carbon::parse($request->input('date'))->toDateString() : null;
        if ($holidayDate) {
            $emptyDetailSchedules = \App\Models\DetailSchedule::whereDate('date', $holidayDate)
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
                    \App\Models\Attendance::where('id', $attId)->whereNull('start_time')->delete();
                }
            }
        }

        $this->clearAllCaches(); // Panggil fungsi pembersihan cache

        \App\Helper\ActivityLogger::log('CREATE', 'Master Data', "Admin menambahkan Hari Libur baru: {$request->input('title')} ({$request->input('date')})");

        return redirect()->back()->with('success', 'Data Hari libur berhasil ditambahkan!');
    }

    public function updateHoliday(Request $request, int $id)
    {
        $oldHoliday = Holiday::find($id);
        $oldDate = $oldHoliday && $oldHoliday->date ? Carbon::parse($oldHoliday->date)->toDateString() : null;

        $this->holidayService->update($request, $id);

        $newDate = $request->input('date') ? Carbon::parse($request->input('date'))->toDateString() : null;

        // Jika tanggal berubah, pulihkan jadwal pada tanggal lama yang sekarang bukan lagi hari libur
        if ($oldDate && $oldDate !== $newDate) {
            $this->restoreSchedulesForDate($oldDate);
        }

        // Dan hapus jadwal kosong pada tanggal libur baru
        if ($newDate) {
            $emptyDetailSchedules = \App\Models\DetailSchedule::whereDate('date', $newDate)
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
                    \App\Models\Attendance::where('id', $attId)->whereNull('start_time')->delete();
                }
            }
        }

        $this->clearAllCaches(); // Panggil fungsi pembersihan cache

        \App\Helper\ActivityLogger::log('UPDATE', 'Master Data', "Admin memperbarui Hari Libur: {$request->input('title')} ({$request->input('date')})", ['holiday_id' => $id]);

        return redirect()->back()->with('success', 'Data Hari libur berhasil diperbarui!');
    }

    public function deleteHoliday(int $id)
    {
        $holiday = Holiday::find($id);
        $holidayDate = $holiday && $holiday->date ? Carbon::parse($holiday->date)->toDateString() : null;

        $this->holidayService->delete($id);

        if ($holidayDate) {
            $this->restoreSchedulesForDate($holidayDate);
        }

        $this->clearAllCaches(); // Panggil fungsi pembersihan cache

        \App\Helper\ActivityLogger::log('DELETE', 'Master Data', "Admin menghapus data Hari Libur ID: {$id}", ['holiday_id' => $id]);

        return redirect()->back()->with('success', 'Data Hari libur berhasil dihapus dan jadwal terkait telah dipulihkan kembali!');
    }

    /**
     * Sinkronkan hari libur nasional dari API publik.
     */
    public function syncHolidays(Request $request, HolidaySyncService $syncService)
    {
        $year = $request->input('year') ? (int) $request->input('year') : null;
        $result = $syncService->syncHolidays($year);

        if ($result['success']) {
            \App\Helper\ActivityLogger::log('SYNC', 'Master Data', "Admin menyinkronkan {$result['synced_count']} hari libur nasional dari API" . ($year ? " (Tahun {$year})" : ""));
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($result);
        }

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Memulihkan kembali jadwal pemagang aktif ketika hari libur dibatalkan / dihapus.
     */
    public function restoreSchedulesForDate(string $date): void
    {
        try {
            $carbonDate = Carbon::parse($date);

            // 1. Hari Minggu tidak pernah memiliki jadwal reguler
            if ($carbonDate->isSunday()) {
                return;
            }

            // 2. Jika tanggal ini masih terdaftar di tabel hari libur lain, jangan di-restore
            if (Holiday::whereDate('date', $date)->exists()) {
                return;
            }

            // 3. Ambil semua Schedule yang periode berlakunya mencakup tanggal ini
            $schedules = \App\Models\Schedule::whereDate('start_period', '<=', $date)
                ->whereDate('end_period', '>=', $date)
                ->get();

            foreach ($schedules as $sched) {
                // Cek apakah sudah ada DetailSchedule untuk jadwal dan tanggal ini
                $existingDs = \App\Models\DetailSchedule::where('schedule_id', $sched->id)
                    ->whereDate('date', $date)
                    ->first();

                if ($existingDs) {
                    continue; // Sudah ada, tidak perlu dibuat ulang
                }

                // Tentukan shift_id: cari dari minggu yang sama atau hari terdekat dalam jadwal ini
                $nearbyShiftId = \App\Models\DetailSchedule::where('schedule_id', $sched->id)
                    ->whereNotNull('shift_id')
                    ->whereBetween('date', [
                        $carbonDate->copy()->startOfWeek()->toDateString(),
                        $carbonDate->copy()->endOfWeek()->toDateString()
                    ])
                    ->value('shift_id');

                if (!$nearbyShiftId) {
                    $nearbyShiftId = \App\Models\DetailSchedule::where('schedule_id', $sched->id)
                        ->whereNotNull('shift_id')
                        ->latest('date')
                        ->value('shift_id');
                }

                if (!$nearbyShiftId) {
                    $nearbyShiftId = \App\Models\Shift::first()?->id ?? 1;
                }

                // Buat atau cari Attendance
                $attendance = \App\Models\Attendance::firstOrCreate(
                    [
                        'intern_id' => $sched->intern_id,
                        'date' => $date
                    ],
                    []
                );

                // Buat DetailSchedule baru
                \App\Models\DetailSchedule::create([
                    'attendance_id' => $attendance->id,
                    'schedule_id' => $sched->id,
                    'shift_id' => $nearbyShiftId,
                    'office_id' => $sched->office_id ?? 1,
                    'date' => $date,
                    'attd_status_id' => 1,
                    'isChangeSchedule' => 0,
                ]);
            }
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\Log::error("Error restoring schedules for date {$date}: " . $th->getMessage());
        }
    }

    public function updateOfficeInfo(Request $request)
    {
        $request->validate([
            'office_id' => 'required',
            'sop_url' => 'nullable|string|max:1000',
            'rules_url' => 'nullable|string|max:1000',
            'rules_description' => 'nullable|string',
            'piket_url' => 'nullable|string|max:1000',
            'piket_description' => 'nullable|string',
            'piket_max_per_week' => 'nullable|integer|min:1|max:7',
        ]);

        $isCheckboxChecked = $request->boolean('apply_all');
        $officeId = $request->input('office_id');

        $targetMode = ($officeId === 'all' || $isCheckboxChecked) ? 'all' : 'specific';
        if ($targetMode === 'specific' && (!$officeId || !is_numeric($officeId))) {
            $officeId = Office::first()?->id;
        }

        $dataToUpdate = [];
        if ($request->has('sop_url')) {
            $dataToUpdate['sop_url'] = $request->input('sop_url');
        }
        if ($request->has('rules_url')) {
            $dataToUpdate['rules_url'] = $request->input('rules_url');
        }
        if ($request->has('rules_description')) {
            $dataToUpdate['rules_description'] = $request->input('rules_description');
        }
        if ($request->has('piket_url')) {
            $dataToUpdate['piket_url'] = $request->input('piket_url');
        }
        if ($request->has('piket_description')) {
            $dataToUpdate['piket_description'] = $request->input('piket_description');
        }
        if ($request->filled('piket_max_per_week')) {
            $limitVal = max(1, min(7, (int)$request->input('piket_max_per_week', 1)));
            $dataToUpdate['piket_max_per_week'] = $limitVal;
            \App\Models\AutomationSetting::updateOrCreate(
                ['key' => 'piket_max_per_week'],
                ['value' => (string)$limitVal, 'description' => 'Batas maksimal pemagang ditugaskan piket per minggu']
            );
        }

        if (!empty($dataToUpdate)) {
            if ($targetMode === 'all') {
                Office::query()->update($dataToUpdate);
            } elseif ($officeId && is_numeric($officeId)) {
                $office = Office::findOrFail($officeId);
                $office->update($dataToUpdate);
            }
        }

        $this->clearAllCaches();

        $activeTab = $request->input('active_tab', 'sop');
        $tabLabel = match ($activeTab) {
            'sop' => 'SOP Magang',
            'rules' => 'Peraturan Kantor',
            'piket' => 'Jadwal Piket',
            default => 'Kantor'
        };
        $targetOfficeParam = $targetMode === 'all' ? 'all' : $officeId;
        $redirectParams = [
            'tab' => $activeTab,
        ];

        if ($activeTab === 'sop') {
            $redirectParams['sop_office_id'] = $targetOfficeParam;
        } elseif ($activeTab === 'rules') {
            $redirectParams['rules_office_id'] = $targetOfficeParam;
        } elseif ($activeTab === 'piket') {
            $redirectParams['piket_office_id'] = $targetOfficeParam;
            if ($request->filled('piket_start_date')) {
                $redirectParams['piket_start_date'] = $request->input('piket_start_date');
            }
            if ($request->filled('piket_end_date')) {
                $redirectParams['piket_end_date'] = $request->input('piket_end_date');
            }
        }

        return redirect()->route('admin.pengaturan.holiday', $redirectParams)
            ->with('success', "Pengaturan dokumen {$tabLabel} berhasil diperbarui!");
    }

    /**
     * Generate jadwal piket otomatis dari jadwal WFO yang aktif.
     */
    public function generatePiket(Request $request, PiketSchedulingService $piketService)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'office_id' => 'nullable',
            'max_piket_per_week' => 'nullable|integer|min:1|max:7',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $officeId = $request->input('office_id');
        $targetOfficeId = ($officeId && $officeId !== 'all') ? (int)$officeId : null;
        $maxPiketPerWeek = $request->filled('max_piket_per_week') ? (int)$request->input('max_piket_per_week') : null;

        $result = $piketService->generatePiketForPeriod($startDate, $endDate, $targetOfficeId, $maxPiketPerWeek);

        if ($result['success']) {
            $officeName = $targetOfficeId ? (Office::find($targetOfficeId)?->name ?? 'Kantor') : 'Semua Kantor';
            $limitNote = $maxPiketPerWeek ? "{$maxPiketPerWeek}x/minggu" : 'sesuai setting';
            \App\Helper\ActivityLogger::log(
                'CREATE',
                'Master Data',
                "Admin mengenerate jadwal piket otomatis periode {$startDate} s/d {$endDate} ({$officeName}, batas {$limitNote}): {$result['total_piket']} penugasan",
                ['result' => $result]
            );
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($result);
        }

        return redirect()->route('admin.pengaturan.holiday', [
            'tab' => 'piket',
            'piket_office_id' => $officeId ?: 'all',
            'piket_start_date' => $startDate,
            'piket_end_date' => $endDate,
        ])->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Reset / Hapus penugasan piket pada periode dan kantor tertentu.
     */
    public function resetPiket(Request $request, PiketSchedulingService $piketService)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'office_id' => 'nullable',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $officeId = $request->input('office_id');
        $targetOfficeId = ($officeId && $officeId !== 'all') ? (int)$officeId : null;

        $deletedCount = $piketService->resetPiketForPeriod($startDate, $endDate, $targetOfficeId);

        \App\Helper\ActivityLogger::log(
            'DELETE',
            'Master Data',
            "Admin mereset {$deletedCount} jadwal piket periode {$startDate} s/d {$endDate}"
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Berhasil mereset {$deletedCount} penugasan piket pada periode tersebut.",
                'deleted_count' => $deletedCount,
            ]);
        }

        return redirect()->route('admin.pengaturan.holiday', [
            'tab' => 'piket',
            'piket_office_id' => $officeId ?: 'all',
            'piket_start_date' => $startDate,
            'piket_end_date' => $endDate,
        ])->with('success', "Berhasil mereset {$deletedCount} penugasan piket.");
    }

    /**
     * API Roster piket untuk update AJAX.
     */
    public function getPiketRoster(Request $request, PiketSchedulingService $piketService)
    {
        $startDate = $request->input('start_date', now()->startOfWeek(Carbon::MONDAY)->toDateString());
        $endDate = $request->input('end_date', now()->endOfWeek(Carbon::SUNDAY)->toDateString());
        $officeId = $request->input('office_id');
        $targetOfficeId = ($officeId && $officeId !== 'all') ? (int)$officeId : null;

        $roster = $piketService->getPiketRoster($startDate, $endDate, $targetOfficeId);

        return response()->json([
            'success' => true,
            'roster' => $roster,
            'total' => $roster->count(),
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ]
        ]);
    }
}
