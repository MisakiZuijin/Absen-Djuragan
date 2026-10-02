<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use App\Models\PermitLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ToiletPermitService;

class AdminIzinToiletController extends Controller
{
    protected ToiletPermitService $toiletPermitService;

    public function __construct(ToiletPermitService $toiletPermitService)
    {
        $this->toiletPermitService = $toiletPermitService;
    }

    /**
     * Menampilkan halaman monitoring izin toilet dengan paginasi.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // [DIREKOMENDASIKAN] Tentukan jumlah item per halaman untuk penggunaan normal.
        // Angka 1 yang Anda gunakan sebelumnya sangat bagus untuk testing UI.
        // Sekarang kita kembalikan ke nilai standar, misalnya 25.
        $perPage = 25;

        // Panggil service dengan parameter jumlah item per halaman.
        $paginatedInterns = $this->toiletPermitService->getTodayInternsWithToiletPermits($perPage);

        return view('admin.izin-toilet', [
            'user' => Auth::user(),
            'interns' => $paginatedInterns,
        ]);
    }

    /**
     * Mengambil durasi real-time untuk izin toilet.
     *
     * @param \App\Models\PermitLog $permitLog
     * @return \Illuminate\Http\JsonResponse
     */
    public function getToiletDuration(PermitLog $permitLog)
    {
        $result = $this->toiletPermitService->getToiletDuration($permitLog);

        if (isset($result['error'])) {
            abort(404);
        }

        return response()->json($result);
    }

    /**
     * Menampilkan halaman detail history izin toilet untuk seorang intern.
     *
     * @param \App\Models\Intern $intern
     * @return \Illuminate\View\View
     */
    public function toiletHistoryDetail(Intern $intern)
    {
        // Jumlah item per halaman untuk halaman history, 15 sudah cukup baik.
        $perPage = 15;

        // Panggil service untuk mendapatkan data history yang sudah dipaginasi.
        $permitLogs = $this->toiletPermitService->getToiletHistory($intern, $perPage);

        return view('admin.toilet-history-detail', [
            'intern' => $intern,
            'permitLogs' => $permitLogs
        ]);
    }
}
