<?php

namespace App\Repositories\Implementation;

use App\Models\DetailProjects;
use App\Repositories\Interface\DetailProjectRepository;

class DetailProjectRepositoryIMPL implements DetailProjectRepository
{
    protected DetailProjects $model;

    public function __construct(DetailProjects $detailProjects)
    {
        $this->model = $detailProjects;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $project = $this->model->find($id);
        if ($project) {
            $project->update($data);
            return $project;
        }
        return null;
    }

    public function getAll()
    {
        return $this->model->get();
    }

    public function delete(int $id)
    {
        $project = $this->model->find($id);
        if ($project) {
            return $project->delete();
        }
        return false;
    }
}
