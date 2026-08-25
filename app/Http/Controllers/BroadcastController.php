<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Broadcast;
use App\Models\BroadcastImage;
use App\Models\Division;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BroadcastController extends Controller
{
    /**
     * Menampilkan halaman utama untuk mengelola pengumuman.
     */
    public function index()
    {
        $broadcastlist = Broadcast::with('divisions', 'users', 'images')->latest()->paginate(10);
        $divisions = Division::orderBy('name')->get();
        $users = User::whereHas('intern')->with('profile')->get();

       return view('admin.pengaturan-broadcast', compact('broadcastlist', 'divisions', 'users'));
    }

    /**
     * Menyimpan pengumuman baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'broadcast_type' => 'required|in:all,division,specific',
            'divisions' => 'nullable|required_if:broadcast_type,division|array',
            'users' => 'nullable|required_if:broadcast_type,specific|array',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $broadcast = Broadcast::create([
                'title' => $request->title,
                'message' => $request->message,
                'broadcast_type' => $request->broadcast_type,
            ]);

            if ($request->broadcast_type === 'division') {
                $broadcast->divisions()->sync($request->divisions ?? []);
            } elseif ($request->broadcast_type === 'specific') {
                $broadcast->users()->sync($request->users ?? []);
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('broadcast-image'), $filename);
                    $broadcast->images()->create(['image' => $filename]);
                }
            }

            DB::commit();

            // [PERBAIKAN] Menggunakan URL absolut untuk redirect
            return redirect()->to('/admin/broadcasts')->with('success', 'Pengumuman baru berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Memperbarui data pengumuman yang sudah ada.
     */
    public function update(Request $request, Broadcast $broadcast)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'broadcast_type' => 'required|in:all,division,specific',
            'divisions' => 'nullable|required_if:broadcast_type,division|array',
            'users' => 'nullable|required_if:broadcast_type,specific|array',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'deleted_images' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $broadcast->update($request->only('title', 'message', 'broadcast_type'));

            if ($request->broadcast_type === 'division') {
                $broadcast->divisions()->sync($request->input('divisions', []));
                $broadcast->users()->sync([]);
            } elseif ($request->broadcast_type === 'specific') {
                $broadcast->users()->sync($request->input('users', []));
                $broadcast->divisions()->sync([]);
            } else {
                $broadcast->divisions()->sync([]);
                $broadcast->users()->sync([]);
            }

            if ($request->filled('deleted_images')) {
                $deletedImageIds = explode(',', $request->deleted_images);
                $imagesToDelete = $broadcast->images()->whereIn('id', $deletedImageIds)->get();
                
                foreach ($imagesToDelete as $image) {
                    $imagePath = public_path('broadcast-image/' . $image->image);
                    if (File::exists($imagePath)) {
                        File::delete($imagePath);
                    }
                    $image->delete();
                }
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('broadcast-image'), $filename);
                    $broadcast->images()->create(['image' => $filename]);
                }
            }

            DB::commit();

            // [PERBAIKAN] Menggunakan URL absolut untuk redirect
            return redirect()->to('/admin/broadcasts')->with('success', 'Pengumuman berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menghapus pengumuman dari database.
     */
    public function destroy(Broadcast $broadcast)
    {
        DB::beginTransaction();
        try {
            foreach ($broadcast->images as $image) {
                $imagePath = public_path('broadcast-image/' . $image->image);
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                }
            }
            
            $broadcast->delete();

            DB::commit();

            // [PERBAIKAN] Menggunakan URL absolut untuk redirect
            return redirect()->to('/admin/broadcasts')->with('success', 'Pengumuman berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus pengumuman.');
        }
    }
}