<?php

namespace App\Http\Requests;

use App\Helper\GmapsHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ubah ini menjadi `true` jika otorisasi tidak diperlukan
        return true;
    }

    /**
     * Prepare inputs before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => trim((string) $this->input('phone')),
            ]);
        }
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->input('email'))),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "username" => 'required|string|min:3|max:30|unique:users,username',
            "email" => [
                'required',
                'string',
                'email',
                'max:100',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com|yahoo\.co\.id|email\.com)$/i',
            ],
            "password" => 'required|string|min:6|confirmed',
            "full_name" => 'required|string|max:100',
            "gmaps_url" => 'required|string|max:1000',
            "address" => 'nullable|string|max:255',
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]+$/',
                'min:10',
                'max:15',
            ],
            "date_of_birth" => 'required|date',
            "birth_place" => "required|string|max:50",
            "school_origin_id" => 'required|integer|exists:schools,id',
            "is_gps_available" => 'nullable|integer',
            'nim' => 'nullable|string|max:50',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'religion' => 'required|string|in:Islam,Kristen,Katolik,Hindu,Buddha,Khonghucu',
            'home_latitude' => 'nullable|numeric',
            'home_longitude' => 'nullable|numeric',
        ];
    }

    /**
     * Validasi ketersediaan koordinat dari link Google Maps yang dimasukkan
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $input = trim((string) $this->input('gmaps_url', ''));
            if (!empty($input)) {
                // 1. Jika frontend sudah berhasil melakukan geocoding dan mengirimkan koordinat yang valid, gunakan koordinat tersebut
                $existingLat = $this->input('home_latitude');
                $existingLng = $this->input('home_longitude');
                if ($existingLat !== null && $existingLng !== null && is_numeric($existingLat) && is_numeric($existingLng)) {
                    $lat = (float) $existingLat;
                    $lng = (float) $existingLng;
                    if (GmapsHelper::isValidCoordinate($lat, $lng)) {
                        return;
                    }
                }

                // 2. Jika koordinat belum tersedia dari frontend, coba selesaikan melalui backend GmapsHelper
                $coords = GmapsHelper::resolveCoordinates($input);
                if (!$coords || !GmapsHelper::isValidCoordinate($coords['lat'] ?? null, $coords['lng'] ?? null)) {
                    $validator->errors()->add(
                        'gmaps_url',
                        'Link Google Maps tidak valid atau titik lokasi tidak ditemukan. Pastikan link disalin dari Google Maps (contoh: https://maps.app.goo.gl/...).'
                    );
                } else {
                    $mergeData = [
                        'home_latitude' => $coords['lat'],
                        'home_longitude' => $coords['lng'],
                    ];
                    if (empty($this->input('address')) && !empty($coords['address'])) {
                        $mergeData['address'] = $coords['address'];
                    }
                    $this->merge($mergeData);
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 30 karakter.',
            'username.unique' => 'Username sudah terdaftar, silakan gunakan username lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.regex' => 'Email harus menggunakan domain @gmail.com, @yahoo.com, atau @email.com.',
            'email.unique' => 'Email sudah terdaftar, silakan gunakan email lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'gmaps_url.required' => 'Link Google Maps domisili / kos wajib diisi.',
            'phone.required' => 'Nomor telepon/WhatsApp wajib diisi.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka (tidak boleh ada huruf, spasi, atau tanda minus).',
            'phone.min' => 'Nomor telepon minimal 10 digit angka.',
            'phone.max' => 'Nomor telepon maksimal 15 digit angka.',
            'date_of_birth.required' => 'Tanggal lahir wajib diisi.',
            'birth_place.required' => 'Tempat lahir wajib diisi.',
            'school_origin_id.required' => 'Asal sekolah/kampus wajib dipilih.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Pilihan jenis kelamin harus Laki-laki atau Perempuan.',
            'religion.required' => 'Agama wajib dipilih.',
            'religion.in' => 'Pilihan agama harus salah satu dari 6 agama resmi di Indonesia (Islam, Kristen, Katolik, Hindu, Buddha, Khonghucu).',
            'address.max' => 'Alamat / keterangan kos maksimal 255 karakter.',
        ];
    }
}
