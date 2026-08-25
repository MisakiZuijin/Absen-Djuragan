<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use App\Models\User;
use App\Models\Division;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\File;

class SettingBroadcastController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $userData = $this->userService->getUserLoggedData();
        $broadcastlist = Broadcast::with(['divisions', 'users.profile'])
            ->latest()
            ->paginate(10);
        $divisions = Division::all();
        $users = User::with('profile')->where('role_id', 3)->get();
        $data = [
            "broadcastlist" => $broadcastlist,
            "user" => $userData,
            "divisions" => $divisions,
            "users" => $users
        ];
        return view('admin.pengaturan-broadcast')->with($data);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'message' => 'required|string',
            'broadcast_type' => 'required|in:all,division,specific',
            'divisions' => 'required_if:broadcast_type,division|array',
            'divisions.*' => 'exists:divisions,id',
            'users' => 'required_if:broadcast_type,specific|array',
            'users.*' => 'exists:users,id',
        ]);
        
        $imageName = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('broadcast-image'), $imageName);
        }

        $broadcast = Broadcast::create([
            'title' => $validatedData['title'],
            'message' => $validatedData['message'],
            'image' => $imageName,
            'broadcast_type' => $validatedData['broadcast_type'],
        ]);

        if ($validatedData['broadcast_type'] === 'division') {
            $broadcast->divisions()->attach($validatedData['divisions']);
        } elseif ($validatedData['broadcast_type'] === 'specific') {
            $broadcast->users()->attach($validatedData['users']);
        }

        return redirect()->route('admin.pengaturan.broadcast')->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function update(Request $request, Broadcast $broadcast)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'message' => 'required|string',
            'broadcast_type' => 'required|in:all,division,specific',
            'divisions' => 'required_if:broadcast_type,division|array',
            'divisions.*' => 'exists:divisions,id',
            'users' => 'required_if:broadcast_type,specific|array',
            'users.*' => 'exists:users,id',
        ]);
        
        $imageName = $broadcast->image;
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada dan file-nya benar-benar ada
            if ($broadcast->image && File::exists(public_path('broadcast-image/' . $broadcast->image))) {
                File::delete(public_path('broadcast-image/' . $broadcast->image));
            }
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('broadcast-image'), $imageName);
        }

        $broadcast->update([
            'title' => $validatedData['title'],
            'message' => $validatedData['message'],
            'image' => $imageName,
            'broadcast_type' => $validatedData['broadcast_type'],
        ]);
        
        // Logika update relasi yang bersih menggunakan sync() dan detach()
        if ($validatedData['broadcast_type'] === 'division') {
            $broadcast->divisions()->sync($request->input('divisions', []));
            $broadcast->users()->detach(); // Hapus relasi user yang mungkin ada sebelumnya
        } elseif ($validatedData['broadcast_type'] === 'specific') {
            $broadcast->users()->sync($request->input('users', []));
            $broadcast->divisions()->detach(); // Hapus relasi divisi yang mungkin ada sebelumnya
        } else { // Jika tipenya 'all'
            $broadcast->divisions()->detach();
            $broadcast->users()->detach();
        }

        return redirect()->route('admin.pengaturan.broadcast')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Broadcast $broadcast)
    {
        // Hapus file gambar dari storage sebelum menghapus record
        if ($broadcast->image && File::exists(public_path('broadcast-image/' . $broadcast->image))) {
            File::delete(public_path('broadcast-image/' . $broadcast->image));
        }
        
        // Hapus semua relasi di pivot table
        $broadcast->divisions()->detach();
        $broadcast->users()->detach();
        
        // Hapus record broadcast
        $broadcast->delete();

        return redirect()->route('admin.pengaturan.broadcast')->with('success', 'Pengumuman berhasil dihapus.');
    }
}