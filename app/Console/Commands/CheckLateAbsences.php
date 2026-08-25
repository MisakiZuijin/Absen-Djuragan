<?php
// File: app/Console/Commands/CheckLateAbsences.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\LateAbsence;
use Carbon\Carbon;

class CheckLateAbsences extends Command
{
    protected $signature = 'attendance:check-late {date?}';
    protected $description = 'Check dan debug keterlambatan untuk tanggal tertentu';

    public function handle()
    {
        $date = $this->argument('date') ?? now()->format('Y-m-d');
        $this->info("🔍 Checking late absences for date: {$date}");
        $this->line("");

        // Ambil semua attendance yang ada start_time untuk tanggal tersebut
        $attendances = Attendance::whereDate('date', $date)
            ->whereNotNull('start_time')
            ->with(['detailSchedules' => function($query) {
                $query->with(['shift', 'schedule.intern.user.profile']);
            }])
            ->get();

        $this->info("📋 Found {$attendances->count()} attendances with start_time");
        $this->line("");

        $lateCount = 0;
        $onTimeCount = 0;

        foreach ($attendances as $attendance) {
            $detailSchedule = $attendance->detailSchedules->first();
            
            if (!$detailSchedule || !$detailSchedule->shift) {
                $this->warn("⚠️  Skipping attendance ID {$attendance->id} - no shift found");
                continue;
            }

            $shift = $detailSchedule->shift;
            $intern = $detailSchedule->schedule->intern ?? null;
            
            if (!$intern) {
                $this->warn("⚠️  Skipping attendance ID {$attendance->id} - no intern found");
                continue;
            }

            // Parse waktu seperti di controller yang sudah diperbaiki
            $timezone = config('app.timezone', 'Asia/Jakarta');
            $absenTime = Carbon::parse($attendance->start_time)->setTimezone($timezone);
            $attendanceDate = Carbon::parse($attendance->date)->setTimezone($timezone);
            $shiftStartTime = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $shift->start_time, $timezone);

            $toleranceMinutes = 5;
            
            // Hitung keterlambatan
            $isLate = false;
            $differenceMinutes = 0;
            
            if ($absenTime->greaterThan($shiftStartTime)) {
                $differenceMinutes = $absenTime->diffInMinutes($shiftStartTime);
                $isLate = $differenceMinutes > $toleranceMinutes;
            }

            // Display info
            $internName = $intern->user->profile->full_name ?? 'Unknown';
            $status = $isLate ? "🔴 LATE" : "🟢 ON TIME";
            
            $this->line("👤 {$internName}");
            $this->line("   Shift: {$shift->name} (Start: {$shift->start_time})");
            $this->line("   Absen: {$absenTime->format('H:i:s')}");
            $this->line("   Diff: {$differenceMinutes} minutes");
            $this->line("   Status: {$status}");

            if ($isLate) {
                $lateMinutes = $differenceMinutes - $toleranceMinutes;
                $lateCount++;
                
                // Check if record exists
                $existing = LateAbsence::where('attendance_id', $attendance->id)->first();
                
                if ($existing) {
                    $this->line("   💾 Late record exists (ID: {$existing->id}, Status: {$existing->status})");
                } else {
                    $this->error("   ❌ Missing late_absence record! Should be {$lateMinutes} minutes late");
                    
                    // Optionally create the record
                    if ($this->confirm("Create missing late_absence record?")) {
                        LateAbsence::create([
                            'intern_id' => $intern->id,
                            'shift_id' => $shift->id,
                            'attendance_id' => $attendance->id,
                            'absen_time' => $absenTime,
                            'late_minutes' => $lateMinutes,
                            'status' => 'telat'
                        ]);
                        $this->info("   ✅ Late absence record created!");
                    }
                }
            } else {
                $onTimeCount++;
            }
            
            $this->line("");
        }

        // Summary
        $this->info("📊 SUMMARY for {$date}:");
        $this->line("   🟢 On Time: {$onTimeCount}");
        $this->line("   🔴 Late: {$lateCount}");
        
        $existingRecords = LateAbsence::whereDate('absen_time', $date)->count();
        $this->line("   💾 Existing late_absence records: {$existingRecords}");
        
        if ($lateCount != $existingRecords) {
            $this->error("   ❌ Mismatch! Expected {$lateCount} late records, but found {$existingRecords}");
        } else {
            $this->info("   ✅ All late records properly saved!");
        }
    }
}

// Jangan lupa register command ini di app/Console/Kernel.php:
// protected $commands = [
//     \App\Console\Commands\CheckLateAbsences::class,
// ];