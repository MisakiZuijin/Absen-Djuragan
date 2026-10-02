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
use App\Models\ChangeTimeSetting;
use App\Models\PopupSetting;
use App\Models\Shift;
use App\Models\Office;
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

        // Pastikan default data ada jika belum ada
        if (!$settings->has('toilet')) {
            PermitSetting::create(['type' => 'toilet', 'max_daily_count' => 0, 'max_duration_minutes' => 10]);
        }
        if (!$settings->has('prayer')) {
            PermitSetting::create(['type' => 'prayer', 'max_daily_count' => 5, 'max_duration_minutes' => 15]);
        }
        if (!$settings->has('leave')) {
            PermitSetting::create(['type' => 'leave', 'max_daily_count' => 1, 'max_duration_minutes' => 0]);
        }

        $settings = PermitSetting::all()->keyBy('type');

        $permitTypes = [
            'prayer' => [
                'label' => 'Izin Sholat / Ibadah',
                'icon' => 'fas fa-mosque',
                'color' => 'emerald',
                'has_daily_count' => true,
                'has_duration_limit' => true,
                'default_count' => 5,
                'default_duration' => 15,
            ],
            'toilet' => [
                'label' => 'Izin Toilet / Kamar Mandi',
                'icon' => 'fas fa-restroom',
                'color' => 'blue',
                'has_daily_count' => false, // Frekuensi tidak dibatasi
                'has_duration_limit' => true,
                'default_count' => 0,
                'default_duration' => 10,
            ],
            'leave'  => [
                'label' => 'Izin Keluar (Keperluan Mendesak)',
                'icon' => 'fa-solid fa-door-open',
                'color' => 'amber',
                'has_daily_count' => true,
                'has_duration_limit' => false, // Tidak dibatasi durasi otomatis
                'default_count' => 1,
                'default_duration' => 0,
            ],
        ];

        return view('admin.pengaturan-batas-izin', compact('settings', 'permitTypes'));
    }

    // ========================================================================
    // [BARU] Method untuk menyimpan perubahan Pengaturan Batas Izin
    // ========================================================================
    public function updatePermitSettings(Request $request)
    {
        $request->validate([
            'limits' => 'nullable|array',
            'limits.*' => 'nullable|integer|min:0',
            'duration_limits' => 'nullable|array',
            'duration_limits.*' => 'nullable|integer|min:0',
        ]);

        $validTypes = ['prayer', 'toilet', 'leave'];

        foreach ($validTypes as $type) {
            $dailyCount = isset($request->limits[$type]) ? (int) $request->limits[$type] : 0;
            $durationMinutes = isset($request->duration_limits[$type]) ? (int) $request->duration_limits[$type] : 0;

            // Khusus toilet: frekuensi tidak dibatasi (0 = unlimited)
            if ($type === 'toilet') {
                $dailyCount = 0;
            }

            // Khusus leave: durasi otomatis tidak dibatasi (0 = manual admin)
            if ($type === 'leave') {
                $durationMinutes = 0;
            }

            PermitSetting::updateOrCreate(
                ['type' => $type],
                [
                    'max_daily_count' => $dailyCount,
                    'max_duration_minutes' => $durationMinutes,
                ]
            );
        }

        \Illuminate\Support\Facades\Cache::forget('permit_settings_daily_limits');
        \Illuminate\Support\Facades\Cache::forget('permit_settings_duration_limits');

        \App\Helper\ActivityLogger::log(
            'UPDATE',
            'Pengaturan',
            'Admin memperbarui konfigurasi batas izin & durasi sholat/toilet'
        );

        return redirect()->back()->with('success', 'Pengaturan batas izin dan durasi berhasil diperbarui!');
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
        \App\Helper\ActivityLogger::log('CREATE', 'Quotes', "Admin menambahkan kutipan motivasi harian: " . \Illuminate\Support\Str::limit($request->input('quote'), 60));
        return redirect()->route('admin.pengaturan.view')->with('success', 'Quote berhasil ditambahkan!');
    }

    public function storeQuoteUltah(StoreQuoteRequest $request)
    {
        $this->quoteService->store($request);
        \App\Helper\ActivityLogger::log('CREATE', 'Quotes', "Admin menambahkan kutipan ulang tahun: " . \Illuminate\Support\Str::limit($request->input('quote'), 60));
        return redirect()->route('admin.pengaturan.view')->with('success', 'Quote ulang tahun berhasil ditambahkan!');
    }

    public function deleteQuote(int $id)
    {
        $this->quoteService->delete($id);
        \App\Helper\ActivityLogger::log('DELETE', 'Quotes', "Admin menghapus kutipan (ID: {$id})");
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

    public function changeTimeSettingsView(): View
    {
        $setting = ChangeTimeSetting::getSettings();
        $shifts = Shift::where('id', '>', 1)->orderBy('start_time')->get();
        $offices = Office::orderBy('name')->get();

        return view('admin.pengaturan-ganti-jam', compact('setting', 'shifts', 'offices'));
    }

    public function updateChangeTimeSettings(Request $request)
    {
        $request->validate([
            'allowed_shift_ids' => 'nullable|array',
            'allowed_shift_ids.*' => 'integer|exists:shifts,id',
            'allowed_office_ids' => 'nullable|array',
            'allowed_office_ids.*' => 'integer|exists:offices,id',
            'default_office_id' => 'nullable|integer|exists:offices,id',
            'intern_notice_text' => 'nullable|string|max:1000',
        ]);

        $setting = ChangeTimeSetting::getSettings();
        $setting->restrict_to_holidays = $request->boolean('restrict_to_holidays');
        $setting->allowed_shift_ids = $request->input('allowed_shift_ids', []);
        $setting->allowed_office_ids = $request->input('allowed_office_ids', []);
        $setting->default_office_id = $request->input('default_office_id') ?: ($setting->allowed_office_ids[0] ?? 1);
        $setting->intern_notice_text = $request->input('intern_notice_text');
        $setting->save();

        \Illuminate\Support\Facades\Cache::forget('change_time_settings_data');

        \App\Helper\ActivityLogger::log(
            'UPDATE',
            'Pengaturan',
            'Admin memperbarui pengaturan Ganti Jam (pilihan shift, kantor, dan pembatasan hari libur)'
        );

        return redirect()->back()->with('success', 'Pengaturan Ganti Jam berhasil diperbarui!');
    }

    public function managePopupSettingsView(): View
    {
        // Pastikan default data ada jika belum ada di database
        $defaults = [
            'attd_status_button' => [
                'label' => 'Status Presensi Pemagang',
                'group' => 'pemagang',
                'interval' => 5,
                'description' => 'Memperbarui status tombol presensi, timer jam kerja, dan popup bantuan di akun pemagang.',
            ],
            'broadcast_popup' => [
                'label' => 'Popup Pengumuman & Chat Personal',
                'group' => 'pemagang',
                'interval' => 5,
                'description' => 'Mengecek dan menampilkan popup broadcast pengumuman serta pertanyaan personal dari admin ke pemagang secara otomatis.',
            ],
            'change_time_info' => [
                'label' => 'Status Sesi Ganti Jam',
                'group' => 'pemagang',
                'interval' => 10,
                'description' => 'Memperbarui status dan timer berjalan pada sesi ganti jam pemagang.',
            ],
            'raise_hand_manager' => [
                'label' => 'Tabel Antrean Bantuan (Raise Hand)',
                'group' => 'admin',
                'interval' => 5,
                'description' => 'Memperbarui daftar antrean permintaan bantuan pemagang di halaman Raise Hand Admin.',
            ],
            'toilet_monitor' => [
                'label' => 'Monitor Izin (Toilet, Shalat, Keluar)',
                'group' => 'admin',
                'interval' => 15,
                'description' => 'Memperbarui daftar pemagang dan timer berjalan saat sedang izin toilet, shalat, atau izin keluar di panel admin.',
            ],
            'raise_hand_notification' => [
                'label' => 'Suara Notifikasi & Pesan Chat',
                'group' => 'admin',
                'interval' => 5,
                'description' => 'Mengecek notifikasi suara lonceng dan kartu pemberitahuan chat/bantuan baru di panel admin.',
            ],
        ];

        foreach ($defaults as $key => $meta) {
            $setting = PopupSetting::where('key', $key)->first();
            if (!$setting) {
                PopupSetting::create([
                    'key' => $key,
                    'label' => $meta['label'],
                    'group' => $meta['group'],
                    'is_enabled' => true,
                    'interval_seconds' => $meta['interval'],
                    'default_interval' => $meta['interval'],
                    'description' => $meta['description'],
                ]);
            } else {
                $setting->update([
                    'label' => $meta['label'],
                    'description' => $meta['description'],
                    'default_interval' => $meta['interval'],
                ]);
            }
        }

        $settings = PopupSetting::orderBy('id')->get()->keyBy('key');

        return view('admin.pengaturan-manage-popup', compact('settings'));
    }

    public function updatePopupSettings(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.interval_seconds' => 'required|integer|min:2|max:300',
        ]);

        $allSettings = PopupSetting::all();
        $submitted = $request->input('settings', []);

        foreach ($allSettings as $setting) {
            if (isset($submitted[$setting->key])) {
                $data = $submitted[$setting->key];
                $setting->interval_seconds = max(2, (int) ($data['interval_seconds'] ?? $setting->default_interval ?? 10));
                $setting->is_enabled = isset($data['is_enabled']) && ($data['is_enabled'] === '1' || $data['is_enabled'] === true || $data['is_enabled'] === 'on');
                $setting->save();
            }
        }

        PopupSetting::clearAllCache();

        \App\Helper\ActivityLogger::log(
            'UPDATE',
            'Pengaturan',
            'Admin memperbarui konfigurasi interval & toggle refresh Livewire (Manage Popup)'
        );

        return redirect()->back()->with('success', 'Pengaturan interval refresh dan popup berhasil disimpan! Perubahan interval akan aktif saat halaman terkait dimuat ulang (refresh).');
    }

    public function resetPopupSettings()
    {
        $settings = PopupSetting::all();
        foreach ($settings as $setting) {
            $setting->interval_seconds = $setting->default_interval ?: 10;
            $setting->is_enabled = true;
            $setting->save();
        }

        PopupSetting::clearAllCache();

        \App\Helper\ActivityLogger::log(
            'RESET',
            'Pengaturan',
            'Admin mereset seluruh interval refresh Livewire (Manage Popup) ke setelan default pabrik'
        );

        return redirect()->back()->with('success', 'Seluruh konfigurasi refresh berhasil direset ke pengaturan default!');
    }
}
