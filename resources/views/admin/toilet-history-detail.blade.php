@extends('layouts.main')

@section('title', 'Riwayat Izin Toilet: ' . ($intern->user->profile->full_name ?? $intern->user->name))

@section('contents')
    @include($sidebarView ?? 'layouts.sidebar-assistant')
    @include('layouts.navbar', ['user' => $user ?? null])

    <main class="ml-64 mt-24 p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-yellow-500 rounded-lg shadow-sm">
                        <i class="fas fa-restroom text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Riwayat Izin Toilet</h1>
                        <p class="text-gray-600 mt-1">Detail riwayat untuk: <strong>{{ $intern->user->profile->full_name ?? $intern->user->name }}</strong></p>
                    </div>
                </div>
                <div>
                    {{-- [FIXED] Route disesuaikan menjadi 'admin.izin.toilet.index' --}}
                    <a href="{{ route('admin.izinToilet.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
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
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
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
                                            if ($log->end_time) {
                                                $startTime = \Carbon\Carbon::parse($log->start_time);
                                                $endTime = \Carbon\Carbon::parse($log->end_time);
                                                $durationString = $startTime->diff($endTime)->format('%H:%I:%S');
                                            } else {
                                                $durationString = '-';
                                            }
                                        @endphp
                                        {{ $durationString }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        Tidak ada riwayat izin toilet untuk intern ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                 <div class="mt-6">
                    {{ $permitLogs->links() }}
                </div>
            </div>
        </div>
    </main>
@endsection