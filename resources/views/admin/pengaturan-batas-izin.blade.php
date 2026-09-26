@extends('layouts.main')

@section('title', 'Pengaturan Batas Izin')

@section('contents')
    @include('layouts.sidebar-pengaturan')

    <!-- Main Content -->
    <main class="ml-0 lg:ml-[32rem] mt-2 lg:mt-20 p-3 sm:p-6 min-w-0 max-w-full overflow-x-hidden">
        <div class="w-full max-w-full min-w-0 space-y-6">

            <!-- Header Section -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs">
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Manage Batas Izin</h1>
                <p class="text-gray-500 text-sm">
                    Atur batas maksimal harian untuk setiap jenis izin yang dapat diambil oleh intern/pemagang.
                </p>
            </div>

            @if (session('success'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 p-4 rounded-xl shadow-xs" role="alert">
                    <p class="font-bold">Sukses!</p>
                    <p class="text-xs sm:text-sm">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Form Pengaturan Batas Izin -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <form action="{{ route('admin.pengaturan.izin.update') }}" method="POST">
                    @csrf
                    <div class="p-6">
                        <h3 class="text-base font-bold text-gray-900 mb-6">Formulir Batas Izin Harian</h3>

                        <div class="space-y-6 max-w-2xl">
                            {{-- Loop untuk setiap jenis izin --}}
                            @foreach ($permitTypes as $type => $label)
                            <div class="grid grid-cols-1 sm:grid-cols-3 sm:items-center gap-2 sm:gap-4">
                                <label for="limit_{{ $type }}" class="text-xs sm:text-sm font-semibold text-gray-700">
                                    {{ $label }}
                                </label>
                                <div class="sm:col-span-2">
                                    <div class="flex items-center gap-3">
                                        <input type="number"
                                               name="limits[{{ $type }}]"
                                               id="limit_{{ $type }}"
                                               class="w-32 px-3.5 py-2 text-xs sm:text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                               value="{{ $settings[$type]->max_daily_count ?? 0 }}"
                                               min="0">
                                        <span class="text-xs text-gray-500 font-medium">kali per hari</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-100 text-right">
                        <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-xs font-semibold text-xs sm:text-sm transition">
                            <i class="fas fa-save mr-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
@endsection

<?php
    $permitTypes = [
        'prayer' => 'Izin Sholat/Ibadah',
        'leave'  => 'Izin Keluar (Keperluan Mendesak)',
    ];
?>
