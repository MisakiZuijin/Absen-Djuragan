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
            'nama_Shift' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
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
            'is_split_shift' => 'nullable|in:0,1',
            'edit_is_split_shift' => 'nullable|in:0,1',
            'split_wfh_start' => 'nullable',
            'split_wfh_end' => 'nullable',
            'split_wfo_start' => 'nullable',
            'split_wfo_end' => 'nullable',
            'edit_split_wfh_start' => 'nullable',
            'edit_split_wfh_end' => 'nullable',
            'edit_split_wfo_start' => 'nullable',
            'edit_split_wfo_end' => 'nullable',
            'is_rolling' => 'nullable|in:0,1',
            'edit_is_rolling' => 'nullable|in:0,1',
            'rolling_partner_shift_id' => 'nullable|integer',
            'edit_rolling_partner_shift_id' => 'nullable|integer',
        ];
    }
}
