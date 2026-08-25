<?php

namespace App\Repositories\Interface;

interface SchoolRepository {
    public function getAllSchool();
    public function store($data);
    public function update($id, $data);
    public function delete($id);
}
