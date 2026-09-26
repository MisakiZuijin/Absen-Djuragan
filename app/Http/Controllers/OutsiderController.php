<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Intern;
use App\Models\School;
use App\Models\Outsider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class OutsiderController extends Controller
{
    /**
     * Menampilkan daftar semua user yang merupakan 'Outsider'
     */
    public function index(Request $request)
    {
        $query = User::with(['outsider', 'profile', 'outsider.interns.user.profile', 'outsider.interns.school'])
            ->whereHas('outsider');

        // Filter by search (nama/email/username)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('profile', function($q2) use ($search) {
                    $q2->where('full_name', 'like', "%$search%");
                })
                ->orWhere('email', 'like', "%$search%")
                ->orWhere('username', 'like', "%$search%") ;
            });
        }
        // Filter by type
        if ($request->filled('type')) {
            $type = $request->input('type');
            $query->whereHas('outsider', function($q) use ($type) {
                $q->where('type', $type);
            });
        }
        // Filter by status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        // Filter by school
        if ($request->filled('school_filter')) {
            $schoolId = $request->input('school_filter');
            $query->whereHas('outsider.interns', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }

        $outsiders = $query->latest()->paginate(10)->appends($request->all());
        if ($request->ajax()) {
            return view('admin.outsiders._table', [
                'outsiders' => $outsiders
            ])->render();
        }
        return view('admin.Outsiders.index', [
            'outsiders' => $outsiders,
            'filter_search' => $request->input('search', ''),
            'filter_type' => $request->input('type', ''),
            'filter_status' => $request->input('status', ''),
        ]);
    }

    /**
     * Menampilkan form untuk membuat Outsider baru
     */
    public function create()
    {
        $interns = Intern::with(['user.profile', 'school'])->get();
        $schools = School::whereHas('interns')->get();
        return view('admin.outsiders.create', compact('interns', 'schools'));
    }

    /**
     * Menyimpan data dari form ke database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'type' => 'required|in:guru,ortu',
            'phone_number' => 'nullable|string',
            'school_id' => 'required_if:type,guru|nullable|exists:schools,id',
            'intern_id' => 'required_if:type,ortu|nullable|exists:interns,id',
            'notif_enabled' => 'nullable',
            'is_active' => 'nullable',
            'is_confirm' => 'nullable',
            'is_reset_token' => 'nullable',
            'can_view_logs' => 'nullable|boolean',
            
        ]);

        DB::beginTransaction();
        try {
            // Buat user baru
            $user = User::create([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role_id' => 5,
                'is_active' => $request->has('is_active') ? true : false,
                'is_confirm' => $request->has('is_confirm') ? true : false,
                'is_reset_token' => $request->has('is_reset_token') ? true : false,
                'os' => '-',
                'browser' => '-',
                'device' => '-',
                'is_gps_support' => false,
                'is_gps_activate' => false,
            ]);

            $user->profile()->create([
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone_number'],
                'date_of_birth' => '1970-01-01',
                'birth_place' => '-',
            ]);

            $outsider = Outsider::create([
                'user_id' => $user->id,
                'type' => $validated['type'],
                'phone_number' => $validated['phone_number'],
                'notif_enabled' => $request->has('notif_enabled') ? true : false,
                'can_view_logs' => $request->has('can_view_logs'),
            ]);

            if ($validated['type'] === 'guru' && !empty($validated['school_id'])) {
                $internIds = Intern::where('school_id', $validated['school_id'])->pluck('id');
                $outsider->interns()->sync($internIds);
            }

            if ($validated['type'] === 'ortu' && !empty($validated['intern_id'])) {
                $outsider->interns()->sync([$validated['intern_id']]);

                // Handle WhatsApp notification for ortu
                if ($request->has('notif_enabled') && !empty($validated['phone_number'])) {
                    \App\Models\WhatsappNumber::updateOrCreate(
                        [
                            'intern_id' => $validated['intern_id'],
                            'phone_number' => $validated['phone_number']
                        ],
                        [
                            'is_notification_active' => true
                        ]
                    );
                }
            }

            DB::commit();

            $adminName = auth()->user()?->name ?? 'Admin';
            \App\Helper\ActivityLogger::log('CREATE', 'User Management', "Admin {$adminName} membuat akun Mentor/Outsider: {$validated['full_name']} ({$validated['type']})", ['user_id' => $user->id]);

            return redirect()->route('admin.outsiders.index')->with('success', 'Outsider berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create Outsider error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal menambahkan outsider. Silakan periksa kembali data Anda.')->withInput();
        }
    }

    /**
     * Menampilkan form untuk mengedit data
     */
    public function edit(User $user)
    {
        $user->load(['profile', 'outsider.interns']);
        $schools = School::whereHas('interns')->get();
        $interns = Intern::with(['user.profile', 'school'])->get();

        return view('admin.outsiders.edit', compact('user', 'schools', 'interns'));
    }

    /**
     * Mengupdate data yang ada di database
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'type' => 'required|in:guru,ortu',
            'phone_number' => 'nullable|string',
            'school_id' => 'required_if:type,guru|exists:schools,id|nullable',
            'intern_id' => 'required_if:type,ortu|exists:interns,id|nullable',
            'notif_enabled' => 'nullable',
            'is_active' => 'nullable',
            'is_confirm' => 'nullable',
            'is_reset_token' => 'nullable',
            'can_view_logs' => 'nullable|boolean',
            
        ]);

        DB::beginTransaction();
        try {
            // Update user data
            $userData = [
                'email' => $validated['email'],
                'is_active' => $request->has('is_active') ? true : false,
                'is_confirm' => $request->has('is_confirm') ? true : false,
                'is_reset_token' => $request->has('is_reset_token') ? true : false,
            ];

            if ($request->filled('password')) {
                $userData['password'] = $validated['password'];
            }

            $user->update($userData);

            // Update profile
            $user->profile()->update([
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone_number'] ?? null,
            ]);

            // Update outsider data
            $outsiderData = [
                'type' => $validated['type'],
                'phone_number' => $validated['phone_number'] ?? null,
                'notif_enabled' => $request->has('notif_enabled') ? true : false,
                'can_view_logs' => $request->has('can_view_logs'),
            ];
            $user->outsider()->update($outsiderData);

            // Handle interns relationship based on type
            if ($validated['type'] === 'guru' && !empty($validated['school_id'])) {
                $interns = \App\Models\Intern::where('school_id', $validated['school_id'])->pluck('id');
                $user->outsider->interns()->sync($interns);
            } elseif ($validated['type'] === 'ortu' && !empty($validated['intern_id'])) {
                $user->outsider->interns()->sync([$validated['intern_id']]);

                // Handle WhatsApp notification for ortu
                if ($request->has('notif_enabled') && !empty($validated['phone_number'])) {
                    \App\Models\WhatsappNumber::updateOrCreate(
                        [
                            'intern_id' => $validated['intern_id'],
                            'phone_number' => $validated['phone_number']
                        ],
                        [
                            'is_notification_active' => true
                        ]
                    );
                } else {
                    // Disable notification if unchecked or no phone number
                    if (!empty($validated['phone_number'])) {
                        \App\Models\WhatsappNumber::where('intern_id', $validated['intern_id'])
                            ->where('phone_number', $validated['phone_number'])
                            ->update(['is_notification_active' => false]);
                    }
                }
            } else {
                $user->outsider->interns()->sync([]);
            }

            DB::commit();

            $adminName = auth()->user()?->name ?? 'Admin';
            \App\Helper\ActivityLogger::log('UPDATE', 'User Management', "Admin {$adminName} memperbarui akun Mentor/Outsider: {$validated['full_name']}", ['user_id' => $user->id]);

            return redirect()->route('admin.outsiders.index')->with('success', 'Outsider berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Outsider error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal memperbarui data outsider. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menghapus data dari database
     */
    public function destroy(User $user)
    {
        $name = $user->profile?->full_name ?? $user->username;
        $uid = $user->id;
        DB::transaction(function () use ($user) {
            $user->load('outsider');
            if ($user->outsider) {
                $user->outsider->interns()->detach();
                $user->outsider->delete();
            }
            $user->profile()->delete();
            $user->delete();
        });

        $adminName = auth()->user()?->name ?? 'Admin';
        \App\Helper\ActivityLogger::log('DELETE', 'User Management', "Admin {$adminName} menghapus akun Mentor/Outsider: {$name}", ['user_id' => $uid]);

        return redirect()->route('admin.outsiders.index')->with('success', 'Data Outsider berhasil dihapus.');
    }
}
