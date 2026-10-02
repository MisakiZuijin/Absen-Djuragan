<?php

namespace App\Http\Controllers;

use App\Helper\ActivityLogger;
use App\Models\ChangeTimeNote;
use App\Models\ChangeTimeRegistration;
use App\Models\ChangeTimeSetting;
use App\Models\Intern;
use App\Models\Office;
use App\Models\Shift;
use App\Services\DebtCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeTimeRegistrationController extends Controller
{
    /**
     * Pemagang mengajukan pendaftaran pra-ganti jam (hanya mengisi catatan/alasan).
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->intern) {
            return redirect()->back()->with('error', 'Data pemagang tidak valid.');
        }

        $intern = $user->intern;

        $request->validate([
            'requested_date' => 'required|date|after:today',
            'shift_id' => 'required|integer|exists:shifts,id',
            'reason' => 'required|string|max:500',
        ], [
            'requested_date.required' => 'Hari / tanggal rencana ganti jam wajib dipilih.',
            'requested_date.after' => 'Tanggal rencana ganti jam tidak dapat memilih hari ini atau tanggal lampau.',
            'shift_id.required' => 'Pilihan shift ganti jam wajib dipilih.',
            'reason.required' => 'Catatan / keterangan rencana ganti jam wajib diisi.',
            'reason.max' => 'Catatan / keterangan maksimal 500 karakter.',
        ]);

        // Validasi 1: Pemagang harus memiliki hutang jam yang valid
        $debtService = app(DebtCalculationService::class);
        $debts = $debtService->getInternDebts($intern->id, true);
        if ($debts->isEmpty()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki catatan kekurangan/hutang jam kerja saat ini.');
        }

        // Validasi 2: Pemagang hanya boleh memiliki 1 pendaftaran aktif (pending atau approved yang belum terlaksana)
        $existingActive = ChangeTimeRegistration::where('intern_id', $intern->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingActive) {
            return redirect()->back()->with(
                'error',
                "Anda sudah memiliki pendaftaran ganti jam yang aktif. Anda dapat langsung memulai sesi ganti jam atau batalkan pendaftaran sebelumnya terlebih dahulu."
            );
        }

        // Ambil default office dari setting jika ada
        $setting = ChangeTimeSetting::getSettings();
        $officeId = $setting->default_office_id ?: ($setting->allowed_office_ids[0] ?? $intern->office_id ?? 1);

        // Buat pendaftaran baru
        $registration = ChangeTimeRegistration::create([
            'intern_id' => $intern->id,
            'requested_date' => Carbon::parse($request->input('requested_date'))->toDateString(),
            'shift_id' => $request->input('shift_id'),
            'office_id' => $officeId,
            'reason' => $request->input('reason'),
            'status' => 'pending',
            'target_schedule_ids' => null,
            'estimated_minutes' => 0,
        ]);

        $userName = $user->profile->full_name ?? $user->username;
        $dateStr = $registration->requested_date ? Carbon::parse($registration->requested_date)->format('d/m/Y') : '-';
        ActivityLogger::log(
            'CREATE',
            'Change Time Registration',
            "Pemagang {$userName} mendaftarkan rencana ganti jam untuk tanggal {$dateStr}",
            [
                'registration_id' => $registration->id,
                'intern_id' => $intern->id,
                'requested_date' => $registration->requested_date,
                'shift_id' => $registration->shift_id,
                'reason' => $registration->reason,
            ]
        );

        return redirect()->back()->with(
            'success',
            'Pendaftaran rencana ganti jam berhasil diajukan dan menunggu persetujuan Admin.'
        );
    }

    /**
     * Pemagang membatalkan pengajuan pra-pendaftaran ganti jam miliknya.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function cancel(int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->intern) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $registration = ChangeTimeRegistration::where('id', $id)
            ->where('intern_id', $user->intern->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if (!$registration) {
            return redirect()->back()->with('error', 'Data pendaftaran ganti jam tidak ditemukan atau sudah tidak aktif.');
        }

        $registration->update(['status' => 'cancelled']);

        // Hapus log diskusi / chat terkait pendaftaran ini
        ChangeTimeNote::where('registration_id', $registration->id)->delete();

        $userName = $user->profile->full_name ?? $user->username;
        $dateStr = $registration->requested_date ? $registration->requested_date->format('d/m/Y') : '(menunggu jadwal admin)';
        ActivityLogger::log(
            'CANCEL',
            'Change Time Registration',
            "Pemagang {$userName} membatalkan pendaftaran ganti jam untuk tanggal {$dateStr}",
            ['registration_id' => $registration->id]
        );

        return redirect()->back()->with('success', 'Pengajuan ganti jam berhasil dibatalkan.');
    }

    /**
     * Admin mengirim catatan / pesan ke pemagang terkait pendaftaran ganti jam.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function sendNote(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ], [
            'message.required' => 'Pesan tidak boleh kosong.',
            'message.max' => 'Pesan maksimal 1000 karakter.',
        ]);

        $registration = ChangeTimeRegistration::with('intern.user.profile')->findOrFail($id);

        $note = ChangeTimeNote::create([
            'registration_id' => $registration->id,
            'user_id' => Auth::id(),
            'message' => $request->input('message'),
            'is_from_admin' => true,
            'is_read' => false,
        ]);

        $internName = $registration->intern?->user?->profile?->full_name ?? 'Pemagang';
        $adminName = Auth::user()?->profile?->full_name ?? Auth::user()?->username ?? 'Admin';

        // Tandai pesan pemagang pada pendaftaran ini sebagai sudah dibaca admin
        ChangeTimeNote::where('registration_id', $registration->id)
            ->where('is_from_admin', false)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        ActivityLogger::log(
            'CREATE',
            'Change Time Note',
            "Admin {$adminName} mengirim pesan diskusi ganti jam ke {$internName}",
            ['registration_id' => $registration->id]
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'note' => [
                    'id' => $note->id,
                    'registration_id' => $registration->id,
                    'message' => $note->message,
                    'is_from_admin' => true,
                    'sender_name' => $adminName,
                    'time' => $note->created_at->format('H:i, d M Y'),
                ],
            ]);
        }

        return redirect()->back();
    }

    /**
     * Admin menandai semua pesan pemagang pada pendaftaran ini sebagai sudah dibaca.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function markAsRead(int $id): JsonResponse
    {
        ChangeTimeNote::where('registration_id', $id)
            ->where('is_from_admin', false)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Pemagang membalas catatan / pesan dari admin.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function replyNote(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->intern) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'message' => 'required|string|max:1000',
        ], [
            'message.required' => 'Balasan tidak boleh kosong.',
            'message.max' => 'Balasan maksimal 1000 karakter.',
        ]);

        $registration = ChangeTimeRegistration::where('id', $id)
            ->where('intern_id', $user->intern->id)
            ->firstOrFail();

        ChangeTimeNote::create([
            'registration_id' => $registration->id,
            'user_id' => $user->id,
            'message' => $request->input('message'),
            'is_from_admin' => false,
            'is_read' => false,
        ]);

        // Tandai pesan admin sebelumnya sebagai sudah dibaca
        ChangeTimeNote::where('registration_id', $registration->id)
            ->where('is_from_admin', true)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return redirect()->back()->with('success', 'Balasan berhasil dikirim ke admin.');
    }

    /**
     * Admin menyetujui pra-pendaftaran ganti jam pemagang (opsional / langsung).
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function adminApprove(Request $request, int $id): RedirectResponse
    {
        $registration = ChangeTimeRegistration::with('intern.user.profile')->findOrFail($id);

        $request->validate([
            'requested_date' => 'nullable|date|after:today',
            'shift_id' => 'nullable|integer|exists:shifts,id',
            'office_id' => 'nullable|integer|exists:offices,id',
            'admin_notes' => 'nullable|string|max:500',
        ], [
            'requested_date.after' => 'Tanggal rencana ganti jam tidak dapat memilih hari ini atau tanggal lampau.',
        ]);

        $requestedDate = $request->filled('requested_date')
            ? Carbon::parse($request->input('requested_date'), 'Asia/Jakarta')->toDateString()
            : $registration->requested_date;

        $dataToUpdate = [
            'status' => 'approved',
            'requested_date' => $requestedDate,
            'shift_id' => $request->filled('shift_id') ? $request->input('shift_id') : $registration->shift_id,
            'office_id' => $request->filled('office_id') ? $request->input('office_id') : $registration->office_id,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'admin_notes' => $request->input('admin_notes') ?: 'Disetujui oleh Admin',
        ];

        $registration->update($dataToUpdate);

        // Hapus log diskusi / chat terkait pendaftaran ini setelah disetujui
        ChangeTimeNote::where('registration_id', $registration->id)->delete();

        $internName = $registration->intern?->user?->profile?->full_name ?? $registration->intern?->user?->username ?? 'Pemagang';
        $adminName = Auth::user()?->profile?->full_name ?? Auth::user()?->username ?? 'Admin';

        ActivityLogger::log(
            'APPROVE',
            'Change Time Registration',
            "Admin {$adminName} menyetujui pendaftaran ganti jam untuk {$internName}",
            [
                'registration_id' => $registration->id,
                'approved_by' => Auth::id(),
                'requested_date' => $dataToUpdate['requested_date'],
                'shift_id' => $dataToUpdate['shift_id'],
                'office_id' => $dataToUpdate['office_id'],
                'admin_notes' => $dataToUpdate['admin_notes'],
            ]
        );

        return redirect()->back()->with('success', "Pendaftaran ganti jam untuk {$internName} berhasil disetujui!");
    }

    /**
     * Admin menolak pra-pendaftaran ganti jam pemagang.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function adminReject(Request $request, int $id): RedirectResponse
    {
        $registration = ChangeTimeRegistration::with('intern.user.profile')->findOrFail($id);

        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ], [
            'admin_notes.required' => 'Harap berikan alasan penolakan pengajuan ganti jam.',
        ]);

        $registration->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        // Hapus log diskusi / chat terkait pendaftaran ini setelah ditolak
        ChangeTimeNote::where('registration_id', $registration->id)->delete();

        $internName = $registration->intern?->user?->profile?->full_name ?? 'Pemagang';
        $adminName = Auth::user()?->profile?->full_name ?? Auth::user()?->username ?? 'Admin';

        $dateStr = $registration->requested_date ? $registration->requested_date->format('d/m/Y') : '(menunggu jadwal admin)';
        ActivityLogger::log(
            'REJECT',
            'Change Time Registration',
            "Admin {$adminName} menolak pra-pendaftaran ganti jam untuk {$internName} pada tanggal {$dateStr}",
            [
                'registration_id' => $registration->id,
                'rejected_by' => Auth::id(),
                'admin_notes' => $request->input('admin_notes'),
            ]
        );

        return redirect()->back()->with('success', "Pra-pendaftaran ganti jam untuk {$internName} telah ditolak.");
    }

    /**
     * Admin menghapus record pra-pendaftaran ganti jam.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function adminDestroy(int $id): RedirectResponse
    {
        $registration = ChangeTimeRegistration::with('intern.user.profile')->findOrFail($id);
        $internName = $registration->intern?->user?->profile?->full_name ?? $registration->intern?->user?->username ?? 'Pemagang';
        $adminName = Auth::user()?->profile?->full_name ?? Auth::user()?->username ?? 'Admin';
        $dateStr = $registration->requested_date ? $registration->requested_date->format('d/m/Y') : '(menunggu jadwal admin)';

        // Jika pendaftaran disetujui dan ada sesi ganti jam terkait yang belum selesai, bersihkan juga
        if ($registration->status === 'approved' && $registration->requested_date) {
            \App\Models\ChangeTimeSession::where('intern_id', $registration->intern_id)
                ->whereDate('session_date', $registration->requested_date)
                ->where('status', '!=', 'approved')
                ->delete();
        }

        // Hapus log chat jika ada
        ChangeTimeNote::where('registration_id', $registration->id)->delete();

        $registration->delete();

        ActivityLogger::log(
            'DELETE',
            'Change Time Registration',
            "Admin {$adminName} menghapus pendaftaran ganti jam untuk {$internName} (tanggal: {$dateStr})",
            ['registration_id' => $id]
        );

        return redirect()->back()->with('success', "Data pendaftaran ganti jam untuk {$internName} berhasil dihapus.");
    }
}
