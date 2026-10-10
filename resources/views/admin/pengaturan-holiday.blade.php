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
                @if(isset($totalPiketCount) && $totalPiketCount > 0)
                <span class="text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full font-bold">
                    {{ $totalPiketCount }}
                </span>
                @endif
            </button>

        </div>

        <!-- TAB 1: HARI LIBUR -->
        <div id="content-tab-holiday" class="admin-tab-content space-y-4">
            <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-gray-100 shadow-xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                    <!-- Action Buttons (Left) -->
                    <div class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                        <button id="addHolidayButton" type="button"
                            class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl shadow-xs font-semibold text-xs sm:text-sm transition">
                            <i class="fas fa-plus text-xs"></i>
                            <span>Tambahkan Libur</span>
                        </button>
                        <button id="syncHolidayButton" type="button"
                            class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl shadow-xs font-semibold text-xs sm:text-sm transition">
                            <i class="fas fa-cloud-arrow-down text-xs"></i>
                            <span>Sinkronkan API</span>
                        </button>
                    </div>

                    <!-- Filter & Search (Right) -->
                    <div class="flex items-center gap-2 w-full lg:w-auto">
                        <!-- Badge Tahun -->
                        <span class="inline-flex items-center gap-1.5 px-3 py-2.5 bg-blue-50 text-blue-700 border border-blue-100 rounded-xl text-xs font-bold shrink-0">
                            <i class="fa-regular fa-calendar-days text-blue-600"></i>
                            <span>Tahun 2026</span>
                        </span>

                        <!-- Search Input -->
                        <div class="relative flex-1 lg:w-64">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-search text-xs"></i>
                            </span>
                            <input type="text" id="searchInput" placeholder="Cari Hari Libur..."
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50/50 hover:bg-white focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-2xs">
                        </div>
                    </div>
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
        <div id="content-tab-piket" class="admin-tab-content hidden space-y-5">

            <!-- 1. Panel Filter Tanggal & Generator Otomatis Jadwal Piket -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-200 p-5 sm:p-6 space-y-5">
                <!-- Header Panel -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold border border-amber-200/80 shadow-2xs shrink-0">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 text-sm sm:text-base flex items-center gap-2">
                                <span>Filter Tanggal & Generator Piket</span>
                            </h4>
                            <p class="text-xs text-gray-500 mt-0.5">Tentukan rentang tanggal untuk memfilter roster atau jalankan distribusi jadwal piket otomatis.</p>
                        </div>
                    </div>

                    <!-- Quick Preset Dropdown -->
                    <div class="relative inline-block text-left" id="piket-preset-dropdown-container">
                        <button type="button" onclick="togglePiketPresetDropdown(event)" id="piket-preset-dropdown-btn"
                            class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold bg-white hover:bg-gray-50 text-gray-700 rounded-xl border border-gray-300 shadow-2xs transition focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <i class="fa-regular fa-calendar-check text-amber-600 text-xs"></i>
                            <span>Pilih Periode Cepat</span>
                            <i class="fas fa-chevron-down text-[10px] text-gray-400 transition-transform duration-200" id="piket-preset-chevron"></i>
                        </button>
                        <div id="piket-preset-dropdown-menu"
                            class="hidden absolute right-0 mt-1.5 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-30 ring-1 ring-black/5">
                            <button type="button" onclick="setPiketPeriodPreset('this_week')"
                                class="w-full text-left px-3.5 py-2 text-xs font-medium text-gray-700 hover:bg-amber-50 hover:text-amber-800 flex items-center gap-2.5 transition">
                                <i class="fa-solid fa-calendar-week text-amber-500 text-xs w-3.5 text-center"></i>
                                <span>Minggu Ini</span>
                            </button>
                            <button type="button" onclick="setPiketPeriodPreset('next_week')"
                                class="w-full text-left px-3.5 py-2 text-xs font-medium text-gray-700 hover:bg-amber-50 hover:text-amber-800 flex items-center gap-2.5 transition">
                                <i class="fa-solid fa-forward text-amber-500 text-xs w-3.5 text-center"></i>
                                <span>Minggu Depan</span>
                            </button>
                            <button type="button" onclick="setPiketPeriodPreset('this_month')"
                                class="w-full text-left px-3.5 py-2 text-xs font-medium text-gray-700 hover:bg-amber-50 hover:text-amber-800 flex items-center gap-2.5 transition">
                                <i class="fa-regular fa-calendar-days text-amber-500 text-xs w-3.5 text-center"></i>
                                <span>Bulan Ini</span>
                            </button>
                        </div>
                    </div>
                </div>

                <form id="piket-generator-form" class="space-y-4">
                    @csrf
                    <!-- Baris 1: Filter Rentang Tanggal -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        <div class="sm:col-span-5">
                            <label for="piket_gen_start" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                <i class="fa-regular fa-calendar mr-1 text-gray-400"></i> Tanggal Mulai <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" id="piket_gen_start" name="start_date"
                                value="{{ $piketStartDate }}"
                                class="w-full h-10 px-3.5 text-xs sm:text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none shadow-2xs bg-white">
                        </div>

                        <div class="sm:col-span-5">
                            <label for="piket_gen_end" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                <i class="fa-regular fa-calendar mr-1 text-gray-400"></i> Tanggal Selesai <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" id="piket_gen_end" name="end_date"
                                value="{{ $piketEndDate }}"
                                class="w-full h-10 px-3.5 text-xs sm:text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none shadow-2xs bg-white">
                        </div>

                        <div class="sm:col-span-2">
                            <button type="button" onclick="reloadPiketView()"
                                class="w-full h-10 px-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold text-xs sm:text-sm transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer"
                                title="Terapkan filter rentang tanggal">
                                <i class="fas fa-filter text-xs"></i>
                                <span>Filter Tanggal</span>
                            </button>
                        </div>
                    </div>

                    <!-- Baris 2: Generator & Tindakan Piket -->
                    <div class="bg-slate-50/90 border border-slate-200/90 rounded-2xl p-4 sm:p-5 space-y-3.5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-800">Distribusi & Eksekusi Piket</span>
                            </div>
                            <div class="text-[11px] text-gray-500 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-info text-amber-500 shrink-0"></i>
                                <span>WFH otomatis dilewati &bull; Terjadwal: <span id="piket_limit_info_badge" class="font-bold text-amber-700">{{ $currentPiketLimit ?? 1 }}x seminggu</span> saat WFO</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                            <!-- Batas Piket -->
                            <div class="lg:col-span-3">
                                <label for="piket_gen_max_per_week" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Batas Piket / Minggu
                                </label>
                                <div class="relative">
                                    <select id="piket_gen_max_per_week" name="max_piket_per_week"
                                        class="w-full h-10 pl-3 pr-8 text-xs font-semibold border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 bg-white cursor-pointer appearance-none shadow-2xs text-gray-800"
                                        title="Batas berapa kali pemagang terdaftar piket dalam seminggu">
                                        @for($i = 1; $i <= 6; $i++)
                                        <option value="{{ $i }}" {{ (int)($currentPiketLimit ?? 1) === $i ? 'selected' : '' }}>
                                            Maks {{ $i }}x Seminggu {{ $i === 1 ? '(Default)' : '' }}
                                        </option>
                                        @endfor
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-3 top-3.5 text-gray-400 text-[10px] pointer-events-none"></i>
                                </div>
                            </div>

                            <!-- Pilih Kantor Target -->
                            <div class="lg:col-span-4">
                                <label for="piket_gen_office" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Lokasi Kantor Target
                                </label>
                                <div class="relative">
                                    <select id="piket_gen_office" name="office_id"
                                        class="w-full h-10 pl-3 pr-8 text-xs font-semibold border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 bg-white cursor-pointer appearance-none shadow-2xs text-gray-800">
                                        <option value="all">Semua Kantor (Global)</option>
                                        @foreach($offices as $office)
                                        <option value="{{ $office->id }}">{{ $office->name }}</option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-3 top-3.5 text-gray-400 text-[10px] pointer-events-none"></i>
                                </div>
                            </div>

                            <!-- Tombol Aksi: Reset & Generate -->
                            <div class="sm:col-span-2 lg:col-span-5 flex flex-wrap sm:flex-nowrap items-center gap-2">
                                <button type="button" id="btn-reset-piket" onclick="confirmResetPiket()"
                                    class="flex-1 sm:flex-initial h-10 px-4 bg-white hover:bg-rose-50 text-rose-600 hover:text-rose-700 border border-rose-300 rounded-xl font-bold text-xs shadow-2xs transition flex items-center justify-center gap-1.5 cursor-pointer shrink-0"
                                    title="Reset jadwal piket pada periode & kantor yang dipilih">
                                    <i class="fas fa-trash-can text-xs"></i>
                                    <span>Reset Piket</span>
                                </button>

                                <button type="button" id="btn-generate-piket" onclick="executePiketGeneration()"
                                    class="flex-1 h-10 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs sm:text-sm shadow-xs transition flex items-center justify-center gap-2 cursor-pointer shrink-0">
                                    <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                                    <span id="btn-generate-piket-text">
                                        Generate Piket Otomatis
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- 2. Ringkasan Statistik Pemerataan Harian (Senin - Sabtu) -->
            @php
            $piketByDayName = [
            'Senin' => 0, 'Selasa' => 0, 'Rabu' => 0,
            'Kamis' => 0, 'Jumat' => 0, 'Sabtu' => 0
            ];
            foreach ($piketRoster as $pItem) {
            $dayName = \Carbon\Carbon::parse($pItem->date)->locale('id')->translatedFormat('l');
            if (isset($piketByDayName[$dayName])) {
            $piketByDayName[$dayName]++;
            }
            }
            @endphp
            <div class="bg-white rounded-2xl shadow-xs border border-gray-200 p-5 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h4 class="font-bold text-gray-900 text-sm">Pemerataan Petugas Piket Harian (Senin - Sabtu)</h4>
                        <span class="text-xs bg-amber-50 text-amber-800 border border-amber-200 px-2.5 py-0.5 rounded-full font-bold font-mono">
                            Total: {{ $totalPiketCount }} Penugasan
                        </span>
                    </div>
                    <div class="text-[11px] text-gray-500 font-mono">
                        Periode: {{ \Carbon\Carbon::parse($piketStartDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($piketEndDate)->format('d M Y') }}
                    </div>
                </div>

                <!-- Mini Grid 6 Hari Kerja -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 pt-1">
                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $dName)
                    @php
                    $cnt = $piketByDayName[$dName] ?? 0;
                    $cardBg = $cnt > 0 ? 'bg-amber-50/70 border-amber-200/80 text-amber-950' : 'bg-gray-50 border-gray-200 text-gray-400';
                    @endphp
                    <div class="p-3 rounded-xl border {{ $cardBg }} text-center space-y-1">
                        <div class="text-xs font-bold">{{ $dName }}</div>
                        <div class="text-lg font-extrabold {{ $cnt > 0 ? 'text-amber-700' : 'text-gray-400' }}">{{ $cnt }}</div>
                        <div class="text-[10px] text-gray-500 font-medium">Petugas Piket</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- 3. Tabel Roster Petugas Piket -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden space-y-0">
                <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-gray-50/60">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-clipboard-list"></i>
                                </div>
                                <h4 class="font-bold text-gray-900 text-sm sm:text-base">
                                    Daftar Roster Petugas Piket
                                </h4>
                            </div>
                            <span id="piket-visible-summary" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold font-mono bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs">
                                {{ $globalPiketCount }} Penugasan
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 pl-9 sm:pl-9">
                            Daftar pemagang yang bertugas piket disatukan per sesi shift.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full lg:w-auto shrink-0">
                        <!-- Filter 1: Shift -->
                        <div class="relative min-w-[150px] sm:w-44">
                            <select id="piketShiftFilter" onchange="applyPiketRosterFilter()"
                                class="w-full h-10 pl-3 pr-8 text-xs sm:text-sm font-semibold border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white cursor-pointer appearance-none shadow-2xs">
                                <option value="all">Semua Shift</option>
                                @foreach($shifts as $sh)
                                <option value="{{ $sh->id }}">{{ $sh->name }}</option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-3.5 text-gray-400 text-xs pointer-events-none"></i>
                        </div>

                        <!-- Search Input -->
                        <div class="relative w-full sm:w-72">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-search text-xs"></i>
                            </span>
                            <input type="text" id="piketSearchInput" placeholder="Cari nama, divisi, sekolah, shift..."
                                autocomplete="off"
                                class="w-full h-10 pl-10 pr-9 text-xs sm:text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white shadow-2xs placeholder-gray-400">
                            <button type="button" id="clearPiketSearch" onclick="clearPiketSearch()" title="Hapus pencarian (Esc)"
                                class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition cursor-pointer">
                                <i class="fa-solid fa-circle-xmark text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sub-Tabs Kantor Khusus List Jadwal Piket -->
                <div class="px-4 sm:px-5 py-2.5 bg-white border-b border-gray-100 flex items-center gap-2 overflow-x-auto no-scrollbar">
                    <button type="button" onclick="filterPiketListByOffice('all')" id="tab-piket-office-all"
                        class="tab-piket-office-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 bg-amber-600 text-white shadow-xs">
                        <i class="fa-solid fa-earth-asia text-xs"></i>
                        <span>Semua Kantor (Global)</span>
                        <span id="badge-piket-office-all" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-700 text-white">
                            {{ $globalPiketCount }}
                        </span>
                    </button>
                    @foreach($offices as $office)
                    @php
                    $officeCount = $officePiketCounts[$office->id] ?? 0;
                    @endphp
                    <button type="button" onclick="filterPiketListByOffice('{{ $office->id }}')" id="tab-piket-office-{{ $office->id }}"
                        class="tab-piket-office-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
                        <i class="fa-solid fa-building text-xs"></i>
                        <span>{{ $office->name }}</span>
                        <span id="badge-piket-office-{{ $office->id }}" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-gray-100 text-gray-700 border border-gray-200">
                            {{ $officeCount }}
                        </span>
                    </button>
                    @endforeach
                </div>

                <!-- Table Body -->
                <div class="overflow-x-auto min-w-0 max-h-96 overflow-y-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="sticky top-0 bg-gray-50 z-10">
                            <tr class="border-b border-gray-100 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3 px-4 text-center w-12">No</th>
                                <th class="py-3 px-4 w-40">Tanggal & Hari</th>
                                <th class="py-3 px-4 w-36">Lokasi Kantor</th>
                                <th class="py-3 px-4 w-44">Shift Jam Piket</th>
                                <th class="py-3 px-4">Daftar Petugas Piket (Disatukan)</th>
                            </tr>
                        </thead>
                        <tbody id="piketRosterTableBody" class="divide-y divide-gray-100 font-medium text-gray-700">
                            @php
                            $groupedPiket = $piketRoster->groupBy(function ($item) {
                            return $item->date . '_' . $item->office_id . '_' . ($item->shift_id ?? '0');
                            });
                            $rowCounter = 0;
                            @endphp
                            @forelse($groupedPiket as $groupKey => $group)
                            @php
                            $rowCounter++;
                            $firstItem = $group->first();
                            $cDate = \Carbon\Carbon::parse($firstItem->date);
                            $dayName = $cDate->locale('id')->translatedFormat('l');
                            $formattedDate = $cDate->translatedFormat('d M Y');
                            $officeName = $firstItem->office?->name ?? 'Kantor';
                            $shiftName = $firstItem->shift?->name ?? 'Shift';
                            $shiftHours = ($firstItem->shift?->start_time && $firstItem->shift?->end_time)
                            ? substr($firstItem->shift->start_time, 0, 5) . ' - ' . substr($firstItem->shift->end_time, 0, 5)
                            : '';

                            $groupSearchTokens = $group->map(function ($it) {
                                $u = $it->intern?->user;
                                $name = $u?->profile?->full_name ?: ($u?->username ?: ($u?->name ?: 'Pemagang #' . $it->intern_id));
                                $div = $it->intern?->division?->name ?? '';
                                $school = $it->intern?->school?->name ?? '';
                                $nim = $it->intern?->nim ?? '';
                                return "{$name} {$div} {$school} {$nim}";
                            })->join(' ');

                            $rowMeta = strtolower("{$dayName} {$formattedDate} {$officeName} {$shiftName} " . $cDate->format('Y-m-d'));
                            $allRowSearch = strtolower("{$rowMeta} {$groupSearchTokens}");
                            @endphp
                            <tr class="hover:bg-amber-50/30 transition-colors piket-roster-row"
                                data-office-id="{{ $firstItem->office_id }}"
                                data-shift-id="{{ $firstItem->shift_id }}"
                                data-row-meta="{{ $rowMeta }}"
                                data-search="{{ $allRowSearch }}"
                                data-intern-count="{{ $group->count() }}">
                                <td class="py-3.5 px-4 text-center text-gray-400 font-bold align-top piket-row-index">{{ $rowCounter }}</td>
                                <td class="py-3.5 px-4 whitespace-nowrap align-top">
                                    <div class="font-bold text-gray-900">{{ $dayName }}</div>
                                    <div class="text-[11px] text-gray-500 font-mono">{{ $formattedDate }}</div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap align-top">
                                    <div class="font-semibold text-gray-800 flex items-center gap-1.5">
                                        <i class="fa-solid fa-building text-gray-400 text-xs"></i>
                                        <span>{{ $officeName }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap align-top">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-200 flex items-center gap-1 w-max">
                                        <i class="fa-solid fa-broom text-[10px] text-amber-700"></i>
                                        <span>{{ $shiftName }}</span>
                                    </span>
                                    @if($shiftHours)
                                    <div class="text-[10px] text-gray-500 font-mono mt-0.5 pl-1">{{ $shiftHours }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 align-top">
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <span class="piket-group-counter px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-1 transition">
                                            <i class="fa-solid fa-users text-[9px] text-amber-600 piket-counter-icon"></i>
                                            <span class="piket-counter-text">{{ $group->count() }} Petugas Piket</span>
                                        </span>
                                        <button type="button" class="piket-colleague-toggle hidden text-[11px] text-amber-700 hover:text-amber-800 font-semibold underline underline-offset-2 transition cursor-pointer" onclick="toggleRowColleagues(this)">
                                            Lihat rekan lain ({{ $group->count() }})
                                        </button>
                                    </div>
                                    <div class="flex flex-wrap gap-2 piket-intern-cards-container">
                                        @foreach($group as $item)
                                        @php
                                        $u = $item->intern?->user;
                                        $internName = $u?->profile?->full_name ?: ($u?->username ?: ($u?->name ?: 'Pemagang #'.$item->intern_id));
                                        $divName = $item->intern?->division?->name ?? '-';
                                        $schoolName = $item->intern?->school?->name ?? '-';
                                        $nim = $item->intern?->nim ?? '';
                                        $initial = strtoupper(mb_substr($internName, 0, 1));
                                        $cardSearch = strtolower("{$internName} {$divName} {$schoolName} {$nim}");
                                        @endphp
                                        <div class="piket-intern-card flex items-center gap-2 px-2.5 py-1.5 bg-gray-50/90 hover:bg-amber-50 border border-gray-200 hover:border-amber-300 rounded-xl transition shadow-2xs group relative"
                                            data-intern-search="{{ $cardSearch }}"
                                            data-intern-name="{{ $internName }}">
                                            <div class="piket-avatar-badge w-6 h-6 rounded-full bg-amber-500/10 text-amber-700 font-bold flex items-center justify-center text-[10px] shrink-0 border border-amber-200/60 transition">
                                                {{ $initial }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="piket-intern-name font-bold text-gray-900 leading-tight group-hover:text-amber-800 transition truncate max-w-[160px]">{{ $internName }}</span>
                                                    <i class="fa-solid fa-circle-check text-emerald-600 text-[10px] piket-match-icon hidden" title="Cocok dengan pencarian"></i>
                                                </div>
                                                <div class="text-[10px] text-gray-500 leading-tight flex items-center gap-1 mt-0.5 truncate">
                                                    <span class="text-blue-600 font-semibold">{{ $divName }}</span>
                                                    <span class="text-gray-300">•</span>
                                                    <span class="text-gray-400 truncate max-w-[120px]">{{ $schoolName }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr id="piket-empty-row">
                                <td colspan="5" class="py-12 text-center text-gray-400">
                                    <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-2 text-xl">
                                        <i class="fa-solid fa-broom"></i>
                                    </div>
                                    <div class="font-semibold text-gray-600 text-sm">Belum Ada Jadwal Piket pada Periode Ini</div>
                                    <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">
                                        Gunakan panel generator di atas untuk membagikan tugas piket otomatis secara merata dari Senin s/d Sabtu.
                                    </p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. Form Ketentuan SOP Piket -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-200 p-6 space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                    <i class="fa-solid fa-file-lines text-amber-600"></i>
                    <h4 class="font-bold text-gray-900 text-sm">Ketentuan SOP Tugas Piket</h4>
                </div>

                <form action="{{ route('admin.pengaturan.holiday.updateInfo') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="active_tab" value="piket">
                    <input type="hidden" name="piket_start_date" value="{{ $piketStartDate }}">
                    <input type="hidden" name="piket_end_date" value="{{ $piketEndDate }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="piket_office_select" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Pilih Kantor Target</label>
                            <select id="piket_office_select" name="office_id" onchange="handleOfficeSelectChange('piket', this.value)" class="w-full px-3.5 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none text-xs sm:text-sm bg-white font-medium">
                                <option value="all" {{ $piketOfficeId === 'all' ? 'selected' : '' }}>Semua Lokasi Kantor (Global)</option>
                                @foreach($offices as $office)
                                <option value="{{ $office->id }}" {{ (string)$piketOfficeId === (string)$office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="input_piket_max_per_week" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Batasan Piket Pemagang (Berapa Kali Seminggu)
                            </label>
                            <select id="input_piket_max_per_week" name="piket_max_per_week"
                                class="w-full px-3.5 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none text-xs sm:text-sm bg-white font-semibold">
                                @for($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}" {{ (int)($piketOffice?->piket_max_per_week ?? ($globalPiketMaxPerWeek ?? 1)) === $i ? 'selected' : '' }}>
                                    {{ $i }}x dalam seminggu {{ $i === 1 ? '(Default sistem)' : '' }}
                                </option>
                                @endfor
                            </select>
                            <p class="text-[11px] text-gray-500 mt-1">Mengatur batas berapa kali seorang pemagang terdaftar piket dalam 1 minggu kalender saat generate otomatis.</p>
                        </div>
                    </div>

                    <div>
                        <label for="input_piket_desc" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Poin Tugas & Ketentuan Piket Kebersihan
                        </label>
                        <textarea id="input_piket_desc" name="piket_description" rows="5"
                            placeholder="Contoh:&#10;1. Datang 15 menit lebih awal untuk persiapan ruang kerja.&#10;2. Menyapu dan merapikan ruang kerja bersama.&#10;3. Membuang sampah ke tempat pembuangan luar di akhir jam kerja.&#10;4. Memastikan AC dan lampu telah dimatikan sebelum pulang..."
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none text-xs sm:text-sm leading-relaxed">{{ $piketOffice?->piket_description ?? '' }}</textarea>
                    </div>

                    <div class="flex items-center">
                        <input type="hidden" name="apply_all" value="0">
                        <input type="checkbox" id="piket_apply_all" name="apply_all" value="1" onchange="handleApplyAllToggle('piket', this.checked)" class="h-4 w-4 text-amber-600 rounded border-gray-300 cursor-pointer" {{ $isPiketApplyAll ? 'checked' : '' }}>
                        <label for="piket_apply_all" class="ml-2 text-xs font-medium text-gray-700 cursor-pointer">Terapkan ketentuan piket ini ke semua kantor sekaligus</label>
                    </div>

                    <div class="pt-2 border-t flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold text-xs sm:text-sm shadow-xs transition flex items-center gap-2">
                            <i class="fas fa-save"></i> Simpan SOP & Ketentuan
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
            $('#input_piket_desc').val(office.piket_description || '');
            $('#input_piket_max_per_week').val(office.piket_max_per_week || 1);
        }
    }

    function loadOfficeRules(officeId) {
        handleOfficeSelectChange('rules', officeId);
    }

    function loadOfficePiket(officeId) {
        handleOfficeSelectChange('piket', officeId);
    }

    let currentPiketOfficeFilter = 'all';

    function filterPiketListByOffice(officeId) {
        currentPiketOfficeFilter = String(officeId);

        // Update styling tombol tab kantor di dalam card roster
        $('.tab-piket-office-btn')
            .removeClass('bg-amber-600 text-white shadow-xs')
            .addClass('bg-white border border-gray-200 text-gray-700 hover:bg-gray-50');
        $('.tab-piket-office-btn span[id^="badge-piket-office-"]')
            .removeClass('bg-amber-700 text-white')
            .addClass('bg-gray-100 text-gray-700 border border-gray-200');

        const activeBtn = $(`#tab-piket-office-${officeId}`);
        activeBtn.removeClass('bg-white border border-gray-200 text-gray-700 hover:bg-gray-50')
            .addClass('bg-amber-600 text-white shadow-xs');
        $(`#badge-piket-office-${officeId}`)
            .removeClass('bg-gray-100 text-gray-700 border border-gray-200')
            .addClass('bg-amber-700 text-white');

        applyPiketRosterFilter();
    }

    function clearPiketSearch() {
        $('#piketSearchInput').val('').focus();
        $('#clearPiketSearch').addClass('hidden');
        applyPiketRosterFilter();
    }

    function toggleRowColleagues(btn) {
        const $btn = $(btn);
        const $row = $btn.closest('.piket-roster-row');
        const isShowingAll = $btn.data('showing-all') === true;

        if (isShowingAll) {
            // Sembunyikan kembali rekan yang tidak cocok
            $row.find('.piket-intern-card').each(function() {
                if (!$(this).hasClass('piket-card-matched')) {
                    $(this).hide();
                }
            });
            $btn.data('showing-all', false).text(`Lihat rekan lain (${$row.data('intern-count')})`);
        } else {
            // Tampilkan semua rekan dalam baris ini dengan styling transparan/dimmed
            $row.find('.piket-intern-card').each(function() {
                if (!$(this).hasClass('piket-card-matched')) {
                    $(this).show().addClass('opacity-60 hover:opacity-100');
                }
            });
            $btn.data('showing-all', true).text('Hanya yang cocok');
        }
    }

    function applyPiketRosterFilter() {
        const rawQuery = ($('#piketSearchInput').val() || '').trim();
        const queryLower = rawQuery.toLowerCase();
        const selectedShift = String($('#piketShiftFilter').val() || 'all');
        const terms = queryLower ? queryLower.split(/\s+/).filter(Boolean) : [];
        const hasSearch = terms.length > 0;

        // Toggle tombol clear (X)
        if (rawQuery.length > 0) {
            $('#clearPiketSearch').removeClass('hidden');
        } else {
            $('#clearPiketSearch').addClass('hidden');
        }

        let visibleRows = 0;
        let totalInternsVisible = 0;

        $('.piket-roster-row').each(function() {
            const $row = $(this);
            const rowOffice = String($row.data('office-id') || '');
            const rowShift = String($row.data('shift-id') || '');
            const rowMeta = String($row.data('row-meta') || '').toLowerCase();
            const totalInGroup = parseInt($row.data('intern-count') || 0, 10);

            const matchOffice = (currentPiketOfficeFilter === 'all' || rowOffice === currentPiketOfficeFilter);
            const matchShift = (selectedShift === 'all' || rowShift === selectedShift);

            if (!matchOffice || !matchShift) {
                $row.hide();
                return;
            }

            const $cards = $row.find('.piket-intern-card');
            const $colleagueToggle = $row.find('.piket-colleague-toggle');
            $colleagueToggle.data('showing-all', false);

            if (!hasSearch) {
                // Mode Tanpa Pencarian: Tampilkan semua kartu secara default
                $cards.show()
                    .removeClass('ring-2 ring-amber-500 border-amber-400 bg-amber-50/90 shadow-xs piket-card-matched opacity-60')
                    .addClass('border-gray-200 bg-gray-50/90');
                $cards.find('.piket-avatar-badge')
                    .removeClass('bg-amber-600 text-white')
                    .addClass('bg-amber-500/10 text-amber-700');
                $cards.find('.piket-match-icon').addClass('hidden');

                $row.find('.piket-group-counter')
                    .removeClass('bg-emerald-50 text-emerald-800 border-emerald-300')
                    .addClass('bg-amber-50 text-amber-800 border-amber-200');
                $row.find('.piket-counter-icon')
                    .removeClass('fa-user-check text-emerald-600')
                    .addClass('fa-users text-amber-600');
                $row.find('.piket-counter-text').text(`${totalInGroup} Petugas Piket`);
                $colleagueToggle.addClass('hidden');

                $row.show();
                visibleRows++;
                totalInternsVisible += totalInGroup;
                $row.find('.piket-row-index').text(visibleRows);
                return;
            }

            // Mode Pencarian: Cocokkan per kartu pemagang
            let matchedCardsCount = 0;

            $cards.each(function() {
                const $card = $(this);
                const cardSearch = String($card.data('intern-search') || '').toLowerCase();
                const combinedSearch = rowMeta + ' ' + cardSearch;

                const isMatch = terms.every(term => combinedSearch.includes(term));

                if (isMatch) {
                    matchedCardsCount++;
                    $card.show()
                        .addClass('ring-2 ring-amber-500 border-amber-400 bg-amber-50/90 shadow-xs piket-card-matched')
                        .removeClass('border-gray-200 bg-gray-50/90 opacity-60');
                    $card.find('.piket-avatar-badge')
                        .removeClass('bg-amber-500/10 text-amber-700')
                        .addClass('bg-amber-600 text-white');
                    $card.find('.piket-match-icon').removeClass('hidden');
                } else {
                    $card.hide()
                        .removeClass('ring-2 ring-amber-500 border-amber-400 bg-amber-50/90 shadow-xs piket-card-matched opacity-60')
                        .addClass('border-gray-200 bg-gray-50/90');
                    $card.find('.piket-avatar-badge')
                        .removeClass('bg-amber-600 text-white')
                        .addClass('bg-amber-500/10 text-amber-700');
                    $card.find('.piket-match-icon').addClass('hidden');
                }
            });

            if (matchedCardsCount > 0) {
                $row.show();
                visibleRows++;
                totalInternsVisible += matchedCardsCount;
                $row.find('.piket-row-index').text(visibleRows);

                if (matchedCardsCount === totalInGroup) {
                    $row.find('.piket-group-counter')
                        .removeClass('bg-emerald-50 text-emerald-800 border-emerald-300')
                        .addClass('bg-amber-50 text-amber-800 border-amber-200');
                    $row.find('.piket-counter-icon')
                        .removeClass('fa-user-check text-emerald-600')
                        .addClass('fa-users text-amber-600');
                    $row.find('.piket-counter-text').text(`${totalInGroup} Petugas Piket`);
                    $colleagueToggle.addClass('hidden');
                } else {
                    $row.find('.piket-group-counter')
                        .removeClass('bg-amber-50 text-amber-800 border-amber-200')
                        .addClass('bg-emerald-50 text-emerald-800 border-emerald-300');
                    $row.find('.piket-counter-icon')
                        .removeClass('fa-users text-amber-600')
                        .addClass('fa-user-check text-emerald-600');
                    $row.find('.piket-counter-text').text(`${matchedCardsCount} dari ${totalInGroup} Petugas (Cocok)`);
                    $colleagueToggle.removeClass('hidden').text(`Lihat rekan lain (${totalInGroup})`);
                }
            } else {
                $row.hide();
            }
        });

        // Update badge total di header tabel
        if (hasSearch) {
            $('#piket-visible-summary').text(`${totalInternsVisible} Petugas Cocok (${visibleRows} Sesi Shift)`);
        } else {
            $('#piket-visible-summary').text(`${totalInternsVisible} Penugasan (${visibleRows} Sesi Shift)`);
        }

        if (visibleRows === 0) {
            if ($('#piket-no-match-row').length === 0) {
                const safeQuery = $('<div>').text(rawQuery).html();
                const message = queryLower ?
                    `Tidak ada petugas piket atau jadwal yang cocok dengan kata kunci "<strong>${safeQuery}</strong>".` :
                    'Tidak ada jadwal piket untuk kombinasi kantor dan shift yang dipilih.';
                $('#piketRosterTableBody').append(`
                    <tr id="piket-no-match-row">
                        <td colspan="5" class="py-12 text-center text-gray-400 font-medium">
                            <i class="fas fa-broom text-2xl mb-2 text-gray-300 block"></i>
                            <div>${message}</div>
                            ${queryLower ? `
                                <button type="button" onclick="clearPiketSearch()" 
                                    class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 transition cursor-pointer">
                                    <i class="fa-solid fa-rotate-left text-[10px]"></i>
                                    <span>Reset Pencarian</span>
                                </button>
                            ` : ''}
                        </td>
                    </tr>
                `);
            }
        } else {
            $('#piket-no-match-row').remove();
        }
    }

    function togglePiketPresetDropdown(event) {
        if (event) event.stopPropagation();
        $('#piket-preset-dropdown-menu').toggleClass('hidden');
        $('#piket-preset-chevron').toggleClass('rotate-180');
    }

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#piket-preset-dropdown-container').length) {
            $('#piket-preset-dropdown-menu').addClass('hidden');
            $('#piket-preset-chevron').removeClass('rotate-180');
        }
    });

    function setPiketPeriodPreset(preset) {
        $('#piket-preset-dropdown-menu').addClass('hidden');
        $('#piket-preset-chevron').removeClass('rotate-180');
        const today = new Date();
        let start = new Date(today);
        let end = new Date(today);

        if (preset === 'this_week') {
            const day = today.getDay(); // 0: Sunday, 1: Monday...
            const diffToMonday = day === 0 ? -6 : 1 - day;
            start.setDate(today.getDate() + diffToMonday);
            end = new Date(start);
            end.setDate(start.getDate() + 6);
        } else if (preset === 'next_week') {
            const day = today.getDay();
            const diffToMonday = (day === 0 ? -6 : 1 - day) + 7;
            start.setDate(today.getDate() + diffToMonday);
            end = new Date(start);
            end.setDate(start.getDate() + 6);
        } else if (preset === 'this_month') {
            start = new Date(today.getFullYear(), today.getMonth(), 1);
            end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        }

        const formatDate = (d) => {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        const startStr = formatDate(start);
        const endStr = formatDate(end);

        $('#piket_gen_start').val(startStr);
        $('#piket_gen_end').val(endStr);

        window.location.href = `{{ route('admin.pengaturan.holiday') }}?tab=piket&piket_start_date=${startStr}&piket_end_date=${endStr}`;
    }

    function reloadPiketView() {
        const startStr = $('#piket_gen_start').val();
        const endStr = $('#piket_gen_end').val();

        if (!startStr || !endStr) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanggal Belum Lengkap',
                    text: 'Silakan pilih tanggal mulai dan selesai terlebih dahulu.'
                });
            } else {
                alert('Silakan pilih tanggal mulai dan selesai');
            }
            return;
        }

        window.location.href = `{{ route('admin.pengaturan.holiday') }}?tab=piket&piket_start_date=${startStr}&piket_end_date=${endStr}`;
    }

    function executePiketGeneration() {
        const startDate = $('#piket_gen_start').val();
        const endDate = $('#piket_gen_end').val();
        const officeId = $('#piket_gen_office').val() || 'all';
        const maxPerWeek = $('#piket_gen_max_per_week').val() || 1;

        if (!startDate || !endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Tanggal Belum Lengkap',
                text: 'Silakan tentukan tanggal mulai dan tanggal selesai terlebih dahulu.',
                confirmButtonColor: '#d97706'
            });
            return;
        }

        if (startDate > endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Rentang Tanggal Tidak Valid',
                text: 'Tanggal mulai tidak boleh melebihi tanggal selesai.',
                confirmButtonColor: '#d97706'
            });
            return;
        }

        Swal.fire({
            title: 'Generate Jadwal Piket Otomatis?',
            text: `Sistem akan mendistribusikan tugas piket maksimal ${maxPerWeek}x seminggu untuk setiap pemagang WFO secara merata (Senin s/d Sabtu) untuk periode ${startDate} sampai ${endDate}.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Ya, Generate Sekarang',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Penjadwalan Piket...',
                    text: `Membaca jadwal kerja WFO pemagang dan meratakan penugasan piket (maks ${maxPerWeek}x/minggu)...`,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('admin.piket.generate') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        start_date: startDate,
                        end_date: endDate,
                        office_id: officeId,
                        max_piket_per_week: maxPerWeek
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Digenerate!',
                                text: response.message,
                                confirmButtonColor: '#059669'
                            }).then(() => {
                                window.location.href = `{{ route('admin.pengaturan.holiday') }}?tab=piket&piket_start_date=${startDate}&piket_end_date=${endDate}`;
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Generate',
                                text: response.message || 'Terjadi kesalahan sistem saat menjadwalkan piket.',
                                confirmButtonColor: '#e11d48'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: xhr.responseJSON?.message || 'Gagal menghubungi server.',
                            confirmButtonColor: '#e11d48'
                        });
                    }
                });
            }
        });
    }

    function confirmResetPiket() {
        const startDate = $('#piket_gen_start').val();
        const endDate = $('#piket_gen_end').val();
        const officeId = $('#piket_gen_office').val() || 'all';

        if (!startDate || !endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Tanggal Belum Dipilih',
                text: 'Silakan tentukan periode tanggal yang ingin direset.',
                confirmButtonColor: '#d97706'
            });
            return;
        }

        Swal.fire({
            title: 'Reset Jadwal Piket?',
            text: `Semua penugasan piket pada periode ${startDate} sampai ${endDate} akan dihapus. Jadwal jam kerja pemagang tetap aman dan tidak berubah.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-trash-can mr-1"></i> Ya, Reset Piket',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Mereset Data Piket...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('admin.piket.reset') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        start_date: startDate,
                        end_date: endDate,
                        office_id: officeId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Direset!',
                                text: response.message,
                                confirmButtonColor: '#059669'
                            }).then(() => {
                                window.location.href = `{{ route('admin.pengaturan.holiday') }}?tab=piket&piket_start_date=${startDate}&piket_end_date=${endDate}`;
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message || 'Gagal menghapus jadwal piket.',
                                confirmButtonColor: '#e11d48'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: xhr.responseJSON?.message || 'Gagal menghubungi server.',
                            confirmButtonColor: '#e11d48'
                        });
                    }
                });
            }
        });
    }

    $(document).ready(function() {
        // Assuming `HolidayList` is passed as a JavaScript array from backend for demonstration
        const HolidayList = JSON.parse(document.getElementById('holiday-data')?.textContent || '[]');
        const itemsPerPage = 10;
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

        // Sinkronisasi Hari Libur Nasional dari API
        $('#syncHolidayButton').on('click', function() {
            if (typeof Swal === 'undefined') {
                if (confirm('Sinkronkan hari libur nasional resmi dari API?')) {
                    const form = $('<form action="{{ route("holidays.sync") }}" method="POST">@csrf</form>');
                    $('body').append(form);
                    form.submit();
                }
                return;
            }

            Swal.fire({
                title: 'Sinkronkan Hari Libur?',
                text: 'Sistem akan menarik daftar hari libur nasional & cuti bersama resmi Indonesia dari API publik ke database.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-cloud-arrow-down mr-1"></i> Ya, Sinkronkan Sekarang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menyinkronkan...',
                        text: 'Sedang menghubungi API dan memperbarui data hari libur...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('holidays.sync') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Disinkronkan!',
                                    text: response.message,
                                    confirmButtonColor: '#059669'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Sinkronisasi',
                                    text: response.message || 'Terjadi kesalahan saat sinkronisasi.',
                                    confirmButtonColor: '#e11d48'
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Gagal menghubungi server.',
                                confirmButtonColor: '#e11d48'
                            });
                        }
                    });
                }
            });
        });

        // Live filter roster piket berdasarkan nama, divisi, asal sekolah, dan kantor aktif
        $('#piketSearchInput').on('input', function() {
            applyPiketRosterFilter();
        }).on('keydown', function(e) {
            if (e.key === 'Escape') {
                clearPiketSearch();
            }
        });

        // Sinkronkan batas piket generator saat kantor target diubah
        $('#piket_gen_office').on('change', function() {
            const offId = $(this).val();
            let targetLimit = 1;
            if (offId && offId !== 'all') {
                const found = allOfficesData.find(o => String(o.id) === String(offId));
                if (found && found.piket_max_per_week) {
                    targetLimit = found.piket_max_per_week;
                }
            } else if (allOfficesData[0]?.piket_max_per_week) {
                targetLimit = allOfficesData[0].piket_max_per_week;
            }
            $('#piket_gen_max_per_week').val(targetLimit);
            $('#piket_limit_info_badge').text(targetLimit + 'x seminggu');
        });

        $('#piket_gen_max_per_week').on('change', function() {
            $('#piket_limit_info_badge').text($(this).val() + 'x seminggu');
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
    });
</script>
@endsection