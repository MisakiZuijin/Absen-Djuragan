<?php

namespace App\Repositories\Interface;

interface OfficeRepository {
    public function getAll();
    public function create($data);
    public function update($id, $data);
    public function delete($id);
}
