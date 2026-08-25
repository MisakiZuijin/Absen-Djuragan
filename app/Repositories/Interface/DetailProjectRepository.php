<?php

namespace App\Repositories\Interface;

interface DetailProjectRepository {
    public function create($data);
    public function update($id, $data);
    public function getAll();
    public function delete($id);
}
