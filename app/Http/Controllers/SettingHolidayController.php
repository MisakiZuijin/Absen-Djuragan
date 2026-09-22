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

        $data = [
            "holidaylist" => $holidaylist,
            "offices" => $offices,
            "user" => $userData,
            "activeTab" => $request->get('tab', 'holiday'),
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
        $this->clearAllCaches(); // Panggil fungsi pembersihan cache
        return redirect()->back()->with('success', 'Data Hari libur berhasil ditambahkan!');
    }

    public function updateHoliday(Request $request, int $id)
    {
        $this->holidayService->update($request, $id);
        $this->clearAllCaches(); // Panggil fungsi pembersihan cache
        return redirect()->back()->with('success', 'Data Hari libur berhasil diperbarui!');
    }

    public function deleteHoliday(int $id)
    {
        $this->holidayService->delete($id);
        $this->clearAllCaches(); // Panggil fungsi pembersihan cache
        return redirect()->back()->with('success', 'Data Hari libur berhasil dihapus!');
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

        $applyAll = $request->boolean('apply_all');
        $officeId = $request->input('office_id');

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
            if ($applyAll || $officeId === 'all') {
                Office::query()->update($dataToUpdate);
            } else {
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
        return redirect()->route('admin.pengaturan.holiday', ['tab' => $activeTab])
            ->with('success', "Pengaturan dokumen {$tabLabel} berhasil diperbarui!");
    }
}
