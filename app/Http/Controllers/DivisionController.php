<?php

namespace App\Http\Controllers;

use App\Helper\LogConsole;
use App\Models\NameProjects;
use App\Services\UserService;
use App\Services\DivisionService;
use App\Services\InternService;
use App\Services\SchoolService;
use App\Services\ShiftService;
use App\Models\Projects;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    protected $userService;
    protected $divisionService;
    protected $schoolService;
    protected $shiftService;
    protected $internService;
    protected $projectService;

    public function __construct(UserService $userService, DivisionService $divisionService, SchoolService $schoolService, ShiftService $shiftService, InternService $internService, ProjectService $projectService)
    {
        $this->divisionService = $divisionService;
        $this->userService = $userService;
        $this->schoolService = $schoolService;
        $this->shiftService = $shiftService;
        $this->internService = $internService;
        $this->projectService = $projectService;
    }

    public function divisionView()
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

    public function divisionTeamView($divisionId)
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

    public function divisionTeamEditView($userId)
    {
        $userData = $this->userService->getUserLoggedData();
        $teamData = $this->userService->getUserById($userId);
        $listSchool = $this->schoolService->getAllSchool();
        $division = $this->divisionService->getAll();
        $shifts = $this->shiftService->getAllShift();
        $project = NameProjects::all();

        $data = [
            "user" => $userData,
            "schoolList" => $listSchool->isSuccess() ? $listSchool->getData() : null,
            "divisions" => $division->getData(),
            "shifts" => $shifts->isSuccess() ? $shifts->getData() : null,
            "projects" => $project,
        ];

        $isProjectVisible = true;

        if ($teamData->isSuccess()) {
            $team = $teamData->getData();
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

    public function destroy($internId)
    {
        $this->internService->deleteInternDivision($internId);

        return redirect()->back()->with('success', 'Data berhasil diperbarui!');
    }

    public function bulkAction(Request $request)
    {
        $this->divisionService->updateProject($request);

        return redirect()->back()->with('success', 'Data berhasil diperbarui!');
    }
}
