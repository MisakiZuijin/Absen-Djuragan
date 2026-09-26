<?php

namespace App\Services\Attendance\State;

use App\DTO\AttendanceDTO;
use App\Helper\ActionResult;
use Illuminate\Support\Facades\Log;
use App\Repositories\Interface\AttendanceRepository;
use App\Repositories\Interface\DetailScheduleRepository;
use App\Repositories\Interface\UserRepository;
use App\Services\Attendance\AttendanceState;
use App\Services\LocationService;
use Carbon\Carbon;

class AttendanceOutState implements AttendanceState
{
    private UserRepository $userRepository;
    private AttendanceRepository $attendanceRepository;
    private DetailScheduleRepository $detailScheduleRepository;
    private LocationService $locationService;

    public function __construct(
        UserRepository $userRepository,
        AttendanceRepository $attendanceRepository,
        DetailScheduleRepository $detailScheduleRepository,
        LocationService $locationService
    ) {
        $this->userRepository = $userRepository;
        $this->attendanceRepository = $attendanceRepository;
        $this->detailScheduleRepository = $detailScheduleRepository;
        $this->locationService = $locationService;
    }

    public function handle(AttendanceDTO $data): ActionResult
    {
        try {
            // Get basic data
            $timeNow = $data->getTimeNow();
            $scheduleId = $data->getScheduleId();
            $detailScheduleId = $data->getDetailSchedule();

            // Get attendance record
            $attendanceTarget = $this->attendanceRepository->getById($data->getAttendanceId());
            if (!$attendanceTarget) {
                return new ActionResult(false, "Data presensi tidak ditemukan");
            }

            // Get detail schedule
            $detailSchedule = $this->detailScheduleRepository->find($detailScheduleId);
            if (!$detailSchedule) {
                return new ActionResult(false, "Detail jadwal tidak ditemukan");
            }

            $shift = $detailSchedule->shift;
            if (!$shift) {
                return new ActionResult(false, "Data shift tidak ditemukan");
            }

            $isWfhSchedule = $detailSchedule && strtolower($detailSchedule->work_type ?? '') === 'wfh';
            $user = $this->userRepository->findById($data->getUserId()) ?? \Illuminate\Support\Facades\Auth::user();
            $isGpsRequired = ($user && $user->is_gps_activate == 1) && (!$shift || $shift->is_gps_active == 1) && !$isWfhSchedule;

            // Validasi data lokasi jika GPS diwajibkan
            $latitude = $data->getLatitude();
            $longitude = $data->getLongitude();

            if ($isGpsRequired) {
                if ($latitude === null || $longitude === null) {
                    return new ActionResult(
                        false,
                        "Data lokasi tidak tersedia. Pastikan Anda mengizinkan akses GPS/lokasi pada perangkat Anda."
                    );
                }

                if (!is_numeric($latitude) || !is_numeric($longitude)) {
                    return new ActionResult(
                        false,
                        "Format data lokasi tidak valid."
                    );
                }

                // Location validation
                $mapsTrack = $this->locationService->checkIsInOfficeArea(
                    (float) $latitude,
                    (float) $longitude,
                    true
                );

                if (!$mapsTrack->isInArea) {
                    return new ActionResult(false, "Anda tidak berada di area kantor");
                }
            }

            // Time calculations
            $startTime = Carbon::parse($attendanceTarget->start_time);
            $currentTime = Carbon::parse($timeNow);
            $shiftEndTime = Carbon::parse($shift->end_time);

            // PREVENT EARLY CLOCK-OUT
            if ($currentTime->lt($shiftEndTime)) {
                return new ActionResult(
                    false,
                    "Anda tidak dapat pulang sebelum jam shift berakhir (Jam pulang: " . $shift->end_time . ")"
                );
            }

            // Calculate work duration
            $endTimeForCalc = $currentTime;
            $totalMinutes = $startTime->diffInMinutes($endTimeForCalc);
            $totalHours = $startTime->diffInHours($endTimeForCalc);

            // Prepare attendance data
            $attData = [
                "end_time" => $timeNow, // Always record actual clock-out time
                "total_min" => $totalMinutes,
                "total_hours" => $totalHours,
                "end_time_status" => $this->getClockOutStatus($timeNow, $shift->end_time),
                "end_time_message" => $data->getDescription(),
                "latitude_end" => $latitude,
                "longitude_end" => $longitude,
                "attd_status_id" => 2 // Present status
            ];

            // Update attendance record
            $updatedAttendance = $this->attendanceRepository->update($data->getAttendanceId(), $attData);

            // Tutup otomatis izin (mis. izin keluar) yang masih terbuka,
            // karena pemagang tidak lagi punya tombol "Kembali dari Izin"
            // untuk tipe leave.
            $openPermits = \App\Models\PermitLog::where('attendance_id', $data->getAttendanceId())
                ->whereNull('end_time')
                ->get();

            foreach ($openPermits as $permit) {
                $permitEnd = Carbon::now();
                $permit->update([
                    'end_time' => $permitEnd->format('Y-m-d H:i:s'),
                    'duration_in_minutes' => (int) ceil(Carbon::parse($permit->start_time)->diffInSeconds($permitEnd) / 60),
                ]);
            }

            \App\Helper\ActivityLogger::log(
                'UPDATE',
                'Presensi',
                'Pemagang ' . (auth()->user()?->profile?->full_name ?? auth()->user()?->username ?? 'User') . ' melakukan Absen Pulang.'
            );

            return new ActionResult(
                true,
                "Berhasil melakukan presensi pulang",
                [
                    "absenceHistory" => $updatedAttendance,
                    "schedule_id" => $scheduleId,
                    "detail_schedule_id" => $detailScheduleId,
                    "stage" => "all_done",
                    "shift" => $shift
                ]
            );
        } catch (\Throwable $e) {
            Log::error("Error in AttendanceOutState: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return new ActionResult(false, "Gagal melakukan presensi pulang: " . $e->getMessage(), null);
        }
    }

    /**
     * Determine clock-out status (on-time/late)
     */
    private function getClockOutStatus(string $clockOutTime, string $shiftEndTime): string
    {
        $clockOut = Carbon::parse($clockOutTime);
        $endTime = Carbon::parse($shiftEndTime);

        return $clockOut->gt($endTime) ? 'late' : 'on-time';
    }
}
