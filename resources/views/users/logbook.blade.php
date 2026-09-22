@extends('users.layouts.main')

@section('title', 'Logbook Harian Pemagang')

@section('contents')
<div class="max-w-6xl mx-auto px-4 py-6 md:py-8 space-y-6">

    <!-- Top Navigation / Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <a href="{{ route('user.home') }}"
            class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-blue-600 transition-colors w-fit">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Beranda</span>
        </a>

        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 shadow-sm">
                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Divisi: {{ $user->intern->division->name ?? 'Umum' }}</span>
            </span>
            @if($user->intern?->school)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5" />
                </svg>
                <span>{{ $user->intern->school->name }}</span>
            </span>
            @endif
        </div>
    </div>

    <!-- Banner Card -->
    <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-700 text-white rounded-3xl p-6 md:p-8 shadow-xl relative overflow-hidden">
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2 text-blue-300 text-xs font-bold uppercase tracking-wider">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Laporan Harian Kerja</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
                    Logbook Harian Pemagang
                </h1>
                <p class="text-xs md:text-sm text-slate-300 max-w-xl leading-relaxed">
                    Catat rincian kegiatan kerja harian Anda secara rutin dan pantau histori logbook yang telah Anda laporkan.
                </p>
            </div>
            <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl p-4 flex items-center gap-4 self-start md:self-center">
                <div class="w-12 h-12 rounded-xl bg-blue-600/40 border border-blue-400/30 flex items-center justify-center text-blue-300">
                    <i class="fa-regular fa-file-lines text-2xl"></i>
                </div>
                <div>
                    <div class="text-[11px] text-slate-300 font-medium">Hari Ini</div>
                    <div class="text-sm font-bold text-white">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Alerts -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-xs font-semibold shadow-sm">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3 text-red-800 text-xs font-semibold shadow-sm">
        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif
    @if(isset($errors) && $errors->any())
    <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-red-800 text-xs font-semibold shadow-sm space-y-1">
        @foreach($errors->all() as $err)
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $err }}</span>
        </div>
        @endforeach
    </div>
    @endif

    <!-- MAIN UNIFIED LOGBOOK CARD (2 TAB) -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">

        <!-- Tab Navigation -->
        <div class="flex border-b border-gray-200 bg-gray-50 text-sm font-medium">
            <button type="button" onclick="switchLogbookTab('tab-today')" id="btn-tab-today"
                class="logbook-tab-btn flex-1 py-3.5 px-5 text-center border-b-2 border-blue-600 text-blue-600 font-semibold focus:outline-none flex items-center justify-center gap-2 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span>Isi Laporan Hari Ini</span>
                @if($hasFilledLogToday)
                <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-bold">Sudah Diisi</span>
                @else
                <span class="text-[10px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-bold">Belum Diisi</span>
                @endif
            </button>
            <button type="button" onclick="switchLogbookTab('tab-history')" id="btn-tab-history"
                class="logbook-tab-btn flex-1 py-3.5 px-5 text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none flex items-center justify-center gap-2 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Riwayat Logbook</span>
                <span class="text-[10px] bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full font-bold">{{ count($logActivityHistory ?? []) }} hari</span>
            </button>
        </div>

        <!-- Tab Body Content -->
        <div class="p-6">

            <!-- ==================================================== -->
            <!-- TAB 1: LAPORAN HARI INI -->
            <!-- ==================================================== -->
            <div id="content-tab-today" class="logbook-tab-content space-y-6">

                <!-- Date & Status Info Card -->
                <div class="p-4 rounded-xl border {{ $hasFilledLogToday ? 'bg-emerald-50 border-emerald-200' : 'bg-blue-50 border-blue-200' }} flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg {{ $hasFilledLogToday ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-100 text-blue-600' }} flex items-center justify-center text-lg flex-shrink-0">
                            <i class="fa-regular {{ $hasFilledLogToday ? 'fa-circle-check' : 'fa-calendar' }}"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal Laporan</div>
                            <div class="font-bold text-gray-800 text-sm md:text-base">
                                {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                            </div>
                        </div>
                    </div>

                    <div>
                        @if($hasFilledLogToday)
                        @php
                        $todayStatusId = $todaysLogActivity->status_id ?? 1;
                        $todayStatusName = $todaysLogActivity->status->name ?? 'Menunggu';
                        @endphp
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $todayStatusId == 2 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($todayStatusId == 3 ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-amber-100 text-amber-800 border border-amber-300') }}">
                            <span class="w-2 h-2 rounded-full {{ $todayStatusId == 2 ? 'bg-emerald-500' : ($todayStatusId == 3 ? 'bg-rose-500' : 'bg-amber-500 animate-pulse') }}"></span>
                            <span>Status: {{ $todayStatusName }}</span>
                        </div>
                        @else
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span>Belum Disimpan</span>
                        </div>
                        @endif
                    </div>
                </div>

                @if($hasFilledLogToday)
                <!-- TAMPILAN JIKA SUDAH MENGISI LOGBOOK HARI INI -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="block font-bold text-gray-700 text-sm flex items-center gap-1.5">
                            <i class="fa-solid fa-list-check text-blue-600"></i>
                            Aktivitas yang Telah Dilaporkan:
                        </label>
                        @if(($todaysLogActivity->status_id ?? 1) != 2)
                        <button type="button" onclick="toggleTodayEditMode()" id="btn-toggle-today-edit" class="text-xs text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1 border border-blue-300 hover:border-blue-500 px-3 py-1.5 rounded-lg transition bg-blue-50/50">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span id="text-toggle-today-edit">Edit Laporan</span>
                        </button>
                        @endif
                    </div>

                    <!-- Read-only view -->
                    <div id="today-readonly-view" class="bg-gray-50 border border-gray-200 rounded-xl p-5 text-gray-800 text-sm whitespace-pre-line leading-relaxed min-h-[120px] shadow-inner">
                        {!! nl2br(e($todaysLogActivity->activity ?? '')) !!}
                    </div>

                    @if(($todaysLogActivity->status_id ?? 1) != 2)
                    <!-- Form Edit Mode (hidden by default) -->
                    <form id="today-edit-form" action="{{ route('home.logActivity.update.action') }}" method="POST" class="hidden space-y-3">
                        @csrf
                        <input type="hidden" name="id" value="{{ $todaysLogActivity->id }}">
                        <div>
                            <textarea class="border border-gray-300 rounded-xl p-4 w-full h-40 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition"
                                id="today_edit_activity" name="activity" required>{{ $todaysLogActivity->activity ?? '' }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Anda dapat memperbarui poin kegiatan sebelum admin/mentor menyetujui logbook ini.</p>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" onclick="toggleTodayEditMode()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-100 transition">Batal</button>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm transition flex items-center gap-1.5">
                                <i class="fa-solid fa-check"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                    @else
                    <div class="p-3.5 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-800 flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                        <span>Logbook hari ini sudah diverifikasi & disetujui oleh admin/mentor. Laporan sudah terkunci.</span>
                    </div>
                    @endif

                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-600 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-blue-500 text-base"></i>
                            <span>Ingin melihat seluruh riwayat logbook hari-hari sebelumnya?</span>
                        </div>
                        <button type="button" onclick="switchLogbookTab('tab-history')" class="text-xs text-blue-600 hover:text-blue-800 font-bold flex items-center gap-1.5 border border-blue-300 hover:border-blue-500 px-3 py-1.5 rounded-lg transition bg-white shrink-0 shadow-sm cursor-pointer">
                            <i class="fa-solid fa-clock-rotate-left"></i> Buka Riwayat Logbook &rarr;
                        </button>
                    </div>
                </div>

                @else
                <!-- FORM INPUT JIKA BELUM MENGISI HARI INI -->
                <div class="p-3.5 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800 flex items-start gap-2.5">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600 text-base mt-0.5"></i>
                    <div>
                        <span class="font-bold">Perhatian:</span> Pastikan mengisi dan menyimpan logbook aktivitas sebelum Anda menekan tombol <strong>Pulang</strong> presensi.
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-gray-500 pt-1 pb-1">
                    <span>Sudah pernah mengisi logbook sebelumnya?</span>
                    <button type="button" onclick="switchLogbookTab('tab-history')" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-clock-rotate-left"></i> Buka Riwayat Logbook &rarr;
                    </button>
                </div>

                <form id="activity-form" action="{{ route('home.logActivity.action') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <div>
                        <label class="block font-bold text-gray-700 text-sm mb-1.5 flex items-center justify-between" for="activity">
                            <span>Rincian Aktivitas Hari Ini <span class="text-red-500">*</span></span>
                            <span class="text-xs font-normal text-gray-400" id="char-count">0 karakter</span>
                        </label>
                        <textarea class="border border-gray-300 rounded-xl p-4 w-full h-44 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition"
                            id="activity" name="activity"
                            placeholder="Contoh:&#10;1. Mengerjakan implementasi integrasi API&#10;2. Melakukan code review dan perbaikan logic tugas&#10;3. Menyelesaikan testing dan dokumentasi progress harian" required></textarea>
                        <div id="activity-error" class="text-red-500 text-xs mt-1.5 hidden"></div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                        <button id="submit-button" type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl font-semibold text-sm hover:bg-blue-700 transition flex items-center gap-2 shadow-sm">
                            <i class="fa-regular fa-paper-plane"></i>
                            <span>Simpan Laporan Hari Ini</span>
                        </button>
                    </div>
                </form>
                @endif

            </div>

            <!-- ==================================================== -->
            <!-- TAB 2: RIWAYAT LOGBOOK -->
            <!-- ==================================================== -->
            <div id="content-tab-history" class="logbook-tab-content hidden space-y-4">

                <!-- Search Box -->
                <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" id="search-logbook" placeholder="Cari tanggal atau rincian aktivitas..."
                            class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    </div>
                    <div class="text-xs text-gray-500 flex items-center gap-1 self-end sm:self-center">
                        <span>Total Catatan:</span>
                        <span class="font-bold text-gray-800">{{ count($logActivityHistory ?? []) }} hari</span>
                    </div>
                </div>

                <!-- History Table Container -->
                <div class="overflow-x-auto border border-gray-200 rounded-xl">
                    <table class="w-full text-left border-collapse" id="logbook-history-table">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase tracking-wider text-gray-600 font-semibold">
                                <th class="py-3.5 px-4 w-12 text-center">No</th>
                                <th class="py-3.5 px-4 w-40">Tanggal</th>
                                <th class="py-3.5 px-4">Rincian Aktivitas</th>
                                <th class="py-3.5 px-4 w-32 text-center">Status</th>
                                <th class="py-3.5 px-4 w-24 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse($logActivityHistory ?? [] as $index => $history)
                            @php
                            $sId = $history->status_id ?? 1;
                            $sBadge = 'bg-amber-100 text-amber-800 border-amber-300';
                            if ($sId == 2) {
                            $sBadge = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                            } elseif ($sId == 3) {
                            $sBadge = 'bg-rose-100 text-rose-800 border-rose-300';
                            }
                            @endphp
                            <tr class="logbook-history-row hover:bg-gray-50/80 transition" data-search="{{ strtolower($history->activity . ' ' . $history->date) }}">
                                <td class="py-3.5 px-4 text-center text-xs text-gray-400 font-medium row-number">{{ $index + 1 }}</td>
                                <td class="py-3.5 px-4 whitespace-nowrap text-xs">
                                    <div class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($history->date)->locale('id')->isoFormat('D MMMM Y') }}</div>
                                    <div class="text-[11px] text-gray-500">{{ \Carbon\Carbon::parse($history->date)->locale('id')->isoFormat('dddd') }}</div>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs md:max-w-md">
                                    <p class="text-xs text-gray-700 whitespace-pre-line line-clamp-3 hover:line-clamp-none transition">
                                        {{ $history->activity }}
                                    </p>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $sBadge }}">
                                        {{ $history->status->name ?? 'Menunggu' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($sId != 2)
                                    <button type="button"
                                        data-id="{{ $history->id }}"
                                        data-activity="{{ $history->activity }}"
                                        onclick="openInlineEditLog(this)"
                                        class="inline-flex items-center gap-1 text-xs px-2.5 py-1 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg font-medium border border-blue-200 transition">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    @else
                                    <span class="text-emerald-500 text-base" title="Disetujui">
                                        <i class="fa-solid fa-circle-check"></i>
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr id="row-empty-history">
                                <td colspan="5" class="py-12 text-center text-gray-400 text-sm">
                                    <i class="fa-regular fa-folder-open text-3xl mb-2 text-gray-300 block"></i>
                                    Belum ada data riwayat logbook
                                </td>
                            </tr>
                            @endforelse
                            <tr id="row-no-search-results" class="hidden">
                                <td colspan="5" class="py-10 text-center text-gray-400 text-sm">
                                    <i class="fa-solid fa-magnifying-glass text-2xl mb-1 text-gray-300 block"></i>
                                    Tidak ada logbook yang sesuai dengan pencarian
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Client-side Pagination Controls -->
                <div id="history-pagination-container" class="flex flex-col sm:flex-row justify-between items-center gap-2 pt-3 text-xs text-gray-500">
                    <span id="history-page-info">Menampilkan halaman 1</span>
                    <div class="flex items-center gap-1">
                        <button type="button" id="btn-history-prev" onclick="changeHistoryPage(-1)"
                            class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <span id="history-page-numbers" class="flex items-center gap-1"></span>
                        <button type="button" id="btn-history-next" onclick="changeHistoryPage(1)"
                            class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

            </div>

        </div>

        <!-- Card Footer -->
        <div class="flex justify-between items-center px-6 py-4 border-t bg-gray-50/80">
            <span class="text-xs text-gray-500 flex items-center gap-1.5">
                <i class="fa-solid fa-shield-halved text-gray-400"></i> Absen Djuragan Intern System
            </span>
            <a href="{{ route('user.home') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-xs font-semibold transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- SUB-MODAL: EDIT HISTORICAL LOGBOOK -->
    <div id="modal-edit-historical-log" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden z-[10000] p-4">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate-fadeIn">
            <div class="flex justify-between items-center px-5 py-4 border-b bg-slate-800 text-white">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <h3 class="font-bold text-base">Edit Rincian Logbook</h3>
                </div>
                <button type="button" onclick="closeInlineEditLog()" class="text-gray-300 hover:text-white text-xl font-bold leading-none">&times;</button>
            </div>
            <form id="historical-edit-form" action="{{ route('home.logActivity.update.action') }}" method="POST" class="p-5 space-y-4">
                @csrf
                <input type="hidden" id="historical_edit_id" name="id">
                <div>
                    <label class="block font-bold text-gray-700 text-xs mb-1.5" for="historical_edit_activity">
                        Rincian Aktivitas <span class="text-red-500">*</span>
                    </label>
                    <textarea class="border border-gray-300 rounded-xl p-3 w-full h-36 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition"
                        id="historical_edit_activity" name="activity" required></textarea>
                    <p class="text-xs text-gray-500 mt-1">Hanya huruf, angka, spasi, dan tanda baca dasar yang diperbolehkan.</p>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeInlineEditLog()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 text-xs font-medium transition">
                        Batal
                    </button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-blue-700 transition flex items-center gap-1">
                        <i class="fa-solid fa-check"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    // Tab switching
    function switchLogbookTab(tabId) {
        $('.logbook-tab-content').addClass('hidden');
        $(`#content-${tabId}`).removeClass('hidden');

        $('.logbook-tab-btn').removeClass('border-blue-600 text-blue-600 font-bold')
            .addClass('border-transparent text-gray-500 font-medium');

        $(`#btn-${tabId}`).removeClass('border-transparent text-gray-500 font-medium')
            .addClass('border-blue-600 text-blue-600 font-bold');

        if (tabId === 'tab-history') {
            renderHistoryPage();
        }

        try {
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, null, '#' + tabId);
            }
        } catch (e) {}
    }

    // Toggle today's edit mode
    function toggleTodayEditMode() {
        const readonlyView = $('#today-readonly-view');
        const editForm = $('#today-edit-form');
        const btnText = $('#text-toggle-today-edit');

        if (editForm.hasClass('hidden')) {
            editForm.removeClass('hidden');
            readonlyView.addClass('hidden');
            btnText.text('Tutup Edit');
        } else {
            editForm.addClass('hidden');
            readonlyView.removeClass('hidden');
            btnText.text('Edit Laporan');
        }
    }

    // Open inline edit modal for past log entries
    function openInlineEditLog(btnOrId, activity) {
        let id = btnOrId;
        let act = activity;
        if (btnOrId && typeof btnOrId === 'object' && btnOrId.getAttribute) {
            id = btnOrId.getAttribute('data-id');
            act = btnOrId.getAttribute('data-activity') || '';
        }
        $('#historical_edit_id').val(id);
        $('#historical_edit_activity').val(act);
        $('#modal-edit-historical-log').removeClass('hidden').addClass('flex');
    }

    function closeInlineEditLog() {
        $('#modal-edit-historical-log').addClass('hidden').removeClass('flex');
    }

    // Client-side pagination & filter for Riwayat Logbook
    let historyCurrentPage = 1;
    const historyRowsPerPage = 8;
    let filteredHistoryRows = [];

    function initHistoryTable() {
        const allRows = $('.logbook-history-row');
        filteredHistoryRows = allRows.toArray();
        renderHistoryPage();
    }

    function renderHistoryPage() {
        const allRows = $('.logbook-history-row');
        const allRowsCount = allRows.length;

        if (filteredHistoryRows.length === 0 && allRowsCount > 0 && !$('#search-logbook').val()) {
            filteredHistoryRows = allRows.toArray();
        }

        const totalRows = filteredHistoryRows.length;
        const totalPages = Math.ceil(totalRows / historyRowsPerPage) || 1;

        if (historyCurrentPage > totalPages) historyCurrentPage = totalPages;
        if (historyCurrentPage < 1) historyCurrentPage = 1;

        allRows.addClass('hidden');

        if (allRowsCount === 0) {
            $('#row-empty-history').removeClass('hidden');
            $('#row-no-search-results').addClass('hidden');
            $('#history-pagination-container').addClass('hidden');
            return;
        }

        $('#row-empty-history').addClass('hidden');

        if (totalRows === 0) {
            $('#row-no-search-results').removeClass('hidden');
            $('#history-pagination-container').addClass('hidden');
        } else {
            $('#row-no-search-results').addClass('hidden');
            $('#history-pagination-container').removeClass('hidden');

            const startIndex = (historyCurrentPage - 1) * historyRowsPerPage;
            const endIndex = Math.min(startIndex + historyRowsPerPage, totalRows);

            for (let i = startIndex; i < endIndex; i++) {
                $(filteredHistoryRows[i]).removeClass('hidden');
            }

            $('#history-page-info').text(`Menampilkan ${startIndex + 1} - ${endIndex} dari ${totalRows} data`);
            $('#btn-history-prev').prop('disabled', historyCurrentPage === 1);
            $('#btn-history-next').prop('disabled', historyCurrentPage === totalPages);

            let pageBtnHtml = '';
            for (let p = 1; p <= totalPages; p++) {
                if (p === historyCurrentPage) {
                    pageBtnHtml += `<button type="button" class="w-7 h-7 rounded-lg bg-blue-600 text-white font-semibold text-xs flex items-center justify-center shadow-sm cursor-pointer">${p}</button>`;
                } else if (p === 1 || p === totalPages || (p >= historyCurrentPage - 1 && p <= historyCurrentPage + 1)) {
                    pageBtnHtml += `<button type="button" onclick="goToHistoryPage(${p})" class="w-7 h-7 rounded-lg border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 text-xs flex items-center justify-center transition cursor-pointer">${p}</button>`;
                } else if (p === 2 || p === totalPages - 1) {
                    pageBtnHtml += `<span class="px-0.5 text-gray-400">...</span>`;
                }
            }
            $('#history-page-numbers').html(pageBtnHtml);
        }
    }

    function changeHistoryPage(delta) {
        historyCurrentPage += delta;
        renderHistoryPage();
    }

    function goToHistoryPage(page) {
        historyCurrentPage = page;
        renderHistoryPage();
    }

    // Input validation
    function validateActivityInput(input) {
        const regex = /^[a-zA-Z0-9\s.,!?():;'-]+$/;
        const lines = input.split('\n');
        for (let i = 0; i < lines.length; i++) {
            if (!regex.test(lines[i].trim()) && lines[i].trim() !== '') {
                return false;
            }
        }
        return true;
    }

    $(document).ready(function() {
        initHistoryTable();

        // Search live filter
        $('#search-logbook').on('input', function() {
            const keyword = $(this).val().toLowerCase().trim();
            const allRows = $('.logbook-history-row');

            if (allRows.length === 0) return;

            if (!keyword) {
                filteredHistoryRows = allRows.toArray();
            } else {
                filteredHistoryRows = allRows.filter(function() {
                    const searchContent = $(this).attr('data-search') || '';
                    return searchContent.includes(keyword);
                }).toArray();
            }
            historyCurrentPage = 1;
            renderHistoryPage();
        });

        // Character counter
        $('#activity').on('input', function() {
            const count = $(this).val().length;
            $('#char-count').text(`${count} karakter`);
        });

        // Form submission validation
        $('#activity-form').on('submit', function(e) {
            const activityInput = $('#activity').val().trim();
            const errorDiv = $('#activity-error');

            if (!activityInput) {
                errorDiv.text('Keterangan aktivitas tidak boleh kosong.').removeClass('hidden');
                e.preventDefault();
                return;
            }

            if (!validateActivityInput(activityInput)) {
                errorDiv.text('Input mengandung karakter yang tidak diizinkan. Hanya huruf, angka, dan tanda baca standar yang diperbolehkan.').removeClass('hidden');
                e.preventDefault();
                return;
            }

            errorDiv.addClass('hidden');
        });

        // Realtime validation
        $('#activity').on('input', function() {
            const errorDiv = $('#activity-error');
            const input = $(this).val();

            if (!validateActivityInput(input)) {
                errorDiv.text('Input mengandung karakter yang tidak diizinkan. Hanya huruf, angka, dan tanda baca standar yang diperbolehkan.').removeClass('hidden');
            } else {
                errorDiv.addClass('hidden');
            }
        });

        // Check if tab is requested via URL parameter or hash
        const urlParams = new URLSearchParams(window.location.search);
        const hash = window.location.hash;
        if (urlParams.get('tab') === 'history' || hash === '#tab-history' || hash === '#history') {
            switchLogbookTab('tab-history');
        }
    });
</script>
@endsection