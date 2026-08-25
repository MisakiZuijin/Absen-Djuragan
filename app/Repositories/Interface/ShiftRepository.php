<?php

namespace App\Repositories\Interface;

interface ShiftRepository {
    public function create($data);
    public function update(array $data, $id);
    public function getById($id);
    public function getAll();
    public function getByTimeRange($time);
    public function delete($id);
    public function getWhere(string $column, $value);
}