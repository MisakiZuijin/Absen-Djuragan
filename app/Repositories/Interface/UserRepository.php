<?php

namespace App\Repositories\Interface;

interface UserRepository
{
    public function attemptLogin(mixed $credentials);
    public function getAuthenticatedUser();
    public function deleteAuthenticatedUser();
    public function store(array $data);
    public function update(int $id, array $data);
    public function findById(int $id);
    public function findByEmail(string $email);
    public function findByUsername(string $username);
    public function getAll();
    public function deleteById(int $id);
}
