@extends('layouts.main')

@section('title', 'Manajemen Shift - ' . ($shiftNames[$activeShiftName] ?? 'Shift'))

@section('contents')
<main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 bg-slate-50/50 min-h-screen min-w-0">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- Header Halaman -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-xs border border-gray-100">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                        <i class="fa-solid fa-business-time text-lg"></i>
                    </span>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">
                            Manajemen Shift: {{ $shiftNames[$activeShiftName] ?? 'Shift' }}
                        </h1>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Daftar pemagang aktif beserta divisi dan kantor untuk jadwal hari ini, <span class="font-medium text-gray-700">{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                @if(Route::has('admin.shifts.bulk-update.form'))
                <a href="{{ route('admin.shifts.bulk-update.form') }}"
                    class="inline-flex items-center gap-2 bg-gray-900 hover:bg-gray-800 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition">
                    <i class="fa-solid fa-users-gear text-sm"></i>
                    <span>Ganti Shift Massal</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Alert Notifikasi -->
        @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span class="text-xs sm:text-sm font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold">&times;</button>
        </div>
        @endif

        @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                <span class="text-xs sm:text-sm font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold">&times;</button>
        </div>
        @endif

        <!-- Filter Shift Tabs & Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Pilihan Shift -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mr-1 shrink-0">Shift:</span>
                @if(!empty($shiftNames))
                @foreach($shiftNames as $shiftName => $displayName)
                <a href="{{ route('admin.shift.index', $shiftName) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 flex items-center gap-1.5 {{ $activeShiftName === $shiftName ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200/80 hover:text-gray-900' }}">
                    <i class="fa-solid fa-business-time text-[11px]"></i>
                    <span>{{ $displayName }}</span>
                </a>
                @endforeach
                @endif
            </div>

            <!-- Search Bar -->
            <div class="relative w-full md:w-72 shrink-0">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="shiftSearchInput" placeholder="Cari pemagang, divisi, kantor..."
                    class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition outline-none">
            </div>
        </div>

        <!-- Tabel Daftar Pemagang di Shift Ini -->
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h2 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-blue-600"></i>
                    <span>Daftar Pemagang ({{ $shiftNames[$activeShiftName] ?? 'Shift' }})</span>
                </h2>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100/80 inline-flex items-center gap-1 self-start sm:self-auto" id="tableRowCountBadge">
                    <i class="fa-solid fa-users text-[10px]"></i>
                    <span>Total: {{ $detailSchedules->count() }} Orang</span>
                </span>
            </div>

            <!-- Mobile Scroll Hint -->
            <div class="sm:hidden px-4 py-2 bg-blue-50/70 border-b border-blue-100/80 text-blue-700 text-[11px] flex items-center justify-between">
                <span class="flex items-center gap-1.5 font-medium">
                    <i class="fa-solid fa-arrows-left-right text-blue-500"></i>
                    Geser tabel ke samping untuk melihat kolom lengkap
                </span>
                <i class="fa-solid fa-chevron-right text-[10px] text-blue-400"></i>
            </div>

            <div class="overflow-x-auto touch-pan-x">
                <table class="w-full min-w-[850px] divide-y divide-gray-200 text-left" id="shiftInternsTable">
                    <thead class="bg-gray-50/75 text-gray-600 text-xs uppercase font-semibold">
                        <tr>
                            <th class="py-3.5 px-4 w-14 text-center">No</th>
                            <th class="py-3.5 px-4 min-w-[240px]">Nama Pemagang</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Divisi</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Kantor / Brand</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Sekolah / Kampus</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Jam Shift</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @forelse($detailSchedules as $detail)
                        @php
                        $intern = $detail->schedule->intern ?? null;
                        $user = $intern->user ?? null;
                        $profile = $user->profile ?? null;
                        $fullName = $profile->full_name ?? $user->name ?? 'N/A';
                        $divisionName = $intern->division->name ?? '-';
                        $officeName = $detail->office->name ?? '-';
                        $schoolName = $intern->school->name ?? '-';
                        $nim = $intern->nim ?? $user->username ?? '';
                        @endphp
                        <tr class="hover:bg-blue-50/25 transition intern-row">
                            <!-- No -->
                            <td class="py-3.5 px-4 text-center text-gray-500 font-medium row-number">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Nama Pemagang & NIM -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs uppercase">
                                        {{ strtoupper(substr($fullName, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-gray-900 text-xs sm:text-sm leading-tight search-target">
                                            {{ $fullName }}
                                        </div>
                                        @if($nim && $nim !== '-')
                                            <div class="text-[11px] text-gray-500 font-mono mt-0.5 flex items-center gap-1 search-target">
                                                @if(!str_starts_with(strtoupper($nim), 'NIM'))
                                                    <span class="text-gray-400">NIM:</span>
                                                @endif
                                                <span class="font-medium text-gray-700">{{ $nim }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Divisi -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($divisionName !== '-')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-800 search-target">
                                    <i class="fa-solid fa-sitemap text-[10px] text-gray-500"></i>
                                    <span>{{ $divisionName }}</span>
                                </span>
                                @else
                                <span class="text-xs text-gray-400 italic">Belum Ada Divisi</span>
                                @endif
                            </td>

                            <!-- Kantor / Brand -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($officeName !== '-')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-100 search-target">
                                    <i class="fa-solid fa-building text-[10px] text-emerald-600"></i>
                                    <span>{{ $officeName }}</span>
                                </span>
                                @else
                                <span class="text-xs text-gray-400 italic">Belum Ditentukan</span>
                                @endif
                            </td>

                            <!-- Sekolah / Kampus -->
                            <td class="py-3.5 px-4 whitespace-nowrap search-target">
                                @if($schoolName !== '-')
                                <div class="flex items-center gap-2 text-gray-700">
                                    <i class="fa-solid fa-graduation-cap text-gray-400 text-xs shrink-0"></i>
                                    <span class="font-medium text-xs">{{ $schoolName }}</span>
                                </div>
                                @else
                                <span class="text-xs text-gray-400 italic">Sekolah tidak terdaftar</span>
                                @endif
                            </td>

                            <!-- Jam Shift -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                    <i class="fa-regular fa-clock text-blue-500 text-[11px]"></i>
                                    <span class="font-mono">
                                        {{ \Carbon\Carbon::parse($detail->shift->start_time ?? $currentShift->start_time ?? '00:00')->format('H:i') }} - {{ \Carbon\Carbon::parse($detail->shift->end_time ?? $currentShift->end_time ?? '00:00')->format('H:i') }}
                                    </span>
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr id="emptyRow">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-xl mb-1">
                                        <i class="fa-solid fa-user-slash"></i>
                                    </div>
                                    <p class="font-semibold text-gray-700 text-sm">Tidak ada pemagang di shift ini hari ini.</p>
                                    <p class="text-xs text-gray-400">Silakan gunakan fitur <span class="text-blue-600 font-medium">Ganti Shift Massal</span> untuk menambahkan atau memindahkan jadwal pemagang.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination Bar -->
            @if($detailSchedules->isNotEmpty())
            <div class="p-3.5 sm:p-4 bg-gray-50/75 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-gray-600">
                <span id="shiftPaginationInfo" class="font-medium text-gray-600 text-center md:text-left">
                    Menampilkan 1 - {{ min(50, $detailSchedules->count()) }} dari {{ $detailSchedules->count() }} orang
                </span>
                <div id="shiftPaginationControls" class="flex items-center justify-center flex-wrap gap-1.5">
                    <!-- Navigasi pagination 50 pemagang dirender via JS -->
                </div>
                <button type="button" onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
                    class="text-[11px] font-semibold text-blue-600 hover:underline flex items-center gap-1 cursor-pointer shrink-0">
                    Kembali ke Atas ↑
                </button>
            </div>
            @endif
        </div>

    </div>
</main>

<script>
    let currentShiftPage = 1;
    const shiftPerPage = 50;

    function renderShiftTable(resetPage = false) {
        if (resetPage) {
            currentShiftPage = 1;
        }

        const searchInput = document.getElementById('shiftSearchInput');
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const allRows = Array.from(document.querySelectorAll('#shiftInternsTable tbody tr.intern-row'));
        const matchingRows = [];

        allRows.forEach(row => {
            const text = row.innerText.toLowerCase();
            if (!query || text.includes(query)) {
                matchingRows.push(row);
            } else {
                row.style.display = 'none';
            }
        });

        const totalMatching = matchingRows.length;
        const totalPages = Math.ceil(totalMatching / shiftPerPage) || 1;

        if (currentShiftPage > totalPages) {
            currentShiftPage = totalPages;
        }
        if (currentShiftPage < 1) {
            currentShiftPage = 1;
        }

        const startIndex = (currentShiftPage - 1) * shiftPerPage;
        const endIndex = Math.min(startIndex + shiftPerPage, totalMatching);

        matchingRows.forEach((row, index) => {
            if (index >= startIndex && index < endIndex) {
                row.style.display = '';
                const rowNumberCell = row.querySelector('.row-number');
                if (rowNumberCell) {
                    rowNumberCell.textContent = index + 1;
                }
            } else {
                row.style.display = 'none';
            }
        });

        // Update Top Badge
        const countBadge = document.getElementById('tableRowCountBadge');
        if (countBadge) {
            if (query) {
                countBadge.innerHTML = `<i class="fa-solid fa-filter text-[10px]"></i><span>Hasil: ${totalMatching} dari ${allRows.length} Orang</span>`;
            } else {
                countBadge.innerHTML = `<i class="fa-solid fa-users text-[10px]"></i><span>Total: ${allRows.length} Orang</span>`;
            }
        }

        // Update Bottom Pagination Info
        const infoEl = document.getElementById('shiftPaginationInfo');
        if (infoEl) {
            if (totalMatching > 0) {
                infoEl.textContent = `Menampilkan ${startIndex + 1} - ${endIndex} dari ${totalMatching} orang${query ? ` (difilter dari ${allRows.length})` : ''}`;
            } else {
                infoEl.textContent = `Tidak ada pemagang ditemukan (0 dari ${allRows.length} orang)`;
            }
        }

        renderShiftPagination(totalPages, totalMatching);
    }

    function renderShiftPagination(totalPages, totalMatching) {
        const container = document.getElementById('shiftPaginationControls');
        if (!container) return;

        if (totalMatching === 0 || totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center gap-1 sm:gap-1.5 flex-wrap">';

        // Tombol Sebelumnya
        if (currentShiftPage > 1) {
            html += `
                <button type="button" onclick="goToShiftPage(${currentShiftPage - 1})"
                    class="relative inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer shadow-2xs">
                    <svg class="w-4 h-4 mr-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span>Sebelumnya</span>
                </button>
            `;
        } else {
            html += `
                <span class="relative inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-400 bg-white border border-gray-200 cursor-not-allowed rounded-lg opacity-60 shadow-2xs">
                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span>Sebelumnya</span>
                </span>
            `;
        }

        // Nomor Halaman
        let pages = [];
        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) pages.push(i);
        } else {
            pages.push(1);
            if (currentShiftPage > 3) pages.push('...');
            const start = Math.max(2, currentShiftPage - 1);
            const end = Math.min(totalPages - 1, currentShiftPage + 1);
            for (let i = start; i <= end; i++) {
                if (!pages.includes(i)) pages.push(i);
            }
            if (currentShiftPage < totalPages - 2) pages.push('...');
            if (!pages.includes(totalPages)) pages.push(totalPages);
        }

        pages.forEach(p => {
            if (p === '...') {
                html += `<span class="relative inline-flex items-center px-2 py-1.5 text-xs font-medium text-gray-500">...</span>`;
            } else if (p === currentShiftPage) {
                html += `
                    <span aria-current="page" class="relative inline-flex items-center px-3.5 py-1.5 text-xs font-bold text-white bg-blue-600 border border-blue-600 rounded-lg shadow-2xs z-10">
                        ${p}
                    </span>
                `;
            } else {
                html += `
                    <button type="button" onclick="goToShiftPage(${p})"
                        class="relative inline-flex items-center px-3.5 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer shadow-2xs">
                        ${p}
                    </button>
                `;
            }
        });

        // Tombol Selanjutnya
        if (currentShiftPage < totalPages) {
            html += `
                <button type="button" onclick="goToShiftPage(${currentShiftPage + 1})"
                    class="relative inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer shadow-2xs">
                    <span>Selanjutnya</span>
                    <svg class="w-4 h-4 ml-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
            `;
        } else {
            html += `
                <span class="relative inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-400 bg-white border border-gray-200 cursor-not-allowed rounded-lg opacity-60 shadow-2xs">
                    <span>Selanjutnya</span>
                    <svg class="w-4 h-4 ml-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </span>
            `;
        }

        html += '</nav>';
        container.innerHTML = html;
    }

    function goToShiftPage(page) {
        currentShiftPage = page;
        renderShiftTable(false);
        const table = document.getElementById('shiftInternsTable');
        if (table) {
            table.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('shiftSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                renderShiftTable(true);
            });
        }
        renderShiftTable(true);
    });
</script>
@endsection