<?php

namespace App\Repositories\Interface;

interface DetailScheduleRepository
{

    public function create(array $data);

    public function all();
    public function find(int $id);
    public function findByInternId(int $internId);
    public function findByScheduleIdAndDate(int $id, string $date);
    public function findByInternIdAndMonth(int $internId, int $month);
    public function findByInternIdAndWeek(int $internId, string $date);

    public function findByName(string $name, int $perPage, int $currentPage);
    public function findByDate(string $date, int $perPage, int $currentPage);
    public function findByNameAndDate(string $name, string $date, int $perPage, int $currentPage);
    public function findByCriteria(array $criteria, int $perPage, int $currentPage);

    public function update(int $id, array $data);
    public function updateAttdStatus(int $id, int $statusId);

    public function updateShift(int $id, int $shiftId);

    public function countAttendance(string $date, int $status_attd_id);

    public function delete(int $id): bool;
}
