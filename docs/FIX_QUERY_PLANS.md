# 📊 FIX QUERY PLANS — Optimasi Performa Database

> **Tanggal Audit:** 24 September 2026
> **Proyek:** Absen Djuragan (Laravel 10)
> **Cakupan:** Controllers, Services, Repositories, Livewire Components, Models, Blade Views

---

## 📈 Ringkasan Eksekutif

Audit menyeluruh menemukan **52 temuan unik** masalah performa database di seluruh backend:

|  Severity   | Jumlah | Deskripsi                                                                      |
| :---------: | :----: | :----------------------------------------------------------------------------- |
| 🔴 CRITICAL |   10   | Query di polling Livewire, N+1 masif (100-600 query/request), bulk tanpa batch |
|   🟠 HIGH   |   16   | N+1 pada export PDF/CSV, view blade tanpa eager load, query di accessor model  |
|  🟡 MEDIUM  |   18   | Query agregasi bisa digabung, duplikasi query, in-memory processing            |
|   🟢 LOW    |   8    | Lazy loading pada single record, dead code, model duplikat                     |

---

## 📋 Daftar Status Perbaikan

|  #  | Kategori                              |   Status   |
| :-: | :------------------------------------ | :--------: |
|  1  | Query Database di Polling Livewire    | ✅ Selesai |
|  2  | N+1 Masif pada Endpoint Utama         | ✅ Selesai |
|  3  | Bulk Operations Tanpa Batch           | ✅ Selesai |
|  4  | N+1 pada Export PDF/CSV & Laporan     | ✅ Selesai |
|  5  | N+1 pada Controller & View Blade      | ✅ Selesai |
|  6  | Query di Accessor/Method Model        | ✅ Selesai |
|  7  | Query Agregasi Bisa Digabung          | ✅ Selesai |
|  8  | Query Redundan & Duplikasi            | ✅ Selesai |
|  9  | In-Memory Processing & Missing Select | ✅ Selesai |
| 10  | Query INSERT/UPDATE di Dalam Loop     | ✅ Selesai |
| 11  | Arsitektur & Dead Code                | ✅ Selesai |
| 12  | Lazy Loading Minor & Cleanup          | ✅ Selesai |

---

## 🔴 Kategori 1: Query Database di Polling Livewire

> **Dampak:** Sangat tinggi — query dieksekusi berulang setiap 3-10 detik per user yang membuka halaman. Dengan 20 user aktif simultan, server menerima ribuan query per menit.

### 1.1 Query DB & UPDATE di Blade View `attd-status-button` (Polling 5 detik)

- **Status:** ✅ Selesai
- **File:** `resources/views/livewire/attd-status-button.blade.php` & `app/Livewire/AttdStatusButton.php`
- **Severity:** 🔴 CRITICAL
- **Masalah:** File Blade berisi blok `@php` yang menjalankan **10-14 query SELECT** dan **1 query UPDATE** (`PermitLog::closeExpiredLeavePermits()`) langsung di template. Dieksekusi setiap 5 detik via `wire:poll.5s`.
- **Dampak:** 20 user × 12 query × 12x/menit = **~2.880 query/menit** hanya dari satu komponen.
- **Solusi:** Seluruh kalkulasi status kehadiran, limit izin, dan penutupan izin expired dipindahkan ke `AttdStatusButton::render()`. Blok query di file Blade template dihapus bersih.

### 1.2 Full Table Scan di `BroadcastPopup` (Polling 10 detik)

- **Status:** ✅ Selesai
- **File:** `app/Livewire/BroadcastPopup.php` (L69-L104)
- **Severity:** 🔴 CRITICAL
- **Masalah:** Method `checkForBroadcasts()` menarik **SELURUH** record dari tabel `broadcasts` beserta 5 relasi many-to-many (`divisions`, `users`, `shifts`, `offices`, `images`) lalu filter di memori PHP. Dipanggil setiap 10 detik via polling.
- **Dampak:** Seiring bertambahnya data broadcast, query semakin berat dan memori PHP melonjak. 20 user = 120 full table scan per menit.
- **Solusi:** Filter pemfilteran target shift, divisi, user, dan office dipindahkan langsung ke database query builder SQL menggunakan `whereNotIn`, `whereHas`, dan `->first()`.

### 1.3 8 Query COUNT Terpisah di `RaiseHandManager` (Polling 3 detik)

- **Status:** ✅ Selesai
- **File:** `app/Livewire/Admin/RaiseHandManager.php`, `resources/views/livewire/admin/raise-hand-manager.blade.php`, `app/Models/HandRaise.php`
- **Severity:** 🔴 CRITICAL
- **Masalah:**
    1. 8 query `COUNT(*)` terpisah dieksekusi setiap 3 detik.
    2. Query master `Division::orderBy('name')->get()` diambil ulang setiap 3 detik padahal data statis.
    3. N+1 query di accessor `HandRaise::getEffectiveShiftAttribute()` pada setiap baris tabel presentasi.
- **Dampak:** 8+ query × 20 per menit × jumlah admin = ratusan query per menit untuk data yang jarang berubah.
- **Solusi:**
    1. 8 query count digabung menjadi 1 query conditional aggregation `selectRaw('COUNT(CASE WHEN...)')`.
    2. Master data `$divisions` dimuat di `mount()` sekali saja.
    3. Polling dinaikkan menjadi `wire:poll.10s`.
    4. Accessor `getEffectiveShiftAttribute()` memeriksa `relationLoaded` sebelum mengeksekusi query database.

---

## 🔴 Kategori 2: N+1 Masif pada Endpoint Utama

> **Dampak:** Endpoint yang paling sering diakses memicu 100-500+ query per request.

### 2.1 N+1 pada Tabel Presensi Intern (`AttendanceService::getInternAttendance`)

- **Status:** ✅ Selesai
- **File:** `app/Services/AttendanceService.php` (L754-L845)
- **Severity:** 🔴 CRITICAL
- **Masalah:** Loop paginasi mengakses 10 relasi lazy loading per baris: `schedule.intern.user.profile`, `permitReason`, `attdStatus`, `shift`, `attendance`, `adjustableAttendance`, `logActivity`. Repository `DetailScheduleRepositoryIMPL::findByCriteria` tidak melakukan `->with()`.
- **Dampak:** 15 data/halaman × 10 relasi = **150+ query**. Halaman 50 data = **500+ query**.
- **Solusi:** Ditambahkan eager loading relasi komprehensif pada hasil paginasi (`$result->load([...])`) sebelum iterasi pemetaan data.

### 2.2 N+1 pada Laporan Presensi (`AttendanceService::attendanceReport`)

- **Status:** ✅ Selesai
- **File:** `app/Services/AttendanceService.php` (L963-L998)
- **Severity:** 🔴 CRITICAL
- **Masalah:**
    1. Eager loading `with(['schedules.detailSchedules'])` memuat ribuan row ke memori tapi **tidak dipakai** di loop.
    2. Di dalam loop, `$intern->schedules()` memulai query builder baru (bukan menggunakan relasi yang sudah di-load).
    3. `$intern->user->profile` tidak di-eager load — N+1 lazy loading.
    4. Redundant `$internRepository->count()` padahal `$interns->total()` sudah tersedia dari `paginate()`.
- **Dampak:** 100 intern per halaman = 100 query agregasi + 200 lazy loading query.
- **Solusi:** Diubah menjadi `Intern::with('user.profile')`. Seluruh statistik kehadiran pemagang pada halaman aktif dihitung dalam **1 query agregasi join** dengan `whereIn('schedules.intern_id', $internIds)` lalu di-`keyBy('intern_id')`.

### 2.3 N+1 pada Presensi Otomatis (`AttendanceService::shortAutomaticAttendance`)

- **Status:** ✅ Selesai
- **File:** `app/Services/AttendanceService.php` (L1862-L1870) & `app/Repositories/Implementation/AttendanceRepositoryIMPL.php`
- **Severity:** 🔴 CRITICAL
- **Masalah:** Repository method (`getAllAutoEnd`, `getAutoEndStatusByDate`) mengembalikan model tanpa relasi. Loop mengakses `$item->detailSchedules->schedule->intern->user->profile` dan `office`, `shift` — **7 query per baris**.
- **Dampak:** 20 data = **140 query database**.
- **Solusi:** Ditambahkan eager loading berjenjang di seluruh method repository terkait (`with(['detailSchedules.office', 'detailSchedules.shift', 'detailSchedules.schedule.intern.user.profile'])`) dan proteksi `loadMissing` di service.

### 2.4 N+1 pada Notifikasi Alpha Massal (`NotificationService`)

- **Status:** ✅ Selesai
- **File:** `app/Services/NotificationService.php` (L83-L104)
- **Severity:** 🔴 CRITICAL
- **Masalah:** `notifyAllOutsidersForAlphaToday` mengambil `DetailSchedule::get()` tanpa eager loading. Di dalam loop, `$schedule->schedule->intern`, `->user->profile`, `->whatsappNumber`, `->outsiders()` semuanya lazy-loaded.
- **Dampak:** 30 pemagang Alpha = **210-240 query** saat cron job berjalan.
- **Solusi:** Ditambahkan eager loading relasi lengkap `with(['schedule.intern.user.profile', 'schedule.intern.whatsappNumber', 'schedule.intern.outsiders.user.profile'])` dan optimasi evaluasi relasi preloaded pada `notifyOutsidersForAlpha`.

---

## 🔴 Kategori 3: Bulk Operations Tanpa Batch

> **Dampak:** Request timeout, deadlock database, response time 5-30+ detik.

### 3.1 Bulk Update Shift — 600 Query UPDATE Individual

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/ShiftController.php` (L139-L152)
- **Severity:** 🔴 CRITICAL
- **Masalah:** `foreach ($schedulesToUpdate as $schedule) { $schedule->update(['shift_id' => $shiftId]); }` — 20 pemagang × 30 hari = **600 query UPDATE individual**.
- **Solusi:** Diganti menjadi 1 query bulk update langsung pada query builder (`DetailSchedule::whereHas(...)->whereBetween(...)->update(['shift_id' => $shiftId])`).

### 3.2 Pembuatan Jadwal — 150-300 Query INSERT

- **Status:** ✅ Selesai
- **File:** `app/Services/ScheduleService.php` (L119-L144)
- **Severity:** 🔴 CRITICAL
- **Masalah:** While-loop menjalankan `attendanceRepository->create()` + `detailScheduleRepository->create()` per hari kerja. Jadwal 3-6 bulan = **150-300 INSERT individual**.
- **Solusi:** Mengumpulkan data tanggal ke array penampung, lalu melakukan bulk insert `Attendance::insert()` dan `DetailSchedule::insert()` hanya dalam 3 query.

### 3.3 Hapus Pemagang — 200+ Cascading DELETE

- **Status:** ✅ Selesai
- **File:** `app/Services/InternService.php` (L217-L258)
- **Severity:** 🟠 HIGH
- **Masalah:** Triple nested loop `foreach (schedules → detailSchedules → delete)` dengan lazy loading di setiap level. Menghapus 1 pemagang = **200+ query DELETE dan SELECT**.
- **Solusi:** Diubah menggunakan bulk delete `whereIn` pada semua entitas (`AdjustableAttd`, `DetailSchedule`, `Attendance`, `DiscountTime`, `Schedule`).

### 3.4 Bulk End-Time — UPDATE total_min di Loop

- **Status:** ✅ Selesai
- **File:** `app/Repositories/Implementation/AttendanceRepositoryIMPL.php` (L289-L297)
- **Severity:** 🟠 HIGH
- **Masalah:** Setelah bulk update `end_time`, mengambil seluruh record lalu loop `$attendance->update(['total_min' => ...])` satu per satu.
- **Solusi:** Perhitungan `total_min` dihitung langsung di dalam 1 query bulk update SQL menggunakan `TIMESTAMPDIFF(MINUTE, attendances.start_time, '{$endTime}')`.

---

## 🟠 Kategori 4: N+1 pada Export PDF/CSV & Laporan

### 4.1 Export PDF Seluruh Pemagang — N+1 `AdjustableAttd`

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/AttendanceController.php` (L671-L708)
- **Severity:** 🟠 HIGH
- **Masalah:** Nested loop 3 tingkat (`intern → schedule → detailSchedule`) menjalankan `AdjustableAttd::where('detail_schedule_id', ...)->get()` per baris. Padahal model `DetailSchedule` sudah punya relasi `adjustableAttendance`.
- **Solusi:** Menambahkan relasi `'schedules.detailSchedules.adjustableAttendance'` ke eager loading `with()`, dan menggunakan properti relasi `$detailSchedule->adjustableAttendance` di loop.

### 4.2 Export PDF User Perorangan — N+1 `AdjustableAttd`

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/AttendanceController.php` (L552-L574)
- **Severity:** 🟠 HIGH
- **Masalah:** Pola sama dengan 4.1, ditambah `$intern->user->profile` tidak di-eager load.
- **Solusi:** Menambahkan eager load `'user.profile'` dan `'schedules.detailSchedules.adjustableAttendance'`, serta menggunakan properti relasi preloaded di loop.

### 4.3 Export PDF Admin — Query Agregasi di Loop + Wasted Eager Load

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/AdminController.php` (L610, L631-L643)
- **Severity:** 🟠 HIGH
- **Masalah:**
    1. `with(['schedules.detailSchedules'])` memuat ribuan row tapi tidak dipakai di loop.
    2. Di loop, `$intern->schedules()->join(...)` memulai query baru per intern.
- **Dampak:** 100 pemagang = 100 query agregasi.
- **Solusi:** Menghapus `schedules.detailSchedules` dari eager loading. Seluruh ringkasan absensi pemagang dihitung dalam **1 query agregasi join SQL** (`whereIn('schedules.intern_id', $internIds)`) lalu dipetakan menggunakan `keyBy('intern_id')`.

### 4.4 Export CSV Keterlambatan — Missing `reviewer` Eager Load

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/LateAbsenceController.php` (L743, L784-L801)
- **Severity:** 🟠 HIGH
- **Masalah:** `$late->reviewer->name` diakses di loop CSV tapi `'reviewer'` tidak ada di `with()`.
- **Solusi:** Menambahkan `'reviewer'` ke array eager loading `with()`.

---

## 🟠 Kategori 5: N+1 pada Controller & View Blade

### 5.1 Halaman Ganti Jam — 90-180 Query Attendance per User

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/UserController.php` (L496-L517)
- **Severity:** 🟠 HIGH
- **Masalah:** Loop `foreach ($allSchedules as $schedule)` menjalankan `Attendance::where('intern_id', ...)->first()` per hari. Pemagang 3-6 bulan = **90-180 query**.
- **Solusi:** Pre-fetch semua attendance 1 kali, lalu `keyBy(fn($a) => Carbon::parse($a->date)->format('Y-m-d'))`.

### 5.2 Tab Overview Admin — 60 Query di Loop Tanggal

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/AdminController.php` (L432-L442)
- **Severity:** 🟠 HIGH
- **Masalah:** Loop `for ($d = 30 hari)` menjalankan 2 query per hari (`DetailSchedule` + `OfflineAttendance`). Data sudah di-fetch sebelumnya di L166-L189 tapi diulang di loop.
- **Solusi:** Gunakan koleksi yang sudah di-load, kelompokkan dengan `groupBy(fn($ds) => $ds->date)`.

### 5.3 LateAbsenceController — Query `getInternShiftForDate` di Loop

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/LateAbsenceController.php` (L134-L136, L198-L200, L311-L313)
- **Severity:** 🟠 HIGH
- **Masalah:** `getInternShiftForDate()` menjalankan query `DetailSchedule::whereHas(...)` per attendance di loop `create()`, `store()`, dan `scanLateAbsences()`. Padahal attendance sudah di-load dengan `detailSchedules.shift`.
- **Solusi:** Ambil shift dari relasi yang sudah di-load: `$attendance->detailSchedules->first()?->shift`.

### 5.4 HandRaiseController — 3 Query Duplikat dengan 9 Eager Load

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/HandRaiseController.php` (L86-L112)
- **Severity:** 🟠 HIGH
- **Masalah:** 3 query `HandRaise::with($baseRelations)->get()` mengambil data yang sama (`is_raised = true, status != 'done'`), hanya beda filter `type`. Setiap query memuat 9 relasi. Total = **30 query database**.
- **Solusi:** 1 query tunggal, lalu pisahkan dengan collection `->filter()` di PHP.

### 5.5 View `admin/tim.blade.php` — N+1 Query per Anggota Divisi

- **Status:** ✅ Selesai
- **File:** `resources/views/admin/tim.blade.php` (L125-L138)
- **Repository:** `InternRepositoryIMPL::getByDivisionId()` tanpa `with()`
- **Severity:** 🟠 HIGH
- **Masalah:** Loop Blade mengakses `$team->user->profile->full_name`, `$team->user->intern->id` (redundant self-reference). 30 anggota = **90 query**.
- **Solusi:** Tambahkan `->with('user.profile')` di repository. Di Blade, ganti `$team->user->intern->id` dengan `$team->id`.

### 5.6 View `admin/divisi.blade.php` — N+1 pada Intern Tanpa Divisi

- **Status:** ✅ Selesai
- **File:** `resources/views/admin/divisi.blade.php` (L71-L78)
- **Repository:** `InternRepositoryIMPL::getWithoutDivision()` tanpa `with()`
- **Severity:** 🟠 HIGH
- **Masalah:** `$intern->user->profile->full_name` memicu 2 lazy loading query per baris.
- **Solusi:** Tambahkan `->with('user.profile')` pada `getWithoutDivision()`.

---

## 🟠 Kategori 6: Query di Accessor/Method Model

### 6.1 `HandRaise::getEffectiveShiftAttribute()` — Query di Accessor

- **Status:** ✅ Selesai
- **File:** `app/Models/HandRaise.php` (L53-L90)
- **Severity:** 🟠 HIGH
- **Masalah:** Accessor `effective_shift` menjalankan query `DetailSchedule::whereHas(...)` setiap dipanggil. Di loop tabel Raise Hand = 20 query tambahan.
- **Solusi:** Pre-load jadwal shift di controller/Livewire, atau simpan `shift_id` saat pembuatan HandRaise.

### 6.2 `User::getNameAttribute()` — Lazy Loading Profile

- **Status:** ✅ Selesai
- **File:** `app/Models/User.php` (L50-L53)
- **Severity:** 🟡 MEDIUM
- **Masalah:** `$this->profile?->full_name` memicu lazy loading saat `$user->name` diakses tanpa prior eager load.
- **Solusi:** Cek `$this->relationLoaded('profile')` sebelum mengakses relasi; fallback ke `$this->username`.

### 6.3 `Attendance::isLate()` & `getTotalLateMinutes()` — Query Builder di Method

- **Status:** ✅ Selesai
- **File:** `app/Models/Attendance.php` (L95-L108)
- **Severity:** 🟡 MEDIUM
- **Masalah:** Method menggunakan `$this->lateAbsences()->exists()` dan `->sum()` yang selalu menjalankan SQL, meskipun relasi sudah di-eager-load.
- **Solusi:** Prioritaskan evaluasi collection jika relasi ter-load:
    ```php
    if ($this->relationLoaded('lateAbsences')) {
        return $this->lateAbsences->isNotEmpty();
    }
    return $this->lateAbsences()->exists();
    ```

---

## 🟡 Kategori 7: Query Agregasi Bisa Digabung

### 7.1 AdminController — 3 Permit Count Terpisah

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/AdminController.php` (L68-L70)
- **Severity:** 🟡 MEDIUM
- **Masalah:** 3 query `PermitLog::where('type', ...)->count()` terpisah dengan `whereHas('attendance')` yang berat.
- **Solusi:** 1 query conditional aggregation `selectRaw('COUNT(CASE WHEN...)')`.

### 7.2 AssistantAdminController — 3 Permit Count Terpisah

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/AssistantAdminController.php` (L124-L126)
- **Severity:** 🟡 MEDIUM
- **Masalah:** Sama dengan 7.1, 3 query count terpisah.
- **Solusi:** 1 query conditional aggregation `selectRaw('COUNT(CASE WHEN...)')`.

### 7.3 AttendanceService — 6 Count Terpisah di Dashboard

- **Status:** ✅ Selesai
- **File:** `app/Services/AttendanceService.php` (L667-L682)
- **Severity:** 🟡 MEDIUM
- **Masalah:** 6 query `COUNT` independen ke tabel `detail_schedules` untuk tanggal yang sama (3 status + 3 office).
- **Solusi:** 1 query dengan `selectRaw('COUNT(CASE WHEN attd_status_id = 2 THEN 1 END) as ..., ...')`.

### 7.4 SchoolService — N+1 Count per Sekolah

- **Status:** ✅ Selesai
- **File:** `app/Services/SchoolService.php` (L60-L63)
- **Severity:** 🟡 MEDIUM
- **Masalah:** Loop `for` menjalankan `$this->internRepository->countBySchool($id)` per sekolah.
- **Solusi:** `School::withCount('interns')->get()`.

### 7.5 DivisionService — N+1 Count per Divisi

- **Status:** ✅ Selesai
- **File:** `app/Services/DivisionService.php` (L31-L33)
- **Severity:** 🟡 MEDIUM
- **Masalah:** Loop `foreach` menjalankan `$this->internRepository->countByDivision($id)` per divisi.
- **Solusi:** `Division::withCount('intern')->get()`.

---

## 🟡 Kategori 8: Query Redundan & Duplikasi

### 8.1 SettingOfficeController — Duplikasi Query Coordinate

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/SettingOfficeController.php` (L73-L74)
- **Severity:** 🟡 MEDIUM
- **Masalah:** `Office::with('coordinate')->findOrFail($id)` lalu `Coordinate::where('office_id', $id)->get()` — query ulang data yang sudah di-load.
- **Solusi:** Gunakan `$office->coordinate` dari relasi.

### 8.2 SettingProjectController — Duplikasi Query Intern + `DetailProjects::all()`

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/SettingProjectController.php` (L36-L45)
- **Severity:** 🟠 HIGH
- **Masalah:**
    1. Data intern diambil 2 kali: via service dan via direct query.
    2. `DetailProjects::all()` memuat seluruh baris tabel tanpa batasan.
- **Solusi:** Hapus query ganda, gunakan `Projects::with(['nameProject', 'members.intern.user.profile'])->get()`.

### 8.3 ScheduleController — Duplikasi Query Intern

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/ScheduleController.php` (L63-L66)
- **Severity:** 🟡 MEDIUM
- **Masalah:** `Intern::with('user.profile')->find($internId)` padahal data sudah tersedia dari `getScheduleByInternId()`.
- **Solusi:** Ambil data dari relasi schedule.

### 8.4 PermitController — Duplikasi Pencarian Attendance

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/PermitController.php` (L74-L93)
- **Severity:** 🟡 MEDIUM
- **Masalah:** Mengambil `PermitLog` lalu query ulang `Attendance` terpisah, padahal PermitLog sudah punya relasi `attendance`.
- **Solusi:** Eager load `->with('attendance')` pada `PermitLog`, gunakan `$activePermitLog->attendance`.

### 8.5 ShiftService — Double UPDATE pada Record Shift

- **Status:** ✅ Selesai
- **File:** `app/Services/ShiftService.php` (L94-L100)
- **Severity:** 🟡 MEDIUM
- **Masalah:** `DB::table('shifts')->update(...)` lalu `$shiftRepository->update(...)` — 2 UPDATE ke record yang sama.
- **Solusi:** Gabungkan kolom `adt_start_break_time` dan `adt_end_break_time` ke array `$data`, cukup 1 panggilan update.

### 8.6 User Model — Duplikasi `getActiveTasksCount()`

- **Status:** ✅ Selesai
- **File:** `app/Models/User.php` (L148-L187), `app/Livewire/AttdStatusButton.php` (L61-L64)
- **Severity:** 🟡 MEDIUM
- **Masalah:** `hasActiveTasks()` memanggil `getActiveTasksCount()` (2 query count). Lalu dipanggil berturutan: `$user->hasActiveTasks()` + `$user->getActiveTasksCount()` = **4 query padahal 2 cukup**.
- **Solusi:** Simpan hasil count ke variabel: `$count = $user->getActiveTasksCount(); $has = $count > 0;`

### 8.7 Redundant `getById()` Setelah `create()` / `store()`

- **Status:** ✅ Selesai
- **File:**
    - `app/Services/Attendance/State/AdjustableInState.php` (L135-L138)
    - `app/Services/InternService.php` (L133-L140)
    - `app/Services/Attendance/State/AttendanceInState.php` (L115-L116)
- **Severity:** 🟢 LOW
- **Masalah:** Method `create()` / `store()` sudah mengembalikan model instance, tapi kode memanggil `getById($result->id)` lagi — query SELECT sia-sia.
- **Solusi:** Langsung gunakan model hasil kembalian tanpa query tambahan.

### 8.8 UserController — Query Fallback Duplikat HandRaise

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/UserController.php` (L165-L170)
- **Severity:** 🟡 MEDIUM
- **Masalah:** Jika user tidak punya raise hand aktif, query `HandRaise::where(...)` dijalankan 2 kali.
- **Solusi:** Ambil 2 record terbaru 1 kali, filter di PHP.

### 8.9 HandRaiseController — Duplikasi Count + Get pada Polling

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/HandRaiseController.php` (L741-L758)
- **Severity:** 🟡 MEDIUM
- **Masalah:** `HandRaise::get()` dan `HandRaise::count()` terpisah dengan filter yang sama.
- **Solusi:** Ambil `get()` saja, gunakan `$collection->count()` untuk jumlah.

---

## 🟡 Kategori 9: In-Memory Processing & Missing Select

### 9.1 AttendanceService — Paginasi In-Memory `detailAttendanceReport`

- **Status:** ✅ Selesai
- **File:** `app/Services/AttendanceService.php` (L1049-L1055)
- **Severity:** 🟡 MEDIUM
- **Masalah:** Seluruh riwayat jadwal dimuat ke memori PHP, difilter dan dipaginasi manual (`forPage`), lalu relasi diakses lazy-loading.
- **Solusi:** Lakukan paginasi dan filter di level database:
    ```php
    DetailSchedule::with([...])
        ->whereHas('schedule', fn($q) => $q->where('intern_id', $internId))
        ->when($statusId, fn($q) => $q->where('attd_status_id', $statusId))
        ->orderBy('date')->paginate($pageSize);
    ```

### 9.2 InternRepositoryIMPL — JOIN Tanpa `select()` (Column Collision)

- **Status:** ✅ Selesai
- **File:** `app/Repositories/Implementation/InternRepositoryIMPL.php` (L97-L104)
- **Severity:** 🟡 MEDIUM
- **Masalah:** `->join("users", ...)->join("profiles", ...)->get()` tanpa `select()` menyebabkan `SELECT *` dari 3 tabel. Kolom `id` saling menimpa.
- **Solusi:** Tambahkan `->select('interns.*')`.

### 9.3 DetailScheduleRepositoryIMPL — JOIN Tanpa `select()`

- **Status:** ✅ Selesai
- **File:** `app/Repositories/Implementation/DetailScheduleRepositoryIMPL.php` (L76-L81, L96-L101)
- **Severity:** 🟡 MEDIUM
- **Masalah:** Sama dengan 9.2, join tanpa `select()` pada `findByInternIdAndMonth` dan `findByInternIdAndWeek`.
- **Solusi:** Tambahkan `->select('detail_schedules.*')`.

### 9.4 LocationService & AttendanceInState — N+1 Office Coordinates

- **Status:** ✅ Selesai
- **File:** `app/Services/LocationService.php` (L21-L25), `app/Services/Attendance/State/AttendanceInState.php` (L235-L239)
- **Severity:** 🟠 HIGH
- **Masalah:** `$offices = Office::all()` tanpa eager load. Loop mengakses `$office->coordinates` — 1 query per kantor. Dipanggil pada **setiap check-in GPS**.
- **Solusi:** Di `OfficeRepositoryIMPL::getAll()`, ubah menjadi `$this->model->with('coordinates')->get()`.

---

## 🟡 Kategori 10: Query INSERT/UPDATE di Dalam Loop

### 10.1 ScheduleService — Query per Tanggal pada Update Multi

- **Status:** ✅ Selesai
- **File:** `app/Services/ScheduleService.php` (L266-L315)
- **Severity:** 🟡 MEDIUM
- **Masalah:** Loop per tanggal menjalankan 1 SELECT + 1-3 INSERT/UPDATE per iterasi. 20 tanggal = 60-80 query.
- **Solusi:** Pre-fetch semua `DetailSchedule` yang ada untuk seluruh rentang tanggal dengan `whereIn('date', $datesMap)` dalam 1 query, lakukan bulk `update()` untuk record yang sudah ada, dan batch `insert()` untuk tanggal yang baru.

### 10.2 AttendanceService — Loop Update `setToEndTime()`

- **Status:** ✅ Selesai
- **File:** `app/Services/AttendanceService.php` (L1543-L1590)
- **Severity:** 🟡 MEDIUM
- **Masalah:** `$attendanceRepository->update()` menjalankan `find()` + `update()` + `fresh()` (3 query) per row. 40 siswa = **120 query**.
- **Solusi:** Menggabungkan pembaruan menjadi 1 query batch update via `Attendance::whereIn('id', $ids)->update(...)` dengan kalkulasi raw `CASE WHEN` SQL untuk `total_min`, `back_time`, dan `total_break_min`.

### 10.3 AttendanceService — Loop Update `markMissedSchedulesAsAlpha()`

- **Status:** ✅ Selesai
- **File:** `app/Services/AttendanceService.php` (L1980-L2015)
- **Severity:** 🟡 MEDIUM
- **Masalah:** `$schedule->update(['attd_status_id' => ...])` per jadwal terlewat.
- **Solusi:** Mengumpulkan ID jadwal terlewat ke array `$alphaIds`, lalu mengeksekusi 1 query batch `DetailSchedule::whereIn('id', $alphaIds)->update(['attd_status_id' => $statusAlpha])`.

### 10.4 ProjectService & DivisionService — INSERT Member di Loop

- **Status:** ✅ Selesai
- **File:** `app/Services/ProjectService.php` (L38-L46, L93-L105), `app/Services/DivisionService.php` (L161-L177)
- **Severity:** 🟡 MEDIUM
- **Masalah:** `DetailProjects::create(...)` / `updateOrCreate(...)` dieksekusi per anggota di dalam loop.
- **Solusi:** Mengubah penambahan anggota proyek menjadi batch `DetailProjects::insert($memberRecords)` setelah menyaring diff anggota yang belum ada.

### 10.5 LateAbsenceController — Multiple Updates per Item di `bulkUpdateStatus()`

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/LateAbsenceController.php` (L586-L640)
- **Severity:** 🟡 MEDIUM
- **Masalah:** Setiap item memicu 2-3 query update (restore, clear, adjust) dan N+1 query lazy load `attendance` serta `shift`. 50 record = 100-150 query.
- **Solusi:** Menambahkan eager loading `with(['attendance', 'shift'])` pada pengambilan data, memproses penyesuaian waktu in-memory, dan mengeksekusi 1 query batch `LateAbsence::whereIn('id', $selectedIds)->update(...)`.

### 10.6 UserController — Writes di GET Request

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/UserController.php` (L818-L848)
- **Severity:** 🟠 HIGH
- **Masalah:** Method GET `taskDivisionView()` menjalankan query `update()` di dalam perulangan untuk status project dan mentor task.
- **Solusi:** Mengumpulkan ID project dan ID task yang perlu diubah statusnya, lalu mengeksekusi batch `Projects::whereIn('id', $doneProjectIds)->update(['status' => 'done'])` dan `HandRaise::whereIn('id', $doneTaskIds)->update(...)` hanya jika ada record yang perlu diperbarui.

---

## 🟡 Kategori 11: Arsitektur & Dead Code

### 11.1 File Blade Berisi Source Code PHP Class

- **Status:** ✅ Selesai
- **File:** `resources/views/livewire/admin/izin-toilet-monitor.blade.php`, `app/Livewire/Admin/IzinToiletMonitor.php`
- **Severity:** 🟠 HIGH
- **Masalah:** File Blade template berisi definisi PHP class (`IzinToiletMonitor extends Component`) lengkap dengan namespace, use statements, property, dan method. Bukan template HTML.
- **Solusi:** Logika class dipindahkan ke `app/Livewire/Admin/IzinToiletMonitor.php` dengan namespace yang benar (`App\Livewire\Admin`), dan file Blade `izin-toilet-monitor.blade.php` dibersihkan menjadi template HTML/Blade standar.

### 11.2 Relasi Rusak pada Model Intern (`toiletPermits` & `prayerPermits`)

- **Status:** ✅ Selesai
- **File:** `app/Models/Intern.php` (L107-L130)
- **Severity:** 🟡 MEDIUM
- **Masalah:** Relasi `hasMany(PermitLog::class, 'intern_id')` salah — tabel `permit_logs` **tidak punya** kolom `intern_id`. Filter `where('permit_type', ...)` juga salah — kolomnya bernama `type`, bukan `permit_type`. Akan melempar SQL Exception jika dipanggil.
- **Solusi:** Mengubah relasi menjadi `hasManyThrough(PermitLog::class, Attendance::class, 'intern_id', 'attendance_id')` dengan filter eksplisit `->where('permit_logs.type', 'toilet')` dan `->where('permit_logs.type', 'prayer')`.

### 11.3 Model Duplikat `Pemagang` dan `Intern`

- **Status:** ✅ Selesai
- **File:** `app/Models/Pemagang.php`, `app/Models/User.php` (L74-L85)
- **Severity:** 🟢 LOW
- **Masalah:** Kedua model merepresentasikan tabel `interns` tapi mendefinisikan relasi `outsiders` dengan pivot yang berbeda (`pemagang_user` yang tidak ada tabelnya di DB).
- **Solusi:** Mengkonsolidasikan model `Pemagang` menjadi subclass/alias yang mewarisi `App\Models\Intern`, dan memperbaiki relasi `pemagangs()` di `User.php` agar menggunakan pivot tabel yang valid (`outsider_intern`).

### 11.4 AttendanceRepositoryIMPL — Parameter Paginasi Tidak Terpakai

- **Status:** ✅ Selesai
- **File:** `app/Repositories/Implementation/AttendanceRepositoryIMPL.php` (L295-L300)
- **Severity:** 🟢 LOW
- **Masalah:** Method `getByIdAndAutomaticalyStatus()` menerima `$perPage` dan `$currentPage` tapi memanggil `->get()` tanpa paginasi.
- **Solusi:** Mengubah pemanggilan `->get()` menjadi `->paginate($perPage, ['*'], 'page', $currentPage)` sehingga parameter paginasi benar-benar dihormati dan return type konsisten dengan method auto-end lainnya.

---

## 🟢 Kategori 12: Lazy Loading Minor & Cleanup

### 12.1 AttendanceController — Missing `user.profile` Eager Load

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/AttendanceController.php` (L530-L533)
- **Severity:** 🟢 LOW
- **Solusi:** Menambahkan `'user.profile'` ke `Intern::with(['user.profile', 'schedules.detailSchedules.logActivity'])` pada method `show()`.

### 12.2 BroadcastController & ScheduledBroadcastController — Lazy Loading Images

- **Status:** ✅ Selesai
- **File:** `BroadcastController.php` (L156), `ScheduledBroadcastController.php` (L145)
- **Severity:** 🟢 LOW
- **Solusi:** Menambahkan `$broadcast->loadMissing('images')` sebelum iterasi penghapusan file gambar di method `destroy()`.

### 12.3 LogActivityController — Missing `logActivity` Eager Load

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/LogActivityController.php` (L49-L53)
- **Severity:** 🟢 LOW
- **Solusi:** Menambahkan `->with('logActivity')` pada query `$todaysDetailSchedule` sebelum mengakses `$todaysDetailSchedule?->logActivity`.

### 12.4 InternController — Chained Lazy Loading pada `raiseHandToggle()`

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/InternController.php` (L34-L38)
- **Severity:** 🟢 LOW
- **Solusi:** Menambahkan `$user->loadMissing(['intern.detailProject', 'intern.division', 'profile'])` di awal method `raiseHandToggle()` untuk memuat seluruh relasi yang dibutuhkan sekaligus.

### 12.5 AdminIzinShalatController — Missing Eager Load pada Detail History

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/AdminIzinShalatController.php` (L62-L73)
- **Severity:** 🟢 LOW
- **Solusi:** Menambahkan `$intern->loadMissing('user.profile')` sebelum mengoper data ke view `prayer-history-detail`.

### 12.6 AssistantAdminController — Chained Lazy Loading 5 Tingkat

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/AssistantAdminController.php` (L220, L241)
- **Severity:** 🟢 LOW
- **Solusi:** Menambahkan `$log->loadMissing('detailSchedule.schedule.intern.user.profile')` pada method `approveLog()` dan `rejectLog()`.

### 12.7 HrMonitoringController — In-Memory ID Array Transfer

- **Status:** ✅ Selesai
- **File:** `app/Http/Controllers/HrMonitoringController.php` (L30-L55)
- **Severity:** 🟢 LOW
- **Masalah:** Query pertama `pluck('intern_id')` lalu `whereIn('id', $ids)` membebani RAM PHP dan memicu 2 query.
- **Solusi:** Menggabungkan query menjadi 1 SQL query ber-subquery: `Intern::whereHas('attendances', fn($q) => $q->whereDate('date', today()))->with(['user.profile', 'division', 'activePermitLog'])->paginate(25)`.

### 12.8 AssistantAdminService — Eager Load Tidak Terpakai

- **Status:** ✅ Selesai
- **File:** `app/Services/AssistantAdminService.php` (L195-L205)
- **Severity:** 🟢 LOW
- **Masalah:** `LogActivity::with([5 relasi])->findOrFail($id)` memuat 6-8 relasi ke memori hanya untuk update 3 kolom.
- **Solusi:** Disederhanakan menjadi `LogActivity::findOrFail($id)` tanpa eager loading yang mubazir.

---

## 🎯 Urutan Prioritas Eksekusi

### Fase 1: Emergency Fix (Dampak Server Langsung)

> Perbaikan yang langsung mengurangi beban server secara drastis.

|  #  | Item                                              | Estimasi Query Terhemat |
| :-: | :------------------------------------------------ | :---------------------: |
|  1  | 1.1 Query di Blade `attd-status-button` (5s poll) |  ~2.880/menit/20 user   |
|  2  | 1.2 Full table scan `BroadcastPopup` (10s poll)   |  ~120 full scan/menit   |
|  3  | 1.3 Gabung 8 count `RaiseHandManager` (3s poll)   |   ~3.200 query/menit    |
|  4  | 3.1 Bulk update shift (600→1 query)               |    599 query/request    |
|  5  | 2.1 Eager load tabel presensi                     |  150-500 query/request  |

### Fase 2: Core Optimization (Endpoint Sering Diakses)

> Endpoint utama yang digunakan sehari-hari oleh admin dan pemagang.

|  #  | Item                                     | Estimasi Query Terhemat |
| :-: | :--------------------------------------- | :---------------------: |
|  6  | 2.2 Laporan presensi                     |    300 query/request    |
|  7  | 2.3 Auto attendance                      |    140 query/request    |
|  8  | 5.1 Halaman ganti jam                    |  90-180 query/request   |
|  9  | 5.2 Overview admin                       |    60 query/request     |
| 10  | 9.4 Office coordinates (setiap check-in) |   3-5 query/check-in    |

### Fase 3: Batch & Export Optimization

> Operasi batch dan export yang jarang tapi berat.

|  #  | Item                      |
| :-: | :------------------------ |
| 11  | 3.2 Bulk insert jadwal    |
| 12  | 3.3 Batch delete pemagang |
| 13  | 3.4 Bulk end-time         |
| 14  | 4.1-4.4 Export PDF/CSV    |
| 15  | 2.4 Notifikasi Alpha      |

### Fase 4: Konsolidasi & Cleanup

> Query redundan, count agregasi, dan perbaikan minor.

|  #  | Item                             |
| :-: | :------------------------------- |
| 16  | 7.1-7.5 Gabung query agregasi    |
| 17  | 8.1-8.9 Hapus query duplikat     |
| 18  | 10.1-10.6 Update/insert di loop  |
| 19  | 6.1-6.3 Accessor model           |
| 20  | 11.1-11.4 Arsitektur & dead code |
| 21  | 12.1-12.8 Lazy loading minor     |
