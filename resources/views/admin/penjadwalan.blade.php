@extends('layouts.main')

@section('title', 'Penjadwalan Shift')

@section('contents')
    <!-- Select2 Searchable Dropdown CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Select2 Custom Tailwind Styling for Penjadwalan Filters */
        .select2-container {
            width: 100% !important;
        }
        .select2-container--default .select2-selection--single {
            height: 38px !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 0.75rem !important; /* rounded-xl */
            padding: 0 10px !important;
            display: flex !important;
            align-items: center !important;
            background-color: #ffffff !important;
            font-size: 0.875rem !important; /* text-sm */
            transition: all 0.15s ease-in-out !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #374151 !important;
            line-height: 36px !important;
            padding-left: 0 !important;
            padding-right: 20px !important;
            font-weight: 500 !important;
            font-size: 0.875rem !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 8px !important;
        }
        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
            outline: none !important;
        }
        .select2-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
            font-size: 0.8125rem !important;
            z-index: 50 !important;
            overflow: hidden !important;
            margin-top: 4px !important;
        }
        .select2-search--dropdown {
            padding: 8px 10px !important;
            position: relative !important;
            background-color: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.5rem !important;
            padding: 6px 10px 6px 28px !important;
            font-size: 0.8125rem !important;
            outline: none !important;
            background-color: #ffffff !important;
            box-sizing: border-box !important;
            width: 100% !important;
        }
        .select2-search--dropdown .select2-search__field:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 1px #3b82f6 !important;
        }
        .select2-search--dropdown::before {
            content: "\f002";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            left: 19px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 11px;
            color: #94a3b8;
            pointer-events: none;
            z-index: 10;
        }
        .select2-results__options {
            max-height: 220px !important;
        }
        .select2-results__option {
            padding: 7px 12px !important;
            font-size: 0.8125rem !important;
            color: #334155 !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #eff6ff !important;
            color: #1d4ed8 !important;
            font-weight: 600 !important;
        }
    </style>

    <!-- Main Content -->
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-6">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-calendar-days text-blue-600"></i>
                    <span>Penjadwalan Shift Pemagang</span>
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">Kelola pembagian shift kerja, kantor penempatan, dan rotasi rolling untuk seluruh pemagang.</p>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('admin.automation-rules.index') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-semibold text-xs rounded-xl shadow-xs hover:shadow transition">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>Otomatisasi Jadwal</span>
                </a>
            </div>
        </div>

        @if (session('success'))
            <div id="success-message"
                class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl relative transition-opacity duration-500 flex items-center justify-between shadow-xs"
                role="alert">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-green-600 text-lg"></i>
                    <div>
                        <strong class="font-bold">Berhasil!</strong>
                        <span class="block sm:inline text-sm">{{ session('success') }}</span>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('success-message').remove()" class="text-green-600 hover:text-green-800">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div id="error-message"
                class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl relative transition-opacity duration-500 flex items-center justify-between shadow-xs"
                role="alert">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-red-600 text-lg"></i>
                    <div>
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline text-sm">{{ session('error') }}</span>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('error-message').remove()" class="text-red-600 hover:text-red-800">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        <!-- Summary Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 mb-6">
            <div class="bg-white rounded-xl p-3 sm:p-4 border border-gray-100 shadow-xs flex items-center gap-2.5 sm:gap-3 min-w-0">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold shrink-0">
                    <i class="fa-solid fa-users text-base sm:text-lg"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] sm:text-xs text-gray-500 font-medium truncate">Total Pemagang</div>
                    <div class="text-base sm:text-lg font-bold text-gray-900 leading-tight">{{ $totalInterns ?? $interns->total() }}</div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-3 sm:p-4 border border-gray-100 shadow-xs flex items-center gap-2.5 sm:gap-3 min-w-0">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold shrink-0">
                    <i class="fa-solid fa-arrows-rotate text-base sm:text-lg"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] sm:text-xs text-gray-500 font-medium truncate">Rolling</div>
                    <div class="text-base sm:text-lg font-bold text-indigo-700 leading-tight">{{ $totalRolling ?? 0 }}</div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-3 sm:p-4 border border-gray-100 shadow-xs flex items-center gap-2.5 sm:gap-3 min-w-0">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-slate-50 text-slate-600 flex items-center justify-center font-bold shrink-0">
                    <i class="fa-solid fa-calendar-day text-base sm:text-lg"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] sm:text-xs text-gray-500 font-medium truncate">Shift Tetap</div>
                    <div class="text-base sm:text-lg font-bold text-slate-800 leading-tight">{{ $totalFixed ?? 0 }}</div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-3 sm:p-4 border border-gray-100 shadow-xs flex items-center gap-2.5 sm:gap-3 min-w-0">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-base sm:text-lg"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] sm:text-xs text-gray-500 font-medium truncate">Belum Diatur</div>
                    <div class="text-base sm:text-lg font-bold text-amber-700 leading-tight">{{ $totalUnset ?? 0 }}</div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-3.5 sm:p-4 mb-6">
            <form action="{{ route('admin.penjadwalan.index') }}" method="GET" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
                    <!-- Search Input (Server + Instant Filter) -->
                    <div class="sm:col-span-2 lg:col-span-1 xl:col-span-1">
                        <label for="searchInput" class="block text-xs font-semibold text-gray-600 mb-1">Cari Pemagang</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                <i class="fa-solid fa-search text-sm"></i>
                            </span>
                            <input type="text" id="searchInput" name="search" value="{{ request('search') }}"
                                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                placeholder="Ketik nama, NIM, sekolah...">
                        </div>
                    </div>

                    <!-- Filter Sekolah -->
                    <div>
                        <label for="school_id" class="block text-xs font-semibold text-gray-600 mb-1">Sekolah / Kampus</label>
                        <select name="school_id" id="school_id" onchange="this.form.submit()"
                            class="filter-searchable-select w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            <option value="">Semua Sekolah</option>
                            @foreach ($schools as $sch)
                                <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>
                                    {{ $sch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Divisi -->
                    <div>
                        <label for="division_id" class="block text-xs font-semibold text-gray-600 mb-1">Divisi</label>
                        <select name="division_id" id="division_id" onchange="this.form.submit()"
                            class="filter-searchable-select w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            <option value="">Semua Divisi</option>
                            @foreach ($divisions as $div)
                                <option value="{{ $div->id }}" {{ request('division_id') == $div->id ? 'selected' : '' }}>
                                    {{ $div->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Kantor -->
                    <div>
                        <label for="office_id_filter" class="block text-xs font-semibold text-gray-600 mb-1">Kantor</label>
                        <select name="office_id" id="office_id_filter" onchange="this.form.submit()"
                            class="filter-searchable-select w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            <option value="">Semua Kantor</option>
                            @foreach ($offices as $off)
                                <option value="{{ $off->id }}" {{ request('office_id') == $off->id ? 'selected' : '' }}>
                                    {{ $off->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Sisa Hari Jadwal -->
                    <div>
                        <label for="schedule_remaining" class="block text-xs font-semibold text-gray-600 mb-1">Sisa Hari Jadwal</label>
                        <select name="schedule_remaining" id="schedule_remaining" onchange="this.form.submit()"
                            class="filter-searchable-select w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            <option value="">Semua Sisa Jadwal</option>
                            <option value="today" {{ request('schedule_remaining') === 'today' ? 'selected' : '' }}>Hari Terakhir (0 hr)</option>
                            <option value="soon_3" {{ request('schedule_remaining') === 'soon_3' ? 'selected' : '' }}>Sisa ≤ 3 Hari</option>
                            <option value="soon_7" {{ request('schedule_remaining') === 'soon_7' ? 'selected' : '' }}>Sisa ≤ 7 Hari</option>
                            <option value="active" {{ request('schedule_remaining') === 'active' ? 'selected' : '' }}>Sisa > 7 Hari</option>
                            <option value="upcoming" {{ request('schedule_remaining') === 'upcoming' ? 'selected' : '' }}>Belum Mulai</option>
                            <option value="expired" {{ request('schedule_remaining') === 'expired' ? 'selected' : '' }}>Jadwal Berakhir</option>
                            <option value="unset" {{ request('schedule_remaining') === 'unset' ? 'selected' : '' }}>Belum Diatur</option>
                        </select>
                    </div>

                    <!-- Filter Sisa Hari Magang -->
                    <div>
                        <label for="intern_remaining" class="block text-xs font-semibold text-gray-600 mb-1">Sisa Hari Magang</label>
                        <select name="intern_remaining" id="intern_remaining" onchange="this.form.submit()"
                            class="filter-searchable-select w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            <option value="">Semua Sisa Magang</option>
                            <option value="today" {{ request('intern_remaining') === 'today' ? 'selected' : '' }}>Hari Terakhir (0 hr)</option>
                            <option value="soon_7" {{ request('intern_remaining') === 'soon_7' ? 'selected' : '' }}>Sisa ≤ 7 Hari</option>
                            <option value="soon_30" {{ request('intern_remaining') === 'soon_30' ? 'selected' : '' }}>Sisa ≤ 30 Hari (1 Bln)</option>
                            <option value="active" {{ request('intern_remaining') === 'active' ? 'selected' : '' }}>Sisa > 30 Hari</option>
                            <option value="upcoming" {{ request('intern_remaining') === 'upcoming' ? 'selected' : '' }}>Belum Mulai</option>
                            <option value="expired" {{ request('intern_remaining') === 'expired' ? 'selected' : '' }}>Magang Selesai</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-gray-100">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <!-- Filter Sistem Shift Toggle -->
                        <span class="text-xs text-gray-500 mr-1 font-medium">Sistem:</span>
                        <a href="{{ request()->fullUrlWithQuery(['system_type' => '']) }}"
                            class="px-2.5 py-1 text-xs rounded-lg transition {{ !request('system_type') ? 'bg-blue-600 text-white font-bold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Semua</a>
                        <a href="{{ request()->fullUrlWithQuery(['system_type' => 'weekly']) }}"
                            class="px-2.5 py-1 text-xs rounded-lg transition {{ request('system_type') === 'weekly' ? 'bg-blue-600 text-white font-bold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Rolling</a>
                        <a href="{{ request()->fullUrlWithQuery(['system_type' => 'daily']) }}"
                            class="px-2.5 py-1 text-xs rounded-lg transition {{ request('system_type') === 'daily' ? 'bg-blue-600 text-white font-bold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Tetap</a>
                        <a href="{{ request()->fullUrlWithQuery(['system_type' => 'none']) }}"
                            class="px-2.5 py-1 text-xs rounded-lg transition {{ request('system_type') === 'none' ? 'bg-amber-600 text-white font-bold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Belum Diatur</a>
                    </div>
                    <div class="flex items-center gap-2 justify-end">
                        <a href="{{ route('admin.penjadwalan.index') }}"
                            class="flex-1 sm:flex-initial text-center px-3.5 py-1.5 text-xs text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50 rounded-xl transition font-medium">
                            Reset Filter
                        </a>
                        <button type="submit"
                            class="flex-1 sm:flex-initial text-center px-4 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-xs transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-filter text-xs"></i>
                            Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
            <!-- Mobile Scroll Indicator Hint -->
            <div class="sm:hidden px-4 py-2 bg-blue-50/70 border-b border-blue-100/80 text-blue-700 text-[11px] flex items-center justify-between">
                <span class="flex items-center gap-1.5 font-medium">
                    <i class="fa-solid fa-arrows-left-right text-blue-500"></i>
                    Geser tabel ke samping untuk melihat kolom lengkap
                </span>
                <i class="fa-solid fa-chevron-right text-[10px] text-blue-400"></i>
            </div>

            <div class="overflow-x-auto touch-pan-x">
                <table class="w-full min-w-[980px] divide-y divide-gray-200">
                    <thead class="bg-gray-50/75 text-gray-700 text-xs uppercase font-semibold">
                        <tr>
                            <th class="py-3.5 px-4 text-left w-12">No</th>
                            <th class="py-3.5 px-4 text-left">Pemagang & Sekolah</th>
                            <th class="py-3.5 px-4 text-left">Divisi</th>
                            <th class="py-3.5 px-4 text-left">Kantor</th>
                            <th class="py-3.5 px-4 text-left">Sistem Shift</th>
                            <th class="py-3.5 px-4 text-left">Shift Kerja</th>
                            <th class="py-3.5 px-4 text-left">Periode & Sisa Jadwal</th>
                            <th class="py-3.5 px-4 text-left">Periode & Sisa Magang</th>
                            <th class="py-3.5 px-4 text-center w-36">Aksi</th>
                        </tr>
                    </thead>

                    @if ($interns->isEmpty())
                        <tbody>
                            <tr>
                                <td colspan="9" class="py-12 px-4 text-center">
                                    <div class="max-w-xs mx-auto text-center">
                                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3 text-gray-400">
                                            <i class="fa-solid fa-user-slash text-xl"></i>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900">Tidak ada pemagang ditemukan</h3>
                                        <p class="text-xs text-gray-500 mt-1">Coba sesuaikan kata kunci pencarian atau reset filter.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @else
                        <tbody id="teamTableBody" class="divide-y divide-gray-100 text-sm">
                            @foreach ($interns as $key => $data)
                                @php
                                    $sched = $data->schedules->sortByDesc('id')->first() ?? $data->schedules->first();
                                    $schedOffice = $sched?->office?->name ?? ($data->lockedOffice?->name ?? '');
                                    $currentShift = $data->resolved_shift ?? $data->shift;
                                    $schedShiftId = $data->resolved_shift_id ?? ($data->shift_id ?? $sched?->detailSchedules?->first()?->shift_id);
                                    $schedType = $sched ? ($sched->type === 'weekly' ? '2' : '1') : '1';
                                    $isWeekly = $sched && $sched->type === 'weekly';

                                    // Nilai untuk modal prefill
                                    $modalStart = $sched?->start_period ?? $data->start_date;
                                    $modalEnd = $sched?->end_period ?? $data->end_date;

                                    $today = \Carbon\Carbon::today();

                                    // 1. Perhitungan Periode & Sisa Hari Jadwal
                                    $hasSchedule = $sched && !empty($sched->start_period) && !empty($sched->end_period);
                                    $schedBadgeText = null;
                                    $schedBadgeClass = '';
                                    $schedTotalDays = 0;

                                    if ($hasSchedule) {
                                        try {
                                            $sStart = \Carbon\Carbon::parse($sched->start_period)->startOfDay();
                                            $sEnd = \Carbon\Carbon::parse($sched->end_period)->startOfDay();
                                            $schedTotalDays = (int) $sStart->diffInDays($sEnd) + 1;

                                            if ($today->gt($sEnd)) {
                                                $schedBadgeText = 'Jadwal Berakhir';
                                                $schedTextClass = 'text-gray-500 font-medium';
                                                $schedDotClass = 'bg-gray-400';
                                            } elseif ($today->lt($sStart)) {
                                                $daysToStartS = (int) $today->diffInDays($sStart);
                                                $schedBadgeText = 'Mulai ' . ($daysToStartS === 0 ? 'hari ini' : $daysToStartS . ' hr lagi');
                                                $schedTextClass = 'text-blue-600 font-medium';
                                                $schedDotClass = 'bg-blue-500';
                                            } else {
                                                $sisaHariJadwal = (int) $today->diffInDays($sEnd);
                                                if ($sisaHariJadwal === 0) {
                                                    $schedBadgeText = 'Hari Terakhir';
                                                    $schedTextClass = 'text-rose-600 font-semibold';
                                                    $schedDotClass = 'bg-rose-500';
                                                } elseif ($sisaHariJadwal <= 3) {
                                                    $schedBadgeText = 'Sisa ' . $sisaHariJadwal . ' hari';
                                                    $schedTextClass = 'text-rose-600 font-semibold';
                                                    $schedDotClass = 'bg-rose-500';
                                                } elseif ($sisaHariJadwal <= 7) {
                                                    $schedBadgeText = 'Sisa ' . $sisaHariJadwal . ' hari';
                                                    $schedTextClass = 'text-amber-600 font-medium';
                                                    $schedDotClass = 'bg-amber-500';
                                                } else {
                                                    $schedBadgeText = 'Sisa ' . $sisaHariJadwal . ' hari';
                                                    $schedTextClass = 'text-emerald-600 font-medium';
                                                    $schedDotClass = 'bg-emerald-500';
                                                }
                                            }
                                        } catch (\Throwable $e) {
                                            $hasSchedule = false;
                                        }
                                    }

                                    // 2. Perhitungan Periode & Sisa Hari Magang
                                    $hasInternPeriod = !empty($data->start_date) && !empty($data->end_date);
                                    $internBadgeText = null;
                                    $internTextClass = '';
                                    $internDotClass = '';
                                    $internTotalDays = 0;

                                    if ($hasInternPeriod) {
                                        try {
                                            $mStart = \Carbon\Carbon::parse($data->start_date)->startOfDay();
                                            $mEnd = \Carbon\Carbon::parse($data->end_date)->startOfDay();
                                            $internTotalDays = (int) $mStart->diffInDays($mEnd) + 1;

                                            if ($today->gt($mEnd)) {
                                                $internBadgeText = 'Magang Selesai';
                                                $internTextClass = 'text-gray-500 font-medium';
                                                $internDotClass = 'bg-gray-400';
                                            } elseif ($today->lt($mStart)) {
                                                $daysToStartM = (int) $today->diffInDays($mStart);
                                                $internBadgeText = 'Mulai ' . ($daysToStartM === 0 ? 'hari ini' : $daysToStartM . ' hr lagi');
                                                $internTextClass = 'text-blue-600 font-medium';
                                                $internDotClass = 'bg-blue-500';
                                            } else {
                                                $sisaHariMagang = (int) $today->diffInDays($mEnd);
                                                if ($sisaHariMagang === 0) {
                                                    $internBadgeText = 'Hari Terakhir';
                                                    $internTextClass = 'text-rose-600 font-semibold';
                                                    $internDotClass = 'bg-rose-500';
                                                } elseif ($sisaHariMagang <= 7) {
                                                    $internBadgeText = 'Sisa ' . $sisaHariMagang . ' hari';
                                                    $internTextClass = 'text-amber-600 font-medium';
                                                    $internDotClass = 'bg-amber-500';
                                                } else {
                                                    $internBadgeText = 'Sisa ' . $sisaHariMagang . ' hari';
                                                    $internTextClass = 'text-emerald-600 font-medium';
                                                    $internDotClass = 'bg-emerald-500';
                                                }
                                            }
                                        } catch (\Throwable $e) {
                                            $hasInternPeriod = false;
                                        }
                                    }
                                @endphp
                                <tr class="hover:bg-blue-50/30 transition">
                                    <td class="py-3.5 px-4 text-gray-500 font-medium">
                                        {{ method_exists($interns, 'firstItem') ? ($interns->firstItem() + $key) : ($key + 1) }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-gray-900">{{ $data->user->profile->full_name ?? ($data->user->name ?? 'Tanpa Nama') }}</div>
                                        <div class="flex items-center gap-1.5 text-xs text-gray-600 mt-1">
                                            <i class="fa-solid fa-graduation-cap text-gray-400"></i>
                                            <span class="font-medium">{{ $data->school->name ?? 'Sekolah tidak terdaftar' }}</span>
                                        </div>
                                        @if ($data->nim)
                                             <div class="text-[11px] text-gray-500 font-mono mt-0.5 flex items-center gap-1 pl-4">
                                                <span class="text-gray-400">NIM:</span>
                                                <span class="font-semibold text-gray-700">{{ $data->nim }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if ($data->division)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-800">
                                                <i class="fa-solid fa-sitemap text-[10px] text-gray-500"></i>
                                                {{ $data->division->name }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum Ada</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @php
                                            $officeNames = $data->schedules->pluck('office.name')->filter()->unique()->implode(', ') ?: ($data->lockedOffice->name ?? '');
                                        @endphp
                                        @if ($officeNames)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-100">
                                                <i class="fa-solid fa-building text-[10px] text-emerald-600"></i>
                                                {{ $officeNames }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum Ditentukan</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if ($sched)
                                            @if ($isWeekly)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                                    <i class="fa-solid fa-arrows-rotate text-[11px]"></i>
                                                    Rolling
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                                    <i class="fa-solid fa-calendar-day text-[11px]"></i>
                                                    Tetap
                                                </span>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                                Belum Diatur
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if ($currentShift)
                                            <div class="text-xs font-semibold text-gray-800">{{ $currentShift->name }}</div>
                                            <div class="text-[11px] text-gray-500 font-mono">
                                                {{ \Carbon\Carbon::parse($currentShift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($currentShift->end_time)->format('H:i') }}
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                                Belum Ada Shift
                                            </span>
                                        @endif
                                    </td>
                                    {{-- Kolom Periode & Sisa Jadwal --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap text-xs">
                                        @if ($hasSchedule)
                                            <div class="flex items-center gap-1.5 text-gray-700 font-medium">
                                                <i class="fa-regular fa-calendar-check text-gray-400 text-xs"></i>
                                                <span>{{ \Carbon\Carbon::parse($sched->start_period)->format('d M Y') }}</span>
                                                <span class="text-gray-400">-</span>
                                                <span>{{ \Carbon\Carbon::parse($sched->end_period)->format('d M Y') }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-1">
                                                <span class="inline-flex items-center text-[11px] {{ $schedTextClass }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $schedDotClass }} mr-1.5"></span>
                                                    {{ $schedBadgeText }}
                                                </span>
                                                <span class="text-[11px] text-gray-400">
                                                    ({{ $schedTotalDays }} hari)
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum Diatur</span>
                                        @endif
                                    </td>
                                    {{-- Kolom Periode & Sisa Magang --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap text-xs">
                                        @if ($hasInternPeriod)
                                            <div class="flex items-center gap-1.5 text-gray-700 font-medium">
                                                <i class="fa-regular fa-calendar-days text-gray-400 text-xs"></i>
                                                <span>{{ \Carbon\Carbon::parse($data->start_date)->format('d M Y') }}</span>
                                                <span class="text-gray-400">-</span>
                                                <span>{{ \Carbon\Carbon::parse($data->end_date)->format('d M Y') }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-1">
                                                <span class="inline-flex items-center text-[11px] {{ $internTextClass }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $internDotClass }} mr-1.5"></span>
                                                    {{ $internBadgeText }}
                                                </span>
                                                <span class="text-[11px] text-gray-400">
                                                    ({{ $internTotalDays }} hari)
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum Diatur</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap text-center">
                                        <div class="relative inline-block text-left dropdown-container">
                                            <!-- Toggle Dropdown Button -->
                                            <button
                                                type="button"
                                                onclick="toggleActionDropdown(event, 'action-dropdown-{{ $data->id }}')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 text-xs font-semibold rounded-xl transition border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 shadow-2xs">
                                                <span>Aksi</span>
                                                <i class="fa-solid fa-chevron-down text-[10px] text-gray-500 transition-transform duration-200 dropdown-arrow"></i>
                                            </button>

                                            <!-- Dropdown Menu -->
                                            <div id="action-dropdown-{{ $data->id }}"
                                                class="action-dropdown-menu hidden absolute right-0 mt-1.5 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-40 text-left">
                                                <button
                                                    type="button"
                                                    data-id="{{ $data->id }}"
                                                    data-name="{{ $data->user->profile->full_name ?? ($data->user->name ?? '') }}"
                                                    data-division="{{ $data->division->name ?? 'Belum ada divisi' }}"
                                                    data-office="{{ $schedOffice }}"
                                                    data-shift="{{ $schedShiftId }}"
                                                    data-type="{{ $schedType }}"
                                                    data-start="{{ $modalStart ? \Carbon\Carbon::parse($modalStart)->format('Y-m-d') : '' }}"
                                                    data-end="{{ $modalEnd ? \Carbon\Carbon::parse($modalEnd)->format('Y-m-d') : '' }}"
                                                    onclick="closeAllDropdowns(); openModal(this.dataset.id, this.dataset.name, this.dataset.division, this.dataset.office, this.dataset.shift, this.dataset.type, this.dataset.start, this.dataset.end)"
                                                    class="w-full text-left px-3.5 py-2 text-xs font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center gap-2.5 transition">
                                                    <i class="fa-solid {{ $sched ? 'fa-pen-to-square text-blue-600' : 'fa-plus text-emerald-600' }} w-4 text-center"></i>
                                                    <span>{{ $sched ? 'Atur Ulang Shift' : 'Atur Shift Baru' }}</span>
                                                </button>
                                                <a href="{{ route('admin.shift.schedule.update', ['internId' => $data->id]) }}"
                                                    onclick="closeAllDropdowns()"
                                                    class="w-full text-left px-3.5 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 flex items-center gap-2.5 transition border-t border-gray-100">
                                                    <i class="fa-regular fa-calendar-days text-gray-500 w-4 text-center"></i>
                                                    <span>Kalender Detail</span>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                </table>
            </div>

            <!-- Pagination Bar (50 Pemagang per Halaman) -->
            @if (method_exists($interns, 'hasPages') && $interns->hasPages())
                <div class="px-3 sm:px-5 py-3 sm:py-3.5 border-t border-gray-100 bg-gray-50/50 overflow-x-auto">
                    {{ $interns->links() }}
                </div>
            @endif
        </div>

        <!-- Shift Setting Modal Form -->
        <form id="shiftForm" action="{{ route('create.Schedule') }}" method="POST">
            @csrf
            <div id="shiftModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-xs z-50 hidden transition-opacity p-3 sm:p-4 overflow-y-auto">
                <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full my-auto max-h-[92vh] flex flex-col overflow-hidden">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between p-4 sm:p-5 border-b border-gray-100 shrink-0">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-clock text-base"></i>
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-bold text-gray-900">Atur Shift Pemagang</h2>
                                <p class="text-[11px] sm:text-xs text-gray-500">Tentukan konfigurasi jadwal shift dan kantor penempatan</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 flex items-center justify-center transition">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <!-- Modal Body (Scrollable for mobile screens) -->
                    <div class="p-4 sm:p-6 overflow-y-auto space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                            <input type="hidden" id="intern-id" name="intern_id">

                            <div>
                                <label for="internName" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nama Pemagang</label>
                                <input id="internName" type="text" value="" disabled
                                    class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 text-gray-700 font-medium">
                            </div>

                            <div>
                                <label for="division" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Divisi</label>
                                <input id="division" type="text" value="" disabled
                                    class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 text-gray-700 font-medium">
                            </div>

                            <div>
                                <label for="type" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Sistem Shift</label>
                                <select id="type" name="type"
                                    class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                    required>
                                    <option value="1">Tetap (Shift sama setiap hari)</option>
                                    <option value="2">Rolling</option>
                                </select>
                            </div>

                            <div>
                                <label for="shift_id" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Shift Kerja</label>
                                <select id="shift_id" name="shift_id"
                                    class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                    required>
                                    <option value="" disabled selected>-- Pilih Shift Kerja --</option>
                                    @foreach ($shifts as $item)
                                        <option value="{{ $item->id }}" data-rolling="{{ $item->is_rolling ? '1' : '0' }}">
                                            {{ $item->name }} {{ $item->is_rolling ? '✓ (Bisa Rolling)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Helper Info saat Sistem Bergantian Dipilih -->
                            <div id="rollingInfoAlert" class="col-span-1 sm:col-span-2 hidden p-3 bg-blue-50 text-blue-900 rounded-xl border border-blue-200 text-xs">
                                <div class="flex items-start gap-2.5">
                                    <i class="fa-solid fa-arrows-rotate text-blue-600 mt-0.5 text-sm shrink-0"></i>
                                    <div>
                                        <p class="font-bold">Sistem Shift Rolling Aktif</p>
                                        <p class="text-blue-700 mt-0.5 leading-relaxed">Shift akan otomatis bergantian setiap minggu antara shift yang diaktifkan untuk rolling di Pengaturan Shift (contoh: Pagi ↔ Siang). Pilihan shift di atas menentukan shift untuk <strong>minggu pertama</strong>.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Helper Info saat Kantor Hanya Memiliki 1 Shift -->
                            <div id="singleShiftNotice" class="col-span-1 sm:col-span-2 hidden p-3 bg-amber-50 text-amber-900 rounded-xl border border-amber-200 text-xs">
                                <div class="flex items-start gap-2.5">
                                    <i class="fa-solid fa-circle-info text-amber-600 mt-0.5 text-sm shrink-0"></i>
                                    <div>
                                        <p class="font-bold">Kantor Ini Hanya Memiliki 1 Shift Aktif</p>
                                        <p class="text-amber-700 mt-0.5 leading-relaxed">Kantor yang dipilih hanya mengaktifkan 1 shift kerja. Oleh karena itu, sistem shift otomatis dikunci menjadi <strong>Tetap</strong> (rotasi/rolling tidak berlaku).</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="office_id" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Kantor Penempatan</label>
                                <select id="office_id" name="office_id"
                                    class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                    required>
                                    <option value="" disabled selected>-- Pilih Kantor --</option>
                                    @foreach ($offices as $item)
                                        @php
                                            $enabledShiftIds = $item->shiftCapacities ? $item->shiftCapacities->where('is_enabled', true)->where('max_capacity', '>', 0)->pluck('shift_id')->values()->toArray() : [];
                                        @endphp
                                        <option value="{{ $item->id }}" data-shifts="{{ json_encode($enabledShiftIds) }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="start_period" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Periode Jadwal Shift</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input id="start_period" name="start_period" type="date"
                                        class="w-full px-2.5 sm:px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                        required>
                                    <input id="end_period" name="end_period" type="date"
                                        class="w-full px-2.5 sm:px-3 py-2 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                        required>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-1">Rentang tanggal awal dan akhir berlakunya jadwal shift ini.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-2 p-4 sm:p-5 border-t border-gray-100 bg-gray-50/50 shrink-0">
                        <button type="button" onclick="closeModal()"
                            class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 rounded-xl text-xs font-bold transition">Batal</button>
                        <button type="submit"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>Simpan Jadwal</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <script>
            function openModal(id, name, division, officeName, shiftId, type, startDate, endDate) {
                const shiftModal = document.getElementById('shiftModal');
                if (!shiftModal) return;

                shiftModal.classList.remove('hidden');

                const internIdInput = document.getElementById('intern-id');
                if (internIdInput) internIdInput.value = id || '';

                const internNameInput = document.getElementById('internName');
                if (internNameInput) internNameInput.value = name || '';

                const divisionInput = document.getElementById('division');
                if (divisionInput) divisionInput.value = division || '';

                const officeDropdown = document.getElementById('office_id');
                if (officeDropdown && officeName) {
                    for (let i = 0; i < officeDropdown.options.length; i++) {
                        if (officeDropdown.options[i].text.trim() === officeName.trim()) {
                            officeDropdown.selectedIndex = i;
                            break;
                        }
                    }
                }

                const typeDropdown = document.getElementById('type');
                if (typeDropdown) {
                    typeDropdown.value = type ? String(type) : '1';
                    toggleRollingInfo(typeDropdown.value);
                }

                const shiftDropdown = document.getElementById('shift_id');
                if (shiftDropdown && shiftId) {
                    shiftDropdown.value = String(shiftId);
                }

                if (startDate) {
                    const startInput = document.getElementById('start_period');
                    if (startInput) startInput.value = startDate;
                }
                if (endDate) {
                    const endInput = document.getElementById('end_period');
                    if (endInput) endInput.value = endDate;
                }

                // Cek pembatasan shift kantor (jika hanya 1 shift, paksa Tetap)
                handleOfficeShiftRestriction();
            }

            function handleOfficeShiftRestriction() {
                const officeDropdown = document.getElementById('office_id');
                const typeDropdown = document.getElementById('type');
                const shiftDropdown = document.getElementById('shift_id');
                const singleNotice = document.getElementById('singleShiftNotice');
                if (!officeDropdown || !typeDropdown) return;

                const selectedOption = officeDropdown.options[officeDropdown.selectedIndex];
                let enabledShifts = [];
                if (selectedOption && selectedOption.getAttribute('data-shifts')) {
                    try {
                        enabledShifts = JSON.parse(selectedOption.getAttribute('data-shifts'));
                    } catch(e) {}
                }

                if (enabledShifts.length === 1) {
                    // Kantor hanya memiliki 1 shift aktif (misal Kantor 2)
                    typeDropdown.value = '1';
                    typeDropdown.disabled = true;
                    toggleRollingInfo('1');
                    if (singleNotice) singleNotice.classList.remove('hidden');

                    let hiddenType = document.getElementById('hidden_type');
                    if (!hiddenType) {
                        hiddenType = document.createElement('input');
                        hiddenType.type = 'hidden';
                        hiddenType.id = 'hidden_type';
                        hiddenType.name = 'type';
                        typeDropdown.parentNode.appendChild(hiddenType);
                    }
                    hiddenType.value = '1';

                    if (shiftDropdown && enabledShifts[0]) {
                        shiftDropdown.value = String(enabledShifts[0]);
                    }
                } else {
                    typeDropdown.disabled = false;
                    const hiddenType = document.getElementById('hidden_type');
                    if (hiddenType) hiddenType.remove();
                    if (singleNotice) singleNotice.classList.add('hidden');
                }
            }

            function toggleRollingInfo(typeVal) {
                const infoAlert = document.getElementById('rollingInfoAlert');
                if (infoAlert) {
                    if (String(typeVal) === '2') {
                        infoAlert.classList.remove('hidden');
                    } else {
                        infoAlert.classList.add('hidden');
                    }
                }
            }

            function closeModal() {
                const shiftModal = document.getElementById('shiftModal');
                if (shiftModal) {
                    shiftModal.classList.add('hidden');
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                const shiftModal = document.getElementById('shiftModal');
                if (shiftModal) {
                    shiftModal.addEventListener('click', function(e) {
                        if (!e.target.closest('.bg-white')) {
                            closeModal();
                        }
                    });
                }

                const officeDropdown = document.getElementById('office_id');
                if (officeDropdown) {
                    officeDropdown.addEventListener('change', handleOfficeShiftRestriction);
                }

                const typeDropdown = document.getElementById('type');
                if (typeDropdown) {
                    typeDropdown.addEventListener('change', function() {
                        toggleRollingInfo(this.value);
                    });
                }

                // Instant client-side search across all row contents
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        const filter = this.value.toLowerCase().trim();
                        const tableRows = document.querySelectorAll('#teamTableBody tr');
                        if (tableRows.length > 0) {
                            tableRows.forEach(function(row) {
                                const rowText = row.textContent.toLowerCase();
                                row.style.display = rowText.includes(filter) ? '' : 'none';
                            });
                        }
                    });
                }
            });

            // Toggle Action Dropdown
            function toggleActionDropdown(event, dropdownId) {
                event.stopPropagation();
                const dropdown = document.getElementById(dropdownId);
                if (!dropdown) return;

                const isHidden = dropdown.classList.contains('hidden');
                closeAllDropdowns();

                if (isHidden) {
                    dropdown.classList.remove('hidden');
                    const button = event.currentTarget;
                    const arrow = button.querySelector('.dropdown-arrow');
                    if (arrow) arrow.classList.add('rotate-180');

                    // If dropdown goes below viewport, show upwards
                    const rect = dropdown.getBoundingClientRect();
                    if (rect.bottom > window.innerHeight - 20) {
                        dropdown.classList.remove('mt-1.5');
                        dropdown.classList.add('bottom-full', 'mb-1.5');
                    } else {
                        dropdown.classList.remove('bottom-full', 'mb-1.5');
                        dropdown.classList.add('mt-1.5');
                    }
                }
            }

            function closeAllDropdowns() {
                document.querySelectorAll('.action-dropdown-menu').forEach(menu => {
                    menu.classList.add('hidden');
                });
                document.querySelectorAll('.dropdown-arrow').forEach(arrow => {
                    arrow.classList.remove('rotate-180');
                });
            }

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown-container')) {
                    closeAllDropdowns();
                }
            });
        </script>

        <!-- Select2 Searchable Dropdown JS & Initialization -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                // Inisialisasi Searchable Select pada seluruh filter dropdown penjadwalan
                $('.filter-searchable-select').select2({
                    width: '100%',
                    minimumResultsForSearch: 0,
                    language: {
                        noResults: function() {
                            return 'Tidak ada pilihan ditemukan';
                        }
                    }
                }).on('select2:select', function() {
                    this.form.submit();
                });

                // Fokus otomatis ke search field saat dropdown dibuka dan beri placeholder pencarian
                $(document).on('select2:open', () => {
                    setTimeout(() => {
                        const searchInput = document.querySelector('.select2-container--open .select2-search__field');
                        if (searchInput) {
                            searchInput.setAttribute('placeholder', 'Ketik untuk mencari opsi...');
                            searchInput.focus();
                        }
                    }, 50);
                });
            });
        </script>
    </main>
@endsection
