<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Services\HolidayService;
use App\Models\Holiday;
use App\Models\Office;
use Carbon\Carbon;
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
        $holidaylist = Holiday::all()->map(function ($holiday) {
            $holiday->date = Carbon::parse($holiday->date)->format('d-m-Y');
            return $holiday;
        });
        $offices = Office::all();

        $defaultOfficeId = (string)($offices->first()?->id ?? '1');
        $legacyOfficeId = $request->get('office_id');
        $activeTab = $request->get('tab', 'holiday');

        // SOP Magang: default global ('all') atau spesifik jika dipilih
        $sopOfficeId = $request->get('sop_office_id', ($activeTab === 'sop' && $legacyOfficeId) ? $legacyOfficeId : 'all');

        // Peraturan Kantor: default spesifik per-kantor pertama agar tidak menimpa semua kantor
        $rulesOfficeId = $request->get('rules_office_id', ($activeTab === 'rules' && $legacyOfficeId) ? $legacyOfficeId : $defaultOfficeId);

        // Jadwal Piket: default spesifik per-kantor pertama agar tidak menimpa semua kantor
        $piketOfficeId = $request->get('piket_office_id', ($activeTab === 'piket' && $legacyOfficeId) ? $legacyOfficeId : $defaultOfficeId);

        $data = [
            "holidaylist" => $holidaylist,
            "offices" => $offices,
            "user" => $userData,
            "activeTab" => $activeTab,
            "sopOfficeId" => $sopOfficeId,
            "rulesOfficeId" => $rulesOfficeId,
            "piketOfficeId" => $piketOfficeId,
        ];

        return view('admin.pengaturan-holiday')->with($data);
    }

    /**
     * Fungsi helper untuk membersihkan semua cache secara paksa.
     */
    private function clearAllCaches()
    {
        // Perintah ini akan menjalankan 'php artisan optimize:clear' dari dalam kode.
        // Ini jauh lebih kuat daripada Cache::flush() karena membersihkan
        // cache aplikasi, view, config, dan route.
        Artisan::call('optimize:clear');
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
        ]);

        $isCheckboxChecked = $request->boolean('apply_all');
        $officeId = $request->input('office_id');

        if ($officeId === 'all') {
            if ($isCheckboxChecked) {
                $targetMode = 'all';
            } else {
                $targetMode = 'specific';
                $officeId = Office::first()?->id;
            }
        } else {
            $targetMode = $isCheckboxChecked ? 'all' : 'specific';
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
        }

        return redirect()->route('admin.pengaturan.holiday', $redirectParams)
            ->with('success', "Pengaturan dokumen {$tabLabel} berhasil diperbarui!");
    }
}
