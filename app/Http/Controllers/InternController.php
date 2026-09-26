<?php

namespace App\Http\Controllers;

use App\Services\InternService;
use App\Http\Requests\EditInternRequest;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Division;
use App\Models\HandRaise;
use App\Models\Intern;
use App\Models\InternAccount;
use App\Models\Profile;
use App\Models\School;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class InternController extends Controller
{
    protected InternService $internService;

    public function __construct(InternService $internService)
    {
        $this->internService = $internService;
    }

    /**
     * Tampilan form pendaftaran pemagang baru oleh Admin / Super Admin.
     */
    public function createInternView()
    {
        $schools = School::orderBy('name')->get();
        $divisions = Division::orderBy('name')->get();
        $shifts = Shift::orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        /** @var User|null $user */
        $user = auth()->user();
        if ($user) {
            $user->loadMissing('profile');
        }

        return view('admin.intern.create', compact('schools', 'divisions', 'shifts', 'brands', 'user'));
    }

    /**
     * Menyimpan data pemagang baru yang didaftarkan langsung oleh Admin / Super Admin.
     */
    public function storeInternAction(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'username' => 'required|string|max:30|unique:users,username',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'school_id' => 'required|exists:schools,id',
            'division_id' => 'required|exists:divisions,id',
            'shift_id' => 'required|exists:shifts,id',
            'brand_id' => 'nullable|exists:brands,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'phone' => 'required|string|max:20',
            'gender' => 'required|string',
            'birth_place' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'nip' => 'nullable|string|max:50',
            'nim' => 'nullable|string|max:50',
            'is_gps_active' => 'nullable|in:0,1',
        ], [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'school_id.required' => 'Asal sekolah/kampus wajib dipilih.',
            'division_id.required' => 'Divisi wajib dipilih.',
            'shift_id.required' => 'Shift kerja wajib dipilih.',
            'start_date.required' => 'Tanggal mulai magang wajib diisi.',
            'end_date.required' => 'Tanggal selesai magang wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'phone.required' => 'Nomor WhatsApp / HP wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
        ]);

        DB::beginTransaction();
        try {
            $agent = new Agent();
            $gpsActive = isset($validated['is_gps_active']) ? (int) $validated['is_gps_active'] : 1;

            $user = User::create([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role_id' => 3, // Intern
                'is_active' => true,
                'is_confirm' => true,
                'is_gps_activate' => $gpsActive,
                'is_gps_support' => $gpsActive,
                'os' => $agent->platform() ?: 'Windows',
                'browser' => $agent->browser() ?: 'Chrome',
                'device' => bin2hex(random_bytes(8)),
            ]);

            Profile::create([
                'user_id' => $user->id,
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'birth_place' => !empty($validated['birth_place']) ? $validated['birth_place'] : '-',
                'date_of_birth' => !empty($validated['date_of_birth']) ? $validated['date_of_birth'] : now()->subYears(18)->toDateString(),
                'gender' => $validated['gender'],
                'NIP' => $validated['nip'] ?? null,
            ]);

            $intern = Intern::create([
                'user_id' => $user->id,
                'school_id' => $validated['school_id'],
                'division_id' => $validated['division_id'],
                'brand_id' => $validated['brand_id'] ?? null,
                'nim' => $validated['nim'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]);

            InternAccount::create([
                'intern_id' => $intern->id,
            ]);

            DB::commit();

            \App\Helper\ActivityLogger::log('CREATE', 'User Management', "Admin mendaftarkan pemagang baru: {$validated['full_name']} ({$validated['username']})");

            return redirect()->route('admin.division.team', ['divisionId' => $validated['division_id']])
                ->with('success', "Pemagang {$validated['full_name']} berhasil didaftarkan.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('storeInternAction error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan pemagang: ' . $e->getMessage());
        }
    }

    public function adminUpdateInternAction(EditInternRequest $editInternRequest)
    {
        $result = $this->internService->update($editInternRequest);

        if ($result->isSuccess()) {
            \App\Helper\ActivityLogger::log('UPDATE', 'User Management', "Admin memperbarui data pemagang: {$editInternRequest->input('full_name')}");
            return redirect()->back()->with('success', 'Data intern berhasil diperbarui.');
        }

        return redirect()->back()->with('error', $result->getMessage());
    }

    public function raiseHandToggle(\Illuminate\Http\Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $user->loadMissing(['intern.detailProject', 'intern.division', 'profile']);

        $activeHandRaise = HandRaise::where('user_id', $user->id)
            ->where('is_raised', true)
            ->latest()
            ->first();

        // Mode: Turunkan tangan (jika sudah terangkat atau request action == lower)
        if ($request->input('action') === 'lower' || ($activeHandRaise && !$request->has('type'))) {
            if ($activeHandRaise) {
                $activeHandRaise->update([
                    'is_raised' => false,
                ]);
            }

            Log::info('Raise hand lowered', [
                'user_id' => $user->id,
            ]);

            return redirect()->back()->with('success', 'Tangan berhasil diturunkan!');
        }

        // Validasi input form raise hand 3 mode
        $validated = $request->validate([
            'type' => 'required|in:question,new_task,presentation',
            'notes' => 'nullable|string|max:1000',
            'note' => 'nullable|string|max:1000',
            'presentation_notes' => 'nullable|string|max:1000',
            'presentation_mode' => 'nullable|in:online,offline',
            'presentation_date' => 'nullable|date',
            'scheduled_time' => 'nullable|string',
            'project_id' => 'nullable|integer',
        ]);

        $type = $validated['type'] ?? 'question';
        $notes = $validated['notes'] ?? ($validated['note'] ?? null);
        $presentationNotes = $validated['presentation_notes'] ?? ($validated['note'] ?? null);
        $presentationMode = $type === 'presentation' ? ($validated['presentation_mode'] ?? 'offline') : null;
        $presentationDate = $type === 'presentation' ? ($validated['presentation_date'] ?? today()->toDateString()) : null;
        $scheduledTime = $type === 'presentation' ? ($validated['scheduled_time'] ?? null) : null;

        // Status awal pengajuan presentasi adalah pending (menunggu konfirmasi mentor)
        $status = 'pending';

        // Cari project_id jika kategori adalah presentasi (tugas baru belum memiliki project_id)
        $projectId = null;
        if ($type === 'presentation') {
            $projectId = $request->input('project_id') ?: null;
            if (!$projectId && !$request->has('project_id') && $user->intern && $user->intern->detailProject) {
                $lastDetail = $user->intern->detailProject->last();
                $projectId = $lastDetail?->project_id;
            }
        }

        // Nonaktifkan raise hand aktif sebelumnya jika ada agar tidak dobel aktif
        if ($activeHandRaise) {
            $activeHandRaise->update(['is_raised' => false]);
        }

        // Ambil link Google Meet divisi jika presentasi online
        $meetUrl = null;
        $divisionName = null;
        if ($type === 'presentation' && $presentationMode === 'online') {
            $user->loadMissing('intern.division');
            $division = $user->intern?->division;
            $meetUrl = $division?->meet_url;
            $divisionName = $division?->name ?? 'Divisi';
        }

        // Buat record baru untuk setiap pengajuan agar seluruh riwayat tersimpan utuh
        $handRaise = HandRaise::create([
            'user_id' => $user->id,
            'project_id' => $projectId,
            'type' => $type,
            'presentation_mode' => $presentationMode,
            'meet_url' => $meetUrl,
            'presentation_date' => $presentationDate,
            'scheduled_time' => $scheduledTime,
            'status' => $status,
            'notes' => $notes,
            'reason' => $presentationNotes ?: $notes,
            'is_raised' => true,
            'resolved_at' => null,
            'resolved_by' => null,
            'performance_rating' => null,
            'performance_notes' => null,
            'admin_response' => null,
        ]);

        Log::info('Raise hand submitted', [
            'user_id' => $user->id,
            'type' => $type,
            'status' => $status,
            'presentation_mode' => $presentationMode,
            'presentation_date' => $presentationDate,
            'scheduled_time' => $scheduledTime,
            'meet_url' => $meetUrl,
        ]);

        $typeLabel = match ($type) {
            'question' => 'Bertanya / Kendala',
            'new_task' => 'Permintaan Tugas Baru',
            'presentation' => 'Penjadwalan Presentasi',
            default => 'Raise Hand'
        };

        \App\Helper\ActivityLogger::log('CREATE', 'Raise Hand', "Pemagang " . ($user->profile?->full_name ?? $user->username) . " mengajukan {$typeLabel}");

        $message = match ($type) {
            'question' => 'Pertanyaan berhasil diajukan kepada mentor/admin!',
            'new_task' => 'Permintaan tugas baru berhasil dikirim kepada mentor!',
            'presentation' => 'Pengajuan presentasi berhasil dikirim! Menunggu konfirmasi dan persetujuan jadwal dari mentor.',
            default => 'Tangan berhasil diangkat!'
        };

        if ($type === 'presentation') {
            $user->loadMissing('intern.division');
            $divisionName = $user->intern?->division?->name ?? 'Divisi';

            return redirect()->back()->with([
                'success' => $message,
                'show_presentation_submitted_modal' => true,
                'presentation_detail' => [
                    'id' => $handRaise->id,
                    'title' => $notes ?: 'Presentasi Modul',
                    'date' => $presentationDate ? \Carbon\Carbon::parse($presentationDate)->format('d M Y') : '-',
                    'time' => $scheduledTime ? \Carbon\Carbon::parse($scheduledTime)->format('H:i') . ' WIB' : 'Menunggu penetapan jam',
                    'note' => $presentationNotes ?: $notes,
                    'mode' => $presentationMode,
                    'status' => 'pending',
                    'division_name' => $divisionName,
                    'meet_url' => $meetUrl,
                ]
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}
