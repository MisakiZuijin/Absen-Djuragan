@extends('layouts.main')

@section('title', 'Manajemen Izin Tidak Hadir')

@section('contents')
    @include('layouts.sidebar')
    @include('layouts.navbar')

    <main class="ml-64 mt-24 p-4 md:p-6 lg:ml-64 bg-slate-50 min-h-screen">
        <!-- Header Section -->
        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-gradient-to-tr from-amber-600 to-indigo-600 rounded-2xl shadow-md text-white">
                        <i class="fa-solid fa-user-clock text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Manajemen Izin Tidak Hadir</h1>
                        <p class="text-xs md:text-sm text-slate-500 mt-0.5">Kelola izin keperluan sekolah/pribadi (wajib ganti jam) dan pemantauan status alpha pemagang.</p>
                    </div>
                </div>

                <!-- Info Pill -->
                <div class="flex items-center gap-2 px-3.5 py-2 bg-amber-50 rounded-xl border border-amber-200 text-xs font-semibold text-amber-800 shadow-sm">
                    <i class="fa-solid fa-clock-rotate-left text-amber-600"></i>
                    <span>Izin Keperluan & Alpha: Masuk Hutang Jam Kerja</span>
                </div>
            </div>
        </div>

        <!-- Alert Notification -->
        @if (session('success'))
            <div class="mb-6 flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 flex items-center justify-between p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
                    <p class="text-sm font-medium">{{ session('error') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        <!-- Summary Metric Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Card 1: Total Izin -->
            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Izin Keperluan</span>
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                        <i class="fa-solid fa-clipboard-list text-sm"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl md:text-3xl font-bold text-slate-800">{{ $totalIzin }}</span>
                    <span class="text-xs text-slate-400">pengajuan</span>
                </div>
            </div>

            <!-- Card 2: Hari Ini -->
            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tidak Hadir Hari Ini</span>
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                        <i class="fa-solid fa-calendar-day text-sm"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl md:text-3xl font-bold text-blue-600">{{ $todayCount }}</span>
                    <span class="text-xs text-slate-400">pemagang</span>
                </div>
            </div>

            <!-- Card 3: Wajib Ganti Jam -->
            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Wajib Ganti Jam</span>
                    <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                        <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl md:text-3xl font-bold text-indigo-600">{{ $gantiJamCount }}</span>
                    <span class="text-xs text-slate-400">jadwal</span>
                </div>
            </div>

            <!-- Card 4: Total Alpha -->
            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Alpha (Tidak Hadir)</span>
                    <span class="p-2 bg-rose-50 text-rose-600 rounded-xl">
                        <i class="fa-solid fa-ban text-sm"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl md:text-3xl font-bold text-rose-600">{{ $alphaCount }}</span>
                    <span class="text-xs text-slate-400">hutang penuh</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200 shadow-sm mb-6 space-y-3">
            <!-- Tabs Periode Filter & Tipe Filter -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Periode: Hari Ini vs Semua -->
                    <div class="inline-flex rounded-xl bg-slate-100 p-0.5 border border-slate-200">
                        <a href="{{ route('admin.permitKeperluan.index', array_merge(request()->except(['page', 'date']), ['period' => 'today'])) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ ($period ?? 'today') === 'today' && empty($dateFilter) ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                            <i class="fa-solid fa-calendar-day mr-1"></i> Hari Ini
                        </a>
                        <a href="{{ route('admin.permitKeperluan.index', array_merge(request()->except(['page', 'date']), ['period' => 'all'])) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ ($period ?? 'today') === 'all' && empty($dateFilter) ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                            <i class="fa-solid fa-calendar-days mr-1"></i> Semua
                        </a>
                    </div>

                    <div class="h-5 w-px bg-slate-200 mx-1 hidden sm:block"></div>

                    <!-- Tipe: Semua / Izin / Alpha -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <a href="{{ route('admin.permitKeperluan.index', array_merge(request()->query(), ['type' => 'all'])) }}"
                           class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $typeFilter === 'all' ? 'bg-slate-800 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Semua Tipe
                        </a>
                        <a href="{{ route('admin.permitKeperluan.index', array_merge(request()->query(), ['type' => 'izin'])) }}"
                           class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $typeFilter === 'izin' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            <i class="fa-solid fa-clipboard-question mr-1"></i> Izin Keperluan
                        </a>
                        <a href="{{ route('admin.permitKeperluan.index', array_merge(request()->query(), ['type' => 'alpha'])) }}"
                           class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $typeFilter === 'alpha' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            <i class="fa-solid fa-ban mr-1"></i> Alpha
                        </a>
                    </div>
                </div>

                @if(request()->hasAny(['type', 'status', 'date', 'search']) || request('period') === 'all')
                    <a href="{{ route('admin.permitKeperluan.index') }}"
                       class="text-xs font-medium text-slate-500 hover:text-rose-600 inline-flex items-center gap-1 transition-colors">
                        <i class="fa-solid fa-rotate-left"></i> Reset Filter
                    </a>
                @endif
            </div>

            <!-- Form Search & Filter -->
            <form method="GET" action="{{ route('admin.permitKeperluan.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <input type="hidden" name="type" value="{{ $typeFilter }}">
                <input type="hidden" name="period" value="{{ $period ?? 'today' }}">

                <div class="md:col-span-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama pemagang, divisi, sekolah..."
                           class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                </div>

                <div class="md:col-span-3">
                    <input type="date" name="date" value="{{ $dateFilter }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                </div>

                <div class="md:col-span-3">
                    <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status Penggantian Jam</option>
                        <option value="ganti_jam" {{ $statusFilter === 'ganti_jam' ? 'selected' : '' }}>Wajib Ganti Jam (Masuk Hutang Shift)</option>
                        <option value="lunas" {{ $statusFilter === 'lunas' ? 'selected' : '' }}>Bebas Ganti Jam (Dispensasi Lunas)</option>
                        <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Menunggu Keputusan / ACC Admin</option>
                    </select>
                </div>

                <div class="md:col-span-1">
                    <button type="submit" class="w-full h-full py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- List Item Izin Keperluan & Alpha (Dibatasi 5 item per halaman) -->
        <div class="space-y-3 mb-6">
            @forelse ($permits as $index => $item)
                @php
                    $intern = $item->schedule?->intern;
                    $user = $intern?->user;
                    $profile = $user?->profile;
                    $name = $profile?->full_name ?? $user?->name ?? 'Pemagang';
                    $divisionName = $intern?->division?->name ?? 'Umum';
                    $schoolName = $intern?->school?->name ?? '-';

                    $isAlpha = ($item->attd_status_id == 5);
                    $isLunas = ($item->isChangeSchedule == 1);
                    $isGantiJam = ($item->isChangeSchedule == 2);
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200/90 p-4 md:p-5 shadow-xs hover:shadow-md transition-all">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <!-- Kolom 1: Profil Pemagang -->
                        <div class="flex items-start gap-3.5 min-w-[240px]">
                            <div class="w-11 h-11 rounded-2xl {{ $isAlpha ? 'bg-gradient-to-br from-rose-500 to-red-700' : 'bg-gradient-to-br from-amber-500 to-orange-600' }} text-white flex items-center justify-center font-extrabold text-sm shadow-xs shrink-0">
                                {{ strtoupper(substr($name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 text-sm md:text-base">{{ $name }}</div>
                                <div class="text-xs text-slate-500 flex flex-col items-start gap-1 mt-1">
                                    <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-semibold text-[11px] border border-slate-200/60">
                                        {{ $divisionName }}
                                    </span>
                                    <span class="text-slate-500 text-xs truncate max-w-[240px]" title="{{ $schoolName }}">
                                        <i class="fa-solid fa-graduation-cap text-slate-400 mr-1"></i>{{ $schoolName }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom 2: Tanggal, Shift, Tipe & Alasan -->
                        <div class="flex-1 space-y-2 border-t lg:border-t-0 lg:border-l lg:border-r border-slate-100 pt-3 lg:pt-0 lg:px-5">
                            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-700">
                                @if ($isAlpha)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class="fa-solid fa-ban text-[11px]"></i> Alpha (Tidak Hadir)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fa-solid fa-clipboard-question text-[11px]"></i> {{ $item->permitReason?->category?->name ?? 'Izin Keperluan' }}
                                    </span>
                                @endif

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700">
                                    <i class="fa-regular fa-calendar text-amber-600"></i>
                                    {{ \Carbon\Carbon::parse($item->date)->translatedFormat('l, d F Y') }}
                                </span>

                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 font-medium">
                                    <i class="fa-regular fa-clock text-slate-400"></i>
                                    {{ $item->shift?->name ?? 'Shift' }} ({{ $item->shift?->start_time ?? '-' }} - {{ $item->shift?->end_time ?? '-' }})
                                </span>
                            </div>

                            <div class="text-xs text-slate-600 bg-slate-50/70 p-2.5 rounded-xl border border-slate-100">
                                <span class="font-bold text-slate-700">Alasan:</span>
                                <span>{{ $item->permitReason?->description ?? ($isAlpha ? 'Tidak hadir tanpa keterangan izin' : 'Izin keperluan') }}</span>
                            </div>

                            <div class="flex items-center gap-2 pt-0.5">
                                @if (!empty($item->permitReason?->proof_url))
                                    <a href="{{ $item->permitReason->proof_url }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-lg text-xs font-semibold transition shadow-2xs">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                        <span>Lihat Lampiran Bukti</span>
                                    </a>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-slate-400 italic text-xs bg-slate-50 rounded-lg border border-slate-200/60">
                                        <i class="fa-solid fa-file-excel text-[11px]"></i> Tanpa lampiran bukti
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Kolom 3: Status Hutang Jam & Aksi -->
                        <div class="flex lg:flex-col items-center lg:items-end justify-between gap-3 shrink-0 pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                            <div>
                                @if ($isAlpha)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class="fa-solid fa-clock-rotate-left"></i> Wajib Ganti Jam (Alpha)
                                    </span>
                                @elseif ($isGantiJam)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fa-solid fa-clock-rotate-left"></i> Wajib Ganti Jam
                                    </span>
                                @elseif ($isLunas)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-check-circle"></i> Bebas Jam (Dispensasi Lunas)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        <i class="fa-solid fa-hourglass-half"></i> Menunggu Keputusan Admin
                                    </span>
                                @endif
                            </div>

                            <!-- Dropdown Aksi -->
                            <div class="relative inline-block text-left">
                                <button type="button" onclick="togglePermitDropdown('dropdown-keperluan-{{ $item->id }}', event)"
                                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition cursor-pointer">
                                    <span>Aksi</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                                </button>
                                <div id="dropdown-keperluan-{{ $item->id }}" class="permit-dropdown hidden absolute right-0 mt-1.5 w-52 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 z-50 text-left">
                                    <!-- ACC Bebas Jam (Dispensasi) -->
                                    <form method="POST" action="{{ route('admin.permitKeperluan.approveLunas', $item->id) }}" class="m-0 p-0"
                                          onsubmit="return confirm('ACC izin keperluan ini sebagai Bebas Ganti Jam (Dispensasi)? Pemagang tidak perlu mengganti jam.');">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-left text-xs font-bold text-emerald-700 hover:bg-emerald-50 transition cursor-pointer">
                                            <i class="fa-solid fa-circle-check text-emerald-600 w-4"></i>
                                            <span>ACC (Bebas Jam)</span>
                                        </button>
                                    </form>

                                    <!-- Wajib Ganti Jam -->
                                    <form method="POST" action="{{ route('admin.permitKeperluan.approveGantiJam', $item->id) }}" class="m-0 p-0"
                                          onsubmit="return confirm('Tetapkan izin keperluan ini sebagai Wajib Ganti Jam (Tanpa Bukti Surat)? Pemagang wajib mengganti jam shift.');">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-left text-xs font-bold text-amber-700 hover:bg-amber-50 transition cursor-pointer">
                                            <i class="fa-solid fa-clock-rotate-left text-amber-600 w-4"></i>
                                            <span>Wajib Ganti Jam</span>
                                        </button>
                                    </form>

                                    <!-- Jadikan Alpha -->
                                    @if(!$isAlpha)
                                        <form method="POST" action="{{ route('admin.permitKeperluan.setAlpha', $item->id) }}" class="m-0 p-0"
                                              onsubmit="return confirm('Ubah status menjadi Alpha (Tidak Hadir)? Pemagang otomatis berhutang jam shift penuh.');">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-left text-xs font-bold text-rose-700 hover:bg-rose-50 transition cursor-pointer">
                                                <i class="fa-solid fa-ban text-rose-600 w-4"></i>
                                                <span>Set Status Alpha</span>
                                            </button>
                                        </form>
                                    @endif

                                    <div class="border-t border-slate-100 my-1"></div>

                                    <!-- Edit Detail -->
                                    <button type="button"
                                            onclick="openEditModal({{ $item->id }}, '{{ addslashes($item->permitReason?->description ?? '') }}', '{{ $item->permitReason?->proof_url ?? '' }}', {{ $item->isChangeSchedule ?? 2 }}, {{ $item->attd_status_id ?? 3 }})"
                                            class="w-full flex items-center gap-2.5 px-4 py-2 text-left text-xs font-medium text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                                        <i class="fa-solid fa-pen-to-square text-slate-400 w-4"></i>
                                        <span>Edit Detail</span>
                                    </button>

                                    @if (!empty($item->permitReason?->proof_url))
                                        <!-- Lihat Bukti -->
                                        <a href="{{ $item->permitReason->proof_url }}" target="_blank"
                                           class="flex items-center gap-2.5 px-4 py-2 text-left text-xs font-medium text-teal-700 hover:bg-teal-50 transition">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-teal-600 w-4"></i>
                                            <span>Lihat Lampiran Bukti</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400">
                    <div class="flex flex-col items-center justify-center">
                        <i class="fa-solid fa-user-clock text-4xl text-slate-300 mb-3"></i>
                        <p class="font-bold text-slate-600">Tidak ada pengajuan izin keperluan / alpha</p>
                        <p class="text-xs text-slate-400 mt-1">Belum ada data sesuai filter yang dipilih.</p>
                    </div>
                </div>
            @endforelse

            @if ($permits->hasPages())
                <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-xs flex items-center justify-center">
                    {{ $permits->links() }}
                </div>
            @endif
        </div>
    </main>

    <!-- Modal Edit Detail Keperluan -->
    <div id="modalEditDetail" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden animate-fade-in">
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg mx-4 overflow-hidden" onclick="event.stopPropagation();">
            <div class="p-5 bg-gradient-to-r from-amber-800 to-indigo-950 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/10 rounded-xl">
                        <i class="fa-solid fa-user-clock text-amber-300"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base">Edit Izin Tidak Hadir</h3>
                        <p class="text-xs text-slate-300">Ubah rincian alasan atau ketentuan ganti jam.</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal();" class="text-slate-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="editDetailForm" method="POST" action="" class="p-6 space-y-4">
                @csrf
                <div>
                    <label for="edit_status_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Status Kehadiran <span class="text-rose-500">*</span>
                    </label>
                    <select id="edit_status_id" name="status_id" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500" required>
                        <option value="3">Izin Tidak Hadir (Ada Pengajuan Izin Keperluan)</option>
                        <option value="5">Alpha (Tidak Hadir Tanpa Izin / Keterangan)</option>
                    </select>
                </div>

                <div>
                    <label for="edit_jam_option" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Ketentuan Penggantian Jam <span class="text-rose-500">*</span>
                    </label>
                    <select id="edit_jam_option" name="jam_option" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500" required>
                        <option value="2">Wajib Ganti Jam (Masuk ke Rekap Hutang Jam Kerja Shift)</option>
                        <option value="1">Bebas Ganti Jam (Dispensasi Khusus - Status Lunas / Bebas Hutang Jam)</option>
                    </select>
                </div>

                <div>
                    <label for="edit_description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Alasan / Keterangan Keperluan <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="edit_description" name="description" rows="3" required
                              class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                              placeholder="Rincian keperluan izin..."></textarea>
                </div>

                <div>
                    <label for="edit_proof_url" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Link Google Drive Lampiran <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <input type="url" id="edit_proof_url" name="proof_url"
                           placeholder="https://drive.google.com/file/..."
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeEditModal();"
                            class="px-4 py-2 border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openEditModal(id, description, proofUrl, jamOption, statusId) {
            const form = document.getElementById('editDetailForm');
            form.action = `/admin/izin-tidak-hadir/${id}/update-detail`;

            document.getElementById('edit_description').value = description;
            document.getElementById('edit_proof_url').value = proofUrl;
            document.getElementById('edit_jam_option').value = jamOption || 2;
            document.getElementById('edit_status_id').value = statusId || 3;

            document.getElementById('modalEditDetail').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('modalEditDetail').classList.add('hidden');
        }

        function togglePermitDropdown(menuId, event) {
            event.stopPropagation();
            const menu = document.getElementById(menuId);
            const isCurrentlyHidden = menu.classList.contains('hidden');

            document.querySelectorAll('.permit-dropdown').forEach(el => {
                el.classList.add('hidden');
            });

            if (isCurrentlyHidden) {
                menu.classList.remove('hidden');
            }
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.permit-dropdown') && !e.target.closest('button[onclick*="togglePermitDropdown"]')) {
                document.querySelectorAll('.permit-dropdown').forEach(el => {
                    el.classList.add('hidden');
                });
            }
        });
    </script>
    @endpush
@endsection
