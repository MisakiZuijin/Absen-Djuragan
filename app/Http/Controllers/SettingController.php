<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Services\InternService;
use App\Services\QuotesService;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use App\Models\PermitSetting; // Gunakan model baru

class SettingController extends Controller
{
    protected $userService;
    protected $internService;
    protected $quoteService;

    public function __construct(UserService $userService, InternService $internService, QuotesService $quoteService)
    {
        $this->userService = $userService;
        $this->internService = $internService;
        $this->quoteService = $quoteService;
    }

    // ========================================================================
    // [DIROMBAK] Method untuk menampilkan halaman Pengaturan Batas Izin
    // ========================================================================
    public function managePermitSettingsView(): View
    {
        $settings = PermitSetting::all()->keyBy('type');

        $permitTypes = [
            'prayer' => 'Izin Sholat/Ibadah',
            'leave'  => 'Izin Keluar (Keperluan Mendesak)',
        ];

        return view('admin.pengaturan-batas-izin', compact('settings', 'permitTypes'));
    }

    // ========================================================================
    // [BARU] Method untuk menyimpan perubahan Pengaturan Batas Izin
    // ========================================================================
    public function updatePermitSettings(Request $request)
    {
        $request->validate([
            'limits' => 'required|array',
            'limits.*' => 'required|integer|min:0',
        ]);

        // Hanya update setting untuk prayer dan leave
        foreach ($request->limits as $type => $count) {
            if (in_array($type, ['prayer', 'leave'])) {
                PermitSetting::updateOrCreate(
                    ['type' => $type],
                    ['max_daily_count' => $count]
                );
            }
        }

        return redirect()->back()->with('success', 'Pengaturan batas izin berhasil diperbarui!');
    }

    // --- Kode lama yang tidak diubah ---

    public function adminSettingView(): View
    {
        $userData = $this->userService->getUserLoggedData();
        $quotesResult = $this->quoteService->getByCategory('quote');
        $quotesultahResult = $this->quoteService->getByCategory('ultah');
        $data = [
            'user' => $userData,
            'quotes' => $quotesResult->isSuccess() ? $quotesResult->getData() : [],
            'quotesultah' => $quotesultahResult->isSuccess() ? $quotesultahResult->getData() : [],
        ];
        return view('admin.pengaturan')->with($data);
    }

    public function profileSettingView(): View
    {
        $userData = $this->userService->getUserLoggedData();
        return view('admin.edit-profile', ['user' => $userData]);
    }

    public function updateProfile(UpdateProfileRequest $request, $id)
    {
        $this->userService->updateProfile($request, $id);
        return redirect()->route('profile.pengaturan.view')->with('success', 'Profil berhasil diperbarui!');
    }

    public function storeQuote(StoreQuoteRequest $request)
    {
        $this->quoteService->store($request);
        return redirect()->route('admin.pengaturan.view')->with('success', 'Quote berhasil ditambahkan!');
    }

    public function storeQuoteUltah(StoreQuoteRequest $request)
    {
        $this->quoteService->store($request);
        return redirect()->route('admin.pengaturan.view')->with('success', 'Quote ulang tahun berhasil ditambahkan!');
    }

    public function deleteQuote($id)
    {
        $this->quoteService->delete($id);
        return redirect()->route('admin.pengaturan.view')->with('success', 'Quote berhasil dihapus!');
    }
}
