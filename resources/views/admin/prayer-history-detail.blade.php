@extends('layouts.main')

@section('title', 'History Izin Shalat: ' . ($intern->user->profile->full_name ?? $intern->user->name))

@section('contents')
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 bg-gray-50 min-h-screen min-w-0">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-green-600 rounded-lg shadow-sm">
                        <i class="fas fa-history text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">History Izin Shalat</h1>
                        <p class="text-gray-600 mt-1">Detail riwayat untuk: <strong>{{ $intern->user->profile->full_name ?? $intern->user->name }}</strong></p>
                    </div>
                </div>
                <div>
                    <a href="{{ route('admin.izinShalat.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke Monitoring
                    </a>
                </div>
            </div>
        </div>

        <!-- History Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Izin Mulai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Izin Selesai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status / Hutang Jam</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                             {{-- [PERBAIKAN] Menggunakan variabel $permitLogs yang dikirim controller --}}
                            @forelse ($permitLogs as $log)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($log->start_time)->isoFormat('dddd, D MMMM YYYY') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($log->start_time)->format('H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log->end_time ? \Carbon\Carbon::parse($log->end_time)->format('H:i:s') : 'Belum Selesai' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 font-mono">
                                        @php
                                            $durationString = '00:00:00';
                                            if ($log->end_time) {
                                                $startTime = \Carbon\Carbon::parse($log->start_time);
                                                $endTime = \Carbon\Carbon::parse($log->end_time);
                                                $totalSeconds = $endTime->diffInSeconds($startTime);

                                                $hours = floor($totalSeconds / 3600);
                                                $minutes = floor(($totalSeconds % 3600) / 60);
                                                $seconds = $totalSeconds % 60;

                                                $durationString = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                                            } else {
                                                $durationString = '-';
                                            }
                                        @endphp
                                        {{ $durationString }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-700">
                                        @if($log->is_mandatory_replace)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                <i class="fas fa-exclamation-triangle text-rose-500"></i>
                                                Hutang Jam: {{ $log->agreed_duration_minutes ?? $log->duration_in_minutes }} menit
                                            </span>
                                            @if($log->description)
                                                <div class="text-[11px] text-gray-500 mt-1 max-w-xs truncate" title="{{ $log->description }}">{{ $log->description }}</div>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i class="fas fa-check-circle text-emerald-500"></i> Sesuai Batas
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        Tidak ada riwayat izin shalat untuk intern ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($permitLogs->hasPages())
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-3.5 bg-white rounded-2xl border border-slate-200/90 shadow-2xs mt-6">
                    <div class="text-xs text-slate-500 font-medium">
                        Menampilkan <span class="font-bold text-slate-800">{{ $permitLogs->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-800">{{ $permitLogs->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-800">{{ $permitLogs->total() }}</span> riwayat shalat
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