<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreSchoolRequest extends FormRequest {

    public function authorize(): bool {
        // $user = Auth::user();
        // return $user->role_id == 1 ? true : false;

        return true;
    }

    public function rules(): array {
        return [
            'namaSekolah' => 'required|string|max:255',
            'schoolType1' => 'required|integer',
            'alamatSekolah' => 'required|string'
        ];
    }
}
