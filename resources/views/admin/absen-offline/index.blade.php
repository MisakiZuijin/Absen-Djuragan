@extends('layouts.main')

@section('title', 'Presensi Offline')

@section('contents')
<main class="ml-0 md:ml-48 lg:ml-64 mt-16 md:mt-20 p-3 sm:p-6 bg-gray-50 pb-12 min-h-screen min-w-0">
    <div class="w-full space-y-5">

        <!-- Header Section -->
        <div class="relative bg-white rounded-2xl shadow-sm border border-gray-200 p-5 md:p-6 overflow-hidden">
            <div class="absolute top-0 right-0 w-48 h-48 bg-blue-50 rounded-full -translate-y-20 translate-x-20 pointer-events-none"></div>
            <div class="absolute bottom-0 right-36 w-32 h-32 bg-indigo-50 rounded-full translate-y-16 pointer-events-none"></div>

            <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center space-x-3.5">
                    <div class="w-12 h-12 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-md shadow-blue-500/20 text-white shrink-0">
                        <!-- SVG Clipboard User -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight">Presensi Offline</h1>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $isAssistant ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                {{ $isAssistant ? 'Assistant Admin' : 'Admin' }}
                            </span>
                        </div>
                        <p class="text-gray-500 text-xs md:text-sm mt-0.5">Pemeriksaan & verifikasi kehadiran fisik pemagang langsung di kantor</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Date Picker Filter -->
                    <form method="GET" action="{{ route($routePrefix . 'index') }}" class="flex items-center gap-2 bg-gray-50 hover:bg-gray-100 px-3 py-2 rounded-xl border border-gray-200 shadow-xs transition-all">
                        <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                            class="bg-transparent text-xs font-semibold text-gray-800 focus:outline-none border-none cursor-pointer">
                        @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                    </form>

                    <!-- Tombol Buka Modal Form Presensi Offline -->
                    <button type="button" onclick="openOfflineModal()"
                        class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs rounded-xl shadow-xs hover:shadow transition-all">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Presensi Offline</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Alert Notifications -->
        @if(session('success'))
        <div class="flex items-center p-3.5 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl text-emerald-800 shadow-xs text-sm">
            <svg class="w-5 h-5 text-emerald-600 mr-2.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="flex-1 font-medium">{{ session('success') }}</div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 ml-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div class="flex items-center p-3.5 bg-red-50 border-l-4 border-red-500 rounded-r-xl text-red-800 shadow-xs text-sm">
            <svg class="w-5 h-5 text-red-600 mr-2.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="flex-1 font-medium">{{ session('error') }}</div>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 ml-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        @endif

        <!-- Statistik Ringkas Hari Ini (2 Baris x 3 Card) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
            <!-- 1. Total Pemagang -->
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-xl font-bold text-gray-900 leading-none">{{ $totalInterns }}</div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">Total Pemagang</div>
                </div>
            </div>

            <!-- 2. Hadir Fisik (Offline) -->
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-xl font-bold text-emerald-600 leading-none">{{ $totalOfflineHadir }}</div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">Hadir ({{ $totalOfflineEarly }} Early)</div>
                </div>
            </div>

            <!-- 3. Terlambat Fisik -->
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-xl font-bold text-amber-600 leading-none">{{ $totalOfflineTerlambat }}</div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">Terlambat Fisik</div>
                </div>
            </div>

            <!-- 4. Izin Keperluan & Izin Sakit -->
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-xl font-bold text-sky-700 leading-none">{{ $totalOfflineIzin + $totalOfflineSakit }}</div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">Izin: {{ $totalOfflineIzin }} | Sakit: {{ $totalOfflineSakit }}</div>
                </div>
            </div>

            <!-- 5. Alpha Fisik -->
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-xl font-bold text-red-600 leading-none">{{ $totalOfflineAlpha }}</div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">Alpha Fisik</div>
                </div>
            </div>

            <!-- 6. Indikasi Bohong -->
            <div class="bg-white p-4 rounded-2xl border {{ $totalFraud > 0 ? 'border-red-300 bg-red-50/30' : 'border-gray-200' }} shadow-xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl {{ $totalFraud > 0 ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-400' }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-xl font-bold {{ $totalFraud > 0 ? 'text-red-600' : 'text-gray-900' }} leading-none">{{ $totalFraud }}</div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">Indikasi Bohong</div>
                </div>
            </div>
        </div>

        <!-- Tabel Rekap Presensi Tanggal Terpilih -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Table Header Bar -->
            <div class="p-4 sm:p-5 border-b border-gray-100 space-y-3.5">
                <!-- Baris Atas: Judul & Form Pencarian + Dropdown Rincian Status -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg">Daftar Kehadiran & Verifikasi Pemagang</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Komparasi data online vs pemeriksaan offline pada tanggal {{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }}</p>
                    </div>

                    <!-- Search Form Bar -->
                    <form method="GET" action="{{ route($routePrefix . 'index') }}" class="flex items-center gap-2 w-full sm:w-auto">
                        <input type="hidden" name="date" value="{{ $date }}">
                        @if($statusFilter)
                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                        @endif
                        <div class="relative w-full sm:w-72">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, divisi, sekolah..."
                                class="w-full pl-9 pr-8 py-2 text-xs border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50/70 focus:bg-white transition-all">
                            @if($search)
                            <a href="{{ route($routePrefix . 'index', array_filter(['date' => $date, 'status' => $statusFilter])) }}"
                                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600" title="Hapus pencarian">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </a>
                            @endif
                        </div>
                        <button type="submit" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-xs font-bold shadow-xs transition-colors shrink-0">
                            Cari
                        </button>
                    </form>
                </div>

                <!-- Baris Bawah: Segmented Filter Utama (Full Kiri-Kanan & Warna Soft / Elegan) -->
                <div class="pt-3 border-t border-gray-100 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-2.5 w-full">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 w-full flex-1">
                        <!-- 1. Semua -->
                        @php $isAll = empty($statusFilter) || $statusFilter === 'all'; @endphp
                        <a href="{{ route($routePrefix . 'index', array_filter(['date' => $date, 'search' => $search])) }}"
                            class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all border {{ $isAll ? 'bg-blue-600 text-white border-blue-600 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 hover:border-slate-300' }}">
                            <div class="flex items-center gap-2 truncate">
                                <svg class="w-3.5 h-3.5 shrink-0 {{ $isAll ? 'text-blue-100' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span class="truncate">Semua</span>
                            </div>
                            <span class="ml-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $isAll ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-700' }}">
                                {{ $totalInterns }}
                            </span>
                        </a>

                        <!-- 2. Sudah Terabsen -->
                        @php $isSudah = $statusFilter === 'sudah_diabsen'; @endphp
                        <a href="{{ route($routePrefix . 'index', array_filter(['date' => $date, 'search' => $search, 'status' => 'sudah_diabsen'])) }}"
                            class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all border {{ $isSudah ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 hover:border-slate-300' }}">
                            <div class="flex items-center gap-2 truncate">
                                <svg class="w-3.5 h-3.5 shrink-0 {{ $isSudah ? 'text-emerald-100' : 'text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="truncate">Sudah Terabsen</span>
                            </div>
                            <span class="ml-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $isSudah ? 'bg-emerald-700 text-white' : 'bg-emerald-50 text-emerald-700 border border-emerald-100' }}">
                                {{ $totalSudahDiabsen }}
                            </span>
                        </a>

                        <!-- 3. Belum Terabsen -->
                        @php $isBelum = $statusFilter === 'belum_diabsen'; @endphp
                        <a href="{{ route($routePrefix . 'index', array_filter(['date' => $date, 'search' => $search, 'status' => 'belum_diabsen'])) }}"
                            class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all border {{ $isBelum ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 hover:border-slate-300' }}">
                            <div class="flex items-center gap-2 truncate">
                                <svg class="w-3.5 h-3.5 shrink-0 {{ $isBelum ? 'text-amber-100' : 'text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="truncate">Belum Terabsen</span>
                            </div>
                            <span class="ml-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $isBelum ? 'bg-amber-700 text-white' : 'bg-amber-50 text-amber-700 border border-amber-100' }}">
                                {{ $totalUnverified }}
                            </span>
                        </a>

                        <!-- 4. Menunggu Konfirmasi Admin (Pending) -->
                        @php $isPending = $statusFilter === 'pending'; @endphp
                        <a href="{{ route($routePrefix . 'index', array_filter(['date' => $date, 'search' => $search, 'status' => 'pending'])) }}"
                            class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all border {{ $isPending ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : ($totalPendingApproval > 0 ? 'bg-amber-50/70 text-amber-900 border-amber-300 hover:bg-amber-100/60' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 hover:border-slate-300') }}">
                            <div class="flex items-center gap-2 truncate">
                                <svg class="w-3.5 h-3.5 shrink-0 {{ $isPending ? 'text-indigo-100' : ($totalPendingApproval > 0 ? 'text-amber-600 animate-pulse' : 'text-slate-400') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="truncate">Konfirmasi</span>
                            </div>
                            <span class="ml-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $isPending ? 'bg-indigo-700 text-white' : ($totalPendingApproval > 0 ? 'bg-amber-200 text-amber-900 font-extrabold border border-amber-300' : 'bg-slate-100 text-slate-700') }}">
                                {{ $totalPendingApproval }}
                            </span>
                        </a>

                        <!-- 5. Alpha -->
                        @php $isAlpha = $statusFilter === 'alpha'; @endphp
                        <a href="{{ route($routePrefix . 'index', array_filter(['date' => $date, 'search' => $search, 'status' => 'alpha'])) }}"
                            class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all border {{ $isAlpha ? 'bg-rose-600 text-white border-rose-600 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 hover:border-slate-300' }}">
                            <div class="flex items-center gap-2 truncate">
                                <svg class="w-3.5 h-3.5 shrink-0 {{ $isAlpha ? 'text-rose-100' : 'text-rose-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                                <span class="truncate">Alpha</span>
                            </div>
                            <span class="ml-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $isAlpha ? 'bg-rose-700 text-white' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                                {{ $totalOfflineAlpha }}
                            </span>
                        </a>

                        <!-- 6. Indikasi Bohong -->
                        @php $isFraud = $statusFilter === 'fraud'; @endphp
                        <a href="{{ route($routePrefix . 'index', array_filter(['date' => $date, 'search' => $search, 'status' => 'fraud'])) }}"
                            class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all border {{ $isFraud ? 'bg-red-600 text-white border-red-600 shadow-xs' : ($totalFraud > 0 ? 'bg-rose-50/40 text-rose-800 border-rose-200 hover:bg-rose-50' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 hover:border-slate-300') }}">
                            <div class="flex items-center gap-2 truncate">
                                <svg class="w-3.5 h-3.5 shrink-0 {{ $isFraud ? 'text-red-100' : ($totalFraud > 0 ? 'text-rose-600' : 'text-slate-400') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span class="truncate">Indikasi Bohong</span>
                            </div>
                            <span class="ml-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $isFraud ? 'bg-red-700 text-white' : ($totalFraud > 0 ? 'bg-rose-100 text-rose-700 border border-rose-200 font-bold' : 'bg-slate-100 text-slate-700') }}">
                                {{ $totalFraud }}
                            </span>
                        </a>
                    </div>

                    <!-- Reset Button -->
                    @if($search || $statusFilter)
                    <a href="{{ route($routePrefix . 'index', ['date' => $date]) }}" class="px-3 py-2 text-xs text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-xl border border-slate-200 hover:border-rose-200 font-semibold inline-flex items-center justify-center gap-1.5 shrink-0 transition bg-white shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>Reset</span>
                    </a>
                    @endif
                </div>
            </div>

            <!-- Daftar Item Presensi Pemagang (Compact Card Layout) -->
            <div class="p-4 sm:p-5 space-y-2.5">
                @forelse($paginatedInterns as $index => $item)
                <div class="p-3 sm:p-3.5 rounded-xl border transition-all {{ $item->verification_status === 'fraud' || $item->verification_status === 'fake_sickness' ? 'bg-rose-50/40 border-rose-200' : 'bg-white border-slate-200/90 hover:border-slate-300 hover:shadow-2xs' }}">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 sm:gap-4">

                        <!-- Bagian 1: Identitas Pemagang & Jadwal (Kiri) -->
                        <div class="flex items-start gap-3 min-w-[240px] max-w-sm shrink-0">
                            <!-- Row Number & Avatar -->
                            <div class="relative shrink-0">
                                <div class="w-10 h-10 rounded-xl bg-slate-800 text-white flex items-center justify-center font-bold text-xs shadow-2xs">
                                    {{ strtoupper(substr($item->user?->name ?? 'P', 0, 2)) }}
                                </div>
                                <span class="absolute -top-1.5 -left-1.5 px-1.5 py-0.2 bg-slate-100 text-slate-600 rounded text-[9px] font-bold border border-slate-300 shadow-2xs">
                                    #{{ ($paginatedInterns->currentPage() - 1) * $paginatedInterns->perPage() + $loop->iteration }}
                                </span>
                            </div>

                            <!-- Detail Pemagang & Shift -->
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-slate-900 text-sm leading-tight truncate" title="{{ $item->user?->name }}">
                                        {{ $item->user?->name ?? 'Pemagang' }}
                                    </span>
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200/60 shrink-0">
                                        {{ $item->division?->name ?? 'Tanpa Divisi' }}
                                    </span>
                                </div>

                                <div class="text-[11px] text-slate-500 truncate mt-0.5" title="{{ $item->school?->name }}">
                                    {{ $item->school?->name ?? '-' }}
                                </div>

                                <!-- Shift & Lokasi -->
                                @php
                                $shiftObj = $item->assigned_shift ?? $item->detail_schedule?->shift ?? $item->offline_record?->shift;
                                $shiftName = $shiftObj?->name ?? $item->assigned_shift_name ?? 'Shift Pagi';
                                $shiftTime = $item->assigned_shift_time ?: ($shiftObj && $shiftObj->start_time && $shiftObj->end_time ? (\Carbon\Carbon::parse($shiftObj->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($shiftObj->end_time)->format('H:i')) : null);
                                $officeObj = $item->assigned_office ?? $item->detail_schedule?->office ?? $item->offline_record?->office;
                                $officeName = $officeObj?->name ?? $item->assigned_office_name ?? 'Kantor Utama';
                                $workType = $item->work_type ?? $item->detail_schedule?->work_type;
                                @endphp
                                <div class="flex items-center gap-1.5 text-[10px] text-slate-500 font-medium mt-1 flex-wrap">
                                    <span class="inline-flex items-center gap-1 text-slate-700 font-semibold">
                                        <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>{{ $shiftName }}@if($workType) ({{ strtoupper($workType) }})@endif</span>
                                    </span>
                                    @if($shiftTime)
                                    <span class="text-slate-300">•</span>
                                    <span>{{ $shiftTime }}</span>
                                    @endif
                                    <span class="text-slate-300">•</span>
                                    <span class="truncate max-w-[130px]">{{ $officeName }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Bagian 2: Komparasi Online vs Offline & Hasil Verifikasi (Tengah) -->
                        <div class="flex-1 min-w-0 bg-slate-50/70 rounded-xl p-2.5 sm:p-3 border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-2.5">

                            <!-- Box Komparasi Status Presensi -->
                            <div class="grid grid-cols-2 gap-2 flex-1 min-w-0">
                                <!-- Online Attendance -->
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Presensi Online</span>
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @if($item->online_status_key === 'hadir')
                                        <span class="text-xs font-bold text-slate-800">{{ $item->online_time ? $item->online_time . ' WIB' : '-' }}</span>
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Hadir
                                        </span>
                                        @elseif($item->online_status_key === 'terlambat')
                                        <span class="text-xs font-bold text-amber-700">{{ $item->online_time ? $item->online_time . ' WIB' : '-' }}</span>
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Telat {{ $item->online_late_minutes }}m
                                        </span>
                                        @elseif($item->online_status_key === 'izin_keperluan' || $item->online_status_key === 'izin')
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Izin Keperluan
                                        </span>
                                        @elseif($item->online_status_key === 'izin_sakit')
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Izin Sakit
                                        </span>
                                        @elseif($item->online_status_key === 'alpha')
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Alpha
                                        </span>
                                        @else
                                        <span class="text-[11px] text-slate-400 font-medium italic">Belum Absen</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Physical / Offline Attendance -->
                                <div class="flex flex-col gap-0.5 border-l border-slate-200/80 pl-2.5">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pemeriksaan Fisik</span>
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @if($item->offline_record)
                                        @if($item->physical_checkin_time && !in_array($item->offline_status_key, ['izin', 'sakit', 'alpha']))
                                        <span class="text-xs font-bold text-slate-800">{{ $item->physical_checkin_time }} WIB</span>
                                        @elseif($item->offline_time && !in_array($item->offline_status_key, ['izin', 'sakit', 'alpha']))
                                        <span class="text-xs font-bold text-slate-800">{{ $item->offline_time }} WIB</span>
                                        @endif

                                        @if($item->offline_status_key === 'hadir')
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Tepat Waktu
                                        </span>
                                        @elseif($item->offline_status_key === 'early')
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-cyan-100 text-cyan-800 border border-cyan-300">
                                            <svg class="w-3 h-3 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                            </svg>
                                            Lebih Awal
                                        </span>
                                        @elseif($item->offline_status_key === 'terlambat')
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                            <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Telat {{ $item->offline_late_minutes }}m
                                        </span>
                                        @elseif($item->offline_status_key === 'izin')
                                        @php $pVal = $item->offline_record->permit_is_valid !== false && $item->offline_record->permit_is_valid !== 0; @endphp
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold {{ $pVal ? 'bg-blue-100 text-blue-800 border border-blue-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                                            @if($pVal)
                                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Izin Keperluan (Valid)
                                            @else
                                            <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Izin Keperluan (Ditolak)
                                            @endif
                                        </span>
                                        @elseif($item->offline_status_key === 'sakit')
                                        @php
                                        $sTyp = $item->offline_record->sickness_verification_type ?? 'doctor_letter';
                                        @endphp
                                        @if($sTyp === 'fake_sickness')
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                            <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Sakit Berbohong
                                        </span>
                                        @elseif($sTyp === 'verified_by_hr')
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                            <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            Izin Sakit (Dicek HR)
                                        </span>
                                        @else
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Izin Sakit (Surat Dokter)
                                        </span>
                                        @endif
                                        @elseif($item->offline_status_key === 'alpha')
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                            <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Alpha (Tidak Hadir)
                                        </span>
                                        @endif
                                        @else
                                        <span class="text-[11px] text-slate-400 font-medium italic">Belum Diperiksa</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Hasil Verifikasi & Petugas Info -->
                            <div class="flex flex-col sm:items-end justify-center gap-1 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 md:border-l md:border-slate-200/80 md:pl-3">
                                <!-- Verifikasi / Sanksi Badge / Button -->
                                <div>
                                    @if($item->verification_status === 'fraud' || $item->verification_status === 'fake_sickness')
                                        @if(!$isAssistant)
                                        <button type="button"
                                            data-offline-id="{{ $item->offline_record?->id }}"
                                            data-intern-name="{{ $item->user?->name }}"
                                            data-date="{{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }}"
                                            data-online-time="{{ $item->online_time ?? '-' }}"
                                            data-case-type="{{ $item->verification_status }}"
                                            data-penalty-type="{{ $item->offline_record?->penalty_type ?? 'ganti_jam' }}"
                                            data-penalty-minutes="{{ $item->offline_record?->penalty_minutes ?? 435 }}"
                                            data-penalty-notes="{{ $item->offline_record?->penalty_notes ?? '' }}"
                                            onclick="openPenaltyModal(this)"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-600 hover:bg-red-700 text-white shadow-2xs transition cursor-pointer"
                                            title="{{ $item->verification_status === 'fake_sickness' ? 'Sakit terindikasi palsu / tanpa bukti sah. Klik untuk berikan sanksi.' : 'Absen online hadir, fisik tidak ada. Klik untuk berikan sanksi.' }}">
                                            <svg class="w-3 h-3 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                            <span>{{ $item->verification_status === 'fake_sickness' ? 'Sakit Berbohong' : 'Indikasi Bohong' }}</span>
                                        </button>
                                        @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-50 text-red-700 border border-red-200 shadow-2xs"
                                            title="Terindikasi berbohong. Penetapan keputusan sanksi merupakan hak akses Admin.">
                                            <i class="fa-solid fa-lock text-red-600 text-xs"></i>
                                            <span>{{ $item->verification_status === 'fake_sickness' ? 'Sakit Berbohong' : 'Indikasi Bohong' }}</span>
                                            <span class="text-[9px] font-medium text-red-600">(Hak Akses Admin)</span>
                                        </span>
                                        @endif
                                    @elseif($item->verification_status === 'penalty_ganti_jam')
                                        @php
                                        $pMin = (int) ($item->offline_record?->penalty_minutes ?? 435);
                                        $pHr = floor($pMin / 60);
                                        $pMn = $pMin % 60;
                                        $pTimeStr = sprintf('%02d:%02d Jam', $pHr, $pMn);
                                        @endphp
                                        @if(!$isAssistant)
                                        <button type="button"
                                            data-offline-id="{{ $item->offline_record?->id }}"
                                            data-intern-name="{{ $item->user?->name }}"
                                            data-date="{{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }}"
                                            data-online-time="{{ $item->online_time ?? '-' }}"
                                            data-penalty-type="ganti_jam"
                                            data-penalty-minutes="{{ $pMin }}"
                                            data-penalty-notes="{{ $item->offline_record?->penalty_notes ?? '' }}"
                                            onclick="openPenaltyModal(this)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 transition cursor-pointer"
                                            title="Wajib ganti {{ $pTimeStr }}. Klik untuk tinjau/ubah.">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                                            <span>Wajib Ganti {{ $pTimeStr }}</span>
                                        </button>
                                        @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200"
                                            title="Sanksi telah ditetapkan oleh Admin: Wajib Ganti {{ $pTimeStr }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                                            <span>Wajib Ganti {{ $pTimeStr }}</span>
                                        </span>
                                        @endif
                                    @elseif($item->verification_status === 'penalty_alpha')
                                        @if(!$isAssistant)
                                        <button type="button"
                                            data-offline-id="{{ $item->offline_record?->id }}"
                                            data-intern-name="{{ $item->user?->name }}"
                                            data-date="{{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }}"
                                            data-online-time="{{ $item->online_time ?? '-' }}"
                                            data-penalty-type="ganti_jam"
                                            data-penalty-minutes="435"
                                            data-penalty-notes="{{ $item->offline_record?->penalty_notes ?? '' }}"
                                            onclick="openPenaltyModal(this)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 transition cursor-pointer"
                                            title="Sanksi Alpha. Klik untuk tinjau/ubah.">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                            <span>Sanksi Alpha</span>
                                        </button>
                                        @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200"
                                            title="Sanksi telah ditetapkan oleh Admin: Alpha">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                            <span>Sanksi Alpha</span>
                                        </span>
                                        @endif
                                    @elseif($item->verification_status === 'penalty_dimaafkan')
                                        @if(!$isAssistant)
                                        <button type="button"
                                            data-offline-id="{{ $item->offline_record?->id }}"
                                            data-intern-name="{{ $item->user?->name }}"
                                            data-date="{{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }}"
                                            data-online-time="{{ $item->online_time ?? '-' }}"
                                            data-penalty-type="dimaafkan"
                                            data-penalty-minutes="0"
                                            data-penalty-notes="{{ $item->offline_record?->penalty_notes ?? '' }}"
                                            onclick="openPenaltyModal(this)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition cursor-pointer"
                                            title="Dimaafkan. Klik untuk tinjau/ubah.">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                            <span>Dimaafkan</span>
                                        </button>
                                        @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200"
                                            title="Keputusan telah ditetapkan oleh Admin: Dimaafkan">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                            <span>Dimaafkan</span>
                                        </span>
                                        @endif
                                    @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium border {{ $item->verification_badge_class }}"
                                        title="{{ $item->verification_note }}">
                                        @if($item->verification_status === 'pending_approval')
                                        <svg class="w-3 h-3 text-amber-600 animate-spin shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        @elseif($item->verification_status === 'rejected')
                                        <svg class="w-3 h-3 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        @elseif(in_array($item->verification_status, ['verified', 'permit_valid']))
                                        <svg class="w-3 h-3 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        @elseif($item->verification_status === 'sickness_doctor')
                                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        @elseif($item->verification_status === 'sickness_hr')
                                        <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        @elseif(in_array($item->verification_status, ['early', 'early_unverified']))
                                        <svg class="w-3 h-3 text-cyan-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        @elseif(in_array($item->verification_status, ['late_mismatch', 'verified_late', 'forgot_online']))
                                        <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        @elseif(in_array($item->verification_status, ['alpha', 'permit_invalid']))
                                        <svg class="w-3 h-3 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        @endif
                                        <span>{{ $item->verification_label }}</span>
                                    </span>
                                    @endif
                                </div>

                                <!-- Petugas & Catatan Singkat -->
                                @if($item->offline_record)
                                <div class="text-[10px] text-slate-500 flex items-center gap-1 flex-wrap">
                                    <span class="font-medium text-slate-700">Petugas: {{ $item->offline_record->recorded_by }}</span>
                                    @if($item->offline_record->notes)
                                    <span class="text-slate-300">•</span>
                                    <span class="italic text-slate-400 truncate max-w-[140px]" title="{{ $item->offline_record->notes }}">"{{ $item->offline_record->notes }}"</span>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Bagian 3: Tombol Aksi (Kanan) -->
                        <div class="flex items-center justify-end gap-1.5 shrink-0">
                            @if($item->offline_record)
                            <!-- Tombol Konfirmasi Khusus Admin jika status Pending -->
                            @if(!$isAssistant && $item->offline_record->approval_status === 'pending')
                            <button type="button"
                                data-offline-id="{{ $item->offline_record->id }}"
                                data-intern-name="{{ $item->user?->name }}"
                                data-division-name="{{ $item->division?->name ?? 'Tanpa Divisi' }}"
                                data-school-name="{{ $item->school?->name ?? '-' }}"
                                data-date="{{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }}"
                                data-status="{{ $item->offline_record->status }}"
                                data-sickness-type="{{ $item->offline_record->sickness_verification_type ?? 'doctor_letter' }}"
                                data-permit-valid="{{ $item->offline_record->permit_is_valid !== false && $item->offline_record->permit_is_valid !== 0 ? '1' : '0' }}"
                                data-recorded-by="{{ $item->offline_record->recorded_by }}"
                                data-notes="{{ $item->offline_record->notes ?? '' }}"
                                onclick="openApprovalModal(this)"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Konfirmasi</span>
                            </button>
                            @endif

                            <!-- Tombol Edit / Update -->
                            <button type="button" data-intern-id="{{ $item->id }}" onclick="openOfflineModal(this.dataset.internId)"
                                title="Ubah presensi offline"
                                class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 rounded-xl transition shadow-2xs flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>

                            <!-- Tombol Batalkan / Hapus Presensi Offline -->
                            <form action="{{ route($routePrefix . 'destroy', $item->offline_record->id) }}" method="POST"
                                data-intern-name="{{ $item->user?->name }}"
                                onsubmit="return confirmDelete(event, this)">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    title="Batalkan presensi offline (absen online pemagang tetap aman)"
                                    class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 hover:border-rose-200 rounded-xl transition shadow-2xs flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                            @else
                            <!-- Tombol Catat Baru -->
                            <button type="button" data-intern-id="{{ $item->id }}" onclick="openOfflineModal(this.dataset.internId)"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>Catat</span>
                            </button>
                            @endif
                        </div>

                    </div>
                </div>
                @empty
                <div class="py-10 text-center text-slate-400 bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                    <div class="w-12 h-12 mx-auto rounded-full bg-white flex items-center justify-center text-slate-400 mb-2.5 shadow-2xs border border-slate-100">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="font-bold text-slate-700 text-sm">Tidak Ada Data Pemagang</div>
                    <p class="text-xs text-slate-400 mt-0.5">Tidak ditemukan pemagang yang sesuai dengan pencarian atau filter status.</p>
                </div>
                @endforelse
            </div>

            <!-- Footer Pagination (Maksimal 10 Data Per Halaman) -->
            @if($paginatedInterns->hasPages() || $paginatedInterns->total() > 0)
            <div class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-gray-50/50">
                <div class="text-xs text-gray-500 font-medium">
                    Menampilkan <span class="font-bold text-gray-700">{{ $paginatedInterns->firstItem() ?? 0 }}</span> - <span class="font-bold text-gray-700">{{ $paginatedInterns->lastItem() ?? 0 }}</span> dari <span class="font-bold text-gray-700">{{ $paginatedInterns->total() }}</span> pemagang
                    <span class="text-gray-400 ml-1">(Dibatasi 10 per halaman)</span>
                </div>
                <div>
                    {{ $paginatedInterns->links('vendor.pagination.custom-pagination') }}
                </div>
            </div>
            @endif
        </div>

    </div>
</main>

<!-- ========================================================================= -->
<!-- MODAL POPUP FORM PRESENSI OFFLINE                                         -->
<!-- ========================================================================= -->
<div id="offlineModal" class="hidden fixed inset-0 z-[9999] overflow-hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div id="modalCard" class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden border border-gray-100 flex flex-col max-h-[90vh] sm:max-h-[88vh] my-auto animate-scale-up">

        <!-- Modal Header -->
        <div class="px-4 sm:px-6 py-3.5 sm:py-4.5 bg-gray-50 border-b border-gray-100 flex items-center justify-between shrink-0">
            <div class="flex items-center space-x-2.5 sm:space-x-3 min-w-0">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 font-bold text-sm shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h2 class="font-bold text-gray-900 text-sm sm:text-base md:text-lg truncate">Form Presensi Offline</h2>
                    <p class="text-[11px] sm:text-xs text-gray-500 truncate">Pencatatan fisik mandiri</p>
                </div>
            </div>

            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                <span class="inline-flex items-center gap-1 px-2 sm:px-2.5 py-1 bg-gray-200/70 text-gray-700 text-[11px] sm:text-xs font-semibold rounded-lg">
                    <svg class="w-3 sm:w-3.5 h-3 sm:h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>{{ \Carbon\Carbon::parse($date)->isoFormat('D MMM Y') }}</span>
                </span>
                <button type="button" onclick="closeOfflineModal()" class="p-1.5 sm:p-2 text-gray-400 hover:text-gray-600 rounded-xl hover:bg-gray-200/50 transition-colors" title="Tutup form">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal Form Body (Scrollable) -->
        <form action="{{ route($routePrefix . 'store') }}" method="POST" id="offlineForm" data-is-assistant="{{ $isAssistant ? '1' : '0' }}" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 sm:space-y-6">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">

            <!-- ======================================================== -->
            <!-- BAGIAN 1: IDENTIFIKASI PEMAGANG & PENUGASAN             -->
            <!-- ======================================================== -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-blue-600">
                    <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[11px]">1</span>
                    <span>Identifikasi Pemagang & Penugasan</span>
                </div>

                <!-- Searchable Combobox Pilih Pemagang -->
                @php
                $selectedOldIntern = old('intern_id') ? $allInterns->firstWhere('id', old('intern_id')) : null;
                $selectedOldText = $selectedOldIntern
                ? ($selectedOldIntern->user?->name ?? 'Pemagang') . ' - ' . ($selectedOldIntern->division?->name ?? 'Tanpa Divisi') . ' (' . ($selectedOldIntern->school?->name ?? '-') . ')'
                : '';
                @endphp
                <div>
                    <label for="internSearchInput" class="block text-sm font-bold text-gray-700 mb-1.5">
                        Nama Pemagang <span class="text-red-500">*</span>
                    </label>

                    <div id="internComboboxContainer" class="relative">
                        <input type="hidden" name="intern_id" id="intern_id" value="{{ old('intern_id') }}">

                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text"
                                id="internSearchInput"
                                autocomplete="off"
                                value="{{ $selectedOldText }}"
                                placeholder="Ketik nama pemagang, divisi, asal sekolah..."
                                class="w-full rounded-xl border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 py-2.5 pl-11 pr-20 text-sm font-medium text-gray-900 bg-white placeholder-gray-400 transition-all">

                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center gap-1">
                                <button type="button" id="clearInternBtn" class="{{ $selectedOldIntern ? '' : 'hidden' }} p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors" title="Hapus pilihan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                <button type="button" id="toggleDropdownBtn" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors" title="Buka daftar pemagang">
                                    <svg class="w-4 h-4 transition-transform duration-200" id="dropdownChevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Dropdown Menu / Suggestions List -->
                        <div id="internDropdownList"
                            class="hidden absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-2xl shadow-xl border border-gray-200 max-h-64 overflow-y-auto divide-y divide-gray-100">

                            <div class="p-2.5 bg-gray-50/90 border-b border-gray-100 text-[11px] font-semibold text-gray-500 flex items-center justify-between sticky top-0 z-10 backdrop-blur-xs">
                                <span>Pilih pemagang dari daftar ({{ count($allInterns) }} pemagang):</span>
                                <span class="text-blue-600 font-normal">Gunakan ↑ / ↓ & Enter</span>
                            </div>

                            <div id="optionsContainer">
                                @foreach($allInterns as $item)
                                @php
                                $onlineBadgeText = 'Belum Absen';
                                $onlineBadgeClass = 'bg-gray-100 text-gray-500 border border-gray-200';
                                if (!empty($item->online_time)) {
                                if ($item->online_status_key === 'terlambat') {
                                $onlineBadgeText = 'Online ' . $item->online_time . ' (' . ($item->online_late_minutes ?? 0) . 'm)';
                                $onlineBadgeClass = 'bg-amber-50 text-amber-700 border border-amber-200 font-bold';
                                } else {
                                $onlineBadgeText = 'Online ' . $item->online_time;
                                $onlineBadgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold';
                                }
                                } elseif ($item->online_status_key === 'izin_sakit') {
                                $onlineBadgeText = 'Izin Sakit Online';
                                $onlineBadgeClass = 'bg-amber-50 text-amber-700 border border-amber-200 font-semibold';
                                } elseif ($item->online_status_key === 'izin_keperluan' || $item->online_status_key === 'izin') {
                                $onlineBadgeText = 'Izin Keperluan Online';
                                $onlineBadgeClass = 'bg-blue-50 text-blue-700 border border-blue-200 font-semibold';
                                } elseif ($item->online_status_key === 'alpha') {
                                $onlineBadgeText = 'Alpha Online';
                                $onlineBadgeClass = 'bg-red-50 text-red-700 border border-red-200 font-semibold';
                                }
                                @endphp
                                <div class="intern-option px-4 py-2.5 hover:bg-blue-50/80 cursor-pointer transition-colors flex items-center justify-between gap-3 border-b border-gray-100 last:border-b-0"
                                    data-id="{{ $item->id }}"
                                    data-name="{{ $item->user?->name ?? 'Pemagang' }}"
                                    data-online-time="{{ $item->online_time ? $item->online_time . ' WIB' : '' }}"
                                    data-online-status="{{ $item->online_status_label ?? 'Belum Absen' }}"
                                    data-display="{{ $item->user?->name ?? 'Pemagang' }} - {{ $item->division?->name ?? 'Tanpa Divisi' }} ({{ $item->school?->name ?? '-' }})"
                                    data-search="{{ strtolower(($item->user?->name ?? '') . ' ' . ($item->user?->username ?? '') . ' ' . ($item->division?->name ?? '') . ' ' . ($item->school?->name ?? '')) }}"
                                    data-shift-id="{{ $item->assigned_shift_id ?? $item->assigned_shift?->id ?? '' }}"
                                    data-office-id="{{ $item->assigned_office_id ?? $item->assigned_office?->id ?? '' }}">

                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <div class="w-8 h-8 rounded-xl bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                            {{ strtoupper(substr($item->user?->name ?? 'P', 0, 2)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-bold text-sm text-gray-900 truncate">
                                                    {{ $item->user?->name ?? 'Pemagang' }}
                                                </span>
                                                <!-- Badge Jam Absen Online -->
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] {{ $onlineBadgeClass }}">
                                                    @if(!empty($item->online_time))
                                                    <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    @else
                                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                    @endif
                                                    <span>{{ $onlineBadgeText }}</span>
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-500 truncate flex items-center gap-1.5 mt-0.5">
                                                <span class="font-medium text-gray-700">{{ $item->division?->name ?? 'Tanpa Divisi' }}</span>
                                                <span class="text-gray-300">•</span>
                                                <span>{{ $item->school?->name ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="shrink-0 flex items-center gap-2">
                                        @if($item->offline_record)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            Sudah Dicatat
                                        </span>
                                        @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-500">
                                            Belum Dicatat
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div id="noMatchMessage" class="hidden p-6 text-center text-gray-400">
                                <svg class="w-7 h-7 mx-auto text-gray-300 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="text-sm font-semibold text-gray-700">Tidak ada pemagang yang cocok</div>
                                <p class="text-xs text-gray-400 mt-0.5">Coba ketik kata kunci lainnya.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Status Alert (Muncul Dinamis Saat Pemagang Dipilih) -->
                <div id="statusContainer" class="hidden rounded-2xl border p-4 transition-all duration-300 shadow-xs">
                    <div class="flex items-start gap-3.5">
                        <div id="statusIcon" class="mt-0.5 shrink-0"></div>
                        <div class="space-y-1.5 flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <h4 id="statusTitle" class="font-bold text-sm leading-tight"></h4>
                                <div id="statusOnlineBadge" class="hidden"></div>
                            </div>
                            <p id="statusMessage" class="text-xs leading-relaxed"></p>
                        </div>
                    </div>
                </div>

                <!-- 2 Kolom: Shift Kerja & Lokasi Kantor -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                    <!-- Pilihan Shift -->
                    <div>
                        <label for="shift_id" class="block text-sm font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Shift Kerja</span> <span class="text-red-500">*</span>
                        </label>
                        <select name="shift_id" id="shift_id" required
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2.5 px-3 text-sm text-gray-800 bg-white">
                            @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilihan Kantor -->
                    <div>
                        <label for="office_id" class="block text-sm font-bold text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>Kantor / Brand</span> <span class="text-red-500">*</span>
                        </label>
                        <select name="office_id" id="office_id" required
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2.5 px-3 text-sm text-gray-800 bg-white">
                            @foreach($offices as $office)
                            <option value="{{ $office->id }}" {{ old('office_id') == $office->id ? 'selected' : '' }}>
                                {{ $office->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <hr class="border-gray-200">

            <!-- ======================================================== -->
            <!-- ======================================================== -->
            <!-- BAGIAN 2: WAKTU MASUK KANTOR (KEDATANGAN FISIK)          -->
            <!-- ======================================================== -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-blue-600">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[11px]">2</span>
                        <span>Waktu Masuk Kantor (Kedatangan Fisik)</span>
                    </div>
                    <span class="text-xs text-gray-400">Jam aktual tiba di kantor</span>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="relative w-full sm:w-48">
                        <input type="time" name="physical_checkin_time" id="physical_checkin_time"
                            value="{{ old('physical_checkin_time') }}"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 text-sm font-bold text-gray-900 bg-white">
                    </div>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <button type="button" onclick="setCurrentPhysicalTime()" class="px-2.5 py-1 text-xs font-semibold bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 text-gray-700 transition-colors flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Jam Sekarang</span>
                        </button>
                        <button type="button" id="btnShiftStart" onclick="setShiftStartTime()" class="px-2.5 py-1 text-xs font-semibold bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 text-gray-700 transition-colors">
                            <span>Jam Mulai Shift</span>
                        </button>
                    </div>

                    <!-- Indikator Otomatis Selisih Waktu Keterlambatan -->
                    <div id="autoLateIndicator" class="hidden text-xs font-semibold px-2.5 py-1 rounded-lg"></div>
                </div>
                <p class="text-[11px] text-gray-400 italic">
                    * Keterlambatan dihitung otomatis oleh sistem dari selisih Waktu Masuk Fisik dan Jam Mulai Shift.
                </p>
            </div>

            <hr class="border-gray-200">

            <!-- ======================================================== -->
            <!-- BAGIAN 3: STATUS KEHADIRAN FISIK & VERIFIKASI            -->
            <!-- ======================================================== -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-blue-600">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[11px]">3</span>
                        <span>Verifikasi Status Kehadiran Fisik</span>
                    </div>
                    <span class="text-xs text-gray-400">Pilih kondisi riil di kantor</span>
                </div>

                <!-- 6 Interactive Radio Cards in 2 rows / 3 columns grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <!-- Tepat Waktu Card -->
                    <label class="status-card cursor-pointer block border-2 border-gray-200 rounded-2xl p-3.5 hover:border-emerald-500 hover:bg-emerald-50/30 transition-all text-left group relative overflow-hidden">
                        <input type="radio" name="status" value="hadir" class="sr-only" required
                            {{ old('status', 'hadir') === 'hadir' || old('status') === 'tepat_waktu' ? 'checked' : '' }}>
                        <div class="flex items-start justify-between mb-1">
                            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Hadir</span>
                        </div>
                        <div class="font-bold text-xs sm:text-sm text-gray-900 group-hover:text-emerald-700">Tepat Waktu</div>
                        <div class="text-[10px] text-gray-500 mt-0.5 leading-tight">Hadir fisik sesuai jam.</div>
                    </label>

                    <!-- Early Check-in Card -->
                    <label class="status-card cursor-pointer block border-2 border-gray-200 rounded-2xl p-3.5 hover:border-cyan-500 hover:bg-cyan-50/30 transition-all text-left group relative overflow-hidden">
                        <input type="radio" name="status" value="early" class="sr-only"
                            {{ old('status') === 'early' ? 'checked' : '' }}>
                        <div class="flex items-start justify-between mb-1">
                            <div class="w-8 h-8 rounded-xl bg-cyan-100 text-cyan-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-cyan-100 text-cyan-800">Early</span>
                        </div>
                        <div class="font-bold text-xs sm:text-sm text-gray-900 group-hover:text-cyan-700">Lebih Awal</div>
                        <div class="text-[10px] text-gray-500 mt-0.5 leading-tight">Tiba sebelum jam shift.</div>
                    </label>

                    <!-- Terlambat Card -->
                    <label class="status-card cursor-pointer block border-2 border-gray-200 rounded-2xl p-3.5 hover:border-amber-500 hover:bg-amber-50/30 transition-all text-left group relative overflow-hidden">
                        <input type="radio" name="status" value="terlambat" class="sr-only"
                            {{ old('status') === 'terlambat' ? 'checked' : '' }}>
                        <div class="flex items-start justify-between mb-1">
                            <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-800">Telat</span>
                        </div>
                        <div class="font-bold text-xs sm:text-sm text-gray-900 group-hover:text-amber-700">Terlambat Fisik</div>
                        <div class="text-[10px] text-gray-500 mt-0.5 leading-tight">Dihitung dari jam masuk.</div>
                    </label>

                    <!-- Izin Card -->
                    <label class="status-card cursor-pointer block border-2 border-gray-200 rounded-2xl p-3.5 hover:border-blue-500 hover:bg-blue-50/30 transition-all text-left group relative overflow-hidden">
                        <input type="radio" name="status" value="izin" class="sr-only"
                            {{ old('status') === 'izin' ? 'checked' : '' }}>
                        <div class="flex items-start justify-between mb-1">
                            <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-800">Izin Keperluan</span>
                                @if($isAssistant)
                                <span class="text-[8px] font-extrabold px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300">Perlu Konfirmasi</span>
                                @endif
                            </div>
                        </div>
                        <div class="font-bold text-xs sm:text-sm text-gray-900 group-hover:text-blue-700">Izin Valid atau Tidak</div>
                        <div class="text-[10px] text-gray-500 mt-0.5 leading-tight">Verifikasi izin keperluan.</div>
                    </label>

                    <!-- Sakit Card -->
                    <label class="status-card cursor-pointer block border-2 border-gray-200 rounded-2xl p-3.5 hover:border-amber-500 hover:bg-amber-50/30 transition-all text-left group relative overflow-hidden">
                        <input type="radio" name="status" value="sakit" class="sr-only"
                            {{ old('status') === 'sakit' ? 'checked' : '' }}>
                        <div class="flex items-start justify-between mb-1">
                            <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-800">Izin Sakit</span>
                                @if($isAssistant)
                                <span class="text-[8px] font-extrabold px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300">Perlu Konfirmasi</span>
                                @endif
                            </div>
                        </div>
                        <div class="font-bold text-xs sm:text-sm text-gray-900 group-hover:text-amber-700">Izin Sakit</div>
                        <div class="text-[10px] text-gray-500 mt-0.5 leading-tight">Cek HR / Surat dokter.</div>
                    </label>

                    <!-- Alpha Card -->
                    <label class="status-card cursor-pointer block border-2 border-gray-200 rounded-2xl p-3.5 hover:border-red-500 hover:bg-red-50/30 transition-all text-left group relative overflow-hidden">
                        <input type="radio" name="status" value="alpha" class="sr-only"
                            {{ old('status') === 'alpha' ? 'checked' : '' }}>
                        <div class="flex items-start justify-between mb-1">
                            <div class="w-8 h-8 rounded-xl bg-red-100 text-red-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                            </div>
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-red-100 text-red-800">Alpha</span>
                        </div>
                        <div class="font-bold text-xs sm:text-sm text-gray-900 group-hover:text-red-700">Alpha (Fraud)</div>
                        <div class="text-[10px] text-red-600 font-medium mt-0.5 leading-tight">Fisik tidak hadir.</div>
                    </label>
                </div>

                @if(!$isAssistant)
                <!-- Sub-Opsi Khusus Izin Keperluan (Muncul Saat Izin Dipilih) -->
                <div id="izinContainer" class="hidden p-4 bg-blue-50/90 border border-blue-200 rounded-2xl space-y-2.5 transition-all duration-300">
                    <span class="block text-xs font-bold text-blue-900 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Verifikasi Izin Valid atau Tidak:</span>
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        <label class="inline-flex items-center gap-2 p-2.5 bg-white rounded-xl border border-blue-200 cursor-pointer hover:border-blue-400 transition-colors">
                            <input type="radio" name="permit_is_valid" value="1" class="text-blue-600 focus:ring-blue-500" checked>
                            <span class="inline-flex items-center gap-1.5 font-semibold text-blue-950">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Izin Valid / Sah (Diterima)</span>
                            </span>
                        </label>
                        <label class="inline-flex items-center gap-2 p-2.5 bg-white rounded-xl border border-rose-200 cursor-pointer hover:border-rose-400 transition-colors">
                            <input type="radio" name="permit_is_valid" value="0" class="text-rose-600 focus:ring-rose-500">
                            <span class="inline-flex items-center gap-1.5 font-semibold text-rose-700">
                                <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                                <span>Izin Tidak Valid (Ditolak / Alpha)</span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Sub-Opsi Khusus Izin Sakit (Muncul Saat Sakit Dipilih) -->
                <div id="sakitContainer" class="hidden p-4 bg-amber-50/90 border border-amber-200 rounded-2xl space-y-2.5 transition-all duration-300">
                    <label for="sickness_verification_type" class="block text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span>Kategori Verifikasi Izin Sakit:</span>
                    </label>
                    <select name="sickness_verification_type" id="sickness_verification_type"
                        class="w-full rounded-xl border-amber-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-xs font-semibold text-gray-800 bg-white py-2 px-3">
                        <option value="verified_by_hr">1. Sakit Sudah Dicek HR (Izin Sakit - Tanpa Ganti Jam)</option>
                        <option value="doctor_letter">2. Sakit Ada Surat Izin / Surat Dokter (Validasi Offline)</option>
                        <option value="fake_sickness">3. Sakit Berbohong / Fraud Tanpa Bukti (Sanksi Alpha)</option>
                    </select>
                    <p class="text-[11px] text-amber-800/80 italic mt-1">
                        * Sakit yang sudah dicek HR masuk ke kategori Izin Sakit resmi tanpa ganti jam. Opsi surat izin/dokter untuk validasi fisik offline data perizinan online.
                    </p>
                </div>
                @endif
            </div>

            <hr class="border-gray-200">

            <!-- ======================================================== -->
            <!-- BAGIAN 4: CATATAN PENGAWAS                               -->
            <!-- ======================================================== -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-blue-600">
                    <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[11px]">4</span>
                    <span>Catatan Pengawas (Opsional)</span>
                </div>

                <input type="text" name="notes" id="notes" value="{{ old('notes') }}"
                    placeholder="Contoh: Terlihat di ruangan 2, izin ke klinik pagi hari, indikasi absen online palsu, dsb."
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2.5 px-3 text-sm text-gray-800 bg-white">
            </div>
        </form>

        <!-- Modal Footer Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-2 text-xs text-gray-600">
                <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <span class="font-semibold text-gray-800">Petugas:</span>
                    <span class="text-gray-500">{{ $user->name }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                <button type="button" onclick="closeOfflineModal()"
                    class="w-1/2 sm:w-auto px-4 py-2.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-100 font-semibold text-xs rounded-xl transition-colors">
                    Batal
                </button>
                <button type="button" onclick="submitOfflineForm()" id="submitBtn"
                    class="w-1/2 sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Simpan Presensi</span>
                </button>
            </div>
        </div>

    </div>
</div>

@if(!$isAssistant)
<!-- ========================================================================= -->
<!-- MODAL TINDAK LANJUT INDIKASI BERBOHONG / PENETAPAN SANKSI (HANYA ADMIN)   -->
<!-- ========================================================================= -->
<div id="penaltyModal" class="hidden fixed inset-0 z-[9999] overflow-hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div id="penaltyModalCard" class="bg-white rounded-3xl shadow-2xl max-w-xl w-full overflow-hidden border border-gray-100 flex flex-col max-h-[90vh] sm:max-h-[88vh] my-auto animate-scale-up">

        <!-- Modal Header -->
        <div class="px-6 py-4.5 bg-gradient-to-r from-red-600 to-rose-700 text-white flex items-center justify-between shrink-0">
            <div class="flex items-center space-x-3 p-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-xs flex items-center justify-center font-bold shadow-md shadow-red-800/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-base md:text-lg text-white">Tindak Lanjut Indikasi Berbohong</h2>
                    <p class="text-xs text-red-100">Tetapkan keputusan sanksi setelah konfirmasi di luar sistem</p>
                </div>
            </div>

            <button type="button" onclick="closePenaltyModal()" class="p-2 text-white/80 hover:text-white rounded-xl hover:bg-white/10 transition-colors" title="Tutup modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Form Tindak Lanjut Sanksi -->
        <form id="penaltyForm" method="POST" action="" data-route-template="{{ route('admin.absen-offline.penalty', ':id') }}" class="flex-1 overflow-y-auto p-6 space-y-5">
            @csrf

            <!-- Card Informasi Kasus & Peringatan Konfirmasi Manual -->
            <div class="p-4 bg-red-50/80 rounded-2xl border border-red-200 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-red-700 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                        <span id="penaltyModalCaseTitle">Kasus Indikasi Berbohong</span>
                    </span>
                    <span id="penaltyModalDate" class="text-xs text-gray-500 font-semibold"></span>
                </div>

                <div class="font-extrabold text-base text-gray-900" id="penaltyModalInternName">
                    Nama Pemagang
                </div>

                <div class="text-xs text-red-800 space-y-1 pt-1.5 border-t border-red-200/60">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Sistem Online: Tercatat Masuk pukul <strong id="penaltyModalOnlineTime">08:00</strong> WIB</span>
                    </div>
                    <div class="flex items-center gap-2" id="penaltyModalCaseDesc">
                        <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span>Pemeriksaan Fisik Offline: <strong class="text-red-700">TIDAK ADA / ALPHA DI KANTOR</strong></span>
                    </div>
                </div>

                <!-- Box Pengingat Konfirmasi Luar Sistem -->
                <div class="text-xs text-amber-900 bg-amber-50/90 p-3 rounded-xl border border-amber-200 flex items-start gap-2 mt-2">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="leading-relaxed">
                        <strong>Perhatian:</strong> Pastikan Anda telah mengonfirmasi pemagang secara langsung di luar sistem (telepon / pesan pribadi) terlebih dahulu mengenai alasan ketidakhadirannya sebelum memutuskan tindakan di bawah ini.
                    </div>
                </div>
            </div>

            <!-- Pilihan Tindakan / Sanksi (2 Pilihan Keputusan) -->
            <div class="space-y-3">
                <span class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                    Pilihan Keputusan / Tindakan <span class="text-red-500">*</span>
                </span>

                <div class="grid grid-cols-1 gap-3">
                    <!-- Opsi 1: Wajib Ganti Full 1 Shift (Dimasukkan ke Kondisi Alpha) -->
                    <label class="penalty-option-card block border-2 border-purple-500 bg-purple-50/60 rounded-2xl p-4 cursor-pointer transition-all hover:bg-purple-50 shadow-xs ring-2 ring-purple-500/20">
                        <div class="flex items-start gap-3">
                            <input type="radio" name="penalty_type" value="ganti_jam" class="mt-1 text-purple-600 focus:ring-purple-500 shrink-0" checked onchange="handlePenaltyTypeChange()">
                            <input type="hidden" name="penalty_minutes" id="penalty_minutes" value="435">
                            <div class="flex-1 space-y-1.5 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 sm:gap-2">
                                    <div class="font-bold text-sm text-purple-950 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                                        </svg>
                                        <span>Wajib Ganti Full (Alpha)</span>
                                    </div>
                                    <span class="text-[11px] bg-purple-200 text-purple-900 font-extrabold px-2.5 py-0.5 rounded-full shrink-0 self-start sm:self-auto">1 Shift Penuh (07:15 Jam)</span>
                                </div>
                                <p class="text-xs text-purple-800 leading-relaxed">
                                    Presensi online pemagang <strong>secara otomatis dimasukkan ke kondisi Alpha</strong> di sistem dan dibebankan <strong>ganti jam 1 shift kerja penuh (07:15 jam) tanpa toleransi</strong> yang wajib dilunasi melalui sistem Ganti Jam. Sesi absensi hari ini langsung ditutup.
                                </p>
                            </div>
                        </div>
                    </label>

                    <!-- Opsi 2: Klarifikasi Sah (Dimaafkan) -->
                    <label class="penalty-option-card block border-2 border-gray-200 hover:border-blue-400 rounded-2xl p-4 cursor-pointer transition-all hover:bg-blue-50/40">
                        <div class="flex items-start gap-3">
                            <input type="radio" name="penalty_type" value="dimaafkan" class="mt-1 text-blue-600 focus:ring-blue-500 shrink-0" onchange="handlePenaltyTypeChange()">
                            <div class="flex-1 space-y-1.5 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 sm:gap-2">
                                    <div class="font-bold text-sm text-blue-950 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>Klarifikasi Sah (Dimaafkan)</span>
                                    </div>
                                    <span class="text-[11px] bg-blue-100 text-blue-800 font-extrabold px-2.5 py-0.5 rounded-full shrink-0 self-start sm:self-auto">Hadir Sah</span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed">
                                    Setelah dikonfirmasi di luar sistem, pemagang terbukti memiliki tugas dinas luar atau alasan sah yang disetujui. Sesi presensi dikembalikan ke jam login awal, jam pulang dikosongkan agar sistem kembali normal, dan status disahkan menjadi <strong>Hadir Tepat Waktu</strong>.
                                </p>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Catatan Hasil Konfirmasi -->
            <div>
                <label for="penalty_notes" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Catatan Hasil Konfirmasi (Di Luar Sistem)
                </label>
                <textarea name="penalty_notes" id="penalty_notes" rows="3"
                    placeholder="Contoh: Dikonfirmasi via telepon jam 09.30, pemagang menitipkan akun presensi online ke temannya karena terlambat bangun tidur..."
                    class="w-full rounded-xl border border-gray-300 p-3 text-xs text-gray-800 focus:ring-2 focus:ring-red-500 focus:border-red-500 shadow-xs bg-white"></textarea>
            </div>

            <!-- Modal Footer Actions -->
            <div class="pt-3 flex items-center justify-end gap-3 border-t border-gray-100">
                <button type="button" onclick="closePenaltyModal()"
                    class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-100 transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 active:bg-red-800 text-white text-xs font-bold shadow-md shadow-red-500/20 hover:shadow-lg transition-all">
                    Simpan Keputusan Sanksi
                </button>
            </div>
        </form>

    </div>
</div>
@endif

<!-- ========================================================================= -->
<!-- MODAL KONFIRMASI / PERSETUJUAN PERMOHONAN SAKIT & IZIN DARI ASISTEN       -->
<!-- ========================================================================= -->
<div id="approvalModal" class="hidden fixed inset-0 z-[9999] overflow-hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div id="approvalModalCard" class="bg-white rounded-3xl shadow-2xl max-w-xl w-full overflow-hidden border border-gray-100 flex flex-col max-h-[90vh] sm:max-h-[88vh] my-auto animate-scale-up">

        <!-- Modal Header -->
        <div class="px-6 py-4.5 bg-gradient-to-r from-amber-500 to-amber-600 text-white flex items-center justify-between shrink-0">
            <div class="flex items-center space-x-3 p-2">
                <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-xs flex items-center justify-center font-bold shadow-md shadow-amber-700/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-base md:text-lg text-white">Konfirmasi Pengajuan Presensi</h2>
                    <p class="text-xs text-amber-100">Verifikasi & persetujuan izin/sakit dari Asisten Admin</p>
                </div>
            </div>

            <button type="button" onclick="closeApprovalModal()" class="p-2 text-white/80 hover:text-white rounded-xl hover:bg-white/10 transition-colors" title="Tutup modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Form Konfirmasi -->
        <form id="approvalForm" method="POST" action="" data-route-template="{{ route('admin.absen-offline.confirm-permit', ':id') }}" class="flex-1 overflow-y-auto p-6 space-y-5">
            @csrf
            <input type="hidden" name="action" id="approvalAction" value="approve">

            <!-- Info Card Pengajuan -->
            <div class="p-4 bg-amber-50/80 rounded-2xl border border-amber-200 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-800 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                        <span id="approvalModalStatusTitle">Pengajuan Izin Sakit</span>
                    </span>
                    <span id="approvalModalDate" class="text-xs text-gray-500 font-semibold"></span>
                </div>

                <div>
                    <div class="font-extrabold text-base text-gray-900" id="approvalModalInternName">Nama Pemagang</div>
                    <div class="text-xs text-gray-500 mt-0.5" id="approvalModalInternMeta">Divisi • Asal Sekolah</div>
                </div>

                <div class="text-xs text-amber-900 space-y-1 pt-2 border-t border-amber-200/60">
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500">Diajukan oleh:</span>
                        <strong id="approvalModalRecordedBy" class="text-amber-950">-</strong>
                    </div>
                    <div class="flex items-start gap-2" id="approvalModalNotesContainer">
                        <span class="text-gray-500 shrink-0">Catatan Asisten:</span>
                        <span id="approvalModalNotes" class="italic text-gray-700">-</span>
                    </div>
                </div>
            </div>

            <!-- Bagian Pengaturan Khusus Sakit -->
            <div id="approvalSakitSection" class="space-y-2.5">
                <label for="approvalSicknessType" class="block text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span>Kategori Verifikasi Izin Sakit:</span>
                </label>
                <select name="sickness_verification_type" id="approvalSicknessType"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-xs font-semibold text-gray-800 bg-white py-2.5 px-3">
                    <option value="verified_by_hr">1. Sakit Sudah Dicek HR (Izin Sakit - Tanpa Ganti Jam)</option>
                    <option value="doctor_letter">2. Sakit Ada Surat Izin / Surat Dokter (Validasi Offline)</option>
                    <option value="fake_sickness">3. Sakit Berbohong / Fraud Tanpa Bukti (Sanksi Alpha)</option>
                </select>
            </div>

            <!-- Bagian Pengaturan Khusus Izin Keperluan -->
            <div id="approvalIzinSection" class="space-y-2.5">
                <span class="block text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Verifikasi Keabsahan Izin:</span>
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                    <label class="inline-flex items-center gap-2 p-2.5 bg-white rounded-xl border border-gray-200 cursor-pointer hover:border-blue-400 transition-colors">
                        <input type="radio" name="permit_is_valid" id="approvalPermitValid1" value="1" class="text-blue-600 focus:ring-blue-500" checked>
                        <span class="inline-flex items-center gap-1.5 font-semibold text-slate-800">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Izin Valid / Sah (Diterima)</span>
                        </span>
                    </label>
                    <label class="inline-flex items-center gap-2 p-2.5 bg-white rounded-xl border border-gray-200 cursor-pointer hover:border-rose-400 transition-colors">
                        <input type="radio" name="permit_is_valid" id="approvalPermitValid0" value="0" class="text-rose-600 focus:ring-rose-500">
                        <span class="inline-flex items-center gap-1.5 font-semibold text-rose-700">
                            <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                            <span>Izin Tidak Valid (Ditolak)</span>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Catatan Admin (Opsional / Alasan Penolakan) -->
            <div id="rejectionNoteContainer" class="space-y-1.5">
                <label for="approvalRejectionNote" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                    Catatan / Alasan Penolakan (Wajib jika ditolak)
                </label>
                <textarea name="rejection_note" id="approvalRejectionNote" rows="2"
                    placeholder="Masukkan alasan penolakan jika menolak pengajuan..."
                    class="w-full rounded-xl border border-gray-300 p-3 text-xs text-gray-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 shadow-xs bg-white"></textarea>
            </div>

            <!-- Modal Footer Actions -->
            <div class="pt-3 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-100">
                <button type="button" onclick="closeApprovalModal()"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-100 transition-colors">
                    Tutup
                </button>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="button" onclick="submitApproval('reject')"
                        class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-xs font-bold shadow-xs transition-all flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span>Tolak</span>
                    </button>
                    <button type="button" onclick="submitApproval('approve')"
                        class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold shadow-md shadow-emerald-500/20 hover:shadow-lg transition-all flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Setujui</span>
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

<script>
    // ==========================================
    // MODAL TINDAK LANJUT INDIKASI BERBOHONG
    // ==========================================
    function openPenaltyModal(btn) {
        const offlineId = btn.dataset.offlineId;
        const internName = btn.dataset.internName || 'Pemagang';
        const date = btn.dataset.date || '';
        const onlineTime = btn.dataset.onlineTime || '-';
        const caseType = btn.dataset.caseType || 'fraud';
        let penaltyType = btn.dataset.penaltyType || 'ganti_jam';
        if (penaltyType !== 'ganti_jam' && penaltyType !== 'dimaafkan') {
            penaltyType = 'ganti_jam';
        }
        const penaltyNotes = btn.dataset.penaltyNotes || '';

        const modal = document.getElementById('penaltyModal');
        const form = document.getElementById('penaltyForm');
        if (!modal || !form) return;

        const template = form.dataset.routeTemplate;
        form.action = template.replace(':id', offlineId);

        document.getElementById('penaltyModalInternName').textContent = internName;
        document.getElementById('penaltyModalDate').textContent = date;
        document.getElementById('penaltyModalOnlineTime').textContent = onlineTime;
        document.getElementById('penalty_notes').value = penaltyNotes;

        const caseTitleElem = document.getElementById('penaltyModalCaseTitle');
        const caseDescElem = document.getElementById('penaltyModalCaseDesc');
        if (caseTitleElem) {
            caseTitleElem.textContent = caseType === 'fake_sickness' ? 'Kasus Sakit Berbohong' : 'Kasus Indikasi Berbohong';
        }
        if (caseDescElem) {
            if (caseType === 'fake_sickness') {
                caseDescElem.innerHTML = `
                    <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span>Pemeriksaan Fisik Offline: <strong class="text-red-700">SAKIT BERBOHONG / FIKTIF (ALPHA)</strong></span>
                `;
            } else {
                caseDescElem.innerHTML = `
                    <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span>Pemeriksaan Fisik Offline: <strong class="text-red-700">TIDAK ADA / ALPHA DI KANTOR</strong></span>
                `;
            }
        }

        const radio = document.querySelector(`input[name="penalty_type"][value="${penaltyType}"]`);
        if (radio) {
            radio.checked = true;
        }

        handlePenaltyTypeChange();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        document.documentElement.classList.add('overflow-hidden');
    }

    function closePenaltyModal() {
        const modal = document.getElementById('penaltyModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        document.documentElement.classList.remove('overflow-hidden');
    }

    // ==========================================
    // MODAL KONFIRMASI IZIN / SAKIT (ADMIN)
    // ==========================================
    function openApprovalModal(btn) {
        const offlineId = btn.dataset.offlineId;
        const internName = btn.dataset.internName || 'Pemagang';
        const divisionName = btn.dataset.divisionName || '';
        const schoolName = btn.dataset.schoolName || '';
        const date = btn.dataset.date || '';
        const status = btn.dataset.status || 'sakit';
        const sicknessType = btn.dataset.sicknessType || 'doctor_letter';
        const permitValid = btn.dataset.permitValid === '1';
        const recordedBy = btn.dataset.recordedBy || 'Asisten Admin';
        const notes = btn.dataset.notes || '';

        const modal = document.getElementById('approvalModal');
        const form = document.getElementById('approvalForm');
        if (!modal || !form) return;

        const template = form.dataset.routeTemplate;
        form.action = template.replace(':id', offlineId);

        document.getElementById('approvalModalInternName').textContent = internName;
        document.getElementById('approvalModalInternMeta').textContent = `${divisionName} • ${schoolName}`;
        document.getElementById('approvalModalDate').textContent = date;
        document.getElementById('approvalModalRecordedBy').textContent = recordedBy;
        document.getElementById('approvalModalNotes').textContent = notes ? `"${notes}"` : 'Tidak ada catatan';

        const statusTitleElem = document.getElementById('approvalModalStatusTitle');
        const sakitSection = document.getElementById('approvalSakitSection');
        const izinSection = document.getElementById('approvalIzinSection');

        if (status === 'sakit') {
            if (statusTitleElem) statusTitleElem.textContent = 'Pengajuan Izin Sakit';
            if (sakitSection) sakitSection.classList.remove('hidden');
            if (izinSection) izinSection.classList.add('hidden');
            const sickSelect = document.getElementById('approvalSicknessType');
            if (sickSelect) sickSelect.value = sicknessType;
        } else {
            if (statusTitleElem) statusTitleElem.textContent = 'Pengajuan Izin Keperluan';
            if (sakitSection) sakitSection.classList.add('hidden');
            if (izinSection) izinSection.classList.remove('hidden');
            const radio = document.querySelector(`input[name="permit_is_valid"][id="approvalPermitValid${permitValid ? '1' : '0'}"]`);
            if (radio) radio.checked = true;
        }

        document.getElementById('approvalRejectionNote').value = '';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        document.documentElement.classList.add('overflow-hidden');
    }

    function closeApprovalModal() {
        const modal = document.getElementById('approvalModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        document.documentElement.classList.remove('overflow-hidden');
    }

    function submitApproval(action) {
        const form = document.getElementById('approvalForm');
        const actionInput = document.getElementById('approvalAction');
        const rejectionNote = document.getElementById('approvalRejectionNote');
        if (!form || !actionInput) return;

        actionInput.value = action;

        if (action === 'reject') {
            if (rejectionNote && !rejectionNote.value.trim()) {
                rejectionNote.focus();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Alasan Penolakan Wajib Diisi',
                        text: 'Harap berikan alasan penolakan agar asisten dan pemagang mengetahui alasannya.',
                        confirmButtonColor: '#e11d48'
                    });
                } else {
                    alert('Harap berikan alasan penolakan terlebih dahulu.');
                }
                return;
            }
        }

        form.submit();
    }

    function handlePenaltyTypeChange() {
        const cards = document.querySelectorAll('.penalty-option-card');

        cards.forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                if (radio.value === 'ganti_jam') {
                    card.className = 'penalty-option-card block border-2 border-purple-500 bg-purple-50/60 rounded-2xl p-4 cursor-pointer transition-all shadow-xs ring-2 ring-purple-500/20';
                } else if (radio.value === 'dimaafkan') {
                    card.className = 'penalty-option-card block border-2 border-blue-500 bg-blue-50/60 rounded-2xl p-4 cursor-pointer transition-all shadow-xs ring-2 ring-blue-500/20';
                }
            } else {
                card.className = 'penalty-option-card block border-2 border-gray-200 rounded-2xl p-4 cursor-pointer transition-all hover:bg-gray-50';
            }
        });
    }

    function calculateAutoLate() {
        const shiftSelect = document.getElementById('shift_id');
        const timeInput = document.getElementById('physical_checkin_time');
        const indicator = document.getElementById('autoLateIndicator');
        if (!shiftSelect || !timeInput || !timeInput.value || !indicator) return;

        const selectedOpt = shiftSelect.options[shiftSelect.selectedIndex];
        if (!selectedOpt) return;

        const match = selectedOpt.textContent.match(/\((\d{2}):(\d{2})/);
        if (!match) return;

        const shiftHours = parseInt(match[1], 10);
        const shiftMinutes = parseInt(match[2], 10);
        const shiftTotalMinutes = shiftHours * 60 + shiftMinutes;

        const timeParts = timeInput.value.split(':');
        if (timeParts.length < 2) return;

        const inputHours = parseInt(timeParts[0], 10);
        const inputMinutes = parseInt(timeParts[1], 10);
        const inputTotalMinutes = inputHours * 60 + inputMinutes;

        const diff = inputTotalMinutes - shiftTotalMinutes;

        indicator.classList.remove('hidden');
        if (diff > 0) {
            indicator.className = 'text-xs font-bold px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800 border border-amber-300 inline-flex items-center gap-1';
            indicator.innerHTML = `
                <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Terlambat +${diff}m (Otomatis)</span>
            `;
        } else if (diff < 0) {
            indicator.className = 'text-xs font-bold px-2.5 py-1 rounded-lg bg-cyan-100 text-cyan-800 border border-cyan-300 inline-flex items-center gap-1';
            indicator.innerHTML = `
                <svg class="w-3.5 h-3.5 text-cyan-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span>Lebih Awal ${Math.abs(diff)}m (Otomatis)</span>
            `;
        } else {
            indicator.className = 'text-xs font-bold px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1';
            indicator.innerHTML = `
                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Tepat Waktu (Otomatis)</span>
            `;
        }
    }

    function setCurrentPhysicalTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const input = document.getElementById('physical_checkin_time');
        if (input) {
            input.value = `${hours}:${minutes}`;
            calculateAutoLate();
        }
    }

    function setShiftStartTime() {
        const shiftSelect = document.getElementById('shift_id');
        const input = document.getElementById('physical_checkin_time');
        if (shiftSelect && input) {
            const selectedOpt = shiftSelect.options[shiftSelect.selectedIndex];
            if (selectedOpt) {
                const text = selectedOpt.textContent;
                const match = text.match(/\((\d{2}:\d{2})/);
                if (match && match[1]) {
                    input.value = match[1];
                    calculateAutoLate();
                }
            }
        }
    }

    // Modal Controllers
    function openOfflineModal(internId = null) {
        const modal = document.getElementById('offlineModal');
        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        document.documentElement.classList.add('overflow-hidden');

        if (internId) {
            const opt = document.querySelector(`.intern-option[data-id="${internId}"]`);
            if (opt) {
                opt.click();
            }
        } else {
            const timeInput = document.getElementById('physical_checkin_time');
            if (timeInput && !timeInput.value) {
                setCurrentPhysicalTime();
            }
        }
    }

    function closeOfflineModal() {
        const modal = document.getElementById('offlineModal');
        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        document.documentElement.classList.remove('overflow-hidden');
    }

    function submitOfflineForm() {
        const form = document.getElementById('offlineForm');
        if (form) {
            form.requestSubmit();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('offlineModal');
        const modalCard = document.getElementById('modalCard');
        const internInput = document.getElementById('intern_id');
        const searchInput = document.getElementById('internSearchInput');
        const dropdown = document.getElementById('internDropdownList');
        const dropdownChevron = document.getElementById('dropdownChevron');
        const toggleDropdownBtn = document.getElementById('toggleDropdownBtn');
        const clearInternBtn = document.getElementById('clearInternBtn');
        const options = Array.from(document.querySelectorAll('.intern-option'));
        const noMatchMsg = document.getElementById('noMatchMessage');
        let highlightedIndex = -1;

        const internSelect = internInput;
        const shiftSelect = document.getElementById('shift_id');
        const officeSelect = document.getElementById('office_id');
        const statusContainer = document.getElementById('statusContainer');
        const statusIcon = document.getElementById('statusIcon');
        const statusTitle = document.getElementById('statusTitle');
        const statusMessage = document.getElementById('statusMessage');
        const physicalTimeInput = document.getElementById('physical_checkin_time');
        const izinContainer = document.getElementById('izinContainer');
        const sakitContainer = document.getElementById('sakitContainer');
        const sicknessSelect = document.getElementById('sickness_verification_type');
        const statusRadios = document.querySelectorAll('input[name="status"]');
        const statusCards = document.querySelectorAll('.status-card');

        const statusRoute = "{{ route($routePrefix . 'status', ['internId' => ':id']) }}";
        const selectedDate = "{{ $date }}";

        // Listeners for live late calculation
        if (physicalTimeInput) {
            physicalTimeInput.addEventListener('input', calculateAutoLate);
            physicalTimeInput.addEventListener('change', calculateAutoLate);
        }
        if (shiftSelect) {
            shiftSelect.addEventListener('change', calculateAutoLate);
        }

        // Close modal on click backdrop
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeOfflineModal();
                }
            });
        }

        // Close penalty modal on click backdrop
        const penaltyModal = document.getElementById('penaltyModal');
        if (penaltyModal) {
            penaltyModal.addEventListener('click', function(e) {
                if (e.target === penaltyModal) {
                    closePenaltyModal();
                }
            });
        }

        // Close approval modal on click backdrop
        const approvalModal = document.getElementById('approvalModal');
        if (approvalModal) {
            approvalModal.addEventListener('click', function(e) {
                if (e.target === approvalModal) {
                    closeApprovalModal();
                }
            });
        }

        // Close modal on press Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeOfflineModal();
                closePenaltyModal();
                closeApprovalModal();
            }
        });

        // ==========================================
        // LOGIKA SEARCHABLE COMBOBOX PEMAGANG (MODAL)
        // ==========================================
        function openDropdown() {
            if (!dropdown) return;
            dropdown.classList.remove('hidden');
            if (dropdownChevron) dropdownChevron.classList.add('rotate-180');
            filterOptions();
        }

        function closeDropdown() {
            if (!dropdown) return;
            dropdown.classList.add('hidden');
            if (dropdownChevron) dropdownChevron.classList.remove('rotate-180');
            highlightedIndex = -1;
            updateHighlighted();
        }

        function getVisibleOptions() {
            return options.filter(opt => opt.style.display !== 'none');
        }

        function filterOptions() {
            const query = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            options.forEach(opt => {
                const searchData = opt.getAttribute('data-search') || '';
                const isExactSelection = internInput.value && opt.getAttribute('data-display') === searchInput.value;
                const matchesQuery = !query || isExactSelection || searchData.includes(query);

                if (matchesQuery) {
                    opt.style.display = 'flex';
                    visibleCount++;
                } else {
                    opt.style.display = 'none';
                }
            });

            if (visibleCount === 0) {
                if (noMatchMsg) noMatchMsg.classList.remove('hidden');
            } else {
                if (noMatchMsg) noMatchMsg.classList.add('hidden');
            }
        }

        function selectOption(opt) {
            const id = opt.getAttribute('data-id');
            const display = opt.getAttribute('data-display');
            const shiftId = opt.getAttribute('data-shift-id');
            const officeId = opt.getAttribute('data-office-id');

            internInput.value = id;
            searchInput.value = display;
            if (clearInternBtn) clearInternBtn.classList.remove('hidden');
            searchInput.classList.remove('border-red-500', 'ring-2', 'ring-red-200');

            // Instantly fill shift & office from option metadata
            if (shiftId && shiftSelect) {
                shiftSelect.value = String(shiftId);
            }
            if (officeId && officeSelect) {
                officeSelect.value = String(officeId);
            }

            closeDropdown();
            calculateAutoLate();

            // Picu pembaruan status dan sinkronisasi data online/offline
            internInput.dispatchEvent(new Event('change'));
        }

        function updateHighlighted() {
            const visible = getVisibleOptions();
            visible.forEach((opt, idx) => {
                if (idx === highlightedIndex) {
                    opt.classList.add('bg-blue-100', 'ring-1', 'ring-blue-400');
                    opt.scrollIntoView({
                        block: 'nearest'
                    });
                } else {
                    opt.classList.remove('bg-blue-100', 'ring-1', 'ring-blue-400');
                }
            });
        }

        if (searchInput) {
            searchInput.addEventListener('focus', openDropdown);
            searchInput.addEventListener('click', openDropdown);

            searchInput.addEventListener('input', function() {
                if (internInput.value) {
                    internInput.value = '';
                    if (clearInternBtn) clearInternBtn.classList.add('hidden');
                }
                openDropdown();
                highlightedIndex = 0;
                updateHighlighted();
            });

            searchInput.addEventListener('keydown', function(e) {
                const visible = getVisibleOptions();

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (dropdown.classList.contains('hidden')) {
                        openDropdown();
                    } else if (visible.length > 0) {
                        highlightedIndex = (highlightedIndex + 1) % visible.length;
                        updateHighlighted();
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (dropdown.classList.contains('hidden')) {
                        openDropdown();
                    } else if (visible.length > 0) {
                        highlightedIndex = (highlightedIndex - 1 + visible.length) % visible.length;
                        updateHighlighted();
                    }
                } else if (e.key === 'Enter') {
                    if (!dropdown.classList.contains('hidden')) {
                        e.preventDefault();
                        if (highlightedIndex >= 0 && highlightedIndex < visible.length) {
                            selectOption(visible[highlightedIndex]);
                        } else if (visible.length > 0) {
                            selectOption(visible[0]);
                        }
                    }
                } else if (e.key === 'Escape') {
                    if (!dropdown.classList.contains('hidden')) {
                        e.stopPropagation();
                        closeDropdown();
                    } else {
                        closeOfflineModal();
                    }
                }
            });
        }

        if (toggleDropdownBtn) {
            toggleDropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (dropdown.classList.contains('hidden')) {
                    searchInput.focus();
                    openDropdown();
                } else {
                    closeDropdown();
                }
            });
        }

        if (clearInternBtn) {
            clearInternBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                internInput.value = '';
                searchInput.value = '';
                clearInternBtn.classList.add('hidden');
                if (statusContainer) statusContainer.classList.add('hidden');
                const onlineBadgeElem = document.getElementById('statusOnlineBadge');
                if (onlineBadgeElem) onlineBadgeElem.classList.add('hidden');
                searchInput.focus();
                openDropdown();
            });
        }

        options.forEach(opt => {
            opt.addEventListener('click', function() {
                selectOption(this);
            });
        });

        document.addEventListener('click', function(e) {
            const combobox = document.getElementById('internComboboxContainer');
            if (combobox && !combobox.contains(e.target)) {
                closeDropdown();
                if (!internInput.value && searchInput) {
                    searchInput.value = '';
                    if (clearInternBtn) clearInternBtn.classList.add('hidden');
                }
            }
        });

        // Validasi form submit
        const offlineForm = document.getElementById('offlineForm');
        if (offlineForm) {
            offlineForm.addEventListener('submit', function(e) {
                if (!internInput.value) {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.classList.add('border-red-500', 'ring-2', 'ring-red-200');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Pemagang Belum Dipilih',
                            text: 'Silakan ketik dan pilih salah satu pemagang dari daftar terlebih dahulu.',
                            confirmButtonColor: '#2563eb'
                        });
                    } else {
                        alert('Silakan ketik dan pilih salah satu pemagang dari daftar terlebih dahulu.');
                    }
                    return false;
                }
            });
        }

        // Update style status radio cards
        function updateRadioStyles() {
            const selectedStatus = document.querySelector('input[name="status"]:checked')?.value;

            statusCards.forEach(card => {
                const radio = card.querySelector('input[type="radio"]');
                if (radio.checked) {
                    if (radio.value === 'hadir' || radio.value === 'tepat_waktu') {
                        card.className = 'status-card cursor-pointer block border-2 border-emerald-500 bg-emerald-50/60 rounded-2xl p-3.5 transition-all text-left shadow-xs ring-2 ring-emerald-500/20';
                    } else if (radio.value === 'early') {
                        card.className = 'status-card cursor-pointer block border-2 border-cyan-500 bg-cyan-50/60 rounded-2xl p-3.5 transition-all text-left shadow-xs ring-2 ring-cyan-500/20';
                    } else if (radio.value === 'terlambat') {
                        card.className = 'status-card cursor-pointer block border-2 border-amber-500 bg-amber-50/60 rounded-2xl p-3.5 transition-all text-left shadow-xs ring-2 ring-amber-500/20';
                    } else if (radio.value === 'izin') {
                        card.className = 'status-card cursor-pointer block border-2 border-blue-500 bg-blue-50/60 rounded-2xl p-3.5 transition-all text-left shadow-xs ring-2 ring-blue-500/20';
                    } else if (radio.value === 'sakit') {
                        card.className = 'status-card cursor-pointer block border-2 border-amber-500 bg-amber-50/60 rounded-2xl p-3.5 transition-all text-left shadow-xs ring-2 ring-amber-500/20';
                    } else if (radio.value === 'alpha') {
                        card.className = 'status-card cursor-pointer block border-2 border-red-500 bg-red-50/60 rounded-2xl p-3.5 transition-all text-left shadow-xs ring-2 ring-red-500/20';
                    }
                } else {
                    card.className = 'status-card cursor-pointer block border-2 border-gray-200 rounded-2xl p-3.5 hover:border-gray-300 transition-all text-left group';
                }
            });

            // Toggle Izin Sub-options Container
            if (izinContainer) {
                if (selectedStatus === 'izin') {
                    izinContainer.classList.remove('hidden');
                } else {
                    izinContainer.classList.add('hidden');
                }
            }

            // Toggle Sakit Sub-options Container
            if (sakitContainer) {
                if (selectedStatus === 'sakit') {
                    sakitContainer.classList.remove('hidden');
                } else {
                    sakitContainer.classList.add('hidden');
                }
            }

            // Sembunyikan auto late indicator jika status izin, sakit, atau alpha
            const autoLateIndicator = document.getElementById('autoLateIndicator');
            if (['izin', 'sakit', 'alpha'].includes(selectedStatus)) {
                if (autoLateIndicator) autoLateIndicator.classList.add('hidden');
            } else {
                calculateAutoLate();
            }

            // Ubah teks & styling tombol submit jika Asisten memilih Izin atau Sakit (Perlu Konfirmasi Admin)
            const isAssistant = document.getElementById('offlineForm')?.dataset?.isAssistant === '1';
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                if (isAssistant && (selectedStatus === 'izin' || selectedStatus === 'sakit')) {
                    submitBtn.className = 'w-1/2 sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-bold text-xs rounded-xl shadow-md transition-all';
                    submitBtn.innerHTML = `
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Ajukan Konfirmasi Admin</span>
                    `;
                } else {
                    submitBtn.className = 'w-1/2 sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs rounded-xl shadow-md transition-all';
                    submitBtn.innerHTML = `
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Simpan Presensi</span>
                    `;
                }
            }
        }

        statusRadios.forEach(radio => {
            radio.addEventListener('change', updateRadioStyles);
        });
        updateRadioStyles();

        // Fetch Intern Status via AJAX saat nama pemagang dipilih
        internSelect.addEventListener('change', function() {
            const internId = this.value;
            if (!internId) return;

            statusContainer.classList.remove('hidden');
            statusContainer.className = 'rounded-xl border p-4 bg-gray-50 border-gray-200 text-gray-600 transition-all duration-300 shadow-sm';
            statusIcon.innerHTML = `
            <svg class="animate-spin w-5 h-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        `;
            statusTitle.textContent = 'Memeriksa status kehadiran...';
            statusMessage.textContent = 'Menghubungkan ke data online & offline pemagang...';

            const url = statusRoute.replace(':id', internId) + '?date=' + encodeURIComponent(selectedDate);

            fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.intern) {
                            if (data.intern.default_shift_id && shiftSelect) {
                                shiftSelect.value = String(data.intern.default_shift_id);
                            }
                            if (data.intern.default_office_id && officeSelect) {
                                officeSelect.value = String(data.intern.default_office_id);
                            }
                        }

                        if (data.offline_attendance) {
                            if (data.offline_attendance.shift_id && shiftSelect) {
                                shiftSelect.value = String(data.offline_attendance.shift_id);
                            }
                            if (data.offline_attendance.office_id && officeSelect) {
                                officeSelect.value = String(data.offline_attendance.office_id);
                            }

                            const offStatus = data.offline_attendance.status;
                            const radioToSelect = ['hadir', 'early', 'terlambat', 'izin', 'sakit', 'alpha'].includes(offStatus) ? offStatus : 'hadir';
                            const radioElem = document.querySelector(`input[name="status"][value="${radioToSelect}"]`);
                            if (radioElem) {
                                radioElem.checked = true;
                            }

                            if (physicalTimeInput) {
                                physicalTimeInput.value = data.offline_attendance.physical_checkin_time || data.offline_attendance.check_time || '';
                            }

                            if (offStatus === 'izin' && typeof data.offline_attendance.permit_is_valid !== 'undefined') {
                                const permitVal = data.offline_attendance.permit_is_valid ? '1' : '0';
                                const pRadio = document.querySelector(`input[name="permit_is_valid"][value="${permitVal}"]`);
                                if (pRadio) pRadio.checked = true;
                            }

                            if (offStatus === 'sakit' && sicknessSelect && data.offline_attendance.sickness_verification_type) {
                                sicknessSelect.value = data.offline_attendance.sickness_verification_type;
                            }

                            const notesElem = document.getElementById('notes');
                            if (notesElem && data.offline_attendance.notes) {
                                notesElem.value = data.offline_attendance.notes;
                            }
                        } else {
                            // Default when creating new record
                            if (physicalTimeInput && data.online_attendance && data.online_attendance.start_time) {
                                physicalTimeInput.value = data.online_attendance.start_time;
                            } else if (physicalTimeInput && !physicalTimeInput.value) {
                                setCurrentPhysicalTime();
                            }
                        }

                        calculateAutoLate();
                        updateRadioStyles();

                        const alertType = data.status.alert_type;
                        statusTitle.textContent = data.status.alert_title;
                        statusMessage.textContent = data.status.alert_message;

                        const onlineBadgeElem = document.getElementById('statusOnlineBadge');
                        if (onlineBadgeElem) {
                            if (data.online && data.online.time) {
                                onlineBadgeElem.classList.remove('hidden');
                                const isLate = data.online.status_key === 'terlambat';
                                onlineBadgeElem.className = `inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold ${isLate ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300'}`;
                                onlineBadgeElem.innerHTML = `
                                    <svg class="w-3.5 h-3.5 ${isLate ? 'text-amber-600' : 'text-emerald-600'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Online: ${data.online.time}</span>
                                `;
                            } else if (data.online && data.online.status_key === 'izin_sakit') {
                                onlineBadgeElem.classList.remove('hidden');
                                onlineBadgeElem.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300';
                                onlineBadgeElem.innerHTML = `
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <span>Online: Izin Sakit</span>
                                `;
                            } else if (data.online && (data.online.status_key === 'izin_keperluan' || data.online.status_key === 'izin')) {
                                onlineBadgeElem.classList.remove('hidden');
                                onlineBadgeElem.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-300';
                                onlineBadgeElem.innerHTML = `
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    <span>Online: Izin Keperluan</span>
                                `;
                            } else if (data.online && data.online.status_key === 'alpha') {
                                onlineBadgeElem.classList.remove('hidden');
                                onlineBadgeElem.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-red-100 text-red-800 border border-red-300';
                                onlineBadgeElem.innerHTML = `
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    <span>Online: Alpha</span>
                                `;
                            } else {
                                onlineBadgeElem.classList.remove('hidden');
                                onlineBadgeElem.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-200/80 text-gray-700';
                                onlineBadgeElem.innerHTML = `
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Belum Absen Online</span>
                                `;
                            }
                        }

                        if (alertType === 'warning') {
                            statusContainer.className = 'rounded-2xl border p-4 bg-amber-50 border-amber-300 text-amber-900 transition-all duration-300 shadow-xs';
                            statusIcon.innerHTML = `
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    `;
                        } else if (alertType === 'success') {
                            statusContainer.className = 'rounded-2xl border p-4 bg-emerald-50 border-emerald-300 text-emerald-900 transition-all duration-300 shadow-xs';
                            statusIcon.innerHTML = `
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    `;
                        } else if (alertType === 'danger') {
                            statusContainer.className = 'rounded-2xl border p-4 bg-red-50 border-red-300 text-red-900 transition-all duration-300 shadow-xs';
                            statusIcon.innerHTML = `
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    `;
                        } else {
                            statusContainer.className = 'rounded-2xl border p-4 bg-blue-50 border-blue-200 text-blue-900 transition-all duration-300 shadow-xs';
                            statusIcon.innerHTML = `
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    `;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching intern status:', error);
                    statusContainer.classList.add('hidden');
                });
        });

        if (internSelect.value) {
            internSelect.dispatchEvent(new Event('change'));
        }
    });

    // Konfirmasi Hapus / Reset Presensi Offline
    function confirmDelete(e, form) {
        e.preventDefault();
        const internName = form.dataset.internName || 'Pemagang';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Batalkan Presensi Offline?',
                text: `Apakah Anda yakin ingin membatalkan presensi offline untuk ${internName}? Presensi online pemagang di sistem akan TETAP UTUH dan aman.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Batalkan Offline',
                cancelButtonText: 'Kembali'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            if (confirm(`Apakah Anda yakin ingin membatalkan presensi offline untuk ${internName}? Presensi online pemagang tetap aman.`)) {
                form.submit();
            }
        }
        return false;
    }
</script>

@if(old('intern_id') || $errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openOfflineModal();
    });
</script>
@endif
@endsection