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
                    <p class="text-sm text-gray-600 mt-1">Pengaturan jam kerja operasional, istirahat, validasi GPS, rotasi mingguan, dan skema split-shift</p>
                </div>
                <button id="openAddShiftModal"
                    class="px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm hover:shadow self-start sm:self-auto shrink-0 cursor-pointer">
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
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 bg-red-50 border border-red-400 text-red-800 px-4 py-3 rounded-xl relative flex items-center justify-between shadow-xs transition-opacity duration-300"
                    role="alert">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-red-600 text-lg"></i>
                        <span class="font-medium text-sm">{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-800 cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 bg-red-50 border border-red-400 text-red-800 px-4 py-3 rounded-xl shadow-xs" role="alert">
                    <div class="font-bold text-sm mb-1 flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-red-600"></i>
                        Terjadi Kesalahan Validasi:
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
                <!-- Mobile Scroll Hint -->
                <div class="sm:hidden px-4 py-2 bg-blue-50/70 border-b border-blue-100/80 text-blue-700 text-[11px] flex items-center justify-between">
                    <span class="flex items-center gap-1.5 font-medium">
                        <i class="fa-solid fa-arrows-left-right text-blue-500"></i>
                        Geser tabel ke samping untuk melihat kolom lengkap
                    </span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-blue-400"></i>
                </div>

                <div class="overflow-x-auto touch-pan-x w-full">
                    <table class="w-full min-w-[850px] text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200 font-bold tracking-wider">
                            <tr>
                                <th class="py-3.5 px-4 sm:px-6 text-center w-16">No</th>
                                <th class="py-3.5 px-4 sm:px-6">Nama Shift</th>
                                <th class="py-3.5 px-4 sm:px-6 whitespace-nowrap">Jam Masuk - Pulang</th>
                                <th class="py-3.5 px-4 sm:px-6">Durasi Istirahat</th>
                                <th class="py-3.5 px-4 sm:px-6 text-center">Jadwal Terkait</th>
                                <th class="py-3.5 px-4 sm:px-6 text-center">Validasi GPS</th>
                                <th class="py-3.5 px-4 sm:px-6 text-center">Rolling Mingguan</th>
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
                <button type="button" id="closeAddShiftModal" class="text-gray-400 hover:text-gray-600 transition cursor-pointer">
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

                <div class="mb-4">
                    <label for="addDescription" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Keterangan / Deskripsi <span class="text-gray-400 font-normal text-[11px]">(Opsional)</span>
                    </label>
                    <textarea id="addDescription" name="description" rows="2"
                        class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Contoh: Shift operasional kerja 08:00 - 17:00"></textarea>
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
                        <input type="time" id="start_break_time" name="start_break_time" value="12:00"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            required>
                    </div>

                    <div>
                        <label for="end_break_time" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Selesai Istirahat <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="end_break_time" name="end_break_time" value="13:00"
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

                <!-- Pengaturan Split-Shift (WFH di Rumah + Lanjut WFO di Kantor) -->
                <div class="p-3.5 bg-purple-50/70 rounded-xl border border-purple-200/80 mb-4">
                    <label class="flex items-start gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" id="is_split_shift" name="is_split_shift" value="1"
                            class="w-4 h-4 mt-0.5 text-purple-600 rounded border-gray-300 focus:ring-purple-500 cursor-pointer"
                            onchange="toggleSplitShiftInputs('add')">
                        <div>
                            <span class="text-xs font-bold text-slate-800">
                                Aktifkan Skema Split-Shift (WFH di Rumah + Lanjut WFO di Kantor)
                            </span>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Shift membagi hari jadi sesi WFH di awal (misal 13:00–16:00 di rumah/kos) lalu WFO di kantor (16:00–21:00) saat meja kantor mulai longgar.
                            </p>
                        </div>
                    </label>

                    <div id="add_split_shift_container" class="hidden space-y-3 mt-3 pt-3 border-t border-purple-200/60">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="split_wfh_start" class="block text-[11px] font-bold text-purple-800 uppercase tracking-wider mb-1">
                                    Mulai Sesi WFH (Rumah)
                                </label>
                                <input type="time" id="split_wfh_start" name="split_wfh_start" value="13:00"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
                            </div>
                            <div>
                                <label for="split_wfh_end" class="block text-[11px] font-bold text-purple-800 uppercase tracking-wider mb-1">
                                    Selesai Sesi WFH
                                </label>
                                <input type="time" id="split_wfh_end" name="split_wfh_end" value="16:00"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="split_wfo_start" class="block text-[11px] font-bold text-indigo-800 uppercase tracking-wider mb-1">
                                    Mulai Sesi WFO (Kantor)
                                </label>
                                <input type="time" id="split_wfo_start" name="split_wfo_start" value="16:00"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
                            </div>
                            <div>
                                <label for="split_wfo_end" class="block text-[11px] font-bold text-indigo-800 uppercase tracking-wider mb-1">
                                    Selesai Sesi WFO (Pulang)
                                </label>
                                <input type="time" id="split_wfo_end" name="split_wfo_end" value="21:00"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pengaturan Rolling / Rotasi Mingguan -->
                <div class="p-3.5 bg-blue-50/70 rounded-xl border border-blue-200/80 mb-4">
                    <label class="flex items-start gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" id="is_rolling" name="is_rolling" value="1"
                            class="w-4 h-4 mt-0.5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer">
                        <div>
                            <span class="text-xs font-bold text-slate-800">
                                Dapat Digunakan untuk Rolling / Rotasi Mingguan
                            </span>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Centang jika shift ini dapat dimasukkan dalam rotasi otomatis mingguan (misal bergantian Pagi ↔ Siang).
                            </p>
                        </div>
                    </label>
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
                        class="px-4 py-2 text-xs font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-xl shadow-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition cursor-pointer">
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
                <button type="button" id="closeEditModal" class="text-gray-400 hover:text-gray-600 transition cursor-pointer">
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
                        placeholder="Contoh: Pagi, Siang, Middle" required>
                </div>

                <div class="mb-4">
                    <label for="edit_description" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Keterangan / Deskripsi <span class="text-gray-400 font-normal text-[11px]">(Opsional)</span>
                    </label>
                    <textarea id="edit_description" name="description" rows="2"
                        class="w-full px-3.5 py-2 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Contoh: Shift operasional kerja 08:00 - 17:00"></textarea>
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

                <!-- Pengaturan Split-Shift (WFH di Rumah + Lanjut WFO di Kantor) -->
                <div class="p-3.5 bg-purple-50/70 rounded-xl border border-purple-200/80 mb-4" id="edit_split_shift_wrapper">
                    <label class="flex items-start gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" id="edit_is_split_shift" name="edit_is_split_shift" value="1"
                            class="w-4 h-4 mt-0.5 text-purple-600 rounded border-gray-300 focus:ring-purple-500 cursor-pointer"
                            onchange="toggleSplitShiftInputs('edit')">
                        <div>
                            <span class="text-xs font-bold text-slate-800">
                                Aktifkan Skema Split-Shift (WFH di Rumah + Lanjut WFO di Kantor)
                            </span>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Shift membagi hari jadi sesi WFH di awal (misal 13:00–16:00 di rumah/kos) lalu WFO di kantor (16:00–21:00) saat meja kantor mulai longgar.
                            </p>
                        </div>
                    </label>

                    <div id="edit_split_shift_container" class="hidden space-y-3 mt-3 pt-3 border-t border-purple-200/60">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="edit_split_wfh_start" class="block text-[11px] font-bold text-purple-800 uppercase tracking-wider mb-1">
                                    Mulai Sesi WFH (Rumah)
                                </label>
                                <input type="time" id="edit_split_wfh_start" name="edit_split_wfh_start"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
                            </div>
                            <div>
                                <label for="edit_split_wfh_end" class="block text-[11px] font-bold text-purple-800 uppercase tracking-wider mb-1">
                                    Selesai Sesi WFH
                                </label>
                                <input type="time" id="edit_split_wfh_end" name="edit_split_wfh_end"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="edit_split_wfo_start" class="block text-[11px] font-bold text-indigo-800 uppercase tracking-wider mb-1">
                                    Mulai Sesi WFO (Kantor)
                                </label>
                                <input type="time" id="edit_split_wfo_start" name="edit_split_wfo_start"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            </div>
                            <div>
                                <label for="edit_split_wfo_end" class="block text-[11px] font-bold text-indigo-800 uppercase tracking-wider mb-1">
                                    Selesai Sesi WFO (Pulang)
                                </label>
                                <input type="time" id="edit_split_wfo_end" name="edit_split_wfo_end"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pengaturan Rolling / Rotasi Mingguan -->
                <div class="p-3.5 bg-blue-50/70 rounded-xl border border-blue-200/80 mb-4">
                    <label class="flex items-start gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" id="edit_is_rolling" name="edit_is_rolling" value="1"
                            class="w-4 h-4 mt-0.5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer">
                        <div>
                            <span class="text-xs font-bold text-slate-800">
                                Dapat Digunakan untuk Rolling / Rotasi Mingguan
                            </span>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Centang jika shift ini dapat dimasukkan dalam rotasi otomatis mingguan (misal bergantian Pagi ↔ Siang).
                            </p>
                        </div>
                    </label>
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
                        class="px-4 py-2 text-xs font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-xl shadow-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-xs transition cursor-pointer">
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

            <p class="text-sm text-gray-700 mb-4">
                Apakah Anda yakin ingin menghapus shift <strong id="deleteShiftNameDisplay" class="text-gray-900 font-bold"></strong>?
            </p>

            <form id="deleteForm" action="" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" id="deleteShiftId" name="shiftId">

                <!-- Peringatan jika shift masih digunakan oleh jadwal -->
                <div id="deleteScheduleWarning" class="hidden mb-4 p-3.5 bg-amber-50 border border-amber-300 rounded-xl text-xs text-amber-900">
                    <div class="flex items-start gap-2.5">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 mt-0.5 text-sm shrink-0"></i>
                        <div>
                            <p class="font-bold">Shift ini sedang digunakan oleh <span id="deleteScheduleCount" class="text-red-600 font-extrabold text-sm">0</span> jadwal.</p>
                            <p class="mt-1 text-[11px] text-amber-800 leading-relaxed">
                                Jika dihapus, seluruh data jadwal dan riwayat penugasan terkait shift ini akan otomatis dibersihkan agar database tetap konsisten.
                            </p>
                            <label class="flex items-center gap-2 mt-2.5 pt-2 border-t border-amber-200 cursor-pointer font-bold text-red-700 select-none">
                                <input type="checkbox" id="forceDeleteCheckbox" name="force_delete" value="1" class="rounded text-red-600 focus:ring-red-500 cursor-pointer">
                                <span>Ya, saya yakin hapus paksa beserta seluruh jadwal terkait</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Info jika shift aman dihapus -->
                <div id="deleteSafeNotice" class="hidden mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 shrink-0"></i>
                    <span>Shift ini belum digunakan oleh jadwal manapun sehingga aman untuk dihapus.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" id="closeDeleteModal"
                        class="px-4 py-2 text-xs font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-xl shadow-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-xs transition cursor-pointer">
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

            // Helper format waktu ke HH:mm (Standar HTML5 input type="time")
            function formatTimeToHHMM(timeStr, defaultVal = '') {
                if (!timeStr) return defaultVal;
                const cleanStr = String(timeStr).trim();
                const parts = cleanStr.split(':');
                if (parts.length >= 2) {
                    const h = parts[0].padStart(2, '0');
                    const m = parts[1].padStart(2, '0');
                    return `${h}:${m}`;
                }
                return defaultVal;
            }

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
                            <td colspan="8" class="py-8 text-center text-gray-400 text-xs">
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

                    const rollingBadge = (shift.is_rolling == 1 || shift.is_rolling === true)
                        ? '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-600 text-white rounded-full text-[11px] font-bold shadow-xs" title="Shift ini dapat digunakan untuk rotasi bergantian mingguan"><i class="fa-solid fa-arrows-rotate text-[10px]"></i> Aktif</span>'
                        : '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-gray-100 text-gray-500 rounded-full text-[11px] font-medium border border-gray-200">Non-Aktif</span>';

                    const scheduleCount = shift.detail_schedules_count || 0;
                    const scheduleBadge = scheduleCount > 0
                        ? `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-full text-[11px] font-bold shadow-xs" title="Digunakan oleh ${scheduleCount} jadwal"><i class="fa-solid fa-calendar-check text-[10px]"></i> ${scheduleCount} Jadwal</span>`
                        : `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-gray-100 text-gray-500 rounded-full text-[11px] font-medium border border-gray-200" title="Belum ada jadwal yang menggunakan shift ini">0 Jadwal</span>`;

                    const fridayBadge = (shift.is_friday_break_active == 1 || shift.is_friday_break_active === true)
                        ? `<div class="mt-1"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-bold" title="Istirahat Khusus Jumat Laki-Laki: ${formatTimeToHHMM(shift.friday_start_break_time, '11:40')} - ${formatTimeToHHMM(shift.friday_end_break_time, '12:40')}"><i class="fa-solid fa-person text-amber-600"></i> Jumat (L): ${formatTimeToHHMM(shift.friday_start_break_time, '11:40')} - ${formatTimeToHHMM(shift.friday_end_break_time, '12:40')}</span></div>`
                        : '';

                    const splitBadge = (shift.is_split_shift == 1 || shift.is_split_shift === true)
                        ? `<div class="mt-1"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-purple-50 text-purple-800 border border-purple-200 text-[10px] font-bold" title="Split-Shift: WFH ${formatTimeToHHMM(shift.split_wfh_start, '13:00')}-${formatTimeToHHMM(shift.split_wfh_end, '16:00')} | WFO ${formatTimeToHHMM(shift.split_wfo_start, '16:00')}-${formatTimeToHHMM(shift.split_wfo_end, '21:00')}"><i class="fa-solid fa-code-fork text-purple-600"></i> Split: WFH ${formatTimeToHHMM(shift.split_wfh_start, '13:00')}-${formatTimeToHHMM(shift.split_wfh_end, '16:00')} &amp; WFO ${formatTimeToHHMM(shift.split_wfo_start, '16:00')}-${formatTimeToHHMM(shift.split_wfo_end, '21:00')}</span></div>`
                        : '';

                    const descriptionText = shift.description 
                        ? `<div class="text-[11px] text-gray-500 font-normal mt-0.5 truncate max-w-xs" title="${shift.description}">${shift.description}</div>` 
                        : '';

                    const row = document.createElement('tr');
                    row.className = 'hover:bg-blue-50/30 transition-colors border-b border-gray-100';
                    row.innerHTML = `
                        <td class="py-3.5 px-4 sm:px-6 text-center text-gray-500 font-mono text-xs">${start + index + 1}</td>
                        <td class="py-3.5 px-4 sm:px-6 font-bold text-gray-800">
                            <div>${shift.name}</div>
                            ${descriptionText}
                            ${splitBadge}
                        </td>
                        <td class="py-3.5 px-4 sm:px-6 font-mono text-xs font-semibold text-gray-800 whitespace-nowrap">
                            <i class="fa-regular fa-clock text-gray-400 mr-1.5"></i>${formatTimeToHHMM(shift.start_time)} - ${formatTimeToHHMM(shift.end_time)}
                        </td>
                        <td class="py-3.5 px-4 sm:px-6 text-xs text-gray-700 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded bg-gray-100 font-medium">${shift.break_time_in_minute || 0} menit (${formatTimeToHHMM(shift.start_break_time)} - ${formatTimeToHHMM(shift.end_break_time)})</span>
                            ${fridayBadge}
                        </td>
                        <td class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">${scheduleBadge}</td>
                        <td class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">${gpsBadge}</td>
                        <td class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">${rollingBadge}</td>
                        <td class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <button class="editShiftModalBtn px-2.5 py-1.5 bg-amber-500 text-white hover:bg-amber-600 rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1 cursor-pointer"
                                    data-id="${shift.id}"
                                    title="Edit Shift">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>
                                <button class="deleteShiftModalBtn px-2.5 py-1.5 bg-rose-600 text-white hover:bg-rose-700 rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1 cursor-pointer" 
                                    data-id="${shift.id}" 
                                    title="Hapus Shift">
                                    <i class="fa-solid fa-trash"></i>
                                    <span>Hapus</span>
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
                filteredShifts = shifts.filter(shift => (shift.name || '').toLowerCase().includes(searchTerm) || (shift.description || '').toLowerCase().includes(searchTerm));
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
                // Event listener tombol EDIT
                document.querySelectorAll('.editShiftModalBtn').forEach(button => {
                    button.addEventListener('click', function() {
                        const shiftId = this.getAttribute('data-id');
                        const shift = shifts.find(s => String(s.id) === String(shiftId));
                        if (!shift) return;

                        $('#shiftId').val(shift.id);
                        $('#nama_Shift').val(shift.name || '');
                        $('#edit_description').val(shift.description || '');
                        $('#jamMulai').val(formatTimeToHHMM(shift.start_time));
                        $('#jamBerakhir').val(formatTimeToHHMM(shift.end_time));
                        $('#edit_start_break_time').val(formatTimeToHHMM(shift.start_break_time, '12:00'));
                        $('#edit_end_break_time').val(formatTimeToHHMM(shift.end_break_time, '13:00'));

                        // GPS
                        const gpsVal = (shift.is_gps_active === 0 || shift.is_gps_active === '0') ? '0' : '1';
                        $('#edit_is_gps_active').val(gpsVal);

                        // Friday Break
                        const isFridayActive = shift.is_friday_break_active == 1 || shift.is_friday_break_active === true;
                        $('#edit_is_friday_break_active').prop('checked', isFridayActive);
                        $('#edit_friday_start_break_time').val(formatTimeToHHMM(shift.friday_start_break_time, '11:40'));
                        $('#edit_friday_end_break_time').val(formatTimeToHHMM(shift.friday_end_break_time, '12:40'));
                        if (isFridayActive) {
                            $('#edit_friday_break_container').removeClass('hidden');
                        } else {
                            $('#edit_friday_break_container').addClass('hidden');
                        }

                        // Split Shift
                        const isSplitActive = shift.is_split_shift == 1 || shift.is_split_shift === true;
                        $('#edit_is_split_shift').prop('checked', isSplitActive);
                        $('#edit_split_wfh_start').val(formatTimeToHHMM(shift.split_wfh_start, '13:00'));
                        $('#edit_split_wfh_end').val(formatTimeToHHMM(shift.split_wfh_end, '16:00'));
                        $('#edit_split_wfo_start').val(formatTimeToHHMM(shift.split_wfo_start, '16:00'));
                        $('#edit_split_wfo_end').val(formatTimeToHHMM(shift.split_wfo_end, '21:00'));
                        if (isSplitActive) {
                            $('#edit_split_shift_container').removeClass('hidden');
                        } else {
                            $('#edit_split_shift_container').addClass('hidden');
                        }

                        // Rolling Mingguan
                        const isRolling = shift.is_rolling == 1 || shift.is_rolling === true;
                        $('#edit_is_rolling').prop('checked', isRolling);

                        // Set Action URL
                        var actionUrl = "{{ route('shifts.update', ':id') }}".replace(':id', shift.id);
                        $('#topupForm').attr('action', actionUrl);

                        $('#editShiftModal').removeClass('hidden');
                    });
                });

                // Event listener tombol HAPUS
                document.querySelectorAll('.deleteShiftModalBtn').forEach(button => {
                    button.addEventListener('click', function() {
                        const shiftId = this.getAttribute('data-id');
                        const shift = shifts.find(s => String(s.id) === String(shiftId));
                        if (!shift) return;

                        var actionUrl = "{{ route('shifts.delete', ':id') }}".replace(':id', shift.id);
                        $('#deleteForm').attr('action', actionUrl);
                        $('#deleteForm').find('input[name="shiftId"]').val(shift.id);

                        $('#deleteShiftNameDisplay').text(shift.name);

                        const count = shift.detail_schedules_count || 0;
                        if (count > 0) {
                            $('#deleteScheduleCount').text(count);
                            $('#deleteScheduleWarning').removeClass('hidden');
                            $('#deleteSafeNotice').addClass('hidden');
                            $('#forceDeleteCheckbox').prop('checked', false);
                        } else {
                            $('#deleteScheduleWarning').addClass('hidden');
                            $('#deleteSafeNotice').removeClass('hidden');
                            $('#forceDeleteCheckbox').prop('checked', false);
                        }

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

            window.toggleSplitShiftInputs = function(type) {
                if (type === 'add') {
                    const isChecked = $('#is_split_shift').is(':checked');
                    if (isChecked) {
                        $('#add_split_shift_container').removeClass('hidden');
                    } else {
                        $('#add_split_shift_container').addClass('hidden');
                    }
                } else if (type === 'edit') {
                    const isChecked = $('#edit_is_split_shift').is(':checked');
                    if (isChecked) {
                        $('#edit_split_shift_container').removeClass('hidden');
                    } else {
                        $('#edit_split_shift_container').addClass('hidden');
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

                $('#is_split_shift').prop('checked', false);
                $('#add_split_shift_container').addClass('hidden');
                $('#split_wfh_start').val('13:00');
                $('#split_wfh_end').val('16:00');
                $('#split_wfo_start').val('16:00');
                $('#split_wfo_end').val('21:00');

                $('#is_rolling').prop('checked', false);
                $('#addDescription').val('');

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
