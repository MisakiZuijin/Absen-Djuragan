@extends('layouts.main')

@section('title', 'Manajemen Izin Keluar')

@section('contents')
<main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 bg-slate-50/50 min-h-screen min-w-0">
    <div class="max-w-6xl mx-auto space-y-5">
        
        <!-- Flash Alert Notification -->
        @if (session('success'))
        <div class="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-2xs">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                <p class="text-xs sm:text-sm font-medium">{{ session('success') }}</p>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        @endif

        @if ($errors->any())
        <div class="flex items-center justify-between p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-2xs">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
                <p class="text-xs sm:text-sm font-medium">{{ $errors->first() }}</p>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        @endif

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="p-3 bg-blue-600 text-white rounded-2xl shadow-sm shadow-blue-500/20">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Manajemen Izin Keluar</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Pantau akumulasi durasi izin keluar pemagang dan tetapkan kebijakan Bebas Waktu atau Wajib Ganti Waktu.</p>
                </div>
            </div>
        </div>

        <!-- 5 Summary Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <!-- 1. Total Izin Keluar Hari Ini -->
            <a href="{{ route('admin.izinKeluar.index', array_filter(['search' => $search])) }}"
                class="bg-white p-3.5 rounded-2xl border {{ empty($statusFilter) || $statusFilter === 'all' ? 'border-slate-800 ring-2 ring-slate-800/10' : 'border-slate-200/90' }} shadow-2xs hover:shadow-sm transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                    <i class="fas fa-users text-sm"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-lg font-bold text-slate-800 leading-none">{{ $summaryMetrics['total_leave_today'] ?? 0 }}</div>
                    <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mt-1 truncate">Izin Keluar</div>
                </div>
            </a>

            <!-- 2. Sedang di Luar -->
            <a href="{{ route('admin.izinKeluar.index', array_filter(['status' => 'sedang_keluar', 'search' => $search])) }}"
                class="bg-white p-3.5 rounded-2xl border {{ $statusFilter === 'sedang_keluar' ? 'border-amber-500 ring-2 ring-amber-500/20 bg-amber-50/20' : 'border-slate-200/90' }} shadow-2xs hover:shadow-sm transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl {{ ($summaryMetrics['total_active'] ?? 0) > 0 ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center shrink-0">
                    <i class="fas fa-walking text-sm"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-lg font-bold {{ ($summaryMetrics['total_active'] ?? 0) > 0 ? 'text-amber-600' : 'text-slate-800' }} leading-none">{{ $summaryMetrics['total_active'] ?? 0 }}</div>
                    <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mt-1 truncate">Sedang Keluar</div>
                </div>
            </a>

            <!-- 3. Sudah Kembali -->
            <a href="{{ route('admin.izinKeluar.index', array_filter(['status' => 'sudah_kembali', 'search' => $search])) }}"
                class="bg-white p-3.5 rounded-2xl border {{ $statusFilter === 'sudah_kembali' ? 'border-blue-500 ring-2 ring-blue-500/20 bg-blue-50/20' : 'border-slate-200/90' }} shadow-2xs hover:shadow-sm transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center shrink-0">
                    <i class="fas fa-check-double text-sm"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-lg font-bold text-blue-700 leading-none">{{ $summaryMetrics['total_completed'] ?? 0 }}</div>
                    <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mt-1 truncate">Sudah Kembali</div>
                </div>
            </a>

            <!-- 4. Bebas Waktu -->
            <a href="{{ route('admin.izinKeluar.index', array_filter(['status' => 'bebas_waktu', 'search' => $search])) }}"
                class="bg-white p-3.5 rounded-2xl border {{ $statusFilter === 'bebas_waktu' ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/20' : 'border-slate-200/90' }} shadow-2xs hover:shadow-sm transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shrink-0">
                    <i class="fas fa-circle-check text-sm"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-lg font-bold text-emerald-700 leading-none">{{ $summaryMetrics['total_bebas_waktu'] ?? 0 }}</div>
                    <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mt-1 truncate">Bebas Waktu</div>
                </div>
            </a>

            <!-- 5. Wajib Ganti Waktu -->
            <a href="{{ route('admin.izinKeluar.index', array_filter(['status' => 'wajib_ganti', 'search' => $search])) }}"
                class="bg-white p-3.5 rounded-2xl border {{ $statusFilter === 'wajib_ganti' ? 'border-purple-500 ring-2 ring-purple-500/20 bg-purple-50/20' : 'border-slate-200/90' }} shadow-2xs hover:shadow-sm transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 border border-purple-200 flex items-center justify-center shrink-0">
                    <i class="fas fa-clock-rotate-left text-sm"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-lg font-bold text-purple-700 leading-none">{{ $summaryMetrics['total_wajib_ganti'] ?? 0 }}</div>
                    <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mt-1 truncate">Wajib Ganti</div>
                </div>
            </a>
        </div>

        <!-- Filter & Search Container Card -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <!-- Search & Filter Controls -->
            <div class="p-4 sm:p-5 border-b border-slate-100 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Daftar Kehadiran & Izin Keluar Hari Ini</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Tanggal: {{ \Carbon\Carbon::today()->isoFormat('dddd, D MMMM Y') }}</p>
                    </div>

                    <!-- Search Form -->
                    <form method="GET" action="{{ route('admin.izinKeluar.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
                        @if($statusFilter)
                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                        @endif
                        <div class="relative w-full sm:w-72">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-magnifying-glass text-xs"></i>
                            </div>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, divisi, sekolah..."
                                class="w-full pl-9 pr-8 py-2 text-xs border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50/70 focus:bg-white transition-all">
                            @if($search)
                            <a href="{{ route('admin.izinKeluar.index', array_filter(['status' => $statusFilter])) }}"
                                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600" title="Hapus pencarian">
                                <i class="fas fa-times text-xs"></i>
                            </a>
                            @endif
                        </div>
                        <button type="submit" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-2xs transition shrink-0">
                            Cari
                        </button>
                    </form>
                </div>

                <!-- Segmented Tabs Filter -->
                <div class="pt-3 border-t border-slate-100 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-2 w-full">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 w-full flex-1">
                        <!-- Tab: Semua -->
                        @php $isAll = empty($statusFilter) || $statusFilter === 'all'; @endphp
                        <a href="{{ route('admin.izinKeluar.index', array_filter(['search' => $search])) }}"
                            class="flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border {{ $isAll ? 'bg-slate-800 text-white border-slate-800 shadow-2xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                            <span class="truncate">Semua Pemagang</span>
                            <span class="ml-1 px-1.5 py-0.2 rounded-md text-[10px] font-bold shrink-0 {{ $isAll ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-700' }}">
                                {{ $interns->total() }}
                            </span>
                        </a>

                        <!-- Tab: Sedang Keluar -->
                        @php $isAct = $statusFilter === 'sedang_keluar'; @endphp
                        <a href="{{ route('admin.izinKeluar.index', array_filter(['status' => 'sedang_keluar', 'search' => $search])) }}"
                            class="flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border {{ $isAct ? 'bg-slate-800 text-white border-slate-800 shadow-2xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                            <span class="truncate">Sedang di Luar</span>
                            <span class="ml-1 px-1.5 py-0.2 rounded-md text-[10px] font-bold shrink-0 {{ $isAct ? 'bg-slate-700 text-amber-300' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                {{ $summaryMetrics['total_active'] ?? 0 }}
                            </span>
                        </a>

                        <!-- Tab: Sudah Kembali -->
                        @php $isDone = $statusFilter === 'sudah_kembali'; @endphp
                        <a href="{{ route('admin.izinKeluar.index', array_filter(['status' => 'sudah_kembali', 'search' => $search])) }}"
                            class="flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border {{ $isDone ? 'bg-slate-800 text-white border-slate-800 shadow-2xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                            <span class="truncate">Sudah Kembali</span>
                            <span class="ml-1 px-1.5 py-0.2 rounded-md text-[10px] font-bold shrink-0 {{ $isDone ? 'bg-slate-700 text-blue-300' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                {{ $summaryMetrics['total_completed'] ?? 0 }}
                            </span>
                        </a>

                        <!-- Tab: Bebas Waktu -->
                        @php $isBebas = $statusFilter === 'bebas_waktu'; @endphp
                        <a href="{{ route('admin.izinKeluar.index', array_filter(['status' => 'bebas_waktu', 'search' => $search])) }}"
                            class="flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border {{ $isBebas ? 'bg-slate-800 text-white border-slate-800 shadow-2xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                            <span class="truncate">Bebas Waktu</span>
                            <span class="ml-1 px-1.5 py-0.2 rounded-md text-[10px] font-bold shrink-0 {{ $isBebas ? 'bg-slate-700 text-emerald-300' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                {{ $summaryMetrics['total_bebas_waktu'] ?? 0 }}
                            </span>
                        </a>

                        <!-- Tab: Wajib Ganti Waktu -->
                        @php $isGanti = $statusFilter === 'wajib_ganti'; @endphp
                        <a href="{{ route('admin.izinKeluar.index', array_filter(['status' => 'wajib_ganti', 'search' => $search])) }}"
                            class="flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border {{ $isGanti ? 'bg-slate-800 text-white border-slate-800 shadow-2xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                            <span class="truncate">Wajib Ganti</span>
                            <span class="ml-1 px-1.5 py-0.2 rounded-md text-[10px] font-bold shrink-0 {{ $isGanti ? 'bg-slate-700 text-purple-300' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                {{ $summaryMetrics['total_wajib_ganti'] ?? 0 }}
                            </span>
                        </a>
                    </div>

                    <!-- Reset Button -->
                    @if($search || $statusFilter)
                    <a href="{{ route('admin.izinKeluar.index') }}" class="px-3 py-1.5 text-xs text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-xl border border-slate-200 hover:border-rose-200 font-semibold inline-flex items-center justify-center gap-1.5 shrink-0 transition bg-white shadow-2xs">
                        <i class="fas fa-rotate-left text-xs"></i>
                        <span>Reset</span>
                    </a>
                    @endif
                </div>
            </div>

            <!-- List Card Pemagang (Structured & Refined Layout) -->
            <div class="p-4 sm:p-5 space-y-3.5">
                @forelse($interns as $intern)
                @php
                    $name = $intern->user?->profile?->full_name ?? $intern->user?->name ?? 'Pemagang';
                    $divisionName = $intern->division?->name ?? 'Tanpa Divisi';
                    $schoolName = $intern->school?->name ?? '-';

                    $todayLeaves = $intern->today_leave_permits_collection;
                    $hasLeaveToday = $todayLeaves->isNotEmpty();
                    $activeLeave = $intern->today_active_leave_permit;
                    $latestLeave = $intern->today_latest_leave_permit;
                    $totalMinutes = $intern->today_total_leave_minutes;
                    $formattedDuration = $intern->today_leave_formatted_duration;

                    // Status persetujuan
                    $isMandatory = $latestLeave && (bool) $latestLeave->is_mandatory_replace;
                    $isApproved = $latestLeave && $latestLeave->approval_status === 'approved';
                    $isRejected = $latestLeave && $latestLeave->approval_status === 'rejected';
                    $isPending = $latestLeave && $latestLeave->approval_status === 'pending';
                @endphp
                <div class="p-4 rounded-2xl border transition-all shadow-2xs hover:shadow-xs {{ $activeLeave ? 'bg-amber-50/20 border-amber-300 ring-1 ring-amber-300/40' : ($hasLeaveToday ? 'bg-white border-slate-200/90 hover:border-slate-300' : 'bg-slate-50/50 border-slate-200/70') }}">
                    
                    <!-- 1. Header Bar: Identitas Pemagang (Kiri) & Badges Status (Kanan) -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                        <!-- Profil Pemagang -->
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl {{ $activeLeave ? 'bg-amber-500 text-white shadow-xs shadow-amber-500/30' : ($hasLeaveToday ? 'bg-slate-800 text-white shadow-2xs' : 'bg-slate-200 text-slate-600') }} flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-bold text-slate-900 text-sm leading-tight truncate" title="{{ $name }}">
                                        {{ $name }}
                                    </h3>
                                    <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200/60 shrink-0">
                                        {{ $divisionName }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 truncate mt-0.5" title="{{ $schoolName }}">
                                    <i class="fa-solid fa-graduation-cap text-slate-400 mr-1"></i>{{ $schoolName }}
                                </div>
                            </div>
                        </div>

                        <!-- Status Kehadiran & Keputusan Admin Badges -->
                        <div class="flex items-center gap-2 flex-wrap shrink-0">
                            @if($activeLeave)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                </span>
                                <span>Sedang di Luar</span>
                            </span>
                            @elseif($hasLeaveToday)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                <i class="fa-solid fa-check text-blue-600"></i>
                                <span>Sudah Kembali</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-100 text-slate-500 border border-slate-200">
                                <i class="fa-solid fa-user-check text-slate-400"></i>
                                <span>Hadir Normal</span>
                            </span>
                            @endif

                            @if($hasLeaveToday)
                                @if($isApproved && $isMandatory)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-300">
                                    <i class="fa-solid fa-clock-rotate-left text-purple-600"></i>
                                    <span>Wajib Ganti: {{ $latestLeave->agreed_duration_minutes ?? $totalMinutes }}m</span>
                                </span>
                                @elseif($isApproved && !$isMandatory)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                    <span>Bebas Waktu</span>
                                </span>
                                @elseif($isRejected)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                    <i class="fa-solid fa-ban text-rose-600"></i>
                                    <span>Ditolak</span>
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="fa-solid fa-hourglass-half text-amber-500"></i>
                                    <span>Menunggu Keputusan</span>
                                </span>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- 2. Body Grid: 3 Info Tiles (Akumulasi Waktu, Sesi Terakhir, Keterangan) -->
                    @if($hasLeaveToday)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2.5 pt-1">
                        <!-- Tile 1: Akumulasi Waktu -->
                        <div class="bg-slate-50/80 rounded-xl p-3 border border-slate-100/90 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shrink-0">
                                <i class="fa-regular fa-clock text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Akumulasi Waktu</span>
                                <div class="flex items-baseline gap-1.5 flex-wrap">
                                    <span class="text-sm font-bold text-slate-800">{{ $formattedDuration }}</span>
                                    <span class="text-xs text-slate-500 font-semibold">({{ $totalMinutes }} menit)</span>
                                </div>
                                <span class="text-[10px] text-slate-400 font-medium block mt-0.5">
                                    <i class="fa-solid fa-rotate text-[9px] mr-1 text-slate-400"></i>{{ $todayLeaves->count() }}x Sesi Keluar Hari Ini
                                </span>
                            </div>
                        </div>

                        <!-- Tile 2: Sesi Terakhir -->
                        <div class="bg-slate-50/80 rounded-xl p-3 border border-slate-100/90 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl {{ $activeLeave ? 'bg-amber-100 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600 border border-slate-200/60' }} flex items-center justify-center shrink-0">
                                <i class="fas fa-door-open text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Sesi Terakhir</span>
                                @if($activeLeave)
                                    <div class="text-sm font-bold text-amber-700 truncate">
                                        Mulai {{ \Carbon\Carbon::parse($activeLeave->start_time)->format('H:i') }} WIB
                                    </div>
                                    <span class="text-[10px] text-amber-600 font-semibold block mt-0.5">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse mr-1"></span>Sedang Berjalan...
                                    </span>
                                @elseif($latestLeave)
                                    <div class="text-sm font-bold text-slate-800 truncate">
                                        {{ \Carbon\Carbon::parse($latestLeave->start_time)->format('H:i') }} - {{ $latestLeave->end_time ? \Carbon\Carbon::parse($latestLeave->end_time)->format('H:i') : '-' }} WIB
                                    </div>
                                    <span class="text-[10px] text-slate-500 font-medium block mt-0.5">
                                        Durasi Sesi: <strong class="text-slate-700">{{ $latestLeave->duration_in_minutes ?? 0 }} Menit</strong>
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Tidak ada sesi</span>
                                @endif
                            </div>
                        </div>

                        <!-- Tile 3: Keterangan & Izin -->
                        <div class="bg-slate-50/80 rounded-xl p-3 border border-slate-100/90 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-comment-dots text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Keterangan</span>
                                    <span class="text-[10px] text-slate-500 truncate">
                                        Izin: <strong class="text-slate-700">{{ $latestLeave?->authorized_by ?: 'Atasan' }}</strong>
                                    </span>
                                </div>
                                <p class="text-xs text-slate-700 font-medium truncate mt-0.5" title="{{ $latestLeave?->description }}">
                                    "{{ $latestLeave?->description ?: 'Tanpa keterangan' }}"
                                </p>
                                <span class="text-[10px] text-slate-400 block mt-0.5 truncate">
                                    Status Log: <strong class="{{ $isApproved ? 'text-emerald-600' : 'text-amber-600' }}">{{ $latestLeave?->approval_status ? ucfirst($latestLeave->approval_status) : 'Pending' }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="p-3 bg-slate-50/50 rounded-xl border border-slate-100 text-xs text-slate-400 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-slate-400"></i>
                        <span>Belum ada riwayat izin keluar pada hari ini.</span>
                    </div>
                    @endif

                    <!-- 3. Footer Action Bar: Riwayat (Kiri) & Tombol Aksi Admin (Kanan) -->
                    <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <!-- Riwayat Sesi Button -->
                        <a href="{{ route('admin.keluar.history.detail', $intern->id) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-slate-600 hover:text-blue-600 hover:bg-blue-50 border border-slate-200/80 hover:border-blue-200 rounded-xl text-xs font-semibold transition-all shadow-2xs bg-white w-fit">
                            <i class="fas fa-history text-xs text-slate-400"></i>
                            <span>Riwayat Lengkap Sesi</span>
                        </a>

                        <!-- Aksi Keputusan Admin -->
                        @if($hasLeaveToday && $latestLeave)
                        <div class="flex items-center gap-2 flex-wrap justify-end">
                            <!-- Tombol Bebas Waktu -->
                            <form method="POST" action="{{ route('admin.leave-permit.bebas-waktu', $latestLeave->id) }}"
                                onsubmit="return confirm('Izinkan pemagang {{ $name }} keluar dengan BEBAS WAKTU (tanpa wajib ganti jam)?');">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 {{ $isApproved && !$isMandatory ? 'bg-emerald-700 ring-2 ring-emerald-300' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white rounded-xl text-xs font-bold shadow-2xs transition-all cursor-pointer hover:shadow-xs"
                                    title="Bebaskan dari kewajiban ganti waktu (Lunas/Diizinkan)">
                                    <i class="fa-solid fa-check text-xs"></i>
                                    <span>{{ $isApproved && !$isMandatory ? 'Bebas Waktu (Aktif)' : 'Bebas Waktu' }}</span>
                                </button>
                            </form>

                            <!-- Tombol Wajib Ganti Waktu -->
                            <button type="button"
                                class="open-wajib-ganti-btn inline-flex items-center gap-1.5 px-3.5 py-1.5 {{ $isApproved && $isMandatory ? 'bg-purple-700 ring-2 ring-purple-300' : 'bg-purple-600 hover:bg-purple-700' }} text-white rounded-xl text-xs font-bold shadow-2xs transition-all cursor-pointer hover:shadow-xs"
                                data-action="{{ route('admin.leave-permit.wajib-ganti', $latestLeave->id) }}"
                                data-name="{{ $name }}"
                                data-minutes="{{ $totalMinutes > 0 ? $totalMinutes : ($latestLeave->agreed_duration_minutes ?? 15) }}"
                                title="Tetapkan kewajiban mengganti waktu kerja">
                                <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                                <span>{{ $isApproved && $isMandatory ? 'Ubah Wajib Ganti' : 'Wajib Ganti' }}</span>
                            </button>
                        </div>
                        @endif
                    </div>

                </div>
                @empty
                <div class="p-12 text-center text-slate-400 bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                    <div class="w-12 h-12 mx-auto rounded-full bg-white flex items-center justify-center text-slate-400 mb-2.5 shadow-2xs border border-slate-100">
                        <i class="fas fa-sign-out-alt text-xl text-slate-400"></i>
                    </div>
                    <div class="font-bold text-slate-700 text-sm">Tidak Ada Data Pemagang</div>
                    <p class="text-xs text-slate-400 mt-0.5">Tidak ditemukan pemagang yang sesuai dengan pencarian atau filter status.</p>
                </div>
                @endforelse
            </div>

            <!-- Footer Pagination -->
            @if($interns->hasPages() || $interns->total() > 0)
            <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-50/50">
                <div class="text-xs text-slate-500 font-medium">
                    Menampilkan <span class="font-bold text-slate-700">{{ $interns->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-700">{{ $interns->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-700">{{ $interns->total() }}</span> pemagang
                </div>
                <div>
                    {{ $interns->appends(request()->except('page'))->links('vendor.pagination.custom-pagination') }}
                </div>
            </div>
            @endif

        </div>

    </div>
</main>

<!-- Modal: Tetapkan Wajib Ganti Waktu -->
<div id="wajibGantiModal" class="fixed inset-0 z-50 items-center justify-center bg-black/60 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-auto overflow-hidden animate-fadeIn">
        <div class="flex justify-between items-center px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-purple-600"></i>
                <span>Tetapkan Wajib Ganti Waktu</span>
            </h3>
            <button type="button" onclick="closeWajibGantiModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
        </div>

        <form id="wajibGantiForm" method="POST" class="p-6 space-y-4">
            @csrf

            <div>
                <span class="text-xs text-slate-400">Pemagang:</span>
                <div id="modalInternName" class="text-sm font-bold text-slate-800"></div>
            </div>

            <div>
                <label for="modalMinutesInput" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Durasi yang Wajib Diganti (Menit) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" name="minutes" id="modalMinutesInput" min="1" max="480" required
                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
                    <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs text-slate-400 font-semibold pointer-events-none">
                        Menit
                    </span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Otomatis terisi dari akumulasi waktu keluar pemagang. Anda dapat menyesuaikannya bila diperlukan.</p>
            </div>

            <div>
                <label for="modalAdminNotes" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Catatan Admin <span class="text-slate-400 font-normal">(opsional)</span>
                </label>
                <textarea name="admin_notes" id="modalAdminNotes" rows="2" maxlength="500"
                    placeholder="Contoh: Wajib ganti jam pada shift berikutnya..."
                    class="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeWajibGantiModal()"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition shadow-2xs">
                    <i class="fa-solid fa-save mr-1.5"></i>Simpan Wajib Ganti
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openWajibGantiModal(btn) {
        const modal = document.getElementById('wajibGantiModal');
        const form = document.getElementById('wajibGantiForm');
        const nameEl = document.getElementById('modalInternName');
        const minutesEl = document.getElementById('modalMinutesInput');
        const notesEl = document.getElementById('modalAdminNotes');

        form.action = btn.dataset.action;
        nameEl.textContent = btn.dataset.name || 'Pemagang';
        minutesEl.value = btn.dataset.minutes || '15';
        notesEl.value = '';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeWajibGantiModal() {
        const modal = document.getElementById('wajibGantiModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.open-wajib-ganti-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                openWajibGantiModal(this);
            });
        });

        const modal = document.getElementById('wajibGantiModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeWajibGantiModal();
            });
        }
    });
</script>
@endsection