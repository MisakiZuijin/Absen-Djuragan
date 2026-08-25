@extends('layouts.outsider')

@section('title', 'Dashboard Presensi')

@section('contents')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <div class="p-6 space-y-6">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-700 to-blue-800 text-white p-6 rounded-xl shadow-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="flex flex-col space-y-4">
                    <h1 class="text-4xl font-bold">Dashboard Presensi</h1>
                    <p class="text-lg opacity-90" id="date-display">Data per tanggal {{ $dateNow ?? '' }}</p>
                </div>

                <!-- Right Column -->
                <div class="flex flex-col space-y-2">
                    <label for="search-name" class="text-lg font-medium">Cari Mahasiswa</label>
                    <div class="relative">
                        <div class="flex items-center border border-blue-500 rounded-lg bg-white shadow-sm">
                            <div class="p-3 rounded-l-lg bg-blue-50">
                                <i class="fa-solid fa-search text-blue-500"></i>
                            </div>
                            <input type="text" id="search-name"
                                class="p-3 pl-2 rounded-r-lg text-gray-800 focus:outline-none w-full"
                                placeholder="Masukkan nama mahasiswa...">
                            <button id="clear-search"
                                class="absolute right-3 text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                        <div id="search-suggestions"
                            class="absolute z-10 w-full bg-white border border-gray-200 rounded-b-lg shadow-lg mt-1 hidden max-h-48 overflow-y-auto">
                            <!-- Suggestions will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats and Filters Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 bg-white p-6 shadow-md rounded-xl">
            <!-- Left: Attendance Stats -->
            <div>
                <h2 class="text-xl font-bold mb-4 text-gray-800">Statistik Kehadiran</h2>
                <div class="space-y-4">
                    <div class="flex flex-wrap gap-4">
                        <div class="flex items-center bg-green-50 px-4 py-2 rounded-lg">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-gray-700">Total Masuk</span>
                            <span id="total_presence"
                                class="px-3 py-1 text-sm font-medium text-white bg-green-600 rounded-full ml-2">{{ $totalHadir ?? 0 }}</span>
                        </div>
                        <div class="flex items-center bg-yellow-50 px-4 py-2 rounded-lg">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                            <span class="text-gray-700">Total Izin</span>
                            <span id="total_permit"
                                class="px-3 py-1 text-sm font-medium text-white bg-yellow-600 rounded-full ml-2">{{ $totalIzin ?? 0 }}</span>
                        </div>
                        <div class="flex items-center bg-red-50 px-4 py-2 rounded-lg">
                            <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                            <span class="text-gray-700">Total Tidak Masuk</span>
                            <span id="total_absence"
                                class="px-3 py-1 text-sm font-medium text-white bg-red-600 rounded-full ml-2">{{ $totalAlpha ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600 flex items-center">
                        <i class="fa-solid fa-info-circle mr-2"></i>
                        <span id="data-info">Menampilkan data presensi hari ini</span>
                    </div>
                </div>
            </div>

            <!-- Right: Filters -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700">Tanggal</label>
                    <input type="date" id="date-target" value="{{ $dateNowYMD ?? '' }}"
                        class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700">Status</label>
                    <select id="filter-status"
                        class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="" selected>Semua Status</option>
                        @foreach ($attd_statuses ?? [] as $status)
                            <option value="{{ $status['id'] }}">{{ $status['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700">Shift</label>
                    <select id="filter-shift"
                        class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="" selected>Semua Shift</option>
                        @foreach ($shifts ?? [] as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
    <div class="flex items-end gap-2">

        <!-- Bagian Dropdown Kantor -->
        <div class="flex-grow space-y-1">
            <label class="text-sm font-medium text-gray-700">Kantor</label>
            <select id="filter-office"
                class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                <option value="" selected>Semua Kantor</option>
                @foreach ($offices ?? [] as $office)
                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Bagian Tombol Filter -->
        <div>
            <button id="filter-btn"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center justify-center transition-colors text-sm shadow-md hover:shadow-lg">
                <i class="fa-solid fa-filter mr-2"></i>
                <span>Filter</span>
            </button>
        </div>

    </div>
</div>
            </div>
        </div>

        <!-- Quick Stats Cards (Sama seperti sebelumnya) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-5 flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fa-solid fa-users text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 font-medium">Total Siswa</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-students">{{ $presensiHariIni->count() }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-5 flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fa-solid fa-male text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 font-medium">Laki-laki</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-male">{{ $jumlahLaki }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-5 flex items-center">
                <div class="p-3 bg-pink-100 rounded-full">
                    <i class="fa-solid fa-female text-pink-500 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 font-medium">Perempuan</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-female">{{ $jumlahPerempuan }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-5 flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fa-solid fa-percentage text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 font-medium">Tingkat Kehadiran</p>
                    <p class="text-2xl font-bold text-gray-900" id="attendance-rate">
                        @if($presensiHariIni->count() > 0)
                            {{ round(($totalHadir / $presensiHariIni->count()) * 100, 1) }}%
                        @else
                            0%
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Loading Indicator (Sama seperti sebelumnya) -->
        <div id="loading-indicator" class="hidden flex justify-center items-center py-8">
            <div class="flex flex-col items-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-2"></div>
                <span class="text-gray-600">Memuat data...</span>
            </div>
        </div>

        <!-- Main Table (Sama seperti sebelumnya) -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-gray-600 font-semibold uppercase text-xs tracking-wider">
                            <th class="p-4 text-left">No</th>
                            <th class="p-4 text-left">Nama Siswa</th>
                            <th class="p-4 text-left">Status</th>
                            <th class="p-4 text-left">Jam Masuk</th>
                            <th class="p-4 text-left">Jam Pulang</th>
                            <th class="p-4 text-left">Shift</th>
                            <th class="p-4 text-center">Log Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody id="report-tbody" class="divide-y divide-gray-200">
                        @forelse ($presensiHariIni as $presensi)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="p-4">{{ $loop->iteration }}</td>
                                <td class="p-4 font-medium text-gray-900">
                                    <a href="{{ route('outsider.presensi.show', $presensi->schedule->intern_id) }}"
                                        class="text-blue-600 hover:text-blue-800 hover:underline cursor-pointer">
                                        {{ $presensi->schedule->intern->user->profile->full_name ?? 'N/A' }}
                                    </a>
                                </td>
                                <td class="p-4">
                                    <span class="px-3 py-1.5 rounded-full text-white text-xs font-semibold
                                                    @if($presensi->attd_status_id == 2) bg-green-500
                                                    @elseif($presensi->attd_status_id == 3) bg-yellow-500
                                                    @else bg-red-500 @endif">
                                        {{ optional($presensi->attdStatus)->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="font-mono">{{ $presensi->attendance->start_time ?? '--:--' }}</span>
                                    @if($presensi->attendance && $presensi->attendance->start_time_message)
                                        <i class="fa-solid fa-circle-info text-blue-500 ml-1 cursor-help"
                                            title="{{ $presensi->attendance->start_time_message }}"></i>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <span class="font-mono">{{ $presensi->attendance->end_time ?? '--:--' }}</span>
                                    @if($presensi->attendance && $presensi->attendance->end_time_message)
                                        <i class="fa-solid fa-circle-info text-blue-500 ml-1 cursor-help"
                                            title="{{ $presensi->attendance->end_time_message }}"></i>
                                    @endif
                                </td>
                                <td class="p-4">{{ $presensi->shift->name ?? 'N/A' }}</td>
                                <td class="p-4 text-center">
                                    @if ($presensi->logActivity)
                                        <a href="{{ route('outsider.log', $presensi->schedule->intern_id) }}"
                                            class="text-indigo-600 hover:text-indigo-800 hover:underline text-sm flex items-center justify-center gap-1 transition-colors">
                                            <i class="fa-solid fa-list"></i>
                                            Lihat Log
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-sm flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-minus"></i>
                                            Belum Ada Log
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center p-8 text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fa-solid fa-inbox text-4xl text-gray-300 mb-3"></i>
                                        <p class="text-lg mb-1">Tidak ada data presensi siswa untuk hari ini.</p>
                                        <p class="text-sm">Silakan coba tanggal lain atau sesuaikan filter.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center space-x-2">
                <button id="refresh-btn"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-md flex items-center transition-colors text-sm"
                    title="Refresh data (Ctrl+R)">
                    <i class="fa-solid fa-refresh mr-1 sm:mr-2"></i>
                    <span class="hidden sm:inline">Refresh</span>
                </button>
            </div>

            <div class="text-sm text-gray-500 flex items-center">
                <i class="fa-solid fa-clock mr-2"></i>
                <span id="last-updated">Terakhir diperbarui: {{ now()->format('H:i') }}</span>
            </div>
        </div>
    </div>

    <!-- Toast Notification (Sama seperti sebelumnya) -->
    <div id="toast" class="fixed top-6 right-6 z-50 hidden">
        <div
            class="bg-white border-l-4 border-green-500 shadow-lg rounded-lg p-4 max-w-sm transform transition-all duration-300 ease-in-out">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fa-solid fa-check-circle text-green-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900" id="toast-message">Success message</p>
                </div>
                <div class="ml-auto pl-3">
                    <button id="toast-close" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fa-solid fa-times text-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ... (variabel const di awal SAMA) ...
            const dateTarget = document.getElementById('date-target');
            const filterStatus = document.getElementById('filter-status');
            const filterShift = document.getElementById('filter-shift');
            const filterOffice = document.getElementById('filter-office');
            const searchName = document.getElementById('search-name');
            const clearSearch = document.getElementById('clear-search');
            const searchSuggestions = document.getElementById('search-suggestions');
            const tableBody = document.getElementById('report-tbody');
            const loadingIndicator = document.getElementById('loading-indicator');
            const dataInfo = document.getElementById('data-info');
            const refreshBtn = document.getElementById('refresh-btn');
            const lastUpdatedSpan = document.getElementById('last-updated');
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            const toastClose = document.getElementById('toast-close');
            let searchTimeout;
            let allStudents = [];

            // ... (fungsi sebelum updateUI() SAMA) ...
            function initializeSearchSuggestions() {
                const currentStudents = Array.from(document.querySelectorAll('#report-tbody tr')).map(row => {
                    const nameCell = row.querySelector('td:nth-child(2) a');
                    return nameCell ? nameCell.textContent.trim() : '';
                }).filter(name => name && name !== 'N/A');

                allStudents = [...new Set(currentStudents)];
            }
            function toggleClearSearch() {
                if (searchName.value.length > 0) {
                    clearSearch.classList.remove('hidden');
                } else {
                    clearSearch.classList.add('hidden');
                    searchSuggestions.classList.add('hidden');
                }
            }
            function showSearchSuggestions(query) {
                if (!query) {
                    searchSuggestions.classList.add('hidden');
                    return;
                }

                const filtered = allStudents.filter(student =>
                    student.toLowerCase().includes(query.toLowerCase())
                );

                if (filtered.length > 0) {
                    searchSuggestions.innerHTML = filtered.map(student =>
                        `<div class="p-2 hover:bg-gray-100 cursor-pointer text-black search-suggestion" data-name="${student}">
                                <i class="fa-solid fa-user mr-2 text-gray-400"></i>${student}
                            </div>`
                    ).join('');
                    searchSuggestions.classList.remove('hidden');
                } else {
                    searchSuggestions.classList.add('hidden');
                }
            }
            searchSuggestions.addEventListener('click', function (e) {
                if (e.target.closest('.search-suggestion')) {
                    const name = e.target.closest('.search-suggestion').dataset.name;
                    searchName.value = name;
                    searchSuggestions.classList.add('hidden');
                    fetchPresensiData();
                }
            });
            clearSearch.addEventListener('click', function () {
                searchName.value = '';
                searchSuggestions.classList.add('hidden');
                toggleClearSearch();
                fetchPresensiData();
            });
            searchName.addEventListener('input', function () {
                toggleClearSearch();
                showSearchSuggestions(this.value);
            });
            document.addEventListener('click', function (e) {
                if (!searchName.contains(e.target) && !searchSuggestions.contains(e.target)) {
                    searchSuggestions.classList.add('hidden');
                }
            });
            function showLoading() {
                loadingIndicator.classList.remove('hidden');
                if (tableBody) tableBody.style.opacity = '0.5';
            }
            function hideLoading() {
                loadingIndicator.classList.add('hidden');
                if (tableBody) tableBody.style.opacity = '1';
            }
            function fetchPresensiData() {
                showLoading();

                const date = dateTarget.value;
                const status = filterStatus.value;
                const shift = filterShift.value;
                const office = filterOffice.value;
                const name = searchName.value;
                const filters = [];

                if (date) filters.push(`tanggal ${new Date(date).toLocaleDateString('id-ID')}`);
                if (status) filters.push(`status ${filterStatus.options[filterStatus.selectedIndex].text}`);
                if (shift) filters.push(`shift ${filterShift.options[filterShift.selectedIndex].text}`);
                if (office) filters.push(`kantor ${filterOffice.options[filterOffice.selectedIndex].text}`);
                if (name) filters.push(`nama "${name}"`);

                dataInfo.textContent = filters.length > 0
                    ? `Menampilkan data dengan filter: ${filters.join(', ')}`
                    : 'Menampilkan semua data presensi';

                fetch(`{{ route('outsider.presensi.filter') }}?date=${date}&attd_status_id=${status}&shift_id=${shift}&office_id=${office}&name=${name}`)
                    .then(response => response.json())
                    .then(data => {
                        updateUI(data);
                        document.getElementById('total_presence').textContent = data.totalHadir;
                        document.getElementById('total_permit').textContent = data.totalIzin;
                        document.getElementById('total_absence').textContent = data.totalAlpha;
                        document.getElementById('date-display').textContent = `Data per tanggal ${data.dateNow}`;
                        updateQuickStats(data.presensi);
                        const now = new Date();
                        lastUpdatedSpan.textContent = `Terakhir diperbarui: ${now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`;
                        hideLoading();
                        showToast('Data berhasil diperbarui', 'success');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        hideLoading();
                        if (tableBody) {
                            tableBody.innerHTML = `
                                    <tr>
                                        <td colspan="7" class="text-center p-6 text-red-500">
                                            <i class="fa-solid fa-exclamation-triangle mr-2"></i>
                                            Terjadi kesalahan saat memuat data. Silakan coba lagi.
                                        </td>
                                    </tr>
                                `;
                        }
                        showToast('Terjadi kesalahan saat memuat data', 'error');
                    });
            }
            function updateQuickStats(presensiData) {
                const totalStudents = presensiData.length;
                document.getElementById('total-students').textContent = totalStudents;

                if (totalStudents > 0) {
                    const hadirCount = presensiData.filter(p => p.attd_status_id == 2).length;
                    const attendanceRate = ((hadirCount / totalStudents) * 100).toFixed(1);
                    document.getElementById('attendance-rate').textContent = `${attendanceRate}%`;
                    
                    const maleCount = presensiData.filter(p => p.schedule?.intern?.user?.profile?.gender === 'Laki-laki').length;
                    const femaleCount = presensiData.filter(p => p.schedule?.intern?.user?.profile?.gender === 'Perempuan').length;

                    document.getElementById('total-male').textContent = maleCount;
                    document.getElementById('total-female').textContent = femaleCount;
                } else {
                    document.getElementById('attendance-rate').textContent = '0%';
                    document.getElementById('total-male').textContent = '0';
                    document.getElementById('total-female').textContent = '0';
                }
            }


            // Update UI with new data
            function updateUI(data) {
                if (!tableBody) return;

                tableBody.innerHTML = '';

                if (data.presensi.length > 0) {
                    data.presensi.forEach((p, index) => {
                        const statusClass = p.attd_status_id == 2 ? 'bg-green-500' : (p.attd_status_id == 3 ? 'bg-yellow-500' : 'bg-red-500');
                        const startTimeMessage = p.attendance?.start_time_message ?
                            `<i class="fa-solid fa-circle-info text-blue-500 ml-1 cursor-help" title="${p.attendance.start_time_message}"></i>` : '';
                        const endTimeMessage = p.attendance?.end_time_message ?
                            `<i class="fa-solid fa-circle-info text-blue-500 ml-1 cursor-help" title="${p.attendance.end_time_message}"></i>` : '';

                        let logActivityContent;
                        if (p.log_activity) {
                            // =========================================================================
                            // PERBAIKAN DI SINI: Menambahkan '/presensi' pada URL
                            // =========================================================================
                            logActivityContent = `
                                    <a href="/outsider/presensi/log/${p.schedule.intern_id}"
                                       class="text-indigo-600 hover:text-indigo-800 hover:underline text-sm flex items-center justify-center gap-1 transition-colors">
                                        <i class="fa-solid fa-list"></i>
                                        Lihat Log
                                    </a>`;
                        } else {
                            logActivityContent = `
                                    <span class="text-gray-400 text-sm flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-minus"></i>
                                        Belum Ada Log
                                    </span>`;
                        }

                        const row = `
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-4">${index + 1}</td>
                                    <td class="p-4 font-medium text-gray-900">
                                        <a href="/outsider/presensi/${p.schedule?.intern_id}"
                                           class="text-blue-600 hover:text-blue-800 hover:underline cursor-pointer">
                                            ${p.schedule?.intern?.user?.profile?.full_name ?? '-'}
                                        </a>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-3 py-1.5 rounded-full text-white text-xs font-semibold ${statusClass}">
                                            ${p.attd_status?.name ?? 'N/A'}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <span class="font-mono">${p.attendance?.start_time ?? '--:--'}</span>
                                        ${startTimeMessage}
                                    </td>
                                    <td class="p-4">
                                        <span class="font-mono">${p.attendance?.end_time ?? '--:--'}</span>
                                        ${endTimeMessage}
                                    </td>
                                    <td class="p-4">${p.shift?.name ?? 'N/A'}</td>
                                    <td class="p-4 text-center">
                                        ${logActivityContent}
                                    </td>
                                </tr>
                            `;
                        tableBody.insertAdjacentHTML('beforeend', row);
                    });
                } else {
                    tableBody.innerHTML = `
                            <tr>
                                <td colspan="7" class="text-center p-8 text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fa-solid fa-search text-4xl text-gray-300 mb-3"></i>
                                        <p class="text-lg mb-1">Tidak ada data yang cocok dengan filter yang dipilih.</p>
                                        <p class="text-sm">Coba ubah filter atau kata kunci pencarian.</p>
                                    </div>
                                </td>
                            </tr>
                        `;
                }
                initializeSearchSuggestions();
            }

            // ... (sisa kode JavaScript SAMA) ...
            function showToast(message, type = 'success') {
                if (!toast || !toastMessage) return;

                const icon = toast.querySelector('.flex-shrink-0 i');
                const border = toast.querySelector('.border-l-4');

                toastMessage.textContent = message;

                if (type === 'error') {
                    icon.className = 'fa-solid fa-exclamation-triangle text-red-500 text-xl';
                    border.className = 'border-l-4 border-red-500';
                } else if (type === 'info') {
                    icon.className = 'fa-solid fa-info-circle text-blue-500 text-xl';
                    border.className = 'border-l-4 border-blue-500';
                } else {
                    icon.className = 'fa-solid fa-check-circle text-green-500 text-xl';
                    border.className = 'border-l-4 border-green-500';
                }

                toast.classList.remove('hidden');
                setTimeout(() => {
                    toast.classList.add('hidden');
                }, 3000);
            }
            dateTarget.addEventListener('change', fetchPresensiData);
            filterStatus.addEventListener('change', fetchPresensiData);
            filterShift.addEventListener('change', fetchPresensiData);
            filterOffice.addEventListener('change', fetchPresensiData);
            document.getElementById('filter-btn').addEventListener('click', fetchPresensiData);
            searchName.addEventListener('keyup', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    fetchPresensiData();
                }, 300);
            });
            initializeSearchSuggestions();
            toggleClearSearch();
            refreshBtn.addEventListener('click', fetchPresensiData);
            if (toastClose) {
                toastClose.addEventListener('click', () => {
                    toast.classList.add('hidden');
                });
            }
            document.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                    e.preventDefault();
                    fetchPresensiData();
                }
                if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                    e.preventDefault();
                    searchName.focus();
                }
                if (e.key === 'Escape' && document.activeElement === searchName) {
                    searchName.value = '';
                    searchSuggestions.classList.add('hidden');
                    toggleClearSearch();
                    fetchPresensiData();
                }
            });
            setInterval(() => {
                const hasFilters = dateTarget.value !== '{{ $dateNowYMD ?? "" }}' ||
                    filterStatus.value ||
                    filterShift.value ||
                    filterOffice.value ||
                    searchName.value;

                if (!hasFilters) {
                    fetchPresensiData();
                }
            }, 5 * 60 * 1000);
            if (!localStorage.getItem('outsider-dashboard-help-shown')) {
                setTimeout(() => {
                    showToast('💡 Tips: Gunakan Ctrl+F untuk pencarian, Ctrl+R untuk refresh', 'info');
                    localStorage.setItem('outsider-dashboard-help-shown', 'true');
                }, 2000);
            }
        });
    </script>
@endsection