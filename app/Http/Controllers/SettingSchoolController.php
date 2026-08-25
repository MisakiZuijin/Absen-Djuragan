<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Services\SchoolService;
use App\Http\Requests\StoreSchoolRequest;
use App\Http\Requests\UpdateSchoolRequest;
use App\Models\EducationalLevel;

class SettingSchoolController extends Controller
{
    protected $userService;
    protected $schoolService;

    public function __construct(UserService $userService, SchoolService $schoolService)
    {
        $this->userService = $userService;
        $this->schoolService = $schoolService;
    }

    public function adminSettingSekolahView(): View
    {
        $userData = $this->userService->getUserLoggedData();
        $schoolList = $this->schoolService->getAllSchool();
        $educationalLevels = EducationalLevel::all();

        $data = [
            "educationalLevels" => $educationalLevels,
            "user" => $userData,
            "schoolList" => $schoolList->isSuccess() ? $schoolList->getData() : null
        ];

        return view('admin.pengaturan-sekolah')->with($data);
    }

    public function storeSchool(StoreSchoolRequest $storeSchoolRequest)
    {
        $this->schoolService->create($storeSchoolRequest);

        return redirect()->back()->with('success', 'Data Sekolah berhasil ditambahkan!');
    }

    public function updateSchool(UpdateSchoolRequest $updateSchoolRequest, $id)
    {
        $this->schoolService->update($updateSchoolRequest, $id);

        return redirect()->back()->with('success', 'Data Sekolah berhasil diperbarui!');
    }

    public function deleteSchool($id)
    {
        $this->schoolService->delete($id);

        return redirect()->back()->with('success', 'Data Sekolah berhasil dihapus!');
    }
}
