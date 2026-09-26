<?php

namespace App\Repositories\Interface;

interface AttendanceRepository
{
    public function create(array $data);
    public function update(int $id, array $data);
    public function getById(int $id);
    public function getByDate(string $date, int $perPage, int $currentPage);
    public function getByName(string $name, int $perPage, int $currentPage);
    public function getByNameAndDate(string $name, string $date, int $perPage, int $currentPage);
    public function getByInternIdAndDate(int $userId, string $date);
    public function getAll();
    public function getByWeek(int $userId, string $date);
    public function getByMonth(int $userId, int $month);
    public function getDateBetween(int $internId, string $start, string $end);
    public function countAbsenceByDay(string $date);
    public function updateStatus(int $id, int $status_id);
    public function updateStatusByShiftandDate(int $status, int $shift_id, string $dateNow, ?string $timeNow = null);
    public function updateEndTimeAll(string $dateNow, int $shift_id, string $end_time);
    public function updateTime(int $id, array $data);
    public function getByIdAndAutomaticalyStatus(int $id, int $perPage = 10, int $currentPage = 1);
    public function getAllAutoEnd(int $perPage = 10, int $currentPage = 1);
    public function getAutoEndStatusByName(string $name, int $perPage = 10, int $currentPage = 1);
    public function getAutoEndStatusByDate(string $date, int $perPage = 10, int $currentPage = 1);
    public function getAutoEndStatusByDateAndName(string $name, string $date, int $perPage = 10, int $currentPage = 1);
    public function getTotalCount(?string $name = null, ?string $date_start = null, ?string $date_end = null);

    public function getAttendanceStillNotBack(string $date, int $shift_id);
}
