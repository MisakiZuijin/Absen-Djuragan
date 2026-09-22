<?php

namespace App\Repositories\Implementation;

use App\Models\Coordinate;
use App\Repositories\Interface\CoordinateRepository;

class CoordinateRepositoryIMPL implements CoordinateRepository
{
    protected Coordinate $model;

    public function __construct(Coordinate $coordinate)
    {
        $this->model = $coordinate;
    }

    public function create(mixed $data)
    {
        $result = $this->model->create($data);
        return $result;
    }

    public function getAll()
    {
        $result = $this->model->get();
        if (!$result) {
            return null;
        }
        return $result;
    }

    public function getById(int $id)
    {
        $result = $this->model->find($id);
        return $result;
    }

    public function getByOfficeId(int $officeId)
    {
        $result = $this->model->where("office_id", $officeId)->get();
        return $result;
    }

    public function update(int $id, array $data)
    {
        $coordinate = $this->model->find($id);

        if (!$coordinate) {
            return null;
        }

        $coordinate->update($data);
        return $coordinate;
    }

    public function delete(int $id)
    {
        $coordinate = $this->model->find($id);
        return $coordinate->delete();
    }
}
