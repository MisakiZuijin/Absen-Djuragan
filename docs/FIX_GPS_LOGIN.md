# FIX: GPS Location Validation & Login Session Bugs

> **Tanggal**: 2026-09-23  
> **Status**: ✅ Fixed  
> **Severity**: Critical

---

## Daftar Bug & Perbaikan

### 🔴 A. Bug Login Session (Double Login)

#### A1. Session Tidak Di-regenerate Saat Login — CRITICAL
**File**: `app/Http/Controllers/AuthController.php`

**Masalah**: Setelah `Auth::login($user)`, `$request->session()->regenerate()` tidak pernah dipanggil. Akibatnya session ID lama tetap digunakan, menyebabkan konflik saat session sebelumnya sudah expire.

**Fix**: Tambah `$request->session()->regenerate()` di `loginAction()` setelah login berhasil.

```php
if ($user->isSuccess()) {
    $request->session()->regenerate(); // ← DITAMBAHKAN
    $data = $user->getData();
    // ...
}
```

---

#### A2. Session Tidak Di-invalidate Saat Logout — CRITICAL
**File**: `app/Http/Controllers/AuthController.php`

**Masalah**: `logoutAction()` hanya memanggil `Auth::logout()` tanpa `session()->invalidate()` dan `session()->regenerateToken()`. Session file dan CSRF token lama tetap ada di browser.

**Fix**: Tambah invalidasi session dan regenerasi CSRF token:

```php
public function logoutAction(Request $request) {
    $this->userService->logout();

    $request->session()->invalidate();      // ← DITAMBAHKAN
    $request->session()->regenerateToken(); // ← DITAMBAHKAN

    return redirect()->route("login.view")->with('success', 'Anda berhasil keluar halaman.');
}
```

---

#### A3. Middleware Authenticate Redirect ke POST Route — CRITICAL
**File**: `app/Http/Middleware/Authenticate.php`

**Masalah**: `redirectTo()` menggunakan `route('login')` yang ternyata adalah POST route (`Route::post('/login', ...)`). Ketika middleware mencoba redirect user yang belum login, akan menghasilkan error 405 Method Not Allowed.

**Fix**: Ganti `route('login')` → `route('login.view')`:

```php
protected function redirectTo(Request $request): ?string {
    return $request->expectsJson() ? null : route('login.view'); // ← DIPERBAIKI
}
```

---

#### A4. Ghost Route `POST /login` — HIGH
**File**: `routes/web.php`

**Masalah**: `Route::post('/login', [AuthController::class, 'login'])->name('login')` mengarah ke method `login()` yang **tidak ada** di `AuthController`. Login form sebenarnya POST ke `/loginAction` (route `login.action`).

**Fix**: Route dihapus/dinonaktifkan karena tidak berfungsi.

---

#### A5. RouteServiceProvider::HOME = '/home' (404) — HIGH
**File**: `app/Providers/RouteServiceProvider.php`

**Masalah**: `HOME = '/home'` tapi route `/home` tidak ada. Yang ada: `/user/home` dan `/admin/home`.

**Fix**: Ganti ke `HOME = '/'`.

---

#### A6. RedirectIfAuthenticated Tidak Role-Aware — HIGH
**File**: `app/Http/Middleware/RedirectIfAuthenticated.php`

**Masalah**: Middleware redirect semua authenticated user ke `RouteServiceProvider::HOME` tanpa mempertimbangkan role.

**Fix**: Redirect berdasarkan `role_id` user:
- Role 1 (Admin) → `/admin/home`
- Role 3 (Pemagang) → `/user/home`
- Role 5 (Outsider) → `/outsider`
- Role 6 (Assistant) → `/assistant/dashboard`

---

### 🔴 B. Bug GPS / Validasi Lokasi Absen Masuk

#### B1. Geofence Bypass Saat Tidak Ada detailScheduleId — CRITICAL
**File**: `app/Services/Attendance/State/AttendanceInState.php`

**Masalah**: Fungsi `checkIsInOfficeArea()` dipanggil, tapi hasilnya (`$mapsTrack->isInArea`) **TIDAK dicek** sebelum membuat attendance saat `!$detailScheduleId`. User bisa absen masuk dari mana saja di dunia.

**Fix**: Pindahkan pengecekan `$mapsTrack->isInArea` ke **awal** method `handle()`, sebelum semua branch logic. Jika user di luar area → langsung return error.

```php
$mapsTrack = $this->checkIsInOfficeArea((float) $latitude, (float) $longitude);

// Validasi area kantor HARUS dilakukan di semua jalur
if ($mapsTrack->isInArea == false) {
    return new ActionResult(false, "Kamu tidak di Area Kantor manapun");
}
```

---

#### B2. Crash TypeError Jika GPS Koordinat Null — HIGH
**File**: `app/Services/Attendance/State/AttendanceInState.php`

**Masalah**: `checkIsInOfficeArea(float $latitude, float $longitude)` menggunakan type hint `float` non-nullable. Jika frontend mengirim `null` (misalnya GPS tidak tersedia), PHP 8 crash dengan `TypeError`.

**Fix**: Tambah null check di awal `handle()`:

```php
$latitude = $data->getLatitude();
$longitude = $data->getLongitude();

if (is_null($latitude) || is_null($longitude)) {
    return new ActionResult(false, "Gagal mendapatkan lokasi GPS. Pastikan GPS aktif dan izinkan akses lokasi.", null);
}
```

---

#### B3. Tombol "Masuk" Tidak Ada di Mobile View — HIGH
**File**: `resources/views/livewire/attd-status-button.blade.php`

**Masalah**: Tombol Masuk hanya ada di container desktop (`hidden md:flex`). Di container mobile (`md:hidden floating-btn-container`), tombol Masuk **tidak ada**. User HP tidak bisa absen masuk.

**Fix**: Tambahkan tombol Masuk di container mobile, tepat sebelum tombol Ganti Jam.

---

#### B4. Method findById Tidak Ada di DetailScheduleRepository — HIGH
**File**: `app/Services/AttendanceService.php`

**Masalah**: Di `AttendanceService::attendanceAction()`, pemanggilan `$this->detailScheduleRepository->findById()` menyebabkan error karena method di repository adalah `find()`.

**Fix**: Ganti `$this->detailScheduleRepository->findById()` → `$this->detailScheduleRepository->find()`.

---

#### B5. Method getDetailScheduleId() Tidak Ada di AttendanceDTO — HIGH
**File**: `app/DTO/AttendanceDTO.php`

**Masalah**: Getter di `AttendanceDTO` sebelumnya bernama `getDetailSchedule()`, sedangkan `AttendanceService` memanggil `getDetailScheduleId()`, menyebabkan fatal error.

**Fix**: Tambahkan method `getDetailScheduleId()` di `AttendanceDTO`.

---

## Catatan Penting

### Tentang `is_gps_activate`
Validasi GPS hanya aktif jika `users.is_gps_activate == 1` per user. **Default-nya adalah `0` (nonaktif)**. Artinya user baru otomatis tidak divalidasi lokasinya.

Ini adalah **desain yang disengaja** agar admin bisa mengaktifkan GPS per user melalui panel admin. Pastikan admin mengaktifkan `is_gps_activate` untuk semua user WFO yang perlu divalidasi lokasinya.

### Tentang Bounding Box vs Haversine
Sistem menggunakan **Bounding Box (kotak)** bukan **Haversine (lingkaran)** untuk validasi area. Ini berarti di pojok kotak, user bisa ~41% lebih jauh dari radius yang diinginkan (misal: radius 25m, pojok kotak membolehkan ~35m).

Untuk kebanyakan kasus penggunaan, ini cukup akurat. Jika dibutuhkan presisi lebih, bisa diupgrade ke Haversine formula di kemudian hari.

### Tentang Validasi Kantor
User bisa absen di **kantor manapun** yang terdaftar di sistem, tidak harus kantor yang ditugaskan. Ini adalah kebijakan yang disengaja.

---

## Files Modified

| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/AuthController.php` | Session regenerate & invalidate |
| `app/Http/Middleware/Authenticate.php` | Redirect ke `login.view` |
| `app/Http/Middleware/RedirectIfAuthenticated.php` | Role-based redirect |
| `app/Providers/RouteServiceProvider.php` | HOME = '/' |
| `routes/web.php` | Hapus ghost route POST /login |
| `app/Services/Attendance/State/AttendanceInState.php` | GPS null check + geofence fix |
| `app/Services/AttendanceService.php` | Fix `findById` → `find` & logging |
| `app/DTO/AttendanceDTO.php` | Tambah getter `getDetailScheduleId()` |
| `resources/views/livewire/attd-status-button.blade.php` | Tombol Masuk mobile |

