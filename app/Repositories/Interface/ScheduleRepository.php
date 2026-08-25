<?php

namespace App\Repositories\Interface;

interface ScheduleRepository {
    public function create(array $data);

    public function find(int $id);

    public function findByInternId($intern_id);

    public function findByInternIdAndDate($inter_id, $date);

    public function all();

    public function update(int $id, array $data);

    public function delete(int $id): bool;
}
