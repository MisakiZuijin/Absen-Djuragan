<?php

namespace App\Repositories\Implementation;

use App\Helper\LogConsole;
use App\Models\DetailSchedule;
use App\Repositories\Interface\DetailScheduleRepository;

class DetailScheduleRepositoryIMPL implements DetailScheduleRepository
{
    protected DetailSchedule $model;

    public function __construct(DetailSchedule $detailSchedule)
    {
        $this->model = $detailSchedule;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function find(int $id)
    {
        return $this->model->find($id);
    }

    public function findByScheduleIdAndDate(int $id, string $date)
    {
        return $this->model->where('schedule_id', $id)
            ->whereDate('date', $date)
            ->first();
    }

    public function all()
    {
        return $this->model->all();
    }

    public function update(int $id, array $data)
    {
        $detailSchedule = $this->model->find($id);

        if ($detailSchedule) {
            $detailSchedule->update($data);
            return $this->model->find($id);
        }

        return null;
    }

    public function delete(int $id): bool
    {
        $detailSchedule = $this->model->find($id);

        if ($detailSchedule) {
            return $detailSchedule->delete();
        }

        return false;
    }

    public function findByInternId(int $internId)
    {
        return $this->model
            ->with(['attendance.permitLogs', 'shift', 'adjustableAttendance', 'attdStatus', 'permitReason.category'])
            ->whereHas('schedule', function ($query) use ($internId) {
                $query->where('intern_id', $internId);
            })
            ->get();
    }

    public function findByInternIdAndMonth(int $internId, int $month)
    {
        return $this->model
            ->with(['shift', 'attdStatus', 'attendance.permitLogs'])
            ->whereHas('schedule', function ($query) use ($internId) {
                $query->where('intern_id', $internId);
            })
            ->whereMonth('date', $month)
            ->whereDate('date', '<', now())
            ->orderBy('date', 'asc')
            ->get();
    }

    public function findByInternIdAndWeek(int $internId, string $date)
    {
        $carbonDate = \Carbon\Carbon::parse($date);

        if ($carbonDate->isSunday()) {
            $startDate = $carbonDate->copy()->addWeek()->startOfWeek()->toDateString();
            $endDate = $carbonDate->copy()->addWeek()->endOfWeek()->toDateString();
        } else {
            $startDate = $carbonDate->copy()->startOfWeek()->toDateString();
            $endDate = $carbonDate->copy()->endOfWeek()->toDateString();
        }

        return $this->model
            ->with(['shift', 'attdStatus', 'attendance.permitLogs', 'schedule', 'office'])
            ->whereHas('schedule', function ($query) use ($internId) {
                $query->where('intern_id', $internId);
            })
            ->whereBetween("date", [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();
    }

    public function updateAttdStatus(int $id, int $statusId)
    {
        return $this->model->find($id)->update(["attd_status_id" => $statusId]);
    }

    public function countAttendance(string $date, int $status_attd_id)
    {
        if (empty($date) || !is_numeric($status_attd_id)) {
            return 0;
        }

        $count = $this->model->where('date', $date)
            ->where('attd_status_id', $status_attd_id)
            ->count();

        if (is_null($count)) {
            return 0;
        }

        return $count;
    }

    public function updateShift(int $id, int $shiftId)
    {
        return $this->model->find($id)->update(["shift_id", $shiftId]);
    }

    public function findByName(string $name, int $perPage, int $currentPage)
    {
        return $this->findByCriteria(['name' => $name], $perPage, $currentPage);
    }

    public function findByDate(string $date, int $perPage, int $currentPage)
    {
        return $this->findByCriteria(['date' => $date], $perPage, $currentPage);
    }

    public function findByNameAndDate(string $name, string $date, int $perPage, int $currentPage)
    {
        return $this->findByCriteria(['name' => $name, 'date' => $date], $perPage, $currentPage);
    }

    public function findByCriteria(array $criteria, int $perPage, int $currentPage)
    {
        $query = $this->model->newQuery();

        if (!empty($criteria['date'])) {
            $query->where('date', $criteria['date']);
        }

        if (!empty($criteria['status_id'])) {
            $query->where('attd_status_id', $criteria['status_id']);
        }

        if (!empty($criteria['office_id'])) {
            $query->where('office_id', $criteria['office_id']);
        }

        if (!empty($criteria['shift_id'])) {
            $query->where('shift_id', $criteria['shift_id']);
        }

        if (!empty($criteria['name'])) {
            $name = trim($criteria['name']);
            $query->whereHas('schedule.intern', function ($internQuery) use ($name) {
                $internQuery->where(function ($iq) use ($name) {
                    $iq->where('nim', 'like', "%{$name}%")
                        ->orWhereHas('user', function ($uq) use ($name) {
                            $uq->where('username', 'like', "%{$name}%")
                                ->orWhereHas('profile', function ($pq) use ($name) {
                                    $pq->where('full_name', 'like', "%{$name}%")
                                        ->orWhere('NIP', 'like', "%{$name}%");
                                });
                        });
                });
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $currentPage);
    }
}
