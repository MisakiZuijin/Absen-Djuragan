<?php

namespace App\Repositories\Interface;

interface ProjectRepository
{
    public function create(array $data);
    public function createProject(array $data);
    public function update(int $id, array $data);
    public function getAll();
    public function delete(int $id);
}
