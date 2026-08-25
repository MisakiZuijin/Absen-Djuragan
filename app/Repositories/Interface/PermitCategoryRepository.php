<?php

namespace App\Repositories\Interface;

interface PermitCategoryRepository {

    public function save(array $data);

    public function findById(int $id);

    public function findAll();

    public function update(int $id, array $data);

    public function deleteById(int $id);
}
