# 📋 Analisis Sistem PopupSetting (Pengaturan Refresh Otomatis)

> **Dokumen ini menjelaskan arsitektur, alur kerja, dan masalah yang ditemukan pada fitur Pengaturan Refresh Otomatis.**
> Dibuat: 2026-10-01

---

## 1. Arsitektur Sistem

### Model: `PopupSetting`

**File:** `app/Models/PopupSetting.php`

| Kolom              | Tipe      | Deskripsi                                      |
|--------------------|-----------|-------------------------------------------------|
| `key`              | string    | Identifier unik (e.g. `attd_status_button`)     |
| `label`            | string    | Nama tampilan                                   |
| `group`            | string    | Grup: `pemagang` atau `admin`                   |
| `is_enabled`       | boolean   | Apakah polling aktif                            |
| `interval_seconds` | integer   | Interval polling saat ini (detik)               |
| `default_interval` | integer   | Interval default pabrik (detik)                 |
| `description`      | text      | Deskripsi untuk UI                              |

### Method Penting

```php
PopupSetting::getInterval('key', $default)
// → Mengembalikan interval (detik). Return 0 jika disabled. Minimal 2 detik.
// → Menggunakan Cache::remember() selama 1 jam (3600 detik).

PopupSetting::isEnabled('key', $default)
// → Mengembalikan boolean. Cached selama 1 jam.

PopupSetting::clearAllCache()
// → Dipanggil saat update/reset settings.
```

### Controller: `SettingController`

**File:** `app/Http/Controllers/SettingController.php` (line 278-394)

| Method                    | Fungsi                                                       |
|---------------------------|--------------------------------------------------------------|
| `managePopupSettingsView` | Tampilkan halaman + auto-seed default jika belum ada di DB   |
| `updatePopupSettings`     | Simpan perubahan interval & toggle dari form                 |
| `resetPopupSettings`      | Reset semua ke `default_interval` + `is_enabled = true`      |

---

## 2. Alur Data per Key (Tracing End-to-End)

### ✅ KEY 1: `attd_status_button` — Status Presensi Pemagang

```
[DB: popup_settings] 
  → PopupSetting::getInterval('attd_status_button', 5)
    → [Livewire] AttdStatusButton.php line 744 → render() mengirim $pollInterval ke view
      → [Blade] attd-status-button.blade.php line 1:
        <div wire:poll.{{ $pollInterval }}s>
```

**Status: ✅ BERFUNGSI NORMAL**
- Alur lengkap dan konsisten.
- Livewire membaca setting dari DB setiap kali render().
- `wire:poll` interval dinamis sesuai setting.

---

### ✅ KEY 2: `broadcast_popup` — Popup Pengumuman

```
[DB: popup_settings]
  → PopupSetting::getInterval('broadcast_popup', 15)
    → [Livewire] BroadcastPopup.php line 216 → render() mengirim $pollInterval
      → [Blade] broadcast-popup.blade.php line 1:
        <div wire:poll.{{ $pollInterval }}s="checkForBroadcasts">
```

**Status: ✅ BERFUNGSI NORMAL**
- Sama seperti key 1, alur lengkap via Livewire.

---

### ✅ KEY 3: `change_time_info` — Status Sesi Ganti Jam

```
[DB: popup_settings]
  → PopupSetting::getInterval('change_time_info', 10)
    → [Livewire] ChangeTimeInfoContainer.php line 406 → render() mengirim $pollInterval
      → [Blade] change-time-info-container.blade.php line 1:
        <div wire:poll.{{ $pollInterval }}s>
```

**Status: ✅ BERFUNGSI NORMAL**

---

### ✅ KEY 4: `raise_hand_manager` — Tabel Antrean Bantuan

```
[DB: popup_settings]
  → PopupSetting::getInterval('raise_hand_manager', 5)
    → [Livewire] Admin/RaiseHandManager.php line 272 → render() mengirim $pollInterval
      → [Blade] raise-hand-manager.blade.php line 1:
        <div wire:poll.{{ $pollInterval }}s>
```

**Status: ✅ BERFUNGSI NORMAL**

---

### ⚠️ KEY 5: `toilet_monitor` — Monitor Izin (Toilet, Shalat, Keluar)

**INI YANG BERMASALAH — Ada DUAL SYSTEM (2 mekanisme refresh berbeda):**

#### Mekanisme A: Livewire Component (TIDAK DIPAKAI di halaman admin utama)

```
[DB: popup_settings]
  → PopupSetting::getInterval('toilet_monitor', 15)
    → [Livewire] Admin/Permit/ToiletMonitor.php line 12 → render() mengirim $pollInterval
      → [Blade] livewire/admin/permit/toilet-monitor.blade.php line 1:
        <div wire:poll.{{ $pollInterval }}s="loadInterns">
```

**TAPI:** Livewire component `ToiletMonitor` ini **TIDAK dipakai** di halaman admin utama!
Halaman `admin/izin-toilet.blade.php` dan `admin/izin-shalat.blade.php` **TIDAK** menggunakan `@livewire('admin.permit.toilet-monitor')`.

#### Mekanisme B: Vanilla JS Timer Script (YANG BENAR-BENAR DIPAKAI)

```
[DB: popup_settings]
  → PopupSetting::getInterval('toilet_monitor', 15)
    → [Blade Partial] admin/partials/universal-permit-timer-script.blade.php line 2:
      $toiletMonitorIntervalSec = PopupSetting::getInterval('toilet_monitor', 15);
    → Line 6: const pollIntervalMs = {{ ... * 1000 }};
    → Line 34-56: setInterval(() => fetch(...), pollIntervalMs);
```

Halaman yang memakai mekanisme B:
- `admin/izin-toilet.blade.php` line 153: `@include('admin.partials.universal-permit-timer-script')`
- `admin/izin-shalat.blade.php` line 153: `@include('admin.partials.universal-permit-timer-script')`

#### ❌ Masalah Ditemukan:

| # | Masalah | Dampak |
|---|---------|--------|
| 1 | **`izin-keluar.blade.php` TIDAK include timer script** | Halaman izin keluar admin **tidak punya auto-refresh sama sekali** — harus refresh manual |
| 2 | **Timer script bukan Livewire** — interval di-embed saat page load sebagai JavaScript `const` | Mengubah setting di halaman Pengaturan **tidak langsung berubah** di tab yang sudah terbuka. User harus **refresh/reload halaman** izin toilet/shalat agar interval baru berlaku |
| 3 | **Livewire ToiletMonitor component tidak terpakai** di halaman admin utama | Component `Admin\Permit\ToiletMonitor` ada tapi tidak dipanggil — dead code |
| 4 | **Satu key untuk 3 halaman** | Key `toilet_monitor` mengontrol toilet + shalat + keluar. Tidak bisa set interval berbeda per jenis izin |

---

### ⚠️ KEY 6: `raise_hand_notification` — Suara Notifikasi & Pesan Chat

```
[DB: popup_settings]
  → PopupSetting::getInterval('raise_hand_notification', 5)
  → PopupSetting::isEnabled('raise_hand_notification', true)
    → [Layout] layouts/main.blade.php line 70-75:
      window.__raiseHandPollIntervalMs = {{ $interval * 1000 }};
      window.__raiseHandNotificationsEnabled = {{ enabled ? 'true' : 'false' }};
    → [Layout] layouts/assistant.blade.php line 85-86: (sama)
    → [JS] public/js/admin/raise-hand-notifications.js line 81-82:
      if (window.__raiseHandPollIntervalMs >= 2000) {
          this.pollIntervalMs = window.__raiseHandPollIntervalMs;
      }
```

#### ❌ Masalah Ditemukan:

| # | Masalah | Dampak |
|---|---------|--------|
| 1 | **Interval di-inject saat page load** sebagai `window.__raiseHandPollIntervalMs` | Sama seperti key 5: ubah setting → **harus reload semua tab admin** agar interval JS berubah |
| 2 | **JS file di-cache browser** (static asset `raise-hand-notifications.js`) | Walau DB sudah update, browser mungkin serve JS dari cache lama. Perlu cache busting (`?v=timestamp`) |
| 3 | **Hardcoded fallback di JS** line 17: `this.pollIntervalMs = 5000` dan line 491: `this.pollIntervalMs = 4000` | Jika `window.__raiseHandPollIntervalMs` undefined (race condition), JS fallback ke hardcoded value, bukan DB value |

---

## 3. Masalah Utama: **Cache File Driver + TTL 1 Jam**

```env
CACHE_DRIVER=file
```

```php
// PopupSetting::getSetting()
Cache::remember("popup_setting_{$key}", 3600, function () { ... });
```

### Dampak:

Setelah admin menekan "Simpan Pengaturan":
1. `updatePopupSettings()` memanggil `PopupSetting::clearAllCache()` ✅
2. Cache dihapus untuk semua 6 key ✅
3. **TAPI:** nilai baru baru terbaca saat halaman yang mengonsumsi setting di-load ulang

### Mengapa "tidak terasa berubah"?

**Untuk Livewire components (key 1-4):**
- `$pollInterval` dibaca di method `render()` setiap kali Livewire re-render
- Livewire `wire:poll.Xs` interval **sudah di-set saat pertama kali render** dan **TIDAK berubah** tanpa full page reload
- Artinya: walau `render()` mengembalikan `$pollInterval` baru, directive `wire:poll.Xs` di root `<div>` sudah terpasang dengan interval lama

> **Kesimpulan:** Livewire `wire:poll` interval bersifat **immutable setelah initial render**. Mengubah `$pollInterval` di render() TIDAK otomatis mengubah interval polling yang sudah berjalan.

**Untuk vanilla JS (key 5-6):**
- Interval disimpan sebagai JavaScript `const` saat page load
- `setInterval()` sudah berjalan dengan interval lama
- Tidak ada mekanisme untuk update interval tanpa page reload

---

## 4. Ringkasan Status per Key

| # | Key | Mekanisme | Setting Terbaca? | Langsung Berubah Tanpa Reload? | Status |
|---|-----|-----------|-----------------|-------------------------------|--------|
| 1 | `attd_status_button` | Livewire `wire:poll` | ✅ Ya | ❌ Tidak — perlu reload | ⚠️ Partial |
| 2 | `broadcast_popup` | Livewire `wire:poll` | ✅ Ya | ❌ Tidak — perlu reload | ⚠️ Partial |
| 3 | `change_time_info` | Livewire `wire:poll` | ✅ Ya | ❌ Tidak — perlu reload | ⚠️ Partial |
| 4 | `raise_hand_manager` | Livewire `wire:poll` | ✅ Ya | ❌ Tidak — perlu reload | ⚠️ Partial |
| 5 | `toilet_monitor` | Vanilla JS `setInterval` | ✅ Ya (toilet & shalat only) | ❌ Tidak — perlu reload | ⚠️ Partial + ❌ izin-keluar tidak ada |
| 6 | `raise_hand_notification` | Vanilla JS `setInterval` | ✅ Ya | ❌ Tidak — perlu reload | ⚠️ Partial |

---

## 5. Rekomendasi Perbaikan

### Prioritas Tinggi 🔴

#### 5.1 Tambahkan auto-refresh ke `izin-keluar.blade.php`
Halaman ini tidak punya mekanisme refresh apapun. Tambahkan `@include('admin.partials.universal-permit-timer-script')`.

#### 5.2 Perjelas di UI bahwa perubahan berlaku setelah reload
Tambahkan notice di halaman Pengaturan setelah simpan:
> "Pengaturan berhasil disimpan. Perubahan interval akan berlaku saat halaman terkait dimuat ulang (refresh)."

### Prioritas Sedang 🟡

#### 5.3 Pertimbangkan migrasi izin toilet/shalat/keluar ke Livewire
Sudah ada `Admin\Permit\ToiletMonitor` Livewire component yang tidak terpakai. Pertimbangkan:
- Migrasi halaman `izin-toilet.blade.php`, `izin-shalat.blade.php`, `izin-keluar.blade.php` untuk menggunakan Livewire component
- Atau hapus Livewire component yang dead code

#### 5.4 Pisahkan key per jenis izin (opsional)
Saat ini 1 key `toilet_monitor` mengontrol 3 halaman (toilet, shalat, keluar). Jika ingin kontrol granular, bisa ditambahkan:
- `shalat_monitor` 
- `keluar_monitor`

### Prioritas Rendah 🟢

#### 5.5 Cache busting untuk JS static file
Tambahkan version query string pada `raise-hand-notifications.js`:
```blade
<script src="{{ asset('js/admin/raise-hand-notifications.js') }}?v={{ filemtime(public_path('js/admin/raise-hand-notifications.js')) }}"></script>
```

#### 5.6 Live update interval tanpa reload (advanced)
Untuk benar-benar membuat interval berubah real-time tanpa reload:
- **Livewire:** Gunakan `wire:poll` dengan dynamic interval via Alpine.js `x-init` yang membaca dari server
- **Vanilla JS:** Gunakan meta-polling — JS pertama fetch interval terbaru dari API endpoint, lalu update `setInterval` duration

---

## 6. Diagram Alur Sistem

```
┌─────────────────────┐
│   Admin: Halaman     │
│   Pengaturan Popup   │
│   /admin/pengaturan  │
│   /popup             │
└──────────┬──────────┘
           │ POST form
           ▼
┌─────────────────────┐
│ SettingController    │
│ updatePopupSettings()│
│ → save to DB         │
│ → clearAllCache()    │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ popup_settings table │
│ (MySQL)              │
│                      │
│ key: attd_status_... │
│ interval_seconds: 5  │
│ is_enabled: true     │
└──────────┬──────────┘
           │
     ┌─────┴──────────────────┐
     │                        │
     ▼                        ▼
┌──────────────┐    ┌──────────────────┐
│ Livewire     │    │ Vanilla JS       │
│ Components   │    │ (Layout/Partial) │
│              │    │                  │
│ Key 1-4:     │    │ Key 5:           │
│ render() {   │    │ timer-script.php │
│   getInterval│    │ → setInterval()  │
│   → $poll    │    │                  │
│ }            │    │ Key 6:           │
│              │    │ main.blade.php   │
│ wire:poll.Xs │    │ → window.__var   │
│ (IMMUTABLE   │    │ → notifications  │
│  setelah     │    │    .js           │
│  render      │    │ → setInterval()  │
│  pertama)    │    │                  │
└──────────────┘    └──────────────────┘
       │                      │
       ▼                      ▼
  ⚠️ Interval BARU        ⚠️ Interval BARU
  berlaku setelah          berlaku setelah
  FULL PAGE RELOAD         FULL PAGE RELOAD
```

---

## 7. Kesimpulan

**Sistem PopupSetting sudah benar secara arsitektur** — data tersimpan ke DB, cache dihapus saat update, dan semua consumer membaca dari `PopupSetting::getInterval()`.

**Masalah utama bukan di backend, tapi di frontend:**
1. **Livewire `wire:poll.Xs`** — interval di-set saat initial render dan tidak berubah tanpa reload halaman
2. **JavaScript `setInterval()`** — interval disimpan sebagai `const` dan tidak bisa diubah tanpa reload
3. **Halaman `izin-keluar`** — tidak include timer script sama sekali

**Solusi paling pragmatis:** Tambahkan pesan informasi di halaman Pengaturan yang menjelaskan bahwa perubahan interval berlaku setelah halaman terkait di-refresh/reload.
