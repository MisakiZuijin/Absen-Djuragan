<?php

namespace App\Repositories\Interface;

interface ProjectRepository {
    public function create($data);
    public function createProject($data);
    public function update($id, $data);
    public function getAll();
    public function delete($id);
}
