<?php

namespace App\Repositories\Interface;

interface LogActivityRepository
{
    public function store(array $data);
    public function update(int $id, array $data);
    public function updateStatus(int $id, int $status_id);
    public function findById(int $id);
    public function findByInternId(int $internId);
    public function findAll();
    public function count();
}
