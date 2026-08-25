<?php

namespace App\Repositories\Implementation;

use App\Models\PermitReason;
use App\Repositories\Interface\PermitReasonRepository;

class PermitReasonRepositoryIMPL implements PermitReasonRepository {
    protected $model;

    public function __construct(PermitReason $permitReason) {
        $this->model = $permitReason;
    }

    public function save(array $data) {
        return $this->model->create($data);
    }

    public function findAll() {
        return $this->model->get();
    }

    public function findById(int $id) {
        return $this->model->find($id);
    }

    public function update($id, $data) {
        $data = $this->model->find($id);
        if (!$data) return false;
        $data->update($data);

        return true;
    }

    public function deleteById(int $id) {
        $data = $this->model->find($id);
        if (!$data) return false;
        $data->delete();

        return true;
    }
}
