@extends('layouts.main')

@section('title', 'Manajemen Raise Hand & Presentasi')

@section('contents')
    <div class="ml-64 mt-20 p-4 md:p-6 bg-gray-50 min-h-screen">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-blue-600 rounded-xl shadow-sm text-white">
                        <i class="fa-solid fa-hand-paper text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Manajemen Raise Hand & Presentasi</h1>
                        <p class="text-gray-500 text-sm mt-0.5">Kelola pertanyaan, permintaan tugas baru, jadwal presentasi siswa, dan berikan tanggapan / evaluasi</p>
                    </div>
                </div>
                <!-- Auto-refresh indicator -->
                <div class="flex items-center gap-2 text-xs bg-white border border-gray-200 px-3 py-2 rounded-xl shadow-sm">
                    <div id="refreshIndicator" class="w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-gray-600 font-medium">Auto-refresh aktif</span>
                </div>
            </div>
        </div>

        @livewire('admin.raise-hand-manager')
    </div>

    @include('admin.partials.raise-hand-modals')
@endsection
