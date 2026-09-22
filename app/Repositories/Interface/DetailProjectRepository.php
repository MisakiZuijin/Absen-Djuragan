<?php

namespace App\Repositories\Interface;

interface DetailProjectRepository
{
    public function create(array $data);
    public function update(int $id, array $data);
    public function getAll();
    public function delete(int $id);
}
