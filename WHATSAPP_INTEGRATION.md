# Integrasi WhatsApp Notification System

## Overview

Sistem notifikasi WhatsApp untuk outsider type "ortu" yang akan menerima notifikasi setiap kali anak mereka melakukan presensi masuk atau pulang.

## Cara Kerja

### 1. Flow Notifikasi

```
User Absen → AttendanceService → WhatsappService → Bot Server → WhatsApp
```

1. User melakukan absen masuk/pulang
2. `AttendanceService` memproses absensi
3. `WhatsappService` mencari semua target notifikasi:
   - Dari tabel `whatsapp_numbers` (existing)
   - Dari outsider type "ortu" yang terkait
4. Mengirim notifikasi ke semua nomor yang aktif
5. Bot server mengirim pesan ke WhatsApp

### 2. Struktur Data

#### Tabel `outsiders`
```sql
- user_id: ID user (foreign key)
- type: Enum ('guru', 'ortu')
- phone_number: Nomor WhatsApp
- notif_enabled: Boolean untuk mengaktifkan/menonaktifkan notifikasi
```

#### Tabel `whatsapp_numbers`
```sql
- intern_id: ID intern (foreign key)
- phone_number: Nomor WhatsApp
- is_notification_active: Boolean untuk status notifikasi
```

### 3. Relasi Data

```
User (role: intern) → Intern → Outsider (type: ortu)
User (role: outsider) → Outsider → Intern
```

## Setup dan Konfigurasi

### 1. Environment Variables

Tambahkan di file `.env`:
```env
WHATSAPP_API_URL=http://localhost:3000
```

### 2. Bot Server Setup

1. Pastikan bot server berjalan:
```bash
cd whatsapp-bot-api
npm install
node bot-server.js
```

2. Scan QR code yang muncul di terminal

3. Bot siap menerima request dari Laravel

### 3. Database Migration

Pastikan migration sudah dijalankan:
```bash
php artisan migrate
```

## Cara Penggunaan

### 1. Menambah Outsider Type "Ortu"

1. Login sebagai admin
2. Buka menu "Outsiders"
3. Klik "Tambah Outsider"
4. Pilih type "ortu"
5. Pilih intern (anak)
6. Masukkan nomor WhatsApp
7. Centang "Aktifkan Notifikasi"
8. Simpan

### 2. Testing Notifikasi

#### Via Web Interface
1. Login sebagai user intern
2. Di dashboard, klik tombol "Test Notifikasi"
3. Cek WhatsApp untuk notifikasi test

#### Via Command Line
```bash
php artisan whatsapp:test {intern_id} --status=MASUK --time=08:00
```

### 3. Toggle Notifikasi

User intern dapat mengaktifkan/menonaktifkan notifikasi dari dashboard mereka.

## File-file Penting

### Services
- `app/Services/WhatsappService.php` - Service utama untuk mengirim notifikasi
- `app/Services/AttendanceService.php` - Service attendance yang memanggil WhatsApp service

### State Pattern
- `app/Services/Attendance/State/AttendanceInState.php` - State untuk absen masuk
- `app/Services/Attendance/State/AttendanceOutState.php` - State untuk absen pulang

### Observer
- `app/Observers/OutsiderObserver.php` - Observer untuk mengelola data outsider

### Controllers
- `app/Http/Controllers/UserController.php` - Controller untuk toggle dan test notifikasi
- `app/Http/Controllers/OutsiderController.php` - Controller untuk manajemen outsider

### Models
- `app/Models/Outsider.php` - Model outsider
- `app/Models/WhatsappNumber.php` - Model whatsapp number
- `app/Models/Intern.php` - Model intern

## Format Pesan

Pesan yang dikirim ke WhatsApp:
```
Notifikasi Presensi: Anak Anda, [nama], telah melakukan presensi *[status]* pada pukul [waktu]. Terima kasih.
```

Contoh:
```
Notifikasi Presensi: Anak Anda, John Doe, telah melakukan presensi *MASUK* pada pukul 08:00. Terima kasih.
```

## Troubleshooting

### 1. Bot Server Tidak Merespon
- Pastikan bot server berjalan di port 3000
- Cek log bot server untuk error
- Pastikan QR code sudah di-scan

### 2. Notifikasi Tidak Terkirim
- Cek konfigurasi `WHATSAPP_API_URL` di `.env`
- Cek log Laravel untuk error
- Pastikan ada outsider type "ortu" dengan nomor valid
- Pastikan `notif_enabled` = true

### 3. Duplikasi Notifikasi
- Sistem akan mengirim ke semua target yang aktif
- Jika ada data di `whatsapp_numbers` dan `outsiders`, keduanya akan menerima notifikasi

## Security Considerations

1. **Nomor WhatsApp**: Pastikan format nomor benar (62xxx)
2. **Rate Limiting**: Bot server memiliki timeout 10 detik
3. **Logging**: Semua aktivitas notifikasi di-log untuk monitoring
4. **Error Handling**: Sistem menangani error dengan graceful

## Monitoring

### Log Files
- Laravel log: `storage/logs/laravel.log`
- Bot server log: Console output

### Database Monitoring
```sql
-- Cek outsider yang aktif
SELECT * FROM outsiders WHERE type = 'ortu' AND notif_enabled = 1;

-- Cek whatsapp numbers yang aktif
SELECT * FROM whatsapp_numbers WHERE is_notification_active = 1;
```

## Future Enhancements

1. **Template Pesan**: Customizable message template
2. **Scheduling**: Notifikasi terjadwal
3. **Analytics**: Dashboard untuk monitoring notifikasi
4. **Bulk Operations**: Kirim notifikasi ke multiple targets
5. **Media Support**: Kirim gambar/dokumen
