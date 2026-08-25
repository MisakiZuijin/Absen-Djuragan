<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use App\Models\PermitLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\LeavePermitService;

class AdminIzinKeluarController extends Controller
{
    protected $leavePermitService;

    public function __construct(LeavePermitService $leavePermitService)
    {
        $this->leavePermitService = $leavePermitService;
    }

    /**
     * Menampilkan halaman monitoring izin keluar dengan paginasi.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Tentukan jumlah item per halaman.
        $perPage = 25;

        // Panggil service dengan parameter jumlah item per halaman.
        $paginatedInterns = $this->leavePermitService->getTodayInternsWithLeavePermits($perPage);

        return view('admin.izin-keluar', [
            'user'        => Auth::user(),
            'interns'     => $paginatedInterns,
            'sidebarView' => 'layouts.sidebar'
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
     * Menampilkan halaman detail history izin keluar untuk seorang intern.
     *
     * @param \App\Models\Intern $intern
     * @return \Illuminate\View\View
     */
    public function showKeluarHistoryDetail(Intern $intern)
    {
        // [DISESUAIKAN] Terapkan pola paginasi yang sama di sini untuk konsistensi.
        $perPage = 15;

        // Panggil service untuk mendapatkan data history yang sudah dipaginasi.
        $permitLogs = $this->leavePermitService->getLeaveHistory($intern, $perPage);

        return view('admin.keluar-history-detail', [
            'intern'      => $intern,
            'permitLogs'  => $permitLogs,
            'user'        => Auth::user(),
            'sidebarView' => 'layouts.sidebar',
        ]);
    }
}