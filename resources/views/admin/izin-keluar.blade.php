@extends('layouts.main')

@section('title', 'Izin Keluar')

@section('contents')
    @include('layouts.sidebar')
    @include('layouts.navbar')

    <main class="ml-64 mt-24 p-6 md:ml-48 lg:ml-64 bg-gray-50 min-h-screen">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-blue-600 rounded-lg shadow-sm">
                        <i class="fas fa-sign-out-alt text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Monitoring Izin Keluar</h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="mb-6">
            <input type="text" id="searchInput" placeholder="Cari nama intern..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disetujui Oleh</th>
                                {{-- Kolom header untuk Aksi sudah ada --}}
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="internsTableBody" class="bg-white divide-y divide-gray-200">
                            {{-- [DIUBAH] Menggunakan variabel $interns dari paginator --}}
                            @forelse($interns as $intern)
                                @php
                                    $permit = $intern->activePermitLog;
                                    $isLeave = $permit && $permit->type === 'leave';
                                @endphp
                                <tr class="intern-row hover:bg-gray-50 transition-all duration-200"
                                    data-name="{{ strtolower($intern->user->profile->full_name ?? '') }}">
                                    {{-- [DIUBAH] Nomor urut agar berlanjut di setiap halaman --}}
                                    <td class="px-6 py-4 whitespace-nowrap">{{ ($interns->currentPage() - 1) * $interns->perPage() + $loop->iteration }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $intern->user->profile->full_name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $intern->school->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap" @if($isLeave) id="status-cell-{{ $permit->id }}" @endif>
                                        @if($isLeave)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-sign-out-alt mr-1"></i> Sedang Izin
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check mr-1"></i> Normal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                                        @if($isLeave)
                                            <div class="text-gray-900 timer-value" data-permit-id="{{ $permit->id }}" data-permit-type="leave">00:00:00</div>
                                        @else
                                            <div class="text-gray-500">-</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $isLeave ? $permit->description : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $isLeave ? $permit->authorized_by : '-' }}</td>
                                    
                                     <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <a href="{{ route('admin.keluar.history.detail', $intern->id) }}"
                                           class="text-blue-500 hover:text-blue-700"
                                           title="Lihat riwayat izin toilet {{ $intern->user->profile->full_name ?? '' }}">
                                            <i class="fas fa-history"></i> History
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    {{-- Colspan sudah benar 8 (jika Aksi dan No digabung), tapi sebaiknya disesuaikan jumlah kolom th = 8 --}}
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
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
@endsection