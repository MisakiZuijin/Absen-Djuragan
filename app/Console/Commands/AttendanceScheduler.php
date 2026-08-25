<?php

namespace App\Console\Commands;

use App\Repositories\Interface\DetailScheduleRepository;
use App\Repositories\Interface\ShiftRepository;
use App\Services\AttendanceService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceScheduler extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:attd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule attendance based on shifts';

    protected $shiftRepository;
    protected $detailScheduleRepository;
    protected $attendanceService;

    public function __construct(DetailScheduleRepository $detailsScheduleRepository, ShiftRepository $shiftRepository, AttendanceService $attendanceService) {
        parent::__construct();
        $this->shiftRepository = $shiftRepository;
        $this->attendanceService = $attendanceService;
        $this->detailScheduleRepository = $detailsScheduleRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle() {

        try {
            $now = Carbon::now();
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

            $this->info('Attendance scheduling completed.');
        } catch (\Throwable $th) {
            Log::info("error $th");
        }
    }
}
