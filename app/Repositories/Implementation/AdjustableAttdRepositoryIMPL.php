<?php

namespace App\Repositories\Implementation;

use App\Helper\LogConsole;
use App\Models\AdjustableAttd;
use App\Repositories\Interface\AdjustableAttdRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdjustableAttdRepositoryIMPL implements AdjustableAttdRepository
{
    protected AdjustableAttd $model;

    function __construct(AdjustableAttd $adjustableAttd)
    {
        $this->model = $adjustableAttd;
    }

    function store(array $data): AdjustableAttd
    {
        return $this->model->create($data);
    }

    function getById(int $id): AdjustableAttd
    {
        return $this->model->findOrFail($id);
    }

    function getAll()
    {
        return $this->model->all();
    }

    function update(int $id, array $data)
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException("Invalid ID provided for update: " . $id);
        }

        $record = $this->model->findOrFail($id);
        $record->update($data);
        return $record->fresh();
    }

    function delete(int $id): ?bool
    {
        $record = $this->model->findOrFail($id);
        return $record->delete();
    }

    function getByScheduleIdAndDate(int $scheduleId, string $date)
    {
        return $this->model->where("detail_schedule_id", $scheduleId)->where("date", $date)->get();
    }

    function countByDetailScheduleId(int $id)
    {
        return $this->model->where("detail_schedule_id", $id)->count();
    }
}
