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
use FFI;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LogActivityController extends Controller {
    protected $logActivityService;
    protected $userService;
    protected $quoteService;

    public function __construct(UserService $userService, LogActivityService $logActivityService, QuotesService $quoteService) {
        $this->logActivityService = $logActivityService;
        $this->userService = $userService;
        $this->quoteService = $quoteService;
    }

    public function logActivityAction(LogActivityRequest $request) {


        $result = $this->logActivityService->create($request);

        $data = [];
        if (!$result->isSuccess()) {
            $data["error"] = $result->getMessage();
        }


        return redirect()->route("user.home")->with('success', $result->getMessage());
    }

    public function updateLogActivityAction(UpdateLogActivityRequest $request) {
        $reqData = $request->validated();

        $result = $this->logActivityService->updateLogActivity($reqData);

        $data = [];
        if (!$result->isSuccess()) {
            $data["error"] = $result->getMessage();
        }

        return redirect()->route("home.historyActivity")->with('success', 'Data History Activity berhasil diperbarui');
    }

    public function historyActivityView(): View {
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


    public function updateStatus(Request $request, $id) {
        $this->logActivityService->UpdateLGActivity($request, $id);

        return redirect()->back()->with('status', 'Status Log Activity berhasil diperbarui');
    }

    public function yesall($date) {
        LogActivity::where('date', Carbon::parse($date)->format('Y-m-d'))->update([
            'status_id' => 2
        ]);

        return redirect()->back()->with('status', 'Log Activity Hari Ini Sudah Disetujui Semua');
    }

    public function updateIsi(Request $request ,$id) {
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
