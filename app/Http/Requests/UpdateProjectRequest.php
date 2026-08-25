<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProjectRequest extends FormRequest {

    public function authorize(): bool {
        // $user = Auth::user();
        // return $user->role_id == 1 ? true : false;

        return true;
    }

    public function rules(): array {
        return [
        //    'project_name' => 'string|max:255',
            'team_name' => 'string|max:255',
            'description' => 'nullable|string',
            'members.*' => 'exists:interns,id'
        ];
    }
}
