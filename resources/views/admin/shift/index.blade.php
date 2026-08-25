@extends('layouts.main')

@section('title', 'Daftar Intern per Shift')

@section('contents')
    @include('layouts.sidebar')
    @include('layouts.navbar')

    <main class="ml-64 mt-24 p-6 md:ml-48 lg:ml-64 bg-gray-50 min-h-screen">
        
        <!-- Header Halaman -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-blue-600 rounded-lg shadow-sm">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                    <div>
                        {{-- Judul dinamis berdasarkan shift yang aktif --}}
                        <h1 class="text-3xl font-bold text-gray-900">
                            Daftar Intern di Shift: {{ $shiftNames[$activeShiftName] ?? 'Tidak Ditemukan' }}
                        </h1>
                        <p class="text-gray-600 mt-1">Jadwal untuk hari ini, {{ now()->isoFormat('dddd, D MMMM Y') }}</p>
                    </div>
                </div>
                {{-- Tombol ini tetap relevan --}}
                @if(Route::has('admin.shifts.bulk-update.form'))
                <a href="{{ route('admin.shifts.bulk-update.form') }}" class="px-4 py-2 bg-gray-800 text-white font-semibold rounded-lg shadow-md hover:bg-gray-700 transition-colors">
                    <i class="fas fa-users-cog mr-2"></i> Ganti Shift Massal
                </a>
                @endif
            </div>
        </div>

        <!-- Tombol Filter Shift -->
        <div class="mb-6">
            <p class="text-sm font-semibold text-gray-600 mb-2">Pilih Shift:</p>
            <div class="flex flex-wrap gap-2">
                @if(!empty($shiftNames))
                    @foreach($shiftNames as $shiftName => $displayName)
                        <a href="{{ route('admin.shift.index', $shiftName) }}"
                           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $activeShiftName === $shiftName ? 'bg-blue-600 text-white shadow' : 'bg-white text-gray-700 border hover:bg-gray-100' }}">
                            {{ $displayName }}
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
        
        <!-- Tabel Daftar Intern -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Intern</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIM</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sekolah / Kampus</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($interns as $intern)
                                <tr>
                                    <td class="px-6 py-4">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $intern->user->profile->full_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ $intern->nim ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ $intern->school->name ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-10 text-gray-500">
                                        Tidak ada intern yang dijadwalkan di shift ini untuk hari ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
@endsection