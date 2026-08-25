@extends('layouts.main')

@section('title', 'Izin Shalat')

@section('contents')
    @include('layouts.sidebar')
    @include('layouts.navbar')

    <main class="ml-64 mt-24 p-6 md:ml-48 lg:ml-64 bg-gray-50 min-h-screen">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-green-600 rounded-lg shadow-sm">
                        <i class="fas fa-mosque text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Monitoring Izin Shalat</h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="mb-6">
            <input type="text" id="searchInput" placeholder="Cari nama intern..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
        </div>

        <!-- Interns List -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Daftar Intern</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sekolah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">History</th>
                            </tr>
                        </thead>
                        <tbody id="internsTableBody" class="bg-white divide-y divide-gray-200">
                            {{-- [DIUBAH] Menggunakan variabel $interns dari paginator --}}
                            @forelse($interns as $intern)
                                @php
                                    $permit = $intern->activePermitLog;
                                    $isPraying = $permit && $permit->type === 'prayer';
                                @endphp
                                <tr class="intern-row hover:bg-gray-50 transition-all duration-200"
                                    data-name="{{ strtolower($intern->user->profile->full_name ?? '') }}">
                                    {{-- [DIUBAH] Penomoran agar sesuai dengan halaman --}}
                                    <td class="px-6 py-4 whitespace-nowrap">{{ ($interns->currentPage() - 1) * $interns->perPage() + $loop->iteration }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $intern->user->profile->full_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $intern->school->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap" @if($isPraying) id="status-cell-{{ $permit->id }}" @endif>
                                        @if($isPraying)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-mosque mr-1"></i> Sedang Shalat
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-check mr-1"></i> Normal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                                        @if($isPraying)
                                            <div class="text-gray-900 timer-value" data-permit-id="{{ $permit->id }}" data-permit-type="prayer">00:00:00</div>
                                        @else
                                            <div class="text-gray-500">-</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('admin.prayer.history.detail', $intern->id) }}"
                                        class="text-blue-500 hover:text-blue-700"
                                        title="Lihat riwayat izin shalat {{ $intern->user->profile->full_name ?? '' }}">
                                            <i class="fas fa-history"></i> History
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        Tidak ada data intern yang hadir hari ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- [DITAMBAHKAN] Bagian untuk menampilkan link paginasi --}}
               <div class="mt-6 flex items-center justify-center">
    {{ $interns->links('vendor.pagination.custom-pagination') }}
</div>
            </div>
        </div>
    </main>
    
    @include('admin.partials.universal-permit-timer-script')

    {{-- Script untuk filter pencarian --}}
    <script>
        // Peringatan: Script pencarian ini HANYA akan mencari data di halaman yang sedang aktif.
        // Untuk pencarian di seluruh data, diperlukan pencarian sisi server (server-side).
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('keyup', function() {
                const filter = searchInput.value.toLowerCase();
                const rows = document.querySelectorAll('#internsTableBody .intern-row');
                rows.forEach(row => {
                    const internName = row.dataset.name;
                    if (internName.includes(filter)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
@endsection