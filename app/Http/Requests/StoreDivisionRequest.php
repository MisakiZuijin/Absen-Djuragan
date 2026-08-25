<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreDivisionRequest extends FormRequest {

    public function authorize(): bool {
        // $user = Auth::user();
        // return $user->role_id == 1 ? true : false;

        return true;
    }

    public function rules(): array {
        return [
            'namaDivisi' => 'required|string|max:255',
            'iconDivisi' => 'required|file|image|mimes:jpg,png,jpeg,gif,svg|max:2048'
        ];
    }
}
