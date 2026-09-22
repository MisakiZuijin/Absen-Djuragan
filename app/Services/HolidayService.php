<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Repositories\Interface\HolidayRepository;
use Illuminate\Http\Request;
use App\Models\Holiday;

use function Sentry\captureException;

class HolidayService
{

    protected HolidayRepository $holidayRepository;

    public function __construct(HolidayRepository $holidayRepository)
    {
        $this->holidayRepository = $holidayRepository;
    }

    public function getAll(): ActionResult
    {
        try {
            $result =  $this->holidayRepository->getAll();
            return new ActionResult(true, "success retrive office data", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "something weird", null);
        }
    }

    public function create(Request $Request): ActionResult
    {
        try {
            $validatedData = $Request->validate([
                'date' => 'required|date',
                'name' => 'required|string|max:255',
            ]);

            $holiday = Holiday::create([
                'date' => $validatedData['date'],
                'name' => $validatedData['name'],
            ]);

            return new ActionResult(true, "Successfully added holiday data", $holiday);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "Failed to create holiday, something went wrong", null);
        }
    }

    public function update(Request $Request, int $id): ActionResult
    {
        try {
            $validatedData = $Request->validate([
                'date' => 'required|date',
                'name' => 'required|string|max:255',
            ]);

            $holiday = Holiday::findOrFail($id);

            $holiday->update([
                'date' => $validatedData['date'],
                'name' => $validatedData['name'],
            ]);

            return new ActionResult(true, "Successfully updated holiday data", $holiday);
        } catch (\Throwable $th) {
            captureException($th); // Error tracking
            return new ActionResult(false, "Failed to update, something went wrong", null);
        }
    }

    public function delete(int $id): ActionResult
    {
        try {
            $holiday = Holiday::findOrFail($id);

            $holiday->delete();

            return new ActionResult(true, "Successfully deleted holiday", $holiday);
        } catch (\Throwable $th) {
            captureException($th); // Error tracking
            return new ActionResult(false, "Failed to delete holiday, something went wrong", null);
        }
    }
}
