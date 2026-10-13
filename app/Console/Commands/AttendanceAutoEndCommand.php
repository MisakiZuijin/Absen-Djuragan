<?php

namespace App\Console\Commands;

use App\Helper\ActivityLogger;
use App\Models\Attendance;
use App\Models\ChangeTimeSession;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceAutoEndCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-end {date? : Tanggal presensi yang diproses (default: hari ini)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis menutup presensi dan sesi ganti jam yang belum selesai pada akhir hari (23:59:00)';

    /**
     * Execute the console command.
     */
    public function handle(AttendanceService $attendanceService)
    {
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $date = $this->argument('date') ?? Carbon::today($timezone)->toDateString();

        $this->info("🌙 Menjalankan eksekusi pulang otomatis untuk tanggal: {$date}");

        // =========================================================================
        // 1. PULANG OTOMATIS PRESENSI HARIAN
        // =========================================================================
        $isSpecificDate = $this->argument('date') !== null;
        $query = Attendance::whereNotNull('start_time')
            ->whereNull('end_time')
            ->where('is_auto_end', false)
            ->with(['intern.user.profile', 'detailSchedules.shift', 'intern.shift']);

        if ($isSpecificDate) {
            $query->whereDate('date', $date);
        } else {
            $query->whereDate('date', '<=', $date);
        }

        $pendingAttendances = $query->get();

        $autoEndCount = 0;
        $autoEndFailed = 0;
        $now = Carbon::now($timezone);

        foreach ($pendingAttendances as $attendance) {
            // Jika tanggalnya hari ini dan shift belum selesai (misal dipanggil manual siang hari), lewati
            $attdDateStr = $attendance->date ? Carbon::parse($attendance->date)->format('Y-m-d') : $date;
            $shift = $attendance->detailSchedules?->shift ?? $attendance->intern?->shift;
            if ($attdDateStr === Carbon::today($timezone)->toDateString() && $shift && $shift->end_time) {
                $shiftEndTime = Carbon::parse($attdDateStr . ' ' . $shift->end_time, $timezone);
                if ($shift->start_time && $shift->end_time && $shift->end_time < $shift->start_time) {
                    $shiftEndTime->addDay();
                }
                if ($now->lessThan($shiftEndTime)) {
                    continue; // Shift belum berakhir hari ini
                }
            }

            try {
                $result = $attendanceService->confirmAutoEndAttendance(
                    $attendance->id,
                    'Dipulangkan otomatis oleh sistem'
                );

                if ($result->isSuccess()) {
                    $autoEndCount++;
                    $name = $attendance->intern?->user?->profile?->full_name ?? "Intern #{$attendance->intern_id}";
                    $this->line("   ✅ Presensi dipulangkan otomatis: {$name}");
                } else {
                    $autoEndFailed++;
                    $this->warn("   ⚠️ Gagal auto-end presensi ID {$attendance->id}: {$result->getMessage()}");
                }
            } catch (\Throwable $e) {
                $autoEndFailed++;
                Log::error("AttendanceAutoEndCommand error on attendance ID {$attendance->id}: " . $e->getMessage());
                $this->error("   ❌ Error pada presensi ID {$attendance->id}: " . $e->getMessage());
            }
        }

        $this->info("📊 Presensi Harian: Berhasil ditutup {$autoEndCount}, Gagal: {$autoEndFailed}");

        // =========================================================================
        // 2. AUTO-CLOSE SESI GANTI JAM (CHANGE TIME) YANG TIDAK DITUTUP
        // =========================================================================
        $staleSessions = ChangeTimeSession::where('status', 'active')
            ->whereDate('session_date', '<=', $date)
            ->with(['intern.user.profile', 'shift'])
            ->get();

        $sessionCloseCount = 0;

        foreach ($staleSessions as $session) {
            DB::beginTransaction();
            try {
                $nowTime = '23:59:00';
                $sessionDateStr = $session->session_date ? $session->session_date->format('Y-m-d') : $date;
                $startDateTime = Carbon::parse($sessionDateStr . ' ' . $session->start_time, $timezone);
                $endDateTime = Carbon::parse($sessionDateStr . ' ' . $nowTime, $timezone);

                // Hitung menit kotor dan istirahat
                $grossMinutes = max(0, $endDateTime->diffInMinutes($startDateTime));
                $breakMinutes = (int) $session->total_break_minutes;

                if (!empty($session->break_time) && empty($session->back_time)) {
                    $breakStart = Carbon::parse($sessionDateStr . ' ' . $session->break_time, $timezone);
                    $breakMinutes = max(0, $endDateTime->diffInMinutes($breakStart));
                    $session->back_time = $nowTime;
                    $session->total_break_minutes = $breakMinutes;
                }

                // Batasi maksimal 7 jam 15 menit (435 menit)
                $netWorkMinutes = min(435, max(0, $grossMinutes - $breakMinutes));

                $session->update([
                    'end_time' => $nowTime,
                    'total_work_minutes' => $netWorkMinutes,
                    'total_break_minutes' => $breakMinutes,
                    'status' => 'pending_approval',
                    'end_time_message' => 'Sesi ditutup otomatis oleh sistem pada pukul 23:59:00 karena belum diselesaikan.',
                ]);

                $internName = $session->intern?->user?->profile?->full_name ?? 'Pemagang';
                ActivityLogger::log(
                    'UPDATE',
                    'Ganti Jam',
                    "Sistem menutup otomatis sesi ganti jam pemagang {$internName} pada pukul 23:59:00 (durasi: {$netWorkMinutes} menit).",
                    ['session_id' => $session->id, 'total_work_minutes' => $netWorkMinutes]
                );

                DB::commit();
                $sessionCloseCount++;
                $this->line("   ⏱️ Sesi ganti jam ditutup otomatis: {$internName} ({$netWorkMinutes} menit)");
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("AttendanceAutoEndCommand error on session ID {$session->id}: " . $e->getMessage());
                $this->error("   ❌ Error menutup sesi ganti jam ID {$session->id}: " . $e->getMessage());
            }
        }

        $this->info("📊 Sesi Ganti Jam: Berhasil ditutup otomatis {$sessionCloseCount}");

        if ($autoEndCount > 0 || $sessionCloseCount > 0) {
            ActivityLogger::log(
                'UPDATE',
                'Pulang Otomatis',
                "Eksekusi pulang otomatis harian selesai ({$autoEndCount} presensi dan {$sessionCloseCount} sesi ganti jam diproses).",
                [
                    'date' => $date,
                    'attendances_closed' => $autoEndCount,
                    'sessions_closed' => $sessionCloseCount
                ]
            );
        }

        return Command::SUCCESS;
    }
}
