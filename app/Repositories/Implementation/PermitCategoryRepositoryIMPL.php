<?php

namespace App\Repositories\Implementation;

use App\Models\PermitCategory;
use App\Repositories\Interface\PermitCategoryRepository;

class PermitCategoryRepositoryIMPL implements PermitCategoryRepository
{
    protected PermitCategory $model;

    public function __construct(PermitCategory $permitReason)
    {
        $this->model = $permitReason;
    }

    public function save(array $data)
    {
        return $this->model->create($data);
    }

    public function findAll()
    {
        return \Illuminate\Support\Facades\Cache::remember('permit_categories_all', 3600, fn() => $this->model->get());
    }

    public function findById(int $id)
    {
        return $this->model->find($id);
    }

    public function update(int $id, array $data)
    {
        $record = $this->model->find($id);
        if (!$record) return false;
        $record->update($data);

        return true;
    }

    public function deleteById(int $id)
    {
        $record = $this->model->find($id);
        if (!$record) return false;
        $record->delete();

        return true;
    }
}
