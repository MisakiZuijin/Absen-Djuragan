<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePermitPresenceRequest extends FormRequest {

    public function authorize(): bool {
        // $user = Auth::user();
        // return $user->role_id == 1 ? true : false;

        return true;
    }

    public function rules(): array {
        return [
            'id' => 'nullable|integer',
            'id-shift' => 'nullable|integer',
            'id-schedule' => 'nullable|integer',
           'keterangan' => 'required|string',
            'link-google-drive' => 'nullable|url',
            'kategori-izin' => 'required|string',
            'jam-option' => 'required|string',
        ];
    }
}
