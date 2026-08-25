<?php

namespace App\Http\Requests;

use App\Helper\LogConsole;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'user_id' => "required|numeric",
            "stage" => "required|numeric",
            "is_adjustable" => "required|boolean",
            "attendance_id" => "nullable|integer",
            "adjustable_id" => "nullable|integer",
            "description" => "nullable|string",
            "latitude" => "nullable|numeric|between:-90,90",
            "longitude" => "nullable|numeric|between:-180,180"
        ];
    }

   
}
