<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        // Ubah ini menjadi `true` jika otorisasi tidak diperlukan
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            "username" => 'required|string|min:8|max:30',
            "email" => 'required|string|email|max:100',
            "password" => 'required|string|min:8|confirmed',
            "full_name" => 'required|string|max:100',
            "address" => 'nullable|string|max:100',
            'phone' => 'required|numeric|digits_between:7,16',
            "date_of_birth" => 'required|date',
            "birth_place" => "required|string|max:20",
            "school_origin_id" => 'required|integer',
            "is_gps_available" => 'required|integer',
            'nim' => 'max:50',
            'gender' => 'required|in:Laki-laki,Perempuan',
        ];
    }
}
