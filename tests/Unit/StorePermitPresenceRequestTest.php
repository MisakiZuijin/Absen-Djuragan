<?php

namespace Tests\Unit;

use App\Http\Requests\StorePermitPresenceRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StorePermitPresenceRequestTest extends TestCase
{
    private function validate(array $data): \Illuminate\Validation\Validator
    {
        $request = StorePermitPresenceRequest::create('/', 'POST', $data);

        return Validator::make($data, $request->rules(), $request->messages());
    }

    public function test_keperluan_sekolah_requires_drive_link(): void
    {
        $validator = $this->validate([
            'keterangan' => 'Ujian kampus',
            'kategori-izin' => '3',
            'jam-option' => '2',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('link-google-drive'));
    }

    public function test_keperluan_lain_requires_drive_link(): void
    {
        $validator = $this->validate([
            'keterangan' => 'Keperluan keluarga',
            'kategori-izin' => '4',
            'link-google-drive' => '',
            'jam-option' => '2',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('link-google-drive'));
    }

    public function test_keperluan_accepts_valid_drive_link(): void
    {
        $validator = $this->validate([
            'keterangan' => 'Ujian kampus',
            'kategori-izin' => '3',
            'link-google-drive' => 'https://drive.google.com/file/d/abc',
            'jam-option' => '2',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_sakit_tanpa_surat_allows_empty_drive_link(): void
    {
        $validator = $this->validate([
            'keterangan' => 'Demam',
            'kategori-izin' => '2',
            'jam-option' => '0',
        ]);

        $this->assertFalse($validator->errors()->has('link-google-drive'));
    }
}
