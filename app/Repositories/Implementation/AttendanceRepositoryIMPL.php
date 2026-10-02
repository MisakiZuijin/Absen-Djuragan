<?php

namespace App\Repositories\Implementation;

use App\Helper\LogConsole;
use App\Models\Attendance;
use App\Repositories\Interface\AttendanceRepository;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AttendanceRepositoryIMPL implements AttendanceRepository
{
    protected Attendance $model;

    public function __construct(Attendance $absenceModel)
    {
        $this->model = $absenceModel;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $model = $this->model->find($id);

        // Add null check to prevent the error
        if (!$model) {
            throw new ModelNotFoundException("Attendance record with ID {$id} not found");
        }

        $model->update($data);

        return $model->fresh();
    }

    public function updateTime(int $id, array $data)
    {
        $attendance = $this->model->find($id);

        if (!$attendance) {
            return null;
        }

        $field = $data['field'];
        $time = $data['time'];

        if (!in_array($field, ['start_time', 'end_time', 'break_time', 'back_time'])) {
            throw new InvalidArgumentException("Field tidak valid: $field");
        }

        $attendance->$field = $time;

        // Update message field jika ada
        $messageField = $field . '_message';
        if (isset($data['message']) && property_exists($attendance, $messageField)) {
            $attendance->$messageField = $data['message'];
        }

        // Calculate times
        $this->calculateAndSaveTimes($attendance);

        $attendance->save();
        return $attendance;
    }

    public function updateAdjustableTime(int $id, array $data)
    {
        $field = $data['field'];
        $time = $data['time'];

        // Update adjustable time
        $updated = DB::table('adjustable_attds')->where('id', $id)->update([$field => $time]);

        if (!$updated) {
            return null;
        }

        // Get updated data
        $adjustableData = DB::table('adjustable_attds')->where('id', $id)->first();

        if (!$adjustableData) {
            return null;
        }

        // Calculate and update totals
        $totalMinutes = $this->calculateTotalMinutes(
            $adjustableData->start_time,
            $adjustableData->end_time
        );

        $totalBreakMinutes = $this->calculateBreakMinutes(
            $adjustableData->break_time,
            $adjustableData->back_time
        );

        DB::table('adjustable_attds')
            ->where('id', $id)
            ->update([
                'total_min' => max($totalMinutes, 0),
                'total_break_min' => max($totalBreakMinutes, 0),
            ]);

        return DB::table('adjustable_attds')->where('id', $id)->first();
    }

    public function calculateTotalMinutes(?string $startTime, ?string $endTime)
    {
        $startTime = $startTime ? strtotime($startTime) : null;
        $endTime = $endTime ? strtotime($endTime) : null;

        return $startTime && $endTime ? round(($endTime - $startTime) / 60) : 0;
    }

    public function calculateBreakMinutes(?string $breakTime, ?string $backTime)
    {
        $breakTime = $breakTime ? strtotime($breakTime) : null;
        $backTime = $backTime ? strtotime($backTime) : null;

        return $breakTime && $backTime ? round(($backTime - $breakTime) / 60) : 0;
    }

    private function calculateAndSaveTimes(Attendance $attendance)
    {
        $totalMinutes = $this->calculateTotalMinutes(
            $attendance->start_time,
            $attendance->end_time
        );

        $totalBreakMinutes = $this->calculateBreakMinutes(
            $attendance->break_time,
            $attendance->back_time
        );

        $attendance->total_min = max($totalMinutes, 0);
        $attendance->total_break_min = max($totalBreakMinutes, 0);
    }

    public function updateStatus(int $id, int $status_id)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return false;
        }
        $data->status_id = $status_id;
        $data->save();
        return true;
    }

    public function getById(int $id)
    {
        return $this->model->find($id);
    }

    public function getByDate(string $date, int $perPage, int $currentPage)
    {
        // return $this->model
        //     ->whereHas('detailSchedules', function ($query) use ($date) {
        //         $query->where('date', $date);
        //     })
        //     ->paginate($perPage, ['*'], 'page', $currentPage);

        return $this->model->where('date', $date)->paginate($perPage, ['*'], 'page', $currentPage);
    }


    public function getByInternIdAndDate(int $intern, string $date)
    {
        return $this->model->where("intern_id", $intern)->where("date", $date)->first();
    }

    public function getByName(string $name, int $perPage, int $currentPage)
    {
        return $this->model
            ->join('interns', 'attendances.intern_id', '=', 'interns.id')
            ->join('users', 'interns.user_id', '=', 'users.id')
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->where('profiles.full_name', 'LIKE', "%$name%")
            ->select('attendances.*')
            ->paginate($perPage, ['*'], 'page', $currentPage);
    }



    public function getByNameAndDate(string $name, string $date, int $perPage, int $currentPage)
    {
        return $this->model
            ->join('detail_schedules', 'attendances.id', '=', 'detail_schedules.attendance_id')
            ->join('schedules', 'schedules.id', '=', 'detail_schedules.schedule_id')
            ->join('interns', 'interns.id', '=', 'schedules.intern_id')
            ->join('users', 'interns.user_id', '=', 'users.id')
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->where('profiles.full_name', 'LIKE', "%{$name}%")
            ->where('detail_schedules.date', $date)
            ->select('attendances.*')
            ->paginate($perPage, ['*'], 'page', $currentPage);
    }


    public function getAll() {}

    public function getByWeek(int $profileId, string $date)
    {

        return $this->model->where("profile_id", $profileId)->whereBetween('date', [$date, $date]);
    }

    public function getByMonth(int $internId, int $month)
    {
        return $this->model
            ->whereHas('detailSchedules.schedule.intern', function ($query) use ($internId) {
                $query->where('interns.id', $internId);
            })
            ->whereHas('detailSchedules', function ($query) use ($month) {
                $query->whereMonth('date', $month);
            })
            ->get();
    }

    public function getDateBetween(int $internId, string $start, string $end)
    {
        return $this->model
            ->whereHas('detailSchedules.schedule.intern', function ($query) use ($internId) {
                $query->where('interns.id', $internId);
            })
            ->whereHas('detailSchedules', function ($query) use ($start, $end) {
                $query->whereBetween('date', [$start, $end]);
            })
            ->get();
    }

    public function countAbsenceByDay(string $date)
    {
        return $this->model->where("date", $date)->count();
    }

    public function updateStatusByShiftAndDate(int $status, int $shiftId, string $dateNow, ?string $endTime = null)
    {

        $endTime = $endTime ?? now();

        if ($status == 5) {
            return $this->model
                ->join('detail_schedules', 'attendances.id', '=', 'detail_schedules.attendance_id')
                ->where('attendances.date', $dateNow)
                ->whereNull('attendances.start_time')
                ->where('detail_schedules.shift_id', $shiftId)
                ->update(['detail_schedules.attd_status_id' => $status]);
        }

        return $this->model
            ->join('detail_schedules', 'attendances.id', '=', 'detail_schedules.attendance_id')
            ->where('attendances.date', $dateNow)
            ->whereNull('attendances.start_time')
            ->where('detail_schedules.shift_id', $shiftId)
            ->update(['attendance.end_time' => $endTime]);
    }


    public function updateEndTimeAll(string $dateNow, int $shiftId, string $endTime)
    {
        // Ambil tanggal sekarang jika belum ada
        $currentDate = date('Y-m-d');

        // Format ulang $endTime menjadi Y-m-d H:i:s
        try {
            $time = new DateTime("$currentDate $endTime"); // Kombinasi tanggal dan waktu
            $endTime = $time->format('Y-m-d H:i:s'); // Format menjadi Y-m-d H:i:s
        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid end time provided');
        }

        $updated = $this->model
            ->join('detail_schedules', 'attendances.id', '=', 'detail_schedules.attendance_id')
            ->where('attendances.date', $dateNow)
            ->where('detail_schedules.shift_id', $shiftId)
            ->whereNotNull('attendances.start_time')
            ->update([
                'attendances.end_time' => $endTime,
                'attendances.is_auto_end' => true,
                'attendances.total_min' => DB::raw("GREATEST(0, TIMESTAMPDIFF(MINUTE, attendances.start_time, '{$endTime}'))"),
            ]);

        return $updated;
    }



    public function getByIdAndAutomaticalyStatus(int $id, $perPage = 10, $currentPage = 1)
    {
        return $this->model
            ->where("id", $id)
            ->where("is_auto_end", true)
            ->orderBy("date", "desc")
            ->paginate($perPage, ['*'], 'page', $currentPage);
    }

    private function buildAutoEndQuery(?string $name = null, ?string $date = null)
    {
        $query = $this->model->newQuery()
            ->with([
                'intern.user.profile',
                'intern.division',
                'intern.school',
                'intern.shift',
                'detailSchedules.office',
                'detailSchedules.shift',
                'detailSchedules.schedule.intern.user.profile',
                'detailSchedules.schedule.intern.division',
                'detailSchedules.schedule.intern.school',
            ])
            ->where("is_auto_end", true);

        if (!empty($date)) {
            $query->whereDate("date", $date);
        }

        if (!empty($name)) {
            $query->where(function ($q) use ($name) {
                if (is_numeric($name)) {
                    $q->where('intern_id', $name)
                      ->orWhere('id', $name);
                }
                $q->orWhereHas('intern.user.profile', function ($p) use ($name) {
                    $p->where('full_name', 'LIKE', "%{$name}%");
                })->orWhereHas('intern.user', function ($u) use ($name) {
                    $u->where('name', 'LIKE', "%{$name}%")
                      ->orWhere('username', 'LIKE', "%{$name}%");
                })->orWhereHas('intern', function ($i) use ($name) {
                    $i->where('nim', 'LIKE', "%{$name}%");
                })->orWhereHas('detailSchedules.schedule.intern.user.profile', function ($p) use ($name) {
                    $p->where('full_name', 'LIKE', "%{$name}%");
                });
            });
        }

        return $query->orderBy("date", "desc")->orderBy("id", "desc");
    }

    public function getAllAutoEnd($perPage = 10, $currentPage = 1)
    {
        return $this->buildAutoEndQuery()->paginate($perPage, ['*'], 'page', $currentPage);
    }


    public function getAutoEndStatusByDate(string $date, $perPage = 10, $currentPage = 1)
    {
        return $this->buildAutoEndQuery(date: $date)->paginate($perPage, ['*'], 'page', $currentPage);
    }


    public function getAutoEndStatusByName(string $name, int $perPage = 10, int $currentPage = 1)
    {
        return $this->buildAutoEndQuery(name: $name)->paginate($perPage, ['*'], 'page', $currentPage);
    }

    public function getAutoEndStatusByDateAndName(string $name, string $date, $perPage = 10, $currentPage = 1)
    {
        return $this->buildAutoEndQuery(name: $name, date: $date)->paginate($perPage, ['*'], 'page', $currentPage);
    }

    public function getTotalCount(?string $name = null, ?string $date_start = null, ?string $date_end = null)
    {
        $query = $this->model->newQuery()->where("is_auto_end", true);

        if (!empty($date_start) && !empty($date_end)) {
            if ($date_start === $date_end) {
                $query->whereDate('date', $date_start);
            } else {
                $query->whereBetween('date', [$date_start, $date_end]);
            }
        } elseif (!empty($date_start)) {
            $query->whereDate('date', '>=', $date_start);
        } elseif (!empty($date_end)) {
            $query->whereDate('date', '<=', $date_end);
        }

        if (!empty($name)) {
            $query->where(function ($q) use ($name) {
                if (is_numeric($name)) {
                    $q->where('intern_id', $name)
                      ->orWhere('id', $name);
                }
                $q->orWhereHas('intern.user.profile', function ($p) use ($name) {
                    $p->where('full_name', 'LIKE', "%{$name}%");
                })->orWhereHas('intern.user', function ($u) use ($name) {
                    $u->where('name', 'LIKE', "%{$name}%")
                      ->orWhere('username', 'LIKE', "%{$name}%");
                })->orWhereHas('intern', function ($i) use ($name) {
                    $i->where('nim', 'LIKE', "%{$name}%");
                })->orWhereHas('detailSchedules.schedule.intern.user.profile', function ($p) use ($name) {
                    $p->where('full_name', 'LIKE', "%{$name}%");
                });
            });
        }

        return $query->count();
    }

    public function getAttendanceStillNotBack(string $date, int $shiftId)
    {
        return $this->model
            ->join('detail_schedules', 'attendances.id', '=', 'detail_schedules.attendance_id')
            ->where('detail_schedules.shift_id', $shiftId)
            ->where('attendances.end_time', null)
            ->whereNotNull('attendances.start_time')
            ->where('attendances.date', $date)
            ->select('attendances.*')
            ->get();
    }
}
