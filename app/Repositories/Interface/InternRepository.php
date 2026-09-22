<?php

namespace App\Repositories\Interface;

interface InternRepository
{
    public function store(array $data);
    public function update(int $id, array $data);
    public function getById(int $id);
    public function getWithoutDivision();
    public function getBySchoolId(int $schoolId);
    public function getByProfileId(int $id);
    public function getByDivisionId(int $id);
    public function getAll();
    public function getAllWithPaggination(int $pagnt, int $currentPage);
    public function getMultiByNamePagination(string $name, int $pagnt, int $currentPage);
    public function count();
    public function countByInternRole();
    public function countBySchool(int $schoolId);
    public function countByDivision(int $divisionId);
}
