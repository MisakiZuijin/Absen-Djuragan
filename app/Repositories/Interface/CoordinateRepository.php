<?php

namespace App\Repositories\Interface;

interface CoordinateRepository
{
    public function create(mixed $data);
    public function getAll();
    public function getById(int $id);
    public function getByOfficeId(int $officeId);
    public function update(int $id, array $data);
    public function delete(int $id);
}
