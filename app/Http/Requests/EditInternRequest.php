<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class EditInternRequest extends FormRequest {

    public function authorize(): bool {
        return true;
    }

    protected function prepareForValidation(): void {
        if ($this->filled('gmaps_url')) {
            $coords = \App\Helper\GmapsHelper::resolveCoordinates((string) $this->gmaps_url);
            if ($coords) {
                $mergeData = [
                    'home_latitude' => $coords['lat'],
                    'home_longitude' => $coords['lng'],
                ];
                if (!$this->filled('home_address_notes') && !empty($coords['address'])) {
                    $mergeData['home_address_notes'] = $coords['address'];
                }
                if (!$this->filled('address') && !empty($coords['address'])) {
                    $mergeData['address'] = $coords['address'];
                }
                $this->merge($mergeData);
            }
        }
        if ($this->has('home_latitude') && is_string($this->home_latitude)) {
            $this->merge(['home_latitude' => str_replace(',', '.', trim($this->home_latitude))]);
        }
        if ($this->has('home_longitude') && is_string($this->home_longitude)) {
            $this->merge(['home_longitude' => str_replace(',', '.', trim($this->home_longitude))]);
        }
        if ($this->has('parent_whatsapp_number')) {
            $rawWa = (string) $this->parent_whatsapp_number;
            $cleanWa = preg_replace('/[\s\-\.\(\)]+/', '', trim($rawWa));
            if ($cleanWa === '') {
                $this->merge(['parent_whatsapp_number' => null]);
            } else {
                if (str_starts_with($cleanWa, '+')) {
                    $cleanWa = substr($cleanWa, 1);
                }
                $this->merge(['parent_whatsapp_number' => $cleanWa]);
            }
        }
    }

    public function rules(): array {
        return [
            "gmaps_url" => "nullable|string|max:1000",
            "user_id" => "required|integer",
            "email" => "required|email",
            "username" => "required|string",
            "full_name" => "required|string",
            "password" => "nullable|string",
            "confirm_password" => "nullable|string",
            "school_id" => "nullable|integer",
            "birth_date" => "required|date",
            "birth_place" => "required|string",
            "phone" => "required",
            "nim" => "nullable|string",
            "in_date" => "nullable|date",
            "out_date" => "nullable|date",
            "nip" => "required|string",
            "division_id" => "nullable|integer",
            "shift_id" => "nullable|integer|exists:shifts,id",
            "religion" => "nullable|string|in:Islam,Kristen,Katolik,Hindu,Buddha,Khonghucu",
            "address" => "nullable|string|max:100",
            "brand_id" => "nullable|integer|exists:brands,id",
            "project_id" => "nullable|integer",
            "os" => "required|string",
            "browser" => "required|string",
            "office_id" => "nullable|integer",
            "account_status" => "required|boolean",
            "email_confirm" => "required|boolean",
            "is_reset_device_token" => "nullable|boolean",
            "is_gps_active" => "required|in:0,1",
            "parent_whatsapp_number" => "nullable|numeric|starts_with:0,62",
            "gender" => "nullable|string",
            "enabled_platforms" => "nullable|array",
            "enabled_platforms.*" => "nullable|string|in:gdrive,spreadsheet,github,figma,sosmed",
            "gdrive_url" => "nullable|url|max:500",
            "spreadsheet_url" => "nullable|url|max:500",
            "github_url" => "nullable|url|max:255",
            "gmail_account" => "nullable|string|max:255",
            "gmail_password" => "nullable|string|max:255",
            "figma_url" => "nullable|url|max:500",
            "social_media_links" => "nullable|array",
            "social_media_links.*.platform" => "nullable|string|max:50",
            "social_media_links.*.username" => "nullable|string|max:100",
            "social_media_links.*.url" => "nullable|string|max:500",
            "notes" => "nullable|string|max:2000",
            "home_latitude" => "nullable|numeric",
            "home_longitude" => "nullable|numeric",
            "home_address_notes" => "nullable|string|max:500",
            "work_placement_type" => "nullable|string|in:auto_office,wfo_only,wfh_only,hybrid",
            "locked_office_id" => "nullable|integer|exists:offices,id",
            "blacklisted_office_id" => "nullable|integer|exists:offices,id",
            "hr_placement_notes" => "nullable|string|max:1000",
            "conflict_intern_id" => "nullable|integer|exists:interns,id"
        ];
    }

    public function messages(): array {
        return [
            'parent_whatsapp_number.starts_with' => 'Nomor WhatsApp orang tua harus diawali dengan 0 atau 62.',
            'parent_whatsapp_number.numeric' => 'Nomor WhatsApp orang tua hanya boleh berisi angka.',
        ];
    }
}
