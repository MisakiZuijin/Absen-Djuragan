@extends('layouts.hr')

@section('title', 'Monitoring Izin Sholat - HR')

@section('contents')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Monitoring Izin Sholat</h1>
            <div class="flex space-x-2">
                <a href="{{ route('hr.monitor.toilet') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    <i class="fas fa-restroom mr-2"></i> Izin Toilet
                </a>
                <a href="{{ route('user.home') }}" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-green-600 text-white">
                <h2 class="text-lg font-semibold">Daftar Status Pemagang</h2>
                <p class="text-sm">Terakhir diperbarui: <span id="last-updated">{{ now()->format('H:i:s') }}</span></p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Divisi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Mulai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">History</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="prayer-table-body">
                        @forelse($interns as $intern)
                            @php
                                $permit = $intern->activePermitLog;
                                // Cek apakah izin aktif adalah izin sholat
                                $isPrayer = $permit && $permit->type === 'prayer';
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ ($interns->currentPage() - 1) * $interns->perPage() + $loop->iteration }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($intern->user->profile_photo)
                                                <img class="h-10 w-10 rounded-full" src="{{ asset('storage/' . $intern->user->profile_photo) }}" alt="">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-600">
                                                    <span>{{ strtoupper(substr($intern->user->name ?? 'A', 0, 1)) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $intern->user->profile->full_name ?? $intern->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $intern->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $intern->division->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($isPrayer)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800">
                                            <i class="fas fa-pray mr-1"></i> Sedang Sholat ({{ $permit->prayer_type ?? 'Wajib' }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Normal
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $isPrayer ? \Carbon\Carbon::parse($permit->start_time)->format('H:i:s') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($isPrayer)
                                        <div id="duration-{{ $permit->id }}" data-start-time="{{ $permit->start_time }}">
                                            <span class="font-mono">Menghitung...</span>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('hr.prayer.history.detail', $intern->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-history mr-1"></i> History
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada data intern yang hadir hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($interns->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $interns->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        // Script Javascript tidak perlu diubah, sudah universal
        function updateDurations() {
            document.querySelectorAll('[id^="duration-"]').forEach(element => {
                const startTimeString = element.getAttribute('data-start-time');
                if (!startTimeString) return;
                const startTime = new Date(startTimeString);
                const now = new Date();
                const durationInSeconds = Math.floor((now - startTime) / 1000);

                if (durationInSeconds < 0) return;
                
                const hours = String(Math.floor(durationInSeconds / 3600)).padStart(2, '0');
                const minutes = String(Math.floor((durationInSeconds % 3600) / 60)).padStart(2, '0');
                const seconds = String(durationInSeconds % 60).padStart(2, '0');
                
                element.querySelector('span').textContent = `${hours}:${minutes}:${seconds}`;
            });
        }
        document.addEventListener('DOMContentLoaded', function() {
            updateDurations();
            setInterval(updateDurations, 1000);
            setInterval(() => { location.reload(); }, 30000);
        });
    </script>
@endsection