<?php

namespace App\Console\Commands;

use App\Repositories\Interface\DetailScheduleRepository;
use App\Repositories\Interface\ShiftRepository;
use App\Services\AttendanceService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceScheduler extends Command
{
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

    protected ShiftRepository $shiftRepository;
    protected DetailScheduleRepository $detailScheduleRepository;
    protected AttendanceService $attendanceService;

    public function __construct(DetailScheduleRepository $detailsScheduleRepository, ShiftRepository $shiftRepository, AttendanceService $attendanceService)
    {
        parent::__construct();
        $this->shiftRepository = $shiftRepository;
        $this->attendanceService = $attendanceService;
        $this->detailScheduleRepository = $detailsScheduleRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $now = Carbon::now('Asia/Jakarta');
            $today = $now->toDateString();
            $shifts = $this->shiftRepository->getAll();

            foreach ($shifts as $shift) {
                if (empty($shift->start_time) || empty($shift->end_time)) {
                    continue;
                }

                $startTime = Carbon::createFromFormat('H:i:s', $shift->start_time, 'Asia/Jakarta')->setDateFrom($now);
                $endTime = Carbon::createFromFormat('H:i:s', $shift->end_time, 'Asia/Jakarta')->setDateFrom($now);

                // Jika shift malam melewati tengah malam (end_time < start_time)
                if ($shift->end_time < $shift->start_time) {
                    $endTime->addDay();
                }

                if ($now->greaterThan($startTime->copy()->addMinutes(30))) {
                    $this->attendanceService->setAllAttdStatusByShift(5, $shift->id, $today);
                }

                // Toleransi waktu: jika sekarang sudah mencapai atau melewati 20 menit setelah jam pulang shift
                if ($now->greaterThanOrEqualTo($endTime->copy()->addMinutes(20))) {
                    $this->attendanceService->setToEndTime($today, $shift->id, $shift->end_time);
                }
            }

            $this->info('Attendance scheduling completed.');
        } catch (\Throwable $th) {
            Log::error("AttendanceScheduler error: " . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
        }
    }
}
