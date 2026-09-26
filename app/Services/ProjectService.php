<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Helper\LogConsole;
use App\Repositories\Interface\ProjectRepository;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\DetailProjects;
use Illuminate\Http\Request;
use App\Models\Projects;
use App\Models\NameProjects;

use function Sentry\captureException;

class ProjectService
{

    protected ProjectRepository $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function create(StoreProjectRequest $StoreProjectRequest): ActionResult
    {
        try {
            $data = $StoreProjectRequest->validated();

            $data['name_project_id'] = $data['project_name'];
            $data['team'] = $data['team_name'];
            $data['description'] = $data['description'];

            $project = $this->projectRepository->create($data);

            if (!empty($data['members'])) {
                $memberRecords = array_map(function ($internId) use ($project) {
                    return [
                        'project_id' => $project->id,
                        'intern_id' => $internId,
                    ];
                }, $data['members']);
                DetailProjects::insert($memberRecords);
            }

            return new ActionResult(true, "Successfully added data into project", $project);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "Failed to update, something went wrong", null);
        }
    }

    public function createProject(Request $request): ActionResult
    {
        try {
            $prefix = $request->input('prefix', '');
            $rawName = $request->input('new_project_name', '');

            if (!empty($prefix) && !str_starts_with($rawName, $prefix)) {
                $projectName = trim($prefix . $rawName);
            } else {
                $projectName = trim($rawName);
            }

            if (empty($projectName)) {
                return new ActionResult(false, "Nama project tidak boleh kosong", null);
            }

            $existing = NameProjects::where('name', $projectName)->first();
            if ($existing) {
                return new ActionResult(true, "Project sudah ada", $existing);
            }

            $data['name'] = $projectName;
            $project = $this->projectRepository->createProject($data);

            return new ActionResult(true, "Successfully added project", $project);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "Failed to create project, something went wrong", null);
        }
    }


    public function update(UpdateProjectRequest $updateProjectRequest, int $id): ActionResult
    {
        try {
            $data = $updateProjectRequest->validated();

            if (!empty($data['project_name'])) {
                $data['name_project_id'] = $data['project_name'];
            }
            $data['team'] = $data['team_name'];
            $data['description'] = $data['description'];

            $project = Projects::find($id);

            if (!$project) {
                return new ActionResult(false, "Project not found", null);
            }

            $project->update($data);

            $existingMembers = DetailProjects::where('project_id', $project->id)->pluck('intern_id')->toArray();

            $newMembers = $data['members'] ?? [];
            $membersToAdd = array_diff($newMembers, $existingMembers);
            $membersToRemove = array_diff($existingMembers, $newMembers);

            if (!empty($membersToAdd)) {
                $memberRecords = array_map(function ($internId) use ($project) {
                    return [
                        'project_id' => $project->id,
                        'intern_id' => $internId,
                    ];
                }, $membersToAdd);
                DetailProjects::insert($memberRecords);
            }

            if (!empty($membersToRemove)) {
                DetailProjects::where('project_id', $project->id)
                    ->whereIn('intern_id', $membersToRemove)
                    ->delete();
            }

            return new ActionResult(true, "Successfully updated data in the project", $project);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "Failed to update, something went wrong", null);
        }
    }

    public function getAllProject(): ActionResult
    {
        try {
            $data = $this->projectRepository->getAll();
            return new ActionResult(true, "success retrive data project", $data);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed to retrive data project", null);
        }
    }

    public function delete(int $id)
    {
        try {
            $project = Projects::find($id);

            if (!$project) {
                return new ActionResult(false, "Project not found", null);
            }

            $nameProjectId = $project->name_project_id;

            $project->detailProjects()->delete();

            $project->delete();

            if (!empty($nameProjectId)) {
                $relatedDataCount = Projects::where('name_project_id', $nameProjectId)->count();

                if ($relatedDataCount === 0) {
                    NameProjects::where('id', $nameProjectId)->delete();
                }
            }

            return new ActionResult(true, "Project and related data deleted successfully", null);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "Failed to delete data, something went wrong", null);
        }
    }
}
