@extends('layouts.main')

@section('title', 'Monitoring Raise Hand & Presentasi')

@section('contents')
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-4 sm:p-6 lg:p-8 bg-gray-50 min-h-screen min-w-0">
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header Section -->
            <div class="mb-6 md:mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <div class="p-3 sm:p-4 bg-indigo-600 rounded-2xl shadow-sm text-white shrink-0">
                            <i class="fa-solid fa-hand-paper text-xl sm:text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">Monitoring Raise Hand & Presentasi</h1>
                            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Pantau status pertanyaan, tugas baru, antrean presentasi, dan riwayat siswa magang</p>
                        </div>
                    </div>
                    <!-- Auto-refresh indicator -->
                    <div class="self-start sm:self-auto flex items-center gap-2 text-xs bg-white border border-gray-200 px-3 py-2 rounded-xl shadow-xs">
                        <div id="refreshIndicator" class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></div>
                        <span class="text-gray-600 font-medium">Auto-refresh aktif</span>
                    </div>
                </div>
            </div>

            <!-- Livewire Component for Raise Hand Management -->
            @livewire('admin.raise-hand-manager')
        </div>
    </main>

    @include('admin.partials.raise-hand-modals')
@endsection