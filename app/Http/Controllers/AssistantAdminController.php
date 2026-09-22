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
use App\Services\UserService;
use App\Services\AssistantAdminService;

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

            $this->assistantAdminService->createAssistantAdmin($validated);

            return redirect()
                ->route('admin.assistant-admins.index')
                ->with('success', 'Assistant admin berhasil dibuat.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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

        return redirect()->route('admin.assistant-admins.index')
            ->with('success', 'Assistant admin updated successfully.');
    }

    public function destroy(User $assistant_admin)
    {
        $assistant_admin->delete();

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

        // 2. [PERBAIKAN FINAL] Menggunakan nama kolom 'type' dan logika 'end_time' IS NULL
        $pendingIzinKeluarCount = PermitLog::where('type', 'leave')->whereNull('end_time')->count();
        $pendingIzinShalatCount = PermitLog::where('type', 'prayer')->whereNull('end_time')->count();
        $pendingIzinToiletCount = PermitLog::where('type', 'toilet')->whereNull('end_time')->count();

        // 3. Siapkan data baru untuk digabungkan
        $permitData = [
            'pendingIzinKeluarCount' => $pendingIzinKeluarCount,
            'pendingIzinShalatCount' => $pendingIzinShalatCount,
            'pendingIzinToiletCount' => $pendingIzinToiletCount,
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
        $handRaises = $this->assistantAdminService->getRaiseHandList();

        return view('assistant_admin.raise-hand-list', [
            'user' => Auth::user(),
            'handRaises' => $handRaises,
            'sidebarView' => 'layouts.sidebar-assistant'
        ]);
    }

    public function confirmHandRaise(Request $request, int $id)
    {
        $this->assistantAdminService->confirmHandRaise($id);
        return back()->with('success', 'Raise hand siswa berhasil dikonfirmasi.');
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
        try {
            $handRaise = HandRaise::findOrFail($id);
            $handRaise->delete();

            return redirect()->route('assistant.raisehand.list')
                ->with('success', 'Permintaan Raise Hand telah berhasil dikonfirmasi.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengkonfirmasi. Terjadi kesalahan: ' . $e->getMessage());
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
        // Menggunakan nama status yang konsisten: 'Accepted'
        $approvedStatus = Status::where('name', 'Accepted')->first();
        if ($approvedStatus) {
            $log->update(['status_id' => $approvedStatus->id]);

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
        // Menggunakan nama status yang konsisten: 'Rejected'
        $rejectedStatus = Status::where('name', 'Rejected')->first();
        if ($rejectedStatus) {
            $log->update(['status_id' => $rejectedStatus->id]);

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

            $log = LogActivity::findOrFail($id);

            // Perbarui teks aktivitas & catatan (selalu dilakukan untuk semua aksi)
            $log->activity = $request->activity;
            if ($request->has('assistant_notes')) {
                $log->assistant_notes = $request->assistant_notes;
            }

            // Logika untuk mengubah status berdasarkan aksi
            $action = $request->action;
            $message = 'Perubahan pada log aktivitas berhasil disimpan.'; // Pesan default

            if ($action === 'approve') {
                $status = Status::where('name', 'Accepted')->firstOrFail();
                $log->status_id = $status->id;
                $message = 'Log aktivitas berhasil disetujui.';
            } elseif ($action === 'reject') {
                $status = Status::where('name', 'Rejected')->firstOrFail();
                $log->status_id = $status->id;
                $message = 'Log aktivitas berhasil ditolak.';
            }

            $log->save(); // Simpan semua perubahan ke database

            DB::commit();

            // Redirect kembali ke halaman daftar log dengan tanggal yang benar
            $redirectDate = $request->input('redirect_date');

            return redirect()->route('assistant.logactivity', ['date' => $redirectDate])
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
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
