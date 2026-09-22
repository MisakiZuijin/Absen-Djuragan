<?php

namespace App\Repositories\Implementation;

use App\Models\Holiday;
use App\Repositories\Interface\HolidayRepository;

class HolidayRepositoryIMPL implements HolidayRepository
{
    protected Holiday $model;

    public function __construct(Holiday $holiday)
    {
        $this->model = $holiday;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function create() {}

    public function update() {}

    public function delete() {}
}
