<?php

namespace App\Repositories\Implementation;

use App\Models\Quotes;
use App\Repositories\Interface\QuotesRepository;

class QuotesRepositoryIMPL implements QuotesRepository
{
    protected Quotes $model;

    public function create(array $data)
    {
        return Quotes::create($data);
    }

    public function getByCategory()
    {
        return Quotes::all();
    }

    public function getAll()
    {
        return Quotes::query();
    }

    public function delete(int $id)
    {
        return Quotes::destroy($id);
    }
}
