# 📋 Rencana Implementasi Baru — Absen Djuragan

> Dokumen ini menggabungkan **plan lama yang belum selesai** (Fase 6 & 7 dari `docs/PLANS.md`) dengan **fitur baru** yang diminta.
> Dirancang sebagai **panduan mandiri** yang bisa dikerjakan tanpa bantuan AI.

---

## Daftar Isi & Status

| No  | Fitur                                                                                                | Prioritas    | Estimasi | Status   |
| --- | ---------------------------------------------------------------------------------------------------- | ------------ | -------- | -------- |
| 1   | [Izin Keperluan: Link Bukti Drive Wajib](#1-izin-keperluan-link-bukti-drive-wajib)                   | ⚡ Quick Win | 30 menit | ⬜ Belum |
| 2   | [Popup Teks Saat Masuk (Terlambat/Tepat Waktu)](#2-popup-teks-saat-masuk-terlambattepat-waktu)       | 🟡 Sedang    | 2-3 jam  | ⬜ Belum |
| 3   | [Izin Keluar: Peningkatan Manajemen](#3-izin-keluar-peningkatan-manajemen)                           | 🟡 Sedang    | 3-4 jam  | ⬜ Belum |
| 4   | [Presensi Regular: Hutang Waktu dari Izin Keluar](#4-presensi-regular-hutang-waktu-dari-izin-keluar) | 🔴 Kompleks  | 3-4 jam  | ⬜ Belum |
| 5   | [Sistem Broadcast Terjadwal](#5-sistem-broadcast-terjadwal)                                          | 🔴 Kompleks  | 5-7 jam  | ⬜ Belum |
| 6   | [Penyesuaian Web untuk macOS/iOS](#6-penyesuaian-web-untuk-macosios)                                 | 🟡 Sedang    | 2-3 jam  | ⬜ Belum |
| 7   | [Role Superadmin & Pembagian Hak Akses](#7-role-superadmin--pembagian-hak-akses)                     | 🟡 Sedang    | 3-4 jam  | ⬜ Belum |
| 8   | [Penataan & Penyatuan File Migrasi Database](#8-penataan--penyatuan-file-migrasi-database)           | ⚠️ Hati-hati | 4-5 jam  | ⬜ Belum |

---

## 1. Izin Keperluan: Link Bukti Drive Wajib

### Deskripsi

Saat pemagang mengajukan **Izin Keperluan**, field **link bukti Google Drive** harus menjadi **wajib diisi** (required), bukan opsional seperti sekarang.

### Analisis Kode Saat Ini

**File yang perlu diubah:**

1. **`resources/views/users/index.blade.php`** — Form izin di sisi pemagang
2. **`app/Http/Controllers/AttendanceController.php`** — Validasi backend saat submit izin

**Kondisi sekarang:**

- Di `resources/views/users/index.blade.php` sekitar baris 1314-1317:
    ```javascript
    // Saat user memilih kategori "Izin Keperluan":
    if (labelProof) labelProof.innerHTML = 'Link Dokumen Pendukung <span class="text-slate-400 font-normal">(opsional)</span>';
    if (inputProof) {
        inputProof.required = false;
        inputProof.placeholder = 'https://drive.google.com/file/d/... (opsional)';
    ```
- Di sisi admin `AdminPermitKeperluanController.php` baris 204:
    ```php
    'proof_url' => 'nullable|url|max:500',
    ```

### Langkah Implementasi

#### Langkah 1: Ubah JavaScript Form Pemagang

**File**: `resources/views/users/index.blade.php`

Cari bagian JavaScript yang menangani perubahan kategori izin (sekitar baris 1310-1320). Saat kategori **Izin Keperluan** dipilih, ubah:

```javascript
// SEBELUM (opsional):
if (labelProof) labelProof.innerHTML = 'Link Dokumen Pendukung <span class="text-slate-400 font-normal">(opsional)</span>';
if (inputProof) {
    inputProof.required = false;
    inputProof.placeholder = 'https://drive.google.com/file/d/... (opsional)';

// SESUDAH (wajib):
if (labelProof) labelProof.innerHTML = 'Link Google Drive Bukti Keperluan <span class="text-rose-500">*</span>';
if (inputProof) {
    inputProof.required = true;
    inputProof.placeholder = 'https://drive.google.com/file/d/...';
    inputProof.className = 'w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500';
```

#### Langkah 2: Ubah Validasi Backend

**File**: `app/Http/Controllers/AttendanceController.php`

Cari method yang memproses pengajuan izin tidak hadir dari pemagang. Pada rules validasi, ubah field `link-google-drive` menjadi required khusus saat kategori izin = keperluan:

```php
// Tambahkan validasi kondisional:
'link-google-drive' => [
    'nullable',
    'url',
    'max:500',
    // Wajib jika kategori = 3 (Keperluan)
    Rule::requiredIf(function () use ($request) {
        return $request->input('permit_category_id') == 3;
    }),
],
```

Jangan lupa tambahkan `use Illuminate\Validation\Rule;` di atas file controller.

#### Langkah 3: Ubah Validasi di Admin Controller

**File**: `app/Http/Controllers/AdminPermitKeperluanController.php`

Di method `updateDetail()` (baris 200-207), ubah:

```php
// SEBELUM:
'proof_url' => 'nullable|url|max:500',

// SESUDAH:
'proof_url' => 'required|url|max:500',
```

### Verifikasi

1. Login sebagai pemagang
2. Klik "Izin Tidak Hadir" → Pilih kategori "Izin Keperluan"
3. Pastikan field link bukti Drive menampilkan tanda `*` (wajib)
4. Coba submit tanpa mengisi link → harus gagal dengan pesan error
5. Isi link valid → harus berhasil

---

## 2. Popup Teks Saat Masuk (Terlambat/Tepat Waktu)

### Deskripsi

Saat pemagang menekan tombol **Masuk** di sistem presensi:

- Jika **tepat waktu**: tampilkan popup teks khusus (misal: "Selamat datang! Anda tepat waktu hari ini 🎉")
- Jika **terlambat**: tampilkan popup teks peringatan (misal: "Anda terlambat! Harap datang tepat waktu")
- Teks dan gambar opsional **di-setting oleh admin** di halaman Settings

### File yang Perlu Dibuat/Diubah

#### A. Migration — Tabel `checkin_messages`

**File Baru**: `database/migrations/xxxx_xx_xx_create_checkin_messages_table.php`

```bash
php artisan make:migration create_checkin_messages_table
```

```php
Schema::create('checkin_messages', function (Blueprint $table) {
    $table->id();
    $table->enum('type', ['on_time', 'late'])->unique();
    $table->text('message'); // Teks popup
    $table->string('image')->nullable(); // Path gambar opsional
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Seed data default
DB::table('checkin_messages')->insert([
    [
        'type' => 'on_time',
        'message' => 'Selamat datang! Anda tepat waktu hari ini 🎉',
        'image' => null,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'type' => 'late',
        'message' => 'Anda terlambat hari ini. Harap datang tepat waktu!',
        'image' => null,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
]);
```

#### B. Model

**File Baru**: `app/Models/CheckinMessage.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckinMessage extends Model
{
    protected $fillable = ['type', 'message', 'image', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getOnTimeMessage(): ?self
    {
        return static::where('type', 'on_time')->where('is_active', true)->first();
    }

    public static function getLateMessage(): ?self
    {
        return static::where('type', 'late')->where('is_active', true)->first();
    }
}
```

#### C. Controller Admin Settings

**File**: `app/Http/Controllers/SettingController.php`

Tambahkan method baru:

```php
use App\Models\CheckinMessage;

// Tampilkan halaman setting popup check-in
public function checkinMessageSettingsView(): View
{
    $messages = CheckinMessage::all()->keyBy('type');
    return view('admin.pengaturan-checkin-message', compact('messages'));
}

// Simpan perubahan setting popup check-in
public function updateCheckinMessages(Request $request)
{
    $request->validate([
        'on_time_message' => 'required|string|max:500',
        'late_message' => 'required|string|max:500',
        'on_time_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'late_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    foreach (['on_time', 'late'] as $type) {
        $msg = CheckinMessage::firstOrCreate(['type' => $type]);
        $msg->message = $request->input("{$type}_message");

        if ($request->hasFile("{$type}_image")) {
            // Hapus gambar lama jika ada
            if ($msg->image && file_exists(public_path('checkin-images/' . $msg->image))) {
                unlink(public_path('checkin-images/' . $msg->image));
            }
            $file = $request->file("{$type}_image");
            $filename = $type . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('checkin-images'), $filename);
            $msg->image = $filename;
        }

        // Jika admin ingin menghapus gambar
        if ($request->has("remove_{$type}_image")) {
            if ($msg->image && file_exists(public_path('checkin-images/' . $msg->image))) {
                unlink(public_path('checkin-images/' . $msg->image));
            }
            $msg->image = null;
        }

        $msg->save();
    }

    return redirect()->back()->with('success', 'Pengaturan popup check-in berhasil diperbarui!');
}
```

#### D. Route Admin

**File**: `routes/web.php`

Di dalam group `Route::prefix('setting')`, tambahkan:

```php
Route::get('/checkin-message', [SettingController::class, 'checkinMessageSettingsView'])
    ->name('admin.pengaturan.checkin-message');
Route::post('/checkin-message', [SettingController::class, 'updateCheckinMessages'])
    ->name('admin.pengaturan.checkin-message.update');
```

#### E. View Admin Settings

**File Baru**: `resources/views/admin/pengaturan-checkin-message.blade.php`

Buat halaman admin dengan form yang berisi:

- Textarea untuk teks "Tepat Waktu"
- Input file gambar untuk "Tepat Waktu" (opsional)
- Textarea untuk teks "Terlambat"
- Input file gambar untuk "Terlambat" (opsional)
- Preview gambar jika sudah ada
- Tombol simpan

Gunakan layout yang sama dengan `pengaturan-batas-izin.blade.php` sebagai referensi.

#### F. Modifikasi Flow Check-In Pemagang

**File**: `app/Livewire/AttdStatusButton.php` atau `app/Http/Controllers/AttendanceController.php`

Saat proses check-in berhasil, kirim data popup ke frontend:

```php
use App\Models\CheckinMessage;

// Setelah proses check-in berhasil:
$isLate = /* logika cek terlambat yang sudah ada */;
$popupMsg = $isLate
    ? CheckinMessage::getLateMessage()
    : CheckinMessage::getOnTimeMessage();

// Kirim data popup ke session flash atau response
session()->flash('checkin_popup', [
    'type' => $isLate ? 'late' : 'on_time',
    'message' => $popupMsg?->message ?? ($isLate ? 'Anda terlambat!' : 'Tepat waktu!'),
    'image' => $popupMsg?->image ? asset('checkin-images/' . $popupMsg->image) : null,
]);
```

**File**: `resources/views/users/index.blade.php`

Tambahkan modal/popup di bagian bawah file:

```html
@if(session('checkin_popup'))
<div
    id="checkinPopup"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
>
    <div
        class="bg-white rounded-2xl shadow-xl max-w-sm w-full mx-4 p-6 text-center"
    >
        @php $popup = session('checkin_popup'); @endphp @if($popup['image'])
        <img
            src="{{ $popup['image'] }}"
            alt="Checkin"
            class="w-32 h-32 mx-auto mb-4 rounded-xl object-cover"
        />
        @endif

        <div
            class="text-lg font-bold mb-2 {{ $popup['type'] === 'late' ? 'text-red-600' : 'text-emerald-600' }}"
        >
            {{ $popup['type'] === 'late' ? '⚠️ Terlambat' : '✅ Tepat Waktu' }}
        </div>

        <p class="text-sm text-slate-600 mb-4">{{ $popup['message'] }}</p>

        <button
            onclick="document.getElementById('checkinPopup').remove()"
            class="px-6 py-2 bg-slate-800 text-white rounded-xl text-sm font-semibold hover:bg-slate-700"
        >
            Mengerti
        </button>
    </div>
</div>
@endif
```

#### G. Tambahkan Link di Sidebar/Menu Admin

Tambahkan link navigasi ke halaman pengaturan popup check-in di sidebar admin (file `resources/views/admin/layouts/sidebar.blade.php` atau sejenisnya).

### Verifikasi

1. Jalankan `php artisan migrate`
2. Login sebagai admin → Setting → Popup Check-in → Isi teks dan upload gambar
3. Login sebagai pemagang → Cek in tepat waktu → Harus muncul popup "Tepat Waktu"
4. Cek in terlambat → Harus muncul popup "Terlambat" dengan pesan dan gambar yang diset admin

---

## 3. Izin Keluar: Peningkatan Manajemen

### Deskripsi

Pada halaman manajemen **Izin Keluar** (`admin/izin-keluar.blade.php`), tambahkan:

- **Durasi** izin keluar (berapa lama)
- **Keterangan** alasan izin keluar
- **Disetujui oleh** (siapa admin/HR yang menyetujui)
- **Set Waktu izin** yang disepakati (1 jam, 2 jam, dll.) — ini yang jadi acuan hutang waktu di Fitur #4

### Analisis Kode Saat Ini

Tabel `permit_logs` sudah memiliki:

- `type` — enum: 'leave', 'toilet', 'prayer', 'other'
- `description` — text nullable
- `authorized_by` — string nullable
- `start_time`, `end_time` — timestamps
- `duration_in_minutes` — integer nullable

Yang perlu ditambah:

- `agreed_duration_minutes` — integer: waktu izin yang disepakati admin (dalam menit)
- `is_mandatory_replace` — boolean: apakah wajib ganti jam atas izin keluar ini
- `approved_by_user_id` — foreignId: relasi ke user admin yang approve (opsional, lebih baik dari string)

### Langkah Implementasi

#### Langkah 1: Migration — Tambah Kolom ke `permit_logs`

```bash
php artisan make:migration add_agreed_duration_to_permit_logs_table
```

```php
Schema::table('permit_logs', function (Blueprint $table) {
    // Waktu izin yang disepakati admin (dalam menit)
    $table->unsignedInteger('agreed_duration_minutes')->nullable()->after('duration_in_minutes');
    // Apakah wajib ganti jam?
    $table->boolean('is_mandatory_replace')->default(false)->after('agreed_duration_minutes');
    // Status persetujuan
    $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('is_mandatory_replace');
});
```

#### Langkah 2: Update Model `PermitLog`

**File**: `app/Models/PermitLog.php`

```php
protected $fillable = [
    'attendance_id',
    'type',
    'description',
    'authorized_by',
    'start_time',
    'end_time',
    'duration_in_minutes',
    'agreed_duration_minutes',    // BARU
    'is_mandatory_replace',       // BARU
    'approval_status',            // BARU
];

protected $casts = [
    'start_time' => 'datetime',
    'end_time' => 'datetime',
    'is_mandatory_replace' => 'boolean',
];
```

#### Langkah 3: Update Controller Izin Keluar

**File**: Cari controller yang menangani izin keluar. Kemungkinan di `app/Http/Controllers/AttendanceController.php` atau controller khusus izin keluar.

Tambahkan method untuk admin approve izin keluar dengan set durasi:

```php
public function approveLeavePermit(Request $request, PermitLog $permitLog)
{
    $validated = $request->validate([
        'agreed_duration_minutes' => 'required|integer|min:15|max:480', // 15 menit s/d 8 jam
        'is_mandatory_replace' => 'required|boolean',
        'admin_notes' => 'nullable|string|max:500',
    ]);

    $permitLog->update([
        'authorized_by' => auth()->user()->name,
        'agreed_duration_minutes' => $validated['agreed_duration_minutes'],
        'is_mandatory_replace' => $validated['is_mandatory_replace'],
        'approval_status' => 'approved',
        'description' => $validated['admin_notes'] ?? $permitLog->description,
    ]);

    return redirect()->back()->with('success', 'Izin keluar berhasil disetujui.');
}
```

#### Langkah 4: Update View Admin Izin Keluar

**File**: `resources/views/admin/izin-keluar.blade.php`

Tambahkan kolom baru di tabel:

```html
<!-- Header tabel tambahan -->
<th>Durasi (Menit)</th>
<th>Keterangan</th>
<th>Disetujui Oleh</th>
<th>Waktu Disepakati</th>
<th>Wajib Ganti</th>

<!-- Data kolom di setiap row -->
<td>{{ $log->duration_in_minutes ?? '-' }} menit</td>
<td>{{ $log->description ?? '-' }}</td>
<td>{{ $log->authorized_by ?? 'Belum' }}</td>
<td>
    @if($log->agreed_duration_minutes) {{ floor($log->agreed_duration_minutes /
    60) }} Jam {{ $log->agreed_duration_minutes % 60 }} Menit @else
    <span class="text-amber-500">Belum diset</span>
    @endif
</td>
<td>
    @if($log->is_mandatory_replace)
    <span class="text-red-600 font-bold">Ya</span>
    @else
    <span class="text-green-600">Tidak</span>
    @endif
</td>
```

Tambahkan modal approve dengan form:

```html
<!-- Modal Approve Izin Keluar -->
<form
    method="POST"
    action="{{ route('admin.leave-permit.approve', $log->id) }}"
>
    @csrf @method('PUT')

    <label>Waktu Izin yang Disepakati</label>
    <select name="agreed_duration_minutes">
        <option value="30">30 Menit</option>
        <option value="60">1 Jam</option>
        <option value="90">1,5 Jam</option>
        <option value="120">2 Jam</option>
        <option value="180">3 Jam</option>
        <option value="240">4 Jam</option>
    </select>

    <label>Wajib Ganti Jam?</label>
    <select name="is_mandatory_replace">
        <option value="1">Ya, Wajib Ganti</option>
        <option value="0">Tidak, Gratis</option>
    </select>

    <textarea name="admin_notes" placeholder="Catatan admin..."></textarea>

    <button type="submit">Setujui Izin Keluar</button>
</form>
```

#### Langkah 5: Tambahkan Route

**File**: `routes/web.php`

```php
Route::put('/leave-permit/{permitLog}/approve', [AttendanceController::class, 'approveLeavePermit'])
    ->name('admin.leave-permit.approve');
```

### Verifikasi

1. Jalankan `php artisan migrate`
2. Login sebagai admin → Izin Keluar
3. Klik approve pada suatu izin keluar → Pastikan bisa set durasi dan kewajiban ganti jam
4. Cek data tersimpan di tabel `permit_logs` dengan kolom baru

---

## 4. Presensi Regular: Hutang Waktu dari Izin Keluar

### Deskripsi

Di halaman presensi regular:

- Jika **izin keluar disetujui** dengan status **wajib ganti jam** (`is_mandatory_replace = true`), maka **hutang waktu hari ini bertambah** sebesar durasi yang disepakati (`agreed_duration_minutes`)
- Jika **tidak wajib ganti jam** (`is_mandatory_replace = false`), hutang waktu **tetap normal** (tidak bertambah)

### Alur Logika

```
1. Pemagang check-in
2. Pemagang izin keluar (misal: 1 jam)
3. Admin approve izin keluar → set waktu 1 jam, wajib ganti = Ya
4. Di perhitungan presensi hari itu:
   - Jika wajib ganti: hutang_hari_ini += 60 menit
   - Jika tidak wajib: hutang_hari_ini tetap normal
```

### Langkah Implementasi

#### Langkah 1: Update `AttendanceService.php`

**File**: `app/Services/AttendanceService.php`

Cari method yang menghitung hutang waktu harian (kemungkinan di `getInternAttendance()`, `detailAttendanceReport()`, atau `calculateDailyWorkHours()`).

Tambahkan logika baru setelah perhitungan hutang normal:

```php
// Setelah menghitung hutang waktu normal dari keterlambatan / pulang cepat...

// Cek apakah ada izin keluar yang wajib ganti jam
$mandatoryReplaceMinutes = 0;
if ($attendance && $attendance->permitLogs) {
    $mandatoryReplaceMinutes = $attendance->permitLogs
        ->where('type', 'leave')
        ->where('is_mandatory_replace', true)
        ->where('approval_status', 'approved')
        ->sum('agreed_duration_minutes');
}

// Tambahkan ke hutang jika ada
if ($mandatoryReplaceMinutes > 0) {
    $totalDebtMinutes += $mandatoryReplaceMinutes;
}
```

#### Langkah 2: Update `TimeHelper.php`

**File**: `app/Helper/TimeHelper.php`

Jika perhitungan hutang waktu juga ada di TimeHelper, tambahkan parameter opsional:

```php
public static function calculateDailyWorkHours(
    $detailSchedule,
    $attendance = null,
    $additionalDebtMinutes = 0  // BARU: hutang tambahan dari izin keluar
): array {
    // ... logika existing ...

    // Di bagian akhir sebelum return:
    if ($additionalDebtMinutes > 0) {
        $totalTarget += $additionalDebtMinutes;
    }

    // ... return ...
}
```

#### Langkah 3: Update View Detail Presensi

**File**: `resources/views/admin/detail-presensi.blade.php`

Di bagian tabel presensi harian, tambahkan indikator jika ada hutang tambahan dari izin keluar:

```html
@if($mandatoryReplaceMinutes > 0)
<span class="text-xs text-red-500 block">
    + {{ floor($mandatoryReplaceMinutes/60) }}j {{ $mandatoryReplaceMinutes%60
    }}m (izin keluar wajib ganti)
</span>
@endif
```

### Verifikasi

1. Buat data testing: pemagang izin keluar 1 jam, admin approve dengan wajib ganti = Ya
2. Lihat halaman presensi pemagang tersebut → hutang harus bertambah 1 jam
3. Buat data testing: pemagang izin keluar 1 jam, admin approve dengan wajib ganti = Tidak
4. Lihat halaman presensi → hutang tetap normal

---

## 5. Sistem Broadcast Terjadwal

### Deskripsi

Buat halaman **Broadcast** baru yang meng-enhance sistem broadcast yang sudah ada, dengan kemampuan:

- Mengirim teks/pertanyaan kepada pemagang
- Admin men-setting **kapan** broadcast dikirim (jadwal)
- Target: ke **divisi** tertentu, ke **siapa** (specific user), ke **shift** tertentu, atau ke **brand/kantor** tertentu
- Waktu pengiriman bebas di-set admin/HR

### Analisis Kode Saat Ini

Broadcast yang sudah ada (`Broadcast` model):

- `title`, `message`, `broadcast_type` (all/division/specific)
- Relasi: `divisions()` (many-to-many), `users()` (many-to-many), `images()` (hasMany)
- **Belum ada**: scheduling, targeting by shift, targeting by brand/office

### Langkah Implementasi

#### Langkah 1: Migration — Tambah Kolom ke `broadcasts`

```bash
php artisan make:migration add_scheduling_to_broadcasts_table
```

```php
Schema::table('broadcasts', function (Blueprint $table) {
    // Scheduling
    $table->timestamp('scheduled_at')->nullable()->after('broadcast_type');
    // null = kirim sekarang, ada value = kirim terjadwal
    $table->boolean('is_sent')->default(false)->after('scheduled_at');
    $table->timestamp('sent_at')->nullable()->after('is_sent');

    // Enhanced targeting (extend broadcast_type)
    // broadcast_type tetap: 'all', 'division', 'specific'
    // Tambah opsi baru:
    // Ubah broadcast_type enum atau tambah kolom terpisah

    // Targeting by shift
    $table->json('target_shift_ids')->nullable()->after('sent_at');
    // Targeting by office/brand
    $table->json('target_office_ids')->nullable()->after('target_shift_ids');

    // Pengirim
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('target_office_ids');
});
```

#### Langkah 2: Migration — Tabel Pivot `broadcast_shift`

```bash
php artisan make:migration create_broadcast_shift_table
```

```php
Schema::create('broadcast_shift', function (Blueprint $table) {
    $table->id();
    $table->foreignId('broadcast_id')->constrained()->onDelete('cascade');
    $table->foreignId('shift_id')->constrained()->onDelete('cascade');
});

Schema::create('broadcast_office', function (Blueprint $table) {
    $table->id();
    $table->foreignId('broadcast_id')->constrained()->onDelete('cascade');
    $table->foreignId('office_id')->constrained()->onDelete('cascade');
});
```

#### Langkah 3: Update Model `Broadcast`

**File**: `app/Models/Broadcast.php`

```php
protected $fillable = [
    'title',
    'message',
    'broadcast_type',
    'scheduled_at',      // BARU
    'is_sent',           // BARU
    'sent_at',           // BARU
    'target_shift_ids',  // BARU
    'target_office_ids', // BARU
    'created_by',        // BARU
];

protected $casts = [
    'scheduled_at' => 'datetime',
    'sent_at' => 'datetime',
    'is_sent' => 'boolean',
    'target_shift_ids' => 'array',
    'target_office_ids' => 'array',
];

protected $with = ['divisions', 'users', 'images', 'shifts', 'offices'];

// Relasi baru
public function shifts()
{
    return $this->belongsToMany(Shift::class, 'broadcast_shift');
}

public function offices()
{
    return $this->belongsToMany(Office::class, 'broadcast_office');
}

public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}

// Scope: Broadcast yang belum terkirim dan sudah waktunya
public function scopePendingSend($query)
{
    return $query->where('is_sent', false)
        ->where(function ($q) {
            $q->whereNull('scheduled_at') // Kirim langsung
              ->orWhere('scheduled_at', '<=', now()); // Sudah waktunya
        });
}
```

#### Langkah 4: Buat Job untuk Broadcast Terjadwal

```bash
php artisan make:job SendScheduledBroadcasts
```

**File**: `app/Jobs/SendScheduledBroadcasts.php`

```php
<?php

namespace App\Jobs;

use App\Models\Broadcast;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendScheduledBroadcasts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $broadcasts = Broadcast::pendingSend()->get();

        foreach ($broadcasts as $broadcast) {
            $broadcast->update([
                'is_sent' => true,
                'sent_at' => now(),
            ]);

            // Broadcast sudah tersedia di database, pemagang akan melihatnya
            // saat membuka halaman dashboard (sudah ada logika existing)
        }
    }
}
```

#### Langkah 5: Daftarkan Schedule

**File**: `routes/console.php` atau `app/Console/Kernel.php`

```php
use App\Jobs\SendScheduledBroadcasts;
use Illuminate\Support\Facades\Schedule;

// Cek setiap menit apakah ada broadcast terjadwal yang perlu dikirim
Schedule::job(new SendScheduledBroadcasts)->everyMinute();
```

> **PENTING**: Pastikan cron sudah berjalan di server:
>
> ```bash
> * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
> ```
>
> Untuk development di Laragon, jalankan `php artisan schedule:work` di terminal terpisah.

#### Langkah 6: Update BroadcastController

**File**: `app/Http/Controllers/BroadcastController.php`

Update method `store()`:

```php
public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'message' => 'required|string',
        'broadcast_type' => 'required|in:all,division,specific,shift,office',
        'divisions' => 'nullable|required_if:broadcast_type,division|array',
        'users' => 'nullable|required_if:broadcast_type,specific|array',
        'shifts' => 'nullable|required_if:broadcast_type,shift|array',
        'offices' => 'nullable|required_if:broadcast_type,office|array',
        'images' => 'nullable|array',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        'scheduled_at' => 'nullable|date|after:now', // Jadwal kirim
    ]);

    DB::beginTransaction();
    try {
        $broadcast = Broadcast::create([
            'title' => $request->title,
            'message' => $request->message,
            'broadcast_type' => $request->broadcast_type,
            'scheduled_at' => $request->scheduled_at,
            'is_sent' => $request->scheduled_at ? false : true, // Langsung terkirim jika tanpa jadwal
            'sent_at' => $request->scheduled_at ? null : now(),
            'created_by' => auth()->id(),
        ]);

        // Sync relasi sesuai tipe
        match ($request->broadcast_type) {
            'division' => $broadcast->divisions()->sync($request->divisions ?? []),
            'specific' => $broadcast->users()->sync($request->users ?? []),
            'shift'    => $broadcast->shifts()->sync($request->shifts ?? []),
            'office'   => $broadcast->offices()->sync($request->offices ?? []),
            default    => null,
        };

        // Upload gambar
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('broadcast-image'), $filename);
                $broadcast->images()->create(['image' => $filename]);
            }
        }

        DB::commit();
        return redirect()->to('/admin/broadcasts')->with('success', 'Pengumuman berhasil dibuat.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
    }
}
```

#### Langkah 7: Update View Broadcast

**File**: `resources/views/admin/pengaturan-broadcast.blade.php`

Tambahkan field baru di form:

```html
<!-- Scheduling -->
<div>
    <label class="block text-xs font-bold text-slate-700 mb-1.5">
        Jadwal Pengiriman
        <span class="text-slate-400 font-normal"
            >(kosongkan untuk kirim sekarang)</span
        >
    </label>
    <input
        type="datetime-local"
        name="scheduled_at"
        class="w-full border border-slate-300 rounded-xl p-2.5 text-xs"
    />
</div>

<!-- Target Shift (tampilkan jika broadcast_type = 'shift') -->
<div id="shiftTarget" style="display:none;">
    <label class="block text-xs font-bold text-slate-700 mb-1.5"
        >Target Shift</label
    >
    @foreach($shifts as $shift)
    <label class="flex items-center gap-2 text-xs">
        <input type="checkbox" name="shifts[]" value="{{ $shift->id }}" />
        {{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})
    </label>
    @endforeach
</div>

<!-- Target Office/Brand (tampilkan jika broadcast_type = 'office') -->
<div id="officeTarget" style="display:none;">
    <label class="block text-xs font-bold text-slate-700 mb-1.5"
        >Target Kantor/Brand</label
    >
    @foreach($offices as $office)
    <label class="flex items-center gap-2 text-xs">
        <input type="checkbox" name="offices[]" value="{{ $office->id }}" />
        {{ $office->name }}
    </label>
    @endforeach
</div>
```

Update dropdown `broadcast_type`:

```html
<select name="broadcast_type" id="broadcast_type">
    <option value="all">Semua Pemagang</option>
    <option value="division">Per Divisi</option>
    <option value="specific">Per Individu</option>
    <option value="shift">Per Shift</option>
    <option value="office">Per Kantor/Brand</option>
</select>
```

Tambahkan JavaScript untuk toggle visibility:

```javascript
document
    .getElementById("broadcast_type")
    .addEventListener("change", function () {
        const val = this.value;
        document.getElementById("divisionTarget").style.display =
            val === "division" ? "block" : "none";
        document.getElementById("specificTarget").style.display =
            val === "specific" ? "block" : "none";
        document.getElementById("shiftTarget").style.display =
            val === "shift" ? "block" : "none";
        document.getElementById("officeTarget").style.display =
            val === "office" ? "block" : "none";
    });
```

#### Langkah 8: Update Query Broadcast di Sisi Pemagang

**File**: `app/Http/Controllers/UserController.php` (di method `userView()` atau yang menampilkan broadcast)

Pastikan query broadcast hanya menampilkan yang `is_sent = true`:

```php
$broadcasts = Broadcast::where('is_sent', true)
    ->where(function ($q) use ($user) {
        $q->where('broadcast_type', 'all')
          ->orWhereHas('divisions', function ($dq) use ($user) {
              $dq->where('division_id', $user->intern?->division_id);
          })
          ->orWhereHas('users', function ($uq) use ($user) {
              $uq->where('user_id', $user->id);
          })
          ->orWhereHas('shifts', function ($sq) use ($user) {
              // Cek shift pemagang hari ini
              $sq->whereIn('shift_id', $user->intern?->activeShiftIds() ?? []);
          })
          ->orWhereHas('offices', function ($oq) use ($user) {
              $oq->where('office_id', $user->intern?->office_id);
          });
    })
    ->latest()
    ->get();
```

#### Langkah 9: Pass Data Tambahan ke View

**File**: `app/Http/Controllers/BroadcastController.php` (method `index()`)

```php
use App\Models\Shift;
use App\Models\Office;

public function index()
{
    $broadcastlist = Broadcast::with('divisions', 'users', 'images', 'shifts', 'offices')
        ->latest()->paginate(10);
    $divisions = Division::orderBy('name')->get();
    $users = User::whereHas('intern')->with('profile')->get();
    $shifts = Shift::orderBy('name')->get();       // BARU
    $offices = Office::orderBy('name')->get();     // BARU

    return view('admin.pengaturan-broadcast', compact('broadcastlist', 'divisions', 'users', 'shifts', 'offices'));
}
```

### Verifikasi

1. Jalankan migrasi: `php artisan migrate`
2. Buat broadcast tanpa jadwal → Harus langsung muncul di dashboard pemagang
3. Buat broadcast dengan jadwal 5 menit ke depan → Jalankan `php artisan schedule:work`
4. Setelah 5 menit, broadcast harus muncul di dashboard pemagang
5. Test targeting per shift, per office
6. Pastikan broadcast lama yang sudah ada tetap berfungsi normal

---

## 6. Penyesuaian Web untuk macOS/iOS

### Deskripsi

Pastikan tampilan web responsif dan berfungsi dengan baik di browser Safari pada perangkat macOS dan iOS.

### Masalah Umum Safari/iOS

1. **`100vh` tidak benar di iOS Safari** — Gunakan `dvh` (dynamic viewport height) atau JavaScript fallback
2. **Smooth scrolling berbeda** — iOS Safari memiliki momentum scroll sendiri
3. **Date input** — `input[type="date"]` dan `input[type="datetime-local"]` tampil berbeda
4. **Font rendering** — `-webkit-font-smoothing: antialiased`
5. **Flexbox gap** — Versi lama Safari tidak support `gap` di flexbox
6. **`position: fixed`** — Bermasalah saat virtual keyboard muncul di iOS
7. **Touch events** — Button hover states, tap delay

### Langkah Implementasi

#### Langkah 1: Tambahkan Meta Tags untuk iOS

**File**: `resources/views/users/layouts/main.blade.php` dan layout admin

Di `<head>`, tambahkan:

```html
<!-- iOS Meta Tags -->
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="default" />
<meta name="apple-mobile-web-app-title" content="Absen Djuragan" />

<!-- Viewport fix untuk iOS -->
<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover"
/>
```

#### Langkah 2: CSS Fixes

**File**: `resources/css/app.css` atau inline di layout

Tambahkan CSS fixes:

```css
/* ===== iOS Safari Fixes ===== */

/* Fix viewport height untuk iOS Safari */
:root {
    --vh: 1vh;
}

/* Fix 100vh issue */
.min-h-screen {
    min-height: 100vh;
    min-height: 100dvh; /* Dynamic viewport height - modern browsers */
}

/* Fix untuk elemen fixed di iOS saat keyboard muncul */
.fixed-ios-safe {
    position: fixed;
    /* Safe area insets untuk iPhone notch/home indicator */
    padding-bottom: env(safe-area-inset-bottom, 0);
    padding-top: env(safe-area-inset-top, 0);
}

/* Fix font smoothing di macOS */
body {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Fix tap highlight di iOS */
* {
    -webkit-tap-highlight-color: transparent;
}

/* Fix input zoom prevention di iOS (font < 16px causes zoom) */
input,
select,
textarea {
    font-size: 16px !important;
}

/* Atau lebih spesifik: */
@media screen and (-webkit-min-device-pixel-ratio: 0) {
    select:focus,
    textarea:focus,
    input:focus {
        font-size: 16px !important;
    }
}

/* Fix flexbox gap fallback untuk Safari lama */
@supports not (gap: 1rem) {
    .flex.gap-2 > * + * {
        margin-left: 0.5rem;
    }
    .flex.gap-4 > * + * {
        margin-left: 1rem;
    }
}

/* Fix modal overlay di iOS */
.modal-overlay {
    position: fixed;
    -webkit-overflow-scrolling: touch;
}

/* Fix bottom navigation bar safe area */
.bottom-nav {
    padding-bottom: env(safe-area-inset-bottom, 16px);
}

/* Fix date input styling untuk Safari */
input[type="date"],
input[type="datetime-local"],
input[type="time"] {
    -webkit-appearance: none;
    appearance: none;
}

/* Fix scrollbar di macOS */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
}
```

#### Langkah 3: JavaScript Fixes

**File**: `public/js/user/index.js` atau inline di layout

```javascript
// Fix viewport height di iOS
function setViewportHeight() {
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty("--vh", `${vh}px`);
}

window.addEventListener("resize", setViewportHeight);
window.addEventListener("orientationchange", () => {
    setTimeout(setViewportHeight, 100);
});
setViewportHeight();

// Fix: Prevent double-tap zoom di iOS
let lastTouchEnd = 0;
document.addEventListener(
    "touchend",
    function (event) {
        const now = new Date().getTime();
        if (now - lastTouchEnd <= 300) {
            event.preventDefault();
        }
        lastTouchEnd = now;
    },
    false,
);

// Fix: Keyboard push-up di iOS (scroll to input)
if (/iPhone|iPad|iPod/.test(navigator.userAgent)) {
    document.addEventListener("focusin", function (e) {
        if (
            e.target.tagName === "INPUT" ||
            e.target.tagName === "TEXTAREA" ||
            e.target.tagName === "SELECT"
        ) {
            setTimeout(() => {
                e.target.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
            }, 300);
        }
    });
}
```

#### Langkah 4: Test & Fix Specific Components

Komponen yang perlu dicek khusus di Safari:

1. **Modal/Popup izin** — Fixed positioning + scroll
2. **Geolocation API** — Safari memerlukan HTTPS untuk geolocation
3. **Audio/Notification sounds** — Safari lebih ketat dengan autoplay
4. **Camera/File upload** — `capture` attribute behavior berbeda
5. **Bottom navigation bar** — Safe area inset

### Verifikasi

1. Buka di Safari macOS → Cek semua halaman utama
2. Buka di Safari iOS (iPhone/iPad) → Cek:
    - Login page
    - Dashboard pemagang
    - Check-in/out flow
    - Modal izin
    - Popup broadcast
3. Pastikan tidak ada elemen yang terpotong, overlap, atau tidak berfungsi
4. Test landscape dan portrait orientation

---

## 7. Role Superadmin & Pembagian Hak Akses

> _Sumber: PLANS.md Fase 6 — Belum dikerjakan_

### Deskripsi

Tambahkan role **Superadmin** dengan hak akses tertinggi yang mencakup semua kemampuan admin plus fitur khusus pengelolaan sistem.

### Langkah Implementasi

#### Langkah 1: Seed/Insert Role Superadmin

```bash
php artisan make:migration add_superadmin_role_to_roles_table
```

```php
public function up(): void
{
    // Tambahkan role Superadmin (ID: 7) jika belum ada
    DB::table('roles')->insertOrIgnore([
        'id' => 7,
        'name' => 'Superadmin',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
```

#### Langkah 2: Update RoleMiddleware untuk Multi-Role

**File**: `app/Http/Middleware/RoleMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Handle an incoming request.
     * Mendukung multi-role: role:1,7 (Admin atau Superadmin)
     */
    public function handle(Request $request, Closure $next, ...$roleIds): Response
    {
        $user = $this->userService->getUserLoggedData();

        if (is_null($user)) {
            return redirect('/')->with('error', 'Anda harus login terlebih dahulu.');
        }

        // Convert semua role IDs ke integer
        $allowedRoles = array_map('intval', $roleIds);

        // Superadmin (role_id = 7) selalu punya akses ke semua route admin (role_id = 1)
        if ((int) $user->role_id === 7) {
            // Superadmin bisa akses route admin maupun superadmin
            if (in_array(1, $allowedRoles) || in_array(7, $allowedRoles)) {
                return $next($request);
            }
        }

        // Cek apakah role user termasuk dalam daftar yang diizinkan
        if (!in_array((int) $user->role_id, $allowedRoles)) {
            return redirect('/')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
```

#### Langkah 3: Update Route Definitions

**File**: `routes/web.php`

Saat ini route admin menggunakan `->middleware('role:1')`. Ubah jadi mendukung superadmin:

```php
// SEBELUM:
Route::middleware(['auth', 'role:1'])->prefix('admin')->group(function () {
    // ...
});

// SESUDAH (Superadmin otomatis punya akses berkat logika di middleware):
// Tidak perlu ubah route karena middleware sudah handle otomatis
// Tapi jika ada route KHUSUS superadmin:
Route::middleware(['auth', 'role:7'])->prefix('superadmin')->group(function () {
    // Route khusus superadmin
    Route::get('/manage-admins', [SuperadminController::class, 'manageAdmins'])->name('superadmin.manage-admins');
    Route::post('/change-role/{user}', [SuperadminController::class, 'changeRole'])->name('superadmin.change-role');
    Route::post('/reset-password/{user}', [SuperadminController::class, 'resetPassword'])->name('superadmin.reset-password');
});
```

#### Langkah 4: Buat SuperadminController

```bash
php artisan make:controller SuperadminController
```

**File**: `app/Http/Controllers/SuperadminController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperadminController extends Controller
{
    /**
     * Halaman manajemen admin/user roles
     */
    public function manageAdmins()
    {
        $users = User::with(['profile', 'intern'])->get();
        $roles = Role::all();
        return view('admin.superadmin.manage-admins', compact('users', 'roles'));
    }

    /**
     * Ubah role user
     */
    public function changeRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update(['role_id' => $validated['role_id']]);

        return redirect()->back()->with('success', "Role {$user->name} berhasil diubah.");
    }

    /**
     * Reset password user (darurat)
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return redirect()->back()->with('success', "Password {$user->name} berhasil direset.");
    }
}
```

#### Langkah 5: Buat View Superadmin

**File Baru**: `resources/views/admin/superadmin/manage-admins.blade.php`

Buat tabel user dengan:

- Nama, Email, Role saat ini
- Dropdown untuk ubah role
- Tombol reset password
- Gunakan layout admin yang sudah ada

#### Langkah 6: Update Sidebar Admin

Tambahkan menu Superadmin di sidebar (hanya tampil untuk role_id = 7):

```html
@if(auth()->user()->role_id == 7)
<div class="mt-4 border-t pt-4">
    <h3 class="text-xs font-bold text-slate-400 uppercase mb-2">Superadmin</h3>
    <a href="{{ route('superadmin.manage-admins') }}" class="...">
        <i class="fa-solid fa-users-gear"></i> Kelola Admin & Role
    </a>
</div>
@endif
```

### Verifikasi

1. Jalankan migrasi: `php artisan migrate`
2. Ubah manual role_id salah satu user menjadi 7 di database
3. Login sebagai superadmin → Pastikan bisa akses semua halaman admin
4. Pastikan menu khusus superadmin tampil
5. Test ubah role user lain
6. Test reset password user
7. Login sebagai admin biasa → Pastikan menu superadmin tidak tampil

---

## 8. Penataan & Penyatuan File Migrasi Database

> _Sumber: PLANS.md Fase 7 — Belum dikerjakan_

### Deskripsi

Menggabungkan puluhan file migrasi `add_*` dan `update_*` ke dalam file migrasi utama `create_*_table` untuk merapikan struktur database.

### ⚠️ PERINGATAN PENTING

> **Ini adalah operasi BERISIKO TINGGI.** Lakukan di environment development terlebih dahulu, backup database sebelum menjalankan, dan pastikan semua seeder berjalan sempurna.

### Langkah Implementasi

#### Langkah 1: Backup Database

```bash
# Export database saat ini
mysqldump -u root absen_djuragan > backup_sebelum_merge_migrasi.sql
```

#### Langkah 2: Dokumentasikan Semua Kolom

Jalankan query untuk mendapatkan skema saat ini:

```sql
-- Untuk setiap tabel penting:
SHOW CREATE TABLE attendances;
SHOW CREATE TABLE users;
SHOW CREATE TABLE profiles;
SHOW CREATE TABLE hand_raises;
SHOW CREATE TABLE shifts;
SHOW CREATE TABLE detail_schedules;
SHOW CREATE TABLE broadcasts;
SHOW CREATE TABLE permit_logs;
```

Simpan hasil output sebagai referensi.

#### Langkah 3: Merge Migrasi Tabel `attendances`

**Target**: Gabungkan semua migrasi ke `2024_08_19_150045_attendances.php`

1. Buka file `create` asli
2. Buka semua file `add_*` dan `update_*` yang terkait
3. Tambahkan semua kolom tambahan ke dalam `Schema::create()` asli
4. Pindahkan file alter lama ke folder `database/migrations/_archived/`

Contoh:

```php
// File: 2024_08_19_150045_attendances.php (MERGED)
Schema::create('attendances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('intern_id')->constrained();
    $table->foreignId('shift_id')->nullable()->constrained();
    $table->date('date');
    $table->time('start_time')->nullable();
    $table->time('break_time')->nullable();
    $table->time('back_time')->nullable();
    $table->time('permit_start')->nullable();
    $table->time('permit_back')->nullable();
    $table->time('end_time')->nullable();
    $table->time('adjusted_end_time')->nullable();
    $table->text('checkout_notes')->nullable();
    $table->integer('total_min')->default(0);
    $table->integer('total_break_min')->default(0);
    $table->integer('total_permit_min')->default(0);
    $table->text('start_time_message')->nullable();
    $table->text('break_time_message')->nullable();
    $table->text('back_time_message')->nullable();
    $table->text('permit_start_message')->nullable();
    $table->text('permit_back_message')->nullable();
    $table->text('end_time_message')->nullable();
    $table->boolean('is_permit')->default(false);
    $table->text('description')->nullable();
    $table->string('latitude_start')->nullable();
    $table->string('longitude_start')->nullable();
    $table->string('latitude_end')->nullable();
    $table->string('longitude_end')->nullable();
    $table->text('keterangan')->nullable();
    // Kolom dari migrasi tambahan:
    $table->string('permit_type')->nullable();
    $table->string('permit_description')->nullable();
    $table->string('permit_authorized_by')->nullable();
    $table->string('authorized_by')->nullable();
    $table->text('proof_link')->nullable();
});
```

#### Langkah 4: Ulangi untuk Tabel Lain

Lakukan hal yang sama untuk:

- `users`
- `profiles`
- `hand_raises`
- `shifts`
- `detail_schedules`
- `broadcasts`

#### Langkah 5: Buat Folder Arsip

```bash
mkdir database/migrations/_archived
```

Pindahkan semua file `add_*` dan `update_*` ke folder arsip:

```bash
# Contoh:
move database/migrations/2025_08_15_110828_add_permit_type_to_attendances_table.php database/migrations/_archived/
move database/migrations/2025_08_16_081118_update_permit_type_enum_add_prayer.php database/migrations/_archived/
# ... dan seterusnya
```

#### Langkah 6: Validasi

```bash
# Reset dan test dari awal
php artisan migrate:fresh --seed

# Pastikan tidak ada error
php artisan migrate:status
```

#### Langkah 7: Cek Semua Fitur

Setelah `migrate:fresh`, test semua fitur utama:

- Login admin & pemagang
- Check-in/out
- Izin sakit, keperluan, keluar
- Raise hand
- Broadcast
- Project management

### Tips Penting

- **JANGAN** merge migrasi di production tanpa backup
- Kerjakan satu tabel dulu, test, baru lanjut ke tabel berikutnya
- Simpan file arsip setidaknya sampai yakin semuanya berjalan normal
- Jika menggunakan Git, buat branch khusus: `git checkout -b merge-migrations`

---

## Tips Umum Pengerjaan

### Urutan yang Disarankan

1. **Mulai dari Quick Win** → Fitur #1 (Izin Keperluan Wajib)
2. **Fitur mandiri sederhana** → Fitur #2 (Popup Check-in) & #6 (iOS/macOS)
3. **Fitur terkait** → Fitur #3 (Izin Keluar) **dulu**, baru #4 (Hutang Waktu) karena #4 bergantung pada #3
4. **Fitur kompleks** → Fitur #5 (Broadcast Terjadwal)
5. **Perubahan arsitektur** → Fitur #7 (Superadmin)
6. **TERAKHIR** → Fitur #8 (Merge Migrasi) — setelah semua fitur baru sudah selesai

### Command Berguna

```bash
# Buat model + migration + controller
php artisan make:model NamaModel -mc

# Buat migration saja
php artisan make:migration nama_migration

# Jalankan migration
php artisan migrate

# Rollback 1 step
php artisan migrate:rollback

# Clear semua cache
php artisan optimize:clear

# Khusus view cache
php artisan view:clear

# Cek route list
php artisan route:list --name=admin
```

### Perintah Git yang Disarankan

```bash
# Buat branch per fitur
git checkout -b fitur/1-izin-keperluan-wajib
# ... kerjakan ...
git add -A && git commit -m "feat: link bukti drive wajib untuk izin keperluan"
git checkout main && git merge fitur/1-izin-keperluan-wajib

# Ulangi untuk fitur berikutnya
git checkout -b fitur/2-popup-checkin
```

---

## Checklist Verifikasi Akhir

- [ ] Link bukti Drive wajib saat izin keperluan (frontend + backend validation)
- [ ] Popup teks saat masuk tepat waktu / terlambat, admin bisa setting
- [ ] Izin keluar menampilkan durasi, keterangan, disetujui oleh, waktu disepakati
- [ ] Hutang waktu bertambah jika izin keluar wajib ganti jam
- [ ] Broadcast terjadwal bisa di-set waktu, target shift, target kantor
- [ ] Tampilan web rapi di Safari macOS & iOS
- [ ] Role Superadmin berfungsi dengan akses penuh + menu khusus
- [ ] Migrasi database berhasil `migrate:fresh --seed` tanpa error
- [ ] Semua fitur lama tetap berfungsi normal setelah perubahan
