@extends('layouts.main')

@section('title', 'Riwayat Izin Toilet: ' . ($intern->user->profile->full_name ?? $intern->user->name))

@section('contents')
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-4 sm:p-6 lg:p-8 bg-gray-50 min-h-screen min-w-0">
        <div class="max-w-7xl mx-auto space-y-6">

        <div class="mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="p-3 sm:p-4 bg-indigo-600 rounded-2xl shadow-sm text-white shrink-0">
                        <i class="fas fa-restroom text-xl sm:text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">Riwayat Izin Toilet</h1>
                        <p class="text-xs sm:text-sm text-gray-600 mt-0.5">Detail riwayat untuk: <strong>{{ $intern->user->profile->full_name ?? $intern->user->name }}</strong></p>
                    </div>
                </div>
                <div class="self-start sm:self-auto">
                    <a href="{{ route('assistant.izin.toilet.index') }}" class="inline-flex items-center px-3.5 sm:px-4 py-2 bg-white border border-gray-300 rounded-xl text-xs sm:text-sm font-semibold text-gray-700 hover:bg-gray-50 shadow-xs transition">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke Monitoring
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 sm:p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Izin Mulai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Izin Selesai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- [BENAR] Loop menggunakan $permitLogs, bukan $allInterns --}}
                            @forelse ($permitLogs as $log)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                        {{ \Carbon\Carbon::parse($log->start_time)->isoFormat('dddd, D MMMM YYYY') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                        {{ \Carbon\Carbon::parse($log->start_time)->format('H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                        {{ $log->end_time ? \Carbon\Carbon::parse($log->end_time)->format('H:i:s') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                       {{ $log->end_time ? \Carbon\Carbon::parse($log->start_time)->diffForHumans($log->end_time, true) : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-box-open fa-3x text-gray-300"></i>
                                            <p class="mt-4">Tidak ada riwayat izin toilet untuk siswa ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($permitLogs->hasPages())
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-3.5 bg-white rounded-2xl border border-slate-200/90 shadow-2xs mt-6">
                    <div class="text-xs text-slate-500 font-medium">
                        Menampilkan <span class="font-bold text-slate-800">{{ $permitLogs->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-800">{{ $permitLogs->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-800">{{ $permitLogs->total() }}</span> riwayat toilet
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
        </div>
    </main>
@endsection