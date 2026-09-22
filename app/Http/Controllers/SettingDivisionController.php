<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreDivisionRequest;
use App\Http\Requests\UpdateDivisionRequest;
use App\Services\DivisionService;
use App\Models\Division;
use App\Services\OfficeService;
use App\Services\QuotesService;
use App\Services\SchoolService;

class SettingDivisionController extends Controller
{
    protected UserService $userService;
    protected QuotesService $quoteService;
    protected DivisionService $divisionService;
    protected OfficeService $officeService;
    protected SchoolService $schoolService;
    protected ShiftController $shiftService;
    protected InternController $internService;

    public function __construct(UserService $userService, DivisionService $divisionService)
    {
        $this->userService = $userService;
        $this->divisionService = $divisionService;
    }

    public function adminSettingDivisiView(): View
    {
        $userData = $this->userService->getUserLoggedData();

        $division = $this->divisionService->getAllDivision();

        $data = [
            "user" => $userData,
            "division" => $division->isSuccess() ? $division->getData() : null
        ];

        return view('admin.pengaturan-divisi')->with($data);
    }

    public function storeDivision(StoreDivisionRequest $storeDivisionRequest)
    {
        $this->divisionService->create($storeDivisionRequest);

        return redirect()->route('admin.pengaturan.divisi')->with('success', 'Data Divisi berhasil ditambahkan!');
    }

    public function updateDivision(UpdateDivisionRequest $updateDivisionRequest, int $id)
    {
        $this->divisionService->update($updateDivisionRequest, $id);

        return redirect()->route('admin.pengaturan.divisi')->with('success', 'Data Divisi berhasil diperbarui!');
    }

    public function deleteDivision(int $id)
    {
        $this->divisionService->delete($id);

        return redirect()->route('admin.pengaturan.divisi')->with('success', 'Data Divisi berhasil dihapus!');
    }
}
