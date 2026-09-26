@extends('layouts.main')

@section('title', 'Persetujuan Log Aktivitas')

@section('contents')
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-4 sm:p-6 lg:p-8 bg-gray-50 min-h-screen min-w-0">
        <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header Section -->
        <div class="mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 mb-4">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="p-3 sm:p-4 bg-indigo-600 rounded-2xl shadow-sm shrink-0">
                        <i class="fa-solid fa-clipboard-check text-white text-xl sm:text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800">Persetujuan Log Aktivitas</h1>
                        <p class="text-xs sm:text-sm text-gray-600 mt-0.5">Tinjau laporan aktivitas harian dari siswa magang.</p>
                    </div>
                </div>
                <a href="{{ route('assistant.dashboard') }}" class="self-start sm:self-auto bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-3.5 sm:px-4 border border-gray-300 rounded-xl text-xs sm:text-sm shadow-xs transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-800 p-4 rounded-xl shadow-xs" role="alert">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-xl shrink-0"></i>
                    <p class="font-bold text-xs sm:text-sm">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Status Tabs & Date Picker Container -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('assistant.logactivity', ['status' => 'pending', 'date' => $selectedDate]) }}"
                   class="px-3.5 py-2 rounded-xl border text-xs sm:text-sm font-semibold transition
                   {{ $status === 'pending' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                    Menunggu Persetujuan
                </a>
                <a href="{{ route('assistant.logactivity', ['status' => 'processed', 'date' => $selectedDate]) }}"
                   class="px-3.5 py-2 rounded-xl border text-xs sm:text-sm font-semibold transition
                   {{ $status === 'processed' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                    Sudah Diproses
                </a>
            </div>

            <!-- Date Picker -->
            <form method="GET" action="{{ route('assistant.logactivity') }}" class="flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="date" name="date" value="{{ $selectedDate }}" class="border border-gray-200 bg-white rounded-xl px-3 py-1.5 text-xs sm:text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition shadow-xs">Pilih</button>
            </form>
        </div>

        <!-- Selected Date Header -->
        <div class="mb-4">
            <h2 class="text-xl font-bold text-gray-700">
                Log Aktivitas Tanggal: {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}
            </h2>
        </div>

        <!-- Search and Filter Section -->
        <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-search text-gray-400"></i>
                </div>
                <input type="text" id="logSearchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Cari nama siswa atau isi aktivitas...">
            </div>
        </div>

        <!-- Log Activity Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siswa</th>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktivitas</th>
                            <th class="py-3 px-6 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="py-3 px-6 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="logTableBody" class="divide-y divide-gray-200">
                        @forelse ($logActivities as $log)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                            @php
                                                $fullName = $log->detailSchedule?->schedule?->intern?->user?->profile?->full_name ?? 'U';
                                                $initial = strtoupper(substr($fullName, 0, 1));
                                            @endphp
                                            <span class="font-bold text-indigo-600">{{ $initial }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 student-name">
                                                {{ $log->detailSchedule?->schedule?->intern?->user?->profile?->full_name ?? 'Data Siswa Tidak Ditemukan' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $log->detailSchedule?->schedule?->intern?->user?->email ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($log->date)->translatedFormat('d F Y') }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="text-sm text-gray-900 activity-description">{{ Str::limit($log->activity, 80) }}</div>
                                </td>
                                <td class="py-4 px-6 text-center whitespace-nowrap">
                                    @if ($log->status?->name === 'Accepted')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                                    @elseif ($log->status?->name === 'Rejected')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center whitespace-nowrap">
                                    <a href="{{ route('assistant.logactivity.confirm', $log->id) }}"
                                       class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-1 px-3 rounded-md text-sm transition-colors">
                                        <i class="fa-solid fa-eye mr-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10">
                                    <div class="text-gray-400">
                                        <i class="fa-solid fa-folder-open text-4xl mb-3"></i>
                                        <p class="font-semibold text-gray-600">Tidak ada log aktivitas untuk ditinjau.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="noResults" class="text-center py-10 hidden">
                <div class="text-gray-400">
                    <i class="fa-solid fa-search text-4xl mb-3"></i>
                    <p class="font-semibold text-gray-600">Pencarian tidak ditemukan.</p>
                </div>
            </div>
        </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('logSearchInput');
            const tableBody = document.getElementById('logTableBody');
            const noResults = document.getElementById('noResults');
            const rows = tableBody.getElementsByTagName('tr');

            searchInput.addEventListener('keyup', function () {
                const filter = searchInput.value.toLowerCase();
                let visibleRows = 0;

                for (let i = 0; i < rows.length; i++) {
                    const studentNameCell = rows[i].querySelector('.student-name');
                    const descriptionCell = rows[i].querySelector('.activity-description');

                    if (studentNameCell || descriptionCell) {
                        const studentName = studentNameCell.textContent.toLowerCase();
                        const description = descriptionCell.textContent.toLowerCase();

                        if (studentName.includes(filter) || description.includes(filter)) {
                            rows[i].style.display = "";
                            visibleRows++;
                        } else {
                            rows[i].style.display = "none";
                        }
                    }
                }

                if (visibleRows === 0) {
                    noResults.style.display = 'block';
                } else {
                    noResults.style.display = 'none';
                }
            });
        });
    </script>
@endsection
