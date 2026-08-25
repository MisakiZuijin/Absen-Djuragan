<?php

namespace App\Repositories\Interface;

interface AttendanceRepository {
    public function create($data);
    public function update($id, $data);
    public function getById($id);
    public function getByDate($date, $perPage, $currentPage);
    public function getByName($name, $perPage, $currentPage);
    public function getByNameAndDate($name, $date,  $perPage, $currentPage);
    public function getByInternIdAndDate($userId, $date);
    public function getAll();
    public function getByWeek($userId, $date);
    public function getByMonth($userId, $month);
    public function getDateBetween($internId, $start, $end);
    public function countAbsenceByDay($date);
    public function updateStatus($id, $status_id);
    public function updateStatusByShiftandDate($status, $shift_id, $dateNow, $timeNow);
    public function updateEndTimeAll($dateNow, $shift_id,  $end_time);
    public function createPermitPresence($data);
    public function updatePermitPresence($data);

    public function updateTime($id, $data);
    public function updateAdjustableTime($id, $data);
    public function calculateTotalMinutes($startTime, $endTime);
    public function calculateBreakMinutes($breakTime, $backTime);
    public function updateShift();
    public function storeNote();
    // public function getAllChangeTime($internId);
    public function getByIdAndAutomaticalyStatus(int $id, int $perPage = 10, int $currentPage = 1);
    public function getAllAutoEnd(int $perPage = 10, int $currentPage = 1);
    public function getAutoEndStatusByName($name, int $perPage = 10, int $currentPage = 1);
    public function getAutoEndStatusByDate($date, int $perPage = 10, int $currentPage = 1);
    public function getAutoEndStatusByDateAndName($name, $date, int $perPage = 10, int $currentPage = 1);
    public function getTotalCount($name = null, $date_start = null, $date_end = null);

    public function getAttendanceStillNotBack($date, $shift_id);
}
