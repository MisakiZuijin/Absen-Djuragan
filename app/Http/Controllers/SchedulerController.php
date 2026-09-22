<?php

namespace App\Http\Controllers;

use App\Helper\LogConsole;
use App\Repositories\Interface\ShiftRepository;
use Carbon\Carbon;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Log;

class SchedulerController extends Controller
{
    protected ShiftRepository $shiftRepository;
    protected AttendanceService $attendanceService;

    public function __construct(ShiftRepository $shiftRepository, AttendanceService $attendanceService)
    {
        $this->shiftRepository = $shiftRepository;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Schedule attendance for shifts
     */
    public function scheduleAttendance()
    {
        try {
            $now = Carbon::now('Asia/Jakarta');

            // for testing purpose
            // $now->hour(17)->minute(value: 20);

            $shifts = $this->shiftRepository->getAll();

            foreach ($shifts as $shift) {
                $startTime = Carbon::createFromFormat('H:i:s', $shift->start_time);
                $endTime = Carbon::createFromFormat('H:i:s', $shift->end_time);

                if ($now->greaterThan($startTime->copy()->addMinutes(30))) {
                    $this->attendanceService->setAllAttdStatusByShift(5, $shift->id, $now->toDateString());
                }

                if ($now->isSameMinute($endTime->copy()->addMinutes(20))) {

                    $this->attendanceService->setToEndTime($now->toDateString(), $shift->id, $now->format("H:i:s"));
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance scheduling completed.',
                'data' => null,
            ], 200);
        } catch (\Throwable $th) {
            Log::error("Error: $th");
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while scheduling attendance.',
                'data' => null,
            ], 500);
        }
    }
}
