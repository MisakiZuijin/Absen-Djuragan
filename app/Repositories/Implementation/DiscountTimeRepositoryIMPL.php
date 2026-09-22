<?php

namespace App\Repositories\Implementation;

use App\Models\DiscountTime;
use App\Repositories\Interface\DiscountTimeRepository;

class DiscountTimeRepositoryIMPL implements DiscountTimeRepository
{
    protected DiscountTime $discountModel;

    public function __construct(DiscountTime $discountModel)
    {
        $this->discountModel = $discountModel;
    }

    public function store(array $data)
    {
        return $this->discountModel->create($data);
    }

    public function update(int $id, array $data)
    {
        $discount = $this->discountModel->find($id);

        if ($discount) {
            $discount->update($data);
            return $discount;
        }

        return null;
    }

    public function findById(int $id)
    {
        return $this->discountModel->find($id);
    }

    public function findByScheduleId(int $scheduleId)
    {
        return $this->discountModel->where("schedule_id", $scheduleId)->first();
    }

    public function delete(int $id)
    {
        $discount = $this->discountModel->find($id);

        if ($discount) {
            $discount->delete();
            return true;
        }

        return false;
    }
}
