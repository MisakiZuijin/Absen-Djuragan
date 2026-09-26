<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermitPresenceRequest extends FormRequest {

    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'id' => 'nullable|integer',
            'id-shift' => 'nullable|integer',
            'id-schedule' => 'nullable|integer',
            'keterangan' => 'required|string',
            'link-google-drive' => [
                'nullable',
                'url',
                'max:500',
                Rule::requiredIf($this->isKeperluanCategory()),
            ],
            'kategori-izin' => 'required|string',
            'jam-option' => 'required|string',
        ];
    }

    public function messages(): array {
        return [
            'link-google-drive.required' => 'Link Google Drive bukti keperluan wajib diisi.',
            'link-google-drive.url' => 'Link Google Drive harus berupa URL yang valid.',
        ];
    }

    private function isKeperluanCategory(): bool {
        return in_array((int) $this->input('kategori-izin'), [3, 4], true);
    }
}
