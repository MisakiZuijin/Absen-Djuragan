<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ChangeTimeSession;
use App\Models\ChangeTimeSessionTarget;
use App\Models\DetailSchedule;
use App\Models\Attendance;
use App\Models\Shift;
use App\Models\Office;

class MigrateLegacyChangeTimeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ganti-jam:migrate-legacy {--force : Force migration without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate historical data from legacy adjustable_attds table to new change_time_sessions and change_time_session_targets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== MIGRASI DATA HISTORIS GANTI JAM (adjustable_attds -> change_time_sessions) ===');

        if (!DB::getSchemaBuilder()->hasTable('adjustable_attds')) {
            $this->warn('Tabel legacy adjustable_attds tidak ditemukan. Migrasi dibatalkan.');
            return 0;
        }

        $legacyCount = DB::table('adjustable_attds')->count();
        $this->info("Ditemukan {$legacyCount} data pada tabel legacy adjustable_attds.");

        if ($legacyCount === 0) {
            $this->info('Tidak ada data yang perlu dimigrasikan.');
            return 0;
        }

        if (!$this->option('force') && !$this->confirm('Apakah Anda ingin melanjutkan proses migrasi data ke skema baru?')) {
            $this->info('Migrasi dibatalkan.');
            return 0;
        }

        $defaultShift = Shift::first();
        $defaultOffice = Office::first();

        $migratedCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();

        try {
            $legacyRecords = DB::table('adjustable_attds')->orderBy('id')->get();

            foreach ($legacyRecords as $legacy) {
                // Check if target schedule exists
                $schedule = DetailSchedule::find($legacy->detail_schedule_id);
                if (!$schedule) {
                    $this->warn("DetailSchedule ID {$legacy->detail_schedule_id} tidak ditemukan. Melewati record legacy ID {$legacy->id}.");
                    $skippedCount++;
                    continue;
                }

                // Check if already migrated
                $existingTarget = ChangeTimeSessionTarget::where('detail_schedule_id', $legacy->detail_schedule_id)->first();
                if ($existingTarget) {
                    $skippedCount++;
                    continue;
                }

                $shiftId = $schedule->shift_id ?? ($defaultShift ? $defaultShift->id : 1);
                $officeId = $schedule->office_id ?? ($defaultOffice ? $defaultOffice->id : 1);

                $status = 'pending_approval';
                if ((int)$legacy->is_approved === 1) {
                    $status = 'approved';
                } elseif (empty($legacy->end_time)) {
                    $status = 'active';
                }

                $workMinutes = (int)($legacy->total_min ?? 0);
                $targetDebtMinutes = $workMinutes > 0 ? $workMinutes : 240; // Default 4 hours if 0

                // Create Session
                $session = ChangeTimeSession::create([
                    'intern_id' => $legacy->intern_id,
                    'session_date' => $legacy->date,
                    'shift_id' => $shiftId,
                    'office_id' => $officeId,
                    'start_time' => $legacy->start_time ?? '08:00:00',
                    'break_time' => $legacy->break_time,
                    'back_time' => $legacy->back_time,
                    'end_time' => $legacy->end_time,
                    'total_work_minutes' => $workMinutes,
                    'total_break_minutes' => (int)($legacy->total_break_min ?? 0),
                    'total_target_debt_minutes' => $targetDebtMinutes,
                    'status' => $status,
                    'approved_by' => (int)$legacy->is_approved === 1 ? 1 : null,
                    'approved_at' => (int)$legacy->is_approved === 1 ? now() : null,
                    'start_time_message' => $legacy->start_time_message,
                    'break_time_message' => $legacy->break_time_message,
                    'back_time_message' => $legacy->back_time_message,
                    'end_time_message' => $legacy->end_time_message,
                    'latitude_start' => $legacy->latitude_start,
                    'longitude_start' => $legacy->longitude_start,
                    'latitude_end' => $legacy->latitude_end,
                    'longitude_end' => $legacy->longitude_end,
                ]);

                // Find linked attendance
                $attendance = $schedule->attendance ?? Attendance::where('intern_id', $legacy->intern_id)->where('date', $schedule->date)->first();

                // Create Target
                ChangeTimeSessionTarget::create([
                    'change_time_session_id' => $session->id,
                    'detail_schedule_id' => $legacy->detail_schedule_id,
                    'attendance_id' => $attendance ? $attendance->id : null,
                    'debt_minutes' => $targetDebtMinutes,
                    'paid_minutes' => $status === 'approved' ? $targetDebtMinutes : $workMinutes,
                    'is_fulfilled' => $status === 'approved',
                ]);

                // Update attendance if approved
                if ($status === 'approved' && $attendance) {
                    $attendance->is_debt_fulfilled = true;
                    $attendance->debt_fulfilled_session_id = $session->id;
                    $attendance->debt_fulfilled_at = now();
                    $attendance->save();
                }

                $migratedCount++;
            }

            DB::commit();

            $this->info("✅ Migrasi selesai dengan sukses!");
            $this->table(
                ['Metric', 'Jumlah'],
                [
                    ['Total Data Legacy', $legacyCount],
                    ['Berhasil Dimigrasikan', $migratedCount],
                    ['Dilewati / Sudah Ada', $skippedCount],
                    ['Total Sesi Baru Saat Ini', ChangeTimeSession::count()],
                ]
            );

            return 0;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Gagal melakukan migrasi: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
