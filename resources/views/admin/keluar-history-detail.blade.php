{{-- resources/views/admin/keluar-history-detail.blade.php --}}

@extends('layouts.main')

@section('title', 'Riwayat Izin Keluar: ' . ($intern->user->profile->full_name ?? $intern->user->username))

@section('contents')
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 bg-slate-50/50 min-h-screen min-w-0">
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="p-3 bg-blue-600 text-white rounded-2xl shadow-sm shadow-blue-500/20">
                        <i class="fas fa-sign-out-alt text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">Riwayat Izin Keluar</h1>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Detail riwayat untuk: <strong class="text-slate-700">{{ $intern->user->profile->full_name ?? $intern->user->username }}</strong>
                            &mdash; {{ $intern->division->name ?? 'Umum' }}
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.izinKeluar.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs w-fit">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Kembali ke Monitoring
                </a>
            </div>

            <!-- History Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800">Daftar Riwayat</h2>
                    <p class="text-xs text-slate-400">Total data: {{ $permitLogs->total() }} izin keluar</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 border-b border-slate-200">
                                <th class="px-4 py-3 font-bold whitespace-nowrap">Tanggal</th>
                                <th class="px-4 py-3 font-bold whitespace-nowrap">Izin Mulai</th>
                                <th class="px-4 py-3 font-bold whitespace-nowrap">Izin Selesai</th>
                                <th class="px-4 py-3 font-bold whitespace-nowrap">Keterangan</th>
                                <th class="px-4 py-3 font-bold whitespace-nowrap">Disetujui Oleh</th>
                                <th class="px-4 py-3 font-bold whitespace-nowrap">Waktu Disepakati</th>
                                <th class="px-4 py-3 font-bold whitespace-nowrap">Wajib Ganti</th>
                                <th class="px-4 py-3 font-bold whitespace-nowrap text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($permitLogs as $log)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-800 font-semibold">
                                        {{ \Carbon\Carbon::parse($log->start_time)->isoFormat('dddd, D MMMM YYYY') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-600 font-mono">
                                        {{ \Carbon\Carbon::parse($log->start_time)->format('H:i') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-600 font-mono">
                                        @if($log->end_time)
                                            {{ \Carbon\Carbon::parse($log->end_time)->format('H:i') }}
                                        @elseif($log->agreed_duration_minutes)
                                            {{ \Carbon\Carbon::parse($log->start_time)->addMinutes($log->agreed_duration_minutes)->format('H:i') }}
                                        @else
                                            <span class="text-amber-600 font-sans font-bold text-[11px]">Sedang Berlangsung</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 max-w-[220px]">
                                        <p class="truncate" title="{{ $log->description ?? '-' }}">{{ $log->description ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                        {{ $log->authorized_by ?? 'Belum' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                        @php
                                            $durMins = $log->duration_in_minutes ?? $log->agreed_duration_minutes;
                                        @endphp
                                        @if($durMins)
                                            @if($durMins >= 60)
                                                {{ intdiv($durMins, 60) }} Jam{{ $durMins % 60 ? ' ' . ($durMins % 60) . ' Menit' : '' }}
                                            @else
                                                {{ $durMins }} Menit
                                            @endif
                                        @else
                                            <span class="text-amber-600 font-bold">Berjalan</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($log->is_mandatory_replace)
                                            <span class="text-red-600 font-bold">Ya</span>
                                        @else
                                            <span class="text-green-600">Tidak</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        @if($log->approval_status === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-[11px]">
                                                <i class="fas fa-check"></i> Disetujui
                                            </span>
                                        @elseif($log->approval_status === 'rejected')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 font-bold text-[11px]">
                                                <i class="fas fa-ban"></i> Ditolak
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 font-bold text-[11px]">
                                                <i class="fas fa-hourglass-half"></i> Menunggu
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-12 text-center text-slate-400">
                                        <i class="fas fa-sign-out-alt text-4xl text-slate-300 mb-3 block"></i>
                                        <p class="font-bold text-slate-600">Tidak ada riwayat izin keluar</p>
                                        <p class="text-xs text-slate-400 mt-1">Pemagang ini belum pernah mengajukan izin keluar.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($permitLogs->hasPages())
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-3.5 bg-white rounded-2xl border border-slate-200/90 shadow-2xs mt-4">
                    <div class="text-xs text-slate-500 font-medium">
                        Menampilkan <span class="font-bold text-slate-800">{{ $permitLogs->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-800">{{ $permitLogs->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-800">{{ $permitLogs->total() }}</span> riwayat izin
                    </div>
                    <div class="flex items-center space-x-1.5">
                        {{-- Prev Button --}}
                        @if ($permitLogs->onFirstPage())
                            <button class="bg-gray-200 text-gray-400 px-3 py-1.5 rounded-lg text-xs font-semibold cursor-not-allowed shadow-2xs flex items-center gap-1" disabled>
                                <i class="fas fa-chevron-left text-[10px]"></i>
                                <span>Prev</span>
                            </button>
                        @else
                            <a href="{{ $permitLogs->previousPageUrl() }}" class="bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold shadow-2xs transition flex items-center gap-1">
                                <i class="fas fa-chevron-left text-[10px]"></i>
                                <span>Prev</span>
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        <div class="flex space-x-1">
                            @foreach (range(1, $permitLogs->lastPage()) as $page)
                                @if ($page == $permitLogs->currentPage())
                                    <span class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold shadow-2xs transition">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $permitLogs->url($page) }}" class="px-3 py-1.5 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-xs font-bold shadow-2xs transition">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        </div>

                        {{-- Next Button --}}
                        @if ($permitLogs->hasMorePages())
                            <a href="{{ $permitLogs->nextPageUrl() }}" class="bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold shadow-2xs transition flex items-center gap-1">
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
        </div>
    </main>
@endsection
