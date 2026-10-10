# RANCANGAN LENGKAP & SPESIFIKASI TEKNIS: OPTIMALISASI SMART AUTO-SCHEDULING (OTOMATISASI JADWAL)

**Dokumen**: `docs/AUTOMASI_PLANS.md`  
**Status**: Draf Spesifikasi & Rencana Aksi Terpadu  
**Target Sistem**: Presensi & Penjadwalan Cerdas Djuragan (Laravel Backend & Blade UI)

---

## DAFTAR ISI
1. [Ringkasan Eksekutif & Sasaran Perubahan](#1-ringkasan-eksekutif--sasaran-perubahan)
2. [Hierarki Pola Alokasi Jadwal (Cascade Priority)](#2-hierarki-pola-alokasi-jadwal-cascade-priority)
3. [Arsitektur Rolling Mingguan (Shift & Hybrid 50%)](#3-arsitektur-rolling-mingguan-shift--hybrid-50)
4. [Penyatuan Setting Otomatisasi (One-Stop Automation Center)](#4-penyatuan-setting-otomatisasi-one-stop-automation-center)
5. [Filter & Tampilan Detail Hasil Jadwal per Kantor dan per Hari](#5-filter--tampilan-detail-hasil-jadwal-per-kantor-dan-per-hari)
6. [Fitur Analisis & Rekomendasi Setting Otomatis](#6-fitur-analisis--rekomendasi-setting-otomatis)
7. [Skema Database & Model Baru](#7-skema-database--model-baru)
8. [Rincian File yang Dimodifikasi & Ditambahkan](#8-rincian-file-yang-dimodifikasi--ditambahkan)
9. [Tahapan Implementasi & Verifikasi](#9-tahapan-implementasi--verifikasi)

---

## 1. RINGKASAN EKSEKUTIF & SASARAN PERUBAHAN

Sistem penjadwalan otomatis (*Smart Auto-Scheduling*) yang berjalan saat ini memiliki batasan struktural:
1. **Alokasi hybrid tidak berganti per minggu**: Pemagang yang terjadwal WFH di hari Senin akan selalu WFH di hari Senin pada minggu-minggu berikutnya karena pola hanya dihitung berdasarkan paritas hari dalam seminggu (`dayOfWeekIso % 2`).
2. **Rolling shift belum deterministik**: Pola `weekly_rotation` bergantung pada `$index` dinamis dalam koleksi divisi dan `weekOfYear` (yang reset saat pergantian tahun), menyebabkan rotasi dapat meloncat ketika ada pemagang baru atau pemagang selesai magang.
3. **Pengaturan tersebar di banyak menu terpisah**:
   - `Pengaturan Shift`: toggle rolling dan jam split shift.
   - `Edit Lokasi Kantor`: kapasitas kantor, kapasitas shift, dan centang divisi aktif.
   - `Sunting Anggota`: jenis penempatan (WFO/WFH/Hybrid/Lock Office/Blacklist Office).
   - `Pusat Aturan`: matriks kantor-divisi dan aturan konflik.
4. **Hasil simulasi sulit ditinjau secara mendalam**: Tampilan hasil simulasi masih berupa daftar tabel panjang tanpa filter per kantor atau per hari, sehingga admin kesulitan memantau okupansi harian kantor tertentu.

### Sasaran Utama Perbaikan:
- **Hierarki Cerdas**: Utamakan 100% WFO di shift utama terlebih dahulu. Jika kapasitas tidak muat, gunakan alternatif shifting dan split-shift (seperti shift siang WFH 3 jam), baru kemudian terapkan rolling hybrid 50%, dan terakhir fallback 50% ketat jika benar-benar padat.
- **Rolling Mingguan Nyata**:
  - Rolling shift per minggu (Minggu 1: Shift Pagi, Minggu 2: Shift Siang, dst).
  - Rolling hybrid 50% per minggu (Senin minggu ini WFO -> Senin minggu depan WFH).
- **One-Stop Automation Page**: Seluruh pengaturan yang memengaruhi mesin otomatisasi dapat dikelola langsung dari satu halaman `/admin/automation-rules`.
- **Filter Hasil Multidimensi**: Filter hasil simulasi/deploy berdasarkan Kantor, Hari/Tanggal, Shift, Divisi, dan Status Kehadiran (WFO vs WFH).

---

## 2. HIERARKI POLA ALOKASI JADWAL (CASCADE PRIORITY)

Engine penjadwalan baru (`AutoSchedulingEngine.php`) akan menjalankan 4 tingkatan alokasi (*cascading tiers*):

```
+-----------------------------------------------------------------------------------+
| [KONDISI KHUSUS MUTLAK] Pemagang dengan status "wfh_only" atau Sekolah Remote       |
| -> Selalu WFH 100%, Shift 1, tanpa alokasi kursi kantor (Tidak Dapat Diganggu Gugat) |
+-----------------------------------------------------------------------------------+
                                         |
                                         v
+-----------------------------------------------------------------------------------+
| TIER 1: PRIORITAS UTAMA - WFO PENUH & SHIFT TETAP (KAPASITAS MENCUKUPI)             |
| - Utamakan seluruh pemagang tetap WFO 100% di kantor terdekat (Haversine).        |
| - Tempatkan pada Shift Utama / Shift yang ditentukan tanpa pemotongan hari.       |
| - Cek kapasitas kursi concurrent kantor terdekat.                                  |
+-----------------------------------------------------------------------------------+
                                         |
                       (Jika Kapasitas Kantor Terdekat Penuh)
                                         v
+-----------------------------------------------------------------------------------+
| TIER 2: DISTRIBUSI SHIFT & SPLIT SHIFT (WFH 3 JAM)                                |
| - Alihkan pemagang ke shift alternatif (Pagi <-> Siang) yang kursinya masih ada.   |
| - Gunakan Shift dengan Split Shift (contoh: Siang WFH 3 jam di awal/akhir sesi)   |
|   sehingga jam kritis di kantor berkurang dan kapasitas fisik kantor meningkat.   |
| - Tetap mempertahankan kehadiran kerja harian.                                    |
+-----------------------------------------------------------------------------------+
                                         |
                       (Jika Seluruh Shift & Split Shift Penuh)
                                         v
+-----------------------------------------------------------------------------------+
| TIER 3: CAMPURAN HYBRID 50% DENGAN ROLLING MINGGUAN                               |
| - Aktifkan mode Hybrid 50:50 proporsional.                                        |
| - Bagi pemagang menjadi Grup A dan Grup B.                                        |
| - Minggu 1: Grup A WFO (Sen/Rab/Jum), Grup B WFH (Sen/Rab/Jum).                    |
| - Minggu 2: Pola berganti/dibalik secara otomatis (Senin WFO jadi WFH).           |
| - Kantor terisi seimbang ±50% setiap hari kerja.                                  |
+-----------------------------------------------------------------------------------+
                                         |
                (Jika Tetap Melebihi Batas Ekstrem / Overcrowded Berat)
                                         v
+-----------------------------------------------------------------------------------+
| TIER 4: HARD FALLBACK 50% & SPILLOVER KANTOR TERDEKAT BERIKUTNYA                   |
| - Opsi A: Tawarkan kursi di kantor cabang terdekat ke-2 yang masih ada kuota.     |
| - Opsi B: Paksakan kuota 50% ketat dengan penugasan WFH bergilir penuh.           |
| - Cantumkan catatan status dan peringatan (Warning) agar admin dapat me-review.  |
+-----------------------------------------------------------------------------------+
```

### Rincian Aturan Tiap Tier:
1. **Pengecualian Mutlak (`wfh_only` & Sekolah Remote)**:
   - Dideteksi di awal perulangan.
   - Tidak pernah dipaksa WFO atau masuk hitungan kapasitas kursi kantor.
2. **Tier 1 (WFO Tetap)**:
   - Berlaku untuk pemagang bertipe `wfo_only` dan `auto_office` selama kantor terdekat belum *overcrowded*.
   - Jika kuota kantor masih cukup, pemagang mendapatkan 6 hari kerja WFO (atau sesuai hari kerja shift).
3. **Tier 2 (Shifting & Split-Shift WFH 3 Jam)**:
   - Fitur Split Shift yang sudah ada di database (`is_split_shift`, `split_wfh_start`, `split_wfh_end`, `split_wfo_start`, `split_wfo_end`) diintegrasikan ke perhitungan kapasitas concurrent (`canAccommodateConcurrent`).
   - Karena pada jam WFH pemagang berada di kos/rumah, beban kapasitas kantor pada rentang jam tersebut adalah 0 kursi, memungkinkan pemagang shift pagi dan shift siang bertukar kursi secara dinamis.
4. **Tier 3 (Hybrid 50% Rolling Mingguan)**:
   - Dipicu jika total demand pemagang divisi > kapasitas maksimal harian kantor.
   - Menerapkan rumus paritas minggu (dijelaskan pada Bagian 3).
5. **Tier 4 (Hard Fallback)**:
   - Memastikan tidak ada bentrok kehadiran fisik yang melebihi batas bahaya kantor, dengan log audit status yang transparan di tabel hasil simulasi.

---

## 3. ARSITEKTUR ROLLING MINGGUAN (SHIFT & HYBRID 50%)

### A. Konsep Tanggal Acuan (*Anchor Date*)
Untuk menghindari masalah `weekOfYear` bawaan PHP/Carbon yang mereset setiap 1 Januari dan menghasilkan anomali di akhir tahun:
- Sistem menggunakan **Tanggal Acuan Rotasi Global** (`rotation_anchor_date`), default: `2026-01-05` (Senin awal tahun).
- Indeks minggu dihitung menggunakan perbedaan minggu kalender:
  $$\text{weekNumber} = \lfloor \frac{\text{currentDate} - \text{anchorDate}}{7 \text{ hari}} \rfloor$$
- Perhitungan ini bersifat absolut, linier, dan tidak akan meloncat saat pergantian tahun.

### B. Rumus Rolling Hybrid 50% Mingguan
Pemagang dalam divisi dikelompokkan ke dalam 2 grup stabil:
- **Grup A** (`hybrid_group = 'A'` atau ID genap stabil)
- **Grup B** (`hybrid_group = 'B'` atau ID ganjil stabil)

Penentuan status hari kerja:
$$\text{dayIndex} = \text{dayOfWeekIso} \quad (\text{Senin} = 1, \dots, \text{Sabtu} = 6)$$
$$\text{parity} = (\text{dayIndex} + \text{groupOffset} + \text{weekNumber}) \pmod 2$$
$$\text{workType} = \begin{cases} \text{WFO}, & \text{jika } \text{parity} = 0 \\ \text{WFH}, & \text{jika } \text{parity} = 1 \end{cases}$$

#### Tabel Simulasi Jadwal Grup A:
| Periode | Senin | Selasa | Rabu | Kamis | Jumat | Sabtu |
|---|:---:|:---:|:---:|:---:|:---:|:---:|
| **Minggu 1** | **WFO** | WFH | **WFO** | WFH | **WFO** | WFH |
| **Minggu 2** | **WFH** | **WFO** | **WFH** | **WFO** | **WFH** | **WFO** |
| **Minggu 3** | **WFO** | WFH | **WFO** | WFH | **WFO** | WFH |
| **Minggu 4** | **WFH** | **WFO** | **WFH** | **WFO** | **WFH** | **WFO** |

*Catatan: Grup B memiliki pola berkebalikan dari Grup A pada minggu yang sama. Dengan demikian, kantor setiap hari diisi tepat 50% kapasitas secara bergantian.*

### C. Rumus Rolling Shift Mingguan (Pagi / Siang)
Shift yang diikutsertakan dalam rolling adalah shift yang memiliki penanda `is_rolling = true`.
Misalkan daftar shift rolling aktif adalah $[S_1, S_2]$:
$$\text{shiftIndex} = (\text{weekNumber} + \text{internStaggerOffset}) \pmod N$$
- **Minggu 1**: Pemagang kelompok 1 mendapat $S_1$ (Pagi), kelompok 2 mendapat $S_2$ (Siang).
- **Minggu 2**: Pemagang kelompok 1 mendapat $S_2$ (Siang), kelompok 2 mendapat $S_1$ (Pagi).
- Rolling shift berjalan harmonis bersamaan dengan rolling WFO/WFH.

---

## 4. PENYATUAN SETTING OTOMATISASI (ONE-STOP AUTOMATION CENTER)

Seluruh komponen pengaturan yang sebelumnya tersebar di berbagai menu dirangkum ke dalam satu antarmuka terpusat di `resources/views/admin/automation-rules.blade.php`:

### Struktur Tab pada Halaman Otomatisasi:
```
+--------------------------------------------------------------------------------------------------------------------------+
|  [Pusat Aturan & Otomatisasi Jadwal Djuragan]                                                                             |
+--------------------------------------------------------------------------------------------------------------------------+
|  [Tab 1: Matriks Penempatan] | [Tab 2: Kapasitas Kantor & Shift] | [Tab 3: Aturan Divisi & Konflik]                       |
|  [Tab 4: Rolling Shift (BARU)] | [Tab 5: Kebijakan Pemagang (BARU)] | [Tab 6: Parameter Global & AI] | [Tab 7: Simulator] |
+--------------------------------------------------------------------------------------------------------------------------+
```

### Detail Tab Baru:
1. **Tab 4: Rolling Shift & Jam Kerja (`tab-shift-rolling`)**:
   - Menampilkan seluruh shift kerja.
   - Switch toggle `is_rolling` per shift (menentukan shift mana saja yang ikut rotasi mingguan).
   - Konfigurasi parameter Split Shift: jam mulai WFH, jam mulai WFO, durasi WFH (misal 3 jam).
   - Pengaturan urutan rotasi default divisi.
2. **Tab 5: Kebijakan Pemagang Massal (`tab-intern-policies`)**:
   - Menampilkan tabel seluruh pemagang aktif.
   - Kolom dapat diedit langsung (*inline / bulk update*):
     - **Tipe Penempatan**: Dropdown (`auto_office`, `wfo_only`, `wfh_only`, `hybrid`).
     - **Grup Hybrid**: Dropdown (`Auto`, `Grup A`, `Grup B`).
     - **Kunci Kantor (Lock)**: Pilih kantor spesifik jika wajib di sana.
     - **Blacklist Kantor**: Pilih kantor yang dilarang bagi pemagang tersebut.
     - **Titik Koordinat Kos**: Status koordinat (Tersedia / Belum Ada Link GMaps).
   - Tombol **Simpan Kebijakan Pemagang Massal** (tanpa perlu membuka halaman sunting satu per satu).
3. **Tab 6: Parameter Global Mesin Otomatisasi (`tab-global-settings`)**:
   - **Tanggal Acuan Rotasi**: Input tanggal dasar perhitungan minggu.
   - **Toleransi Jarak Maksimal (KM)**: Batas radius kantor yang dapat dijangkau pemagang.
   - **Threshold Overcrowded (%)**: Ambang batas okupansi untuk memicu peralihan tier.
   - **Pengaturan Hari Libur & Hari Minggu**: Toggle `include_sundays` dan `skip_holidays`.
   - Tombol **"Analisis & Rekomendasikan Setting Otomatis"** (lihat Bagian 6).

---

## 5. FILTER & TAMPILAN DETAIL HASIL JADWAL PER KANTOR DAN PER HARI

Pada panel Simulator & Hasil Deploy (`tab-simulator`), bagian peninjauan jadwal diperkaya dengan **bilah filter interaktif multidimensi** dan **visualisasi okupansi**:

### A. Bilah Filter Hasil Jadwal (Interactive Filter Bar)
```
+--------------------------------------------------------------------------------------------------------------------------+
| FILTER HASIL JADWAL:                                                                                                     |
| [Pilih Kantor: Semua Kantor v] [Pilih Tanggal/Hari: Semua Tanggal v] [Pilih Shift: Semua v] [Tipe: WFO & WFH v] [Cari Nama...] |
+--------------------------------------------------------------------------------------------------------------------------+
```
- **Filter Kantor (`#filter_office_id`)**: Menampilkan alokasi hanya untuk kantor terpilih (misal: "Kantor 1 Banguntapan" atau "Kantor 2 Condongcatur").
- **Filter Hari / Tanggal (`#filter_date`)**: Menampilkan jadwal pada tanggal tertentu (misal: "Senin, 06 Okt 2026") lengkap dengan nama hari.
- **Filter Shift (`#filter_shift_id`)**: Memfilter Shift Pagi, Shift Siang, atau Shift Khusus.
- **Filter Tipe Kehadiran (`#filter_work_type`)**: Pilihan `Semua`, `WFO Saja`, `WFH Saja`, `Split-Shift (WFH 3 Jam)`.
- **Filter Divisi (`#filter_division_id`)**: Meninjau jadwal per divisi tertentu.
- **Pencarian Realtime (`#filter_search`)**: Pencarian instan berdasarkan nama pemagang atau NIM.

### B. Kartu Ringkasan Okupansi Harian (Daily Headcount vs Capacity)
Di atas tabel alokasi, ditampilkan kartu metrik per kantor untuk tanggal yang sedang difilter:
- **Kapasitas Kursi Kantor**: `X Kursi`
- **Total Pemagang WFO Hari Ini**: `Y Orang`
- **Tingkat Keterisian**: `(Y / X * 100)%`
- **Badge Status**:
  - Hijau: Aman ($\le 85\%$)
  - Kuning: Penuh Optimal ($86\% - 100\%$)
  - Merah: Melebihi Kapasitas ($> 100\%$)

### C. Kolom Baru pada Tabel Hasil Alokasi
Tabel alokasi (`simulation-results-table`) memuat informasi:
1. **Tanggal & Hari**: (Contoh: *Senin, 06 Okt 2026*)
2. **Minggu Ke**: (Contoh: *Minggu 1* atau *Minggu 2*)
3. **Nama Pemagang & Divisi**
4. **Kantor Alokasi**: Nama kantor + jarak kos (km).
5. **Shift**: Nama shift + jam operasional.
6. **Tipe Kerja**: Badge `WFO`, `WFH`, atau `Split (WFH 3j + WFO)`.
7. **Pola Hybrid**: `Grup A`, `Grup B`, atau `Non-Hybrid`.
8. **Status / Catatan Eksekusi**: Tier yang digunakan (*Tier 1: WFO Murni*, *Tier 2: Split Shift*, *Tier 3: Rolling Hybrid*, dst).

### D. Fitur Pemilihan Pemagang Selektif (Selective Intern Scheduling)
Admin dapat memilih hanya pemagang tertentu yang akan digenerate jadwalnya, tanpa mengganggu atau menimpa jadwal pemagang lainnya:
1. **Opsi Cakupan pada Form Simulasi**:
   - Radio pilihan: `Semua Pemagang dalam Divisi` vs `Pilih Pemagang Tertentu (Kustom)`.
2. **Panel Seleksi Pemagang (Interactive Intern Picker)**:
   - Jika opsi kustom dipilih, tampil daftar pemagang dengan checkbox:
     - Checkbox tiap pemagang (Nama, NIM, Divisi, Kantor Terdekat).
     - Tombol cepat **"Centang Semua"** dan **"Hapus Centang"**.
     - Input pencarian cepat nama pemagang / NIM.
     - Badge counter dinamis: *"X pemagang terpilih"*.
3. **Logika Proteksi Jadwal Non-Target**:
   - Pemagang yang **tidak dipilih** tidak akan disentuh/di-overwrite sama sekali.
   - Kursi kantor yang sudah ditempati pemagang lain di tanggal & jam yang sama tetap dihitung dalam `canAccommodateConcurrent`, sehingga alokasi pemagang terpilih tidak akan menabrak kursi yang sudah terisi.

### E. Fitur Simulasi / Kunci di 1 Kantor Tertentu (Target Office Locking)
Admin dapat mengunci simulasi agar seluruh atau sebagian pemagang yang dipilih diarahkan khusus ke **1 kantor tertentu saja**:
1. **Pilihan Target Kantor pada Form Simulasi**:
   - Pilihan: `Otomatis (Jarak Kos Terdekat & Matriks Divisi)` *(default)* vs `Kunci ke 1 Kantor Tertentu`.
   - Dropdown pilihan kantor tujuan (contoh: *Kantor 1 - Banguntapan*).
2. **Perilaku Mesin Penjadwalan**:
   - Seluruh pemagang yang dipilih akan diposisikan untuk mengisi kantor target tersebut.
   - Mesin melakukan analisis kapasitas terfokus pada kantor itu:
     - Mengisi kursi WFO penuh jika masih muat (Tier 1).
     - Jika kapasitas kantor tersebut tidak muat, menerapkan shifting & split-shift WFH 3 jam (Tier 2).
     - Jika masih melebihi kapasitas, otomatis menerapkan rolling hybrid 50% di kantor tersebut (Tier 3).
3. **Kombinasi Fleksibel**:
   - Admin dapat menggabungkan: **Pilih 5 Pemagang Tertentu** + **Kunci ke Kantor 1** -> Mesin langsung menyusun jadwal shift dan WFO/WFH khusus untuk 5 orang tersebut di Kantor 1 tanpa menyentuh pemagang lain atau kantor lain!

### F. Pembatasan Periode Magang & Notifikasi Belum Diset (Internship Period Boundaries)
Mesin penjadwalan secara ketat mematuhi rentang tanggal masuk (`start_date`) dan tanggal selesai (`end_date`) magang tiap pemagang:
1. **Jadwal Tidak Melampaui Akhir Masa Magang**:
   - Jika simulasi dijalankan untuk 1 minggu atau 1 bulan penuh, tetapi seorang pemagang selesai magang 4 hari lagi (`currentDate > intern->end_date`), maka jadwal untuk pemagang tersebut **hanya dibuat hingga hari ke-4 saja**. Sistem tidak akan memperpanjang atau meluberkan jadwal melebihi masa magangnya.
   - Demikian pula jika pemagang baru aktif di tengah periode (`currentDate < intern->start_date`), jadwal baru dibuat sejak tanggal mulai magangnya.
2. **Proteksi Pemagang Tanpa Periode Magang**:
   - Pemagang yang belum diisi `start_date` atau `end_date`-nya oleh admin (masih `null`/kosong) otomatis **dilewati (tidak dibuatkan jadwal)** agar tidak menimbulkan data presensi yang salah.
3. **Notifikasi Ringkas di Atas Tabel Hasil**:
   - Tepat di atas tabel rincian alokasi hasil simulasi/deploy, muncul catatan kecil yang simpel:
     > ⚠️ **Catatan**: *Beberapa pemagang belum bisa didaftarkan jadwal karena belum diset periode magangnya.*
   - Disertai daftar ringkas nama pemagang yang belum diset dan tautan cepat untuk melengkapi tanggal magang mereka pada tab Kebijakan Pemagang.

---

## 6. FITUR ANALISIS & REKOMENDASI SETTING OTOMATIS

Tombol **"Analisis & Rekomendasikan Setting"** membantu admin menentukan konfigurasi optimal tanpa tebak-tebakan:

### Logika Algoritma Rekomendasi:
1. Hitung total pemagang aktif per divisi ($N$).
2. Hitung total kapasitas kursi kantor yang terhubung pada matriks divisi ($C = \sum \text{kapasitasKantor}$).
3. Hitung rasio beban:
   $$\text{LoadRatio} = \frac{N}{C}$$
4. **Keputusan Rekomendasi**:
   - Jika $\text{LoadRatio} \le 1.0$:
     - Rekomendasi: **Tier 1 (WFO Murni)**.
     - Mode shift: `balanced_quota` (proporsional).
     - Hybrid balancing: **Nonaktif** (kapasitas kantor mencukupi semua pemagang).
   - Jika $1.0 < \text{LoadRatio} \le 1.5$:
     - Rekomendasi: **Tier 2 (Shifting & Split-Shift)**.
     - Mode shift: `weekly_rotation` (rotasi pagi/siang).
     - Aktifkan split shift pada shift siang untuk membagi jam duduk kantor.
   - Jika $1.5 < \text{LoadRatio} \le 2.0$:
     - Rekomendasi: **Tier 3 (Hybrid 50% Rolling Mingguan)**.
     - Mode shift: `weekly_rotation`.
     - Hybrid balancing: **Aktif (50:50 Rolling)**.
   - Jika $\text{LoadRatio} > 2.0$:
     - Rekomendasi: **Tier 4 (Hybrid 50% + Multi-Office Spillover + Split-Shift)**.
     - Admin disarankan mengaktifkan kantor alternatif tambahan pada matriks penempatan.

Admin dapat meninjau kartu rekomendasi dan menekan tombol **"Terapkan Rekomendasi Ini"** untuk otomatis memperbarui input form.

---

## 7. SKEMA DATABASE & MODEL BARU

### A. Tabel Baru: `automation_settings`
Menyimpan konfigurasi parameter global mesin otomatisasi.
```sql
CREATE TABLE automation_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```
**Data Default**:
- `rotation_anchor_date`: `'2026-01-05'`
- `default_hybrid_pattern`: `'daily_alternating'` (opsi: `daily_alternating`, `block_3days`)
- `overcrowded_threshold_percent`: `'100'`
- `include_sundays`: `'0'`
- `skip_holidays`: `'1'`
- `enable_tier2_split_shift`: `'1'`
- `enable_tier3_hybrid_rolling`: `'1'`

### B. Kolom Tambahan pada Tabel `interns`
Menyimpan preferensi grup hybrid individual yang persisten:
```sql
ALTER TABLE interns 
ADD COLUMN hybrid_group ENUM('auto', 'A', 'B') DEFAULT 'auto' AFTER work_placement_type;
```

### C. Kolom Tambahan pada Tabel `detail_schedules`
Memudahkan filter hasil jadwal secara terindeks:
```sql
ALTER TABLE detail_schedules 
ADD COLUMN execution_tier TINYINT UNSIGNED DEFAULT 1 AFTER hybrid_pattern,
ADD INDEX idx_detail_sched_filter (date, office_id, shift_id, work_type);
```

---

## 8. RINCIAN FILE YANG DIMODIFIKASI & DITAMBAHKAN

| No | File Path | Tipe Aksi | Deskripsi Perubahan |
|---|---|:---:|---|
| 1 | `app/Models/AutomationSetting.php` | **Baru** | Model Eloquent dengan helper `get($key, $default)` dan `set($key, $value)` ter-cache. |
| 2 | `database/migrations/2026_10_05_130000_create_automation_settings_table.php` | **Baru** | Migrasi tabel `automation_settings`. |
| 3 | `database/migrations/2026_10_05_130001_add_hybrid_group_to_interns_table.php` | **Baru** | Migrasi penambahan kolom `hybrid_group` di `interns`. |
| 4 | `database/migrations/2026_10_05_130002_add_execution_tier_to_detail_schedules_table.php` | **Baru** | Migrasi penambahan kolom `execution_tier` dan indeks filter. |
| 5 | `app/Services/AutoSchedulingEngine.php` | **Modifikasi** | Implementasi 4 Tier Cascading Priority, formula rolling mingguan shift & hybrid 50%, kalkulasi concurrent dengan split-shift, dan metadata filter. |
| 6 | `app/Http/Controllers/AutomationRuleController.php` | **Modifikasi** | Penambahan method: `saveGlobalSettings()`, `saveShiftRolling()`, `saveBulkInternPolicies()`, `getRecommendation()`, dan penyiapan data untuk tab-tab baru. |
| 7 | `routes/web.php` | **Modifikasi** | Penambahan rute POST untuk simpan setting global, rolling shift, bulk intern, dan rekomendasi otomatis. |
| 8 | `resources/views/admin/automation-rules.blade.php` | **Modifikasi** | Penambahan Tab 4 (Rolling Shift), Tab 5 (Kebijakan Pemagang Massal), Tab 6 (Parameter Global), Bilah Filter Hasil Jadwal (Kantor, Hari, Shift, Tipe), Kartu Metrik Okupansi Harian, dan Panel Rekomendasi. |

---

## 9. TAHAPAN IMPLEMENTASI & VERIFIKASI

### Fase 1: Database & Model (Fondasi)
1. Buat file migrasi tabel `automation_settings`, kolom `hybrid_group` pada `interns`, dan kolom `execution_tier` pada `detail_schedules`.
2. Buat model `app/Models/AutomationSetting.php`.
3. Daftarkan relasi dan casting pada model `Intern.php` dan `DetailSchedule.php`.

### Fase 2: Mesin Engine (`AutoSchedulingEngine.php`)
1. Integrasikan pengambilan konfigurasi dari `AutomationSetting`.
2. Implementasikan logika Tier 1 (WFO Penuh) -> Tier 2 (Split Shift Siang WFH 3 Jam) -> Tier 3 (Hybrid 50% Rolling Mingguan) -> Tier 4 (Hard Fallback).
3. Terapkan rumus linier tanggal acuan (`rotation_anchor_date`) untuk rolling shift mingguan dan rolling WFO/WFH mingguan.
4. Sertakan metadata lengkap (`execution_tier`, `week_number`, `hybrid_group`, `distance_km`) pada payload hasil alokasi.

### Fase 3: Controller & Rute (`AutomationRuleController.php` & `routes/web.php`)
1. Tambahkan endpoint:
   - `POST /admin/automation-rules/global-settings`
   - `POST /admin/automation-rules/shift-rolling`
   - `POST /admin/automation-rules/bulk-interns`
   - `POST /admin/automation-rules/recommendation`
2. Kirim data pemagang aktif, data shift lengkap, dan setting global ke tampilan `admin.automation-rules`.

### Fase 4: Antarmuka Blade & Filter Interaktif (`automation-rules.blade.php`)
1. Susun tab baru: Shift & Rolling, Kebijakan Pemagang Massal, Parameter Global.
2. Bangun panel bilah filter hasil simulasi/deploy:
   - Filter dropdown Kantor
   - Filter dropdown Tanggal / Hari
   - Filter dropdown Shift
   - Filter dropdown Tipe Kehadiran (WFO / WFH / Split)
   - Input pencarian nama / NIM pemagang
3. Bangun ringkasan kartu kapasitas harian (Headcount vs Kursi Kantor).
4. Tambahkan tombol dan modal rekomendasi cerdas.

### Fase 5: Pengujian & Validasi
1. Uji skenario pemagang `wfh_only`: Pastikan selalu WFH 100% dan tidak pernah menduduki kursi kantor.
2. Uji skenario rolling hybrid 50%:
   - Verifikasi bahwa di Minggu 1 pemagang Grup A hadir WFO di hari Senin.
   - Verifikasi bahwa di Minggu 2 pemagang Grup A dijadwalkan WFH di hari Senin.
   - Verifikasi okupansi kantor di hari Senin Minggu 1 dan Minggu 2 tetap seimbang ~50%.
3. Uji skenario rolling shift:
   - Verifikasi pemagang bertukar Shift 1 dan Shift 2 saat berganti minggu.
4. Uji bilah filter:
   - Filter Kantor 1 -> pastikan hanya alokasi Kantor 1 yang tampil.
   - Filter Tanggal tertentu -> pastikan hanya pemagang di tanggal tersebut yang tampil beserta kartu kapasitasnya.

---
*Dokumen ini disusun sebagai panduan implementasi resmi untuk optimalisasi sistem penjadwalan otomatis.*
