<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateDivisionRequest extends FormRequest {

    public function authorize(): bool {
        // $user = Auth::user();
        // return $user->role_id == 1 ? true : false;

        return true;
    }

    public function rules(): array {
        return [
            'namaDivisi' => 'required|string|max:255',
            'iconDivisi' => 'nullable|file|image|mimes:jpg,png,jpeg,webp|max:2048',
        ];
    }

    public function messages(): array {
        return [
            'namaDivisi.required' => 'Nama divisi wajib diisi.',
            'iconDivisi.image' => 'Berkas ikon divisi harus berupa gambar/foto.',
            'iconDivisi.mimes' => 'Format foto ikon divisi harus berupa JPG, JPEG, PNG, atau WEBP.',
            'iconDivisi.max' => 'Ukuran foto ikon divisi maksimal adalah 2MB.',
        ];
    }
}
