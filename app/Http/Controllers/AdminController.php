<?php

namespace App\Http\Controllers;

use App\Helper\LogConsole;
use App\Services\AttendanceService;
use App\Services\InternService;
use App\Services\PermitReasonService;
use App\Services\UserService;
use App\Utils\DateNow;
use Illuminate\Contracts\View\View;
use App\Models\Shift;
use App\Models\Office;
use App\Models\AttdStatus;
use App\Models\Intern;
use App\Models\Attendance;
use App\Models\PermitLog;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    protected $userService;
    protected $internService;
    protected $attendanceService;
    protected $permitReasonService;
    protected $scheduleService;

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

        $prayerRequestsCount = PermitLog::where('type', 'prayer')->whereNull('end_time')->whereHas('attendance', fn($q) => $q->whereDate('date', today()))->count();
        $leaveRequestsCount = PermitLog::where('type', 'leave')->whereNull('end_time')->whereHas('attendance', fn($q) => $q->whereDate('date', today()))->count();
        $toiletPermitsCount = PermitLog::where('type', 'toilet')->whereNull('end_time')->whereHas('attendance', fn($q) => $q->whereDate('date', today()))->count();

        $data['prayerRequestsCount'] = $prayerRequestsCount;
        $data['leaveRequestsCount'] = $leaveRequestsCount;
        $data['toiletPermitsCount'] = $toiletPermitsCount;

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

    public function reportView()
    {
        $userData = $this->userService->getUserLoggedData();
        $data = ["user" => $userData, "dateNow" => DateNow::getCurrentDate()];
        return view('admin.laporan')->with($data);
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

    public function prensenceDetailView($intern_id)
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
            $startDate = $request->query('start_date', $today->startOfDay()->toDateString());
            $endDate = $request->query('end_date', $today->endOfDay()->toDateString());
            $internName = $request->query('search');

            $interns = Intern::with(['user.profile', 'schedules.detailSchedules'])
                ->when($internName, function ($query) use ($internName) {
                    $query->whereHas('user.profile', fn($q) => $q->where('full_name', 'LIKE', '%' . $internName . '%'));
                })
                ->get();

            $internValue = [];
            foreach ($interns as $intern) {
                $attendanceData = $intern->schedules()
                    ->join('detail_schedules', 'schedules.id', '=', 'detail_schedules.schedule_id')
                    ->whereBetween('detail_schedules.date', [$startDate, $endDate])
                    ->selectRaw('SUM(CASE WHEN detail_schedules.attd_status_id = 5 THEN 1 ELSE 0 END) as absence, SUM(CASE WHEN detail_schedules.attd_status_id = 2 THEN 1 ELSE 0 END) as submitted, SUM(CASE WHEN detail_schedules.attd_status_id = 3 THEN 1 ELSE 0 END) as permits')
                    ->first();

                $internValue[] = [
                    'id' => $intern->id,
                    'name' => $intern->user->profile->full_name ?? '-',
                    'nip' => $intern->user->profile->NIP ?? '-',
                    'submitted' => $attendanceData->submitted ?? 0,
                    'absence' => $attendanceData->absence ?? 0,
                    'permits' => $attendanceData->permits ?? 0,
                ];
            }

            $pdf = PDF::loadView('pdf.all-report', compact('internValue', 'startDate', 'endDate'));
            return $pdf->download("report-{$startDate}-{$endDate}.pdf");
        } catch (\Throwable $th) {
            // Log error
        }
    }
}