<?php

namespace App\Repositories\Implementation;

use App\Helper\LogConsole;
use App\Models\Attendance;
use App\Repositories\Interface\AttendanceRepository;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AttendanceRepositoryIMPL implements AttendanceRepository {
    protected $model;

    public function __construct(Attendance $absenceModel) {
        $this->model = $absenceModel;
    }

    public function create($data) {
        return $this->model->create($data);
    }

    public function update($id, $data) {
        $model = $this->model->find($id);

        // Add null check to prevent the error
        if (!$model) {
            throw new ModelNotFoundException("Attendance record with ID {$id} not found");
        }

        $model->update($data);

        return $model->fresh();
    }

    public function updateTime($id, $data) {
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

    public function updateAdjustableTime($id, $data) {
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

    public function calculateTotalMinutes($startTime, $endTime) {
        $startTime = $startTime ? strtotime($startTime) : null;
        $endTime = $endTime ? strtotime($endTime) : null;

        return $startTime && $endTime ? round(($endTime - $startTime) / 60) : 0;
    }

    public function calculateBreakMinutes($breakTime, $backTime) {
        $breakTime = $breakTime ? strtotime($breakTime) : null;
        $backTime = $backTime ? strtotime($backTime) : null;

        return $breakTime && $backTime ? round(($backTime - $breakTime) / 60) : 0;
    }

    private function calculateAndSaveTimes($attendance) {
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

    public function updateStatus($id, $status_id) {
        $data = $this->model->find($id);
        if (!$data) {
            return false;
        }
        $data->status_id = $status_id;
        $data->save();
        return true;
    }

    public function getById($id) {
        return $this->model->find($id);
    }

    public function getByDate($date, $perPage, $currentPage) {
        // return $this->model
        //     ->whereHas('detailSchedules', function ($query) use ($date) {
        //         $query->where('date', $date);
        //     })
        //     ->paginate($perPage, ['*'], 'page', $currentPage);

        return $this->model->where('date', $date)->paginate($perPage, ['*'], 'page', $currentPage);
    }


    public function getByInternIdAndDate($intern, $date) {
        return $this->model->where("intern_id", $intern)->where("date", $date)->first();
    }

    public function getByName($name, $perPage, $currentPage) {
        return $this->model
            ->join('interns', 'attendances.intern_id', '=', 'interns.id')
            ->join('users', 'interns.user_id', '=', 'users.id')
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->where('profiles.full_name', 'LIKE', "%$name%")
            ->select('attendances.*')
            ->paginate($perPage, ['*'], 'page', $currentPage);
    }



    public function getByNameAndDate($name, $date, $perPage, $currentPage) {
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


    public function getAll() {
    }

    public function getByWeek($profileId, $date) {

        return $this->model->where("profile_id", $profileId)->whereBetween('date', [$date, $date]);
    }

    public function getByMonth($internId, $month) {
        return $this->model
            ->whereHas('detailSchedules.schedule.intern', function ($query) use ($internId) {
                $query->where('interns.id', $internId);
            })
            ->whereHas('detailSchedules', function ($query) use ($month) {
                $query->whereMonth('date', $month);
            })
            ->get();
    }

    public function getDateBetween($internId, $start, $end) {
        return $this->model
            ->whereHas('detailSchedules.schedule.intern', function ($query) use ($internId) {
                $query->where('interns.id', $internId);
            })
            ->whereHas('detailSchedules', function ($query) use ($start, $end) {
                $query->whereBetween('date', [$start, $end]);
            })
            ->get();
    }

    public function countAbsenceByDay($date) {
        return $this->model->where("date", $date)->count();
    }

    public function updateStatusByShiftAndDate($status, $shiftId, $dateNow, $endTime = null) {

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


    public function updateEndTimeAll($dateNow, $shiftId, $endTime) {
        // Ambil tanggal sekarang jika belum ada
        $currentDate = date('Y-m-d');

        // Format ulang $endTime menjadi Y-m-d H:i:s
        try {
            $time = new DateTime("$currentDate $endTime"); // Kombinasi tanggal dan waktu
            $endTime = $time->format('Y-m-d H:i:s'); // Format menjadi Y-m-d H:i:s
        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid end time provided');
        }

        return $this->model
            ->join('detail_schedules', 'attendances.id', '=', 'detail_schedules.attendance_id')
            ->where('attendances.date', $dateNow)
            ->where('detail_schedules.shift_id', $shiftId)
            ->whereNotNull('attendances.start_time')
            ->update([
                'attendances.end_time' => $endTime,
                'is_auto_end' => true,
                'attendances.total_min' => DB::raw("TIMESTAMPDIFF(MINUTE, attendances.start_time, '$endTime')")
            ]);
    }


    public function createPermitPresence($data) {
    }

    public function updatePermitPresence($data) {
    }

    public function updateShift() {
    }

    public function storeNote() {
    }
    // public function getAllChangeTime($internId)
    // {
    // }


    public function getByIdAndAutomaticalyStatus($id, $perPage = 10, $currentPage = 1) {
        return $this->model->where("id", $id)->where("is_auto_end", operator: true)->orderBy("date", "desc")->get();
    }

    public function getAllAutoEnd($perPage = 10, $currentPage = 1) {
        return $this->model
            ->where("is_auto_end", true)
            ->orderBy("date", "desc")
            ->paginate($perPage, ['*'], 'page', $currentPage);
    }


    public function getAutoEndStatusByDate($date, $perPage = 10, $currentPage = 1) {
        return $this->model
            ->where("attendances.date", $date)
            ->where("is_auto_end", true)
            ->orderBy("date", "desc")
            ->paginate($perPage, ['*'], 'page', $currentPage);
    }


    public function getAutoEndStatusByName($name, int $perPage = 10, int $currentPage = 1) {
        return $this->model->join('detail_schedules', 'attendances.id', '=', 'detail_schedules.attendance_id')
            ->join('schedules', 'schedules.id', '=', 'detail_schedules.schedule_id')
            ->join('interns', 'interns.id', '=', 'schedules.intern_id')
            ->join('users', 'interns.user_id', '=', 'users.id')
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->where('profiles.full_name', 'LIKE', "%{$name}%")
            ->where("is_auto_end", true)
            ->orderBy("attendances.date", "desc")
            ->select('attendances.*')->paginate($perPage, ['*'], 'page', $currentPage);
    }

    public function getAutoEndStatusByDateAndName($name, $date, $perPage = 10, $currentPage = 1) {
        return $this->model->join('detail_schedules', 'attendances.id', '=', 'detail_schedules.attendance_id')
            ->join('schedules', 'schedules.id', '=', 'detail_schedules.schedule_id')
            ->join('interns', 'interns.id', '=', 'schedules.intern_id')
            ->join('users', 'interns.user_id', '=', 'users.id')
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->where('profiles.full_name', 'LIKE', "%{$name}%")
            ->where("attendances.date", $date)
            ->where("is_auto_end", true)
            ->orderBy("attendances.date", "desc")
            ->select('attendances.*')
            ->paginate($perPage, ['*'], 'page', $currentPage);
    }

    public function getTotalCount($name = null, $date_start = null, $date_end = null) {
        if (is_null($date_start) || is_null($date_end)) {
            return $this->model
                ->join('detail_schedules', 'attendances.id', '=', 'detail_schedules.attendance_id')
                ->join('schedules', 'schedules.id', '=', 'detail_schedules.schedule_id')
                ->join('interns', 'interns.id', '=', 'schedules.intern_id')
                ->join('users', 'interns.user_id', '=', 'users.id')
                ->join('profiles', 'users.id', '=', 'profiles.user_id')
                ->where('profiles.full_name', 'LIKE', "%{$name}%")
                ->where("is_auto_end", true)
                ->count();
        }

        if (is_null($date_start) && is_null($date_end)) {
            return $this->model
                ->join('detail_schedules', 'attendances.id', '=', 'detail_schedules.attendance_id')
                ->join('schedules', 'schedules.id', '=', 'detail_schedules.schedule_id')
                ->join('interns', 'interns.id', '=', 'schedules.intern_id')
                ->join('users', 'interns.user_id', '=', 'users.id')
                ->join('profiles', 'users.id', '=', 'profiles.user_id')
                // ->where('profiles.full_name', 'LIKE', "%{$name}%")
                ->where("is_auto_end", true)
                ->whereBetween('attendances.date', [$date_start, $date_end])
                ->count();
        }
        return $this->model
            ->join('detail_schedules', 'attendances.id', '=', 'detail_schedules.attendance_id')
            ->join('schedules', 'schedules.id', '=', 'detail_schedules.schedule_id')
            ->join('interns', 'interns.id', '=', 'schedules.intern_id')
            ->join('users', 'interns.user_id', '=', 'users.id')
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->where('profiles.full_name', 'LIKE', "%{$name}%")
            ->where("is_auto_end", true)
            ->whereBetween('attendances.date', [$date_start, $date_end])
            ->count();
    }

    public function getAttendanceStillNotBack($date, $shiftId) {
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
