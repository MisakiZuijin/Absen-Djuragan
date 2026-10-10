@extends('layouts.main')

@section('title', 'Pusat Aturan & Otomatisasi Jadwal')

@section('contents')
<main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-5 lg:p-6 min-h-screen bg-slate-50/60 text-slate-800 space-y-4 sm:space-y-6 min-w-0 max-w-full overflow-x-hidden">

    <!-- Header Section -->
    <div class="bg-white rounded-2xl p-4 sm:p-6 border border-slate-200/80 shadow-2xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-6 h-6 text-amber-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-lg sm:text-2xl font-bold text-slate-900 tracking-tight">
                            Pusat Aturan & Simulator Penjadwalan
                        </h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700 font-mono">
                            Core Engine 2.0
                        </span>
                    </div>
                    <p class="text-slate-500 text-xs sm:text-sm mt-0.5 leading-relaxed">
                        Sistem penjadwalan cerdas berbasis jarak kos (Haversine), batas kuota meja fisik per shift, relasi divisi, dan pemisahan konflik.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Alerts -->
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2.5">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 p-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2.5">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 p-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    <!-- 5 Main Navigation Tabs -->
    <div class="bg-white rounded-2xl p-1.5 sm:p-2 border border-slate-200/80 shadow-2xs w-full">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-1.5 sm:gap-2 w-full" id="tabs-container">
            <button type="button" onclick="switchTab('tab-matrix')" id="btn-tab-matrix"
                class="tab-btn w-full flex items-center justify-center gap-1.5 px-2.5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold text-center transition-all duration-200 bg-blue-600 text-white shadow-xs">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span class="truncate">1. Matriks Kantor</span>
            </button>

            <button type="button" onclick="switchTab('tab-capacities')" id="btn-tab-capacities"
                class="tab-btn w-full flex items-center justify-center gap-1.5 px-2.5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-center transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />
                </svg>
                <span class="truncate">2. Kuota Meja</span>
            </button>

            <button type="button" onclick="switchTab('tab-division-rules')" id="btn-tab-division-rules"
                class="tab-btn w-full flex items-center justify-center gap-1.5 px-2.5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-center transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0M3.75 18H7.5m6-6h6m-6 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0M3.75 12H10.5" />
                </svg>
                <span class="truncate">3. Aturan Divisi</span>
            </button>

            <button type="button" onclick="switchTab('tab-conflicts')" id="btn-tab-conflicts"
                class="tab-btn w-full flex items-center justify-center gap-1.5 px-2.5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-center transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
                <span class="truncate">4. Pasangan Konflik</span>
            </button>

            <button type="button" onclick="switchTab('tab-intern-policies')" id="btn-tab-intern-policies"
                class="tab-btn w-full flex items-center justify-center gap-1.5 px-2.5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-center transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                <span class="truncate">5. Kebijakan Anggota</span>
            </button>

            <button type="button" onclick="switchTab('tab-simulator')" id="btn-tab-simulator"
                class="tab-btn w-full flex items-center justify-center gap-1.5 px-2.5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-center transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 00-1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                </svg>
                <span class="truncate">6. Simulator Jadwal</span>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: MATRIKS CHECKLIST DIVISI PER KANTOR                                 -->
    <!-- ========================================================================= -->
    <div id="tab-matrix" class="tab-content space-y-4">
        <form action="{{ route('admin.automation-rules.save-matrix') }}" method="POST">
            @csrf
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            Matriks Checklist Penempatan Divisi per Kantor
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Centang kantor yang diizinkan untuk setiap divisi. Jika kotak tidak dicentang, algoritma <strong>tidak akan pernah</strong> menempatkan pemagang divisi tersebut di kantor tersebut.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" onclick="toggleAllMatrix(true)"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                            Centang Semua
                        </button>
                        <button type="button" onclick="toggleAllMatrix(false)"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                            Hapus Semua
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span>Simpan Matriks</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200 text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="p-3.5 sm:p-4 min-w-[200px]">Divisi</th>
                                @foreach($offices as $office)
                                <th class="p-3.5 sm:p-4 text-center min-w-[150px]">
                                    <div class="flex flex-col items-center">
                                        <span class="font-bold text-slate-900">{{ $office->name }}</span>
                                        <span class="text-[10px] text-slate-400 font-normal">Kapasitas: {{ $office->capacity }} Meja</span>
                                    </div>
                                </th>
                                @endforeach
                                <th class="p-3.5 sm:p-4 text-center w-28">Aksi Baris</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($divisions as $division)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3.5 sm:p-4 font-semibold text-slate-800">
                                    <div class="flex items-center gap-3">
                                        @if(!empty($division->icon))
                                        <img src="{{ asset('img/' . $division->icon) }}" alt="{{ $division->name }}"
                                            class="w-8 h-8 rounded-lg object-contain bg-slate-50 border border-slate-200 p-1 shrink-0">
                                        @else
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0 border border-blue-100">
                                            <i class="fa-solid fa-sitemap"></i>
                                        </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="text-slate-900 font-bold truncate">{{ $division->name }}</div>
                                            <div class="text-[11px] text-slate-400 font-normal">{{ $division->intern->count() }} Anggota</div>
                                        </div>
                                    </div>
                                </td>

                                @foreach($offices as $office)
                                @php
                                $isChecked = isset($matrixMap[$division->id][$office->id])
                                ? $matrixMap[$division->id][$office->id]
                                : false;
                                @endphp
                                <td class="p-3.5 sm:p-4 text-center">
                                    <label class="inline-flex items-center justify-center cursor-pointer select-none">
                                        <input type="checkbox"
                                            name="matrix[{{ $division->id }}][{{ $office->id }}]"
                                            value="1"
                                            {{ $isChecked ? 'checked' : '' }}
                                            class="matrix-checkbox matrix-row-{{ $division->id }} matrix-col-{{ $office->id }} w-5 h-5 text-blue-600 rounded-md border-slate-300 focus:ring-blue-500 cursor-pointer transition">
                                    </label>
                                </td>
                                @endforeach

                                <td class="p-3.5 sm:p-4 text-center">
                                    <button type="button" data-division-id="{{ $division->id }}" onclick="toggleRowMatrix(this.dataset.divisionId)"
                                        class="px-2 py-1 text-[11px] font-semibold text-slate-600 hover:text-blue-600 bg-slate-100 hover:bg-blue-50 rounded-md transition"
                                        title="Balik centang baris ini">
                                        Toggle
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ count($offices) + 2 }}" class="p-6 text-center text-slate-400">
                                    Belum ada data divisi. Silakan tambahkan divisi terlebih dahulu.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: SHIFT AKTIF & KUOTA MEJA KANTOR                                    -->
    <!-- ========================================================================= -->
    <div id="tab-capacities" class="tab-content hidden space-y-4">
        <form action="{{ route('admin.automation-rules.save-capacities') }}" method="POST">
            @csrf
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />
                            </svg>
                            Kapasitas Meja Universal & Shift Operasional per Kantor
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Atur <strong>meja fisik kantor</strong> sebagai batas maksimal, lalu tentukan <strong>kuota kursi tiap shift</strong> (tidak boleh melebihi meja fisik). Contoh: 15 meja → Pagi 10 kursi, Siang 5 kursi.
                        </p>
                    </div>

                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition self-start sm:self-auto shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span>Simpan Pengaturan Meja & Shift</span>
                    </button>
                </div>

                <div class="p-4 sm:p-6 space-y-6">
                    @foreach($offices as $office)
                    <div class="border border-slate-200 rounded-2xl p-4 sm:p-5 bg-white shadow-2xs hover:border-slate-300 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900">{{ $office->name }}</h3>
                                    <p class="text-xs text-slate-500">{{ $office->address ?? 'Alamat kantor' }}</p>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                <!-- 50:50 Hybrid Toggle Checklist per Kantor -->
                                <label class="flex items-center gap-2.5 bg-purple-50 hover:bg-purple-100/70 px-3.5 py-2.5 rounded-xl border border-purple-200 cursor-pointer transition select-none">
                                    <input type="checkbox" name="office_hybrid[{{ $office->id }}]" value="1"
                                        class="w-4 h-4 text-purple-600 rounded border-purple-300 focus:ring-purple-500"
                                        {{ $office->is_hybrid_enabled ? 'checked' : '' }}>
                                    <div>
                                        <div class="text-xs font-bold text-purple-900 flex items-center gap-1.5">
                                            <span>Izinkan Skema 50:50 Bergilir</span>
                                            <span class="px-1.5 py-0.5 rounded text-[9px] bg-purple-200 text-purple-800 font-extrabold uppercase">Grup A & B</span>
                                        </div>
                                        <div class="text-[10px] text-purple-700 font-medium">Jika kursi penuh, pasangkan sisa pemagang 50% WFO selang-seling</div>
                                    </div>
                                </label>

                                <div class="flex items-center gap-3 bg-blue-50/70 px-4 py-2.5 rounded-xl border border-blue-200">
                                    <div>
                                        <label for="office_cap_{{ $office->id }}" class="text-xs font-bold text-blue-900 block">
                                            Kapasitas Meja Fisik Kantor:
                                        </label>
                                        <span class="text-[10px] text-blue-700">Batas fisik maksimal orang hadir bersamaan</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <input type="number" min="1" max="500"
                                            id="office_cap_{{ $office->id }}"
                                            name="office_capacities[{{ $office->id }}]"
                                            value="{{ $office->capacity ?? 15 }}"
                                            data-office-id="{{ $office->id }}"
                                            oninput="handleOfficeCapacityChange(this)"
                                            class="w-20 px-2.5 py-1 text-sm font-bold text-center bg-white border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-2xs">
                                        <span class="text-xs font-semibold text-blue-800">meja</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Per-shift Operational Grid -->
                        <div>
                            <div class="text-xs font-bold text-slate-700 mb-2 flex items-center justify-between">
                                <span>Shift yang Beroperasi di Kantor Ini:</span>
                                <span class="text-[11px] text-slate-400 font-normal">Aktifkan shift yang dibuka untuk kantor ini</span>
                            </div>

                            <div class="mb-3.5 p-2.5 bg-blue-50/70 border border-blue-200/80 rounded-xl text-[11px] text-blue-800 leading-relaxed flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <strong>Prinsip Meja Fisik Bersamaan:</strong> Meja fisik kantor (<span class="office-cap-label-{{ $office->id }} font-bold">{{ $office->capacity ?? 15 }} Meja</span>) dipakai bersama di jam shift yang saling beririsan. Algoritma otomatis membatasi agar total pemagang yang duduk bersamaan tidak pernah melebihi kapasitas fisik kantor ini.
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                                @foreach($shifts as $shift)
                                @php
                                $capData = $capacityMap[$office->id][$shift->id] ?? [
                                'max_capacity' => (int) $office->capacity,
                                'is_enabled' => true
                                ];
                                $isEnabled = (bool) $capData['is_enabled'];
                                @endphp
                                <div class="p-3.5 rounded-xl border {{ $isEnabled ? 'border-slate-200 bg-slate-50/50' : 'border-dashed border-slate-200 bg-slate-100/50 opacity-60' }} flex flex-col justify-between gap-3 transition">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <div class="font-bold text-xs sm:text-sm text-slate-900">{{ $shift->name }}</div>
                                            <div class="text-[11px] text-slate-500 font-mono">
                                                {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                            </div>
                                        </div>

                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox"
                                                name="capacities[{{ $office->id }}][{{ $shift->id }}][is_enabled]"
                                                value="1"
                                                {{ $isEnabled ? 'checked' : '' }}
                                                class="sr-only peer"
                                                onchange="this.closest('.p-3.5').classList.toggle('opacity-60', !this.checked); const badge = this.closest('.p-3.5').querySelector('.shift-status-badge'); if(badge) { badge.textContent = this.checked ? 'Aktif Beroperasi' : 'Nonaktif'; badge.className = 'shift-status-badge font-bold px-2 py-0.5 rounded-md border ' + (this.checked ? 'text-emerald-700 bg-emerald-50 border-emerald-200/60' : 'text-slate-500 bg-slate-100 border-slate-200'); }">
                                            <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                        </label>
                                    </div>

                                    <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 text-[11px] gap-2">
                                        <label for="shift_cap_{{ $office->id }}_{{ $shift->id }}" class="text-slate-500 font-medium">Kuota Kursi Shift:</label>
                                        <div class="flex items-center gap-1">
                                            <input type="number" min="1"
                                                max="{{ (int) ($office->capacity ?? 15) }}"
                                                id="shift_cap_{{ $office->id }}_{{ $shift->id }}"
                                                name="capacities[{{ $office->id }}][{{ $shift->id }}][max_capacity]"
                                                value="{{ min((int) $capData['max_capacity'] ?: (int) ($office->capacity ?? 15), (int) ($office->capacity ?? 15)) }}"
                                                data-office-id="{{ $office->id }}"
                                                oninput="clampShiftCapacity(this)"
                                                class="shift-cap-input-{{ $office->id }} w-16 px-2 py-0.5 text-xs font-bold text-center bg-white border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                            <span class="text-slate-500">/ <span class="office-cap-num-{{ $office->id }}">{{ (int) ($office->capacity ?? 15) }}</span></span>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="text-slate-500 font-medium">Status Operasional:</span>
                                        <span class="shift-status-badge font-bold {{ $isEnabled ? 'text-emerald-700 bg-emerald-50 border-emerald-200/60' : 'text-slate-500 bg-slate-100 border-slate-200' }} px-2 py-0.5 rounded-md border">
                                            {{ $isEnabled ? 'Aktif Beroperasi' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </form>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: DISTRIBUSI SHIFT & PASANGAN KONFLIK                                 -->
    <!-- ========================================================================= -->
    <div id="tab-division-rules" class="tab-content hidden space-y-6">

        <!-- Section 3.1: Fleksibilitas Divisi & Aturan Shift -->
        <form action="{{ route('admin.automation-rules.save-division-rules') }}" method="POST">
            @csrf
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0M3.75 18H7.5m6-6h6m-6 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0M3.75 12H10.5" />
                            </svg>
                            Pola Kerja & Fleksibilitas Divisi
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Atur izin shift per divisi, strategi pemerataan shift, serta aktivasi protokol darurat Hybrid 50:50 dan Split-Shift.
                        </p>
                    </div>

                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition self-start sm:self-auto shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span>Simpan Aturan Divisi</span>
                    </button>
                </div>

                <div class="p-4 sm:p-6 space-y-5">
                    @foreach($divisions as $division)
                    @php
                    $rule = $divisionRules->get($division->id);
                    $allowedShifts = $rule?->allowed_shift_ids ?? $shifts->pluck('id')->toArray();
                    $mode = $rule?->shift_distribution_mode ?? 'balanced_quota';
                    $allowHybrid = $rule ? (bool) $rule->allow_hybrid_balancing : true;
                    $allowSplit = $rule ? (bool) $rule->allow_split_shift : true;
                    @endphp
                    <div class="p-4 sm:p-5 rounded-2xl border border-slate-200 bg-white shadow-2xs hover:border-slate-300 transition-colors">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-3 mb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                @if(!empty($division->icon))
                                <img src="{{ asset('img/' . $division->icon) }}" alt="{{ $division->name }}"
                                    class="w-10 h-10 rounded-xl object-contain bg-slate-50 border border-slate-200 p-2 shrink-0">
                                @else
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-base shrink-0 border border-indigo-100">
                                    <i class="fa-solid fa-sitemap text-base"></i>
                                </div>
                                @endif
                                <div>
                                    <h3 class="text-base font-bold text-slate-900">{{ $division->name }}</h3>
                                    <span class="text-xs text-slate-400 font-medium">{{ $division->intern->count() }} orang pemagang aktif</span>
                                </div>
                            </div>

                            <!-- Distribusi Shift Strategy Select -->
                            <div class="flex items-center gap-2">
                                <label for="mode_{{ $division->id }}" class="text-xs font-semibold text-slate-600 whitespace-nowrap">
                                    Strategi Distribusi:
                                </label>
                                <select id="mode_{{ $division->id }}"
                                    name="rules[{{ $division->id }}][shift_distribution_mode]"
                                    class="text-xs font-semibold bg-slate-50 border border-slate-300 rounded-xl px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none cursor-pointer">
                                    <option value="balanced_quota" {{ $mode === 'balanced_quota' ? 'selected' : '' }}>
                                        Proporsional / Bagi Rata (Default)
                                    </option>
                                    <option value="weekly_rotation" {{ $mode === 'weekly_rotation' ? 'selected' : '' }}>
                                        Rotasi Mingguan (Gantian Pagi/Siang)
                                    </option>
                                    <option value="distance_optimized" {{ $mode === 'distance_optimized' ? 'selected' : '' }}>
                                        Prioritas Jarak Kos (Jauh Diutamakan Siang)
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <!-- Shift Checkboxes -->
                            <div class="bg-slate-50/70 p-3.5 rounded-xl border border-slate-200">
                                <div class="text-xs font-bold text-slate-700 mb-2 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Shift yang Diizinkan untuk Divisi Ini:</span>
                                </div>
                                <div class="flex flex-wrap gap-2.5">
                                    @foreach($shifts as $shift)
                                    <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-slate-200 bg-white cursor-pointer select-none hover:bg-slate-50 transition">
                                        <input type="checkbox"
                                            name="rules[{{ $division->id }}][allowed_shift_ids][]"
                                            value="{{ $shift->id }}"
                                            {{ in_array($shift->id, $allowedShifts) ? 'checked' : '' }}
                                            class="w-4 h-4 text-blue-600 rounded-sm border-slate-300 focus:ring-blue-500">
                                        <span class="text-xs font-semibold text-slate-800">{{ $shift->name }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Fallback & Flexibility Toggles -->
                            <div class="bg-slate-50/70 p-3.5 rounded-xl border border-slate-200 space-y-2.5">
                                <div class="text-xs font-bold text-slate-700 mb-2 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                    <span>Protokol Fallback & Fleksibilitas Meja:</span>
                                </div>

                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="checkbox"
                                        name="rules[{{ $division->id }}][allow_hybrid_balancing]"
                                        value="1"
                                        {{ $allowHybrid ? 'checked' : '' }}
                                        class="w-4 h-4 mt-0.5 text-blue-600 rounded-sm border-slate-300 focus:ring-blue-500">
                                    <div>
                                        <span class="text-xs font-semibold text-slate-900 block">Izinkan Mode Hybrid 50:50 Otomatis</span>
                                        <span class="text-[11px] text-slate-500 leading-tight block">Jika meja kantor terdekat penuh, otomatis rotasi 50% ganjil WFH & 50% genap WFH sehingga kuota meja hemat 2x lipat.</span>
                                    </div>
                                </label>

                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="checkbox"
                                        name="rules[{{ $division->id }}][allow_split_shift]"
                                        value="1"
                                        {{ $allowSplit ? 'checked' : '' }}
                                        class="w-4 h-4 mt-0.5 text-blue-600 rounded-sm border-slate-300 focus:ring-blue-500">
                                    <div>
                                        <span class="text-xs font-semibold text-slate-900 block">Izinkan Split-Shift Siang</span>
                                        <span class="text-[11px] text-slate-500 leading-tight block">Pemagang shift siang mulai 13:00 di kos, lalu hadir fisik ke kantor 16:00–21:00 setelah shift pagi pulang.</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </form>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 4: ATURAN PASANGAN KONFLIK (ANTI-SATU SHIFT & KANTOR)                 -->
    <!-- ========================================================================= -->
    <div id="tab-conflicts" class="tab-content hidden space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        Aturan Pasangan Konflik Personal (Anti-Satu Shift & Kantor)
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Algoritma menjamin kedua pemagang yang didaftarkan di sini <strong>tidak akan pernah ditempatkan di kantor dan shift yang sama secara bersamaan</strong>.
                    </p>
                </div>

                <button type="button" onclick="openConflictModal()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-xs hover:shadow transition active:scale-[0.98] cursor-pointer self-start sm:self-auto shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah Pasangan Konflik</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200 text-[11px] uppercase tracking-wider">
                        <tr>
                            <th class="p-3.5 sm:p-4">Pemagang 1</th>
                            <th class="p-3.5 sm:p-4 text-center w-12">
                                <svg class="w-4 h-4 text-slate-400 mx-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                </svg>
                            </th>
                            <th class="p-3.5 sm:p-4">Pemagang 2</th>
                            <th class="p-3.5 sm:p-4">Alasan HR / Catatan</th>
                            <th class="p-3.5 sm:p-4 text-center">Status</th>
                            <th class="p-3.5 sm:p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($conflictRules as $conflict)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3.5 sm:p-4 font-semibold text-slate-900">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-700">
                                        {{ substr($conflict->intern1?->user?->profile?->full_name ?? $conflict->intern1?->user?->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <div>{{ $conflict->intern1?->user?->profile?->full_name ?? $conflict->intern1?->user?->name ?? 'Pemagang #'.$conflict->intern_id_1 }}</div>
                                        @if($conflict->intern1?->school)
                                        <div class="text-[11px] font-medium text-slate-600">{{ $conflict->intern1->school->name }}</div>
                                        @endif
                                        <div class="text-[10px] text-slate-400 font-normal">{{ $conflict->intern1?->division?->name ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3.5 sm:p-4 text-center text-rose-500 font-bold">
                                <svg class="w-4 h-4 mx-auto text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                            </td>
                            <td class="p-3.5 sm:p-4 font-semibold text-slate-900">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-700">
                                        {{ substr($conflict->intern2?->user?->profile?->full_name ?? $conflict->intern2?->user?->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <div>{{ $conflict->intern2?->user?->profile?->full_name ?? $conflict->intern2?->user?->name ?? 'Pemagang #'.$conflict->intern_id_2 }}</div>
                                        @if($conflict->intern2?->school)
                                        <div class="text-[11px] font-medium text-slate-600">{{ $conflict->intern2->school->name }}</div>
                                        @endif
                                        <div class="text-[10px] text-slate-400 font-normal">{{ $conflict->intern2?->division?->name ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3.5 sm:p-4 text-slate-600 text-xs">
                                {{ $conflict->reason ?? 'Pemisahan penempatan atas arahan HR' }}
                            </td>
                            <td class="p-3.5 sm:p-4 text-center">
                                <button type="button" data-conflict-id="{{ $conflict->id }}" onclick="toggleConflict(this.dataset.conflictId)"
                                    id="conflict-badge-{{ $conflict->id }}"
                                    class="px-2.5 py-1 rounded-full text-[11px] font-bold transition {{ $conflict->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                    {{ $conflict->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="p-3.5 sm:p-4 text-center">
                                <form action="{{ route('admin.automation-rules.delete-conflict', $conflict->id) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus aturan konflik pasangan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-400">
                                Belum ada aturan pasangan konflik yang didaftarkan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 4: KEBIJAKAN PEMAGANG MASSAL & PERIODE MAGANG                         -->
    <!-- ========================================================================= -->
    <div id="tab-intern-policies" class="tab-content hidden space-y-4">
        <form action="{{ route('admin.automation-rules.save-bulk-interns') }}" method="POST">
            @csrf
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <!-- Header Card (Title & Action Buttons) -->
                <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            <span>Kebijakan Penempatan & Masa Magang</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Atur tipe kerja, grup hybrid, kunci kantor, dan masa magang. Sekolah remote otomatis terkunci WFH penuh.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0 w-full sm:w-auto">
                        <button type="button" onclick="confirmBalanceHybridGroups()"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white shadow-xs hover:shadow transition active:scale-[0.98] cursor-pointer shrink-0"
                            title="Bagi rata pemagang non-remote 50% Grup A dan 50% Grup B secara proporsional per divisi">
                            <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                            <span>Bagi Rata Grup A & B</span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-purple-800/80 text-purple-100">50:50</span>
                        </button>

                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white shadow-xs hover:shadow transition active:scale-[0.98] cursor-pointer shrink-0">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span>Simpan Kebijakan Massal</span>
                        </button>
                    </div>
                </div>

                <!-- Filter & Search Toolbar (Dedicated Filter Row) -->
                <div class="p-3.5 sm:p-4 bg-white border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 flex-1">
                        <!-- 1. Filter Sekolah -->
                        @php
                        $schoolList = isset($schools) ? $schools : $interns->pluck('school')->filter()->unique('id')->sortBy('name');
                        $unsetPeriodCount = $interns->filter(fn($i) => empty($i->start_date) || empty($i->end_date))->count();
                        @endphp
                        <div>
                            <select id="intern-policy-school-filter" onchange="filterInternPolicyTable()"
                                class="w-full bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-2xs cursor-pointer">
                                <option value="">Semua Sekolah ({{ $schoolList->count() }})</option>
                                @foreach($schoolList as $sch)
                                <option value="{{ strtolower($sch->name) }}">{{ $sch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 2. Filter Divisi -->
                        <div>
                            <select id="intern-policy-division-filter" onchange="filterInternPolicyTable()"
                                class="w-full bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-2xs cursor-pointer">
                                <option value="">Semua Divisi ({{ $divisions->count() }})</option>
                                @foreach($divisions as $div)
                                <option value="{{ strtolower($div->name) }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 3. Filter Status / Tipe -->
                        <div>
                            <select id="intern-policy-status-filter" onchange="filterInternPolicyTable()"
                                class="w-full bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-2xs cursor-pointer">
                                <option value="">Semua Status Penempatan</option>
                                <option value="unset_period" class="font-bold text-amber-700">⚠️ Belum Diset Periode ({{ $unsetPeriodCount }})</option>
                                <option value="auto_office">Otomatis (Jarak/Kuota)</option>
                                <option value="wfo_only">WFO Saja (Wajib Kantor)</option>
                                <option value="wfh_only">WFH Saja (Remote Full)</option>
                                <option value="hybrid">Hybrid 50% Selang-Seling</option>
                            </select>
                        </div>

                        <!-- 4. Search Nama / NIM / Sekolah -->
                        <div class="relative">
                            <input type="text" id="intern-policy-search" oninput="filterInternPolicyTable()"
                                placeholder="Cari nama, NIM, sekolah..."
                                class="w-full bg-white border border-slate-300 rounded-xl pl-8 pr-3 py-1.5 text-xs text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-2xs">
                            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                    </div>

                    <div class="flex items-center justify-between md:justify-end gap-2 shrink-0 pt-1 md:pt-0 border-t md:border-t-0 border-slate-100 flex-wrap">
                        @if($unsetPeriodCount > 0)
                        <button type="button" onclick="setInternPolicyStatusFilter('unset_period')" title="Tampilkan pemagang yang periode magangnya belum ditentukan"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 transition shadow-2xs cursor-pointer shrink-0">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span>Belum Diset ({{ $unsetPeriodCount }})</span>
                        </button>
                        @endif

                        <button type="button" onclick="resetInternPolicyFilters()" title="Reset semua filter"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-100 border border-slate-200 transition shadow-2xs cursor-pointer shrink-0">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            <span>Reset</span>
                        </button>
                        <span id="intern-policy-counter" class="text-xs text-slate-500 font-medium whitespace-nowrap">
                            Menampilkan 1 - {{ min(50, count($interns)) }} dari {{ count($interns) }} pemagang
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                    <table class="w-full text-left text-xs sm:text-sm" id="intern-policy-table">
                        <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200 text-[11px] uppercase tracking-wider sticky top-0 z-10">
                            <tr>
                                <th class="p-3.5 min-w-[220px]">Pemagang, Sekolah & Divisi</th>
                                <th class="p-3.5 text-center min-w-[100px]">Titik Kos</th>
                                <th class="p-3.5 min-w-[170px]">Tipe Penempatan</th>
                                <th class="p-3.5 min-w-[130px]">Grup Hybrid</th>
                                <th class="p-3.5 min-w-[150px]">Kunci Kantor (Lock)</th>
                                <th class="p-3.5 min-w-[150px]">Blacklist Kantor</th>
                                <th class="p-3.5 min-w-[220px]">Periode Magang (Mulai - Selesai)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="intern-policy-body">
                            @foreach($interns as $intern)
                            @php
                            $hasCoords = !empty($intern->home_latitude) && !empty($intern->home_longitude);
                            $isUnsetPeriod = empty($intern->start_date) || empty($intern->end_date);
                            $isRemoteSchool = (bool) ($intern->school?->is_remote_school ?? false);
                            @endphp
                            <tr class="intern-policy-row hover:bg-slate-50/80 transition-colors {{ $isUnsetPeriod ? 'bg-amber-50/30' : '' }}"
                                data-name="{{ strtolower($intern->user?->profile?->full_name ?? $intern->user?->name ?? '') }}"
                                data-nim="{{ strtolower($intern->nim ?? '') }}"
                                data-division="{{ strtolower($intern->division?->name ?? '') }}"
                                data-school="{{ strtolower($intern->school?->name ?? '') }}"
                                data-type="{{ $intern->work_placement_type ?? 'auto_office' }}"
                                data-unset-period="{{ $isUnsetPeriod ? '1' : '0' }}">
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-900">
                                        {{ $intern->user?->profile?->full_name ?? $intern->user?->name ?? 'Pemagang #'.$intern->id }}
                                    </div>
                                    @if($intern->school)
                                    <div class="text-xs font-semibold text-slate-600 mt-0.5">
                                        {{ $intern->school->name }}
                                        @if($isRemoteSchool)
                                        <span class="inline-flex items-center text-purple-700 font-bold">• Kampus Luar Kota</span>
                                        @endif
                                    </div>
                                    @endif
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                        NIM: {{ $intern->nim ?? '-' }} • <span class="font-medium text-blue-700">{{ $intern->division?->name ?? 'Umum' }}</span>
                                    </div>
                                </td>

                                <td class="p-3.5 text-center">
                                    @if($hasCoords)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Tersedia
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800" title="Titik belum diset, jarak kos diestimasi default">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Belum Ada
                                    </span>
                                    @endif
                                </td>

                                <td class="p-3.5">
                                    @if($isRemoteSchool)
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-bold bg-purple-50 text-purple-800 border border-purple-200" title="Terkunci mengikuti status kampus luar kota di menu Pengaturan Sekolah">
                                        <svg class="w-3.5 h-3.5 text-purple-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                        </svg>
                                        <span>WFH Penuh (Sekolah Remote)</span>
                                    </div>
                                    <input type="hidden" name="interns[{{ $intern->id }}][work_placement_type]" value="wfh_only">
                                    @else
                                    <select name="interns[{{ $intern->id }}][work_placement_type]"
                                        class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500">
                                        <option value="auto_office" {{ $intern->work_placement_type === 'auto_office' ? 'selected' : '' }}>Otomatis (Jarak/Kuota)</option>
                                        <option value="wfo_only" {{ $intern->work_placement_type === 'wfo_only' ? 'selected' : '' }}>WFO Saja (Wajib Kantor)</option>
                                        <option value="wfh_only" {{ $intern->work_placement_type === 'wfh_only' ? 'selected' : '' }}>WFH Saja (Remote Full)</option>
                                        <option value="hybrid" {{ $intern->work_placement_type === 'hybrid' ? 'selected' : '' }}>Hybrid 50% Selang-Seling</option>
                                    </select>
                                    @endif
                                </td>

                                <td class="p-3.5">
                                    @if($isRemoteSchool)
                                    <span class="text-xs font-mono text-slate-400 text-center block">-</span>
                                    <input type="hidden" name="interns[{{ $intern->id }}][hybrid_group]" value="auto">
                                    @else
                                    <select name="interns[{{ $intern->id }}][hybrid_group]"
                                        class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-purple-500">
                                        <option value="auto" {{ ($intern->hybrid_group ?? 'auto') === 'auto' ? 'selected' : '' }}>Otomatis ({{ $intern->effective_hybrid_group }})</option>
                                        <option value="A" {{ $intern->hybrid_group === 'A' ? 'selected' : '' }}>Grup A (Sen WFO)</option>
                                        <option value="B" {{ $intern->hybrid_group === 'B' ? 'selected' : '' }}>Grup B (Sen WFH)</option>
                                    </select>
                                    @endif
                                </td>

                                <td class="p-3.5">
                                    @if($isRemoteSchool)
                                    <span class="text-xs font-mono text-slate-400 text-center block">-</span>
                                    <input type="hidden" name="interns[{{ $intern->id }}][locked_office_id]" value="">
                                    @else
                                    <select name="interns[{{ $intern->id }}][locked_office_id]"
                                        class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-500">
                                        <option value="">-- Bebas (Auto) --</option>
                                        @foreach($offices as $office)
                                        <option value="{{ $office->id }}" {{ $intern->locked_office_id == $office->id ? 'selected' : '' }}>
                                            {{ $office->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @endif
                                </td>

                                <td class="p-3.5">
                                    <select name="interns[{{ $intern->id }}][blacklisted_office_id]"
                                        class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-rose-500">
                                        <option value="">-- Tidak Ada --</option>
                                        @foreach($offices as $office)
                                        <option value="{{ $office->id }}" {{ $intern->blacklisted_office_id == $office->id ? 'selected' : '' }}>
                                            {{ $office->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="p-3.5">
                                    <div class="flex items-center gap-1.5">
                                        <input type="date" name="interns[{{ $intern->id }}][start_date]"
                                            value="{{ $intern->start_date ? \Carbon\Carbon::parse($intern->start_date)->toDateString() : '' }}"
                                            class="w-1/2 bg-white border {{ empty($intern->start_date) ? 'border-amber-400 bg-amber-50/50' : 'border-slate-200' }} rounded-lg px-2 py-1 text-[11px] font-mono text-slate-800 focus:ring-1 focus:ring-blue-500"
                                            title="Tanggal Mulai Magang">
                                        <span class="text-slate-400 font-bold">-</span>
                                        <input type="date" name="interns[{{ $intern->id }}][end_date]"
                                            value="{{ $intern->end_date ? \Carbon\Carbon::parse($intern->end_date)->toDateString() : '' }}"
                                            class="w-1/2 bg-white border {{ empty($intern->end_date) ? 'border-amber-400 bg-amber-50/50' : 'border-slate-200' }} rounded-lg px-2 py-1 text-[11px] font-mono text-slate-800 focus:ring-1 focus:ring-blue-500"
                                            title="Tanggal Selesai Magang">
                                    </div>
                                    @if($isUnsetPeriod)
                                    <div class="text-[10px] text-amber-600 font-semibold mt-0.5 flex items-center gap-1">
                                        ⚠️ Periode magang belum diset
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3.5 sm:p-4 bg-slate-50 border-t border-slate-200/80 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-slate-600">
                    <span id="intern-policy-count" class="font-medium text-slate-600 text-center md:text-left">
                        Menampilkan 1 - {{ min(50, count($interns)) }} dari {{ count($interns) }} pemagang
                    </span>
                    <div id="intern-policy-pagination" class="flex items-center justify-center flex-wrap gap-1.5">
                        <!-- Navigasi pagination 50 pemagang dirender via JS -->
                    </div>
                    <button type="button" onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
                        class="text-[11px] font-semibold text-blue-600 hover:underline flex items-center gap-1 cursor-pointer shrink-0">
                        Kembali ke Atas ↑
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 5: SIMULATOR & GENERATOR JADWAL (CORE SCHEDULING ENGINE)              -->
    <!-- ========================================================================= -->
    <div id="tab-simulator" class="tab-content hidden space-y-6">

        <!-- Control Panel Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 00-1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                        </svg>
                        Simulator & Generator Jadwal
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Simulasikan alokasi meja kantor (*Dry Run*) atau terapkan langsung ke database.
                    </p>
                </div>

                <!-- Quick Presets Dropdown Toggle -->
                <div class="relative shrink-0" id="preset-dropdown-container">
                    <button type="button" onclick="togglePresetDropdown()" id="btn-preset-dropdown"
                        class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl transition border border-slate-200 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                        </svg>
                        <span id="preset-dropdown-label">Preset Tanggal</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div id="preset-dropdown-menu" class="hidden absolute right-0 mt-1.5 w-48 bg-white border border-slate-200 rounded-xl shadow-lg p-1.5 z-30 divide-y divide-slate-100">
                        <div class="py-0.5">
                            <button type="button" onclick="selectPreset('next_week', 'Minggu Depan')"
                                class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg flex items-center justify-between transition">
                                <span class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    Minggu Depan
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono">7 Hari</span>
                            </button>
                        </div>
                        <div class="py-0.5">
                            <button type="button" onclick="selectPreset('current_month', 'Bulan Ini')"
                                class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg flex items-center justify-between transition">
                                <span class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Bulan Ini
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono">1 Bln</span>
                            </button>
                        </div>
                        <div class="py-0.5">
                            <button type="button" onclick="selectPreset('next_month', 'Bulan Depan')"
                                class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg flex items-center justify-between transition">
                                <span class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                    Bulan Depan
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono">1 Bln</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <form id="simulator-form" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                    <div>
                        <label for="sim_start_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tanggal Mulai *
                        </label>
                        <input type="date" id="sim_start_date" name="start_date"
                            value="{{ now()->addDay()->toDateString() }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="sim_end_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tanggal Selesai *
                        </label>
                        <input type="date" id="sim_end_date" name="end_date"
                            value="{{ now()->addDays(7)->toDateString() }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="sim_target_office_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Target Kantor
                        </label>
                        <select id="sim_target_office_id" name="target_office_id" onchange="onTargetOfficeChange()"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:outline-none cursor-pointer">
                            <option value="">Otomatis (Matriks & Jarak)</option>
                            @foreach($offices as $office)
                            <option value="{{ $office->id }}">Kunci: {{ $office->name }} ({{ $office->capacity ?? 15 }} Meja)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Filter Divisi
                        </label>
                        <div class="relative">
                            <button type="button" onclick="toggleDivisionFilterDropdown()" id="division-filter-btn"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-800 flex items-center justify-between focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <span id="division-filter-label" class="truncate">Semua Divisi ({{ count($divisions) }})</span>
                                <svg class="w-4 h-4 text-slate-400 shrink-0 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <div id="division-filter-dropdown" class="hidden absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-xl shadow-lg p-3 z-30 space-y-1.5 max-h-56 overflow-y-auto">
                                <label class="flex items-center gap-2 cursor-pointer pb-2 border-b border-slate-100">
                                    <input type="checkbox" id="check-all-divisions" checked onchange="toggleSelectAllDivisions(this.checked)"
                                        class="w-4 h-4 text-blue-600 rounded-sm border-slate-300 focus:ring-blue-500">
                                    <span class="text-xs font-bold text-slate-800">Pilih Semua Divisi</span>
                                </label>
                                @foreach($divisions as $div)
                                <label id="div-filter-item-{{ $div->id }}" class="flex items-center justify-between gap-2 p-1.5 rounded-lg hover:bg-slate-50 cursor-pointer transition" data-div-id="{{ $div->id }}">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <input type="checkbox" name="division_ids[]" value="{{ $div->id }}" checked
                                            id="div-checkbox-{{ $div->id }}"
                                            class="div-filter-checkbox w-4 h-4 text-blue-600 rounded-sm border-slate-300 focus:ring-blue-500 shrink-0"
                                            onchange="updateDivisionFilterLabel()">
                                        <span class="text-xs text-slate-700 font-medium truncate">{{ $div->name }}</span>
                                    </div>
                                    <span class="div-status-tag hidden text-[10px] text-slate-400 font-semibold italic shrink-0">Tidak terdaftar</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opsi Tambahan: Kalender & Cakupan Pemagang -->
                <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80 space-y-3">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 text-xs">
                        <!-- Kalender Operasional -->
                        <div class="flex items-center gap-4 flex-wrap">
                            <span class="font-bold text-slate-700 flex items-center gap-1.5 shrink-0">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                                </svg>
                                <span>Kalender:</span>
                            </span>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                                <input type="checkbox" name="skip_holidays" id="sim_skip_holidays" value="1" checked
                                    class="w-4 h-4 text-blue-600 rounded-sm border-slate-300 focus:ring-blue-500">
                                <span class="font-medium text-slate-700">Lewati Libur Nasional</span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                                <input type="checkbox" name="include_sundays" id="sim_include_sundays" value="1"
                                    class="w-4 h-4 text-blue-600 rounded-sm border-slate-300 focus:ring-blue-500">
                                <span class="font-medium text-slate-700">Sertakan Hari Minggu</span>
                            </label>
                        </div>

                        <!-- Pembatas Garis Desktop -->
                        <div class="hidden lg:block w-px h-5 bg-slate-200 shrink-0"></div>

                        <!-- Cakupan Pemagang -->
                        <div class="flex items-center gap-4 flex-wrap">
                            <span class="font-bold text-slate-700 flex items-center gap-1.5 shrink-0">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                </svg>
                                <span>Cakupan Pemagang:</span>
                            </span>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="intern_scope" value="all" checked onchange="toggleInternScope(this.value)"
                                    class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                                <span class="font-medium text-slate-700">Semua Pemagang</span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="intern_scope" value="custom" onchange="toggleInternScope(this.value)"
                                    class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                                <span class="font-medium text-slate-700">Pilih Kustom</span>
                            </label>
                        </div>
                    </div>

                    <!-- Interactive Intern Picker Panel (Hidden initially) -->
                    <div id="custom-intern-picker-container" class="hidden pt-3 border-t border-slate-200 space-y-2.5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="selectAllCustomInterns(true)"
                                    class="px-2.5 py-1 text-xs font-semibold bg-white border border-slate-300 rounded-lg hover:bg-slate-100 text-slate-700 transition cursor-pointer">
                                    Centang Yang Tampil
                                </button>
                                <button type="button" onclick="selectAllCustomInterns(false)"
                                    class="px-2.5 py-1 text-xs font-semibold bg-white border border-slate-300 rounded-lg hover:bg-slate-100 text-slate-700 transition cursor-pointer">
                                    Hapus Centang
                                </button>
                                <span id="custom-intern-count-badge" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 font-mono">
                                    0 pemagang dipilih
                                </span>
                            </div>
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <div class="relative w-full sm:w-64">
                                    <input type="text" id="custom-intern-search" oninput="filterCustomInternPicker()"
                                        placeholder="Cari nama / NIM / sekolah..."
                                        class="w-full bg-white border border-slate-300 rounded-lg pl-7 pr-2.5 py-1 text-xs text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 max-h-48 overflow-y-auto p-1 bg-white rounded-xl border border-slate-200" id="custom-intern-list">
                            @foreach($interns as $intern)
                            <label class="custom-intern-item flex items-center gap-2 p-2 rounded-lg hover:bg-slate-50 cursor-pointer border border-transparent hover:border-slate-200 transition text-xs"
                                data-name="{{ strtolower($intern->user?->profile?->full_name ?? $intern->user?->name ?? '') }}"
                                data-nim="{{ strtolower($intern->nim ?? '') }}"
                                data-division-id="{{ $intern->division_id }}"
                                data-division="{{ strtolower($intern->division?->name ?? '') }}"
                                data-school="{{ strtolower($intern->school?->name ?? '') }}">
                                <input type="checkbox" name="sim_custom_intern_ids[]" value="{{ $intern->id }}" onchange="updateCustomInternCount()"
                                    class="custom-intern-checkbox w-4 h-4 text-blue-600 rounded-sm border-slate-300 focus:ring-blue-500 shrink-0">
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 truncate">{{ $intern->user?->profile?->full_name ?? $intern->user?->name ?? 'Pemagang #'.$intern->id }}</div>
                                    @if($intern->school)
                                    <div class="text-[11px] font-medium text-slate-600 truncate">{{ $intern->school->name }}</div>
                                    @endif
                                    <div class="text-[10px] text-slate-400 truncate">{{ $intern->division?->name ?? 'Umum' }} • NIM: {{ $intern->nim ?? '-' }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 border-t border-slate-100">
                    <div class="text-xs text-slate-500 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <span>Simulasikan jadwal terlebih dahulu untuk memverifikasi kapasitas meja.</span>
                    </div>

                    <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap shrink-0">
                        <button type="button" onclick="openRecommendationModal()"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200/80 shadow-2xs hover:shadow-xs transition active:scale-[0.98] cursor-pointer">
                            <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                            </svg>
                            <span>Rekomendasi Cerdas</span>
                        </button>

                        <button type="button" id="btn-run-simulation" onclick="runSimulation()"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white shadow-xs hover:shadow transition active:scale-[0.98] cursor-pointer">
                            <span id="sim-icon-container" class="inline-flex items-center shrink-0">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M5.25 4.5v15l13.5-7.5-13.5-7.5z" />
                                </svg>
                            </span>
                            <span id="sim-text">Jalankan Simulasi</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Simulation Results Area (Hidden initially) -->
        <div id="simulation-results-container" class="hidden space-y-6">

            <!-- Status Banner: Simulasi Pratinjau (Belum Masuk Database) -->
            <div class="p-4 rounded-2xl bg-amber-50 border-2 border-amber-300 text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-200/90 text-amber-800 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-amber-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-200 text-amber-900 uppercase tracking-wide">Status: Hasil Simulasi (Draft)</span>
                            <span class="text-xs font-bold text-amber-800 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse inline-block"></span>
                                Belum Masuk Database
                            </span>
                        </div>
                        <p class="text-xs text-amber-800 mt-1">
                            Jadwal di bawah ini <strong>hanya simulasi uji coba</strong> dan <u>belum disimpan ke database</u>. Silakan tinjau pembagian meja dan shift di bawah. Jika sudah sesuai, klik tombol <strong>"Setujui & Terapkan Jadwal"</strong> untuk menyimpannya ke sistem secara resmi.
                        </p>
                    </div>
                </div>
                <button type="button" id="btn-deploy-schedule" onclick="confirmDeploySchedule()"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-xs hover:shadow transition shrink-0 cursor-pointer active:scale-[0.98]">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span>Setujui & Terapkan Jadwal</span>
                </button>
            </div>

            <!-- Summary KPI Badges -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pemagang</div>
                    <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-1" id="kpi-interns">0</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Total terproses</div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Entri</div>
                    <div class="text-xl sm:text-2xl font-extrabold text-blue-600 mt-1" id="kpi-allocations">0</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Jadwal harian</div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">WFO Fisik</div>
                    <div class="text-xl sm:text-2xl font-extrabold text-emerald-600 mt-1" id="kpi-wfo">0</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Hadir di kantor</div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">WFH / Remote</div>
                    <div class="text-xl sm:text-2xl font-extrabold text-indigo-600 mt-1" id="kpi-wfh">0</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Kerja di rumah/kos</div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Spillover</div>
                    <div class="text-xl sm:text-2xl font-extrabold text-amber-600 mt-1" id="kpi-spillover">0</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Dialihkan ke kantor lain</div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rotasi Hybrid</div>
                    <div class="text-xl sm:text-2xl font-extrabold text-purple-600 mt-1" id="kpi-hybrid">0</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Fallback 50:50 aktif</div>
                </div>
            </div>

            <!-- Warnings and Alerts Section -->
            <div id="sim-warnings-box" class="hidden p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 space-y-1.5 shadow-2xs">
                <div class="flex items-center gap-2 font-bold text-xs sm:text-sm text-amber-800">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <span>Catatan Sistem & Deteksi Kepadatan:</span>
                </div>
                <ul id="sim-warnings-list" class="list-disc list-inside text-xs space-y-1 text-amber-700 pl-1">
                </ul>
            </div>

            <!-- Unconfigured Interns Note Box (Simpel & Pendek) -->
            <div id="sim-unconfigured-note-box" class="hidden p-3.5 rounded-2xl bg-amber-50/90 border border-amber-200 text-amber-900 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 text-xs text-amber-800">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <span id="sim-unconfigured-note-text" class="font-medium">Beberapa pemagang belum bisa didaftarkan jadwal karena belum diset periode magangnya.</span>
                </div>
                <button type="button" onclick="switchTab('tab-intern-policies')"
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[11px] font-bold bg-amber-200/80 hover:bg-amber-300 text-amber-900 transition shrink-0 cursor-pointer">
                    <span>Atur Tanggal di Tab Kebijakan Anggota</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>

            <!-- Office & Shift Desk Utilization Matrix -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-4 sm:p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                            Puncak Okupansi Meja Fisik Kantor (Hadir Bersamaan)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tingkat keterisian meja fisik tertinggi saat jam shift saling beririsan dibandingkan kapasitas meja fisik kantor.</p>
                    </div>
                </div>

                <div id="utilization-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    <!-- Injected via JavaScript -->
                </div>
            </div>

            <!-- Daily & Office Detailed Occupancy Card -->
            <div id="sim-daily-occupancy-card" class="hidden p-4 rounded-2xl bg-gradient-to-r from-blue-50/80 via-indigo-50/50 to-white border border-blue-200/80 shadow-2xs space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-blue-700 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            <span id="daily-card-title">Okupansi Harian Detail</span>
                        </div>
                        <div class="text-xs text-slate-600 mt-0.5" id="daily-card-subtitle">
                            Menampilkan perbandingan kapasitas kursi fisik dan pemagang hadir pada hari & kantor terpilih.
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 shrink-0" id="daily-card-stats">
                        <!-- Injected via JS -->
                    </div>
                </div>
                <!-- Shift Breakdown in selected date/office -->
                <div id="daily-card-shift-breakdown" class="pt-2.5 border-t border-blue-100 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                    <!-- Injected via JS -->
                </div>
            </div>

            <!-- Allocations Interactive Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden space-y-3">
                <div class="p-4 sm:p-5 border-b border-slate-100 space-y-3 bg-slate-50/50">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                                </svg>
                                Rincian Alokasi Penempatan Pemagang
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Filter jadwal per hari, per kantor, dan shift untuk melihat penempatan secara detail.</p>
                        </div>

                        <!-- Reset Filter Button -->
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="resetSimulationFilters()" title="Reset semua filter ke default"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-100 border border-slate-200 transition shadow-2xs cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                <span>Reset Filter</span>
                            </button>
                        </div>
                    </div>

                    <!-- Search & Detailed Filter Controls -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2.5">
                        <!-- 1. Filter Hari / Tanggal (Baru) -->
                        <div>
                            <select id="sim-table-date-filter" onchange="filterSimulationTable()"
                                class="w-full bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none cursor-pointer">
                                <option value="">Semua Tanggal</option>
                                <!-- Injected dynamically via JS -->
                            </select>
                        </div>

                        <!-- 2. Filter Kantor -->
                        <div>
                            <select id="sim-table-office-filter" onchange="onOfficeFilterChange()"
                                class="w-full bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none cursor-pointer">
                                <option value="">Semua Kantor</option>
                                @foreach($offices as $office)
                                <option value="{{ $office->id }}">{{ $office->name }}</option>
                                @endforeach
                                <option value="wfh">Rumah / Kos (WFH)</option>
                            </select>
                        </div>

                        <!-- 3. Filter Shift -->
                        <div>
                            <select id="sim-table-shift-filter" onchange="filterSimulationTable()"
                                class="w-full bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none cursor-pointer">
                                <option value="">Semua Shift</option>
                                @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }} ({{ substr($shift->start_time, 0, 5) }}-{{ substr($shift->end_time, 0, 5) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 4. Filter Tipe Kerja -->
                        <div>
                            <select id="sim-table-type-filter" onchange="filterSimulationTable()"
                                class="w-full bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none cursor-pointer">
                                <option value="">Semua Tipe</option>
                                <option value="wfo">WFO (Kantor)</option>
                                <option value="wfh">WFH (Rumah/Kos)</option>
                                <option value="split_shift">Split</option>
                            </select>
                        </div>

                        <!-- 5. Filter Divisi -->
                        <div>
                            <select id="sim-table-division-filter" onchange="filterSimulationTable()"
                                class="w-full bg-white border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none cursor-pointer">
                                <option value="">Semua Divisi</option>
                                @foreach($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 6. Search Box -->
                        <div class="relative">
                            <input type="text" id="sim-table-search" oninput="filterSimulationTable()"
                                placeholder="Cari nama pemagang / sekolah..."
                                class="w-full bg-white border border-slate-300 rounded-xl pl-8 pr-3 py-1.5 text-xs text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                    <table class="w-full text-left text-xs sm:text-sm" id="sim-allocations-table">
                        <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200 text-[11px] uppercase tracking-wider sticky top-0 z-10">
                            <tr>
                                <th class="p-3.5 text-center w-12">No.</th>
                                <th class="p-3.5">Tanggal & Hari</th>
                                <th class="p-3.5">Pemagang & Sekolah</th>
                                <th class="p-3.5">Divisi</th>
                                <th class="p-3.5">Kantor Terpilih</th>
                                <th class="p-3.5 text-center">Jarak Kos</th>
                                <th class="p-3.5">Shift</th>
                                <th class="p-3.5 text-center">Tipe Kerja</th>
                                <th class="p-3.5 text-center">Grup Hybrid</th>
                                <th class="p-3.5">Status Alokasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="sim-allocations-body">
                            <!-- Injected via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <div class="p-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span id="sim-table-count">Menampilkan 0 entri</span>
                    <button type="button" onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
                        class="text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1">
                        <span>Kembali ke atas</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                        </svg>
                    </button>
                </div>
            </div>

        </div>
    </div>

</main>

<!-- ========================================================================= -->
<!-- MODAL: TAMBAH PASANGAN KONFLIK (TAB 3)                                     -->
<!-- ========================================================================= -->
<div id="conflict-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Tambah Pasangan Konflik</h3>
            </div>
            <button type="button" onclick="closeConflictModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('admin.automation-rules.store-conflict') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="intern_id_1" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Pemagang Pertama *
                </label>
                <select id="intern_id_1" name="intern_id_1" required
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-rose-500 focus:outline-none cursor-pointer">
                    <option value="">-- Pilih Pemagang 1 --</option>
                    @foreach($interns as $intern)
                    <option value="{{ $intern->id }}">
                        {{ $intern->user?->profile?->full_name ?? $intern->user?->name ?? 'User #'.$intern->user_id }} ({{ $intern->division?->name ?? 'Tanpa Divisi' }} - {{ $intern->school?->name ?? 'Umum' }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="intern_id_2" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Pemagang Kedua (Yang Dipisahkan) *
                </label>
                <select id="intern_id_2" name="intern_id_2" required
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-rose-500 focus:outline-none cursor-pointer">
                    <option value="">-- Pilih Pemagang 2 --</option>
                    @foreach($interns as $intern)
                    <option value="{{ $intern->id }}">
                        {{ $intern->user?->profile?->full_name ?? $intern->user?->name ?? 'User #'.$intern->user_id }} ({{ $intern->division?->name ?? 'Tanpa Divisi' }} - {{ $intern->school?->name ?? 'Umum' }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="conflict_reason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Alasan Pemisahan HR (Opsional)
                </label>
                <input type="text" id="conflict_reason" name="reason"
                    placeholder="Contoh: Konflik interpersonal, rekomendasi pembimbing, dll"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-rose-500 focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeConflictModal()"
                    class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-xs transition">
                    Simpan Aturan Konflik
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: REKOMENDASI CERDAS PENJADWALAN (AI / CAPACITY BASED)               -->
<!-- ========================================================================= -->
<div id="recommendation-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm shadow-2xs">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Rekomendasi Cerdas Otomatisasi Jadwal</h3>
                    <p class="text-xs text-slate-500">Analisis beban kapasitas kantor, rotasi shift, dan pola kerja optimal.</p>
                </div>
            </div>
            <button type="button" onclick="closeRecommendationModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div id="recommendation-loading" class="py-12 text-center space-y-3">
            <svg class="animate-spin w-8 h-8 text-indigo-600 mx-auto" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <div class="text-xs font-semibold text-slate-600">Menganalisis matriks kapasitas dan riwayat penempatan...</div>
        </div>

        <div id="recommendation-content" class="hidden space-y-4">
            <!-- Capacity Summary Card -->
            <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <div>
                    <div class="font-bold text-indigo-950">Ringkasan Beban Kapasitas Keseluruhan</div>
                    <div class="text-indigo-700 mt-0.5" id="rec-summary-text">Analisis perbandingan total pemagang vs ketersediaan kursi kantor.</div>
                </div>
                <div class="flex items-center gap-2 shrink-0 font-mono font-bold" id="rec-summary-badges">
                    <!-- Badges Injected via JS -->
                </div>
            </div>

            <!-- Recommendations by Division -->
            <div class="space-y-2.5" id="recommendation-list">
                <!-- Injected via JS -->
            </div>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeRecommendationModal()"
                class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

@php
$jsOfficeCapacities = [];
foreach ($offices as $office) {
    $dailyShiftCap = 0;
    if (isset($capacityMap[$office->id])) {
        foreach ($capacityMap[$office->id] as $sCap) {
            if (!empty($sCap['is_enabled'])) {
                $dailyShiftCap += (int) $sCap['max_capacity'];
            }
        }
    }
    if ($dailyShiftCap === 0) {
        $dailyShiftCap = (int) ($office->capacity ?? 0);
    }
    $jsOfficeCapacities[$office->id] = [
        'id' => $office->id,
        'name' => $office->name,
        'capacity' => $dailyShiftCap,
    ];
}

$jsOfficeAllowedDivisions = [];
foreach ($offices as $office) {
    $allowedDivs = [];
    foreach ($divisions as $div) {
        if (!empty($matrixMap[$div->id][$office->id])) {
            $allowedDivs[] = $div->id;
        }
    }
    $jsOfficeAllowedDivisions[$office->id] = $allowedDivs;
}
@endphp

<!-- ========================================================================= -->
<!-- JAVASCRIPT LOGIC                                                          -->
<!-- ========================================================================= -->
<script type="application/json" id="automation-office-capacities">{!! json_encode($jsOfficeCapacities) !!}</script>
<script type="application/json" id="automation-office-allowed-divisions">{!! json_encode($jsOfficeAllowedDivisions) !!}</script>

<script>
    // =========================================================================
    // GLOBAL STATE & DATA MATRICES
    // =========================================================================
    const officeCapacities = JSON.parse(document.getElementById('automation-office-capacities')?.textContent || '{}');
    const officeAllowedDivisions = JSON.parse(document.getElementById('automation-office-allowed-divisions')?.textContent || '{}');

    function handleOfficeCapacityChange(input) {
        const officeId = input.getAttribute('data-office-id');
        const val = input.value;
        document.querySelectorAll('.office-cap-label-' + officeId).forEach(el => el.textContent = val + ' Meja');
        syncOfficeCapacity(officeId, val);
    }

    // Kuota kursi per shift: tidak boleh melebihi meja fisik kantor
    function clampShiftCapacity(input) {
        const max = parseInt(input.max, 10) || 1;
        let val = parseInt(input.value, 10);
        if (isNaN(val)) return; // biarkan user mengetik
        if (val > max) input.value = max;
        if (val < 1) input.value = 1;
    }

    function syncOfficeCapacity(officeId, rawValue) {
        const physical = Math.max(1, parseInt(rawValue, 10) || 1);
        document.querySelectorAll('.office-cap-num-' + officeId).forEach(el => el.textContent = physical);
        document.querySelectorAll('.shift-cap-input-' + officeId).forEach(input => {
            input.max = physical;
            if ((parseInt(input.value, 10) || 0) > physical) input.value = physical;
        });
    }

    // Tab Switching Logic
    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white', 'shadow-xs');
            btn.classList.add('text-slate-600', 'hover:bg-slate-100', 'hover:text-slate-900');
            const svg = btn.querySelector('svg');
            if (svg && btn.id !== 'btn-tab-simulator') {
                svg.classList.add('text-slate-400');
            }
        });

        const activeContent = document.getElementById(tabId);
        const activeBtn = document.getElementById('btn-' + tabId);

        if (activeContent) activeContent.classList.remove('hidden');
        if (activeBtn) {
            activeBtn.classList.add('bg-blue-600', 'text-white', 'shadow-xs');
            activeBtn.classList.remove('text-slate-600', 'hover:bg-slate-100', 'hover:text-slate-900');
            const svg = activeBtn.querySelector('svg');
            if (svg && activeBtn.id !== 'btn-tab-simulator') {
                svg.classList.remove('text-slate-400');
            }
        }

        // Update URL query parameter without reload
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);

        if (tabId === 'tab-intern-policies' && typeof filterInternPolicyTable === 'function') {
            filterInternPolicyTable(false);
        }
    }

    // Auto-detect tab from URL query param
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const initialTab = urlParams.get('tab') || 'tab-matrix';
        switchTab(initialTab);
        if (typeof onTargetOfficeChange === 'function') {
            onTargetOfficeChange();
        }
        if (typeof filterInternPolicyTable === 'function') {
            filterInternPolicyTable(true);
        }
    });

    // Tab 1 Matrix Helpers
    function toggleAllMatrix(state) {
        document.querySelectorAll('.matrix-checkbox').forEach(cb => cb.checked = state);
    }

    function toggleRowMatrix(divisionId) {
        const checkboxes = document.querySelectorAll('.matrix-row-' + divisionId);
        const hasUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
        checkboxes.forEach(cb => cb.checked = hasUnchecked);
    }

    // Tab 3 Conflict Modal Helpers
    function openConflictModal() {
        document.getElementById('conflict-modal').classList.remove('hidden');
    }

    function closeConflictModal() {
        document.getElementById('conflict-modal').classList.add('hidden');
    }

    function toggleConflict(id) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch(`/admin/automation-rules/conflict/${id}/toggle`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const badge = document.getElementById(`conflict-badge-${id}`);
                    if (badge) {
                        if (data.is_active) {
                            badge.className = 'px-2.5 py-1 rounded-full text-[11px] font-bold transition bg-emerald-100 text-emerald-800 hover:bg-emerald-200';
                            badge.textContent = 'Aktif';
                        } else {
                            badge.className = 'px-2.5 py-1 rounded-full text-[11px] font-bold transition bg-slate-100 text-slate-600 hover:bg-slate-200';
                            badge.textContent = 'Nonaktif';
                        }
                    }
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Gagal mengubah status aturan konflik.', 'error');
            });
    }

    // Tab 4 Presets Dropdown
    function togglePresetDropdown() {
        const menu = document.getElementById('preset-dropdown-menu');
        if (menu) menu.classList.toggle('hidden');
    }

    function selectPreset(type, label) {
        setPresetDate(type);
        const labelEl = document.getElementById('preset-dropdown-label');
        if (labelEl) labelEl.textContent = label;
        const menu = document.getElementById('preset-dropdown-menu');
        if (menu) menu.classList.add('hidden');
    }

    document.addEventListener('click', function(e) {
        const container = document.getElementById('preset-dropdown-container');
        const menu = document.getElementById('preset-dropdown-menu');
        if (container && menu && !container.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });

    // Tab 4 Presets
    function setPresetDate(type) {
        const now = new Date();
        const formatDate = (d) => d.toISOString().split('T')[0];

        if (type === 'next_week') {
            const nextMonday = new Date(now);
            const day = nextMonday.getDay();
            const diff = (day === 0 ? 1 : 8 - day);
            nextMonday.setDate(nextMonday.getDate() + diff);

            const nextSaturday = new Date(nextMonday);
            nextSaturday.setDate(nextMonday.getDate() + 5);

            document.getElementById('sim_start_date').value = formatDate(nextMonday);
            document.getElementById('sim_end_date').value = formatDate(nextSaturday);
        } else if (type === 'current_month') {
            const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
            const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

            document.getElementById('sim_start_date').value = formatDate(firstDay);
            document.getElementById('sim_end_date').value = formatDate(lastDay);
        } else if (type === 'next_month') {
            const firstDay = new Date(now.getFullYear(), now.getMonth() + 1, 1);
            const lastDay = new Date(now.getFullYear(), now.getMonth() + 2, 0);

            document.getElementById('sim_start_date').value = formatDate(firstDay);
            document.getElementById('sim_end_date').value = formatDate(lastDay);
        }
    }

    // Tab 4 Division Filter Dropdown
    function toggleDivisionFilterDropdown() {
        const dd = document.getElementById('division-filter-dropdown');
        dd.classList.toggle('hidden');
    }

    document.addEventListener('click', function(e) {
        const btn = document.getElementById('division-filter-btn');
        const dd = document.getElementById('division-filter-dropdown');
        if (btn && dd && !btn.contains(e.target) && !dd.contains(e.target)) {
            dd.classList.add('hidden');
        }
    });

    function onTargetOfficeChange() {
        const targetOfficeId = document.getElementById('sim_target_office_id')?.value;
        const allowedDivs = (targetOfficeId && officeAllowedDivisions[targetOfficeId]) ?
            officeAllowedDivisions[targetOfficeId] :
            null;

        document.querySelectorAll('.div-filter-checkbox').forEach(cb => {
            const divId = parseInt(cb.value);
            const labelItem = document.getElementById(`div-filter-item-${divId}`);
            const tag = labelItem?.querySelector('.div-status-tag');

            if (allowedDivs === null) {
                // Kantor tidak dikunci (Otomatis sesuai jarak terdekat):
                // SEMUA divisi diaktifkan dan DI-CHECKLIST SEMUA secara otomatis
                cb.disabled = false;
                cb.checked = true;
                if (labelItem) {
                    labelItem.classList.remove('opacity-40', 'cursor-not-allowed', 'bg-slate-50');
                    labelItem.classList.add('cursor-pointer', 'hover:bg-slate-50');
                }
                if (tag) tag.classList.add('hidden');
            } else {
                if (allowedDivs.includes(divId)) {
                    // Divisi terdaftar di kantor ini: AKTIF dan DI-CHECKLIST OTOMATIS
                    cb.disabled = false;
                    cb.checked = true;
                    if (labelItem) {
                        labelItem.classList.remove('opacity-40', 'cursor-not-allowed', 'bg-slate-50');
                        labelItem.classList.add('cursor-pointer', 'hover:bg-slate-50');
                    }
                    if (tag) tag.classList.add('hidden');
                } else {
                    // Divisi TIDAK terdaftar di kantor ini: ABU-ABU, DISABLED, dan TIDAK TER-CHECKLIST
                    cb.disabled = true;
                    cb.checked = false;
                    if (labelItem) {
                        labelItem.classList.add('opacity-40', 'cursor-not-allowed', 'bg-slate-50');
                        labelItem.classList.remove('cursor-pointer', 'hover:bg-slate-50');
                    }
                    if (tag) tag.classList.remove('hidden');
                }
            }
        });

        // Sinkronkan master checkbox "Pilih Semua Divisi"
        const checkAll = document.getElementById('check-all-divisions');
        if (checkAll) {
            checkAll.checked = true;
        }

        updateDivisionFilterLabel();
        filterCustomInternPicker();
    }

    function toggleSelectAllDivisions(checked) {
        document.querySelectorAll('.div-filter-checkbox').forEach(cb => {
            if (!cb.disabled) {
                cb.checked = checked;
            }
        });
        updateDivisionFilterLabel();
    }

    function updateDivisionFilterLabel() {
        const enabledBoxes = document.querySelectorAll('.div-filter-checkbox:not(:disabled)');
        const checked = document.querySelectorAll('.div-filter-checkbox:checked');
        const label = document.getElementById('division-filter-label');
        const checkAll = document.getElementById('check-all-divisions');

        if (checked.length === enabledBoxes.length && enabledBoxes.length > 0) {
            label.textContent = `Semua Divisi (${checked.length})`;
            if (checkAll) checkAll.checked = true;
        } else if (checked.length === 0) {
            label.textContent = 'Pilih Divisi';
            if (checkAll) checkAll.checked = false;
        } else {
            label.textContent = `${checked.length} Divisi Terpilih`;
            if (checkAll) checkAll.checked = false;
        }

        filterCustomInternPicker();
    }

    // =========================================================================
    // TAB 4 INTERN POLICIES FILTERING & PAGINATION (50 PEMAGANG PER HALAMAN)
    // =========================================================================
    let internPolicyCurrentPage = 1;
    const internPolicyPerPage = 50;

    function filterInternPolicyTable(resetPage = true) {
        if (resetPage) {
            internPolicyCurrentPage = 1;
        }

        const query = (document.getElementById('intern-policy-search')?.value || '').toLowerCase().trim();
        const divFilter = (document.getElementById('intern-policy-division-filter')?.value || '').toLowerCase().trim();
        const schoolFilter = (document.getElementById('intern-policy-school-filter')?.value || '').toLowerCase().trim();
        const statusFilter = (document.getElementById('intern-policy-status-filter')?.value || '').trim();

        const allRows = Array.from(document.querySelectorAll('#tab-intern-policies tbody tr.intern-policy-row'));
        const matchingRows = [];

        allRows.forEach(tr => {
            const name = (tr.dataset.name || '').toLowerCase();
            const nim = (tr.dataset.nim || '').toLowerCase();
            const div = (tr.dataset.division || '').toLowerCase();
            const school = (tr.dataset.school || '').toLowerCase();
            const type = tr.dataset.type || '';
            const isUnset = tr.dataset.unsetPeriod === '1';

            const matchesQuery = !query || name.includes(query) || nim.includes(query) || school.includes(query);
            const matchesDiv = !divFilter || div === divFilter;
            const matchesSchool = !schoolFilter || school === schoolFilter;

            let matchesStatus = true;
            if (statusFilter === 'unset_period') {
                matchesStatus = isUnset;
            } else if (statusFilter) {
                matchesStatus = (type === statusFilter);
            }

            if (matchesQuery && matchesDiv && matchesSchool && matchesStatus) {
                matchingRows.push(tr);
            } else {
                tr.style.display = 'none';
            }
        });

        const totalMatching = matchingRows.length;
        const totalPages = Math.ceil(totalMatching / internPolicyPerPage) || 1;

        if (internPolicyCurrentPage > totalPages) {
            internPolicyCurrentPage = totalPages;
        }
        if (internPolicyCurrentPage < 1) {
            internPolicyCurrentPage = 1;
        }

        const startIndex = (internPolicyCurrentPage - 1) * internPolicyPerPage;
        const endIndex = Math.min(startIndex + internPolicyPerPage, totalMatching);

        matchingRows.forEach((tr, index) => {
            if (index >= startIndex && index < endIndex) {
                tr.style.display = '';
            } else {
                tr.style.display = 'none';
            }
        });

        const counterTop = document.getElementById('intern-policy-counter');
        const counterBottom = document.getElementById('intern-policy-count-bottom') || document.getElementById('intern-policy-count');
        const counterText = totalMatching > 0 ?
            `Menampilkan ${startIndex + 1} - ${endIndex} dari ${totalMatching} pemagang` :
            `Tidak ada pemagang ditemukan (0 dari ${allRows.length})`;

        if (counterTop) counterTop.textContent = counterText;
        if (counterBottom) counterBottom.textContent = counterText;

        renderInternPolicyPagination(totalPages, totalMatching);
    }

    function renderInternPolicyPagination(totalPages, totalMatching) {
        const container = document.getElementById('intern-policy-pagination');
        if (!container) return;

        if (totalMatching === 0 || totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center gap-1 sm:gap-1.5 flex-wrap">';

        // Tombol Sebelumnya
        if (internPolicyCurrentPage > 1) {
            html += `
                <button type="button" onclick="goToInternPolicyPage(${internPolicyCurrentPage - 1})" 
                    class="relative inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer shadow-2xs">
                    <svg class="w-4 h-4 mr-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span>Sebelumnya</span>
                </button>
            `;
        } else {
            html += `
                <span class="relative inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-400 bg-white border border-gray-200 cursor-not-allowed rounded-lg opacity-60 shadow-2xs">
                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span>Sebelumnya</span>
                </span>
            `;
        }

        // Nomor Halaman
        let pages = [];
        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) pages.push(i);
        } else {
            pages.push(1);
            if (internPolicyCurrentPage > 3) pages.push('...');
            const start = Math.max(2, internPolicyCurrentPage - 1);
            const end = Math.min(totalPages - 1, internPolicyCurrentPage + 1);
            for (let i = start; i <= end; i++) {
                if (!pages.includes(i)) pages.push(i);
            }
            if (internPolicyCurrentPage < totalPages - 2) pages.push('...');
            if (!pages.includes(totalPages)) pages.push(totalPages);
        }

        pages.forEach(p => {
            if (p === '...') {
                html += `<span class="relative inline-flex items-center px-2 py-1.5 text-xs font-medium text-gray-500">...</span>`;
            } else if (p === internPolicyCurrentPage) {
                html += `
                    <span aria-current="page" class="relative inline-flex items-center px-3.5 py-1.5 text-xs font-bold text-white bg-blue-600 border border-blue-600 rounded-lg shadow-2xs z-10">
                        ${p}
                    </span>
                `;
            } else {
                html += `
                    <button type="button" onclick="goToInternPolicyPage(${p})" 
                        class="relative inline-flex items-center px-3.5 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer shadow-2xs">
                        ${p}
                    </button>
                `;
            }
        });

        // Tombol Selanjutnya
        if (internPolicyCurrentPage < totalPages) {
            html += `
                <button type="button" onclick="goToInternPolicyPage(${internPolicyCurrentPage + 1})" 
                    class="relative inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer shadow-2xs">
                    <span>Selanjutnya</span>
                    <svg class="w-4 h-4 ml-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
            `;
        } else {
            html += `
                <span class="relative inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-400 bg-white border border-gray-200 cursor-not-allowed rounded-lg opacity-60 shadow-2xs">
                    <span>Selanjutnya</span>
                    <svg class="w-4 h-4 ml-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </span>
            `;
        }

        html += '</nav>';
        container.innerHTML = html;
    }

    function goToInternPolicyPage(page) {
        internPolicyCurrentPage = page;
        filterInternPolicyTable(false);
        const tableContainer = document.querySelector('#tab-intern-policies .overflow-y-auto');
        if (tableContainer) {
            tableContainer.scrollTop = 0;
        }
        const table = document.getElementById('intern-policy-table');
        if (table) {
            table.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }
    }

    function resetInternPolicyFilters() {
        if (document.getElementById('intern-policy-search')) document.getElementById('intern-policy-search').value = '';
        if (document.getElementById('intern-policy-division-filter')) document.getElementById('intern-policy-division-filter').value = '';
        if (document.getElementById('intern-policy-school-filter')) document.getElementById('intern-policy-school-filter').value = '';
        if (document.getElementById('intern-policy-status-filter')) document.getElementById('intern-policy-status-filter').value = '';
        filterInternPolicyTable(true);
    }

    function setInternPolicyStatusFilter(status) {
        const select = document.getElementById('intern-policy-status-filter');
        if (select) {
            select.value = status;
            filterInternPolicyTable(true);
        }
    }

    function confirmBalanceHybridGroups() {
        Swal.fire({
            title: 'Bagi Rata Grup A & B (50:50)?',
            html: `Sistem akan membagi seluruh pemagang non-remote 50% ke <strong>Grup A</strong> dan 50% ke <strong>Grup B</strong> secara proporsional per divisi.<br><small class="text-slate-500">Pemagang dari sekolah/kampus WFH Penuh tidak akan diubah.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#7c3aed',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Bagi Rata!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                Swal.fire({
                    title: 'Sedang Memproses...',
                    text: 'Membagi rata grup pemagang...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch("{{ route('admin.automation-rules.balance-groups') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#2563eb'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', data.message || 'Terjadi kesalahan sistem.', 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error', 'Gagal membagi rata grup pemagang.', 'error');
                    });
            }
        });
    }

    // =========================================================================
    // TAB 7 INTERACTIVE INTERN PICKER (SELECTIVE SCHEDULING)
    // =========================================================================
    function toggleInternScope(val) {
        const picker = document.getElementById('custom-intern-picker-container');
        if (!picker) return;
        if (val === 'custom') {
            picker.classList.remove('hidden');
            filterCustomInternPicker();
        } else {
            picker.classList.add('hidden');
        }
    }

    function selectAllCustomInterns(state) {
        document.querySelectorAll('.custom-intern-checkbox').forEach(cb => {
            const parent = cb.closest('.custom-intern-item');
            if (state) {
                if (!parent || parent.style.display !== 'none') {
                    cb.checked = true;
                }
            } else {
                cb.checked = false;
            }
        });
        updateCustomInternCount();
    }

    function updateCustomInternCount() {
        const checked = document.querySelectorAll('.custom-intern-checkbox:checked').length;
        const badge = document.getElementById('custom-intern-count-badge');
        if (badge) {
            badge.textContent = `${checked} pemagang dipilih`;
            if (checked > 0) {
                badge.className = 'px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-600 text-white font-mono';
            } else {
                badge.className = 'px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 font-mono';
            }
        }
    }

    function filterCustomInternPicker() {
        const query = (document.getElementById('custom-intern-search')?.value || '').toLowerCase().trim();
        const activeDivIds = new Set(Array.from(document.querySelectorAll('.div-filter-checkbox:checked')).map(cb => cb.value));

        document.querySelectorAll('.custom-intern-item').forEach(el => {
            const name = el.dataset.name || '';
            const nim = el.dataset.nim || '';
            const divId = el.dataset.divisionId || '';
            const divName = el.dataset.division || '';
            const schoolName = el.dataset.school || '';

            const matchesDiv = activeDivIds.has(divId);
            const matchesQuery = !query || name.includes(query) || nim.includes(query) || divName.includes(query) || schoolName.includes(query);

            if (matchesDiv && matchesQuery) {
                el.style.display = 'flex';
            } else {
                el.style.display = 'none';
                // Jika divisi pemagang ini tidak aktif / tidak dicentang di filter utama, uncheck otomatis
                if (!matchesDiv) {
                    const cb = el.querySelector('.custom-intern-checkbox');
                    if (cb && cb.checked) {
                        cb.checked = false;
                    }
                }
            }
        });

        updateCustomInternCount();
    }

    // =========================================================================
    // MODAL REKOMENDASI CERDAS (AI / CAPACITY RECOMMENDATIONS)
    // =========================================================================
    function openRecommendationModal() {
        const modal = document.getElementById('recommendation-modal');
        const loading = document.getElementById('recommendation-loading');
        const content = document.getElementById('recommendation-content');
        if (!modal) return;

        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        content.classList.add('hidden');

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch("{{ route('admin.automation-rules.recommendation') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                loading.classList.add('hidden');
                content.classList.remove('hidden');
                renderRecommendationContent(data);
            })
            .catch(err => {
                console.error(err);
                loading.classList.add('hidden');
                Swal.fire('Error', 'Gagal memuat rekomendasi otomatisasi jadwal.', 'error');
                closeRecommendationModal();
            });
    }

    function closeRecommendationModal() {
        const modal = document.getElementById('recommendation-modal');
        if (modal) modal.classList.add('hidden');
    }

    function renderRecommendationContent(data) {
        const summary = data.summary || {};
        const badgesEl = document.getElementById('rec-summary-badges');
        if (badgesEl) {
            badgesEl.innerHTML = `
                <span class="px-2.5 py-1 rounded-xl bg-blue-100 text-blue-800">Pemagang: ${summary.total_interns || 0}</span>
                <span class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800">Kursi: ${summary.total_seats || 0}</span>
                <span class="px-2.5 py-1 rounded-xl ${summary.is_overloaded ? 'bg-rose-100 text-rose-800' : 'bg-indigo-100 text-indigo-800'}">Rasio: ${summary.overall_ratio_pct || 0}%</span>
            `;
        }

        const summaryText = document.getElementById('rec-summary-text');
        if (summaryText) {
            summaryText.textContent = summary.is_overloaded ?
                `Kapasitas kursi fisik (${summary.total_seats} meja) lebih sedikit daripada pemagang aktif (${summary.total_interns} orang). Direkomendasikan mengaktifkan Hybrid 50% Rolling atau Split Shift.` :
                `Kapasitas kursi fisik memadai (${summary.total_seats} meja untuk ${summary.total_interns} pemagang). Prioritas WFO 100% aman diterapkan.`;
        }

        const listEl = document.getElementById('recommendation-list');
        listEl.innerHTML = '';
        const rawItems = data.recommendations || [];
        const items = Array.isArray(rawItems) ? rawItems : Object.values(rawItems);

        if (items.length === 0) {
            listEl.innerHTML = `<div class="p-4 text-center text-slate-400 text-xs">Belum ada data divisi untuk dianalisis.</div>`;
            return;
        }

        items.forEach(rec => {
            const card = document.createElement('div');
            card.className = 'p-3.5 rounded-2xl border border-slate-200 bg-white shadow-2xs space-y-2';
            const label = rec.suggested_label || rec.tier_name || (rec.suggested_mode === 'wfo_only' ? 'WFO Aman' : 'Hybrid Disarankan');
            const reasonText = rec.reason || rec.advice || '-';
            const isWfo = rec.suggested_mode === 'wfo_only' || (rec.recommended_tier === 1);

            card.innerHTML = `
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-bold text-xs sm:text-sm text-slate-900">${rec.division_name}</div>
                        <div class="text-[11px] text-slate-500">${rec.intern_count} pemagang aktif • Kapasitas Kantor: ${rec.office_capacity || 0} kursi</div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold ${isWfo ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800'}">
                        ${label}
                    </span>
                </div>
                <div class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                    ${reasonText}
                </div>
            `;
            listEl.appendChild(card);
        });
    }

    // =========================================================================
    // TAB 7 SIMULATION & DEPLOY STATE
    // =========================================================================
    let simulationAllocationsData = [];

    function onOfficeFilterChange() {
        const officeId = document.getElementById('sim-table-office-filter')?.value;
        const divSelect = document.getElementById('sim-table-division-filter');

        if (divSelect) {
            const allowedDivs = (officeId && officeId !== 'wfh' && officeAllowedDivisions[officeId]) ?
                officeAllowedDivisions[officeId] :
                null;

            Array.from(divSelect.options).forEach(opt => {
                if (!opt.value) {
                    opt.disabled = false;
                    return;
                }

                const divId = parseInt(opt.value);
                if (allowedDivs === null) {
                    opt.disabled = false;
                    if (opt.dataset.origText) opt.textContent = opt.dataset.origText;
                } else {
                    if (!opt.dataset.origText) opt.dataset.origText = opt.textContent.replace(' (Tidak aktif di kantor ini)', '');
                    if (allowedDivs.includes(divId)) {
                        opt.disabled = false;
                        opt.textContent = opt.dataset.origText;
                    } else {
                        opt.disabled = true;
                        opt.textContent = opt.dataset.origText + ' (Tidak aktif di kantor ini)';
                    }
                }
            });

            // Jika opsi divisi yang sedang terpilih ternyata tidak aktif di kantor ini, reset ke Semua Divisi
            if (divSelect.selectedOptions[0]?.disabled) {
                divSelect.value = '';
            }
        }

        filterSimulationTable();
    }

    function runSimulation() {
        const startDate = document.getElementById('sim_start_date')?.value;
        const endDate = document.getElementById('sim_end_date')?.value;

        if (!startDate || !endDate) {
            Swal.fire('Validasi', 'Harap tentukan tanggal mulai dan selesai.', 'warning');
            return;
        }

        const checkedDivisions = Array.from(document.querySelectorAll('.div-filter-checkbox:checked')).map(cb => parseInt(cb.value));
        const targetOfficeId = document.getElementById('sim_target_office_id')?.value || null;
        const internScope = document.querySelector('input[name="intern_scope"]:checked')?.value || 'all';

        let selectedInterns = null;
        if (internScope === 'custom') {
            selectedInterns = Array.from(document.querySelectorAll('.custom-intern-checkbox:checked')).map(cb => parseInt(cb.value));
            if (selectedInterns.length === 0) {
                Swal.fire('Validasi', 'Anda memilih opsi "Pilih Pemagang Tertentu Saja", namun belum memilih satupun pemagang pada daftar di bawah.', 'warning');
                return;
            }
        }

        const includeSundays = document.getElementById('sim_include_sundays')?.checked ? 1 : 0;
        const skipHolidays = document.getElementById('sim_skip_holidays')?.checked ? 1 : 0;
        const enableTier2 = document.getElementById('sim_enable_tier2_split_shift')?.checked ? 1 : 0;
        const enableTier3 = document.getElementById('sim_enable_tier3_hybrid_rolling')?.checked ? 1 : 0;

        const simBtn = document.getElementById('btn-run-simulation');
        const simIconContainer = document.getElementById('sim-icon-container');
        const simText = document.getElementById('sim-text');

        simBtn.disabled = true;
        simIconContainer.innerHTML = `
            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        `;
        simText.textContent = 'Memproses Haversine, Periode & Kuota...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch("{{ route('admin.automation-rules.simulate') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    start_date: startDate,
                    end_date: endDate,
                    division_ids: checkedDivisions,
                    target_office_id: targetOfficeId ? parseInt(targetOfficeId) : null,
                    intern_ids: selectedInterns,
                    include_sundays: includeSundays,
                    skip_holidays: skipHolidays,
                    enable_tier2_split_shift: enableTier2,
                    enable_tier3_hybrid_rolling: enableTier3
                })
            })
            .then(res => res.json())
            .then(data => {
                simBtn.disabled = false;
                simIconContainer.innerHTML = `
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M5.25 4.5v15l13.5-7.5-13.5-7.5z" />
                    </svg>
                `;
                simText.textContent = 'Jalankan Simulasi';

                if (!data.success) {
                    Swal.fire('Gagal Simulasi', data.message || 'Terjadi kesalahan sistem.', 'error');
                    return;
                }

                renderSimulationResults(data);
                const deployBtn = document.getElementById('btn-deploy-schedule');
                if (deployBtn) {
                    deployBtn.disabled = false;
                }

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 4500
                });
            })
            .catch(err => {
                console.error(err);
                simBtn.disabled = false;
                simIconContainer.innerHTML = `
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M5.25 4.5v15l13.5-7.5-13.5-7.5z" />
                    </svg>
                `;
                simText.textContent = 'Jalankan Simulasi';
                Swal.fire('Error', 'Gagal menjalankan simulasi penjadwalan.', 'error');
            });
    }

    function renderSimulationResults(data) {
        const container = document.getElementById('simulation-results-container');
        container.classList.remove('hidden');

        // Update KPIs
        const summary = data.summary || {};
        document.getElementById('kpi-interns').textContent = summary.total_interns || 0;
        document.getElementById('kpi-allocations').textContent = summary.total_allocations || 0;
        document.getElementById('kpi-wfo').textContent = summary.total_wfo || 0;
        document.getElementById('kpi-wfh').textContent = summary.total_wfh || 0;
        document.getElementById('kpi-spillover').textContent = summary.spillover_count || 0;
        document.getElementById('kpi-hybrid').textContent = summary.hybrid_count || 0;

        // Note: Unconfigured Interns Note Box
        const unconfBox = document.getElementById('sim-unconfigured-note-box');
        const unconfText = document.getElementById('sim-unconfigured-note-text');
        if (data.unconfigured_note || (data.unconfigured_interns && data.unconfigured_interns.length > 0)) {
            unconfBox.classList.remove('hidden');
            unconfText.textContent = data.unconfigured_note || 'Beberapa pemagang belum bisa didaftarkan jadwal karena belum diset periode magangnya.';
        } else {
            unconfBox.classList.add('hidden');
        }

        // Warnings Box
        const warningsBox = document.getElementById('sim-warnings-box');
        const warningsList = document.getElementById('sim-warnings-list');
        warningsList.innerHTML = '';
        if (data.warnings && data.warnings.length > 0) {
            warningsBox.classList.remove('hidden');
            data.warnings.forEach(w => {
                const li = document.createElement('li');
                li.textContent = w;
                warningsList.appendChild(li);
            });
        } else {
            warningsBox.classList.add('hidden');
        }

        // Desk Utilization Grid
        const utilGrid = document.getElementById('utilization-grid');
        utilGrid.innerHTML = '';
        if (summary.utilization && summary.utilization.length > 0) {
            summary.utilization.forEach(u => {
                let badgeColor = 'bg-emerald-500';
                let textBadgeColor = 'text-emerald-700 bg-emerald-100';
                if (u.percentage >= 90) {
                    badgeColor = 'bg-rose-500';
                    textBadgeColor = 'text-rose-700 bg-rose-100';
                } else if (u.percentage >= 70) {
                    badgeColor = 'bg-amber-500';
                    textBadgeColor = 'text-amber-700 bg-amber-100';
                }

                const peakConcurrent = u.peak_concurrent_office !== undefined ? u.peak_concurrent_office : u.peak_used;
                const remainingDesks = Math.max(0, u.max_capacity - peakConcurrent);

                const card = document.createElement('div');
                card.className = 'p-3.5 rounded-xl border border-slate-200 bg-slate-50/60 space-y-2';
                card.innerHTML = `
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-bold text-xs text-slate-900">${u.office_name}</div>
                            <div class="text-[11px] text-slate-500 font-medium">${u.shift_name} <span class="font-bold text-slate-700">(${u.peak_used} orang)</span></div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono ${textBadgeColor}">
                            ${u.percentage}%
                        </span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div class="${badgeColor} h-2 rounded-full transition-all duration-500" style="width: ${Math.min(100, u.percentage)}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 font-mono">
                        <span>Puncak Bersamaan: ${peakConcurrent} / ${u.max_capacity} meja</span>
                        <span>${remainingDesks > 0 ? remainingDesks + ' sisa meja' : 'Penuh'}</span>
                    </div>
                `;
                utilGrid.appendChild(card);
            });
        }

        // Store allocations
        simulationAllocationsData = data.allocations || [];

        // Populate unique dates into Date Filter Dropdown
        const dateFilterEl = document.getElementById('sim-table-date-filter');
        if (dateFilterEl) {
            const uniqueDates = [...new Set(simulationAllocationsData.map(a => a.date))].sort();
            dateFilterEl.innerHTML = '<option value="">Semua Tanggal</option>';
            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            uniqueDates.forEach(d => {
                const dateObj = new Date(d);
                const dayName = isNaN(dateObj) ? '' : dayNames[dateObj.getDay()] + ', ';
                const opt = document.createElement('option');
                opt.value = d;
                opt.textContent = `${dayName}${d}`;
                dateFilterEl.appendChild(opt);
            });
        }

        // Sinkronkan filter kantor & divisi, lalu render tabel
        onOfficeFilterChange();

        // Smooth scroll to results
        container.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    function resetSimulationFilters() {
        const dateFilter = document.getElementById('sim-table-date-filter');
        const officeFilter = document.getElementById('sim-table-office-filter');
        const divisionFilter = document.getElementById('sim-table-division-filter');
        const shiftFilter = document.getElementById('sim-table-shift-filter');
        const typeFilter = document.getElementById('sim-table-type-filter');
        const searchInput = document.getElementById('sim-table-search');

        if (dateFilter) dateFilter.value = '';
        if (officeFilter) officeFilter.value = '';
        if (divisionFilter) divisionFilter.value = '';
        if (shiftFilter) shiftFilter.value = '';
        if (typeFilter) typeFilter.value = '';
        if (searchInput) searchInput.value = '';

        onOfficeFilterChange();
    }

    function filterSimulationTable() {
        const dateFilter = document.getElementById('sim-table-date-filter')?.value || '';
        const officeFilter = document.getElementById('sim-table-office-filter')?.value || '';
        const divisionFilter = document.getElementById('sim-table-division-filter')?.value || '';
        const shiftFilter = document.getElementById('sim-table-shift-filter')?.value || '';
        const typeFilter = document.getElementById('sim-table-type-filter')?.value || '';
        const query = (document.getElementById('sim-table-search')?.value || '').toLowerCase().trim();

        const tbody = document.getElementById('sim-allocations-body');
        tbody.innerHTML = '';

        const filtered = simulationAllocationsData.filter(item => {
            // 1. Date Filter
            if (dateFilter && item.date !== dateFilter) return false;

            // 2. Office Filter
            if (officeFilter === 'wfh') {
                if (item.work_type !== 'wfh' && item.office_id) return false;
            } else if (officeFilter) {
                if (item.office_id != officeFilter) return false;
            }

            // 3. Shift Filter
            if (shiftFilter && item.shift_id != shiftFilter) return false;

            // 4. Work Type Filter
            if (typeFilter) {
                if (typeFilter === 'wfo' && (item.work_type !== 'wfo' || item.is_split_shift)) return false;
                if (typeFilter === 'wfh' && item.work_type !== 'wfh') return false;
                if (typeFilter === 'split_shift' && (item.work_type !== 'split_shift' && !item.is_split_shift)) return false;
            }

            // 5. Division Filter (Menampilkan pemagang sesuai divisi yang dipilih)
            if (divisionFilter) {
                const matchesDivId = item.division_id !== undefined && item.division_id == divisionFilter;
                const matchesDivName = item.division_name && item.division_name.toLowerCase() === divisionFilter.toLowerCase();
                if (!matchesDivId && !matchesDivName) return false;
            }

            // 6. Search Query
            if (query) {
                const nameMatch = item.intern_name && item.intern_name.toLowerCase().includes(query);
                const divMatch = item.division_name && item.division_name.toLowerCase().includes(query);
                const officeMatch = item.office_name && item.office_name.toLowerCase().includes(query);
                const schoolMatch = item.school_name && item.school_name.toLowerCase().includes(query);
                if (!nameMatch && !divMatch && !officeMatch && !schoolMatch) return false;
            }

            return true;
        });

        document.getElementById('sim-table-count').textContent = `Menampilkan ${filtered.length} dari ${simulationAllocationsData.length} entri`;

        // Update Daily & Office Occupancy Card
        updateDailyOccupancyCard(dateFilter, officeFilter, filtered);

        if (filtered.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="p-6 text-center text-slate-400">
                        Tidak ada alokasi jadwal yang cocok dengan filter yang dipilih.
                    </td>
                </tr>
            `;
            return;
        }

        const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        filtered.slice(0, 500).forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/80 transition-colors';

            // Date & Day formatting
            const dObj = new Date(item.date);
            const dName = isNaN(dObj) ? '' : dayNames[dObj.getDay()];
            const formattedDate = `${dName ? `<span class="font-bold text-slate-800">${dName}</span>, ` : ''}${item.date}`;

            // Work type badge
            let typeBadge = '';
            if (item.work_type === 'wfo') {
                typeBadge = `<span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">WFO</span>`;
            } else if (item.work_type === 'split_shift') {
                typeBadge = `<span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">Split</span>`;
            } else {
                typeBadge = `<span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800">WFH</span>`;
            }

            // Hybrid Group badge
            let hybridGroupBadge = `<span class="text-slate-400 font-mono text-[11px]">-</span>`;
            if (item.hybrid_group) {
                hybridGroupBadge = `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 font-mono">Grup ${item.hybrid_group}</span>`;
            } else if (item.hybrid_pattern && item.hybrid_pattern !== 'none') {
                const groupLetter = item.hybrid_pattern.toUpperCase().includes('B') ? 'B' : 'A';
                hybridGroupBadge = `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 font-mono">Grup ${groupLetter}</span>`;
            }

            // Status Alokasi (Istilah Ramah Pengguna & Mudah Dimengerti)
            let statusTags = '';
            if (item.is_remote || item.status_note === 'WFH Full' || (item.work_type === 'wfh' && item.office_name && item.office_name.toLowerCase().includes('remote')) || item.execution_tier === 0) {
                statusTags = `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">WFH Full</span>`;
            } else if (item.work_type === 'split_shift' || (item.is_split_shift && item.work_type !== 'wfh') || (item.status_note && item.status_note.toLowerCase().includes('split'))) {
                statusTags = `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Split (WFH + WFO Kantor)</span>`;
            } else if (item.work_type === 'wfh') {
                if (item.status_note && (item.status_note.includes('Penuh') || item.status_note.includes('Sementara'))) {
                    statusTags = `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">WFH Sementara (Meja Penuh)</span>`;
                } else if (item.execution_tier === 3) {
                    const grpLabel = item.hybrid_group ? `Grup ${item.hybrid_group}` : 'Bergilir';
                    statusTags = `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">Bergilir WFO/WFH (${grpLabel})</span>`;
                } else {
                    statusTags = `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">WFH Kebijakan</span>`;
                }
            } else if (item.is_spillover) {
                statusTags = `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Alih Kantor Cabang</span>`;
            } else if (item.execution_tier === 3) {
                const grpLabel = item.hybrid_group ? `Grup ${item.hybrid_group}` : 'Bergilir';
                statusTags = `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">Bergilir WFO/WFH (${grpLabel})</span>`;
            } else {
                statusTags = `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">WFO Prioritas</span>`;
            }

            if (item.is_last_day_of_internship) {
                statusTags += `<span class="ml-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 whitespace-nowrap">Hari Terakhir Magang</span>`;
            }

            const distDisplay = item.distance_km !== null ?
                `<span class="font-mono font-semibold text-slate-700">${item.distance_km} km</span>` :
                `<span class="text-slate-400 font-mono">-</span>`;

            tr.innerHTML = `
                <td class="p-3.5 text-center font-mono text-slate-500 font-bold text-xs">${index + 1}</td>
                <td class="p-3.5 font-mono text-slate-700 text-xs whitespace-nowrap">${formattedDate}</td>
                <td class="p-3.5">
                    <div class="font-bold text-slate-900">${item.intern_name}</div>
                    ${item.school_name && item.school_name !== '-' ? `<div class="text-[11px] font-medium text-slate-500 mt-0.5">${item.school_name}</div>` : ''}
                </td>
                <td class="p-3.5 text-slate-600 text-xs">${item.division_name || '-'}</td>
                <td class="p-3.5 font-semibold text-slate-800">${item.office_name}</td>
                <td class="p-3.5 text-center">${distDisplay}</td>
                <td class="p-3.5 text-slate-700 text-xs font-medium">${item.shift_name}</td>
                <td class="p-3.5 text-center">${typeBadge}</td>
                <td class="p-3.5 text-center">${hybridGroupBadge}</td>
                <td class="p-3.5 text-xs whitespace-nowrap">${statusTags}</td>
            `;

            tbody.appendChild(tr);
        });
    }

    function updateDailyOccupancyCard(dateFilter, officeFilter, currentFilteredAllocations) {
        const card = document.getElementById('sim-daily-occupancy-card');
        if (!card) return;

        // Show card if dateFilter or officeFilter is active
        if (!dateFilter && !officeFilter) {
            card.classList.add('hidden');
            return;
        }

        card.classList.remove('hidden');

        const titleEl = document.getElementById('daily-card-title');
        const subtitleEl = document.getElementById('daily-card-subtitle');
        const statsEl = document.getElementById('daily-card-stats');
        const breakdownEl = document.getElementById('daily-card-shift-breakdown');

        const wfoCount = currentFilteredAllocations.filter(a => a.work_type === 'wfo' || a.work_type === 'split_shift').length;
        const wfhCount = currentFilteredAllocations.filter(a => a.work_type === 'wfh').length;

        // Determine capacity
        let totalCapacity = 0;
        let officeName = 'Semua Kantor';

        if (officeFilter && officeFilter !== 'wfh' && officeCapacities[officeFilter]) {
            totalCapacity = officeCapacities[officeFilter].capacity;
            officeName = officeCapacities[officeFilter].name;
        } else if (officeFilter === 'wfh') {
            totalCapacity = 0;
            officeName = 'WFH (Rumah / Kos)';
        } else {
            // sum all office capacities
            Object.values(officeCapacities).forEach(o => totalCapacity += o.capacity);
        }

        const pct = totalCapacity > 0 ? Math.round((wfoCount / totalCapacity) * 100) : 0;
        let statusBadgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
        let statusText = 'Kapasitas Aman';

        if (officeFilter === 'wfh') {
            statusBadgeClass = 'bg-purple-100 text-purple-800 border-purple-300';
            statusText = 'Remote / WFH';
        } else if (pct > 100) {
            statusBadgeClass = 'bg-rose-100 text-rose-800 border-rose-300';
            statusText = 'Melebihi Kapasitas';
        } else if (pct === 100) {
            statusBadgeClass = 'bg-blue-100 text-blue-800 border-blue-300';
            statusText = 'Penuh';
        } else if (pct >= 80) {
            statusBadgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
            statusText = 'Hampir Penuh';
        } else if (pct === 0 && totalCapacity > 0) {
            statusBadgeClass = 'bg-slate-100 text-slate-600 border-slate-300';
            statusText = 'Kosong';
        }

        titleEl.textContent = `Okupansi: ${officeName} ${dateFilter ? '• ' + dateFilter : ''}`;
        subtitleEl.textContent = `Total pemagang hadir fisik: ${wfoCount} orang | Kapasitas meja harian: ${totalCapacity > 0 ? totalCapacity + ' kursi' : '-'}`;

        if (officeFilter === 'wfh') {
            statsEl.innerHTML = `
                <span class="px-2.5 py-1 rounded-xl text-xs font-bold border ${statusBadgeClass}">
                    ${statusText}
                </span>
                <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-purple-100 text-purple-800 font-mono">
                    ${wfhCount} WFH
                </span>
            `;
        } else {
            statsEl.innerHTML = `
                <span class="px-2.5 py-1 rounded-xl text-xs font-bold border ${statusBadgeClass}">
                    ${statusText} (${pct}%)
                </span>
                <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-blue-100 text-blue-800 font-mono">
                    ${wfoCount} WFO / ${wfhCount} WFH
                </span>
            `;
        }

        // Shift breakdown in current selection
        const shiftMap = {};
        currentFilteredAllocations.forEach(a => {
            if (a.shift_name) {
                shiftMap[a.shift_name] = (shiftMap[a.shift_name] || 0) + 1;
            }
        });

        breakdownEl.innerHTML = '';
        Object.entries(shiftMap).forEach(([sName, count]) => {
            const div = document.createElement('div');
            div.className = 'p-2 rounded-xl bg-white/80 border border-blue-100 flex items-center justify-between';
            div.innerHTML = `
                <span class="font-medium text-slate-700 truncate">${sName}</span>
                <span class="font-bold text-slate-900 font-mono text-xs bg-slate-100 px-2 py-0.5 rounded-lg">${count} orang</span>
            `;
            breakdownEl.appendChild(div);
        });
    }

    function confirmDeploySchedule() {
        const startDate = document.getElementById('sim_start_date')?.value;
        const endDate = document.getElementById('sim_end_date')?.value;

        Swal.fire({
            title: 'Setujui & Terapkan Jadwal ke Database?',
            html: `Hasil simulasi jadwal periode <strong>${startDate} s/d ${endDate}</strong> akan disetujui dan diterapkan secara resmi ke database.<br><small class="text-slate-500">Jadwal yang sudah ada pada rentang tanggal ini akan disinkronkan secara aman tanpa merusak riwayat presensi.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Setujui & Terapkan Sekarang!',
            cancelButtonText: 'Batal (Tinjau Kembali)'
        }).then((result) => {
            if (result.isConfirmed) {
                deploySchedule();
            }
        });
    }

    function deploySchedule() {
        const startDate = document.getElementById('sim_start_date')?.value;
        const endDate = document.getElementById('sim_end_date')?.value;
        const checkedDivisions = Array.from(document.querySelectorAll('.div-filter-checkbox:checked')).map(cb => parseInt(cb.value));
        const targetOfficeId = document.getElementById('sim_target_office_id')?.value || null;
        const internScope = document.querySelector('input[name="intern_scope"]:checked')?.value || 'all';

        let selectedInterns = null;
        if (internScope === 'custom') {
            selectedInterns = Array.from(document.querySelectorAll('.custom-intern-checkbox:checked')).map(cb => parseInt(cb.value));
        }

        const includeSundays = document.getElementById('sim_include_sundays')?.checked ? 1 : 0;
        const skipHolidays = document.getElementById('sim_skip_holidays')?.checked ? 1 : 0;
        const enableTier2 = document.getElementById('sim_enable_tier2_split_shift')?.checked ? 1 : 0;
        const enableTier3 = document.getElementById('sim_enable_tier3_hybrid_rolling')?.checked ? 1 : 0;

        const deployBtn = document.getElementById('btn-deploy-schedule');
        deployBtn.disabled = true;
        deployBtn.innerHTML = `
            <svg class="animate-spin w-4 h-4 text-white inline mr-1" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Menyetujui & Menyimpan ke Database...</span>
        `;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch("{{ route('admin.automation-rules.deploy') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    start_date: startDate,
                    end_date: endDate,
                    division_ids: checkedDivisions,
                    target_office_id: targetOfficeId ? parseInt(targetOfficeId) : null,
                    intern_ids: selectedInterns,
                    include_sundays: includeSundays,
                    skip_holidays: skipHolidays,
                    enable_tier2_split_shift: enableTier2,
                    enable_tier3_hybrid_rolling: enableTier3
                })
            })
            .then(res => res.json())
            .then(data => {
                deployBtn.disabled = false;
                deployBtn.innerHTML = `
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span>Setujui & Terapkan Jadwal</span>
                `;

                if (!data.success) {
                    Swal.fire('Gagal Menyimpan', data.message || 'Terjadi kesalahan sistem saat menyimpan jadwal.', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Berhasil Disetujui & Diterapkan!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#2563eb'
                });
            })
            .catch(err => {
                console.error(err);
                deployBtn.disabled = false;
                deployBtn.innerHTML = `
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span>Setujui & Terapkan Jadwal</span>
                `;
                Swal.fire('Error', 'Gagal menerapkan jadwal ke database.', 'error');
            });
    }

    // Inisialisasi awal tabel kebijakan pemagang (batasi 50 pemagang per halaman)
    if (typeof filterInternPolicyTable === 'function') {
        filterInternPolicyTable(true);
    }
</script>
@endsection