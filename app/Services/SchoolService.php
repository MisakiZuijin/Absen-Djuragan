<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Http\Requests\StoreSchoolRequest;
use App\Http\Requests\UpdateSchoolRequest;
use App\Repositories\Interface\InternRepository;
use App\Repositories\Interface\SchoolRepository;
use App\Helper\LogConsole;

use function Sentry\captureException;

class SchoolService
{
    protected $schoolRepository;
    protected $internRepository;

    public function __construct(SchoolRepository $schoolRepositor, InternRepository $internRepository)
    {
        $this->schoolRepository = $schoolRepositor;
        $this->internRepository = $internRepository;
    }

    public function getTeamBySchoolId($schoolId): ActionResult
    {
        try {

            $result  = $this->internRepository->getBySchoolId($schoolId);
            return new ActionResult(true, "success retrive team by school id", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed retrive team", null);
        }
    }

    public function getAllSchool()
    {

        try {
            $result = $this->schoolRepository->getAllSchool();
            return new ActionResult(true, "success retrive all school data", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed retrive school data", null);
        }
    }


    public function getCountSchool(): ActionResult
    {

        try {
            $data = $this->schoolRepository->getAllSchool();

            if (!$data) {
                return new ActionResult(false, "school is empty", null);
            }

            for ($i = 0; $i < sizeof($data); $i++) {
                $data[$i]["intern_total"] = $this->internRepository->countBySchool($data[$i]->id) ?? 0;
            }

            return new ActionResult(true, "success count data school", $data);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed count school data", null);
        }
    }

    public function create(StoreSchoolRequest $storeSchoolRequest): ActionResult
    {
        try {
            $data = $storeSchoolRequest->validated();

            $data['name'] = $data['namaSekolah'];
            $data['educational_level_id'] = $data['schoolType1'];
            $data['address'] = $data['alamatSekolah'];

            $result = $this->schoolRepository->store($data);

            return new ActionResult(true, "Successfully added data into quotes", $result);
        } catch (\Throwable $th) {
            captureException($th); // Error tracking
            return new ActionResult(false, "Failed to update, something went wrong", null);
        }
    }

    public function update(UpdateSchoolRequest $updateSchoolRequest, $id): ActionResult
    {
        try {
            $data = $updateSchoolRequest->validated();

            $data['name'] = $data['namaSekolah'];
            $data['address'] = $data['alamatSekolah'];
            $data['educational_level_id'] = $data['schoolType'];

            $result = $this->schoolRepository->update($id, $data);

            return new ActionResult(true, "Successfully added data into office", $result);
        } catch (\Throwable $th) {
            captureException($th); // Error tracking
            return new ActionResult(false, "Failed to update, something went wrong", null);
        }
    }

    public function delete($id)
    {
        try {
            $result = $this->schoolRepository->delete($id);
            return new ActionResult(true, "success delete school", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed delete data, something weird", null);
        }
    }
}
