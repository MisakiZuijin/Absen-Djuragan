<?php

    namespace App\Services;

    use Carbon\Carbon;
    use App\Models\User;
    use App\Models\HandRaise;
    use App\Models\LogActivity;
    use App\Models\Status;
    use App\Models\Intern;
    use App\Models\PermitLog;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Contracts\Pagination\LengthAwarePaginator;

    class AssistantAdminService
    {
        public function getAssistantAdmins()
        {
            return User::where('role_id', 6)
                    ->latest()
                    ->paginate(10);
                 }

        public function createAssistantAdmin(array $data)
        {
            return User::create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role_id' => 6,
                'is_active' => true,
                'is_confirm' => true,
                'os' => '',
                'browser' => '',
                'device' => '',
                'is_reset_token' => false,
                'is_gps_support' => false,
                'is_gps_activate' => false,
            ]);
        }

        public function updateAssistantAdmin(User $user, array $data)
        {
            $updateData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'role_id' => 6,
                'is_active' => true,
                'is_confirm' => true,
                'os' => '',
                'browser' => '',
                'device' => '',
                'is_reset_token' => false,
                'is_gps_support' => false,
                'is_gps_activate' => false,
            ];

            if (!empty($data['password'])) {
                $updateData['password'] = $data['password'];
            }

            return $user->update($updateData);
        }

        public function getDashboardData()
        {
            $waitingStudentsCount = HandRaise::where('is_raised', true)->count();

            $pendingStatus = Status::where('name', 'In Review')->first();
            $approvedStatus = Status::where('name', 'Accepted')->first();

            $pendingLogsCount = $pendingStatus
                ? LogActivity::where('status_id', $pendingStatus->id)->count()
                : 0;

            $approvedLogsTodayCount = $approvedStatus
                ? LogActivity::where('status_id', $approvedStatus->id)
                            ->whereDate('date', today())
                            ->count()
                : 0;

            $recentActivities = $pendingStatus
                ? LogActivity::with('user')
                    ->where('status_id', $pendingStatus->id)
                    ->latest('date')
                    ->take(5)
                    ->get()
                : collect();

            $waitingStudents = HandRaise::with([
                    'user.profile',
                    'user.intern.school',
                    'user.intern.detailProject.project'
                ])
                ->where('is_raised', true)
                ->latest()
                ->take(5)
                ->get();

            return [
                'waitingStudentsCount' => $waitingStudentsCount,
                'pendingLogsCount' => $pendingLogsCount,
                'approvedLogsTodayCount' => $approvedLogsTodayCount,
                'recentActivities' => $recentActivities,
                'waitingStudents' => $waitingStudents
            ];
        }

        public function getRaiseHandList()
        {
            return HandRaise::with([
                'user.profile',
                'user.intern',
                'user.intern.school',
                'user.intern.detailProject.project.nameProject'
            ])->where('is_raised', true)->get();
        }

        public function confirmHandRaise($id)
        {
            $handRaise = HandRaise::findOrFail($id);
            $handRaise->is_raised = false;
            $handRaise->save();
            return $handRaise;
        }

        public function getLogActivities($selectedMonth, $selectedDate, $status)
        {
            $dates = LogActivity::selectRaw('DATE(date) as date')
                ->whereHas('detailSchedule.schedule.intern.user')
                ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$selectedMonth])
                ->groupBy('date')
                ->orderByDesc('date')
                ->pluck('date')
                ->map(fn($d) => Carbon::parse($d)->toDateString());

            $months = LogActivity::selectRaw("DATE_FORMAT(date, '%Y-%m') as value")
                ->whereHas('detailSchedule.schedule.intern.user')
                ->groupBy('value')
                ->orderByDesc('value')
                ->pluck('value')
                ->map(function($val) {
                    return [
                        'value' => $val,
                        'label' => Carbon::createFromFormat('Y-m', $val)->translatedFormat('F Y')
                    ];
                });

            $query = LogActivity::with([
                'detailSchedule.schedule.intern.user.profile',
                'detailSchedule.schedule.intern.school',
                'status'
            ])
            ->whereHas('detailSchedule.schedule.intern.user')
            ->whereDate('date', $selectedDate);

            if ($status === 'pending') {
                $query->whereHas('status', function($q) {
                    $q->where('name', 'In Review');
                });
            } elseif ($status === 'processed') {
                $query->whereHas('status', function($q) {
                    $q->whereIn('name', ['Accepted', 'Rejected']);
                });
            }

            $logActivities = $query->latest('date')->get();

            return [
                'dates' => $dates,
                'months' => $months,
                'logActivities' => $logActivities
            ];
        }

        public function updateLogActivityStatus($id, $activity, $action)
        {
            $log = LogActivity::with([
                'detailSchedule.schedule.intern.user.profile',
                'detailSchedule.schedule.intern.school',
                'status'
            ])->findOrFail($id);

            $log->activity = $activity;

            if ($action === 'approve') {
                $approvedStatus = Status::where('name', 'Accepted')->firstOrFail();
                $log->status_id = $approvedStatus->id;
                $log->is_approved = true;
            } else {
                $rejectedStatus = Status::where('name', 'Rejected')->firstOrFail();
                $log->status_id = $rejectedStatus->id;
                $log->is_approved = false;
            }

            $log->save();
            return $log;
        }

       public function getInternsWithPermits(string $type, int $perPage = 25): LengthAwarePaginator
{
    return Intern::query()
        ->with('user.profile', 'school', 'activePermitLog')
        ->whereHas('attendances', function ($query) {
            $query->whereDate('date', today());
        })
        ->whereHas('user', function ($query) {
            $query->where('is_active', true);
        })
        ->leftJoin('attendances', function ($join) {
            $join->on('interns.id', '=', 'attendances.intern_id')
                 ->whereDate('attendances.date', today());
        })
        ->leftJoin('permit_logs', function ($join) use ($type) {
            $join->on('attendances.id', '=', 'permit_logs.attendance_id')
                ->where('permit_logs.type', '=', $type)
                ->whereNull('permit_logs.end_time');
        })
        ->select('interns.*', DB::raw('permit_logs.id as permit_log_id'), DB::raw('permit_logs.start_time as permit_start_time'))
        ->distinct()
        ->orderBy(DB::raw('permit_logs.id IS NOT NULL'), 'desc')
        ->orderBy('permit_start_time', 'asc')
        ->orderBy('interns.id', 'asc')
        ->paginate($perPage);
}


        public function getPermitDuration(PermitLog $permitLog, $expectedType)
        {
            // Kode ini sudah benar dan tidak perlu diubah
            if ($permitLog->type !== $expectedType) {
                return [
                    'error' => "Not a {$expectedType} permit",
                    'duration' => '00:00:00',
                    'is_active' => false
                ];
            }

            if ($permitLog->end_time) {
                return [
                    'duration' => '00:00:00',
                    'is_active' => false
                ];
            }

            $duration = Carbon::parse($permitLog->start_time)->diff(now());
            $durationString = sprintf('%02d:%02d:%02d', $duration->h, $duration->i, $duration->s);

            return [
                'duration' => $durationString,
                'is_active' => true
            ];
        }

        /**
         * [DIPERBAIKI] Mengambil riwayat izin untuk seorang intern dengan paginasi.
         *
         * @param Intern $intern
         * @param string $type
         * @param int $perPage
         * @return LengthAwarePaginator
         */
        public function getPermitHistory(Intern $intern, string $type, int $perPage = 15): LengthAwarePaginator
        {
            return PermitLog::whereHas('attendance', function ($query) use ($intern) {
                    $query->where('intern_id', $intern->id);
                })
                ->where('type', $type)
                ->latest('start_time')
                ->paginate($perPage);
        }
    }
