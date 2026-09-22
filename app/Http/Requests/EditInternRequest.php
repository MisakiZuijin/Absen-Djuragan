<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class EditInternRequest extends FormRequest {

    public function authorize(): bool {
        // $user = Auth::user();
        // return $user->role_id == 1 ? true : false;

        return true;
    }

    public function rules(): array {
        return [
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
            "project_id" => "nullable|integer",
            "os" => "required|string",
            "browser" => "required|string",
            "office_id" => "nullable|integer",
            "account_status" => "required|boolean",
            "email_confirm" => "required|boolean",
            "is_reset_device_token" => "nullable|boolean",
            "is_gps_active" => "required|boolean",
            "parent_whatsapp_number" => "nullable|numeric|starts_with:62",
            "gender" => "nullable|string",
            "gdrive_url" => "nullable|url|max:500",
            "github_url" => "nullable|url|max:255",
            "gmail_account" => "nullable|string|max:255",
            "gmail_password" => "nullable|string|max:255",
            "figma_url" => "nullable|url|max:500",
            "social_media_links" => "nullable|array",
            "social_media_links.*.platform" => "nullable|string|max:50",
            "social_media_links.*.username" => "nullable|string|max:100",
            "social_media_links.*.url" => "nullable|string|max:500",
            "notes" => "nullable|string|max:2000"
        ];
    }
}
