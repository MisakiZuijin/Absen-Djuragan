<?php

namespace App\Repositories\Interface;

interface QuotesRepository
{
    public function create(array $data);
    public function getByCategory();
    public function getAll();
    public function delete(int $id);
}
