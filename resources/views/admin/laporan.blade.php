@extends('layouts.main')

@section('title', 'Laporan Presensi' . (($isSuperAdmin ?? false) ? ' & Analitik Performa' : ''))

@section('contents')
@if($isSuperAdmin ?? false)
<!-- ========================================================================= -->
<!-- SUPER ADMIN EXECUTIVE DASHBOARD VIEW                                      -->
<main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-5 min-h-screen bg-slate-50 text-slate-800 space-y-4 min-w-0">

    <!-- EXECUTIVE HEADER SECTION -->
    <div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-xl shadow-xs p-4 sm:p-5 text-white overflow-hidden border border-slate-800">
        <!-- Decorative Ambient Glow -->
        <div class="absolute -right-8 -top-8 w-32 h-32 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-8 -bottom-8 w-32 h-32 bg-blue-500/20 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded-full text-[10px] font-semibold uppercase tracking-wider">
                    <i class="fa-solid fa-crown text-amber-400"></i>
                    <span>Executive Intelligence & Audit Laporan</span>
                </div>
                <h1 class="text-lg sm:text-xl font-bold tracking-tight text-white flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-indigo-400"></i>
                    Laporan Performa & Presensi Pemagang
                </h1>
                <p class="text-slate-300 text-[11px] sm:text-xs max-w-2xl leading-relaxed">
                    Analisis menyeluruh performa kehadiran, tingkat ketepatan waktu, audit presensi fisik (offline), jam kerja, dan indeks disiplin pemagang per periode.
                </p>
            </div>

            <!-- Top Action Buttons -->
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <button type="button" onclick="window.print()" class="px-3 py-1.5 bg-slate-800/90 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-lg text-2xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-print text-[10px]"></i>
                    <span>Cetak</span>
                </button>
                <a href="{{ route('admin.download-report', request()->query()) }}" class="px-3 py-1.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white rounded-lg text-2xs font-semibold shadow-xs transition flex items-center gap-1.5">
                    <i class="fa-solid fa-file-pdf text-[10px]"></i>
                    <span>Download PDF</span>
                </a>
            </div>
        </div>
    </div>

    <!-- WHITE FILTER BAR CARD -->
    <div class="bg-white rounded-xl shadow-2xs border border-slate-200/80 p-4 transition-all space-y-3.5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0 border border-blue-100">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800">Filter Data & Periode Laporan</h3>
                    <p class="text-[11px] text-slate-500">Sesuaikan rentang tanggal, divisi, dan instansi untuk menyaring data</p>
                </div>
            </div>

            <!-- Quick Preset Badges -->
            <div class="flex flex-wrap items-center gap-1.5 self-start sm:self-auto">
                <span class="text-[11px] font-semibold text-slate-400 flex items-center gap-1">
                    <i class="fa-solid fa-bolt text-amber-500 text-[10px]"></i>
                    Preset:
                </span>
                <button type="button" onclick="setPresetRange('today')" class="px-2.5 py-1 bg-slate-50 hover:bg-blue-600 hover:text-white text-slate-700 rounded-lg border border-slate-200 hover:border-blue-600 text-[11px] font-semibold transition shadow-2xs">
                    Hari Ini
                </button>
                <button type="button" onclick="setPresetRange('7days')" class="px-2.5 py-1 bg-slate-50 hover:bg-blue-600 hover:text-white text-slate-700 rounded-lg border border-slate-200 hover:border-blue-600 text-[11px] font-semibold transition shadow-2xs">
                    7 Hari Terakhir
                </button>
                <button type="button" onclick="setPresetRange('thismonth')" class="px-2.5 py-1 bg-slate-50 hover:bg-blue-600 hover:text-white text-slate-700 rounded-lg border border-slate-200 hover:border-blue-600 text-[11px] font-semibold transition shadow-2xs">
                    Bulan Ini
                </button>
            </div>
        </div>

        <!-- FILTER FORM GRID (Proportional 12-Columns) -->
        <form method="GET" action="{{ route('admin.report') }}" id="superadmin-filter-form" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 lg:grid-cols-12 gap-3 items-end">
            <input type="hidden" name="tab" id="form-tab-input" value="{{ $activeTab ?? 'overview' }}">

            <!-- 1. Rentang Tanggal (Span 4 on LG) -->
            <div class="col-span-1 sm:col-span-2 md:col-span-6 lg:col-span-4 space-y-1.5">
                <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                    <i class="fa-solid fa-calendar-days text-blue-600 text-[10px]"></i>
                    Rentang Tanggal
                </label>
                <div class="flex items-center gap-1.5">
                    <div class="relative w-full">
                        <input type="date" name="start_date" id="filter-start-date" value="{{ $startDate }}"
                            class="w-full bg-slate-50 hover:bg-white text-slate-800 border border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100 rounded-lg px-2.5 py-1.5 text-xs font-semibold outline-none transition shadow-2xs">
                    </div>
                    <span class="text-slate-400 text-xs font-bold shrink-0">s/d</span>
                    <div class="relative w-full">
                        <input type="date" name="end_date" id="filter-end-date" value="{{ $endDate }}"
                            class="w-full bg-slate-50 hover:bg-white text-slate-800 border border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100 rounded-lg px-2.5 py-1.5 text-xs font-semibold outline-none transition shadow-2xs">
                    </div>
                </div>
            </div>

            <!-- 2. Filter Divisi (Span 2 on LG) -->
            <div class="col-span-1 sm:col-span-1 md:col-span-3 lg:col-span-2 space-y-1.5">
                <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                    <i class="fa-solid fa-sitemap text-blue-600 text-[10px]"></i>
                    Divisi
                </label>
                <select name="division_id" class="w-full bg-slate-50 hover:bg-white text-slate-800 border border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100 rounded-lg px-2.5 py-1.5 text-xs font-semibold outline-none transition shadow-2xs truncate">
                    <option value="">Semua Divisi</option>
                    @foreach($divisions as $div)
                    <option value="{{ $div->id }}" {{ (string)($divisionId ?? '') === (string)$div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Filter Sekolah / Kampus (Span 2 on LG) -->
            <div class="col-span-1 sm:col-span-1 md:col-span-3 lg:col-span-2 space-y-1.5">
                <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                    <i class="fa-solid fa-graduation-cap text-blue-600 text-[10px]"></i>
                    Sekolah / Kampus
                </label>
                <select name="school_id" class="w-full bg-slate-50 hover:bg-white text-slate-800 border border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100 rounded-lg px-2.5 py-1.5 text-xs font-semibold outline-none transition shadow-2xs truncate">
                    <option value="">Semua Kampus/Sekolah</option>
                    @foreach($schools as $sch)
                    <option value="{{ $sch->id }}" {{ (string)($schoolId ?? '') === (string)$sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 4. Cari Mahasiswa (Span 2 on LG) -->
            <div class="col-span-1 sm:col-span-2 md:col-span-4 lg:col-span-2 space-y-1.5">
                <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                    <i class="fa-solid fa-search text-blue-600 text-[10px]"></i>
                    Cari Mahasiswa
                </label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Nama / NIP..."
                        class="w-full bg-slate-50 hover:bg-white text-slate-800 border border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100 rounded-lg pl-7 pr-2.5 py-1.5 text-xs font-semibold outline-none transition shadow-2xs">
                    <i class="fa-solid fa-search absolute left-2.5 top-2.5 text-slate-400 text-[10px]"></i>
                </div>
            </div>

            <!-- 5. Action Buttons (Span 2 on LG) -->
            <div class="col-span-1 sm:col-span-2 md:col-span-2 lg:col-span-2 flex items-center gap-1.5">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg py-2 px-3 text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-filter text-[10px]"></i>
                    <span>Filter</span>
                </button>
                <a href="{{ route('admin.report') }}" title="Reset Filter" class="bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg p-2 text-xs font-bold transition flex items-center justify-center border border-slate-200 shadow-2xs shrink-0">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- EXECUTIVE KPI METRIC CARDS (3 ATAS & 2 BAWAH) -->
    <div class="space-y-3 sm:space-y-3.5">
        <!-- Row 1: 3 Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-3.5">
            <!-- Card 1: Kehadiran -->
            <div class="bg-white rounded-xl p-3.5 sm:p-4 border border-slate-200/80 shadow-2xs hover:shadow-xs transition flex flex-col justify-between space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-2xs sm:text-xs font-semibold text-slate-500">Tingkat Kehadiran</span>
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shadow-2xs border border-emerald-100">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">{{ $kpis['attendance_rate'] }}%</div>
                    <div class="mt-0.5 text-[11px] text-slate-500 font-medium truncate">
                        {{ $kpis['total_submitted_all'] }} / {{ $kpis['total_scheduled_all'] }} Sesi Hadir
                    </div>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" @style(['width: ' . min(100, $kpis['attendance_rate']) . '%'])></div>
                </div>
            </div>

            <!-- Card 2: Ketepatan Waktu -->
            <div class="bg-white rounded-xl p-3.5 sm:p-4 border border-slate-200/80 shadow-2xs hover:shadow-xs transition flex flex-col justify-between space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-2xs sm:text-xs font-semibold text-slate-500">Ketepatan Waktu</span>
                    <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs shadow-2xs border border-blue-100">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">{{ $kpis['on_time_rate'] }}%</div>
                    <div class="mt-0.5 text-[11px] text-slate-500 font-medium truncate">
                        {{ $kpis['total_on_time_all'] }} Tepat • {{ $kpis['total_late_all'] }} Telat
                    </div>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-500" @style(['width: ' . min(100, $kpis['on_time_rate']) . '%'])></div>
                </div>
            </div>

            <!-- Card 3: Presensi Fisik -->
            <div class="bg-white rounded-xl p-3.5 sm:p-4 border border-slate-200/80 shadow-2xs hover:shadow-xs transition flex flex-col justify-between space-y-2 sm:col-span-2 md:col-span-1">
                <div class="flex items-center justify-between">
                    <span class="text-2xs sm:text-xs font-semibold text-slate-500">Presensi Fisik (Offline)</span>
                    <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs shadow-2xs border border-purple-100">
                        <i class="fa-solid fa-building"></i>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">{{ $kpis['total_offline_hadir'] }} <span class="text-xs font-normal text-slate-400">log</span></div>
                    <div class="mt-0.5 text-[11px] text-slate-500 font-medium truncate">
                        {{ $kpis['total_offline_records'] }} Total Log Fisik
                    </div>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    @php
                    $offlineRatio = $kpis['total_offline_records'] > 0 ? round(($kpis['total_offline_hadir'] / $kpis['total_offline_records']) * 100) : 0;
                    @endphp
                    <div class="bg-purple-500 h-1.5 rounded-full transition-all duration-500" @style(['width: ' . $offlineRatio . '%'])></div>
                </div>
            </div>
        </div>

        <!-- Row 2: 2 Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-3.5">
            <!-- Card 4: Sanksi & Ganti Jam -->
            <div class="bg-white rounded-xl p-3.5 sm:p-4 border border-slate-200/80 shadow-2xs hover:shadow-xs transition flex flex-col justify-between space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-2xs sm:text-xs font-semibold text-slate-500">Sanksi & Ganti Jam</span>
                    <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs shadow-2xs border border-amber-100">
                        <i class="fa-solid fa-gavel"></i>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">{{ $kpis['total_offline_penalties'] }} <span class="text-xs font-normal text-slate-400">kasus sanksi</span></div>
                    <div class="mt-0.5 text-[11px] text-slate-500 font-medium truncate">
                        Total {{ $kpis['total_offline_penalty_minutes'] }} menit penggantian jam
                    </div>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-amber-500 h-1.5 rounded-full transition-all duration-500" @style(['width: ' . min(100, $kpis['total_offline_penalties'] * 20) . '%'])></div>
                </div>
            </div>

            <!-- Card 5: Skor Disiplin -->
            <div class="bg-white rounded-xl p-3.5 sm:p-4 border border-slate-200/80 shadow-2xs hover:shadow-xs transition flex flex-col justify-between space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-2xs sm:text-xs font-semibold text-slate-500">Skor Disiplin Rata-rata</span>
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs shadow-2xs border border-indigo-100">
                        <i class="fa-solid fa-award"></i>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">{{ $kpis['avg_discipline_score'] }}<span class="text-xs font-normal text-slate-400">/100</span></div>
                    <div class="mt-0.5 text-[11px] text-slate-500 font-medium truncate">
                        Grade {{ $kpis['avg_discipline_score'] >= 85 ? 'A (Sangat Baik)' : ($kpis['avg_discipline_score'] >= 70 ? 'B (Baik)' : ($kpis['avg_discipline_score'] >= 55 ? 'C (Cukup)' : 'D (Evaluasi)')) }} • {{ $kpis['total_interns'] }} Pemagang
                    </div>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-500" @style(['width: ' . min(100, $kpis['avg_discipline_score']) . '%'])></div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB CONTROLS & NAVIGATION -->
    <div class="bg-white rounded-xl p-1.5 border border-slate-200/80 shadow-2xs flex flex-wrap gap-1.5">
        <button type="button" onclick="switchSuperTab('overview')" id="tab-btn-overview" class="super-tab-btn px-3.5 py-2 rounded-lg text-2xs sm:text-xs font-bold transition flex items-center gap-1.5 bg-indigo-600 text-white shadow-xs">
            <i class="fa-solid fa-chart-pie text-2xs"></i>
            <span>Ringkasan & Tren Harian</span>
        </button>
        <button type="button" onclick="switchSuperTab('individual')" id="tab-btn-individual" class="super-tab-btn px-3.5 py-2 rounded-lg text-2xs sm:text-xs font-bold transition flex items-center gap-1.5 text-slate-600 hover:bg-slate-100">
            <i class="fa-solid fa-users text-2xs"></i>
            <span>Performa & Disiplin Perorangan</span>
            <span class="px-1.5 py-0.5 bg-indigo-100 text-indigo-700 text-[10px] rounded-full font-extrabold">{{ count($individualPerformances) }}</span>
        </button>
        <button type="button" onclick="switchSuperTab('offline')" id="tab-btn-offline" class="super-tab-btn px-3.5 py-2 rounded-lg text-2xs sm:text-xs font-bold transition flex items-center gap-1.5 text-slate-600 hover:bg-slate-100">
            <i class="fa-solid fa-building text-2xs"></i>
            <span>Log Presensi Offline (Fisik)</span>
            <span class="px-1.5 py-0.5 bg-purple-100 text-purple-700 text-[10px] rounded-full font-extrabold">{{ count($offlineRecords) }}</span>
        </button>
        <button type="button" onclick="switchSuperTab('standard')" id="tab-btn-standard" class="super-tab-btn px-3.5 py-2 rounded-lg text-2xs sm:text-xs font-bold transition flex items-center gap-1.5 text-slate-600 hover:bg-slate-100">
            <i class="fa-solid fa-table-list text-2xs"></i>
            <span>Rekap Presensi Standar</span>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: RINGKASAN & TREN HARIAN                                            -->
    <!-- ========================================================================= -->
    <div id="tab-content-overview" class="super-tab-content space-y-3">

        <!-- Section: Tren & Breakdown Kehadiran Harian -->
        <div class="bg-white rounded-xl p-3.5 sm:p-4 border border-slate-200/80 shadow-2xs space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-slate-100">
                <div>
                    <h2 class="text-xs sm:text-sm font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-timeline text-indigo-500"></i>
                        Tren & Rekap Kehadiran Harian
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Rincian aktivitas presensi seluruh pemagang hari demi hari dalam rentang tanggal terpilih</p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <span id="pagination-info-daily" class="text-[11px] text-slate-600 font-bold bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200/60 shadow-2xs">
                        Total {{ count($dailyBreakdowns) }} Hari Aktif
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200/90">
                <table class="min-w-full divide-y divide-slate-200 text-xs text-left" id="table-daily-records">
                    <thead class="bg-slate-50/90 font-bold text-slate-600 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-3 py-2.5 whitespace-nowrap">Tanggal & Hari</th>
                            <th class="px-2 py-2.5 text-center whitespace-nowrap">Jadwal</th>
                            <th class="px-2 py-2.5 text-center whitespace-nowrap">Hadir</th>
                            <th class="px-2 py-2.5 text-center whitespace-nowrap">Belum Hadir</th>
                            <th class="px-2 py-2.5 text-center whitespace-nowrap">Tepat Waktu</th>
                            <th class="px-2 py-2.5 text-center whitespace-nowrap">Terlambat</th>
                            <th class="px-2 py-2.5 text-center whitespace-nowrap">Izin</th>
                            <th class="px-2 py-2.5 text-center whitespace-nowrap">Alpha</th>
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">Offline (Fisik)</th>
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">Tingkat Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                        @forelse($dailyBreakdowns as $db)
                        <tr class="hover:bg-indigo-50/20 transition daily-row">
                            <td class="px-3 py-2.5 font-semibold text-slate-800 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 bg-slate-100 text-slate-700 rounded-md text-[10px] font-bold border border-slate-200/60">{{ $db['day_name'] }}</span>
                                    <span class="text-2xs sm:text-xs font-bold text-slate-900">{{ $db['date_formatted'] }}</span>
                                </div>
                            </td>
                            <td class="px-2 py-2.5 text-center font-extrabold text-slate-800 whitespace-nowrap text-2xs sm:text-xs">{{ $db['scheduled_count'] }}</td>
                            <td class="px-2 py-2.5 text-center font-extrabold text-emerald-600 whitespace-nowrap text-2xs sm:text-xs">{{ $db['present_count'] }}</td>
                            <td class="px-2 py-2.5 text-center font-semibold text-slate-400 whitespace-nowrap text-2xs sm:text-xs">
                                @if(($db['pending_count'] ?? 0) > 0)
                                <span class="px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded-md text-[10px] font-bold border border-slate-200/60">{{ $db['pending_count'] }}</span>
                                @else
                                <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="px-2 py-2.5 text-center text-blue-600 font-bold whitespace-nowrap text-2xs sm:text-xs">{{ $db['on_time_count'] }}</td>
                            <td class="px-2 py-2.5 text-center text-amber-600 font-bold whitespace-nowrap text-2xs sm:text-xs">{{ $db['late_count'] }}</td>
                            <td class="px-2 py-2.5 text-center text-yellow-600 font-bold whitespace-nowrap text-2xs sm:text-xs">{{ $db['permit_count'] }}</td>
                            <td class="px-2 py-2.5 text-center text-rose-600 font-bold whitespace-nowrap text-2xs sm:text-xs">{{ $db['alpha_count'] }}</td>
                            <td class="px-3 py-2.5 text-center font-semibold text-purple-700 whitespace-nowrap">
                                <span class="px-2 py-0.5 bg-purple-50 border border-purple-100 rounded-lg text-[10px] font-bold inline-block">
                                    {{ $db['offline_count'] }} Hadir
                                    @if($db['offline_late_count'] > 0)
                                    <span class="text-amber-600 font-medium">({{ $db['offline_late_count'] }} Tlt)</span>
                                    @endif
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <div class="w-12 bg-slate-100 rounded-full h-1 overflow-hidden">
                                        <div class="bg-emerald-500 h-1 rounded-full" @style(['width: ' . $db['attendance_rate'] . '%'])></div>
                                    </div>
                                    <span class="font-extrabold text-[11px] sm:text-2xs {{ $db['attendance_rate'] >= 80 ? 'text-emerald-600' : ($db['attendance_rate'] >= 60 ? 'text-amber-600' : 'text-rose-600') }}">{{ $db['attendance_rate'] }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-3 py-6 text-center text-slate-400 text-2xs">Tidak ada rekap aktivitas harian pada periode tanggal ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls Daily Breakdown -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 pt-2 border-t border-slate-100">
                <div class="text-[11px] text-slate-500 font-medium" id="pagination-daily-info"></div>
                <div class="flex items-center gap-1" id="pagination-daily"></div>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: PERFORMA & DISIPLIN PERORANGAN                                     -->
    <!-- ========================================================================= -->
    <div id="tab-content-individual" class="super-tab-content hidden space-y-3">
        <div class="bg-white rounded-xl p-3.5 sm:p-4 border border-slate-200/80 shadow-2xs space-y-3">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 pb-2.5 border-b border-slate-100">
                <div>
                    <h2 class="text-xs sm:text-sm font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-user-check text-indigo-500"></i>
                        Performa & Evaluasi Kedisiplinan Pemagang
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Analisis individual pemagang mencakup presensi online, kehadiran fisik offline, jam kerja, dan indeks disiplin.</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" id="filter-individual-table" placeholder="Cari nama, NIP, divisi..." class="pl-7 pr-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-2xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 focus:bg-white w-48 sm:w-56 text-slate-800 shadow-2xs transition">
                        <i class="fa-solid fa-search absolute left-2.5 top-2 text-slate-400 text-[10px]"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200/90">
                <table class="min-w-full divide-y divide-slate-200 text-xs text-left" id="table-individual-records">
                    <thead class="bg-slate-50/90 font-bold text-slate-600 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-2.5 py-2.5 text-center w-8 whitespace-nowrap">No</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Mahasiswa</th>
                            <th class="px-2 py-2.5 text-center whitespace-nowrap">Jadwal</th>
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">Presensi Online</th>
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">Presensi Fisik</th>
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">Jam Kerja (Akt / Tgt)</th>
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">Indeks Disiplin</th>
                            <th class="px-2.5 py-2.5 text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                        @forelse($individualPerformances as $idx => $p)
                        <tr class="hover:bg-indigo-50/20 transition individual-row" data-name="{{ strtolower($p['name']) }}" data-nip="{{ strtolower($p['nip']) }}" data-divisi="{{ strtolower($p['division_name']) }}">
                            <td class="px-2.5 py-2.5 text-center font-bold text-slate-400 text-2xs">{{ $idx + 1 }}</td>
                            <td class="px-3 py-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 font-extrabold flex items-center justify-center shrink-0 border border-indigo-100 overflow-hidden text-[10px] shadow-2xs">
                                        @if(!empty($p['avatar']))
                                        <img src="{{ asset('storage/' . $p['avatar']) }}" alt="{{ $p['name'] }}" class="w-full h-full object-cover">
                                        @else
                                        {{ strtoupper(substr($p['name'], 0, 2)) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0 space-y-0.5">
                                        <a href="{{ route('admin.presence.detail', $p['id']) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition block text-2xs sm:text-xs leading-tight" title="{{ $p['name'] }}">
                                            {{ $p['name'] }}
                                        </a>
                                        <div class="text-[10px] text-slate-400 flex items-center gap-1 leading-normal">
                                            <span>NIP: {{ $p['nip'] }}</span> •
                                            <span class="text-indigo-600 font-semibold">{{ $p['division_name'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-2 py-2.5 text-center whitespace-nowrap">
                                <span class="font-extrabold text-slate-800 text-xs">{{ $p['scheduled_days'] }}</span>
                                <span class="text-[10px] text-slate-400 block font-medium">hari</span>
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                <div class="inline-flex flex-col items-center gap-1">
                                    <div class="text-2xs font-bold text-slate-800">
                                        <span class="text-emerald-600 font-extrabold">{{ $p['submitted_days'] }}</span>
                                        <span class="text-slate-400 font-normal">/ {{ $p['scheduled_days'] }} hadir</span>
                                    </div>
                                    <div class="flex flex-wrap items-center justify-center gap-0.5 text-[10px]">
                                        <span class="px-1.5 py-0.5 rounded-md bg-blue-50 text-blue-700 font-semibold">{{ $p['on_time_days'] }} Tepat</span>
                                        <span class="px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-700 font-semibold">{{ $p['late_days'] }} Telat</span>
                                        @if($p['permit_days'] > 0)
                                        <span class="px-1.5 py-0.5 rounded-md bg-yellow-50 text-yellow-700 font-semibold">{{ $p['permit_days'] }} Izin</span>
                                        @endif
                                        @if($p['alpha_days'] > 0)
                                        <span class="px-1.5 py-0.5 rounded-md bg-rose-50 text-rose-700 font-bold">{{ $p['alpha_days'] }} Alpha</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                <div class="inline-flex flex-col items-center gap-0.5">
                                    <span class="px-2 py-0.5 bg-purple-50 border border-purple-100 text-purple-800 font-bold rounded-md text-[11px]">
                                        {{ $p['offline_hadir'] }} Hadir
                                    </span>
                                    @if($p['offline_late'] > 0 || $p['offline_penalties_count'] > 0)
                                    <div class="flex items-center gap-1 text-[10px]">
                                        @if($p['offline_late'] > 0)
                                        <span class="text-amber-600 font-semibold">{{ $p['offline_late'] }} Telat</span>
                                        @endif
                                        @if($p['offline_penalties_count'] > 0)
                                        <span class="text-rose-600 font-bold">• {{ $p['offline_penalties_count'] }} Sanksi</span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                <div class="flex flex-col items-center gap-0.5 text-2xs">
                                    <div class="font-bold text-slate-800">
                                        <span class="text-emerald-600">{{ $p['actual_formatted'] }}</span>
                                        <span class="text-slate-400 font-normal">/ {{ $p['target_formatted'] }}</span>
                                    </div>
                                    @if($p['lack_minutes'] > 0)
                                    <span class="px-1.5 py-0.5 bg-rose-50 text-rose-700 font-bold rounded-md text-[10px]">
                                        Hutang: {{ $p['lack_formatted'] }}
                                    </span>
                                    @else
                                    <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-700 font-semibold rounded-md text-[10px]">
                                        Lengkap
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                <div class="inline-flex flex-col items-center gap-0.5">
                                    <span class="px-2 py-0.5 rounded-lg text-2xs font-black border {{ $p['grade_badge'] }}">
                                        Grade {{ $p['grade'] }} ({{ $p['discipline_score'] }}%)
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-medium">{{ $p['grade_label'] }}</span>
                                </div>
                            </td>
                            <td class="px-2.5 py-2.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="openInternModalFromButton(this)" data-intern="{{ json_encode($p) }}" class="p-1.5 bg-slate-100 hover:bg-indigo-600 hover:text-white text-slate-600 rounded-lg transition text-2xs font-bold shadow-2xs" title="Ringkasan Cepat">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <a href="{{ route('admin.presence.detail', $p['id']) }}" class="p-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition text-2xs font-bold shadow-2xs" title="Buka Detail Presensi">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-3 py-6 text-center text-slate-400 text-2xs">Tidak ada data pemagang pada filter yang dipilih.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls Individual -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 pt-2 border-t border-slate-100">
                <div class="text-[11px] text-slate-500 font-medium" id="pagination-individual-info"></div>
                <div class="flex items-center gap-1" id="pagination-individual"></div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: LOG PRESENSI OFFLINE (FISIK)                                       -->
    <!-- ========================================================================= -->
    <div id="tab-content-offline" class="super-tab-content hidden space-y-3">
        <div class="bg-white rounded-xl p-3.5 sm:p-4 border border-slate-200/80 shadow-2xs space-y-3">

            <!-- Header Log Offline -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 pb-2.5 border-b border-slate-100">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs shadow-2xs border border-purple-200">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <h2 class="text-xs sm:text-sm font-bold text-slate-800">
                            Log & Audit Presensi Fisik (Offline)
                        </h2>
                        <span class="px-2 py-0.5 bg-purple-50 text-purple-700 border border-purple-200 rounded-full text-[10px] font-extrabold">
                            {{ count($offlineRecords) }} Records
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500">Rekapitulasi pemeriksaan fisik kehadiran di kantor oleh admin/asisten</p>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" id="filter-offline-table" placeholder="Cari nama, petugas, tanggal..." class="pl-7 pr-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-2xs font-semibold focus:outline-none focus:ring-2 focus:ring-purple-100 focus:border-purple-500 focus:bg-white w-48 sm:w-56 text-slate-800 shadow-2xs transition">
                        <i class="fa-solid fa-search absolute left-2.5 top-2 text-slate-400 text-[10px]"></i>
                    </div>
                </div>
            </div>

            <!-- Clean Table Log Offline -->
            <div class="overflow-x-auto rounded-xl border border-slate-200/90">
                <table class="min-w-full divide-y divide-slate-200 text-xs text-left" id="table-offline-records">
                    <thead class="bg-slate-50/90 font-bold text-slate-600 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-2.5 py-2.5 text-center w-8 whitespace-nowrap">No</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Tanggal & Waktu</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Mahasiswa</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Shift & Lokasi</th>
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">Status Kehadiran</th>
                            <th class="px-2 py-2.5 text-center whitespace-nowrap">Keterlambatan</th>
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">Tindakan / Sanksi</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Petugas</th>
                            <th class="px-3 py-2.5">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                        @forelse($offlineRecords as $idx => $off)
                        @php
                        $internUser = $off->intern?->user;
                        $fullName = $internUser?->profile?->full_name ?? $internUser?->name ?? 'Pemagang';
                        $verifierName = $off->adminUser?->name ?? $off->recorded_by ?? 'Sistem';
                        $dateStr = $off->date ? $off->date->format('d M Y') : '-';
                        $checkTimeStr = $off->check_time ? \Carbon\Carbon::parse($off->check_time)->format('H:i') . ' WIB' : 'Belum absen';
                        @endphp
                        <tr class="hover:bg-purple-50/20 transition offline-row"
                            data-name="{{ strtolower($fullName) }}"
                            data-admin="{{ strtolower($verifierName) }}"
                            data-date="{{ strtolower($dateStr) }}"
                            data-status="{{ strtolower($off->status ?? '') }}">

                            <!-- No -->
                            <td class="px-2.5 py-2.5 text-center font-bold text-slate-400 text-2xs">{{ $idx + 1 }}</td>

                            <!-- Tanggal & Waktu -->
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <div class="font-bold text-slate-800 text-2xs sm:text-xs">{{ $dateStr }}</div>
                                <div class="text-[10px] text-slate-500 font-semibold flex items-center gap-1 mt-0.5">
                                    <i class="fa-regular fa-clock text-slate-400"></i>
                                    <span>{{ $checkTimeStr }}</span>
                                </div>
                            </td>

                            <!-- Mahasiswa -->
                            <td class="px-3 py-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 font-bold flex items-center justify-center shrink-0 border border-indigo-100 text-[10px]">
                                        {{ strtoupper(substr($fullName, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 truncate max-w-[130px] text-2xs sm:text-xs" title="{{ $fullName }}">{{ $fullName }}</div>
                                        <div class="text-[10px] text-slate-400 truncate max-w-[130px]">
                                            <span>{{ $off->intern?->division?->name ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Shift & Lokasi -->
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <div class="font-semibold text-slate-800 text-2xs sm:text-xs">{{ $off->shift?->name ?? 'Shift Reguler' }}</div>
                                <div class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5">
                                    <i class="fa-solid fa-location-dot text-[10px] text-slate-400"></i>
                                    <span>{{ $off->office?->name ?? 'Kantor Utama' }}</span>
                                </div>
                            </td>

                            <!-- Status Kehadiran Fisik -->
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                @if($off->status === 'hadir')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold rounded-md text-[10px]">
                                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                    <span>Hadir Tepat</span>
                                </span>
                                @elseif($off->status === 'terlambat')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 font-bold rounded-md text-[10px]">
                                    <i class="fa-solid fa-clock text-amber-500"></i>
                                    <span>Terlambat</span>
                                </span>
                                @elseif($off->status === 'alpha')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 font-bold rounded-md text-[10px]">
                                    <i class="fa-solid fa-circle-xmark text-rose-500"></i>
                                    <span>Alpha</span>
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-slate-100 text-slate-600 font-medium rounded-md text-[10px]">
                                    <span>Belum Dicek</span>
                                </span>
                                @endif
                            </td>

                            <!-- Keterlambatan -->
                            <td class="px-2 py-2.5 text-center whitespace-nowrap">
                                @if($off->late_minutes > 0)
                                <span class="font-extrabold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded-md border border-amber-200 text-[10px]">
                                    +{{ $off->late_minutes }} mnt
                                </span>
                                @else
                                <span class="text-slate-400 text-2xs">-</span>
                                @endif
                            </td>

                            <!-- Status Tindakan / Sanksi -->
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                @if(!empty($off->penalty_type))
                                @if($off->penalty_type === 'ganti_jam')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-100 text-purple-800 border border-purple-300 font-extrabold rounded-md text-[10px]">
                                    <span>⚖️ Ganti Jam ({{ $off->penalty_minutes ?? 0 }}m)</span>
                                </span>
                                @elseif($off->penalty_type === 'tanpa_ganti_jam')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-800 text-white font-extrabold rounded-md text-[10px]">
                                    <span>🚫 Tetap Alpha</span>
                                </span>
                                @elseif($off->penalty_type === 'dimaafkan')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 font-semibold rounded-md text-[10px]">
                                    <span>🕊️ Sah</span>
                                </span>
                                @endif
                                @else
                                <span class="text-slate-400 text-2xs">-</span>
                                @endif
                            </td>

                            <!-- Petugas Verifikator -->
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <div class="font-semibold text-slate-800 text-2xs flex items-center gap-1">
                                    <i class="fa-solid fa-user-shield text-slate-400 text-[10px]"></i>
                                    <span>{{ $verifierName }}</span>
                                </div>
                            </td>

                            <!-- Catatan -->
                            <td class="px-3 py-2.5 text-[11px] text-slate-600 max-w-xs">
                                @if(!empty($off->penalty_notes) || !empty($off->notes))
                                <span class="bg-slate-50 px-1.5 py-0.5 rounded-md border border-slate-200/60 inline-block truncate max-w-[140px]" title="{{ $off->penalty_notes ?? $off->notes }}">
                                    {{ $off->penalty_notes ?? $off->notes }}
                                </span>
                                @else
                                <span class="text-slate-400 text-2xs">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-3 py-6 text-center text-slate-400 text-2xs">
                                Tidak ada data presensi fisik (offline) pada periode ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls Offline -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 pt-2 border-t border-slate-100">
                <div class="text-[11px] text-slate-500 font-medium" id="pagination-offline-info"></div>
                <div class="flex items-center gap-1" id="pagination-offline"></div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 4: REKAP PRESENSI STANDAR                                             -->
    <!-- ========================================================================= -->
    <div id="tab-content-standard" class="super-tab-content hidden space-y-3">
        <div class="bg-white rounded-xl p-3.5 sm:p-4 border border-slate-200/80 shadow-2xs space-y-3">
            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                <div>
                    <h2 class="text-xs sm:text-sm font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-table-list text-indigo-500"></i>
                        Rekap Standar Presensi
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Tampilan data rekap presensi konvensional untuk kebutuhan arsip cepat</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200/90">
                <table class="min-w-full divide-y divide-slate-200 text-xs text-left" id="table-standard-records">
                    <thead class="bg-slate-50/90 font-bold text-slate-600 uppercase tracking-wider text-[10px] text-center">
                        <tr>
                            <th class="px-2.5 py-2.5 w-8 whitespace-nowrap">No</th>
                            <th class="px-3 py-2.5 text-left whitespace-nowrap">Nama</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">NIP</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Total Hadir</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Total Izin</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Total Alpha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 bg-white text-center">
                        @foreach($individualPerformances as $idx => $item)
                        <tr class="hover:bg-slate-50/80 transition standard-row">
                            <td class="px-2.5 py-2.5 font-bold text-slate-400 text-2xs">{{ $idx + 1 }}</td>
                            <td class="px-3 py-2.5 text-left font-bold text-indigo-600 hover:text-indigo-800 text-2xs sm:text-xs">
                                <a href="{{ route('admin.presence.detail', $item['id']) }}">{{ $item['name'] }}</a>
                            </td>
                            <td class="px-3 py-2.5 text-slate-500 font-semibold text-2xs">{{ $item['nip'] }}</td>
                            <td class="px-3 py-2.5 font-bold text-emerald-600 text-2xs sm:text-xs">{{ $item['submitted_days'] }}</td>
                            <td class="px-3 py-2.5 font-bold text-yellow-600 text-2xs sm:text-xs">{{ $item['permit_days'] }}</td>
                            <td class="px-3 py-2.5 font-bold text-rose-600 text-2xs sm:text-xs">{{ $item['alpha_days'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls Standard -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 pt-2 border-t border-slate-100">
                <div class="text-[11px] text-slate-500 font-medium" id="pagination-standard-info"></div>
                <div class="flex items-center gap-1" id="pagination-standard"></div>
            </div>
        </div>
    </div>

</main>

<!-- MODAL QUICK VIEW RESUME PEMAGANG -->
<div id="internQuickModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Resume Performa Pemagang</h3>
            </div>
            <button type="button" onclick="closeInternModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <div class="space-y-4 text-xs">
            <!-- Profile Header -->
            <div class="flex items-center gap-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-200/60">
                <div id="modal-avatar" class="w-12 h-12 rounded-xl bg-indigo-600 text-white font-extrabold flex items-center justify-center text-sm shrink-0 shadow-xs">
                    AZ
                </div>
                <div class="min-w-0">
                    <div id="modal-name" class="font-extrabold text-slate-900 text-sm truncate">Nama Pemagang</div>
                    <div class="text-slate-500 text-2xs mt-0.5">
                        <span id="modal-nip" class="font-semibold">NIP: -</span> • <span id="modal-division" class="text-indigo-600 font-bold">Divisi</span>
                    </div>
                    <div id="modal-school" class="text-2xs text-slate-400 mt-0.5 truncate">Asal Sekolah / Kampus</div>
                </div>
            </div>

            <!-- Metrics Grid -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/60">
                    <span class="text-2xs text-slate-400 font-medium">Skor Disiplin & Grade</span>
                    <div class="text-base font-extrabold text-slate-800 mt-1" id="modal-grade">Grade A (95%)</div>
                    <div class="text-2xs text-slate-500 mt-0.5" id="modal-grade-label">Sangat Memuaskan</div>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/60">
                    <span class="text-2xs text-slate-400 font-medium">Tingkat Kehadiran</span>
                    <div class="text-base font-extrabold text-emerald-600 mt-1" id="modal-rate">100%</div>
                    <div class="text-2xs text-slate-500 mt-0.5" id="modal-sched">20 dari 20 hari kerja</div>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/60">
                    <span class="text-2xs text-slate-400 font-medium">Presensi Fisik (Offline)</span>
                    <div class="text-sm font-bold text-purple-700 mt-1" id="modal-offline">18 Hadir | 2 Telat</div>
                    <div class="text-2xs text-slate-500 mt-0.5" id="modal-penalties">0 Sanksi Pelanggaran</div>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/60">
                    <span class="text-2xs text-slate-400 font-medium">Status Jam Kerja</span>
                    <div class="text-sm font-bold text-slate-800 mt-1" id="modal-hours">Tgt: 145j | Akt: 145j</div>
                    <div class="text-2xs text-rose-600 font-bold mt-0.5" id="modal-debt">Hutang: 0j 00m</div>
                </div>
            </div>
        </div>

        <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
            <button type="button" onclick="closeInternModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">Tutup</button>
            <a id="modal-detail-link" href="#" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/30 flex items-center gap-1.5 transition">
                <span>Buka Detail Lengkap</span>
                <i class="fa-solid fa-arrow-right text-2xs"></i>
            </a>
        </div>
    </div>
</div>

<script>
    // Tab Switcher for Super Admin
    function switchSuperTab(tabKey) {
        document.querySelectorAll('.super-tab-btn').forEach(btn => {
            btn.classList.remove('bg-indigo-600', 'text-white', 'shadow-sm', 'shadow-indigo-600/30');
            btn.classList.add('text-slate-600', 'hover:bg-slate-100');
        });
        document.querySelectorAll('.super-tab-content').forEach(content => {
            content.classList.add('hidden');
        });

        const activeBtn = document.getElementById('tab-btn-' + tabKey);
        const activeContent = document.getElementById('tab-content-' + tabKey);
        const tabInput = document.getElementById('form-tab-input');

        if (activeBtn) {
            activeBtn.classList.add('bg-indigo-600', 'text-white', 'shadow-sm', 'shadow-indigo-600/30');
            activeBtn.classList.remove('text-slate-600', 'hover:bg-slate-100');
        }
        if (activeContent) {
            activeContent.classList.remove('hidden');
        }
        if (tabInput) {
            tabInput.value = tabKey;
        }
    }

    // Quick Preset Date Ranges
    function setPresetRange(type) {
        const today = new Date();
        const pad = (n) => String(n).padStart(2, '0');
        const formatDate = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

        const startInput = document.getElementById('filter-start-date');
        const endInput = document.getElementById('filter-end-date');

        if (type === 'today') {
            startInput.value = formatDate(today);
            endInput.value = formatDate(today);
        } else if (type === '7days') {
            const past = new Date();
            past.setDate(today.getDate() - 6);
            startInput.value = formatDate(past);
            endInput.value = formatDate(today);
        } else if (type === 'thismonth') {
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            startInput.value = formatDate(firstDay);
            endInput.value = formatDate(today);
        }

        const filterForm = document.getElementById('superadmin-filter-form');
        if (filterForm) {
            filterForm.submit();
        }
    }

    // Client-Side 10-Item Pagination Engine
    class TablePaginator {
        constructor(tableId, rowsClass, paginationContainerId, infoContainerId, perPage = 10) {
            this.table = document.getElementById(tableId);
            this.rowsClass = rowsClass;
            this.paginationContainer = document.getElementById(paginationContainerId);
            this.infoContainer = document.getElementById(infoContainerId);
            this.perPage = perPage;
            this.currentPage = 1;
            this.filteredRows = [];
            this.allRows = [];
            this.init();
        }

        init() {
            if (!this.table) return;
            this.allRows = Array.from(this.table.querySelectorAll('.' + this.rowsClass));
            this.filteredRows = [...this.allRows];
            this.render();
        }

        setFilteredRows(filtered) {
            this.filteredRows = filtered;
            this.currentPage = 1;
            this.render();
        }

        render() {
            const totalItems = this.filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(totalItems / this.perPage));
            if (this.currentPage > totalPages) this.currentPage = totalPages;

            // Hide all rows first
            this.allRows.forEach(r => r.style.display = 'none');

            // Calculate slice range
            const start = (this.currentPage - 1) * this.perPage;
            const end = Math.min(start + this.perPage, totalItems);

            // Show current page items
            for (let i = start; i < end; i++) {
                if (this.filteredRows[i]) {
                    this.filteredRows[i].style.display = '';
                }
            }

            // Render information text
            if (this.infoContainer) {
                if (totalItems === 0) {
                    this.infoContainer.textContent = 'Menampilkan 0 data';
                } else {
                    this.infoContainer.textContent = `Menampilkan ${start + 1} - ${end} dari total ${totalItems} data (10 per halaman)`;
                }
            }

            // Render pagination buttons
            if (this.paginationContainer) {
                this.paginationContainer.innerHTML = '';
                if (totalPages <= 1) return;

                // Prev button
                const prevBtn = document.createElement('button');
                prevBtn.type = 'button';
                prevBtn.className = `px-3 py-1.5 rounded-xl border text-xs font-bold transition flex items-center gap-1 ${this.currentPage <= 1 ? 'text-slate-300 border-slate-200 cursor-not-allowed' : 'text-slate-700 bg-white border-slate-300 hover:bg-slate-100 cursor-pointer'}`;
                prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left text-2xs"></i> Prev';
                prevBtn.disabled = this.currentPage <= 1;
                prevBtn.addEventListener('click', () => {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.render();
                    }
                });
                this.paginationContainer.appendChild(prevBtn);

                // Page buttons
                for (let p = 1; p <= totalPages; p++) {
                    if (totalPages > 7) {
                        if (p !== 1 && p !== totalPages && Math.abs(p - this.currentPage) > 2) {
                            if (p === 2 || p === totalPages - 1) {
                                const dots = document.createElement('span');
                                dots.className = 'px-2 py-1 text-slate-400 text-xs font-bold';
                                dots.textContent = '...';
                                this.paginationContainer.appendChild(dots);
                            }
                            continue;
                        }
                    }

                    const pageBtn = document.createElement('button');
                    pageBtn.type = 'button';
                    pageBtn.textContent = p;
                    pageBtn.className = `px-3 py-1.5 rounded-xl border text-xs font-bold transition cursor-pointer ${p === this.currentPage ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'text-slate-700 bg-white border-slate-300 hover:bg-slate-100'}`;
                    pageBtn.addEventListener('click', () => {
                        this.currentPage = p;
                        this.render();
                    });
                    this.paginationContainer.appendChild(pageBtn);
                }

                // Next button
                const nextBtn = document.createElement('button');
                nextBtn.type = 'button';
                nextBtn.className = `px-3 py-1.5 rounded-xl border text-xs font-bold transition flex items-center gap-1 ${this.currentPage >= totalPages ? 'text-slate-300 border-slate-200 cursor-not-allowed' : 'text-slate-700 bg-white border-slate-300 hover:bg-slate-100 cursor-pointer'}`;
                nextBtn.innerHTML = 'Next <i class="fa-solid fa-chevron-right text-2xs"></i>';
                nextBtn.disabled = this.currentPage >= totalPages;
                nextBtn.addEventListener('click', () => {
                    if (this.currentPage < totalPages) {
                        this.currentPage++;
                        this.render();
                    }
                });
                this.paginationContainer.appendChild(nextBtn);
            }
        }
    }

    let dailyPaginator, individualPaginator, offlinePaginator, standardPaginator;

    document.addEventListener('DOMContentLoaded', () => {
        // Initialize Table Paginators (10 items per page)
        dailyPaginator = new TablePaginator('table-daily-records', 'daily-row', 'pagination-daily', 'pagination-daily-info', 10);
        individualPaginator = new TablePaginator('table-individual-records', 'individual-row', 'pagination-individual', 'pagination-individual-info', 10);
        offlinePaginator = new TablePaginator('table-offline-records', 'offline-row', 'pagination-offline', 'pagination-offline-info', 10);
        standardPaginator = new TablePaginator('table-standard-records', 'standard-row', 'pagination-standard', 'pagination-standard-info', 10);

        // Real-time table filter for individual performance
        document.getElementById('filter-individual-table')?.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            const filtered = individualPaginator.allRows.filter(row => {
                const name = row.getAttribute('data-name') || '';
                const nip = row.getAttribute('data-nip') || '';
                const divisi = row.getAttribute('data-divisi') || '';
                return name.includes(q) || nip.includes(q) || divisi.includes(q);
            });
            individualPaginator.setFilteredRows(filtered);
        });

        // Real-time table filter for offline presence log
        document.getElementById('filter-offline-table')?.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            const filtered = offlinePaginator.allRows.filter(row => {
                const name = row.getAttribute('data-name') || '';
                const admin = row.getAttribute('data-admin') || '';
                const date = row.getAttribute('data-date') || '';
                const status = row.getAttribute('data-status') || '';
                return name.includes(q) || admin.includes(q) || date.includes(q) || status.includes(q);
            });
            offlinePaginator.setFilteredRows(filtered);
        });

        // Restore active tab
        const currentTab = "{{ $activeTab ?? 'overview' }}";
        if (currentTab) {
            switchSuperTab(currentTab);
        }
    });

    // Modal Handlers
    function openInternModalFromButton(btn) {
        try {
            const data = JSON.parse(btn.getAttribute('data-intern') || '{}');
            openInternModal(data);
        } catch (e) {
            console.error('Error parsing intern data:', e);
        }
    }

    function openInternModal(data) {
        document.getElementById('modal-name').textContent = data.name;
        document.getElementById('modal-nip').textContent = 'NIP: ' + data.nip;
        document.getElementById('modal-division').textContent = data.division_name;
        document.getElementById('modal-school').textContent = data.school_name;
        document.getElementById('modal-avatar').textContent = data.name.substring(0, 2).toUpperCase();

        document.getElementById('modal-grade').textContent = `Grade ${data.grade} (${data.discipline_score}%)`;
        document.getElementById('modal-grade-label').textContent = data.grade_label;
        document.getElementById('modal-rate').textContent = `${data.attendance_rate}%`;
        document.getElementById('modal-sched').textContent = `${data.submitted_days} dari ${data.scheduled_days} hari kerja`;

        document.getElementById('modal-offline').textContent = `${data.offline_hadir} Hadir | ${data.offline_late} Telat | ${data.offline_alpha} Alpha`;
        document.getElementById('modal-penalties').textContent = `${data.offline_penalties_count} Sanksi (+${data.offline_penalty_minutes}m)`;

        document.getElementById('modal-hours').textContent = `Tgt: ${data.target_formatted} | Akt: ${data.actual_formatted}`;
        document.getElementById('modal-debt').textContent = `Hutang: ${data.lack_formatted}`;

        document.getElementById('modal-detail-link').href = `/admin/presence/detail/${data.id}`;

        document.getElementById('internQuickModal').classList.remove('hidden');
    }

    function closeInternModal() {
        document.getElementById('internQuickModal').classList.add('hidden');
    }
</script>

@else
<!-- ========================================================================= -->
<!-- REGULAR ADMIN STANDARD REPORT VIEW                                        -->
<!-- ========================================================================= -->
<main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">
    <div class="bg-gray-700 text-white p-4 sm:p-6 rounded-t-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
            <!-- Left Column -->
            <div class="flex flex-col space-y-1 sm:space-y-4">
                <div class="text-2xl sm:text-4xl font-bold">Laporan Data Presensi</div>
                <div class="text-sm sm:text-lg" id="date-range-display">Data per tanggal {{ $dateNow ?? '' }}</div>
            </div>

            <!-- Right Column -->
            <div class="flex flex-col space-y-2">
                <label for="search-student" class="text-sm sm:text-lg font-medium">Cari Mahasiswa</label>
                <div class="flex items-center border border-gray-300 rounded">
                    <div class="bg-white p-2 rounded-l">
                        <i class="ml-2 fa-solid fa-search text-gray-500"></i>
                    </div>

                    <input type="text" id="search-student"
                        class="p-2 pl-3 pr-3 rounded-r text-gray-800 border-gray-300 focus:outline-none focus:border-blue-500 w-full text-sm sm:text-base"
                        placeholder="Masukkan nama mahasiswa">
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white p-3.5 sm:p-4 rounded-b-lg border border-t-0 border-gray-200 shadow-xs">
        <!-- Date Input dan Filter -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:gap-4 w-full">
            <!-- Date Inputs for Range -->
            <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-3 w-full sm:w-auto">
                <input type="date"
                    class="p-2 sm:p-2.5 w-full sm:w-auto border border-gray-400 rounded-md focus:outline-none focus:border-blue-500 text-xs sm:text-sm"
                    id="start-date" placeholder="Start Date">
                <span class="text-gray-500 text-xs font-bold sm:inline">s/d</span>
                <input type="date"
                    class="p-2 sm:p-2.5 w-full sm:w-auto border border-gray-400 rounded-md focus:outline-none focus:border-blue-500 text-xs sm:text-sm"
                    id="end-date" placeholder="End Date">
            </div>

            <!-- Search Button -->
            <button id="search-button"
                class="bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-700 flex items-center justify-center gap-2 text-xs sm:text-sm font-semibold transition">
                <i class="fas fa-search"></i>
                Search
            </button>
        </div>
    </div>

    <div class="overflow-x-auto w-full max-w-full min-w-0 bg-white shadow-xs rounded-lg mt-4 border border-gray-200">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-200 text-gray-700 text-xs sm:text-sm uppercase leading-normal text-center">
                    <th rowspan="2" class="px-3 py-2 border-r">No</th>
                    <th rowspan="2" class="px-3 py-2 border-r">Nama</th>
                    <th rowspan="2" class="px-3 py-2 border-r">NIP</th>
                    <th rowspan="2" class="px-3 py-2 border-r">Total Kehadiran</th>
                    <th rowspan="2" class="px-3 py-2 border-r">Total Izin</th>
                    <th rowspan="2" class="px-3 py-2 border-r">Total Ketidakhadiran</th>
                </tr>
            </thead>

            <tbody class="text-xs sm:text-sm font-normal text-gray-800" id="report-tbody">

            </tbody>
        </table>
    </div>

    <div id="loading-spinner" class="flex justify-center items-center py-4 hidden">
        <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-10 w-10"></div>
    </div>

    <div id="no-data-message" class="text-center py-4 text-gray-600 hidden">
        No data available
    </div>

    <!-- Pagination Controls -->
    <div class="mt-4 flex justify-center items-center space-x-2 border rounded-md p-2 bg-white">
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

    <div class="mt-4 flex justify-end">
        <button id="download-pdf"
            class="px-3 py-2 text-sm font-medium text-center text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800 rounded-md">
            <i class="fa-solid fa-download"></i>
            <span>Download PDF</span>
        </button>
    </div>

</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentPage = 1;
        const perPage = 10;
        const prevPageButton = document.querySelector('#prev-page');
        const nextPageButton = document.querySelector('#next-page');
        const pageNumbersContainer = document.querySelector('#page-numbers');
        const tbody = document.querySelector('#report-tbody');
        let totalPages = 1;

        const fetchData = (page, startDate = '', endDate = '', internName = null, filter = '') => {
            const params = {
                page: page,
                perPage: perPage,
                startDate: startDate,
                endDate: endDate
            };
            if (internName != null) {
                params.internName = internName;
            }

            axios.get('/api/report-attendance', {
                    params
                })
                .then(response => {
                    totalPages = response.data.totalPages;
                    currentPage = response.data.currentPage;
                    tbody.innerHTML = '';

                    response.data.reportData.forEach((item, key) => {
                        const row = document.createElement('tr');
                        row.className = 'border-b border-gray-300 text-center';

                        const rowNumber = (currentPage - 1) * perPage + (key + 1);

                        row.innerHTML = `
                            <td class="px-4 py-2">${rowNumber}</td>
                            <td class="px-4 py-2 text-left hover:underline cursor-pointer text-blue-600 hover:text-blue-800">
                                <a href="/admin/presence/detail/${item.id}">${item.name}</a>
                            </td>
                            <td class="px-4 py-2">${item.nip}</td>
                            <td class="px-4 py-2">
                                <a href="#" class="inline-block ml-2">${item.submitted}</a>
                            </td>
                            <td class="px-4 py-2 text-yellow-600">
                                <a href="#" class="inline-block ml-2">${item.permits}</a>
                            </td>
                            <td class="px-4 py-2 text-red-600">
                                <a href="#" class="inline-block ml-2">${item.absence}</a>
                            </td>
                        `;
                        tbody.appendChild(row);
                        updatePaginationButtons();
                        renderPageNumbers();
                    });

                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                });
        };

        const updatePaginationButtons = () => {
            prevPageButton.disabled = currentPage <= 1;
            nextPageButton.disabled = currentPage >= totalPages;
        };

        const renderPageNumbers = () => {
            pageNumbersContainer.innerHTML = '';

            for (let i = 1; i <= totalPages; i++) {
                const pageButton = document.createElement('button');
                pageButton.textContent = i;
                pageButton.className = 'cursor-pointer px-4 py-2 rounded-md border';

                if (i === currentPage) {
                    pageButton.classList.add('bg-blue-600', 'text-white');
                } else {
                    pageButton.classList.add('text-blue-600', 'bg-white', 'hover:bg-gray-100');
                }

                pageButton.addEventListener('click', () => {
                    if (i !== currentPage) {
                        currentPage = i;
                        fetchData(currentPage);
                    }
                });

                pageNumbersContainer.appendChild(pageButton);
            }
        };

        fetchData(currentPage);

        prevPageButton.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                fetchData(currentPage);
            }
        });

        nextPageButton.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                fetchData(currentPage);
            }
        });

        document.querySelector('#search-student').addEventListener('input', function() {
            const query = this.value;
            fetchData(1, '', '', query);
        });

        document.querySelector('#search-button').addEventListener('click', () => {
            const startDate = document.querySelector('#start-date').value;
            const endDate = document.querySelector('#end-date').value;
            const query = document.querySelector('#search-student').value;
            fetchData(1, startDate, endDate, query);
        });

    });

    document.addEventListener('DOMContentLoaded', () => {
        const startDateInput = document.getElementById('start-date');
        const endDateInput = document.getElementById('end-date');
        const dateRangeDisplay = document.getElementById('date-range-display');

        const updateDateRange = () => {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;

            if (startDate && endDate) {
                const [startYear, startMonth, startDay] = startDate.split('-');
                const [endYear, endMonth, endDay] = endDate.split('-');

                const formattedStartDate = `${startDay}-${startMonth}-${startYear}`;
                const formattedEndDate = `${endDay}-${endMonth}-${endYear}`;

                dateRangeDisplay.textContent = `Data per tanggal ${formattedStartDate} s/d ${formattedEndDate}`;
            } else {
                dateRangeDisplay.textContent = `Data per tanggal {{ $dateNow ?? '' }}`;
            }
        };

        startDateInput.addEventListener('change', updateDateRange);
        endDateInput.addEventListener('change', updateDateRange);
    });

    document.getElementById('download-pdf').addEventListener('click', function() {
        const searchStudent = document.getElementById('search-student').value;
        let startDate = document.getElementById('start-date').value;
        let endDate = document.getElementById('end-date').value;

        const today = new Date().toISOString().split('T')[0];
        if (!startDate) startDate = today;
        if (!endDate) endDate = today;

        const url = `./report/download?search=${encodeURIComponent(searchStudent)}&start_date=${startDate}&end_date=${endDate}`;

        window.location.href = url;
    });
</script>
@endif
@endsection