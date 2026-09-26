<?php

namespace App\Http\Controllers;

use App\Services\AttendanceService;
use App\Services\InternService;
use App\Services\PermitReasonService;
use App\Services\UserService;
use App\Services\ScheduleService;
use App\Utils\DateNow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Models\User;
use App\Models\Shift;
use App\Models\Office;
use App\Models\AttdStatus;
use App\Models\Intern;
use App\Models\Attendance;
use App\Models\PermitLog;
use App\Models\Division;
use App\Models\School;
use App\Models\DetailSchedule;
use App\Models\OfflineAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    protected UserService $userService;
    protected InternService $internService;
    protected AttendanceService $attendanceService;
    protected PermitReasonService $permitReasonService;
    protected ScheduleService $scheduleService;

    public function __construct(
        UserService $userService,
        InternService $internService,
        AttendanceService $attendanceService,
        PermitReasonService $permitReasonService,
        ScheduleService $scheduleService
    ) {
        $this->userService = $userService;
        $this->internService = $internService;
        $this->attendanceService = $attendanceService;
        $this->permitReasonService = $permitReasonService;
        $this->scheduleService = $scheduleService;
    }

    public function homeView(): View
    {
        $this->attendanceService->markMissedSchedulesAsAlpha();
        $userData = $this->userService->getUserLoggedData();
        $internTotal = $this->internService->internTotal();
        $attendanceTotalToday = $this->attendanceService->getTotalInternAbsenceHome();

        $data = [
            "user" => $userData,
            "internTotal" => $internTotal->isSuccess() ? $internTotal->getData() : 0,
        ];

        if ($attendanceTotalToday->isSuccess()) {
            $data = array_merge($data,  $attendanceTotalToday->getData());
        } else {
            $attd = ["attendanceTotal" => 0, "absenceTotal" => 0, "permitTotal" => 0];
            $data = array_merge($data, $attd);
        }

        $permitCounts = PermitLog::whereNull('end_time')
            ->whereHas('attendance', fn($q) => $q->whereDate('date', today()))
            ->selectRaw("
                COUNT(CASE WHEN type = 'prayer' THEN 1 END) as prayer_count,
                COUNT(CASE WHEN type = 'leave' THEN 1 END) as leave_count,
                COUNT(CASE WHEN type = 'toilet' THEN 1 END) as toilet_count
            ")
            ->first();

        $data['prayerRequestsCount'] = (int) ($permitCounts->prayer_count ?? 0);
        $data['leaveRequestsCount'] = (int) ($permitCounts->leave_count ?? 0);
        $data['toiletPermitsCount'] = (int) ($permitCounts->toilet_count ?? 0);

        return view('admin.index')->with($data);
    }

    public function presenceView(Request $request): View
    {
        $this->attendanceService->markMissedSchedulesAsAlpha();
        $userData = $this->userService->getUserLoggedData();
        $permitCategories = $this->permitReasonService->getAllPermitCategory();
        $shifts = Shift::where('id', '!=', 1)->get();
        $attd_statuses = AttdStatus::where('id', '!=', 4)->get();
        $office = Office::all();

        $data = [
            "user" => $userData,
            "shifts" => $shifts,
            "attd_statuses" => $attd_statuses,
            "office" => $office,
            "dateNow" => DateNow::getCurrentDate(),
            "dateNowYMD" => DateNow::getCurrentDateYMD(),
            "listPermitCategory" => $permitCategories->isSuccess() ? $permitCategories->getData() : null,
        ];

        return view("admin.presensi")->with($data);
    }

    public function reportView(Request $request)
    {
        $userData = $this->userService->getUserLoggedData();
        if ($userData && !$userData->relationLoaded('profile')) {
            $userData->loadMissing('profile');
        }
        $currentUser = auth()->user() ?? $userData;
        if ($currentUser && !$currentUser->relationLoaded('profile')) {
            $currentUser->loadMissing('profile');
        }
        $isSuperAdmin = $currentUser && ($currentUser->isSuperAdmin() || (int) $currentUser->role_id === 7);

        $dateNow = DateNow::getCurrentDate();

        if (!$isSuperAdmin) {
            $data = [
                "user" => $userData,
                "dateNow" => $dateNow,
                "isSuperAdmin" => false,
            ];
            return view('admin.laporan')->with($data);
        }

        $superAdminData = $this->getSuperAdminReportData($request, $userData, $dateNow);

        return view('admin.laporan')->with($superAdminData);
    }

    /**
     * Menghitung dan menyusun data analitik performa eksekutif & audit presensi offline untuk Super Admin.
     */
    private function getSuperAdminReportData(Request $request, mixed $userData, string $dateNow): array
    {
        $today = Carbon::now();
        $startDate = $request->query('start_date', $today->copy()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', $today->toDateString());
        $divisionId = $request->query('division_id');
        $schoolId = $request->query('school_id');
        $search = $request->query('search');
        $activeTab = $request->query('tab', 'overview'); // overview, individual, offline, standard

        // Master filters
        $divisions = Division::orderBy('name')->get();
        $schools = School::get();

        // Query active interns
        $internsQuery = Intern::with(['user.profile', 'division', 'school', 'shift'])
            ->whereHas('user', function ($q) {
                $q->where('role_id', 3)->where('is_active', true);
            });

        if (!empty($divisionId)) {
            $internsQuery->where('division_id', $divisionId);
        }

        if (!empty($schoolId)) {
            $internsQuery->where('school_id', $schoolId);
        }

        if (!empty($search)) {
            $internsQuery->where(function ($q) use ($search) {
                $q->whereHas('user.profile', fn($p) => $p->where('full_name', 'like', "%{$search}%"))
                    ->orWhereHas('user', fn($u) => $u->where('username', 'like', "%{$search}%"))
                    ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        $interns = $internsQuery->get()->sortBy(fn($i) => $i->user?->profile?->full_name ?? $i->user?->name ?? '')->values();
        $internIds = $interns->pluck('id')->toArray();

        // Ambil data DetailSchedules dalam rentang tanggal untuk seluruh pemagang
        $detailSchedules = DetailSchedule::whereBetween('date', [$startDate, $endDate])
            ->whereDate('date', '<=', $today->toDateString())
            ->whereHas('schedule', fn($q) => $q->whereIn('intern_id', $internIds))
            ->with(['schedule', 'attendance.lateAbsences', 'shift'])
            ->get()
            ->groupBy(fn($ds) => $ds->schedule?->intern_id);

        // Ambil data Attendances langsung sebagai sumber kebenaran presensi online
        $allAttendances = Attendance::whereBetween('date', [$startDate, $endDate])
            ->whereIn('intern_id', $internIds)
            ->with('lateAbsences')
            ->get()
            ->groupBy(function ($att) {
                $d = $att->date ? (is_string($att->date) ? $att->date : Carbon::parse($att->date)->format('Y-m-d')) : '';
                return $att->intern_id . '_' . $d;
            });

        // Ambil data OfflineAttendances dalam rentang tanggal untuk seluruh pemagang dalam 1 query tunggal
        $rawOfflineAttendances = OfflineAttendance::whereBetween('date', [$startDate, $endDate])
            ->whereIn('intern_id', $internIds)
            ->with(['shift', 'office', 'adminUser'])
            ->get();

        // Hubungkan relasi intern dari koleksi $interns yang sudah dimuat di memori untuk mencegah lazy-loading N+1
        $internsKeyed = $interns->keyBy('id');
        $rawOfflineAttendances->each(function ($record) use ($internsKeyed) {
            if ($intern = $internsKeyed->get($record->intern_id)) {
                $record->setRelation('intern', $intern);
            }
        });

        $offlineAttendances = $rawOfflineAttendances->groupBy('intern_id');

        // Hitung performa perorangan
        $individualPerformances = [];
        $totalScheduledAll = 0;
        $totalSubmittedAll = 0;
        $totalOnTimeAll = 0;
        $totalLateAll = 0;
        $totalPermitAll = 0;
        $totalAlphaAll = 0;
        $totalUnrecordedAll = 0;
        $totalOfflineHadirAll = 0;
        $totalOfflineLateAll = 0;
        $totalOfflineAlphaAll = 0;
        $totalOfflinePenaltiesAll = 0;
        $totalOfflinePenaltyMinutesAll = 0;
        $totalTargetMinutesAll = 0;
        $totalActualMinutesAll = 0;
        $disciplineScoresSum = 0;

        foreach ($interns as $intern) {
            $internDetailSchedules = $detailSchedules->get($intern->id, collect());
            $internOfflineList = $offlineAttendances->get($intern->id, collect());

            $scheduledDays = $internDetailSchedules->count();
            $submittedDays = 0;
            $onTimeDays = 0;
            $lateDays = 0;
            $permitDays = 0;
            $alphaDays = 0;
            $unrecordedDays = 0;
            $actualWorkMinutes = 0;
            $targetWorkMinutes = 0;

            foreach ($internDetailSchedules as $ds) {
                $shift = $ds->shift ?? $intern->shift;
                $shiftMinutes = 435; // Default 7 jam 15 menit
                if ($shift && $shift->start_time && $shift->end_time) {
                    $st = Carbon::parse($shift->start_time);
                    $et = Carbon::parse($shift->end_time);
                    $shiftMinutes = max(60, $st->diffInMinutes($et));
                }
                $targetWorkMinutes += $shiftMinutes;

                $dsDateStr = $ds->date ? (is_string($ds->date) ? $ds->date : Carbon::parse($ds->date)->format('Y-m-d')) : '';
                $attd = $ds->attendance ?? $allAttendances->get($intern->id . '_' . $dsDateStr)?->first();
                $statusId = (int) $ds->attd_status_id;

                // Seseorang dianggap HADIR jika:
                // 1. Statusnya Hadir (2) atau Hadir Ganti Jam (4), ATAU
                // 2. Memiliki record presensi aktif dengan jam masuk (start_time)
                $isAttended = ($statusId === 2 || $statusId === 4) || (!empty($attd) && !empty($attd->start_time));

                if ($isAttended) {
                    $submittedDays++;
                    $lateMins = $attd?->lateAbsences?->sum('late_minutes') ?? 0;
                    $isLateCheckin = ($statusId === 4 || $lateMins > 0);

                    // Jika belum tercatat sebagai telat di database, periksa apakah jam absen masuk melebihi jam mulai shift
                    if (!$isLateCheckin && $attd && $attd->start_time && $shift && $shift->start_time) {
                        $attdTime = Carbon::parse($attd->start_time)->format('H:i:s');
                        $shiftTime = Carbon::parse($shift->start_time)->format('H:i:s');
                        if ($attdTime > $shiftTime) {
                            $isLateCheckin = true;
                            $diffMins = Carbon::parse($shiftTime)->diffInMinutes(Carbon::parse($attdTime));
                            $lateMins = max(1, $diffMins);
                        }
                    }

                    if ($isLateCheckin) {
                        $lateDays++;
                    } else {
                        $onTimeDays++;
                    }

                    if ($attd && $attd->start_time && $attd->end_time) {
                        $actualWorkMinutes += Carbon::parse($attd->start_time)->diffInMinutes(Carbon::parse($attd->end_time));
                    } elseif ($attd && $attd->start_time) {
                        $actualWorkMinutes += max(0, min($shiftMinutes, Carbon::parse($attd->start_time)->diffInMinutes(now())));
                    } else {
                        $actualWorkMinutes += $shiftMinutes;
                    }
                } elseif ($statusId === 3) {
                    $permitDays++;
                } elseif ($statusId === 5) {
                    $alphaDays++;
                } else {
                    // Status 1 (Dijadwalkan) dan belum ada presensi masuk (Belum Hadir)
                    $unrecordedDays++;
                }
            }

            // Statistik Offline
            $offHadir = $internOfflineList->where('status', 'hadir')->count();
            $offLate = $internOfflineList->where('status', 'terlambat')->count();
            $offAlpha = $internOfflineList->where('status', 'alpha')->count();
            $offTotal = $internOfflineList->count();
            $offPenaltyMins = (int) $internOfflineList->sum('penalty_minutes');
            $offPenaltiesCount = $internOfflineList->filter(fn($o) => !empty($o->penalty_type) && $o->penalty_type !== 'dimaafkan')->count();

            // Total Hutang Jam
            $lackMinutes = max(0, $targetWorkMinutes - $actualWorkMinutes) + $offPenaltyMins;
            $surplusMinutes = max(0, $actualWorkMinutes - $targetWorkMinutes);

            // Perhitungan Indeks Disiplin (0 - 100%)
            $evaluatedDays = max($submittedDays + $permitDays + $alphaDays, 1);
            $baseDiscipline = (($onTimeDays * 100) + ($lateDays * 70) + ($permitDays * 80)) / ($evaluatedDays * 100) * 100;
            $fraudDeduction = $offPenaltiesCount * 10;
            $disciplineScore = max(0, min(100, round($baseDiscipline - $fraudDeduction)));

            // Grade & Badge
            if ($disciplineScore >= 90) {
                $grade = 'A';
                $gradeLabel = 'Sangat Memuaskan';
                $gradeBadgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                $gradeBarClass = 'bg-emerald-500';
            } elseif ($disciplineScore >= 75) {
                $grade = 'B';
                $gradeLabel = 'Baik / Disiplin';
                $gradeBadgeClass = 'bg-blue-100 text-blue-800 border-blue-300';
                $gradeBarClass = 'bg-blue-500';
            } elseif ($disciplineScore >= 60) {
                $grade = 'C';
                $gradeLabel = 'Cukup / Perhatian';
                $gradeBadgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
                $gradeBarClass = 'bg-amber-500';
            } else {
                $grade = 'D';
                $gradeLabel = 'Kurang / Evaluasi';
                $gradeBadgeClass = 'bg-rose-100 text-rose-800 border-rose-300';
                $gradeBarClass = 'bg-rose-500';
            }

            $formattedTarget = sprintf('%dj %02dm', floor($targetWorkMinutes / 60), $targetWorkMinutes % 60);
            $formattedActual = sprintf('%dj %02dm', floor($actualWorkMinutes / 60), $actualWorkMinutes % 60);
            $formattedLack = sprintf('%dj %02dm', floor($lackMinutes / 60), $lackMinutes % 60);

            $individualPerformances[] = [
                'intern' => $intern,
                'id' => $intern->id,
                'name' => $intern->user?->profile?->full_name ?? $intern->user?->name ?? 'Tanpa Nama',
                'nip' => $intern->user?->profile?->NIP ?? $intern->nim ?? '-',
                'avatar' => $intern->user?->profile?->avatar ?? null,
                'division_name' => $intern->division?->name ?? 'Belum ada divisi',
                'school_name' => $intern->school?->name ?? '-',
                'scheduled_days' => $scheduledDays,
                'submitted_days' => $submittedDays,
                'on_time_days' => $onTimeDays,
                'late_days' => $lateDays,
                'permit_days' => $permitDays,
                'alpha_days' => $alphaDays,
                'unrecorded_days' => $unrecordedDays,
                'attendance_rate' => $scheduledDays > 0 ? round(($submittedDays / $scheduledDays) * 100, 1) : 0,
                'offline_total' => $offTotal,
                'offline_hadir' => $offHadir,
                'offline_late' => $offLate,
                'offline_alpha' => $offAlpha,
                'offline_penalty_minutes' => $offPenaltyMins,
                'offline_penalties_count' => $offPenaltiesCount,
                'target_minutes' => $targetWorkMinutes,
                'actual_minutes' => $actualWorkMinutes,
                'lack_minutes' => $lackMinutes,
                'target_formatted' => $formattedTarget,
                'actual_formatted' => $formattedActual,
                'lack_formatted' => $formattedLack,
                'discipline_score' => $disciplineScore,
                'grade' => $grade,
                'grade_label' => $gradeLabel,
                'grade_badge' => $gradeBadgeClass,
                'grade_bar' => $gradeBarClass,
            ];

            // Akumulator Keseluruhan
            $totalScheduledAll += $scheduledDays;
            $totalSubmittedAll += $submittedDays;
            $totalOnTimeAll += $onTimeDays;
            $totalLateAll += $lateDays;
            $totalPermitAll += $permitDays;
            $totalAlphaAll += $alphaDays;
            $totalUnrecordedAll += $unrecordedDays;
            $totalOfflineHadirAll += $offHadir;
            $totalOfflineLateAll += $offLate;
            $totalOfflineAlphaAll += $offAlpha;
            $totalOfflinePenaltiesAll += $offPenaltiesCount;
            $totalOfflinePenaltyMinutesAll += $offPenaltyMins;
            $totalTargetMinutesAll += $targetWorkMinutes;
            $totalActualMinutesAll += $actualWorkMinutes;
            $disciplineScoresSum += $disciplineScore;
        }

        $internsCount = count($interns);
        $avgDiscipline = $internsCount > 0 ? round($disciplineScoresSum / $internsCount, 1) : 0;
        $overallAttendanceRate = $totalScheduledAll > 0 ? round(($totalSubmittedAll / $totalScheduledAll) * 100, 1) : 0;
        $overallOnTimeRate = $totalSubmittedAll > 0 ? round(($totalOnTimeAll / max($totalSubmittedAll, 1)) * 100, 1) : 0;
        $totalDebtMinutesAll = max(0, $totalTargetMinutesAll - $totalActualMinutesAll) + $totalOfflinePenaltyMinutesAll;

        $kpis = [
            'total_interns' => $internsCount,
            'total_scheduled_all' => $totalScheduledAll,
            'total_submitted_all' => $totalSubmittedAll,
            'total_on_time_all' => $totalOnTimeAll,
            'total_late_all' => $totalLateAll,
            'total_permit_all' => $totalPermitAll,
            'total_alpha_all' => $totalAlphaAll,
            'total_unrecorded_all' => $totalUnrecordedAll,
            'attendance_rate' => $overallAttendanceRate,
            'on_time_rate' => $overallOnTimeRate,
            'total_offline_records' => $totalOfflineHadirAll + $totalOfflineLateAll + $totalOfflineAlphaAll,
            'total_offline_hadir' => $totalOfflineHadirAll,
            'total_offline_late' => $totalOfflineLateAll,
            'total_offline_alpha' => $totalOfflineAlphaAll,
            'total_offline_penalties' => $totalOfflinePenaltiesAll,
            'total_offline_penalty_minutes' => $totalOfflinePenaltyMinutesAll,
            'avg_discipline_score' => $avgDiscipline,
            'total_target_hours' => round($totalTargetMinutesAll / 60, 1),
            'total_actual_hours' => round($totalActualMinutesAll / 60, 1),
            'total_debt_hours' => round($totalDebtMinutesAll / 60, 1),
        ];

        // Breakdown Performa per Divisi
        $divisionPerformances = [];
        foreach ($divisions as $div) {
            $divInterns = collect($individualPerformances)->filter(fn($p) => $p['intern']->division_id === $div->id);
            if ($divInterns->count() > 0) {
                $divSched = $divInterns->sum('scheduled_days');
                $divSub = $divInterns->sum('submitted_days');
                $divOnTime = $divInterns->sum('on_time_days');
                $divLate = $divInterns->sum('late_days');
                $divPermit = $divInterns->sum('permit_days');
                $divAlpha = $divInterns->sum('alpha_days');
                $divUnrec = $divInterns->sum('unrecorded_days');
                $divOff = $divInterns->sum('offline_total');
                $divAvgDisc = round($divInterns->avg('discipline_score'), 1);

                $divisionPerformances[] = [
                    'division_id' => $div->id,
                    'division_name' => $div->name,
                    'intern_count' => $divInterns->count(),
                    'scheduled_days' => $divSched,
                    'submitted_days' => $divSub,
                    'attendance_rate' => $divSched > 0 ? round(($divSub / $divSched) * 100, 1) : 0,
                    'on_time_rate' => $divSub > 0 ? round(($divOnTime / max($divSub, 1)) * 100, 1) : 0,
                    'late_days' => $divLate,
                    'permit_days' => $divPermit,
                    'alpha_days' => $divAlpha,
                    'unrecorded_days' => $divUnrec,
                    'offline_total' => $divOff,
                    'avg_discipline' => $divAvgDisc,
                ];
            }
        }

        // Breakdown Tren Harian dalam rentang tanggal
        $dailyBreakdowns = [];
        $periodStart = Carbon::parse($startDate);
        $periodEnd = Carbon::parse($endDate)->min($today);

        $allDetailSchedulesByDate = $detailSchedules->flatten()->groupBy(fn($ds) => Carbon::parse($ds->date)->format('Y-m-d'));
        $allOfflineByDate = $offlineAttendances->flatten()->groupBy(fn($off) => Carbon::parse($off->date)->format('Y-m-d'));

        for ($d = $periodEnd->copy(); $d->greaterThanOrEqualTo($periodStart); $d->subDay()) {
            $curDateStr = $d->toDateString();
            $dayDetailSchedules = $allDetailSchedulesByDate->get($curDateStr, collect());
            $dayOffline = $allOfflineByDate->get($curDateStr, collect());

            $daySchedCount = $dayDetailSchedules->count();
            if ($daySchedCount === 0 && $dayOffline->count() === 0) {
                continue;
            }

            $dayHadir = 0;
            $dayOnTime = 0;
            $dayLate = 0;
            $dayPermit = 0;
            $dayAlpha = 0;
            $dayPending = 0;

            foreach ($dayDetailSchedules as $ds) {
                $statusId = (int) $ds->attd_status_id;
                $dsInternId = $ds->schedule?->intern_id;
                $attd = $ds->attendance ?? ($dsInternId ? $allAttendances->get($dsInternId . '_' . $curDateStr)?->first() : null);

                $isAttended = ($statusId === 2 || $statusId === 4) || (!empty($attd) && !empty($attd->start_time));

                if ($isAttended) {
                    $dayHadir++;
                    $shift = $ds->shift ?? $internsKeyed->get($dsInternId)?->shift;
                    $lateMins = $attd?->lateAbsences?->sum('late_minutes') ?? 0;
                    $isLateCheckin = ($statusId === 4 || $lateMins > 0);

                    // Jika belum tercatat sebagai telat di database, periksa apakah jam absen masuk melebihi jam mulai shift
                    if (!$isLateCheckin && $attd && $attd->start_time && $shift && $shift->start_time) {
                        $attdTime = Carbon::parse($attd->start_time)->format('H:i:s');
                        $shiftTime = Carbon::parse($shift->start_time)->format('H:i:s');
                        if ($attdTime > $shiftTime) {
                            $isLateCheckin = true;
                        }
                    }

                    if ($isLateCheckin) {
                        $dayLate++;
                    } else {
                        $dayOnTime++;
                    }
                } elseif ($statusId === 3) {
                    $dayPermit++;
                } elseif ($statusId === 5) {
                    $dayAlpha++;
                } else {
                    $dayPending++;
                }
            }

            $dayNamesIndo = [
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];

            $dailyBreakdowns[] = [
                'date' => $curDateStr,
                'date_formatted' => $d->translatedFormat('d M Y'),
                'day_name' => $dayNamesIndo[$d->format('l')] ?? $d->format('l'),
                'scheduled_count' => $daySchedCount,
                'present_count' => $dayHadir,
                'on_time_count' => $dayOnTime,
                'late_count' => $dayLate,
                'permit_count' => $dayPermit,
                'alpha_count' => $dayAlpha,
                'pending_count' => $dayPending,
                'offline_count' => $dayOffline->where('status', 'hadir')->count(),
                'offline_late_count' => $dayOffline->where('status', 'terlambat')->count(),
                'offline_alpha_count' => $dayOffline->where('status', 'alpha')->count(),
                'attendance_rate' => $daySchedCount > 0 ? round(($dayHadir / $daySchedCount) * 100, 1) : 0,
            ];
        }

        // Daftar Log Presensi Offline Terperinci (Tab 3) - gunakan koleksi memory yang sudah dimuat
        $offlineRecords = $rawOfflineAttendances->sortBy([
            ['date', 'desc'],
            ['check_time', 'desc']
        ])->values();

        return [
            "user" => $userData,
            "dateNow" => $dateNow,
            "isSuperAdmin" => true,
            "startDate" => $startDate,
            "endDate" => $endDate,
            "divisionId" => $divisionId,
            "schoolId" => $schoolId,
            "search" => $search,
            "activeTab" => $activeTab,
            "divisions" => $divisions,
            "schools" => $schools,
            "kpis" => $kpis,
            "individualPerformances" => $individualPerformances,
            "divisionPerformances" => $divisionPerformances,
            "dailyBreakdowns" => $dailyBreakdowns,
            "offlineRecords" => $offlineRecords,
        ];
    }

    public function tabelView()
    {
        $userData = $this->userService->getUserLoggedData();
        $autoResult = $this->attendanceService->shortAutomaticAttendance();
        $totalToday = $this->attendanceService->getCountAttendanceToday();
        $data = [
            "user" => $userData,
            "auto_attd_data" => $autoResult->getData(),
            "total_auto_end_today" =>  $totalToday->getData() ?? 0,
        ];
        return view('admin.table-user')->with($data);
    }

    public function prensenceDetailView(int|string $intern_id)
    {
        $userData = $this->userService->getUserLoggedData();
        $internData = $this->userService->getUserDataByInternId($intern_id);
        $schedule = $this->scheduleService->getScheduleByInternId($intern_id);
        $internTarget = $this->attendanceService->internTarget($intern_id);
        $permitCategories = $this->permitReasonService->getAllPermitCategory();
        $shifts = Shift::where('id', '!=', 1)->get();
        $attd_statuses = AttdStatus::where('id', '!=', 4)->get();
        $notes = Intern::where('id', $intern_id)->pluck('attention_message')->first() ?? '';

        $internTargetData = $internTarget->isSuccess() ? $internTarget->getData() : null;
        $target_in_minutes = 0;

        // Logika baru untuk mengubah format "572j 45m" menjadi angka menit (misal: 34365)
        if ($internTargetData && isset($internTargetData['target_time'])) {
            $targetString = $internTargetData['target_time'];
            if (preg_match('/(-?)(\d+)j\s*(\d+)m/', $targetString, $matches)) {
                $sign = ($matches[1] == '-') ? -1 : 1;
                $hours = (int) $matches[2];
                $minutes = (int) $matches[3];
                $target_in_minutes = ($hours * 60 + $minutes) * $sign;
            }
        }

        // Memasukkan semua variabel ke dalam array $data untuk dikirim ke view
        $data = [
            "notes" => $notes,
            "user" => $userData,
            "shifts" => $shifts,
            "attd_statuses" => $attd_statuses,
            "schedule_data" => $schedule->isSuccess() ? $schedule->getData() : null,
            "intern_data" => $internData->isSuccess() ? $internData->getData() : null,
            "intern_id" => $intern_id,
            "listPermitCategory" => $permitCategories->isSuccess() ? $permitCategories->getData() : null,
            "intern_target" => $internTargetData,
            "target_in_minutes" => $target_in_minutes, // WAJIB: Mengirim total menit ke view
        ];

        return view('admin.detail-presensi')->with($data);
    }

    public function reportDownload(Request $request)
    {
        try {
            $today = Carbon::now();
            $startDate = $request->query('start_date', $today->copy()->startOfMonth()->toDateString());
            $endDate = $request->query('end_date', $today->toDateString());
            $internName = $request->query('search');
            $divisionId = $request->query('division_id');
            $schoolId = $request->query('school_id');

            $currentUser = auth()->user() ?? $this->userService->getUserLoggedData();
            $isSuperAdmin = $currentUser && ($currentUser->isSuperAdmin() || (int) $currentUser->role_id === 7);

            $reportData = $this->getAttendanceReportData($startDate, $endDate, $divisionId, $schoolId, $internName, 'individual');

            $internValue = [];
            foreach ($reportData['individualPerformances'] as $perf) {
                $internValue[] = [
                    'id' => $perf['intern']->id,
                    'name' => $perf['intern']->user->profile->full_name ?? $perf['intern']->user->name ?? '-',
                    'nip' => $perf['intern']->user->profile->NIP ?? $perf['intern']->nim ?? '-',
                    'division' => $perf['intern']->division?->name ?? '-',
                    'school' => $perf['intern']->school?->name ?? '-',
                    'total_days' => $perf['scheduled_days'],
                    'submitted' => $perf['submitted_days'],
                    'on_time' => $perf['on_time_days'],
                    'late' => $perf['late_days'],
                    'absence' => $perf['alpha_days'],
                    'permits' => $perf['permit_days'],
                    'offline_hadir' => $perf['offline_hadir'],
                    'offline_late' => $perf['offline_late'],
                    'offline_alpha' => $perf['offline_alpha'],
                    'offline_penalties' => $perf['offline_penalties_count'],
                    'discipline_score' => $perf['discipline_score'],
                ];
            }

            $adminName = auth()->user()?->name ?? 'Admin';
            \App\Helper\ActivityLogger::log('EXPORT', 'Presensi', "Admin {$adminName} mengunduh Laporan Rekap Presensi PDF ({$startDate} s/d {$endDate})", [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_super_admin' => $isSuperAdmin,
            ]);

            $pdf = PDF::loadView('pdf.all-report', compact('internValue', 'startDate', 'endDate', 'isSuperAdmin'))->setPaper('a4', 'landscape');
            return $pdf->download("report-presensi-{$startDate}-{$endDate}.pdf");
        } catch (\Throwable $th) {
            Log::error('Download PDF Report error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return redirect()->back()->with('error', 'Gagal membuat dan mengunduh laporan PDF. Silakan coba beberapa saat lagi.');
        }
    }
}
