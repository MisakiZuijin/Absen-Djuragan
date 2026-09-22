<?php

namespace App\Repositories\Interface;

interface ProfileRepository
{
    public function getAll();
    public function store(array $data);
    public function update(int $id, array $data);
    public function findById(int $id);
    public function findByUserId(int $user_id);
    public function deleteById(int $id);
    public function updateProfile(int $id, array $data);
}
