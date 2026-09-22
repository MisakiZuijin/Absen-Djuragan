@extends('layouts.main')

@section('title', 'Detail Presensi')

@section('contents')
    @include('layouts.sidebar')

    @include('layouts.navbar')
    <!-- Main Content -->
    <main class="ml-64 mt-24 p-6 md:ml-48 lg:ml-64">
        <div class="bg-gray-700 text-white p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="flex items-center space-x-4">
                    <!-- Profile Icon -->
                    <i class="fas fa-user-circle text-7xl"></i>

                    <!-- Text Content -->
                    <div class="flex flex-col space-y-2">
                        <div id="fullname" class="text-4xl font-bold">{{ $intern_data->full_name }}</div>
                        <div class="text-lg">NIP : <span id="nip">{{ $intern_data->NIP }}</span></div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="flex flex-col space-y-2">
                    <label for="search-student" class="text-lg font-medium">Cari Status Kehadiran</label>
                    <div class="flex items-center border border-gray-300 rounded">
                        <div class="bg-white p-2 rounded-l">
                            <i class="ml-2 fa-solid fa-search text-gray-500"></i>
                        </div>

                        <select id="search-student"
                            class="p-2 pl-2 pr-2 text-gray-800 focus:outline-none focus:border-blue-500 w-full">
                            <option value="" disabled selected>--Pilih Kehadiran--</option>
                            @foreach ($attd_statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex space-x-4 mt-2">

            <!-- Second Card -->
            <div class="bg-white border border-gray-300 rounded-lg shadow-md p-3 mb-2">
                <h2 class="border-b font-semibold pb-2"> Periode Magang :
                    {{-- Menggunakan Carbon untuk format tanggal yang lebih aman --}}
                    {{ $intern_target['start_period'] ? \Carbon\Carbon::parse($intern_target['start_period'])->format('d-m-Y') : '' }} s/d {{ $intern_target['end_period'] ? \Carbon\Carbon::parse($intern_target['end_period'])->format('d-m-Y') : '' }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div class="flex items-center space-x-4">
                        <span class="font-semibold">Total Jam Kerja</span>
                        <span class="text-right flex-1">
                            <span class="px-4 py-1 text-sm font-medium text-center text-white bg-green-500 rounded-xl">
                                {{ $intern_target['total_work_time'] ?? '' }}
                            </span>
                        </span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="font-semibold">Total Masuk</span>
                        <span class="text-right flex-1">
                            <span class="px-4 py-1 text-sm font-medium text-center text-white bg-gray-700 rounded-xl">
                                {{ $intern_target['admission_total'] ?? '' }}
                            </span>
                        </span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="font-semibold">Target</span>
                        <span class="text-right flex-1">
                            <span class="px-4 py-1 text-sm font-medium text-center text-white bg-green-500 rounded-xl">
                                {{ $intern_target['target_time'] ?? '' }}
                            </span>
                        </span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="font-semibold">Sisa</span>
                        <span class="text-right flex-1">
                            <span class="px-4 py-1 text-sm font-medium text-center text-white bg-gray-700 rounded-xl">
                                {{ $intern_target['time_target_remaining'] ?? '' }}
                            </span>
                        </span>
                    </div>
                    
                    {{-- ID SUDAH DITAMBAHKAN DI SINI --}}
                    <div class="flex items-center space-x-4">
                        <span class="font-semibold">Total Ganti Jam</span>
                        <span class="text-right flex-1">
                            <span id="total-ganti-jam-value" class="px-4 py-1 text-sm font-medium text-center text-white bg-gray-700 rounded-xl">
                                {{ $intern_target['change_time_total'] ?? '' }}
                            </span>
                        </span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="font-semibold">sisa setelah diskon</span>
                        <span class="text-right flex-1">
                            <span id="sisa-setelah-diskon-value" class="px-4 py-1 text-sm font-medium text-center text-white bg-gray-700 rounded-xl">
                                {{ $intern_target['remaing_time_after_discount'] ?? '' }}
                            </span>
                        </span>
                    </div>

                    {{-- FORM SUDAH DIPERBAIKI DENGAN ID DAN INPUT TERSEMBUNYI --}}
                    <form id="discount-form" action="{{ route('discount-time.store') }}" method="POST">
                        @csrf
                        <input name="schedule_id" type="hidden" value="{{ $schedule_data['schedule']['id'] ?? '' }}">
                        
                        <!-- WAJIB: Input tersembunyi untuk mengirim target yang konsisten -->
                        <input type="hidden" name="cumulative_target_minutes" value="{{ $target_in_minutes ?? 0 }}">
                        
                        <div class="flex items-center space-x-4">
                            <input type="number" placeholder="diskon jam (Menit)" name="discount_time" id="discount-input" value="{{ $intern_target['discount_time'] ?? 0 }}"
                                class="flex-1 py-1 px-2 w-10 text-sm border rounded-xl focus:outline-none focus:ring focus:ring-gray-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                            <button type="submit" id="save-discount-btn"
                                class="px-3 py-1 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-300">
                                Simpan
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <!-- Third Card -->
            <div class="bg-white border border-gray-300 rounded-lg shadow-md p-3 mb-2">
                <div class="font-semibold border-b border-gray-500 mb-2 pb-2">Total Presensi(ditandai)</div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">Masuk</span>
                            <span class="text-right flex-1"><span
                                    class="px-4 p-1 text-sm font-medium text-center text-white bg-gray-700 rounded-xl">{{ $intern_target['admit_total'] ?? '' }}x</span></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">Istirahat Keluar</span>
                            <span class="text-right flex-1"><span
                                    class="px-4 p-1 text-sm font-medium text-center text-white bg-gray-700 rounded-xl">{{ $intern_target['break_start_total'] ?? '' }}x</span></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">Izin Keluar</span>
                            <span class="text-right flex-1"><span
                                    class="px-4 p-1 text-sm font-medium text-center text-white bg-gray-700 rounded-xl">{{ $intern_target['permit_total'] ?? '' }}x</span></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">Ganti Jam Total</span>
                            <span class="text-right flex-1"><span
                                    class="px-4 p-1 text-sm font-medium text-center text-white bg-gray-700 rounded-xl">{{ $intern_target['adjustable_total'] ?? '' }}x</span></span>
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">Pulang</span>
                            <span class="text-right flex-1"><span
                                    class="px-4 p-1 text-sm font-medium text-center text-white bg-gray-700 rounded-xl">{{ $intern_target['back_total'] ?? '' }}x</span></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">Istirahat Kembali</span>
                            <span class="text-right flex-1"><span
                                    class="px-4 p-1 text-sm font-medium text-center text-white bg-gray-700 rounded-xl">{{ $intern_target['break_back_total'] ?? '' }}x</span></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">Izin Kembali</span>
                            <span class="text-right flex-1"><span
                                    class="px-4 p-1 text-sm font-medium text-center text-white bg-gray-700 rounded-xl">{{ $intern_target['permit_back_total'] ?? '' }}x</span></span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="font-semibold">Ganti Jam Diterima</span>
                            <span class="text-right flex-1"><span
                                    class="px-4 p-1 text-sm font-medium text-center text-white bg-gray-700 rounded-xl">{{ $intern_target['accepted_adjustable_total'] ?? '' }}x</span></span>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Form -->
            <div class="bg-white border border-gray-300 rounded-lg shadow-md p-6 flex-1 max-w-md mb-2">
                <form class="space-y-4" action="{{ route('intern.storeNote', ['id' => $intern_data->id]) }}" method="POST">
                    @csrf
                    <div class="flex flex-col space-y-2">
                        <textarea id="text-input" name="attention_message" rows="4"
                            class="p-3 w-full border border-gray-800 rounded-md focus:outline-none focus:border-blue-500"
                            placeholder="Tambahkan catatan untuk User">{{ $notes }}</textarea>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="submit"
                            class="bg-gray-700 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">Tambahkan</button>
                        <button type="button" id="btn-cancel-note"
                            class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">Batal</button>
                    </div>
                </form>
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

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white shadow-md rounded-lg border-separate border-spacing-1">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-sm uppercase leading-normal text-center">

                        <th rowspan="2" class="px-4 py-2 border-r">No</th>
                        <th rowspan="2" class="px-4 py-2 text-left border-r">Tanggal</th>
                        <!-- Left-aligned for Tanggal column -->
                        <th colspan="2" class="px-4 py-2 border-r">Jam Kerja</th> <!-- Heading for Jam Kerja -->
                        <th colspan="2" class="px-4 py-2 border-r">Jam Istirahat</th>
                        <!-- Heading for Jam Istirahat -->
                        <th colspan="2" class="px-4 py-2 border-r">Total Jam Kerja</th>
                        <!-- Heading for Total Jam Kerja -->
                        <th rowspan="2" class="px-4 py-2 border-r">Kehadiran</th>
                        <th rowspan="2" class="px-4 py-2 border-r">Location</th>
                        <th rowspan="2" class="px-4 py-2 border-r">Log Activity</th>
                        <th rowspan="2" class="px-4 py-2">Aksi</th>
                    </tr>
                    <tr class="bg-gray-200 text-gray-700 text-sm uppercase leading-normal text-center">
                        <!-- Sub-headings for the merged columns -->
                        <th class="px-4 py-2 border-r border-t">Masuk</th>
                        <th class="px-4 py-2 border-r border-t">Pulang</th>
                        <th class="px-4 py-2 border-r border-t">Mulai</th>
                        <th class="px-4 py-2 border-r border-t">Selesai</th>
                        <th class="px-4 py-2 border-r border-t">Total Jam</th>
                        <th class="px-4 py-2 border-r border-t">(+/-)</th>
                    </tr>
                </thead>

                <tbody id="report-tbody" class="text-sm font-normal text-gray-800">
                </tbody>

            </table>

            <div id="loading-spinner" class="flex justify-center items-center py-4 hidden">
                <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-10 w-10"></div>
            </div>

            <div id="no-data-message" class="text-center py-4 text-gray-600 hidden">
                No data available
            </div>
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

        <!-- Buttons placed side by side -->
        <div class="mt-4 flex justify-end space-x-4">

            <button id="download-pdf"
                class="px-3 py-2 text-sm font-medium text-center text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                <i class="fa-solid fa-download"></i>
                <span>Download PDF</span>
            </button>

            <!-- Download Log Activity Button -->
            <a href="/admin/presence/log-activity/{{ $intern_id }}" target="_blank"
                class="px-3 py-2 text-sm font-medium text-center text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                <i class="fa-solid fa-download"></i>
                <span>Download Log Activity</span>
            </a>

        </div>
    </main>

    <!-- Modal Edit Presensi -->
    <div id="editModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
        <div class="bg-white rounded-lg overflow-hidden shadow-lg max-w-sm w-full">
            <div class="px-6 py-4">
                <h2 class="text-lg font-semibold mb-4 text-center">Edit Presensi</h2>
                <p class="bg-red-300 p-2 rounded mb-5">
                    Anda akan merubah presensi <span id="field1"></span> tanggal
                    <span id="date-display" class="font-semibold">---</span> atas nama:
                    <span id="name-display" class="font-semibold">---</span>
                </p>
                <form id="editForm" action="" method="POST">
                    @csrf
                    <input type="hidden" name="field" id="field">
                    <input type="hidden" name="tipe" id="tipe">
                    <input type="hidden" name="attendance_id" id="attendanceId">
                    <input type="hidden" name="current_page" id="current-page" value="1">
                    <!-- Tambahkan input hidden untuk halaman -->
                    <div class="mb-4">
                        <label for="time" class="block text-gray-700 text-sm font-bold mb-2">Waktu:</label>
                        <input type="time" name="time" id="time" step="1"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="message" class="block text-gray-700 text-sm font-bold mb-2">Keterangan:</label>
                        <input type="text" name="message" id="message"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div class="flex justify-end">
                        <button type="button" onclick="closeModal()"
                            class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 mr-2">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Status Kehadiran -->
    <div id="modal-presence" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md relative">
            <h2 class="text-2xl font-semibold mb-4 text-center">Status Kehadiran</h2>
            <form id="permit-form" method="POST" action="">
                @csrf
                <input type="hidden" id="id" name="id" />
                <input type="hidden" id="id-schedule" name="id-schedule" />
                <input type="hidden" id="id-shift" name="id-shift" />
                <div class="mb-4">
                    <label for="keterangan" class="block text-gray-700 mb-2">Keterangan Ketidakhadiran<span
                            class="text-red-500">*</span></label>
                    <textarea id="keterangan" name="keterangan" rows="4"
                        class="w-full h-20 border border-gray-300 rounded-lg p-2" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="link-google-drive" class="block text-gray-700 mb-2">Link Google Drive<span
                            class="text-red-500">*</span></label>
                    <input type="url" id="link-google-drive" name="link-google-drive"
                        class="w-full border border-gray-300 rounded-lg p-2" required />
                </div>
                <div class="mb-4">
                    <label for="kategori-izin" class="block text-gray-700 mb-2">Kategori Izin<span
                            class="text-red-500">*</span></label>
                    <select id="kategori-izin" name="kategori-izin" class="w-full border border-gray-300 rounded-lg p-2"
                        required>
                        @foreach ($listPermitCategory as $category)
                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="jam-option" class="block text-gray-700 mb-2">Status<span
                            class="text-red-500">*</span></label>
                    <select id="jam-option" name="jam-option" class="w-full border border-gray-800 rounded-lg p-2" required>
                        <option value="1">Tidak Ganti Jam</option>
                        <option value="2">Ganti Jam</option>
                    </select>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Simpan</button>
                </div>
            </form>
            <button id="close-modal" class="absolute top-2 right-2 text-gray-700 hover:text-gray-900">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Modal Log Activity -->
    <div id="modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-4 rounded-lg w-11/12 max-w-xl">
            <h2 class="text-lg font-semibold mb-4">Log Activity</h2>
            <form id="log-activity-form" action="" method="POST">
                @csrf
                <!-- Textarea -->
                <div class="mb-4">
                    <input type="hidden" id="attdId" name="attd_id" />
                    <input type="hidden" id="activity-date" name="date" />
                    <textarea id="log-activity" name="activity"
                        class="mt-1 p-2 w-full border border-gray-300 rounded-md focus:outline-none focus:ring focus:border-blue-500"
                        rows="4"></textarea>
                </div>

                <!-- Tombol Tutup -->
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Perbarui
                    </button>
                    <div class="px-1"></div>
                    <button type="button" id="close-modal-btn" onclick="closeModalActivity()"
                        class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                        Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Update Shift -->
    <div id="modalUpdateShift" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white rounded-lg w-1/3 p-6">
            <!-- Modal Header -->
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold">Update Shift tanggal <span id="dateShiftText"></span></h3>
                <button class="text-gray-600" onclick="closeModalShift()">&times;</button>
            </div>

            <form action="{{ route('update.Shift') }}" method="POST">
                @csrf

                <!-- Modal Body -->
                <div class="mt-4">
                    <input id="scheduleId" name="scheduleId" type="hidden" value="" />
                    <!-- Shift Saat Ini -->
                    <label for="currentShift" class="block text-sm font-medium text-gray-700">Shift Saat Ini</label>
                    <select id="currentShift" name="currentShift"
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @foreach ($shifts as $shift)
                            <option value="{{ $shift->id }}" {{ $shift->id == old('currentShift') ? 'selected' : '' }}>
                                {{ $shift->name }}
                            </option>
                        @endforeach
                    </select>

                    <label for="work_type" class="mt-3 block text-sm font-medium text-gray-700">Pilih tipe kerja</label>
                    <select id="work_type" name="work_type"
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="wfo"> WFO </option>
                        <option value="wfh"> WFH </option>
                    </select>


                    <label for="schedule_type" class="mt-3 block text-sm font-medium text-gray-700">Tipe Jadwal</label>
                    <select id="schedule_type" name="schedule_type"
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="1"> Ganti Jam</option>
                        <option value="0"> Jadwal Biasa </option>
                    </select>


                    <label for="approved_change_time" class="mt-3 block text-sm font-medium text-gray-700">Terima Ganti
                        jam</label>
                    <select id="approved_change_time" name="approved_change_time"
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="1"> Ya </option>
                        <option value="0"> Tidak </option>
                    </select>

                    <label for="back_first" class="mt-3 block text-sm font-medium text-gray-700">Pulang lebih Awal</label>
                    <select id="back_first" name="back_first"
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="1"> Ya </option>
                        <option value="0"> Tidak </option>
                    </select>

                    <label for="break_first" class="mt-3 block text-sm font-medium text-gray-700">Istirahat lebih
                        Awal</label>
                    <select id="break_first" name="break_first"
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="1"> Ya </option>
                        <option value="0"> Tidak </option>
                    </select>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end space-x-4 mt-6">
                    <button class="bg-red-600 text-white hover:bg-red-700 px-4 py-2 rounded" type="submit">Simpan</button>
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

        function openModalActivity(activity, id, attdId, date) {
            const modal = document.getElementById('modal');

            const textarea = document.getElementById('log-activity');
            const form = document.getElementById('log-activity-form');
            const attd_id = document.getElementById('attdId');
            const activityDate = document.getElementById('activity-date');
            form.action = `../../log-activity/update-isi/${id}`;
            attd_id.value = attdId;
            textarea.value = activity;
            activityDate.value = date;

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
        data: form.serialize(), // Mengambil semua data dari form
        
        // Fungsi ini berjalan jika request berhasil
        success: function(response) {
            if (response.success) {
                // 4. INI BAGIAN KUNCI PERBAIKANNYA
                // Langsung perbarui teks di halaman dengan data akurat dari server.
                const target = response.intern_target;
                $('#sisa-aktual-value').text(target.remaing_time_after_calculate);
                $('#total-ganti-jam-value').text(target.change_time_total);
                $('#sisa-setelah-diskon-value').text(target.remaing_time_after_discount);

                // Update juga nilai input diskon itu sendiri agar konsisten
                $('#discount-input').val(target.discount_time);

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
// Fungsi untuk reload intern target data
// Fungsi untuk reload intern target data
function reloadInternTarget() {
    $.ajax({
        url: `/api/intern-target/${internId}`, // Sesuaikan dengan endpoint API Anda
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            // Update semua nilai di card kedua
            if(data.intern_target) {
                const target = data.intern_target;
                
                // Update periode
                $('.bg-white.border.border-gray-300:eq(0) h2').html(
                    `Periode Magang : ${target.start_period || ''} s/d ${target.end_period || ''}`
                );
                
                // Update nilai-nilai dalam card (TAMBAHKAN UPDATE UNTUK SISA SETELAH DISKON)
                updateCardValue('Total Jam Kerja', target.total_work_time);
                updateCardValue('Total Masuk', target.admission_total);
                updateCardValue('Target', target.target_time);
                updateCardValue('Sisa', target.time_target_remaining);
                updateCardValue('Sisa Aktual', target.remaing_time_after_calculate);
                updateCardValue('Total Ganti Jam', target.change_time_total);
                updateCardValue('sisa setelah diskon', target.remaing_time_after_discount); // PERBAIKAN DISINI
                
                // Update discount_time input value
                if(target.discount_time !== undefined) {
                    $('input[name="discount_time"]').val(target.discount_time);
                }
            }
            
            // Update card ketiga (Total Presensi)
            if(data.intern_target) {
                updatePresenceCount('Masuk', data.intern_target.admit_total);
                updatePresenceCount('Istirahat Keluar', data.intern_target.break_start_total);
                updatePresenceCount('Izin Keluar', data.intern_target.permit_total);
                updatePresenceCount('Ganti Jam Total', data.intern_target.adjustable_total);
                updatePresenceCount('Pulang', data.intern_target.back_total);
                updatePresenceCount('Istirahat Kembali', data.intern_target.break_back_total);
                updatePresenceCount('Izin Kembali', data.intern_target.permit_back_total);
                updatePresenceCount('Ganti Jam Diterima', data.intern_target.accepted_adjustable_total);
            }
        },
        error: function(xhr) {
            console.error('Failed to reload intern target data');
        }
    });
}

function updateCardValue(label, value) {
    $('.bg-white.border.border-gray-300').eq(0).find('.font-semibold').each(function() {
        if($(this).text().trim() === label) {
            $(this).siblings('span').find('span.px-4').text(value || '0j 0m');
            return false; 
        }
    });
}

function updatePresenceCount(label, value) {
    $('.bg-white.border.border-gray-300').eq(1).find('.font-semibold').each(function() {
        if($(this).text().trim() === label) {
            $(this).siblings('span').find('span.px-4').text(value ? value + 'x' : '0x');
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
                                        <tr class="border-b border-gray-300 text-center mb-2">
                                            <td ${rowspan} class="border-t text-center align-middle">${nomorUrut}</td>
                                            <td ${rowspan} class="border-t text-center align-middle">
                                                ${schedule.date || '---'}
                                                <button class="px-1 text-sm" onclick="openModalShift('${schedule.id}', '${schedule.shift_id}', '${schedule.date}', '${schedule.work_type}', ${schedule.is_change_schedule_approved}, ${schedule.isChangeSchedule}, ${schedule.isBackFirst}, ${schedule.is_break_first})">
                                                    <i class="fa-solid fa-gear"></i>
                                                </button>
                                            </td>
                                            <td class="p-2 border-t text-center align-middle">
                                                ${generateAttendanceCell('default', 'Jam Mulai', 'start_time', attendance.start_time, attendance.id, schedule.date, '{{ $intern_data->full_name }}', attendance.start_time_message)}
                                            </td>
                                            <td class="border-t text-center align-middle">
                                                ${generateAttendanceCell('default', 'Jam Pulang', 'end_time', attendance.end_time, attendance.id, schedule.date, '{{ $intern_data->full_name }}', attendance.end_time_message)}
                                            </td>
                                            <td class="border-t text-center align-middle">
                                                ${generateAttendanceCell('default', 'Mulai Istirahat', 'break_time', attendance.break_time, attendance.id, schedule.date, '{{ $intern_data->full_name }}', attendance.break_time_message)}
                                            </td>
                                            <td class="border-t text-center align-middle">
                                                ${generateAttendanceCell('default', 'Selesai Istirahat', 'back_time', attendance.back_time, attendance.id, schedule.date, '{{ $intern_data->full_name }}', attendance.back_time_message)}
                                            </td>
                                            <td class="border-t text-center align-middle">${attendance.total_min_format}</td>
                                            <td class="border-t text-center align-middle ${attendance.target_time >= 0 ? 'text-green-600' : 'text-red-600'}">
                                                ${attendance.target_time_format}
                                            </td>
                                            ${attendanceType}
                                            ${mapsValue}
                                            <td ${rowspan} class="border-t text-center align-middle">
                                                <button id="open-modal-btn"
                                                        data-activity="${logActivity?.activity || 'Belum membuat Log Activity'}"
                                                        onclick="openModalActivity('${logActivity?.activity || 'Belum membuat Log Activity'}','${logActivity?.id || 'kosong'}','${schedule.id}','${schedule.date}')"
                                                        class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded text-sm">
                                                    Cek Disini
                                                </button>
                                            </td>
                                            <td  class="border-t text-center align-middle">
                                                ${statusIconsMain}
                                            </td>
                                        </tr>
                                    `;
                            tbody.append(mainRow);

                            // Iterate over each adjustableAttendance entry and create additional rows
                            $.each(adjustableAttendances, function (adjIndex, adjustable) {
                                // Determine background color based on is_approved
                                var bgColor = adjustable.is_approved === 1 ?
                                    'bg-blue-100' : 'bg-red-100';

                                // Generate status icons for adjustable attendance
                                var statusIconsAdjustable = getStatusIconsAdjustable(
                                    adjustable.is_approved, adjustable.id);

                                // Generate the adjustable attendance row
                                var adjustableRow = `
                                            <tr class="border-b border-gray-300 text-center ${bgColor} mx-4">
                                                <td class="p-2 border-t text-center align-middle">
                                                    ${generateAttendanceCell('adst', 'Jam Mulai', 'start_time', adjustable.start_time, adjustable.id, schedule.date, '{{ $intern_data->full_name }}', adjustable.start_time_message)}
                                                </td>
                                                <td class="border-t text-center align-middle">
                                                    ${generateAttendanceCell('adst', 'Jam Pulang', 'end_time', adjustable.end_time, adjustable.id, schedule.date, '{{ $intern_data->full_name }}', adjustable.end_time_message)}
                                                </td>
                                                <td class="border-t text-center align-middle">
                                                    ${generateAttendanceCell('adst', 'Mulai Istirahat', 'break_time', adjustable.break_time, adjustable.id, schedule.date, '{{ $intern_data->full_name }}', adjustable.break_time_message)}
                                                </td>
                                                <td class="border-t text-center align-middle">
                                                    ${generateAttendanceCell('adst', 'Selesai Istirahat', 'back_time', adjustable.back_time, adjustable.id, schedule.date, '{{ $intern_data->full_name }}', adjustable.back_time_message)}
                                                </td>
                                                <td class="border-t text-center align-middle">${adjustable.total_min_format}</td>
                                                <td class="border-t text-center align-middle ${adjustable.target_time >= 0 ? 'text-green-600' : 'text-red-600'}">
                                                    ${adjustable.target_time >= 0 ? '+' : '-'} ${adjustable.target_time}
                                                </td>
                                                <td class="border-t text-center align-middle">
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
             * @param {string} tipe
             * @param {string} label - The label for the modal.
             * @param {string} field - The field name.
             * @param {string|null} time - The time value.
             * @param {number} scheduleId - The schedule ID.
             * @param {string} date - The date of attendance.
             * @param {string} name - The name associated with the attendance.
             * @param {string|null} message - The message to display on hover.
             * @returns {string} - The generated HTML for the cell.
             */
            function generateAttendanceCell(tipe, label, field, time, scheduleId, date, name, message) {
                // Format waktu dengan detik
                const formattedTime = formatTimeWithSeconds(time);

                // Dapatkan halaman saat ini
                var currentPage = pageNow;

                return `
                            <span onclick="openModal('${tipe}','${label}', '${field}', '${time}', '${scheduleId}', '${date}', '${name}', '${message}', ${currentPage})"
                                class="cursor-pointer text-blue-600 hover:text-blue-800 hover:underline">
                                ${formattedTime}
                            </span>
                            ${message ? `
                                <span class="relative group">
                                    <i class="fa-solid fa-circle-info text-red-600 cursor-pointer text-sm"></i>
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block
                                                bg-gray-700 text-white text-sm rounded px-2 py-1 z-10 min-w-max max-w-xs">
                                        ${message}
                                    </div>
                                </span>` : ''}
                        `;
            }

            /**
             * Generates status icons for the main attendance row based on status_id.
             * @param {number} status_id - The status ID from log_activity.
             * @param {number} logActivityId - The ID of the log activity.
             * @returns {string} - The generated HTML for the status icons.
             */
            function getStatusIconsMain(status_id, logActivityId, attendance, schedule, attdStatus) {
                if (status_id != null) { // Pending
                    return `
                                <form action="../../log-activity/update-status/${logActivityId}" method="POST" class="inline group ">
                                    @csrf
                                    <input type="hidden" name="status" value="2">
                                    <button type="submit" class="bg-transparent border-none cursor-pointer group">
                                        <i class="fa-solid fa-check-circle ${status_id == 2 ? "text-green-600" : "text-gray-500"} text-xl hover:text-gray-800"></i>
                                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                                            Setujui Log Activty
                                        </span>
                                    </button>
                                </form>
                                <form action="../../log-activity/update-status/${logActivityId}" method="POST" class="inline group ">
                                    @csrf
                                    <input type="hidden" name="status" value="3">
                                    <button type="submit" class="bg-transparent border-none cursor-pointer group">
                                        <i class="fa-solid fa-xmark-circle ${status_id == 3 ? "text-red-600" : "text-gray-500"} text-xl hover:text-gray-800"></i>
                                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                                            Tolak Log Activty
                                        </span>
                                    </button>
                                </form>
                                <form onsubmit="return confirm('Apa kamu yakin ingin merubah status kehadiran tanggal ${schedule.date} menjadi ${attdStatus.id == 2 ? 'Tidak Hadir' : 'Hadir'}?');" action="../../attendance/updatestatusattd/${schedule.id}" method="POST" class="inline group ">
                                    @csrf
                                    <input type="hidden" name="status" value="3">
                                    <button type="submit" class="bg-transparent border-none cursor-pointer group" >
                                        <i class="fa-solid fa-file-lines text-gray-500 text-xl hover:text-gray-800"></i>
                                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                                            Tolak Log Activty
                                        </span>
                                    </button>
                                </form>
                                <form onsubmit="return confirm('Apa kamu yakin ingin mereset tanggal ${schedule.date}?');" action="../../attendance/reset/${attendance.id}" method="POST" class="inline group ">
                                    @csrf
                                    <input type="hidden" name="status" value="3">
                                    <button type="submit" class="bg-transparent border-none cursor-pointer group" >
                                        <i class="fa-solid fa-recycle text-gray-500 text-xl hover:text-gray-800"></i>
                                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                                            Tolak Log Activty
                                        </span>
                                    </button>
                                </form>
                                <form onsubmit="return confirm('Apa kamu yakin ingin menghapus tanggal ${schedule.date}?');" action="../../attendance/delete/${schedule.id}" method="POST" class="inline group ">
                                    @csrf
                                    <input type="hidden" name="status" value="3">
                                    <button type="submit" class="bg-transparent border-none cursor-pointer group" >
                                        <i class="fa-solid fa-trash text-red-600 text-xl hover:text-gray-800"></i>
                                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                                            Tolak Log Activty
                                        </span>
                                    </button>
                                </form>
                            `;
                } else {
                    return `-`;
                }
            }

            /**
    * Generates status icons for adjustable attendance rows based on is_approved.
    * Adds tooltips with Tailwind CSS for better user experience.
    * @param {number} is_approved - The approval status (0: Not Approved, 1: Approved, 2: Rejected).
    * @param {number} adjustableId - The ID of the adjustable attendance entry.
    * @returns {string} - The generated HTML for the status icons with tooltips.
    */
            function getStatusIconsAdjustable(is_approved, adjustableId) {
                if (is_approved != null) { // Not approved
                    return `
                <form action="../../adjustable-attendance/update-status/${adjustableId}" method="POST" class="inline group ">
                    @csrf
                    <input type="hidden" name="is_approved" value="1">
                    <button type="submit" class="bg-transparent border-none cursor-pointer group">
                        <i class="fa-solid fa-check-circle ${is_approved == 1 ? "text-blue-600" : "text-gray-500"} text-xl hover:text-gray-800"></i>
                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                            Setujui Ganti Jam
                        </span>
                    </button>
                </form>
                <form action="../../adjustable-attendance/update-status/${adjustableId}" method="POST" class="inline group ">
                    @csrf
                    <input type="hidden" name="is_approved" value="2">
                    <button type="submit" class="bg-transparent border-none cursor-pointer group">
                        <i class="fa-solid fa-xmark-circle ${is_approved == 2 ? "text-red-500" : "text-gray-500"} text-xl hover:text-gray-800"></i>
                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                            Tolak Ganti Jam
                        </span>
                    </button>
                </form>
                <!-- TAMBAHKAN RECYCLE DAN DELETE -->
                <form action="../../adjustable-attendance/restore/${adjustableId}" method="POST" class="inline group ">
                    @csrf
                    <button type="submit" class="bg-transparent border-none cursor-pointer group">
                        <i class="fa-solid fa-recycle text-green-600 text-xl hover:text-green-800"></i>
                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                            Restore Ganti Jam
                        </span>
                    </button>
                </form>
                <form action="../../adjustable-attendance/delete/${adjustableId}" method="POST" class="inline group ">
                    @csrf
                    <button type="submit" class="bg-transparent border-none cursor-pointer group" onclick="return confirm('Apakah Anda yakin ingin menghapus data ganti jam ini?')">
                        <i class="fa-solid fa-trash text-red-600 text-xl hover:text-red-800"></i>
                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-700 text-white text-xs rounded px-2 py-1 z-10">
                            Hapus Ganti Jam
                        </span>
                    </button>
                </form>
            `;
                } else {
                    return `-`;
                }
            }

            /**
             * Generates the attendance type cell based on attdStatus.id.
             * @param {object} attdStatus - The attendance status object.
             * @param {object} schedule - The schedule object.
             * @param {object|null} permitData - The permit data object.
             * @param {number} rowspan - The rowspan value based on adjustableAttendance count.
             * @returns {string} - The generated HTML for the attendance type cell.
             */
            function generateAttendanceType(attdStatus, schedule, permitData, rowspan) {
                var cellContent = '';
                if (attdStatus.id === 1 || attdStatus.id === 5) {
                    cellContent = `
                                <button class="open-modal-presence hover:underline"
                                        data-schedule-id="${schedule.id ?? ''}"
                                        data-shift-id="${schedule.shift_id}">
                                    ${attdStatus.name} <i class="fa-solid fa-circle-info text-gray-800"></i>
                                </button>
                            `;
                } else if (attdStatus.id === 3) {
                    const permitCategoryId = permitData?.permit_category_id || '';
                    const description = (permitData?.description || '').toLowerCase();
                    const isSakit = (permitCategoryId == 1 || permitCategoryId == 2 || description.includes('sakit'));
                    const statusName = isSakit ? 'Izin Sakit' : (attdStatus.name || 'Izin');

                    cellContent = `
                                <button class="open-modal-presence hover:underline"
                                        data-schedule-id="${schedule.id}"
                                        data-shift-id="${schedule.shift_id}"
                                        data-permit-id="${permitData?.id || ''}"
                                        data-description="${permitData?.description || 'Tidak ada keterangan'}"
                                        data-proof-url="${permitData?.proof_url || 'Tidak ada link'}"
                                        data-permit-category-id="${permitData?.permit_category_id || ''}"
                                        data-ischange-schedule="${schedule.isChangeSchedule || ''}">
                                    ${statusName} <i class="fa-solid fa-circle-info"></i>
                                </button>
                            `;
                } else {
                    cellContent = attdStatus.name;
                }

                return `
                            <td ${rowspan} class="text-center align-middle ${[1, 5, 3].includes(attdStatus.id) ? 'cursor-pointer text-blue-600 underline' : ''}" data-attendance-status="${attdStatus.id}">
                                ${cellContent}
                            </td>
                        `;
            }

            /**
             * Generates the maps cell based on attendance location data.
             * @param {object} attendance - The attendance object.
             * @param {number} rowspan - The rowspan value based on adjustableAttendance count.
             * @returns {string} - The generated HTML for the maps cell.
             */
            function generateMapsCell(schedule, attendance, adjustableCount) {
                if (attendance.latitude_start && attendance.longitude_start) {
                    return `
                                <td ${adjustableCount > 0 ? `rowspan="${1 + adjustableCount}"` : ''} class="text-center align-middle">
                                    <a href="{{ Route('location.user.view') }}?office_id=${schedule.office_id}&lat_start=${attendance.latitude_start}&long_start=${attendance.longitude_start}&lat_end=${attendance.latitude_end}&long_end=${attendance.longitude_end}"
                                    target="_blank"
                                    class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded text-sm">
                                        Cek Disini
                                    </a>
                                </td>
                            `;
                } else {
                    return `
                                <td ${adjustableCount > 0 ? `rowspan="${1 + adjustableCount}"` : ''} class="text-center align-middle">-</td>
                            `;
                }
            }

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
            document.getElementById("work_type").value = workType;
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
