# Rencana Integrasi Registrasi Pemagang & Akun Terpusat via REST API
## Absen Djuragan (Core System) ⟷ Internship Management System SevenInc

Dokumen ini adalah panduan arsitektur dan teknis untuk menghubungkan proses **Pendaftaran (Register)** antara **Internship Management System** dan **Absen Djuragan** menggunakan **REST API**.

---

## 1. Prinsip Utama & Batasan Arsitektur (*Core Principles & Constraints*)

Sesuai arahan teknis, arsitektur ini dibangun dengan aturan mutlak sebagai berikut:

1. **Role "user" di Web Intern Dibiarkan Tetap Ada:**
   - Struktur database, kolom ENUM role (`admin`, `pemagang`, `user`), serta logika hak akses di web internship **tidak diubah dan tidak dihapus**.
2. **Logic Penyimpanan Lokal Database Intern 100% Dipertahankan:**
   - **TIDAK ADA** logika penyimpanan database `internship_manage` yang dibongkar atau diubah.
   - Semua alur registrasi yang sudah ada (menyimpan record `users` lokal, tabel `internship_registrations`, penyimpanan dokumen/foto, relasi kota/institusi, dll.) **tetap berjalan persis seperti kode aslinya**.
3. **Penambahan Hook API Sinkronisasi di Bagian Register:**
   - Saat calon pemagang menekan tombol daftar/submit di Web Intern, data pendaftaran yang berhasil disimpan secara lokal akan **dikirimkan juga ke endpoint API Absen Djuragan**.
4. **Tujuan Utama: Kemudahan Login Bergantian (Shared Credentials):**
   - Pemagang dan admin dapat langsung login ke **Web Internship** maupun ke **Absen Djuragan** secara bergantian menggunakan email/username dan kata sandi (password) yang sama persis, tanpa perlu mendaftar ulang di dua tempat.

---

## 2. Diagram Alur Registrasi Terpadu (Registration Pipeline)

```
[ Calon Pemagang Mengisi Formulir Register di Web Intern ]
                            │
                            ▼
[ 1. Logic Asli Web Intern Berjalan (TIDAK DIUBAH) ]
  ├── Simpan ke database lokal: internship_manage.users
  ├── Simpan ke: internship_manage.internship_registrations
  └── Upload berkas / simpan session lokal seperti biasa
                            │
                            ▼ (Data lokal sukses tersimpan)
[ 2. API Hook: Kirim Payload Registrasi ke Absen Djuragan ]
  POST /api/v1/external/register-sync
  Header: X-API-KEY: {SECRET_TOKEN}
                            │
                            ▼
[ 3. Absen Djuragan Memproses & Menyimpan Data Master ]
  ├── DB Transaction:
  │   ├── A. users (email, username, password bcrypt, role_id: 3, is_active: 1)
  │   ├── B. profiles (full_name, phone, gender, date_of_birth, address)
  │   └── C. interns (school_id, division_id, brand_id, start_date, end_date)
  └── Response JSON: { success: true, absen_user_id: 150, username: "naufal.ramadhan" }
                            │
                            ▼
[ 4. Selesai! ]
  - Pendaftaran lokal sukses 100%
  - Akun di Absen Djuragan langsung aktif
  - Pemagang & Admin bisa login ke kedua web menggunakan email & password yang sama!
```

---

## 3. Pemetaan Data Registrasi (Field Mapping)

Ketika form register disubmit di Web Intern, data dipetakan ke database Absen Djuragan sebagai berikut:

| Data Pendaftar | Field di Web Intern (`internship_manage`) | Target di Absen Djuragan (`absen_djuragan`) | Keterangan Pemetaan |
| :--- | :--- | :--- | :--- |
| **Email** | `users.email` / `registrations.email` | `users.email` | Kunci unik akun login bersama. |
| **Password** | `password` (input raw pendaftar) | `users.password` | Dihash menggunakan `Hash::make()` / `bcrypt` di Absen Djuragan sehingga user bisa langsung login di Absen. |
| **Username** | *Dihasilkan otomatis* | `users.username` | Dibuat otomatis dari nama/email (misal: `fajar.wulandari`). Pemagang bisa login menggunakan email atau username ini. |
| **Role User** | `users.role = 'user'` / `'pemagang'` | `users.role_id = 3` | Di Absen Djuragan langsung diberikan `role_id: 3` (Pemagang). |
| **Status Akun** | Status pendaftaran lokal | `users.is_active = 1`, `is_confirm = 1` | Akun langsung berstatus aktif dan terkonfirmasi di Absen. |
| **Nama Lengkap** | `fullname` | `profiles.full_name` | Disimpan di tabel profil Absen Djuragan. |
| **Nomor HP / WA**| `phone_number` | `profiles.phone` & `whatsapp_numbers` | Dinormalisasi awalan `0` / `62`. |
| **Tanggal Lahir**| `born_date` | `profiles.date_of_birth` | Format `YYYY-MM-DD`. |
| **Jenis Kelamin**| `gender` ('Laki-laki' / 'Perempuan') | `profiles.gender` ('L' / 'P') | Dikonversi sesuai format ENUM Absen. |
| **Institusi** | `institution_id` / nama kampus | `interns.school_id` | Dicocokkan dengan nama sekolah/kampus di Absen Djuragan (jika belum ada, otomatis ditambahkan). |
| **NIM / NISN** | `student_id` | `interns.nim` | Disimpan di data magang Absen Djuragan. |
| **Divisi** | `division_id` / nama divisi | `interns.division_id` | Dicocokkan berdasarkan nama divisi di Absen. |
| **Brand** | `brand_id` / nama brand | `interns.brand_id` | Dicocokkan berdasarkan nama/slug brand di Absen. |
| **Tgl Magang** | `start_date` & `end_date` | `interns.start_date` & `interns.end_date`| Tanggal mulai dan berakhir magang. |
| **Shift Magang** | *Belum ada di Web Intern* | `interns.shift_id` | Diberikan default shift (misal: Shift Pagi ID = 1) dan dapat diubah admin nanti. |

---

## 4. Spesifikasi Endpoint API di Absen Djuragan

Absen Djuragan bertindak sebagai **API Provider**. Route didaftarkan di `routes/api.php` dan diproteksi middleware `ExternalApiAuthMiddleware`.

### Endpoint: `POST /api/v1/external/register-sync`

#### Headers:
```http
X-API-KEY: djuragan_ext_secret_key_xxxxxx
Content-Type: application/json
Accept: application/json
```

#### Request Body (JSON):
```json
{
  "email": "naufal.ramadhan@gmail.com",
  "password": "PasswordRahasia123!",
  "full_name": "Naufal Ramadhan",
  "phone": "081234567890",
  "parent_whatsapp_number": "081298765432",
  "gender": "Laki-laki",
  "born_date": "2003-08-17",
  "birth_place": "Yogyakarta",
  "address": "Jl. Kaliurang KM 9, Sleman",
  "school_name": "Universitas Gadjah Mada",
  "student_id": "21/478912/SV/19823",
  "division_name": "Web Developer",
  "brand_name": "Djuragan Kreatif",
  "start_date": "2026-10-15",
  "end_date": "2027-02-15"
}
```

#### Response Success (`201 Created`):
```json
{
  "success": true,
  "message": "Akun dan data pemagang berhasil disinkronkan ke Absen Djuragan.",
  "data": {
    "absen_user_id": 156,
    "username": "naufal.ramadhan",
    "email": "naufal.ramadhan@gmail.com",
    "intern_id": 99,
    "is_active": true
  }
}
```

#### Response Jika Email Sudah Terdaftar (`200 OK` / Update Existing):
Jika email sudah pernah ada di Absen Djuragan, sistem akan memperbarui profil dan password tanpa membuat akun duplikat.

---

## 5. Implementasi Hook di Controller Register Web Intern

Di sisi Web Intern, kode registrasi asli **tetap dipertahankan 100%**. Kita hanya menambahkan pemanggilan API sederhana di akhir proses penyimpanan:

```php
// =========================================================================
// 1. LOGIC ASLI WEB INTERN (JANGAN DIUBAH SAMA SEKALI)
// =========================================================================
$user = User::create([
    'name'     => $request->fullname,
    'email'    => $request->email,
    'password' => Hash::make($request->password),
    'role'     => 'user', // Role user lokal tetap berjalan normal
]);

$registration = InternshipRegistration::create([
    'user_id'      => $user->id,
    'fullname'     => $request->fullname,
    // ... semua kolom pendaftaran lokal tetap disimpan normal ...
]);

// =========================================================================
// 2. TAMBAHAN HOOK: SINKRONKAN KE ABSEN DJURAGAN (DENGAN TRY-CATCH AMAN)
// =========================================================================
try {
    $absenApiUrl = config('services.absen_djuragan.url') . '/api/v1/external/register-sync';
    $apiKey      = config('services.absen_djuragan.api_key');

    $response = Http::timeout(5)->withHeaders([
        'X-API-KEY' => $apiKey,
        'Accept'    => 'application/json',
    ])->post($absenApiUrl, [
        'email'         => $request->email,
        'password'      => $request->password, // Password mentah agar dihash seragam di Absen
        'full_name'     => $request->fullname,
        'phone'         => $request->phone_number,
        'gender'        => $request->gender,
        'born_date'     => $request->born_date,
        'school_name'   => $request->institution_name ?? null,
        'student_id'    => $request->student_id ?? null,
        'division_name' => $request->division_name ?? null,
        'brand_name'    => $request->brand_name ?? null,
        'start_date'    => $request->start_date ?? null,
        'end_date'      => $request->end_date ?? null,
    ]);

    if ($response->successful()) {
        $resData = $response->json();
        // Opsional: simpan referensi ID absen jika kolom tersedia
        if (isset($resData['data']['absen_user_id']) && Schema::hasColumn('internship_registrations', 'absen_user_id')) {
            $registration->update(['absen_user_id' => $resData['data']['absen_user_id']]);
        }
    } else {
        Log::warning('Gagal sinkronisasi registrasi ke Absen Djuragan: ' . $response->body());
    }
} catch (\Throwable $e) {
    // Keamanan: Jika koneksi API Absen error, pendaftaran lokal di Web Intern
    // TETAP SUKSES dan tidak akan membatalkan proses registrasi user!
    Log::error('Error API sinkronisasi registrasi ke Absen: ' . $e->getMessage());
}

// Lanjutkan return redirect / response sukses seperti aslinya
return redirect()->route('registration.success');
```

---

## 6. Prinsip Keamanan & Ketahanan Sistem (*Fault Tolerance*)

1. **Prinsip Non-Blocking (Pendaftaran Lokal Tidak Boleh Batal):**
   - Pemanggilan API ke Absen dibungkus dengan blok `try-catch` dan timeout singkat (5 detik).
   - Jika suatu saat Absen Djuragan sedang maintenance atau restart, **pendaftaran di Web Intern tetap berjalan sukses 100% tanpa error bagi calon pemagang**.
2. **Kerahasiaan Kunci API:**
   - Komunikasi antar-sistem diamankan menggunakan `X-API-KEY` yang hanya diketahui oleh kedua aplikasi melalui file `.env`.
3. **Login Bergantian yang Mulus:**
   - Karena password yang dimasukkan user saat mendaftar dikirim secara aman dan dihash dengan standar `bcrypt` yang sama di Absen Djuragan, user bisa langsung login ke:
     - Web Absen Djuragan: menggunakan email & password tersebut.
     - Web Internship: menggunakan email & password yang sama.

---

## 7. Rencana Tahapan Eksekusi

### Langkah 1: Sisi Absen Djuragan
1. Buat Middleware `ExternalApiAuthMiddleware` di `app/Http/Middleware/ExternalApiAuthMiddleware.php`.
2. Buat Controller `app/Http/Controllers/Api/ExternalRegistrationController.php` dengan method `registerSync(Request $request)`.
3. Daftarkan route di `routes/api.php`:
   `Route::post('/external/register-sync', [ExternalRegistrationController::class, 'registerSync'])->middleware('external.api.auth');`
4. Tambahkan konfigurasi `EXTERNAL_API_KEY` di file `.env` Absen Djuragan.

### Langkah 2: Sisi Internship Management System
1. Daftarkan konfigurasi endpoint dan API key di `config/services.php` dan file `.env`.
2. Tambahkan pemanggilan `Http::post(...)` ke API Absen pada controller yang menangani penyimpanan registrasi/pendaftaran magang.
3. Jalankan uji coba pendaftaran untuk memastikan data masuk secara mulus di kedua database.
