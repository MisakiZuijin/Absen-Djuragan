<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use App\Models\Attendance;
use App\Models\Shift;
use App\Models\Office;
use App\Models\DetailSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\LogActivity;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class OutsiderDashboardController extends Controller
{
    /**
     * Menampilkan halaman utama dashboard presensi.
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user->outsider) {
            abort(403, 'Akses tidak valid.');
        }

        // Ambil semua ID intern yang dimiliki outsider
        $internIds = $user->outsider->interns()->pluck('interns.id');

        // Ambil data presensi hari ini
        $presensiHariIni = DetailSchedule::whereHas('schedule', function ($query) use ($internIds) {
            $query->whereIn('intern_id', $internIds);
        })
            ->whereDate('date', Carbon::today())
            ->with([
                'schedule.intern.user.profile',
                'schedule.intern.school',
                'schedule.office',
                'attendance',
                'attdStatus',
                'shift',
                'logActivity'
            ])
            ->get();

        // Hitung jumlah berdasarkan status kehadiran
        $totalHadir = $presensiHariIni->where('attd_status_id', 2)->count();
        $totalIzin = $presensiHariIni->where('attd_status_id', 3)->count();
        $totalAlpha = $presensiHariIni->where('attd_status_id', 5)->count();

        // Hitung jumlah berdasarkan gender dari presensi hari ini
        $jumlahLaki = $presensiHariIni->filter(function ($detail) {
            return optional($detail->schedule->intern->user)->profile &&
                $detail->schedule->intern->user->profile->gender === 'Laki-laki';
        })->count();

        $jumlahPerempuan = $presensiHariIni->filter(function ($detail) {
            return optional($detail->schedule->intern->user)->profile &&
                $detail->schedule->intern->user->profile->gender === 'Perempuan';
        })->count();

        $dateNow = Carbon::today()->translatedFormat('d F Y');
        $dateNowYMD = Carbon::today()->format('Y-m-d');

        $shifts = Shift::all();
        $offices = Office::all();
        $attd_statuses = [
            ['id' => 2, 'name' => 'Hadir'],
            ['id' => 3, 'name' => 'Izin'],
            ['id' => 4, 'name' => 'Sakit'],
            ['id' => 5, 'name' => 'Alpha'],
        ];

        return view('outsiders.presensi.index', compact(
            'presensiHariIni',
            'totalHadir',
            'totalIzin',
            'totalAlpha',
            'dateNow',
            'dateNowYMD',
            'shifts',
            'offices',
            'attd_statuses',
            'jumlahLaki',
            'jumlahPerempuan'
        ));
    }

    /**
     * Menampilkan detail presensi untuk satu mahasiswa.
     */
    /**
     * Menampilkan detail presensi untuk satu mahasiswa.
     */
    public function show(int $intern_id, Request $request)
    {
        $user = auth()->user();
        if (!$user->outsider) {
            abort(403, 'Akses tidak valid.');
        }

        $internIds = $user->outsider->interns()->pluck('interns.id');
        if (!$internIds->contains($intern_id)) {
            abort(403, 'Anda tidak berhak mengakses data ini.');
        }

        $pemagang = Intern::with(['user.profile', 'school'])->findOrFail($intern_id);

        // Ambil parameter bulan dan tahun dari request
        $selectedMonth = $request->input('month', Carbon::now()->format('m'));
        $selectedYear = $request->input('year', Carbon::now()->format('Y'));

        // Validasi bulan dan tahun
        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = Carbon::now()->format('m');
        }
        if ($selectedYear < 2020 || $selectedYear > Carbon::now()->addYears(5)->format('Y')) {
            $selectedYear = Carbon::now()->format('Y');
        }

        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];

        $currentYear = Carbon::now()->format('Y');
        $years = range($currentYear - 2, $currentYear + 1);

        // Hitung bulan sebelumnya dan selanjutnya
        $currentDate = Carbon::create($selectedYear, $selectedMonth, 1);
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();

        // Batasi next month agar tidak melebihi bulan sekarang
        if ($nextMonth->greaterThan(Carbon::now()->endOfMonth())) {
            $nextMonth = null;
        }

        // Batasi prev month agar tidak kurang dari 2020
        if ($prevMonth->year < 2020) {
            $prevMonth = null;
        }

        // Ambil semua jadwal untuk bulan yang dipilih (termasuk yang belum ada presensinya)
        $startDate = Carbon::create($selectedYear, $selectedMonth, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Ambil jadwal yang sudah ada
        $riwayatPresensi = DetailSchedule::whereHas('schedule', function ($query) use ($intern_id) {
            $query->where('intern_id', $intern_id);
        })
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with([
                'schedule.office',
                'attendance',
                'attdStatus',
                'shift'
            ])
            ->orderBy('date', 'desc')
            ->get();

        // Generate semua hari dalam bulan tersebut untuk memastikan tampilan lengkap
        $allDaysInMonth = collect();
        $currentDay = $startDate->copy();

        while ($currentDay->lte($endDate)) {
            $existingPresensi = $riwayatPresensi->firstWhere('date', $currentDay->format('Y-m-d'));

            if (!$existingPresensi) {
                // Buat record dummy untuk hari yang tidak ada jadwalnya
                $dummyPresensi = new DetailSchedule([
                    'date' => $currentDay->format('Y-m-d'),
                    'attd_status_id' => null,
                ]);

                // Tambahkan relasi kosong
                $dummyPresensi->setRelation('attendance', null);
                $dummyPresensi->setRelation('attdStatus', null);
                $dummyPresensi->setRelation('shift', null);
                $dummyPresensi->setRelation('schedule', (object)[
                    'office' => (object)['name' => '-']
                ]);

                $allDaysInMonth->push($dummyPresensi);
            } else {
                $allDaysInMonth->push($existingPresensi);
            }

            $currentDay->addDay();
        }

        // Urutkan berdasarkan tanggal (desc) dan status
        $riwayatPresensi = $allDaysInMonth->sortByDesc('date')->values();

        // Hitung statistik presensi
        $stats = $this->hitungStatistikPresensi($riwayatPresensi);

        return view('outsiders.presensi.show', compact(
            'pemagang',
            'riwayatPresensi',
            'stats',
            'months',
            'years',
            'selectedMonth',
            'selectedYear',
            'prevMonth',
            'nextMonth'
        ));
    }

    /**
     * Hitung statistik presensi
     */
    /**
     * Hitung statistik presensi (tidak termasuk hari Minggu)
     */
    private function hitungStatistikPresensi(Collection $riwayatPresensi)
    {
        $total = 0;
        $hadir = 0;
        $izin = 0;
        $sakit = 0;
        $tidakHadir = 0;
        $libur = 0; // Menghitung hari Minggu sebagai libur
        $currentDate = Carbon::now();

        foreach ($riwayatPresensi as $presensi) {
            $presensiDate = Carbon::parse($presensi->date);
            $isSunday = $presensiDate->isSunday(); // Cek apakah hari Minggu

            // Abaikan hari Minggu (libur)
            if ($isSunday) {
                $libur++;
                continue;
            }

            $isFuture = $presensiDate->isFuture();

            // Hanya hitung hari yang sudah lewat atau hari ini (bukan hari Minggu)
            if (!$isFuture) {
                $total++;

                if ($presensi->attd_status_id == 2) {
                    $hadir++;
                } elseif ($presensi->attd_status_id == 3) {
                    $izin++;
                } elseif ($presensi->attd_status_id == 4) {
                    $sakit++;
                } elseif ($presensi->attd_status_id == 5) {
                    $tidakHadir++;
                } else {
                    // Jika tidak ada status dan sudah lewat dari hari ini
                    if ($presensiDate->isPast()) {
                        $tidakHadir++;
                    }
                }
            }
        }

        return [
            'total' => $total,
            'hadir' => $hadir,
            'izin' => $izin,
            'sakit' => $sakit,
            'tidak_hadir' => $tidakHadir,
            'libur' => $libur
        ];
    }
    /**
     * API endpoint untuk memfilter data presensi secara dinamis (AJAX).
     */
    public function filterData(Request $request)
    {
        $user = auth()->user();
        if (!$user->outsider) {
            return response()->json(['error' => 'Akses tidak valid.'], 403);
        }

        $internIds = $user->outsider->interns()->pluck('interns.id');

        $query = DetailSchedule::query()->whereHas('schedule', function ($q) use ($internIds) {
            $q->whereIn('intern_id', $internIds);
        });

        // Filter berdasarkan tanggal
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $query->whereDate('date', Carbon::parse($date));

        // Filter berdasarkan status kehadiran
        if ($request->filled('attd_status_id')) {
            $query->where('attd_status_id', $request->input('attd_status_id'));
        }

        // Filter berdasarkan shift
        if ($request->filled('shift_id')) {
            $query->where('shift_id', $request->input('shift_id'));
        }

        // Filter berdasarkan kantor
        if ($request->filled('office_id')) {
            $query->whereHas('schedule', function ($q) use ($request) {
                $q->where('office_id', $request->input('office_id'));
            });
        }

        // Filter berdasarkan nama
        if ($request->filled('name')) {
            $name = $request->input('name');
            $query->whereHas('schedule.intern.user.profile', function ($q) use ($name) {
                $q->where('full_name', 'LIKE', '%' . $name . '%');
            });
        }

        // Eager loading yang konsisten
        $presensi = $query->with([
            'schedule.intern.user.profile',
            'schedule.intern.school',
            'schedule.office',
            'attendance',
            'attdStatus',
            'shift',
            'logActivity'
        ])->get();

        // Hitung total berdasarkan status
        $totalHadir = $presensi->where('attd_status_id', 2)->count();
        $totalIzin = $presensi->where('attd_status_id', 3)->count();
        $totalAlpha = $presensi->where('attd_status_id', 5)->count();

        // Hitung berdasarkan gender
        $maleCount = $presensi->filter(function ($p) {
            return optional($p->schedule->intern->user)->profile &&
                $p->schedule->intern->user->profile->gender === 'Laki-laki';
        })->count();

        $femaleCount = $presensi->filter(function ($p) {
            return optional($p->schedule->intern->user)->profile &&
                $p->schedule->intern->user->profile->gender === 'Perempuan';
        })->count();

        return response()->json([
            'presensi' => $presensi,
            'totalHadir' => $totalHadir,
            'totalIzin' => $totalIzin,
            'totalAlpha' => $totalAlpha,
            'maleCount' => $maleCount,
            'femaleCount' => $femaleCount,
            'dateNow' => Carbon::parse($date)->translatedFormat('d F Y'),
            'lastUpdated' => now()->format('H:i')
        ]);
    }

    public function showLogActivity(int $internId)
    {
        $user = auth()->user();
        $intern = Intern::with(['user.profile'])->findOrFail($internId);

        // Validasi: Pastikan outsider ini berhak melihat log siswa ini
        $allowedInternIds = $user->outsider->interns()->pluck('interns.id')->toArray();
        if (!in_array($intern->id, $allowedInternIds)) {
            abort(403, 'Anda tidak memiliki akses untuk melihat log siswa ini.');
        }

        // Hanya ambil log yang sudah disetujui
        $logs = LogActivity::whereHas('detailSchedule.schedule', function ($query) use ($internId) {
            $query->where('intern_id', $internId);
        })
            ->where('is_approved', true)
            ->latest('date')
            ->paginate(15);

        // Kirim data user yang login untuk digunakan di layout
        return view('outsiders.presensi.log', compact('user', 'intern', 'logs'));
    }
}
