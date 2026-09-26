<?php

namespace App\Repositories\Implementation;

use App\Models\Coordinate;
use App\Models\Office;
use App\Repositories\Interface\OfficeRepository;

class OfficeRepositoryImpl implements OfficeRepository
{
    protected Office $model;

    public function __construct(Office $office)
    {
        $this->model = $office;
    }

    public function getAll()
    {
        return $this->model->with('coordinates')->get();
    }

    public function create(array $data)
    {
        return Office::create($data);
    }

    public function update(int $id, array $data)
    {
        $office = $this->model->find($id);
        if ($office) {
            $office->update($data);
            return $office;
        }
        return null;
    }

    public function delete(int $id)
    {
        Coordinate::where('office_id', $id)->delete();

        return Office::destroy($id);
    }
}
