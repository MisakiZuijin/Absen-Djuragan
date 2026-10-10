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
            if ($differenceInMinutes < 0) {
                $differenceInMinutes += 1440;
            }

            $startBreak = $storeShiftRequest->input('start_break_time');
            $endBreak = $storeShiftRequest->input('end_break_time');

            $breakTime = ($startBreak && $endBreak) ? max(0, DateNow::getDifferentInMinute($startBreak, $endBreak)) : 0;
            if ($breakTime < 0) {
                $breakTime += 1440;
            }

            $totalMinutes = max(0, $differenceInMinutes - $breakTime);

            $data = [
                'name' => $storeShiftRequest->input('addNamaShift') ?? $storeShiftRequest->input('name'),
                'description' => $storeShiftRequest->input('description'),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'break_time_in_minute' => $breakTime,
                'total_time_in_minute' => $totalMinutes,
                'start_break_time' => $startBreak,
                'end_break_time' => $endBreak,
            ];

            // Pengaturan Istirahat Khusus Hari Jumat (Pemagang Laki-Laki)
            $isFridayBreakActive = $storeShiftRequest->boolean('is_friday_break_active') || $storeShiftRequest->input('is_friday_break_active') == '1';
            $fridayStartBreak = $storeShiftRequest->input('friday_start_break_time');
            $fridayEndBreak = $storeShiftRequest->input('friday_end_break_time');
            $fridayBreakMinutes = ($isFridayBreakActive && $fridayStartBreak && $fridayEndBreak)
                ? max(0, DateNow::getDifferentInMinute($fridayStartBreak, $fridayEndBreak))
                : 0;

            $data['is_friday_break_active'] = $isFridayBreakActive;
            $data['friday_start_break_time'] = $isFridayBreakActive ? $fridayStartBreak : null;
            $data['friday_end_break_time'] = $isFridayBreakActive ? $fridayEndBreak : null;
            $data['friday_break_time_in_minute'] = $fridayBreakMinutes;

            // Pengaturan Split-Shift (WFH di awal sesi + WFO di kantor)
            $isSplitShift = $storeShiftRequest->boolean('is_split_shift') || $storeShiftRequest->input('is_split_shift') == '1';
            $data['is_split_shift'] = $isSplitShift;
            $data['split_wfh_start'] = $isSplitShift ? ($storeShiftRequest->input('split_wfh_start') ?: '13:00:00') : null;
            $data['split_wfh_end'] = $isSplitShift ? ($storeShiftRequest->input('split_wfh_end') ?: '16:00:00') : null;
            $data['split_wfo_start'] = $isSplitShift ? ($storeShiftRequest->input('split_wfo_start') ?: '16:00:00') : null;
            $data['split_wfo_end'] = $isSplitShift ? ($storeShiftRequest->input('split_wfo_end') ?: ($endTime ?: '21:00:00')) : null;

            // Pengaturan Rolling / Rotasi Mingguan
            $isRolling = $storeShiftRequest->boolean('is_rolling') || $storeShiftRequest->input('is_rolling') == '1';
            $data['is_rolling'] = $isRolling;

            if ($storeShiftRequest->filled('adt_start_break_time')) {
                $data['adt_start_break_time'] = $storeShiftRequest->input('adt_start_break_time');
            }

            if ($storeShiftRequest->filled('adt_end_break_time')) {
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
            if ($differenceInMinutes < 0) {
                $differenceInMinutes += 1440;
            }

            $startBreak = $updateShiftRequest->input('edit_start_break_time') ?? $updateShiftRequest->input('start_break_time');
            $endBreak = $updateShiftRequest->input('edit_end_break_time') ?? $updateShiftRequest->input('end_break_time');

            $breakTime = ($startBreak && $endBreak) ? max(0, DateNow::getDifferentInMinute($startBreak, $endBreak)) : 0;
            if ($breakTime < 0) {
                $breakTime += 1440;
            }

            $totalMinutes = max(0, $differenceInMinutes - $breakTime);

            $data = [
                'id' => $id,
                'name' => $updateShiftRequest->input('nama_Shift') ?? $updateShiftRequest->input('name'),
                'description' => $updateShiftRequest->input('description'),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'break_time_in_minute' => $breakTime,
                'total_time_in_minute' => $totalMinutes,
                'start_break_time' => $startBreak,
                'end_break_time' => $endBreak,
            ];

            if ($updateShiftRequest->filled('edit_adt_start_break_time')) {
                $data['adt_start_break_time'] = $updateShiftRequest->input('edit_adt_start_break_time');
            }
            if ($updateShiftRequest->filled('edit_adt_end_break_time')) {
                $data['adt_end_break_time'] = $updateShiftRequest->input('edit_adt_end_break_time');
            }

            // Pengaturan Istirahat Khusus Hari Jumat (Pemagang Laki-Laki)
            $isFridayBreakActive = $updateShiftRequest->boolean('edit_is_friday_break_active')
                || $updateShiftRequest->boolean('is_friday_break_active')
                || $updateShiftRequest->input('edit_is_friday_break_active') == '1'
                || $updateShiftRequest->input('is_friday_break_active') == '1';

            $fridayStartBreak = $updateShiftRequest->input('edit_friday_start_break_time') ?? $updateShiftRequest->input('friday_start_break_time');
            $fridayEndBreak = $updateShiftRequest->input('edit_friday_end_break_time') ?? $updateShiftRequest->input('friday_end_break_time');
            $fridayBreakMinutes = ($isFridayBreakActive && $fridayStartBreak && $fridayEndBreak)
                ? max(0, DateNow::getDifferentInMinute($fridayStartBreak, $fridayEndBreak))
                : 0;

            $data['is_friday_break_active'] = $isFridayBreakActive;
            $data['friday_start_break_time'] = $isFridayBreakActive ? $fridayStartBreak : null;
            $data['friday_end_break_time'] = $isFridayBreakActive ? $fridayEndBreak : null;
            $data['friday_break_time_in_minute'] = $fridayBreakMinutes;

            // Pengaturan Split-Shift (WFH di awal sesi + WFO di kantor)
            $isSplitShift = $updateShiftRequest->boolean('edit_is_split_shift')
                || $updateShiftRequest->boolean('is_split_shift')
                || $updateShiftRequest->input('edit_is_split_shift') == '1'
                || $updateShiftRequest->input('is_split_shift') == '1';

            $splitWfhStart = $updateShiftRequest->input('edit_split_wfh_start') ?? $updateShiftRequest->input('split_wfh_start');
            $splitWfhEnd = $updateShiftRequest->input('edit_split_wfh_end') ?? $updateShiftRequest->input('split_wfh_end');
            $splitWfoStart = $updateShiftRequest->input('edit_split_wfo_start') ?? $updateShiftRequest->input('split_wfo_start');
            $splitWfoEnd = $updateShiftRequest->input('edit_split_wfo_end') ?? $updateShiftRequest->input('split_wfo_end');

            $data['is_split_shift'] = $isSplitShift;
            $data['split_wfh_start'] = $isSplitShift ? ($splitWfhStart ?: '13:00:00') : null;
            $data['split_wfh_end'] = $isSplitShift ? ($splitWfhEnd ?: '16:00:00') : null;
            $data['split_wfo_start'] = $isSplitShift ? ($splitWfoStart ?: '16:00:00') : null;
            $data['split_wfo_end'] = $isSplitShift ? ($splitWfoEnd ?: ($endTime ?: '21:00:00')) : null;

            // Pengaturan Rolling / Rotasi Mingguan
            $isRolling = $updateShiftRequest->boolean('edit_is_rolling')
                || $updateShiftRequest->boolean('is_rolling')
                || $updateShiftRequest->input('edit_is_rolling') == '1'
                || $updateShiftRequest->input('is_rolling') == '1';
            $data['is_rolling'] = $isRolling;

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
