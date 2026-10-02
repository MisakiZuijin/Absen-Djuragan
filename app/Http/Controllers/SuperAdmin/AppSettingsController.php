<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helper\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AppSettingsController extends Controller
{
    /**
     * Tampilkan halaman pengaturan tampilan aplikasi & website.
     */
    public function index(): View
    {
        $appSetting = AppSetting::getSettings();

        return view('super_admin.app_settings.index', [
            'appSetting' => $appSetting,
        ]);
    }

    /**
     * Simpan pembaruan aset tampilan dan informasi aplikasi.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'app_name' => 'nullable|string|max:100',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,svg,webp,ico|max:4096',
            'favicon' => 'nullable|file|mimes:ico,png,jpg,jpeg,svg|max:2048',
            'intern_banner' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:6144',
            'banner_slides' => 'nullable|array',
            'banner_slides.*' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:6144',
            'login_background' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:6144',
        ], [
            'logo.mimes' => 'Format logo harus berupa JPG, PNG, SVG, WEBP, atau ICO.',
            'logo.max' => 'Ukuran file logo maksimal 4 MB.',
            'favicon.mimes' => 'Format favicon harus berupa ICO, PNG, JPG, atau SVG.',
            'favicon.max' => 'Ukuran file favicon maksimal 2 MB.',
            'intern_banner.mimes' => 'Format banner pemagang harus berupa JPG, PNG, atau WEBP.',
            'intern_banner.max' => 'Ukuran file banner pemagang maksimal 6 MB.',
            'banner_slides.*.mimes' => 'Format file slide banner harus berupa JPG, PNG, atau WEBP.',
            'banner_slides.*.max' => 'Ukuran setiap file slide banner maksimal 6 MB.',
            'login_background.mimes' => 'Format background login harus berupa JPG, PNG, atau WEBP.',
            'login_background.max' => 'Ukuran file background login maksimal 6 MB.',
        ]);

        $setting = AppSetting::first() ?? new AppSetting();
        $uploadDir = public_path('uploads/app-settings');

        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        $uploadedItems = [];

        // 1. Upload Logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            if ($file->isValid()) {
                if ($setting->logo && File::exists($uploadDir . '/' . $setting->logo)) {
                    File::delete($uploadDir . '/' . $setting->logo);
                }
                $ext = $file->getClientOriginalExtension();
                $logoName = 'logo_' . time() . '_' . Str::random(6) . '.' . $ext;
                $file->move($uploadDir, $logoName);
                $setting->logo = $logoName;
                $uploadedItems[] = 'Logo';
            }
        }

        // 2. Upload Favicon
        if ($request->hasFile('favicon')) {
            $file = $request->file('favicon');
            if ($file->isValid()) {
                if ($setting->favicon && File::exists($uploadDir . '/' . $setting->favicon)) {
                    File::delete($uploadDir . '/' . $setting->favicon);
                }
                $ext = $file->getClientOriginalExtension();
                $faviconName = 'favicon_' . time() . '_' . Str::random(6) . '.' . $ext;
                $file->move($uploadDir, $faviconName);
                $setting->favicon = $faviconName;
                $uploadedItems[] = 'Favicon';
            }
        }

        // 3. Upload Banner Pemagang Utama
        if ($request->hasFile('intern_banner')) {
            $file = $request->file('intern_banner');
            if ($file->isValid()) {
                if ($setting->intern_banner && File::exists($uploadDir . '/' . $setting->intern_banner)) {
                    File::delete($uploadDir . '/' . $setting->intern_banner);
                }
                $ext = $file->getClientOriginalExtension();
                $bannerName = 'banner_' . time() . '_' . Str::random(6) . '.' . $ext;
                $file->move($uploadDir, $bannerName);
                $setting->intern_banner = $bannerName;
                $uploadedItems[] = 'Banner Pemagang';
            }
        }

        // 3.1. Upload Slide Banner Tambahan (Carousel)
        if ($request->hasFile('banner_slides')) {
            $currentSlides = is_array($setting->banner_slides) ? $setting->banner_slides : [];
            $newSlidesUploaded = 0;

            foreach ($request->file('banner_slides') as $slideFile) {
                if ($slideFile && $slideFile->isValid()) {
                    $ext = $slideFile->getClientOriginalExtension();
                    $slideName = 'slide_' . time() . '_' . Str::random(8) . '.' . $ext;
                    $slideFile->move($uploadDir, $slideName);
                    $currentSlides[] = $slideName;
                    $newSlidesUploaded++;
                }
            }

            if ($newSlidesUploaded > 0) {
                $setting->banner_slides = array_values($currentSlides);
                $uploadedItems[] = "{$newSlidesUploaded} Slide Banner Baru";
            }
        }

        // 4. Upload Background Login
        if ($request->hasFile('login_background')) {
            $file = $request->file('login_background');
            if ($file->isValid()) {
                if ($setting->login_background && File::exists($uploadDir . '/' . $setting->login_background)) {
                    File::delete($uploadDir . '/' . $setting->login_background);
                }
                $ext = $file->getClientOriginalExtension();
                $loginBgName = 'login_bg_' . time() . '_' . Str::random(6) . '.' . $ext;
                $file->move($uploadDir, $loginBgName);
                $setting->login_background = $loginBgName;
                $uploadedItems[] = 'Background Login';
            }
        }

        // 5. Update App Name
        if ($request->filled('app_name')) {
            $setting->app_name = trim((string) $request->input('app_name'));
        }

        $setting->save();
        AppSetting::clearSettingsCache();

        $logDesc = count($uploadedItems) > 0
            ? 'Super Admin memperbarui pengaturan aplikasi: ' . implode(', ', $uploadedItems) . '.'
            : 'Super Admin memperbarui nama aplikasi.';

        ActivityLogger::log('UPDATE', 'App Settings', $logDesc, [
            'uploaded' => $uploadedItems,
            'app_name' => $setting->app_name,
        ]);

        return redirect()->route('super-admin.app-settings.index')
            ->with('success', 'Pengaturan tampilan aplikasi dan website berhasil disimpan!');
    }

    /**
     * Hapus satu slide banner tertentu dari daftar slide carousel.
     */
    public function deleteBannerSlide(int $index): RedirectResponse
    {
        $setting = AppSetting::first();
        if ($setting && !empty($setting->banner_slides) && is_array($setting->banner_slides)) {
            $slides = $setting->banner_slides;
            if (isset($slides[$index])) {
                $slideFile = $slides[$index];
                $filePath = public_path('uploads/app-settings/' . $slideFile);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
                array_splice($slides, $index, 1);
                $setting->banner_slides = empty($slides) ? null : array_values($slides);
                $setting->save();
                AppSetting::clearSettingsCache();

                ActivityLogger::log('DELETE', 'App Settings', "Super Admin menghapus 1 slide banner pemagang (index: {$index}).");

                return redirect()->route('super-admin.app-settings.index')
                    ->with('success', 'Slide banner carousel berhasil dihapus!');
            }
        }

        return redirect()->route('super-admin.app-settings.index')
            ->with('error', 'Slide banner tidak ditemukan.');
    }

    /**
     * Reset aset gambar tertentu ke pengaturan bawaan (default).
     */
    public function resetImage(string $type): RedirectResponse
    {
        $allowedTypes = [
            'logo' => 'Logo Aplikasi',
            'favicon' => 'Favicon Tab Browser',
            'intern_banner' => 'Banner Header Pemagang',
            'login_background' => 'Background Login',
        ];

        if (!array_key_exists($type, $allowedTypes)) {
            return redirect()->route('super-admin.app-settings.index')
                ->with('error', 'Jenis aset tidak valid.');
        }

        $setting = AppSetting::first();
        if ($setting && $setting->$type) {
            $filePath = public_path('uploads/app-settings/' . $setting->$type);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
            $setting->$type = null;
            $setting->save();
        }

        AppSetting::clearSettingsCache();

        ActivityLogger::log('DELETE', 'App Settings', "Super Admin mereset {$allowedTypes[$type]} ke tampilan default.");

        return redirect()->route('super-admin.app-settings.index')
            ->with('success', "{$allowedTypes[$type]} berhasil direset ke tampilan default bawaan sistem!");
    }
}
