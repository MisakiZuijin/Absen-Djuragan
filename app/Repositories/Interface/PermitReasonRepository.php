<?php

namespace App\Repositories\Interface;

interface PermitReasonRepository {

    public function save(array $data);

    public function findById(int $id);

    public function findAll();

    public function update(int $id, array $data);

    public function deleteById(int $id);
}
