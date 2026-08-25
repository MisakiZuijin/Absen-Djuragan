<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StorePermitPresenceRequest extends FormRequest {

    public function authorize(): bool {
        // $user = Auth::user();
        // return $user->role_id == 1 ? true : false;

        return true;
    }

    public function rules(): array {
        return [
            'keterangan' => 'required|string',
            'link-google-drive' => 'nullable|url',
            'kategori-izin' => 'required|string',
            'jam-option' => 'string',
            'id' => '',
            'id-shift' => '',
            'id-schedule' => ''
        ];
    }
}
