<?php

namespace App\Repositories\Implementation;

use App\Models\User;
use App\Models\Profile;
use App\Repositories\Interface\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class UserRepositoryIMPL implements UserRepository
{
    protected User $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function attemptLogin(mixed $credentials)
    {
        return Auth::login($credentials);
    }

    public function getAuthenticatedUser()
    {
        return Auth::user();
    }

    public function deleteAuthenticatedUser()
    {
        Auth::logout();
    }

    public function store(array $data)
    {
        return $this->model->create($data);
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByUsername(string $username)
    {
        return $this->model->where('username', $username)->first();
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function findById(int $id)
    {
        return $this->model->find($id);
    }

    public function deleteById(int $id)
    {
        return $this->model->where('id', $id)->delete();
    }

    public function update(int $id, array $data)
    {
        $entity = $this->model->find($id);

        if (!$entity) {
            throw new ModelNotFoundException("User with ID $id not found.");
        }

        if (!$entity->update($data)) {
            throw new \Exception("Failed to update user with ID $id.");
        }

        return $entity;
    }

    public function createPermitPresence(mixed $data) {}
}
