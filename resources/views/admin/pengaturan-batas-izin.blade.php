@extends('layouts.main')

@section('title', 'Pengaturan Batas Izin & Durasi')

@section('contents')
    @include('layouts.sidebar-pengaturan')

    <!-- Main Content -->
    <main class="ml-0 lg:ml-[32rem] mt-2 lg:mt-20 p-3 sm:p-6 min-w-0 max-w-full overflow-x-hidden">
        <div class="w-full max-w-full min-w-0 space-y-6">

            <!-- Header Section -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-0.5">Pengaturan Batas Izin & Durasi</h1>
                        <p class="text-gray-500 text-xs sm:text-sm">
                            Atur batas frekuensi harian dan batas maksimal durasi per sesi untuk izin operasional pemagang.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Info Card Konsekuensi Hutang Jam -->
            <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-4 sm:p-5 text-amber-950 shadow-xs flex items-start gap-3.5">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5 text-base font-bold">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="space-y-0.5 text-xs sm:text-sm leading-relaxed">
                    <div class="font-bold text-amber-900">Ketentuan Hutang Jam:</div>
                    <p class="text-amber-800">
                        Kelebihan durasi pada <strong>Izin Sholat</strong> dan <strong>Izin Toilet</strong> otomatis masuk ke <strong>Hutang Jam</strong> pemagang.
                    </p>
                </div>
            </div>

            @if (session('success'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 p-4 rounded-xl shadow-xs flex items-center gap-2.5 text-xs sm:text-sm" role="alert">
                    <i class="fas fa-check-circle text-emerald-600 text-base shrink-0"></i>
                    <div>
                        <span class="font-bold">Sukses:</span> {{ session('success') }}
                    </div>
                </div>
            @endif

            <!-- Form Pengaturan Batas Izin -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <form action="{{ route('admin.pengaturan.izin.update') }}" method="POST">
                    @csrf
                    <div class="p-5 sm:p-6 space-y-6">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Konfigurasi Per Kategori Izin</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Tentukan batas frekuensi dan durasi per kategori</p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            @foreach ($permitTypes as $type => $info)
                            @php
                                $curSetting = $settings[$type] ?? null;
                                $curCount = $curSetting ? $curSetting->max_daily_count : $info['default_count'];
                                $curDuration = $curSetting ? $curSetting->max_duration_minutes : $info['default_duration'];
                            @endphp

                            <div class="p-4 sm:p-5 rounded-2xl border border-gray-200/80 bg-gray-50/40 hover:bg-gray-50 transition-all space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white shadow-2xs border border-gray-200 flex items-center justify-center text-base {{ $type === 'prayer' ? 'text-emerald-600' : ($type === 'toilet' ? 'text-blue-600' : 'text-amber-600') }}">
                                        <i class="{{ $info['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm sm:text-base text-gray-900">{{ $info['label'] }}</h4>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-gray-200/60">
                                    <!-- 1. Batas Frekuensi Harian -->
                                    <div>
                                        @if($info['has_daily_count'])
                                        <label for="limit_{{ $type }}" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                            Batas Frekuensi Harian
                                        </label>
                                        <div class="flex items-center gap-2">
                                            <input type="number"
                                                   name="limits[{{ $type }}]"
                                                   id="limit_{{ $type }}"
                                                   class="w-28 sm:w-32 px-3.5 py-2 text-xs sm:text-sm font-bold text-gray-900 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white shadow-2xs"
                                                   value="{{ $curCount }}"
                                                   min="0" required>
                                            <span class="text-xs font-medium text-gray-500">kali / hari</span>
                                        </div>
                                        <p class="text-[11px] text-gray-400 mt-1 italic">* 0 = tidak dibatasi</p>
                                        @else
                                        <span class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                            Batas Frekuensi Harian
                                        </span>
                                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 py-2">
                                            <i class="fas fa-infinity text-blue-600"></i>
                                            <span>Tidak dibatasi frekuensi ke toilet</span>
                                        </div>
                                        <input type="hidden" name="limits[{{ $type }}]" value="0">
                                        @endif
                                    </div>

                                    <!-- 2. Batas Maksimal Durasi (Menit) -->
                                    <div>
                                        @if($info['has_duration_limit'])
                                        <label for="duration_{{ $type }}" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                            Batas Maksimal Durasi Per Sesi
                                        </label>
                                        <div class="flex items-center gap-2">
                                            <input type="number"
                                                   name="duration_limits[{{ $type }}]"
                                                   id="duration_{{ $type }}"
                                                   class="w-28 sm:w-32 px-3.5 py-2 text-xs sm:text-sm font-bold text-gray-900 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white shadow-2xs"
                                                   value="{{ $curDuration }}"
                                                   min="0" required>
                                            <span class="text-xs font-medium text-gray-500">menit / sesi</span>
                                        </div>
                                        <p class="text-[11px] text-amber-700 font-medium mt-1">
                                            * Jika melebihi {{ $curDuration }} menit, kelebihan waktu otomatis masuk ke hutang jam.
                                        </p>
                                        @else
                                        <span class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                            Batas Maksimal Durasi Per Sesi
                                        </span>
                                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 py-2">
                                            <i class="fas fa-handshake text-amber-600"></i>
                                            <span>Disepakati manual saat Admin menyetujui</span>
                                        </div>
                                        <input type="hidden" name="duration_limits[{{ $type }}]" value="0">
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-gray-500">Perubahan akan langsung berlaku pada sesi izin berikutnya.</span>
                        <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl shadow-xs hover:shadow font-semibold text-xs sm:text-sm transition cursor-pointer">
                            <i class="fas fa-save mr-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
@endsection
