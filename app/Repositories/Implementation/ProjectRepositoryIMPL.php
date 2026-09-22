<?php

namespace App\Repositories\Implementation;

use App\Helper\LogConsole;
use App\Models\Projects;
use App\Models\NameProjects;
use App\Repositories\Interface\ProjectRepository;

class ProjectRepositoryIMPL implements ProjectRepository
{
    protected Projects $model;

    public function __construct(Projects $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        return Projects::create($data);
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

    public function createProject(array $data)
    {
        return NameProjects::create($data);
    }

    public function delete(int $id)
    {
        return Projects::destroy($id);
    }

    public function getAll()
    {
        $result = $this->model->get();
        return $result;
    }
}
