<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Shift;
use App\Models\Intern;
use App\Models\Office;
use App\Utils\DateNow;
use App\Helper\LogConsole;
use App\Models\Attendance;
use App\Models\Coordinate;
use App\Models\LateAbsence;
use App\Helper\ActionResult;
use Illuminate\Http\Request;
use App\Services\UserService;
use GuzzleHttp\Psr7\Response;
use App\Models\AdjustableAttd;
use App\Models\DetailSchedule;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mappers\AttendanceMapper;
use Illuminate\Support\Facades\DB;
use App\Services\AdjustableService;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use App\Http\Requests\AttendanceRequest;
use Illuminate\Support\Facades\Notification;
use App\Repositories\Interface\ShiftRepository;
use App\Http\Resources\InternAttendanceResource;
use App\Http\Requests\StorePermitPresenceRequest;
use App\Http\Requests\UpdatePermitPresenceRequest;
use App\Http\Requests\UpdateTimeAttendanceRequest;
use App\Http\Requests\UpdateStatusAttendanceRequest;
use App\Http\Resources\DetailInternAttendanceResource;
use App\Models\CheckinMessage; // [BARU] Import model CheckinMessage

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;
    protected ShiftRepository $shiftRepository;
    protected UserService $userService;
    protected AdjustableService $adjustableService;
    protected NotificationService $notificationService;

    public function __construct(
        AttendanceService $attendanceService,
        ShiftRepository $shiftRepository,
        AdjustableService $adjustableService,
        UserService $userService,
        NotificationService $notificationService
    ) {
        $this->attendanceService = $attendanceService;
        $this->shiftRepository = $shiftRepository;
        $this->adjustableService = $adjustableService;
        $this->userService = $userService;
        $this->notificationService = $notificationService;
    }

    public function actionPresence(AttendanceRequest $request)
    {
        $requestData = $request->validated();
        $result = $this->attendanceService->attendanceAction($requestData);

        if (!$result->isSuccess()) {
            return response()->json([
                'success' => false,
                'message' => $result->getMessage()
            ], 400);
        }

        // [BARU] Variabel untuk menyimpan data popup check-in
        $checkinPopupData = null;

        try {
            $absenData = $result->getData()['absenceHistory'];
            $attendanceId = $absenData->id;
            $shiftId = $absenData->shift_id;

            // Validasi data yang diperlukan
            if (!$shiftId || !$absenData->start_time) {
                Log::warning('Data shift atau start_time tidak tersedia', [
                    'shift_id' => $shiftId,
                    'start_time' => $absenData->start_time
                ]);
                return response()->json(['success' => true, 'message' => $result->getMessage(), 'data' => $result->getData()], 200);
            }

            // Dapatkan shift
            $shift = \App\Models\Shift::find($shiftId);
            if (!$shift) {
                Log::warning('Shift tidak ditemukan', ['shift_id' => $shiftId]);
                return response()->json(['success' => true, 'message' => $result->getMessage(), 'data' => $result->getData()], 200);
            }

            // PERBAIKAN UTAMA: Parse waktu dengan benar
            $timezone = config('app.timezone', 'Asia/Jakarta');

            // Parse waktu absen masuk
            $absenTime = Carbon::parse($absenData->start_time)->setTimezone($timezone);

            // Parse tanggal absen
            $attendanceDate = Carbon::parse($absenData->date)->setTimezone($timezone);

            // Buat waktu mulai shift untuk tanggal yang sama dengan absen
            $shiftStartTime = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $shift->start_time, $timezone);

            Log::debug('Perbandingan waktu untuk deteksi keterlambatan:', [
                'attendance_id' => $attendanceId,
                'absen_time' => $absenTime->format('Y-m-d H:i:s'),
                'shift_start_time' => $shiftStartTime->format('Y-m-d H:i:s'),
                'shift_name' => $shift->name
            ]);

            // Toleransi keterlambatan
            $toleranceMinutes = 5;

            // PERBAIKAN: Hitung selisih waktu dengan benar
            // Jika absenTime lebih besar dari shiftStartTime = telat
            if ($absenTime->greaterThan($shiftStartTime)) {
                $differenceMinutes = $absenTime->diffInMinutes($shiftStartTime);
                $isLate = $differenceMinutes > $toleranceMinutes;
            } else {
                // Absen lebih awal atau tepat waktu
                $differenceMinutes = 0;
                $isLate = false;
            }

            Log::debug('Hasil perhitungan keterlambatan:', [
                'difference_minutes' => $differenceMinutes,
                'tolerance_minutes' => $toleranceMinutes,
                'is_late' => $isLate,
                'absen_vs_shift' => $absenTime->format('H:i:s') . ' vs ' . $shiftStartTime->format('H:i:s')
            ]);

            // ============================================================
            // [BARU] Siapkan data popup check-in berdasarkan status
            // ============================================================
            $popupMsg = $isLate
                ? CheckinMessage::getLateMessage()
                : CheckinMessage::getOnTimeMessage();

            if ($popupMsg) {
                $checkinPopupData = [
                    'type'    => $isLate ? 'late' : 'on_time',
                    'message' => $popupMsg->message,
                    'image'   => $popupMsg->image ? asset('checkin-images/' . $popupMsg->image) : null,
                ];
            } else {
                // Fallback jika belum ada setting di database
                $checkinPopupData = [
                    'type'    => $isLate ? 'late' : 'on_time',
                    'message' => $isLate
                        ? 'Anda terlambat hari ini. Harap datang tepat waktu!'
                        : 'Selamat datang! Anda tepat waktu hari ini 🎉',
                    'image'   => null,
                ];
            }
            // ============================================================

            if ($isLate) {
                // Durasi telat = selisih waktu dikurangi toleransi
                $lateMinutes = $differenceMinutes - $toleranceMinutes;

                // Cek apakah sudah ada record untuk attendance ini
                $existingLateAbsence = LateAbsence::where('attendance_id', $attendanceId)->first();

                if (!$existingLateAbsence) {
                    $lateAbsence = LateAbsence::create([
                        'intern_id' => $absenData->intern_id,
                        'shift_id' => $shift->id,
                        'attendance_id' => $attendanceId,
                        'date' => $attendanceDate->format('Y-m-d'),
                        'absen_time' => $absenTime,
                        'scheduled_time' => $shift->start_time,
                        'late_minutes' => $lateMinutes,
                        'status' => 'telat',
                        'type' => 'checkin'
                    ]);

                    Log::info('Data keterlambatan berhasil disimpan:', [
                        'late_absence_id' => $lateAbsence->id,
                        'intern_id' => $absenData->intern_id,
                        'attendance_id' => $attendanceId,
                        'date' => $attendanceDate->format('Y-m-d'),
                        'late_minutes' => $lateMinutes,
                        'shift_start' => $shiftStartTime->format('H:i:s'),
                        'actual_time' => $absenTime->format('H:i:s'),
                        'difference' => $differenceMinutes . ' menit'
                    ]);

                    // Kirim notifikasi jika ada
                    $this->sendLateNotification($lateAbsence);
                } else {
                    Log::info('Data keterlambatan sudah ada', [
                        'attendance_id' => $attendanceId,
                        'existing_id' => $existingLateAbsence->id
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error dalam proses keterlambatan: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

        // ============================================================
        // [BARU] Flash data popup ke session agar tampil di view
        // ============================================================
        if ($checkinPopupData) {
            session()->flash('checkin_popup', $checkinPopupData);
        }
        // ============================================================

        return response()->json(['success' => true, 'message' => $result->getMessage(), 'data' => $result->getData()], 200);
    }

    // Method untuk mengirim notifikasi keterlambatan
    private function sendLateNotification(LateAbsence $lateAbsence)
    {
        try {
            // Dapatkan data intern
            $intern = $lateAbsence->intern;
            $shift = $lateAbsence->shift;

            if (!$intern || !$shift) {
                Log::warning('Data intern atau shift tidak ditemukan untuk notifikasi', [
                    'late_absence_id' => $lateAbsence->id
                ]);
                return;
            }

            // Format pesan notifikasi
            $message = "⚠️ Peringatan Keterlambatan!\n" .
                "Nama: " . ($intern->user->profile->full_name ?? 'Unknown') . "\n" .
                "Shift: " . $shift->name . " (" . $shift->start_time . " - " . $shift->end_time . ")\n" .
                "Waktu Absen: " . $lateAbsence->absen_time->format('H:i:s') . "\n" .
                "Terlambat: " . $lateAbsence->late_minutes . " menit\n" .
                "Tanggal: " . $lateAbsence->absen_time->format('d-m-Y');

            Log::info('Notifikasi keterlambatan: ' . $message);

            Notification::create([
                'user_id' => $intern->user_id,
                'type' => 'late_attendance',
                'message' => $message,
                'data' => [
                    'late_absence_id' => $lateAbsence->id,
                    'late_minutes' => $lateAbsence->late_minutes
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi keterlambatan: ' . $e->getMessage());
        }
    }

    public function actionAttendaceReport(Request $request)
    {
        $result = $this->attendanceService->attendanceReport($request);

        $data = [
            'reportData' => [],
            'totalPages' => 0,
        ];

        if ($result->isSuccess()) {
            $responseData = $result->getData();
            $data = array_merge($data, $responseData);
        }

        return response()->json($data);
    }

    public function updateTime(UpdateTimeAttendanceRequest $updateTimeAttendanceRequest, int $id)
    {
        $result = $this->attendanceService->updateTime($updateTimeAttendanceRequest, $id);

        if ($result->isSuccess()) {
            return redirect()->back()->with('status', 'Data Waktu berhasil diupdate!');
        } else {
            return redirect()->back()->with('error', $result->getMessage());
        }
    }

    public function updateStatus(UpdateStatusAttendanceRequest $updateStatusAttendanceRequest, int $id)
    {
        $this->attendanceService->updateStatus($updateStatusAttendanceRequest, $id);

        return redirect()->back()->with('status', 'Data Status berhasil diupdate!');
    }

    public function updateStatusAdjustable(Request $request, int $id)
    {
        $request->validate([
            'is_approved' => 'required',
        ]);

        $adjustableAttd = AdjustableAttd::find($id);

        if (!$adjustableAttd) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $adjustableAttd->is_approved = $request->input('is_approved');

        $adjustableAttd->save();

        return redirect()->back()->with('status', 'Data Status berhasil diupdate!');
    }

    public function attendaceAdmin(Request $request)
    {
        $this->attendanceService->markMissedSchedulesAsAlpha();

        $attendanceTotalToday = $this->attendanceService->getTotalInternAbsence($request);
        $listAttendaceToday = $this->attendanceService->getInternAttendance($request);
        $resultValue = $listAttendaceToday->isSuccess() ? $listAttendaceToday->getData() : null;

        $data = [
            "listAttendance" => $resultValue["data"] ?? [],
            "dateNow" => DateNow::getCurrentDate(),
            "dateNowYMD" => DateNow::getCurrentDateYMD(),
            "attendanceTotal" => $attendanceTotalToday->isSuccess() ? $attendanceTotalToday->getData() : null,
        ];

        return new InternAttendanceResource($data, $resultValue["pagination"] ?? []);
    }

    public function attendanceDetailAdmin(Request $request)
    {
        Log::info('Attendance detail request received:', [
            'intern_id' => $request->get('intern_id'),
            'page' => $request->get('page'),
            'per_page' => $request->get('per_page'),
            'all_params' => $request->all()
        ]);

        try {
            $result = $this->attendanceService->detailAttendanceReport($request);

            Log::info('Service result:', [
                'is_success' => $result->isSuccess(),
                'message' => $result->getMessage(),
                'has_data' => $result->getData() !== null
            ]);

            if (!$result->isSuccess()) {
                Log::error('Service returned error:', [
                    'message' => $result->getMessage()
                ]);
                return response()->json([
                    'status' => false,
                    'status_code' => 400,
                    'message' => $result->getMessage(),
                    'data' => null,
                    'meta' => null,
                ], 400);
            }

            return new DetailInternAttendanceResource($result->getData());
        } catch (\Exception $e) {
            Log::error('Exception in attendanceDetailAdmin:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Internal server error: ' . $e->getMessage(),
                'data' => null,
                'meta' => null,
            ], 500);
        }
    }

    public function testDetail(Request $request)
    {
        try {
            $internId = $request->get('intern_id');

            $intern = \App\Models\Intern::find($internId);
            if (!$intern) {
                return response()->json([
                    'status' => false,
                    'message' => 'Intern not found',
                    'intern_id' => $internId
                ]);
            }

            $attendances = \App\Models\Attendance::where('intern_id', $internId)
                ->limit(5)
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Test successful',
                'intern' => $intern,
                'attendances_count' => $attendances->count(),
                'attendances' => $attendances
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function updatePermitPresence(UpdatePermitPresenceRequest $updatePermitPresenceRequest)
    {
        $this->attendanceService->updatePermitPresence($updatePermitPresenceRequest);

        return redirect()->back()->with('status', 'Data status kehadiran berhasil diperbarui!');
    }

    public function addPermitPresenceadmin(StorePermitPresenceRequest $storePermitPresenceRequest)
    {
        $this->attendanceService->createPermitPresence($storePermitPresenceRequest);

        return redirect()->back()->with('status', 'Data keterangan izin berhasil disimpan!');
    }

    public function locationUser(Request $request)
    {
        $office_id = $request->query('office_id');

        $coordinates = Coordinate::where('office_id', $office_id)
            ->where('is_main', 1)
            ->get(['latitude', 'longitude']);

        $officeName = Office::where('id', $office_id)->value('name');

        $lat_ = $request->query('lat_start');
        $lat_start = $request->query('lat_start');
        $long_start = $request->query('long_start');
        $lat_end = $request->query('lat_end');
        $long_end = $request->query('long_end');

        return view("admin.maps-location-user", compact('lat_start', 'long_start', 'lat_end', 'long_end', 'coordinates', 'officeName'));
    }

    public function updateShift(Request $request)
    {
        $this->attendanceService->updateShift($request);

        return redirect()->back()->with('status', 'Data Shift berhasil diperbarui!');
    }

    public function storeNote(Request $request, int $id)
    {
        $this->attendanceService->storeNote($request, $id);

        return redirect()->back()->with('status', 'Catatan berhasil ditambahkan.');
    }

    public function show(int $internId)
    {
        $intern = Intern::with(['schedules.detailSchedules.logActivity'])
            ->findOrFail($internId);

        $profileName = $intern->user->profile->full_name ?? 'No Profile Name';

        $logActivities = [];

        foreach ($intern->schedules as $schedule) {
            foreach ($schedule->detailSchedules as $detailSchedule) {
                if ($detailSchedule->logActivity) {
                    $logActivities[] = $detailSchedule->logActivity;
                }
            }
        }

        return view('admin.log-activity', compact('logActivities', 'internId', 'profileName'));
    }

    public function reportUserPDF(int $internId)
    {
        $attdStatusId = request('attd_status_id');

        $intern = Intern::with([
            'schedules.detailSchedules' => function ($query) use ($attdStatusId) {
                if ($attdStatusId) {
                    $query->where('attd_status_id', $attdStatusId);
                }
            },
            'schedules.detailSchedules.attdStatus',
            'schedules.detailSchedules.attendance',
            'schedules.detailSchedules.shift'
        ])->findOrFail($internId);

        $profileName = $intern->user->profile->full_name ?? 'No Profile Name';

        $groupedData = [];
        $totalMinutes = 0;
        $totalExcessMinutes = 0;
        $totalDeficitMinutes = 0;

        foreach ($intern->schedules as $schedule) {
            foreach ($schedule->detailSchedules as $detailSchedule) {
                $shiftMinutes = $detailSchedule->shift->total_time_in_minute ?? 0;
                $attendance = $detailSchedule->attendance;
                $adjustableAttendance = AdjustableAttd::where('detail_schedule_id', $detailSchedule->id)->get();

                $date = $attendance->date ?? ($adjustableAttendance->first()->date ?? null);
                $date = $date ? Carbon::parse($date)->format('d-m-Y') : '-';

                if (!isset($groupedData[$date])) {
                    $groupedData[$date] = [];
                }

                $totalMin = $attendance->total_min ?? 0;
                $totalBreakMin = $attendance->total_break_min ?? 0;

                $isExcused = \App\Helper\TimeHelper::isApprovedExcusedLeave($detailSchedule);

                if ($isExcused) {
                    $workingMinutes = $shiftMinutes;
                    $difference = 0;
                } else {
                    $workingMinutes = $totalMin - $totalBreakMin;
                    $difference = $shiftMinutes - $workingMinutes;
                }

                $hours = floor($workingMinutes / 60);
                $minutes = $workingMinutes % 60;

                $formattedWorkingTime = "{$hours}j {$minutes}m";

                $differenceFormatted = $difference > 0
                    ? '-' . floor($difference / 60) . 'j ' . ($difference % 60) . 'm'
                    : ($difference < 0
                        ? '+' . floor(abs($difference) / 60) . 'j ' . (abs($difference) % 60) . 'm'
                        : '0j 0m');

                $adjustableData = $adjustableAttendance->map(function ($adj) {
                    $totalMin = $adj->total_min;
                    $totalBreakMin = $adj->total_break_min;
                    $workingMinutes = $totalMin - $totalBreakMin;

                    $hours = floor($workingMinutes / 60);
                    $minutes = $workingMinutes % 60;
                    $formattedWorkingTime = "{$hours}j {$minutes}m";

                    return [
                        'start_time' => $adj->start_time,
                        'end_time' => $adj->end_time,
                        'break_time' => $adj->break_time,
                        'back_time' => $adj->back_time,
                        'formatted_duration' => $formattedWorkingTime,
                    ];
                });

                $attdStatus = $detailSchedule->attdStatus->name ?? '-';

                if ($detailSchedule->attdStatus && $detailSchedule->attdStatus->id == 3) {
                    if ($detailSchedule->isChangeSchedule == 1 || $isExcused) {
                        $attdStatus = 'Izin (Tidak Ganti Jam)';
                    } elseif ($detailSchedule->isChangeSchedule == 2) {
                        $attdStatus = 'Izin (Ganti Jam)';
                    }
                }

                $groupedData[$date][] = [
                    'attendance' => $attendance,
                    'adjustable' => $adjustableData,
                    'attdStatus' => $attdStatus,
                    'shift_minutes' => $shiftMinutes,
                    'difference' => $differenceFormatted,
                    'total_time' => $formattedWorkingTime,
                ];
            }
        }

        $totalHours = floor($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;

        $data = [
            'profileName' => $profileName,
            'groupedData' => $groupedData,
            'totalHours' => $totalHours,
            'remainingMinutes' => $remainingMinutes,
            'totalExcessMinutes' => $totalExcessMinutes,
            'totalDeficitMinutes' => $totalDeficitMinutes,
        ];

        $pdf = PDF::loadView('pdf.report-user', $data);
        return $pdf->download("report-{$profileName}.pdf");
    }

    public function reportPDF()
    {
        $date = request('date', Carbon::today()->format('Y-m-d'));

        $name = request('name');
        $attdStatusId = request('attd_status_id');
        $shiftId = request('shift_id');
        $officeId = request('office_id');

        $users = Intern::with([
            'user.profile',
            'schedules.detailSchedules' => function ($query) use ($date, $attdStatusId, $shiftId, $officeId) {
                $query->whereDate('date', $date);

                if (!is_null($attdStatusId)) {
                    $query->where('attd_status_id', $attdStatusId);
                }

                if (!is_null($officeId)) {
                    $query->where('office_id', $officeId);
                }

                if (!is_null($shiftId)) {
                    $query->where('shift_id', $shiftId);
                }
            },
            'schedules.detailSchedules.attdStatus',
            'schedules.detailSchedules.attendance',
            'schedules.detailSchedules.shift'
        ])
            ->when(!is_null($name), function ($query) use ($name) {
                $query->whereHas('user.profile', function ($query) use ($name) {
                    $query->where('full_name', 'LIKE', "%{$name}%");
                });
            })
            ->get();

        $groupedData = [];

        foreach ($users as $intern) {
            $profileName = $intern->user->profile->full_name ?? 'No Profile Name';

            foreach ($intern->schedules as $schedule) {
                foreach ($schedule->detailSchedules as $detailSchedule) {
                    $shiftMinutes = $detailSchedule->shift->total_time_in_minute ?? 0;
                    $attendance = $detailSchedule->attendance;
                    $adjustableAttendance = AdjustableAttd::where('detail_schedule_id', $detailSchedule->id)->get();

                    $totalMin = $attendance->total_min ?? 0;
                    $totalBreakMin = $attendance->total_break_min ?? 0;

                    $isExcused = \App\Helper\TimeHelper::isApprovedExcusedLeave($detailSchedule);

                    if ($isExcused) {
                        $workingMinutes = $shiftMinutes;
                        $difference = 0;
                    } else {
                        $workingMinutes = $totalMin - $totalBreakMin;
                        $difference = $shiftMinutes - $workingMinutes;
                    }

                    $hours = floor($workingMinutes / 60);
                    $minutes = $workingMinutes % 60;

                    $formattedWorkingTime = "{$hours}j {$minutes}m";

                    $differenceFormatted = $difference > 0
                        ? '-' . floor($difference / 60) . 'j ' . ($difference % 60) . 'm'
                        : ($difference < 0
                            ? '+' . floor(abs($difference) / 60) . 'j ' . (abs($difference) % 60) . 'm'
                            : '0j 0m');

                    $adjustableData = $adjustableAttendance->map(function ($adj) {
                        $totalMin = $adj->total_min;
                        $totalBreakMin = $adj->total_break_min;
                        $workingMinutes = $totalMin - $totalBreakMin;

                        $hours = floor($workingMinutes / 60);
                        $minutes = $workingMinutes % 60;
                        $formattedWorkingTime = "{$hours}j {$minutes}m";

                        return [
                            'start_time' => $adj->start_time,
                            'end_time' => $adj->end_time,
                            'break_time' => $adj->break_time,
                            'back_time' => $adj->back_time,
                            'formatted_duration' => $formattedWorkingTime,
                            'total_time' => $formattedWorkingTime,
                        ];
                    });

                    $attdStatus = $detailSchedule->attdStatus->name ?? '-';

                    if ($detailSchedule->attdStatus && $detailSchedule->attdStatus->id == 3) {
                        if ($detailSchedule->isChangeSchedule == 1 || $isExcused) {
                            $attdStatus = 'Izin (Tidak Ganti Jam)';
                        } elseif ($detailSchedule->isChangeSchedule == 2) {
                            $attdStatus = 'Izin (Ganti Jam)';
                        }
                    }

                    $groupedData[$profileName][] = [
                        'attendance' => $attendance,
                        'adjustable' => $adjustableData,
                        'attdStatus' => $attdStatus,
                        'shift_minutes' => $shiftMinutes,
                        'working_minutes' => $workingMinutes,
                        'difference' => $differenceFormatted,
                        'total_time' => $formattedWorkingTime,
                    ];
                }
            }
        }

        $data = [
            'date' => Carbon::parse($date)->format('d-m-Y'),
            'groupedData' => $groupedData,
        ];

        $pdf = PDF::loadView('pdf.report-all-users', $data);
        return $pdf->download("report-{$date}.pdf");
    }

    public function detailAutoAttendance(int $id)
    {
        $userData = $this->userService->getUserLoggedData();
        $autoResult = $this->attendanceService->shortAutomaticAttendance(name: $id);
        $countResult = $this->attendanceService->getCountAutomaticAttendance($id);
        $data = [
            "user" => $userData,
            "id" => $id,
            "auto_attd_data" => $autoResult->getData(),
            "count" => $countResult->getData()
        ];

        return view("admin.detail_auto_attendance")->with($data);
    }

    public function updatestatusattd(int $id)
    {
        $detailSchedule = DetailSchedule::find($id);
        if (!$detailSchedule) {
            return redirect()->back()->with('error', 'Data tidak ditemukan!');
        }
        if ($detailSchedule->attd_status_id == 2) {
            $detailSchedule->attd_status_id = 5;
        } else {
            $detailSchedule->attd_status_id = 2;
        }
        $detailSchedule->save();

        return redirect()->back()->with('status', 'Status berhasil diperbarui!');
    }

    public function reset(int $id)
    {
        Attendance::where('id', $id)->update([
            'start_time' => null,
            'break_time' => null,
            'back_time' => null,
            'permit_start' => null,
            'permit_back' => null,
            'end_time' => null,
            'total_min' => 0,
            'total_break_min' => 0,
            'total_permit_min' => 0,
            'start_time_message' => null,
            'break_time_message' => null,
            'back_time_message' => null,
            'permit_start_message' => null,
            'permit_back_message' => null,
            'end_time_message' => null,
            'latitude_start' => null,
            'longitude_start' => null,
            'latitude_end' => null,
            'longitude_end' => null,
        ]);

        return redirect()->back()->with('status', 'Data berhasil direset!');
    }

    public function delete(int $id)
    {
        try {
            DB::beginTransaction();

            $detailSchedule = DetailSchedule::find($id);
            if (!$detailSchedule) {
                return redirect()->back()->with('error', 'Data tidak ditemukan!');
            }

            $attendanceId = $detailSchedule->attendance_id;

            AdjustableAttd::where('detail_schedule_id', $id)->delete();

            $detailSchedule->delete();

            if ($attendanceId) {
                Attendance::where('id', $attendanceId)->delete();
            }

            DB::commit();
            return redirect()->back()->with('status', 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting schedule: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function sendAlphaNotification(int $id)
    {
        $detailSchedule = DetailSchedule::find($id);

        if (!$detailSchedule) {
            return response()->json(['message' => 'Jadwal presensi tidak ditemukan.'], 404);
        }

        if ($detailSchedule->attd_status_id != 5) {
            return response()->json(['message' => 'Hanya bisa mengirim notifikasi untuk siswa yang berstatus Alpha.'], 422);
        }

        $result = $this->notificationService->notifyOutsidersForAlpha($detailSchedule);

        if ($result->isSuccess()) {
            return response()->json([
                'success' => true,
                'message' => $result->getMessage()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result->getMessage()
        ], 500);
    }

    public function sendBulkAlphaNotifications(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $result = $this->notificationService->notifyAllOutsidersForAlphaToday($request->date);

        return response()->json([
            'success' => $result->isSuccess(),
            'message' => $result->getMessage()
        ], $result->isSuccess() ? 200 : 500);
    }

    public function sendPermitNotification(int $id)
    {
        $detailSchedule = DetailSchedule::find($id);

        if (!$detailSchedule) {
            return response()->json(['message' => 'Jadwal presensi tidak ditemukan.'], 404);
        }

        if ($detailSchedule->attd_status_id != 3) {
            return response()->json(['message' => 'Hanya bisa mengirim notifikasi untuk siswa yang berstatus Izin.'], 422);
        }

        $result = $this->notificationService->notifyOutsidersForPermit($detailSchedule);

        if ($result->isSuccess()) {
            return response()->json([
                'success' => true,
                'message' => $result->getMessage()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result->getMessage()
        ], 500);
    }

    public function sendBulkPermitNotifications(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $result = $this->notificationService->notifyAllOutsidersForPermitToday($request->date);

        return response()->json([
            'success' => $result->isSuccess(),
            'message' => $result->getMessage()
        ], $result->isSuccess() ? 200 : 500);
    }

    public function deleteAdjustableAttendance(int $id)
    {
        try {
            DB::beginTransaction();

            $adjustableAttd = AdjustableAttd::find($id);

            if (!$adjustableAttd) {
                return redirect()->back()->with('error', 'Data adjustable attendance tidak ditemukan!');
            }

            $detailScheduleId = $adjustableAttd->detail_schedule_id;

            $adjustableAttd->delete();

            DB::commit();
            return redirect()->back()->with('status', 'Data adjustable attendance berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting adjustable attendance: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data adjustable attendance: ' . $e->getMessage());
        }
    }

    public function restoreAdjustableAttendance(int $id)
    {
        try {
            DB::beginTransaction();

            $adjustableAttd = AdjustableAttd::find($id);

            if (!$adjustableAttd) {
                return redirect()->back()->with('error', 'Data adjustable attendance tidak ditemukan!');
            }

            $adjustableAttd->is_approved = null;
            $adjustableAttd->save();

            DB::commit();
            return redirect()->back()->with('status', 'Data adjustable attendance berhasil di-restore!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error restoring adjustable attendance: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal restore data adjustable attendance: ' . $e->getMessage());
        }
    }
}
