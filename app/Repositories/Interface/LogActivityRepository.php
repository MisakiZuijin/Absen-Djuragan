<?php

namespace App\Repositories\Interface;

interface LogActivityRepository {

    public function store($data);
    public function update($id, $data);
    public function updateStatus($id, $status_id);
    public function findById($id);
    public function findByInternId($internId);
    public function findAll();
    public function count();
}
