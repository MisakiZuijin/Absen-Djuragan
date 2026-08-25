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
     * Calculate daily work hours with proper break time handling
     */
    public static function calculateDailyWorkHours($attendance, $shift)
    {
        // Initialize variables
        $startTime = $attendance->start_time ?? null;
        $endTime = $attendance->end_time ?? null;
        $breakTime = $attendance->break_time ?? null;
        $backTime = $attendance->back_time ?? null;
        
        $actualWorkMinutes = 0;
        $shiftTargetMinutes = $shift->total_time_in_minute ?? 0;
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