<?php

namespace App\Repositories\Interface;


interface DiscountTimeRepository {
    public function store(array $data);
    public function update(int $id, array $data);
    public function findById(int $id);
    public function findByScheduleId(int $scheduleId);
    public function delete(int $id);
}
