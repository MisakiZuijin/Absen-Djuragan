<?php

namespace App\Repositories\Interface;

interface DivisionRepository
{
    public function getById(int $id);
    public function getAll();
    public function getAllDivision();
    public function create(array $data);
    public function update(array $data);
    public function delete(int $id);
    public function updateProject(array $data);
}
