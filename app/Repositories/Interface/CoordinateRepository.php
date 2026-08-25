<?php

namespace App\Repositories\Interface;

interface CoordinateRepository {
    public function create($data);

    public function getAll();
    public function getById($id);
    public function getByOfficeId($officeId);

    public function update($id, $data);

    public function delete($id);
}
