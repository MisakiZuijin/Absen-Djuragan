<?php

namespace App\Repositories\Implementation;

use App\Helper\LogConsole;
use App\Models\Shift;
use App\Repositories\Interface\ShiftRepository;

class ShiftRepositoryIMPL implements ShiftRepository {

     public function getWhere(string $column, $value)
    {
        return Shift::where($column, $value)->get();
    }
    protected $model;

    public function __construct(Shift $shift) {
        $this->model = $shift;
    }

    public function create($data) {
        return $this->model->create($data);
    }

    public function update(array $data, $id) {
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

    public function getById($id) {
        return $this->model->find($id);
    }

    public function getAll() {
        return $this->model->where('id', '!=', 1)->get();
    }

    public function delete($id) {
        $shift = $this->model->find($id);
        if ($shift) {
            $shift->delete();
            return true;
        }
        return false;
    }

    public function getByTimeRange($time) {
        $shift = $this->model
            ->where(function ($query) use ($time) {
                // Kondisi untuk waktu sekarang sebelum start_time tetapi dalam rentang 1 jam sebelumnya
                $query->where('start_time', '>=', date('H:i', strtotime($time . ' -1 hour')))
                    ->where('start_time', '<=', $time);
            })
            ->orWhere(function ($query) use ($time) {
                // Kondisi untuk waktu sekarang berada di antara start_time dan end_time
                $query->where('start_time', '<=', $time)
                    ->where('end_time', '>=', $time);
            })
            ->orWhere(function ($query) use ($time) {
                // Kondisi untuk waktu sekarang sebelum start_time tetapi dalam hari yang sama
                $query->where('start_time', '>=', $time)
                    ->where('start_time', '<=', date('H:i', strtotime($time . ' +1 hour')));
            })
            ->first();
        return $shift;
    }
}
