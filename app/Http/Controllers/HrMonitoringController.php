<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PrayerPermitService;
use App\Services\ToiletPermitService;

class HrMonitoringController extends Controller
{
    protected PrayerPermitService $prayerPermitService;
    protected ToiletPermitService $toiletPermitService;

    public function __construct(PrayerPermitService $prayerPermitService, ToiletPermitService $toiletPermitService)
    {
        $this->prayerPermitService = $prayerPermitService;
        $this->toiletPermitService = $toiletPermitService;

        $this->middleware(function ($request, $next) {
            if (!Auth::user()->intern || !Auth::user()->intern->division || Auth::user()->intern->division->name !== 'Human Resource') {
                abort(403, 'Akses ditolak. Hanya untuk divisi Human Resource.');
            }
            return $next($request);
        });
    }

    public function monitorToilet()
    {
        $presentInternIds = Attendance::where('date', today())->pluck('intern_id');

        $interns = Intern::whereIn('id', $presentInternIds)
            ->with(['user.profile', 'division', 'activePermitLog'])
            ->paginate(25);

        return view('hr.monitor-toilet', [
            'user' => Auth::user(),
            'interns' => $interns,
            'day_now' => $this->getIndonesianDay(),
            'date_now' => $this->getFormattedDate(),
        ]);
    }

    public function monitorPrayer()
    {
        $presentInternIds = Attendance::where('date', today())->pluck('intern_id');

        $interns = Intern::whereIn('id', $presentInternIds)
            ->with(['user.profile', 'division', 'activePermitLog'])
            ->paginate(25);

        return view('hr.monitor-prayer', [
            'user' => Auth::user(),
            'interns' => $interns,
            'day_now' => $this->getIndonesianDay(),
            'date_now' => $this->getFormattedDate(),
        ]);
    }

    private function getIndonesianDay()
    {
        return now()->translatedFormat('l');
    }

    private function getFormattedDate()
    {
        return now()->translatedFormat('d F Y');
    }

    /**
     * Menampilkan halaman detail history izin toilet untuk seorang intern.
     * (DIPERBAIKI: Menambahkan variabel $user)
     */
    public function toiletHistoryDetail(Intern $intern)
    {
        $perPage = 15;
        $permitLogs = $this->toiletPermitService->getToiletHistory($intern, $perPage);

        return view('hr.toilet-history-detail', [
            'intern' => $intern,
            'permitLogs' => $permitLogs,
            'day_now' => $this->getIndonesianDay(),
            'date_now' => $this->getFormattedDate(),
            'user' => Auth::user(), // <-- DITAMBAHKAN
        ]);
    }

    /**
     * Menampilkan halaman detail history izin sholat untuk seorang intern.
     * (DIPERBAIKI: Menambahkan variabel $user)
     */
    public function prayerHistoryDetail(Intern $intern)
    {
        $perPage = 15;
        $permitLogs = $this->prayerPermitService->getPrayerHistory($intern, $perPage);

        return view('hr.prayer-history-detail', [
            'intern' => $intern,
            'permitLogs' => $permitLogs,
            'day_now' => $this->getIndonesianDay(),
            'date_now' => $this->getFormattedDate(),
            'user' => Auth::user(), // <-- DITAMBAHKAN
        ]);
    }
}
