<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Services\OfficeService;
use App\Http\Requests\StoreOfficeRequest;
use App\Http\Requests\UpdateOfficeRequest;
use App\Models\Office;
use App\Models\Coordinate;


class SettingOfficeController extends Controller
{
    protected $userService;
    protected $officeService;

    public function __construct(UserService $userService, OfficeService $officeService)
    {
        $this->userService = $userService;
        $this->officeService = $officeService;
    }

    public function adminSettingKantorView(): View
    {
        $office =  $this->officeService->getAll();
        $userData = $this->userService->getUserLoggedData();


        $data = [
            "user" => $userData,
            "office" => $office->isSuccess() ? $office->getData() : null,
        ];

        return view('admin.pengaturan-kantor')->with($data);
    }

    public function storeOffice(StoreOfficeRequest $storeOfficeRequest)
    {
        $this->officeService->create($storeOfficeRequest);

        return redirect()->route('admin.pengaturan.kantor')->with('success', 'Data kantor berhasil ditambahkan!');
    }

    public function updateOffice(UpdateOfficeRequest $updateOfficeRequest, $id)
    {
        $this->officeService->update($updateOfficeRequest, $id);

        return redirect()->route('admin.pengaturan.kantor')->with('success', 'Data kantor berhasil diperbarui!');
    }

    public function deleteOffice($id)
    {
        $this->officeService->delete($id);

        return redirect()->route('admin.pengaturan.kantor')->with('success', 'Data kantor berhasil dihapus!');
    }

    public function showEditLocation($id)
    {
        $office = Office::with('coordinate')->findOrFail($id);
        $coordinates = Coordinate::where('office_id', $id)->get();

        return view('admin.edit-maps-location', compact('office', 'coordinates'));
    }
}
