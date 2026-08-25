@extends('layouts.main')

@section('title', 'Persetujuan Log Aktivitas')

@section('contents')
    @include('layouts.sidebar-assistant')
    @include('layouts.navbar', ['user' => $user])

    <main class="ml-64 mt-24 p-6 bg-gray-50 min-h-screen">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-indigo-600 rounded-lg shadow-md">
                        <i class="fa-solid fa-clipboard-check text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Persetujuan Log Aktivitas</h1>
                        <p class="text-gray-600 mt-1">Tinjau laporan aktivitas harian dari siswa magang.</p>
                    </div>
                </div>
                <a href="{{ route('assistant.dashboard') }}" class="bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-300 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-800 px-6 py-4 rounded-lg shadow-md" role="alert">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-xl"></i>
                    <p class="font-bold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Status Tabs -->
        <div class="mb-6 flex gap-2">
            <a href="{{ route('assistant.logactivity', ['status' => 'pending', 'date' => $selectedDate]) }}"
               class="px-4 py-2 rounded-lg border text-sm font-semibold
               {{ $status === 'pending' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                Menunggu Persetujuan
            </a>
            <a href="{{ route('assistant.logactivity', ['status' => 'processed', 'date' => $selectedDate]) }}"
               class="px-4 py-2 rounded-lg border text-sm font-semibold
               {{ $status === 'processed' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                Sudah Diproses
            </a>
        </div>

        <!-- Date Picker -->
        <div class="mb-4">
            <form method="GET" action="{{ route('assistant.logactivity') }}">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="date" name="date" value="{{ $selectedDate }}" class="border rounded px-3 py-2">
                <button type="submit" class="ml-2 px-4 py-2 bg-indigo-600 text-white rounded">Pilih Tanggal</button>
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
