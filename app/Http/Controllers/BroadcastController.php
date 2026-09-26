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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BroadcastController extends Controller
{
    /**
     * Menampilkan halaman utama untuk mengelola pengumuman.
     */
    public function index()
    {
        $broadcastlist = Broadcast::announcements()
            ->with([
                'divisions:id,name',
                'users:id,username',
                'users.profile:id,user_id,full_name',
                'shifts:id,name',
                'images:id,broadcast_id,image'
            ])
            ->latest()
            ->paginate(10);
        $divisions = Division::select('id', 'name')->orderBy('name')->get();
        $users = User::select('id', 'username')->whereHas('intern')->with('profile:id,user_id,full_name')->get();

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
                'category' => 'announcement',
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

            \App\Helper\ActivityLogger::log('CREATE', 'Broadcast', "Admin membuat pengumuman baru: {$request->title}");

            return redirect()->to('/admin/broadcasts')->with('success', 'Pengumuman baru berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Broadcast store error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan pengumuman. Silakan coba lagi.')->withInput();
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

            \App\Helper\ActivityLogger::log('UPDATE', 'Broadcast', "Admin memperbarui pengumuman: {$broadcast->title}");

            return redirect()->to('/admin/broadcasts')->with('success', 'Pengumuman berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Broadcast update error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui pengumuman. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menghapus pengumuman dari database.
     */
    public function destroy(Broadcast $broadcast)
    {
        DB::beginTransaction();
        try {
            $broadcast->loadMissing('images');
            foreach ($broadcast->images as $image) {
                $imagePath = public_path('broadcast-image/' . $image->image);
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                }
            }
            
            $title = $broadcast->title;
            $broadcast->delete();

            DB::commit();

            \App\Helper\ActivityLogger::log('DELETE', 'Broadcast', "Admin menghapus pengumuman: {$title}");

            return redirect()->to('/admin/broadcasts')->with('success', 'Pengumuman berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus pengumuman.');
        }
    }
}