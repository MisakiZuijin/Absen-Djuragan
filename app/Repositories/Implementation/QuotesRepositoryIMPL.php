<?php

namespace App\Repositories\Implementation;

use App\Models\Quotes;
use App\Repositories\Interface\QuotesRepository;

class QuotesRepositoryIMPL implements QuotesRepository {
    protected $model;

    public function create($data)
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


    public function delete($id)
    {
        return Quotes::destroy($id);
    }
}
