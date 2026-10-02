<?php

namespace App\Repositories\Implementation;

use App\Helper\LogConsole;
use App\Models\Shift;
use App\Repositories\Interface\ShiftRepository;

class ShiftRepositoryIMPL implements ShiftRepository
{

    protected Shift $model;

    public function __construct(Shift $shift)
    {
        $this->model = $shift;
    }

    public function getWhere(string $column, mixed $value)
    {
        return Shift::where($column, $value)->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(array $data, int $id)
    {
        $shift = $this->model->find($id);

        if (isset($data['id'])) {
            unset($data['id']);
        }

        if ($shift) {
            $shift->update($data);
            return $shift;
        }

        return null;
    }

    public function getById(int $id)
    {
        return $this->model->find($id);
    }

    public function find(int $id)
    {
        return $this->model->find($id);
    }

    public function getAll()
    {
        return $this->model->where('id', '!=', 1)->get();
    }

    public function delete(int $id)
    {
        $shift = $this->model->find($id);
        if ($shift) {
            $shift->delete();
            return true;
        }
        return false;
    }

    public function getByTimeRange(string $time)
    {
        $shift = $this->model
            ->where(function ($query) use ($time) {
                $query->where('start_time', '>=', date('H:i', strtotime($time . ' -1 hour')))
                    ->where('start_time', '<=', $time);
            })
            ->orWhere(function ($query) use ($time) {
                $query->where('start_time', '<=', $time)
                    ->where('end_time', '>=', $time);
            })
            ->orWhere(function ($query) use ($time) {
                $query->where('start_time', '>=', $time)
                    ->where('start_time', '<=', date('H:i', strtotime($time . ' +1 hour')));
            })
            ->first();
        return $shift;
    }
}
