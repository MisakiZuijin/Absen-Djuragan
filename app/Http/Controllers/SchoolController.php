<?php

namespace App\Http\Controllers;

use App\Services\InternService;
use App\Services\OfficeService;
use App\Services\SchoolService;
use App\Services\ShiftService;
use App\Services\UserService;
use Illuminate\Contracts\View\View;

class SchoolController extends Controller {
    protected $userService;
    protected $schoolService;
    protected $internService;
    protected $shiftService;
    protected $officeService;

    public function __construct(UserService $userService, SchoolService $schoolService, ShiftService $shiftService, OfficeService $officeService) {
        $this->userService = $userService;
        $this->schoolService = $schoolService;
        $this->shiftService = $shiftService;
        $this->officeService = $officeService;
    }

    public function adminSchoolView(): View {
        $userData = $this->userService->getUserLoggedData();
        $schoolList = $this->schoolService->getCountSchool();



        $data = [
            "user" => $userData,
            "schoolList" =>  $schoolList->isSuccess() ? $schoolList->getData() : null,
        ];

        return view('admin.sekolah')->with($data);
    }

    public function adminSchoolTeamView($schoolId): View {
        $userData = $this->userService->getUserLoggedData();
        $teamData = $this->schoolService->getTeamBySchoolId($schoolId);
        $shift = $this->shiftService->getAllShift();
        $office  = $this->officeService->getAll();
        $data = [
            "user" => $userData,
            "teamData" => $teamData->isSuccess() ? $teamData->getData() : null,
            "shifts" => $shift->isSuccess() ? $shift->getData() : null,
            "offices" => $office->isSuccess() ? $office->getData() : null
        ];

        return view('admin.anggota-sekolah')->with($data);
    }
}
