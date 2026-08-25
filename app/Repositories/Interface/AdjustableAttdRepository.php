<?php

namespace App\Repositories\Interface;

interface AdjustableAttdRepository {
    function store(array $data);
    function getById(int $id);
    function getByScheduleIdAndDate(int $scheduleId, $date);
    function getAll();
    function countByDetailScheduleId(int $id);
    function update(int $id, array $data);
    function delete(int $id);
}
