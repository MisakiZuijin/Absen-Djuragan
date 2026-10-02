@extends('layouts.main')

@section('title', 'Manajemen Izin Sakit')

@section('contents')
<main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 bg-slate-50 min-h-screen min-w-0">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-gradient-to-tr from-emerald-600 to-teal-600 rounded-2xl shadow-md text-white">
                    <i class="fa-solid fa-notes-medical text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Manajemen Izin Sakit</h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-0.5">Verifikasi surat keterangan sakit pemagang. Pengajuan yang disetujui bebas dari kewajiban ganti jam kerja.</p>
                </div>
            </div>

            <!-- Info Pill -->
            <div class="flex items-center gap-2 px-3.5 py-2 bg-emerald-50 rounded-xl border border-emerald-200 text-xs font-semibold text-emerald-800 shadow-sm">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span>Sakit Disetujui: Bebas Ganti Jam (Lunas)</span>
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
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <!-- Card 1: Total Sakit (period-scoped) -->
        <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Izin Sakit</span>
                <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                    <i class="fa-solid fa-hospital-user text-sm"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl md:text-3xl font-bold text-slate-800">{{ $totalSakit }}</span>
                <span class="text-xs text-slate-400">pengajuan</span>
            </div>
            <div class="mt-1.5 text-[10px] text-slate-400 font-medium uppercase tracking-wide">
                <i class="fa-solid fa-filter mr-0.5"></i> {{ $periodLabel }}
            </div>
        </div>

        <!-- Card 2: Lunas / Bebas Jam (period-scoped) -->
        <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Bebas Ganti Jam</span>
                <span class="p-2 bg-teal-50 text-teal-600 rounded-xl">
                    <i class="fa-solid fa-circle-check text-sm"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl md:text-3xl font-bold text-teal-600">{{ $lunasCount }}</span>
                <span class="text-xs text-slate-400">lunas</span>
            </div>
            <div class="mt-1.5 text-[10px] text-slate-400 font-medium uppercase tracking-wide">
                <i class="fa-solid fa-filter mr-0.5"></i> {{ $periodLabel }}
            </div>
        </div>

        <!-- Card 3: Menunggu Verifikasi (period-scoped) -->
        <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Menunggu ACC</span>
                <span class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                    <i class="fa-solid fa-hourglass-half text-sm"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl md:text-3xl font-bold text-amber-600">{{ $pendingCount }}</span>
                <span class="text-xs text-slate-400">perlu dicek</span>
            </div>
            <div class="mt-1.5 text-[10px] text-slate-400 font-medium uppercase tracking-wide">
                <i class="fa-solid fa-filter mr-0.5"></i> {{ $periodLabel }}
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200 shadow-sm mb-6 space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.permitSakit.index', array_merge(request()->except(['page', 'date']), ['period' => 'today'])) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ ($period ?? 'today') === 'today' && empty($dateFilter) ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <i class="fa-solid fa-calendar-day mr-1"></i> Hari Ini
                </a>
                <a href="{{ route('admin.permitSakit.index', array_merge(request()->except(['page', 'date']), ['period' => 'all'])) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ ($period ?? 'today') === 'all' && empty($dateFilter) ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <i class="fa-solid fa-calendar-days mr-1"></i> Semua
                </a>
            </div>

            @if(request()->hasAny(['status', 'date', 'search']) || request('period') === 'all')
            <a href="{{ route('admin.permitSakit.index') }}"
                class="text-xs font-medium text-slate-500 hover:text-rose-600 inline-flex items-center gap-1 transition-colors">
                <i class="fa-solid fa-rotate-left"></i> Reset Filter
            </a>
            @endif
        </div>

        <form method="GET" action="{{ route('admin.permitSakit.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3">
            <input type="hidden" name="period" value="{{ $period ?? 'today' }}">
            <!-- Search Input -->
            <div class="md:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama pemagang, divisi, sekolah..."
                    class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
            </div>

            <!-- Date Filter -->
            <div class="md:col-span-3">
                <input type="date" name="date" value="{{ $dateFilter }}"
                    class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
            </div>

            <!-- Status Filter -->
            <div class="md:col-span-3">
                <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status Penggantian Jam</option>
                    <option value="lunas" {{ $statusFilter === 'lunas' ? 'selected' : '' }}>Bebas Ganti Jam (Lunas - Ada Surat Dokter)</option>
                    <option value="ganti_jam" {{ $statusFilter === 'ganti_jam' ? 'selected' : '' }}>Wajib Ganti Jam (Hutang Shift)</option>
                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Menunggu Keputusan / ACC Admin</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="md:col-span-1">
                <button type="submit" class="w-full h-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    <span>Cari</span>
                </button>
            </div>
        </form>
    </div>

    <!-- List Item Izin Sakit (Dibatasi 5 item per halaman) -->
    <div class="space-y-3 mb-6">
        @forelse ($permits as $index => $item)
        @php
        $intern = $item->schedule?->intern;
        $user = $intern?->user;
        $profile = $user?->profile;
        $name = $profile?->full_name ?? $user?->name ?? 'Pemagang';
        $divisionName = $intern?->division?->name ?? 'Umum';
        $schoolName = $intern?->school?->name ?? '-';

        $isLunas = ($item->isChangeSchedule == 1);
        $isGantiJam = ($item->isChangeSchedule == 2);
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 md:p-5 shadow-xs hover:shadow-md transition-all">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Kolom 1: Profil Pemagang -->
                <div class="flex items-start gap-3.5 min-w-[240px]">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white flex items-center justify-center font-extrabold text-sm shadow-xs shrink-0">
                        {{ strtoupper(substr($name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="font-bold text-slate-800 text-sm md:text-base">{{ $name }}</div>
                        <div class="text-xs text-slate-500 flex flex-col items-start gap-1 mt-1">
                            <span class="inline-block px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold text-[11px] border border-emerald-200/60">
                                {{ $divisionName }}
                            </span>
                            <span class="text-slate-500 text-xs truncate max-w-[240px]" title="{{ $schoolName }}">
                                <i class="fa-solid fa-graduation-cap text-slate-400 mr-1"></i>{{ $schoolName }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Kolom 2: Tanggal, Shift, Keterangan & Surat Dokter -->
                <div class="flex-1 space-y-2 border-t lg:border-t-0 lg:border-l lg:border-r border-slate-100 pt-3 lg:pt-0 lg:px-5">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-700">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700">
                            <i class="fa-regular fa-calendar text-emerald-600"></i>
                            {{ \Carbon\Carbon::parse($item->date)->translatedFormat('l, d F Y') }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 font-medium">
                            <i class="fa-regular fa-clock text-slate-400"></i>
                            {{ $item->shift?->name ?? 'Shift' }} ({{ $item->shift?->start_time ?? '-' }} - {{ $item->shift?->end_time ?? '-' }})
                        </span>
                    </div>

                    <div class="text-xs text-slate-600 bg-slate-50/70 p-2.5 rounded-xl border border-slate-100">
                        <span class="font-bold text-slate-700">Keterangan Sakit:</span>
                        <span>{{ $item->permitReason?->description ?? 'Sakit' }}</span>
                    </div>

                    <div class="flex items-center gap-2 pt-0.5">
                        @if (!empty($item->permitReason?->proof_url))
                        <a href="{{ $item->permitReason->proof_url }}" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-semibold transition shadow-2xs">
                            <i class="fa-solid fa-file-medical text-xs"></i>
                            <span>Lihat Surat Dokter</span>
                        </a>
                        @elseif ($item->permitReason?->permit_category_id == 2 || str_contains(strtolower($item->permitReason?->description ?? ''), 'hr'))
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-amber-700 font-semibold text-xs bg-amber-50 rounded-lg border border-amber-200">
                            <i class="fa-solid fa-user-check text-[11px]"></i> Dikonfirmasi & Dicek HR
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-slate-400 italic text-xs bg-slate-50 rounded-lg border border-slate-200/60">
                            <i class="fa-solid fa-file-excel text-[11px]"></i> Tanpa surat dokter
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Kolom 3: Status & Aksi -->
                <div class="flex lg:flex-col items-center lg:items-end justify-between gap-3 shrink-0 pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                    <div>
                        @if ($isLunas)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <i class="fa-solid fa-circle-check"></i> Bebas Jam (ACC)
                        </span>
                        @elseif ($isGantiJam)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            <i class="fa-solid fa-clock-rotate-left"></i> Wajib Ganti Jam
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                            <i class="fa-solid fa-hourglass-half"></i> Menunggu Keputusan
                        </span>
                        @endif
                    </div>

                    <!-- Dropdown Aksi -->
                    <div class="relative inline-block text-left">
                        <button type="button" onclick="togglePermitDropdown('dropdown-sakit-{{ $item->id }}', event)"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition cursor-pointer">
                            <span>Aksi</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>
                        <div id="dropdown-sakit-{{ $item->id }}" class="permit-dropdown hidden absolute right-0 mt-1.5 w-52 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 z-50 text-left">
                            <!-- ACC Bebas Jam -->
                            <form method="POST" action="{{ route('admin.permitSakit.approveLunas', $item->id) }}" class="m-0 p-0"
                                onsubmit="return confirm('ACC izin sakit ini sebagai Bebas Ganti Jam (Lunas)? Pemagang tidak akan berhutang jam kerja.');">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-left text-xs font-bold text-emerald-700 hover:bg-emerald-50 transition cursor-pointer">
                                    <i class="fa-solid fa-circle-check text-emerald-600 w-4"></i>
                                    <span>ACC (Bebas Jam)</span>
                                </button>
                            </form>

                            <!-- Set Wajib Ganti Jam (Hutang Jam) -->
                            <form method="POST" action="{{ route('admin.permitSakit.setWajibGantiJam', $item->id) }}" class="m-0 p-0"
                                onsubmit="return confirm('Tetapkan izin sakit ini sebagai Wajib Ganti Jam (bukti tidak valid/tanpa bukti surat)? Pemagang akan berhutang jam kerja shift penuh.');">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-left text-xs font-bold text-amber-700 hover:bg-amber-50 transition cursor-pointer">
                                    <i class="fa-solid fa-clock-rotate-left text-amber-600 w-4"></i>
                                    <span>Wajib Ganti Jam</span>
                                </button>
                            </form>

                            <div class="border-t border-slate-100 my-1"></div>

                            <!-- Edit Detail -->
                            <button type="button"
                                onclick="openEditModalFromButton(this)"
                                data-id="{{ $item->id }}"
                                data-description="{{ $item->permitReason?->description ?? '' }}"
                                data-proof-url="{{ $item->permitReason?->proof_url ?? '' }}"
                                data-jam-option="{{ $item->isChangeSchedule ?? 1 }}"
                                class="w-full flex items-center gap-2.5 px-4 py-2 text-left text-xs font-medium text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                                <i class="fa-solid fa-pen-to-square text-slate-400 w-4"></i>
                                <span>Edit Detail</span>
                            </button>

                            @if (!empty($item->permitReason?->proof_url))
                            <!-- Lihat Bukti / Surat Dokter -->
                            <a href="{{ $item->permitReason->proof_url }}" target="_blank"
                                class="flex items-center gap-2.5 px-4 py-2 text-left text-xs font-medium text-teal-700 hover:bg-teal-50 transition">
                                <i class="fa-solid fa-file-medical text-teal-600 w-4"></i>
                                <span>Lihat Surat Dokter</span>
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
                <i class="fa-solid fa-notes-medical text-4xl text-slate-300 mb-3"></i>
                <p class="font-bold text-slate-600">Tidak ada pengajuan izin sakit</p>
                <p class="text-xs text-slate-400 mt-1">Belum ada data izin sakit sesuai filter yang dipilih.</p>
            </div>
        </div>
        @endforelse

        @if ($permits->hasPages())
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-3.5 bg-white rounded-2xl border border-slate-200/90 shadow-2xs mt-4">
            <div class="text-xs text-slate-500 font-medium">
                Menampilkan <span class="font-bold text-slate-800">{{ $permits->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-800">{{ $permits->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-800">{{ $permits->total() }}</span> pengajuan izin
            </div>
            <div class="flex items-center space-x-1.5">
                {{-- Prev Button --}}
                @if ($permits->onFirstPage())
                    <button class="bg-gray-200 text-gray-400 px-3 py-1.5 rounded-lg text-xs font-semibold cursor-not-allowed shadow-2xs flex items-center gap-1" disabled>
                        <i class="fas fa-chevron-left text-[10px]"></i>
                        <span>Prev</span>
                    </button>
                @else
                    <a href="{{ $permits->previousPageUrl() }}" class="bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold shadow-2xs transition flex items-center gap-1">
                        <i class="fas fa-chevron-left text-[10px]"></i>
                        <span>Prev</span>
                    </a>
                @endif

                {{-- Page Numbers --}}
                <div class="flex space-x-1">
                    @foreach (range(1, $permits->lastPage()) as $page)
                        @if ($page == $permits->currentPage())
                            <span class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold shadow-2xs transition">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $permits->url($page) }}" class="px-3 py-1.5 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-xs font-bold shadow-2xs transition">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                </div>

                {{-- Next Button --}}
                @if ($permits->hasMorePages())
                    <a href="{{ $permits->nextPageUrl() }}" class="bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold shadow-2xs transition flex items-center gap-1">
                        <span>Next</span>
                        <i class="fas fa-chevron-right text-[10px]"></i>
                    </a>
                @else
                    <button class="bg-gray-200 text-gray-400 px-3 py-1.5 rounded-lg text-xs font-semibold cursor-not-allowed shadow-2xs flex items-center gap-1" disabled>
                        <span>Next</span>
                        <i class="fas fa-chevron-right text-[10px]"></i>
                    </button>
                @endif
            </div>
        </div>
        @endif
    </div>
</main>

<!-- Modal Edit Detail Sakit -->
<div id="modalEditDetail" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden animate-fade-in">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg mx-4 overflow-hidden" onclick="event.stopPropagation();">
        <div class="p-5 bg-gradient-to-r from-emerald-800 to-teal-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white/10 rounded-xl">
                    <i class="fa-solid fa-notes-medical text-emerald-300"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base">Edit Detail Izin Sakit</h3>
                    <p class="text-xs text-emerald-200">Perbarui surat dokter atau status pembebasan jam.</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal();" class="text-emerald-300 hover:text-white transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="editDetailForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            <div>
                <label for="edit_jam_option" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Ketentuan Penggantian Jam <span class="text-rose-500">*</span>
                </label>
                <select id="edit_jam_option" name="jam_option" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                    <option value="1">Bebas Ganti Jam (Lunas - Tidak Berhutang Jam Kerja Shift)</option>
                    <option value="2">Wajib Ganti Jam (Masuk ke Rekap Hutang Jam Kerja Shift)</option>
                </select>
            </div>

            <div>
                <label for="edit_description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Keterangan Sakit <span class="text-rose-500">*</span>
                </label>
                <textarea id="edit_description" name="description" rows="3" required
                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="Diagnosis singkat atau rincian kondisi sakit..."></textarea>
            </div>

            <div>
                <label for="edit_proof_url" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Link Google Drive Surat Dokter
                </label>
                <input type="url" id="edit_proof_url" name="proof_url"
                    placeholder="https://drive.google.com/file/..."
                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditModal();"
                    class="px-4 py-2 border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openEditModalFromButton(btn) {
        const id = btn.getAttribute('data-id');
        const description = btn.getAttribute('data-description') || '';
        const proofUrl = btn.getAttribute('data-proof-url') || '';
        const jamOption = btn.getAttribute('data-jam-option') || 1;
        openEditModal(id, description, proofUrl, jamOption);
    }

    function openEditModal(id, description, proofUrl, jamOption) {
        const form = document.getElementById('editDetailForm');
        form.action = `/admin/izin-sakit/${id}/update-detail`;

        document.getElementById('edit_description').value = description;
        document.getElementById('edit_proof_url').value = proofUrl;
        document.getElementById('edit_jam_option').value = jamOption || 1;

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