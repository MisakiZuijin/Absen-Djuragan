<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\NameProjects;
use App\Services\DivisionService;
use App\Services\InternService;
use App\Services\ProjectService;
use App\Services\SchoolService;
use App\Services\ShiftService;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    protected UserService $userService;
    protected DivisionService $divisionService;
    protected SchoolService $schoolService;
    protected ShiftService $shiftService;
    protected InternService $internService;
    protected ProjectService $projectService;

    public function __construct(
        UserService $userService,
        DivisionService $divisionService,
        SchoolService $schoolService,
        ShiftService $shiftService,
        InternService $internService,
        ProjectService $projectService
    ) {
        $this->divisionService = $divisionService;
        $this->userService = $userService;
        $this->schoolService = $schoolService;
        $this->shiftService = $shiftService;
        $this->internService = $internService;
        $this->projectService = $projectService;
    }

    public function divisionView(): View
    {
        $userData = $this->userService->getUserLoggedData();
        $division = $this->divisionService->getAll();
        $internWihtoutDivision = $this->divisionService->getAllWithNoDivision();

        $data = [
            "user" => $userData,
            "division" => $division->getData(),
            "internWithoutDivision" => $internWihtoutDivision->isSuccess() ? $internWihtoutDivision->getData() : null
        ];

        return view('admin.divisi')->with($data);
    }

    public function divisionTeamView(int $divisionId): View
    {
        $userData = $this->userService->getUserLoggedData();
        $internData = $this->divisionService->getAllTeamInDivision($divisionId);
        $projects = NameProjects::all();

        $data = [
            "user" => $userData,
            "projects" => $projects
        ];

        if ($internData->isSuccess()) {
            $data["teams"] = $internData->getData();
        }

        return view('admin.tim')->with($data);
    }

    public function divisionTeamEditView(int $userId): View
    {
        $userData = $this->userService->getUserLoggedData();
        $teamData = $this->userService->getUserById($userId);
        $listSchool = $this->schoolService->getAllSchool();
        $division = $this->divisionService->getAll();
        $shifts = $this->shiftService->getAllShift();
        $project = NameProjects::all();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        $data = [
            "user" => $userData,
            "schoolList" => $listSchool->isSuccess() ? $listSchool->getData() : null,
            "divisions" => $division->getData(),
            "shifts" => $shifts->isSuccess() ? $shifts->getData() : null,
            "projects" => $project,
            "brands" => $brands,
        ];

        $isProjectVisible = true;

        if ($teamData->isSuccess()) {
            $team = $teamData->getData();
            if ($team->intern) {
                $team->intern->loadMissing(['account', 'division', 'brand']);
            }
            $teamProject = $team->intern->detailProject;
            foreach ($teamProject as $project) {
                if ($project->is_done == false) {
                    $isProjectVisible = false;
                }
            }
            $data["team"] = $team;
        }

        $data["projectVisibility"] = $isProjectVisible;

        return view('admin.sunting-anggota', $data);
    }

    public function destroy(int $internId): RedirectResponse
    {
        // Hanya Super Admin (Role 7) yang memiliki hak untuk menghapus data pemagang
        if ((int) auth()->user()->role_id !== 7) {
            abort(403, 'Aksi Ditolak: Hanya Super Admin yang memiliki hak akses untuk menghapus akun pemagang.');
        }

        $intern = \App\Models\Intern::with('user.profile')->find($internId);
        $internName = $intern?->user?->profile?->full_name ?? $intern?->user?->username ?? "ID #{$internId}";

        $this->internService->deleteInternDivision($internId);

        \App\Helper\ActivityLogger::log('DELETE', 'User Management', "Super Admin menghapus data pemagang: {$internName}");

        return redirect()->back()->with('success', "Data pemagang {$internName} berhasil dihapus!");
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $this->divisionService->updateProject($request);

        return redirect()->back()->with('success', 'Data berhasil diperbarui!');
    }
}
