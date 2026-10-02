@extends('layouts.main')

@section('title', 'Pengaturan Info & Libur')

@section('contents')

@include('layouts.sidebar-pengaturan')

<!-- Main Content -->
<main class="ml-0 lg:ml-[32rem] mt-2 lg:mt-20 p-3 sm:p-6 min-w-0 max-w-full overflow-x-hidden">
    <div class="w-full max-w-full min-w-0 space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs">
        <h1 class="text-2xl font-bold text-gray-900 mb-1">Manage Info & Libur</h1>
        <p class="text-gray-500 text-sm">Pengaturan kalender hari libur, tautan SOP magang, dan tata tertib kantor</p>
    </div>

    @if (session('success'))
    <div id="success-message"
        class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl relative transition-opacity duration-500 shadow-xs flex items-center justify-between"
        role="alert">
        <div>
            <strong class="font-bold">Sukses!</strong>
            <span class="block sm:inline ml-1">{{ session('success') }}</span>
        </div>
        <button type="button" class="text-emerald-600 hover:text-emerald-900" onclick="removeMessage()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    <!-- Tab Navigation -->
    <div class="grid grid-cols-2 md:grid-cols-4 border-b border-gray-200 bg-white rounded-xl p-1.5 shadow-xs gap-1">

        <button type="button" onclick="switchAdminTab('holiday')" id="btn-tab-holiday"
            class="admin-tab-btn w-full py-2.5 px-4 text-xs sm:text-sm font-semibold rounded-lg bg-blue-600 text-white focus:outline-none flex items-center justify-center gap-2 transition-all shadow-xs">
            <i class="fa-regular fa-calendar-days text-sm"></i>
            <span>Hari Libur</span>
            <span class="text-xs bg-white text-blue-700 px-2 py-0.5 rounded-full font-bold">
                {{ count($holidaylist) }}
            </span>
        </button>

        <button type="button" onclick="switchAdminTab('sop')" id="btn-tab-sop"
            class="admin-tab-btn w-full py-2.5 px-4 text-xs sm:text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none flex items-center justify-center gap-2 transition-all">
            <i class="fa-regular fa-file-lines text-sm"></i>
            <span>SOP Magang</span>
        </button>

        <button type="button" onclick="switchAdminTab('rules')" id="btn-tab-rules"
            class="admin-tab-btn w-full py-2.5 px-4 text-xs sm:text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none flex items-center justify-center gap-2 transition-all">
            <i class="fas fa-building text-sm"></i>
            <span>Peraturan Kantor</span>
        </button>

        <button type="button" onclick="switchAdminTab('piket')" id="btn-tab-piket"
            class="admin-tab-btn w-full py-2.5 px-4 text-xs sm:text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none flex items-center justify-center gap-2 transition-all">
            <i class="fa-solid fa-broom text-sm"></i>
            <span>Jadwal Piket</span>
        </button>

    </div>

    <!-- TAB 1: HARI LIBUR -->
    <div id="content-tab-holiday" class="admin-tab-content space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-xl border border-gray-100 shadow-xs">
            <!-- Add Holiday Button -->
            <button id="addHolidayButton"
                class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-xs font-semibold text-xs sm:text-sm transition">
                <i class="fas fa-plus mr-2"></i> Tambahkan Hari Libur
            </button>

            <!-- Search Input -->
            <div class="relative w-full sm:w-64">
                <input type="text" id="searchInput" placeholder="Cari Hari Libur..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto min-w-0">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-3.5 px-4 text-center w-16">No</th>
                            <th class="py-3.5 px-4">Tanggal</th>
                            <th class="py-3.5 px-4">Nama Hari Libur</th>
                            <th class="py-3.5 px-4 text-right pr-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="holidayTableBody" class="divide-y divide-gray-100 text-xs font-medium text-gray-700">
                        <!-- Rows will be dynamically inserted here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div class="flex flex-wrap justify-between items-center gap-3 bg-white p-4 rounded-xl border border-gray-100 shadow-xs">
            <span class="text-xs text-gray-500 font-medium">Navigasi Halaman</span>
            <div class="flex items-center space-x-2">
                <button id="prev-page"
                    class="cursor-pointer bg-gray-800 hover:bg-gray-900 text-white px-3 py-1.5 rounded-lg text-xs font-semibold disabled:bg-gray-300 disabled:text-gray-500 transition shadow-xs" disabled>
                    Previous
                </button>

                <!-- Page numbers will be dynamically added here -->
                <div id="page-numbers" class="flex items-center space-x-1"></div>

                <button id="next-page" class="bg-gray-800 hover:bg-gray-900 text-white px-3 py-1.5 rounded-lg text-xs font-semibold disabled:bg-gray-300 disabled:text-gray-500 transition shadow-xs">
                    Next
                </button>
            </div>
        </div>
    </div>

    @php
    $sopOfficeId = $sopOfficeId ?? 'all';
    $sopOffice = ($sopOfficeId && $sopOfficeId !== 'all')
        ? ($offices->firstWhere('id', (int)$sopOfficeId) ?? $offices->first())
        : $offices->first();
    $isSopApplyAll = ($sopOfficeId === 'all');

    $firstOfficeId = (string)($offices->first()?->id ?? '1');

    $rulesOfficeId = $rulesOfficeId ?? $firstOfficeId;
    $rulesOffice = ($rulesOfficeId && $rulesOfficeId !== 'all')
        ? ($offices->firstWhere('id', (int)$rulesOfficeId) ?? $offices->first())
        : $offices->first();
    $isRulesApplyAll = ($rulesOfficeId === 'all');

    $piketOfficeId = $piketOfficeId ?? $firstOfficeId;
    $piketOffice = ($piketOfficeId && $piketOfficeId !== 'all')
        ? ($offices->firstWhere('id', (int)$piketOfficeId) ?? $offices->first())
        : $offices->first();
    $isPiketApplyAll = ($piketOfficeId === 'all');
    @endphp

    <!-- TAB 2: SOP MAGANG -->
    <div id="content-tab-sop" class="admin-tab-content hidden space-y-6">
        <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5 flex items-start space-x-3 sm:space-x-4 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-700 text-lg flex-shrink-0">
                <i class="fas fa-file-contract text-blue-600"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 text-sm sm:text-base">Standar Operasional Prosedur (SOP) Magang</h3>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                    Tautan dokumen resmi SOP magang (Google Docs / PDF) yang dapat diakses pemagang di modal Info & Libur.
                </p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <form action="{{ route('admin.pengaturan.holiday.updateInfo') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="active_tab" value="sop">

                <div>
                    <label for="sop_office_id" class="block text-sm font-semibold text-gray-700 mb-2">Pilih Kantor / Target Penerapan</label>
                    <select id="sop_office_id" name="office_id" onchange="handleOfficeSelectChange('sop', this.value)" class="w-full md:w-1/2 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm">
                        <option value="all" {{ $sopOfficeId === 'all' ? 'selected' : '' }}>Semua Lokasi Kantor (Global)</option>
                        @foreach($offices as $office)
                        <option value="{{ $office->id }}" {{ (string)$sopOfficeId === (string)$office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="input_sop_url" class="block text-sm font-semibold text-gray-700 mb-2">
                        Link Dokumen SOP Magang (URL)
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="url" id="input_sop_url" name="sop_url"
                            value="{{ $sopOffice?->sop_url ?? 'https://docs.google.com/document/d/sop-magang-djuragan' }}"
                            placeholder="https://docs.google.com/document/d/..."
                            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm">
                        <a id="btn-preview-sop"
                            href="{{ $sopOffice?->sop_url ?? '#' }}"
                            target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-800 hover:bg-gray-900 text-white rounded-xl font-semibold text-xs transition shadow-xs">
                            <i class="fas fa-arrow-up-right-from-square mr-1.5"></i> Tes Link
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-1.5">Pastikan akses share dokumen Google Docs disetel ke <em>"Anyone with the link can view"</em>.</p>
                </div>

                <div class="flex items-center">
                    <input type="hidden" name="apply_all" value="0">
                    <input type="checkbox" id="sop_apply_all" name="apply_all" value="1" onchange="handleApplyAllToggle('sop', this.checked)" class="h-4 w-4 text-blue-600 rounded border-gray-300 cursor-pointer" {{ $isSopApplyAll ? 'checked' : '' }}>
                    <label for="sop_apply_all" class="ml-2 text-xs font-medium text-gray-700 cursor-pointer">Terapkan tautan SOP ini ke semua lokasi kantor</label>
                </div>

                <div class="pt-3 border-t flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-xs sm:text-sm shadow-xs transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Pengaturan SOP
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 3: PERATURAN KANTOR -->
    <div id="content-tab-rules" class="admin-tab-content hidden space-y-6">
        <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5 flex items-start space-x-3 sm:space-x-4 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-700 text-lg flex-shrink-0">
                <i class="fas fa-building text-indigo-600"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 text-sm sm:text-base">Tata Tertib & Peraturan Kantor</h3>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                    Kelola poin tata tertib dan dokumen aturan penempatan sesuai kantor masing-masing.
                </p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <form action="{{ route('admin.pengaturan.holiday.updateInfo') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="active_tab" value="rules">

                <div>
                    <label for="rules_office_select" class="block text-sm font-semibold text-gray-700 mb-2">Pilih Kantor / Target Penerapan</label>
                    <select id="rules_office_select" name="office_id" onchange="handleOfficeSelectChange('rules', this.value)" class="w-full md:w-1/2 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm">
                        <option value="all" {{ $rulesOfficeId === 'all' ? 'selected' : '' }}>Semua Lokasi Kantor (Global)</option>
                        @foreach($offices as $office)
                        <option value="{{ $office->id }}" {{ (string)$rulesOfficeId === (string)$office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="input_rules_url" class="block text-sm font-semibold text-gray-700 mb-2">
                        Link Dokumen Peraturan Kantor (URL)
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="url" id="input_rules_url" name="rules_url"
                            value="{{ $rulesOffice?->rules_url ?? '' }}"
                            placeholder="https://docs.google.com/document/d/..."
                            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm">
                        <a id="btn-preview-rules"
                            href="{{ $rulesOffice?->rules_url ?? '#' }}"
                            target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-800 hover:bg-gray-900 text-white rounded-xl font-semibold text-xs transition shadow-xs">
                            <i class="fas fa-arrow-up-right-from-square mr-1.5"></i> Tes Link
                        </a>
                    </div>
                </div>

                <div>
                    <label for="input_rules_desc" class="block text-sm font-semibold text-gray-700 mb-2">
                        Poin-Poin Tata Tertib & Peraturan Kantor
                    </label>
                    <textarea id="input_rules_desc" name="rules_description" rows="7"
                        placeholder="Contoh:&#10;1. Wajib hadir dan presensi tepat waktu.&#10;2. Berpakaian rapi dan mengenakan ID Card.&#10;3. Menjaga kebersihan meja kerja..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm leading-relaxed">{{ $rulesOffice?->rules_description ?? '' }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Gunakan baris baru (Enter) atau angka urut untuk setiap poin aturan.</p>
                </div>

                <div class="flex items-center">
                    <input type="hidden" name="apply_all" value="0">
                    <input type="checkbox" id="rules_apply_all" name="apply_all" value="1" onchange="handleApplyAllToggle('rules', this.checked)" class="h-4 w-4 text-indigo-600 rounded border-gray-300 cursor-pointer" {{ $isRulesApplyAll ? 'checked' : '' }}>
                    <label for="rules_apply_all" class="ml-2 text-xs font-medium text-gray-700 cursor-pointer">Terapkan aturan & link ini ke semua kantor sekaligus</label>
                </div>

                <div class="pt-3 border-t flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold text-xs sm:text-sm shadow-xs transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Peraturan Kantor
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 4: JADWAL PIKET -->
    <div id="content-tab-piket" class="admin-tab-content hidden space-y-6">
        <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5 flex items-start space-x-3 sm:space-x-4 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-700 text-lg flex-shrink-0">
                <i class="fa-solid fa-broom text-amber-600"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 text-sm sm:text-base">Jadwal & Tugas Piket Kantor</h3>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                    Kelola tautan spreadsheet dan rincian tugas piket kebersihan harian kantor.
                </p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <form action="{{ route('admin.pengaturan.holiday.updateInfo') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="active_tab" value="piket">

                <div>
                    <label for="piket_office_select" class="block text-sm font-semibold text-gray-700 mb-2">Pilih Kantor / Target Penerapan</label>
                    <select id="piket_office_select" name="office_id" onchange="handleOfficeSelectChange('piket', this.value)" class="w-full md:w-1/2 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:outline-none text-sm">
                        <option value="all" {{ $piketOfficeId === 'all' ? 'selected' : '' }}>Semua Lokasi Kantor (Global)</option>
                        @foreach($offices as $office)
                        <option value="{{ $office->id }}" {{ (string)$piketOfficeId === (string)$office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="input_piket_url" class="block text-sm font-semibold text-gray-700 mb-2">
                        Link Dokumen / Spreadsheet Jadwal Piket (URL)
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="url" id="input_piket_url" name="piket_url"
                            value="{{ $piketOffice?->piket_url ?? '' }}"
                            placeholder="https://docs.google.com/spreadsheets/d/..."
                            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:outline-none text-sm">
                        <a id="btn-preview-piket"
                            href="{{ $piketOffice?->piket_url ?? '#' }}"
                            target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-800 hover:bg-gray-900 text-white rounded-xl font-semibold text-xs transition shadow-xs">
                            <i class="fas fa-arrow-up-right-from-square mr-1.5"></i> Tes Link
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-1.5">Contoh: Link Google Spreadsheet pembagian giliran piket per hari.</p>
                </div>

                <div>
                    <label for="input_piket_desc" class="block text-sm font-semibold text-gray-700 mb-2">
                        Poin Tugas & Ketentuan Piket Kebersihan
                    </label>
                    <textarea id="input_piket_desc" name="piket_description" rows="7"
                        placeholder="Contoh:&#10;1. Datang 15 menit lebih awal untuk persiapan ruang kerja.&#10;2. Menyapu dan merapikan ruang kerja bersama.&#10;3. Membuang sampah ke tempat pembuangan akhir di sore hari.&#10;4. Memastikan AC dan lampu telah dimatikan sebelum pulang..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:outline-none text-sm leading-relaxed">{{ $piketOffice?->piket_description ?? '' }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Poin-poin tugas yang wajib dijalankan oleh petugas piket hari tersebut.</p>
                </div>

                <div class="flex items-center">
                    <input type="hidden" name="apply_all" value="0">
                    <input type="checkbox" id="piket_apply_all" name="apply_all" value="1" onchange="handleApplyAllToggle('piket', this.checked)" class="h-4 w-4 text-amber-600 rounded border-gray-300 cursor-pointer" {{ $isPiketApplyAll ? 'checked' : '' }}>
                    <label for="piket_apply_all" class="ml-2 text-xs font-medium text-gray-700 cursor-pointer">Terapkan tautan & ketentuan piket ini ke semua kantor sekaligus</label>
                </div>

                <div class="pt-3 border-t flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-semibold text-xs sm:text-sm shadow-xs transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Pengaturan Piket
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
</main>

<!-- Modal Tambah Hari Libur -->
<div id="addHolidayModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4 transition-all">
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md border border-gray-100 animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">Tambahkan Hari Libur</h2>
            <button type="button" class="text-gray-400 hover:text-gray-600 transition" onclick="$('#addHolidayModal').addClass('hidden')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('holidays.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="holidayDate" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Tanggal<span class="text-rose-500 ml-0.5">*</span></label>
                <input type="date" id="holidayDate" name="date"
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>
            <div>
                <label for="holidayName" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nama Hari Libur<span class="text-rose-500 ml-0.5">*</span></label>
                <input type="text" id="holidayName" name="name" placeholder="Contoh: Hari Raya Idul Fitri"
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" id="closeAddHolidayModal"
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Hari Libur -->
<div id="editHolidayModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4 transition-all">
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md border border-gray-100 animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">Update Hari Libur</h2>
            <button type="button" class="text-gray-400 hover:text-gray-600 transition" onclick="$('#editHolidayModal').addClass('hidden')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editHolidayForm" action="" method="POST" class="space-y-4">
            @csrf
            {{-- @method('PUT') --}}
            <input type="hidden" id="editHolidayId" name="holidayId">
            <div>
                <label for="editHolidayDate" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Tanggal<span class="text-rose-500 ml-0.5">*</span></label>
                <input type="date" id="editHolidayDate" name="date"
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>
            <div>
                <label for="editHolidayName" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nama Hari Libur<span class="text-rose-500 ml-0.5">*</span></label>
                <input type="text" id="editHolidayName" name="name"
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" id="closeEditHolidayModal"
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteHolidayModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4 transition-all">
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm border border-gray-100 animate-in fade-in zoom-in duration-200 text-center">
        <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 mx-auto flex items-center justify-center text-xl mb-4">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2 class="text-base font-bold text-gray-900 mb-2">Hapus Hari Libur</h2>
        <p class="text-xs text-gray-500 mb-6">Apakah Anda yakin ingin menghapus data hari libur ini? Tindakan ini tidak dapat dibatalkan.</p>
        <form id="deleteHolidayForm" method="POST">
            @csrf
            @method('DELETE')
            <input type="hidden" id="deleteHolidayId" name="holidayId">
            <div class="flex justify-center gap-2">
                <button type="button" id="closeDeleteHolidayModal"
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

<script id="holiday-data" type="application/json">
    @json($holidaylist)
</script>
<script id="offices-data" type="application/json">
    @json($offices)
</script>
<script>
    const allOfficesData = JSON.parse(document.getElementById('offices-data')?.textContent || '[]');

    function switchAdminTab(tabName) {
        $('.admin-tab-content').addClass('hidden');
        $(`#content-tab-${tabName}`).removeClass('hidden');

        $('.admin-tab-btn').removeClass('bg-blue-600 text-white font-semibold shadow-xs')
            .addClass('text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-medium');

        $(`#btn-tab-${tabName}`).removeClass('text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-medium')
            .addClass('bg-blue-600 text-white font-semibold shadow-xs');
    }

    function handleOfficeSelectChange(type, officeId) {
        const checkbox = document.getElementById(`${type}_apply_all`);
        if (officeId === 'all') {
            if (checkbox) checkbox.checked = true;
            const first = allOfficesData[0] || {};
            populateOfficeFields(type, first);
        } else {
            if (checkbox) checkbox.checked = false;
            const found = allOfficesData.find(o => String(o.id) === String(officeId)) || {};
            populateOfficeFields(type, found);
        }
    }

    function handleApplyAllToggle(type, isChecked) {
        const selectId = type === 'rules' ? 'rules_office_select' : (type === 'piket' ? 'piket_office_select' : 'sop_office_id');
        const select = document.getElementById(selectId);
        if (!select) return;

        if (isChecked) {
            select.value = 'all';
            // Biarkan isi input tetap ada agar admin bisa menerapkan teks/link yang sedang diketik ke semua kantor
        } else {
            if (select.value === 'all') {
                const firstId = allOfficesData[0]?.id;
                if (firstId) {
                    select.value = String(firstId);
                }
            }
        }
    }

    function populateOfficeFields(type, office) {
        if (type === 'sop') {
            $('#input_sop_url').val(office.sop_url || '');
            $('#btn-preview-sop').attr('href', office.sop_url || '#');
        } else if (type === 'rules') {
            $('#input_rules_url').val(office.rules_url || '');
            $('#input_rules_desc').val(office.rules_description || '');
            $('#btn-preview-rules').attr('href', office.rules_url || '#');
        } else if (type === 'piket') {
            $('#input_piket_url').val(office.piket_url || '');
            $('#input_piket_desc').val(office.piket_description || '');
            $('#btn-preview-piket').attr('href', office.piket_url || '#');
        }
    }

    function loadOfficeRules(officeId) {
        handleOfficeSelectChange('rules', officeId);
    }

    function loadOfficePiket(officeId) {
        handleOfficeSelectChange('piket', officeId);
    }

    $(document).ready(function() {
        // Assuming `HolidayList` is passed as a JavaScript array from backend for demonstration
        const HolidayList = JSON.parse(document.getElementById('holiday-data')?.textContent || '[]');
        const itemsPerPage = 5;
        let currentPage = 1;
        let filteredData = HolidayList; // Array to hold filtered results

        const totalPages = () => Math.ceil(filteredData.length / itemsPerPage);

        function displayPage(page) {
            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const paginatedData = filteredData.slice(start, end);

            $('#holidayTableBody').html(''); // Clear table body
            if (paginatedData.length === 0) {
                $('#holidayTableBody').html(`
                    <tr>
                        <td colspan="4" class="py-8 text-center text-gray-400 font-normal text-xs">
                            <i class="fas fa-calendar-times text-2xl mb-2 block text-gray-300"></i>
                            Tidak ada data hari libur ditemukan
                        </td>
                    </tr>
                `);
                return;
            }

            paginatedData.forEach((holiday, index) => {
                $('#holidayTableBody').append(`
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="py-3 px-4 text-center text-gray-400 font-bold">${start + index + 1}</td>
                        <td class="py-3 px-4 font-semibold text-gray-900">${holiday.date}</td>
                        <td class="py-3 px-4 text-gray-700">${holiday.name}</td>
                        <td class="py-3 px-4 text-right pr-6">
                            <div class="flex items-center justify-end gap-2">
                                <button class="editHolidayModal px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold transition shadow-xs" data-id="${holiday.id}" data-name="${holiday.name}" data-date="${holiday.date}">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </button>
                                <button class="deleteHoliday px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition shadow-xs" data-id="${holiday.id}">
                                    <i class="fas fa-trash mr-1"></i>Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                `);
            });
        }

        function updatePagination() {
            $('#page-numbers').empty();

            for (let i = 1; i <= totalPages(); i++) {
                const isActive = i === currentPage ? 'bg-blue-600 text-white font-bold' : 'bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold';
                const pageButton = $(`
                    <button class="page-number ${isActive} px-3 py-1.5 rounded-lg text-xs transition shadow-xs" data-page="${i}">${i}</button>
                `);

                pageButton.on('click', function() {
                    currentPage = i;
                    displayPage(currentPage);
                    updatePagination();
                });

                $('#page-numbers').append(pageButton);
            }

            $('#prev-page').prop('disabled', currentPage === 1);
            $('#next-page').prop('disabled', currentPage === totalPages() || totalPages() === 0);
        }

        $('#prev-page').on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                displayPage(currentPage);
                updatePagination();
            }
        });

        $('#next-page').on('click', function() {
            if (currentPage < totalPages()) {
                currentPage++;
                displayPage(currentPage);
                updatePagination();
            }
        });

        // Search functionality
        $('#searchInput').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            filteredData = HolidayList.filter(holiday =>
                holiday.name.toLowerCase().includes(searchTerm) ||
                holiday.date.toLowerCase().includes(searchTerm)
            );
            currentPage = 1;
            displayPage(currentPage);
            updatePagination();
        });

        // Initial display
        displayPage(currentPage);
        updatePagination();

        $(document).on('click', '.editHolidayModal', function() {
            const holidayId = $(this).data('id');
            const holidayName = $(this).data('name');
            let holidayDate = $(this).data('date');

            if (holidayDate) {
                const parts = holidayDate.split('-');
                holidayDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
            }

            // Set values in the form
            $('#editHolidayId').val(holidayId);
            $('#editHolidayName').val(holidayName);
            $('#editHolidayDate').val(holidayDate);

            // Update form action URL
            let actionUrl = '/admin/setting/update-holiday/:id';
            actionUrl = actionUrl.replace(':id', holidayId);
            $('#editHolidayForm').attr('action', actionUrl);

            // Show the edit modal
            $('#editHolidayModal').removeClass('hidden');
        });

        // Close edit modal
        $('#closeEditHolidayModal').on('click', function() {
            $('#editHolidayModal').addClass('hidden');
        });

        $(document).on('click', '.deleteHoliday', function() {
            const holidayId = $(this).data('id');

            let actionUrl = '/admin/setting/delete-holiday/:id';
            actionUrl = actionUrl.replace(':id', holidayId);
            $('#deleteHolidayForm').attr('action', actionUrl);

            $('#deleteHolidayModal').removeClass('hidden');
        });

        $('#closeDeleteHolidayModal').on('click', function() {
            $('#deleteHolidayModal').addClass('hidden');
        });

        $(window).on('click', function(event) {
            if ($(event.target).is('#addHolidayModal')) {
                $('#addHolidayModal').addClass('hidden');
            }
            if ($(event.target).is('#editHolidayModal')) {
                $('#editHolidayModal').addClass('hidden');
            }
            if ($(event.target).is('#deleteHolidayModal')) {
                $('#deleteHolidayModal').addClass('hidden');
            }
        });

        // Modal for adding a new holiday
        $('#addHolidayButton').on('click', function() {
            $('#addHolidayModal').removeClass('hidden');
        });

        $('#closeAddHolidayModal').on('click', function() {
            $('#addHolidayModal').addClass('hidden');
        });

    });

    // Display success message temporarily & initialize active tab
    document.addEventListener('DOMContentLoaded', function() {
        const message = document.getElementById('success-message');
        if (message) {
            setTimeout(() => {
                message.style.opacity = 0;
                setTimeout(() => message.remove(), 600);
            }, 3000);
        }

        const initialTab = "{{ $activeTab ?? 'holiday' }}";
        switchAdminTab(initialTab);

        $('#input_sop_url').on('input', function() {
            const val = $(this).val();
            $('#btn-preview-sop').attr('href', val || '#');
        });

        $('#input_rules_url').on('input', function() {
            const val = $(this).val();
            $('#btn-preview-rules').attr('href', val || '#');
        });

        $('#input_piket_url').on('input', function() {
            const val = $(this).val();
            $('#btn-preview-piket').attr('href', val || '#');
        });
    });
</script>
@endsection