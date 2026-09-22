<?php

namespace App\Repositories\Interface;

interface ScheduleRepository
{
    public function create(array $data);
    public function find(int $id);
    public function findByInternId(int $intern_id);
    public function findByInternIdAndDate(int $intern_id, string $date);
    public function all();
    public function update(int $id, array $data);
    public function delete(int $id): bool;
}
