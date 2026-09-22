<?php

namespace App\Repositories\Implementation;

use App\Models\Schedule;
use App\Repositories\Interface\ScheduleRepository;

class ScheduleRepositoryIMPL implements ScheduleRepository
{
    protected Schedule $model;

    public function __construct(Schedule $schedule)
    {
        $this->model = $schedule;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function find(int $id)
    {
        return $this->model->find($id);
    }

    public function findByInternId(int $intern_id)
    {
        return $this->model->where('intern_id', $intern_id)->first();
    }

    public function findByInternIdAndDate(int $intern_id, string $date)
    {
        return $this->model->where('intern_id', $intern_id)
            ->whereDate('start_period', '<=', $date)
            ->whereDate('end_period', '>=', $date)
            ->first();
    }

    public function all()
    {
        return $this->model->all();
    }

    public function update(int $id, array $data)
    {
        $schedule = $this->model->find($id);

        if ($schedule) {
            $schedule->update($data);
            return $schedule->fresh();
        }

        return null;
    }

    public function delete(int $id): bool
    {
        $schedule = $this->model->find($id);

        if ($schedule) {
            return $schedule->delete();
        }

        return false;
    }
}
