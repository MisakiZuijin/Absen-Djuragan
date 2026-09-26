@extends('layouts.hr')

@section('title', 'History Izin Sholat - ' . $intern->user->name)

@section('contents')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">History Izin Sholat</h1>
            <p class="text-gray-600">{{ $intern->user->name }} - {{ $intern->division->name ?? 'N/A' }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('hr.monitor.prayer') }}"
                class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            <a href="{{ route('user.home') }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                <i class="fas fa-home mr-2"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-green-600 text-white">
            <h2 class="text-lg font-semibold">Riwayat Izin Sholat</h2>
            <p class="text-sm">Total: {{ $permitLogs->total() }} izin</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu
                            Mulai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu
                            Selesai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Durasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis
                            Sholat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($permitLogs as $index => $permit)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            {{ ($permitLogs->currentPage() - 1) * $permitLogs->perPage() + $index + 1 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $permit->created_at->format('d-m-Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $permit->start_time->format('H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $permit->end_time ? $permit->end_time->format('H:i:s') : 'Masih berlangsung' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($permit->end_time)
                            @php
                            $duration = $permit->start_time->diff($permit->end_time);
                            $hours = $duration->h;
                            $minutes = $duration->i;
                            $seconds = $duration->s;
                            @endphp
                            @if($hours > 0)
                            {{ $hours }}j {{ $minutes }}m {{ $seconds }}d
                            @elseif($minutes > 0)
                            {{ $minutes }}m {{ $seconds }}d
                            @else
                            {{ $seconds }}d
                            @endif
                            @else
                            -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $permit->prayer_type ?? 'Sholat' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($permit->end_time)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Selesai
                            </span>
                            @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i> Berlangsung
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            Tidak ada riwayat izin sholat
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($permitLogs->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $permitLogs->links('vendor.pagination.custom-pagination') }}
        </div>
        @endif
    </div>

    <!-- Statistics Card -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-list text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Izin</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $permitLogs->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Izin Selesai</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $permitLogs->where('end_time', '!=', null)->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Sedang Berlangsung</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $permitLogs->where('end_time', null)->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-mosque text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Jenis Sholat Unik</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $permitLogs->pluck('prayer_type')->unique()->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Prayer Type Distribution -->
    @if($permitLogs->count() > 0)
    <div class="mt-6 bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribusi Jenis Sholat</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
            $prayerTypes = $permitLogs->groupBy('prayer_type')->map->count()->sortDesc();
            @endphp
            @foreach($prayerTypes as $type => $count)
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-700">{{ $type ?? 'Tidak Diketahui' }}</span>
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full">
                        {{ $count }}
                    </span>
                </div>
                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full"
                        @style(['width: ' . (($count / max(1, $permitLogs->count())) * 100) . '%'])></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    {{ number_format(($count / $permitLogs->count()) * 100, 1) }}% dari total
                </p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection