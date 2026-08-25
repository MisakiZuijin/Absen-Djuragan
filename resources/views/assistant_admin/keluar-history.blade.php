@extends('layouts.main')

@section('title', 'Riwayat Izin Keluar - ' . ($intern->user->profile->name ?? $intern->user->username))

@section('contents')
    {{-- Memuat sidebar dan navbar --}}
    @include($sidebarView ?? 'layouts.sidebar-assistant')
    @include('layouts.navbar', ['user' => $user ?? null])

    {{-- Wrapper utama untuk konten halaman, mencegah tumpang tindih --}}
    <main class="ml-64 mt-24 p-6">

        {{-- [UI DIROMBAK] Header halaman disamakan dengan halaman riwayat lainnya --}}
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between"> {{-- <-- Typo 'betwaeen' diperbaiki --}}
                <div class="flex items-center gap-4">
                    {{-- [DIUBAH] Ikon, warna, dan judul disesuaikan untuk Izin Keluar --}}
                    <div class="p-3 bg-blue-600 rounded-lg shadow-sm">
                        <i class="fas fa-sign-out-alt text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Riwayat Izin Keluar</h1>
                        <p class="text-gray-600 mt-1">Detail riwayat untuk: <strong>{{ $intern->user->profile->name ?? $intern->user->username }}</strong></p>
                    </div>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('assistant.izin.leave.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke Monitoring
                    </a>
                </div>
            </div>
        </div>

        {{-- [UI DIROMBAK] Menggunakan tabel di dalam kartu agar konsisten --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-4 sm:p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Izin Mulai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Izin Selesai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $log->reason ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-box-open fa-3x text-gray-300"></i>
                                            <p class="mt-4">Tidak ada riwayat izin keluar untuk siswa ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                 {{-- Link Pagination --}}
                 <div class="mt-6">
                    {{ $permitLogs->links() }}
                </div>
            </div>
        </div>
    </main>
@endsection