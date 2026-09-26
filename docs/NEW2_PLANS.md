# 📋 Rencana Implementasi Sistem Baru (Batch 2) — Absen Djuragan

> Dokumen ini berisi spesifikasi teknis, rancangan alur, skema database, dan panduan langkah demi langkah (_step-by-step_) untuk 7 modul peningkatan utama pada sistem **Absen Djuragan**.  
> Disusun sebagai panduan kerja mandiri yang presisi, lengkap dengan referensi file, potongan kode, dan instruksi pengujian.

---

## 📑 Daftar Isi & Status Rencana

| No  | Modul / Fitur                                                                                                                                                                | Prioritas | Estimasi  | Status     |
| --- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------- | --------- | ---------- |
| 1   | [Sistem Presentasi: Alur Persetujuan (Terima/Reschedule/Tolak) & Detail Popup](#1-sistem-presentasi-alur-persetujuan-terima-reschedule-tolak--popup-detail)                  | 🔴 Tinggi | 4 - 5 Jam | ✅ Selesai |
| 2   | [Pembersihan Opsi Hybrid & Perbaikan Fitur Toggle GPS Sunting Anggota](#2-pembersihan-opsi-hybrid--perbaikan-fitur-toggle-gps-sunting-anggota)                               | 🟡 Sedang | 2 - 3 Jam | ✅ Selesai |
| 3   | [Pendaftaran Pemagang oleh Admin/Super Admin & Pembatasan Hak Hapus](#3-pendaftaran-pemagang-oleh-adminsuper-admin--pembatasan-hak-hapus)                                    | 🔴 Tinggi | 3 - 4 Jam | ✅ Selesai |
| 4   | [Manajemen Project: Dependent Dropdown Berbasis Divisi](#4-manajemen-project-dependent-dropdown-berbasis-divisi)                                                             | 🟡 Sedang | 2 - 3 Jam | ✅ Selesai |
| 5   | [Super Admin Audit Log: Ekspor CSV Selektif Berbasis Search & Checklist](#5-super-admin-audit-log-ekspor-csv-selektif-berbasis-search--checklist)                            | 🟡 Sedang | 2 - 3 Jam | ✅ Selesai |
| 6   | [Penyempurnaan Presensi Offline: Waktu Masuk Fisik & Verifikasi Alpha/Izin/Sakit/Early](#6-penyempurnaan-presensi-offline-waktu-masuk-fisik--verifikasi-alphaizinsakitearly) | 🔴 Tinggi | 4 - 6 Jam | 📝 Planned |
| 7   | [Standarisasi Desain Mobile & Layout Responsif](#7-standarisasi-desain-mobile--layout-responsif)                                                                             | 🟡 Sedang | 3 - 4 Jam | 📝 Planned |

---

## 1. Sistem Presentasi: Alur Persetujuan (Terima / Reschedule / Tolak) & Popup Detail

### 1.1. Latar Belakang & Kebutuhan

Saat ini, pengajuan presentasi dari pemagang langsung masuk ke antrean _pre-review_ tanpa alur persetujuan jadwal formal. Diperlukan alur baru di mana:

1. **Admin / Mentor harus menyetujui (menerima)** pengajuan presentasi terlebih dahulu sebelum form evaluasi, selesai, dan review nilai dapat dibuka.
2. Di form peninjauan/persetujuan jadwal presentasi tersedia 3 aksi utama:
    - **Terima Presentasi**: Menetapkan jadwal fix (tanggal & jam), status berubah menjadi `accepted` / `ready`.
    - **Reschedule Presentasi**: Mengubah tanggal/jam dan mencantumkan catatan alasan pemindahan jadwal (status: `rescheduled`).
    - **Tolak Presentasi**: Menolak pengajuan presentasi dengan alasan/catatan revisi (status: `rejected` / `needs_revision`).
3. Form persetujuan menampilkan informasi:
    - **Nama Pemagang & Foto/Avatar**
    - **Divisi & Judul Project**
    - **Shift Pemagang** (misal: _Middle (09:00 - 17:00)_)
    - **Input Jam Presentasi** (`presentation_time` / `scheduled_time`)
    - **Input Tanggal Presentasi** (`presentation_date`)
    - **Input Catatan / Note Mentor** (`notes` / `admin_response`)
4. Pada dashboard pemagang, setelah submit form _Raise Hand Minta Presentasi_, langsung tampil **modal popup keterangan / detail pengajuan** yang memuat ringkasan project, tanggal pengajuan, status persetujuan, dan link Google Meet (jika online).

---

### 1.2. Analisis Kode & Database

#### Perubahan Database (`hand_raises`)

Tambahkan field baru pada tabel `hand_raises`:

- `scheduled_time` (`time`, nullable): Jam pelaksanaan presentasi yang disetujui/dijadwalkan admin.
- Penyesuaian enum / string kolom `status`:
    - `'pending'` : Menunggu persetujuan admin.
    - `'accepted'` : Jadwal presentasi diterima & siap dilaksanakan.
    - `'rescheduled'` : Jadwal diubah oleh mentor.
    - `'rejected'` : Pengajuan ditolak / perlu perbaikan sebelum presentasi.
    - `'in_progress'` : Sesi presentasi sedang berlangsung.
    - `'needs_revision'` : Presentasi selesai namun ada revisi project.
    - `'ready'` / `'done'` : Presentasi disetujui & project tervalidasi selesai.

```php
// Migration: add_scheduled_time_to_hand_raises_table.php
Schema::table('hand_raises', function (Blueprint $table) {
    if (!Schema::hasColumn('hand_raises', 'scheduled_time')) {
        $table->time('scheduled_time')->nullable()->after('presentation_date');
    }
});
```

#### File yang Terlibat

1. `app/Models/HandRaise.php` — Tambahkan `'scheduled_time'` ke `$fillable`.
2. `app/Http/Controllers/HandRaiseController.php` — Tambah method `acceptPresentation`, `reschedulePresentation`, `rejectPresentation`.
3. `app/Livewire/Admin/RaiseHandManager.php` — Tambahkan action handler untuk persetujuan presentasi.
4. `resources/views/livewire/admin/raise-hand-manager.blade.php` — Perbarui card dan modal persetujuan jadwal presentasi.
5. `resources/views/users/index.blade.php` & `app/Http/Controllers/InternController.php` — Tambahkan modal popup detail pengajuan presentasi di sisi pemagang.

---

### 1.3. Langkah Implementasi

#### Langkah 1: Update Model & Database

Update `app/Models/HandRaise.php`:

```php
protected $fillable = [
    'user_id',
    'project_id',
    'type',
    'presentation_mode',
    'meet_url',
    'presentation_date',
    'scheduled_time', // Tambah field ini
    'status',
    'notes',
    'performance_rating',
    'performance_notes',
    'admin_response',
    'reason',
    'resolved_at',
    'resolved_by',
    'is_raised',
];
```

#### Langkah 2: Controller Action di Backend (`HandRaiseController.php`)

Tambahkan endpoint untuk menangani aksi persetujuan:

```php
public function respondPresentationSchedule(Request $request, $id)
{
    $request->validate([
        'action' => 'required|in:accept,reschedule,reject',
        'presentation_date' => 'required_if:action,accept,reschedule|nullable|date',
        'scheduled_time' => 'required_if:action,accept,reschedule|nullable',
        'notes' => 'nullable|string|max:500',
    ]);

    $handRaise = HandRaise::with(['user.profile', 'user.intern.shift'])->findOrFail($id);

    switch ($request->action) {
        case 'accept':
            $handRaise->update([
                'status' => 'accepted',
                'presentation_date' => $request->presentation_date,
                'scheduled_time' => $request->scheduled_time,
                'admin_response' => $request->notes,
                'resolved_by' => auth()->id(),
            ]);
            $msg = "Jadwal presentasi pemagang {$handRaise->user->profile->full_name} berhasil diterima.";
            break;

        case 'reschedule':
            $handRaise->update([
                'status' => 'rescheduled',
                'presentation_date' => $request->presentation_date,
                'scheduled_time' => $request->scheduled_time,
                'admin_response' => $request->notes,
                'resolved_by' => auth()->id(),
            ]);
            $msg = "Jadwal presentasi pemagang {$handRaise->user->profile->full_name} dijadwalkan ulang.";
            break;

        case 'reject':
            $handRaise->update([
                'status' => 'rejected',
                'admin_response' => $request->notes,
                'is_raised' => false,
                'resolved_at' => now(),
                'resolved_by' => auth()->id(),
            ]);
            $msg = "Pengajuan presentasi ditolak dengan catatan: " . $request->notes;
            break;
    }

    \App\Helper\ActivityLogger::log('UPDATE', 'Raise Hand', $msg);
    return response()->json(['success' => true, 'message' => $msg, 'data' => $handRaise]);
}
```

#### Langkah 3: UI Modal Persetujuan Jadwal di Admin (`raise-hand.blade.php`)

Tambahkan modal jadwal dengan menampilkan Shift, Waktu, Tanggal, dan Catatan:

```html
<div
    id="modalApprovePresentation"
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden p-4"
>
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <h3
                class="font-bold text-base text-slate-800 flex items-center gap-2"
            >
                <i class="fa-solid fa-calendar-check text-indigo-600"></i>
                Persetujuan Jadwal Presentasi
            </h3>
            <button
                type="button"
                onclick="closeApproveModal()"
                class="text-slate-400 hover:text-slate-600 text-lg"
            >
                &times;
            </button>
        </div>

        <div
            class="bg-slate-50 p-3.5 rounded-xl border border-slate-100 space-y-1.5 text-xs"
        >
            <div class="flex justify-between">
                <span class="text-slate-500">Nama Pemagang:</span>
                <span class="font-bold text-slate-800" id="modal-intern-name"
                    >-</span
                >
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Shift Kerja:</span>
                <span
                    class="font-semibold text-indigo-700"
                    id="modal-intern-shift"
                    >-</span
                >
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Judul Project:</span>
                <span
                    class="font-semibold text-slate-800"
                    id="modal-project-name"
                    >-</span
                >
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Mode:</span>
                <span
                    class="font-bold text-emerald-700 uppercase"
                    id="modal-presentation-mode"
                    >-</span
                >
            </div>
        </div>

        <form
            id="formPresentationDecision"
            onsubmit="submitPresentationDecision(event)"
        >
            <input type="hidden" id="modal-handraise-id" name="id" />
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label
                        class="block text-xs font-semibold text-slate-700 mb-1"
                        >Tanggal Presentasi</label
                    >
                    <input
                        type="date"
                        id="modal-input-date"
                        name="presentation_date"
                        class="w-full p-2 text-xs border rounded-lg focus:ring-2 focus:ring-indigo-500"
                        required
                    />
                </div>
                <div>
                    <label
                        class="block text-xs font-semibold text-slate-700 mb-1"
                        >Jam Presentasi</label
                    >
                    <input
                        type="time"
                        id="modal-input-time"
                        name="scheduled_time"
                        class="w-full p-2 text-xs border rounded-lg focus:ring-2 focus:ring-indigo-500"
                        required
                    />
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-700 mb-1"
                    >Catatan / Keterangan Mentor</label
                >
                <textarea
                    id="modal-input-notes"
                    name="notes"
                    rows="3"
                    placeholder="Tambahkan instruksi, materi yang harus dipersiapkan, atau link meet..."
                    class="w-full p-2.5 text-xs border rounded-lg focus:ring-2 focus:ring-indigo-500"
                ></textarea>
            </div>

            <div class="grid grid-cols-3 gap-2 pt-2 border-t">
                <button
                    type="button"
                    onclick="submitDecision('reject')"
                    class="py-2 px-3 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1"
                >
                    <i class="fa-solid fa-xmark"></i> Tolak
                </button>
                <button
                    type="button"
                    onclick="submitDecision('reschedule')"
                    class="py-2 px-3 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1"
                >
                    <i class="fa-solid fa-clock-rotate-left"></i> Reschedule
                </button>
                <button
                    type="button"
                    onclick="submitDecision('accept')"
                    class="py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 shadow-md"
                >
                    <i class="fa-solid fa-check"></i> Terima
                </button>
            </div>
        </form>
    </div>
</div>
```

#### Langkah 4: Popup Keterangan Pengajuan di Dashboard Pemagang

Pada saat form submit pengajuan presentasi berhasil (`intern.raisehand.toggle`), kirimkan data payload detail dan render popup Swal/Tailwind Modal:

```javascript
// Di user dashboard (users/index.blade.php)
if (response.type === "presentation") {
    Swal.fire({
        title: "Pengajuan Presentasi Terkirim!",
        html: `
            <div class="text-left text-xs space-y-2 p-2 bg-slate-50 rounded-lg border border-slate-200">
                <p><strong>Project:</strong> ${response.project_name}</p>
                <p><strong>Tanggal Diajukan:</strong> ${response.presentation_date}</p>
                <p><strong>Mode:</strong> <span class="uppercase font-bold text-indigo-600">${response.mode}</span></p>
                <p><strong>Status:</strong> <span class="text-amber-600 font-semibold">Menunggu Konfirmasi Mentor</span></p>
                <p class="text-slate-500 mt-2 text-[11px]">*Detail jadwal jam pelaksanaan dan catatan mentor akan diperbarui di dashboard ini setelah diterima.</p>
            </div>
        `,
        icon: "success",
        confirmButtonColor: "#4f46e5",
        confirmButtonText: "Mengerti",
    });
}
```

---

## 2. Pembersihan Opsi Hybrid & Perbaikan Fitur Toggle GPS Sunting Anggota

### 2.1. Latar Belakang & Kebutuhan

1. **Hapus Opsi 'Hybrid'**: Mode kerja hanya mendukung **WFO** (Work From Office) dan **WFH** (Work From Home). Opsi `'hybrid'` yang tersebar di form jadwal, controller, dan opsi shift harus dibersihkan total.
2. **Pengaturan Shift**: Pengaturan master shift tetap mempertahankan fitur toleransi & aktivasi GPS kantor.
3. **Perbaikan Toggle GPS di Edit User/Anggota (`admin/sunting-anggota`)**:
    - Di form edit anggota per divisi, toggle **GPS Status** (`is_gps_active` / `is_gps_activate`) saat ini sering tidak tersimpan ke database karena inkonsistensi penamaan field (`is_gps_activate` vs `is_gps_active`).
    - Perbaiki validasi `EditInternRequest`, service `InternService::update`, dan binding view agar admin dapat mengaktifkan atau menonaktifkan kewajiban GPS pemagang secara akurat.

---

### 2.2. Analisis Kode & File yang Perlu Diubah

1. **`app/Http/Controllers/ScheduleController.php`**:
    - Baris 59: `$workType = ["wfo", "wfh"];` (hapus `'hybrid'`).
    - Baris 115: `'work_type' => 'required|string|in:wfo,wfh',`
2. **`app/Services/AttendanceService.php`**:
    - Baris 1814-1816: Pastikan fallback hanya `['wfo', 'wfh']`.
3. **`resources/views/admin/detail-presensi.blade.php` & `resources/views/admin/shift_schedule/edit_shift_schedule.blade.php`**:
    - Hapus `<option value="hybrid"> HYBRID </option>`.
4. **`app/Http/Requests/EditInternRequest.php`**:
    - Tambahkan rule validasi untuk `is_gps_active` atau `is_gps_activate`:
    ```php
    'is_gps_active' => 'nullable|in:0,1',
    ```
5. **`app/Services/InternService.php`**:
    - Pada method `update()`, sinkronkan nilai `is_gps_activate` pada model `User`:
    ```php
    if ($request->has('is_gps_active')) {
        $user->is_gps_activate = (int) $request->input('is_gps_active');
        $user->save();
    }
    ```
6. **`resources/views/admin/sunting-anggota.blade.php`**:
    - Pastikan name input dan binding value konsisten:
    ```blade
    <select id="is_gps_active" name="is_gps_active" class="w-full mt-1 p-2 border border-gray-300 rounded">
        <option value="1" {{ (old('is_gps_active', $team->is_gps_activate) == 1) ? 'selected' : '' }}>Aktif</option>
        <option value="0" {{ (old('is_gps_active', $team->is_gps_activate) == 0) ? 'selected' : '' }}>Non-Aktif</option>
    </select>
    ```

---

## 3. Pendaftaran Pemagang oleh Admin/Super Admin & Pembatasan Hak Hapus

### 3.1. Latar Belakang & Kebutuhan

1. **Tambah Form Create Pemagang**:
    - Saat ini pembuatan akun pemagang hanya melalui registrasi publik (`/user/create`).
    - Admin (Role 1) dan Super Admin (Role 7) membutuhkan menu khusus di panel admin untuk mendaftarkan pemagang baru secara manual (Nama Lengkap, Email, Password, Sekolah/Kampus, Divisi, Shift, NIP/NIM, Tanggal Mulai & Selesai).
2. **Pembatasan Hak Hapus Pemagang**:
    - **Admin Biasa (Role 1)**: **DILARANG / TIDAK BISA** menghapus data pemagang. Tombol hapus disembunyikan dan endpoint penghapusan ditolak (`403 Forbidden`).
    - **Super Admin (Role 7)**: Memiliki wewenang eksklusif untuk menghapus akun pemagang jika diperlukan.

---

### 3.2. Rencana Implementasi Teknis

#### File yang Dibuat / Dimodifikasi:

1. `routes/web.php` — Tambahkan rute `GET /admin/interns/create` dan `POST /admin/interns/store`.
2. `app/Http/Controllers/InternController.php` — Tambahkan method `createInternView()` dan `storeInternAction()`.
3. `resources/views/admin/intern/create.blade.php` — Tampilan form tambah pemagang.
4. `app/Http/Controllers/DivisionController.php` & `resources/views/admin/divisi.blade.php` — Beri proteksi tombol hapus berdasarkan role.

#### Langkah 1: Tambahkan Rute di `routes/web.php`

```php
Route::prefix('admin')->middleware(['auth', 'role:1,7'])->group(function () {
    Route::get('/interns/create', [InternController::class, 'createInternView'])->name('admin.interns.create');
    Route::post('/interns/store', [InternController::class, 'storeInternAction'])->name('admin.interns.store');
});
```

#### Langkah 2: Logic Controller Pembuatan Pemagang

```php
// app/Http/Controllers/InternController.php
public function createInternView()
{
    $schools = School::orderBy('name')->get();
    $divisions = Division::orderBy('name')->get();
    $shifts = Shift::orderBy('name')->get();
    $offices = Office::orderBy('name')->get();

    return view('admin.intern.create', compact('schools', 'divisions', 'shifts', 'offices'));
}

public function storeInternAction(Request $request)
{
    $validated = $request->validate([
        'full_name' => 'required|string|max:255',
        'username' => 'required|string|max:50|unique:users,username',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6',
        'school_id' => 'required|exists:schools,id',
        'division_id' => 'required|exists:divisions,id',
        'shift_id' => 'required|exists:shifts,id',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'phone' => 'nullable|string|max:20',
        'gender' => 'required|in:L,P',
    ]);

    DB::transaction(function () use ($validated) {
        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => 3, // Intern
            'is_active' => true,
            'is_confirm' => true,
            'is_gps_activate' => 1,
        ]);

        Profile::create([
            'user_id' => $user->id,
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'] ?? null,
            'gender' => $validated['gender'],
        ]);

        $intern = Intern::create([
            'user_id' => $user->id,
            'school_id' => $validated['school_id'],
            'division_id' => $validated['division_id'],
            'shift_id' => $validated['shift_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);

        // Buat Akun Intern Kosong
        InternAccount::create(['intern_id' => $intern->id]);
    });

    \App\Helper\ActivityLogger::log('CREATE', 'User Management', "Admin menambahkan pemagang baru: {$validated['full_name']}");
    return redirect()->route('admin.division')->with('success', 'Pemagang baru berhasil ditambahkan.');
}
```

#### Langkah 3: Proteksi Hak Hapus di `DivisionController::destroy`

```php
public function destroy($internId)
{
    // Hanya Super Admin (Role 7) yang boleh menghapus
    if ((int) auth()->user()->role_id !== 7) {
        abort(403, 'Aksi Ditolak: Hanya Super Admin yang memiliki hak untuk menghapus akun pemagang.');
    }

    $intern = Intern::findOrFail($internId);
    $user = $intern->user;

    $internName = $user->profile->full_name ?? $user->username;
    $user->delete(); // Cascade delete relasi

    \App\Helper\ActivityLogger::log('DELETE', 'User Management', "Super Admin menghapus pemagang: {$internName}");
    return redirect()->back()->with('success', "Pemagang {$internName} berhasil dihapus.");
}
```

#### Langkah 4: Sembunyikan Tombol Hapus pada View Jika Bukan Super Admin

Di `resources/views/admin/divisi.blade.php` dan `resources/views/admin/anggota-sekolah.blade.php`:

```blade
@if(auth()->user()->role_id == 7)
    <button onclick="confirmDeleteIntern('{{ $member->intern->id }}')" class="text-rose-600 hover:text-rose-800 p-1.5 rounded-lg hover:bg-rose-50" title="Hapus Pemagang (Super Admin Only)">
        <i class="fa-solid fa-trash-can text-xs"></i>
    </button>
@endif
```

---

## 4. Manajemen Project: Dependent Dropdown Berbasis Divisi

### 4.1. Latar Belakang & Kebutuhan

Pada form penambahan / pengelolaan project di `admin/setting/project` (`SettingProjectController`):

- Saat ini daftar pemagang dan judul project dicampur tanpa filter divisi, sehingga rawan salah memasukkan anggota lintas keahlian (misal pemagang Content Writer masuk ke tim Backend).
- **Kebutuhan Baru**:
    1. Menambahkan dropdown **Pilih Divisi** sebagai langkah awal.
    2. Ketika divisi dipilih, sistem secara dinamis (via AJAX/Fetch) hanya menampilkan:
        - **Daftar Master Judul Project** yang relevan dengan divisi tersebut.
        - **Daftar Pemagang Aktif** yang terdaftar di divisi tersebut.

---

### 4.2. Rencana Implementasi

#### File yang Terlibat:

1. `app/Http/Controllers/SettingProjectController.php` — Tambahkan endpoint AJAX `getInternsAndTitlesByDivision($divisionId)`.
2. `resources/views/admin/pengaturan-project.blade.php` — Tambahkan select divisi dan script reactive change.

#### Langkah 1: Endpoint Data Berdasarkan Divisi

```php
// app/Http/Controllers/SettingProjectController.php
public function getInternsAndTitlesByDivision($divisionId)
{
    $interns = Intern::with(['user.profile', 'school'])
        ->where('division_id', $divisionId)
        ->whereHas('user', fn($q) => $q->where('is_active', true))
        ->get()
        ->map(function ($intern) {
            return [
                'id' => $intern->id,
                'name' => $intern->user->profile->full_name ?? $intern->user->username,
                'school' => $intern->school->name ?? '-',
            ];
        });

    $division = Division::find($divisionId);
    $divName = strtolower($division->name ?? '');

    // Master judul relevan atau umum
    $nameProjects = NameProjects::where('name', 'LIKE', "%{$divName}%")
        ->orWhere('name', 'NOT LIKE', 'Project %')
        ->orderBy('name')
        ->get();

    return response()->json([
        'success' => true,
        'interns' => $interns,
        'name_projects' => $nameProjects
    ]);
}
```

#### Langkah 2: UI Dropdown Dinamis di Blade

```html
<div class="mb-3">
    <label class="block text-xs font-bold text-slate-700 mb-1"
        >Pilih Divisi Terlebih Dahulu
        <span class="text-rose-500">*</span></label
    >
    <select
        id="select-project-division"
        name="division_id"
        class="w-full p-2.5 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500"
        onchange="onDivisionSelected(this.value)"
        required
    >
        <option value="">-- Pilih Divisi --</option>
        @foreach($divisions as $div)
        <option value="{{ $div->id }}">{{ $div->name }}</option>
        @endforeach
    </select>
</div>

<div
    id="wrapper-project-form"
    class="space-y-3 opacity-50 pointer-events-none transition-all"
>
    <!-- Judul Project & Anggota Pemagang akan ter-populate otomatis via JS -->
</div>
```

---

## 5. Super Admin Audit Log: Ekspor CSV Selektif Berbasis Search & Checklist

### 5.1. Latar Belakang & Kebutuhan

Pada menu **Audit Log Aktivitas Sistem** (`/admin/super-admin/activity-logs`):

- Saat ini tombol export mendownload data berdasarkan seluruh log atau filter global.
- **Aturan Prioritas Ekspor Baru**:
    1. **Prioritas 1 (Tertinggi - Checklist)**: Jika Super Admin mencentang (_checklist_) beberapa baris log tertentu (walaupun dalam kondisi hasil pencarian), maka **hanya baris yang dicentang** yang diekspor ke CSV.
    2. **Prioritas 2 (Pencarian / Search)**: Jika tidak ada baris yang dicentang namun Super Admin mengisi kata kunci pencarian atau filter modul/aksi, maka **seluruh data hasil pencarian/filter** yang diekspor.
    3. **Fallback**: Jika tidak ada checklist dan tidak ada pencarian, ekspor 1000 log terbaru.

---

### 5.2. Langkah Implementasi

#### File yang Terlibat:

1. `resources/views/super_admin/activity_logs/index.blade.php` — Tambahkan checkbox select-all & checkbox per row.
2. `app/Http/Controllers/SuperAdmin/SystemActivityLogController.php` — Modifikasi method `exportCsv()`.

#### Langkah 1: Update Frontend Checkbox & Form Export

```html
<!-- Table Header Checkbox -->
<th class="p-3 text-center w-10">
    <input
        type="checkbox"
        id="selectAllLogs"
        onchange="toggleSelectAllLogs(this)"
        class="rounded text-indigo-600 focus:ring-indigo-500"
    />
</th>

<!-- Table Body Checkbox -->
<td class="p-3 text-center">
    <input
        type="checkbox"
        name="selected_log_ids[]"
        value="{{ $log->id }}"
        class="log-checkbox rounded text-indigo-600 focus:ring-indigo-500"
    />
</td>
```

```javascript
function submitExportCsv() {
    const selectedCheckboxes = Array.from(
        document.querySelectorAll(".log-checkbox:checked"),
    ).map((cb) => cb.value);
    const searchVal = document.getElementById("searchInput")?.value || "";
    const moduleVal = document.getElementById("moduleFilter")?.value || "";
    const actionVal = document.getElementById("actionFilter")?.value || "";

    const form = document.createElement("form");
    form.method = "GET";
    form.action = "{{ route('super-admin.activity-logs.export') }}";

    if (selectedCheckboxes.length > 0) {
        // Prioritas 1: Kirim ID Checklist
        const inputIds = document.createElement("input");
        inputIds.type = "hidden";
        inputIds.name = "ids";
        inputIds.value = selectedCheckboxes.join(",");
        form.appendChild(inputIds);
    } else {
        // Prioritas 2: Kirim Kriteria Search
        if (searchVal) {
            const inputSearch = document.createElement("input");
            inputSearch.type = "hidden";
            inputSearch.name = "search";
            inputSearch.value = searchVal;
            form.appendChild(inputSearch);
        }
        if (moduleVal) {
            const inputMod = document.createElement("input");
            inputMod.type = "hidden";
            inputMod.name = "module";
            inputMod.value = moduleVal;
            form.appendChild(inputMod);
        }
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
```

#### Langkah 2: Controller Backend `exportCsv`

```php
// app/Http/Controllers/SuperAdmin/SystemActivityLogController.php
public function exportCsv(Request $request)
{
    $query = SystemActivityLog::query()->latest();

    // Prioritas 1: Jika ada daftar ID terpilih
    if ($request->filled('ids')) {
        $ids = explode(',', $request->input('ids'));
        $query->whereIn('id', $ids);
    }
    // Prioritas 2: Jika ada parameter filter / pencarian
    else {
        if ($request->filled('search')) {
            $keyword = trim($request->search);
            $query->where(function ($q) use ($keyword) {
                $q->where('description', 'like', "%{$keyword}%")
                  ->orWhere('user_name', 'like', "%{$keyword}%")
                  ->orWhere('ip_address', 'like', "%{$keyword}%");
            });
        }
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
    }

    $logs = $query->limit(5000)->get();

    // Generate CSV Stream with UTF-8 BOM
    $filename = 'audit_logs_' . now()->format('Ymd_His') . '.csv';
    return response()->streamDownload(function () use ($logs) {
        $handle = fopen('php://output', 'w');
        fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
        fputcsv($handle, ['ID', 'Waktu', 'Pengguna', 'Role', 'Aksi', 'Modul', 'Deskripsi', 'IP Address']);

        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->created_at->format('Y-m-d H:i:s'),
                $log->user_name,
                $log->user_role,
                $log->action,
                $log->module,
                $log->description,
                $log->ip_address,
            ]);
        }
        fclose($handle);
    }, $filename, [
        'Content-Type' => 'text/csv',
        'Cache-Control' => 'no-store, no-cache',
    ]);
}
```

---

## 6. Penyempurnaan Presensi Offline: Waktu Masuk Fisik & Verifikasi Alpha/Izin/Sakit/Early

### 6.1. Latar Belakang & Kebutuhan

Modul **Presensi Offline** (`OfflineAttendanceController` di `/admin/absen-offline`) dirancang untuk memverifikasi kehadiran fisik dan mencegah fraud. Penyesuaian yang diminta:

1. **Informasi Tambahan di Kolom Pemagang**:
    - Menampilkan **Waktu Absen Online** (jam presensi masuk di sistem).
    - Menampilkan **Waktu Masuk ke Kantor** (jam kedatangan fisik aktual).
2. **Form Input Waktu Masuk Kantor**:
    - Input jam (`arrival_time` / `physical_checkin_time`) saat admin mencatat verifikasi fisik.
3. **Pemisahan Kategori Verifikasi Alpha**:
    - Alpha dipecah menjadi 3 verifikasi jelas: **Izin**, **Sakit**, dan **Alpha Murni**.
4. **Verifikasi Izin**:
    - Admin/Super Admin/Asisten memeriksa validitas izin.
    - Jika **Valid** $\rightarrow$ Masuk ke Verifikasi Izin Diterima.
    - Jika **Tidak Valid** $\rightarrow$ Dikonversi menjadi Alpha Murni + Sanksi.
5. **Verifikasi Sakit**:
    - Menampilkan 3 pilihan status sakit:
        1. **Sakit Berbohong** (indikasi kecurangan/tanpa bukti valid $\rightarrow$ penalti Alpha).
        2. **Sakit dengan Surat** (terlampir surat dokter sah $\rightarrow$ verifikasi diterima).
        3. **Sakit Sudah Dicek HR/Admin** (telah dikonfirmasi langsung oleh tim HR).
6. **Verifikasi Masuk Lebih Awal (_Early Check-in_)**:
    - Menambahkan status verifikasi untuk pemagang yang hadir lebih awal dari jam mulai shift.

---

### 6.2. Skema Database & File yang Diubah

#### Perubahan Kolom Tabel `offline_attendances`

```php
Schema::table('offline_attendances', function (Blueprint $table) {
    if (!Schema::hasColumn('offline_attendances', 'physical_checkin_time')) {
        $table->time('physical_checkin_time')->nullable()->after('check_time');
    }
    if (!Schema::hasColumn('offline_attendances', 'sickness_verification_type')) {
        $table->string('sickness_verification_type', 50)->nullable()->after('status');
        // Pilihan: 'fake_sickness', 'doctor_letter', 'verified_by_hr'
    }
    if (!Schema::hasColumn('offline_attendances', 'permit_is_valid')) {
        $table->boolean('permit_is_valid')->nullable()->after('sickness_verification_type');
    }
});
```

#### File yang Terlibat:

1. `app/Models/OfflineAttendance.php` — Tambahkan field baru ke fillable & helper accessor.
2. `app/Http/Controllers/OfflineAttendanceController.php` — Update method `index()`, `store()`, dan status mapping.
3. `resources/views/admin/absen-offline/index.blade.php` — Desain ulang form verifikasi modal dan tabel monitoring fisik.

---

### 6.3. Langkah Implementasi

#### Langkah 1: Controller Query Data Gabungan

Pada `OfflineAttendanceController::index`, gabungkan data presensi online (`attendances.start_time`), data izin hari ini, dan data fisik:

```php
$internData = $interns->map(function ($intern) use ($date) {
    $onlineAttendance = Attendance::where('user_id', $intern->user_id)
        ->whereDate('date', $date)
        ->first();

    $offline = OfflineAttendance::where('intern_id', $intern->id)
        ->whereDate('date', $date)
        ->first();

    return [
        'intern_id' => $intern->id,
        'name' => $intern->user->profile->full_name ?? $intern->user->username,
        'school' => $intern->school->name ?? '-',
        'shift_name' => $intern->shift->name ?? '-',
        'shift_start' => $intern->shift->start_time ?? '-',
        'online_checkin' => $onlineAttendance ? substr($onlineAttendance->start_time, 0, 5) : 'Belum Absen',
        'physical_checkin' => $offline?->physical_checkin_time ? substr($offline->physical_checkin_time, 0, 5) : '-',
        'status' => $offline->status ?? 'belum_dicek',
        'sickness_type' => $offline->sickness_verification_type ?? null,
        'permit_valid' => $offline->permit_is_valid ?? null,
    ];
});
```

#### Langkah 2: Form Verifikasi Modal (Opsi Lengkap)

```html
<div class="space-y-3">
    <!-- Waktu Masuk Kantor -->
    <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1"
            >Waktu Masuk Kantor (Fisik)</label
        >
        <input
            type="time"
            name="physical_checkin_time"
            class="w-full p-2 text-xs border rounded-lg"
        />
    </div>

    <!-- Pilihan Status Verifikasi Utama -->
    <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1"
            >Status Kehadiran Fisik</label
        >
        <select
            name="status"
            id="offline-status-select"
            onchange="onStatusChange(this.value)"
            class="w-full p-2 text-xs border rounded-lg"
        >
            <option value="hadir">Hadir (Sesuai Jam)</option>
            <option value="early">Hadir Lebih Awal (Early Check-in)</option>
            <option value="terlambat">Hadir Terlambat Fisik</option>
            <option value="izin">Izin Tidak Masuk</option>
            <option value="sakit">Sakit</option>
            <option value="alpha">Alpha (Tidak Ada Keterangan / Fraud)</option>
        </select>
    </div>

    <!-- Sub-Opsi Khusus Izin -->
    <div
        id="sub-opt-izin"
        class="hidden p-3 bg-blue-50 rounded-xl border border-blue-100 space-y-2"
    >
        <label class="block text-xs font-bold text-blue-900"
            >Validitas Izin:</label
        >
        <div class="flex gap-4 text-xs">
            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                <input type="radio" name="permit_is_valid" value="1" checked />
                <span>Izin Sah & Valid</span>
            </label>
            <label
                class="inline-flex items-center gap-1.5 cursor-pointer text-rose-600"
            >
                <input type="radio" name="permit_is_valid" value="0" />
                <span>Tidak Sah (Jadikan Alpha)</span>
            </label>
        </div>
    </div>

    <!-- Sub-Opsi Khusus Sakit -->
    <div
        id="sub-opt-sakit"
        class="hidden p-3 bg-amber-50 rounded-xl border border-amber-100 space-y-2"
    >
        <label class="block text-xs font-bold text-amber-900"
            >Kategori Verifikasi Sakit:</label
        >
        <select
            name="sickness_verification_type"
            class="w-full p-2 text-xs border rounded-lg bg-white"
        >
            <option value="doctor_letter">
                1. Sakit dengan Surat Dokter Sah
            </option>
            <option value="verified_by_hr">
                2. Sakit Sudah Dicek HR/Admin
            </option>
            <option value="fake_sickness">
                3. Sakit Berbohong (Fraud / Tanpa Bukti)
            </option>
        </select>
    </div>
</div>
```

---

## 7. Standarisasi Desain Mobile & Layout Responsif

### 7.1. Latar Belakang & Kebutuhan

Menyelaraskan seluruh tampilan halaman admin dan user agar:

1. Tidak ada overflow horizontal di perangkat seluler (`overflow-x-hidden` & `width: auto` dengan margin responsif).
2. Layout tabel memiliki wrapper scroll horizontal yang rapi dengan sticky header.
3. Card metrics dan filter toolbar dapat collapse menjadi 1 atau 2 kolom pada layar sempit tanpa merusak layout.

### 7.2. Aturan CSS & Standar Responsif:

- **Mobile Container**: `ml-0 md:ml-48 lg:ml-64 mt-16 sm:mt-20 p-3 sm:p-6 w-full min-w-0`
- **Dynamic CSS Grid Pattern**: Gunakan `repeat(auto-fill, minmax(280px, 1fr))` atau breakpoint bertingkat (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5`).
- **Tabel Responsif**: Selalu bungkus tag `<table>` di dalam `<div class="overflow-x-auto scrollbar-thin w-full">`.

---

## 8. Panduan Pengujian & Checklist Verifikasi

Setelah seluruh modul diimplementasikan, jalankan verifikasi berikut:

- [ ] **Modul 1 (Presentasi)**:
    - Ajukan raise hand presentasi dari akun pemagang $\rightarrow$ Pastikan muncul modal popup detail pengajuan.
    - Buka tab Presentasi di Admin $\rightarrow$ Buka form terima presentasi $\rightarrow$ Pastikan tampil Shift dan Nama Pemagang.
    - Uji tombol **Terima**, **Reschedule**, dan **Tolak** $\rightarrow$ Pastikan status terupdate dan catatan tersimpan.
- [ ] **Modul 2 (Hybrid & GPS)**:
    - Cek form schedule dan detail presensi $\rightarrow$ Pastikan opsi `hybrid` sudah tidak muncul.
    - Buka menu edit anggota divisi $\rightarrow$ Ubah status GPS menjadi Non-Aktif $\rightarrow$ Simpan $\rightarrow$ Refresh $\rightarrow$ Pastikan nilai tetap tersimpan Non-Aktif.
- [ ] **Modul 3 (Create & Delete Intern)**:
    - Login sebagai Admin biasa $\rightarrow$ Buka form tambah pemagang $\rightarrow$ Buat akun baru $\rightarrow$ Berhasil.
    - Cek tabel anggota $\rightarrow$ Pastikan tombol Hapus tidak muncul untuk Admin biasa.
    - Login sebagai Super Admin $\rightarrow$ Pastikan tombol Hapus muncul dan berfungsi.
- [ ] **Modul 4 (Project Divisi)**:
    - Buka form tambah project $\rightarrow$ Pilih salah satu divisi $\rightarrow$ Pastikan daftar pemagang dan opsi judul terfilter hanya untuk divisi tersebut.
- [ ] **Modul 5 (Audit Log Export)**:
    - Lakukan pencarian log kata "LOGIN" $\rightarrow$ Klik Export CSV tanpa centang $\rightarrow$ File CSV berisi data hasil pencarian.
    - Centang 2 baris log sembarang $\rightarrow$ Klik Export CSV $\rightarrow$ File CSV hanya berisi 2 baris tersebut.
- [x] **Modul 6 (Absen Offline)**:
    - Buka absen offline $\rightarrow$ Input jam masuk fisik $\rightarrow$ Pilih verifikasi sakit (surat dokter / palsu / cek HR) atau izin (valid / ditolak) atau early / terlambat $\rightarrow$ Simpan $\rightarrow$ Pastikan data waktu absen online & fisik tampil berdampingan dan status terverifikasi rapi.
- [ ] **Modul 7 (Mobile Check)**:
    - Uji via Inspect Element (Mode HP 375px & 414px) $\rightarrow$ Pastikan tidak ada scroll horizontal liar dan tombol mudah ditekan (_touch-friendly_).
