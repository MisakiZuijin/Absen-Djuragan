<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest {

    public function authorize(): bool {
        // $user = Auth::user();
        // return $user->role_id == 1 ? true : false;

        return true;
    }

    public function rules(): array {
        return [
            'nama' => 'required|string|max:255',
            'nohp' => 'required|string|max:15',
            'alamat' => 'required|string|max:255',
            'password' => 'nullable|string|max:60'
        ];
    }
}
