<?php

namespace App\Utils;

use App\Helper\LogConsole;
use Carbon\Carbon;

class DateNow {
    private static $daysIndonesian = [
        'Minggu',
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu'
    ];


    public static function getCurrentDate($format = "d-m-Y") {
        return date($format);
    }

    public static function getCurrentDateYMD($format = "Y-m-d") {
        return date($format);
    }

    public static function getCurrentDay($inIndonesian = true) {
        $day = date('w');
        return $inIndonesian ? self::$daysIndonesian[$day] : $day;
    }


    public static function getDayIndex() {
        return date('w');
    }


    public static function getCurrentMonth($format = "F") {
        return date($format);
    }


    public static function getCurrentYear($format = "Y") {
        return date($format);
    }

    public static function getCurrentTime($format = "H:i:s") {
        return date($format);
    }


    public static function getCurrentTimestamp() {
        return time();
    }

    public static function getDifferentInMinute($startTime, $endTime) {
        $startTime = strtotime($startTime);
        $endTime = strtotime($endTime);


        $differenceInSeconds = $endTime - $startTime;
        $totalMinutes = floor($differenceInSeconds / 60);

        return $totalMinutes;
    }

    public static function setToHour($minutes) {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        $formattedTime = "{$hours}j {$remainingMinutes}m";
        return $formattedTime;
    }

    public static function checkIsTimeOrNot($backTimeNow, $shouldBack): bool {
        $backTime = strtotime($backTimeNow);
        $shouldBack = strtotime($shouldBack);

        return $backTime >= $shouldBack;
    }

    public static function getLastHour($time, $timeDistance = 60) {
        $lastTime = strtotime($time) - (60 * 60);
        $result =  date("H:i:s", $lastTime);

        return $result;
    }


    public static function format($dateInput, $format = 'd-m-Y') {
        $date = new \DateTime($dateInput);
        return $date->format($format);
    }

    public static function formatTime(string $timeString): string
    {
        return Carbon::parse($timeString)->format('H:i');
    }

    /**
     * Konversi string jam (format "Xj Ym") ke detik
     * Contoh input: "2j 30m" -> 9000 detik
     */
    public static function hourStringToSeconds($hourString) {
        // Contoh input: "2j 30m"
        preg_match('/(\d+)j\s*(\d+)m/', $hourString, $matches);
        $hours = isset($matches[1]) ? (int)$matches[1] : 0;
        $minutes = isset($matches[2]) ? (int)$matches[2] : 0;
        return ($hours * 3600) + ($minutes * 60);
    }
}
