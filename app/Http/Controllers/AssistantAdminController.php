<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\HandRaise;
use App\Models\LogActivity;
use App\Models\Status;
use App\Models\Intern;
use App\Models\PermitLog; // Pastikan model ini sudah di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\UserService;
use App\Services\AssistantAdminService;
use App\Helper\ActivityLogger;

class AssistantAdminController extends Controller
{
    protected UserService $userService;
    protected AssistantAdminService $assistantAdminService;

    public function __construct(UserService $userService, AssistantAdminService $assistantAdminService)
    {
        $this->userService = $userService;
        $this->assistantAdminService = $assistantAdminService;
    }

    // =============================================
    // ADMIN MANAGEMENT METHODS (ROLE 1)
    // =============================================

    public function index()
    {
        $data = [
            'user' => $this->userService->getUserLoggedData(),
            'assistantAdmins' => $this->assistantAdminService->getAssistantAdmins(),
            'sidebarView' => 'layouts.sidebar'
        ];

        return view('admin.assistant-admins.index', $data);
    }

    public function create()
    {
        return view('admin.assistant-admins.create', [
            'user' => $this->userService->getUserLoggedData(),
            'sidebarView' => 'layouts.sidebar'
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'username' => 'required|string|max:30|unique:users,username',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $newAssistant = $this->assistantAdminService->createAssistantAdmin($validated);

            ActivityLogger::log('CREATE', 'User Management', "Admin membuat akun Asisten Admin baru: {$validated['username']} ({$validated['email']})", ['user_id' => $newAssistant->id]);

            return redirect()
                ->route('admin.assistant-admins.index')
                ->with('success', 'Assistant admin berhasil dibuat.');
        } catch (\Exception $e) {
            Log::error('Create Assistant Admin error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat akun asisten admin. Silakan coba lagi.');
        }
    }

    public function edit(User $assistant_admin)
    {
        return view('admin.assistant-admins.edit', [
            'user' => $this->userService->getUserLoggedData(),
            'assistant_admin' => $assistant_admin,
            'sidebarView' => 'layouts.sidebar'
        ]);
    }

    public function update(Request $request, User $assistant_admin)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:30|unique:users,username,' . $assistant_admin->id,
            'email' => 'required|email|max:255|unique:users,email,' . $assistant_admin->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $this->assistantAdminService->updateAssistantAdmin($assistant_admin, $validated);

        ActivityLogger::log('UPDATE', 'User Management', "Admin memperbarui data akun Asisten Admin: {$assistant_admin->username}", ['user_id' => $assistant_admin->id]);

        return redirect()->route('admin.assistant-admins.index')
            ->with('success', 'Assistant admin updated successfully.');
    }

    public function destroy(User $assistant_admin)
    {
        $uname = $assistant_admin->username;
        $uid = $assistant_admin->id;
        $assistant_admin->delete();

        ActivityLogger::log('DELETE', 'User Management', "Admin menghapus akun Asisten Admin: {$uname}", ['user_id' => $uid]);

        return redirect()->route('admin.assistant-admins.index')
            ->with('success', 'Assistant admin deleted successfully.');
    }

    // =============================================
    // ASSISTANT ADMIN PANEL METHODS (ROLE 6)
    // =============================================

    public function dashboard()
    {
        // 1. Ambil data yang sudah ada dari service Anda
        $dashboardData = $this->assistantAdminService->getDashboardData();

        // 2. [PERBAIKAN PERFORMA] Menggabungkan 3 query count terpisah menjadi 1 conditional aggregation query
        $pendingPermits = PermitLog::whereNull('end_time')
            ->selectRaw("
                COUNT(CASE WHEN type = 'leave' THEN 1 END) as pending_leave,
                COUNT(CASE WHEN type = 'prayer' THEN 1 END) as pending_prayer,
                COUNT(CASE WHEN type = 'toilet' THEN 1 END) as pending_toilet
            ")
            ->first();

        // 3. Siapkan data baru untuk digabungkan
        $permitData = [
            'pendingIzinKeluarCount' => (int) ($pendingPermits->pending_leave ?? 0),
            'pendingIzinShalatCount' => (int) ($pendingPermits->pending_prayer ?? 0),
            'pendingIzinToiletCount' => (int) ($pendingPermits->pending_toilet ?? 0),
        ];

        // 4. Gabungkan semua data dan kirim ke view
        return view('assistant_admin.dashboard', array_merge(
            [
                'user' => Auth::user(),
                'sidebarView' => 'layouts.sidebar-assistant'
            ],
            $dashboardData, // Data dari service (cth: waitingStudents, pendingLogsCount, dll)
            $permitData     // Data izin yang baru kita hitung
        ));
    }
    public function raiseHandList()
    {
        return view('assistant_admin.raise-hand-list', [
            'user' => Auth::user(),
            'sidebarView' => 'layouts.sidebar-assistant'
        ]);
    }

    public function confirmHandRaise(Request $request, int $id)
    {
        if (auth()->check() && (int) auth()->user()->role_id === 6) {
            return redirect()->back()->with('error', 'Aksi ini hanya dapat dilakukan oleh Admin / Pembimbing Utama.');
        }

        try {
            $handRaise = HandRaise::with('user.profile')->findOrFail($id);
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

            $assistantUser = auth()->user();
            $assistantName = $assistantUser->name ?? $assistantUser->username ?? 'Asisten Admin';

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

                // Record or update the assistant admin's response message
                $lastAdminMsg = $handRaise->messages()->where('is_from_admin', true)->latest()->first();
                if ($lastAdminMsg && $handRaise->status === 'responded') {
                    $lastAdminMsg->update([
                        'message' => $adminResponse,
                        'user_id' => auth()->id(),
                    ]);
                } else {
                    \App\Models\HandRaiseMessage::create([
                        'hand_raise_id' => $handRaise->id,
                        'user_id' => auth()->id(),
                        'message' => $adminResponse,
                        'is_from_admin' => true,
                    ]);
                }

                $handRaise->update([
                    'status' => 'responded',
                    'admin_response' => $adminResponse,
                    'resolved_by' => auth()->id(),
                    'is_raised' => true,
                ]);

                ActivityLogger::log(
                    'RESPOND',
                    'Raise Hand',
                    "Asisten Admin {$assistantName} mengirimkan tanggapan bantuan untuk pemagang {$userName}",
                    ['hand_raise_id' => $handRaise->id, 'type' => 'question', 'action' => 'respond_question']
                );

                if ($request->ajax() || $request->wantsJson()) {
                    $lastMsg = $handRaise->messages()->latest()->first();
                    return response()->json([
                        'success' => true,
                        'message' => "Tanggapan berhasil dikirimkan ke {$userName}.",
                        'note' => [
                            'id' => $lastMsg?->id,
                            'sender_name' => auth()->user()->profile?->full_name ?? auth()->user()->name ?? 'Mentor',
                            'message' => $adminResponse,
                            'is_from_admin' => true,
                            'time' => now()->format('H:i'),
                        ],
                    ]);
                }

                return redirect()->route('assistant.raisehand.list', ['tab' => $targetTab])
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
                    "Asisten Admin {$assistantName} menyelesaikan bantuan/pertanyaan untuk pemagang {$userName}",
                    ['hand_raise_id' => $handRaise->id, 'type' => 'question', 'action' => 'complete_question']
                );

                return redirect()->route('assistant.raisehand.list', ['tab' => $targetTab])
                    ->with('success', "Bantuan/pertanyaan untuk {$userName} telah selesai.");
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
                    "Asisten Admin {$assistantName} menyelesaikan sesi permintaan tugas pemagang {$userName}",
                    ['hand_raise_id' => $handRaise->id, 'action' => 'complete_task']
                );

                return redirect()->route('assistant.raisehand.list', ['tab' => $targetTab])
                    ->with('success', "Permintaan tugas baru untuk {$userName} telah diselesaikan.");
            }

            if ($action === 'accept_presentation') {
                $pDate = $request->input('presentation_date') ?: ($handRaise->presentation_date?->toDateString() ?: today()->toDateString());
                $pTime = $request->input('scheduled_time') ?: $request->input('presentation_time');
                $pNotes = $request->input('notes') ?: $request->input('admin_response');

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
                    "Asisten Admin {$assistantName} menerima jadwal presentasi pemagang {$userName} pada {$pDate} jam {$timeFormatted}",
                    ['hand_raise_id' => $handRaise->id, 'action' => 'accept_presentation', 'date' => $pDate, 'time' => $pTime]
                );

                return redirect()->route('assistant.raisehand.list', ['tab' => $targetTab])
                    ->with('success', "Jadwal presentasi untuk {$userName} berhasil DITERIMA. Jadwal: {$pDate} pukul {$timeFormatted} WIB.");
            }

            if ($action === 'reschedule_presentation') {
                $pDate = $request->input('presentation_date') ?: ($handRaise->presentation_date?->toDateString() ?: today()->toDateString());
                $pTime = $request->input('scheduled_time') ?: $request->input('presentation_time');
                $pNotes = $request->input('notes') ?: $request->input('admin_response');

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
                    "Asisten Admin {$assistantName} menjadwalkan ulang presentasi pemagang {$userName} ke tanggal {$pDate} jam {$timeFormatted}",
                    ['hand_raise_id' => $handRaise->id, 'action' => 'reschedule_presentation', 'date' => $pDate, 'time' => $pTime]
                );

                return redirect()->route('assistant.raisehand.list', ['tab' => $targetTab])
                    ->with('success', "Jadwal presentasi untuk {$userName} berhasil DIJADWALKAN ULANG ke tanggal {$pDate} pukul {$timeFormatted} WIB.");
            }

            if ($action === 'reject_presentation') {
                $pNotes = $request->input('notes') ?: $request->input('admin_response') ?: 'Pengajuan presentasi ditolak oleh pembimbing.';

                $handRaise->update([
                    'status' => 'rejected',
                    'admin_response' => $pNotes,
                    'is_raised' => false,
                    'resolved_at' => now(),
                    'resolved_by' => auth()->id(),
                ]);

                \Illuminate\Support\Facades\Cache::forget('navbar_raise_hand_count');

                ActivityLogger::log(
                    'RESOLVE',
                    'Raise Hand',
                    "Asisten Admin {$assistantName} menolak pengajuan presentasi pemagang {$userName}",
                    ['hand_raise_id' => $handRaise->id, 'action' => 'reject_presentation', 'notes' => $pNotes]
                );

                return redirect()->route('assistant.raisehand.list', ['tab' => $targetTab])
                    ->with('success', "Pengajuan presentasi untuk {$userName} telah DITOLAK.");
            }

            if ($action === 'request_revision') {
                $handRaise->update([
                    'status' => 'needs_revision',
                    'resolved_by' => auth()->id(),
                    'is_raised' => true,
                ]);

                if ($handRaise->project) {
                    $handRaise->project->update(['status' => 'progress']);
                }

                ActivityLogger::log(
                    'UPDATE',
                    'Raise Hand',
                    "Asisten Admin {$assistantName} menetapkan status Presentasi pemagang {$userName}: Ada Revisi",
                    ['hand_raise_id' => $handRaise->id, 'action' => 'request_revision']
                );

                return redirect()->route('assistant.raisehand.list', ['tab' => $targetTab])
                    ->with('success', "Status presentasi {$userName} berhasil diatur: Ada Revisi.");
            }

            if ($action === 'ready_presentation') {
                $handRaise->update([
                    'status' => 'ready',
                    'resolved_by' => auth()->id(),
                    'is_raised' => true,
                ]);

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

                ActivityLogger::log(
                    'APPROVE',
                    'Raise Hand',
                    "Asisten Admin {$assistantName} mengesahkan Presentasi pemagang {$userName} Lulus Valid (Tanpa Revisi)",
                    ['hand_raise_id' => $handRaise->id, 'action' => 'ready_presentation']
                );

                return redirect()->route('assistant.raisehand.list', ['tab' => $targetTab])
                    ->with('success', "Status presentasi {$userName} dikonfirmasi: Tanpa Revisi (Selesai Valid).");
            }

            if ($action === 'complete_presentation') {
                $handRaise->update([
                    'is_raised' => false,
                    'resolved_at' => now(),
                    'resolved_by' => auth()->id(),
                ]);

                $project = $handRaise->project;
                if ($handRaise->status !== 'needs_revision' && $project) {
                    $project->update(['status' => 'done']);
                }

                ActivityLogger::log(
                    'RESOLVE',
                    'Raise Hand',
                    "Asisten Admin {$assistantName} menyelesaikan sesi presentasi pemagang {$userName}",
                    ['hand_raise_id' => $handRaise->id, 'status' => $handRaise->status]
                );

                return redirect()->route('assistant.raisehand.list', ['tab' => $targetTab])
                    ->with('success', "Sesi presentasi {$userName} berhasil diselesaikan.");
            }

            // Fallback default: Mark done
            $handRaise->update([
                'status' => 'done',
                'is_raised' => false,
                'resolved_at' => now(),
                'resolved_by' => auth()->id(),
                'admin_response' => $request->input('admin_response') ?? $handRaise->admin_response,
            ]);

            ActivityLogger::log(
                'RESOLVE',
                'Raise Hand',
                "Asisten Admin {$assistantName} menyelesaikan permintaan Raise Hand pemagang {$userName}",
                ['hand_raise_id' => $handRaise->id]
            );

            return redirect()->route('assistant.raisehand.list', ['tab' => $targetTab])
                ->with('success', "Permintaan bantuan untuk {$userName} berhasil diselesaikan.");
        } catch (\Throwable $th) {
            Log::error('Assistant Confirm Raise Hand error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return redirect()->route('assistant.raisehand.list')
                ->with('error', 'Terjadi kesalahan saat memproses data: ' . $th->getMessage());
        }
    }

    public function confirmRaiseHandForm(int $id)
    {
        $handRaise = HandRaise::with([
            'user.profile',
            'user.intern.detailProject.project.nameProject'
        ])->findOrFail($id);

        return view('assistant_admin.confirm-raise-hand', [
            'handRaise' => $handRaise,
            'sidebarView' => 'layouts.sidebar-assistant'
        ]);
    }

    public function confirmRaiseHandAction(Request $request, int $id)
    {
        if (auth()->check() && (int) auth()->user()->role_id === 6) {
            return redirect()->back()->with('error', 'Aksi ini hanya dapat dilakukan oleh Admin / Pembimbing Utama.');
        }

        try {
            $handRaise = HandRaise::with('user.profile')->findOrFail($id);
            $userName = $handRaise->user->profile->full_name ?? $handRaise->user->name ?? 'Pemagang';
            $assistantName = auth()->user()->name ?? auth()->user()->username ?? 'Asisten Admin';
            
            $handRaise->delete();

            ActivityLogger::log('RESOLVE', 'Raise Hand', "Asisten Admin {$assistantName} mengonfirmasi penyelesaian Raise Hand pemagang {$userName}", ['hand_raise_id' => $id]);

            return redirect()->route('assistant.raisehand.list')
                ->with('success', 'Permintaan Raise Hand telah berhasil dikonfirmasi.');
        } catch (\Exception $e) {
            Log::error('Confirm Raise Hand error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('assistant.raisehand.list')
                ->with('error', 'Gagal mengonfirmasi permintaan bantuan. Silakan coba lagi.');
        }
    }

    public function logActivity(Request $request)
    {
        $selectedMonth = $request->query('month', now()->format('Y-m'));
        $selectedDate = $request->query('date', now()->toDateString());
        $status = $request->query('status', 'pending');

        $logData = $this->assistantAdminService->getLogActivities($selectedMonth, $selectedDate, $status);

        return view('assistant_admin.log_activity', array_merge([
            'user' => Auth::user(),
            'sidebarView' => 'layouts.sidebar-assistant',
            'selectedDate' => $selectedDate,
            'selectedMonth' => $selectedMonth,
            'status' => $status,
        ], $logData));
    }

    // === METHOD approveLog DIPERBAIKI ===
    public function approveLog(Request $request, LogActivity $log)
    {
        $log->loadMissing('detailSchedule.schedule.intern.user.profile');

        // Menggunakan nama status yang konsisten: 'Accepted'
        $approvedStatus = Status::where('name', 'Accepted')->first();
        if ($approvedStatus) {
            $log->update(['status_id' => $approvedStatus->id]);

            $internName = $log->detailSchedule?->schedule?->intern?->user?->profile?->full_name ?? 'Pemagang';
            $assistantName = auth()->user()->name ?? auth()->user()->username ?? 'Asisten Admin';
            ActivityLogger::log('APPROVE', 'Logbook', "Asisten Admin {$assistantName} menyetujui logbook harian pemagang {$internName}", ['log_activity_id' => $log->id]);

            // Ambil tanggal dari request untuk redirect yang benar
            $redirectDate = $request->input('date', now()->toDateString());

            return redirect()->route('assistant.logactivity', ['date' => $redirectDate])
                ->with('success', 'Log aktivitas berhasil disetujui.');
        }
        return back()->with('error', 'Status "Accepted" tidak ditemukan di database.');
    }

    // === METHOD rejectLog DIPERBAIKI ===
    public function rejectLog(Request $request, LogActivity $log)
    {
        $log->loadMissing('detailSchedule.schedule.intern.user.profile');

        // Menggunakan nama status yang konsisten: 'Rejected'
        $rejectedStatus = Status::where('name', 'Rejected')->first();
        if ($rejectedStatus) {
            $log->update(['status_id' => $rejectedStatus->id]);

            $internName = $log->detailSchedule?->schedule?->intern?->user?->profile?->full_name ?? 'Pemagang';
            $assistantName = auth()->user()->name ?? auth()->user()->username ?? 'Asisten Admin';
            ActivityLogger::log('REJECT', 'Logbook', "Asisten Admin {$assistantName} menolak logbook harian pemagang {$internName}", ['log_activity_id' => $log->id]);

            // Ambil tanggal dari request untuk redirect yang benar
            $redirectDate = $request->input('date', now()->toDateString());

            return redirect()->route('assistant.logactivity', ['date' => $redirectDate])
                ->with('success', 'Log aktivitas berhasil ditolak.');
        }
        return back()->with('error', 'Status "Rejected" tidak ditemukan di database.');
    }

    public function confirmLogActivity(LogActivity $log)
    {
        $log->load([
            'detailSchedule.schedule.intern.user.profile',
            'detailSchedule.schedule.intern.school',
            'status'
        ]);

        return view('assistant_admin.confirm_log_activity', [
            'user' => Auth::user(),
            'log' => $log,
            'sidebarView' => 'layouts.sidebar-assistant'
        ]);
    }

    // === METHOD updateLogActivity DIPERBAIKI TOTAL ===
    public function updateLogActivity(Request $request, int $id)
    {
        // Validasi diperluas untuk menangani semua aksi dari halaman konfirmasi
        $request->validate([
            'activity' => 'required|string',
            'action' => 'required|in:approve,reject,update_text',
            'redirect_date' => 'required|date_format:Y-m-d', // Validasi tanggal dari hidden input
        ]);

        try {
            DB::beginTransaction();

            $log = LogActivity::with('detailSchedule.schedule.intern.user.profile')->findOrFail($id);

            // Perbarui teks aktivitas & catatan (selalu dilakukan untuk semua aksi)
            $log->activity = $request->activity;
            if ($request->has('assistant_notes')) {
                $log->assistant_notes = $request->assistant_notes;
            }

            // Logika untuk mengubah status berdasarkan aksi
            $action = $request->action;
            $message = 'Perubahan pada log aktivitas berhasil disimpan.'; // Pesan default
            $internName = $log->detailSchedule?->schedule?->intern?->user?->profile?->full_name ?? 'Pemagang';
            $assistantName = auth()->user()->name ?? auth()->user()->username ?? 'Asisten Admin';

            if ($action === 'approve') {
                $status = Status::where('name', 'Accepted')->firstOrFail();
                $log->status_id = $status->id;
                $message = 'Log aktivitas berhasil disetujui.';
                ActivityLogger::log('APPROVE', 'Logbook', "Asisten Admin {$assistantName} menyetujui logbook pemagang {$internName}", ['log_activity_id' => $log->id]);
            } elseif ($action === 'reject') {
                $status = Status::where('name', 'Rejected')->firstOrFail();
                $log->status_id = $status->id;
                $message = 'Log aktivitas berhasil ditolak.';
                ActivityLogger::log('REJECT', 'Logbook', "Asisten Admin {$assistantName} menolak logbook pemagang {$internName}", ['log_activity_id' => $log->id]);
            } else {
                ActivityLogger::log('UPDATE', 'Logbook', "Asisten Admin {$assistantName} memperbarui teks logbook pemagang {$internName}", ['log_activity_id' => $log->id]);
            }

            $log->save(); // Simpan semua perubahan ke database

            DB::commit();

            // Redirect kembali ke halaman daftar log dengan tanggal yang benar
            $redirectDate = $request->input('redirect_date');

            return redirect()->route('assistant.logactivity', ['date' => $redirectDate])
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Update Log Activity error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Terjadi kesalahan saat memperbarui log aktivitas. Silakan coba lagi.')->withInput();
        }
    }

    public function izinLeave()
    {
        $perPage = 25;
        $paginatedInterns = $this->assistantAdminService->getInternsWithPermits('leave', $perPage);

        return view('assistant_admin.izinKeluar', [
            'user'        => Auth::user(),
            'interns'     => $paginatedInterns,
            'sidebarView' => 'layouts.sidebar-assistant'
        ]);
    }

    public function getLeaveDuration(PermitLog $permitLog)
    {
        $result = $this->assistantAdminService->getPermitDuration($permitLog, 'leave');
        if (isset($result['error'])) {
            return response()->json($result, 400);
        }
        return response()->json($result);
    }


    public function showLeaveHistory(Intern $intern)
    {
        $perPage = 15;
        $permitLogs = $this->assistantAdminService->getPermitHistory($intern, 'leave', $perPage);

        return view('assistant_admin.keluar-history', [
            'intern'      => $intern,
            'permitLogs'  => $permitLogs,
            'user'        => Auth::user(),
            'sidebarView' => 'layouts.sidebar-assistant',
        ]);
    }

    public function izinPrayer()
    {
        $perPage = 25;
        $paginatedInterns = $this->assistantAdminService->getInternsWithPermits('prayer', $perPage);

        return view('assistant_admin.izinShalat', [
            'user'       => Auth::user(),
            'interns'    => $paginatedInterns,
            'sidebarView' => 'layouts.sidebar-assistant'
        ]);
    }

    public function getPrayerDuration(PermitLog $permitLog)
    {
        $result = $this->assistantAdminService->getPermitDuration($permitLog, 'prayer');
        if (isset($result['error'])) {
            return response()->json($result, 400);
        }
        return response()->json($result);
    }

    public function showPrayerHistory(Intern $intern)
    {
        $perPage = 15;
        $permitLogs = $this->assistantAdminService->getPermitHistory($intern, 'prayer', $perPage);

        return view('assistant_admin.sholat-history', [
            'intern'      => $intern,
            'permitLogs'  => $permitLogs,
            'user'        => Auth::user(),
            'sidebarView' => 'layouts.sidebar-assistant',
        ]);
    }

    public function izinToilet()
    {
        $perPage = 25;
        $paginatedInterns = $this->assistantAdminService->getInternsWithPermits('toilet', $perPage);

        return view('assistant_admin.izinToilet', [
            'user'       => Auth::user(),
            'interns'    => $paginatedInterns,
            'sidebarView' => 'layouts.sidebar-assistant'
        ]);
    }

    public function getToiletDuration(PermitLog $permitLog)
    {
        $result = $this->assistantAdminService->getPermitDuration($permitLog, 'toilet');
        if (isset($result['error'])) {
            return response()->json($result, 400);
        }
        return response()->json($result);
    }

    public function showToiletHistory(Intern $intern)
    {
        $perPage = 15;
        $permitLogs = $this->assistantAdminService->getPermitHistory($intern, 'toilet', $perPage);

        return view('assistant_admin.toilet-history', [
            'intern'      => $intern,
            'permitLogs'  => $permitLogs,
            'user'        => Auth::user(),
            'sidebarView' => 'layouts.sidebar-assistant',
        ]);
    }
}
