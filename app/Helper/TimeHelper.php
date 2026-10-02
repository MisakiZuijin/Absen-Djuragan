<?php

namespace App\Helper;

class TimeHelper
{
    /**
     * Calculate time difference in minutes
     */
    public static function diffInMinutes(mixed $start, mixed $end): int
    {
        if (!$start || !$end) return 0;

        $startTime = strtotime($start);
        $endTime = strtotime($end);

        if (!$startTime || !$endTime) return 0;

        return max(0, round(($endTime - $startTime) / 60));
    }

    /**
     * Format minutes to hours (H:i format)
     */
    public static function formatMinutesToHours(int|float|string|null $minutes): string
    {
        $minutes = max(0, (float) $minutes);
        $hours = floor($minutes / 60);
        $mins = (int) $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }

    /**
     * Format difference with sign (+/- H:i)
     */
    public static function formatDifference(int|float|string|null $minutes): string
    {
        $min = (float) $minutes;
        if (abs($min) < 0.001) {
            return '00:00';
        }

        $sign = $min > 0 ? '+' : '-';
        $absMinutes = abs($min);
        $hours = (int) floor($absMinutes / 60);
        $mins = (int) round($absMinutes) % 60;

        if ($hours === 0 && $mins === 0) {
            return '00:00';
        }

        return sprintf('%s%02d:%02d', $sign, $hours, $mins);
    }

    /**
     * Check if a detail schedule is an excused/approved permit (e.g. Izin Sakit di-ACC / Bebas Ganti Jam)
     *
     * @param mixed $detailSchedule
     * @return bool
     */
    public static function isApprovedExcusedLeave($detailSchedule): bool
    {
        if (!$detailSchedule) {
            return false;
        }

        // Status must be Izin (attd_status_id == 3)
        $attdStatusId = is_array($detailSchedule)
            ? ($detailSchedule['attd_status_id'] ?? null)
            : ($detailSchedule->attd_status_id ?? null);

        if ((int)$attdStatusId !== 3) {
            return false;
        }

        $isChangeSchedule = is_array($detailSchedule)
            ? ($detailSchedule['isChangeSchedule'] ?? null)
            : ($detailSchedule->isChangeSchedule ?? null);

        $isApproved = is_array($detailSchedule)
            ? ($detailSchedule['is_change_schedule_approved'] ?? null)
            : ($detailSchedule->is_change_schedule_approved ?? null);

        // Jika secara eksplisit ditetapkan Wajib Ganti Jam (2), bukan excused/lunas
        if ((int)$isChangeSchedule === 2) {
            return false;
        }

        // 1. Secara eksplisit di-ACC admin sebagai Bebas Ganti Jam / Lunas (isChangeSchedule == 1 atau is_change_schedule_approved == 1)
        if ((int)$isChangeSchedule === 1 || (int)$isApproved === 1) {
            return true;
        }

        // 2. Izin sakit dengan bukti surat dokter resmi yang valid (dan tidak diset wajib ganti jam)
        $permitReason = is_array($detailSchedule)
            ? ($detailSchedule['permit_reason'] ?? $detailSchedule['permitReason'] ?? null)
            : ($detailSchedule->permitReason ?? null);

        if ($permitReason) {
            $categoryId = is_array($permitReason)
                ? ($permitReason['permit_category_id'] ?? null)
                : ($permitReason->permit_category_id ?? null);
            $proofUrl = is_array($permitReason)
                ? ($permitReason['proof_url'] ?? null)
                : ($permitReason->proof_url ?? null);
            $desc = is_array($permitReason)
                ? ($permitReason['description'] ?? '')
                : ($permitReason->description ?? '');

            $isSakit = in_array((int)$categoryId, [1, 2]) || str_contains(strtolower($desc), 'sakit');
            if ($isSakit && !empty($proofUrl)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Total menit hutang waktu dari izin (keluar, sholat, toilet) yang disetujui
     * dengan status wajib ganti jam / kelebihan waktu, untuk satu attendance.
     *
     * @param mixed $attendance Model Attendance (atau object dengan properti id)
     * @return int
     */
    public static function mandatoryReplaceDebtMinutes($attendance): int
    {
        $attendanceId = is_object($attendance) ? ($attendance->id ?? null) : null;
        if (!$attendanceId) {
            return 0;
        }

        if ($attendance instanceof \App\Models\Attendance && $attendance->relationLoaded('permitLogs')) {
            return (int) $attendance->permitLogs
                ->filter(fn($log) => in_array($log->type, ['leave', 'prayer', 'toilet']) && ((int)$log->is_mandatory_replace === 1 || $log->is_mandatory_replace === true) && $log->approval_status === 'approved')
                ->sum(fn($log) => (int) ($log->agreed_duration_minutes ?: ($log->duration_in_minutes ?: 0)));
        }

        return (int) \App\Models\PermitLog::where('attendance_id', $attendanceId)
            ->whereIn('type', ['leave', 'prayer', 'toilet'])
            ->where('is_mandatory_replace', true)
            ->where('approval_status', 'approved')
            ->selectRaw('COALESCE(agreed_duration_minutes, duration_in_minutes, 0) as mins')
            ->get()
            ->sum('mins');
    }

    /**
     * Calculate daily work hours with proper break time handling
     */
    public static function calculateDailyWorkHours(mixed $attendance, mixed $shift, mixed $detailSchedule = null, int $additionalDebtMinutes = 0): array
    {
        // Resolve detailSchedule if not passed directly
        if (!$detailSchedule && $attendance instanceof \App\Models\Attendance) {
            $detailSchedule = $attendance->detailSchedules;
        }

        $shiftTargetMinutes = $shift->total_time_in_minute ?? 0;
        if ($shiftTargetMinutes <= 0 && $shift && $shift->start_time && $shift->end_time && $shift->start_time !== '00:00:00') {
            $shiftTargetMinutes = max(0, self::diffInMinutes($shift->start_time, $shift->end_time) - ($shift->break_time_in_minute ?? 0));
        }

        // Jika izin disetujui / bebas ganti jam (Lunas / Izin Sakit di-ACC), waktu kerja otomatis memenuhi jam shift dan hutang jam 00:00
        if ($detailSchedule && self::isApprovedExcusedLeave($detailSchedule)) {
            return [
                'actual_work_minutes' => $shiftTargetMinutes,
                'actual_work_formatted' => self::formatMinutesToHours($shiftTargetMinutes),
                'break_minutes' => 0,
                'break_formatted' => '00:00',
                'shift_target_minutes' => $shiftTargetMinutes,
                'shift_target_formatted' => self::formatMinutesToHours($shiftTargetMinutes),
                'diff_minutes' => 0,
                'diff_formatted' => '00:00',
                'is_sufficient' => true,
                'mandatory_replace_minutes' => 0,
            ];
        }

        // 2. Jika Alpha / Tidak Hadir (attd_status_id == 5) atau Izin Wajib Ganti Jam (attd_status_id == 3 && isChangeSchedule == 2)
        // Jam kerja aktual 0 dan berhutang seluruh jam shift
        $isDeficitFullShift = false;
        if ($detailSchedule) {
            $attdStatusId = is_array($detailSchedule) ? ($detailSchedule['attd_status_id'] ?? null) : ($detailSchedule->attd_status_id ?? null);
            $isChange = is_array($detailSchedule) ? ($detailSchedule['isChangeSchedule'] ?? null) : ($detailSchedule->isChangeSchedule ?? null);
            if ((int)$attdStatusId === 5 || ((int)$attdStatusId === 3 && (int)$isChange === 2)) {
                $isDeficitFullShift = true;
            }
        }

        if ($isDeficitFullShift) {
            return [
                'actual_work_minutes' => 0,
                'actual_work_formatted' => '00:00',
                'break_minutes' => 0,
                'break_formatted' => '00:00',
                'shift_target_minutes' => $shiftTargetMinutes,
                'shift_target_formatted' => self::formatMinutesToHours($shiftTargetMinutes),
                'diff_minutes' => -$shiftTargetMinutes,
                'diff_formatted' => self::formatDifference(-$shiftTargetMinutes),
                'is_sufficient' => false,
                'mandatory_replace_minutes' => $shiftTargetMinutes,
            ];
        }

        // Hutang tambahan dari izin keluar yang wajib ganti jam
        $additionalDebtMinutes = max(0, (int) $additionalDebtMinutes);
        $shiftTargetMinutes += $additionalDebtMinutes;

        // Initialize variables
        $startTime = $attendance->start_time ?? null;
        $endTime = $attendance->end_time ?? null;
        $breakTime = $attendance->break_time ?? null;
        $backTime = $attendance->back_time ?? null;

        $actualWorkMinutes = 0;
        $breakMinutes = 0;

        // Calculate actual work time (excluding breaks)
        if ($startTime && $endTime) {
            // Handle different shift types
            $shiftType = $shift->type ?? 'custom';

            switch ($shiftType) {
                case 'pagi':
                    // Shift pagi: 6.30 - 13.00 tanpa istirahat (total 6.5 jam)
                    $actualWorkMinutes = self::diffInMinutes($startTime, $endTime);
                    $breakMinutes = 0;
                    break;

                case 'middle':
                case 'siang':
                default:
                    $totalMinutes = self::diffInMinutes($startTime, $endTime);

                    // Calculate break time
                    if ($breakTime && $backTime) {
                        $breakMinutes = self::diffInMinutes($breakTime, $backTime);
                    } else {
                        $date = is_object($attendance) ? ($attendance->date ?? null) : (is_array($attendance) ? ($attendance['date'] ?? null) : null);
                        if (!$date && $detailSchedule) {
                            $date = is_object($detailSchedule) ? ($detailSchedule->date ?? null) : (is_array($detailSchedule) ? ($detailSchedule['date'] ?? null) : null);
                        }
                        $user = (is_object($attendance) ? ($attendance->intern?->user ?? null) : null)
                            ?? (is_object($detailSchedule) ? ($detailSchedule->schedule?->intern?->user ?? null) : null)
                            ?? (auth()->check() ? auth()->user() : null);

                        if ($shift instanceof \App\Models\Shift) {
                            $breakMinutes = $shift->getEffectiveBreakTimeInMinute($date, $user);
                        } else {
                            $breakMinutes = (int) ($shift->break_time_in_minute ?? ($shiftType === 'siang' ? 60 : 45));
                        }
                    }

                    $actualWorkMinutes = max(0, $totalMinutes - $breakMinutes);
                    break;
            }
        }

        // Calculate difference
        $diffMinutes = $actualWorkMinutes - $shiftTargetMinutes;

        return [
            'actual_work_minutes' => $actualWorkMinutes,
            'actual_work_formatted' => self::formatMinutesToHours($actualWorkMinutes),
            'break_minutes' => $breakMinutes,
            'break_formatted' => self::formatMinutesToHours($breakMinutes),
            'shift_target_minutes' => $shiftTargetMinutes,
            'shift_target_formatted' => self::formatMinutesToHours($shiftTargetMinutes),
            'diff_minutes' => $diffMinutes,
            'diff_formatted' => self::formatDifference($diffMinutes),
            'is_sufficient' => $diffMinutes >= 0,
            'mandatory_replace_minutes' => $additionalDebtMinutes,
        ];
    }

    /**
     * Sinkronisasi jam masuk & jam pulang presensi sesuai jadwal shift ketika izin sakit/keperluan valid.
     *
     * @param mixed $detailSchedule
     * @param mixed $shift
     * @return \App\Models\Attendance|null
     */
    public static function syncValidPermitAttendance(mixed $detailSchedule, mixed $shift = null): ?\App\Models\Attendance
    {
        if (!$detailSchedule) {
            return null;
        }

        $shift = $shift ?? $detailSchedule->shift;
        if (!$shift && $detailSchedule->shift_id) {
            $shift = \App\Models\Shift::find($detailSchedule->shift_id);
        }
        if (!$shift) {
            $shift = \App\Models\Shift::where('id', '!=', 1)->first() ?? \App\Models\Shift::first();
        }

        if (!$shift) {
            return null;
        }

        $startTime = $shift->start_time;
        $endTime = $shift->end_time;
        $breakTime = $shift->start_break_time;
        $backTime = $shift->end_break_time;
        $totalMin = (int) ($shift->total_time_in_minute ?? 435);
        $totalBreakMin = (int) ($shift->break_time_in_minute ?? 60);

        // Update jam pada DetailSchedule
        $detailSchedule->start_time = $startTime;
        $detailSchedule->end_time = $endTime;
        $detailSchedule->attd_status_id = 3; // Izin
        $detailSchedule->save();

        // Cari intern_id
        $internId = $detailSchedule->schedule?->intern_id;
        if (!$internId && $detailSchedule->attendance_id) {
            $internId = \App\Models\Attendance::where('id', $detailSchedule->attendance_id)->value('intern_id');
        }
        if (!$internId) {
            $internId = \App\Models\DetailSchedule::where('id', $detailSchedule->id)
                ->with('schedule')
                ->first()?->schedule?->intern_id;
        }

        $attendance = $detailSchedule->attendance;
        if (!$attendance && $internId) {
            $attendance = \App\Models\Attendance::where('intern_id', $internId)
                ->whereDate('date', $detailSchedule->date)
                ->first();
        }

        $attPayload = [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'break_time' => $breakTime,
            'back_time' => $backTime,
            'total_min' => ((int)$detailSchedule->isChangeSchedule === 2) ? 0 : $totalMin,
            'total_break_min' => $totalBreakMin,
            'keterangan' => 'Izin disetujui (sesuai jadwal)',
            'start_time_message' => 'Izin disetujui',
            'end_time_message' => 'Izin disetujui',
        ];

        if (!$attendance && $internId) {
            $attendance = \App\Models\Attendance::create(array_merge([
                'intern_id' => $internId,
                'date' => $detailSchedule->date,
            ], $attPayload));

            $detailSchedule->attendance_id = $attendance->id;
            $detailSchedule->save();
        } elseif ($attendance) {
            $attPayload['keterangan'] = $attendance->keterangan ?: 'Izin disetujui (sesuai jadwal)';
            $attendance->update($attPayload);

            if (!$detailSchedule->attendance_id) {
                $detailSchedule->attendance_id = $attendance->id;
                $detailSchedule->save();
            }
        }

        return $attendance;
    }

    /**
     * Menghitung sisa hutang menit pada suatu DetailSchedule tertentu,
     * dikurangi menit ganti jam yang sudah pernah terselesaikan untuk jadwal tersebut.
     *
     * @param mixed $detailSchedule Model DetailSchedule atau ID
     * @param int|null $excludeAdjustableId ID AdjustableAttd yang sedang aktif (dikecualikan)
     * @return int
     */
    public static function getScheduleTargetDebtMinutes(mixed $detailSchedule, ?int $excludeAdjustableId = null): int
    {
        if (!$detailSchedule) {
            return 0;
        }

        if (is_numeric($detailSchedule)) {
            $detailSchedule = \App\Models\DetailSchedule::with(['shift', 'attendance.permitLogs', 'permitReason.category', 'schedule'])->find($detailSchedule);
        }

        if (!$detailSchedule || !$detailSchedule->shift) {
            return 0;
        }

        $shift = $detailSchedule->shift;
        $shiftMinutes = (int) ($shift->total_time_in_minute ?? 0);
        if ($shiftMinutes <= 0 && $shift->start_time && $shift->end_time && $shift->start_time !== '00:00:00') {
            $start = \Carbon\Carbon::parse($shift->start_time);
            $end = \Carbon\Carbon::parse($shift->end_time);
            $break = (int) ($shift->break_time_in_minute ?? 0);
            $shiftMinutes = max(0, $end->diffInMinutes($start) - $break);
        }

        // Jika sudah lunas atau izin bebas ganti jam
        if (self::isApprovedExcusedLeave($detailSchedule) || (int)($detailSchedule->isChangeSchedule ?? 0) === 1 || (int)($detailSchedule->is_change_schedule_approved ?? 0) === 1) {
            return 0;
        }

        $baseDeficit = 0;
        $statusId = (int) ($detailSchedule->attd_status_id ?? 1);

        if ($statusId === 5) {
            // Alpha (Tidak Hadir)
            $baseDeficit = $shiftMinutes;
        } elseif ($statusId === 3) {
            // Izin (Wajib Ganti Jam)
            $baseDeficit = $shiftMinutes;
        } else {
            // Reguler (Hadir / Belum Absen / Kurang Jam)
            $attendance = $detailSchedule->attendance;
            if (!$attendance && $detailSchedule->schedule) {
                $attendance = \App\Models\Attendance::where('intern_id', $detailSchedule->schedule->intern_id)
                    ->whereDate('date', $detailSchedule->date)
                    ->with('permitLogs')
                    ->first();
            }

            $workingMinutes = 0;
            if ($attendance && $attendance->start_time && $attendance->end_time) {
                $startTime = \Carbon\Carbon::parse($attendance->start_time);
                $endTime = \Carbon\Carbon::parse($attendance->end_time);
                $totalDuration = $endTime->diffInMinutes($startTime);
                $breakDuration = (int) ($attendance->total_break_min ?? 0);
                $workingMinutes = max(0, $totalDuration - $breakDuration);
            }

            $differenceInMinutes = $shiftMinutes - $workingMinutes;

            // Jika hari ini dan belum pulang, cek keterlambatan masuk
            if (\Carbon\Carbon::parse($detailSchedule->date)->isToday() && $attendance && $attendance->start_time && !$attendance->end_time) {
                $scheduledStart = \Carbon\Carbon::parse($detailSchedule->date . ' ' . $shift->start_time);
                $actualStart = \Carbon\Carbon::parse($attendance->start_time);
                $differenceInMinutes = $actualStart->isAfter($scheduledStart) ? $actualStart->diffInMinutes($scheduledStart) : 0;
            }

            $extraLeaveDebt = self::mandatoryReplaceDebtMinutes($attendance);
            $differenceInMinutes += $extraLeaveDebt;
            $baseDeficit = max(0, $differenceInMinutes);
        }

        // Minus sesi ganti jam yang sudah selesai untuk detail schedule ini (Legacy AdjustableAttd)
        $prevAdjustables = \App\Models\AdjustableAttd::where('detail_schedule_id', $detailSchedule->id)
            ->when($excludeAdjustableId, fn($q) => $q->where('id', '!=', $excludeAdjustableId))
            ->where(function ($q) {
                $q->whereNull('is_approved')->orWhere('is_approved', '!=', 2);
            })
            ->whereNotNull('end_time')
            ->get();

        $prevCovered = 0;
        foreach ($prevAdjustables as $pa) {
            $mins = (int) ($pa->total_min ?? 0);
            if ($mins <= 0 && !empty($pa->start_time) && !empty($pa->end_time)) {
                $paBreak = (int) ($pa->total_break_min ?? 0);
                $mins = max(0, \Carbon\Carbon::parse($pa->end_time)->diffInMinutes(\Carbon\Carbon::parse($pa->start_time)) - $paBreak);
            }
            $prevCovered += $mins;
        }

        // Minus sesi ganti jam (Ganti Jam V2) yang sudah disetujui admin untuk jadwal ini
        $v2PaidMinutes = \App\Models\ChangeTimeSessionTarget::where('detail_schedule_id', $detailSchedule->id)
            ->whereHas('session', function ($q) {
                $q->where('status', 'approved');
            })
            ->sum('paid_minutes');

        $prevCovered += (int) $v2PaidMinutes;

        return max(0, $baseDeficit - $prevCovered);
    }

    /**
     * Set waktu masuk dan pulang pada detail schedule dan attendance sesuai jam shift
     * saat hutang jam kerja pada shift tersebut telah selesai diganti (lunas).
     *
     * @param mixed $detailSchedule DetailSchedule model atau ID
     * @param int|null $internId ID Pemagang
     * @return \App\Models\Attendance|null
     */
    public static function fulfillDebtScheduleAttendance(mixed $detailSchedule, ?int $internId = null): ?\App\Models\Attendance
    {
        if (!$detailSchedule) {
            return null;
        }

        if (is_numeric($detailSchedule)) {
            $detailSchedule = \App\Models\DetailSchedule::with(['shift', 'attendance', 'schedule'])->find($detailSchedule);
        }

        if (!$detailSchedule || !$detailSchedule->shift) {
            return null;
        }

        $shift = $detailSchedule->shift;
        $startTime = $shift->start_time;
        $endTime = $shift->end_time;
        $breakTime = $shift->start_break_time;
        $backTime = $shift->end_break_time;
        $totalMin = (int) ($shift->total_time_in_minute ?? 0);
        if ($totalMin <= 0 && $startTime && $endTime) {
            $totalMin = max(0, \Carbon\Carbon::parse($endTime)->diffInMinutes(\Carbon\Carbon::parse($startTime)) - (int)($shift->break_time_in_minute ?? 0));
        }
        $totalBreakMin = (int) ($shift->break_time_in_minute ?? 0);

        // Update DetailSchedule
        $detailSchedule->start_time = $startTime;
        $detailSchedule->end_time = $endTime;
        $detailSchedule->attd_status_id = 2; // Hadir (Sesuai Jam Shift)
        $detailSchedule->isChangeSchedule = 1; // Lunas / Bebas Ganti Jam
        $detailSchedule->is_change_schedule_approved = 1;

        // Cari intern_id jika belum ada
        if (!$internId) {
            $internId = $detailSchedule->schedule?->intern_id;
            if (!$internId && $detailSchedule->attendance_id) {
                $internId = \App\Models\Attendance::where('id', $detailSchedule->attendance_id)->value('intern_id');
            }
        }

        $attendance = $detailSchedule->attendance;
        if (!$attendance && $internId) {
            $attendance = \App\Models\Attendance::where('intern_id', $internId)
                ->whereDate('date', $detailSchedule->date)
                ->first();
        }

        $attPayload = [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'break_time' => $breakTime,
            'back_time' => $backTime,
            'total_min' => $totalMin,
            'total_break_min' => $totalBreakMin,
            'keterangan' => 'Hadir (Lunas Ganti Jam)',
            'start_time_message' => $attendance?->start_time_message ?: 'Ganti Jam Selesai',
            'end_time_message' => $attendance?->end_time_message ?: 'Ganti Jam Selesai',
        ];

        if (!$attendance && $internId) {
            $attendance = \App\Models\Attendance::create(array_merge([
                'intern_id' => $internId,
                'date' => $detailSchedule->date,
            ], $attPayload));

            $detailSchedule->attendance_id = $attendance->id;
        } elseif ($attendance) {
            $attendance->update($attPayload);

            if (!$detailSchedule->attendance_id) {
                $detailSchedule->attendance_id = $attendance->id;
            }
        }

        $detailSchedule->save();
        return $attendance;
    }
}
