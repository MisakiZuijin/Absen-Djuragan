<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helper\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminManagementController extends Controller
{
    /**
     * Tampilkan daftar seluruh akun Admin (role_id = 1).
     */
    public function index(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user instanceof User && !$user->relationLoaded('profile')) {
            $user->loadMissing('profile');
        }

        $query = User::with('profile')
            ->where('role_id', 1);

        // Filter pencarian (nama, username, email)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($pq) use ($search) {
                        $pq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $admins = $query->latest('id')->paginate(15)->appends($request->all());

        // Statistik ringkas dalam 1 query agregasi tunggal (mengeliminasi duplikasi query & 3 query terpisah)
        $stats = DB::table('users')
            ->whereIn('role_id', [1, 7])
            ->selectRaw("
                SUM(CASE WHEN role_id = 1 THEN 1 ELSE 0 END) as total_admins,
                SUM(CASE WHEN role_id = 1 AND is_active = 1 THEN 1 ELSE 0 END) as active_admins,
                SUM(CASE WHEN role_id = 7 THEN 1 ELSE 0 END) as total_super_admins
            ")
            ->first();

        $totalAdmins = (int) ($stats->total_admins ?? 0);
        $activeAdmins = (int) ($stats->active_admins ?? 0);
        $inactiveAdmins = $totalAdmins - $activeAdmins;
        $totalSuperAdmins = (int) ($stats->total_super_admins ?? 0);

        return view('super_admin.admins.index', compact(
            'admins',
            'totalAdmins',
            'activeAdmins',
            'inactiveAdmins',
            'totalSuperAdmins'
        ));
    }

    /**
     * Simpan akun Admin baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|alpha_dash|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:30',
            'gender' => 'nullable|in:L,P',
            'is_active' => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role_id' => 1, // Admin Role
                'is_active' => $request->has('is_active') ? true : false,
                'is_confirm' => true,
                'is_reset_token' => false,
                'os' => '-',
                'browser' => '-',
                'device' => '-',
                'is_gps_support' => false,
                'is_gps_activate' => false,
            ]);

            $user->profile()->create([
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'] ?? null,
                'gender' => $validated['gender'] ?? 'L',
                'date_of_birth' => '1990-01-01',
                'birth_place' => '-',
            ]);

            ActivityLogger::log(
                'CREATE',
                'User Management',
                "Super Admin membuat akun Admin baru: {$validated['full_name']} (@{$validated['username']})",
                ['admin_id' => $user->id, 'email' => $validated['email']]
            );

            DB::commit();

            return redirect()->route('super-admin.admins.index')
                ->with('success', "Akun Admin '{$validated['full_name']}' berhasil dibuat.");
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat akun Admin: ' . $th->getMessage());
        }
    }

    /**
     * Update akun Admin.
     */
    public function update(Request $request, User $user)
    {
        if ((int) $user->role_id !== 1) {
            return redirect()->back()->with('error', 'User yang dipilih bukan merupakan Admin.');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|alpha_dash|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:30',
            'gender' => 'nullable|in:L,P',
            'is_active' => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            $user->username = $validated['username'];
            $user->email = $validated['email'];
            $user->is_active = $request->has('is_active') ? true : false;

            if (!empty($validated['password'])) {
                $user->password = $validated['password'];
            }
            $user->save();

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => $validated['full_name'],
                    'phone' => $validated['phone'] ?? null,
                    'gender' => $validated['gender'] ?? 'L',
                ]
            );

            ActivityLogger::log(
                'UPDATE',
                'User Management',
                "Super Admin memperbarui data akun Admin: {$validated['full_name']} (@{$user->username})",
                ['admin_id' => $user->id]
            );

            DB::commit();

            return redirect()->route('super-admin.admins.index')
                ->with('success', "Akun Admin '{$validated['full_name']}' berhasil diperbarui.");
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui akun Admin: ' . $th->getMessage());
        }
    }

    /**
     * Toggle status aktif/nonaktif akun Admin.
     */
    public function toggleStatus(User $user)
    {
        if ((int) $user->role_id !== 1) {
            return redirect()->back()->with('error', 'User yang dipilih bukan merupakan Admin.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $name = $user->profile?->full_name ?? $user->username;

        ActivityLogger::log(
            'UPDATE',
            'User Management',
            "Super Admin {$statusText} akun Admin {$name} (@{$user->username})",
            ['admin_id' => $user->id, 'is_active' => $user->is_active]
        );

        return redirect()->back()
            ->with('success', "Akun Admin '{$name}' berhasil {$statusText}.");
    }

    /**
     * Hapus akun Admin.
     */
    public function destroy(User $user)
    {
        if ((int) $user->role_id !== 1) {
            return redirect()->back()->with('error', 'User yang dipilih bukan merupakan Admin.');
        }

        if (Auth::id() === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->profile?->full_name ?? $user->username;

        ActivityLogger::log(
            'DELETE',
            'User Management',
            "Super Admin menghapus akun Admin {$name} (@{$user->username})",
            ['deleted_user_id' => $user->id, 'username' => $user->username]
        );

        $user->profile()?->delete();
        $user->delete();

        return redirect()->route('super-admin.admins.index')
            ->with('success', "Akun Admin '{$name}' berhasil dihapus.");
    }
}
