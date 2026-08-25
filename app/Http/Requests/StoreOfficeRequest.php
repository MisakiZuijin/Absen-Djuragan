<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreOfficeRequest extends FormRequest {

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
            'latitudeoffice' => 'required|string|max:255',
            'longitudeoffice' => 'required|string|max:255',
            'latitulefttop' => 'required|string|max:255',
            'longitudelefttop' => 'required|string|max:255',
            'latiturightbottom' => 'required|string|max:255',
            'longituderightbottom' => 'required|string|max:255',
        ];
    }
}
