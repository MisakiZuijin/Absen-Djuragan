@extends('layouts.main')

@section('title', 'Pengaturan Divisi')

@section('contents')
    @include('layouts.sidebar-pengaturan')

    <!-- Main Content -->
    <main class="ml-0 lg:ml-[32rem] mt-2 lg:mt-20 p-3 sm:p-6 min-w-0 max-w-full overflow-x-hidden">
        <div class="w-full max-w-full min-w-0 space-y-6">

        <!-- Header & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2.5">
                    <i class="fa-solid fa-sitemap text-blue-600"></i>
                    Manage Divisi
                </h1>
                <p class="text-sm text-gray-600 mt-1">Kelola master data divisi penempatan anak magang</p>
            </div>
            <button id="addDivisiButton"
                class="px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm hover:shadow self-start sm:self-auto shrink-0">
                <i class="fas fa-plus"></i>
                <span>Tambahkan Divisi</span>
            </button>
        </div>

        @if (session('status'))
            <div class="mb-5 bg-blue-50 border border-blue-400 text-blue-800 px-4 py-3 rounded-xl relative flex items-center gap-2 shadow-xs" role="alert">
                <i class="fa-solid fa-circle-info text-blue-600 text-lg"></i>
                <span class="font-medium text-sm">{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 bg-red-50 border border-red-400 text-red-800 px-4 py-3 rounded-xl shadow-xs" role="alert">
                <div class="font-bold text-sm mb-1 flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-red-600"></i>
                    Terjadi Kesalahan:
                </div>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div id="success-message"
                class="mb-5 bg-emerald-50 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-xl relative flex items-center justify-between shadow-xs transition-opacity duration-300"
                role="alert">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        @endif

        <!-- Filter & Search Toolbar -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            <div class="relative w-full sm:max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" id="searchInput" placeholder="Cari divisi..."
                    class="w-full text-xs pl-9 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            <div class="text-xs text-gray-500 font-medium shrink-0" id="divisiCountInfo"></div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200 font-bold tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 sm:px-6 text-center w-16">No</th>
                            <th class="py-3.5 px-4 sm:px-6 w-20">Icon</th>
                            <th class="py-3.5 px-4 sm:px-6">Nama Divisi</th>
                            <th class="py-3.5 px-4 sm:px-6">Aturan Logbook</th>
                            <th class="py-3.5 px-4 sm:px-6 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="division-table-body" class="divide-y divide-gray-100">
                        <!-- Rows will be dynamically inserted here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-3.5 bg-white rounded-xl border border-gray-200 shadow-xs">
            <div id="pagination-info" class="text-xs text-gray-500 font-medium"></div>
            <div class="flex items-center space-x-1.5">
                <button id="prev-page"
                    class="cursor-pointer bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed shadow-xs transition" disabled>
                    <i class="fas fa-chevron-left mr-1"></i> Prev
                </button>
                <div id="page-numbers" class="flex space-x-1"></div>
                <button id="next-page"
                    class="cursor-pointer bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed shadow-xs transition">
                    Next <i class="fas fa-chevron-right ml-1"></i>
                </button>
            </div>
        </div>

        </div>
    </main>

    <!-- Modal Tambah Divisi -->
    <div id="addDivisiModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-blue-600"></i>
                    Tambahkan Divisi
                </h2>
                <button type="button" id="closeAddModal" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form id="addDivisiForm" action="{{ route('divisions.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="namaDivisi" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Nama Divisi <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="namaDivisi" name="namaDivisi"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Contoh: Digital Marketing" required>
                </div>
                <div class="mb-5">
                    <label for="iconDivisi" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Icon Divisi <span class="text-red-500">*</span>
                    </label>
                    <input type="file" id="iconDivisi" name="iconDivisi"
                        class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                    <p class="text-[11px] text-gray-400 mt-1">Format foto: JPG, JPEG, PNG, WebP (Maks. 2MB)</p>
                </div>

                <!-- Pengaturan Pengisian Logbook -->
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 mb-4 space-y-2">
                    <div class="flex items-center gap-2 pb-1.5 border-b border-slate-200">
                        <i class="fa-solid fa-book-bookmark text-blue-600 text-xs"></i>
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Aturan Pengisian Logbook</span>
                    </div>

                    <div>
                        <label for="max_logbook_per_day" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Batas Pengisian Logbook / Hari <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="max_logbook_per_day" name="max_logbook_per_day" min="1" max="10" value="1"
                            class="w-full px-3.5 py-2 text-xs border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            required>
                        <p class="text-[11px] text-gray-500 mt-1">Berapa kali pemagang di divisi ini dapat mengisi logbook per hari (Default: 1 kali).</p>
                    </div>
                </div>

                <!-- Info Penjadwalan Otomatis (Auto-Scheduling) -->
                <div class="p-3.5 bg-blue-50/70 rounded-xl border border-blue-200/80 mb-4 space-y-2">
                    <div class="flex items-center gap-2 pb-1.5 border-b border-blue-200/60">
                        <i class="fa-solid fa-wand-magic-sparkles text-blue-600 text-xs"></i>
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Aturan Penjadwalan Otomatis</span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Aturan rotasi shift mingguan, shift yang diizinkan, serta pola WFO/WFH untuk divisi ini dikelola secara terpusat di menu <strong>Pusat Otomatisasi Jadwal (Tab 3: Pola Kerja Divisi)</strong> agar tidak terjadi bentrok pengaturan.
                    </p>
                    <a href="{{ route('admin.automation-rules.index') }}#tab-division-rules" target="_blank"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 pt-1">
                        <span>Buka Aturan Divisi di Pusat Otomatisasi (Tab 3)</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    </a>
                </div>
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="$('#addDivisiModal').addClass('hidden')"
                        class="px-4 py-2 text-xs font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-xl shadow-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition">
                        Simpan Divisi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Divisi -->
    <div id="editDivisiModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-amber-600"></i>
                    Update Divisi
                </h2>
                <button type="button" id="closeEditDivisiModal" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form id="editDivisiForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="editDivisiId" name="divisiId">
                <div class="mb-4">
                    <label for="editNamaDivisi" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Nama Divisi <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="editNamaDivisi" name="namaDivisi"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        required>
                </div>
                <div class="mb-5">
                    <label for="editIconDivisi" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Ganti Icon Divisi (Opsional)
                    </label>
                    <input type="file" id="editIconDivisi" name="iconDivisi"
                        class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <p class="text-[11px] text-gray-400 mt-1">Format foto: JPG, JPEG, PNG, WebP (Maks. 2MB)</p>
                </div>

                <!-- Pengaturan Pengisian Logbook -->
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 mb-4 space-y-2">
                    <div class="flex items-center gap-2 pb-1.5 border-b border-slate-200">
                        <i class="fa-solid fa-book-bookmark text-amber-600 text-xs"></i>
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Aturan Pengisian Logbook</span>
                    </div>

                    <div>
                        <label for="edit_max_logbook_per_day" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Batas Pengisian Logbook / Hari <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="edit_max_logbook_per_day" name="max_logbook_per_day" min="1" max="10" value="1"
                            class="w-full px-3.5 py-2 text-xs border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                            required>
                        <p class="text-[11px] text-gray-500 mt-1">Berapa kali pemagang di divisi ini dapat mengisi logbook per hari (Default: 1 kali).</p>
                    </div>
                </div>

                <!-- Info Penjadwalan Otomatis (Auto-Scheduling) -->
                <div class="p-3.5 bg-amber-50/70 rounded-xl border border-amber-200/80 mb-4 space-y-2">
                    <div class="flex items-center gap-2 pb-1.5 border-b border-amber-200/60">
                        <i class="fa-solid fa-wand-magic-sparkles text-amber-600 text-xs"></i>
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Aturan Penjadwalan Otomatis</span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pengaturan shift yang diizinkan, rotasi mingguan, pola hybrid, dan split-shift dikelola secara terpusat di menu <strong>Pusat Otomatisasi Jadwal (Tab 3: Pola Kerja Divisi)</strong> untuk menjamin akurasi algoritma.
                    </p>
                    <a href="{{ route('admin.automation-rules.index') }}#tab-division-rules" target="_blank"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 hover:text-amber-900 pt-1">
                        <span>Atur di Pusat Otomatisasi (Tab 3)</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    </a>
                </div>
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="$('#editDivisiModal').addClass('hidden')"
                        class="px-4 py-2 text-xs font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-xl shadow-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-xs transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus Divisi -->
    <div id="deleteDivisiModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
            <div class="flex items-center gap-3 mb-4 text-red-600">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Hapus Divisi</h2>
                    <p class="text-xs text-gray-500">Konfirmasi tindakan penghapusan</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-5">Apakah Anda yakin ingin menghapus divisi ini?</p>
            <form id="deleteDivisiForm" action="" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" id="deleteDivisiId" name="divisiId">
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" id="closeDeleteDivisiModal"
                        class="px-4 py-2 text-xs font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-xl shadow-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-xs transition">
                        Hapus Divisi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script id="division-data" type="application/json">@json($division)</script>
    <script>
        $(document).ready(function() {
            const itemsPerPage = 8;
            let currentPage = 1;
            let divisions = JSON.parse(document.getElementById('division-data')?.textContent || '[]');

            function renderTable(page, filteredDivisions) {
                const startIndex = (page - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                const totalItems = filteredDivisions.length;

                if (document.getElementById('divisiCountInfo')) {
                    document.getElementById('divisiCountInfo').innerHTML = `Total: <strong class="text-gray-800">${totalItems}</strong> Divisi`;
                }

                $('#division-table-body').empty();

                if (totalItems === 0) {
                    $('#division-table-body').append(`
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-400 text-xs">
                                <i class="fa-solid fa-folder-open text-2xl mb-2 block"></i>
                                Tidak ada data divisi ditemukan.
                            </td>
                        </tr>
                    `);
                    $('#pagination-info').text('Menampilkan 0 data');
                    $('#prev-page').prop('disabled', true);
                    $('#next-page').prop('disabled', true);
                    $('#page-numbers').empty();
                    return;
                }

                const currentPageData = filteredDivisions.slice(startIndex, endIndex);
                currentPageData.forEach((division, index) => {
                    const iconHtml = division.icon
                        ? `<img src="{{ asset('img') }}/${division.icon}" alt="${division.name}" class="w-8 h-8 rounded-lg object-contain bg-gray-50 border border-gray-200 p-0.5">`
                        : `<div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-400 flex items-center justify-center text-xs"><i class="fa-solid fa-sitemap"></i></div>`;

                    const maxLogbook = division.max_logbook_per_day || 1;
                    const logbookRulesHtml = `
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                            <i class="fa-solid fa-repeat text-[10px]"></i> Maks. ${maxLogbook}x / hari
                        </span>
                    `;

                    $('#division-table-body').append(`
                        <tr class="hover:bg-blue-50/30 transition-colors border-b border-gray-100">
                            <td class="py-3.5 px-4 sm:px-6 text-center text-gray-500 font-mono text-xs">${startIndex + index + 1}</td>
                            <td class="py-3.5 px-4 sm:px-6">${iconHtml}</td>
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-gray-800 whitespace-nowrap">
                                <div>${division.name}</div>
                            </td>
                            <td class="py-3.5 px-4 sm:px-6">
                                ${logbookRulesHtml}
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button class="editDivisi px-2.5 py-1.5 bg-amber-500 text-white hover:bg-amber-600 rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1"
                                        data-id="${division.id}" 
                                        data-nama="${division.name}" 
                                        data-icon="${division.icon}"
                                        data-max-logbook="${maxLogbook}"
                                        title="Edit Divisi">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button data-action="delete" class="deleteDivisi px-2.5 py-1.5 bg-rose-600 text-white hover:bg-rose-700 rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1"
                                        data-id="${division.id}" title="Hapus Divisi">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
                $('#pagination-info').text(`Menampilkan ${Math.min(startIndex + 1, totalItems)}-${Math.min(endIndex, totalItems)} dari ${totalItems} divisi`);
                $('#prev-page').prop('disabled', currentPage === 1);
                $('#next-page').prop('disabled', currentPage === totalPages || totalPages === 0);

                $('#page-numbers').empty();
                for (let i = 1; i <= totalPages; i++) {
                    const activeCls = currentPage === i ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300';
                    $('#page-numbers').append(`
                        <button class="page-number ${activeCls} px-3 py-1.5 rounded-lg text-xs font-bold shadow-xs transition" data-page="${i}">${i}</button>
                    `);
                }
            }

            function filterDivisions(searchTerm) {
                return divisions.filter(division =>
                    division.name.toLowerCase().includes(searchTerm.toLowerCase())
                );
            }

            function updateTable() {
                const searchTerm = $('#searchInput').val();
                const filteredDivisions = filterDivisions(searchTerm);
                currentPage = 1;
                renderTable(currentPage, filteredDivisions);
            }

            // Initial render
            renderTable(currentPage, divisions);

            // Search input event listener
            $('#searchInput').on('input', function() {
                updateTable();
            });

            $(document).on('click', '.page-number', function() {
                currentPage = $(this).data('page');
                const searchTerm = $('#searchInput').val();
                const filteredDivisions = filterDivisions(searchTerm);
                renderTable(currentPage, filteredDivisions);
            });

            $('#prev-page').on('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    const searchTerm = $('#searchInput').val();
                    const filteredDivisions = filterDivisions(searchTerm);
                    renderTable(currentPage, filteredDivisions);
                }
            });

            $('#next-page').on('click', function() {
                const searchTerm = $('#searchInput').val();
                const filteredDivisions = filterDivisions(searchTerm);
                if (currentPage < Math.ceil(filteredDivisions.length / itemsPerPage)) {
                    currentPage++;
                    renderTable(currentPage, filteredDivisions);
                }
            });

            $('#addDivisiButton').on('click', function() {
                $('#namaDivisi').val('');
                $('#iconDivisi').val('');
                $('#max_logbook_per_day').val(1);
                $('#addDivisiModal').removeClass('hidden');
            });

            $('#closeAddModal').on('click', function() {
                $('#addDivisiModal').addClass('hidden');
            });

            $(document).on('click', '.editDivisi', function() {
                const divisiId = $(this).data('id');
                const namaDivisi = $(this).data('nama');
                const maxLogbook = $(this).data('max-logbook') || 1;

                $('#editDivisiId').val(divisiId);
                var actionUrl = "{{ route('divisions.update', ':id') }}".replace(':id', divisiId);
                $('#editDivisiForm').attr('action', actionUrl);
                $('#editNamaDivisi').val(namaDivisi);
                $('#editIconDivisi').val('');
                $('#edit_max_logbook_per_day').val(maxLogbook);

                $('#editDivisiModal').removeClass('hidden');
            });

            $('#closeEditDivisiModal').on('click', function() {
                $('#editDivisiModal').addClass('hidden');
            });

            $(document).on('click', '.deleteDivisi', function() {
                const divisiId = $(this).data('id');
                $('#deleteDivisiId').val(divisiId);
                const actionUrl = "{{ route('divisions.delete', ':id') }}".replace(':id', divisiId);
                $('#deleteDivisiForm').attr('action', actionUrl);
                $('#deleteDivisiModal').removeClass('hidden');
            });

            $('#closeDeleteDivisiModal').on('click', function() {
                $('#deleteDivisiModal').addClass('hidden');
            });

            function validateDivisionIconFile(input) {
                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    const allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
                    const allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                    const ext = (file.name.split('.').pop() || '').toLowerCase();

                    if (!allowedExts.includes(ext) && !allowedMimes.includes(file.type)) {
                        const alertMsg = 'Format file tidak diizinkan. Harap unggah foto ikon divisi berformat JPG, JPEG, PNG, atau WebP.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'warning', title: 'Format Tidak Sesuai', text: alertMsg, confirmButtonColor: '#3085d6' });
                        } else {
                            alert(alertMsg);
                        }
                        input.value = '';
                        return false;
                    }

                    if (file.size > 2 * 1024 * 1024) {
                        const sizeMsg = `Ukuran foto (${(file.size / (1024 * 1024)).toFixed(2)} MB) melebihi batas maksimal 2MB.`;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'warning', title: 'Ukuran Terlalu Besar', text: sizeMsg, confirmButtonColor: '#3085d6' });
                        } else {
                            alert(sizeMsg);
                        }
                        input.value = '';
                        return false;
                    }
                }
                return true;
            }

            $('#iconDivisi, #editIconDivisi').on('change', function() {
                validateDivisionIconFile(this);
            });

            $(window).on('click', function(event) {
                if ($(event.target).is('#addDivisiModal') || $(event.target).is('#editDivisiModal') || $(event.target).is('#deleteDivisiModal')) {
                    $(event.target).addClass('hidden');
                }
            });
        });
    </script>
@endsection
