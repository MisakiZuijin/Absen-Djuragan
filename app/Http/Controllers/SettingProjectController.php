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
use App\Models\Division;

class SettingProjectController extends Controller
{
    protected UserService $userService;
    protected InternService $internService;
    protected ProjectService $projectService;

    public function __construct(UserService $userService, InternService $internService, ProjectService $projectService)
    {
        $this->userService = $userService;
        $this->internService = $internService;
        $this->projectService = $projectService;
    }

    public function adminSettingProjectView(Request $request): View
    {
        $userData = $this->userService->getUserLoggedData();
        $projects = Projects::with('nameProject')->orderBy('id', 'DESC')->get();
        $nameProject = NameProjects::orderBy('name', 'ASC')->get();
        $interns = Intern::with(['user.profile', 'school', 'division'])->get()->keyBy('id');
        $divisions = Division::orderBy('name', 'ASC')->get();

        $projectIds = $projects->pluck('id');
        $allDetails = DetailProjects::whereIn('project_id', $projectIds)->get()->groupBy('project_id');

        $projectsWithDetails = $projects->map(function ($project) use ($interns, $allDetails) {
            $details = $allDetails->get($project->id, collect());

            $internIds = $details->pluck('intern_id')->unique();

            $project->members = $internIds->map(function ($internId) use ($interns) {
                return $interns->get($internId);
            })->filter();

            $firstMember = $project->members->first();
            $project->division_id = $firstMember?->division_id ?? null;

            return $project;
        });

        $data = [
            "nameProject" => $nameProject,
            "user" => $userData,
            "intern" => $interns->values(),
            "projects" => $projectsWithDetails,
            "divisions" => $divisions,
        ];

        return view('admin.pengaturan-project')->with($data);
    }

    /**
     * Endpoint AJAX untuk mengambil daftar pemagang & judul project master berdasarkan divisi terpilih.
     */
    public function getInternsAndTitlesByDivision(int $divisionId)
    {
        $interns = Intern::with(['user.profile', 'school', 'division'])
            ->where('division_id', $divisionId)
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->get()
            ->map(function ($intern) {
                return [
                    'id' => $intern->id,
                    'name' => $intern->user->profile->full_name ?? $intern->user->username ?? 'Peserta',
                    'school' => $intern->school->name ?? '-',
                    'division_name' => $intern->division->name ?? '-',
                ];
            });

        $division = Division::find($divisionId);
        $divName = strtolower($division->name ?? '');

        // Master judul project relevan dengan nama divisi atau project umum
        $nameProjects = NameProjects::query()
            ->where(function ($q) use ($divName) {
                if (!empty($divName)) {
                    $q->where('name', 'LIKE', "%{$divName}%");
                }
            })
            ->orWhere('name', 'NOT LIKE', 'Project %')
            ->orderBy('name')
            ->get(['id', 'name']);

        // Jika tidak ada yang cocok secara spesifik, kembalikan semua master project
        if ($nameProjects->isEmpty()) {
            $nameProjects = NameProjects::orderBy('name')->get(['id', 'name']);
        }

        return response()->json([
            'success' => true,
            'division' => $division,
            'interns' => $interns,
            'name_projects' => $nameProjects,
        ]);
    }

    public function storeProject(StoreProjectRequest $storeProjectRequest)
    {
        $this->projectService->create($storeProjectRequest);

        return redirect()->route('admin.pengaturan.project')->with('success', 'Data project berhasil ditambahkan!');
    }

    public function storeNameProject(Request $request)
    {
        $result = $this->projectService->createProject($request);

        if (!$result->isSuccess()) {
            return redirect()->route('admin.pengaturan.project')->with('error', $result->getMessage());
        }

        $projectName = $result->getData()?->name ?? 'Project Baru';
        \App\Helper\ActivityLogger::log('CREATE', 'Project Management', "Admin menambahkan master nama project: {$projectName}");

        return redirect()->route('admin.pengaturan.project')->with('success', "Nama project '{$projectName}' berhasil ditambahkan!");
    }

    public function update(UpdateProjectRequest $updateProjectRequest, int $id)
    {
        $this->projectService->update($updateProjectRequest, $id);

        return redirect()->route('admin.pengaturan.project')->with('success', 'Data project berhasil diperbarui!');
    }

    public function destroy(int $id)
    {
        $this->projectService->delete($id);

        return redirect()->back()->with('success', 'Data project berhasil dihapus!');
    }

    public function updateStatus(Request $request, int $id)
    {
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
