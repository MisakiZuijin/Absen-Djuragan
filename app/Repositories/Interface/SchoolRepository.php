<?php

namespace App\Repositories\Interface;

interface SchoolRepository
{
    public function getAllSchool();
    public function store(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
