# Panduan Konfigurasi Email SMTP & Fitur Reset Password
**Sistem Absensi Djuragan (Seven Inc)**

Dokumentasi ini menjelaskan langkah-langkah lengkap untuk mengonfigurasi pengiriman email (SMTP) di file `.env`, khususnya menggunakan akun **Gmail Pribadi / Workspace** melalui fitur **Sandi Aplikasi (App Password)**, serta cara melakukan pengujian dan pemecahan masalah (*troubleshooting*).

---

## Daftar Isi
1. [Arsitektur Fitur Reset Password](#1-arsitektur-fitur-reset-password)
2. [Cara Mendapatkan Sandi Aplikasi Gmail (16 Karakter)](#2-cara-mendapatkan-sandi-aplikasi-gmail-16-karakter)
3. [Format Konfigurasi di File `.env`](#3-format-konfigurasi-di-file-env)
4. [Langkah Setelah Mengubah `.env`](#4-langkah-setelah-mengubah-env)
5. [Opsi Testing Tanpa Kirim Email (Mode Log)](#5-opsi-testing-tanpa-kirim-email-mode-log)
6. [Alur & Cara Pengujian Fitur](#6-alur--cara-pengujian-fitur)
7. [Panduan Pemecahan Masalah (Troubleshooting)](#7-panduan-pemecahan-masalah-troubleshooting)

---

## 1. Arsitektur Fitur Reset Password

Fitur reset password pada aplikasi ini dirancang modern, aman, dan efisien:
- **Stateless (Tanpa Tabel Tambahan):** Menggunakan token **JWT (*JSON Web Token*)** bertanda tangan kriptografi HMAC-SHA256. Tidak memerlukan tabel `password_resets` di database, sehingga database tetap bersih.
- **Kedaluwarsa Otomatis (*Auto-Expiry*):** Link reset hanya berlaku selama **1 jam (3600 detik)**.
- **Proteksi Sekali Pakai (*Anti-Replay Protection*):** Token menyematkan sidik jari timestamp `updated_at` akun. Begitu password berhasil diganti satu kali, link tersebut langsung hangus otomatis dan tidak dapat digunakan lagi.
- **Proteksi Spam (*Rate Limiting*):** Route reset password dibatasi maksimal 5 permintaan per 5 menit (`throttle:5,5`).
- **Enkripsi Kuat:** Password baru otomatis di-hash menggunakan algoritma **Bcrypt** standar Laravel.

---

## 2. Cara Mendapatkan Sandi Aplikasi Gmail (16 Karakter)

> **PENTING:** Google sudah tidak mengizinkan penggunaan password login Gmail biasa untuk aplikasi pihak ketiga (SMTP). Anda **wajib** membuat Sandi Aplikasi (*App Password*).

### Langkah-langkah:
1. Masuk ke akun Google Anda di [https://myaccount.google.com/](https://myaccount.google.com/).
2. Pilih menu **Keamanan (*Security*)** di bilah navigasi kiri.
3. Pastikan **Verifikasi 2 Langkah (*2-Step Verification*)** dalam status **Aktif**. Jika belum aktif, aktifkan terlebih dahulu menggunakan nomor HP.
4. Buka halaman pembuatan sandi aplikasi melalui salah satu cara:
   - Ketik **"Sandi Aplikasi"** atau **"App Passwords"** pada kolom pencarian setelan Google akun Anda.
   - Atau langsung buka tautan: [https://myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords).
5. Pada kolom *Nama Aplikasi* / *App Name*, masukkan nama pengenal, misalnya: `Absen Local`.
6. Klik tombol **Buat (*Create*)**.
7. Google akan menampilkan jendela dengan **16 huruf kode rahasia** dalam kotak kuning/abu-abu, contohnya:
   ```text
   celp wnqf cumh wvyj
   ```
8. Salin kode tersebut dan simpan.

---

## 3. Format Konfigurasi di File `.env`

Buka file `.env` di direktori utama proyek (`d:/laragon/www/absen-djuragan/.env`), cari baris konfigurasi `MAIL_`, lalu sesuaikan:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email_anda@gmail.com
MAIL_PASSWORD=celpwnqfcumhwvyj
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="email_anda@gmail.com"
MAIL_FROM_NAME="absen-djuragan"
```

### Penjelasan Variabel:
| Parameter | Nilai Rekomendasi | Keterangan |
| :--- | :--- | :--- |
| `MAIL_MAILER` | `smtp` | Protokol pengiriman email. |
| `MAIL_HOST` | `smtp.gmail.com` | Alamat server SMTP Gmail. |
| `MAIL_PORT` | `587` | Port koneksi TLS (Port standar untuk Gmail SMTP). |
| `MAIL_USERNAME` | `email_anda@gmail.com` | Alamat email Gmail yang Anda gunakan. |
| `MAIL_PASSWORD` | `16_karakter_tanpa_spasi` | **Wajib dirapatkan tanpa spasi!** (Lihat aturan di bawah). |
| `MAIL_ENCRYPTION`| `tls` | Protokol enkripsi keamanan transmisi email. |
| `MAIL_FROM_ADDRESS` | `"email_anda@gmail.com"` | Alamat pengirim yang akan dilihat penerima. Pastikan tidak ada typo domain (misal: bukan `gmai.com`). |
| `MAIL_FROM_NAME` | `"absen-djuragan"` | Nama instansi / pengirim yang muncul di inbox penerima. |

### Aturan Wajib Penulisan `MAIL_PASSWORD`:
> ⚠️ **HAPUS SEMUA SPASI:**
> Google menampilkan sandi aplikasi dengan format 4 kelompok kata (contoh: `celp wnqf cumh wvyj`).
> Di file `.env`, Anda **WAJIB menghapus spasinya** menjadi:
> ```env
> MAIL_PASSWORD=celpwnqfcumhwvyj
> ```
> Jika spasi tidak dihapus, file `.env` akan gagal dibaca dengan error:
> `Failed to parse dotenv file. Encountered unexpected whitespace at [...]`.

---

## 4. Langkah Setelah Mengubah `.env`

Setiap kali Anda mengubah nilai di file `.env`, cache konfigurasi Laravel harus dibersihkan agar pengaturan baru terbaca:

Jalankan perintah berikut di terminal / command prompt:
```bash
php artisan config:clear
```

Atau bersihkan seluruh cache aplikasi:
```bash
php artisan optimize:clear
```

---

## 5. Opsi Testing Tanpa Kirim Email (Mode Log)

Jika Anda ingin melakukan testing cepat di lokal tanpa mengirim email sungguhan ke inbox:
1. Ubah konfigurasi di `.env` menjadi:
   ```env
   MAIL_MAILER=log
   ```
2. Jalankan `php artisan config:clear`.
3. Saat Anda mengklik *Continue* di halaman lupa password, email tidak dikirim ke internet, melainkan dicatat langsung ke file log aplikasi di:
   ```text
   storage/logs/laravel.log
   ```
4. Anda dapat membuka file log tersebut dan menyalin tautan reset password yang tercetak di dalamnya.

---

## 6. Alur & Cara Pengujian Fitur

1. **Buka Halaman Login / Lupa Password:**
   - Akses: `http://localhost/login` (atau domain lokal Anda).
   - Klik link **"Lupa kata sandi? Reset"** atau akses langsung URL:
     ```text
     http://localhost/user/forget-password
     ```
2. **Kirim Permintaan Reset:**
   - Masukkan alamat email yang terdaftar pada tabel `users`.
   - Klik tombol **Continue**.
   - Halaman akan dialihkan ke `/user/notif/success` yang memberitahukan bahwa link telah dikirim.
3. **Buka Email Penerima:**
   - Buka inbox email (atau folder *Spam / Promosi* jika baru pertama kali).
   - Terdapat email dari **absen-djuragan** dengan subjek: `Reset Password Akun - Absensi Djuragan`.
   - Klik tombol **Reset Password Saya**.
4. **Buat Password Baru:**
   - Tautan akan membuka halaman `/user/change-password/{jwt}`.
   - Masukkan password baru (minimal 8 karakter).
   - Masukkan konfirmasi password baru (gunakan tombol ikon mata untuk mengecek teks).
   - Klik **Simpan Password Baru**.
5. **Login Ulang:**
   - Sistem akan mengalihkan kembali ke halaman login dengan pesan:
     *`Password Anda berhasil diperbarui. Silakan login dengan password baru.`*
   - Coba login menggunakan email dan password baru Anda.

---

## 7. Panduan Pemecahan Masalah (Troubleshooting)

### A. Error: `Encountered unexpected whitespace at [...]`
- **Penyebab:** Ada spasi pada baris `MAIL_PASSWORD` di `.env`.
- **Solusi:** Rapatkan 16 karakter sandi aplikasi tanpa spasi sama sekali (`MAIL_PASSWORD=abcdefghijklmnop`).

### B. Error: `535-5.7.8 Username and Password not accepted`
- **Penyebab:** 
  1. Anda memasukkan password akun Google biasa, bukan *App Password*.
  2. Alamat `MAIL_USERNAME` tidak cocok dengan pemilik *App Password*.
  3. Verifikasi 2 Langkah belum diaktifkan di akun Google terkait.
- **Solusi:** Buat ulang Sandi Aplikasi baru di [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords) dan pastikan di-copy dengan benar.

### C. Error: `Connection could not be established with host "mailpit:1025"`
- **Penyebab:** Konfigurasi `.env` masih mengarah ke host `mailpit` bawaan lokal yang tidak berjalan.
- **Solusi:** Pastikan `MAIL_HOST=smtp.gmail.com` dan jalankan `php artisan config:clear`.

### D. Pesan: `"Email tidak terdaftar dalam sistem"`
- **Penyebab:** Email yang diketikkan di form lupa password tidak ditemukan di kolom `email` tabel `users`.
- **Solusi:** Pastikan akun yang ingin diuji coba sudah terdaftar atau gunakan email anggota magang/admin yang ada di database.

### E. Pesan: `"Link reset password ini sudah pernah digunakan"`
- **Penyebab:** Pengguna mencoba membuka atau mengirim form menggunakan link reset yang sama untuk kedua kalinya.
- **Solusi:** Ini adalah fitur keamanan (*anti-replay protection*). Silakan ajukan link reset baru melalui halaman `/user/forget-password`.

### F. Pesan: `"Link reset password sudah kedaluwarsa"`
- **Penyebab:** Link reset dibuka lebih dari 1 jam sejak email diterima.
- **Solusi:** Ajukan link reset baru di halaman `/user/forget-password`.
