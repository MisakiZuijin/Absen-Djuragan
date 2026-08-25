<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateSchoolRequest extends FormRequest {

    public function authorize(): bool {
        // $user = Auth::user();
        // return $user->role_id == 1 ? true : false;

        return true;
    }

    public function rules(): array {
        return [
            'namaSekolah' => 'required|string|max:255',
            'alamatSekolah' => 'required|string',
            'schoolType' => 'required|integer'
        ];
    }
}
