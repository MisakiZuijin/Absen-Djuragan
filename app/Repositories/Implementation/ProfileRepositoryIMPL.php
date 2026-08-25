<?php

namespace App\Repositories\Implementation;

use App\Models\Profile;
use App\Repositories\Interface\ProfileRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProfileRepositoryIMPL implements ProfileRepository {
    protected $model;

    public function __construct(Profile $profile) {
        $this->model = $profile;
    }

    public function getAll() {
        return $this->model->all();
    }

    public function store($data) {
        return $this->model->create($data);
    }

    public function update(int $id, array $data) {
        $entity = $this->model->find($id);

        if (!$entity) {
            throw new ModelNotFoundException("User with ID $id not found.");
        }

        if (!$entity->update($data)) {
            throw new \Exception("Failed to update user with ID $id.");
        }

        return $entity;
    }


    public function findById($id) {
        return $this->model->find($id);
    }

    public function findByUserId($id) {
        return $this->model->where("user_id", $id)->first();
    }

    public function deleteById($id) {
        return $this->model->where("id", $id)->delete();
    }

    public function updateProfile($id, $data)
    {
        $profile = $this->model->find($id);
        if ($profile) {
            $profile->update($data);
            return $profile;
        }
        return null;
    }
}
