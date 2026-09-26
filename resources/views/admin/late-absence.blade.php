@extends('layouts.main')

@section('title', 'Manajemen Keterlambatan')

@section('contents')
<!-- Main Content -->
<main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">
    <div class="bg-gray-700 text-white p-4 sm:p-6 rounded-t-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
            <!-- Left Column -->
            <div class="flex flex-col space-y-1 sm:space-y-4">
                <div class="text-2xl sm:text-4xl font-bold">Manajemen Keterlambatan</div>
                <div class="text-sm sm:text-lg" id="date-display">
                    Data per tanggal {{ request('date', today()->format('d-m-Y')) }}
                </div>
            </div>

            <!-- Right Column -->
            <div class="flex flex-col space-y-2">
                <label for="search-name" class="text-sm sm:text-lg font-medium">Cari Nama Intern</label>
                <div class="flex items-center border border-gray-300 rounded">
                    <div class="bg-white p-2 rounded-l">
                        <i class="ml-2 fa-solid fa-search text-gray-500"></i>
                    </div>
                    <input type="text" id="search-name" name="name" value="{{ request('name') }}"
                        class="p-2 pl-3 pr-3 rounded-r text-gray-800 focus:outline-none focus:border-blue-500 w-full text-sm sm:text-base"
                        placeholder="Masukkan nama intern">
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4 mt-2">
        <!-- Left Side: Summary -->
        <div class="bg-white p-3.5 sm:p-6 rounded-lg shadow-sm border border-gray-100">
            <div class="text-base sm:text-xl font-bold mb-2">Ringkasan Keterlambatan</div>
            <hr class="mb-3 border-gray-200">
            <div class="grid grid-cols-3 gap-1.5 sm:gap-4 mb-2 text-center sm:text-left">
                <div class="p-2 sm:p-0 bg-yellow-50 sm:bg-transparent rounded-lg flex flex-col sm:flex-row items-center sm:justify-start gap-1">
                    <span class="text-[11px] sm:text-sm font-medium text-yellow-800 sm:text-gray-700">Ditinjau</span>
                    <span class="px-2 py-0.5 sm:px-3 sm:py-2 text-xs font-semibold text-center text-white bg-yellow-600 rounded-md sm:rounded-lg">
                        {{ $lateAbsences->where('status', 'telat')->count() }}
                    </span>
                </div>
                <div class="p-2 sm:p-0 bg-green-50 sm:bg-transparent rounded-lg flex flex-col sm:flex-row items-center sm:justify-start gap-1">
                    <span class="text-[11px] sm:text-sm font-medium text-green-800 sm:text-gray-700">Diterima</span>
                    <span class="px-2 py-0.5 sm:px-3 sm:py-2 text-xs font-semibold text-center text-white bg-green-700 rounded-md sm:rounded-lg">
                        {{ $lateAbsences->where('status', 'tepat_waktu')->count() }}
                    </span>
                </div>
                <div class="p-2 sm:p-0 bg-red-50 sm:bg-transparent rounded-lg flex flex-col sm:flex-row items-center sm:justify-start gap-1">
                    <span class="text-[11px] sm:text-sm font-medium text-red-800 sm:text-gray-700">Ditambah</span>
                    <span class="px-2 py-0.5 sm:px-3 sm:py-2 text-xs font-semibold text-center text-white bg-red-700 rounded-md sm:rounded-lg">
                        {{ $lateAbsences->where('status', 'lewat')->count() }}
                    </span>
                </div>
            </div>
            <hr class="mb-2 border-gray-200">
        </div>

        <!-- Right Side: Actions -->
        <div class="bg-white p-3.5 sm:p-6 rounded-lg shadow-sm border border-gray-100">
            <div class="text-base sm:text-xl font-bold mb-2">Actions</div>
            <hr class="mb-4 border-gray-200">
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
                <button onclick="scanLateAbsences()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition shadow-xs text-center">
                    <i class="fas fa-search"></i> Scan
                </button>
                <button onclick="refreshData()"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition shadow-xs text-center">
                    <i class="fas fa-refresh"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white p-3.5 sm:p-4 rounded-lg shadow-sm border border-gray-100 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row flex-wrap gap-2.5 sm:gap-3 items-stretch sm:items-center">
            <div class="flex items-center border border-gray-800 rounded-md">
                <div class="bg-white p-2 rounded-l-md">
                    <i class="fas fa-calendar text-gray-500"></i>
                </div>
                <input type="date" name="date" value="{{ request('date', today()->format('Y-m-d')) }}"
                    class="p-2 pl-2 text-left text-gray-800 rounded-r-md focus:outline-none focus:border-blue-500 text-sm w-full">
            </div>

            <select name="status"
                class="p-2 w-full sm:w-36 border border-gray-800 rounded-md focus:outline-none focus:border-blue-500 text-xs sm:text-sm">
                <option value="">Semua Status</option>
                <option value="telat" {{ request('status') == 'telat' ? 'selected' : '' }}>Belum Ditinjau</option>
                <option value="tepat_waktu" {{ request('status') == 'tepat_waktu' ? 'selected' : '' }}>Alasan Diterima</option>
                <option value="lewat" {{ request('status') == 'lewat' ? 'selected' : '' }}>Jam Ditambah</option>
            </select>

            <input type="number" name="min_late_minutes" value="{{ request('min_late_minutes') }}"
                class="p-2 w-full sm:w-36 border border-gray-800 rounded-md focus:outline-none focus:border-blue-500 text-xs sm:text-sm"
                placeholder="Min telat (menit)">

            <div class="grid grid-cols-2 sm:flex items-center gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-xs sm:text-sm font-medium transition text-center">
                    Filter
                </button>
                <a href="{{ route('admin.late-absence.index') }}"
                    class="text-center bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-xs sm:text-sm font-medium transition">
                    Reset
                </a>
            </div>
        </form>
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

    @if (session('error'))
    <div class="mb-2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Bulk Actions -->
    <div class="mt-4 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
        <form id="bulkForm" method="POST" action="{{ route('admin.late-absence.bulk-update') }}"
            class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
            @csrf
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                <span id="selectedCount" class="text-xs sm:text-sm text-gray-600 hidden w-full sm:w-auto font-medium">
                    <span id="selectedNumber">0</span> item dipilih
                </span>
                <select name="bulk_status"
                    class="p-2 border border-gray-800 rounded-md focus:outline-none focus:border-blue-500 text-xs sm:text-sm w-full sm:w-auto" required>
                    <option value="">Pilih Status</option>
                    <option value="tepat_waktu">Tidak Terlambat</option>
                    <option value="lewat">Jam Ditambah</option>
                </select>
                <input type="text" name="bulk_notes"
                    class="p-2 border border-gray-800 rounded-md focus:outline-none focus:border-blue-500 text-xs sm:text-sm w-full sm:w-auto"
                    placeholder="Catatan (opsional)">
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 sm:px-4 py-2 rounded-md text-xs sm:text-sm font-medium whitespace-nowrap w-full sm:w-auto">
                    Update Terpilih
                </button>
            </div>
        </form>

        <a href="{{ route('admin.late-absence.export.pdf', request()->query()) }}"
            class="inline-flex justify-center items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-xs sm:text-sm font-medium shadow-xs">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-md overflow-x-auto mt-4">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr class="text-left">
                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" id="selectAll">
                    </th>
                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Intern</th>
                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Absen</th>
                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi Telat</th>
                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Status & Overtime
                    </th>
                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($lateAbsences as $index => $late)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-4 whitespace-nowrap">
                        <input type="checkbox" name="selected_ids[]" value="{{ $late->id }}" class="row-checkbox"
                            form="bulkForm">
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ ($lateAbsences->currentPage() - 1) * $lateAbsences->perPage() + $loop->iteration }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $late->absen_time->format('d-m-Y') }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $late->intern->user->profile->full_name ?? '-' }}
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $late->shift->name ?? '-' }}</div>
                        <div class="text-sm text-gray-500">
                            {{ $late->shift->start_time ?? '-' }} - {{ $late->shift->end_time ?? '-' }}
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                        <!-- TAMPILKAN WAKTU ABSEN SESUAI STATUS -->
                        @if($late->status == 'tepat_waktu')
                        <!-- Untuk status tepat_waktu, tampilkan waktu shift -->
                        <div class="space-y-1">
                            <div class="font-semibold text-green-700">
                                {{ $late->shift->start_time ?? '00:00:00' }}
                            </div>
                            @if($late->original_absen_time)
                            <div class="text-xs text-gray-500 mt-1 bg-gray-50 p-1 rounded">
                                <i class="fas fa-history"></i> Waktu asli:
                                {{ \Carbon\Carbon::parse($late->original_absen_time)->format('H:i:s') }}
                            </div>
                            @endif
                        </div>
                        @else
                        <!-- Untuk status lain, tampilkan waktu absen aktual -->
                        <div class="space-y-1">
                            <div class="{{ $late->status == 'telat' ? 'text-red-600 font-semibold' : '' }}">
                                {{ $late->absen_time->format('H:i:s') }}
                            </div>
                            @if($late->original_absen_time && $late->absen_time->format('H:i:s') !== \Carbon\Carbon::parse($late->original_absen_time)->format('H:i:s'))
                            <div class="text-xs text-blue-600 mt-1 bg-blue-50 p-1 rounded">
                                <i class="fas fa-history"></i> Waktu asli:
                                {{ \Carbon\Carbon::parse($late->original_absen_time)->format('H:i:s') }}
                            </div>
                            @endif
                        </div>
                        @endif
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        @if($late->status == 'tepat_waktu')
                        <span
                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            0 menit
                        </span>
                        @else
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                            {{ $late->late_minutes }} menit
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        @if($late->status == 'telat')
                        <span
                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            Belum Ditinjau
                        </span>
                        @elseif($late->status == 'tepat_waktu')
                        <div class="space-y-2">
                            <span
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Alasan Diterima
                            </span>
                            <div class="text-xs text-green-600 bg-green-50 p-2 rounded border">
                                <div class="flex items-center space-x-1 mb-1">
                                    <i class="fas fa-clock text-green-500"></i>
                                    <span class="font-semibold">Waktu Absen Disesuaikan:</span>
                                </div>
                                <div class="text-green-700 font-bold">
                                    {{ $late->shift->start_time ?? '00:00:00' }}
                                </div>
                                @if($late->original_absen_time)
                                <div class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-arrow-right"></i>
                                    Dari: {{ \Carbon\Carbon::parse($late->original_absen_time)->format('H:i:s') }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="space-y-1">
                            <span
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                Jam Ditambah
                            </span>
                            @if($late->attendance && $late->shift)
                            @php
                            $normalEnd = \Carbon\Carbon::parse($late->shift->end_time);
                            $newEnd = $normalEnd->copy()->addMinutes($late->late_minutes);
                            $actualEnd = $late->attendance->end_time ? \Carbon\Carbon::parse($late->attendance->end_time) : null;
                            $isApplied = $actualEnd && $actualEnd->format('H:i') === $newEnd->format('H:i');
                            @endphp
                            @if($isApplied)
                            <div class="text-xs text-gray-600 bg-orange-50 p-2 rounded border">
                                <div class="flex items-center space-x-1 mb-1">
                                    <i class="fas fa-clock text-orange-500"></i>
                                    <span class="font-semibold">Jam Pulang Baru:</span>
                                </div>
                                <div class="text-orange-700 font-bold">
                                    {{ $actualEnd->format('H:i') }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Normal: {{ $late->shift->end_time ?? '-' }}
                                </div>
                                <div class="text-xs text-red-600 mt-1">
                                    <i class="fas fa-plus-circle"></i>
                                    +{{ $late->late_minutes }} menit
                                </div>
                            </div>
                            @else
                            <div class="text-xs text-orange-600 bg-orange-50 p-2 rounded border">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Belum diterapkan</span>
                                <div class="text-xs text-gray-500 mt-1">
                                    Akan ditambah: +{{ $late->late_minutes }} menit
                                </div>
                                @if($late->shift->end_time)
                                <div class="text-xs text-orange-700 mt-1">
                                    {{ $late->shift->end_time }} → {{ $newEnd->format('H:i') }}
                                </div>
                                @endif
                            </div>
                            @endif
                            @endif
                        </div>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-500 max-w-xs truncate">
                        {{ $late->notes ?? '-' }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button type="button"
                                onclick="openUpdateModalFromButton(this)"
                                data-id="{{ $late->id }}"
                                data-status="{{ $late->status }}"
                                data-notes="{{ $late->notes ?? '' }}"
                                class="text-blue-600 hover:text-blue-900" title="Edit Status">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteRecord('{{ $late->id }}')" class="text-red-600 hover:text-red-900"
                                title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-4 py-4 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center py-8">
                            <i class="fas fa-clock text-gray-300 text-4xl mb-4"></i>
                            <p class="text-lg font-medium">Tidak ada data keterlambatan</p>
                            <p class="text-sm">Klik "Scan Keterlambatan" untuk mencari data yang belum tercatat</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex justify-center">
        {{ $lateAbsences->appends(request()->query())->links() }}
    </div>
</main>

<!-- Modal Update Status -->
<div id="updateModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden z-50 p-4">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl sm:text-2xl font-semibold mb-4 text-center text-gray-800">Update Status Keterlambatan</h2>
        <form id="updateForm" method="POST" action="">
            @csrf
            <div class="mb-4">
                <label for="status" class="block text-gray-700 text-sm font-semibold mb-1.5">Status<span class="text-red-500">*</span></label>
                <select id="status" name="status" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required
                    onchange="previewTimeAdjustment()">
                    <option value="telat">Belum Ditinjau</option>
                    <option value="tepat_waktu">Alasan Diterima</option>
                    <option value="lewat">Jam Ditambah</option>
                </select>
            </div>

            <!-- Time Adjustment Preview -->
            <div id="timeAdjustmentPreview" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl hidden">
                <h4 class="font-semibold text-green-800 mb-2 text-xs sm:text-sm">
                    <i class="fas fa-clock"></i> Penyesuaian Waktu Absen
                </h4>
                <div class="text-xs sm:text-sm space-y-1">
                    <div class="flex justify-between">
                        <span>Waktu Absen Saat Ini:</span>
                        <span id="currentAbsenTime" class="font-mono">-</span>
                    </div>
                    <div class="flex justify-between font-semibold text-green-700">
                        <span>Waktu Absen Baru:</span>
                        <span id="newAbsenTime" class="font-mono">-</span>
                    </div>
                    <div class="flex justify-between text-blue-600">
                        <span>Perubahan:</span>
                        <span id="timeChange" class="font-mono">Waktu akan disesuaikan dengan jam shift</span>
                    </div>
                </div>
                <div class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded-lg text-xs text-yellow-700">
                    <i class="fas fa-info-circle"></i> Waktu absen akan otomatis disesuaikan dengan jam mulai shift
                    setelah status disimpan
                </div>
            </div>

            <!-- Overtime Preview -->
            <div id="overtimePreview" class="mb-4 p-3 bg-orange-50 border border-orange-200 rounded-xl hidden">
                <h4 class="font-semibold text-orange-800 mb-2 text-xs sm:text-sm">
                    <i class="fas fa-clock"></i> Perubahan Jam Kerja
                </h4>
                <div class="text-xs sm:text-sm space-y-1">
                    <div class="flex justify-between">
                        <span>Jam Pulang Normal:</span>
                        <span id="normalEndTime" class="font-mono">-</span>
                    </div>
                    <div class="flex justify-between font-semibold text-orange-700">
                        <span>Jam Pulang Baru:</span>
                        <span id="newEndTime" class="font-mono">-</span>
                    </div>
                    <div class="flex justify-between text-red-600">
                        <span>Tambahan Waktu:</span>
                        <span id="additionalTime" class="font-mono">-</span>
                    </div>
                </div>
                <div class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded-lg text-xs text-yellow-700">
                    <i class="fas fa-info-circle"></i> Waktu pulang akan otomatis diperpanjang setelah status disimpan
                </div>
            </div>

            <div class="mb-4">
                <label for="notes" class="block text-gray-700 text-sm font-semibold mb-1.5">Catatan</label>
                <textarea id="notes" name="notes" rows="3" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Tambahkan catatan..."></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <button type="button" onclick="closeUpdateModal()"
                    class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition text-xs sm:text-sm">Batal</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition text-xs sm:text-sm shadow-xs">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Scan Results -->
<div id="scanModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden z-50 p-4">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col">
        <h2 class="text-xl sm:text-2xl font-semibold mb-4 text-center text-gray-800 shrink-0">Hasil Scan Keterlambatan</h2>
        <div id="scanResults" class="mb-4 overflow-y-auto flex-1">
            <!-- Results will be loaded here -->
        </div>
        <div class="flex justify-end pt-3 border-t border-gray-100 shrink-0">
            <button onclick="closeScanModal()"
                class="px-5 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-xl transition text-xs sm:text-sm">Tutup</button>
        </div>
    </div>
</div>

<script>
    let currentLateRecord = null;

    // Select All functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedCount();
    });

    // Update selected count when individual checkboxes change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('row-checkbox')) {
            updateSelectedCount();
            updateSelectAllState();
        }
    });

    function updateSelectedCount() {
        const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
        const selectedCount = document.getElementById('selectedCount');
        const selectedNumber = document.getElementById('selectedNumber');

        if (selectedCheckboxes.length > 0) {
            selectedCount.classList.remove('hidden');
            selectedNumber.textContent = selectedCheckboxes.length;
        } else {
            selectedCount.classList.add('hidden');
        }
    }

    function updateSelectAllState() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        const selectAll = document.getElementById('selectAll');

        if (checkedBoxes.length === 0) {
            selectAll.indeterminate = false;
            selectAll.checked = false;
        } else if (checkedBoxes.length === checkboxes.length) {
            selectAll.indeterminate = false;
            selectAll.checked = true;
        } else {
            selectAll.indeterminate = true;
        }
    }

    // Bulk form validation
    document.getElementById('bulkForm').addEventListener('submit', function(e) {
        const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
        const bulkStatus = document.querySelector('[name="bulk_status"]').value;

        if (selectedCheckboxes.length === 0) {
            e.preventDefault();
            alert('Pilih minimal satu data untuk diupdate');
            return;
        }

        if (!bulkStatus) {
            e.preventDefault();
            alert('Pilih status yang akan diupdate');
            return;
        }

        // Remove existing hidden inputs
        const existingHiddenInputs = this.querySelectorAll('input[name="selected_ids[]"]');
        existingHiddenInputs.forEach(input => input.remove());

        // Add selected IDs as hidden inputs
        selectedCheckboxes.forEach(checkbox => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'selected_ids[]';
            hiddenInput.value = checkbox.value;
            this.appendChild(hiddenInput);
        });

        // Add confirmation for bulk actions
        if (bulkStatus === 'tepat_waktu') {
            if (!confirm(`Yakin ingin mengupdate ${selectedCheckboxes.length} data terpilih menjadi "Tidak Terlambat"?\n\nPerhatian: Waktu absen akan disesuaikan dengan jam mulai shift untuk semua data yang dipilih.`)) {
                e.preventDefault();
                return;
            }
        } else if (bulkStatus === 'lewat') {
            if (!confirm(`Yakin ingin mengupdate ${selectedCheckboxes.length} data terpilih?\n\nPerhatian: Jam pulang akan diperpanjang sesuai durasi keterlambatan untuk semua data yang dipilih.`)) {
                e.preventDefault();
                return;
            }
        } else {
            if (!confirm('Yakin ingin mengupdate ' + selectedCheckboxes.length + ' data terpilih?')) {
                e.preventDefault();
                return;
            }
        }
    });

    function openUpdateModalFromButton(btn) {
        const id = btn.getAttribute('data-id');
        const status = btn.getAttribute('data-status') || '';
        const notes = btn.getAttribute('data-notes') || '';
        openUpdateModal(id, status, notes);
    }

    function openUpdateModal(id, currentStatus, currentNotes) {
        // Store current record data for preview
        fetch(`/admin/late-absence/${id}/details`)
            .then(response => response.json())
            .then(data => {
                currentLateRecord = data;
                document.getElementById('updateForm').action = `/admin/late-absence/${id}/update-status`;
                document.getElementById('status').value = currentStatus;
                document.getElementById('notes').value = currentNotes;

                // Set preview data
                if (data.shift) {
                    document.getElementById('normalEndTime').textContent = data.shift.end_time;
                    document.getElementById('newAbsenTime').textContent = data.shift.start_time;
                }

                // Tampilkan waktu asli jika ada
                const currentTimeElement = document.getElementById('currentAbsenTime');
                if (data.has_original_time && data.original_absen_time) {
                    currentTimeElement.innerHTML = `<span class="text-red-600">${data.original_absen_time} (asli)</span>`;
                } else {
                    currentTimeElement.textContent = data.absen_time;
                }

                previewTimeAdjustment();
                document.getElementById('updateModal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error fetching late record details:', error);
                // Fallback to basic modal
                document.getElementById('updateForm').action = `/admin/late-absence/${id}/update-status`;
                document.getElementById('status').value = currentStatus;
                document.getElementById('notes').value = currentNotes;
                document.getElementById('updateModal').classList.remove('hidden');
            });
    }

    function previewTimeAdjustment() {
        const status = document.getElementById('status').value;
        const timeAdjustmentPreview = document.getElementById('timeAdjustmentPreview');
        const overtimePreview = document.getElementById('overtimePreview');

        // Reset both previews
        timeAdjustmentPreview.classList.add('hidden');
        overtimePreview.classList.add('hidden');

        if (status === 'tepat_waktu' && currentLateRecord) {
            // Show time adjustment preview
            timeAdjustmentPreview.classList.remove('hidden');

            // Tampilkan informasi khusus jika mengembalikan dari status tepat_waktu
            const timeChangeElement = document.getElementById('timeChange');
            if (currentLateRecord.status === 'tepat_waktu') {
                timeChangeElement.innerHTML = '<span class="text-green-600">Status sudah disesuaikan dengan jam shift</span>';
            } else {
                timeChangeElement.innerHTML = '<span class="text-green-600">Waktu akan disesuaikan dengan jam shift</span>';
            }

        } else if (status === 'lewat' && currentLateRecord) {
            // Show overtime preview
            overtimePreview.classList.remove('hidden');

            // Calculate new end time
            if (currentLateRecord.shift && currentLateRecord.shift.end_time && currentLateRecord.late_minutes) {
                const normalEnd = currentLateRecord.shift.end_time;
                const lateMinutes = currentLateRecord.late_minutes;

                // Parse normal end time and add late minutes
                const [hours, minutes] = normalEnd.split(':').map(Number);
                const normalEndDate = new Date();
                normalEndDate.setHours(hours, minutes, 0, 0);

                const newEndDate = new Date(normalEndDate.getTime() + (lateMinutes * 60000));
                const newEndTime = newEndDate.toTimeString().slice(0, 5);

                document.getElementById('newEndTime').textContent = newEndTime;
                document.getElementById('additionalTime').textContent = `+${lateMinutes} menit`;
            }
        } else if (status === 'telat' && currentLateRecord) {
            // Show info untuk status telat
            if (currentLateRecord.status === 'tepat_waktu') {
                // Jika dari tepat_waktu ke telat, tampilkan info akan dikembalikan
                timeAdjustmentPreview.classList.remove('hidden');
                timeAdjustmentPreview.classList.remove('bg-green-50', 'border-green-200');
                timeAdjustmentPreview.classList.add('bg-blue-50', 'border-blue-200');

                const titleElement = timeAdjustmentPreview.querySelector('h4');
                titleElement.innerHTML = '<i class="fas fa-undo"></i> Pengembalian Waktu Absen';
                titleElement.classList.remove('text-green-800');
                titleElement.classList.add('text-blue-800');

                document.getElementById('timeChange').innerHTML =
                    '<span class="text-blue-600">Waktu absen akan dikembalikan ke waktu asli dan keterlambatan akan dihitung ulang</span>';
            }
        }
    }

    function closeUpdateModal() {
        document.getElementById('updateModal').classList.add('hidden');
        currentLateRecord = null;
    }

    function scanLateAbsences() {
        const date = document.querySelector('input[name="date"]').value || '{{ today()->format("Y-m-d") }}';

        showMessage('info', 'Sedang scan keterlambatan...');

        fetch(`/admin/late-absence/scan?date=${date}`)
            .then(response => response.json())
            .then(data => {
                let message = `Scan selesai untuk ${date}:<br>`;
                message += `- Total dicek: ${data.total_checked}<br>`;
                message += `- Terlambat ditemukan: ${data.late_found}<br>`;
                message += `- Record dibuat: ${data.records_created}`;

                document.getElementById('scanResults').innerHTML = `
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <h3 class="font-semibold mb-2">Hasil Scan</h3>
                                <p>${message}</p>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="p-2 text-left">Nama</th>
                                            <th class="p-2 text-left">Shift</th>
                                            <th class="p-2 text-left">Absen</th>
                                            <th class="p-2 text-left">Status</th>
                                            <th class="p-2 text-left">Dampak</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.details.map(detail => `
                                            <tr class="${detail.is_late ? 'bg-red-50' : 'bg-green-50'}">
                                                <td class="p-2">${detail.intern_name}</td>
                                                <td class="p-2">${detail.shift_name} (${detail.shift_start})</td>
                                                <td class="p-2">${detail.absen_time}</td>
                                                <td class="p-2">
                                                    ${detail.is_late ?
                            `<span class="text-red-600">Telat ${detail.late_minutes} menit</span>` :
                            '<span class="text-green-600">Tepat waktu</span>'
                        }
                                                </td>
                                                <td class="p-2">
                                                    ${detail.is_late ?
                            `<span class="text-orange-600">Bisa diubah status</span>` :
                            '-'
                        }
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <h4 class="font-semibold text-yellow-800 mb-1">
                                    <i class="fas fa-info-circle"></i> Catatan Penting
                                </h4>
                                <p class="text-sm text-yellow-700">
                                    - Jika status diubah menjadi "Alasan Diterima", waktu absen akan otomatis disesuaikan dengan jam mulai shift.<br>
                                    - Jika status diubah menjadi "Jam Ditambah", waktu pulang akan otomatis diperpanjang sesuai durasi keterlambatan.
                                </p>
                            </div>
                        `;

                document.getElementById('scanModal').classList.remove('hidden');

                if (data.records_created > 0) {
                    setTimeout(() => location.reload(), 3000);
                }
            })
            .catch(error => {
                showMessage('error', 'Terjadi kesalahan saat scan data');
                console.error('Error:', error);
            });
    }

    function closeScanModal() {
        document.getElementById('scanModal').classList.add('hidden');
    }

    function deleteRecord(id) {
        if (confirm('Yakin ingin menghapus record keterlambatan ini?')) {
            fetch(`/admin/late-absence/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('success', 'Record berhasil dihapus');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showMessage('error', 'Gagal menghapus record');
                    }
                })
                .catch(error => {
                    showMessage('error', 'Terjadi kesalahan');
                    console.error('Error:', error);
                });
        }
    }

    function refreshData() {
        location.reload();
    }

    function showMessage(type, message) {
        const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' :
            type === 'error' ? 'bg-red-100 border-red-400 text-red-700' :
            'bg-blue-100 border-blue-400 text-blue-700';

        const messageDiv = document.createElement('div');
        messageDiv.className = `fixed top-4 right-4 ${alertClass} px-4 py-3 rounded border z-50`;
        messageDiv.innerHTML = `
                    <div class="flex">
                        <div class="flex-1">${message}</div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-2">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;

        document.body.appendChild(messageDiv);

        setTimeout(() => {
            if (messageDiv.parentElement) {
                messageDiv.remove();
            }
        }, 5000);
    }

    // Auto hide success message
    document.addEventListener('DOMContentLoaded', function() {
        const message = document.getElementById('success-message');
        if (message) {
            setTimeout(() => {
                message.style.opacity = 0;
                setTimeout(() => message.remove(), 600);
            }, 3000);
        }
    });

    // Search functionality
    document.getElementById('search-name').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('name', this.value);
            window.location.href = currentUrl.toString();
        }
    });

    // Form submission confirmation for single update
    document.getElementById('updateForm').addEventListener('submit', function(e) {
        const status = document.getElementById('status').value;
        const currentStatus = currentLateRecord?.status;

        let confirmationMessage = '';

        if (status === 'tepat_waktu') {
            if (currentStatus === 'tepat_waktu') {
                confirmationMessage = 'Status sudah "Alasan Diterima". Yakin ingin mempertahankan status ini?';
            } else {
                confirmationMessage = 'Yakin ingin mengubah status menjadi "Alasan Diterima"?\n\nWaktu absen akan disesuaikan dengan jam mulai shift.';
            }
        } else if (status === 'lewat') {
            if (currentStatus === 'lewat') {
                confirmationMessage = 'Status sudah "Jam Ditambah". Yakin ingin mempertahankan status ini?';
            } else {
                confirmationMessage = 'Yakin ingin mengubah status menjadi "Jam Ditambah"?\n\nWaktu pulang akan diperpanjang sesuai durasi keterlambatan.';
            }
        } else if (status === 'telat') {
            if (currentStatus === 'tepat_waktu') {
                confirmationMessage = 'Yakin ingin mengubah status dari "Alasan Diterima" menjadi "Belum Ditinjau"?\n\nWaktu absen akan dikembalikan ke waktu asli dan keterlambatan akan dihitung ulang.';
            } else if (currentStatus === 'lewat') {
                confirmationMessage = 'Yakin ingin mengubah status dari "Jam Ditambah" menjadi "Belum Ditinjau"?\n\nJam pulang akan dikembalikan ke normal.';
            } else {
                confirmationMessage = 'Yakin ingin mengubah status menjadi "Belum Ditinjau"?';
            }
        }

        if (confirmationMessage && !confirm(confirmationMessage)) {
            e.preventDefault();
            return;
        }
    });

    function removeMessage() {
        const message = document.getElementById('success-message');
        if (message) {
            message.remove();
        }
    }
</script>
@endsection