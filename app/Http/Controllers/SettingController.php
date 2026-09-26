<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Services\InternService;
use App\Services\QuotesService;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use App\Models\PermitSetting;
use App\Models\CheckinMessage;
use Illuminate\Support\Facades\File;

class SettingController extends Controller
{
    protected UserService $userService;
    protected InternService $internService;
    protected QuotesService $quoteService;

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

    public function updateProfile(UpdateProfileRequest $request, int $id)
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

    public function deleteQuote(int $id)
    {
        $this->quoteService->delete($id);
        return redirect()->route('admin.pengaturan.view')->with('success', 'Quote berhasil dihapus!');
    }

    public function checkinMessageSettingsView(): View
    {
        $messages = CheckinMessage::all()->keyBy('type');
        return view('admin.pengaturan-checkin-message', compact('messages'));
    }

    public function updateCheckinMessages(Request $request)
    {
        $request->validate([
            'on_time_message' => 'required|string|max:500',
            'late_message' => 'required|string|max:500',
            'on_time_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'late_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // [FIX] Pastikan folder upload ada — move() gagal (500) jika folder belum ada
        $uploadDir = public_path('checkin-images');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach (['on_time', 'late'] as $type) {
            $msg = CheckinMessage::firstOrCreate(['type' => $type]);
            $msg->message = $request->input("{$type}_message", $msg->message);
            $msg->is_active = $request->boolean("{$type}_is_active");

            // [PERBAIKAN] Logika hapus gambar yang akurat (boolean atau '1')
            $shouldRemove = $request->boolean("remove_{$type}_image") || $request->input("remove_{$type}_image") === '1';
            if ($shouldRemove) {
                if ($msg->image) {
                    $oldPath = $uploadDir . '/' . $msg->image;
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                    $msg->image = null;
                }
            }

            // [PERBAIKAN] Upload gambar baru dan hapus gambar lama jika ada
            if ($request->hasFile("{$type}_image")) {
                if ($msg->image) {
                    $oldPath = $uploadDir . '/' . $msg->image;
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }
                $file = $request->file("{$type}_image");
                $filename = $type . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $msg->image = $filename;
            }

            $msg->save();
        }

        return redirect()->back()->with('success', 'Pengaturan popup check-in berhasil diperbarui!');
    }
}
