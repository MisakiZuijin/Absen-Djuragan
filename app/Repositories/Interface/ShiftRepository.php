<?php

namespace App\Repositories\Interface;

interface ShiftRepository
{
    public function create(array $data);
    public function update(array $data, int $id);
    public function getById(int $id);
    public function find(int $id);
    public function getAll();
    public function getByTimeRange(string $time);
    public function delete(int $id);
    public function getWhere(string $column, mixed $value);
}
