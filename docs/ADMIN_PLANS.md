# Rencana Pengembangan: Super Admin & System Activity Log (Audit Trail)

> **Dokumen**: `docs/ADMIN_PLANS.md`  
> **Dibuat**: 24 September 2026  
> **Status**: Dalam Pengerjaan (In Progress)  
> **Tujuan**: Menambahkan hak akses tertinggi **Super Admin (Role 7)**, modul **Manajemen Akun Admin**, dan sistem **Log Aktivitas Sistem (Audit Trail)** untuk memantau seluruh aktivitas pengguna.

---

## 1. Arsitektur Role & Hierarki

```
                 👑 SUPER ADMIN (Role 7)
         [Akses Penuh + Kelola Admin + Audit Log]
                           │
             ┌─────────────┴─────────────┐
             ▼                           ▼
      👨‍💼 ADMIN (Role 1)           📊 AUDIT LOG SISTEM
 [CRUD Operasional & Master]   [Merekam Seluruh User]
             │
     ┌───────┼───────┐
     ▼       ▼       ▼
  Asisten  Mentor  Pemagang
 (Role 6) (Role 5) (Role 3)
```

### Tabel Role (`roles`)
| ID | Nama Role | Deskripsi |
|---|---|---|
| **7** | **Super Admin** | Memiliki hak akses tertinggi terhadap seluruh sistem, manajemen akun admin, dan audit log aktivitas. |
| 1 | Admin | Memiliki akses penuh ke semua fitur operasional dan pengaturan master data. |
| 6 | Asisten Admin | Mengapprove log activity intern, presensi offline, dan bantuan raise hand (tanya). |
| 5 | Outsider / Mentor | User pembimbing kampus dengan akses view-only ke pemagang tertentu. |
| 3 | Magang | Pemagang / Intern dengan akses presensi, logbook, izin, dan raise hand. |

---

## 2. Fitur Utama Super Admin

### A. Manajemen Akun Admin (Role 1)
Superadmin dapat mengelola seluruh akun Admin:
- **Daftar Admin**: Melihat seluruh akun Admin aktif & non-aktif beserta kontak & tanggal dibuat.
- **Tambah Admin**: Membuat akun Admin baru (Nama Lengkap, Username, Email, Password, No. WhatsApp).
- **Edit Admin**: Memperbarui informasi akun, email, dan reset password Admin.
- **Toggle Status**: Mengaktifkan atau menonaktifkan akun Admin secara instan.
- **Hapus Admin**: Menghapus akun Admin secara aman (dengan proteksi agar tidak menghapus akun sendiri).

### B. Audit Trail / Log Aktivitas Sistem (`SystemActivityLog`)
Merekam semua kejadian penting yang dilakukan oleh seluruh tingkatan user secara real-time:
1. **Autentikasi**: Login berhasil, Login gagal, Logout.
2. **Presensi**: Absen masuk, absen pulang, ubah status/jam manual oleh admin, presensi fisik offline, pemberian penalti/sanksi.
3. **Izin & Cuti**: Pengajuan izin, persetujuan (Approve Lunas / Wajib Ganti Jam), penolakan, izin keluar, shalat, toilet.
4. **Mentoring & Tugas**: Raise Hand selesai, penugasan tugas baru, penilaian presentasi.
5. **Manajemen User**: Pembuatan/edit/hapus akun (Admin, Asisten, Mentor, Pemagang), reset password, perubahan status aktif.
6. **Master Data & Pengaturan**: Tambah/edit/hapus Kantor, Shift, Divisi, Sekolah, Hari Libur, Broadcast Pengumuman, dan Scheduled Broadcast.

---

## 3. Skema Basis Data

### Tabel `system_activity_logs`
```sql
CREATE TABLE `system_activity_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NULL,
    `user_name` VARCHAR(255) NULL,
    `user_role` VARCHAR(100) NULL,
    `action` VARCHAR(50) NOT NULL,        -- LOGIN, LOGOUT, CREATE, UPDATE, DELETE, APPROVE, REJECT, PENALTY, EXPORT
    `module` VARCHAR(100) NOT NULL,       -- Auth, Presensi, Izin, Mentoring, Master, User Management, Shift
    `description` TEXT NOT NULL,          -- Deskripsi kalimat aktivitas
    `ip_address` VARCHAR(45) NULL,        -- IPv4 / IPv6
    `user_agent` TEXT NULL,               -- Browser & OS
    `properties` JSON NULL,               -- Payload data (opsional)
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_module` (`module`),
    INDEX `idx_created_at` (`created_at`)
);
```

---

## 4. Helper Logging: `ActivityLogger`

Disediakan helper statis yang mudah dipanggil di controller, service, atau event mana saja:

```php
use App\Helper\ActivityLogger;

// Contoh pemanggilan:
ActivityLogger::log(
    action: 'APPROVE',
    module: 'Izin',
    description: "Admin {$admin->name} menyetujui Izin Sakit pemagang {$intern->name} (Bebas Ganti Jam)",
    properties: ['permit_id' => $permit->id]
);
```

---

## 5. Rute & Akses URL

| Metode | URL | Middleware | Deskripsi |
|---|---|---|---|
| `GET` | `/admin/super-admin/admins` | `auth, role:7` | Daftar Akun Admin |
| `POST` | `/admin/super-admin/admins` | `auth, role:7` | Simpan Admin Baru |
| `PUT` | `/admin/super-admin/admins/{user}` | `auth, role:7` | Update Data Admin |
| `PATCH` | `/admin/super-admin/admins/{user}/toggle` | `auth, role:7` | Toggle Aktif/Nonaktif Admin |
| `DELETE` | `/admin/super-admin/admins/{user}` | `auth, role:7` | Hapus Akun Admin |
| `GET` | `/admin/super-admin/activity-logs` | `auth, role:7` | Antarmuka Audit Log Sistem |
| `DELETE` | `/admin/super-admin/activity-logs/clear` | `auth, role:7` | Bersihkan Log Lama (Opsional) |

*Catatan*: Seluruh rute admin standar (`/admin/*`) dapat diakses oleh Admin (Role 1) dan Super Admin (Role 7).

---

## 6. Laporan & Analitik Performa Eksekutif Super Admin (`/admin/report`)

Halaman laporan (`/admin/report`) memiliki perbedaan hak akses dan fungsionalitas berdasarkan role:

### A. Tampilan Super Admin (Role 7)
Menampilkan **Dashboard Analitik Eksekutif Lengkap**:
1. **Executive Header & Filter Interaktif**:
   - Filter Rentang Tanggal (Kustom, Hari Ini, 7 Hari Terakhir, Bulan Ini).
   - Filter Divisi, Asal Kampus/Sekolah, dan Pencarian Mahasiswa/NIP.
2. **Kartu KPI Ringkasan Eksekutif (Overall Summary)**:
   - 🎯 **Tingkat Kehadiran Keseluruhan (%)**: Akumulasi presensi vs total jadwal kerja.
   - ⏱️ **Tingkat Ketepatan Waktu (%)**: Persentase on-time vs keterlambatan.
   - 🏢 **Audit Presensi Fisik (Offline)**: Total presensi fisik (Hadir, Telat, Alpha di kantor).
   - ⚖️ **Kedisiplinan & Sanksi**: Total kasus penalti ganti jam dan total menit sanksi.
   - 🌟 **Rata-rata Skor Disiplin (0 - 100%)**: Indeks kepatuhan pemagang.
3. **Sistem Tab Interaktif**:
   - **Tab 1: 📊 Ringkasan & Tren Harian**: Matriks performa per divisi dan breakdown presensi harian per tanggal.
   - **Tab 2: 👤 Performa & Disiplin Perorangan**: Tabel lengkap setiap pemagang (Jadwal, Hadir, Telat, Izin, Alpha, Presensi Fisik, Jam Kerja Target vs Aktual vs Hutang Jam, Skor Disiplin 0-100%, Grade A/B/C/D, serta Quick Modal Resume).
   - **Tab 3: 🏢 Log Presensi Offline (Fisik)**: Audit trail presensi fisik di kantor, keterlambatan, status sanksi (Ganti Jam, Tetap Alpha, Dimaafkan), verifikator, dan catatan.
   - **Tab 4: 📋 Rekap Presensi Standar**: Tampilan data rekap presensi konvensional.
4. **Export Laporan**:
   - Cetak langsung dan unduh laporan PDF berformat landscape lengkap dengan metrik performa eksekutif.

### B. Tampilan Admin Reguler (Role 1)
- Hanya menampilkan **Tabel Rekap Presensi Standar** (No, Nama Mahasiswa, NIP, Total Kehadiran, Total Izin, Total Ketidakhadiran) dengan filter nama dan unduh PDF standar tanpa akses ke analitik eksekutif dan audit confidential.

