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
    protected UserService $userService;
    protected OfficeService $officeService;

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

        \App\Helper\ActivityLogger::log('CREATE', 'Master Data', "Admin menambahkan lokasi kantor baru: {$storeOfficeRequest->input('namaKantor')}");

        return redirect()->route('admin.pengaturan.kantor')->with('success', 'Data kantor berhasil ditambahkan!');
    }

    public function updateOffice(UpdateOfficeRequest $updateOfficeRequest, int $id)
    {
        $this->officeService->update($updateOfficeRequest, $id);

        \App\Helper\ActivityLogger::log('UPDATE', 'Master Data', "Admin memperbarui data lokasi kantor: {$updateOfficeRequest->input('namaKantor')}");

        return redirect()->route('admin.pengaturan.kantor')->with('success', 'Data kantor berhasil diperbarui!');
    }

    public function deleteOffice(int $id)
    {
        $office = Office::find($id);
        $officeName = $office ? $office->name : "ID {$id}";

        $this->officeService->delete($id);

        \App\Helper\ActivityLogger::log('DELETE', 'Master Data', "Admin menghapus lokasi kantor: {$officeName}");

        return redirect()->route('admin.pengaturan.kantor')->with('success', 'Data kantor berhasil dihapus!');
    }

    public function showEditLocation(int $id)
    {
        $office = Office::with('coordinates')->findOrFail($id);
        $coordinates = $office->coordinates;
        $radius = $office->radius;

        return view('admin.edit-maps-location', compact('office', 'coordinates', 'radius'));
    }
}
