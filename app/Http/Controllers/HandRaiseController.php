<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\HandRaise;
use App\Models\Projects;
use App\Models\NameProjects;
use App\Models\DetailProjects;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Helper\ActivityLogger;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HandRaiseController extends Controller
{
    /**
     * Method untuk mendapatkan jumlah notifikasi untuk admin
     */
    public function getCount()
    {
        try {
            $totalCount = HandRaise::where('is_raised', true)->count();

            // Hitung hanya permintaan yang masuk kondisi URGENT untuk pengulangan bunyi notifikasi:
            // 1. Question (pertanyaan langsung saat ini)
            // 2. New Task (permintaan tugas baru saat ini)
            // 3. Presentation hari ini atau berstatus urgent
            // Permintaan presentasi besok / masa mendatang (presentation_date > today()) dikecualikan dari pengulangan bunyi
            $urgentCount = HandRaise::where('is_raised', true)
                ->where('status', '!=', 'done')
                ->where(function ($q) {
                    $q->where(function ($sq) {
                        $sq->where('type', '!=', 'presentation')
                            ->orWhereNull('type');
                    })
                        ->orWhere(function ($sq) {
                            $sq->where('type', 'presentation')
                                ->where(function ($ssq) {
                                    $ssq->where('status', 'urgent')
                                        ->orWhereDate('presentation_date', '<=', today());
                                });
                        });
                })
                ->count();

            $latest = HandRaise::with(['user.profile', 'user.intern.school'])
                ->where('is_raised', true)
                ->where('status', '!=', 'done')
                ->latest('updated_at')
                ->first();

            $latestMessage = \App\Models\HandRaiseMessage::where('is_from_admin', false)
                ->latest('id')
                ->first();

            return response()->json([
                'count' => $totalCount,
                'urgent_count' => $urgentCount,
                'latest_id' => $latest?->id ?? 0,
                'latest_message_id' => $latestMessage?->id ?? 0,
                'latest_request' => $latest ? [
                    'id' => $latest->id,
                    'sender_name' => $latest->user?->profile?->full_name ?? $latest->user?->name ?? 'Pemagang',
                    'school' => $latest->user?->intern?->school?->name ?? '-',
                    'type' => $latest->type ?? 'question',
                    'reason' => $latest->notes ?? $latest->reason ?? 'Membutuhkan bantuan/pertanyaan',
                    'created_at' => $latest->created_at ? $latest->created_at->diffForHumans() : 'Baru saja',
                ] : null,
                'type' => 'raise_hand',
                'timestamp' => now()->toISOString(),
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'count' => 0,
                'urgent_count' => 0,
                'latest_id' => 0,
                'latest_message_id' => 0,
                'latest_request' => null,
                'type' => 'raise_hand',
                'timestamp' => now()->toISOString(),
                'status' => 'error',
                'message' => 'Failed to fetch count'
            ], 500);
        }
    }

    /**
     * Tampilkan halaman index raise hand (3 tab aktif + 1 tab history selesai)
     */
    public function index(Request $request)
    {
        return view('admin.raise-hand');
    }

    /**
     * Get table data untuk AJAX refresh (mendukung parameter tab)
     */
    public function getTableData(Request $request)
    {
        try {
            $tab = $request->get('tab', 'question');
            $baseRelations = [
                'user.profile',
                'user.intern.school',
                'user.intern.division',
                'user.intern.detailProject.project.nameProject',
                'project.nameProject',
                'resolver.profile'
            ];

            if ($tab === 'new_task') {
                $items = HandRaise::with($baseRelations)
                    ->where('is_raised', true)
                    ->where('status', '!=', 'done')
                    ->where('type', 'new_task')
                    ->latest()
                    ->get();
            } elseif ($tab === 'presentation') {
                $items = HandRaise::with($baseRelations)
                    ->where('is_raised', true)
                    ->where('status', '!=', 'done')
                    ->where('type', 'presentation')
                    ->orderByRaw("CASE WHEN presentation_date = CURDATE() THEN 0 ELSE 1 END")
                    ->orderBy('presentation_date', 'asc')
                    ->orderBy('created_at', 'desc')
                    ->get();
            } elseif ($tab === 'history') {
                $items = HandRaise::with($baseRelations)
                    ->where(function ($q) {
                        $q->where('status', 'done')
                            ->orWhere(function ($sq) {
                                $sq->where('is_raised', false)->whereNotNull('resolved_at');
                            });
                    })
                    ->latest('resolved_at')
                    ->take(50)
                    ->get();
            } else {
                $items = HandRaise::with($baseRelations)
                    ->where('is_raised', true)
                    ->where('status', '!=', 'done')
                    ->where(function ($q) {
                        $q->where('type', 'question')->orWhereNull('type');
                    })
                    ->latest()
                    ->get();
            }

            return view('admin.partials.raise-hand-table-body', [
                'handRaises' => $items,
                'activeTab' => $tab,
            ])->render();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Dapatkan daftar pesan riwayat percakapan untuk modal chat raise hand real-time
     */
    public function getMessages(int $id): JsonResponse
    {
        try {
            $handRaise = HandRaise::with(['user.profile', 'resolver.profile'])->findOrFail($id);
            $thread = $handRaise->conversation_thread;

            return response()->json([
                'success' => true,
                'id' => $handRaise->id,
                'status' => $handRaise->status,
                'is_raised' => (bool) $handRaise->is_raised,
                'thread' => $thread,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil pesan: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Fetch data raise hand dengan relasi
     */
    private function fetchHandRaises()
    {
        return HandRaise::with([
            'user.profile',
            'user.intern.school',
            'user.intern.division',
            'user.intern.detailProject.project.nameProject',
            'project.nameProject',
            'resolver.profile'
        ])
            ->where('is_raised', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Konfirmasi dan proses raise hand request (Beri Tugas, Edit Tugas, Selesai, Revisi Presentasi, dll)
     */
    public function confirmAction(Request $request, int $id)
    {
        if (auth()->check() && (int) auth()->user()->role_id === 6) {
            return redirect()->back()->with('error', 'Aksi ini hanya dapat dilakukan oleh Admin / Pembimbing Utama.');
        }

        try {
            $handRaise = HandRaise::findOrFail($id);
            $userName = $handRaise->user->profile->full_name ?? $handRaise->user->name ?? 'Peserta';
            $action = $request->input('action');

            $targetTab = $request->input('tab');
            if (!$targetTab || !in_array($targetTab, ['question', 'new_task', 'presentation', 'history'])) {
                if ($action === 'complete_question' || $handRaise->type === 'question') {
                    $targetTab = 'question';
                } elseif (in_array($action, ['give_task', 'update_task', 'complete_task']) || $handRaise->type === 'new_task') {
                    $targetTab = 'new_task';
                } elseif (in_array($action, ['request_revision', 'ready_presentation', 'complete_presentation']) || $handRaise->type === 'presentation') {
                    $targetTab = 'presentation';
                } else {
                    $targetTab = 'question';
                }
            }

            $adminUser = auth()->user();
            $adminName = $adminUser->name ?? $adminUser->username ?? 'Admin';

            if ($action === 'respond_question') {
                $adminResponse = trim($request->input('admin_response') ?? '');
                if (empty($adminResponse)) {
                    return redirect()->back()->with('error', 'Tanggapan / jawaban tidak boleh kosong.');
                }

                // Backfill initial question if not yet in messages table
                if ($handRaise->messages()->count() === 0) {
                    $initNote = trim($handRaise->notes ?? $handRaise->reason ?? '');
                    if (!empty($initNote)) {
                        \App\Models\HandRaiseMessage::create([
                            'hand_raise_id' => $handRaise->id,
                            'user_id' => $handRaise->user_id,
                            'message' => $initNote,
                            'is_from_admin' => false,
                            'created_at' => $handRaise->created_at ?? now(),
                        ]);
                    }
                }

                // Simpan pesan balasan baru dari admin / mentor ke riwayat chat
                $newMessage = \App\Models\HandRaiseMessage::create([
                    'hand_raise_id' => $handRaise->id,
                    'user_id' => auth()->id(),
                    'message' => $adminResponse,
                    'is_from_admin' => true,
                ]);

                $handRaise->update([
                    'status' => 'responded',
                    'admin_response' => $adminResponse,
                    'resolved_by' => auth()->id(),
                    'is_raised' => true,
                ]);

                ActivityLogger::log(
                    'RESPOND',
                    'Raise Hand',
                    "Admin {$adminName} mengirimkan tanggapan bantuan untuk pemagang {$userName}",
                    ['hand_raise_id' => $handRaise->id, 'type' => 'question', 'action' => 'respond_question']
                );

                Log::info("Question responded for {$userName} by " . auth()->user()->name);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => "Tanggapan berhasil dikirimkan ke {$userName}.",
                        'note' => [
                            'id' => $newMessage->id,
                            'sender_name' => auth()->user()->profile?->full_name ?? auth()->user()->name ?? 'Mentor',
                            'message' => $adminResponse,
                            'is_from_admin' => true,
                            'time' => now()->format('H:i'),
                        ],
                    ]);
                }

                return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab])
                    ->with('success', "Tanggapan berhasil dikirimkan ke {$userName}. Pemagang akan melihat popup tanggapan di dashboard.");
            }

            if ($action === 'complete_question') {
                $handRaise->update([
                    'status' => 'done',
                    'is_raised' => false,
                    'resolved_at' => now(),
                    'resolved_by' => auth()->id(),
                ]);

                ActivityLogger::log(
                    'RESOLVE',
                    'Raise Hand',
                    "Admin {$adminName} menyelesaikan bantuan/pertanyaan untuk pemagang {$userName}",
                    ['hand_raise_id' => $handRaise->id, 'type' => 'question', 'action' => 'complete_question']
                );

                Log::info("Question/help resolved for {$userName} by " . auth()->user()->name);
                return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab])
                    ->with('success', "Bantuan/pertanyaan untuk {$userName} telah selesai.");
            }

            if ($action === 'give_task') {
                $taskTitle = $request->input('task_title');
                $project = $this->ensureProjectForTask($handRaise, $request->input('admin_response'), $taskTitle);
                $handRaise->update([
                    'status' => 'in_progress',
                    'admin_response' => $request->input('admin_response'),
                    'project_id' => $project?->id ?? $handRaise->project_id,
                    'resolved_by' => auth()->id(),
                    'is_raised' => true,
                ]);

                ActivityLogger::log(
                    'CREATE',
                    'Raise Hand',
                    "Admin {$adminName} memberikan tugas baru kepada pemagang {$userName}" . ($taskTitle ? " [{$taskTitle}]" : ''),
                    ['hand_raise_id' => $handRaise->id, 'task_title' => $taskTitle, 'action' => 'give_task']
                );

                Log::info("New task given to {$userName} by " . auth()->user()->name);
                return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab])
                    ->with('success', "Tugas baru berhasil diberikan kepada {$userName}. Status sekarang: Sedang Dikerjakan.");
            }

            if ($action === 'update_task') {
                $taskTitle = $request->input('task_title');
                $project = $this->ensureProjectForTask($handRaise, $request->input('admin_response'), $taskTitle);
                $handRaise->update([
                    'admin_response' => $request->input('admin_response'),
                    'project_id' => $project?->id ?? $handRaise->project_id,
                    'resolved_by' => auth()->id(),
                ]);

                ActivityLogger::log(
                    'UPDATE',
                    'Raise Hand',
                    "Admin {$adminName} memperbarui instruksi tugas pemagang {$userName}" . ($taskTitle ? " [{$taskTitle}]" : ''),
                    ['hand_raise_id' => $handRaise->id, 'task_title' => $taskTitle, 'action' => 'update_task']
                );

                Log::info("Task instruction updated for {$userName} by " . auth()->user()->name);
                return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab])
                    ->with('success', "Instruksi tugas untuk {$userName} berhasil diperbarui.");
            }

            if ($action === 'complete_task') {
                $handRaise->update([
                    'status' => 'done',
                    'is_raised' => false,
                    'resolved_at' => now(),
                    'resolved_by' => auth()->id(),
                ]);

                ActivityLogger::log(
                    'RESOLVE',
                    'Raise Hand',
                    "Admin {$adminName} menyelesaikan sesi permintaan tugas pemagang {$userName}",
                    ['hand_raise_id' => $handRaise->id, 'action' => 'complete_task']
                );

                Log::info("Task request resolved for {$userName} by " . auth()->user()->name);
                return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab])
                    ->with('success', "Permintaan tugas baru untuk {$userName} telah diselesaikan.");
            }

            if ($action === 'accept_presentation') {
                $pDate = $request->input('presentation_date') ?: ($handRaise->presentation_date?->toDateString() ?: today()->toDateString());
                $pTime = $request->input('scheduled_time') ?: $request->input('presentation_time');
                $pNotes = $request->input('notes') ?: $request->input('admin_response');

                // Validasi: Jam pelaksanaan tidak boleh di bawah waktu saat ini jika tanggal hari ini / masa lalu
                if ($pDate && $pTime) {
                    try {
                        $pDateTime = \Carbon\Carbon::parse("{$pDate} {$pTime}", 'Asia/Jakarta');
                        $now = \Carbon\Carbon::now('Asia/Jakarta');
                        if ($pDateTime->lessThanOrEqualTo($now)) {
                            $timeFormatted = date('H:i', strtotime($pTime));
                            return redirect()->back()->with('error', "Tidak dapat menerima jadwal: Waktu pelaksanaan yang dipilih ({$pDate} {$timeFormatted} WIB) sudah terlewat dari jam saat ini.");
                        }
                    } catch (\Throwable $e) {
                        // Skip if parse fails
                    }
                }

                $handRaise->update([
                    'status' => 'accepted',
                    'presentation_date' => $pDate,
                    'scheduled_time' => $pTime,
                    'admin_response' => $pNotes,
                    'resolved_by' => auth()->id(),
                    'is_raised' => true,
                ]);

                $timeFormatted = $pTime ? date('H:i', strtotime($pTime)) : '-';
                ActivityLogger::log(
                    'APPROVE',
                    'Raise Hand',
                    "Admin {$adminName} menerima jadwal presentasi pemagang {$userName} pada {$pDate} jam {$timeFormatted}",
                    ['hand_raise_id' => $handRaise->id, 'action' => 'accept_presentation', 'date' => $pDate, 'time' => $pTime]
                );

                Log::info("Presentation accepted for {$userName} on {$pDate} {$timeFormatted} by " . auth()->user()->name);
                return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab])
                    ->with('success', "Jadwal presentasi untuk {$userName} berhasil DITERIMA. Jadwal: {$pDate} pukul {$timeFormatted} WIB. Pemagang sekarang dapat mengakses sesi presentasi.");
            }

            if ($action === 'reschedule_presentation') {
                $pDate = $request->input('presentation_date') ?: ($handRaise->presentation_date?->toDateString() ?: today()->toDateString());
                $pTime = $request->input('scheduled_time') ?: $request->input('presentation_time');
                $pNotes = $request->input('notes') ?: $request->input('admin_response');

                // Validasi: Jam pelaksanaan tidak boleh di bawah waktu saat ini jika tanggal hari ini / masa lalu
                if ($pDate && $pTime) {
                    try {
                        $pDateTime = \Carbon\Carbon::parse("{$pDate} {$pTime}", 'Asia/Jakarta');
                        $now = \Carbon\Carbon::now('Asia/Jakarta');
                        if ($pDateTime->lessThanOrEqualTo($now)) {
                            $timeFormatted = date('H:i', strtotime($pTime));
                            return redirect()->back()->with('error', "Tidak dapat menjadwalkan ulang: Waktu pelaksanaan yang dipilih ({$pDate} {$timeFormatted} WIB) sudah terlewat dari jam saat ini.");
                        }
                    } catch (\Throwable $e) {
                        // Skip if parse fails
                    }
                }

                $handRaise->update([
                    'status' => 'rescheduled',
                    'presentation_date' => $pDate,
                    'scheduled_time' => $pTime,
                    'admin_response' => $pNotes,
                    'resolved_by' => auth()->id(),
                    'is_raised' => true,
                ]);

                $timeFormatted = $pTime ? date('H:i', strtotime($pTime)) : '-';
                ActivityLogger::log(
                    'UPDATE',
                    'Raise Hand',
                    "Admin {$adminName} menjadwalkan ulang presentasi pemagang {$userName} ke tanggal {$pDate} jam {$timeFormatted}",
                    ['hand_raise_id' => $handRaise->id, 'action' => 'reschedule_presentation', 'date' => $pDate, 'time' => $pTime]
                );

                Log::info("Presentation rescheduled for {$userName} to {$pDate} {$timeFormatted} by " . auth()->user()->name);
                return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab])
                    ->with('success', "Jadwal presentasi untuk {$userName} berhasil DIJADWALKAN ULANG ke tanggal {$pDate} pukul {$timeFormatted} WIB.");
            }

            if ($action === 'reject_presentation') {
                $pNotes = $request->input('notes') ?: $request->input('admin_response') ?: 'Pengajuan presentasi ditolak oleh pembimbing. Harap lengkapi materi sebelum mengajukan kembali.';

                $handRaise->update([
                    'status' => 'rejected',
                    'admin_response' => $pNotes,
                    'is_raised' => false,
                    'resolved_at' => now(),
                    'resolved_by' => auth()->id(),
                ]);

                // Pastikan seluruh raise hand presentasi aktif milik pemagang ini diturunkan (is_raised = false)
                HandRaise::where('user_id', $handRaise->user_id)
                    ->where('type', 'presentation')
                    ->where('is_raised', true)
                    ->update([
                        'is_raised' => false,
                        'status' => 'rejected',
                        'admin_response' => $pNotes,
                        'resolved_at' => now(),
                        'resolved_by' => auth()->id(),
                    ]);

                \Illuminate\Support\Facades\Cache::forget('navbar_raise_hand_count');

                ActivityLogger::log(
                    'RESOLVE',
                    'Raise Hand',
                    "Admin {$adminName} menolak pengajuan presentasi pemagang {$userName}",
                    ['hand_raise_id' => $handRaise->id, 'action' => 'reject_presentation', 'notes' => $pNotes]
                );

                Log::info("Presentation rejected for {$userName} by " . auth()->user()->name);
                return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab])
                    ->with('success', "Pengajuan presentasi untuk {$userName} telah DITOLAK. Catatan penolakan telah dikirimkan ke pemagang.");
            }

            if ($action === 'request_revision') {
                if (in_array($handRaise->status, ['pending', 'rescheduled'])) {
                    return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab])
                        ->with('error', 'Presentasi belum diterima. Harap terima atau konfirmasi jadwal presentasi terlebih dahulu sebelum melakukan review.');
                }

                $handRaise->update([
                    'status' => 'needs_revision',
                    'resolved_by' => auth()->id(),
                    'is_raised' => true,
                ]);

                // Pastikan project tetap progress (belum selesai)
                if ($handRaise->project) {
                    $handRaise->project->update(['status' => 'progress']);
                }

                ActivityLogger::log(
                    'UPDATE',
                    'Raise Hand',
                    "Admin {$adminName} menetapkan status Presentasi pemagang {$userName}: Ada Revisi",
                    ['hand_raise_id' => $handRaise->id, 'action' => 'request_revision']
                );

                Log::info("Presentation marked as needs revision for {$userName} by " . auth()->user()->name);
                return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab])
                    ->with('success', "Status presentasi {$userName} berhasil diatur: Ada Revisi. Pemagang dapat mengisi catatan revisi di daftar tugasnya.");
            }

            if ($action === 'ready_presentation') {
                if (in_array($handRaise->status, ['pending', 'rescheduled'])) {
                    return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab])
                        ->with('error', 'Presentasi belum diterima. Harap terima atau konfirmasi jadwal presentasi terlebih dahulu sebelum melakukan review.');
                }

                $handRaise->update([
                    'status' => 'ready',
                    'resolved_by' => auth()->id(),
                    'is_raised' => true,
                ]);

                // Pastikan project_id terhubung jika belum
                $project = $handRaise->project;
                if (!$project && $handRaise->user && $handRaise->user->intern) {
                    $project = $handRaise->user->intern->detailProject?->where('project.status', '!=', 'done')->last()?->project
                        ?? $handRaise->user->intern->detailProject?->last()?->project;
                    if ($project) {
                        $handRaise->update(['project_id' => $project->id]);
                    }
                }

                // Karena sudah presentasi valid (tanpa revisi), tandai project ini sebagai selesai
                if ($project) {
                    $project->update(['status' => 'done']);

                    // Sinkronkan juga tugas new_task terkait project ini agar otomatis selesai
                    HandRaise::where('user_id', $handRaise->user_id)
                        ->where('project_id', $project->id)
                        ->where('type', 'new_task')
                        ->update([
                            'status' => 'done',
                            'is_raised' => false,
                            'resolved_at' => now(),
                            'resolved_by' => auth()->id(),
                        ]);
                }

                ActivityLogger::log(
                    'APPROVE',
                    'Raise Hand',
                    "Admin {$adminName} mengesahkan Presentasi pemagang {$userName} Lulus Valid (Tanpa Revisi)",
                    ['hand_raise_id' => $handRaise->id, 'action' => 'ready_presentation']
                );

                Log::info("Presentation marked as completed/ready for {$userName} by " . auth()->user()->name);
                return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab])
                    ->with('success', "Status presentasi {$userName} dikonfirmasi: Tanpa Revisi (Selesai Valid).");
            }

            if ($action === 'complete_presentation') {
                if (!in_array($handRaise->status, ['ready', 'needs_revision'])) {
                    return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab])
                        ->with('error', 'Status presentasi belum diubah. Harap tentukan status (Lulus atau Revisi) terlebih dahulu sebelum menyelesaikan presentasi.');
                }

                $handRaise->update([
                    'is_raised' => false,
                    'resolved_at' => now(),
                    'resolved_by' => auth()->id(),
                ]);

                $project = $handRaise->project;
                if (!$project && $handRaise->user && $handRaise->user->intern) {
                    $project = $handRaise->user->intern->detailProject?->where('project.status', '!=', 'done')->last()?->project
                        ?? $handRaise->user->intern->detailProject?->last()?->project;
                    if ($project) {
                        $handRaise->update(['project_id' => $project->id]);
                    }
                }

                // HANYA tandai project selesai JIKA BUKAN dalam kondisi revisi
                if ($handRaise->status === 'needs_revision') {
                    // Jika ada revisi, project tetap progress agar pemagang dapat mengerjakan revisi
                    if ($project) {
                        $project->update(['status' => 'progress']);
                    }

                    ActivityLogger::log(
                        'RESOLVE',
                        'Raise Hand',
                        "Admin {$adminName} menyelesaikan sesi presentasi pemagang {$userName} (Ada Revisi)",
                        ['hand_raise_id' => $handRaise->id, 'status' => 'needs_revision']
                    );

                    Log::info("Presentation raise hand ended (with revision) for {$userName} by " . auth()->user()->name);
                    return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab])
                        ->with('success', "Sesi raise hand presentasi {$userName} selesai. Project tetap aktif berjalan dalam status revisi untuk dikerjakan pemagang.");
                } else {
                    // Jika tidak ada revisi / status ready / valid, maka tandai project selesai
                    $handRaise->update(['status' => 'done']);
                    if ($project) {
                        $project->update(['status' => 'done']);

                        // Sinkronkan tugas new_task terkait project ini agar otomatis selesai
                        HandRaise::where('user_id', $handRaise->user_id)
                            ->where('project_id', $project->id)
                            ->where('type', 'new_task')
                            ->update([
                                'status' => 'done',
                                'is_raised' => false,
                                'resolved_at' => now(),
                                'resolved_by' => auth()->id(),
                            ]);
                    }

                    ActivityLogger::log(
                        'RESOLVE',
                        'Raise Hand',
                        "Admin {$adminName} menyelesaikan sesi presentasi pemagang {$userName} (Lulus Selesai Valid)",
                        ['hand_raise_id' => $handRaise->id, 'status' => 'done']
                    );

                    Log::info("Presentation completed validly for {$userName} by " . auth()->user()->name);
                    return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab])
                        ->with('success', "Presentasi {$userName} telah disahkan selesai valid.");
                }
            }

            // Default / Evaluate Presentation / Legacy Question Reply
            $updateData = [
                'is_raised' => false,
                'status' => 'done',
                'resolved_at' => now(),
                'resolved_by' => auth()->id()
            ];

            if ($request->filled('admin_response')) {
                $updateData['admin_response'] = $request->input('admin_response');
            }
            if ($request->filled('performance_notes')) {
                $updateData['performance_notes'] = $request->input('performance_notes');
                if (!isset($updateData['admin_response'])) {
                    $updateData['admin_response'] = $request->input('performance_notes');
                }
            } elseif (isset($updateData['admin_response'])) {
                $updateData['performance_notes'] = $updateData['admin_response'];
            }
            if ($request->filled('performance_rating')) {
                $updateData['performance_rating'] = $request->input('performance_rating');
            }

            $handRaise->update($updateData);

            // Jika ini presentasi dan diselesaikan via modal evaluasi, selesaikan juga project dan tugas terkait
            if ($handRaise->type === 'presentation' && $handRaise->status !== 'needs_revision') {
                $project = $handRaise->project;
                if (!$project && $handRaise->user && $handRaise->user->intern) {
                    $project = $handRaise->user->intern->detailProject?->where('project.status', '!=', 'done')->last()?->project
                        ?? $handRaise->user->intern->detailProject?->last()?->project;
                    if ($project) {
                        $handRaise->update(['project_id' => $project->id]);
                    }
                }
                if ($project) {
                    $project->update(['status' => 'done']);

                    HandRaise::where('user_id', $handRaise->user_id)
                        ->where('project_id', $project->id)
                        ->where('type', 'new_task')
                        ->update([
                            'status' => 'done',
                            'is_raised' => false,
                            'resolved_at' => now(),
                            'resolved_by' => auth()->id(),
                        ]);
                }
            }

            $evalRating = $updateData['performance_rating'] ?? null;
            ActivityLogger::log(
                'RESOLVE',
                'Raise Hand',
                "Admin {$adminName} mengevaluasi dan menyelesaikan sesi raise hand pemagang {$userName}" . ($evalRating ? " (Nilai: {$evalRating})" : ''),
                ['hand_raise_id' => $handRaise->id, 'rating' => $evalRating]
            );

            Log::info("Raise hand confirmed by " . auth()->user()->name . " for user: " . $userName);

            $msg = "Permintaan dari {$userName} berhasil diproses dan diselesaikan";
            if ($handRaise->type === 'presentation' && isset($updateData['performance_rating'])) {
                $msg .= " (Nilai performa: {$updateData['performance_rating']})";
            }

            return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab])
                ->with('success', $msg . ".");
        } catch (Exception $e) {
            Log::error('Error confirming raise hand: ' . $e->getMessage());
            return redirect()->route('admin.raiseHand.index', ['tab' => $targetTab ?? 'question'])
                ->with('error', 'Gagal memproses permintaan: ' . $e->getMessage());
        }
    }

    public function getEnhancedCount(): JsonResponse
    {
        try {
            $count = HandRaise::where('is_raised', true)->count();

            // Get recent raise hands for additional context
            $recentRaiseHands = HandRaise::with(['user.profile', 'user.intern.school'])
                ->where('is_raised', true)
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get()
                ->map(function ($raiseHand) {
                    return [
                        'id' => $raiseHand->id,
                        'user_name' => $raiseHand->user->profile->full_name ?? $raiseHand->user->name,
                        'school' => $raiseHand->user->intern->school->name ?? 'Unknown',
                        'created_at' => $raiseHand->created_at->diffForHumans(),
                        'reason' => $raiseHand->reason ?? 'Tidak ada keterangan'
                    ];
                });

            return response()->json([
                'success' => true,
                'count' => $count,
                'recent_requests' => $recentRaiseHands,
                'timestamp' => now()->toISOString(),
                'message' => $count > 0 ? "Ada {$count} permintaan raise hand" : 'Tidak ada permintaan'
            ]);
        } catch (Exception $e) {
            Log::error('Error getting enhanced raise hand count: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'count' => 0,
                'recent_requests' => [],
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Lightweight polling method for fallback
     */
    public function pollNotifications(): JsonResponse
    {
        try {
            $count = HandRaise::where('is_raised', true)->count();

            return response()->json([
                'success' => true,
                'count' => $count,
                'timestamp' => now()->toISOString(),
                'polling' => true
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'count' => 0,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    public function latest()
    {
        $latest = HandRaise::with(['user.profile', 'user.intern.school'])
            ->where('is_raised', true)
            ->latest()
            ->first();

        if (!$latest) {
            return response()->json(null);
        }

        return response()->json([
            'id' => $latest->id,
            'name' => $latest->user->profile->full_name ?? $latest->user->name ?? 'Peserta',
            'school' => $latest->user->intern->school->name ?? '-',
            'type' => $latest->type ?? 'question',
            'notes' => $latest->notes ?? $latest->reason ?? '',
            'created_at' => $latest->created_at?->toIso8601String(),
        ]);
    }

    /**
     * Quick resolve method for instant feedback
     */
    public function quickResolve(Request $request, int $id): JsonResponse
    {
        if (auth()->check() && (int) auth()->user()->role_id === 6) {
            return response()->json(['success' => false, 'message' => 'Aksi ini hanya dapat dilakukan oleh Admin / Pembimbing Utama.'], 403);
        }

        try {
            $handRaise = HandRaise::findOrFail($id);
            $userName = $handRaise->user->profile->full_name ?? $handRaise->user->name ?? 'User';
            $adminUser = auth()->user();
            $adminName = $adminUser->name ?? $adminUser->username ?? 'Admin';

            $handRaise->update([
                'is_raised' => false,
                'resolved_at' => now(),
                'resolved_by' => auth()->id()
            ]);

            ActivityLogger::log(
                'RESOLVE',
                'Raise Hand',
                "Admin {$adminName} menyelesaikan bantuan Raise Hand pemagang {$userName} (Quick Resolve)",
                ['hand_raise_id' => $handRaise->id, 'action' => 'quick_resolve']
            );

            // Log activity
            Log::info("Quick resolve raise hand by " . auth()->user()->name . " for user: " . $userName);

            // Get updated count
            $newCount = HandRaise::where('is_raised', true)->count();

            return response()->json([
                'success' => true,
                'message' => "Permintaan dari {$userName} berhasil diselesaikan",
                'new_count' => $newCount,
                'resolved_user' => $userName
            ]);
        } catch (Exception $e) {
            Log::error('Error quick resolving raise hand: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Gagal menyelesaikan permintaan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stream()
    {
        // Validate user access
        if (!auth()->check() || auth()->user()->role_id !== 1) {
            abort(403);
        }

        return response()->stream(function () {
            // Set proper SSE headers
            echo "data: " . json_encode(['status' => 'connected', 'time' => now()]) . "\n\n";

            if (ob_get_level()) {
                ob_end_flush();
            }
            flush();

            $lastCount = 0;
            $iterations = 0;

            while ($iterations < 60) { // Maximum 2 minutes (60 * 2 seconds)
                if (connection_aborted()) {
                    break;
                }

                try {
                    $currentCount = HandRaise::where('is_raised', true)->count();

                    if ($currentCount !== $lastCount) {
                        echo "data: " . json_encode([
                            'count' => $currentCount,
                            'type' => 'raise_hand',
                            'timestamp' => now()->toISOString()
                        ]) . "\n\n";

                        $lastCount = $currentCount;

                        if (ob_get_level()) {
                            ob_end_flush();
                        }
                        flush();
                    }
                } catch (\Exception $e) {
                    Log::error('SSE Stream error: ' . $e->getMessage());
                    echo "data: " . json_encode(['error' => 'Connection error']) . "\n\n";
                    if (ob_get_level()) {
                        ob_end_flush();
                    }
                    flush();
                    break;
                }

                sleep(2);
                $iterations++;
            }
        }, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no'
        ]);
    }

    public function getNotificationData(): JsonResponse
    {
        try {
            $rawRaises = HandRaise::with(['user.profile', 'user.intern.school'])
                ->where('is_raised', true)
                ->orderBy('created_at', 'desc')
                ->get();

            $count = $rawRaises->count();
            $recentRequests = $rawRaises->take(5)->map(function ($raise) {
                return [
                    'id' => $raise->id,
                    'user_name' => $raise->user->profile->full_name ?? $raise->user->name,
                    'school' => $raise->user->intern->school->name ?? 'Unknown',
                    'time_ago' => $raise->created_at->diffForHumans(),
                    'reason' => $raise->reason ?? 'Membutuhkan bantuan'
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $count,
                'recent_requests' => $recentRequests,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'count' => 0,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggle()
    {
        $user = auth()->user();

        $handRaise = HandRaise::firstOrCreate(
            ['user_id' => $user->id],
            [
                'is_raised' => false,
                'project_id' => null, // sesuaikan dengan kebutuhan
            ]
        );

        // Toggle status
        $handRaise->is_raised = !$handRaise->is_raised;
        $handRaise->save();

        $status = $handRaise->is_raised ? 'diangkat' : 'diturunkan';

        return redirect()->back()->with('success', "Tangan berhasil {$status}!");
    }

    /**
     * Pastikan tugas baru memiliki project di tabel projects
     * Format judul: tetap mempertahankan identitas divisi dan di sebelahnya judul spesifiknya
     */
    protected function ensureProjectForTask(HandRaise $handRaise, ?string $instruction = null, ?string $taskTitle = null): ?Projects
    {
        try {
            $intern = $handRaise->user?->intern;
            $divName = $intern?->division?->name ?? 'Divisi';
            $prefix = 'Project ' . $divName . ' - ';

            // Jika hand raise sudah terhubung ke project, perbarui deskripsi dan judul jika ada
            if ($handRaise->project) {
                $project = $handRaise->project;
                if ($instruction) {
                    $project->update([
                        'description' => $instruction,
                        'status' => 'progress',
                    ]);
                }
                if ($taskTitle) {
                    $cleanTitle = ltrim($taskTitle, ' -');
                    $fullName = str_starts_with($cleanTitle, 'Project ') ? $cleanTitle : $prefix . $cleanTitle;
                    $nameProject = NameProjects::firstOrCreate(['name' => $fullName], ['name' => $fullName]);
                    $project->update(['name_project_id' => $nameProject->id]);
                }
                return $project;
            }

            // Tentukan nama project: tetap mempertahankan divisi dan di sebelahnya judulnya
            if (!empty($taskTitle)) {
                $cleanTitle = ltrim($taskTitle, ' -');
                $fullName = str_starts_with($cleanTitle, 'Project ') ? $cleanTitle : $prefix . $cleanTitle;
            } else {
                $fullName = 'Project ' . $divName;
            }

            $nameProject = NameProjects::firstOrCreate(
                ['name' => $fullName],
                ['name' => $fullName]
            );

            $project = Projects::create([
                'name_project_id' => $nameProject->id,
                'team' => $divName,
                'description' => $instruction ?: ($handRaise->notes ?: 'Tugas dari pembimbing'),
                'status' => 'progress',
            ]);

            if ($intern) {
                DetailProjects::firstOrCreate([
                    'project_id' => $project->id,
                    'intern_id' => $intern->id,
                ]);
            }

            $handRaise->update(['project_id' => $project->id]);
            return $project;
        } catch (\Throwable $e) {
            Log::warning('Gagal auto-link project: ' . $e->getMessage());
            return null;
        }
    }
}
