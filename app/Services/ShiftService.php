<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Models\Shift;
use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Utils\DateNow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function Sentry\captureException;

use App\Repositories\Interface\ShiftRepository;

class ShiftService
{
    protected ShiftRepository $shiftRepository;

    public function __construct(ShiftRepository $shiftRepository, Shift $model)
    {
        $this->shiftRepository = $shiftRepository;
    }

    public function createShift(StoreShiftRequest $storeShiftRequest)
    {
        try {
            $startTime = $storeShiftRequest->input('addJamMulai');
            $endTime = $storeShiftRequest->input('addJamBerakhir');

            $differenceInMinutes = DateNow::getDifferentInMinute($startTime, $endTime);

            $startBreak = $storeShiftRequest->input('start_break_time');
            $endBreak = $storeShiftRequest->input('end_break_time');

            $breakTime = DateNow::getDifferentInMinute($startBreak, $endBreak);

            $totalMinutes = $differenceInMinutes - $breakTime;

            $data = [
                'name' => $storeShiftRequest->input('addNamaShift'),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'break_time_in_minute' => $breakTime,
                'total_time_in_minute' => $totalMinutes,
                'start_break_time' => $storeShiftRequest->input('start_break_time'),
                'end_break_time' => $storeShiftRequest->input('end_break_time'),
            ];

            // Pengaturan Istirahat Khusus Hari Jumat (Pemagang Laki-Laki)
            $isFridayBreakActive = $storeShiftRequest->boolean('is_friday_break_active') || $storeShiftRequest->input('is_friday_break_active') == '1';
            $fridayStartBreak = $storeShiftRequest->input('friday_start_break_time');
            $fridayEndBreak = $storeShiftRequest->input('friday_end_break_time');
            $fridayBreakMinutes = ($isFridayBreakActive && $fridayStartBreak && $fridayEndBreak)
                ? DateNow::getDifferentInMinute($fridayStartBreak, $fridayEndBreak)
                : 0;

            $data['is_friday_break_active'] = $isFridayBreakActive;
            $data['friday_start_break_time'] = $isFridayBreakActive ? $fridayStartBreak : null;
            $data['friday_end_break_time'] = $isFridayBreakActive ? $fridayEndBreak : null;
            $data['friday_break_time_in_minute'] = $fridayBreakMinutes;

            if ($storeShiftRequest->has('adt_start_break_time')) {
                $data['adt_start_break_time'] = $storeShiftRequest->input('adt_start_break_time');
            }

            if ($storeShiftRequest->has('adt_end_break_time')) {
                $data['adt_end_break_time'] = $storeShiftRequest->input('adt_end_break_time');
            }

            if ($storeShiftRequest->has('is_gps_active')) {
                $data['is_gps_active'] = (int) $storeShiftRequest->input('is_gps_active');
            }

            return $this->shiftRepository->create($data);
        } catch (\Exception $e) {
            Log::error('createShift error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            captureException($e);
            throw $e;
        }
    }

    public function updateShift(UpdateShiftRequest $updateShiftRequest, int $id)
    {
        try {
            $startTime = $updateShiftRequest->input('jamMulai') ?? $updateShiftRequest->input('addJamMulai');
            $endTime = $updateShiftRequest->input('jamBerakhir') ?? $updateShiftRequest->input('addJamBerakhir');

            $differenceInMinutes = DateNow::getDifferentInMinute($startTime, $endTime);

            $startBreak = $updateShiftRequest->input('edit_start_break_time');
            $endBreak = $updateShiftRequest->input('edit_end_break_time');

            $breakTime = DateNow::getDifferentInMinute($startBreak, $endBreak);

            $totalMinutes = max(0, $differenceInMinutes - $breakTime);

            $data = [
                'id' => $id,
                'name' => $updateShiftRequest->input('nama_Shift'),
                'start_time' => $updateShiftRequest->input('jamMulai'),
                'end_time' => $updateShiftRequest->input('jamBerakhir'),
                'break_time_in_minute' => $breakTime,
                'total_time_in_minute' => $totalMinutes,
                'start_break_time' => $updateShiftRequest->input('edit_start_break_time'),
                'end_break_time' => $updateShiftRequest->input('edit_end_break_time'),
                'adt_start_break_time' => $updateShiftRequest->input('edit_adt_start_break_time'),
                'adt_end_break_time' => $updateShiftRequest->input('edit_adt_end_break_time'),
            ];

            // Pengaturan Istirahat Khusus Hari Jumat (Pemagang Laki-Laki)
            $isFridayBreakActive = $updateShiftRequest->boolean('edit_is_friday_break_active')
                || $updateShiftRequest->boolean('is_friday_break_active')
                || $updateShiftRequest->input('edit_is_friday_break_active') == '1'
                || $updateShiftRequest->input('is_friday_break_active') == '1';

            $fridayStartBreak = $updateShiftRequest->input('edit_friday_start_break_time') ?? $updateShiftRequest->input('friday_start_break_time');
            $fridayEndBreak = $updateShiftRequest->input('edit_friday_end_break_time') ?? $updateShiftRequest->input('friday_end_break_time');
            $fridayBreakMinutes = ($isFridayBreakActive && $fridayStartBreak && $fridayEndBreak)
                ? DateNow::getDifferentInMinute($fridayStartBreak, $fridayEndBreak)
                : 0;

            $data['is_friday_break_active'] = $isFridayBreakActive;
            $data['friday_start_break_time'] = $isFridayBreakActive ? $fridayStartBreak : null;
            $data['friday_end_break_time'] = $isFridayBreakActive ? $fridayEndBreak : null;
            $data['friday_break_time_in_minute'] = $fridayBreakMinutes;

            if ($updateShiftRequest->has('is_gps_active')) {
                $data['is_gps_active'] = (int) $updateShiftRequest->input('is_gps_active');
            }

            return $this->shiftRepository->update($data, $id);
        } catch (\Exception $th) {
            Log::error('updateShift error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            captureException($th);
            throw $th;
        }
    }


    public function getAllShift(): ActionResult
    {
        try {
            $result = $this->shiftRepository->getAll();
            return new ActionResult(true, "success retrive data shift", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed retrive data shift", null);
        }
    }
    public function getAllWhereId()
    {
        return $this->shiftRepository->getAll();
    }

    public function deleteShift(int $id)
    {
        return $this->shiftRepository->delete($id);
    }
}
