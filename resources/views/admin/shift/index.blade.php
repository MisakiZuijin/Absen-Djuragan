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
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-blue-600"></i>
                    <span>Daftar Pemagang ({{ $shiftNames[$activeShiftName] ?? 'Shift' }})</span>
                </h2>
                <span class="text-xs text-gray-400" id="tableRowCountBadge">Total: {{ $detailSchedules->count() }} Orang</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600" id="shiftInternsTable">
                    <thead class="bg-gray-50/75 text-[11px] uppercase font-semibold text-gray-500 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3.5 w-12 text-center">No</th>
                            <th class="px-5 py-3.5">Nama Pemagang</th>
                            <th class="px-5 py-3.5">Divisi</th>
                            <th class="px-5 py-3.5">Kantor / Brand</th>
                            <th class="px-5 py-3.5">Sekolah / Kampus</th>
                            <th class="px-5 py-3.5 text-center">Jam Shift</th>
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
                        $nim = $intern->nim ?? $user->username ?? '-';
                        @endphp
                        <tr class="hover:bg-blue-50/30 transition intern-row">
                            <!-- No -->
                            <td class="px-5 py-3.5 text-center text-gray-400 font-medium row-number">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Nama Pemagang & NIM -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs uppercase">
                                        {{ mb_substr($fullName, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-gray-900 truncate search-target">{{ $fullName }}</p>
                                        <p class="text-[11px] text-gray-400 truncate">NIM/ID: <span class="font-mono text-gray-600">{{ $nim }}</span></p>
                                    </div>
                                </div>
                            </td>

                            <!-- Divisi -->
                            <td class="px-5 py-3.5">
                                @if($divisionName !== '-')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/80 search-target">
                                    <i class="fa-solid fa-sitemap text-[10px]"></i>
                                    <span>{{ $divisionName }}</span>
                                </span>
                                @else
                                <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>

                            <!-- Kantor / Brand -->
                            <td class="px-5 py-3.5">
                                @if($officeName !== '-')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200/80 search-target">
                                    <i class="fa-solid fa-building text-[10px]"></i>
                                    <span>{{ $officeName }}</span>
                                </span>
                                @else
                                <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>

                            <!-- Sekolah / Kampus -->
                            <td class="px-5 py-3.5 search-target">
                                <div class="flex items-center gap-1.5 text-gray-700">
                                    <i class="fa-solid fa-graduation-cap text-gray-400 text-xs shrink-0"></i>
                                    <span class="truncate font-medium">{{ $schoolName }}</span>
                                </div>
                            </td>

                            <!-- Jam Shift -->
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200/60">
                                    <i class="fa-regular fa-clock text-amber-600 text-[11px]"></i>
                                    <span>
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
        </div>

    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('shiftSearchInput');
        const rows = document.querySelectorAll('.intern-row');
        const countBadge = document.getElementById('tableRowCountBadge');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                let visibleCount = 0;

                rows.forEach(row => {
                    const text = row.innerText.toLowerCase();
                    if (text.includes(query)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (countBadge) {
                    countBadge.textContent = `Menampilkan: ${visibleCount} Orang`;
                }
            });
        }
    });
</script>
@endsection