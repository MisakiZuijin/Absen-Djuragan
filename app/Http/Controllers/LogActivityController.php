<?php

namespace App\Http\Controllers;

use App\Http\Requests\LogActivityRequest;
use App\Http\Requests\UpdateLogActivityRequest;
use App\Models\Attendance;
use App\Models\DetailSchedule;
use App\Models\LogActivity;
use App\Services\LogActivityService;
use App\Services\UserService;
use App\Services\QuotesService;
use App\Utils\DateNow;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogActivityController extends Controller
{
    protected LogActivityService $logActivityService;
    protected UserService $userService;
    protected QuotesService $quoteService;

    public function __construct(UserService $userService, LogActivityService $logActivityService, QuotesService $quoteService)
    {
        $this->logActivityService = $logActivityService;
        $this->userService = $userService;
        $this->quoteService = $quoteService;
    }

    /**
     * Tampilkan halaman terpisah khusus Logbook Harian Pemagang (konsep terpadu: Hari Ini & Riwayat)
     */
    public function logbookView(Request $request): View|RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->intern) {
            return redirect()->route('login.view');
        }

        $user->load(['profile', 'intern.division', 'intern.school']);

        $today = Carbon::today();

        // Cari detail schedule hari ini
        $todaysDetailSchedule = DetailSchedule::whereHas('schedule', function ($query) use ($user) {
            $query->where('intern_id', $user->intern->id);
        })->whereDate('date', $today)->first();

        // Riwayat logbook pemagang
        $logActivityHistory = LogActivity::whereHas('detailSchedule.schedule', function ($query) use ($user) {
            $query->where('intern_id', $user->intern->id);
        })->with('status')->latest('date')->get();

        // Cari logbook hari ini
        $todaysLogActivity = $todaysDetailSchedule?->logActivity ?? $logActivityHistory->first(function ($log) {
            return Carbon::parse($log->date)->isToday();
        });

        $hasFilledLogToday = !is_null($todaysLogActivity);

        // Quotes & Tanggal
        $birth_date = $user->profile->date_of_birth ?? null;
        $quotesResult = (now()->format('m-d') === ($birth_date ? Carbon::parse($birth_date)->format('m-d') : null))
            ? $this->quoteService->getByCategory('ultah')
            : $this->quoteService->getByCategory('quote');

        $quotes = $quotesResult->isSuccess() ? $quotesResult->getData()->pluck('quote') : [];

        $date_now = DateNow::getCurrentDate();
        $day_now = DateNow::getCurrentDay();

        return view('users.logbook', compact(
            'user',
            'todaysDetailSchedule',
            'todaysLogActivity',
            'hasFilledLogToday',
            'logActivityHistory',
            'quotes',
            'date_now',
            'day_now'
        ));
    }

    public function logActivityAction(LogActivityRequest $request)
    {
        $result = $this->logActivityService->create($request);

        if (!$result->isSuccess()) {
            return redirect()->back()->with('error', $result->getMessage());
        }

        return redirect()->back()->with('success', $result->getMessage());
    }

    public function updateLogActivityAction(UpdateLogActivityRequest $request)
    {
        $reqData = $request->validated();

        $result = $this->logActivityService->updateLogActivity($reqData);

        if (!$result->isSuccess()) {
            return redirect()->back()->with('error', $result->getMessage());
        }

        return redirect()->back()->with('success', 'Data Logbook Harian berhasil diperbarui');
    }

    public function historyActivityView(): View
    {
        $user = $this->userService->getUserLoggedData();
        $quotes = $this->quoteService->getByCategory('quote');
        $result = $this->logActivityService->getLogHistory();

        if ($quotes->isSuccess()) {
            $quotes = $quotes->getData()->pluck('quote');
        }

        $data = [
            'user' => $user,
            "date_now" => DateNow::getCurrentDate(),
            "day_now" => DateNow::getCurrentDay(),
            "quotes" => $quotes,
            "data" => $result->getData()
        ];
        return view("users.log-activity")->with($data);
    }


    public function updateStatus(Request $request, int $id)
    {
        $this->logActivityService->UpdateLGActivity($request, $id);

        return redirect()->back()->with('status', 'Status Log Activity berhasil diperbarui');
    }

    public function yesall(string $date)
    {
        LogActivity::where('date', Carbon::parse($date)->format('Y-m-d'))->update([
            'status_id' => 2
        ]);

        return redirect()->back()->with('status', 'Log Activity Hari Ini Sudah Disetujui Semua');
    }

    public function updateIsi(Request $request, int $id)
    {
        if ($id == "kosong") {
            $request->attd_id;
            $log = LogActivity::create([
                'activity' => $request->activity,
                'date' => $request->date,
                'status_id' => 2,
            ]);
            DetailSchedule::where('id', $request->attd_id)->update([
                'log_activity_id' => $log->id,
            ]);
            return redirect()->back()->with('status', 'Log Activity Sudah Diperbarui');
        } else {
            $logActivity = LogActivity::find($id);
            $logActivity->update([
                'activity' => $request->activity,
            ]);
        }
        return redirect()->back()->with('status', 'Log Activity Sudah Diperbarui');
    }
}
