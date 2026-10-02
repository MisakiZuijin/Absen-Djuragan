<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreProjectRequest extends FormRequest {

    public function authorize(): bool {
        // $user = Auth::user();
        // return $user->role_id == 1 ? true : false;

        return true;
    }

    public function rules(): array {
        return [
            'project_name' => 'required|exists:name_projects,id',
            'team_name' => 'required|string|max:255',
            'members' => 'nullable|array',
            'members.*' => 'exists:interns,id',
            'description' => 'nullable|string',
            'raise_id' => 'nullable|integer|exists:hand_raises,id',
        ];
    }
}
