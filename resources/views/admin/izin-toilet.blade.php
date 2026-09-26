@extends('layouts.main')

@section('title', 'Izin Toilet')

@section('contents')
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 bg-gray-50 min-h-screen min-w-0">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-purple-600 rounded-lg shadow-sm">
                        <i class="fas fa-restroom text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Monitoring Izin Toilet</h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="mb-6">
            <input type="text" id="searchInput" placeholder="Cari nama intern..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
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
                                    $isToilet = $permit && $permit->type === 'toilet';
                                @endphp
                                <tr class="intern-row hover:bg-gray-50 transition-all duration-200"
                                    data-name="{{ strtolower($intern->user->profile->full_name ?? '') }}">
                                    {{-- [DIUBAH] Penomoran agar sesuai dengan halaman --}}
                                    <td class="px-6 py-4 whitespace-nowrap">{{ ($interns->currentPage() - 1) * $interns->perPage() + $loop->iteration }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $intern->user->profile->full_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $intern->school->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap" @if($isToilet) id="status-cell-{{ $permit->id }}" @endif>
                                        @if($isToilet)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                <i class="fas fa-restroom mr-1"></i> Sedang ke Toilet
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-check mr-1"></i> Normal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                                        @if($isToilet)
                                            <div class="text-gray-900 timer-value"
                                                 data-permit-id="{{ $permit->id }}"
                                                 data-permit-type="toilet">00:00:00</div>
                                        @else
                                            <div class="text-gray-500">-</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('admin.toilet.history.detail', $intern->id) }}"
                                           class="text-blue-500 hover:text-blue-700"
                                           title="Lihat riwayat izin toilet {{ $intern->user->profile->full_name ?? '' }}">
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

                <!-- Pagination -->
                @if($interns->hasPages())
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-3.5 bg-white rounded-2xl border border-slate-200/90 shadow-2xs mt-6">
                    <div class="text-xs text-slate-500 font-medium">
                        Menampilkan <span class="font-bold text-slate-800">{{ $interns->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-800">{{ $interns->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-800">{{ $interns->total() }}</span> pemagang
                    </div>
                    <div class="flex items-center space-x-1.5">
                        {{-- Prev Button --}}
                        @if ($interns->onFirstPage())
                            <button class="bg-gray-200 text-gray-400 px-3 py-1.5 rounded-lg text-xs font-semibold cursor-not-allowed shadow-2xs flex items-center gap-1" disabled>
                                <i class="fas fa-chevron-left text-[10px]"></i>
                                <span>Prev</span>
                            </button>
                        @else
                            <a href="{{ $interns->previousPageUrl() }}" class="bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold shadow-2xs transition flex items-center gap-1">
                                <i class="fas fa-chevron-left text-[10px]"></i>
                                <span>Prev</span>
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        <div class="flex space-x-1">
                            @foreach (range(1, $interns->lastPage()) as $page)
                                @if ($page == $interns->currentPage())
                                    <span class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold shadow-2xs transition">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $interns->url($page) }}" class="px-3 py-1.5 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-xs font-bold shadow-2xs transition">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        </div>

                        {{-- Next Button --}}
                        @if ($interns->hasMorePages())
                            <a href="{{ $interns->nextPageUrl() }}" class="bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold shadow-2xs transition flex items-center gap-1">
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

    @include('admin.partials.universal-permit-timer-script')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Peringatan: Script pencarian ini HANYA akan mencari data di halaman yang sedang aktif.
            const searchInput = document.getElementById('searchInput');
            if(searchInput) {
                searchInput.addEventListener('keyup', function() {
                    let filter = this.value.toLowerCase();
                    let rows = document.querySelectorAll('#internsTableBody .intern-row');

                    rows.forEach(row => {
                        let name = row.getAttribute('data-name');
                        if (name.includes(filter)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
@endsection