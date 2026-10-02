@extends('layouts.main')

@section('title', 'Pulang Otomatis')

@section('contents')
<!-- Main Content -->
<main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">
    <!-- Header Section -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center gap-2.5">
                <i class="fa-solid fa-arrow-right-from-bracket text-blue-600"></i>
                <span>Pulang Otomatis</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-1">
                Kelola dan pulangkan pemagang yang lupa melakukan presensi pulang, dengan konfirmasi admin dan pesan catatan opsional.
            </p>
        </div>
    </div>

    @if (session('status'))
    <div class="mb-5 bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-2xl flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('status') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    @endif

    @if (session('error'))
    <div class="mb-5 bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-2xl flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold">
            <i class="fa-solid fa-triangle-exclamation text-red-600 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    @endif

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white p-5 rounded-2xl shadow-xs border border-amber-200 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-amber-700 uppercase tracking-wider block mb-1">Lewat Jam Shift (Belum Pulang)</span>
                <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $total_pending_today ?? 0 }}</p>
                <span class="text-[11px] text-gray-500 mt-0.5 block">Pemagang yang telah melewati batas jam pulang shift</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-user-clock"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-xs border border-blue-200 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-blue-700 uppercase tracking-wider block mb-1">Dipulangkan Otomatis Hari Ini</span>
                <p id="total_presence" class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $total_auto_end_today ?? 0 }}</p>
                <span class="text-[11px] text-gray-500 mt-0.5 block">Total pemagang dipulangkan otomatis hari ini</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-gray-200 mb-6 pb-2">
        <button type="button" onclick="switchTab('pending')" id="tab-btn-pending"
            class="tab-btn px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 {{ ($activeTab ?? 'pending') === 'pending' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
            <i class="fa-solid fa-user-clock"></i>
            <span>Perlu Dipulangkan</span>
            @if(isset($pendingAttendances) && count($pendingAttendances) > 0)
            <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ ($activeTab ?? 'pending') === 'pending' ? 'bg-white text-blue-700' : 'bg-amber-100 text-amber-800' }}">
                {{ count($pendingAttendances) }}
            </span>
            @endif
        </button>

        <button type="button" onclick="switchTab('history')" id="tab-btn-history"
            class="tab-btn px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 {{ ($activeTab ?? 'pending') === 'history' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <span>Riwayat Pulang Otomatis</span>
        </button>
    </div>

    <!-- TAB 1: PERLU DIPULANGKAN (BELUM PULANG) -->
    <div id="tab-content-pending" class="{{ ($activeTab ?? 'pending') === 'pending' ? '' : 'hidden' }} space-y-4">
        <!-- Filter Bar for Pending -->
        <div class="bg-white p-4 rounded-2xl shadow-xs border border-gray-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.pulang-otomatis.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                <input type="hidden" name="tab" value="pending">
                <!-- Date Filter -->
                <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden bg-slate-50 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition">
                    <div class="px-3 text-gray-500">
                        <i class="fa-solid fa-calendar-day text-xs"></i>
                    </div>
                    <input type="date" name="date" value="{{ $selectedDate ?? date('Y-m-d') }}" onchange="this.form.submit()"
                        class="py-2 pr-3 bg-transparent text-xs sm:text-sm text-gray-800 focus:outline-none cursor-pointer">
                </div>

                <!-- Search Input -->
                <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden bg-slate-50 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition">
                    <div class="px-3 text-gray-500">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pemagang..."
                        class="py-2 pr-3 bg-transparent text-xs sm:text-sm text-gray-800 focus:outline-none w-full sm:w-56">
                </div>

                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition shadow-xs">
                    Filter
                </button>
                @if(request('search') || (request('date') && request('date') !== date('Y-m-d')))
                <a href="{{ route('admin.pulang-otomatis.index', ['tab' => 'pending']) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold transition flex items-center justify-center">
                    Reset
                </a>
                @endif
            </form>

            <!-- Bulk Actions -->
            <div class="flex items-center gap-2 self-end sm:self-auto">
                <button type="button" id="btn-bulk-checkout" onclick="openBulkModal()"
                    class="hidden px-4 py-2 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white rounded-xl text-xs font-bold shadow-sm transition items-center gap-2 animate-pulse">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Pulangkan Terpilih (<span id="selected-count">0</span>)</span>
                </button>
            </div>
        </div>

        <!-- Table of Pending Interns -->
        <div class="overflow-x-auto rounded-2xl border border-gray-200 shadow-xs bg-white">
            <table class="min-w-full text-xs sm:text-sm divide-y divide-gray-200">
                <thead class="bg-slate-50 text-slate-700 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4 text-center w-12">
                            <input type="checkbox" id="check-all-pending" onchange="toggleCheckAll(this)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="py-3.5 px-4 text-left">Pemagang</th>
                        <th class="py-3.5 px-4 text-left">Tanggal</th>
                        <th class="py-3.5 px-4 text-left">Kantor & Shift</th>
                        <th class="py-3.5 px-4 text-left">Jam Masuk</th>
                        <th class="py-3.5 px-4 text-left">Jam Pulang</th>
                        <th class="py-3.5 px-4 text-left">Status Waktu</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-slate-800">
                    @if(isset($pendingAttendances) && count($pendingAttendances) > 0)
                    @foreach($pendingAttendances as $attd)
                    @php
                    $profile = $attd->intern?->user?->profile;
                    $internName = $profile?->full_name ?? ($attd->intern?->user?->name ?? 'Pemagang');
                    $shift = $attd->detailSchedules?->shift ?? $attd->intern?->shift;
                    $shiftName = $shift?->name ?? 'Default';
                    $officeName = $attd->detailSchedules?->office?->name ?? ($shift?->office?->name ?? ($attd->intern?->office?->name ?? '-'));
                    $shiftEnd = $shift?->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('H:i') : '-';
                    $startTime = $attd->start_time ? \Carbon\Carbon::parse($attd->start_time)->format('H:i') : '-';
                    $dateFormatted = $attd->date ? \Carbon\Carbon::parse($attd->date)->format('d/m/Y') : '-';
                    $dayName = $attd->date ? \Carbon\Carbon::parse($attd->date)->translatedFormat('l') : '';

                    // Periksa apakah waktu shift sudah lewat
                    $isPastShift = false;
                    $timeDiffText = '';
                    if ($shift && $shift->end_time) {
                    $attdDateStr = $attd->date ? \Carbon\Carbon::parse($attd->date)->format('Y-m-d') : date('Y-m-d');
                    $shiftEndDateTime = \Carbon\Carbon::parse($attdDateStr . ' ' . $shift->end_time);
                    $now = \Carbon\Carbon::now();
                    if ($now->greaterThan($shiftEndDateTime)) {
                    $isPastShift = true;
                    $diffMins = $now->diffInMinutes($shiftEndDateTime);
                    $hours = floor($diffMins / 60);
                    $mins = $diffMins % 60;
                    $timeDiffText = $hours > 0 ? "Lewat {$hours}j {$mins}m" : "Lewat {$mins} menit";
                    } else {
                    $timeDiffText = 'Sedang berlangsung';
                    }
                    }
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3.5 px-4 text-center">
                            <input type="checkbox" name="pending_ids[]" value="{{ $attd->id }}" onchange="updateSelectedCount()" class="pending-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </td>
                        <td class="py-3.5 px-4">
                            <div>
                                <div class="font-bold text-gray-900 leading-tight">{{ $internName }}</div>
                                <div class="flex items-center gap-1.5 text-[11px] text-gray-500 mt-1">
                                    <span class="font-semibold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100">
                                        {{ $attd->intern?->division?->name ?? 'Tanpa Divisi' }}
                                    </span>
                                    @if($attd->intern?->school?->name)
                                    <span class="text-gray-300">•</span>
                                    <span class="text-gray-400 truncate max-w-[150px]">{{ $attd->intern->school->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap text-slate-700 font-medium">
                            <div class="flex items-center gap-1.5">
                                <i class="fa-regular fa-calendar text-slate-400 text-xs"></i>
                                <span class="font-semibold">{{ $dateFormatted }}</span>
                            </div>
                            @if($dayName)
                            <div class="text-[11px] text-slate-400 pl-4.5">{{ $dayName }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-700">
                            <div class="font-medium text-gray-900">{{ $shiftName }}</div>
                            <div class="text-[11px] text-slate-400 font-medium">{{ $officeName }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-emerald-700">
                            {{ $startTime }}
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-700">
                            {{ $shiftEnd }}
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            @if($isPastShift)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                {{ $timeDiffText }}
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                <i class="fa-solid fa-circle-dot text-[8px] text-emerald-500"></i>
                                {{ $timeDiffText }}
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <button type="button"
                                data-id="{{ $attd->id }}"
                                data-name="{{ $internName }}"
                                data-shift="{{ $shiftName }} ({{ $shiftEnd }})"
                                onclick="openSingleModal(this)"
                                class="px-3.5 py-1.5 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5 mx-auto">
                                <i class="fa-solid fa-arrow-right-from-bracket text-[10px]"></i>
                                <span>Pulangkan</span>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="8" class="py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center text-2xl mb-3 shadow-inner">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                                <h3 class="text-sm font-bold text-gray-800">Semua Pemagang Sudah Melakukan Presensi Pulang</h3>
                                <p class="text-xs text-gray-400 mt-1 max-w-sm">Tidak ada pemagang yang sedang aktif tanpa presensi pulang pada tanggal yang dipilih.</p>
                            </div>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: RIWAYAT PULANG OTOMATIS -->
    <div id="tab-content-history" class="{{ ($activeTab ?? 'pending') === 'history' ? '' : 'hidden' }} space-y-4">
        <div class="bg-white p-4 rounded-2xl shadow-xs border border-gray-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-4 w-full">
                <!-- Search Field -->
                <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden bg-slate-50 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition w-full sm:w-64">
                    <div class="px-3 text-gray-500">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Cari nama pemagang..."
                        class="py-2 pr-3 bg-transparent text-xs sm:text-sm text-gray-800 focus:outline-none w-full">
                </div>

                <!-- Date Field -->
                <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden bg-slate-50 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition w-full sm:w-auto">
                    <div class="px-3 text-gray-500">
                        <i class="fa-solid fa-calendar-day text-xs"></i>
                    </div>
                    <input type="date" id="dateInput"
                        class="py-2 pr-3 bg-transparent text-xs sm:text-sm text-gray-800 focus:outline-none cursor-pointer">
                </div>
            </div>
        </div>

        <!-- Livewire Component for History Table -->
        <div class="overflow-x-auto">
            @livewire('auto-attendance-list-component', ['autoAttdData' => $auto_attd_data['data'] ?? [], 'meta' => $auto_attd_data['meta'] ?? null])
        </div>

        <!-- Pagination Controls for History Table -->
        <div class="mt-4 flex flex-wrap justify-center items-center gap-2 border border-slate-200 rounded-2xl p-3 bg-white shadow-xs">
            <button id="prev-page"
                class="cursor-pointer bg-white text-blue-600 px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-xs sm:text-sm font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <i class="fa-solid fa-chevron-left text-xs mr-1"></i> Previous
            </button>

            <div id="page-numbers" class="flex flex-wrap gap-1 text-xs sm:text-sm"></div>

            <button id="next-page" class="cursor-pointer bg-white text-blue-600 px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-xs sm:text-sm font-semibold transition">
                Next <i class="fa-solid fa-chevron-right text-xs ml-1"></i>
            </button>
        </div>
    </div>

    <!-- MODAL KONFIRMASI PULANG OTOMATIS (SINGLE) -->
    <div id="single-checkout-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl p-6 w-full max-w-md border border-slate-200 animate-in fade-in zoom-in-95">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-gray-900">Konfirmasi Pulang Otomatis</h3>
                </div>
                <button type="button" onclick="closeSingleModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="single-checkout-form" method="POST" action="">
                @csrf
                <div class="space-y-4">
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200">
                        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Pemagang:</span>
                        <div id="modal-intern-name" class="font-bold text-sm text-gray-900">Nama Pemagang</div>
                        <div id="modal-shift-info" class="text-xs text-blue-700 font-medium mt-0.5">Shift Pagi</div>
                    </div>

                    <p class="text-xs text-gray-600">
                        Apakah Anda yakin ingin memulangkan pemagang ini secara otomatis? Jam pulang akan disesuaikan dengan jam akhir shift.
                    </p>

                    <!-- Input Catatan Opsional -->
                    <div>
                        <label for="single-note" class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center justify-between">
                            <span>Catatan untuk Pemagang</span>
                            <span class="text-[11px] font-normal text-gray-400 italic">(Opsional)</span>
                        </label>
                        <textarea name="note" id="single-note" rows="3"
                            placeholder="Contoh: Anda belum melakukan presensi pulang. Harap selalu absen pulang sebelum meninggalkan lokasi kerja."
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs sm:text-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:outline-none transition"></textarea>
                        <span class="text-[10px] text-gray-500 mt-1 block">
                            *Catatan ini akan tampil sebagai popup notifikasi 1x saat pemagang membuka aplikasi.
                        </span>
                    </div>
                </div>

                <div class="flex justify-end gap-2.5 mt-6 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeSingleModal()"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Konfirmasi Pulangkan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KONFIRMASI PULANG OTOMATIS MASSAL (BULK) -->
    <div id="bulk-checkout-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl p-6 w-full max-w-md border border-slate-200 animate-in fade-in zoom-in-95">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-gray-900">Pulang Otomatis Massal</h3>
                </div>
                <button type="button" onclick="closeBulkModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="bulk-checkout-form" method="POST" action="{{ route('admin.pulang-otomatis.bulk-confirm') }}">
                @csrf
                <div id="bulk-hidden-inputs"></div>

                <div class="space-y-4">
                    <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200 flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg"></i>
                        <div class="text-xs text-amber-900 font-medium">
                            Anda akan memulangkan <strong id="bulk-modal-count" class="font-bold text-amber-950">0</strong> pemagang yang dipilih secara bersamaan.
                        </div>
                    </div>

                    <!-- Input Catatan Opsional -->
                    <div>
                        <label for="bulk-note" class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center justify-between">
                            <span>Catatan untuk Seluruh Pemagang Terpilih</span>
                            <span class="text-[11px] font-normal text-gray-400 italic">(Opsional)</span>
                        </label>
                        <textarea name="note" id="bulk-note" rows="3"
                            placeholder="Contoh: Anda belum melakukan presensi pulang. Mohon untuk selalu absen pulang pada jadwal berikutnya."
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs sm:text-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:outline-none transition"></textarea>
                        <span class="text-[10px] text-gray-500 mt-1 block">
                            *Catatan ini akan tampil pada masing-masing pemagang saat login / membuka aplikasi.
                        </span>
                    </div>
                </div>

                <div class="flex justify-end gap-2.5 mt-6 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeBulkModal()"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Konfirmasi Pulangkan Semua</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="auto-attd-meta" data-meta='@json($auto_attd_data["meta"] ?? null)' class="hidden"></div>

    <script>
        // TAB SWITCHING
        function switchTab(tabName) {
            const pendingTab = document.getElementById('tab-content-pending');
            const historyTab = document.getElementById('tab-content-history');
            const pendingBtn = document.getElementById('tab-btn-pending');
            const historyBtn = document.getElementById('tab-btn-history');

            if (tabName === 'pending') {
                pendingTab.classList.remove('hidden');
                historyTab.classList.add('hidden');

                pendingBtn.className = "tab-btn px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 bg-blue-600 text-white shadow-sm";
                historyBtn.className = "tab-btn px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 text-gray-600 hover:bg-gray-100";
            } else {
                pendingTab.classList.add('hidden');
                historyTab.classList.remove('hidden');

                pendingBtn.className = "tab-btn px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 text-gray-600 hover:bg-gray-100";
                historyBtn.className = "tab-btn px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 bg-blue-600 text-white shadow-sm";
            }
        }

        // SINGLE MODAL
        function openSingleModal(attendanceIdOrEl, internName, shiftInfo) {
            let attendanceId, name, shift;
            if (typeof attendanceIdOrEl === 'object' && attendanceIdOrEl !== null) {
                attendanceId = attendanceIdOrEl.dataset.id;
                name = attendanceIdOrEl.dataset.name;
                shift = attendanceIdOrEl.dataset.shift;
            } else {
                attendanceId = attendanceIdOrEl;
                name = internName;
                shift = shiftInfo;
            }
            const form = document.getElementById('single-checkout-form');
            form.action = "{{ route('admin.pulang-otomatis.confirm', ':id') }}".replace(':id', attendanceId);

            document.getElementById('modal-intern-name').textContent = name;
            document.getElementById('modal-shift-info').textContent = shift;
            document.getElementById('single-note').value = '';

            document.getElementById('single-checkout-modal').classList.remove('hidden');
        }

        function closeSingleModal() {
            document.getElementById('single-checkout-modal').classList.add('hidden');
        }

        // CHECKBOXES & BULK MODAL
        function toggleCheckAll(source) {
            const checkboxes = document.querySelectorAll('.pending-checkbox');
            checkboxes.forEach(cb => cb.checked = source.checked);
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const selected = document.querySelectorAll('.pending-checkbox:checked');
            const count = selected.length;
            const btnBulk = document.getElementById('btn-bulk-checkout');
            const countSpan = document.getElementById('selected-count');

            countSpan.textContent = count;
            if (count > 0) {
                btnBulk.classList.remove('hidden');
                btnBulk.classList.add('flex');
            } else {
                btnBulk.classList.add('hidden');
                btnBulk.classList.remove('flex');
            }
        }

        function openBulkModal() {
            const selected = document.querySelectorAll('.pending-checkbox:checked');
            if (selected.length === 0) return;

            const hiddenContainer = document.getElementById('bulk-hidden-inputs');
            hiddenContainer.innerHTML = '';

            selected.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'attendance_ids[]';
                input.value = cb.value;
                hiddenContainer.appendChild(input);
            });

            document.getElementById('bulk-modal-count').textContent = selected.length;
            document.getElementById('bulk-note').value = '';
            document.getElementById('bulk-checkout-modal').classList.remove('hidden');
        }

        function closeBulkModal() {
            document.getElementById('bulk-checkout-modal').classList.add('hidden');
        }

        // LIVEWIRE SEARCH & PAGINATION FOR HISTORY TAB
        const searchInput = document.getElementById('searchInput');
        const dateInput = document.getElementById('dateInput');
        let debounceTimeout;

        function dispatchSearch(currentPage = 1) {
            const searchTerm = searchInput ? searchInput.value.trim() : '';
            const dateValue = dateInput ? dateInput.value : '';

            const payload = {
                currentPage: currentPage,
                searchTerm: searchTerm,
                dateValue: dateValue
            };

            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('searchAutoAttd', {
                    payload: payload
                });
            }
        }

        function handleInputEvent() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                dispatchSearch(1);
            }, 400);
        }

        if (searchInput) searchInput.addEventListener('input', handleInputEvent);
        if (dateInput) dateInput.addEventListener('change', () => dispatchSearch(1));

        function updatePagination(currentPage, totalPages, totalData) {
            const prevButton = $('#prev-page');
            const nextButton = $('#next-page');
            const totalPresence = $('#total_presence');

            if (searchInput && dateInput && searchInput.value.trim() === '' && dateInput.value.trim() === '') {
                totalPresence.text("{{ $total_auto_end_today ?? 0 }}");
            } else {
                totalPresence.text(totalData);
            }

            const pageNumbersContainer = $('#page-numbers');
            prevButton.prop('disabled', currentPage <= 1);
            nextButton.prop('disabled', currentPage >= totalPages);

            prevButton.off('click').on('click', function() {
                if (currentPage > 1) dispatchSearch(currentPage - 1);
            });
            nextButton.off('click').on('click', function() {
                if (currentPage < totalPages) dispatchSearch(currentPage + 1);
            });

            pageNumbersContainer.empty();

            const maxPagesToShow = 5;
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
            if (endPage - startPage < maxPagesToShow - 1) {
                startPage = Math.max(1, endPage - maxPagesToShow + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageNumber = $('<button></button>')
                    .text(i)
                    .addClass('cursor-pointer px-3.5 py-1.5 rounded-xl border text-xs font-semibold transition')
                    .toggleClass('bg-blue-600 text-white border-blue-600 shadow-xs', i === currentPage)
                    .toggleClass('text-slate-700 bg-white border-slate-300 hover:bg-slate-50', i !== currentPage)
                    .on('click', function() {
                        dispatchSearch(i);
                    });

                pageNumbersContainer.append(pageNumber);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Livewire !== 'undefined') {
                Livewire.on('updatePaginate', ({
                    meta
                }) => {
                    if (meta) {
                        updatePagination(meta.current_page, meta.total_page, meta.total_data);
                    }
                });
            }
            const metaEl = document.getElementById('auto-attd-meta');
            if (metaEl && metaEl.dataset.meta) {
                try {
                    const meta = JSON.parse(metaEl.dataset.meta);
                    if (meta) {
                        updatePagination(
                            parseInt(meta.current_page || 1, 10),
                            parseInt(meta.total_page || 1, 10),
                            parseInt(meta.total_data || 0, 10)
                        );
                    }
                } catch (e) {
                    console.error('Error parsing pagination meta', e);
                }
            }
        });
    </script>
</main>
@endsection