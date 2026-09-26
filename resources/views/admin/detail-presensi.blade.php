@extends('layouts.main')

@section('title', 'Detail Presensi')

@section('contents')
    <!-- Main Content -->
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">
        <div class="bg-gradient-to-r from-slate-800 to-slate-700 text-white rounded-2xl shadow-xs p-5 border border-slate-700/50">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 items-center">
                <!-- Left Column: User Profile -->
                <div class="flex items-center space-x-3 sm:space-x-4 min-w-0">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-2xl bg-slate-700/80 border border-slate-600 flex items-center justify-center text-2xl sm:text-3xl text-slate-300 shrink-0">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <div id="fullname" class="text-lg sm:text-xl md:text-2xl font-bold text-white truncate">{{ $intern_data->full_name }}</div>
                        <div class="text-xs text-slate-300 font-mono mt-0.5">NIP : <span id="nip" class="font-bold text-white">{{ $intern_data->NIP }}</span></div>
                    </div>
                </div>

                <!-- Right Column: Status Filter -->
                <div class="flex flex-col space-y-1.5">
                    <label for="search-student" class="text-xs font-semibold text-slate-200">Filter Status Kehadiran</label>
                    <div class="flex items-center bg-white rounded-xl border border-slate-300 overflow-hidden shadow-2xs">
                        <div class="p-2 sm:p-2.5 text-gray-400 bg-gray-50 border-r border-gray-200">
                            <i class="fa-solid fa-search text-xs"></i>
                        </div>
                        <select id="search-student"
                            class="p-2 text-xs text-gray-800 font-medium focus:outline-none focus:ring-0 w-full bg-transparent">
                            <option value="" disabled selected>-- Semua Kehadiran --</option>
                            @foreach ($attd_statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 1: Target & Jam Kerja (Left) & Total Presensi Ditandai (Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">

            <!-- Card 1: Periode Magang & Jam Kerja -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-xs p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                                <i class="fa-regular fa-calendar-days"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">Periode Magang</h3>
                                <p id="intern-period-text" class="text-xs text-blue-600 font-semibold mt-0.5">
                                    {{ $intern_target['start_period'] ? \Carbon\Carbon::parse($intern_target['start_period'])->format('d-m-Y') : '-' }} s/d {{ $intern_target['end_period'] ? \Carbon\Carbon::parse($intern_target['end_period'])->format('d-m-Y') : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="font-semibold text-gray-700">Total Jam Kerja</span>
                            <span class="px-3 py-1 text-xs font-bold text-white bg-emerald-600 rounded-lg shadow-2xs">
                                {{ $intern_target['total_work_time'] ?? '0j 0m' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="font-semibold text-gray-700">Total Masuk</span>
                            <span class="px-3 py-1 text-xs font-bold text-white bg-gray-700 rounded-lg shadow-2xs">
                                {{ $intern_target['admission_total'] ?? '0 Hari' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="font-semibold text-gray-700">Target</span>
                            <span class="px-3 py-1 text-xs font-bold text-white bg-emerald-600 rounded-lg shadow-2xs">
                                {{ $intern_target['target_time'] ?? '0j 0m' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="font-semibold text-gray-700">Sisa</span>
                            <span class="px-3 py-1 text-xs font-bold text-white bg-gray-700 rounded-lg shadow-2xs">
                                {{ $intern_target['time_target_remaining'] ?? '0j 0m' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="font-semibold text-gray-700">Total Ganti Jam</span>
                            <span id="total-ganti-jam-value" class="px-3 py-1 text-xs font-bold text-white bg-gray-700 rounded-lg shadow-2xs">
                                {{ $intern_target['change_time_total'] ?? '0j 0m' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="font-semibold text-gray-700">Sisa Setelah Diskon</span>
                            <span id="sisa-setelah-diskon-value" class="px-3 py-1 text-xs font-bold text-white bg-gray-700 rounded-lg shadow-2xs">
                                {{ $intern_target['remaing_time_after_discount'] ?? '0j 0m' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Form Diskon Jam -->
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <form id="discount-form" action="{{ route('discount-time.store') }}" method="POST">
                        @csrf
                        <input name="schedule_id" type="hidden" value="{{ $schedule_data['schedule']['id'] ?? '' }}">
                        <input type="hidden" name="cumulative_target_minutes" value="{{ $target_in_minutes ?? 0 }}">
                        
                        <div class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                            <label for="discount-input" class="text-xs font-semibold text-gray-700 whitespace-nowrap">Diskon Jam (menit):</label>
                            <div class="flex items-center gap-2 flex-1 w-full sm:w-auto">
                                <input type="number" placeholder="Menit" name="discount_time" id="discount-input" value="{{ $intern_target['discount_time'] ?? 0 }}"
                                    class="flex-1 py-1.5 px-3 text-xs border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                                <button type="submit" id="save-discount-btn"
                                    class="px-4 py-1.5 text-xs font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-2xs cursor-pointer shrink-0">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card 2: Total Presensi (ditandai) -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-xs p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                                <i class="fa-solid fa-clipboard-check"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">Total Presensi (Ditandai)</h3>
                                <p class="text-[11px] text-gray-500">Akumulasi tanda kehadiran siswa</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 text-xs">
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="font-semibold text-gray-700">Masuk</span>
                                <span class="px-2.5 py-0.5 text-xs font-bold text-white bg-gray-700 rounded-lg">{{ $intern_target['admit_total'] ?? '0' }}x</span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="font-semibold text-gray-700">Istirahat Keluar</span>
                                <span class="px-2.5 py-0.5 text-xs font-bold text-white bg-gray-700 rounded-lg">{{ $intern_target['break_start_total'] ?? '0' }}x</span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="font-semibold text-gray-700">Izin Keluar</span>
                                <span class="px-2.5 py-0.5 text-xs font-bold text-white bg-gray-700 rounded-lg">{{ $intern_target['permit_total'] ?? '0' }}x</span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="font-semibold text-gray-700">Ganti Jam Total</span>
                                <span class="px-2.5 py-0.5 text-xs font-bold text-white bg-gray-700 rounded-lg">{{ $intern_target['adjustable_total'] ?? '0' }}x</span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="font-semibold text-gray-700">Pulang</span>
                                <span class="px-2.5 py-0.5 text-xs font-bold text-white bg-gray-700 rounded-lg">{{ $intern_target['back_total'] ?? '0' }}x</span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="font-semibold text-gray-700">Istirahat Kembali</span>
                                <span class="px-2.5 py-0.5 text-xs font-bold text-white bg-gray-700 rounded-lg">{{ $intern_target['break_back_total'] ?? '0' }}x</span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="font-semibold text-gray-700">Izin Kembali</span>
                                <span class="px-2.5 py-0.5 text-xs font-bold text-white bg-gray-700 rounded-lg">{{ $intern_target['permit_back_total'] ?? '0' }}x</span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="font-semibold text-gray-700">Ganti Jam Diterima</span>
                                <span class="px-2.5 py-0.5 text-xs font-bold text-white bg-gray-700 rounded-lg">{{ $intern_target['accepted_adjustable_total'] ?? '0' }}x</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Row 2: Form Catatan / Pesan Mentor (Ditaruh di bawah periode magang & total presensi) -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-xs p-5 mt-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Catatan / Pesan Mentor untuk Siswa</h3>
                        <p class="text-[11px] text-gray-500">Pesan ini akan tampil langsung di dashboard pemagang</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    <i class="fa-solid fa-user-tie text-[10px]"></i> Catatan Mentor
                </span>
            </div>
            <form id="note-form" class="space-y-3" action="{{ route('intern.storeNote', ['id' => $intern_data->id]) }}" method="POST">
                @csrf
                <div>
                    <textarea id="text-input" name="attention_message" rows="3"
                        class="p-3 w-full border border-gray-300 rounded-xl text-xs md:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                        placeholder="Tuliskan catatan khusus atau arahan tugas untuk siswa ini...">{{ $notes }}</textarea>
                </div>
                <div class="flex justify-end items-center gap-2">
                    <button type="button" id="btn-cancel-note"
                        class="bg-gray-100 text-gray-700 px-4 py-2 text-xs font-semibold rounded-xl hover:bg-gray-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2 text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Catatan</span>
                    </button>
                </div>
            </form>
        </div>

        @if (session('status'))
            <div id="success-message"
                class="mt-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl relative transition-opacity duration-500 shadow-xs"
                role="alert">
                <strong class="font-bold">Sukses!</strong>
                <span class="block sm:inline ml-1">{{ session('status') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="removeMessage()">
                    <svg class="fill-current h-5 w-5 text-green-600" role="button" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20">
                        <title>Close</title>
                        <path
                            d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                    </svg>
                </span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- List Kehadiran Table Container -->
        <div class="overflow-x-auto bg-white shadow-xs rounded-2xl border border-gray-200 mt-4 min-h-[350px] pb-28">
            <table class="min-w-full text-xs text-center border-collapse">
                <thead>
                    <tr class="bg-slate-800 text-white text-[11px] uppercase tracking-wider font-semibold">
                        <th rowspan="2" class="py-2.5 px-3 border-r border-slate-700">No</th>
                        <th rowspan="2" class="py-2.5 px-3 text-left border-r border-slate-700">Tanggal</th>
                        <th colspan="2" class="py-2 px-3 border-r border-slate-700">Jam Kerja</th>
                        <th colspan="2" class="py-2 px-3 border-r border-slate-700">Jam Istirahat</th>
                        <th colspan="2" class="py-2 px-3 border-r border-slate-700">Total Jam Kerja</th>
                        <th rowspan="2" class="py-2.5 px-3 border-r border-slate-700">Kehadiran</th>
                        <th rowspan="2" class="py-2.5 px-3 border-r border-slate-700">Lokasi</th>
                        <th rowspan="2" class="py-2.5 px-3 border-r border-slate-700">Log Activity</th>
                        <th rowspan="2" class="py-2.5 px-3">Aksi</th>
                    </tr>
                    <tr class="bg-slate-700 text-slate-200 text-[10px] uppercase tracking-wider font-medium">
                        <th class="py-1.5 px-2.5 border-r border-slate-600">Masuk</th>
                        <th class="py-1.5 px-2.5 border-r border-slate-600">Pulang</th>
                        <th class="py-1.5 px-2.5 border-r border-slate-600">Mulai</th>
                        <th class="py-1.5 px-2.5 border-r border-slate-600">Selesai</th>
                        <th class="py-1.5 px-2.5 border-r border-slate-600">Total Jam</th>
                        <th class="py-1.5 px-2.5 border-r border-slate-600">(+/-)</th>
                    </tr>
                </thead>

                <tbody id="report-tbody" class="divide-y divide-gray-100 text-xs text-gray-800">
                </tbody>
            </table>

            <div id="loading-spinner" class="flex justify-center items-center py-8 hidden">
                <div class="loader ease-linear rounded-full border-4 border-t-4 border-blue-500 h-8 w-8 animate-spin"></div>
            </div>

            <div id="no-data-message" class="text-center py-8 text-xs text-gray-500 hidden">
                Tidak ada data kehadiran yang ditemukan.
            </div>
        </div>

        <!-- Pagination Controls -->
        <div class="mt-4 flex flex-wrap justify-between items-center gap-3 bg-white p-3 rounded-2xl border border-gray-200 shadow-xs">
            <button id="prev-page"
                class="cursor-pointer bg-white text-gray-700 font-semibold text-xs px-3.5 py-1.5 rounded-xl border border-gray-300 hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <i class="fa-solid fa-chevron-left mr-1"></i> Sebelumnya
            </button>

            <!-- Page numbers -->
            <div id="page-numbers" class="flex flex-wrap items-center gap-1.5"></div>

            <button id="next-page" class="cursor-pointer bg-white text-gray-700 font-semibold text-xs px-3.5 py-1.5 rounded-xl border border-gray-300 hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
                Selanjutnya <i class="fa-solid fa-chevron-right ml-1"></i>
            </button>
        </div>

        <!-- Action Export Buttons -->
        <div class="mt-4 flex justify-end items-center gap-3">
            <button id="download-pdf"
                class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-xs transition-colors flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Download PDF</span>
            </button>

            <a href="/admin/presence/log-activity/{{ $intern_id }}" target="_blank"
                class="px-4 py-2 text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 rounded-xl shadow-xs transition-colors flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-download"></i>
                <span>Download Log Activity</span>
            </a>
        </div>
    </main>

    <!-- Modal Edit Presensi -->
    <div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 p-4 hidden">
        <div class="bg-white rounded-2xl overflow-hidden shadow-2xl max-w-sm w-full mx-auto max-h-[90vh] overflow-y-auto">
            <div class="p-5 sm:p-6">
                <h2 class="text-lg font-semibold mb-3 text-center text-gray-800">Edit Presensi</h2>
                <p class="bg-red-50 text-red-700 border border-red-200 p-3 rounded-lg mb-4 text-xs sm:text-sm">
                    Anda akan merubah presensi <span id="field1" class="font-bold"></span> tanggal
                    <span id="date-display" class="font-bold">---</span> atas nama:
                    <span id="name-display" class="font-bold">---</span>
                </p>
                <form id="editForm" action="" method="POST">
                    @csrf
                    <input type="hidden" name="field" id="field">
                    <input type="hidden" name="tipe" id="tipe">
                    <input type="hidden" name="attendance_id" id="attendanceId">
                    <input type="hidden" name="current_page" id="current-page" value="1">
                    <!-- Tambahkan input hidden untuk halaman -->
                    <div class="mb-4">
                        <label for="time" class="block text-gray-700 text-sm font-semibold mb-2">Waktu:</label>
                        <input type="time" name="time" id="time" step="1"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="message" class="block text-gray-700 text-sm font-semibold mb-2">Keterangan:</label>
                        <input type="text" name="message" id="message"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm">
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

    <!-- Modal Status Kehadiran -->
    <div id="modal-presence" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 p-4 hidden">
        <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-2xl w-full max-w-md relative max-h-[90vh] overflow-y-auto">
            <h2 class="text-xl sm:text-2xl font-semibold mb-4 text-center text-gray-800">Status Kehadiran</h2>
            <form id="permit-form" method="POST" action="">
                @csrf
                <input type="hidden" id="id" name="id" />
                <input type="hidden" id="id-schedule" name="id-schedule" />
                <input type="hidden" id="id-shift" name="id-shift" />
                <div class="mb-4">
                    <label for="keterangan" class="block text-gray-700 text-sm font-medium mb-1.5">Keterangan Ketidakhadiran<span
                            class="text-red-500">*</span></label>
                    <textarea id="keterangan" name="keterangan" rows="4"
                        class="w-full h-20 border border-gray-300 rounded-xl p-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="link-google-drive" class="block text-gray-700 text-sm font-medium mb-1.5">Link Google Drive<span
                            class="text-red-500">*</span></label>
                    <input type="url" id="link-google-drive" name="link-google-drive"
                        class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required />
                </div>
                <div class="mb-4">
                    <label for="kategori-izin" class="block text-gray-700 text-sm font-medium mb-1.5">Kategori Izin<span
                            class="text-red-500">*</span></label>
                    <select id="kategori-izin" name="kategori-izin" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                        @foreach ($listPermitCategory as $category)
                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="jam-option" class="block text-gray-700 text-sm font-medium mb-1.5">Status<span
                            class="text-red-500">*</span></label>
                    <select id="jam-option" name="jam-option" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="1">Tidak Ganti Jam</option>
                        <option value="2">Ganti Jam</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('modal-presence').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition text-sm">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition text-sm">Simpan</button>
                </div>
            </form>
            <button id="close-modal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>
    </div>

    <!-- Modal Log Activity -->
    <div id="modal" class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-2xs hidden z-50 p-4">
        <div class="bg-white p-5 sm:p-6 rounded-2xl w-full max-w-lg shadow-2xl border border-slate-100 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                        <i class="fa-regular fa-file-lines"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800">Log Activity</h2>
                        <p class="text-xs text-gray-500">Tanggal: <span id="log-activity-date-text" class="font-semibold text-gray-700">---</span></p>
                    </div>
                </div>
                <button type="button" onclick="closeModalActivity()" class="text-gray-400 hover:text-gray-600 cursor-pointer p-1 rounded-lg hover:bg-gray-100 transition">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>
            <form id="log-activity-form" action="" method="POST" class="flex-1 flex flex-col overflow-y-auto">
                @csrf
                <!-- Textarea -->
                <div class="mb-4 flex-1">
                    <input type="hidden" id="attdId" name="attd_id" />
                    <input type="hidden" id="activity-date" name="date" />
                    <label for="log-activity" class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Isi Aktivitas Harian</label>
                    <textarea id="log-activity" name="activity" required
                        placeholder="Tuliskan aktivitas atau kegiatan pemagang di sini..."
                        class="p-3 w-full border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm leading-relaxed"
                        rows="5"></textarea>
                </div>

                <!-- Tombol -->
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100 shrink-0">
                    <button type="button" onclick="closeModalActivity()"
                        class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors cursor-pointer text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 shadow-xs transition-colors cursor-pointer text-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan Log</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Update Shift -->
    <div id="modalUpdateShift" class="fixed inset-0 z-50 bg-gray-900 bg-opacity-50 flex justify-center items-center p-4 hidden">
        <div class="bg-white rounded-2xl w-full max-w-md sm:max-w-lg p-5 sm:p-6 max-h-[90vh] overflow-y-auto shadow-2xl">
            <!-- Modal Header -->
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h3 class="text-base sm:text-lg font-bold text-gray-900">Update Shift tanggal <span id="dateShiftText" class="text-blue-600"></span></h3>
                <button class="text-gray-400 hover:text-gray-600 p-1 rounded-lg" onclick="closeModalShift()">&times;</button>
            </div>

            <form action="{{ route('update.Shift') }}" method="POST">
                @csrf

                <!-- Modal Body -->
                <div class="mt-4 space-y-3">
                    <input id="scheduleId" name="scheduleId" type="hidden" value="" />
                    <!-- Shift Saat Ini -->
                    <div>
                        <label for="currentShift" class="block text-xs font-semibold text-gray-700">Shift Saat Ini</label>
                        <select id="currentShift" name="currentShift"
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-xl shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm">
                            @foreach ($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ $shift->id == old('currentShift') ? 'selected' : '' }}>
                                    {{ $shift->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="work_type" class="block text-xs font-semibold text-gray-700">Pilih tipe kerja</label>
                        <select id="work_type" name="work_type"
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-xl shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm">
                            <option value="wfo"> WFO </option>
                            <option value="wfh"> WFH </option>
                        </select>
                    </div>

                    <div>
                        <label for="schedule_type" class="block text-xs font-semibold text-gray-700">Tipe Jadwal</label>
                        <select id="schedule_type" name="schedule_type"
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-xl shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm">
                            <option value="1"> Ganti Jam</option>
                            <option value="0"> Jadwal Biasa </option>
                        </select>
                    </div>

                    <div>
                        <label for="approved_change_time" class="block text-xs font-semibold text-gray-700">Terima Ganti jam</label>
                        <select id="approved_change_time" name="approved_change_time"
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-xl shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm">
                            <option value="1"> Ya </option>
                            <option value="0"> Tidak </option>
                        </select>
                    </div>

                    <div>
                        <label for="back_first" class="block text-xs font-semibold text-gray-700">Pulang lebih Awal</label>
                        <select id="back_first" name="back_first"
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-xl shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm">
                            <option value="1"> Ya </option>
                            <option value="0"> Tidak </option>
                        </select>
                    </div>

                    <div>
                        <label for="break_first" class="block text-xs font-semibold text-gray-700">Istirahat lebih Awal</label>
                        <select id="break_first" name="break_first"
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-xl shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm">
                            <option value="1"> Ya </option>
                            <option value="0"> Tidak </option>
                        </select>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end gap-2 mt-6 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeModalShift()"
                        class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition text-xs sm:text-sm">
                        Batal
                    </button>
                    <button class="bg-red-600 text-white hover:bg-red-700 px-5 py-2 rounded-xl font-medium transition text-xs sm:text-sm shadow-xs" type="submit">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
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

        // Global variables untuk pagination
        var pageNow = 1;
        const pageSize = 15;
        var status_id = null;
        var totalItems = 0;
        var pageLimit;

        // Jika URL mengandung ?page=... maka gunakan itu sebagai pageNow
        (function () {
            try {
                const params = new URLSearchParams(window.location.search);
                const p = parseInt(params.get('page'));
                if (!isNaN(p) && p > 0) {
                    pageNow = p;
                }
            } catch (e) {
                // ignore
            }
        })();

        function openModal(tipe, Jam, field, currentValue, id, date, name, message, currentPage = null) {
            document.getElementById('tipe').value = tipe;
            document.getElementById('field').value = field;
            document.getElementById('field1').textContent = Jam;
            document.getElementById('attendanceId').value = id;

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
            document.getElementById('date-display').textContent = date || '---';
            document.getElementById('name-display').textContent = name || '---';
            document.getElementById('message').value = message || '';

            // Set current page ke form
            document.getElementById('current-page').value = currentPage || pageNow;

            const form = document.getElementById('editForm');
            form.action = `/admin/attendance/updateTime/${id}?page=${currentPage || pageNow}`;

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

        function openModalActivity(btnOrActivity, id, attdId, date) {
            const modal = document.getElementById('modal');

            const textarea = document.getElementById('log-activity');
            const form = document.getElementById('log-activity-form');
            const attd_id = document.getElementById('attdId');
            const activityDate = document.getElementById('activity-date');

            let rawActivity = '';
            if (typeof btnOrActivity === 'string') {
                rawActivity = (btnOrActivity === 'Belum membuat Log Activity') ? '' : btnOrActivity;
            } else if (btnOrActivity && btnOrActivity.getAttribute) {
                try {
                    rawActivity = decodeURIComponent(btnOrActivity.getAttribute('data-activity') || '');
                } catch (e) {
                    rawActivity = btnOrActivity.getAttribute('data-activity') || '';
                }
            }

            form.action = `/admin/log-activity/update-isi/${id || 'kosong'}`;
            attd_id.value = attdId;
            textarea.value = rawActivity;
            activityDate.value = date;

            const dateTextEl = document.getElementById('log-activity-date-text');
            if (dateTextEl) {
                dateTextEl.textContent = date || '---';
            }

            modal.classList.remove('hidden');
        }

        function closeModalActivity() {
            const modal = document.getElementById('modal');

            modal.classList.add('hidden');
        }

        document.getElementById('modal').addEventListener('click', function (event) {
            if (event.target === this) {
                closeModalActivity();
            }
        });

        $(document).ready(function () {

            $('#discount-form').on('submit', function(e) {
    // 1. Mencegah form dari reload halaman
    e.preventDefault();

    const form = $(this);
    const button = $('#save-discount-btn');
    const originalButtonText = button.text();

    // 2. Nonaktifkan tombol selama proses
    button.prop('disabled', true).text('Menyimpan...');

    // 3. Kirim data via AJAX
    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize(), // Mengambil data form dengan cara yang lebih sederhana
        
        // Fungsi ini berjalan jika request berhasil
        success: function(response) {
            if (response.success) {
                // 4. INI BAGIAN KUNCI PERBAIKANNYA
                // Langsung perbarui teks di halaman dengan data akurat dari server.
                $('#sisa-aktual-value').text(response.intern_target.remaing_time_after_calculate);
                $('#total-ganti-jam-value').text(response.intern_target.change_time_total);
                $('#sisa-setelah-diskon-value').text(response.intern_target.remaing_time_after_discount);

                // Update juga nilai input diskon itu sendiri agar konsisten
                $('#discount-input').val(response.intern_target.discount_time);

                // Tampilkan notifikasi sukses menggunakan fungsi yang sudah Anda punya
                showNotification(response.message, 'success');
            } else {
                // Tampilkan notifikasi jika ada pesan kegagalan dari server
                showNotification(response.message || 'Gagal menyimpan data.', 'error');
            }
        },
        
        // Fungsi ini berjalan jika request gagal (error server)
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan pada server.';
            showNotification(errorMsg, 'error');
        },
        
        // Fungsi ini berjalan setelah request selesai (baik berhasil maupun gagal)
        complete: function() {
            // 5. Kembalikan tombol ke keadaan semula
            button.prop('disabled', false).text(originalButtonText);
        }
    });
});

// Auto refresh untuk form catatan
// Fungsi untuk reload intern target data
function reloadInternTarget() {
    $.ajax({
        url: `/api/intern-target/${internId}`,
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if(data.intern_target) {
                const target = data.intern_target;
                
                // Update periode
                $('#intern-period-text').text(
                    `${target.start_period || ''} s/d ${target.end_period || ''}`
                );
                
                // Update nilai-nilai dalam card
                updateCardValue('Total Jam Kerja', target.total_work_time);
                updateCardValue('Total Masuk', target.admission_total);
                updateCardValue('Target', target.target_time);
                updateCardValue('Sisa', target.time_target_remaining);
                updateCardValue('Total Ganti Jam', target.change_time_total);
                updateCardValue('Sisa Setelah Diskon', target.remaing_time_after_discount);
                
                $('#total-ganti-jam-value').text(target.change_time_total || '0j 0m');
                $('#sisa-setelah-diskon-value').text(target.remaing_time_after_discount || '0j 0m');
                
                // Update discount_time input value
                if(target.discount_time !== undefined) {
                    $('#discount-input').val(target.discount_time);
                }
                
                // Update Total Presensi (ditandai)
                updatePresenceCount('Masuk', target.admit_total);
                updatePresenceCount('Istirahat Keluar', target.break_start_total);
                updatePresenceCount('Izin Keluar', target.permit_total);
                updatePresenceCount('Ganti Jam Total', target.adjustable_total);
                updatePresenceCount('Pulang', target.back_total);
                updatePresenceCount('Istirahat Kembali', target.break_back_total);
                updatePresenceCount('Izin Kembali', target.permit_back_total);
                updatePresenceCount('Ganti Jam Diterima', target.accepted_adjustable_total);
            }
        },
        error: function(xhr) {
            console.error('Failed to reload intern target data');
        }
    });
}

function updateCardValue(label, value) {
    $('.font-semibold').each(function() {
        if($(this).text().trim().toLowerCase() === label.trim().toLowerCase()) {
            $(this).siblings('span').text(value || '0j 0m');
            return false; 
        }
    });
}

function updatePresenceCount(label, value) {
    $('.font-semibold').each(function() {
        if($(this).text().trim().toLowerCase() === label.trim().toLowerCase()) {
            $(this).siblings('span').text(value ? value + 'x' : '0x');
            return false; 
        }
    });
}

function showNotification(message, type = 'success') {
    // Hapus notifikasi lama jika ada
    $('.notification-toast').remove();
    
    const bgColor = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 
                                         'bg-red-100 border-red-400 text-red-700';
    
    const notification = $(`
        <div class="notification-toast fixed top-20 right-4 ${bgColor} px-4 py-3 rounded shadow-lg z-50 transition-opacity duration-500">
            <div class="flex items-center">
                <span class="font-bold mr-2">${type === 'success' ? 'Sukses!' : 'Error!'}</span>
                <span>${message}</span>
                <button onclick="$(this).parent().parent().remove()" class="ml-4">
                    <svg class="fill-current h-4 w-4" viewBox="0 0 20 20">
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </button>
            </div>
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto remove setelah 3 detik
    setTimeout(() => {
        notification.fadeOut(500, () => notification.remove());
    }, 3000);
}

// Handle tombol Batal pada form catatan
$('#btn-cancel-note').on('click', function() {
    // Kosongkan textarea
    $('#text-input').val('');
});

// Optional: Auto-save draft catatan ke localStorage
$('#text-input').on('input', function() {
    const value = $(this).val();
    localStorage.setItem('draft_note_intern_' + internId, value);
});

// Load draft saat page load
$(document).ready(function() {
    const draft = localStorage.getItem('draft_note_intern_' + internId);
    if(draft && !$('#text-input').val()) {
        $('#text-input').val(draft);
    }
});

// Clear draft setelah berhasil submit
$(document).on('ajaxSuccess', function(event, xhr, settings) {
    if(settings.url && settings.url.includes('storeNote')) {
        localStorage.removeItem('draft_note_intern_' + internId);
    }
});
            /**
             * Refactored loadData function to handle multiple adjustableAttendance entries.
             * @param {number} intern_id - The ID of the intern.
             * @param {number} page - Current page number.
             * @param {number} per_page - Number of entries per page.
             * @param {number} status_id - Status filter ID.
             */
            function loadData(intern_id, page, per_page, status_id) {
                var tbody = $('#report-tbody');
                var loadingSpinner = $('#loading-spinner');
                var noDataMessage = $('#no-data-message');
                tbody.empty();
                loadingSpinner.removeClass('hidden');
                noDataMessage.addClass('hidden');

                $.ajax({
                    url: '/api/attendance/detail',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        intern_id: intern_id,
                        page: page,
                        per_page: per_page,
                        status_id: status_id
                    },
                    success: function (response) {
                        loadingSpinner.addClass('hidden');

                        if (!response.status) {
                            console.error("Error: ", response.message);
                            noDataMessage.removeClass('hidden').text(response.message);
                            return;
                        }

                        pageNow = response.meta.current_page;
                        pageLimit = response.meta.last_page;
                        var total = response.meta.total;

                        $('#total_presence').text(total);
                        $('#page-info').text("Page " + pageNow);
                        tbody.empty();

                        // Manage pagination buttons
                        $('#prev-page').prop('disabled', pageNow === 1);
                        $('#next-page').prop('disabled', pageNow === pageLimit);

                        if (response.data.length === 0) {
                            noDataMessage.removeClass('hidden').text('Tidak ada data yang ditemukan.');
                            return;
                        }
                        noDataMessage.addClass('hidden');

                        // Iterate over each attendance entry
                        $.each(response.data, function (index, item) {
                            const schedule = item.schedule;
                            const attendance = item.attendance;
                            const adjustableAttendances = item.adjustableAttendance || [];
                            const logActivity = item.log_activity;
                            const attdStatus = item.attd_status;

                            var status_id_main = logActivity?.status_id ?? 0;
                            var log_activity_id = logActivity?.id ?? 0;

                            // Generate status icons for the main row
                            var statusIconsMain = getStatusIconsMain(status_id_main,
                                log_activity_id, attendance, schedule, attdStatus);

                            // Calculate rowspan based on the number of adjustable attendances
                            var rowspan = adjustableAttendances.length > 0 ?
                                `rowspan="${1 + adjustableAttendances.length}"` : '';

                            // Generate attendance type cell
                            var attendanceType = generateAttendanceType(attdStatus, schedule,
                                item.permit_data, rowspan);

                            // Generate maps cell
                            var mapsValue = generateMapsCell(schedule, attendance,
                                adjustableAttendances.length);

                            // Calculate the serial number
                            var nomorUrut = (pageNow - 1) * per_page + (index + 1);

                            // Generate the main row
                            var mainRow = `
                                        <tr class="border-b border-gray-100 text-center hover:bg-slate-50/50 transition-colors">
                                            <td ${rowspan} class="py-2.5 px-3 border-t border-gray-100 text-center align-middle font-medium text-gray-500 text-xs">${nomorUrut}</td>
                                            <td ${rowspan} class="py-2.5 px-3 border-t border-gray-100 text-left align-middle whitespace-nowrap text-xs">
                                                <span class="font-semibold text-gray-800">${schedule.date || '---'}</span>
                                                <button class="ml-1.5 text-gray-400 hover:text-blue-600 transition cursor-pointer p-0.5" title="Pengaturan Shift" onclick="openModalShift('${schedule.id}', '${schedule.shift_id}', '${schedule.date}', '${schedule.work_type}', ${schedule.is_change_schedule_approved}, ${schedule.isChangeSchedule}, ${schedule.isBackFirst}, ${schedule.is_break_first})">
                                                    <i class="fa-solid fa-gear text-xs"></i>
                                                </button>
                                            </td>
                                            <td class="py-2.5 px-2 border-t border-gray-100 text-center align-middle font-mono text-[11px]">
                                                ${generateAttendanceCell('default', 'Jam Mulai', 'start_time', attendance.start_time, attendance.id, schedule.date, '{{ $intern_data->full_name }}', attendance.start_time_message)}
                                            </td>
                                            <td class="py-2.5 px-2 border-t border-gray-100 text-center align-middle font-mono text-[11px]">
                                                ${generateAttendanceCell('default', 'Jam Pulang', 'end_time', attendance.end_time, attendance.id, schedule.date, '{{ $intern_data->full_name }}', attendance.end_time_message)}
                                            </td>
                                            <td class="py-2.5 px-2 border-t border-gray-100 text-center align-middle font-mono text-[11px]">
                                                ${generateAttendanceCell('default', 'Mulai Istirahat', 'break_time', attendance.break_time, attendance.id, schedule.date, '{{ $intern_data->full_name }}', attendance.break_time_message)}
                                            </td>
                                            <td class="py-2.5 px-2 border-t border-gray-100 text-center align-middle font-mono text-[11px]">
                                                ${generateAttendanceCell('default', 'Selesai Istirahat', 'back_time', attendance.back_time, attendance.id, schedule.date, '{{ $intern_data->full_name }}', attendance.back_time_message)}
                                            </td>
                                            <td class="py-2.5 px-2 border-t border-gray-100 text-center align-middle font-mono text-[11px] font-semibold text-gray-800">${attendance.total_min_format}</td>
                                            <td class="py-2.5 px-2 border-t border-gray-100 text-center align-middle font-mono text-[11px] font-bold ${attendance.target_time >= 0 ? 'text-emerald-600' : 'text-rose-600'}">
                                                ${attendance.target_time_format}
                                                ${(attendance.mandatory_replace_minutes || 0) > 0 ? '<span class="text-[10px] text-rose-500 block font-normal">+ ' + Math.floor(attendance.mandatory_replace_minutes / 60) + 'j ' + (attendance.mandatory_replace_minutes % 60) + 'm (wajib ganti)</span>' : ''}
                                            </td>
                                            ${attendanceType}
                                            ${mapsValue}
                                            <td ${rowspan} class="py-2.5 px-2.5 border-t border-gray-100 text-center align-middle">
                                                <button type="button"
                                                        data-activity="${encodeURIComponent(logActivity?.activity || '')}"
                                                        onclick="openModalActivity(this, '${logActivity?.id || 'kosong'}', '${schedule.id}', '${schedule.date}')"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors cursor-pointer">
                                                    <i class="fa-regular fa-file-lines text-[10px]"></i>
                                                    <span>Cek Log</span>
                                                </button>
                                            </td>
                                            <td class="py-2.5 px-2.5 border-t border-gray-100 text-center align-middle">
                                                ${statusIconsMain}
                                            </td>
                                        </tr>
                                    `;
                            tbody.append(mainRow);

                            // Iterate over each adjustableAttendance entry and create additional rows
                            $.each(adjustableAttendances, function (adjIndex, adjustable) {
                                // Determine background color based on is_approved
                                var bgColor = adjustable.is_approved === 1 ?
                                    'bg-blue-50/70' : 'bg-rose-50/70';

                                // Generate status icons for adjustable attendance
                                var statusIconsAdjustable = getStatusIconsAdjustable(
                                    adjustable.is_approved, adjustable.id);

                                // Generate the adjustable attendance row
                                var adjustableRow = `
                                            <tr class="border-b border-gray-100 text-center ${bgColor}">
                                                <td class="py-2 px-2 border-t border-gray-100 text-center align-middle font-mono text-[11px]">
                                                    ${generateAttendanceCell('adst', 'Jam Mulai', 'start_time', adjustable.start_time, adjustable.id, schedule.date, '{{ $intern_data->full_name }}', adjustable.start_time_message)}
                                                </td>
                                                <td class="py-2 px-2 border-t border-gray-100 text-center align-middle font-mono text-[11px]">
                                                    ${generateAttendanceCell('adst', 'Jam Pulang', 'end_time', adjustable.end_time, adjustable.id, schedule.date, '{{ $intern_data->full_name }}', adjustable.end_time_message)}
                                                </td>
                                                <td class="py-2 px-2 border-t border-gray-100 text-center align-middle font-mono text-[11px]">
                                                    ${generateAttendanceCell('adst', 'Mulai Istirahat', 'break_time', adjustable.break_time, adjustable.id, schedule.date, '{{ $intern_data->full_name }}', adjustable.break_time_message)}
                                                </td>
                                                <td class="py-2 px-2 border-t border-gray-100 text-center align-middle font-mono text-[11px]">
                                                    ${generateAttendanceCell('adst', 'Selesai Istirahat', 'back_time', adjustable.back_time, adjustable.id, schedule.date, '{{ $intern_data->full_name }}', adjustable.back_time_message)}
                                                </td>
                                                <td class="py-2 px-2 border-t border-gray-100 text-center align-middle font-mono text-[11px] font-semibold text-gray-800">${adjustable.total_min_format}</td>
                                                <td class="py-2 px-2 border-t border-gray-100 text-center align-middle font-mono text-[11px] font-bold ${adjustable.target_time >= 0 ? 'text-emerald-600' : 'text-rose-600'}">
                                                    ${adjustable.target_time >= 0 ? '+' : '-'} ${adjustable.target_time}
                                                </td>
                                                <td class="py-2 px-2.5 border-t border-gray-100 text-center align-middle">
                                                    ${statusIconsAdjustable}
                                                </td>
                                            </tr>
                                        `;
                                tbody.append(adjustableRow);
                            });
                        });

                        // Update pagination controls
                        updatePagination();

                        // update URL param tanpa reload supaya refresh tetap di pageNow
                        try {
                            const newUrl = new URL(window.location.href);
                            newUrl.searchParams.set('page', pageNow);
                            // replaceState agar tidak menambah history entry tiap reload data
                            window.history.replaceState({}, '', newUrl);
                        } catch (e) {
                            // ignore
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Terjadi kesalahan: ", error);
                        loadingSpinner.addClass('hidden');
                        noDataMessage.removeClass('hidden').text('Terjadi kesalahan saat memuat data.');
                    }
                });
            }

            /**
             * Generates an attendance cell with optional message tooltip.
             */
            function generateAttendanceCell(tipe, label, field, time, scheduleId, date, name, message) {
                const formattedTime = formatTimeWithSeconds(time);
                var currentPage = pageNow;

                if (!time || formattedTime === '---') {
                    return `<span class="text-gray-400 font-mono text-[11px]">-</span>`;
                }

                return `
                    <span onclick="openModal('${tipe}','${label}', '${field}', '${time}', '${scheduleId}', '${date}', '${name}', '${(message || '').replace(/'/g, "\\'")}', ${currentPage})"
                        class="cursor-pointer text-blue-600 hover:text-blue-800 hover:underline font-mono text-[11px] font-medium" title="Klik untuk edit waktu">
                        ${formattedTime}
                    </span>
                `;
            }

            /**
             * Generates status dropdown for the main attendance row based on status_id.
             * Always displays all 5 core actions (Log Activity approval/rejection, Attendance status toggle, Presence reset, Delete presence).
             */
            function getStatusIconsMain(status_id, logActivityId, attendance, schedule, attdStatus) {
                const hasLogActivity = logActivityId && logActivityId !== 'kosong' && logActivityId !== 0 && logActivityId !== '0';
                const isHadir = (attdStatus && attdStatus.id == 2);
                const toggleStatusText = isHadir ? 'Ubah Jadi Tidak Hadir' : 'Ubah Jadi Hadir';
                const attdId = attendance ? attendance.id : null;

                let approveLogItem = '';
                let rejectLogItem = '';
                if (hasLogActivity) {
                    approveLogItem = `
                        <form action="../../log-activity/update-status/${logActivityId}" method="POST" class="m-0">
                            <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'}">
                            <input type="hidden" name="status" value="2">
                            <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors text-left cursor-pointer ${status_id == 2 ? 'bg-emerald-50/70 font-semibold text-emerald-800' : ''}">
                                <i class="fa-solid fa-circle-check text-emerald-600 w-4 text-center"></i>
                                <span>Setujui Log Activity ${status_id == 2 ? '(Disetujui)' : ''}</span>
                            </button>
                        </form>
                    `;
                    rejectLogItem = `
                        <form action="../../log-activity/update-status/${logActivityId}" method="POST" class="m-0">
                            <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'}">
                            <input type="hidden" name="status" value="3">
                            <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-700 hover:bg-rose-50 hover:text-rose-700 transition-colors text-left cursor-pointer ${status_id == 3 ? 'bg-rose-50/70 font-semibold text-rose-800' : ''}">
                                <i class="fa-solid fa-circle-xmark text-rose-600 w-4 text-center"></i>
                                <span>Tolak Log Activity ${status_id == 3 ? '(Ditolak)' : ''}</span>
                            </button>
                        </form>
                    `;
                } else {
                    approveLogItem = `
                        <div class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-400 cursor-not-allowed opacity-60 select-none" title="Belum ada Log Activity">
                            <i class="fa-solid fa-circle-check text-gray-300 w-4 text-center"></i>
                            <span>Setujui Log Activity</span>
                        </div>
                    `;
                    rejectLogItem = `
                        <div class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-400 cursor-not-allowed opacity-60 select-none" title="Belum ada Log Activity">
                            <i class="fa-solid fa-circle-xmark text-gray-300 w-4 text-center"></i>
                            <span>Tolak Log Activity</span>
                        </div>
                    `;
                }

                let resetAttdItem = '';
                if (attdId) {
                    resetAttdItem = `
                        <form onsubmit="return confirm('Apa kamu yakin ingin mereset presensi tanggal ${schedule?.date || ''}?');" action="../../attendance/reset/${attdId}" method="POST" class="m-0">
                            <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'}">
                            <input type="hidden" name="status" value="3">
                            <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-amber-700 hover:bg-amber-50 hover:text-amber-800 transition-colors text-left cursor-pointer">
                                <i class="fa-solid fa-rotate-left text-amber-600 w-4 text-center"></i>
                                <span>Reset Presensi</span>
                            </button>
                        </form>
                    `;
                } else {
                    resetAttdItem = `
                        <div class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-400 cursor-not-allowed opacity-60 select-none" title="Belum ada data presensi">
                            <i class="fa-solid fa-rotate-left text-gray-300 w-4 text-center"></i>
                            <span>Reset Presensi</span>
                        </div>
                    `;
                }

                return `
                    <div class="relative inline-block text-left dropdown-action-container">
                        <button type="button" onclick="window.toggleActionDropdown(event, this)" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-2xs hover:bg-gray-50 hover:border-gray-400 transition-all cursor-pointer">
                            <span>Aksi</span>
                            <i class="fa-solid fa-chevron-down text-[9px] text-gray-400"></i>
                        </button>
                        <div class="dropdown-menu-list hidden absolute right-0 top-full mt-1.5 w-56 bg-white rounded-2xl shadow-xl border border-slate-200 py-1.5 z-50 text-left divide-y divide-gray-100">
                            <div class="py-1">
                                <div class="px-3 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Log Activity</div>
                                ${approveLogItem}
                                ${rejectLogItem}
                            </div>
                            <div class="py-1">
                                <div class="px-3 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kehadiran</div>
                                <form onsubmit="return confirm('Apa kamu yakin ingin merubah status kehadiran tanggal ${schedule?.date || ''} menjadi ${isHadir ? 'Tidak Hadir' : 'Hadir'}?');" action="../../attendance/updatestatusattd/${schedule?.id || ''}" method="POST" class="m-0">
                                    <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'}">
                                    <input type="hidden" name="status" value="3">
                                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors text-left cursor-pointer">
                                        <i class="fa-solid fa-user-pen text-blue-600 w-4 text-center"></i>
                                        <span>${toggleStatusText}</span>
                                    </button>
                                </form>
                                ${resetAttdItem}
                            </div>
                            <div class="py-1">
                                <form onsubmit="return confirm('Apa kamu yakin ingin menghapus jadwal dan presensi tanggal ${schedule?.date || ''}?');" action="../../attendance/delete/${schedule?.id || ''}" method="POST" class="m-0">
                                    <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'}">
                                    <input type="hidden" name="status" value="3">
                                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-rose-600 hover:bg-rose-50 hover:text-rose-800 transition-colors text-left cursor-pointer font-medium">
                                        <i class="fa-solid fa-trash text-rose-600 w-4 text-center"></i>
                                        <span>Hapus Presensi</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                `;
            }

            /**
             * Generates status dropdown for adjustable attendance rows based on is_approved.
             */
            function getStatusIconsAdjustable(is_approved, adjustableId) {
                if (is_approved != null) {
                    return `
                        <div class="relative inline-block text-left dropdown-action-container">
                            <button type="button" onclick="window.toggleActionDropdown(event, this)" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-2xs hover:bg-gray-50 hover:border-gray-400 transition-all cursor-pointer">
                                <span>Aksi</span>
                                <i class="fa-solid fa-chevron-down text-[9px] text-gray-400"></i>
                            </button>
                            <div class="dropdown-menu-list hidden absolute right-0 top-full mt-1.5 w-52 bg-white rounded-2xl shadow-xl border border-slate-200 py-1.5 z-50 text-left divide-y divide-gray-100">
                                <div class="py-1">
                                    <div class="px-3 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Ganti Jam</div>
                                    <form action="../../adjustable-attendance/update-status/${adjustableId}" method="POST" class="m-0">
                                        <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'}">
                                        <input type="hidden" name="is_approved" value="1">
                                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors text-left cursor-pointer ${is_approved == 1 ? 'bg-emerald-50/70 font-semibold text-emerald-800' : ''}">
                                            <i class="fa-solid fa-circle-check text-emerald-600 w-4 text-center"></i>
                                            <span>Setujui Ganti Jam ${is_approved == 1 ? '(Disetujui)' : ''}</span>
                                        </button>
                                    </form>
                                    <form action="../../adjustable-attendance/update-status/${adjustableId}" method="POST" class="m-0">
                                        <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'}">
                                        <input type="hidden" name="is_approved" value="2">
                                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-700 hover:bg-rose-50 hover:text-rose-700 transition-colors text-left cursor-pointer ${is_approved == 2 ? 'bg-rose-50/70 font-semibold text-rose-800' : ''}">
                                            <i class="fa-solid fa-circle-xmark text-rose-600 w-4 text-center"></i>
                                            <span>Tolak Ganti Jam ${is_approved == 2 ? '(Ditolak)' : ''}</span>
                                        </button>
                                    </form>
                                </div>
                                <div class="py-1">
                                    <form action="../../adjustable-attendance/restore/${adjustableId}" method="POST" class="m-0">
                                        <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'}">
                                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors text-left cursor-pointer">
                                            <i class="fa-solid fa-rotate-left text-blue-600 w-4 text-center"></i>
                                            <span>Restore Ganti Jam</span>
                                        </button>
                                    </form>
                                    <form action="../../adjustable-attendance/delete/${adjustableId}" method="POST" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ganti jam ini?')">
                                        <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'}">
                                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-rose-600 hover:bg-rose-50 hover:text-rose-800 transition-colors text-left cursor-pointer font-medium">
                                            <i class="fa-solid fa-trash text-rose-600 w-4 text-center"></i>
                                            <span>Hapus Ganti Jam</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    return `-`;
                }
            }

            /**
             * Generates the attendance type cell based on attdStatus.id.
             */
            function generateAttendanceType(attdStatus, schedule, permitData, rowspan) {
                var cellContent = '';
                if (attdStatus.id === 1 || attdStatus.id === 5) {
                    const badgeColor = attdStatus.id === 5 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-slate-100 text-slate-700 border-slate-200';
                    cellContent = `
                        <button class="open-modal-presence inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold border ${badgeColor} hover:opacity-80 transition cursor-pointer"
                                data-schedule-id="${schedule.id ?? ''}"
                                data-shift-id="${schedule.shift_id}">
                            <span>${attdStatus.name}</span>
                            <i class="fa-solid fa-circle-info text-[10px]"></i>
                        </button>
                    `;
                } else if (attdStatus.id === 3) {
                    const permitCategoryId = permitData?.permit_category_id || '';
                    const description = (permitData?.description || '').toLowerCase();
                    const isSakit = (permitCategoryId == 1 || permitCategoryId == 2 || description.includes('sakit'));
                    const statusName = isSakit ? 'Izin Sakit' : (attdStatus.name || 'Izin');
                    const badgeColor = isSakit ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-blue-50 text-blue-700 border-blue-200';

                    cellContent = `
                        <button class="open-modal-presence inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold border ${badgeColor} hover:opacity-80 transition cursor-pointer"
                                data-schedule-id="${schedule.id}"
                                data-shift-id="${schedule.shift_id}"
                                data-permit-id="${permitData?.id || ''}"
                                data-description="${permitData?.description || 'Tidak ada keterangan'}"
                                data-proof-url="${permitData?.proof_url || 'Tidak ada link'}"
                                data-permit-category-id="${permitData?.permit_category_id || ''}"
                                data-ischange-schedule="${schedule.isChangeSchedule || ''}">
                            <span>${statusName}</span>
                            <i class="fa-solid fa-circle-info text-[10px]"></i>
                        </button>
                    `;
                } else if (attdStatus.id === 2) {
                    cellContent = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Hadir</span>`;
                } else {
                    cellContent = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-700 border border-gray-200">${attdStatus.name}</span>`;
                }

                return `
                    <td ${rowspan} class="py-2.5 px-2 border-t border-gray-100 text-center align-middle" data-attendance-status="${attdStatus.id}">
                        ${cellContent}
                    </td>
                `;
            }

            /**
             * Generates the maps cell based on attendance location data.
             */
            function generateMapsCell(schedule, attendance, adjustableCount) {
                if (attendance && attendance.latitude_start && attendance.longitude_start) {
                    const params = new URLSearchParams({
                        office_id: schedule.office_id || '',
                        intern_id: internId || '{{ $intern_id }}',
                        user_name: '{{ $intern_data->full_name }}',
                        date: schedule.date || '',
                        lat_start: attendance.latitude_start,
                        long_start: attendance.longitude_start,
                        lat_end: attendance.latitude_end || '',
                        long_end: attendance.longitude_end || '',
                        page: pageNow
                    });

                    return `
                        <td ${adjustableCount > 0 ? `rowspan="${1 + adjustableCount}"` : ''} class="py-2.5 px-2 border-t border-gray-100 text-center align-middle">
                            <a href="{{ Route('location.user.view') }}?${params.toString()}"
                            class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition-colors cursor-pointer"
                            title="Cek Lokasi & Radius Presensi">
                                <i class="fa-solid fa-map-location-dot text-[10px]"></i>
                                <span>Cek</span>
                            </a>
                        </td>
                    `;
                } else {
                    return `
                        <td ${adjustableCount > 0 ? `rowspan="${1 + adjustableCount}"` : ''} class="py-2.5 px-2 border-t border-gray-100 text-center align-middle text-gray-400 font-mono text-[11px]">-</td>
                    `;
                }
            }

            // Global dropdown toggle handler
            window.toggleActionDropdown = function(event, btn) {
                if (!btn && event && event.nodeType) {
                    btn = event;
                    event = window.event;
                }
                if (event) {
                    if (typeof event.stopPropagation === 'function') event.stopPropagation();
                    if (typeof event.preventDefault === 'function') event.preventDefault();
                }

                if (!btn) return;

                const container = btn.closest('.dropdown-action-container') || btn.closest('.dropdown-container');
                if (!container) return;
                const menu = container.querySelector('.dropdown-menu-list') || container.querySelector('.dropdown-menu');
                if (!menu) return;

                const isCurrentlyOpen = !menu.classList.contains('hidden');

                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu-list, .dropdown-menu').forEach(m => m.classList.add('hidden'));
                document.querySelectorAll('tr, .dropdown-action-container, .dropdown-container').forEach(el => {
                    el.style.zIndex = '';
                });

                if (!isCurrentlyOpen) {
                    // Raise z-index of tr & container so menu is always above other rows
                    const tr = btn.closest('tr');
                    if (tr) {
                        tr.style.zIndex = '50';
                        tr.style.position = 'relative';
                    }
                    container.style.zIndex = '60';

                    // Smart positioning (flip up if near bottom of viewport)
                    const rect = btn.getBoundingClientRect();
                    const spaceBelow = window.innerHeight - rect.bottom;
                    const dropdownHeight = 220;

                    if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                        menu.classList.remove('top-full', 'mt-1.5');
                        menu.classList.add('bottom-full', 'mb-1.5');
                    } else {
                        menu.classList.remove('bottom-full', 'mb-1.5');
                        menu.classList.add('top-full', 'mt-1.5');
                    }

                    menu.classList.remove('hidden');
                }
            };

            // Global click outside to close dropdown
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown-action-container') && !e.target.closest('.dropdown-container')) {
                    document.querySelectorAll('.dropdown-menu-list, .dropdown-menu').forEach(m => m.classList.add('hidden'));
                    document.querySelectorAll('tr, .dropdown-action-container, .dropdown-container').forEach(el => {
                        el.style.zIndex = '';
                    });
                }
            });

            var internId = "{{ $intern_id }}";

            loadData(internId, pageNow, pageSize, status_id);
            updatePagination();

            $('#next-page').on('click', function () {
                if (pageNow < pageLimit) {
                    pageNow += 1;
                    loadData(internId, pageNow, pageSize, status_id);
                    updatePagination();
                }
            });

            // When previous page is clicked
            $('#prev-page').on('click', function () {
                if (pageNow > 1) {
                    pageNow -= 1;
                    loadData(internId, pageNow, pageSize, status_id);
                    updatePagination();
                }
            });

            // When status filter changes
            document.getElementById('search-student').addEventListener('change', function () {
                status_id = this.value;
                pageNow = 1;
                loadData(internId, pageNow, pageSize, status_id);
                updatePagination();
                // update url to page=1 when filter changes
                try {
                    const newUrl = new URL(window.location.href);
                    newUrl.searchParams.set('page', pageNow);
                    window.history.replaceState({}, '', newUrl);
                } catch (e) { }
            });

            // Function to update pagination UI
            function updatePagination() {
                $('#prev-page').prop('disabled', pageNow === 1);
                $('#next-page').prop('disabled', pageNow === pageLimit);

                $('#page-numbers').empty();

                for (let i = 1; i <= pageLimit; i++) {
                    const pageNumber = $('<button></button>')
                        .text(i)
                        .addClass('cursor-pointer px-4 py-2 rounded-md border')
                        .toggleClass('bg-blue-600 text-white', i === pageNow)
                        .toggleClass('text-blue-600 bg-white hover:bg-gray-100', i !== pageNow)
                        .on('click', function () {
                            pageNow = i;
                            loadData(internId, pageNow, pageSize, status_id);
                            updatePagination();
                        });

                    $('#page-numbers').append(pageNumber);
                }

                // Update the page info display
                $('#page-info').text('Page ' + pageNow + ' of ' + pageLimit);
            }

            // ===========================
            // Intercept all POST forms inside main and submit via AJAX
            // supaya tetap berada di halaman pagination sekarang (pageNow)
            // ===========================
            $('main').on('submit', 'form', function (e) {
                const form = this;
                const method = (form.method || 'GET').toUpperCase();

                // hanya handle POST/PATCH/PUT/DELETE yang ingin mencegah reload full page
                if (['POST', 'PATCH', 'PUT', 'DELETE'].includes(method)) {
                    e.preventDefault();

                    const url = form.action;
                    const fd = new FormData(form);

                    // sertakan current page
                    fd.set('current_page', pageNow);

                    // ambil token CSRF (meta atau input)
                    const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const tokenInput = form.querySelector('input[name="_token"]')?.value;
                    const csrfToken = metaToken || tokenInput || '';

                    // kirim AJAX
                    $.ajax({
                        url: url,
                        method: method,
                        headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {},
                        data: fd,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            // tutup modal jika terbuka
                            $('#editModal, #modal, #modal-presence, #modalUpdateShift').addClass('hidden');

                            // reload data pada page sekarang (loadData akan update URL)
                            loadData(internId, pageNow, pageSize, status_id);
                            updatePagination();

                            // tampilkan pesan sukses singkat jika response ada pesan
                            if (response && (response.message || response.status)) {
                                const msg = response.message || response.status || 'Berhasil';
                                const $toast = $(`<div class="fixed right-4 top-20 bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded z-50">${msg}</div>`);
                                $('body').append($toast);
                                setTimeout(() => $toast.fadeOut(300, () => $toast.remove()), 2500);
                            }
                        },
                        error: function (xhr) {
                            // jika error, tetap reload data di page sekarang untuk sinkronisasi
                            loadData(internId, pageNow, pageSize, status_id);
                            updatePagination();

                            // tampilkan error sederhana
                            const $err = $(`<div class="fixed right-4 top-20 bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded z-50">Terjadi kesalahan</div>`);
                            $('body').append($err);
                            setTimeout(() => $err.fadeOut(300, () => $err.remove()), 3000);
                        }
                    });
                }
                // jika bukan POST/PATCH/PUT/DELETE biarkan submit default (mis. download)
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('modal-presence');
            const closeModalButton = document.getElementById('close-modal');
            const idInput = document.getElementById('id');
            const idShiftInput = document.getElementById('id-shift');
            const idScheduleInput = document.getElementById('id-schedule');
            const keteranganTextarea = document.getElementById('keterangan');
            const linkGoogleDriveInput = document.getElementById('link-google-drive');
            const kategoriIzinSelect = document.getElementById('kategori-izin');
            const isChangeSchedule = document.getElementById('jam-option');
            const permitForm = document.getElementById('permit-form');

            if (!modal) {
                console.error('Modal element not found');
                return;
            }

            if (!closeModalButton) {
                console.error('Close modal button not found');
                return;
            }

            if (!idInput || !keteranganTextarea || !linkGoogleDriveInput || !kategoriIzinSelect || !permitForm) {
                console.error('Required input elements not found');
                return;
            }

            // Function to open the modal
            function openModalPresence(scheduleId, shiftId, permitId, description, proofUrl, permitCategoryId,
                isChangeScheduleId) {
                idScheduleInput.value = scheduleId;
                idShiftInput.value = shiftId;
                idInput.value = permitId;
                isChangeSchedule.value = isChangeScheduleId;
                keteranganTextarea.value = description;
                linkGoogleDriveInput.value = proofUrl;
                kategoriIzinSelect.value = permitCategoryId;

                if (description) {
                    permitForm.action = "{{ route('attendance.updatePermitPresence') }}";
                } else {
                    permitForm.action = "{{ route('attendance.addPermitPresenceadmin') }}";
                }

                modal.classList.remove('hidden');
            }

            function closeModalPresence() {
                modal.classList.add('hidden'); // Hide the modal
            }

            document.addEventListener('click', (event) => {
                if (event.target && event.target.classList.contains('open-modal-presence')) {
                    const ScheduleId = event.target.getAttribute('data-schedule-id');
                    const ShiftId = event.target.getAttribute('data-shift-id');
                    const PermitId = event.target.getAttribute('data-permit-id');
                    const description = event.target.getAttribute('data-description');
                    const proofUrl = event.target.getAttribute('data-proof-url');
                    const permitCategoryId = event.target.getAttribute('data-permit-category-id');
                    const isChangeScheduleId = event.target.getAttribute('data-ischange-schedule');

                    openModalPresence(ScheduleId, ShiftId, PermitId, description, proofUrl,
                        permitCategoryId, isChangeScheduleId);
                }
            });

            closeModalButton.addEventListener('click', closeModalPresence);

            window.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModalPresence();
                }
            });
        });

        function openModalShift(scheduleId, shiftId, date, workType, changeTimeStatus, isChangeSchedule, isBackFirst,
            isBreakFirst) {
            document.getElementById('modalUpdateShift').classList.remove('hidden');

            document.getElementById('scheduleId').value = scheduleId;
            document.getElementById('currentShift').value = shiftId;
            document.getElementById('dateShiftText').innerText = date;
            document.getElementById("work_type").value = (workType || '').toLowerCase();
            document.getElementById("schedule_type").value = isChangeSchedule;
            document.getElementById("approved_change_time").value = changeTimeStatus;
            document.getElementById("back_first").value = isBackFirst;
            document.getElementById("break_first").value = isBreakFirst;

            if (isChangeSchedule == 0) {
                document.getElementById("approved_change_time").classList.add("hidden");
                document.querySelector("label[for='approved_change_time']").classList.add("hidden");
            }
        }

        function closeModalShift() {
            document.getElementById('modalUpdateShift').classList.add('hidden');
        }

        document.getElementById('modalUpdateShift').addEventListener('click', function (event) {
            if (event.target === this) {
                closeModalShift();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const message = document.getElementById('success-message');
            if (message) {
                setTimeout(() => {
                    message.style.opacity = 0;
                    setTimeout(() => message.remove(), 600);
                }, 3000);
            }
        });

        document.getElementById('currentShift').addEventListener('change', function () {
            const selectedValue = this.value;
            const scheduleId = document.getElementById('scheduleId').value

            $.ajax({
                url: `/api/schedule/shift/update/${scheduleId}`,
                method: 'PATCH',
                dataType: 'json',
                data: {
                    shift_id: selectedValue
                },
                success: function (response) {
                    // Optional: Tampilkan notifikasi sukses
                },
                error: function (xhr, status, error) {
                    // Optional: Tampilkan notifikasi error
                }
            });
        });

        document.getElementById('download-pdf').addEventListener('click', function () {
            const attdStatus = document.getElementById('search-student').value;
            const url = attdStatus ?
                `{{ route('download-report-user.pdf', ['internId' => $intern_id]) }}?attd_status_id=${attdStatus}` :
                `{{ route('download-report-user.pdf', ['internId' => $intern_id]) }}`;

            window.location.href = url;
        });

        function removeMessage() {
            document.getElementById('success-message').remove();
        }
    </script>
@endsection
