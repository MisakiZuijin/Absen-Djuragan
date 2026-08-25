<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreShiftRequest extends FormRequest {

    public function authorize(): bool {
        // $user = Auth::user();
        // return $user->role_id == 1 ? true : false;

        return true;
    }

    public function rules(): array {
        return [
           'addNamaShift' => 'required|string|max:255',
            'addJamMulai' => 'required|date_format:H:i',
            'addJamBerakhir' => 'required|date_format:H:i',
            'start_break_time' => 'required|date_format:H:i',
            'end_break_time' => 'required|date_format:H:i',
            'adt_start_break_time' => 'nullable|date_format:H:i',
            'adt_end_break_time' => 'nullable|date_format:H:i',
        ];
    }
}
