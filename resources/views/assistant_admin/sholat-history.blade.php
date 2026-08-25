@extends('layouts.main')

@section('title', 'Riwayat Izin Shalat: ' . ($intern->user->profile->full_name ?? $intern->user->name))

@section('contents')
    {{-- Memuat sidebar dan navbar --}}
    @include($sidebarView ?? 'layouts.sidebar-assistant')
    @include('layouts.navbar', ['user' => $user ?? null])

    {{-- Wrapper utama untuk konten halaman, mencegah tumpang tindih --}}
    <main class="ml-64 mt-24 p-6">

        <!-- Header Halaman -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    {{-- [DIUBAH] Ikon dan judul disesuaikan untuk Izin Shalat --}}
                    <div class="p-3 bg-green-600 rounded-lg shadow-sm">
                        <i class="fas fa-mosque text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Riwayat Izin Shalat</h1>
                        <p class="text-gray-600 mt-1">Detail riwayat untuk: <strong>{{ $intern->user->profile->full_name ?? $intern->user->name }}</strong></p>
                    </div>
                </div>
                <div class="mt-4 sm:mt-0">
                    {{-- [DIUBAH] Tombol kembali mengarah ke halaman monitoring shalat --}}
                    <a href="{{ route('assistant.izin.prayer.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke Monitoring
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat di dalam Kartu -->
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
                                {{-- [DIHAPUS] Kolom Alasan tidak diperlukan untuk izin shalat --}}
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
                                    {{-- [DIUBAH] Colspan disesuaikan menjadi 4 karena kolom Alasan dihapus --}}
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-box-open fa-3x text-gray-300"></i>
                                            <p class="mt-4">Tidak ada riwayat izin shalat untuk siswa ini.</p>
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