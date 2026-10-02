@extends('layouts.main')

@section('title', 'Pengaturan Shift')

@section('contents')
    @include('layouts.sidebar-pengaturan')

    <!-- Main Content -->
    <main class="ml-0 lg:ml-[32rem] mt-2 lg:mt-20 p-3 sm:p-6 min-w-0 max-w-full overflow-x-hidden">
        <div class="w-full max-w-full min-w-0 space-y-6">

            <!-- Header & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2.5">
                    <i class="fa-solid fa-clock text-blue-600"></i>
                    Manage Shift
                </h1>
                <p class="text-sm text-gray-600 mt-1">Pengaturan jam kerja, waktu istirahat, dan validasi lokasi GPS shift pemagang</p>
            </div>
            <button id="openAddShiftModal"
                class="px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm hover:shadow self-start sm:self-auto shrink-0">
                <i class="fas fa-plus"></i>
                <span>Tambah Shift</span>
            </button>
        </div>

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

        <!-- Filter & Search Toolbar -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            <div class="relative w-full sm:max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" id="searchInput" placeholder="Cari nama shift..."
                    class="w-full text-xs pl-9 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            <div class="text-xs text-gray-500 font-medium shrink-0" id="shiftCountInfo"></div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200 font-bold tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 sm:px-6 text-center w-16">No</th>
                            <th class="py-3.5 px-4 sm:px-6">Nama Shift</th>
                            <th class="py-3.5 px-4 sm:px-6">Jam Mulai</th>
                            <th class="py-3.5 px-4 sm:px-6">Jam Berakhir</th>
                            <th class="py-3.5 px-4 sm:px-6">Durasi Istirahat</th>
                            <th class="py-3.5 px-4 sm:px-6 text-center">Validasi GPS</th>
                            <th class="py-3.5 px-4 sm:px-6 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="shift-tbody" class="divide-y divide-gray-100">
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

    <!-- Modal Tambah Shift -->
    <div id="showAddShiftModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-blue-600"></i>
                    Tambahkan Shift
                </h2>
                <button type="button" id="closeAddShiftModal" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form id="addShiftForm" action="{{ route('shifts.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="addNamaShift" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Nama Shift <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="addNamaShift" name="addNamaShift"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Contoh: Pagi, Siang, Middle" required>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="addJamMulai" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="addJamMulai" name="addJamMulai"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            required>
                    </div>
                    <div>
                        <label for="addJamBerakhir" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Jam Berakhir <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="addJamBerakhir" name="addJamBerakhir"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            required>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="start_break_time" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Mulai Istirahat <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="start_break_time" name="start_break_time"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            required>
                    </div>

                    <div>
                        <label for="end_break_time" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Selesai Istirahat <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="end_break_time" name="end_break_time"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            required>
                    </div>
                </div>

                <!-- Pengaturan Istirahat Khusus Hari Jumat (Pemagang Laki-Laki) -->
                <div class="p-3.5 bg-amber-50/70 rounded-xl border border-amber-200/80 mb-4">
                    <label class="flex items-start gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" id="is_friday_break_active" name="is_friday_break_active" value="1"
                            class="w-4 h-4 mt-0.5 text-orange-600 rounded border-gray-300 focus:ring-orange-500 cursor-pointer"
                            onchange="toggleFridayBreakInputs('add')">
                        <div>
                            <span class="text-xs font-bold text-slate-800">
                                Aktifkan Jam Istirahat Khusus Hari Jumat (Pemagang Laki-Laki)
                            </span>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Centang jika shift ini memiliki jam istirahat khusus untuk pemagang laki-laki pada hari Jumat (misal shalat Jumat).
                            </p>
                        </div>
                    </label>

                    <div id="add_friday_break_container" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3 pt-3 border-t border-amber-200/60">
                        <div>
                            <label for="friday_start_break_time" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Mulai Istirahat Jumat
                            </label>
                            <input type="time" id="friday_start_break_time" name="friday_start_break_time" value="11:40"
                                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                        </div>
                        <div>
                            <label for="friday_end_break_time" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Selesai Istirahat Jumat
                            </label>
                            <input type="time" id="friday_end_break_time" name="friday_end_break_time" value="12:40"
                                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <label for="add_is_gps_active" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Validasi Lokasi GPS (Maps) <span class="text-red-500">*</span>
                    </label>
                    <select id="add_is_gps_active" name="is_gps_active"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        <option value="1">Aktif (Wajib di Area Kantor)</option>
                        <option value="0">Non-Aktif (WFH / Bebas Area)</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="$('#showAddShiftModal').addClass('hidden')"
                        class="px-4 py-2 text-xs font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-xl shadow-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition">
                        Simpan Shift
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Shift -->
    <div id="editShiftModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-amber-600"></i>
                    Update Shift
                </h2>
                <button type="button" id="closeEditModal" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form id="topupForm" action="" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="shiftId" name="shiftId">
                <div class="mb-4">
                    <label for="nama_Shift" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Nama Shift <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nama_Shift" name="nama_Shift"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        required>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="jamMulai" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="jamMulai" name="jamMulai"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            required>
                    </div>
                    <div>
                        <label for="jamBerakhir" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Jam Berakhir <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="jamBerakhir" name="jamBerakhir"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            required>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="edit_start_break_time" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Mulai Istirahat <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="edit_start_break_time" name="edit_start_break_time"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            required>
                    </div>

                    <div>
                        <label for="edit_end_break_time" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Selesai Istirahat <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="edit_end_break_time" name="edit_end_break_time"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            required>
                    </div>
                </div>

                <!-- Pengaturan Istirahat Khusus Hari Jumat (Pemagang Laki-Laki) -->
                <div class="p-3.5 bg-amber-50/70 rounded-xl border border-amber-200/80 mb-4">
                    <label class="flex items-start gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" id="edit_is_friday_break_active" name="edit_is_friday_break_active" value="1"
                            class="w-4 h-4 mt-0.5 text-orange-600 rounded border-gray-300 focus:ring-orange-500 cursor-pointer"
                            onchange="toggleFridayBreakInputs('edit')">
                        <div>
                            <span class="text-xs font-bold text-slate-800">
                                Aktifkan Jam Istirahat Khusus Hari Jumat (Pemagang Laki-Laki)
                            </span>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Centang jika shift ini memiliki jam istirahat khusus untuk pemagang laki-laki pada hari Jumat (misal shalat Jumat).
                            </p>
                        </div>
                    </label>

                    <div id="edit_friday_break_container" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3 pt-3 border-t border-amber-200/60">
                        <div>
                            <label for="edit_friday_start_break_time" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Mulai Istirahat Jumat
                            </label>
                            <input type="time" id="edit_friday_start_break_time" name="edit_friday_start_break_time"
                                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                        </div>
                        <div>
                            <label for="edit_friday_end_break_time" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Selesai Istirahat Jumat
                            </label>
                            <input type="time" id="edit_friday_end_break_time" name="edit_friday_end_break_time"
                                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <label for="edit_is_gps_active" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Validasi Lokasi GPS (Maps) <span class="text-red-500">*</span>
                    </label>
                    <select id="edit_is_gps_active" name="is_gps_active"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        <option value="1">Aktif (Wajib di Area Kantor)</option>
                        <option value="0">Non-Aktif (WFH / Bebas Area)</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="$('#editShiftModal').addClass('hidden')"
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

    <!-- Modal Hapus Shift -->
    <div id="deleteShiftModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
            <div class="flex items-center gap-3 mb-4 text-red-600">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Hapus Shift</h2>
                    <p class="text-xs text-gray-500">Konfirmasi tindakan penghapusan</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-5">Apakah Anda yakin ingin menghapus shift ini? Data jadwal yang menggunakan shift ini mungkin terpengaruh.</p>
            <form id="deleteForm" action="" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" id="deleteShiftId" name="shiftId">
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" id="closeDeleteModal"
                        class="px-4 py-2 text-xs font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-xl shadow-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-xs transition">
                        Hapus Shift
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script id="shift-data" type="application/json">@json($shift)</script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const shifts = JSON.parse(document.getElementById('shift-data')?.textContent || '[]');
            const perPage = 8;
            let currentPage = 1;
            const shiftTbody = document.querySelector('#shift-tbody');
            const prevPageButton = document.querySelector('#prev-page');
            const nextPageButton = document.querySelector('#next-page');
            const pageNumbersContainer = document.querySelector('#page-numbers');
            const paginationInfo = document.querySelector('#pagination-info');
            const shiftCountInfo = document.querySelector('#shiftCountInfo');
            const searchInput = document.querySelector('#searchInput');
            let filteredShifts = shifts;
            let totalPages = Math.max(1, Math.ceil(filteredShifts.length / perPage));

            const renderTable = () => {
                shiftTbody.innerHTML = '';
                const start = (currentPage - 1) * perPage;
                const end = start + perPage;
                const currentData = filteredShifts.slice(start, end);

                if (shiftCountInfo) {
                    shiftCountInfo.innerHTML = `Total: <strong class="text-gray-800">${filteredShifts.length}</strong> Shift`;
                }

                if (currentData.length === 0) {
                    shiftTbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400 text-xs">
                                <i class="fa-solid fa-folder-open text-2xl mb-2 block"></i>
                                Tidak ada data shift ditemukan
                            </td>
                        </tr>
                    `;
                    if (paginationInfo) paginationInfo.textContent = 'Menampilkan 0 data';
                    updatePaginationButtons();
                    renderPageNumbers();
                    return;
                }

                if (paginationInfo) {
                    const startEntry = start + 1;
                    const endEntry = Math.min(end, filteredShifts.length);
                    paginationInfo.textContent = `Menampilkan ${startEntry}-${endEntry} dari ${filteredShifts.length} shift`;
                }

                currentData.forEach((shift, index) => {
                    const gpsBadge = (shift.is_gps_active == 1 || shift.is_gps_active === undefined || shift.is_gps_active === null)
                        ? '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-600 text-white rounded-full text-[11px] font-bold shadow-xs"><i class="fa-solid fa-location-dot text-[10px]"></i> Aktif</span>'
                        : '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-gray-500 text-white rounded-full text-[11px] font-bold shadow-xs"><i class="fa-solid fa-location-slash text-[10px]"></i> Bebas (WFH)</span>';

                    const fridayBadge = (shift.is_friday_break_active == 1 || shift.is_friday_break_active === true)
                        ? `<div class="mt-1"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-bold" title="Istirahat Khusus Jumat Laki-Laki: ${(shift.friday_start_break_time || '11:40').substring(0, 5)} - ${(shift.friday_end_break_time || '12:40').substring(0, 5)}"><i class="fa-solid fa-person text-amber-600"></i> Jumat (L): ${(shift.friday_start_break_time || '11:40').substring(0, 5)} - ${(shift.friday_end_break_time || '12:40').substring(0, 5)}</span></div>`
                        : '';

                    const row = document.createElement('tr');
                    row.className = 'hover:bg-blue-50/30 transition-colors border-b border-gray-100';
                    row.innerHTML = `
                        <td class="py-3.5 px-4 sm:px-6 text-center text-gray-500 font-mono text-xs">${start + index + 1}</td>
                        <td class="py-3.5 px-4 sm:px-6 font-bold text-gray-800 whitespace-nowrap">${shift.name}</td>
                        <td class="py-3.5 px-4 sm:px-6 font-mono text-xs text-gray-700 whitespace-nowrap"><i class="fa-regular fa-clock text-gray-400 mr-1"></i>${(shift.start_time || '').substring(0, 5)}</td>
                        <td class="py-3.5 px-4 sm:px-6 font-mono text-xs text-gray-700 whitespace-nowrap"><i class="fa-regular fa-clock text-gray-400 mr-1"></i>${(shift.end_time || '').substring(0, 5)}</td>
                        <td class="py-3.5 px-4 sm:px-6 text-xs text-gray-700 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded bg-gray-100 font-medium">${shift.break_time_in_minute || 0} menit (${(shift.start_break_time || '').substring(0, 5)} - ${(shift.end_break_time || '').substring(0, 5)})</span>
                            ${fridayBadge}
                        </td>
                        <td class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">${gpsBadge}</td>
                        <td class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <button class="editShiftModal px-2.5 py-1.5 bg-amber-500 text-white hover:bg-amber-600 rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1"
                                    data-id="${shift.id}"
                                    data-nama="${shift.name}"
                                    data-mulai="${shift.start_time}"
                                    data-berakhir="${shift.end_time}"
                                    data-start-break="${shift.start_break_time}"
                                    data-end-break="${shift.end_break_time}"
                                    data-adt-start-break="${shift.adt_start_break_time}"
                                    data-adt-end-break="${shift.adt_end_break_time}"
                                    data-friday-active="${shift.is_friday_break_active == 1 || shift.is_friday_break_active === true ? 1 : 0}"
                                    data-friday-start-break="${shift.friday_start_break_time || ''}"
                                    data-friday-end-break="${shift.friday_end_break_time || ''}"
                                    data-gps="${shift.is_gps_active !== undefined && shift.is_gps_active !== null ? shift.is_gps_active : 1}"
                                    title="Edit Shift">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="deleteShiftModal px-2.5 py-1.5 bg-rose-600 text-white hover:bg-rose-700 rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1" data-id="${shift.id}" title="Hapus Shift">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    shiftTbody.appendChild(row);
                });

                addEventListenersToButtons();
                updatePaginationButtons();
                renderPageNumbers();
            };

            const filterShifts = () => {
                const searchTerm = (searchInput.value || '').toLowerCase();
                filteredShifts = shifts.filter(shift => (shift.name || '').toLowerCase().includes(searchTerm));
                totalPages = Math.max(1, Math.ceil(filteredShifts.length / perPage));
                currentPage = 1;
                renderTable();
            };

            searchInput.addEventListener('input', filterShifts);

            const updatePaginationButtons = () => {
                prevPageButton.disabled = currentPage <= 1;
                nextPageButton.disabled = currentPage >= totalPages;
            };

            const renderPageNumbers = () => {
                pageNumbersContainer.innerHTML = '';
                for (let i = 1; i <= totalPages; i++) {
                    const pageButton = document.createElement('button');
                    pageButton.textContent = i;
                    pageButton.className = 'cursor-pointer px-3 py-1.5 rounded-lg text-xs font-bold shadow-xs transition';

                    if (i === currentPage) {
                        pageButton.classList.add('bg-blue-600', 'text-white');
                    } else {
                        pageButton.classList.add('text-gray-800', 'bg-gray-200', 'hover:bg-gray-300');
                    }

                    pageButton.addEventListener('click', () => {
                        if (i !== currentPage) {
                            currentPage = i;
                            renderTable();
                        }
                    });
                    pageNumbersContainer.appendChild(pageButton);
                }
            };

            const addEventListenersToButtons = () => {
                document.querySelectorAll('.editShiftModal').forEach(button => {
                    button.addEventListener('click', function() {
                        const shiftId = this.getAttribute('data-id');
                        const shiftName = this.getAttribute('data-nama');
                        const startTime = this.getAttribute('data-mulai');
                        const endTime = this.getAttribute('data-berakhir');
                        const startBreak = this.getAttribute('data-start-break');
                        const endBreak = this.getAttribute('data-end-break');
                        const gpsActive = this.getAttribute('data-gps');
                        const isFridayActive = this.getAttribute('data-friday-active') == '1';
                        const fridayStartBreak = this.getAttribute('data-friday-start-break');
                        const fridayEndBreak = this.getAttribute('data-friday-end-break');

                        const convertTo24HourFormat = (time) => {
                            if (!time) return '';
                            const parts = time.split(':');
                            const hours = parts[0] || '00';
                            const minutes = parts[1] || '00';
                            const seconds = parts[2] ? parts[2].substring(0, 2) : '00';
                            const period = time.slice(-2).toLowerCase();
                            let hours24 = parseInt(hours, 10);
                            if (period === 'pm' && hours24 !== 12) {
                                hours24 += 12;
                            } else if (period === 'am' && hours24 === 12) {
                                hours24 = 0;
                            }
                            return `${hours24.toString().padStart(2, '0')}:${minutes}:${seconds}`;
                        };

                        $('#shiftId').val(shiftId);
                        $('#nama_Shift').val(shiftName);
                        $('#jamMulai').val(convertTo24HourFormat(startTime));
                        $('#jamBerakhir').val(convertTo24HourFormat(endTime));
                        $('#edit_start_break_time').val(convertTo24HourFormat(startBreak));
                        $('#edit_end_break_time').val(convertTo24HourFormat(endBreak));
                        $('#edit_is_gps_active').val(gpsActive !== null && gpsActive !== undefined ? gpsActive : '1');

                        $('#edit_is_friday_break_active').prop('checked', isFridayActive);
                        $('#edit_friday_start_break_time').val(convertTo24HourFormat(fridayStartBreak) || '11:40');
                        $('#edit_friday_end_break_time').val(convertTo24HourFormat(fridayEndBreak) || '12:40');
                        if (isFridayActive) {
                            $('#edit_friday_break_container').removeClass('hidden');
                        } else {
                            $('#edit_friday_break_container').addClass('hidden');
                        }

                        var actionUrl = "{{ route('shifts.update', ':id') }}";
                        actionUrl = actionUrl.replace(':id', shiftId);
                        $('#topupForm').attr('action', actionUrl);

                        $('#editShiftModal').removeClass('hidden');
                    });
                });

                document.querySelectorAll('.deleteShiftModal').forEach(button => {
                    button.addEventListener('click', function() {
                        const shiftId = this.getAttribute('data-id');

                        var actionUrl = "{{ route('shifts.delete', ':id') }}";
                        actionUrl = actionUrl.replace(':id', shiftId);
                        $('#deleteForm').attr('action', actionUrl);
                        $('#deleteForm').find('input[name="shiftId"]').val(shiftId);

                        $('#deleteShiftModal').removeClass('hidden');
                    });
                });
            };

            window.toggleFridayBreakInputs = function(type) {
                if (type === 'add') {
                    const isChecked = $('#is_friday_break_active').is(':checked');
                    if (isChecked) {
                        $('#add_friday_break_container').removeClass('hidden');
                    } else {
                        $('#add_friday_break_container').addClass('hidden');
                    }
                } else if (type === 'edit') {
                    const isChecked = $('#edit_is_friday_break_active').is(':checked');
                    if (isChecked) {
                        $('#edit_friday_break_container').removeClass('hidden');
                    } else {
                        $('#edit_friday_break_container').addClass('hidden');
                    }
                }
            };

            prevPageButton.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderTable();
                }
            });

            nextPageButton.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderTable();
                }
            });

            renderTable();

            $('#openAddShiftModal').on('click', function() {
                $('#is_friday_break_active').prop('checked', false);
                $('#add_friday_break_container').addClass('hidden');
                $('#friday_start_break_time').val('11:40');
                $('#friday_end_break_time').val('12:40');
                $('#showAddShiftModal').removeClass('hidden');
            });

            $('#closeAddShiftModal').on('click', function() {
                $('#showAddShiftModal').addClass('hidden');
            });

            $('#closeEditModal').on('click', function() {
                $('#editShiftModal').addClass('hidden');
            });

            $('#closeDeleteModal').on('click', function() {
                $('#deleteShiftModal').addClass('hidden');
            });

            $(window).on('click', function(event) {
                if ($(event.target).is('#showAddShiftModal')) {
                    $('#showAddShiftModal').addClass('hidden');
                }
                if ($(event.target).is('#editShiftModal')) {
                    $('#editShiftModal').addClass('hidden');
                }
                if ($(event.target).is('#deleteShiftModal')) {
                    $('#deleteShiftModal').addClass('hidden');
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const message = document.getElementById('success-message');
            if (message) {
                setTimeout(() => {
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 400);
                }, 3000);
            }
        });
    </script>
@endsection
