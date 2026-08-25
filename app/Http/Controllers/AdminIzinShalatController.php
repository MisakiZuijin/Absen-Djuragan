<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use App\Models\PermitLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PrayerPermitService;

class AdminIzinShalatController extends Controller
{
    protected $prayerPermitService;

    public function __construct(PrayerPermitService $prayerPermitService)
    {
        $this->prayerPermitService = $prayerPermitService;
    }

    /**
     * Menampilkan halaman monitoring izin shalat dengan paginasi.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Tentukan jumlah item per halaman. 25 adalah nilai standar yang baik.
        $perPage = 25;

        // Panggil service dengan parameter jumlah item per halaman.
        $paginatedInterns = $this->prayerPermitService->getTodayInternsWithPrayerPermits($perPage);

        return view('admin.izin-shalat', [
            'user' => Auth::user(),
            'interns' => $paginatedInterns,
        ]);
    }

    /**
     * Mengambil durasi real-time untuk izin shalat.
     *
     * @param \App\Models\PermitLog $permitLog
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPrayerDuration(PermitLog $permitLog)
    {
        $result = $this->prayerPermitService->getPrayerDuration($permitLog);

        if (isset($result['error'])) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * Menampilkan halaman detail history izin shalat untuk seorang intern.
     *
     * @param \App\Models\Intern $intern
     * @return \Illuminate\View\View
     */
    public function prayerHistoryDetail(Intern $intern)
    {
        // [DISESUAIKAN] Terapkan pola paginasi yang sama di sini untuk konsistensi.
        $perPage = 15;

        // Panggil service untuk mendapatkan data history yang sudah dipaginasi.
        $permitLogs = $this->prayerPermitService->getPrayerHistory($intern, $perPage);

        return view('admin.prayer-history-detail', [
            'intern' => $intern,
            'permitLogs' => $permitLogs
        ]);
    }
}