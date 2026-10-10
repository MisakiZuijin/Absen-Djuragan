<?php

namespace App\Http\Controllers;

use App\Helper\ActivityLogger;
use App\Models\Brand;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SettingBrandController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Tampilkan halaman daftar Brand.
     */
    public function adminSettingBrandView(): View
    {
        $userData = $this->userService->getUserLoggedData();
        $brands = Brand::withCount('interns')->orderBy('name', 'asc')->get();

        return view('admin.pengaturan-brand', [
            'user' => $userData,
            'brands' => $brands,
        ]);
    }

    /**
     * Simpan Brand baru.
     */
    public function storeBrand(Request $request): RedirectResponse
    {
        $messages = [
            'slug.unique' => 'Nama brand ini menghasilkan identifier/slug yang sudah digunakan. Harap gunakan nama brand yang berbeda.',
            'logo.image' => 'File logo harus berupa gambar/foto.',
            'logo.mimes' => 'Format foto logo harus berupa JPG, JPEG, PNG, atau WEBP.',
            'logo.max' => 'Ukuran file logo maksimal adalah 2MB.',
        ];

        // Slug selalu di-generate otomatis langsung dari nama brand
        $generatedSlug = Str::slug($request->input('name', '')) ?: ('brand-' . time());
        $request->merge(['slug' => $generatedSlug]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ], $messages);

        $logoName = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $logoName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/brands'), $logoName);
        }

        $brand = Brand::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'logo' => $logoName,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
        ]);

        ActivityLogger::log('CREATE', 'Master Data', "Admin menambahkan Brand baru: {$brand->name}");

        return redirect()->route('admin.pengaturan.brand')->with('success', 'Brand baru berhasil ditambahkan!');
    }

    /**
     * Perbarui data Brand.
     */
    public function updateBrand(Request $request, int $id): RedirectResponse
    {
        $brand = Brand::findOrFail($id);

        $messages = [
            'slug.unique' => 'Nama brand ini menghasilkan identifier/slug yang sudah digunakan oleh brand lain. Harap gunakan nama yang berbeda.',
            'logo.image' => 'File logo harus berupa gambar/foto.',
            'logo.mimes' => 'Format foto logo harus berupa JPG, JPEG, PNG, atau WEBP.',
            'logo.max' => 'Ukuran file logo maksimal adalah 2MB.',
        ];

        // Slug selalu di-generate otomatis langsung dari nama brand
        $generatedSlug = Str::slug($request->input('name', '')) ?: ('brand-' . time());
        $request->merge(['slug' => $generatedSlug]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug,' . $id,
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ], $messages);

        $data = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : $brand->is_active,
        ];

        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($brand->logo && File::exists(public_path('img/brands/' . $brand->logo))) {
                File::delete(public_path('img/brands/' . $brand->logo));
            }

            $file = $request->file('logo');
            $logoName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/brands'), $logoName);
            $data['logo'] = $logoName;
        }

        $brand->update($data);

        ActivityLogger::log('UPDATE', 'Master Data', "Admin memperbarui data Brand: {$brand->name}", ['brand_id' => $id]);

        return redirect()->route('admin.pengaturan.brand')->with('success', 'Data Brand berhasil diperbarui!');
    }

    /**
     * Hapus data Brand.
     */
    public function deleteBrand(int $id): RedirectResponse
    {
        $brand = Brand::findOrFail($id);
        $brandName = $brand->name;

        if ($brand->logo && File::exists(public_path('img/brands/' . $brand->logo))) {
            File::delete(public_path('img/brands/' . $brand->logo));
        }

        $brand->delete();

        ActivityLogger::log('DELETE', 'Master Data', "Admin menghapus Brand: {$brandName}", ['brand_id' => $id]);

        return redirect()->route('admin.pengaturan.brand')->with('success', 'Brand berhasil dihapus!');
    }

    /**
     * Toggle status aktif/nonaktif Brand.
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        $brand = Brand::findOrFail($id);
        $brand->is_active = !$brand->is_active;
        $brand->save();

        $statusText = $brand->is_active ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLogger::log('UPDATE', 'Master Data', "Admin mengubah status Brand {$brand->name} menjadi {$statusText}", ['brand_id' => $id]);

        return redirect()->route('admin.pengaturan.brand')->with('success', "Brand {$brand->name} berhasil {$statusText}!");
    }
}
