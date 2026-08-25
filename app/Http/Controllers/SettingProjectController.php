<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\InternService;
use App\Repositories\Interface\ProjectRepository;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Services\ProjectService;
use App\Models\Projects;
use App\Models\Intern;
use App\Models\NameProjects;
use App\Models\DetailProjects;

class SettingProjectController extends Controller {
    protected $userService;
    protected $internService;
    protected $projectService;

    public function __construct(UserService $userService, InternService $internService, ProjectService $projectService) {
        $this->userService = $userService;
        $this->internService = $internService;
        $this->projectService = $projectService;
    }

    public function adminSettingProjectView(Request $request): View {
        $userData = $this->userService->getUserLoggedData();
        $intern = $this->internService->getAll();
    
        $projects = Projects::orderBy('name_project_id', 'DESC')->get();
    
        $nameProject = NameProjects::all();
    
        $interns = Intern::all()->keyBy('id');
    
        $projectsWithDetails = $projects->map(function ($project) use ($interns) {
            $details = DetailProjects::where('project_id', $project->id)->get();
    
            $internIds = $details->map(function ($detail) {
                return $detail->intern_id;
            })->unique();
    
            $project->members = $internIds->map(function ($internId) use ($interns) {
                return $interns->get($internId); 
            });
    
            return $project;
        });
    
        $data = [
            "nameProject" => $nameProject,
            "user" => $userData,
            "intern" => $intern->isSuccess() ? $intern->getData() : null,
            "projects" => $projectsWithDetails
        ];
    
        return view('admin.pengaturan-project')->with($data);
    }
    
    public function storeProject(StoreProjectRequest $storeProjectRequest) {
        $this->projectService->create($storeProjectRequest);

        return redirect()->route('admin.pengaturan.project')->with('success', 'Data project berhasil ditambahkan!');
    }

    public function storeNameProject(Request $request) {
        $this->projectService->createProject($request);

        return redirect()->route('admin.pengaturan.project')->with('success', 'Data nama project berhasil ditambahkan!');
    }

    public function update(UpdateProjectRequest $updateProjectRequest, $id) {
        $this->projectService->update($updateProjectRequest, $id);

        return redirect()->route('admin.pengaturan.project')->with('success', 'Data project berhasil diperbarui!');
    }

    public function destroy($id) {
        $this->projectService->delete($id);

        return redirect()->back()->with('success', 'Data project berhasil dihapus!');
    }

    public function updateStatus(Request $request, $id) {
        try {
            $project = Projects::findOrFail($id);
            $project->status = $request->status;
            $project->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
