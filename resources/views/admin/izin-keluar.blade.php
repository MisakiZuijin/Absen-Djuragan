@extends('layouts.main')

@section('title', 'Izin Keluar')

@section('contents')
    @include('layouts.sidebar')
    @include('layouts.navbar')

    <main class="ml-64 mt-20 p-6 md:ml-48 lg:ml-64 bg-slate-50/50 min-h-screen">
        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="p-3 bg-blue-600 text-white rounded-2xl shadow-sm shadow-blue-500/20">
                        <i class="fas fa-sign-out-alt text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">Monitoring Izin Keluar</h1>
                        <p class="text-xs text-slate-500 mt-0.5">Pantau aktivitas izin keluar sementara pemagang secara langsung.</p>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <!-- Card Toolbar / Search -->
                <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Daftar Pemagang Hari Ini</h2>
                        <p class="text-xs text-slate-400">Total data: {{ $interns->total() }} pemagang</p>
                    </div>

                    <!-- Search Input (Compact) -->
                    <div class="relative w-full sm:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-magnifying-glass text-xs"></i>
                        </div>
                        <input type="text" id="searchInput" placeholder="Cari nama pemagang..."
                               class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    </div>
                </div>

                <!-- List Item Izin Keluar (Dibatasi 5 item per halaman) -->
                <div id="internsListContainer" class="space-y-3 mb-6">
                    @forelse($interns as $intern)
                        @php
                            $permit = $intern->activePermitLog;
                            $isLeave = $permit && $permit->type === 'leave';
                            $name = $intern->user->profile->full_name ?? $intern->user->name ?? 'N/A';
                            $divisionName = $intern->division->name ?? 'Umum';
                            $schoolName = $intern->school->name ?? '-';
                        @endphp
                        <div class="intern-card bg-white rounded-2xl border border-slate-200/90 p-4 md:p-5 shadow-xs hover:shadow-md transition-all"
                             data-name="{{ strtolower($name) }}">
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                <!-- Profil Pemagang -->
                                <div class="flex items-start gap-3.5 min-w-[240px]">
                                    <div class="w-11 h-11 rounded-2xl {{ $isLeave ? 'bg-gradient-to-br from-amber-500 to-orange-600' : 'bg-gradient-to-br from-slate-700 to-slate-900' }} text-white flex items-center justify-center font-extrabold text-sm shadow-xs shrink-0">
                                        {{ strtoupper(substr($name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 text-sm md:text-base">{{ $name }}</div>
                                        <div class="text-xs text-slate-500 flex flex-col items-start gap-1 mt-1">
                                            <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-semibold text-[11px] border border-slate-200/60">
                                                {{ $divisionName }}
                                            </span>
                                            <span class="text-slate-500 text-xs truncate max-w-[240px]" title="{{ $schoolName }}">
                                                <i class="fa-solid fa-graduation-cap text-slate-400 mr-1"></i>{{ $schoolName }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status & Durasi Timer -->
                                <div class="flex items-center gap-3 border-t lg:border-t-0 lg:border-l lg:border-r border-slate-100 pt-3 lg:pt-0 lg:px-5">
                                    <div @if($isLeave) id="status-cell-{{ $permit->id }}" @endif>
                                        @if($isLeave)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <i class="fas fa-sign-out-alt text-xs"></i> Sedang Izin Keluar
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i class="fas fa-check text-xs"></i> Hadir Normal
                                            </span>
                                        @endif
                                    </div>

                                    <div class="font-mono text-sm">
                                        @if($isLeave)
                                            <div class="text-blue-700 font-bold timer-value px-2.5 py-1 bg-blue-50 rounded-lg border border-blue-200 inline-block" data-permit-id="{{ $permit->id }}" data-permit-type="leave">00:00:00</div>
                                        @else
                                            <div class="text-slate-400 text-xs px-2 py-1 bg-slate-50 rounded-lg border border-slate-100 inline-block">-</div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Keterangan & Disetujui Oleh -->
                                <div class="flex-1 text-xs text-slate-600 bg-slate-50/70 p-2.5 rounded-xl border border-slate-100">
                                    <div><span class="font-bold text-slate-700">Keterangan:</span> {{ $isLeave ? $permit->description : 'Tidak ada izin aktif' }}</div>
                                    <div class="text-slate-500 mt-1"><span class="font-bold text-slate-700">Disetujui oleh:</span> {{ $isLeave ? ($permit->authorized_by ?? '-') : '-' }}</div>
                                </div>

                                <!-- Aksi Riwayat -->
                                <div class="shrink-0 flex items-center justify-end">
                                    <a href="{{ route('admin.keluar.history.detail', $intern->id) }}"
                                       class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white rounded-xl border border-blue-200 hover:border-blue-600 transition text-xs font-bold shadow-2xs"
                                       title="Lihat riwayat izin keluar {{ $name }}">
                                        <i class="fas fa-history text-xs"></i>
                                        <span>Riwayat</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-sign-out-alt text-4xl text-slate-300 mb-3"></i>
                                <p class="font-bold text-slate-600">Tidak ada pemagang yang hadir</p>
                                <p class="text-xs text-slate-400 mt-1">Belum ada data pemagang yang sedang aktif hari ini.</p>
                            </div>
                        </div>
                    @endforelse

                    <!-- Pagination -->
                    @if($interns->hasPages())
                        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-xs flex items-center justify-center">
                            {{ $interns->links('vendor.pagination.custom-pagination') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    @include('admin.partials.universal-permit-timer-script')

    {{-- Filter Search Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const filter = searchInput.value.toLowerCase();
                    const cards = document.querySelectorAll('#internsListContainer .intern-card');
                    cards.forEach(card => {
                        const internName = card.dataset.name || '';
                        if (internName.includes(filter)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
@endsection