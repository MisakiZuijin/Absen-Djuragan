# Rencana Implementasi & Roadmap Fitur Absen Djuragan

Dokumen ini berisi roadmap dan rencana kerja terstruktur untuk penambahan serta perbaikan fitur pada proyek **Absen Djuragan**.  
Rencana ini diurutkan berdasarkan tingkat kemudahan dan kecepatan pengerjaan (**Quick Wins diletakkan di paling atas**).

---

## Daftar Isi & Ringkasan Prioritas

1. [Fase 1: Quick Wins & Visual / UI Bug Fixes](#fase-1-quick-wins--visual--ui-bug-fixes-prioritas-tertinggi)
2. [Fase 2: Fitur Spesifik Divisi Pemagang & Link GDrive Tugas](#fase-2-fitur-spesifik-divisi-pemagang--link-gdrive-tugas)
3. [Fase 3: Revamp Fitur Raise Hand (3 Tab & Urgensi) + History Presentasi Selesai](#fase-3-revamp-fitur-raise-hand-3-tab--urgensi--history-presentasi-selesai)
4. [Fase 4: Halaman Project Selesai (Portofolio Tim & Pemagang)](#fase-4-halaman-project-selesai-portofolio-tim--pemagang)
5. [Fase 5: Penyempurnaan Logika Jam Kerja & Pemisahan Sakit / Izin / Alpha](#fase-5-penyempurnaan-logika-jam-kerja--pemisahan-sakit--izin--alpha)
6. [Fase 6: Penambahan Role Superadmin & Pembagian Hak Akses](#fase-6-penambahan-role-superadmin--pembagian-hak-akses)
7. [Fase 7: Penataan & Penyatuan File Migrasi Database](#fase-7-penataan--penyatuan-file-migrasi-database)

---

## Fase 1: Quick Wins & Visual / UI Bug Fixes (Status: SELESAI ✅)

_Estimasi: Paling Cepat & Risiko Rendah_

### 1.1 Perbaikan Bug Visual Tabel Jam Kerja / Jadwal Pemagang [SELESAI ✅]

- **File Target**: `resources/views/users/index.blade.php`
- **Tindakan yang Diselesaikan**:
    - Memperbaiki container scroll `overflow-y-auto` agar border dan isi tabel tidak terpotong (`border`, `shadow-sm`, `rounded-xl`, `max-h-60`).
    - Menyelaraskan header tabel: `Tanggal | Hari | Shift | Jam | Tipe | Status`.
    - Menambahkan badge status kehadiran dengan indikator warna lembut (Hadir, Sakit, Izin, Alpha, Terjadwal).
    - Menambahkan empty state `@forelse` jika tidak ada jadwal.

### 1.2 Spacing & Padding Footer Halaman Pemagang [SELESAI ✅]

- **File Target**: `resources/views/users/layouts/main.blade.php`
- **Tindakan yang Diselesaikan**:
    - Mengganti `body class="h-screen bg-gray-100"` menjadi `min-h-screen bg-gray-100`.
    - Menambahkan wrapper dengan `min-h-screen flex flex-col pb-16` dan container `<main class="flex-1 w-full">` untuk `@yield('contents')` agar bagian bawah halaman tidak terpotong di layar bergerak maupun laptop.

### 1.3 Tombol Back / Kembali pada Seluruh Popup & Modal [SELESAI ✅]

- **File Target**: `resources/views/users/index.blade.php`, `resources/views/livewire/attd-status-button.blade.php`, `resources/views/users/change-time.blade.php`, `public/js/user/index.js`
- **Tindakan yang Diselesaikan**:
    - Menambahkan tombol "Batal" dan icon close (X) pada `actionModal` (Keterangan presensi).
    - Menambahkan tombol close (X) pada popup Activity Log (`popup-form`).
    - Menambahkan tombol "Batal" pada modal izin tidak hadir (`modal2`).
    - Menambahkan tombol "Tutup" pada footer modal detail pengumuman (`broadcastModalbyId`).
    - Menambahkan tombol close (X) pada modal izin keluar (`leavePermitModal`) dan modal bukti kekurangan jam (`change-time`).
    - Memperbaiki handler penutupan modal pada `public/js/user/index.js`.

### 1.4 Penggantian Popup Hari Libur (+ SOP & Peraturan Per Kantor) [SELESAI ✅]

- **File Target**: `resources/views/users/index.blade.php`, `resources/views/livewire/attd-status-button.blade.php`, `app/Models/Office.php`, `app/Http/Controllers/UserController.php`, `database/migrations/2026_09_16_000001_add_sop_and_rules_to_offices_table.php`, `resources/views/admin/edit-maps-location.blade.php`, `resources/views/admin/maps-location.blade.php`
- **Tindakan yang Diselesaikan**:
    - Membuat migrasi dan menambahkan kolom `sop_url`, `rules_url`, `rules_description` pada tabel `offices`.
    - Menyesuaikan `Office` model `$fillable`, `StoreOfficeRequest`, dan `UpdateOfficeRequest`.
    - Menambahkan field SOP URL, Rules URL, dan Rules Description pada form create & edit kantor di sisi admin.
    - Mengubah modal "Hari Libur" menjadi modal informasi 3 tab:
        1. **Tab Hari Libur**: Kalender/tabel hari libur nasional & kantor.
        2. **Tab SOP Magang**: Ringkasan SOP umum Djuragan & tombol link dokumen resmi SOP.
        3. **Tab Peraturan Kantor**: Menampilkan aturan spesifik dan link aturan berdasarkan kantor penempatan pemagang (`$userOffice->name`, `$userOffice->rules_url`, `$userOffice->rules_description`).
    - Memperbarui tombol di dashboard pemagang menjadi **"Info & Libur"** dengan icon informatif.

### 1.5 Revamp Logbook Harian Terpadu (Pendekatan B) [SELESAI ✅]

- **File Target**: `app/Http/Controllers/UserController.php`, `app/Http/Controllers/LogActivityController.php`, `app/Services/LogActivityService.php`, `app/Livewire/AttdStatusButton.php`, `resources/views/livewire/attd-status-button.blade.php`, `resources/views/users/index.blade.php`, `public/js/user/index.js`, `docs/LOGACTIVITY.md`
- **Tindakan yang Diselesaikan**:
    - Menyatukan tombol `Log Activity` dan `History Log Activity` menjadi tombol tunggal berkelas identik: **`Logbook Harian`** dengan icon `<i class="fa-solid fa-book-bookmark"></i>`.
    - Menambahkan indikator status subtle langsung di sebelah teks tombol (🟢 Titik hijau jika sudah mengisi hari ini, 🟡 Titik kuning berkedip jika belum mengisi).
    - Membangun Modal Terpadu 2-Tab di `users/index.blade.php`:
        1. **Tab Isi Laporan Hari Ini**: Card status laporan hari ini, tampilan teks aktivitas yang sudah disubmit, mode edit langsung jika status pending, serta formulir input aktivitas jika belum disubmit.
        2. **Tab Riwayat Logbook**: Tabel rekap logbook pemagang dengan format tanggal Indonesia, badge status approval (Menunggu, Disetujui, Ditolak), filter pencarian instan, dan tombol edit untuk logbook yang belum disetujui admin.
    - Menambahkan sub-modal edit logbook lampau dan sinkronisasi date format MySQL `Y-m-d` pada `LogActivityService`.
    - Menjamin backward compatibility (fungsi `togglePopup()` tetap berfungsi normal mengarah ke modal terpadu).

---

## Fase 2: Fitur Spesifik Divisi Pemagang & Link GDrive Tugas

_Estimasi: Cepat - Sedang_

### 2.1 Divisi Programmer: Akun Gmail & GitHub

- **Tindakan**:
    - Form/Popup khusus pemagang divisi Programmer:
        - Input Akun Gmail & Password (disimpan dengan enkripsi Laravel `Crypt::encryptString` demi keamanan data).
        - Input URL/Username GitHub.
    - Sisi Admin: Tab detail pemagang programmer dapat melihat/menyalin data akun tersebut.

### 2.2 Divisi Digital Marketing / Social Media: Multi-Akun Sosmed

- **Tindakan**:
    - Form/Popup dengan tombol interaktif **(+) Tambah Akun**:
        - Input: Platform (Instagram, TikTok, Facebook, YouTube, LinkedIn, dll), Username/Handle, dan Link Akun.
        - Pemagang bisa menambah lebih dari 1 akun sesuai tugas yang diberikan.

### 2.3 Divisi UI/UX: Registrasi Akun Figma

- **Tindakan**:
    - Form/Popup pendaftaran akun/email Figma dan link profil/workspace Figma yang digunakan selama magang.

### 2.4 Divisi Desain, PM, Animasi, Fotografer, Videografer, Las, dll: Link GDrive Tugas

- **Tindakan**:
    - **Sisi Admin**: Form input link Google Drive khusus per pemagang (1 pemagang memiliki 1 link folder GDrive unik tempat pengumpulan tugas).
    - **Sisi Pemagang**: Popup atau tombol pintas di dashboard yang mengarahkan langsung ke link Google Drive pengumpulan tugas masing-masing.

---

## Fase 3: Revamp Fitur Raise Hand (3 Tab & Urgensi) + History Presentasi Selesai

_Estimasi: Sedang_

### 3.1 Skema Data Raise Hand

- **File Target**: `app/Models/HandRaise.php`, migrasi `hand_raises`
- **Field Baru**:
    - `type`: Enum (`question` / Bertanya, `new_task` / Meminta Tugas Baru, `presentation` / Penjadwalan Presentasi).
    - `presentation_mode`: Enum (`online`, `offline`).
    - `presentation_date`: Date.
    - `status`: Enum (`pending`, `urgent`, `in_progress`, `done`).
    - `notes`: Catatan kebutuhan atau materi yang diajukan.

### 3.2 Logika Urgensi Otomatis

- Jika pemagang mengajukan penjadwalan presentasi untuk hari ini (`presentation_date == hari ini`), status otomatis diset menjadi **`urgent`** dengan penanda visual khusus agar admin langsung memprioritaskan.

### 3.3 Antarmuka Raise Hand Admin & Siklus Penugasan Tugas Baru

- **File Target**: `resources/views/admin/raise-hand.blade.php`, `app/Http/Controllers/HandRaiseController.php`, `resources/views/users/tasks.blade.php`
- **Tab 2: Permintaan Tugas Baru (Dua Tombol Aksi Admin)**:
    1. **Tombol "Beri Tugas"**:
       - Mengirimkan instruksi tugas baru ke pemagang (status: `in_progress` / Tugas Diberikan).
       - Tugas ini otomatis masuk dan menyatu langsung ke bagian **Tugas Utama** di halaman pemagang (`/user/tasks`).
       - **Mekanisme Cek & Edit Tugas**: Admin dapat memeriksa kembali tugas yang telah dikirim dan mengedit instruksinya sewaktu-waktu jika terdapat kekeliruan atau perubahan kebutuhan sebelum disahkan.
    2. **Tombol "Selesai"**:
       - Digunakan untuk memvalidasi dan menyudahi pemberian tugas secara sah (ketika instruksi sudah valid, tuntas, dan tidak ada revisi pemberian tugas lagi).
       - Setelah tombol "Selesai" ditekan, penugasan resmi dikunci dan dipindahkan ke **Tab History Selesai**.

### 3.4 Validasi Pra-Presentasi & History Selesai

- **Pemberitahuan Status Perbaikan / Kesiapan Pra-Presentasi**:
    - Sebelum sesi presentasi dilaksanakan, mentor/admin dapat memberikan notifikasi atau catatan apakah project yang dikerjakan **ada perbaikan (revisi)** atau **siap presentasi**.
    - Pemagang mendapatkan kejelasan arahan perbaikan sebelum sesi presentasi dinilai secara final.
- **Tab History Selesai Presentasi & Evaluasi Kinerja**:
    - Rekap riwayat pemagang yang telah menyelesaikan presentasi.
    - Form evaluasi admin untuk mencatat skor/performa (1–100) dan catatan kinerja pemagang saat presentasi ditandai selesai.

---

## Fase 4: Halaman Project Selesai (Portofolio Tim & Pemagang)

_Estimasi: Sedang_

### 4.1 Halaman Project Selesai

- **File Target**: Controller baru `ProjectCompletedController`, View `admin/project-completed.blade.php` & `users/project-completed.blade.php`
- Menampilkan daftar tugas/project yang sudah berstatus `done` / `selesai`.
- Detail informasi:
    - Nama Project / Kategori Tugas
    - Nama Anggota Tim / Pemagang
    - Tanggal Mulai dan Tanggal Selesai
    - **Khusus Divisi Programmer**: Menampilkan link repository GitHub project.
    - **Divisi Non-Programmer**: Menampilkan link hasil kerja / file / link Google Drive tugas selesai.

---

## Fase 5: Penyempurnaan Logika Jam Kerja & Pemisahan Sakit / Izin / Alpha (Status: SELESAI ✅)

_Estimasi: Sedang - Kompleks (Core Calculation)_

### 5.1 Pemisahan Status Kehadiran & Data Ganti Jam Pemagang [SELESAI ✅]

- **File Target**: `app/Models/AttdStatus.php`, `app/Services/AttendanceService.php`, `app/Http/Controllers/UserController.php`, `resources/views/users/change-time.blade.php`
- **Tindakan yang Diselesaikan**:
    - **Sakit (Dengan Bukti Surat Dokter)**: Shift pada hari tersebut dihitung **lunas** (tidak dimasukkan ke hutang jam kerja `change_time_total` maupun daftar hari ganti jam).
    - **Sakit (Tanpa Bukti Surat)**: Masuk daftar ganti jam dengan label `"Sakit (Tanpa Bukti Surat)"` dan teks keterangan: `"Sakit (tanpa bukti surat) - Wajib Ganti Jam {$hours} Jam {$minutes} Menit"`.
    - **Izin Keperluan (Tanpa Bukti Surat)**: Masuk daftar ganti jam dengan label `"Izin Keperluan (Tanpa Bukti Surat)"` dan teks keterangan: `"Izin Keperluan (tanpa bukti surat) - Wajib Ganti Jam {$hours} Jam {$minutes} Menit"`.
    - **Izin Keperluan (Wajib Ganti Jam)**: Masuk daftar dengan label `"Izin Keperluan (Wajib Ganti Jam)"` dan teks: `"Izin Keperluan (Wajib Ganti Jam) {$hours} Jam {$minutes} Menit"`.
    - **Dispensasi Bebas Ganti Jam**: Izin yang disetujui lunas (`isChangeSchedule == 1`) dibebaskan dari hutang jam kerja.
    - **Alpha (Tidak Hadir)**: Shift hari tersebut otomatis dihitung penuh sebagai hutang jam kerja: `"Alpha (Tidak Hadir) - Wajib Ganti Jam {$hours} Jam {$minutes} Menit"`.
    - **Presensi Reguler**: Kekurangan jam kerja reguler (terlambat / pulang cepat) dihitung khusus kehadiran reguler.
    - **Tabel Data Hari Mengganti Jam**: Menambahkan kolom badge **Kategori Ganti Jam**, detail alasan pemagang, dan modal verifikasi bukti lampiran/surat dokter yang informatif.

### 5.2 Penyempurnaan Logika Keterlambatan & Jam Pulang Otomatis [SELESAI ✅]

- **File Target**: `app/Models/Attendance.php`, `app/Http/Controllers/LateAbsenceController.php`, `app/Services/AttendanceService.php`
- **Ketentuan**:
    1. **Keterlambatan Masuk**:
        - Otomatis memundurkan jam pulang (`adjusted_end_time`) sebesar menit keterlambatan.
        - Memotong durasi istirahat atau menggeser jam istirahat sesuai lama keterlambatan.
    2. **Keterlambatan Kembali dari Istirahat**:
        - Jika kembali melebihi batas waktu istirahat (misal >45 menit atau >60 menit), selisih menit keterlambatan otomatis diakumulasikan menambah jam pulang pada hari tersebut.

---

## Fase 6: Penambahan Role Superadmin & Pembagian Hak Akses

_Estimasi: Sedang_

### 6.1 Registrasi Role Superadmin

- Tambahkan data role `Superadmin` (ID: 7).
- Perbarui `app/Http/Middleware/RoleMiddleware.php` agar mendukung multi-role parameters (misal `role:1,7`) atau arsitektur hirarkis (Superadmin otomatis memiliki hak akses role Admin).

### 6.2 Hak Istimewa Superadmin

- Pengelolaan user role (dapat mengubah role Admin, Asisten Admin).
- Hak override persetujuan jam kerja, reset password darurat, dan akses konfigurasi inti aplikasi.

---

## Fase 7: Penataan & Penyatuan File Migrasi Database

_Estimasi: Butuh Kehati-hatian Tinggi (Kerapian Struktur)_

### 7.1 Penyatuan (Merge) Migrasi

- Menggabungkan puluhan migrasi `add_*` dan `update_*` langsung ke dalam file migrasi utama `create_*_table`:
    - Seluruh penambahan kolom tabel `attendances` disatukan ke `2024_08_19_150045_attendances.php`.
    - Penambahan kolom pada `users`, `profiles`, `hand_raises`, `shifts`, dan `detail_schedules` disatukan ke file create aslinya.
- Memindahkan file alter lama ke folder arsip / menghapus migrasi pecahan.
- Validasi akhir dengan `php artisan migrate:fresh --seed` di lokal untuk memastikan seluruh tabel dan seeder terpasang sempurna tanpa error.

---

## Checklist Verifikasi Akhir

- [ ] Tampilan tabel jam kerja di mobile & desktop rapi tanpa bug overflow.
- [ ] Tombol kembali tersedia di semua modal/popup.
- [ ] Popup info memuat Hari Libur, SOP, dan Peraturan Kantor.
- [ ] Input data spesifik divisi (Programmer, Sosmed, UI/UX, Desain/PM) tersimpan dengan benar.
- [ ] Raise hand 3 tab berfungsi dengan filter status Normal, Urgent, dan Done.
- [ ] History presentasi merekap performa pemagang.
- [ ] Halaman project selesai menampilkan link GitHub untuk programmer.
- [ ] Sakit tidak menambah hutang jam; Alpha menambah hutang jam penuh.
- [ ] Keterlambatan masuk & istirahat otomatis menambah jam pulang.
- [ ] Role Superadmin dapat mengakses seluruh menu Admin + menu khusus.
- [ ] Migrasi database berhasil dijalankan dari awal (`migrate:fresh --seed`) dengan bersih.
