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
            ->with(['attendance', 'shift', 'adjustableAttendance', 'attdStatus', 'permitReason.category'])
            ->whereHas('schedule', function ($query) use ($internId) {
                $query->where('intern_id', $internId);
            })
            ->get();
    }

    public function findByInternIdAndMonth(int $internId, int $month)
    {
        return $this->model
            ->join("schedules", "schedules.id", '=', 'detail_schedules.schedule_id')
            ->join("attendances", "attendances.id", '=', 'detail_schedules.attendance_id')
            ->whereMonth('detail_schedules.date', $month)
            ->where('schedules.intern_id', $internId)
            ->whereDate('detail_schedules.date', '<', now())
            ->get();
    }

    public function findByInternIdAndWeek(int $internId, string $date)
    {
        $carbonDate = \Carbon\Carbon::parse($date);

        if ($carbonDate->isSunday()) {
            $startDate = $carbonDate->copy()->addWeek()->startOfWeek();
            $endDate = $carbonDate->copy()->addWeek()->endOfWeek();
        } else {
            $startDate = $carbonDate->copy()->startOfWeek();
            $endDate = $carbonDate->copy()->endOfWeek();
        }

        return $this->model
            ->join("schedules", "schedules.id", '=', 'detail_schedules.schedule_id')
            ->join("attendances", "attendances.id", '=', 'detail_schedules.attendance_id')
            ->whereBetween("detail_schedules.date", [$startDate, $endDate])
            ->where('schedules.intern_id', $internId)
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
        return $this->model
            ->join('schedules', 'detail_schedules.schedule_id', '=', 'schedules.id')
            ->join('interns', 'schedules.intern_id', '=', 'interns.id')
            ->join('users', 'interns.user_id', '=', 'users.id')
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->where('profiles.full_name', 'LIKE', "%$name%")
            ->select('detail_schedules.*')
            ->paginate($perPage, ['*'], 'page', $currentPage);
    }

    public function findByDate(string $date, int $perPage, int $currentPage)
    {
        if (!\Carbon\Carbon::hasFormat($date, 'Y-m-d')) {
            throw new \InvalidArgumentException("Invalid date format. Expected format: Y-m-d.");
        }
        return $this->model->where('date', $date)
            ->paginate($perPage, ['*'], 'page', $currentPage);
    }

    public function findByNameAndDate(string $name, string $date, int $perPage, int $currentPage)
    {
        if (!\Carbon\Carbon::hasFormat($date, 'Y-m-d')) {
            throw new \InvalidArgumentException("Invalid date format. Expected format: Y-m-d.");
        }

        return $this->model
            ->join('schedules', 'detail_schedules.schedule_id', '=', 'schedules.id')
            ->join('interns', 'schedules.intern_id', '=', 'interns.id')
            ->join('users', 'interns.user_id', '=', 'users.id')
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->where('profiles.full_name', 'LIKE', "%$name%")
            ->where('detail_schedules.date', $date)
            ->select('detail_schedules.*')
            ->paginate($perPage, ['*'], 'page', $currentPage);
    }

    public function findByCriteria(array $criteria, int $perPage, int $currentPage)
    {
        $query = $this->model;

        if (isset($criteria['status_id'])) {
            $query = $query->where('attd_status_id', $criteria['status_id']);
        }

        if (isset($criteria['office_id'])) {
            $query = $query->where('office_id', $criteria['office_id']);
        }

        if (isset($criteria['shift_id'])) {
            $query = $query->where('shift_id', $criteria['shift_id']);
        }

        return $query->where('date', $criteria['date'])->paginate($perPage, ['*'], 'page', $currentPage);
    }
}
