@extends('layouts.main')

@section('title', 'Pengaturan Info & Libur')

@section('contents')
@include('layouts.sidebar')

@include('layouts.sidebar-pengaturan')

@include('layouts.navbar')

<!-- Main Content -->
<main class="ml-[32rem] mt-24 p-6">

    <!-- Header -->
    <h1 class="text-2xl font-bold mb-1">Manage Info & Libur</h1>
    <p class="mb-5 text-gray-600 text-sm">Pengaturan kalender hari libur, tautan SOP magang, dan tata tertib kantor</p>

    @if (session('success'))
    <div id="success-message"
        class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative transition-opacity duration-500"
        role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="removeMessage()">
            <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20">
                <title>Close</title>
                <path
                    d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
            </svg>
        </span>
    </div>
    @endif

    <!-- Tab Navigation -->
    <div class="grid grid-cols-4 border-b border-gray-200 bg-white rounded-t-xl px-4 pt-2 mb-6 shadow-sm">

        <button type="button" onclick="switchAdminTab('holiday')" id="btn-tab-holiday"
            class="admin-tab-btn w-full py-3 px-5 text-sm font-semibold border-b-2 border-blue-600 text-blue-600 focus:outline-none flex items-center justify-center gap-2 transition-all">
            <i class="fa-regular fa-calendar-days text-base"></i>
            <span>Hari Libur</span>
            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full font-medium">
                {{ count($holidaylist) }}
            </span>
        </button>

        <button type="button" onclick="switchAdminTab('sop')" id="btn-tab-sop"
            class="admin-tab-btn w-full py-3 px-5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none flex items-center justify-center gap-2 transition-all">
            <i class="fa-regular fa-file-lines text-base"></i>
            <span>SOP Magang</span>
        </button>

        <button type="button" onclick="switchAdminTab('rules')" id="btn-tab-rules"
            class="admin-tab-btn w-full py-3 px-5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none flex items-center justify-center gap-2 transition-all">
            <i class="fas fa-building text-base"></i>
            <span>Peraturan Kantor</span>
        </button>

        <button type="button" onclick="switchAdminTab('piket')" id="btn-tab-piket"
            class="admin-tab-btn w-full py-3 px-5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none flex items-center justify-center gap-2 transition-all">
            <i class="fa-solid fa-broom text-base"></i>
            <span>Jadwal Piket</span>
        </button>

    </div>

    <!-- TAB 1: HARI LIBUR -->
    <div id="content-tab-holiday" class="admin-tab-content space-y-4">
        <div class="flex items-center mb-6">
            <!-- Add Holiday Button -->
            <button id="addHolidayButton"
                class="flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg shadow-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                <i class="fas fa-plus mr-2"></i> Tambahkan Hari Libur
            </button>

            <!-- Search Input -->
            <div class="flex items-center ml-auto">
                <input type="text" id="searchInput" placeholder="Cari Hari Libur"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500">
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-gray-200 text-gray-700">
                        <th class="py-3 px-6 text-left">No</th>
                        <th class="py-3 px-6 text-left">Tanggal</th>
                        <th class="py-3 px-6 text-left">Nama Hari Libur</th>
                        <th class="py-3 px-6 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody id="holidayTableBody">
                    <!-- Rows will be dynamically inserted here -->
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="mt-4 flex justify-center items-center space-x-2 border rounded-md p-2">
            <button id="prev-page"
                class="cursor-pointer bg-white text-blue-600 px-4 py-2 rounded-md border hover:bg-gray-100" disabled>
                Previous
            </button>

            <!-- Page numbers will be dynamically added here -->
            <div id="page-numbers" class="flex space-x-2"></div>

            <button id="next-page" class="bg-white text-blue-600 px-4 py-2 rounded-md border hover:bg-gray-100">
                Next
            </button>
        </div>
    </div>

    <!-- TAB 2: SOP MAGANG -->
    <div id="content-tab-sop" class="admin-tab-content hidden space-y-6">
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 flex items-start space-x-4">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600 text-xl flex-shrink-0">
                <i class="fas fa-file-contract"></i>
            </div>
            <div>
                <h3 class="font-bold text-blue-900 text-base">Standar Operasional Prosedur (SOP) Magang</h3>
                <p class="text-xs text-blue-700 mt-1 leading-relaxed">
                    Tautan ini merupakan URL dokumen resmi (Google Docs / PDF) yang dapat diakses langsung oleh pemagang saat menekan tombol <strong>"Buka Dokumen SOP Lengkap"</strong> pada modal <em>Info & Libur</em> di dashboard mereka.
                </p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <form action="{{ route('admin.pengaturan.holiday.updateInfo') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="active_tab" value="sop">

                <div>
                    <label for="sop_office_id" class="block text-sm font-semibold text-gray-700 mb-2">Pilih Kantor / Target Penerapan</label>
                    <select id="sop_office_id" name="office_id" class="w-full md:w-1/2 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm">
                        <option value="all">Semua Lokasi Kantor (Global)</option>
                        @foreach($offices as $office)
                        <option value="{{ $office->id }}">{{ $office->name }} ({{ $office->address }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="input_sop_url" class="block text-sm font-semibold text-gray-700 mb-2">
                        Link Dokumen SOP Magang (URL)
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="url" id="input_sop_url" name="sop_url"
                            value="{{ $offices->first()->sop_url ?? 'https://docs.google.com/document/d/sop-magang-djuragan' }}"
                            placeholder="https://docs.google.com/document/d/..."
                            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm">
                        <a id="btn-preview-sop"
                            href="{{ $offices->first()->sop_url ?? '#' }}"
                            target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-xs transition border border-gray-300">
                            <i class="fas fa-arrow-up-right-from-square mr-1.5"></i> Tes Link
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-1.5">Pastikan akses share dokumen Google Docs disetel ke <em>"Anyone with the link can view"</em>.</p>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="sop_apply_all" name="apply_all" value="1" class="h-4 w-4 text-blue-600 rounded border-gray-300" checked>
                    <label for="sop_apply_all" class="ml-2 text-xs font-medium text-gray-700">Terapkan tautan SOP ini ke semua lokasi kantor</label>
                </div>

                <div class="pt-3 border-t flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-sm shadow-sm transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Pengaturan SOP
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 3: PERATURAN KANTOR -->
    <div id="content-tab-rules" class="admin-tab-content hidden space-y-6">
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5 flex items-start space-x-4">
            <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 text-xl flex-shrink-0">
                <i class="fas fa-building"></i>
            </div>
            <div>
                <h3 class="font-bold text-indigo-900 text-base">Tata Tertib & Dokumen Peraturan Kantor</h3>
                <p class="text-xs text-indigo-700 mt-1 leading-relaxed">
                    Atur rincian poin tata tertib dan tautan dokumen peraturan penempatan. Pemagang akan melihat rincian aturan ini sesuai kantor penempatan masing-masing.
                </p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <form action="{{ route('admin.pengaturan.holiday.updateInfo') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="active_tab" value="rules">

                <div>
                    <label for="rules_office_select" class="block text-sm font-semibold text-gray-700 mb-2">Pilih Lokasi Kantor</label>
                    <select id="rules_office_select" name="office_id" onchange="loadOfficeRules(this.value)" class="w-full md:w-1/2 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm">
                        @foreach($offices as $office)
                        <option value="{{ $office->id }}">{{ $office->name }} ({{ $office->address }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="input_rules_url" class="block text-sm font-semibold text-gray-700 mb-2">
                        Link Dokumen Peraturan Kantor (URL)
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="url" id="input_rules_url" name="rules_url"
                            value="{{ $offices->first()->rules_url ?? '' }}"
                            placeholder="https://docs.google.com/document/d/..."
                            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm">
                        <a id="btn-preview-rules"
                            href="{{ $offices->first()->rules_url ?? '#' }}"
                            target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-xs transition border border-gray-300">
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
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm leading-relaxed">{{ $offices->first()->rules_description ?? '' }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Gunakan baris baru (Enter) atau angka urut untuk setiap poin aturan.</p>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="rules_apply_all" name="apply_all" value="1" class="h-4 w-4 text-indigo-600 rounded border-gray-300">
                    <label for="rules_apply_all" class="ml-2 text-xs font-medium text-gray-700">Terapkan aturan & link ini ke semua kantor sekaligus</label>
                </div>

                <div class="pt-3 border-t flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Peraturan Kantor
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 4: JADWAL PIKET -->
    <div id="content-tab-piket" class="admin-tab-content hidden space-y-6">
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-start space-x-4">
            <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600 text-xl flex-shrink-0">
                <i class="fa-solid fa-broom"></i>
            </div>
            <div>
                <h3 class="font-bold text-amber-900 text-base">Jadwal & Tugas Piket Kantor</h3>
                <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                    Atur tautan spreadsheet/dokumen jadwal piket kebersihan dan rincian tugas piket harian. Pemagang dapat langsung membuka tautan jadwal piket melalui tombol pada modal <em>Info & Libur</em> di dashboard mereka.
                </p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <form action="{{ route('admin.pengaturan.holiday.updateInfo') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="active_tab" value="piket">

                <div>
                    <label for="piket_office_select" class="block text-sm font-semibold text-gray-700 mb-2">Pilih Kantor / Target Penerapan</label>
                    <select id="piket_office_select" name="office_id" onchange="loadOfficePiket(this.value)" class="w-full md:w-1/2 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:outline-none text-sm">
                        <option value="all">Semua Lokasi Kantor (Global)</option>
                        @foreach($offices as $office)
                        <option value="{{ $office->id }}">{{ $office->name }} ({{ $office->address }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="input_piket_url" class="block text-sm font-semibold text-gray-700 mb-2">
                        Link Dokumen / Spreadsheet Jadwal Piket (URL)
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="url" id="input_piket_url" name="piket_url"
                            value="{{ $offices->first()->piket_url ?? '' }}"
                            placeholder="https://docs.google.com/spreadsheets/d/..."
                            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:outline-none text-sm">
                        <a id="btn-preview-piket"
                            href="{{ $offices->first()->piket_url ?? '#' }}"
                            target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-xs transition border border-gray-300">
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
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:outline-none text-sm leading-relaxed">{{ $offices->first()->piket_description ?? '' }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Poin-poin tugas yang wajib dijalankan oleh petugas piket hari tersebut.</p>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="piket_apply_all" name="apply_all" value="1" class="h-4 w-4 text-amber-600 rounded border-gray-300" checked>
                    <label for="piket_apply_all" class="ml-2 text-xs font-medium text-gray-700">Terapkan tautan & ketentuan piket ini ke semua kantor sekaligus</label>
                </div>

                <div class="pt-3 border-t flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-semibold text-sm shadow-sm transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Pengaturan Piket
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<!-- Modal Tambah Hari Libur -->
<div id="addHolidayModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
        <h2 class="text-xl font-bold mb-4">Tambahkan Hari Libur</h2>
        <form action="{{ route('holidays.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="holidayDate" class="block text-gray-700">Tanggal<span class="text-red-500">*</span></label>
                <input type="date" id="holidayDate" name="date"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                    required>
            </div>
            <div class="mb-4">
                <label for="holidayName" class="block text-gray-700">Nama Hari Libur<span
                        class="text-red-500">*</span></label>
                <input type="text" id="holidayName" name="name"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                    required>
            </div>
            <div class="flex justify-end">
                <button type="button" id="closeAddHolidayModal"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg mr-2">Batal</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Hari Libur -->
<div id="editHolidayModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
        <h2 class="text-xl font-bold mb-4">Update Hari Libur</h2>
        <form id="editHolidayForm" action="" method="POST">
            @csrf
            {{-- @method('PUT') --}}
            <input type="hidden" id="editHolidayId" name="holidayId">
            <div class="mb-4">
                <label for="editHolidayDate" class="block text-gray-700">Tanggal<span
                        class="text-red-500">*</span></label>
                <input type="date" id="editHolidayDate" name="date"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                    required>
            </div>
            <div class="mb-4">
                <label for="editHolidayName" class="block text-gray-700">Nama Hari Libur<span
                        class="text-red-500">*</span></label>
                <input type="text" id="editHolidayName" name="name"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                    required>
            </div>
            <div class="flex justify-end">
                <button type="button" id="closeEditHolidayModal"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg mr-2">Batal</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteHolidayModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
        <h2 class="text-xl font-bold mb-4">Hapus Hari Libur</h2>
        <p>Apakah Anda yakin ingin menghapus hari libur ini?</p>
        <form id="deleteHolidayForm" method="POST">
            @csrf
            @method('DELETE')
            <input type="hidden" id="deleteHolidayId" name="holidayId">
            <div class="flex justify-end mt-4">
                <button type="button" id="closeDeleteHolidayModal"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg mr-2">Batal</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Hapus</button>
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

        $('.admin-tab-btn').removeClass('border-blue-600 text-blue-600 font-semibold')
            .addClass('border-transparent text-gray-500 font-medium');

        $(`#btn-tab-${tabName}`).removeClass('border-transparent text-gray-500 font-medium')
            .addClass('border-blue-600 text-blue-600 font-semibold');
    }

    function loadOfficeRules(officeId) {
        const found = allOfficesData.find(o => o.id == officeId);
        if (found) {
            $('#input_rules_url').val(found.rules_url || '');
            $('#input_rules_desc').val(found.rules_description || '');
            $('#btn-preview-rules').attr('href', found.rules_url || '#');
        }
    }

    function loadOfficePiket(officeId) {
        if (officeId === 'all') {
            const first = allOfficesData[0];
            $('#input_piket_url').val(first?.piket_url || '');
            $('#input_piket_desc').val(first?.piket_description || '');
            $('#btn-preview-piket').attr('href', first?.piket_url || '#');
            return;
        }
        const found = allOfficesData.find(o => o.id == officeId);
        if (found) {
            $('#input_piket_url').val(found.piket_url || '');
            $('#input_piket_desc').val(found.piket_description || '');
            $('#btn-preview-piket').attr('href', found.piket_url || '#');
        }
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
            paginatedData.forEach((holiday, index) => {

                $('#holidayTableBody').append(`
                        <tr class="border-b border-gray-200">
                            <td class="py-4 px-6">${start + index + 1}</td>
                            <td class="py-4 px-6">${holiday.date}</td>
                            <td class="py-4 px-6">${holiday.name}</td>
                            <td class="py-4 px-6">
                                <button class="editHolidayModal px-4 py-2 bg-blue-500 text-white rounded-lg shadow-md hover:bg-blue-600" data-id="${holiday.id}" data-name="${holiday.name}" data-date="${holiday.date}">Edit</button>
                                <button class="deleteHoliday px-4 py-2 bg-red-500 text-white rounded-lg shadow-md hover:bg-red-600" data-id="${holiday.id}">Hapus</button>
                            </td>
                        </tr>
                    `);
            });
        }

        function updatePagination() {
            $('#page-numbers').empty();

            for (let i = 1; i <= totalPages(); i++) {
                const isActive = i === currentPage ? 'bg-blue-500 text-white' : 'bg-white text-blue-600';
                const pageButton = $(`
                        <button class="page-number ${isActive} px-4 py-2 rounded-md border hover:bg-gray-100" data-page="${i}">${i}</button>
                    `);

                pageButton.on('click', function() {
                    currentPage = i;
                    displayPage(currentPage);
                    updatePagination();
                });

                $('#page-numbers').append(pageButton);
            }

            $('#prev-page').prop('disabled', currentPage === 1);
            $('#next-page').prop('disabled', currentPage === totalPages());
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

            console.log('Holiday Date:', holidayDate);

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

        $('#sop_office_id').on('change', function() {
            const officeId = $(this).val();
            if (officeId !== 'all') {
                const found = allOfficesData.find(o => o.id == officeId);
                if (found && found.sop_url) {
                    $('#input_sop_url').val(found.sop_url);
                    $('#btn-preview-sop').attr('href', found.sop_url);
                }
            }
        });

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