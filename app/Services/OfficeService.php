<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Repositories\Interface\OfficeRepository;
use App\Models\Coordinate;
use function Sentry\captureException;
use App\Http\Requests\StoreOfficeRequest;
use App\Http\Requests\UpdateOfficeRequest;

class OfficeService {
    protected $officeRepository;


    public function __construct(OfficeRepository $officeRepository) {
        $this->officeRepository = $officeRepository;
    }

    public function getAll(): ActionResult {
        try {
            $result =  $this->officeRepository->getAll();
            return new ActionResult(true, "success retrive office data", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "something weird", null);
        }
    }

    public function create(StoreOfficeRequest $storeOfficeRequest) {
        try {
            $data = $storeOfficeRequest->validated();

            $data['name'] = $data['namaKantor'];
            $data['address'] = $data['alamatKantor'];
            $data['capacity'] = $data['kapasitasKantor'];

            $result = $this->officeRepository->create($data);

            $office_id = $result->id;

            Coordinate::create([
                'office_id' => $office_id,
                'latitude' => $data['latitudeoffice'],
                'longitude' => $data['longitudeoffice'],
                'is_main' => true,
            ]);

            Coordinate::create([
                'office_id' => $office_id,
                'latitude' => $data['latitulefttop'],
                'longitude' => $data['longitudelefttop'],
                'is_main' => false,
            ]);

            Coordinate::create([
                'office_id' => $office_id,
                'latitude' => $data['latiturightbottom'],
                'longitude' => $data['longituderightbottom'],
                'is_main' => false,
            ]);

            return new ActionResult(true, "Successfully added office and coordinates", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "Failed to update, something went wrong", null);
        }
    }

    public function update(UpdateOfficeRequest $updateOfficeRequest, $id) {
        try {
            $data = $updateOfficeRequest->validated();

            $data['name'] = $data['namaKantor'];
            $data['address'] = $data['alamatKantor'];
            $data['capacity'] = $data['kapasitasKantor'];

            // Update office data
            $result = $this->officeRepository->update($id, $data);

            // Ambil semua koordinat berdasarkan office_id
            $coordinates = Coordinate::where('office_id', $id)->get();

            // Perbarui data koordinat jika ada
            if ($coordinates->count() > 0) {
                $coordinates[0]->update([
                    'latitude' => $data['latitudeoffice'],
                    'longitude' => $data['longitudeoffice'],
                ]);

                $coordinates[1]->update([
                    'latitude' => $data['latitulefttop'],
                    'longitude' => $data['longitudelefttop'],
                ]);

                $coordinates[2]->update([
                    'latitude' => $data['latiturightbottom'],
                    'longitude' => $data['longituderightbottom'],
                ]);
            } else {
                Coordinate::create([
                    'office_id' => $id,
                    'latitude' => $data['latitudeoffice'],
                    'longitude' => $data['longitudeoffice'],
                    'is_main' => true,
                ]);

                Coordinate::create([
                    'office_id' => $id,
                    'latitude' => $data['latitulefttop'],
                    'longitude' => $data['longitudelefttop'],
                    'is_main' => false,
                ]);

                Coordinate::create([
                    'office_id' => $id,
                    'latitude' => $data['latiturightbottom'],
                    'longitude' => $data['longituderightbottom'],
                    'is_main' => false,
                ]);
            }

            return new ActionResult(true, "Successfully updated office and coordinates", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "Failed to update, something went wrong", null);
        }
    }

    public function delete($id) {
        try {
            $result = $this->officeRepository->delete($id);
            return new ActionResult(true, "success delete quotes", $result);
        } catch (\Throwable $th) {
            captureException($th);
            return new ActionResult(false, "failed delete data, something weird", null);
        }
    }
}
