<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use App\Models\PermitLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\LeavePermitService;
use App\Helper\ActivityLogger;

class AdminIzinKeluarController extends Controller
{
    protected LeavePermitService $leavePermitService;

    public function __construct(LeavePermitService $leavePermitService)
    {
        $this->leavePermitService = $leavePermitService;
    }

    /**
     * Menampilkan halaman monitoring izin keluar dengan paginasi dan statistik.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $statusFilter = $request->query('status');
        $search = $request->query('search');
        $perPage = 10;

        $paginatedInterns = $this->leavePermitService->getTodayInternsWithLeavePermits($perPage, $statusFilter, $search);
        $summaryMetrics = $this->leavePermitService->getSummaryMetrics();

        return view('admin.izin-keluar', [
            'user'             => Auth::user(),
            'interns'          => $paginatedInterns,
            'summaryMetrics'   => $summaryMetrics,
            'statusFilter'     => $statusFilter,
            'search'           => $search,
            'sidebarView'      => 'layouts.sidebar'
        ]);
    }

    /**
     * Mengambil durasi real-time untuk izin keluar.
     *
     * @param \App\Models\PermitLog $permitLog
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLeaveDuration(PermitLog $permitLog)
    {
        $result = $this->leavePermitService->getLeaveDuration($permitLog);

        if (isset($result['error'])) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * Menyetujui izin keluar dengan menetapkan durasi yang disepakati
     * dan apakah pemagang wajib mengganti jam (acuan hutang waktu).
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\PermitLog $permitLog
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveLeavePermit(Request $request, PermitLog $permitLog)
    {
        abort_unless($permitLog->type === 'leave', 404);

        $validated = $request->validate([
            'agreed_duration_minutes' => 'nullable|integer|min:0|max:480',
            'is_mandatory_replace'    => 'required|boolean',
            'admin_notes'             => 'nullable|string|max:500',
        ]);

        $admin = $request->user();
        $isMandatory = (bool) $validated['is_mandatory_replace'];
        $agreedMinutes = $isMandatory
            ? ((int) ($validated['agreed_duration_minutes'] ?? $permitLog->duration_in_minutes ?? 0))
            : 0;

        $data = [
            'authorized_by'           => $admin->profile->full_name ?? $admin->name ?? $admin->username,
            'agreed_duration_minutes' => $agreedMinutes,
            'is_mandatory_replace'    => $isMandatory,
            'approval_status'         => 'approved',
        ];

        if (!empty($validated['admin_notes'])) {
            $data['description'] = $validated['admin_notes'];
        }

        $permitLog->update($data);

        $internName = $permitLog->attendance?->intern?->user?->profile?->full_name
            ?? $permitLog->attendance?->intern?->user?->name
            ?? 'Pemagang';
        $adminName = $admin->profile?->full_name ?? $admin->name ?? $admin->username;
        $statusMsg = $isMandatory ? "Wajib Ganti Jam ({$agreedMinutes} menit)" : "Bebas Waktu (Tanpa Ganti Jam)";

        ActivityLogger::log('APPROVE', 'Izin', "Admin {$adminName} memproses Izin Keluar pemagang {$internName}: {$statusMsg}", [
            'permit_log_id'        => $permitLog->id,
            'agreed_duration'      => $agreedMinutes,
            'is_mandatory_replace' => $isMandatory
        ]);

        return redirect()->back()->with('success', "Izin keluar pemagang {$internName} berhasil disetujui ({$statusMsg}).");
    }

    /**
     * Aksi Cepat: Memberikan status Bebas Waktu (Diizinkan tanpa perlu ganti jam).
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\PermitLog $permitLog
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bebasWaktu(Request $request, PermitLog $permitLog)
    {
        abort_unless($permitLog->type === 'leave', 404);

        $admin = $request->user();
        $adminName = $admin->profile?->full_name ?? $admin->name ?? $admin->username;

        $permitLog->update([
            'authorized_by'           => $adminName,
            'agreed_duration_minutes' => 0,
            'is_mandatory_replace'    => false,
            'approval_status'         => 'approved',
        ]);

        $internName = $permitLog->attendance?->intern?->user?->profile?->full_name
            ?? $permitLog->attendance?->intern?->user?->name
            ?? 'Pemagang';

        ActivityLogger::log('APPROVE', 'Izin', "Admin {$adminName} menetapkan Bebas Waktu pada Izin Keluar pemagang {$internName}", [
            'permit_log_id' => $permitLog->id,
            'is_mandatory_replace' => false,
        ]);

        return redirect()->back()->with('success', "Izin keluar pemagang {$internName} berhasil disetujui sebagai Bebas Waktu (Bebas Ganti Jam).");
    }

    /**
     * Aksi: Menetapkan Wajib Ganti Waktu untuk pemagang.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\PermitLog $permitLog
     * @return \Illuminate\Http\RedirectResponse
     */
    public function wajibGantiWaktu(Request $request, PermitLog $permitLog)
    {
        abort_unless($permitLog->type === 'leave', 404);

        $validated = $request->validate([
            'minutes'     => 'nullable|integer|min:1|max:480',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $admin = $request->user();
        $adminName = $admin->profile?->full_name ?? $admin->name ?? $admin->username;

        $minutes = (int) ($validated['minutes'] ?? $permitLog->duration_in_minutes ?? 15);
        if ($minutes <= 0) $minutes = 15;

        $data = [
            'authorized_by'           => $adminName,
            'agreed_duration_minutes' => $minutes,
            'is_mandatory_replace'    => true,
            'approval_status'         => 'approved',
        ];

        if (!empty($validated['admin_notes'])) {
            $data['description'] = $validated['admin_notes'];
        }

        $permitLog->update($data);

        $internName = $permitLog->attendance?->intern?->user?->profile?->full_name
            ?? $permitLog->attendance?->intern?->user?->name
            ?? 'Pemagang';

        ActivityLogger::log('APPROVE', 'Izin', "Admin {$adminName} menetapkan Wajib Ganti Waktu {$minutes} menit pada Izin Keluar pemagang {$internName}", [
            'permit_log_id' => $permitLog->id,
            'agreed_duration_minutes' => $minutes,
            'is_mandatory_replace' => true,
        ]);

        return redirect()->back()->with('success', "Izin keluar pemagang {$internName} ditetapkan Wajib Ganti Waktu: {$minutes} menit.");
    }

    /**
     * Menolak izin keluar (leave) yang diajukan pemagang.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\PermitLog $permitLog
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rejectLeavePermit(Request $request, PermitLog $permitLog)
    {
        abort_unless($permitLog->type === 'leave', 404);

        $admin = $request->user();

        $data = [
            'authorized_by'   => $admin->profile->full_name ?? $admin->name ?? $admin->username,
            'approval_status' => 'rejected',
        ];

        if (is_null($permitLog->end_time)) {
            $endTime = now();
            $data['end_time']            = $endTime->format('Y-m-d H:i:s');
            $data['duration_in_minutes'] = (int) ceil($permitLog->start_time->diffInSeconds($endTime) / 60);
        }

        $permitLog->update($data);

        $internName = $permitLog->attendance?->intern?->user?->profile?->full_name ?? 'Pemagang';
        $adminName = $admin->profile?->full_name ?? $admin->name ?? $admin->username;
        ActivityLogger::log('REJECT', 'Izin', "Admin {$adminName} menolak Izin Keluar pemagang {$internName}", [
            'permit_log_id' => $permitLog->id
        ]);

        return redirect()->back()->with('success', 'Izin keluar telah ditolak.');
    }

    /**
     * Menampilkan halaman detail history izin keluar untuk seorang intern.
     *
     * @param \App\Models\Intern $intern
     * @return \Illuminate\View\View
     */
    public function showKeluarHistoryDetail(Intern $intern)
    {
        $perPage = 15;
        $permitLogs = $this->leavePermitService->getLeaveHistory($intern, $perPage);

        return view('admin.keluar-history-detail', [
            'intern'      => $intern,
            'permitLogs'  => $permitLogs,
            'user'        => Auth::user(),
            'sidebarView' => 'layouts.sidebar',
        ]);
    }
}
