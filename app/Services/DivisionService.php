<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Repositories\Interface\DivisionRepository;
use App\Repositories\Interface\InternRepository;
use App\Http\Requests\StoreDivisionRequest;
use App\Http\Requests\UpdateDivisionRequest;
use Illuminate\Http\Request;
use App\Models\Projects;
use App\Models\DetailProjects;
use Exception;
use function Sentry\captureException;

class DivisionService
{
    protected DivisionRepository $divisionRepository;
    protected InternRepository $internRepository;

    public function __construct(DivisionRepository $divisionRepository, InternRepository $internRepository)
    {
        $this->divisionRepository = $divisionRepository;
        $this->internRepository = $internRepository;
    }

    public function getAll(): ActionResult
    {
        try {
            $datas =  $this->divisionRepository->getAll();
            foreach ($datas as $data) {
                $data["count"] =  $this->internRepository->countByDivision($data->id);
            }
            return new ActionResult(true, "success retrieve data", $datas);
        } catch (\Throwable $e) {
            captureException($e);
            return new ActionResult(false, "something went wrong", null);
        }
    }

    public function getAllDivision(): ActionResult
    {
        try {
            $datas = $this->divisionRepository->getAll();
            return new ActionResult(true, "success retrieve data", $datas);
        } catch (\Throwable $e) {
            captureException($e);
            return new ActionResult(false, "failed retrieve data", null);
        }
    }

    public function getAllWithNoDivision(): ActionResult
    {
        try {
            $datas = $this->internRepository->getWithoutDivision();
            return new ActionResult(true, "success retrieve data", $datas);
        } catch (\Throwable $e) {
            captureException($e);
            return new ActionResult(false, "failed retrieve data", null);
        }
    }

    public function getAllTeamInDivision(int $id): ActionResult
    {
        try {
            if ($id == 0) {
                $data = $this->internRepository->getAll();
                return new ActionResult(true, "success retrieve data", $data);
            }
            $data = $this->internRepository->getByDivisionId($id);
            return new ActionResult(true, "success retrieve data", $data);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed retrieve data | $th", null);
        }
    }


    public function create(StoreDivisionRequest $storeDivisionRequest): ActionResult
    {
        try {
            $data = $storeDivisionRequest->validated();

            $data['name'] = $data['namaDivisi'];

            if ($storeDivisionRequest->hasFile('iconDivisi')) {
                $file = $storeDivisionRequest->file('iconDivisi');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('img'), $filename);
                $data['icon'] = $filename;
            }

            $result = $this->divisionRepository->create($data);

            return new ActionResult(true, "Division created successfully", $data);
        } catch (\Throwable $th) {
            captureException($th); // Error tracking
            return new ActionResult(false, "Failed to create division, something went wrong", null);
        }
    }

    public function update(UpdateDivisionRequest $updateDivisionRequest, int $id)
    {
        try {
            $data = $updateDivisionRequest->validated();

            $data['id'] = $id;
            $data['name'] = $data['namaDivisi'];
            unset($data['namaDivisi']);
            if ($updateDivisionRequest->hasFile('iconDivisi')) {
                $file = $updateDivisionRequest->file('iconDivisi');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('img'), $filename);
                $data['icon'] = $filename;
            }

            $result = $this->divisionRepository->update($data);

            return new ActionResult(true, "Division updated successfully", $data);
        } catch (\Throwable $th) {
            captureException($th); // Error tracking
            return new ActionResult(false, "Failed to create division, something went wrong", null);
        }
    }

    public function delete(int $id)
    {
        try {
            $result = $this->divisionRepository->delete($id);
            return new ActionResult(true, "success delete quotes", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed delete data, something weird", null);
        }
    }


    public function updateProject(Request $request): ActionResult
    {
        try {
            // Validasi input yang masuk
            $validated = $request->validate([
                'action' => 'required|string|in:Tambah,Hapus',
                'team' => 'required|array|min:1',
                'team.*' => 'integer|exists:interns,id',
                'projectId' => 'required|exists:projects,id',
            ]);

            // Ambil data yang sudah divalidasi
            $action = $validated['action'];
            $userIds = $validated['team'];
            $projectId = $validated['projectId'];

            // Cek apakah project ada
            $project = Projects::find($projectId);
            if (!$project) {
                return new ActionResult(false, "Project not found", null);
            }

            if ($action === 'Tambah') {
                // Tambah anggota ke dalam project
                foreach ($userIds as $userId) {
                    DetailProjects::updateOrCreate([
                        'project_id' => $projectId,
                        'intern_id' => $userId,
                    ]);
                }
                $message = 'Members successfully added to the project.';
            } elseif ($action === 'Hapus') {
                // Hapus anggota dari project
                DetailProjects::where('project_id', $projectId)
                    ->whereIn('intern_id', $userIds)
                    ->delete();
                $message = 'Members successfully removed from the project.';
            } else {
                return new ActionResult(false, "Invalid action selected", null);
            }

            // Jika semuanya berhasil
            return new ActionResult(true, $message, null);
        } catch (\Throwable $th) {
            // Lakukan error tracking dan kembalikan error response
            captureException($th); // Error tracking
            return new ActionResult(false, "An error occurred. Please try again.", null);
        }
    }
}
