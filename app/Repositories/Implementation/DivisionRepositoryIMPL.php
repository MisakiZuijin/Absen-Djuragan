<?php

namespace App\Repositories\Implementation;

use App\Models\Division;
use App\Models\Intern;
use App\Repositories\Interface\DivisionRepository;

class DivisionRepositoryIMPL implements DivisionRepository
{
    protected Division $model;

    public function __construct(Division $division)
    {
        $this->model = $division;
    }

    public function getById(int $id)
    {
        return $this->model->getById($id);
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getAllDivision()
    {
        return $this->model->all();
    }

    public function create(array $data)
    {
        return Division::create($data);
    }

    public function update(array $data)
    {
        $division = $this->model->find($data['id']);
        unset($data['id']);
        if ($division) {
            $division->update($data);
            return $division;
        }
        return null;
    }

    public function delete(int $id)
    {
        return Division::destroy($id);
    }

    public function updateProject(array $data) {}
}
