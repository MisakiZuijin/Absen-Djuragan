<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\InternScheduleRequest;
use App\Services\OfficeService;
use App\Services\ScheduleService;
use App\Services\ShiftService;
use App\Services\HolidayService;
use Illuminate\Http\Request;
use App\Models\Intern;

class ScheduleController extends Controller
{
    protected ScheduleService $scheduleService;
    protected ShiftService $shiftService;
    protected OfficeService $officeService;
    protected HolidayService $holidayService;


    public function __construct(ScheduleService $scheduleService, ShiftService $shiftService, OfficeService $officeService, HolidayService $holidayService)
    {
        $this->scheduleService = $scheduleService;
        $this->shiftService = $shiftService;
        $this->officeService = $officeService;
        $this->holidayService = $holidayService;
    }

    public function createSchedule(InternScheduleRequest $request)
    {
        $data = $this->scheduleService->createSchedule($request);

        if ($data->isSuccess()) return back()->with('success', 'Data Anggota berhasil diperbarui!');

        return back()->with('error', $data->getMessage());
    }



    public function updateSchedule(Request $request, int $id)
    {
        $validatedData = $request->validate([
            'shift_id' => 'required|integer',
        ]);


        $result = $this->scheduleService->updateShift($id, $validatedData['shift_id']);

        if ($result->isSuccess()) {
            return ResponseHelper::jsonResponse(true, 'Shift berhasil di perbarui', null);
        }
        return ResponseHelper::jsonResponse(false, 'shift gagal di perbarui', null, 400);
    }


    public function scheduleUpdateView(Request $request, int $internId)
    {
        $workType = ["wfo", "wfh"];
        $shiftData  = $this->shiftService->getAllShift();
        $officeData = $this->officeService->getAll();
        $holidayData = $this->holidayService->getAll();
        $schedulData = $this->scheduleService->getScheduleByInternId($internId);

        $schedule = $schedulData->isSuccess() ? ($schedulData->getData()['schedule'] ?? null) : null;
        $fullName = $schedule?->intern?->user?->profile?->full_name;
        if (!$fullName) {
            $intern = Intern::with('user.profile')->find($internId);
            $fullName = $intern?->user?->profile?->full_name ?? 'Nama tidak tersedia';
        }

        $data = [
            "name" => $fullName,
            "holiday_data" => $holidayData->isSuccess() ? $holidayData->getData() : [],
            "intern_id" => $internId,
            "workTypes" => $workType,
            "shifts" => $shiftData->isSuccess() ? $shiftData->getData() : null,
            "offices" => $officeData->isSuccess() ? $officeData->getData() : null,
            "schedule_data" => $schedulData->isSuccess() ? $schedulData->getData() : null
        ];
        return view('admin.shift_schedule.edit_shift_schedule', $data);
    }


    public function scheduleUpdateSingle(Request $request, int $internId)
    {

        $requestData = $request->validate([
            'date' => 'required|date_format:d',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900|max:2100',
            'shift_id' => 'required|integer',
            'work_type' => 'required|string|in:wfo,wfh',
            'office_id' => 'required|integer',
            'schedule_type' => 'required|integer',
            'back_earlier' => 'required|integer'
        ]);
        $date = $requestData["year"] . "-" . $requestData["month"] . "-" . $requestData["date"];

        $this->scheduleService->updateScheduleSingleData($internId, $date, $requestData);

        return response()->json(['message' => 'Data Schedule berhasil di perbarui!'], 200);
    }


    public function scheduleUpdateMulti(Request $request, int $internId)
    {


        $requestData = $request->validate([
            'date' => 'required|string',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900|max:2100',
            'shift_id' => 'required|integer',
            'work_type' => 'required|string|in:wfo,wfh',
            'office_id' => 'required|integer',
            'schedule_type' => 'required|integer'
        ]);

        $this->scheduleService->updateScheduleMultiData($internId, $requestData);

        return response()->json(['message' => 'Data Schedule berhasil di perbarui!'], 200);
    }
}
