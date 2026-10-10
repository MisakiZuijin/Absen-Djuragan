<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AdminMapsController extends Controller {

    public function officeMapsView() {
        $allShifts = \App\Models\Shift::where('id', '!=', 1)->get();
        $allDivisions = \App\Models\Division::all();
        return View("admin.maps-location", compact('allShifts', 'allDivisions'));
    }

    /**
     * Resolve Google Maps URL (termasuk shortlink maps.app.goo.gl) atau teks koordinat
     */
    public function resolveGmapsUrl(Request $request)
    {
        $input = trim((string) $request->input('url', ''));
        if (empty($input)) {
            return response()->json([
                'success' => false,
                'message' => 'URL atau teks koordinat tidak boleh kosong.'
            ], 422);
        }

        $res = \App\Helper\GmapsHelper::resolveCoordinates($input);
        if ($res) {
            return response()->json([
                'success' => true,
                'lat' => $res['lat'],
                'lng' => $res['lng'],
                'address' => $res['address'] ?? null,
                'source' => $res['source'],
                'display_name' => $res['display_name'] ?? null,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Koordinat tidak ditemukan di dalam link atau teks yang dimasukkan. Pastikan memasukkan link Google Maps yang valid (contoh: https://maps.app.goo.gl/...).'
        ], 422);
    }

    /**
     * Geocode alamat / nama lokasi secara otomatis via Google Maps backend scraping
     * dengan fallback ke Photon Komoot dan OpenStreetMap Nominatim.
     */
    public function geocodeAddress(Request $request)
    {
        $query = trim((string) ($request->input('query') ?? $request->input('address') ?? ''));

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Alamat atau nama lokasi tidak boleh kosong.'
            ], 422);
        }

        $res = \App\Helper\GmapsHelper::resolveCoordinates($query);
        if ($res) {
            return response()->json([
                'success' => true,
                'lat' => $res['lat'],
                'lng' => $res['lng'],
                'display_name' => $res['display_name'] ?? $query,
                'source' => $res['source'],
                'results' => [
                    [
                        'lat' => $res['lat'],
                        'lng' => $res['lng'],
                        'display_name' => $res['display_name'] ?? $query,
                    ]
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Alamat atau link tidak ditemukan di sistem peta. Mohon periksa kembali link Google Maps yang Anda masukkan.'
        ], 404);
    }
}


