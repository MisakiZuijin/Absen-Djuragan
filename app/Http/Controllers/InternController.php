<?php

namespace App\Http\Controllers;

use App\Services\InternService;
use App\Http\Requests\EditInternRequest;
use Illuminate\Http\Request;
use App\Models\HandRaise;
use App\Models\PrayerRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InternController extends Controller
{
    protected InternService $internService;

    public function __construct(InternService $internService)
    {
        $this->internService = $internService;
    }

    public function adminUpdateInternAction(EditInternRequest $editInternRequest)
    {
        $result = $this->internService->update($editInternRequest);

        if ($result->isSuccess()) {
            return redirect()->back()->with('success', 'Data intern berhasil diperbarui.');
        }

        return redirect()->back()->with('error', $result->getMessage());
    }

    public function raiseHandToggle(\Illuminate\Http\Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

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
            'presentation_mode' => 'nullable|in:online,offline',
            'presentation_date' => 'nullable|date',
            'project_id' => 'nullable|integer',
        ]);

        $type = $validated['type'] ?? 'question';
        $notes = $validated['notes'] ?? null;
        $presentationMode = $type === 'presentation' ? ($validated['presentation_mode'] ?? 'offline') : null;
        $presentationDate = $type === 'presentation' ? ($validated['presentation_date'] ?? today()->toDateString()) : null;

        // Logika Otomatis Urgensi: Jika presentasi dijadwalkan hari ini -> urgent!
        $status = 'pending';
        if ($type === 'presentation' && $presentationDate) {
            if (\Carbon\Carbon::parse($presentationDate)->isToday()) {
                $status = 'urgent';
            }
        }

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

        // Buat record baru untuk setiap pengajuan agar seluruh riwayat (tugas baru, tanya, presentasi) tersimpan utuh dan tidak saling menimpa
        $handRaise = HandRaise::create([
            'user_id' => $user->id,
            'project_id' => $projectId,
            'type' => $type,
            'presentation_mode' => $presentationMode,
            'meet_url' => $meetUrl,
            'presentation_date' => $presentationDate,
            'status' => $status,
            'notes' => $notes,
            'reason' => $notes, // sinkronisasi untuk kompatibilitas mundur
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
            'meet_url' => $meetUrl,
        ]);

        $message = match ($type) {
            'question' => 'Pertanyaan berhasil diajukan kepada mentor/admin!',
            'new_task' => 'Permintaan tugas baru berhasil dikirim kepada mentor!',
            'presentation' => 'Jadwal presentasi berhasil diajukan!' . ($status === 'urgent' ? ' (Terjadwal Hari Ini)' : ' (Terjadwal)'),
            default => 'Tangan berhasil diangkat!'
        };

        if ($type === 'presentation' && $presentationMode === 'online') {
            return redirect()->back()->with([
                'success' => $message,
                'show_online_meet_modal' => true,
                'meet_url' => $meetUrl,
                'division_name' => $divisionName,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}
