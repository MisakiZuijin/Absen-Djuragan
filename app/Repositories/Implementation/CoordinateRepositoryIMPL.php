<?php

namespace App\Repositories\Implementation;

use App\Models\Coordinate;
use App\Repositories\Interface\CoordinateRepository;

class CoordinateRepositoryIMPL implements CoordinateRepository {
    protected $model;

    public function __construct(Coordinate $coordinate) {
        $this->model = $coordinate;
    }

    public function create($data) {
        $result = $this->model->create($data);
        return $result;
    }

    public function getAll() {
        $result = $this->model->get();
        if (!$result) {
            return null;
        }
        return $result;
    }

    public function getById($id) {
        $result = $this->model->find($id);
        return $result;
    }

    public function getByOfficeId($officeId) {
        $result = $this->model->where("office_id", $officeId)->get();
        return $result;
    }

    public function update($id, $data) {
        $coordinate = $this->model->find($id);

        if (!$coordinate) {
            return null;
        }

        $coordinate->update($data);
        return $coordinate;
    }

    public function delete($id) {
        $coordinate = $this->model->find($id);
        return $coordinate->delete();
    }
}
