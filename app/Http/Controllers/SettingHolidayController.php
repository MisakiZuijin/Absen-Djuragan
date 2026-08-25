<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Services\HolidayService;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan; // <--- GANTI Cache DENGAN Artisan

class SettingHolidayController extends Controller
{
    protected $userService;
    protected $holidayService;

    public function __construct(UserService $userService, HolidayService $holidayService)
    {
        $this->userService = $userService;
        $this->holidayService = $holidayService;
    }

    // method adminSettingHolidayView() biarkan seperti semula
    public function adminSettingHolidayView(): View
    {
        $userData = $this->userService->getUserLoggedData();
        $holidaylist = Holiday::all()->map(function ($holiday) {
            $holiday->date = Carbon::parse($holiday->date)->format('d-m-Y');
            return $holiday;
        });

        $data = [
            "holidaylist" => $holidaylist,
            "user" => $userData
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

    public function updateHoliday(Request $request, $id)
    {
        $this->holidayService->update($request, $id);
        $this->clearAllCaches(); // Panggil fungsi pembersihan cache
        return redirect()->back()->with('success', 'Data Hari libur berhasil diperbarui!');
    }

    public function deleteHoliday($id)
    {
        $this->holidayService->delete($id);
        $this->clearAllCaches(); // Panggil fungsi pembersihan cache
        return redirect()->back()->with('success', 'Data Hari libur berhasil dihapus!');
    }
}