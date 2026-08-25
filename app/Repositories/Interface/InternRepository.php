<?php

namespace App\Repositories\Interface;

interface InternRepository {
    public function store($data);
    public function update($id, $data);
    public function getById($id);
    public function getWithoutDivision();
    public function getBySchoolId($schoolId);
    public function getByProfileId($id);
    public function getByDivisionId($id);
    public function getAll();
    public function getAllWithPaggination($pagnt, $currentPage);
    public function getMultiByNamePagination($name,  $pagnt, $currentPage);
    public function count();
    public function countByInternRole();
    public function countBySchool($schoolId);
    public function countByDivision($divisionId);
}
