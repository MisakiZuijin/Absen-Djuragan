# Rencana Integrasi Cross-API V2: Web Intern ⟷ Absen Djuragan
## Internship Management System SevenInc ⟷ Absen Djuragan (Presensi & Operasional)

Dokumen ini memuat spesifikasi arsitektur, batasan domain sistem (*separation of concerns*), pemetaan data, dan rencana implementasi integrasi Cross-API **Versi 2 (V2)** antara **Internship Management System SevenInc (Web Intern)** dan **Absen Djuragan (Web Absen)**.

---

## 1. Prinsip Utama & Batasan Domain Sistem (*Core Architectural Boundaries*)

Berdasarkan kesepakatan bisnis dan arsitektur sistem, pembagian tanggung jawab antara kedua aplikasi ditetapkan secara tegas:

1. **Shift Kerja & Tipe Kerja 100% Ranah Web Absen:**
   - Pemilihan **Shift Kerja** (Shift Pagi, Siang, Middle, Rolling, dsb.) dan **Tipe Kerja** (*Work Placement Type*: WFO, WFH, Hybrid, Penempatan Kantor Fisik / Geofencing) **TIDAK digabungkan ke Web Intern**.
   - Pengaturan ini sepenuhnya menjadi wewenang operasional HR / Admin di **Web Absen**.
   - Web Intern tidak menyediakan form/opsi shift maupun tipe kerja agar alur pendaftaran dan seleksi tetap fokus, bersih, dan ringkas.
2. **Alamat Domisili Dikecualikan dari Sinkronisasi Otomatis:**
   - Data alamat domisili atau kota asal dari form pendaftaran Web Intern **tidak perlu disimpan/disinkronkan otomatis** ke Web Absen.
   - Hal ini karena kebutuhan alamat di absensi bersifat opsional dan jika diperlukan di kemudian hari akan disetting langsung secara manual oleh Admin di Web Absen.
3. **Brand & Tanggal Magang Ditetapkan di Web Intern:**
   - **Brand Penempatan** (Seven Inc, Area Kerja, PT KMN, Tipis Ini, dsb.) ditentukan oleh Admin Web Intern melalui pop-up modal saat menerima pendaftar (*Accepted*).
   - **Tanggal Mulai (`start_date`) dan Selesai (`end_date`)** diverifikasi dan dikunci oleh Admin Web Intern, lalu menjadi acuan periode aktif magang dan batas pembuatan jadwal presensi di Web Absen.
4. **Proteksi Konfigurasi Absen yang Sudah Ada (*No Overwrite on Existing*):**
   - Jika data pemagang sudah ada di Web Absen dan admin Web Absen telah mengubah shift (misal ke Shift Siang) atau tipe kerja, sinkronisasi pembaruan dari Web Intern **dilarang menimpa** `shift_id`, `work_placement_type`, maupun `locked_office_id`.

---

## 2. Diagram Alur Integrasi Cross-API V2

```
[ Web Intern: Calon Pemagang ]
         │
         ▼
[ Pendaftaran & Verifikasi Berkas ]
         │
         ▼
[ Admin Web Intern Meninjau & Menerima (ACC) ]
   ├── 1. Pilih Brand Penempatan (Modal Popup Brand)
   ├── 2. Verifikasi Tanggal Magang (start_date & end_date)
   ├── 3. Ubah Status ke "accepted" / "active"
   └── 4. Terbitkan LOA & Kirim Email Penerimaan
         │
         ▼ (Trigger Otomatis Single & Bulk ACC)
[ API Client: AbsenDjuraganSyncService ]
   POST /api/v1/external/register-sync
   Header: X-API-KEY: {SECRET_TOKEN}
         │
         ▼
[ Web Absen: ExternalRegistrationController (Receiver) ]
   ├── A. Identifikasi User via Email:
   │      - User Baru  : Buat User (role_id: 3, is_active: 1) + Set Password Hash
   │      - User Lama  : Update password/profil jika perlu, PERTAHANKAN status aktif
   ├── B. Simpan Profil:
   │      - full_name, phone, gender, date_of_birth, birth_place, religion
   │      - (alamat TIDAK diisi otomatis / dipertahankan)
   ├── C. Resolusi Master Data:
   │      - Sekolah/Kampus (School) via student_id & school_name
   │      - Divisi (Division) via division_name / division_id
   │      - Brand (Brand) via brand_name / brand_id
   ├── D. Operasional Magang (Tabel interns):
   │      - start_date & end_date (dari Web Intern)
   │      - Jika BARU   : Set default Shift Pagi & auto_office
   │      - Jika SUDAH ADA: PERTAHANKAN shift_id & work_placement_type yang sudah diset di Absen!
   ├── E. Nomor WA Orang Tua/Pembimbing (Tabel whatsapp_numbers):
   │      - Simpan nomor untuk broadcast otomatis (izin/alpha)
   └── F. Respon Balik:
          { success: true, absen_user_id: 101, intern_id: 88, username: "..." }
         │
         ▼
[ Web Intern Menyimpan Ref ID (absen_user_id) ]
```

---

## 3. Spesifikasi Data yang Diambil & Disinkronkan

### A. Dari Form Pendaftaran Web Intern (Data Calon Pemagang):
| Data | Field Web Intern | Tujuan di Web Absen | Rationale / Kegunaan |
| :--- | :--- | :--- | :--- |
| **Kredensial Login** | `email`, `password` (hash) | `users.email`, `users.password` | Shared login: pemagang langsung login dengan akun yang sama di kedua web. |
| **Nama Lengkap** | `fullname` | `profiles.full_name` | Identitas resmi di web presensi dan kartu absen. |
| **No. WhatsApp** | `phone_number` | `profiles.phone` | Kontak darurat dan verifikasi login/OTP. |
| **Tempat & Tgl Lahir** | `birth_place`, `born_date` | `profiles.birth_place`, `profiles.date_of_birth` | Arsip data pemagang di Web Absen. |
| **Jenis Kelamin** | `gender` | `profiles.gender` | Filter absensi dan pengelompokan. |
| **Agama** | `religion` | `profiles.religion` | **Kritis:** Digunakan Web Absen untuk jadwal istirahat Sholat Jumat otomatis pria muslim. |
| **Data Akademik** | `student_id`, `institution_name` | `interns.nim`, `schools.name` | Pencatatan asal institusi dan nomor induk siswa/mahasiswa. |
| **Kontak Ortu / Pembimbing** | `supervisor_contact` | `whatsapp_numbers.phone_number` | Nomor tujuan broadcast WhatsApp otomatis saat pemagang izin, sakit, atau alpha. |
| **Divisi** | `division_id` / nama divisi | `interns.division_id` | Penempatan tugas dan aturan hari kerja per divisi. |

### B. Dari Sisi Penerimaan Admin Web Intern:
| Data | Sumber di Web Intern | Tujuan di Web Absen | Rationale / Kegunaan |
| :--- | :--- | :--- | :--- |
| **Brand Perusahaan** | Modal Brand saat ACC (`brand`) | `interns.brand_id` | Penempatan pemagang pada unit bisnis terkait. |
| **Tanggal Magang Pasti** | `start_date` & `end_date` | `interns.start_date`, `interns.end_date` | Periode aktif pemagang dan batas otomatis pembuatan jadwal presensi. |
| **Status Magang** | `internship_status` (`accepted`, `active`, `completed`, `exited`) | `users.is_active` | Mengaktifkan saat diterima, dan menonaktifkan akun saat keluar/selesai. |

### C. Data yang Dikecualikan (TIDAK Diambil / Dikelola Terpisah):
* **Alamat Domisili (`current_city` / `boarding_info`):** Tidak disinkronkan otomatis; dikelola manual oleh admin Web Absen jika diperlukan.
* **Shift Kerja (`shift_id`):** Dikelola 100% di Web Absen.
* **Tipe Kerja (`work_placement_type` - WFO/WFH/Hybrid):** Dikelola 100% di Web Absen.
* **Kantor Fisik (`locked_office_id` / Geofence GPS):** Dikelola 100% di Web Absen.

---

## 4. Rincian Masalah yang Ditemukan & Rencana Penyesuaian

### 4.1. Proteksi Shift & Tipe Kerja di Web Absen (`ExternalRegistrationController.php`)
* **Kondisi Bermasalah:**
  Saat ini kode selalu menetapkan `$effectiveShiftId = $shiftId ?: ...` di mana `$shiftId` selalu mengambil Shift Pagi. Akibatnya, pemagang yang sudah diubah shift-nya menjadi Shift Siang oleh admin Absen akan ter-reset kembali ke Shift Pagi saat menerima re-sync dari Web Intern.
* **Perbaikan di V2:**
  Logika resolusi shift dan work placement type diubah menjadi:
  ```php
  // Jika pemagang sudah ada, prioritaskan nilai yang SUDAH diatur di Absen
  $effectiveShiftId = $existingIntern?->shift_id ?: ($shiftId ?: null);
  $effectiveWorkPlacement = $existingIntern?->work_placement_type ?: 'auto_office';
  $effectiveHybridGroup = $existingIntern?->hybrid_group ?: 'auto';
  $effectiveLockedOffice = $existingIntern?->locked_office_id ?: null;
  ```

### 4.2. Perbaikan Celah pada "Bulk Update Status" di Web Intern (`InternController.php`)
* **Kondisi Bermasalah:**
  Pada `bulkUpdateStatus()`, saat admin mencentang banyak pemagang dan mengubah status menjadi `accepted`, fungsi `AbsenDjuraganSyncService::syncIntern()` belum dipanggil sama sekali. Pendaftar yang di-ACC massal tidak pernah terkirim ke Web Absen.
* **Perbaikan di V2:**
  Tambahkan pemanggilan sinkronisasi di dalam perulangan `bulkUpdateStatus()`:
  ```php
  if ($old !== IR::STATUS_ACCEPTED && $intern->internship_status === IR::STATUS_ACCEPTED) {
      // Sinkronkan ke Web Absen
      \App\Services\AbsenDjuraganSyncService::syncIntern($intern, $intern->brand);
  }
  ```

### 4.3. Sinkronisasi Ulang saat Edit Pasca-ACC (`InternController::update()`)
* **Kondisi Bermasalah:**
  Jika admin mengubah tanggal magang (`start_date`, `end_date`), divisi, atau brand pemagang melalui modal Edit setelah pemagang berstatus `accepted` atau `active`, perubahan tersebut tidak dikirim ke Web Absen sehingga jadwal presensi di Web Absen tetap menggunakan tanggal lama.
* **Perbaikan di V2:**
  Tambahkan trigger sinkronisasi pada `InternController::update()`:
  ```php
  if (in_array($intern->internship_status, [IR::STATUS_ACCEPTED, IR::STATUS_ACTIVE])) {
      \App\Services\AbsenDjuraganSyncService::syncIntern($intern, $intern->brand);
  }
  ```

### 4.4. Penanganan Status Selesai / Keluar (`completed` & `exited`)
* **Kondisi Bermasalah:**
  Saat pemagang keluar (`exited`) atau selesai magang (`completed`), akun di Web Absen tetap aktif (`is_active = true`), sehingga sistem Web Absen terus membuat jadwal harian dan mencatat Alpha serta mengirim notifikasi ke orang tua.
* **Perbaikan di V2:**
  Kirim payload status ke Web Absen. Jika status yang dikirim adalah `exited` atau `completed`:
  ```php
  if (in_array($status, ['exited', 'completed', 'rejected'])) {
      $user->is_active = false;
      $user->save();
  }
  ```

---

## 5. Kontrak Payload REST API V2

### Endpoint:
`POST /api/v1/external/register-sync`

### Headers:
```http
X-API-KEY: {ABSEN_DJURAGAN_API_KEY}
Content-Type: application/json
Accept: application/json
```

### JSON Request Payload:
```json
{
  "email": "budi.santoso@example.com",
  "password_hash": "$2y$10$e0MYzXyjpJS7Pd0RVvHwHeF...",
  "full_name": "Budi Santoso",
  "phone": "081234567890",
  "parent_whatsapp_number": "081987654321",
  "gender": "Laki-laki",
  "born_date": "2004-05-15",
  "birth_place": "Yogyakarta",
  "religion": "Islam",
  "school_name": "Universitas Gadjah Mada",
  "student_id": "21/475123/TK/52100",
  "division_name": "Programmer",
  "division_id": 4,
  "brand_name": "Seven Inc",
  "brand_id": 1,
  "start_date": "2026-11-01",
  "end_date": "2027-04-30",
  "status": "accepted"
}
```

### JSON Response (Success 200/201):
```json
{
  "success": true,
  "message": "Akun dan data pemagang berhasil disinkronkan ke Absen Djuragan.",
  "data": {
    "absen_user_id": 142,
    "username": "budi.santoso",
    "email": "budi.santoso@example.com",
    "full_name": "Budi Santoso",
    "intern_id": 95,
    "division_id": 4,
    "brand_id": 1,
    "school_id": 12,
    "shift_id": 1,
    "is_active": true
  }
}
```

---

## 6. Rencana Tahapan Eksekusi (*Execution Steps*)

1. **Tahap 1: Penyesuaian Endpoint Receiver di Web Absen (`absen-djuragan`)**
   - Modifikasi `app/Http/Controllers/Api/ExternalRegistrationController.php`:
     - Tambahkan proteksi agar `shift_id`, `work_placement_type`, dan `locked_office_id` tidak ditimpa jika data pemagang sudah ada.
     - Tambahkan penanganan field `status` untuk mengatur `users.is_active` (`false` bila `exited`/`completed`).
     - Pastikan field alamat tidak menimpa data yang sudah ada atau dibiarkan null/manual.
2. **Tahap 2: Penyesuaian Client & Controller di Web Intern (`Internship_Management_System_SevenInc`)**
   - Modifikasi `app/Services/AbsenDjuraganSyncService.php`:
     - Sesuaikan payload agar mengirimkan status magang (`internship_status`).
     - Simpan `absen_user_id` / `intern_id` yang diterima dari respon API.
   - Modifikasi `app/Http/Controllers/Admin/InternController.php`:
     - Tambahkan pemanggilan `syncIntern()` pada `bulkUpdateStatus()`.
     - Tambahkan pemanggilan `syncIntern()` pada `update()` jika pemagang sudah berstatus `accepted` / `active`.
     - Kirim sinkronisasi update saat pemagang berpindah status ke `exited` atau `completed`.
3. **Tahap 3: Verifikasi & Pengujian Lintas Aplikasi**
   - Test 1: Single ACC pemagang + Verifikasi login di Web Absen.
   - Test 2: Ganti shift pemagang di Web Absen menjadi "Shift Siang", lalu lakukan update tanggal di Web Intern. Verifikasi bahwa shift di Web Absen **tetap Shift Siang** dan tidak ter-reset.
   - Test 3: Bulk ACC pemagang dan pastikan seluruh akun terbuat di Web Absen.
   - Test 4: Ubah status pemagang ke `exited` dan pastikan akun di Web Absen menjadi non-aktif.
