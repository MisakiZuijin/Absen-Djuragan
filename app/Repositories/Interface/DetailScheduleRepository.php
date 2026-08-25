<?php

namespace App\Repositories\Interface;

interface DetailScheduleRepository {

    public function create(array $data);

    public function all();
    public function find(int $id);
    public function findByInternId(int $internId);
    public function findByScheduleIdAndDate($id, $date);
    public function findByInternIdAndMonth($internId, $date);
    public function findByInternIdAndWeek($internId, $date);


    public function findByName(String $name, int $perPage, int $currentPage);
    public function findByDate(String $data, int $perPage, int $currentPage);
    public function findByNameAndDate(String $name, String $date, int $perPage, int $currentPage);
    public function findByCriteria(array $criteria, int $perPage, int $currentPage);

    public function update(int $id, array $data);
    public function updateAttdStatus(int $id, int $statusId);

    public function updateShift(int $id, int $shiftId);

    public function countAttendance($date, $status_attd_id);

    public function delete(int $id): bool;
}
