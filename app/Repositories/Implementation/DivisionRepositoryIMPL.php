<?php

namespace App\Repositories\Implementation;

use App\Models\Division;
use App\Models\Intern;
use App\Repositories\Interface\DivisionRepository;

class DivisionRepositoryIMPL implements DivisionRepository {
    protected $model;

    public function __construct(Division $division) {
        $this->model = $division;
    }

    public function getById($id) {
        return $this->model->getById($id);
    }

    // public function getInternCountByDivisionId($divisionId)
    // {
    //     return Intern::where('division_id', $divisionId)->count();
    // }

    public function getAll() {
        return $this->model->all();
    }

    public function getAllDivision()
    {
        return $this->model->all();
    }

    public function create($data)
    {
        return Division::create($data);
    }

    public function update($data)
    {
        $division = $this->model->find($data['id']);
        unset($data['id']);
        if ($division) {
            $division->update($data);
            return $division;
        }
        return null;
    }

    public function delete($id)
    {
        return Division::destroy($id);
    }

    public function updateProject($data)
    {
        
    }
}
