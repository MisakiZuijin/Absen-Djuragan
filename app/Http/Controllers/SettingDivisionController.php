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
    protected DivisionService $divisionService;

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

        \App\Helper\ActivityLogger::log('CREATE', 'Master Data', "Admin menambahkan Divisi baru: {$storeDivisionRequest->input('name')}");

        return redirect()->route('admin.pengaturan.divisi')->with('success', 'Data Divisi berhasil ditambahkan!');
    }

    public function updateDivision(UpdateDivisionRequest $updateDivisionRequest, int $id)
    {
        $this->divisionService->update($updateDivisionRequest, $id);

        \App\Helper\ActivityLogger::log('UPDATE', 'Master Data', "Admin memperbarui data Divisi: {$updateDivisionRequest->input('name')}", ['division_id' => $id]);

        return redirect()->route('admin.pengaturan.divisi')->with('success', 'Data Divisi berhasil diperbarui!');
    }

    public function deleteDivision(int $id)
    {
        $this->divisionService->delete($id);

        \App\Helper\ActivityLogger::log('DELETE', 'Master Data', "Admin menghapus data Divisi ID: {$id}", ['division_id' => $id]);

        return redirect()->route('admin.pengaturan.divisi')->with('success', 'Data Divisi berhasil dihapus!');
    }
}
