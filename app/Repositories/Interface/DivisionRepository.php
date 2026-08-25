<?php

namespace App\Repositories\Interface;

interface DivisionRepository {
    public function getById($id);
    public function getAll();
    public function getAllDivision();
    public function create($data);
    public function update($id);
    public function delete($id);
    public function updateProject($data);
}
