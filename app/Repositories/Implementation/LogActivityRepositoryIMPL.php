<?php

namespace App\Repositories\Implementation;

use App\Helper\LogConsole;
use App\Models\LogActivity;
use App\Repositories\Interface\LogActivityRepository;

class LogActivityRepositoryIMPL implements LogActivityRepository
{
    protected LogActivity $model;

    public function __construct(LogActivity $logActivity)
    {
        $this->model = $logActivity;
    }

    public function store(array $data)
    {
        $created = $this->model->create($data);

        if (!$created) {
            throw new \Exception('Failed to create log activity.');
        }

        return $created;
    }

    public function update(int $id, array $data)
    {
        $logActivity = $this->model->find($id);

        if (!$logActivity) {
            throw new \Exception('Log activity not found.');
        }

        $updated = $logActivity->update($data);

        if (!$updated) {
            throw new \Exception('Failed to update log activity.');
        }

        return $updated;
    }

    public function updateStatus(int $id, int $status_id)
    {
        $logActivity = $this->model->find($id);

        if (!$logActivity) {
            return false;
        }

        $logActivity->update(["status_id" => $status_id]);
        return true;
    }

    public function findById(int $id)
    {
        return $this->model->find($id);
    }

    public function findAll()
    {
        return $this->model->all();
    }

    public function count()
    {
        return $this->model->count();
    }

    public function findByInternId(int $internId)
    {
        return $this->model
            ->whereHas('detailSchedule.schedule', function ($query) use ($internId) {
                $query->where('intern_id', $internId);
            })
            ->get();
    }
}
