<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\DetailSchedule;
use App\Models\Intern;
use App\Models\LateAbsence;
use App\Models\Office;
use App\Models\OfflineAttendance;
use App\Models\PermitReason;
use App\Models\Schedule;
use App\Models\Shift;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OfflineAttendanceController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Tampilkan halaman utama Absen Offline
     */
    public function index(Request $request)
    {
        $user = $this->userService->getUserLoggedData() ?? Auth::user();
        if ($user && !$user->relationLoaded('profile')) {
            $user->loadMissing('profile');
        }
        $isAssistant = (int) $user->role_id === 6;
        $sidebarView = $isAssistant ? 'layouts.sidebar-assistant' : 'layouts.sidebar';
        $routePrefix = $isAssistant ? 'assistant.absen-offline.' : 'admin.absen-offline.';

        $date = $request->get('date', today()->format('Y-m-d'));
        $search = $request->get('search');
        $statusFilter = $request->get('status');

        // Daftar pemagang aktif (role_id 3)
        $internsQuery = Intern::with(['user.profile', 'school', 'division', 'shift'])
            ->whereHas('user', function ($q) {
                $q->where('role_id', 3)->where('is_active', true);
            });

        if (!empty($search)) {
            $internsQuery->where(function ($sq) use ($search) {
                $sq->whereHas('user.profile', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%");
                })->orWhereHas('user', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%");
                });
            });
        }

        $allInterns = $internsQuery->get()
            ->sortBy(function ($intern) {
                return $intern->user?->name ?? '';
            })
            ->values();

        $internIds = $allInterns->pluck('id')->toArray();

        // Ambil data offline attendances untuk tanggal terpilih
        $offlineAttendances = OfflineAttendance::whereDate('date', $date)
            ->with(['shift', 'office', 'adminUser'])
            ->get()
            ->keyBy('intern_id');

        // Ambil data online attendances untuk tanggal terpilih (lengkap dengan lateAbsences)
        $onlineAttendances = Attendance::whereDate('date', $date)
            ->with('lateAbsences')
            ->get()
            ->keyBy('intern_id');

        // Ambil data detail_schedules untuk tanggal terpilih lengkap dengan status, shift, office dan permitReason
        // Tidak perlu eager-load 'attendance.lateAbsences' lagi karena sudah diambil pada $onlineAttendances
        $detailSchedules = DetailSchedule::whereDate('date', $date)
            ->whereHas('schedule.intern.user', function ($q) {
                $q->where('is_active', true);
            })
            ->with(['attdStatus', 'schedule', 'shift', 'office', 'permitReason.category'])
            ->get()
            ->filter(fn($ds) => $ds->schedule && $ds->schedule->intern_id)
            ->keyBy(fn($ds) => $ds->schedule->intern_id);

        // Hubungkan relasi attendance dari $onlineAttendances ke detailSchedules in-memory
        $detailSchedules->each(function ($ds) use ($onlineAttendances) {
            $internId = $ds->schedule?->intern_id;
            if ($internId && $online = $onlineAttendances->get($internId)) {
                $ds->setRelation('attendance', $online);
            }
        });

        // Ambil daftar shift dan kantor lebih awal untuk digunakan sebagai fallback dan dropdown modal
        $shifts = Shift::orderBy('start_time', 'asc')->get();
        $offices = Office::with('coordinates')->get();
        $firstOffice = $offices->first();
        $firstShift = $shifts->first();

        // Ambil data master schedules terakhir hanya untuk pemagang aktif (menghindari full table scan)
        $latestSchedules = empty($internIds) ? collect() : Schedule::whereIn('intern_id', $internIds)
            ->with(['shift', 'office'])
            ->orderBy('id', 'desc')
            ->get()
            ->keyBy('intern_id');

        // Gabungkan data online & offline untuk setiap pemagang
        $mappedInterns = $allInterns->map(function ($intern) use ($offlineAttendances, $onlineAttendances, $detailSchedules, $latestSchedules, $firstOffice, $firstShift) {
            $offline = $offlineAttendances->get($intern->id);
            $online = $onlineAttendances->get($intern->id);
            $ds = $detailSchedules->get($intern->id);
            $latestSched = $latestSchedules->get($intern->id);

            // Resolusi Shift & Kantor Pemagang dari Jadwal (DetailSchedule / Master Schedule)
            $assignedShift = $ds?->shift
                ?? $latestSched?->shift
                ?? $offline?->shift
                ?? $firstShift;

            $assignedOffice = $ds?->office
                ?? $latestSched?->office
                ?? $offline?->office
                ?? $firstOffice;

            $shiftStartTime = $ds?->start_time ?: $assignedShift?->start_time;
            $shiftEndTime = $ds?->end_time ?: $assignedShift?->end_time;
            $shiftTimeRange = null;
            if ($shiftStartTime && $shiftEndTime) {
                $shiftTimeRange = Carbon::parse($shiftStartTime)->format('H:i') . ' - ' . Carbon::parse($shiftEndTime)->format('H:i') . ' WIB';
            }

            $intern->assigned_shift = $assignedShift;
            $intern->assigned_shift_id = $assignedShift?->id;
            $intern->assigned_shift_name = $assignedShift?->name ?? 'Shift Pagi';
            $intern->assigned_shift_time = $shiftTimeRange;
            $intern->assigned_office = $assignedOffice;
            $intern->assigned_office_id = $assignedOffice?->id;
            $intern->assigned_office_name = $assignedOffice?->name ?? 'Kantor Utama';
            $intern->work_type = $ds?->work_type;
            $intern->detail_schedule = $ds;

            // 1. Status Online (Pembedaan Izin Keperluan vs Izin Sakit)
            $onlineStatusKey = 'belum_absen';
            $onlineStatusLabel = 'Belum Absen';
            $onlineTime = null;
            $onlineLateMinutes = 0;

            $isSickOnline = false;
            $isReasonOnline = false;
            if ($ds && $ds->attd_status_id == 3) {
                $pCatId = $ds->permitReason?->permit_category_id;
                $pCatName = strtolower($ds->permitReason?->category?->name ?? '');
                $pDesc = strtolower($ds->permitReason?->description ?? '');
                if (in_array($pCatId, [1, 2]) || str_contains($pCatName, 'sakit') || str_contains($pDesc, 'sakit')) {
                    $isSickOnline = true;
                } else {
                    $isReasonOnline = true;
                }
            }

            if ($online && $online->start_time) {
                $onlineTime = Carbon::parse($online->start_time)->format('H:i');
                $lateAbsence = $online->lateAbsences?->first();
                $onlineLateMinutes = $lateAbsence?->late_minutes ?? 0;

                if ($onlineLateMinutes > 0 || ($ds && $ds->attd_status_id == 4)) {
                    $onlineStatusKey = 'terlambat';
                    $onlineStatusLabel = "Terlambat ({$onlineLateMinutes}m)";
                } else {
                    $onlineStatusKey = 'hadir';
                    $onlineStatusLabel = 'Hadir Tepat Waktu';
                }
            } elseif ($isSickOnline) {
                $onlineStatusKey = 'izin_sakit';
                $onlineStatusLabel = 'Izin Sakit';
            } elseif ($isReasonOnline || ($ds && $ds->attd_status_id == 3)) {
                $onlineStatusKey = 'izin_keperluan';
                $onlineStatusLabel = 'Izin Keperluan';
            } elseif ($ds && $ds->attd_status_id == 5) {
                $onlineStatusKey = 'alpha';
                $onlineStatusLabel = 'Alpha';
            }

            // 2. Status Offline
            $offlineStatusKey = $offline?->status; // 'hadir', 'early', 'terlambat', 'izin', 'sakit', 'alpha', or null
            $offlineStatusLabel = 'Belum Diperiksa';
            $offlineTime = null;
            $physicalCheckinTime = null;
            $offlineLateMin = 0;

            if ($offline) {
                $offlineTime = $offline->check_time ? Carbon::parse($offline->check_time)->format('H:i') : null;
                $physicalCheckinTime = null;
                if (in_array($offline->status, ['hadir', 'early', 'terlambat'])) {
                    $physicalCheckinTime = $offline->physical_checkin_time ? Carbon::parse($offline->physical_checkin_time)->format('H:i') : $offlineTime;
                }
                $offlineLateMin = $offline->late_minutes;

                if ($offline->status === 'hadir') {
                    $offlineStatusLabel = 'Hadir Tepat Waktu';
                } elseif ($offline->status === 'early') {
                    $offlineStatusLabel = 'Hadir Lebih Awal';
                } elseif ($offline->status === 'terlambat') {
                    $offlineStatusLabel = $offlineLateMin > 0 ? "Terlambat ({$offlineLateMin}m)" : 'Terlambat';
                } elseif ($offline->status === 'izin') {
                    $isValid = $offline->permit_is_valid !== false && $offline->permit_is_valid !== 0;
                    $offlineStatusLabel = $isValid ? 'Izin Keperluan (Valid)' : 'Izin Keperluan (Ditolak)';
                } elseif ($offline->status === 'sakit') {
                    $sType = $offline->sickness_verification_type ?? 'doctor_letter';
                    $offlineStatusLabel = match ($sType) {
                        'fake_sickness' => 'Sakit Berbohong (Alpha)',
                        'verified_by_hr' => 'Izin Sakit (Dicek HR)',
                        default => 'Izin Sakit (Surat Dokter)',
                    };
                } elseif ($offline->status === 'alpha') {
                    $offlineStatusLabel = 'Alpha / Tidak Hadir';
                }
            }

            // 3. Logika Cross-check / Deteksi Kejujuran & Penanganan Sanksi
            $verificationStatus = 'unverified';
            $verificationLabel = 'Belum Diperiksa';
            $verificationBadgeClass = 'bg-gray-100 text-gray-600 border-gray-200';
            $verificationNote = null;

            if ($offline) {
                // Cek apakah sudah pernah dijatuhi sanksi / keputusan penanganan
                if (!empty($offline->penalty_type)) {
                    if ($offline->penalty_type === 'ganti_jam') {
                        $hours = floor(($offline->penalty_minutes ?? 435) / 60);
                        $mins = ($offline->penalty_minutes ?? 435) % 60;
                        $timeStr = sprintf('%02d:%02d Jam', $hours, $mins);
                        $verificationStatus = 'penalty_ganti_jam';
                        $verificationLabel = "Alpha (Ganti {$timeStr})";
                        $verificationBadgeClass = 'bg-purple-100 text-purple-800 border-purple-300 font-bold hover:bg-purple-200 cursor-pointer shadow-sm';
                        $verificationNote = "Sudah ditindak: Absen online dimasukkan ke kondisi Alpha & wajib ganti jam {$timeStr}. Klik untuk tinjau/ubah.";
                    } elseif ($offline->penalty_type === 'tanpa_ganti_jam') {
                        $verificationStatus = 'penalty_alpha';
                        $verificationLabel = 'Sanksi: Tetap Alpha';
                        $verificationBadgeClass = 'bg-red-800 text-white font-bold hover:bg-red-900 cursor-pointer shadow-sm';
                        $verificationNote = 'Sudah ditindak: Absen online dianulir (Alpha fiktif tanpa kompensasi). Klik untuk tinjau/ubah.';
                    } elseif ($offline->penalty_type === 'dimaafkan') {
                        $verificationStatus = 'penalty_dimaafkan';
                        $verificationLabel = 'Klarifikasi Sah (Dimaafkan)';
                        $verificationBadgeClass = 'bg-blue-100 text-blue-800 border-blue-300 font-semibold hover:bg-blue-200 cursor-pointer shadow-sm';
                        $verificationNote = 'Klarifikasi diterima oleh admin (izin sah / dinas luar). Klik untuk tinjau/ubah.';
                    }
                } elseif ($offline->status === 'alpha') {
                    if (in_array($onlineStatusKey, ['hadir', 'terlambat'])) {
                        // INDIKASI BOHONG: Online tercatat hadir, tapi fisik di kantor tidak ada!
                        $verificationStatus = 'fraud';
                        $verificationLabel = 'Indikasi Berbohong';
                        $verificationBadgeClass = 'bg-red-600 text-white font-bold';
                        $verificationNote = 'Absen online tercatat hadir, namun secara fisik TIDAK HADIR di kantor! Klik untuk beri sanksi.';
                    } else {
                        $verificationStatus = 'alpha';
                        $verificationLabel = 'Alpha';
                        $verificationBadgeClass = 'bg-red-100 text-red-800 border-red-200';
                        $verificationNote = 'Pemagang tidak hadir (offline & online)';
                    }
                } elseif ($offline->status === 'izin') {
                    $isValid = $offline->permit_is_valid !== false && $offline->permit_is_valid !== 0;
                    if ($isValid) {
                        $verificationStatus = 'permit_valid';
                        $verificationLabel = 'Izin Keperluan (Valid)';
                        $verificationBadgeClass = 'bg-blue-100 text-blue-800 border-blue-300 font-semibold';
                        $verificationNote = 'Izin keperluan pemagang telah disetujui & diverifikasi valid oleh admin.';
                    } else {
                        $verificationStatus = 'permit_invalid';
                        $verificationLabel = 'Izin Keperluan Ditolak (Alpha)';
                        $verificationBadgeClass = 'bg-red-100 text-red-800 border-red-300 font-bold';
                        $verificationNote = 'Izin keperluan tidak valid / ditolak oleh admin, dikonversi ke sanksi Alpha.';
                    }
                } elseif ($offline->status === 'sakit') {
                    $sType = $offline->sickness_verification_type ?? 'doctor_letter';
                    if ($sType === 'fake_sickness') {
                        $verificationStatus = 'fake_sickness';
                        $verificationLabel = 'Sakit Berbohong';
                        $verificationBadgeClass = 'bg-red-600 text-white font-bold';
                        $verificationNote = 'Sakit terindikasi palsu / tanpa bukti sah. Klik untuk beri sanksi.';
                    } elseif ($sType === 'verified_by_hr') {
                        $verificationStatus = 'sickness_hr';
                        $verificationLabel = 'Izin Sakit (Dicek HR)';
                        $verificationBadgeClass = 'bg-amber-100 text-amber-800 border-amber-300 font-semibold';
                        $verificationNote = 'Sakit telah dikonfirmasi dan dicek langsung oleh HR. Kategori izin sakit sah tanpa ganti jam.';
                    } else {
                        $verificationStatus = 'sickness_doctor';
                        $verificationLabel = 'Izin Sakit (Surat Dokter)';
                        $verificationBadgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-300 font-semibold';
                        $verificationNote = 'Izin sakit terverifikasi fisik offline dengan surat izin / surat dokter yang sah.';
                    }
                } elseif ($offline->status === 'terlambat') {
                    if ($onlineStatusKey === 'hadir') {
                        // Online tepat waktu, tapi fisik telat
                        $verificationStatus = 'late_mismatch';
                        $verificationLabel = 'Terlambat Fisik';
                        $verificationBadgeClass = 'bg-amber-100 text-amber-800 border-amber-300 font-semibold';
                        $verificationNote = "Online absen tepat waktu, namun fisik tiba telat {$offlineLateMin} menit.";
                    } else {
                        $verificationStatus = 'verified_late';
                        $verificationLabel = 'Terverifikasi Terlambat';
                        $verificationBadgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
                        $verificationNote = 'Fisik dan online sama-sama tercatat terlambat.';
                    }
                } elseif ($offline->status === 'early') {
                    if ($onlineStatusKey === 'belum_absen') {
                        $verificationStatus = 'early_unverified';
                        $verificationLabel = 'Early (Belum Online)';
                        $verificationBadgeClass = 'bg-cyan-100 text-cyan-800 border-cyan-300 font-semibold';
                        $verificationNote = 'Hadir fisik lebih awal di kantor, namun belum absen online.';
                    } else {
                        $verificationStatus = 'early';
                        $verificationLabel = 'Early Check-in';
                        $verificationBadgeClass = 'bg-cyan-100 text-cyan-800 border-cyan-300 font-bold';
                        $verificationNote = 'Hadir fisik lebih awal sebelum jam shift dimulai.';
                    }
                } elseif ($offline->status === 'hadir') {
                    if ($onlineStatusKey === 'belum_absen') {
                        // Fisik ada, tapi belum absen online
                        $verificationStatus = 'forgot_online';
                        $verificationLabel = 'Lupa Absen Online';
                        $verificationBadgeClass = 'bg-yellow-100 text-yellow-800 border-yellow-300';
                        $verificationNote = 'Fisik hadir di kantor, tapi belum absen online di sistem.';
                    } else {
                        // Sesuai & Valid
                        $verificationStatus = 'verified';
                        $verificationLabel = 'Terverifikasi Hadir';
                        $verificationBadgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-300 font-medium';
                        $verificationNote = 'Hadir fisik terverifikasi sesuai.';
                    }
                }
            }

            $intern->online_status_key = $onlineStatusKey;
            $intern->online_status_label = $onlineStatusLabel;
            $intern->online_time = $onlineTime;
            $intern->online_late_minutes = $onlineLateMinutes;

            $intern->offline_record = $offline;
            $intern->offline_status_key = $offlineStatusKey;
            $intern->offline_status_label = $offlineStatusLabel;
            $intern->offline_time = $offlineTime;
            $intern->physical_checkin_time = $physicalCheckinTime;
            $intern->offline_late_minutes = $offlineLateMin;

            $intern->verification_status = $verificationStatus;
            $intern->verification_label = $verificationLabel;
            $intern->verification_badge_class = $verificationBadgeClass;
            $intern->verification_note = $verificationNote;

            return $intern;
        });

        // Filter berdasarkan status tab jika dipilih
        $filteredInterns = $mappedInterns;
        if (!empty($statusFilter)) {
            if ($statusFilter === 'sudah_diabsen') {
                $filteredInterns = $mappedInterns->whereNotNull('offline_record')->values();
            } elseif ($statusFilter === 'belum_diabsen') {
                $filteredInterns = $mappedInterns->whereNull('offline_record')->values();
            } elseif ($statusFilter === 'hadir') {
                $filteredInterns = $mappedInterns->whereIn('offline_status_key', ['hadir', 'early'])->values();
            } elseif ($statusFilter === 'early') {
                $filteredInterns = $mappedInterns->where('offline_status_key', 'early')->values();
            } elseif ($statusFilter === 'terlambat') {
                $filteredInterns = $mappedInterns->where('offline_status_key', 'terlambat')->values();
            } elseif ($statusFilter === 'izin') {
                $filteredInterns = $mappedInterns->where('offline_status_key', 'izin')->values();
            } elseif ($statusFilter === 'sakit') {
                $filteredInterns = $mappedInterns->where('offline_status_key', 'sakit')->values();
            } elseif ($statusFilter === 'alpha') {
                $filteredInterns = $mappedInterns->where('offline_status_key', 'alpha')->values();
            } elseif ($statusFilter === 'fraud') {
                $filteredInterns = $mappedInterns->filter(function ($item) {
                    return in_array($item->verification_status, ['fraud', 'penalty_ganti_jam', 'penalty_alpha', 'penalty_dimaafkan', 'fake_sickness', 'permit_invalid']);
                })->values();
            }
        }

        // Summary counts
        $totalInterns = $mappedInterns->count();
        $totalSudahDiabsen = $mappedInterns->whereNotNull('offline_record')->count();
        $totalOfflineHadir = $mappedInterns->whereIn('offline_status_key', ['hadir', 'early'])->count();
        $totalOfflineEarly = $mappedInterns->where('offline_status_key', 'early')->count();
        $totalOfflineTerlambat = $mappedInterns->where('offline_status_key', 'terlambat')->count();
        $totalOfflineIzin = $mappedInterns->where('offline_status_key', 'izin')->count();
        $totalOfflineSakit = $mappedInterns->where('offline_status_key', 'sakit')->count();
        $totalOfflineAlpha = $mappedInterns->where('offline_status_key', 'alpha')->count();
        $totalFraud = $mappedInterns->filter(function ($item) {
            return in_array($item->verification_status, ['fraud', 'penalty_ganti_jam', 'penalty_alpha', 'penalty_dimaafkan', 'fake_sickness', 'permit_invalid']);
        })->count();
        $totalUnverified = $mappedInterns->whereNull('offline_record')->count();

        // Paginasi 10 pemagang per halaman (sesuai permintaan user)
        $perPage = 10;
        $currentPage = Paginator::resolveCurrentPage() ?: 1;
        $currentItems = $filteredInterns->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedInterns = new LengthAwarePaginator(
            $currentItems,
            $filteredInterns->count(),
            $perPage,
            $currentPage,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => $request->query()
            ]
        );

        $allInterns = $mappedInterns;

        return view('admin.absen-offline.index', compact(
            'user',
            'isAssistant',
            'sidebarView',
            'routePrefix',
            'date',
            'search',
            'statusFilter',
            'allInterns',
            'filteredInterns',
            'paginatedInterns',
            'totalInterns',
            'totalSudahDiabsen',
            'totalOfflineHadir',
            'totalOfflineEarly',
            'totalOfflineTerlambat',
            'totalOfflineIzin',
            'totalOfflineSakit',
            'totalOfflineAlpha',
            'totalFraud',
            'totalUnverified',
            'shifts',
            'offices'
        ));
    }

    /**
     * Simpan atau Update Presensi Offline
     */
    public function store(Request $request)
    {
        $user = $this->userService->getUserLoggedData() ?? Auth::user();

        $validated = $request->validate([
            'intern_id' => 'required|exists:interns,id',
            'shift_id' => 'required|exists:shifts,id',
            'office_id' => 'required|exists:offices,id',
            'date' => 'required|date',
            'status' => 'required|in:tepat_waktu,hadir,early,terlambat,izin,sakit,alpha',
            'physical_checkin_time' => 'nullable|string',
            'check_time' => 'nullable|string',
            'late_minutes' => 'nullable|integer|min:0',
            'sickness_verification_type' => 'nullable|in:doctor_letter,verified_by_hr,fake_sickness',
            'permit_is_valid' => 'nullable|in:0,1,true,false',
            'notes' => 'nullable|string|max:500',
        ]);

        $intern = Intern::with(['user'])->findOrFail($validated['intern_id']);
        $shift = Shift::findOrFail($validated['shift_id']);
        $office = Office::findOrFail($validated['office_id']);

        $authorRole = (int) $user->role_id === 6 ? 'Assistant' : 'Admin';
        $authorName = "{$user->name} ({$authorRole})";

        // Map status
        $rawStatus = $validated['status'];
        $dbStatus = match ($rawStatus) {
            'tepat_waktu', 'hadir' => 'hadir',
            'early' => 'early',
            'terlambat' => 'terlambat',
            'izin' => 'izin',
            'sakit' => 'sakit',
            'alpha' => 'alpha',
            default => 'hadir',
        };

        // Tentukan jam kedatangan fisik (hanya jika hadir fisik / early / terlambat)
        $physicalCheckinTime = null;
        if (in_array($dbStatus, ['hadir', 'early', 'terlambat'])) {
            if (!empty($validated['physical_checkin_time'])) {
                $physicalCheckinTime = Carbon::parse($validated['physical_checkin_time'])->format('H:i:s');
            } elseif (!empty($validated['check_time'])) {
                $physicalCheckinTime = Carbon::parse($validated['check_time'])->format('H:i:s');
            } else {
                $physicalCheckinTime = now()->format('H:i:s');
            }
        }

        // Tentukan jam pencatatan
        $checkTime = !empty($validated['check_time'])
            ? Carbon::parse($validated['check_time'])->format('H:i:s')
            : ($physicalCheckinTime ?: now()->format('H:i:s'));

        // Hitung menit keterlambatan otomatis dari selisih Waktu Masuk Fisik dan Jam Mulai Shift
        $lateMinutes = 0;
        if ($dbStatus === 'terlambat' || (!empty($physicalCheckinTime) && in_array($dbStatus, ['hadir', 'terlambat']))) {
            $checkTimeStr = $physicalCheckinTime ?: (!empty($validated['check_time']) ? $validated['check_time'] : now()->format('H:i:s'));
            $actualTime = Carbon::parse($validated['date'] . ' ' . $checkTimeStr);
            $shiftStartTime = Carbon::parse($validated['date'] . ' ' . $shift->start_time);

            if ($actualTime->greaterThan($shiftStartTime)) {
                $lateMinutes = (int) $shiftStartTime->diffInMinutes($actualTime);
            } elseif ($dbStatus === 'terlambat') {
                $lateMinutes = (int) ($validated['late_minutes'] ?? 0);
                if ($lateMinutes <= 0) {
                    $lateMinutes = 1; // Minimal 1 menit jika status ditandai terlambat
                }
            }
        }

        // Sub-verifikasi Sakit & Izin
        $sicknessType = null;
        if ($dbStatus === 'sakit') {
            $sicknessType = $validated['sickness_verification_type'] ?? 'doctor_letter';
        }

        $permitIsValid = null;
        if ($dbStatus === 'izin') {
            $permitIsValid = isset($validated['permit_is_valid']) ? (bool) $validated['permit_is_valid'] : true;
        }

        try {
            // Simpan atau Update ke tabel dedicated offline_attendances
            OfflineAttendance::updateOrCreate(
                [
                    'intern_id' => $intern->id,
                    'date' => $validated['date'],
                ],
                [
                    'shift_id' => $shift->id,
                    'office_id' => $office->id,
                    'status' => $dbStatus,
                    'check_time' => $checkTime,
                    'physical_checkin_time' => $physicalCheckinTime,
                    'sickness_verification_type' => $sicknessType,
                    'permit_is_valid' => $permitIsValid,
                    'late_minutes' => $lateMinutes,
                    'notes' => $validated['notes'] ?? null,
                    'recorded_by' => $authorName,
                    'admin_user_id' => $user->id,
                ]
            );

            // Sinkronisasi status kehadiran ke DetailSchedule & PermitReason
            $detailSchedule = DetailSchedule::whereDate('date', $validated['date'])
                ->whereHas('schedule', function ($q) use ($intern) {
                    $q->where('intern_id', $intern->id);
                })
                ->first();

            // Jika belum ada DetailSchedule untuk tanggal tersebut, buatkan agar tercatat di jadwal
            if (!$detailSchedule) {
                $masterSchedule = Schedule::where('intern_id', $intern->id)->latest('id')->first();
                if (!$masterSchedule) {
                    $masterSchedule = Schedule::create([
                        'intern_id' => $intern->id,
                        'office_id' => $office->id,
                        'start_period' => $validated['date'],
                        'end_period' => $validated['date'],
                        'type' => 'daily',
                    ]);
                }
                $detailSchedule = DetailSchedule::create([
                    'schedule_id' => $masterSchedule->id,
                    'shift_id' => $shift->id,
                    'office_id' => $office->id,
                    'date' => $validated['date'],
                    'attd_status_id' => 1,
                    'work_type' => 'wfo',
                ]);
            }

            if ($dbStatus === 'sakit') {
                if ($sicknessType === 'verified_by_hr') {
                    // Izin Sakit Dikonfirmasi/Dicek HR -> Bebas Ganti Jam (Lunas) & masuk ke Log Izin Sakit
                    $desc = !empty($validated['notes'])
                        ? "Sakit (Dikonfirmasi & dicek oleh HR): " . $validated['notes']
                        : "Sakit (Dikonfirmasi dan dicek langsung oleh HR)";

                    if ($detailSchedule->permit_reason_id && $detailSchedule->permit_reason_id > 0) {
                        $permitReason = PermitReason::find($detailSchedule->permit_reason_id);
                        if ($permitReason) {
                            $permitReason->update([
                                'description' => $desc,
                                'permit_category_id' => 2, // 2 = Sakit tanpa surat dokter / Cek HR
                                'proof_url' => $permitReason->proof_url ?? null,
                            ]);
                        } else {
                            $permitReason = PermitReason::create([
                                'description' => $desc,
                                'permit_category_id' => 2,
                                'proof_url' => null,
                            ]);
                            $detailSchedule->permit_reason_id = $permitReason->id;
                        }
                    } else {
                        $permitReason = PermitReason::create([
                            'description' => $desc,
                            'permit_category_id' => 2,
                            'proof_url' => null,
                        ]);
                        $detailSchedule->permit_reason_id = $permitReason->id;
                    }

                    $detailSchedule->attd_status_id = 3; // 3 = Izin
                    $detailSchedule->isChangeSchedule = 1; // 1 = Bebas Ganti Jam (Lunas)
                    $detailSchedule->is_change_schedule_approved = 1;
                    $detailSchedule->save();

                    // Bersihkan keterlambatan jika ada
                    LateAbsence::where('intern_id', $intern->id)
                        ->whereDate('date', $validated['date'])
                        ->delete();
                } elseif ($sicknessType === 'doctor_letter') {
                    // Sakit dengan Surat Dokter -> Jika belum ada permit reason, buatkan agar tercatat di log izin sakit
                    $desc = !empty($validated['notes'])
                        ? "Izin Sakit (Surat Dokter): " . $validated['notes']
                        : "Izin Sakit dengan surat keterangan dokter";

                    if ($detailSchedule->permit_reason_id && $detailSchedule->permit_reason_id > 0) {
                        $permitReason = PermitReason::find($detailSchedule->permit_reason_id);
                        if ($permitReason) {
                            $permitReason->update([
                                'description' => $desc,
                                'permit_category_id' => 1, // 1 = Sakit dengan surat dokter
                            ]);
                        } else {
                            $permitReason = PermitReason::create([
                                'description' => $desc,
                                'permit_category_id' => 1,
                                'proof_url' => null,
                            ]);
                            $detailSchedule->permit_reason_id = $permitReason->id;
                        }
                    } else {
                        $permitReason = PermitReason::create([
                            'description' => $desc,
                            'permit_category_id' => 1,
                            'proof_url' => null,
                        ]);
                        $detailSchedule->permit_reason_id = $permitReason->id;
                    }

                    $detailSchedule->attd_status_id = 3; // Izin
                    $detailSchedule->isChangeSchedule = 1; // Bebas ganti jam (Lunas)
                    $detailSchedule->is_change_schedule_approved = 1;
                    $detailSchedule->save();
                } elseif ($sicknessType === 'fake_sickness') {
                    // Sakit Berbohong / Fraud -> Status Alpha (Wajib Ganti Jam)
                    $detailSchedule->attd_status_id = 5; // 5 = Alpha
                    $detailSchedule->isChangeSchedule = 2; // Wajib Ganti Jam
                    $detailSchedule->is_change_schedule_approved = 0;
                    $detailSchedule->save();
                }
            } elseif ($dbStatus === 'izin') {
                $desc = !empty($validated['notes'])
                    ? "Izin Keperluan: " . $validated['notes']
                    : "Izin Keperluan disahkan via Presensi Offline";

                if ($permitIsValid) {
                    if ($detailSchedule->permit_reason_id && $detailSchedule->permit_reason_id > 0) {
                        $permitReason = PermitReason::find($detailSchedule->permit_reason_id);
                        if ($permitReason) {
                            $permitReason->update([
                                'description' => $desc,
                                'permit_category_id' => 4, // Keperluan lain
                            ]);
                        } else {
                            $permitReason = PermitReason::create([
                                'description' => $desc,
                                'permit_category_id' => 4,
                                'proof_url' => null,
                            ]);
                            $detailSchedule->permit_reason_id = $permitReason->id;
                        }
                    } else {
                        $permitReason = PermitReason::create([
                            'description' => $desc,
                            'permit_category_id' => 4,
                            'proof_url' => null,
                        ]);
                        $detailSchedule->permit_reason_id = $permitReason->id;
                    }

                    $detailSchedule->attd_status_id = 3; // Izin
                    $detailSchedule->isChangeSchedule = 2; // Wajib ganti jam
                    $detailSchedule->is_change_schedule_approved = 0;
                    $detailSchedule->save();
                } else {
                    $detailSchedule->attd_status_id = 5; // Ditolak -> Alpha
                    $detailSchedule->isChangeSchedule = 2;
                    $detailSchedule->is_change_schedule_approved = 0;
                    $detailSchedule->save();
                }
            } elseif (in_array($dbStatus, ['hadir', 'early'])) {
                $detailSchedule->attd_status_id = 2; // Hadir
                $detailSchedule->isChangeSchedule = 0;
                $detailSchedule->is_change_schedule_approved = 0;
                $detailSchedule->save();
            } elseif ($dbStatus === 'terlambat') {
                $detailSchedule->attd_status_id = 4; // Hadir dan Mengganti Jam / Terlambat
                $detailSchedule->isChangeSchedule = 2;
                $detailSchedule->save();
            } elseif ($dbStatus === 'alpha') {
                $detailSchedule->attd_status_id = 5; // Alpha
                $detailSchedule->isChangeSchedule = 2;
                $detailSchedule->is_change_schedule_approved = 0;
                $detailSchedule->save();
            }

            $statusText = match ($dbStatus) {
                'hadir' => 'HADIR TEPAT WAKTU',
                'early' => 'HADIR LEBIH AWAL (EARLY CHECK-IN)',
                'terlambat' => "TERLAMBAT ({$lateMinutes} menit)",
                'izin' => 'IZIN KEPERLUAN (' . ($permitIsValid ? 'VALID' : 'TIDAK VALID') . ')',
                'sakit' => 'IZIN SAKIT (' . strtoupper(str_replace('_', ' ', $sicknessType ?? '')) . ')',
                'alpha' => 'ALPHA / TIDAK HADIR',
            };

            $actionMessage = "Presensi offline untuk {$intern->user?->name} berhasil dicatat sebagai {$statusText}.";

            \App\Helper\ActivityLogger::log(
                'CREATE',
                'Offline Presensi',
                "Petugas {$authorName} mencatat presensi offline pemagang {$intern->user?->name} sebagai {$statusText}",
                ['intern_id' => $intern->id, 'status' => $dbStatus, 'date' => $validated['date']]
            );

            $routePrefix = (int) $user->role_id === 6 ? 'assistant.absen-offline.index' : 'admin.absen-offline.index';
            return redirect()->route($routePrefix, ['date' => $validated['date']])
                ->with('success', $actionMessage);
        } catch (\Exception $e) {
            Log::error('Error pada Absen Offline: ' . $e->getMessage(), [
                'intern_id' => $validated['intern_id'],
                'date' => $validated['date'],
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mencatat presensi offline: ' . $e->getMessage());
        }
    }

    /**
     * Batalkan Presensi Offline
     * Menghapus record dari tabel offline_attendances tanpa menyentuh tabel attendances online pemagang.
     */
    public function destroy(Request $request, int|string $id)
    {
        $user = $this->userService->getUserLoggedData() ?? Auth::user();

        try {
            $offlineAttendance = OfflineAttendance::with('intern.user')->findOrFail($id);
            $internName = $offlineAttendance->intern?->user?->name ?? 'Pemagang';
            $date = $offlineAttendance->date ? $offlineAttendance->date->format('Y-m-d') : today()->format('Y-m-d');

            // Hapus record offline presensi
            $offlineAttendance->delete();

            // Revert DetailSchedule jika sebelumnya diset oleh presensi offline
            $detailSchedule = DetailSchedule::whereDate('date', $date)
                ->whereHas('schedule', function ($q) use ($offlineAttendance) {
                    $q->where('intern_id', $offlineAttendance->intern_id);
                })
                ->first();

            $onlineAttendance = Attendance::where('intern_id', $offlineAttendance->intern_id)
                ->whereDate('date', $date)
                ->first();

            if ($detailSchedule) {
                if ($onlineAttendance && $onlineAttendance->start_time) {
                    $detailSchedule->attd_status_id = 2;
                    $detailSchedule->isChangeSchedule = 0;
                } else {
                    $detailSchedule->attd_status_id = 1;
                    $detailSchedule->isChangeSchedule = 0;
                    $detailSchedule->is_change_schedule_approved = 0;
                }
                $detailSchedule->save();
            }

            \App\Helper\ActivityLogger::log(
                'DELETE',
                'Offline Presensi',
                "Petugas {$user->name} membatalkan presensi offline pemagang {$internName} pada {$date}",
                ['intern_id' => $offlineAttendance->intern_id, 'date' => $date]
            );

            $routePrefix = (int) $user->role_id === 6 ? 'assistant.absen-offline.index' : 'admin.absen-offline.index';
            return redirect()->route($routePrefix, ['date' => $date])
                ->with('success', "Presensi offline untuk {$internName} pada tanggal {$date} berhasil dibatalkan.");
        } catch (\Exception $e) {
            Log::error('Gagal membatalkan presensi offline: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Gagal membatalkan presensi offline: ' . $e->getMessage());
        }
    }

    /**
     * Ambil Status Presensi Pemagang Hari Ini (Online & Offline) via AJAX untuk Modal Form
     */
    public function getInternStatus(Request $request, int|string $internId)
    {
        $date = $request->get('date', today()->format('Y-m-d'));
        $intern = Intern::with(['user.profile', 'division', 'school', 'shift'])->findOrFail($internId);

        // Cari online attendance & schedule
        $onlineAttendance = Attendance::where('intern_id', $intern->id)
            ->whereDate('date', $date)
            ->with('lateAbsences')
            ->first();

        $detailSchedule = DetailSchedule::whereDate('date', $date)
            ->whereHas('schedule', function ($q) use ($intern) {
                $q->where('intern_id', $intern->id);
            })
            ->with(['attdStatus', 'shift', 'office', 'schedule.shift', 'schedule.office', 'permitReason.category'])
            ->first();

        // Cari offline attendance
        $offlineAttendance = OfflineAttendance::where('intern_id', $intern->id)
            ->whereDate('date', $date)
            ->with(['shift', 'office'])
            ->first();

        $masterSchedule = Schedule::where('intern_id', $intern->id)
            ->with(['shift', 'office'])
            ->latest('id')
            ->first();

        $assignedShift = $offlineAttendance?->shift
            ?? $detailSchedule?->shift
            ?? $detailSchedule?->schedule?->shift
            ?? $masterSchedule?->shift
            ?? $intern->shift
            ?? Shift::orderBy('start_time', 'asc')->first();

        $assignedOffice = $offlineAttendance?->office
            ?? $detailSchedule?->office
            ?? $detailSchedule?->schedule?->office
            ?? $masterSchedule?->office
            ?? Office::first();

        $defaultShiftId = $assignedShift?->id ?? Shift::first()?->id;
        $defaultOfficeId = $assignedOffice?->id ?? Office::first()?->id;

        $onlineTimeStr = ($onlineAttendance && $onlineAttendance->start_time) ? Carbon::parse($onlineAttendance->start_time)->format('H:i') : null;
        $lateMin = $onlineAttendance?->lateAbsences?->first()?->late_minutes ?? 0;
        $onlineStatusKey = 'belum_absen';
        $onlineStatusLabel = 'Belum Absen Online';

        $isSickOnline = false;
        $isReasonOnline = false;
        if ($detailSchedule && $detailSchedule->attd_status_id == 3) {
            $pCatId = $detailSchedule->permitReason?->permit_category_id;
            $pCatName = strtolower($detailSchedule->permitReason?->category?->name ?? '');
            $pDesc = strtolower($detailSchedule->permitReason?->description ?? '');
            if (in_array($pCatId, [1, 2]) || str_contains($pCatName, 'sakit') || str_contains($pDesc, 'sakit')) {
                $isSickOnline = true;
            } else {
                $isReasonOnline = true;
            }
        }

        if ($onlineTimeStr) {
            if ($lateMin > 0 || ($detailSchedule && $detailSchedule->attd_status_id == 4)) {
                $onlineStatusKey = 'terlambat';
                $onlineStatusLabel = "Terlambat ({$lateMin}m)";
            } else {
                $onlineStatusKey = 'hadir';
                $onlineStatusLabel = 'Hadir Tepat Waktu';
            }
        } elseif ($isSickOnline) {
            $onlineStatusKey = 'izin_sakit';
            $onlineStatusLabel = 'Izin Sakit Online';
        } elseif ($isReasonOnline || ($detailSchedule && $detailSchedule->attd_status_id == 3)) {
            $onlineStatusKey = 'izin_keperluan';
            $onlineStatusLabel = 'Izin Keperluan Online';
        } elseif ($detailSchedule && $detailSchedule->attd_status_id == 5) {
            $onlineStatusKey = 'alpha';
            $onlineStatusLabel = 'Alpha Online';
        }

        // Tentukan pesan status
        if ($offlineAttendance) {
            $statusLabel = match ($offlineAttendance->status) {
                'hadir' => 'Hadir Tepat Waktu',
                'early' => 'Hadir Lebih Awal (Early Check-in)',
                'terlambat' => "Terlambat ({$offlineAttendance->late_minutes} menit)",
                'izin' => 'Izin Keperluan (' . ($offlineAttendance->permit_is_valid !== false ? 'Valid' : 'Ditolak') . ')',
                'sakit' => match ($offlineAttendance->sickness_verification_type ?? 'doctor_letter') {
                    'fake_sickness' => 'Sakit Berbohong (Fraud)',
                    'verified_by_hr' => 'Izin Sakit (Dicek HR - Tanpa Ganti Jam)',
                    default => 'Izin Sakit (Surat Dokter)',
                },
                'alpha' => 'Alpha / Tidak Hadir',
                default => 'Hadir',
            };
            $isFraud = $offlineAttendance->status === 'alpha' || ($offlineAttendance->status === 'sakit' && $offlineAttendance->sickness_verification_type === 'fake_sickness');
            $alertType = $isFraud ? 'danger' : 'success';
            $alertTitle = "Presensi Offline: {$statusLabel}";
            
            $onlineInfo = $onlineTimeStr 
                ? "Absen Online: Masuk pukul {$onlineTimeStr} WIB ({$onlineStatusLabel})."
                : "Absen Online: " . ($onlineStatusKey === 'belum_absen' ? "Belum melakukan check-in online." : "Status {$onlineStatusLabel}.");
                
            $alertMessage = "{$onlineInfo} Dicatat offline oleh {$offlineAttendance->recorded_by}" . ($offlineAttendance->notes ? " (Catatan: '{$offlineAttendance->notes}')" : '') . ". Anda dapat memperbaruinya melalui form ini.";
        } elseif ($onlineTimeStr) {
            if ($onlineStatusKey === 'terlambat') {
                $alertType = 'warning';
                $alertTitle = "Sudah Absen Online pukul {$onlineTimeStr} WIB (Terlambat {$lateMin}m)";
                $alertMessage = "Pemagang telah check-in online pukul {$onlineTimeStr} WIB dengan keterlambatan {$lateMin} menit. Belum diperiksa/dicatat di Presensi Offline.";
            } else {
                $alertType = 'success';
                $alertTitle = "Sudah Absen Online pukul {$onlineTimeStr} WIB (Hadir Tepat Waktu)";
                $alertMessage = "Pemagang telah check-in online pukul {$onlineTimeStr} WIB tepat waktu. Silakan verifikasi apakah fisiknya benar ada di kantor.";
            }
        } elseif ($isSickOnline) {
            $alertType = 'info';
            $alertTitle = 'Sedang Dalam Status Izin Sakit Online';
            $alertMessage = "Pemagang memiliki status pengajuan Izin Sakit resmi pada tanggal ini.";
        } elseif ($isReasonOnline || ($detailSchedule && $detailSchedule->attd_status_id == 3)) {
            $alertType = 'info';
            $alertTitle = 'Sedang Dalam Status Izin Keperluan Online';
            $alertMessage = "Pemagang memiliki status pengajuan Izin Keperluan resmi pada tanggal ini.";
        } else {
            $alertType = 'info';
            $alertTitle = 'Belum Melakukan Absen Online';
            $alertMessage = "Pemagang belum melakukan check-in online untuk tanggal {$date}.";
        }

        return response()->json([
            'success' => true,
            'intern' => [
                'id' => $intern->id,
                'name' => $intern->user?->name,
                'default_shift_id' => $defaultShiftId,
                'default_office_id' => $defaultOfficeId,
            ],
            'online' => [
                'status_key' => $onlineStatusKey,
                'status_label' => $onlineStatusLabel,
                'time' => $onlineTimeStr ? $onlineTimeStr . ' WIB' : null,
                'late_minutes' => $lateMin,
            ],
            'status' => [
                'alert_type' => $alertType,
                'alert_title' => $alertTitle,
                'alert_message' => $alertMessage,
                'online_time' => $onlineTimeStr ? $onlineTimeStr . ' WIB' : null,
                'online_status_label' => $onlineStatusLabel,
            ],
            'offline_attendance' => $offlineAttendance ? [
                'id' => $offlineAttendance->id,
                'status' => $offlineAttendance->status,
                'check_time' => $offlineAttendance->check_time ? Carbon::parse($offlineAttendance->check_time)->format('H:i') : null,
                'physical_checkin_time' => $offlineAttendance->physical_checkin_time ? Carbon::parse($offlineAttendance->physical_checkin_time)->format('H:i') : null,
                'sickness_verification_type' => $offlineAttendance->sickness_verification_type,
                'permit_is_valid' => $offlineAttendance->permit_is_valid,
                'late_minutes' => $offlineAttendance->late_minutes,
                'notes' => $offlineAttendance->notes,
                'shift_id' => $offlineAttendance->shift_id,
                'office_id' => $offlineAttendance->office_id,
            ] : null,
            'online_attendance' => $onlineAttendance ? [
                'start_time' => $onlineAttendance->start_time ? Carbon::parse($onlineAttendance->start_time)->format('H:i') : null,
                'keterangan' => $onlineAttendance->keterangan,
            ] : null,
        ]);
    }

    /**
     * Simpan Keputusan Sanksi Pemagang Terindikasi Berbohong / Fiktif
     */
    public function storePenalty(Request $request, int|string $id)
    {
        $user = $this->userService->getUserLoggedData() ?? Auth::user();

        $validated = $request->validate([
            'penalty_type' => 'required|in:ganti_jam,tanpa_ganti_jam,dimaafkan',
            'penalty_minutes' => 'nullable|integer|min:0',
            'penalty_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $offline = OfflineAttendance::with(['intern.user', 'shift'])->findOrFail($id);
            $intern = $offline->intern;
            $internName = $intern?->user?->name ?? 'Pemagang';
            $date = $offline->date ? $offline->date->format('Y-m-d') : today()->format('Y-m-d');
            $authorRole = (int) $user->role_id === 6 ? 'Assistant' : 'Admin';
            $authorName = "{$user->name} ({$authorRole})";

            $penaltyType = $validated['penalty_type'];
            $penaltyMinutes = (int) ($validated['penalty_minutes'] ?? 0);
            $penaltyNotes = $validated['penalty_notes'] ?? null;
            $successMessage = 'Keputusan sanksi berhasil disimpan.';

            if ($penaltyType === 'ganti_jam' && $penaltyMinutes <= 0) {
                $penaltyMinutes = 435; // default 07:15 jam (435 menit) sesuai jam kerja shift standar
            }

            $hours = floor($penaltyMinutes / 60);
            $mins = $penaltyMinutes % 60;
            $timeFormatted = sprintf('%02d:%02d Jam', $hours, $mins);

            // Update record offline attendance
            $offline->status = 'alpha'; // Fisik offline tetap tercatat tidak hadir / alpha
            $offline->penalty_type = $penaltyType;
            $offline->penalty_minutes = $penaltyType === 'ganti_jam' ? $penaltyMinutes : 0;
            $offline->penalty_notes = $penaltyNotes;
            $offline->penalty_by = $authorName;
            $offline->penalty_at = now();

            // Hubungkan ke data Attendance online pemagang
            $attendance = Attendance::where('intern_id', $offline->intern_id)
                ->whereDate('date', $date)
                ->first();

            // Hubungkan ke DetailSchedule pemagang
            $detailSchedule = DetailSchedule::whereDate('date', $date)
                ->whereHas('schedule', function ($q) use ($offline) {
                    $q->where('intern_id', $offline->intern_id);
                })
                ->with('shift')
                ->first();

            // Hitung menit 1 shift penuh (default 435 menit / 07:15 jam jika total_time_in_minute tidak diset)
            $shiftTotalMinutes = $detailSchedule?->shift?->total_time_in_minute
                ?? $offline->shift?->total_time_in_minute
                ?? 435;
            $penaltyMinutes = $shiftTotalMinutes > 0 ? (int)$shiftTotalMinutes : 435;

            $hours = floor($penaltyMinutes / 60);
            $mins = $penaltyMinutes % 60;
            $timeFormatted = sprintf('%02d:%02d Jam', $hours, $mins);

            // Hubungkan ke data Attendance online pemagang
            $attendance = Attendance::where('intern_id', $offline->intern_id)
                ->whereDate('date', $date)
                ->first();

            if ($penaltyType === 'ganti_jam') {
                // Sanksi 1: Wajib Ganti Full 1 Shift (Otomatis dimasukkan ke kondisi ALPHA & jam hutang 1 shift penuh)
                if ($detailSchedule) {
                    $detailSchedule->attd_status_id = 5; // 5 = ALPHA (Tidak Hadir)
                    $detailSchedule->isChangeSchedule = 2; // 2 = Wajib Ganti Jam
                    $detailSchedule->is_change_schedule_approved = 0; // Belum lunas
                    $detailSchedule->save();
                }

                if ($attendance) {
                    // Tutup sesi absensi online seketika jika masih terbuka agar pemagang tidak bisa melanjutkan absen
                    if (is_null($attendance->end_time)) {
                        $attendance->end_time = $attendance->start_time ?? now()->format('H:i:s');
                    }
                    $attendance->keterangan = "Absen online fiktif (Berbohong): Otomatis ditutup & dimasukkan ke kondisi Alpha (Wajib Ganti 1 Shift Full {$timeFormatted}). (" . ($penaltyNotes ?: 'Telah dikonfirmasi') . ")";
                    $attendance->adjusted_end_time = null;
                    $attendance->save();

                    // Hapus record keterlambatan LateAbsence jika ada agar tidak double count dengan Alpha
                    LateAbsence::where('intern_id', $offline->intern_id)
                        ->whereDate('date', $date)
                        ->delete();
                }

                $offline->status = 'alpha';
                $offline->penalty_type = 'ganti_jam';
                $offline->penalty_minutes = $penaltyMinutes;
                $offline->penalty_notes = $penaltyNotes;
                $offline->penalty_by = $authorName;
                $offline->penalty_at = now();
                $offline->save();

                $successMessage = "Sanksi untuk {$internName} berhasil ditetapkan: Absen online otomatis dimasukkan ke kondisi ALPHA & dikenakan Wajib Ganti 1 Shift Full ({$timeFormatted}).";
            } elseif ($penaltyType === 'tanpa_ganti_jam') {
                // Sanksi 2: Tanpa Ganti Jam (Absen Online Dianulir, Tetap Alpha)
                if ($detailSchedule) {
                    $detailSchedule->attd_status_id = 5; // 5 = Alpha
                    $detailSchedule->save();
                }

                if ($attendance) {
                    // Tutup sesi absensi online seketika jika masih terbuka
                    if (is_null($attendance->end_time)) {
                        $attendance->end_time = $attendance->start_time ?? now()->format('H:i:s');
                    }
                    $attendance->keterangan = "Absen online dianulir (Alpha fiktif). Sesi ditutup oleh Admin. Catatan: " . ($penaltyNotes ?: 'Tidak hadir fisik di kantor');
                    $attendance->save();

                    // Hapus dari rekap keterlambatan jika ada
                    LateAbsence::where('intern_id', $offline->intern_id)
                        ->whereDate('date', $date)
                        ->delete();
                }

                $offline->status = 'alpha';
                $offline->penalty_type = 'tanpa_ganti_jam';
                $offline->penalty_minutes = 0;
                $offline->penalty_notes = $penaltyNotes;
                $offline->penalty_by = $authorName;
                $offline->penalty_at = now();
                $offline->save();

                $successMessage = "Keputusan untuk {$internName}: Absen online DIANULIR SEBAGAI ALPHA PENUH (Tanpa Kompensasi Ganti Jam).";
            } elseif ($penaltyType === 'dimaafkan') {
                // Opsi 3: Klarifikasi Sah (Dimaafkan)
                if ($detailSchedule) {
                    $detailSchedule->attd_status_id = 2; // 2 = Hadir
                    $detailSchedule->isChangeSchedule = 0; // Reset hutang ganti jam
                    $detailSchedule->is_change_schedule_approved = 0;
                    $detailSchedule->save();
                }

                if ($attendance) {
                    // KEMBALIKAN KE KONDISI AWAL SEPERTI SEMULA:
                    // Waktu login tetap waktu awal dia login (start_time).
                    // Jam pulang (end_time) di-set KOSONG (null) agar sesi aktif kembali dan pemagang bisa melanjutkan absensi normal / pulang.
                    $attendance->end_time = null;
                    $attendance->adjusted_end_time = null;
                    $attendance->keterangan = $penaltyNotes ? "Klarifikasi sah diterima: {$penaltyNotes}" : null;
                    $attendance->save();

                    // Hapus data sanksi atau keterlambatan berbohong jika ada
                    LateAbsence::where('intern_id', $offline->intern_id)
                        ->whereDate('date', $date)
                        ->where('notes', 'like', '%Sanksi Berbohong%')
                        ->delete();
                }

                $offline->status = 'hadir';
                $offline->penalty_type = 'dimaafkan';
                $offline->penalty_minutes = 0;
                $offline->penalty_notes = $penaltyNotes;
                $offline->penalty_by = $authorName;
                $offline->penalty_at = now();
                $offline->save();

                $successMessage = "Klarifikasi untuk {$internName} DITERIMA: Sesi presensi dikembalikan ke jam login awal, jam pulang dikosongkan agar sistem kembali normal, dan status kehadiran disahkan sebagai Hadir Tepat Waktu.";
            }

            $routePrefix = (int) $user->role_id === 6 ? 'assistant.absen-offline.index' : 'admin.absen-offline.index';
            return redirect()->route($routePrefix, ['date' => $date])
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            Log::error('Gagal memproses sanksi presensi offline: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Gagal memproses sanksi: ' . $e->getMessage());
        }
    }
}
