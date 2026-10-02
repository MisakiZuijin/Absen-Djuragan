<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateOfficeRequest extends FormRequest {

    public function authorize(): bool {
        // $user = Auth::user();
        // return $user->role_id == 1 ? true : false;

        return true;
    }

    public function rules(): array {
        return [
            'namaKantor' => 'required|string|max:255',
            'alamatKantor' => 'required|string|max:1000',
            'kapasitasKantor' => 'required|integer|min:1',
            'radius' => 'nullable|numeric|min:5|max:1000',
            'latitudeoffice' => 'required|string|max:255',
            'longitudeoffice' => 'required|string|max:255',
            'latitulefttop' => 'required|string|max:255',
            'longitudelefttop' => 'required|string|max:255',
            'latiturightbottom' => 'required|string|max:255',
            'longituderightbottom' => 'required|string|max:255',
            'sop_url' => 'nullable|string|max:1000',
            'rules_url' => 'nullable|string|max:1000',
            'rules_description' => 'nullable|string',
            'piket_url' => 'nullable|string|max:1000',
            'piket_description' => 'nullable|string',
        ];
    }
}
