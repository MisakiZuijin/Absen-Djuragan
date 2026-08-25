@extends('layouts.outsider')

@section('title', 'Detail Presensi Pemagang')

@section('contents')
    <div class="min-h-screen bg-gray-50 p-6">
        <!-- Header Section -->
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-1">Detail Presensi</h1>
                    <p class="text-lg text-gray-600">{{ $pemagang->user->profile->full_name ?? '-' }}</p>
                </div>
                <a href="{{ route('outsider.presensi.index') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 font-medium shadow-sm">
                    <i class="fa-solid fa-arrow-left mr-2 text-sm"></i> 
                    Kembali ke Dashboard
                </a>
            </div>

            <!-- Intern Info Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pemagang</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Nama Lengkap</p>
                        <p class="text-base font-semibold text-gray-900">{{ $pemagang->user->profile->full_name ?? '-' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Sekolah/Universitas</p>
                        <p class="text-base font-semibold text-gray-900">{{ $pemagang->school->name ?? '-' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Program Magang</p>
                        <p class="text-base font-semibold text-gray-900">{{ $pemagang->division->name ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Filter Periode</h2>
                <form method="GET" action="{{ route('outsider.presensi.show', $pemagang->id) }}"
                    class="flex flex-col sm:flex-row gap-4 items-end">
                    <div class="flex-1 min-w-0">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                        <select name="month"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 font-medium">
                            @foreach($months as $key => $monthName)
                                <option value="{{ $key }}" {{ $selectedMonth == $key ? 'selected' : '' }}>{{ $monthName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-0">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                        <select name="year"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 font-medium">
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 font-medium shadow-sm">
                        <i class="fa-solid fa-filter mr-2"></i> 
                        Terapkan Filter
                    </button>
                </form>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 text-center">
                    <div class="text-2xl font-bold text-gray-900 mb-1">{{ $stats['total'] + $stats['libur'] }}</div>
                    <div class="text-sm font-medium text-gray-600">Total Hari Kerja</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 text-center">
                    <div class="text-2xl font-bold text-green-600 mb-1">{{ $stats['hadir'] }}</div>
                    <div class="text-sm font-medium text-gray-600">Hadir</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 text-center">
                    <div class="text-2xl font-bold text-yellow-600 mb-1">{{ $stats['izin'] + $stats['sakit'] }}</div>
                    <div class="text-sm font-medium text-gray-600">Izin/Sakit</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 text-center">
                    <div class="text-2xl font-bold text-red-600 mb-1">{{ $stats['tidak_hadir'] }}</div>
                    <div class="text-sm font-medium text-gray-600">Tidak Hadir</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 text-center">
                    <div class="text-2xl font-bold text-purple-600 mb-1">{{ $stats['libur'] }}</div>
                    <div class="text-sm font-medium text-gray-600">Hari Libur</div>
                </div>
            </div>

            <!-- Attendance Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Riwayat Presensi</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ $months[$selectedMonth] }} {{ $selectedYear }}</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Hari</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jam Masuk</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jam Pulang</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Shift</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kantor</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($riwayatPresensi as $presensi)
                                @php
                                $currentDate = \Carbon\Carbon::now();
                                $presensiDate = \Carbon\Carbon::parse($presensi->date);
                                $isSunday = $presensiDate->isSunday();
                                $isToday = $presensiDate->isToday();
                                $isPast = $presensiDate->isPast() && !$isToday;
                                $isFuture = $presensiDate->isFuture();

                                $statusClass = 'bg-gray-100 text-gray-800';
                                $statusText = 'Belum Waktunya Absen';
                                $keterangan = '-';

                                if ($isSunday) {
                                    $statusClass = 'bg-purple-100 text-purple-800';
                                    $statusText = 'Libur';
                                    $keterangan = 'Hari Minggu - Libur';
                                } elseif ($presensi->attd_status_id == 2) {
                                    $statusClass = 'bg-green-100 text-green-800';
                                    $statusText = 'Hadir';
                                } elseif ($presensi->attd_status_id == 3) {
                                    $statusClass = 'bg-yellow-100 text-yellow-800';
                                    $statusText = 'Izin';
                                } elseif ($presensi->attd_status_id == 4) {
                                    $statusClass = 'bg-blue-100 text-blue-800';
                                    $statusText = 'Sakit';
                                } elseif ($presensi->attd_status_id == 5) {
                                    $statusClass = 'bg-red-100 text-red-800';
                                    $statusText = 'Tidak Hadir';
                                } else {
                                    if ($isFuture) {
                                        $statusClass = 'bg-gray-100 text-gray-800';
                                        $statusText = 'Belum Waktunya Absen';
                                        $keterangan = 'Jadwal absensi belum dimulai';
                                    } elseif ($isToday) {
                                        $statusClass = 'bg-orange-100 text-orange-800';
                                        $statusText = 'Belum Absen';
                                        $keterangan = 'Hari ini - belum melakukan absensi';
                                    } elseif ($isPast) {
                                        $statusClass = 'bg-red-100 text-red-800';
                                        $statusText = 'Tidak Hadir';
                                        $keterangan = 'Tidak melakukan absensi';
                                    }
                                }
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $presensiDate->format('d M Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-600">{{ $presensiDate->translatedFormat('l') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $isSunday ? '-' : ($presensi->attendance->start_time ?? '-') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $isSunday ? '-' : ($presensi->attendance->end_time ?? '-') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-600">
                                            {{ $isSunday ? '-' : ($presensi->shift->name ?? '-') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-600">
                                            {{ $isSunday ? '-' : ($presensi->schedule->office->name ?? '-') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600 max-w-xs">
                                            @if($isSunday)
                                                Hari Minggu - Libur
                                            @elseif($presensi->attendance && $presensi->attendance->start_time_message)
                                                {{ $presensi->attendance->start_time_message }}
                                            @else
                                                {{ $keterangan }}
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="text-gray-500">
                                            <i class="fa-solid fa-calendar-xmark text-4xl mb-3"></i>
                                            <p class="text-lg font-medium mb-1">Tidak ada data presensi</p>
                                            <p class="text-sm">Tidak ada data presensi untuk bulan yang dipilih.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-600">
                    Menampilkan data untuk: 
                    <span class="font-semibold text-gray-900">{{ $months[$selectedMonth] }} {{ $selectedYear }}</span>
                </div>

                <div class="flex gap-3">
                    @if($prevMonth)
                        <a href="{{ route('outsider.presensi.show', ['intern_id' => $pemagang->id, 'month' => $prevMonth->format('m'), 'year' => $prevMonth->format('Y')]) }}"
                            class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 font-medium shadow-sm">
                            <i class="fa-solid fa-chevron-left mr-2 text-sm"></i> 
                            Bulan Sebelumnya
                        </a>
                    @endif

                    @if($nextMonth && $nextMonth->lte(\Carbon\Carbon::now()->endOfMonth()))
                        <a href="{{ route('outsider.presensi.show', ['intern_id' => $pemagang->id, 'month' => $nextMonth->format('m'), 'year' => $nextMonth->format('Y')]) }}"
                            class="inline-flex items-center px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 font-medium shadow-sm">
                            Bulan Selanjutnya 
                            <i class="fa-solid fa-chevron-right ml-2 text-sm"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection