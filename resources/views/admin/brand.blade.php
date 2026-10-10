@extends('layouts.main')

@section('title', 'Brand')

@section('contents')
    <!-- Main Content -->
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">

        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1 flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-tags text-base"></i>
                            </span>
                            <span>Daftar Brand</span>
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-500">
                            Daftar brand dan unit bisnis yang menaungi penugasan pemagang
                        </p>
                    </div>

                    <!-- Tombol Kelola Master Brand (Shortcut ke Pengaturan) -->
                    <div>
                        <a href="{{ route('admin.pengaturan.brand') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 hover:text-blue-600 hover:border-blue-300 rounded-xl text-xs sm:text-sm font-semibold transition shadow-xs">
                            <i class="fa-solid fa-gear text-gray-400"></i>
                            <span>Kelola Master Brand</span>
                        </a>
                    </div>
                </div>

                <!-- Search Field -->
                <div class="bg-white p-3 sm:p-4 rounded-2xl border border-gray-100 shadow-xs">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-search text-sm"></i>
                        </span>
                        <input type="text" id="searchInput"
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50/50 hover:bg-white focus:bg-white border border-gray-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-2xs"
                            placeholder="Cari nama brand...">
                    </div>
                </div>

                <!-- Brand Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6" id="brandListContainer">
                    @forelse ($brandList as $item)
                        <a href="{{ route('admin.brand.team.view', ['brandId' => $item->id]) }}"
                            class="brand-card group bg-white hover:bg-blue-50/20 rounded-2xl overflow-hidden shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between border border-gray-200/80 hover:border-blue-300">
                            <div class="p-4 sm:p-5 flex-grow">
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <!-- Logo / Avatar -->
                                    <div class="w-12 h-12 rounded-xl bg-gray-50 p-1.5 flex items-center justify-center shrink-0 overflow-hidden border border-gray-200 shadow-2xs">
                                        @if ($item->logo && file_exists(public_path('img/brands/' . $item->logo)))
                                            <img src="{{ asset('img/brands/' . $item->logo) }}" alt="{{ $item->name }}" class="w-full h-full object-contain rounded-lg">
                                        @else
                                            <i class="fa-solid fa-tags text-xl text-blue-600"></i>
                                        @endif
                                    </div>

                                    <!-- Status Badge -->
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $item->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                                        {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>

                                <h2 class="text-base sm:text-lg font-bold text-gray-900 group-hover:text-blue-600 transition line-clamp-1">
                                    {{ $item->name }}
                                </h2>
                                @if($item->description)
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2 leading-relaxed">
                                        {{ $item->description }}
                                    </p>
                                @else
                                    <p class="text-xs text-gray-400 mt-1 italic">
                                        Unit bisnis brand terdaftar
                                    </p>
                                @endif

                                <div class="flex items-center justify-between mt-5 pt-3 border-t border-gray-100">
                                    <span class="text-xs text-gray-500 font-medium">Total Pemagang</span>
                                    <div class="flex items-center gap-1.5 text-gray-900 font-bold text-base">
                                        <i class="fa-solid fa-users text-xs text-blue-600"></i>
                                        <span>{{ $item->interns_count ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50/80 group-hover:bg-blue-50/50 px-4 py-2.5 flex justify-between items-center text-xs font-semibold text-gray-600 group-hover:text-blue-600 border-t border-gray-100 transition-colors">
                                <span>Lihat Pemagang</span>
                                <i class="fa-solid fa-arrow-right text-[11px] group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full bg-white rounded-2xl p-10 text-center border border-gray-100 shadow-xs">
                            <i class="fa-solid fa-tags text-3xl text-gray-300 mb-3 block"></i>
                            <h3 class="text-sm font-bold text-gray-800">Belum Ada Brand Terdaftar</h3>
                            <p class="text-xs text-gray-500 mt-1 mb-4">Tambahkan brand baru melalui menu Pengaturan Brand.</p>
                            <a href="{{ route('admin.pengaturan.brand') }}"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition">
                                <i class="fa-solid fa-plus text-xs"></i>
                                <span>Tambah Brand</span>
                            </a>
                        </div>
                    @endforelse

                    <!-- Kartu Khusus: Pemagang Tanpa Brand (Jika Ada) -->
                    @if(isset($unassignedCount) && $unassignedCount > 0)
                        <a href="{{ route('admin.brand.team.view', ['brandId' => 'unassigned']) }}"
                            class="brand-card group bg-white hover:bg-amber-50/30 rounded-2xl overflow-hidden shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between border border-dashed border-amber-300 hover:border-amber-400">
                            <div class="p-4 sm:p-5 flex-grow">
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="w-12 h-12 rounded-xl bg-amber-50 p-1.5 flex items-center justify-center shrink-0 border border-amber-200 shadow-2xs">
                                        <i class="fa-solid fa-user-tag text-xl text-amber-600"></i>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        Perlu Ditugaskan
                                    </span>
                                </div>

                                <h2 class="text-base sm:text-lg font-bold text-gray-900 group-hover:text-amber-700 transition line-clamp-1">
                                    Tanpa Brand
                                </h2>
                                <p class="text-xs text-gray-500 mt-1 line-clamp-2 leading-relaxed">
                                    Daftar pemagang yang belum terafiliasi dengan Brand manapun.
                                </p>

                                <div class="flex items-center justify-between mt-5 pt-3 border-t border-gray-100">
                                    <span class="text-xs text-gray-500 font-medium">Total Pemagang</span>
                                    <div class="flex items-center gap-1.5 text-amber-700 font-bold text-base">
                                        <i class="fa-solid fa-users text-xs text-amber-600"></i>
                                        <span>{{ $unassignedCount }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-amber-50/40 group-hover:bg-amber-50/80 px-4 py-2.5 flex justify-between items-center text-xs font-semibold text-amber-800 border-t border-amber-100 transition-colors">
                                <span>Lihat Pemagang</span>
                                <i class="fa-solid fa-arrow-right text-[11px] group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const searchInput = document.getElementById('searchInput');
                const cards = document.querySelectorAll('.brand-card');

                if (searchInput) {
                    searchInput.addEventListener('input', function () {
                        const term = this.value.toLowerCase().trim();
                        cards.forEach(card => {
                            const text = card.textContent.toLowerCase();
                            card.style.display = text.includes(term) ? '' : 'none';
                        });
                    });
                }
            });
        </script>
    </main>
@endsection
