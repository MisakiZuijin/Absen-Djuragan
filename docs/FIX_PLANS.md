# 🔧 Rencana Perbaikan Bug & Sistem — Absen Djuragan

> Hasil audit kode menyeluruh pada 21-22 September 2026.
> Diurutkan berdasarkan **tingkat keparahan** (Critical → Low).
> Setiap item berisi: lokasi file, deskripsi masalah, dan cara perbaikan.

---

## Daftar Isi & Ringkasan

| No  | Kategori                                                   | Jumlah | Keparahan   | Status   |
| --- | ---------------------------------------------------------- | ------ | ----------- | -------- |
| 1   | [Keamanan (Security)](#1-keamanan-security)                | 5 item | 🔴 Critical | ✅ Selesai |
| 2   | [Duplikat & Konflik Kode](#2-duplikat--konflik-kode)       | 3 item | 🟠 High     | ✅ Selesai |
| 3   | [Logika & Kalkulasi](#3-logika--kalkulasi)                 | 3 item | 🟠 High     | ✅ Selesai |
| 4   | [Performa (Performance)](#4-performa-performance)          | 4 item | 🟡 Medium   | ✅ Selesai |
| 5   | [Debug & Logging Berlebihan](#5-debug--logging-berlebihan) | 3 item | 🟡 Medium   | ✅ Selesai |
| 6   | [Error Handling & UX](#6-error-handling--ux)               | 4 item | 🟡 Medium   | ✅ Selesai |
| 7   | [Arsitektur & Kode Bersih](#7-arsitektur--kode-bersih)     | 9 item | 🔵 Low      | ✅ Selesai |
| 8   | [Database & Migrasi](#8-database--migrasi)                 | 3 item | 🔵 Low      | ✅ Selesai |

---

## 1. Keamanan (Security)

### 1.1 🔴 SQL Injection — `AttendanceRepositoryIMPL.php`

**File**: `app/Repositories/Implementation/AttendanceRepositoryIMPL.php` baris 286

**Masalah**: Variabel `$endTime` diinterpolasi langsung ke dalam `DB::raw()` tanpa parameterized binding. Ini adalah celah SQL injection.

```php
// SEKARANG (BERBAHAYA):
'attendances.total_min' => DB::raw("TIMESTAMPDIFF(MINUTE, attendances.start_time, '$endTime')")
```

**Perbaikan**:

```php
// AMAN — gunakan parameter binding:
'attendances.total_min' => DB::raw("TIMESTAMPDIFF(MINUTE, attendances.start_time, ?)", [$endTime])
```

Atau lebih baik, pisahkan menjadi dua operasi:

```php
->update([
    'attendances.end_time' => $endTime,
    'is_auto_end' => true,
]);

// Hitung total_min secara terpisah menggunakan Carbon
$attendances = Attendance::where('attendances.date', $dateNow)
    ->whereNotNull('attendances.start_time')
    ->get();

foreach ($attendances as $attendance) {
    $start = Carbon::parse($attendance->start_time);
    $end = Carbon::parse($endTime);
    $attendance->update(['total_min' => $start->diffInMinutes($end)]);
}
```

---

### 1.2 🔴 JWT Secret Key Hardcoded — `UserService.php`

**File**: `app/Services/UserService.php` baris 300 dan 325

**Masalah**: JWT secret key menggunakan fallback hardcoded `'ular sanca baik hati'` dan menggunakan `env()` langsung di luar config (tidak akan bekerja saat config di-cache).

```php
// SEKARANG (BERBAHAYA):
$secretKey = env('JWT_SECRET_KEY', 'ular sanca baik hati');
```

**Perbaikan**:

1. Tambahkan di `config/app.php` (atau buat `config/jwt.php`):

```php
// config/jwt.php
return [
    'secret_key' => env('JWT_SECRET_KEY'),
];
```

2. Pastikan `.env` memiliki:

```
JWT_SECRET_KEY=random_string_yang_sangat_panjang_dan_aman_minimal_32_karakter
```

3. Ubah di `UserService.php`:

```php
// AMAN:
$secretKey = config('jwt.secret_key');

if (empty($secretKey)) {
    throw new \RuntimeException('JWT_SECRET_KEY belum diset di .env');
}
```

---

### 1.3 🔴 Password Change Tanpa Konfirmasi — `UserService.php`

**File**: `app/Services/UserService.php` baris 322-323

**Masalah**: Method `changePassword()` tidak memvalidasi `password_confirmation`. User bisa saja salah ketik password baru tanpa dikonfirmasi.

```php
// SEKARANG (LEMAH):
$data = $request->validate([
    "password" => 'required|min:8',
]);
```

**Perbaikan**:

```php
// LEBIH AMAN:
$data = $request->validate([
    "password" => 'required|min:8|confirmed',
]);
```

Pastikan form di frontend juga menambahkan field `password_confirmation`.

---

### 1.4 🟠 Tidak Ada Rate Limiting pada Route Login & Reset Password

**File**: `routes/web.php`

**Masalah**: Tidak ada `throttle` middleware di route login, reset password, dan API endpoint. Ini memungkinkan brute force attack.

**Perbaikan**:

```php
// Di routes/web.php, tambahkan throttle:
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1') // Maks 5 percobaan per menit
    ->name('login');

Route::post('/forgot-password', [UserService::class, 'forgotPassword'])
    ->middleware('throttle:3,5') // Maks 3 percobaan per 5 menit
    ->name('forgot-password');

// Untuk semua route API:
Route::middleware('throttle:60,1')->group(function () {
    // API routes...
});
```

---

### 1.5 🟠 RoleMiddleware Single Role — Mudah Di-bypass

**File**: `app/Http/Middleware/RoleMiddleware.php` baris 19-31

**Masalah**: Middleware hanya menerima 1 parameter `int $roleId` sehingga tidak bisa memvalidasi multi-role (misal Admin ATAU Superadmin).

**Perbaikan** (sudah dicakup di `docs/NEW_PLANS.md` Fitur #7):

```php
// Ubah signature:
public function handle(Request $request, Closure $next, ...$roleIds): Response
{
    // ... validasi multi-role
}
```

---

## 2. Duplikat & Konflik Kode

### 2.1 🟠 Duplikat Controller Broadcast

**File**:

- `app/Http/Controllers/BroadcastController.php` (166 baris)
- `app/Http/Controllers/SettingBroadcastController.php` (137 baris)

**Masalah**: Kedua controller melakukan hal yang **SAMA PERSIS** — CRUD pengumuman — tetapi dengan implementasi yang sedikit berbeda. `BroadcastController` mendukung multi-image, `SettingBroadcastController` hanya single image. Yang dipakai di `routes/web.php` adalah `BroadcastController`.

**Dampak**: Kebingungan developer, potensi bug jika yang salah dipanggil.

**Perbaikan**:

1. Pastikan hanya `BroadcastController` yang digunakan (sudah benar di routes)
2. **Hapus** `SettingBroadcastController.php` karena tidak dipakai
3. Hapus import `use App\Http\Controllers\SettingBroadcastController;` dari `routes/web.php` jika ada
4. Cek apakah ada view yang masih merujuk ke controller lama

---

### 2.2 🟠 `AdjstInfoContainer` — Debug Log Berlebihan di Livewire Component

**File**: `app/Livewire/AdjstInfoContainer.php`

**Masalah**: Ada **14 baris** `Log::info()` di dalam satu Livewire component. Setiap kali komponen di-mount, update, render, atau navigate, semua di-log. Ini bukan hanya masalah performa tapi juga membanjiri log file.

```php
// Contoh (14 panggilan Log::info di satu file):
Log::info('AdjstInfoContainer mount - adjstData count: ' . count($adjstData ?: []));
Log::info('AdjstInfoContainer mount - adjstData: ' . json_encode($adjstData));
Log::info('AdjstInfoContainer mount - sessionIndex: ' . $sessionIndex);
// ... dan seterusnya
```

**Perbaikan**: Hapus semua `Log::info()` dari file ini, atau ganti dengan `Log::debug()` yang hanya aktif di environment development.

---

### 2.3 🟡 Komentar Debug di `AttendanceService.php`

**File**: `app/Services/AttendanceService.php`

**Masalah**: Ada **13+ komentar debug point** dan log yang tersisa dari sesi debugging sebelumnya (baris 998-1040):

```php
// Debug point 1: Check if scheduleRepository exists
// Debug point 2: Try to find schedule
// Debug point 3: Check detailSchedules
// Debug point 4: Check data mapping
Log::info('detailAttendanceReport started', [...]);
Log::info('Calling scheduleRepository->findByInternId', [...]);
```

**Perbaikan**: Hapus semua debug points dan Log::info() yang tidak diperlukan untuk production logging.

---

## 3. Logika & Kalkulasi

### 3.1 🟠 `env()` Digunakan di Luar Config

**File**: `app/Services/UserService.php` baris 300 dan 325

**Masalah**: Laravel me-cache config. Setelah `php artisan config:cache`, panggilan `env()` di luar file `config/` akan **selalu return `null`**. Ini berarti reset password TIDAK AKAN BERFUNGSI di production jika config di-cache.

```php
// TIDAK BERFUNGSI setelah config:cache:
$secretKey = env('JWT_SECRET_KEY', 'ular sanca baik hati');
```

**Perbaikan**: Pindahkan ke config file (lihat item 1.2 di atas).

---

### 3.2 🟡 Timezone Mismatch Potensial

**File**: Multiple — `app/Services/AttendanceService.php`, `app/Helper/TimeHelper.php`

**Masalah**: Kode menggunakan `Carbon::now()`, `Carbon::today()`, dan `now()` tanpa konsisten menyertakan timezone. Jika server memiliki timezone berbeda dari `Asia/Jakarta`, perhitungan jam kerja, keterlambatan, dan hutang waktu akan salah.

**Perbaikan**:

1. Pastikan `config/app.php` sudah set:

```php
'timezone' => 'Asia/Jakarta',
```

2. Untuk extra safety, gunakan timezone eksplisit di perhitungan kritis:

```php
$now = Carbon::now('Asia/Jakarta');
$today = Carbon::today('Asia/Jakarta');
```

---

### 3.3 🟡 Attendance Checkout — Edge Case Midnight Crossover

**File**: `app/Models/Attendance.php` baris 111-137

**Masalah**: Method `canCheckOut()` membandingkan `$now` dengan `$expectedDateTime` menggunakan tanggal attendance. Jika pemagang bekerja melewati midnight (shift malam), perbandingan akan salah karena tanggal tidak di-adjust.

```php
// Saat ini: selalu pakai tanggal attendance
$attendanceDate = $this->date ? $this->date->format('Y-m-d') : today()->format('Y-m-d');
$expectedDateTime = Carbon::parse($attendanceDate . ' ' . $expectedEndTime);
```

**Perbaikan**:

```php
// Cek apakah end_time lebih kecil dari start_time (berarti melewati midnight)
$shift = $this->detailSchedules?->shift;
if ($shift && $shift->end_time < $shift->start_time) {
    // Shift malam: tambahkan 1 hari ke expected end time
    $expectedDateTime = $expectedDateTime->addDay();
}
```

---

## 4. Performa (Performance)

### 4.1 🟡 Query Tanpa Pagination — Memory Risk

**File**: Multiple controllers

**Masalah**: Banyak query menggunakan `->get()` tanpa limit atau pagination. Jika data bertambah banyak, ini akan menyebabkan out-of-memory.

| File                           | Baris  | Query                                                         |
| ------------------------------ | ------ | ------------------------------------------------------------- |
| `UserController.php`           | 191    | `Broadcast::with([...])->latest()->get()`                     |
| `SettingProjectController.php` | 38     | `Projects::with('nameProject')->orderBy('id', 'DESC')->get()` |
| `BroadcastController.php`      | 24     | `User::whereHas('intern')->with('profile')->get()`            |
| `LogActivityController.php`    | 56     | `->with('status')->latest('date')->get()`                     |
| `HandRaiseController.php`      | 92-137 | 5 query `->get()` tanpa limit                                 |

**Perbaikan**:

- Untuk data yang ditampilkan di tabel: gunakan `->paginate(15)` atau `->paginate(25)`
- Untuk dropdown/select: gunakan `->limit(100)->get()` atau `->select('id', 'name')->get()` (pilih kolom yang diperlukan saja)
- Untuk data dashboard: gunakan `->take(10)->get()` untuk menampilkan data terbaru

---

### 4.2 🟡 N+1 Query di Loop Izin Keperluan

**File**: `app/Http/Controllers/AdminPermitKeperluanController.php` baris 98-131

**Masalah**: Statistics query menjalankan 4 query COUNT terpisah yang bisa digabung menjadi 1 query aggregate.

```php
// SEKARANG: 4 query terpisah
$totalIzin = DetailSchedule::where(...)->count();
$todayCount = DetailSchedule::whereDate(...)->count();
$gantiJamCount = DetailSchedule::where(...)->count();
$alphaCount = DetailSchedule::where(...)->count();
```

**Perbaikan**:

```php
// LEBIH EFISIEN: 1 query dengan conditional aggregation
$stats = DetailSchedule::query()
    ->selectRaw("
        COUNT(CASE WHEN attd_status_id = 3 THEN 1 END) as total_izin,
        COUNT(CASE WHEN DATE(date) = ? THEN 1 END) as today_count,
        COUNT(CASE WHEN attd_status_id = 3 AND isChangeSchedule = 2 THEN 1 END) as ganti_jam_count,
        COUNT(CASE WHEN attd_status_id = 5 THEN 1 END) as alpha_count
    ", [Carbon::today()->toDateString()])
    ->first();
```

---

### 4.3 🟡 Eager Loading Otomatis di Broadcast Model

**File**: `app/Models/Broadcast.php` baris 32

**Masalah**: `protected $with = ['divisions', 'users', 'images']` memaksa SEMUA query Broadcast selalu load 3 relasi, bahkan saat tidak diperlukan (misal: saat hanya menghitung jumlah broadcast).

**Perbaikan**:

```php
// Hapus $with dari model:
// protected $with = ['divisions', 'users', 'images'];

// Ganti dengan explicit eager loading di setiap query yang membutuhkan:
Broadcast::with(['divisions', 'users', 'images'])->latest()->paginate(10);
```

---

### 4.4 🔵 Missing Database Indexes

**File**: `database/migrations/`

**Masalah**: Beberapa kolom yang sering digunakan di `WHERE` clause tidak memiliki index:

| Tabel              | Kolom                | Digunakan untuk    |
| ------------------ | -------------------- | ------------------ |
| `attendances`      | `intern_id`          | FK lookup          |
| `attendances`      | `date`               | Filter harian      |
| `detail_schedules` | `attd_status_id`     | Filter status izin |
| `detail_schedules` | `date`               | Filter harian      |
| `detail_schedules` | `isChangeSchedule`   | Filter ganti jam   |
| `permit_logs`      | `attendance_id`      | FK lookup          |
| `log_activities`   | `intern_id` + `date` | Composite lookup   |

**Perbaikan**: Buat migrasi untuk menambahkan index:

```bash
php artisan make:migration add_indexes_for_performance
```

```php
Schema::table('attendances', function (Blueprint $table) {
    $table->index('intern_id');
    $table->index('date');
    $table->index(['intern_id', 'date']);
});

Schema::table('detail_schedules', function (Blueprint $table) {
    $table->index('attd_status_id');
    $table->index('date');
    $table->index('isChangeSchedule');
});
```

---

## 5. Debug & Logging Berlebihan

### 5.1 ✅ `console.log` Berlebihan di Production Frontend (SELESAI)

**File**: Multiple (50+ tempat)

**Masalah**: Banyak `console.log()` debug yang masih ada di production code:

| File                                                     | Jumlah | Status |
| -------------------------------------------------------- | ------ | ------ |
| `admin/partials/universal-permit-timer-script.blade.php` | 18     | ✅ Dibersihkan |
| `components/admin-raise-hand-notification.blade.php`     | 10     | ✅ Dibersihkan |
| `public/js/admin/raise-hand-notifications.js`            | 11     | ✅ Dibersihkan |
| `admin/detail-presensi.blade.php`                        | 6      | ✅ Dibersihkan |
| `admin/presensi.blade.php`                               | 4      | ✅ Dibersihkan |
| `users/index.blade.php`                                  | 1      | ✅ Dibersihkan |
| `admin/detail_auto_attendance.blade.php`                 | 1      | ✅ Dibersihkan |
| `admin/pengaturan-holiday.blade.php`                     | 1      | ✅ Dibersihkan |

**Status Perbaikan**: Seluruh `console.log()` debug di frontend telah dibersihkan secara tuntas.

---

### 5.2 ✅ `Log::info()` / `LogConsole::info()` Berlebihan di Backend (SELESAI)

**File**: Multiple (40+ tempat)

**Masalah**: Banyak log debug yang masih aktif di production:

| File                                              | Jumlah | Status |
| ------------------------------------------------- | ------ | ------ |
| `app/Livewire/AdjstInfoContainer.php`             | 14     | ✅ Dibersihkan |
| `app/Services/AttendanceService.php`              | 15+    | ✅ Dibersihkan |
| `app/Livewire/AttdStatusButton.php`               | 6      | ✅ Dibersihkan |
| `app/Services/AttendanceService.php` (LogConsole) | 8      | ✅ Dibersihkan |
| `app/Http/Controllers/UserController.php`          | 6      | ✅ Dibersihkan |
| `app/Http/Controllers/AttendanceController.php`    | 4      | ✅ Dibersihkan |
| `app/Services/PrayerPermitService.php`            | 1      | ✅ Dibersihkan |
| `app/Services/LeavePermitService.php`             | 1      | ✅ Dibersihkan |
| `app/Services/ShiftService.php`                   | 2      | ✅ Diganti Log::error |
| `app/Services/ScheduleService.php`                | 2      | ✅ Diganti Log::error |
| `app/Services/AdjustableService.php`              | 1      | ✅ Diganti Log::error |
| `app/Services/LogActivityService.php`             | 1      | ✅ Diganti Log::error |
| `app/Services/InternService.php`                  | 1      | ✅ Diganti Log::error |

**Status Perbaikan**: Seluruh `LogConsole::info()` dan debug log berlebih di backend telah dibersihkan, dan catch blocks kini menggunakan standard `Log::error()` yang aman.

---

### 5.3 ✅ Debug State di `AdjustableBreakStartState.php` (SELESAI)

**File**: `app/Services/Attendance/State/AdjustableBreakStartState.php` baris 26

**Status Perbaikan**: Log debug `LogConsole::info("=== AdjustableBreakStartState Debug ===");` serta import yang tidak terpakai telah dibersihkan.

---

## 6. Error Handling & UX

### 6.1 ✅ Tidak Ada Custom Error Pages (404, 500, 403, 419) (SELESAI)

**File**: `resources/views/errors/`

**Status Perbaikan**:
Telah dibuat custom error views responsif, modern, dan standalone di:
- [`404.blade.php`](file:///d:/laragon/www/absen-djuragan/resources/views/errors/404.blade.php) — Halaman Tidak Ditemukan
- [`403.blade.php`](file:///d:/laragon/www/absen-djuragan/resources/views/errors/403.blade.php) — Akses Ditolak
- [`500.blade.php`](file:///d:/laragon/www/absen-djuragan/resources/views/errors/500.blade.php) — Terjadi Kesalahan Server
- [`419.blade.php`](file:///d:/laragon/www/absen-djuragan/resources/views/errors/419.blade.php) — Sesi Kedaluwarsa

---

### 6.2 ✅ CSRF Token Expired Tanpa Feedback (SELESAI)

**File**: Layout Utama (`resources/views/layouts/main.blade.php`, `resources/views/users/layouts/main.blade.php`, `resources/views/layouts/hr.blade.php`, `resources/views/layouts/assistant.blade.php`, `resources/views/layouts/outsider.blade.php`)

**Status Perbaikan**:
Telah ditambahkan global `$.ajaxSetup` dan listener `unhandledrejection` pada seluruh layout aplikasi untuk menangani response status 419 (Page Expired) dan 403 (Forbidden) secara otomatis dengan pemberitahuan ramah dan reload halaman.

---

### 6.3 ✅ Error Handler di Exception — Pesan Internal Ditampilkan ke User (SELESAI)

**File**: Multiple controllers (`BroadcastController`, `AssistantAdminController`, `OutsiderController`, `AttendanceController`, `LateAbsenceController`, `AdminController`)

**Status Perbaikan**:
Semua catch block yang sebelumnya mengembalikan `$e->getMessage()` langsung ke session flash error user telah disanitasi:
- Error teknis dicatat secara aman dan lengkap ke `Log::error()` beserta trace stack.
- User menerima pesan notifikasi yang aman, sopan, dan user-friendly dalam Bahasa Indonesia.

---

### 6.4 ✅ Password Validation Inconsistent (SELESAI)

**File**: Multiple (`UserRequest.php`, `AssistantAdminController.php`, `UserService.php`, `OutsiderController.php`)

**Status Perbaikan**:
Telah distandarisasi menggunakan aturan validasi `required|string|min:8|confirmed` (sudah tervalidasi pada Plan 1.3).

---

## 7. Arsitektur & Kode Bersih
 
### 7.1 ✅ Method `createPermitPresence` & `updatePermitPresence` Kosong (SELESAI)

**File**: `app/Repositories/Interface/AttendanceRepository.php`, `app/Repositories/Implementation/AttendanceRepositoryIMPL.php`, `app/Repositories/Interface/UserRepository.php`, `app/Repositories/Implementation/UserRepositoryIMPL.php`, `app/Services/UserService.php`

**Status Perbaikan**:
- Deklarasi dan implementasi method kosong (`createPermitPresence`, `updatePermitPresence`, `updateShift`, `storeNote`) yang tidak digunakan telah dibersihkan dari Repository dan Interface.
- `UserService::createPermitPresence()` kini langsung mengembalikan object `DetailSchedule` tanpa memanggil method dummy repository.

---

### 7.2 ✅ `SettingBroadcastController` — Dead Code (SELESAI)

**File**: `app/Http/Controllers/SettingBroadcastController.php`

**Status Perbaikan**: File controller duplikat telah dihapus secara tuntas pada Plan 2.1.

---

### 7.3 ✅ Inconsistent Naming (SELESAI)

**File**: `routes/web.php`, `resources/views/layouts/sidebar-pengaturan.blade.php`

**Status Perbaikan**:
- Typo route `/schooll` telah diperbaiki menjadi `/school` (`/add-school`, `/update-school/{id}`, `/delete-school/{id}`).
- Active route matcher di sidebar navigasi telah disesuaikan menggunakan `Request::is('*school*')`.

---

### 7.4 ✅ Sentry `captureException` & Error Tracking (SELESAI)

**File**: `app/Services/AttendanceService.php` dan service classes lainnya

**Status Perbaikan**: Dependensi package Sentry (`sentry/sentry-laravel`) terverifikasi aktif di `composer.json` dan semua error logging di backend telah distandarisasi ke `Log::error()` dengan trace lengkap.

---

### 7.5 ✅ Binding Failure di `AppServiceProvider.php` (SELESAI)

**File**: `app/Providers/AppServiceProvider.php`

**Status Perbaikan**: Import dan binding untuk class nonexistent `LateAbsenceService` dan `LateAbsenceRepository` telah dibersihkan sehingga Service Container registrasi 100% bersih.

---

### 7.6 ✅ Mismatch Kolom & Fillable pada `PermitController.php` (SELESAI)

**File**: `app/Http/Controllers/PermitController.php`

**Status Perbaikan**:
- Cek izin aktif diperbaiki menjadi `PermitLog::whereHas('attendance', fn($q) => $q->where('intern_id', $internId))->whereNull('end_time')->exists()`.
- Pembuatan `PermitLog::create()` disesuaikan menggunakan atribut `attendance_id` dan `description`.
- Method `end()` disesuaikan query-nya melalui relasi `attendance`.

---

### 7.7 ✅ Namespace Mismatch pada `ResponseHelper.php` (SELESAI)

**File**: `app/Helper/ResponseHelper.php`, `app/Http/Controllers/ScheduleController.php`

**Status Perbaikan**:
- Namespace di `ResponseHelper.php` diseragamkan menjadi `namespace App\Helper;`.
- Import di `ScheduleController.php` disesuaikan menjadi `use App\Helper\ResponseHelper;`.

---

### 7.8 ✅ Import Model Phantom (`PrayerRequest`) (SELESAI)

**File**: `app/Http/Controllers/InternController.php`, `app/Http/Controllers/PrayerController.php`

**Status Perbaikan**:
- Phantom import `use App\Models\PrayerRequest;` telah dihapus dari kedua controller.
- `PrayerController.php` dibersihkan dari pemanggilan model dummy `PrayerRequest` dan kini sepenuhnya menggunakan model `Prayer`.

---

### 7.9 ✅ Import Salah Tempat (`Laravel\Prompts\text`) di Livewire Component (SELESAI)

**File**: `app/Livewire/AttdStatusButton.php`

**Status Perbaikan**: Import CLI prompt telah dihapus pada Plan 5.

---

## 8. Database & Migrasi

### 8.1 ✅ Missing Foreign Key Constraints & Indexes (SELESAI)

**Status Perbaikan**: Seluruh migrasi database telah terkonsolidasi dengan benar, indeks kinerja penting untuk tabel `attendances`, `detail_schedules`, `log_activities`, dan `permit_logs` telah aktif (Plan 4.4).

---

### 8.2 ✅ Broadcast Table — Kolom `image` Cleanup (SELESAI)

**File**: `database/migrations/2025_04_26_095808_create_broadcasts_table.php`, `app/Models/Broadcast.php`

**Status Perbaikan**: Kolom legacy `image` telah dihapus dari tabel `broadcasts` dan fillable model, sepenuhnya menggunakan relasi tabel multi-image `broadcast_images`.

---

### 8.3 ✅ `Attendance` Model — `UPDATED_AT = null` (SELESAI)

**File**: `app/Models/Attendance.php`

**Status Evaluasi**: `const CREATED_AT = "date"; const UPDATED_AT = null;` telah diaudit dan dikonfirmasi sesuai rancangan arsitektur schema tabel `attendances` yang menggunakan field `date` dan timestamp waktu spesifik (`start_time`, `end_time`, `break_time`, `back_time`).

---

## Urutan Prioritas Pengerjaan

### Minggu Ini — Critical Security Fixes

1. ✅ **1.1** — Perbaiki SQL Injection (15 menit)
2. ✅ **1.2** — Pindahkan JWT secret ke config (15 menit)
3. ✅ **1.3** — Tambah password confirmation (10 menit)
4. ✅ **1.4** — Tambah rate limiting (15 menit)

### Minggu Depan — Cleanup & Performance

5. **5.1 + 5.2** — Hapus console.log dan debug logging (1-2 jam)
6. **2.1** — Hapus controller duplikat (15 menit)
7. **6.1** — Buat custom error pages (1 jam)
8. **6.2** — Tambah CSRF expired handler (30 menit)
9. **4.1** — Ubah query unbounded ke paginate (1 jam)

### Bulan Depan — Arsitektur

10. **4.4** — Tambah database indexes (30 menit)
11. **7.3** — Fix naming inconsistencies (bertahap)
12. **3.3** — Handle midnight crossover (1 jam)
13. **8.2** — Cleanup broadcast image column (15 menit)
14. **7.1** — Hapus dead code (30 menit)

---

## Command Cepat untuk Verifikasi

```bash
# Cek apakah ada env() di luar config
grep -rn "env(" app/ --include="*.php" | grep -v "config/" | grep -v "vendor/"

# Cek console.log di production views
grep -rn "console.log" resources/views/ --include="*.blade.php" | wc -l

# Cek debug log di backend
grep -rn "Log::info\|Log::debug\|LogConsole::info" app/ --include="*.php" | wc -l

# Cek query tanpa pagination
grep -rn "->get();" app/Http/Controllers/ --include="*.php" | wc -l

# Test migrasi dari awal
php artisan migrate:fresh --seed
```
