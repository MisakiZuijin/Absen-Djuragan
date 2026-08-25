<?php
namespace App\Repositories\Interface;

interface HolidayRepository {
    public function create();
    public function getAll();
    public function update();
    public function delete();
}
