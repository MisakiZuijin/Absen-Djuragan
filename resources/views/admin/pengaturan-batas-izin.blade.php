@extends('layouts.main')

@section('title', 'Pengaturan Batas Izin')

@section('contents')
    @include('layouts.sidebar')
    @include('layouts.sidebar-pengaturan')
    @include('layouts.navbar')

    <!-- Main Content -->
    <main class="ml-[32rem] mt-24 p-6 bg-gray-50 min-h-screen">

        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Pengaturan Batas Izin</h1>
            <p class="text-gray-600 leading-relaxed">
                Atur batas maksimal harian untuk setiap jenis izin yang dapat diambil oleh intern/pemagang.
            </p>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p class="font-bold">Sukses!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <!-- Form Pengaturan Batas Izin -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <form action="{{ route('admin.pengaturan.izin.update') }}" method="POST">
                @csrf
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Formulir Batas Izin Harian</h3>

                    <div class="space-y-6">
                        {{-- Loop untuk setiap jenis izin --}}
                        @foreach ($permitTypes as $type => $label)
                        <div class="grid grid-cols-3 items-center gap-4">
                            <label for="limit_{{ $type }}" class="col-span-1 text-sm font-medium text-gray-700">
                                {{ $label }}
                            </label>
                            <div class="col-span-2">
                                <div class="flex items-center">
                                    <input type="number"
                                           name="limits[{{ $type }}]"
                                           id="limit_{{ $type }}"
                                           class="w-full max-w-xs px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                           value="{{ $settings[$type]->max_daily_count ?? 0 }}"
                                           min="0">
                                    <span class="ml-3 text-sm text-gray-500">kali per hari</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 text-right rounded-b-xl">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </main>
@endsection

<?php
    $permitTypes = [
        'prayer' => 'Izin Sholat/Ibadah',
        'leave'  => 'Izin Keluar (Keperluan Mendesak)',
    ];
?>
