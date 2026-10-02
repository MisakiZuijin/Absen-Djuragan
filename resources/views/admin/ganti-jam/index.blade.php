@extends('layouts.main')

@section('title', 'Persetujuan & Manajemen Ganti Jam')

@section('contents')
<main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-5 lg:p-6 min-h-screen bg-slate-50/60 text-slate-800 space-y-4 sm:space-y-6 min-w-0 max-w-full overflow-x-hidden">

    <!-- Header Section -->
    <div class="bg-white rounded-2xl p-4 sm:p-6 border border-slate-200/80 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 sm:gap-4">
            <div class="flex items-start sm:items-center gap-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center shrink-0 border border-orange-100 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        Persetujuan & Manajemen Ganti Jam
                    </h1>
                    <p class="text-slate-500 text-xs sm:text-sm mt-0.5 leading-relaxed">
                        Kelola alur pra-pendaftaran ganti jam pemagang (H-1) serta persetujuan sesi ganti jam & pelunasan absensi.
                    </p>
                </div>
            </div>

            <!-- Link ke Pengaturan Ganti Jam -->
            <a href="{{ route('admin.pengaturan.ganti-jam.view') }}"
                class="inline-flex items-center justify-center gap-2 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition self-stretch sm:self-auto shrink-0 border border-slate-200">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0M3.75 18H7.5m6-6h6m-6 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0M3.75 12H10.5" />
                </svg>
                <span>Pengaturan Ganti Jam</span>
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
    <div class="p-3.5 sm:p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-center gap-2.5 shadow-2xs">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-3.5 sm:p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-center gap-2.5 shadow-2xs">
        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Tab Navigasi: Pendaftaran Ganti Jam vs Sesi Ganti Jam & Pelunasan -->
    <div class="p-1 sm:p-1.5 rounded-2xl flex flex-col sm:inline-flex sm:flex-row items-stretch sm:items-center gap-1.5 sm:gap-2 shadow-2xs w-full sm:w-auto" style="background-color: #e2e8f0 !important; border: 1px solid #cbd5e1 !important;">
        <!-- Tab 1: Pendaftaran Ganti Jam -->
        <a href="{{ route('admin.ganti-jam.index', array_filter(['tab' => 'registrations', 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
            class="flex items-center justify-between sm:justify-start gap-2.5 px-4 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer text-left sm:text-center"
            style="{{ $tab === 'registrations' ? 'background-color: #ea580c !important; color: #ffffff !important; box-shadow: 0 4px 6px -1px rgba(234, 88, 12, 0.3) !important;' : 'background-color: #ffffff !important; color: #334155 !important; border: 1px solid #cbd5e1 !important;' }}">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clipboard-list" style="{{ $tab === 'registrations' ? 'color: #ffffff !important;' : 'color: #ea580c !important;' }}"></i>
                <span>Pendaftaran Ganti Jam</span>
            </div>
            @if($regPendingCount > 0)
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black shrink-0"
                style="{{ $tab === 'registrations' ? 'background-color: #ffffff !important; color: #ea580c !important;' : 'background-color: #ea580c !important; color: #ffffff !important;' }}">
                {{ $regPendingCount }} Menunggu ACC
            </span>
            @endif
        </a>

        <!-- Tab 2: Sesi Ganti Jam & Pelunasan -->
        <a href="{{ route('admin.ganti-jam.index', array_filter(['tab' => 'sessions', 'date_from' => $dateFrom, 'date_to' => $dateTo])) }}"
            class="flex items-center justify-between sm:justify-start gap-2.5 px-4 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer text-left sm:text-center"
            style="{{ $tab !== 'registrations' ? 'background-color: #ea580c !important; color: #ffffff !important; box-shadow: 0 4px 6px -1px rgba(234, 88, 12, 0.3) !important;' : 'background-color: #ffffff !important; color: #334155 !important; border: 1px solid #cbd5e1 !important;' }}">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-stopwatch" style="{{ $tab !== 'registrations' ? 'color: #ffffff !important;' : 'color: #ea580c !important;' }}"></i>
                <span>Sesi Ganti Jam & Pelunasan</span>
            </div>
            @if($pendingCount > 0)
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black shrink-0"
                style="{{ $tab !== 'registrations' ? 'background-color: #ffffff !important; color: #ea580c !important;' : 'background-color: #ea580c !important; color: #ffffff !important;' }}">
                {{ $pendingCount }} Menunggu ACC
            </span>
            @endif
        </a>
    </div>

    @if($tab === 'registrations')
    <!-- ========================================================================= -->
    <!-- TAB 1: PRA-PENDAFTARAN GANTI JAM (LIVEWIRE 10 DETIK AUTO-REFRESH)         -->
    <!-- ========================================================================= -->
    @livewire('admin.change-time-registration-manager', [
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'divisionId' => (string) ($divisionId ?? ''),
        'search' => (string) ($search ?? ''),
        'regStatus' => (string) ($regStatus ?? 'pending'),
    ])

    @else
    <!-- ========================================================================= -->
    <!-- TAB 2: SESI GANTI JAM & PELUNASAN (EXISTING SESSIONS)                      -->
    <!-- ========================================================================= -->

    <!-- Summary Stats Cards (Quick Filter Tabs) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
        <!-- Menunggu Persetujuan -->
        <a href="{{ route('admin.ganti-jam.index', array_filter(['tab' => 'sessions', 'status' => 'pending_approval', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'division_id' => $divisionId, 'search' => $search])) }}"
            class="p-3 sm:p-4 rounded-2xl border transition flex flex-col justify-between {{ $status === 'pending_approval' ? 'border-amber-500 bg-amber-50/70 ring-2 ring-amber-500/20 shadow-xs' : 'border-slate-200/80 bg-white hover:border-amber-300 hover:shadow-2xs' }}">
            <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                <span class="text-[11px] sm:text-xs font-semibold text-slate-500 line-clamp-1">Pending</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-bold text-amber-700 font-mono">{{ $pendingCount }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">Perlu tindakan admin</div>
        </a>

        <!-- Disetujui / Lunas -->
        <a href="{{ route('admin.ganti-jam.index', array_filter(['tab' => 'sessions', 'status' => 'approved', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'division_id' => $divisionId, 'search' => $search])) }}"
            class="p-3 sm:p-4 rounded-2xl border transition flex flex-col justify-between {{ $status === 'approved' ? 'border-emerald-500 bg-emerald-50/70 ring-2 ring-emerald-500/20 shadow-xs' : 'border-slate-200/80 bg-white hover:border-emerald-300 hover:shadow-2xs' }}">
            <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                <span class="text-[11px] sm:text-xs font-semibold text-slate-500 line-clamp-1">Disetujui (Lunas)</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-bold text-emerald-700 font-mono">{{ $approvedCount }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">Hutang berhasil dilunaskan</div>
        </a>

        <!-- Ditolak -->
        <a href="{{ route('admin.ganti-jam.index', array_filter(['tab' => 'sessions', 'status' => 'rejected', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'division_id' => $divisionId, 'search' => $search])) }}"
            class="p-3 sm:p-4 rounded-2xl border transition flex flex-col justify-between {{ $status === 'rejected' ? 'border-rose-500 bg-rose-50/70 ring-2 ring-rose-500/20 shadow-xs' : 'border-slate-200/80 bg-white hover:border-rose-300 hover:shadow-2xs' }}">
            <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                <span class="text-[11px] sm:text-xs font-semibold text-slate-500 line-clamp-1">Ditolak</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-bold text-rose-700 font-mono">{{ $rejectedCount }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">Pengajuan tidak valid</div>
        </a>

        <!-- Aktif Berjalan -->
        <a href="{{ route('admin.ganti-jam.index', array_filter(['tab' => 'sessions', 'status' => 'active', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'division_id' => $divisionId, 'search' => $search])) }}"
            class="p-3 sm:p-4 rounded-2xl border transition flex flex-col justify-between {{ $status === 'active' ? 'border-blue-500 bg-blue-50/70 ring-2 ring-blue-500/20 shadow-xs' : 'border-slate-200/80 bg-white hover:border-blue-300 hover:shadow-2xs' }}">
            <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                <span class="text-[11px] sm:text-xs font-semibold text-slate-500 line-clamp-1">Aktif Berjalan</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-bold text-blue-700 font-mono">{{ $activeCount }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">Sedang presensi hari ini</div>
        </a>
    </div>

    <!-- Filters & Search Form Sesi Ganti Jam -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-2xs">
        <form action="{{ route('admin.ganti-jam.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs">
            <input type="hidden" name="tab" value="sessions">

            <!-- Search Intern Name -->
            <div>
                <label for="search_session" class="block font-semibold text-slate-700 mb-1 text-[11px]">Cari Pemagang</label>
                <input type="text" id="search_session" name="search" value="{{ $search }}" placeholder="Nama pemagang..."
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none text-xs bg-slate-50/50 focus:bg-white transition">
            </div>

            <!-- Division Filter -->
            <div>
                <label for="division_id_session" class="block font-semibold text-slate-700 mb-1 text-[11px]">Divisi</label>
                <select id="division_id_session" name="division_id"
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none bg-slate-50/50 focus:bg-white text-xs transition">
                    <option value="">Semua Divisi</option>
                    @foreach($divisions as $div)
                    <option value="{{ $div->id }}" {{ $divisionId == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block font-semibold text-slate-700 mb-1 text-[11px]">Status Sesi</label>
                <select id="status" name="status"
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none bg-slate-50/50 focus:bg-white text-xs transition">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="pending_approval" {{ $status === 'pending_approval' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui (Lunas)</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif Berjalan</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label for="date_from_session" class="block font-semibold text-slate-700 mb-1 text-[11px]">Dari Tanggal</label>
                <input type="date" id="date_from_session" name="date_from" value="{{ $dateFrom }}"
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none bg-slate-50/50 focus:bg-white text-xs transition">
            </div>

            <!-- Date To -->
            <div>
                <label for="date_to_session" class="block font-semibold text-slate-700 mb-1 text-[11px]">Sampai Tanggal</label>
                <input type="date" id="date_to_session" name="date_to" value="{{ $dateTo }}"
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none bg-slate-50/50 focus:bg-white text-xs transition">
            </div>

            <!-- Submit & Reset Buttons -->
            <div class="flex items-end gap-2 pt-1 sm:pt-0">
                <button type="submit" style="background-color: #ea580c !important;" class="flex-1 py-2 px-4 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-xl transition text-center flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                    <i class="fa-solid fa-filter text-xs"></i>
                    <span>Filter</span>
                </button>
                <a href="{{ route('admin.ganti-jam.index', ['tab' => 'sessions']) }}" class="py-2 px-3 border border-slate-300 text-slate-600 hover:bg-slate-100 rounded-xl transition text-center shrink-0" title="Reset Filter (Hari Ini)">
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table Sesi Ganti Jam -->
    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 overflow-hidden">
        <div class="px-4 py-3 sm:px-5 sm:py-3.5 bg-slate-50/70 border-b border-slate-200/80 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-stopwatch text-orange-600"></i>
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Daftar Sesi Ganti Jam</span>
            </div>
            <span class="text-[11px] font-medium text-slate-500">Total: <strong>{{ $sessions->total() }}</strong> data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <th rowspan="2" class="py-3 px-3.5 text-left">Pemagang</th>
                        <th colspan="4" class="py-2 px-3 text-center border-x border-slate-200 bg-slate-200/50">Rekaman Waktu Ganti Jam</th>
                        <th rowspan="2" class="py-3 px-3.5 text-center">Akumulasi Ganti Jam</th>
                        <th rowspan="2" class="py-3 px-3.5 text-center">Status</th>
                        <th rowspan="2" class="py-3 px-3.5 text-center">Aksi</th>
                    </tr>
                    <tr class="bg-slate-100/60 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-2 px-2.5 text-center border-l border-slate-200">Masuk</th>
                        <th class="py-2 px-2.5 text-center">Istirahat</th>
                        <th class="py-2 px-2.5 text-center">Kembali</th>
                        <th class="py-2 px-2.5 text-center border-r border-slate-200">Pulang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($sessions as $session)
                    @php
                    $userProfile = $session->intern?->user?->profile;
                    $fullName = $userProfile?->full_name ?? $session->intern?->user?->username ?? 'Unknown';
                    $divisionName = $session->intern?->division?->name ?? '-';
                    $sessionDate = \Carbon\Carbon::parse($session->session_date)->locale('id')->isoFormat('D MMM Y');
                    $matchedReg = $session->matched_registration;
                    $sessionNotes = $session->notes ?? collect();
                    $sessionUnreadCount = $sessionNotes->where('is_from_admin', false)->where('is_read', false)->count();
                    $sessionNotesJson = $sessionNotes->map(function ($note) {
                        return [
                            'id' => $note->id,
                            'message' => $note->message,
                            'is_from_admin' => (bool) $note->is_from_admin,
                            'sender_name' => $note->user?->profile?->full_name ?? $note->user?->username ?? ($note->is_from_admin ? 'Admin' : 'Pemagang'),
                            'time' => $note->created_at ? $note->created_at->locale('id')->isoFormat('D MMM, HH:mm') : '-',
                        ];
                    })->values()->toArray();
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <!-- Pemagang & Divisi / Tanggal / Status Pendaftaran -->
                        <td class="py-3.5 px-3.5 align-middle">
                            <div class="font-bold text-slate-900 text-sm">{{ $fullName }}</div>
                            <div class="flex items-center gap-1.5 flex-wrap mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 bg-slate-100 text-slate-600 rounded-md text-[10px] font-semibold border border-slate-200">
                                    {{ $divisionName }}
                                </span>
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500">
                                    <i class="fa-regular fa-calendar text-[10px] text-slate-400"></i>
                                    {{ $sessionDate }}
                                </span>
                            </div>

                            {{-- Status Approval Pendaftaran --}}
                            <div class="mt-1.5 flex items-center gap-1 flex-wrap">
                                @if($matchedReg && in_array($matchedReg->status, ['approved', 'completed']))
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs" 
                                          title="Pendaftaran ganti jam telah disetujui admin{{ $matchedReg->shift ? ' (' . $matchedReg->shift->name . ')' : '' }}">
                                        <i class="fa-solid fa-circle-check text-emerald-600 text-[9px]"></i>
                                        <span>Pendaftaran Disetujui</span>
                                    </span>
                                @elseif($matchedReg && $matchedReg->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs" 
                                          title="Pemagang telah mendaftar namun belum disetujui admin saat sesi dimulai">
                                        <i class="fa-solid fa-clock text-amber-600 text-[9px]"></i>
                                        <span>Pendaftaran Belum Disetujui</span>
                                    </span>
                                @elseif($matchedReg && $matchedReg->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200 shadow-2xs" 
                                          title="Pendaftaran ganti jam pernah ditolak admin">
                                        <i class="fa-solid fa-circle-xmark text-rose-600 text-[9px]"></i>
                                        <span>Pendaftaran Ditolak</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200 shadow-2xs" 
                                          title="Pemagang memulai sesi ganti jam langsung tanpa pengajuan pendaftaran">
                                        <i class="fa-solid fa-triangle-exclamation text-amber-500 text-[9px]"></i>
                                        <span>Tanpa Pendaftaran</span>
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Masuk -->
                        <td class="py-3.5 px-2.5 text-center align-middle border-l border-slate-100 font-mono text-xs">
                            @php
                            $startTimeVal = $session->start_time ? substr($session->start_time, 0, 8) : '';
                            $startTimeDisplay = $session->start_time ? substr($session->start_time, 0, 5) : '---';
                            @endphp
                            <button type="button"
                                data-id="{{ $session->id }}"
                                data-field="start_time"
                                data-label="Masuk"
                                data-val="{{ $startTimeVal }}"
                                data-name="{{ $fullName }}"
                                data-date="{{ $sessionDate }}"
                                onclick="openEditTimeModal(this)"
                                class="inline-flex items-center gap-1 font-bold text-blue-600 hover:text-blue-800 hover:underline cursor-pointer group" title="Klik untuk edit jam masuk">
                                <span>{{ $startTimeDisplay }}</span>
                            </button>
                        </td>

                        <!-- Istirahat -->
                        <td class="py-3.5 px-2.5 text-center align-middle font-mono text-xs">
                            @php
                            $breakTimeVal = $session->break_time ? substr($session->break_time, 0, 8) : '';
                            $breakTimeDisplay = $session->break_time ? substr($session->break_time, 0, 5) : '---';
                            @endphp
                            <button type="button"
                                data-id="{{ $session->id }}"
                                data-field="break_time"
                                data-label="Mulai Istirahat"
                                data-val="{{ $breakTimeVal }}"
                                data-name="{{ $fullName }}"
                                data-date="{{ $sessionDate }}"
                                onclick="openEditTimeModal(this)"
                                class="inline-flex items-center gap-1 font-bold text-blue-600 hover:text-blue-800 hover:underline cursor-pointer group" title="Klik untuk edit jam mulai istirahat">
                                <span>{{ $breakTimeDisplay }}</span>
                            </button>
                        </td>

                        <!-- Kembali -->
                        <td class="py-3.5 px-2.5 text-center align-middle font-mono text-xs">
                            @php
                            $backTimeVal = $session->back_time ? substr($session->back_time, 0, 8) : '';
                            $backTimeDisplay = $session->back_time ? substr($session->back_time, 0, 5) : '---';
                            @endphp
                            <button type="button"
                                data-id="{{ $session->id }}"
                                data-field="back_time"
                                data-label="Selesai Istirahat"
                                data-val="{{ $backTimeVal }}"
                                data-name="{{ $fullName }}"
                                data-date="{{ $sessionDate }}"
                                onclick="openEditTimeModal(this)"
                                class="inline-flex items-center gap-1 font-bold text-blue-600 hover:text-blue-800 hover:underline cursor-pointer group" title="Klik untuk edit jam selesai istirahat">
                                <span>{{ $backTimeDisplay }}</span>
                            </button>
                        </td>

                        <!-- Pulang -->
                        <td class="py-3.5 px-2.5 text-center align-middle border-r border-slate-100 font-mono text-xs">
                            @php
                            $endTimeVal = $session->end_time ? substr($session->end_time, 0, 8) : '';
                            $endTimeDisplay = $session->end_time ? substr($session->end_time, 0, 5) : '---';
                            @endphp
                            <button type="button"
                                data-id="{{ $session->id }}"
                                data-field="end_time"
                                data-label="Pulang"
                                data-val="{{ $endTimeVal }}"
                                data-name="{{ $fullName }}"
                                data-date="{{ $sessionDate }}"
                                onclick="openEditTimeModal(this)"
                                class="inline-flex items-center gap-1 font-bold text-blue-600 hover:text-blue-800 hover:underline cursor-pointer group" title="Klik untuk edit jam pulang">
                                <span>{{ $endTimeDisplay }}</span>
                            </button>
                        </td>

                        <!-- Akumulasi Ganti Jam -->
                        <td class="py-3.5 px-3.5 text-center align-middle whitespace-nowrap">
                            @php
                            $workMins = (int) $session->total_work_minutes;
                            if ($session->status === 'active' && !empty($session->start_time) && empty($session->end_time)) {
                                $sessionDateStr = \Carbon\Carbon::parse($session->session_date)->format('Y-m-d');
                                $startDateTime = \Carbon\Carbon::parse($sessionDateStr . ' ' . $session->start_time, 'Asia/Jakarta');
                                $now = \Carbon\Carbon::now('Asia/Jakarta');
                                $gross = max(0, $now->diffInMinutes($startDateTime));
                                $breakMins = (int) $session->total_break_minutes;
                                if (!empty($session->break_time) && empty($session->back_time)) {
                                    $breakStart = \Carbon\Carbon::parse($sessionDateStr . ' ' . $session->break_time, 'Asia/Jakarta');
                                    $breakMins = max(0, $now->diffInMinutes($breakStart));
                                }
                                $workMins = max(0, $gross - $breakMins);
                            }
                            // Maksimal akumulasi ganti jam dibatasi 7 jam 15 menit (435 menit)
                            $workMins = min(435, $workMins);
                            $workHours = floor($workMins / 60);
                            $workRemainderMins = $workMins % 60;
                            $accumulationFormatted = sprintf('%02d:%02d:00', $workHours, $workRemainderMins);
                            @endphp
                            <div class="flex flex-col items-center justify-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-orange-50 border border-orange-200 text-orange-900 font-mono font-bold text-xs sm:text-sm shadow-2xs">
                                    <i class="fa-solid fa-stopwatch text-orange-600"></i>
                                    <span>{{ $accumulationFormatted }}</span>
                                </span>
                                @if($session->status === 'active')
                                <span class="text-[10px] text-blue-600 font-semibold mt-1 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                    <span>Sedang Berjalan</span>
                                </span>
                                @endif
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="py-3.5 px-3.5 text-center align-middle whitespace-nowrap">
                            @if($session->status === 'approved')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <i class="fa-solid fa-check text-[10px]"></i> Disetujui
                            </span>
                            @elseif($session->status === 'pending_approval')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                <i class="fa-solid fa-hourglass-half text-[10px]"></i> Menunggu ACC
                            </span>
                            @elseif($session->status === 'rejected')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                <i class="fa-solid fa-xmark text-[10px]"></i> Ditolak
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                <i class="fa-solid fa-bolt text-[10px]"></i> Aktif
                            </span>
                            @endif
                        </td>

                        <!-- Aksi -->
                        <td class="py-3.5 px-3.5 text-center align-middle whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- Tombol Chat / Tanya Jawab Sesi Ganti Jam (Hanya untuk sesi yang belum selesai / ditolak / disetujui) --}}
                                @if(!in_array($session->status, ['approved', 'rejected']))
                                <button type="button"
                                    id="btn-chat-session-{{ $session->id }}"
                                    data-id="{{ $session->id }}"
                                    data-name="{{ $fullName }}"
                                    data-reason="{{ e($session->start_time_message ?? '-') }}"
                                    data-created="{{ $session->created_at ? $session->created_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') : '-' }}"
                                    data-has-unread="{{ $sessionUnreadCount > 0 ? 'true' : 'false' }}"
                                    data-notes='{{ json_encode($sessionNotesJson) }}'
                                    onclick="openChatSessionModal(this)"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-orange-50 hover:bg-orange-600 text-orange-600 hover:text-white border border-orange-200 hover:border-orange-600 transition shadow-2xs cursor-pointer relative"
                                    title="Tanya Jawab / Chat Sesi Ganti Jam">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-.856-.856 5.97 5.97 0 01.405-2.035C3.398 16.58 3 14.39 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                                    </svg>
                                    <span id="chat-dot-session-{{ $session->id }}" class="chat-dot-session absolute -top-1 -right-1 w-2.5 h-2.5 bg-rose-500 rounded-full border-2 border-white animate-pulse {{ $sessionUnreadCount > 0 ? '' : 'hidden' }}"></span>
                                </button>
                                @endif

                                @if($session->status === 'pending_approval' || ($session->status === 'active' && !empty($session->end_time)))
                                <button type="button"
                                    data-id="{{ $session->id }}"
                                    data-name="{{ $fullName }}"
                                    data-date="{{ $sessionDate }}"
                                    data-time="{{ ($session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('H:i') : '-') . ' - ' . ($session->end_time ? \Carbon\Carbon::parse($session->end_time)->format('H:i') : '-') }}"
                                    data-duration="{{ $accumulationFormatted }}"
                                    onclick="openApproveSessionModal(this)"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-2xs cursor-pointer"
                                    title="Setujui Sesi Ganti Jam">
                                    <i class="fa-solid fa-check text-xs"></i> Setujui
                                </button>
                                @endif

                                @if(in_array($session->status, ['pending_approval', 'active']))
                                <button type="button"
                                    data-id="{{ $session->id }}"
                                    data-name="{{ $fullName }}"
                                    data-date="{{ $sessionDate }}"
                                    onclick="openRejectModal(this)"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-2xs cursor-pointer">
                                    <i class="fa-solid fa-xmark text-xs"></i> Tolak
                                </button>
                                @endif

                                <!-- Tombol Hapus Sesi Ganti Jam (Icon Saja dengan Konfirmasi Popup, Tidak Ditampilkan pada Sesi Aktif Berjalan) -->
                                @if($session->status !== 'active')
                                <button type="button"
                                    data-id="{{ $session->id }}"
                                    data-name="{{ $fullName }}"
                                    data-date="{{ $sessionDate }}"
                                    data-time="{{ ($session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('H:i') : '-') . ' - ' . ($session->end_time ? \Carbon\Carbon::parse($session->end_time)->format('H:i') : '-') }}"
                                    data-status="{{ $session->status }}"
                                    data-status-label="{{ $session->status === 'approved' ? 'Disetujui' : ($session->status === 'rejected' ? 'Ditolak' : ($session->status === 'pending_approval' ? 'Pending' : 'Aktif')) }}"
                                    onclick="openDeleteSessionModal(this)"
                                    class="p-2 rounded-xl bg-white hover:bg-rose-50 text-slate-400 hover:text-rose-600 border border-slate-200 transition shadow-2xs cursor-pointer flex items-center justify-center"
                                    title="Hapus Sesi Ganti Jam">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <i class="fa-regular fa-folder-open text-3xl mb-2 text-slate-300 block"></i>
                            <span class="font-medium text-sm">Tidak ada data sesi ganti jam.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $sessions->links() }}
        </div>
        @endif
    </div>
    @endif

</main>

<!-- ==================================================================== -->
<!-- POPUP MODAL: DISKUSI & PESAN PENDAFTARAN GANTI JAM (ADMIN)           -->
<!-- ==================================================================== -->
<div id="chatRegModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden z-[9999] p-2.5 pb-7 sm:p-4" onclick="if(event.target === this) closeChatRegModal();">
    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-full max-w-lg mx-auto relative flex flex-col max-h-[85vh] sm:max-h-[88vh] mb-2 sm:mb-0 overflow-hidden border border-slate-100 animate-fadeIn" onclick="event.stopPropagation();">
        <!-- Header -->
        <div class="p-3 sm:p-4.5 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/70">
            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 pr-2">
                <div class="p-1.5 sm:p-2.5 bg-orange-100 text-orange-600 rounded-xl shrink-0">
                    <i class="fa-solid fa-comments text-base sm:text-lg"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 truncate" id="chat-reg-title">Diskusi Ganti Jam</h3>
                    <p class="text-[11px] sm:text-xs text-slate-500 truncate" id="chat-reg-subtitle">Komunikasi dua arah dengan pemagang</p>
                </div>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-600 transition p-1.5 rounded-lg hover:bg-slate-200/50 cursor-pointer shrink-0" onclick="closeChatRegModal()">
                <i class="fa-solid fa-xmark text-base sm:text-lg"></i>
            </button>
        </div>

        <!-- Tanggal, Shift & Catatan Pemagang Info Card -->
        <div class="px-3.5 sm:px-5 py-2.5 shrink-0 bg-amber-50/60 border-b border-amber-100 space-y-2">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                <div class="flex items-center gap-1.5 font-bold text-slate-800">
                    <i class="fa-regular fa-calendar-days text-amber-600 text-[11px]"></i>
                    <span id="chat-reg-date-display">-</span>
                </div>
                <div class="flex items-center gap-1.5 font-semibold text-blue-800">
                    <i class="fa-solid fa-business-time text-blue-600 text-[10px]"></i>
                    <span id="chat-reg-shift-display">-</span>
                </div>
            </div>
            <div class="pt-1.5 border-t border-amber-200/60">
                <div class="text-[11px] font-bold text-amber-900 flex items-center justify-between mb-1">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-regular fa-note-sticky text-amber-600"></i>
                        <span>Catatan Pengajuan Pemagang:</span>
                    </span>
                    <span class="text-[10px] text-amber-700 font-normal shrink-0 ml-2" id="chat-reg-created">-</span>
                </div>
                <p class="text-xs text-slate-700 italic bg-white/95 p-2 sm:p-2.5 rounded-xl border border-amber-200/70 max-h-20 sm:max-h-28 overflow-y-auto whitespace-pre-line break-words leading-relaxed" id="chat-reg-reason">-</p>
            </div>
        </div>

        <!-- Chat History List -->
        <div id="chat-reg-messages" class="flex-1 overflow-y-auto p-3.5 sm:p-5 space-y-2.5 sm:space-y-3 min-h-[120px] max-h-[40vh] sm:max-h-[320px] bg-slate-50/30">
            <!-- Dynamically populated by JS -->
        </div>

        <!-- Form Kirim Pesan -->
        <div class="p-3 sm:p-4.5 pb-4 sm:pb-4.5 border-t border-slate-200/80 bg-white shrink-0">
            <form id="chat-reg-form" method="POST" action="">
                @csrf
                <div class="space-y-2 sm:space-y-2.5">
                    <label for="chat_reg_input" class="block font-bold text-slate-700 text-[11px] sm:text-xs">
                        Kirim Pesan / Pertanyaan ke Pemagang:
                    </label>
                    <textarea id="chat_reg_input" name="message" rows="1" required
                        placeholder="Contoh: Kamu berencana ganti jam di kantor mana dan shift jam berapa?..."
                        class="w-full px-2.5 py-1.5 sm:p-2.5 border border-slate-300 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none text-[11px] sm:text-xs transition leading-normal sm:leading-relaxed resize-none min-h-[36px] sm:min-h-[50px]"></textarea>
                    
                    <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-between gap-2 pt-1 border-t border-slate-100">
                        <button type="button" onclick="closeChatRegModal()" class="w-full sm:w-auto px-3.5 py-1.5 sm:py-2 border border-slate-300 text-slate-600 rounded-lg sm:rounded-xl hover:bg-slate-100 text-[11px] sm:text-xs font-semibold transition cursor-pointer text-center">
                            Tutup
                        </button>
                        <div class="flex items-center gap-2 justify-end w-full sm:w-auto">
                            <button type="submit"
                                style="background-color: #ea580c !important; color: #ffffff !important;"
                                class="w-full sm:w-auto px-4 py-1.5 sm:py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-lg sm:rounded-xl text-[11px] sm:text-xs transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-paper-plane text-xs"></i>
                                <span>Kirim Pesan</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================================================================== -->
<!-- POPUP MODAL: TANYA JAWAB SESI GANTI JAM (ADMIN)                      -->
<!-- ==================================================================== -->
<div id="chatSessionModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden z-[9999] p-2.5 pb-7 sm:p-4" onclick="if(event.target === this) closeChatSessionModal();">
    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-full max-w-lg mx-auto relative flex flex-col max-h-[85vh] sm:max-h-[88vh] mb-2 sm:mb-0 overflow-hidden border border-slate-100 animate-fadeIn" onclick="event.stopPropagation();">
        <!-- Header -->
        <div class="p-3 sm:p-4.5 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/70">
            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 pr-2">
                <div class="p-1.5 sm:p-2.5 bg-orange-100 text-orange-600 rounded-xl shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-.856-.856 5.97 5.97 0 01.405-2.035C3.398 16.58 3 14.39 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 truncate" id="chat-session-title">Tanya Jawab Sesi Ganti Jam</h3>
                    <p class="text-[11px] sm:text-xs text-slate-500 truncate" id="chat-session-subtitle">Komunikasi dua arah saat sesi berjalan</p>
                </div>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-600 transition p-1.5 rounded-lg hover:bg-slate-200/50 cursor-pointer shrink-0" onclick="closeChatSessionModal()">
                <i class="fa-solid fa-xmark text-base sm:text-lg"></i>
            </button>
        </div>

        <!-- Info Sesi / Catatan Awal Pemagang -->
        <div class="px-3.5 sm:px-5 py-2 sm:py-2.5 shrink-0 bg-orange-50/50 border-b border-orange-100">
            <div class="text-[11px] font-bold text-orange-950 flex items-center justify-between mb-1">
                <span class="flex items-center gap-1.5">
                    <i class="fa-regular fa-note-sticky text-orange-600"></i>
                    <span>Keterangan Awal Masuk Pemagang:</span>
                </span>
                <span class="text-[10px] text-orange-700 font-normal shrink-0 ml-2" id="chat-session-created">-</span>
            </div>
            <p class="text-xs text-slate-700 italic bg-white/90 p-2 sm:p-2.5 rounded-xl border border-orange-200/70 max-h-20 sm:max-h-28 overflow-y-auto whitespace-pre-line break-words leading-relaxed" id="chat-session-reason">-</p>
        </div>

        <!-- Chat History List -->
        <div id="chat-session-messages" class="flex-1 overflow-y-auto p-3.5 sm:p-5 space-y-2.5 sm:space-y-3 min-h-[120px] max-h-[40vh] sm:max-h-[320px] bg-slate-50/30">
            <!-- Dynamically populated by JS -->
        </div>

        <!-- Form Kirim Pesan -->
        <div class="p-3 sm:p-4.5 pb-4 sm:pb-4.5 border-t border-slate-200/80 bg-white shrink-0">
            <form id="chat-session-form" method="POST" action="">
                @csrf
                <div class="space-y-2 sm:space-y-2.5">
                    <label for="chat_session_input" class="block font-bold text-slate-700 text-[11px] sm:text-xs">
                        Kirim Pesan / Pertanyaan ke Pemagang:
                    </label>
                    <textarea id="chat_session_input" name="message" rows="1" required
                        placeholder="Tulis pesan atau pertanyaan untuk pemagang yang sedang ganti jam..."
                        class="w-full px-2.5 py-1.5 sm:p-2.5 border border-slate-300 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none text-[11px] sm:text-xs transition leading-normal sm:leading-relaxed resize-none min-h-[36px] sm:min-h-[50px]"></textarea>
                    
                    <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-between gap-2 pt-1 border-t border-slate-100">
                        <button type="button" onclick="closeChatSessionModal()" class="w-full sm:w-auto px-3.5 py-1.5 sm:py-2 border border-slate-300 text-slate-600 rounded-lg sm:rounded-xl hover:bg-slate-100 text-[11px] sm:text-xs font-semibold transition cursor-pointer text-center">
                            Tutup
                        </button>
                        <div class="flex items-center gap-2 justify-end w-full sm:w-auto">
                            <button type="submit"
                                style="background-color: #ea580c !important; color: #ffffff !important;"
                                class="w-full sm:w-auto px-4 py-1.5 sm:py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-lg sm:rounded-xl text-[11px] sm:text-xs transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-paper-plane text-xs"></i>
                                <span>Kirim Pesan</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================================================================== -->
<!-- POPUP MODAL: SETUJUI PRA-PENDAFTARAN GANTI JAM                       -->
<!-- ==================================================================== -->
<div id="approveRegModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden z-[9999] p-4" onclick="if(event.target === this) closeApproveRegModal();">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-2xl w-full max-w-lg mx-auto relative animate-in fade-in duration-200" onclick="event.stopPropagation();">
        <div class="flex items-center gap-3 mb-3.5 pb-3 border-b border-slate-100">
            <div class="p-2.5 bg-emerald-100 text-emerald-600 rounded-xl shrink-0">
                <i class="fa-solid fa-calendar-check text-lg"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-base font-bold text-slate-900">Setujui Pendaftaran Ganti Jam</h3>
                <p class="text-xs text-slate-500 truncate" id="approve-reg-subtitle">Konfirmasi persetujuan untuk pemagang.</p>
            </div>
        </div>

        <form id="approve-reg-form" method="POST" action="">
            @csrf
            <div class="space-y-3.5 text-xs">
                <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 leading-snug">
                    <p class="text-xs text-emerald-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-info text-emerald-600"></i>
                        <span>Sesuaikan tanggal, shift, atau kantor hasil diskusi jika diperlukan.</span>
                    </p>
                </div>

                <!-- Tanggal Rencana Ganti Jam -->
                <div>
                    <label for="approve_reg_date" class="block font-bold text-slate-700 mb-1">
                        Tanggal Rencana Ganti Jam <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="requested_date" id="approve_reg_date" required
                        min="{{ \Carbon\Carbon::tomorrow('Asia/Jakarta')->toDateString() }}"
                        class="w-full p-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 text-xs bg-white">
                </div>

                <!-- Grid Shift & Kantor -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Shift -->
                    <div>
                        <label for="approve_reg_shift" class="block font-bold text-slate-700 mb-1">
                            Shift Kerja <span class="text-rose-500">*</span>
                        </label>
                        <select name="shift_id" id="approve_reg_shift" required
                            class="w-full p-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 text-xs bg-white cursor-pointer">
                            <option value="" disabled>-- Pilih Shift --</option>
                            @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">
                                {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kantor / Lokasi -->
                    <div>
                        <label for="approve_reg_office" class="block font-bold text-slate-700 mb-1">
                            Kantor / Lokasi <span class="text-rose-500">*</span>
                        </label>
                        <select name="office_id" id="approve_reg_office" required
                            class="w-full p-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 text-xs bg-white cursor-pointer">
                            <option value="" disabled>-- Pilih Kantor --</option>
                            @foreach($offices as $off)
                            <option value="{{ $off->id }}">
                                {{ $off->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Catatan Admin (Opsional) -->
                <div>
                    <label for="approve_reg_notes" class="block font-bold text-slate-700 mb-1">Catatan Admin (Opsional)</label>
                    <input type="text" name="admin_notes" id="approve_reg_notes" placeholder="Contoh: Disetujui, koordinasikan dengan mentor saat hadir..."
                        class="w-full p-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 text-xs">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3.5 border-t border-slate-100 mt-4">
                <button type="button" onclick="closeApproveRegModal()" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-100 text-xs font-semibold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>Sahkan Persetujuan (ACC)</span>
                </button>
            </div>
        </form>

        <button type="button" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer" onclick="closeApproveRegModal()">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>
</div>

<!-- ==================================================================== -->
<!-- POPUP MODAL: TOLAK PRA-PENDAFTARAN GANTI JAM                         -->
<!-- ==================================================================== -->
<div id="rejectRegModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden z-[9999] p-4" onclick="if(event.target === this) closeRejectRegModal();">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-2xl w-full max-w-md mx-auto relative" onclick="event.stopPropagation();">
        <div class="flex items-center gap-3 mb-3.5 pb-3 border-b border-slate-100">
            <div class="p-2.5 bg-rose-100 text-rose-600 rounded-xl shrink-0">
                <i class="fa-solid fa-calendar-xmark text-lg"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-base font-bold text-slate-900">Tolak Pra-Pendaftaran</h3>
                <p class="text-xs text-slate-500 truncate" id="reject-reg-subtitle">Berikan alasan penolakan untuk pemagang.</p>
            </div>
        </div>

        <form id="reject-reg-form" method="POST" action="">
            @csrf
            <div class="space-y-3 text-xs">
                <div>
                    <label for="reject_reg_notes" class="block font-bold text-slate-700 mb-1">
                        Alasan Penolakan <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="reject_reg_notes" name="admin_notes" rows="3" required
                        placeholder="Contoh: Kantor tutup pada tanggal tersebut atau kuota pembimbing penuh..."
                        class="w-full p-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:outline-none text-xs"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3.5 border-t border-slate-100 mt-4">
                <button type="button" onclick="closeRejectRegModal()" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-100 text-xs font-semibold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-xs cursor-pointer">
                    Tolak Pendaftaran
                </button>
            </div>
        </form>

        <button type="button" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer" onclick="closeRejectRegModal()">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>
</div>

<!-- ==================================================================== -->
<!-- POPUP MODAL: KONFIRMASI HAPUS PENDAFTARAN GANTI JAM (ADMIN)          -->
<!-- ==================================================================== -->
<div id="deleteRegModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden z-[9999] p-4" onclick="if(event.target === this) closeDeleteRegModal();">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-2xl w-full max-w-md mx-auto relative animate-in fade-in duration-200" onclick="event.stopPropagation();">
        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
            <div class="p-2.5 bg-rose-100 text-rose-600 rounded-xl shrink-0">
                <i class="fa-solid fa-trash-can text-lg"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-base font-bold text-slate-900">Hapus Pendaftaran Ganti Jam</h3>
                <p class="text-xs text-slate-500 truncate" id="delete-reg-subtitle">Konfirmasi penghapusan data pendaftaran.</p>
            </div>
        </div>

        <!-- Warning Box for Approved Status -->
        <div id="delete-reg-approved-warning" class="hidden mb-3.5 p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-900 text-xs leading-relaxed">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base shrink-0"></i>
                <div>
                    <span class="font-bold text-rose-950">Jadwal Sudah Disetujui:</span>
                    <span> Menghapus data ini juga akan membatalkan sesi ganti jam yang telah dijadwalkan.</span>
                </div>
            </div>
        </div>

        <!-- Detail Pemagang Info Box -->
        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 text-xs space-y-2 mb-4">
            <div class="flex justify-between items-center pb-1.5 border-b border-slate-200/60">
                <span class="text-slate-500 font-medium">Pemagang:</span>
                <span class="font-bold text-slate-800 text-right" id="delete-reg-name">-</span>
            </div>
            <div class="flex justify-between items-center pb-1.5 border-b border-slate-200/60">
                <span class="text-slate-500 font-medium">Tanggal Pelaksanaan:</span>
                <span class="font-semibold text-slate-700 text-right" id="delete-reg-date">-</span>
            </div>
            <div class="flex justify-between items-center pb-1.5 border-b border-slate-200/60">
                <span class="text-slate-500 font-medium">Shift & Kantor:</span>
                <span class="font-semibold text-slate-700 text-right" id="delete-reg-shift">-</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-500 font-medium">Status Saat Ini:</span>
                <span class="font-bold text-right" id="delete-reg-status-badge">-</span>
            </div>
        </div>

        <p class="text-xs text-slate-600 mb-4 leading-relaxed">
            Apakah Anda yakin ingin melanjutkan penghapusan data ini? Tindakan ini tidak dapat dibatalkan.
        </p>

        <form id="delete-reg-form" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeDeleteRegModal()" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-100 text-xs font-semibold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                    <span>Ya, Hapus Pendaftaran</span>
                </button>
            </div>
        </form>

        <button type="button" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer" onclick="closeDeleteRegModal()">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Sesi Ganti Jam -->
<div id="deleteSessionModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden z-[9999] p-4" onclick="if(event.target === this) closeDeleteSessionModal();">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-2xl w-full max-w-md mx-auto relative animate-in fade-in duration-200" onclick="event.stopPropagation();">
        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
            <div class="p-2.5 bg-rose-100 text-rose-600 rounded-xl shrink-0">
                <i class="fa-solid fa-trash-can text-lg"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-base font-bold text-slate-900">Hapus Sesi Ganti Jam</h3>
                <p class="text-xs text-slate-500 truncate" id="delete-session-subtitle">Konfirmasi penghapusan data sesi.</p>
            </div>
        </div>

        <!-- Detail Sesi Info Box -->
        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 text-xs space-y-2 mb-4">
            <div class="flex justify-between items-center pb-1.5 border-b border-slate-200/60">
                <span class="text-slate-500 font-medium">Pemagang:</span>
                <span class="font-bold text-slate-800 text-right" id="delete-session-name">-</span>
            </div>
            <div class="flex justify-between items-center pb-1.5 border-b border-slate-200/60">
                <span class="text-slate-500 font-medium">Tanggal Sesi:</span>
                <span class="font-semibold text-slate-700 text-right" id="delete-session-date">-</span>
            </div>
            <div class="flex justify-between items-center pb-1.5 border-b border-slate-200/60">
                <span class="text-slate-500 font-medium">Jam Ganti Jam:</span>
                <span class="font-semibold text-slate-700 text-right" id="delete-session-time">-</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-500 font-medium">Status Saat Ini:</span>
                <span class="font-bold text-right" id="delete-session-status-badge">-</span>
            </div>
        </div>

        <p class="text-xs text-slate-600 mb-4 leading-relaxed">
            Apakah Anda yakin ingin menghapus data sesi ganti jam ini? Tindakan ini tidak dapat dibatalkan.
        </p>

        <form id="delete-session-form" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeDeleteSessionModal()" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-100 text-xs font-semibold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                    <span>Ya, Hapus Sesi</span>
                </button>
            </div>
        </form>

        <button type="button" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer" onclick="closeDeleteSessionModal()">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>
</div>

<!-- Modal Konfirmasi Setujui Sesi Ganti Jam -->
<div id="approveSessionModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden z-[9999] p-4" onclick="if(event.target === this) closeApproveSessionModal();">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-2xl w-full max-w-md mx-auto relative animate-in fade-in duration-200" onclick="event.stopPropagation();">
        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
            <div class="p-2.5 bg-emerald-100 text-emerald-600 rounded-xl shrink-0">
                <i class="fa-solid fa-circle-check text-lg"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-base font-bold text-slate-900">Setujui Sesi Ganti Jam</h3>
                <p class="text-xs text-slate-500 truncate" id="approve-session-subtitle">Konfirmasi persetujuan dan pelunasan hutang.</p>
            </div>
        </div>

        <!-- Detail Sesi Info Box -->
        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 text-xs space-y-2 mb-4">
            <div class="flex justify-between items-center pb-1.5 border-b border-slate-200/60">
                <span class="text-slate-500 font-medium">Pemagang:</span>
                <span class="font-bold text-slate-800 text-right" id="approve-session-name">-</span>
            </div>
            <div class="flex justify-between items-center pb-1.5 border-b border-slate-200/60">
                <span class="text-slate-500 font-medium">Tanggal Sesi:</span>
                <span class="font-semibold text-slate-700 text-right" id="approve-session-date">-</span>
            </div>
            <div class="flex justify-between items-center pb-1.5 border-b border-slate-200/60">
                <span class="text-slate-500 font-medium">Jam Ganti Jam:</span>
                <span class="font-semibold text-slate-700 text-right" id="approve-session-time">-</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-500 font-medium">Akumulasi Jam (Maks 07:15):</span>
                <span class="font-bold text-emerald-700 font-mono text-right" id="approve-session-duration">-</span>
            </div>
        </div>

        <p class="text-xs text-slate-600 mb-4 leading-relaxed">
            Apakah Anda yakin ingin menyetujui sesi ganti jam ini? Sistem akan memproses pelunasan jadwal hutang secara otomatis.
        </p>

        <form id="approve-session-form" method="POST" action="">
            @csrf
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeApproveSessionModal()" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-100 text-xs font-semibold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-check text-xs"></i>
                    <span>Ya, Setujui Sesi</span>
                </button>
            </div>
        </form>

        <button type="button" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer" onclick="closeApproveSessionModal()">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>
</div>

<!-- ==================================================================== -->
<!-- POPUP MODAL: TOLAK SESI GANTI JAM (ADMIN)                            -->
<!-- ==================================================================== -->
<div id="rejectSessionModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden z-[9999] p-4" onclick="if(event.target === this) closeRejectModal();">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-2xl w-full max-w-md mx-auto relative" onclick="event.stopPropagation();">
        <div class="flex items-center gap-3 mb-3.5 pb-3 border-b border-slate-100">
            <div class="p-2.5 bg-rose-100 text-rose-600 rounded-xl shrink-0">
                <i class="fa-solid fa-clock-rotate-left text-lg"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-base font-bold text-slate-900">Tolak Sesi Ganti Jam</h3>
                <p class="text-xs text-slate-500 truncate" id="reject-modal-subtitle">Berikan alasan penolakan untuk pemagang.</p>
            </div>
        </div>

        <form id="reject-session-form" method="POST" action="">
            @csrf
            <div class="space-y-3 text-xs">
                <div>
                    <label for="rejection_note" class="block font-bold text-slate-700 mb-1">
                        Alasan Penolakan <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="rejection_note" name="rejection_note" rows="3" required
                        placeholder="Contoh: Durasi kerja kurang dari target hutang yang ditentukan atau lokasi presensi tidak valid..."
                        class="w-full p-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:outline-none text-xs"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3.5 border-t border-slate-100 mt-4">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-100 text-xs font-semibold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-xs cursor-pointer">
                    Tolak Sesi
                </button>
            </div>
        </form>

        <button type="button" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer" onclick="closeRejectModal()">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>
</div>

<!-- ==================================================================== -->
<!-- POPUP MODAL: EDIT WAKTU SESI GANTI JAM (ADMIN)                       -->
<!-- ==================================================================== -->
<div id="editTimeModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-xs hidden z-[9999] p-4" onclick="if(event.target === this) closeEditTimeModal();">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-2xl w-full max-w-sm mx-auto relative" onclick="event.stopPropagation();">
        <div class="flex items-center gap-3 mb-3.5 pb-3 border-b border-slate-100">
            <div class="p-2.5 bg-blue-100 text-blue-600 rounded-xl shrink-0">
                <i class="fa-regular fa-clock text-lg"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-base font-bold text-slate-900">Edit Waktu Presensi</h3>
                <p class="text-xs text-slate-500 truncate" id="edit-time-subtitle">Ubah jam presensi pemagang.</p>
            </div>
        </div>

        <p class="bg-blue-50 text-blue-900 border border-blue-200 p-3 rounded-xl mb-4 text-xs leading-relaxed">
            Anda akan mengubah jam <strong id="edit-time-field-label">Masuk</strong> pada tanggal
            <strong id="edit-time-date-display">---</strong> atas nama:
            <strong id="edit-time-name-display">---</strong>
        </p>

        <form id="edit-time-form" method="POST" action="">
            @csrf
            <input type="hidden" name="field" id="edit-time-field-name">
            <div class="mb-4">
                <label for="edit_time_input" class="block text-slate-700 text-xs font-bold uppercase tracking-wider mb-1.5">Waktu (Jam : Menit : Detik):</label>
                <input type="time" name="time" id="edit_time_input" step="1"
                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm font-mono"
                    required>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditTimeModal()"
                    class="px-4 py-2 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-100 text-xs font-semibold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition shadow-xs cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>

        <button type="button" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer" onclick="closeEditTimeModal()">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>
</div>

<script>
    let activeOpenedSessionId = null;
    let activeOpenedRegId = null;

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function openChatRegModal(btn) {
        const id = btn.dataset.id;
        const name = btn.dataset.name || 'Pemagang';
        const date = btn.dataset.date || 'Belum ditentukan';
        const shift = btn.dataset.shift || 'Bebas Shift';
        const office = btn.dataset.office || '';
        const reason = btn.dataset.reason || '-';
        const created = btn.dataset.created || '-';
        const hasUnread = btn.dataset.hasUnread === 'true';
        let notes = [];
        try {
            notes = JSON.parse(btn.dataset.notes || '[]');
        } catch (e) {
            notes = [];
        }

        const modal = document.getElementById('chatRegModal');
        const form = document.getElementById('chat-reg-form');
        const titleEl = document.getElementById('chat-reg-title');
        const subtitleEl = document.getElementById('chat-reg-subtitle');
        const dateEl = document.getElementById('chat-reg-date-display');
        const shiftEl = document.getElementById('chat-reg-shift-display');
        const createdEl = document.getElementById('chat-reg-created');
        const reasonEl = document.getElementById('chat-reg-reason');
        const msgContainer = document.getElementById('chat-reg-messages');
        const inputEl = document.getElementById('chat_reg_input');

        if (form) form.action = `/admin/ganti-jam/registrations/${id}/send-note`;
        if (titleEl) titleEl.textContent = `Diskusi Ganti Jam - ${name}`;
        if (subtitleEl) subtitleEl.textContent = `Komunikasi dua arah dengan pemagang`;
        if (dateEl) dateEl.textContent = date;
        if (shiftEl) shiftEl.textContent = office ? `${shift} • ${office}` : shift;
        if (createdEl) createdEl.textContent = created;
        if (reasonEl) reasonEl.textContent = `"${reason}"`;
        if (inputEl) inputEl.value = '';

        // Render messages
        if (msgContainer) {
            msgContainer.innerHTML = '';
            if (notes.length === 0) {
                msgContainer.innerHTML = `
                    <div class="py-8 text-center text-slate-400">
                        <i class="fa-regular fa-comments text-3xl mb-2 text-slate-300 block"></i>
                        <span class="text-xs font-medium">Belum ada riwayat diskusi. Tulis pesan di bawah untuk menanyakan ke pemagang.</span>
                    </div>
                `;
            } else {
                notes.forEach(note => {
                    const bubble = document.createElement('div');
                    if (note.is_from_admin) {
                        bubble.className = 'flex flex-col items-end';
                        bubble.innerHTML = `
                            <div style="background-color: #ea580c !important; color: #ffffff !important;" class="bg-orange-600 text-white rounded-2xl rounded-tr-xs px-3 sm:px-3.5 py-1.5 sm:py-2 max-w-[88%] sm:max-w-[82%] text-xs shadow-2xs">
                                <div class="font-bold text-[10px] text-orange-100 mb-0.5">${escapeHtml(note.sender_name)} (Admin)</div>
                                <div class="whitespace-pre-wrap leading-relaxed break-words">${escapeHtml(note.message)}</div>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-0.5">${escapeHtml(note.time)}</span>
                        `;
                    } else {
                        bubble.className = 'flex flex-col items-start';
                        bubble.innerHTML = `
                            <div class="bg-white text-slate-800 rounded-2xl rounded-tl-xs px-3 sm:px-3.5 py-1.5 sm:py-2 max-w-[88%] sm:max-w-[82%] text-xs border border-slate-200/80 shadow-2xs">
                                <div class="font-bold text-[10px] text-slate-600 mb-0.5">${escapeHtml(note.sender_name)}</div>
                                <div class="whitespace-pre-wrap leading-relaxed break-words">${escapeHtml(note.message)}</div>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-0.5">${escapeHtml(note.time)}</span>
                        `;
                    }
                    msgContainer.appendChild(bubble);
                });
            }
            msgContainer.scrollTop = msgContainer.scrollHeight;
        }

        // Auto mark as read if has unread
        if (hasUnread) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || document.querySelector('input[name="_token"]')?.value;
            fetch(`/admin/ganti-jam/registrations/${id}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            }).then(() => {
                btn.dataset.hasUnread = 'false';
                const badge = document.getElementById(`chat-dot-reg-${id}`) || btn.querySelector('.chat-dot-reg');
                if (badge) badge.classList.add('hidden');
            }).catch(e => console.error(e));
        }

        activeOpenedRegId = parseInt(id, 10);
        if (window.gantiJamChatNotifier) {
            window.gantiJamChatNotifier.stopSessionChatSoundLoop();
        }

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                if (inputEl) inputEl.focus();
                if (msgContainer) msgContainer.scrollTop = msgContainer.scrollHeight;
            }, 100);
        }
    }

    function closeChatRegModal() {
        activeOpenedRegId = null;
        const modal = document.getElementById('chatRegModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function openChatSessionModal(btn) {
        const id = btn.dataset.id;
        const name = btn.dataset.name || 'Pemagang';
        const reason = btn.dataset.reason || '-';
        const created = btn.dataset.created || '-';
        const hasUnread = btn.dataset.hasUnread === 'true';
        let notes = [];
        try {
            notes = JSON.parse(btn.dataset.notes || '[]');
        } catch (e) {
            notes = [];
        }

        const modal = document.getElementById('chatSessionModal');
        const form = document.getElementById('chat-session-form');
        const titleEl = document.getElementById('chat-session-title');
        const subtitleEl = document.getElementById('chat-session-subtitle');
        const createdEl = document.getElementById('chat-session-created');
        const reasonEl = document.getElementById('chat-session-reason');
        const msgContainer = document.getElementById('chat-session-messages');
        const inputEl = document.getElementById('chat_session_input');

        if (form) form.action = `/admin/persetujuan-ganti-jam/${id}/send-note`;
        if (titleEl) titleEl.textContent = `Tanya Jawab Sesi Ganti Jam - ${name}`;
        if (subtitleEl) subtitleEl.textContent = `Komunikasi dua arah saat sesi berjalan`;
        if (createdEl) createdEl.textContent = created;
        if (reasonEl) reasonEl.textContent = `"${reason}"`;
        if (inputEl) inputEl.value = '';

        // Render messages
        if (msgContainer) {
            msgContainer.innerHTML = '';
            if (notes.length === 0) {
                msgContainer.innerHTML = `
                    <div class="py-8 text-center text-slate-400">
                        <i class="fa-regular fa-comments text-3xl mb-2 text-slate-300 block"></i>
                        <span class="text-xs font-medium">Belum ada riwayat diskusi. Tulis pesan di bawah untuk menanyakan ke pemagang.</span>
                    </div>
                `;
            } else {
                notes.forEach(note => {
                    const bubble = document.createElement('div');
                    if (note.is_from_admin) {
                        bubble.className = 'flex flex-col items-end';
                        bubble.innerHTML = `
                            <div style="background-color: #ea580c !important; color: #ffffff !important;" class="bg-orange-600 text-white rounded-2xl rounded-tr-xs px-3 sm:px-3.5 py-1.5 sm:py-2 max-w-[88%] sm:max-w-[82%] text-xs shadow-2xs">
                                <div class="font-bold text-[10px] text-orange-100 mb-0.5">${escapeHtml(note.sender_name)} (Admin)</div>
                                <div class="whitespace-pre-wrap leading-relaxed break-words">${escapeHtml(note.message)}</div>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-0.5">${escapeHtml(note.time)}</span>
                        `;
                    } else {
                        bubble.className = 'flex flex-col items-start';
                        bubble.innerHTML = `
                            <div class="bg-white text-slate-800 rounded-2xl rounded-tl-xs px-3 sm:px-3.5 py-1.5 sm:py-2 max-w-[88%] sm:max-w-[82%] text-xs border border-slate-200/80 shadow-2xs">
                                <div class="font-bold text-[10px] text-slate-600 mb-0.5">${escapeHtml(note.sender_name)}</div>
                                <div class="whitespace-pre-wrap leading-relaxed break-words">${escapeHtml(note.message)}</div>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-0.5">${escapeHtml(note.time)}</span>
                        `;
                    }
                    msgContainer.appendChild(bubble);
                });
            }
            msgContainer.scrollTop = msgContainer.scrollHeight;
        }

        // Auto mark as read if has unread
        if (hasUnread) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || document.querySelector('input[name="_token"]')?.value;
            fetch(`/admin/persetujuan-ganti-jam/${id}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            }).then(() => {
                btn.dataset.hasUnread = 'false';
                const badge = document.getElementById(`chat-dot-session-${id}`) || btn.querySelector('.chat-dot-session');
                if (badge) badge.classList.add('hidden');
            }).catch(e => console.error(e));
        }

        activeOpenedSessionId = parseInt(id, 10);
        if (window.gantiJamChatNotifier) {
            window.gantiJamChatNotifier.stopSessionChatSoundLoop();
        }

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                if (inputEl) inputEl.focus();
                if (msgContainer) msgContainer.scrollTop = msgContainer.scrollHeight;
            }, 100);
        }
    }

    function closeChatSessionModal() {
        activeOpenedSessionId = null;
        const modal = document.getElementById('chatSessionModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const chatForm = document.getElementById('chat-session-form');
        if (chatForm) {
            chatForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const input = document.getElementById('chat_session_input');
                const msg = input ? input.value.trim() : '';
                if (!msg) return;

                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75');
                }

                try {
                    const formData = new FormData(this);
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        || document.querySelector('input[name="_token"]')?.value;

                    const res = await fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    if (res.ok) {
                        const data = await res.json();
                        if (input) input.value = '';
                        const msgContainer = document.getElementById('chat-session-messages');
                        if (msgContainer && data.note) {
                            const emptyState = msgContainer.querySelector('.fa-comments')?.closest('.text-center');
                            if (emptyState) emptyState.remove();

                            const bubble = document.createElement('div');
                            bubble.className = 'flex flex-col items-end animate-in fade-in duration-150';
                            bubble.innerHTML = `
                                <div style="background-color: #ea580c !important; color: #ffffff !important;" class="bg-orange-600 text-white rounded-2xl rounded-tr-xs px-3 sm:px-3.5 py-1.5 sm:py-2 max-w-[88%] sm:max-w-[82%] text-xs shadow-2xs">
                                    <div class="font-bold text-[10px] text-orange-100 mb-0.5">${escapeHtml(data.note.sender_name)} (Admin)</div>
                                    <div class="whitespace-pre-wrap leading-relaxed break-words">${escapeHtml(data.note.message)}</div>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-0.5">${escapeHtml(data.note.time)}</span>
                            `;
                            msgContainer.appendChild(bubble);
                            msgContainer.scrollTop = msgContainer.scrollHeight;
                        }
                    } else {
                        this.submit();
                    }
                } catch (err) {
                    this.submit();
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-75');
                    }
                }
            });
        }

        const chatRegForm = document.getElementById('chat-reg-form');
        if (chatRegForm) {
            chatRegForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const input = document.getElementById('chat_reg_input');
                const msg = input ? input.value.trim() : '';
                if (!msg) return;

                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75');
                }

                try {
                    const formData = new FormData(this);
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        || document.querySelector('input[name="_token"]')?.value;

                    const res = await fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    if (res.ok) {
                        const data = await res.json();
                        if (input) input.value = '';
                        const msgContainer = document.getElementById('chat-reg-messages');
                        if (msgContainer && data.note) {
                            const emptyState = msgContainer.querySelector('.fa-comments')?.closest('.text-center');
                            if (emptyState) emptyState.remove();

                            const bubble = document.createElement('div');
                            bubble.className = 'flex flex-col items-end animate-in fade-in duration-150';
                            bubble.innerHTML = `
                                <div style="background-color: #ea580c !important; color: #ffffff !important;" class="bg-orange-600 text-white rounded-2xl rounded-tr-xs px-3 sm:px-3.5 py-1.5 sm:py-2 max-w-[88%] sm:max-w-[82%] text-xs shadow-2xs">
                                    <div class="font-bold text-[10px] text-orange-100 mb-0.5">${escapeHtml(data.note.sender_name)} (Admin)</div>
                                    <div class="whitespace-pre-wrap leading-relaxed break-words">${escapeHtml(data.note.message)}</div>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-0.5">${escapeHtml(data.note.time)}</span>
                            `;
                            msgContainer.appendChild(bubble);
                            msgContainer.scrollTop = msgContainer.scrollHeight;
                        }
                    } else {
                        this.submit();
                    }
                } catch (err) {
                    this.submit();
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-75');
                    }
                }
            });
        }

        const sessionMsgContainer = document.getElementById('chat-session-messages');
        if (sessionMsgContainer) {
            const chatObserver = new MutationObserver(() => {
                sessionMsgContainer.scrollTop = sessionMsgContainer.scrollHeight;
            });
            chatObserver.observe(sessionMsgContainer, { childList: true, subtree: true });
        }

        const regMsgContainer = document.getElementById('chat-reg-messages');
        if (regMsgContainer) {
            const chatObserver = new MutationObserver(() => {
                regMsgContainer.scrollTop = regMsgContainer.scrollHeight;
            });
            chatObserver.observe(regMsgContainer, { childList: true, subtree: true });
        }
    });

    function openApproveRegModal(btn) {
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        const date = btn.dataset.date || '';
        const shiftId = btn.dataset.shiftId || '';
        const officeId = btn.dataset.officeId || '';

        const form = document.getElementById('approve-reg-form');
        const subtitle = document.getElementById('approve-reg-subtitle');
        const dateInput = document.getElementById('approve_reg_date');
        const shiftInput = document.getElementById('approve_reg_shift');
        const officeInput = document.getElementById('approve_reg_office');
        const notesInput = document.getElementById('approve_reg_notes');
        const modal = document.getElementById('approveRegModal');

        const minTomorrow = '{{ \Carbon\Carbon::tomorrow("Asia/Jakarta")->toDateString() }}';

        if (form) {
            form.action = `/admin/ganti-jam/registrations/${id}/approve`;
            form.onsubmit = function(e) {
                if (dateInput && dateInput.min && dateInput.value < dateInput.min) {
                    e.preventDefault();
                    alert('Tanggal rencana ganti jam tidak dapat memilih hari ini atau tanggal lampau.');
                    dateInput.focus();
                    return false;
                }
            };
        }
        if (subtitle) subtitle.textContent = `Pemagang: ${name}`;
        if (dateInput) {
            dateInput.min = minTomorrow;
            dateInput.value = (date && date >= minTomorrow) ? date : minTomorrow;
        }
        if (shiftInput) shiftInput.value = shiftId;
        if (officeInput) officeInput.value = officeId;
        if (notesInput) notesInput.value = '';

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeApproveRegModal() {
        const modal = document.getElementById('approveRegModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function openRejectRegModal(btn) {
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        const date = btn.dataset.date;

        const form = document.getElementById('reject-reg-form');
        const subtitle = document.getElementById('reject-reg-subtitle');
        const notes = document.getElementById('reject_reg_notes');
        const modal = document.getElementById('rejectRegModal');

        if (form) form.action = `/admin/ganti-jam/registrations/${id}/reject`;
        if (subtitle) subtitle.textContent = `Pemagang: ${name} (${date})`;
        if (notes) notes.value = '';

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeRejectRegModal() {
        const modal = document.getElementById('rejectRegModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function openDeleteRegModal(btn) {
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        const date = btn.dataset.date;
        const shift = btn.dataset.shift;
        const office = btn.dataset.office || '-';
        const status = btn.dataset.status;
        const statusLabel = btn.dataset.statusLabel || status;

        const modal = document.getElementById('deleteRegModal');
        const form = document.getElementById('delete-reg-form');
        const subtitle = document.getElementById('delete-reg-subtitle');
        const nameEl = document.getElementById('delete-reg-name');
        const dateEl = document.getElementById('delete-reg-date');
        const shiftEl = document.getElementById('delete-reg-shift');
        const statusEl = document.getElementById('delete-reg-status-badge');
        const warningBox = document.getElementById('delete-reg-approved-warning');

        if (form) form.action = `/admin/ganti-jam/registrations/${id}`;
        if (subtitle) subtitle.textContent = `Pemagang: ${name}`;
        if (nameEl) nameEl.textContent = name;
        if (dateEl) dateEl.textContent = date;
        if (shiftEl) shiftEl.textContent = office !== '-' ? `${shift} (${office})` : shift;
        if (statusEl) {
            statusEl.textContent = statusLabel;
            if (status === 'approved') {
                statusEl.className = 'font-bold text-right text-emerald-600';
            } else if (status === 'rejected') {
                statusEl.className = 'font-bold text-right text-rose-600';
            } else {
                statusEl.className = 'font-bold text-right text-amber-600';
            }
        }

        if (warningBox) {
            if (status === 'approved') {
                warningBox.classList.remove('hidden');
            } else {
                warningBox.classList.add('hidden');
            }
        }

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeDeleteRegModal() {
        const modal = document.getElementById('deleteRegModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function openApproveSessionModal(btn) {
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        const date = btn.dataset.date;
        const time = btn.dataset.time || '-';
        const duration = btn.dataset.duration || '-';

        const modal = document.getElementById('approveSessionModal');
        const form = document.getElementById('approve-session-form');
        const subtitle = document.getElementById('approve-session-subtitle');
        const nameEl = document.getElementById('approve-session-name');
        const dateEl = document.getElementById('approve-session-date');
        const timeEl = document.getElementById('approve-session-time');
        const durationEl = document.getElementById('approve-session-duration');

        if (form) form.action = `/admin/persetujuan-ganti-jam/${id}/approve`;
        if (subtitle) subtitle.textContent = `Pemagang: ${name}`;
        if (nameEl) nameEl.textContent = name;
        if (dateEl) dateEl.textContent = date;
        if (timeEl) timeEl.textContent = time;
        if (durationEl) durationEl.textContent = duration;

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeApproveSessionModal() {
        const modal = document.getElementById('approveSessionModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function openDeleteSessionModal(btn) {
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        const date = btn.dataset.date;
        const time = btn.dataset.time || '-';
        const status = btn.dataset.status;
        const statusLabel = btn.dataset.statusLabel || status;

        const modal = document.getElementById('deleteSessionModal');
        const form = document.getElementById('delete-session-form');
        const subtitle = document.getElementById('delete-session-subtitle');
        const nameEl = document.getElementById('delete-session-name');
        const dateEl = document.getElementById('delete-session-date');
        const timeEl = document.getElementById('delete-session-time');
        const statusEl = document.getElementById('delete-session-status-badge');

        if (form) form.action = `/admin/persetujuan-ganti-jam/${id}`;
        if (subtitle) subtitle.textContent = `Pemagang: ${name}`;
        if (nameEl) nameEl.textContent = name;
        if (dateEl) dateEl.textContent = date;
        if (timeEl) timeEl.textContent = time;
        if (statusEl) {
            statusEl.textContent = statusLabel;
            if (status === 'approved') {
                statusEl.className = 'font-bold text-right text-emerald-600';
            } else if (status === 'rejected') {
                statusEl.className = 'font-bold text-right text-rose-600';
            } else {
                statusEl.className = 'font-bold text-right text-amber-600';
            }
        }

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeDeleteSessionModal() {
        const modal = document.getElementById('deleteSessionModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function openRejectModal(sessionIdOrBtn, internName, sessionDate) {
        let sessionId, name, date;
        if (typeof sessionIdOrBtn === 'object' && sessionIdOrBtn !== null) {
            sessionId = sessionIdOrBtn.dataset.id;
            name = sessionIdOrBtn.dataset.name;
            date = sessionIdOrBtn.dataset.date;
        } else {
            sessionId = sessionIdOrBtn;
            name = internName;
            date = sessionDate;
        }

        const modal = document.getElementById('rejectSessionModal');
        const form = document.getElementById('reject-session-form');
        const subtitle = document.getElementById('reject-modal-subtitle');
        const noteInput = document.getElementById('rejection_note');

        if (form) {
            form.action = `/admin/persetujuan-ganti-jam/${sessionId}/reject`;
        }
        if (subtitle) {
            subtitle.textContent = `Pemagang: ${name} (${date})`;
        }
        if (noteInput) {
            noteInput.value = '';
        }
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeRejectModal() {
        const modal = document.getElementById('rejectSessionModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function openEditTimeModal(sessionIdOrBtn, fieldName, fieldLabel, currentValue, internName, sessionDate) {
        let sessionId, field, label, val, name, date;
        if (typeof sessionIdOrBtn === 'object' && sessionIdOrBtn !== null) {
            sessionId = sessionIdOrBtn.dataset.id;
            field = sessionIdOrBtn.dataset.field;
            label = sessionIdOrBtn.dataset.label;
            val = sessionIdOrBtn.dataset.val;
            name = sessionIdOrBtn.dataset.name;
            date = sessionIdOrBtn.dataset.date;
        } else {
            sessionId = sessionIdOrBtn;
            field = fieldName;
            label = fieldLabel;
            val = currentValue;
            name = internName;
            date = sessionDate;
        }

        const modal = document.getElementById('editTimeModal');
        const form = document.getElementById('edit-time-form');
        const fieldInput = document.getElementById('edit-time-field-name');
        const timeInput = document.getElementById('edit_time_input');
        const fieldLabelEl = document.getElementById('edit-time-field-label');
        const dateDisplayEl = document.getElementById('edit-time-date-display');
        const nameDisplayEl = document.getElementById('edit-time-name-display');

        if (form) {
            form.action = `/admin/persetujuan-ganti-jam/${sessionId}/update-time`;
        }
        if (fieldInput) {
            fieldInput.value = field;
        }
        if (fieldLabelEl) {
            fieldLabelEl.textContent = label;
        }
        if (dateDisplayEl) {
            dateDisplayEl.textContent = date;
        }
        if (nameDisplayEl) {
            nameDisplayEl.textContent = name;
        }

        let timeVal = val || '';
        if (timeVal && timeVal.includes(':')) {
            const parts = timeVal.split(':');
            if (parts.length === 2) {
                timeVal += ':00';
            }
        }
        if (timeInput) {
            timeInput.value = timeVal;
        }

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeEditTimeModal() {
        const modal = document.getElementById('editTimeModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    // ============================================================
    // SISTEM NOTIFIKASI & UPDATE CHAT GANTI JAM REAL-TIME
    // (Terhubung ke Global GantiJamChatNotificationManager)
    // ============================================================
    function handleGantiJamChatData(data) {
        if (!data) return;
        const latestNote = data.latest_note;

        // Update penanda lingkaran kecil pada tombol chat sesi ganti jam
        if (Array.isArray(data.session_ids)) {
            document.querySelectorAll('.chat-dot-session').forEach(el => {
                const idStr = el.id.replace('chat-dot-session-', '');
                const sessId = parseInt(idStr, 10);
                if (data.session_ids.includes(sessId)) {
                    el.classList.remove('hidden');
                    const btn = document.getElementById(`btn-chat-session-${sessId}`);
                    if (btn) btn.dataset.hasUnread = 'true';
                } else {
                    el.classList.add('hidden');
                    const btn = document.getElementById(`btn-chat-session-${sessId}`);
                    if (btn) btn.dataset.hasUnread = 'false';
                }
            });
        }

        // Update penanda lingkaran kecil pada tombol chat pendaftaran
        if (Array.isArray(data.registration_ids)) {
            document.querySelectorAll('.chat-dot-reg').forEach(el => {
                const idStr = el.id.replace('chat-dot-reg-', '');
                const regId = parseInt(idStr, 10);
                if (data.registration_ids.includes(regId)) {
                    el.classList.remove('hidden');
                    const btn = document.getElementById(`btn-chat-reg-${regId}`);
                    if (btn) btn.dataset.hasUnread = 'true';
                } else {
                    el.classList.add('hidden');
                    const btn = document.getElementById(`btn-chat-reg-${regId}`);
                    if (btn) btn.dataset.hasUnread = 'false';
                }
            });
        }

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        if (csrfMeta) {
            headers['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');
        }

        // Jika modal chat sedang terbuka untuk sesi yang baru saja menerima pesan, auto-append pesan ke dalam box
        if (latestNote && activeOpenedSessionId && latestNote.session_id === activeOpenedSessionId) {
            const msgContainer = document.getElementById('chat-session-messages');
            if (msgContainer && !document.getElementById(`chat-msg-${latestNote.id}`)) {
                const bubble = document.createElement('div');
                bubble.id = `chat-msg-${latestNote.id}`;
                bubble.className = 'flex flex-col items-start animate-in fade-in duration-200';
                bubble.innerHTML = `
                    <div class="bg-white text-slate-800 rounded-2xl rounded-tl-xs px-3 sm:px-3.5 py-1.5 sm:py-2 max-w-[88%] sm:max-w-[82%] text-xs border border-slate-200/80 shadow-2xs">
                        <div class="font-bold text-[10px] text-slate-600 mb-0.5">${escapeHtml(latestNote.sender_name)}</div>
                        <div class="whitespace-pre-wrap leading-relaxed break-words">${escapeHtml(latestNote.message)}</div>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-0.5">${escapeHtml(latestNote.time)}</span>
                `;
                msgContainer.appendChild(bubble);
                msgContainer.scrollTop = msgContainer.scrollHeight;

                // Langsung tandai dibaca di server
                fetch(`/admin/persetujuan-ganti-jam/${activeOpenedSessionId}/mark-read`, {
                    method: 'POST',
                    headers: headers
                }).catch(e => console.error(e));
            }
        } else if (latestNote && activeOpenedRegId && latestNote.registration_id === activeOpenedRegId) {
            const msgContainer = document.getElementById('chat-reg-messages');
            if (msgContainer && !document.getElementById(`chat-msg-${latestNote.id}`)) {
                const bubble = document.createElement('div');
                bubble.id = `chat-msg-${latestNote.id}`;
                bubble.className = 'flex flex-col items-start animate-in fade-in duration-200';
                bubble.innerHTML = `
                    <div class="bg-white text-slate-800 rounded-2xl rounded-tl-xs px-3 sm:px-3.5 py-1.5 sm:py-2 max-w-[88%] sm:max-w-[82%] text-xs border border-slate-200/80 shadow-2xs">
                        <div class="font-bold text-[10px] text-slate-600 mb-0.5">${escapeHtml(latestNote.sender_name)}</div>
                        <div class="whitespace-pre-wrap leading-relaxed break-words">${escapeHtml(latestNote.message)}</div>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-0.5">${escapeHtml(latestNote.time)}</span>
                `;
                msgContainer.appendChild(bubble);
                msgContainer.scrollTop = msgContainer.scrollHeight;

                fetch(`/admin/ganti-jam/registrations/${activeOpenedRegId}/mark-read`, {
                    method: 'POST',
                    headers: headers
                }).catch(e => console.error(e));
            }
        }
    }

    // Dengarkan event update dari Global Manager
    window.addEventListener('ganti-jam-chat-update', function(e) {
        if (e.detail) {
            handleGantiJamChatData(e.detail);
        }
    });

    window.playChatNotificationSound = function() {
        if (window.gantiJamChatNotifier) {
            window.gantiJamChatNotifier.playChatChime();
        }
    };
</script>
@endsection