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
            'description' => 'nullable|string|max:500',
            'addJamMulai' => 'required',
            'addJamBerakhir' => 'required',
            'start_break_time' => 'required',
            'end_break_time' => 'required',
            'adt_start_break_time' => 'nullable',
            'adt_end_break_time' => 'nullable',
            'is_gps_active' => 'nullable|in:0,1',
            'is_friday_break_active' => 'nullable|in:0,1',
            'friday_start_break_time' => 'nullable',
            'friday_end_break_time' => 'nullable',
            'is_split_shift' => 'nullable|in:0,1',
            'split_wfh_start' => 'nullable',
            'split_wfh_end' => 'nullable',
            'split_wfo_start' => 'nullable',
            'split_wfo_end' => 'nullable',
            'is_rolling' => 'nullable|in:0,1',
        ];
    }
}
