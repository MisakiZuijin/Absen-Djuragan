@extends('layouts.main')

@section('title', 'Presensi')

@section('contents')
    <!-- Main Content -->
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 pb-16 min-w-0">
        <div class="bg-gray-700 text-white p-4 sm:p-6 rounded-t-2xl">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 items-center">
                <!-- Left Column -->
                <div class="flex flex-col space-y-1">
                    <div class="text-2xl sm:text-3xl font-bold">Data Presensi</div>
                    <div class="text-xs sm:text-sm text-gray-300" id="date-display">Data per tanggal {{ $dateNow ?? '' }}</div>
                </div>

                <!-- Right Column -->
                <div class="flex flex-col space-y-1.5">
                    <label for="search-name" class="text-xs sm:text-sm font-medium text-gray-200">Cari Mahasiswa</label>
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-search text-xs"></i>
                        </div>
                        <input type="text" id="search-name"
                            class="w-full pl-8 pr-3 py-2 bg-white rounded-xl text-gray-800 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition shadow-2xs"
                            placeholder="Masukkan nama mahasiswa...">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-b-2xl shadow-xs border border-t-0 border-gray-200 p-4 sm:p-5 space-y-4">
            <!-- Baris 1: Total Kehadiran (Kiri) & Pemilihan Tanggal (Kanan) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                <!-- Left: Total Kehadiran (Clean Minimalist White Card) -->
                <div class="flex flex-col xs:flex-row sm:flex-row items-start sm:items-center gap-2 sm:gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 shrink-0">
                        Total Kehadiran:
                    </span>
                    <div class="grid grid-cols-3 gap-2 w-full sm:w-auto">
                        <!-- Masuk -->
                        <div class="px-3 py-1.5 bg-white border border-gray-200 hover:border-emerald-300 rounded-xl flex items-center justify-center sm:justify-start gap-2 shadow-2xs transition">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                            <span class="text-xs font-medium text-gray-600">Masuk</span>
                            <span id="total_presence" class="text-xs font-black text-gray-900">0</span>
                        </div>
                        <!-- Izin -->
                        <div class="px-3 py-1.5 bg-white border border-gray-200 hover:border-amber-300 rounded-xl flex items-center justify-center sm:justify-start gap-2 shadow-2xs transition">
                            <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                            <span class="text-xs font-medium text-gray-600">Izin</span>
                            <span id="total_permit" class="text-xs font-black text-gray-900">{{ $permitTotal ?? 0 }}</span>
                        </div>
                        <!-- Alpha -->
                        <div class="px-3 py-1.5 bg-white border border-gray-200 hover:border-rose-300 rounded-xl flex items-center justify-center sm:justify-start gap-2 shadow-2xs transition">
                            <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                            <span class="text-xs font-medium text-gray-600">Alpha</span>
                            <span id="total_absence" class="text-xs font-black text-gray-900">{{ $absenceTotal ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Date Input Target -->
                <div class="relative w-full sm:w-52 shrink-0">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-calendar-alt text-xs"></i>
                    </div>
                    <input type="date" id="date-target" value="{{ $dateNowYMD ?? '' }}"
                        class="w-full pl-8 pr-2.5 py-1.5 bg-gray-50 hover:bg-white border border-gray-300 focus:border-blue-500 focus:bg-white rounded-xl text-xs sm:text-sm font-semibold text-gray-800 focus:outline-none transition shadow-2xs">
                </div>
            </div>

            <!-- Baris 2: Filter Status, Filter Shift, Filter Kantor (3 Kolom Sejajar Rapi) -->
            <div class="pt-3 border-t border-gray-100">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-3">
                    <!-- Filter Status -->
                    <div class="relative w-full">
                        <select id="filter-status"
                            class="w-full px-3 py-2 bg-gray-50 hover:bg-white border border-gray-300 focus:border-blue-500 focus:bg-white rounded-xl text-xs sm:text-sm font-medium text-gray-800 focus:outline-none transition shadow-2xs cursor-pointer">
                            <option value="" disabled selected>Filter Status</option>
                            @foreach ($attd_statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Shift -->
                    <div class="relative w-full">
                        <select id="filter-shift"
                            class="w-full px-3 py-2 bg-gray-50 hover:bg-white border border-gray-300 focus:border-blue-500 focus:bg-white rounded-xl text-xs sm:text-sm font-medium text-gray-800 focus:outline-none transition shadow-2xs cursor-pointer">
                            <option value="" disabled selected>Filter Shift</option>
                            @foreach ($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Kantor -->
                    <div class="relative w-full">
                        <select id="filter-office"
                            class="w-full px-3 py-2 bg-gray-50 hover:bg-white border border-gray-300 focus:border-blue-500 focus:bg-white rounded-xl text-xs sm:text-sm font-medium text-gray-800 focus:outline-none transition shadow-2xs cursor-pointer">
                            <option value="" disabled selected>Filter Kantor</option>
                            @foreach ($office as $officeItem)
                                <option value="{{ $officeItem->id }}">{{ $officeItem->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div id="success-message"
                class="mb-2 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative transition-opacity duration-500"
                role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">{{ session('status') }}</span>
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

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="mt-4 flex flex-col sm:flex-row sm:justify-end items-stretch sm:items-center gap-2 sm:gap-3">
            <form action="./log-activity/yesall/{{ $dateNowYMD ?? '' }}" method="POST" id="yes-all-form" class="w-full sm:w-auto">
                @csrf
                <button id="open-modal-btn" type="submit"
                    class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-3 sm:px-4 text-xs sm:text-sm rounded-lg shadow-sm transition duration-200">
                    Yes to All
                </button>
            </form>

            <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">
                <form id="bulk-permit-notify-form" class="ajax-form w-full sm:w-auto" action="{{ route('admin.attendance.notify.permit.bulk') }}"
                    method="POST">
                    @csrf
                    <input type="hidden" name="date" class="bulk-notify-date" value="{{ $dateNowYMD ?? '' }}">
                    <button type="submit"
                        class="w-full sm:w-auto bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-2.5 sm:px-4 text-xs sm:text-sm rounded-lg shadow-sm transition duration-200 flex items-center justify-center gap-1.5 sm:gap-2">
                        <i class="fab fa-whatsapp"></i>
                        <span class="truncate">Kirim Notif Izin</span>
                    </button>
                </form>

                <form id="bulk-alpha-notify-form" class="ajax-form w-full sm:w-auto" action="{{ route('admin.attendance.notify.alpha.bulk') }}"
                    method="POST">
                    @csrf
                    <input type="hidden" name="date" class="bulk-notify-date" value="{{ $dateNowYMD ?? '' }}">
                    <button type="submit"
                        class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-2.5 sm:px-4 text-xs sm:text-sm rounded-lg shadow-sm transition duration-200 flex items-center justify-center gap-1.5 sm:gap-2">
                        <i class="fab fa-whatsapp"></i>
                        <span class="truncate">Kirim Notif Alpha</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto w-full bg-white rounded-lg shadow-md mt-4 border border-gray-200">
            <table class="min-w-full border-separate border-spacing-1">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-sm uppercase leading-normal text-center">
                        <th rowspan="2" class="px-4 py-2 border">No</th>
                        <th rowspan="2" class="px-4 py-2 text-center border">Nama</th>
                        <th colspan="2" class="px-4 py-2 border">Jam Kerja</th>
                        <th colspan="2" class="px-4 py-2 border">Jam Istirahat</th>
                        <th colspan="2" class="px-4 py-2 border">Total Jam Kerja</th>
                        <th rowspan="2" class="px-4 py-2 border">Status Kehadiran</th>
                        <th rowspan="2" class="px-4 py-2 border">Log Activity</th>
                        <th rowspan="2" class="px-4 py-2 border">Aksi</th>
                    </tr>
                    <tr class="bg-gray-200 text-gray-700 text-sm uppercase leading-normal text-center">
                        <!-- Sub-headings for the merged columns -->
                        <th class="py-2 px-4 border">Masuk</th>
                        <th class="py-2 px-4 border">Pulang</th>
                        <th class="py-2 px-4 border">Mulai</th>
                        <th class="py-2 px-4 border">Selesai</th>
                        <th class="py-2 px-4 border">Total Jam</th>
                        <th class="py-2 px-4 border">(+/-)</th>
                    </tr>
                </thead>

                <tbody class="text-sm font-normal text-gray-800" id="report-tbody">
                </tbody>
            </table>
        </div>

        <div id="loading-spinner" class="flex justify-center items-center py-4 ">
            <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-10 w-10"></div>
        </div>

        <div id="no-data-message" class="text-center py-4 text-gray-600 hidden">
            No data available
        </div>

        <!-- Pagination Controls -->
        <div class="mt-4 flex flex-wrap justify-center items-center gap-2 border rounded-md p-2">
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

        <!-- Download PDF Button -->
        <div class="mt-4 flex justify-end">
            <button id="download-pdf"
                class="px-3 py-2 text-sm font-medium text-center text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800 rounded-md">
                <i class="fa-solid fa-download"></i>
                <span>Download PDF</span>
            </button>
        </div>
    </main>


    <!-- Modal untuk Edit Presensi -->
    <div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 p-4 hidden">
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl max-w-sm w-full mx-auto">
            <div class="p-5 sm:p-6">
                <h2 class="text-lg font-semibold mb-3 text-center text-gray-800">Edit Presensi</h2>
                <p class="bg-red-50 text-red-700 border border-red-200 p-3 rounded-lg mb-4 text-xs sm:text-sm">
                    Anda akan merubah presensi <span id="field1" class="font-bold"></span> pada tanggal
                    <span id="date-display1" class="font-bold">---</span> atas nama:
                    <span id="name-display" class="font-bold">---</span>
                </p>
                <form id="editForm" action="" method="POST">
                    @csrf
                    <input type="hidden" name="field" id="field">
                    <input type="hidden" name="tipe" id="tipe">
                    <input type="hidden" name="attendance_id" id="attendanceId">
                    <div class="mb-4">
                        <label for="time" class="block text-gray-700 text-sm font-semibold mb-2">Waktu:</label>
                        <input type="time" name="time" id="time" step="1"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm"
                            required>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeModal()"
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal untuk Log Activity -->
    <div id="modal-activity" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4 hidden">
        <div class="bg-white p-5 sm:p-6 rounded-xl w-full max-w-xl mx-auto shadow-2xl max-h-[90vh] flex flex-col">
            <h2 class="text-lg font-semibold mb-3 text-gray-800">Log Activity</h2>

            <!-- Textarea -->
            <div class="mb-4 flex-1">
                <textarea id="log-activity"
                    class="mt-1 p-3 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm font-mono"
                    rows="6" readonly></textarea>
            </div>

            <!-- Tombol Tutup -->
            <div class="flex justify-end">
                <button id="close-activity-modal-btn" onclick="closeModalActivity()"
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-sm font-medium transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Aktivasi Izin Remote -->
    <div id="remotePermitModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-xs p-4 hidden">
        <div class="bg-white rounded-2xl overflow-hidden shadow-2xl max-w-md w-full mx-auto border border-slate-100 animate-in fade-in zoom-in duration-200">
            <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base leading-tight">Aktivasi Izin Remote</h3>
                        <p class="text-xs text-amber-100">Beri izin langsung untuk pemagang</p>
                    </div>
                </div>
                <button type="button" onclick="closeRemotePermitModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="remotePermitForm" action="{{ route('admin.presence.remote-permit') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="intern_id" id="remotePermitInternId">

                <!-- Info Pemagang -->
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] font-bold text-amber-800 uppercase tracking-wider">Pemagang Target</div>
                        <div class="text-sm font-bold text-slate-800 truncate" id="remotePermitInternName">---</div>
                    </div>
                </div>

                <!-- Tipe Izin -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih Tipe Izin</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label id="remoteTypeLabel-leave" class="relative flex flex-col items-center justify-center p-3 border-2 rounded-xl cursor-pointer transition border-amber-500 bg-amber-50/50">
                            <input type="radio" name="type" value="leave" checked class="sr-only" onchange="updateRemotePermitType('leave')">
                            <svg class="w-6 h-6 text-amber-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Keluar</span>
                        </label>
                        <label id="remoteTypeLabel-prayer" class="relative flex flex-col items-center justify-center p-3 border-2 rounded-xl cursor-pointer transition border-slate-200 bg-white">
                            <input type="radio" name="type" value="prayer" class="sr-only" onchange="updateRemotePermitType('prayer')">
                            <svg class="w-6 h-6 text-emerald-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Shalat</span>
                        </label>
                        <label id="remoteTypeLabel-toilet" class="relative flex flex-col items-center justify-center p-3 border-2 rounded-xl cursor-pointer transition border-slate-200 bg-white">
                            <input type="radio" name="type" value="toilet" class="sr-only" onchange="updateRemotePermitType('toilet')">
                            <svg class="w-6 h-6 text-blue-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Toilet</span>
                        </label>
                    </div>
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="remotePermitDescription" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan / Alasan (Opsional)</label>
                    <textarea name="description" id="remotePermitDescription" rows="2"
                        placeholder="Contoh: Izin keluar mengurus keperluan..."
                        class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeRemotePermitModal()"
                        class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="remotePermitSubmitBtn"
                        class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        <span>Aktifkan Izin Remote</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ===================================================================
        // BAGIAN UTAMA UNTUK AJAX & NOTIFIKASI
        // ===================================================================
        $(document).ready(function () {
            // Ambil CSRF token dari meta tag untuk semua request AJAX
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Fungsi untuk menampilkan notifikasi pop-up
            function showNotification(type, message) {
                // Hapus dulu notif lama jika ada untuk menghindari duplikasi
                $('#ajax-notification').remove();

                const alertType = type === 'success' ? 'green' : 'red';
                const title = type === 'success' ? 'Berhasil!' : 'Gagal!';
                const notifHtml = `
                        <div id="ajax-notification" class="mb-4 bg-${alertType}-100 border border-${alertType}-400 text-${alertType}-700 px-4 py-3 rounded relative" role="alert" style="display: none;">
                            <strong class="font-bold">${title}</strong>
                            <span class="block sm:inline">${message}</span>
                            <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="$('#ajax-notification').fadeOut(300, function() { $(this).remove(); })">
                                <svg class="fill-current h-6 w-6 text-${alertType}-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <title>Close</title>
                                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                                </svg>
                            </span>
                        </div>
                    `;
                // Tampilkan notif di atas tabel
                $('main .bg-gray-700').after(notifHtml);
                $('#ajax-notification').fadeIn(300);

                // Auto hide setelah 8 detik untuk notif sukses, 10 detik untuk error
                const hideTimeout = type === 'success' ? 8000 : 10000;
                setTimeout(() => {
                    $('#ajax-notification').fadeOut(500, function () { $(this).remove(); });
                }, hideTimeout);
            }

            // Event handler untuk form AJAX
            $(document).on('submit', 'form.ajax-form', function (e) {
                e.preventDefault();

                const form = $(this);
                const url = form.attr('action');
                const method = form.attr('method') || 'POST';
                const button = form.find('button[type="submit"]');
                const originalButtonContent = button.html();

                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: url,
                    method: method,
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function (response) {
                        showNotification('success', response.message || 'Aksi berhasil!');
                        if (typeof loadData === 'function') {
                            loadData({ page: currentPage });
                        }
                    },
                    error: function (xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                        showNotification('error', errorMsg);
                    },
                    complete: function () {
                        button.prop('disabled', false).html(originalButtonContent);
                    }
                });
            });

            // ===================================================================
            // BAGIAN KODE UNTUK LOAD DATA DAN PAGINATION
            // ===================================================================
            let currentPage = 1;
            let totalPages = 1;
            let searchQuery = '';
            let selectedOffice = '';
            let selectedShift = '';
            let selectedStatus = '';
            const date = new Date();
            let selectedDate = date.toLocaleDateString('sv-SE', {
                timeZone: 'Asia/Jakarta'
            });

            // Fungsi untuk format waktu dengan detik
            function formatTimeWithSeconds(timeString) {
                if (!timeString) return '---';

                try {
                    // Jika sudah dalam format HH:mm:ss, langsung return
                    if (timeString.match(/^\d{2}:\d{2}:\d{2}$/)) {
                        return timeString;
                    }

                    // Jika dalam format HH:mm, tambahkan :00
                    if (timeString.match(/^\d{2}:\d{2}$/)) {
                        return timeString + ':00';
                    }

                    // Jika format datetime, parse dan ambil waktu saja
                    const time = new Date(timeString);
                    if (!isNaN(time.getTime())) {
                        return time.toLocaleTimeString('en-GB', {
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: false
                        });
                    }

                    return timeString;
                } catch (error) {
                    console.error('Error formatting time:', error);
                    return timeString;
                }
            }

            // Fungsi untuk escape karakter HTML
            function escapeHtml(text) {
                if (!text) return '';

                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;',
                    '`': '&#96;',
                    '%': '&#37;'
                };
                return text.replace(/[&<>"'`%]/g, function (m) { return map[m]; });
            }

            // Fungsi untuk decode HTML entities
            function decodeHtmlEntities(text) {
                if (!text) return '';

                const textArea = document.createElement('textarea');
                textArea.innerHTML = text;
                return textArea.value;
            }

            // Fungsi untuk membuka modal activity dengan handling karakter khusus
            function openModalActivity(buttonElement) {
                const modal = document.getElementById('modal-activity');
                const textarea = document.getElementById('log-activity');

                // Decode HTML entities sebelum menampilkan
                const decodedActivity = decodeHtmlEntities(buttonElement.dataset.activity);
                textarea.value = decodedActivity;

                modal.classList.remove('hidden');
            }

            function closeModalActivity() {
                const modal = document.getElementById('modal-activity');
                modal.classList.add('hidden');
            }

            // Fungsi untuk generate cell attendance dengan validasi
            function generateAttendanceCell(tipe, label, field, data, name) {
                // Validasi data untuk menghindari error
                if (!data) {
                    return `<span>---</span>`;
                }

                const time = formatTimeWithSeconds(data[field] || '');
                const id = data.id || '';
                const dateNow = data.date || '';

                const escapedName = name ? escapeHtml(name) : '';

                return `
                        <span onclick="openModal('${tipe}','${label}', '${field}', '${time}', '${id}', '${dateNow}', '${escapedName}')" class="cursor-pointer text-blue-600 hover:text-blue-800 hover:underline">${time}</span>
                    `;
            }

            // Fungsi untuk mendapatkan status icons main
            function getStatusIconsMain(item) {
                let logActivityIcons = '-';
                if (item.log_activity) {
                    const status_id = item.log_activity.status_id;
                    const logActivityId = item.log_activity.id;
                    logActivityIcons = `
                             <form action="/admin/log-activity/update-status/${logActivityId}" method="POST" class="inline group ajax-form">
                                 @csrf
                                 <input type="hidden" name="status" value="2">
                                 <button type="submit" class="bg-transparent border-none p-0" title="Setujui Log Activity">
                                     <i class="fa-solid fa-check-circle ${status_id == 2 ? 'text-green-600' : 'text-gray-500'} text-xl hover:text-gray-800"></i>
                                 </button>
                             </form>
                             <form action="/admin/log-activity/update-status/${logActivityId}" method="POST" class="inline group ajax-form">
                                 @csrf
                                 <input type="hidden" name="status" value="3">
                                 <button type="submit" class="bg-transparent border-none p-0" title="Tolak Log Activity">
                                     <i class="fa-solid fa-xmark-circle ${status_id == 3 ? 'text-red-600' : 'text-gray-500'} text-xl hover:text-gray-800"></i>
                                 </button>
                             </form>
                         `;
                }

                const escapedName = item.name ? escapeHtml(item.name).replace(/'/g, "\\'") : '';
                const remotePermitBtn = `
                    <button type="button" onclick="openRemotePermitModal('${item.intern_id}', '${escapedName}')" 
                        class="p-1 rounded-md text-amber-600 hover:text-amber-800 hover:bg-amber-100 transition ml-1.5 cursor-pointer inline-flex items-center justify-center shrink-0"
                        title="Aktivasi Izin Remote untuk ${escapedName}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                `;

                return `<div class="inline-flex items-center justify-center">${logActivityIcons}${remotePermitBtn}</div>`;
            }

            // Fungsi untuk mendapatkan status icons adjustable - DIPERBAIKI
            function getStatusIconsAdjustable(adjustable) {
                const is_approved = adjustable.is_approved;
                const adjustableId = adjustable.id;

                // TAMBAHKAN FITUR RECYCLE DAN DELETE UNTUK GANTI JAM
                let actionIcons = '';

                // Icon Approve dan Reject
                if (is_approved != null) {
                    actionIcons = `
                             <form action="/admin/adjustable-attendance/update-status/${adjustableId}" method="POST" class="inline ajax-form">
                                 @csrf
                                 <input type="hidden" name="is_approved" value="1">
                                 <button type="submit" class="bg-transparent border-none p-0">
                                     <i class="fa-solid fa-check-circle ${is_approved == 1 ? 'text-blue-600' : 'text-gray-500'} text-xl hover:text-gray-800"></i>
                                 </button>
                             </form>
                             <form action="/admin/adjustable-attendance/update-status/${adjustableId}" method="POST" class="inline ajax-form">
                                 @csrf
                                 <input type="hidden" name="is_approved" value="2">
                                 <button type="submit" class="bg-transparent border-none p-0">
                                     <i class="fa-solid fa-xmark-circle ${is_approved == 2 ? 'text-red-500' : 'text-gray-500'} text-xl hover:text-gray-800"></i>
                                 </button>
                             </form>
                         `;
                } else {
                    // Jika belum ada status approval, tampilkan tombol approve/reject default
                    actionIcons = `
                             <form action="/admin/adjustable-attendance/update-status/${adjustableId}" method="POST" class="inline ajax-form">
                                 @csrf
                                 <input type="hidden" name="is_approved" value="1">
                                 <button type="submit" class="bg-transparent border-none p-0">
                                     <i class="fa-solid fa-check-circle text-gray-500 text-xl hover:text-gray-800"></i>
                                 </button>
                             </form>
                             <form action="/admin/adjustable-attendance/update-status/${adjustableId}" method="POST" class="inline ajax-form">
                                 @csrf
                                 <input type="hidden" name="is_approved" value="2">
                                 <button type="submit" class="bg-transparent border-none p-0">
                                     <i class="fa-solid fa-xmark-circle text-gray-500 text-xl hover:text-gray-800"></i>
                                 </button>
                             </form>
                         `;
                }

                // TAMBAHKAN ICON RECYCLE (RESTORE) DAN DELETE
                actionIcons += `
                        <form action="/admin/adjustable-attendance/restore/${adjustableId}" method="POST" class="inline ajax-form ml-1">
                            @csrf
                            <button type="submit" class="bg-transparent border-none p-0" title="Restore">
                                <i class="fa-solid fa-recycle text-green-600 text-xl hover:text-green-800"></i>
                            </button>
                        </form>
                        <form action="/admin/adjustable-attendance/delete/${adjustableId}" method="POST" class="inline ajax-form ml-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-transparent border-none p-0" title="Delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data ganti jam ini?')">
                                <i class="fa-solid fa-trash text-red-600 text-xl hover:text-red-800"></i>
                            </button>
                        </form>
                    `;

                return actionIcons;
            }

            // Fungsi untuk generate attendance type dengan status jelas dan tanpa modal
            function generateAttendanceType(item, rowspan) {
                const attdStatus = item.attd_status || {};
                const scheduleId = item.detail_schedule_id || '';
                const permitData = item.permit_data || {};
                const permitCategoryId = permitData.permit_category_id || '';
                const description = (permitData.description || '').toLowerCase();

                let statusBadge = '';
                const statusId = parseInt(attdStatus.id);

                if (statusId === 2) {
                    statusBadge = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Hadir</span>`;
                } else if (statusId === 1) {
                    statusBadge = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">Dijadwalkan</span>`;
                } else if (statusId === 3) {
                    const isSakit = (permitCategoryId == 1 || permitCategoryId == 2 || description.includes('sakit'));
                    if (isSakit) {
                        statusBadge = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Izin Sakit</span>`;
                    } else {
                        statusBadge = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">Izin Keperluan</span>`;
                    }
                } else if (statusId === 5) {
                    statusBadge = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">Alpha</span>`;
                } else {
                    statusBadge = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">${attdStatus.name || 'Dijadwalkan'}</span>`;
                }

                let notificationIcon = '';
                if (statusId === 5) { // Jika statusnya Alpha
                    if (item.is_notification_sent) {
                        notificationIcon = `<span class="ml-2 text-green-500" title="Notifikasi Alpha sudah dikirim"><i class="fas fa-check-circle"></i></span>`;
                    } else {
                        notificationIcon = `
                            <form action="/admin/attendance/notify-alpha/${scheduleId}" method="POST" class="inline ajax-form">
                                @csrf
                                <button type="submit" class="bg-transparent border-none p-0 ml-1.5 text-red-600 hover:text-red-700 cursor-pointer" title="Kirim Notifikasi Alpha via WhatsApp"><i class="fab fa-whatsapp text-sm"></i></button>
                            </form>
                        `;
                    }
                } else if (statusId === 3) { // Jika statusnya Izin
                    if (item.is_notification_sent) {
                        notificationIcon = `<span class="ml-2 text-green-500" title="Notifikasi Izin sudah dikirim"><i class="fas fa-check-circle"></i></span>`;
                    } else {
                        notificationIcon = `
                            <form action="/admin/attendance/notify-permit/${scheduleId}" method="POST" class="inline ajax-form">
                                @csrf
                                <button type="submit" class="bg-transparent border-none p-0 ml-1.5 text-emerald-600 hover:text-emerald-700 cursor-pointer" title="Kirim Notifikasi Izin via WhatsApp"><i class="fab fa-whatsapp text-sm"></i></button>
                            </form>
                        `;
                    }
                }

                return `<td rowspan="${rowspan}" class="px-4 py-2 border-t text-center align-middle whitespace-nowrap">${statusBadge}${notificationIcon}</td>`;
            }

            // FUNGSI UTAMA LOAD DATA DENGAN ERROR HANDLING
            function loadData(params = {}) {
                const defaultParams = {
                    name: searchQuery,
                    date: selectedDate,
                    page: 1,
                    per_page: 15,
                    office_id: selectedOffice,
                    shift_id: selectedShift,
                    status_id: selectedStatus
                };

                const requestParams = { ...defaultParams, ...params };
                currentPage = requestParams.page;

                var tbody = $('#report-tbody');
                var loadingSpinner = $('#loading-spinner');
                var noDataMessage = $('#no-data-message');
                tbody.empty();
                noDataMessage.addClass('hidden');
                loadingSpinner.removeClass('hidden');

                $.ajax({
                    url: '/api/attendance',
                    method: 'GET',
                    dataType: 'json',
                    data: requestParams,
                    success: function (data) {
                        loadingSpinner.addClass('hidden');

                        // Validasi response structure
                        if (!data || !data.data || !Array.isArray(data.data.listAttendance)) {
                            console.error('Invalid response structure:', data);
                            noDataMessage.removeClass('hidden').text('Struktur data tidak valid dari server.');
                            return;
                        }

                        // Update total counts dengan validasi
                        if (data.data.attendanceTotal) {
                            $('#total_presence').text(data.data.attendanceTotal.attendanceTotal || 0);
                            $('#total_permit').text(data.data.attendanceTotal.permitTotal || 0);
                            $('#total_absence').text(data.data.attendanceTotal.absenceTotal || 0);
                        }

                        if (data.data.listAttendance.length === 0) {
                            noDataMessage.removeClass('hidden').text('Tidak ada data yang ditemukan.');
                            updatePagination(data.meta || {});
                            return;
                        }

                        noDataMessage.addClass('hidden');

                        // Iterate over each attendance entry dengan error handling
                        try {
                            $.each(data.data.listAttendance, function (index, item) {
                                // Validasi item structure
                                if (!item) return;

                                const attdStatus = item.attd_status || {};
                                const attendance = item.attendance || null;
                                const adjustableArray = item.adjustableAttendance || [];
                                const adjustableCount = adjustableArray.length;

                                let rowspan = 0;
                                if (attendance) rowspan++;
                                rowspan += adjustableCount;

                                // Generate status icons untuk main row
                                var statusIconsMain = getStatusIconsMain(item);

                                // Handle attendance type
                                const attendanceType = generateAttendanceType(item, rowspan);

                                // Escape activity log
                                const activityLog = escapeHtml(item.log_activity?.activity || 'Belum membuat Log Activity');

                                let allRowsHtml = '';

                                // Generate the main row
                                if (attendance != null) {
                                    allRowsHtml += `
                                            <tr class="border-b border-gray-300 text-center">
                                                <td rowspan="${rowspan}" class="border-t text-center align-middle">${(currentPage - 1) * requestParams.per_page + index + 1}</td>
                                                <td rowspan="${rowspan}" class="border-t text-center align-middle text-left"><a href="/admin/presence/detail/${item.intern_id}" class="hover:underline cursor-pointer text-blue-600 hover:text-blue-800">${escapeHtml(item.name || '')}</a></td>
                                                <td class="p-2 border-t text-center align-middle">${generateAttendanceCell('default', 'Jam Masuk', 'start_time', attendance, item.name)}</td>
                                                <td class="border-t text-center align-middle">${generateAttendanceCell('default', 'Jam Pulang', 'end_time', attendance, item.name)}</td>
                                                <td class="border-t text-center align-middle">${generateAttendanceCell('default', 'Mulai Istirahat', 'break_time', attendance, item.name)}</td>
                                                <td class="border-t text-center align-middle">${generateAttendanceCell('default', 'Selesai Istirahat', 'back_time', attendance, item.name)}</td>
                                                <td class="border-t text-center align-middle">${attendance.total_time || '00:00'}</td>
                                                <td class="border-t text-center align-middle ${(attendance.target_time?.value || '').startsWith('-') ? 'text-red-600' : ((attendance.target_time?.value || '') === '00:00' ? 'text-gray-700' : 'text-green-600')}">
                                                    ${attendance.target_time?.value || '00:00'}
                                                </td>
                                                ${attendanceType}
                                                <td rowspan="${rowspan}" class="border-t text-center align-middle"><button onclick="openModalActivity(this)" data-activity="${activityLog}" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded text-sm">Cek Disini</button></td>
                                                <td class="border-t text-center align-middle">${statusIconsMain}</td>
                                            </tr>
                                        `;
                                }

                                // Generate adjustable rows
                                if (adjustableCount > 0) {
                                    $.each(adjustableArray, function (adjIndex, adjustable) {
                                        if (!adjustable) return;

                                        var bgColor = adjustable.is_approved === 1 ? 'bg-blue-100' : 'bg-red-100';
                                        const isFirstRow = !attendance && adjIndex === 0;

                                        allRowsHtml += `
                                                <tr class="border-b border-gray-300 text-center ${bgColor}">
                                                    ${isFirstRow ? `<td rowspan="${rowspan}" class="border-t text-center align-middle">${(currentPage - 1) * requestParams.per_page + index + 1}</td><td rowspan="${rowspan}" class="border-t text-center align-middle text-left"><a href="/admin/presence/detail/${item.intern_id}" class="hover:underline cursor-pointer text-blue-600 hover:text-blue-800">${escapeHtml(item.name || '')}</a></td>` : ""}
                                                    <td class="border-t text-center align-middle">${generateAttendanceCell('adst', 'Ganti Jam Masuk', 'start_time', adjustable, item.name)}</td>
                                                    <td class="border-t text-center align-middle">${generateAttendanceCell('adst', 'Ganti Jam Pulang', 'end_time', adjustable, item.name)}</td>
                                                    <td class="border-t text-center align-middle">${generateAttendanceCell('adst', 'Mulai Istirahat', 'break_time', adjustable, item.name)}</td>
                                                    <td class="border-t text-center align-middle">${generateAttendanceCell('adst', 'Selesai Istirahat', 'back_time', adjustable, item.name)}</td>
                                                    <td class="border-t text-center align-middle">${adjustable.total_time || '00:00'}</td>
                                                    <td class="border-t text-center align-middle ${(adjustable.target_time?.value || '').startsWith('-') ? 'text-red-600' : ((adjustable.target_time?.value || '') === '00:00' ? 'text-gray-700' : 'text-green-600')}">${adjustable.target_time?.value || '00:00'}</td>
                                                    ${isFirstRow ? attendanceType : ''}
                                                    ${isFirstRow ? `<td rowspan="${rowspan}" class="border-t text-center align-middle"><button onclick="openModalActivity(this)" data-activity="${activityLog}" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded text-sm">Cek Disini</button></td>` : ''}
                                                    <td class="border-t text-center align-middle">${getStatusIconsAdjustable(adjustable)}</td>
                                                </tr>
                                            `;
                                    });
                                }
                                tbody.append(allRowsHtml);
                            });
                        } catch (error) {
                            console.error('Error rendering table data:', error);
                            noDataMessage.removeClass('hidden').text('Terjadi kesalahan saat menampilkan data.');
                        }

                        updatePagination(data.meta || {});
                    },
                    error: function (xhr, status, error) {
                        console.error("Error occurred: ", error);
                        loadingSpinner.addClass('hidden');
                        noDataMessage.removeClass('hidden').text('Terjadi kesalahan saat memuat data.');
                    }
                });
            }

            // FUNGSI PAGINATION, FILTER, DAN EVENT LISTENER
            $('#search-name').on('input', function () {
                searchQuery = $(this).val();
                loadData({ page: 1 });
            });

            $('#filter-status, #filter-office, #filter-shift, #date-target').on('change', function () {
                selectedStatus = $('#filter-status').val();
                selectedOffice = $('#filter-office').val();
                selectedShift = $('#filter-shift').val();
                selectedDate = $('#date-target').val();
                $('.bulk-notify-date').val(selectedDate);
                $('#yes-all-form').attr('action', `./log-activity/yesall/${selectedDate}`);
                loadData({ page: 1 });
            });

            function updatePagination(meta) {
                const paginationContainer = $('#prev-page').parent();
                if (!meta || meta.total === 0) {
                    paginationContainer.addClass('hidden');
                    return;
                }
                paginationContainer.removeClass('hidden');

                totalPages = meta.last_page || 1;
                $('#prev-page').prop('disabled', currentPage <= 1);
                $('#next-page').prop('disabled', currentPage >= totalPages);

                $('#page-numbers').empty();
                for (let i = 1; i <= totalPages; i++) {
                    const pageNumber = $('<button></button>')
                        .text(i)
                        .addClass('cursor-pointer px-4 py-2 rounded-md border')
                        .toggleClass('bg-blue-600 text-white', i === currentPage)
                        .toggleClass('text-blue-600 bg-white hover:bg-gray-100', i !== currentPage)
                        .on('click', function () {
                            if (i !== currentPage) {
                                loadData({ page: i });
                            }
                        });
                    $('#page-numbers').append(pageNumber);
                }
            }

            $('#prev-page').on('click', function () {
                if (currentPage > 1) {
                    loadData({ page: currentPage - 1 });
                }
            });

            $('#next-page').on('click', function () {
                if (currentPage < totalPages) {
                    loadData({ page: currentPage + 1 });
                }
            });



            // Remote Permit Form Handler
            $('#remotePermitForm').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const submitBtn = $('#remotePermitSubmitBtn');
                const origHtml = submitBtn.html();

                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function (res) {
                        closeRemotePermitModal();
                        showNotification('success', res.message || 'Izin remote berhasil diaktifkan!');
                        if (typeof loadData === 'function') {
                            loadData({ page: currentPage });
                        }
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Gagal mengaktifkan izin remote.';
                        showNotification('error', msg);
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).html(origHtml);
                    }
                });
            });

            // Make loadData and currentPage available globally
            window.loadData = loadData;
            window.currentPage = currentPage;
            window.openModalActivity = openModalActivity;
            window.closeModalActivity = closeModalActivity;

            loadData(); // Initial load
        });

        // FUNGSI MODAL UNTUK AKTIVASI IZIN REMOTE
        function openRemotePermitModal(internId, internName) {
            document.getElementById('remotePermitInternId').value = internId;
            document.getElementById('remotePermitInternName').textContent = internName || 'Pemagang';
            
            // Default select leave
            const leaveRadio = document.querySelector('input[name="type"][value="leave"]');
            if (leaveRadio) {
                leaveRadio.checked = true;
                updateRemotePermitType('leave');
            }
            document.getElementById('remotePermitDescription').value = '';
            document.getElementById('remotePermitModal').classList.remove('hidden');
        }

        function closeRemotePermitModal() {
            document.getElementById('remotePermitModal').classList.add('hidden');
        }

        function updateRemotePermitType(type) {
            const types = ['leave', 'prayer', 'toilet'];
            types.forEach(t => {
                const label = document.getElementById('remoteTypeLabel-' + t);
                if (label) {
                    if (t === type) {
                        label.className = 'relative flex flex-col items-center justify-center p-3 border-2 rounded-xl cursor-pointer transition border-amber-500 bg-amber-50/50';
                    } else {
                        label.className = 'relative flex flex-col items-center justify-center p-3 border-2 rounded-xl cursor-pointer transition border-slate-200 bg-white';
                    }
                }
            });
        }

        document.getElementById('remotePermitModal')?.addEventListener('click', function (event) {
            if (event.target === this) {
                closeRemotePermitModal();
            }
        });

        // FUNGSI MODAL UNTUK EDIT PRESENSI
        function openModal(tipe, jam, field, currentValue, id, date, name) {
            document.getElementById('field').value = field;
            document.getElementById('tipe').value = tipe;
            document.getElementById('attendanceId').value = id;
            document.getElementById('field1').textContent = jam;

            // Format waktu untuk input time (HH:mm:ss)
            let timeValue = currentValue || '';
            if (timeValue && timeValue.includes(':')) {
                // Pastikan format waktu lengkap dengan detik
                const timeParts = timeValue.split(':');
                if (timeParts.length === 2) {
                    timeValue += ':00'; // Tambahkan detik jika tidak ada
                }
            }

            document.getElementById('time').value = timeValue;
            document.getElementById('date-display1').textContent = date || '---';
            document.getElementById('name-display').textContent = name || '---';
            const form = document.getElementById('editForm');
            form.action = `/admin/attendance/updateTime/${id}`;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
        document.getElementById('editModal').addEventListener('click', function (event) {
            if (event.target === this) {
                closeModal();
            }
        });

        // Timeout untuk session message dari server-side rendering
        document.addEventListener('DOMContentLoaded', function () {
            const message = document.getElementById('success-message');
            if (message) {
                setTimeout(() => {
                    message.style.opacity = 0;
                    setTimeout(() => message.remove(), 600);
                }, 3000);
            }
        });

        // Download PDF
        document.getElementById('download-pdf').addEventListener('click', function () {
            const date = document.getElementById('date-target').value;
            const attdStatus = document.getElementById('filter-status').value;
            const shiftId = document.getElementById('filter-shift').value;
            const officeId = document.getElementById('filter-office').value;
            const name = document.getElementById('search-name').value;
            let url = `{{ route('download-report.pdf') }}`;
            const params = new URLSearchParams();
            if (date) params.append('date', date);
            if (name) params.append('name', name);
            if (attdStatus) params.append('attd_status_id', attdStatus);
            if (shiftId) params.append('shift_id', shiftId);
            if (officeId) params.append('office_id', officeId);
            if (params.toString()) {
                url += `?${params.toString()}`;
            }
            window.location.href = url;
        });
    </script>

    <style>
        .loader {
            border-top-color: #3498db;
            -webkit-animation: spinner 1.5s linear infinite;
            animation: spinner 1.5s linear infinite;
        }

        @-webkit-keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spinner {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection
