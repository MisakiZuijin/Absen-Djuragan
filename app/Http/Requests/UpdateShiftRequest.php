<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateShiftRequest extends FormRequest
{

    public function authorize(): bool
    {
        // $user = Auth::user();
        // return $user->role_id == 1 ? true : false;

        return true;
    }

    public function rules(): array
    {
        return [
            'nama_Shift' => 'required',
            'jamMulai' => 'required',
            'jamBerakhir' => 'required',
            'edit_start_break_time' => 'required',
            'edit_end_break_time' => 'required',
            'edit_adt_start_break_time' => 'nullable',
            'edit_adt_end_break_time' => 'nullable',
            'is_gps_active' => 'nullable|in:0,1',
            'is_friday_break_active' => 'nullable|in:0,1',
            'friday_start_break_time' => 'nullable',
            'friday_end_break_time' => 'nullable',
            'edit_is_friday_break_active' => 'nullable|in:0,1',
            'edit_friday_start_break_time' => 'nullable',
            'edit_friday_end_break_time' => 'nullable',
        ];
    }
}
