<?php

namespace App\Repositories\Implementation;

use App\Models\School;
use App\Repositories\Interface\SchoolRepository;

class SchoolRepositoryIMPL implements SchoolRepository
{
    protected School $model;

    public function __construct(School $model)
    {
        $this->model = $model;
    }

    public function getAllSchool()
    {
        return $this->model->all();
    }

    public function store(array $data)
    {
        return School::create($data);
    }

    public function update(int $id, array $data)
    {
        $school = $this->model->find($id);
        if ($school) {
            $school->update($data);
            return $school;
        }
        return null;
    }

    public function delete(int $id)
    {
        return School::destroy($id);
    }
}
