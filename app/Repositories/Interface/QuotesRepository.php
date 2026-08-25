<?php
namespace App\Repositories\Interface;

interface QuotesRepository {
    public function create($data);
    public function getByCategory();
    public function getAll();
    public function delete($id);
}
