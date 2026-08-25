<?php

namespace App\Repositories\Implementation;

use App\Helper\LogConsole;
use App\Models\Projects;
use App\Models\NameProjects;
use App\Repositories\Interface\ProjectRepository;

class ProjectRepositoryIMPL implements ProjectRepository {
    protected $model;

    public function __construct(Projects $model) {
        $this->model = $model;
    }

    public function create($data) {
        return Projects::create($data);
    }

    public function update($id, $data) {
        $project = $this->model->find($id);
        if ($project) {
            $project->update($data);
            return $project;
        }
        return null;
    }

    public function createProject($data) {
        return NameProjects::create($data);
    }

    public function delete($id) {
        return Projects::destroy($id);
    }

    public function getAll() {
        $result = $this->model->get();
        return $result;
    }
}
