<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateTimeAttendanceRequest extends FormRequest {

    public function authorize(): bool {
        // $user = Auth::user();
        // return $user->role_id == 1 ? true : false;

        return true;
    }

    public function rules(): array {
        return [
            'tipe' => 'required|string',
            'field' => 'required|string',
            'time' => 'required',
            'message' => 'nullable',
        ];
    }
}
