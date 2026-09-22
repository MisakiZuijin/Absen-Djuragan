<?php

namespace App\Helper;

class TimeHelper
{
    /**
     * Calculate time difference in minutes
     */
    public static function diffInMinutes($start, $end)
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
    public static function formatMinutesToHours($minutes)
    {
        $minutes = max(0, $minutes);
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }
    
    /**
     * Format difference with sign (+/- H:i)
     */
    public static function formatDifference($minutes)
    {
        if ($minutes === 0) return '+00:00';
        
        $sign = $minutes > 0 ? '+' : '-';
        $minutes = abs($minutes);
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
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
     * Calculate daily work hours with proper break time handling
     */
    public static function calculateDailyWorkHours($attendance, $shift, $detailSchedule = null)
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
            ];
        }

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
                    // Shift middle: 9.00 - 17.00, istirahat 12:15 - 13:00 (45 menit)
                    $totalMinutes = self::diffInMinutes($startTime, $endTime);
                    
                    // Calculate break time
                    if ($breakTime && $backTime) {
                        $breakMinutes = self::diffInMinutes($breakTime, $backTime);
                    } else {
                        // Default break for middle shift: 45 minutes
                        $breakMinutes = 45;
                    }
                    
                    $actualWorkMinutes = max(0, $totalMinutes - $breakMinutes);
                    break;
                    
                case 'siang':
                    // Shift siang: 13.00 - 21.00, istirahat 18:00 - 19:00 (60 menit)
                    $totalMinutes = self::diffInMinutes($startTime, $endTime);
                    
                    // Calculate break time
                    if ($breakTime && $backTime) {
                        $breakMinutes = self::diffInMinutes($breakTime, $backTime);
                    } else {
                        // Default break for siang shift: 60 minutes
                        $breakMinutes = 60;
                    }
                    
                    $actualWorkMinutes = max(0, $totalMinutes - $breakMinutes);
                    break;
                    
                default:
                    // Custom shift handling
                    $totalMinutes = self::diffInMinutes($startTime, $endTime);
                    
                    // Calculate break time
                    if ($breakTime && $backTime) {
                        $breakMinutes = self::diffInMinutes($breakTime, $backTime);
                    } elseif ($shift->break_time_in_minute > 0) {
                        $breakMinutes = $shift->break_time_in_minute;
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
        ];
    }
}