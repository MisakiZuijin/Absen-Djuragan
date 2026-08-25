<?php

namespace App\Repositories\Interface;

interface ProfileRepository {
    public function getAll();
    public function store($data);
    public function update(int $id, array $data);
    public function findById($id);
    public function findByUserId($user_id);
    public function deleteById($id);
    public function updateProfile($id, $data);
}
