@extends('layouts.main')

@section('title', 'Pengaturan Quotes')

@section('contents')

@include('layouts.sidebar-pengaturan')

<!-- Main Content -->
<main class="ml-0 lg:ml-[32rem] mt-2 lg:mt-20 p-3 sm:p-6 min-w-0 max-w-full overflow-x-hidden">
    <div class="w-full max-w-full min-w-0 space-y-6">

        <!-- Header & Summary Cards -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
                        <span class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 shadow-2xs">
                            <i class="fa-solid fa-quote-left text-lg"></i>
                        </span>
                        <span>Manage Quotes</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 ml-0 sm:ml-13">
                        Kelola kutipan motivasi harian dan ucapan selamat ulang tahun untuk beranda pemagang.
                    </p>
                </div>
            </div>

            <!-- Tabs Filter (Semua / Harian / Ultah) -->
            <div class="flex items-center gap-2 mt-5 pt-4 border-t border-slate-100 flex-wrap">
                <button type="button" onclick="switchQuoteTab('all')" id="tab-btn-all"
                    class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition shadow-2xs bg-slate-900 text-white cursor-pointer">
                    <i class="fa-solid fa-grip-vertical mr-1.5"></i> Semua Kategori
                </button>
                <button type="button" onclick="switchQuoteTab('daily')" id="tab-btn-daily"
                    class="tab-btn px-4 py-2 rounded-xl text-xs font-semibold transition border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 cursor-pointer">
                    <i class="fa-solid fa-lightbulb text-blue-600 mr-1.5"></i> Motivasi Harian ({{ count($quotes) }})
                </button>
                <button type="button" onclick="switchQuoteTab('birthday')" id="tab-btn-birthday"
                    class="tab-btn px-4 py-2 rounded-xl text-xs font-semibold transition border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 cursor-pointer">
                    <i class="fa-solid fa-cake-candles text-amber-500 mr-1.5"></i> Ulang Tahun ({{ count($quotesultah) }})
                </button>
            </div>
        </div>

        <!-- Flash Notifications -->
        @if (session('success'))
        <div id="success-message"
            class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-2xl shadow-2xs flex items-center justify-between transition-opacity duration-500"
            role="alert">
            <div class="flex items-center gap-2.5 text-xs sm:text-sm font-medium">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="text-emerald-600 hover:text-emerald-900 cursor-pointer" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif

        @if ($errors->any())
        <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-2xl shadow-2xs flex items-start justify-between" role="alert">
            <div class="flex items-start gap-2.5 text-xs sm:text-sm">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-base mt-0.5"></i>
                <div>
                    <span class="font-bold">Terjadi Kesalahan:</span>
                    <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs text-rose-700">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="text-rose-600 hover:text-rose-900 cursor-pointer" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif

        <!-- 2-Column Equal Grid (Motivasi Harian & Quotes Ulang Tahun) -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">

            <!-- PANEL 1: KUTIPAN MOTIVASI HARIAN -->
            <div id="panel-daily" class="space-y-5 transition-all">
                <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-2xs flex flex-col">
                    <!-- Panel Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 shadow-2xs">
                                <i class="fa-solid fa-lightbulb text-base"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Kutipan Motivasi Harian</h2>
                                <p class="text-xs text-slate-500">Tampil acak di beranda pemagang</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-xl border border-blue-200">
                            {{ count($quotes) }} Data
                        </span>
                    </div>

                    <!-- Form Tambah Quote Harian -->
                    <form action="{{ route('quotes.store') }}" method="POST" class="pt-4 pb-4 border-b border-slate-100 space-y-3">
                        @csrf
                        <input type="hidden" name="kategori" value="quote">
                        <div class="relative">
                            <label for="input-quote-daily" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-pen-nib text-blue-600"></i>
                                    <span>Tulis Kutipan Baru</span>
                                </span>
                                <span id="char-count-daily" class="text-[11px] font-normal text-slate-400">0/255</span>
                            </label>
                            <textarea id="input-quote-daily" name="quote" rows="2" maxlength="255" required
                                oninput="document.getElementById('char-count-daily').textContent = this.value.length + '/255'"
                                placeholder="Tuliskan kata mutiara atau motivasi yang menginspirasi..."
                                class="w-full px-4 py-2.5 bg-slate-50/60 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-blue-500 rounded-2xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition resize-none"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-2xs hover:shadow-xs transition cursor-pointer">
                                <i class="fa-solid fa-plus text-[11px]"></i>
                                <span>Simpan Kutipan</span>
                            </button>
                        </div>
                    </form>

                    <!-- Search Bar Kutipan Harian -->
                    <div class="pt-4 pb-3">
                        <div class="relative">
                            <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" id="search-daily" onkeyup="filterQuotes('daily')"
                                placeholder="Cari kutipan motivasi..."
                                class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-blue-500 transition">
                        </div>
                    </div>

                    <!-- Daftar Kutipan Harian -->
                    <div class="max-h-[380px] overflow-y-auto pr-1 space-y-2.5 custom-scrollbar" id="list-daily">
                        @forelse ($quotes as $index => $quote)
                        <div class="quote-item-daily group flex items-start justify-between gap-3 p-3.5 bg-slate-50/70 hover:bg-slate-100/80 border border-slate-200/70 rounded-2xl transition">
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                <span class="w-6 h-6 rounded-lg bg-blue-100/70 text-blue-700 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">
                                    #{{ $loop->iteration }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="quote-text text-xs sm:text-sm text-slate-800 font-medium leading-relaxed italic">
                                        "{{ $quote->quote }}"
                                    </p>
                                </div>
                            </div>
                            <form action="{{ route('quotes.delete', $quote->id) }}" method="POST" class="shrink-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kutipan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Hapus Kutipan"
                                    class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition cursor-pointer">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                        @empty
                        <div class="py-10 text-center text-slate-400 text-xs empty-state">
                            <i class="fa-solid fa-quote-left text-3xl mb-2.5 block text-slate-300"></i>
                            <span>Belum ada kutipan motivasi harian yang ditambahkan</span>
                        </div>
                        @endforelse
                        <div id="no-search-daily" class="py-8 text-center text-slate-400 text-xs hidden">
                            <i class="fa-solid fa-magnifying-glass text-2xl mb-2 block text-slate-300"></i>
                            <span>Tidak ditemukan kutipan yang cocok</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL 2: QUOTES ULANG TAHUN -->
            <div id="panel-birthday" class="space-y-5 transition-all">
                <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-2xs flex flex-col">
                    <!-- Panel Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 shadow-2xs">
                                <i class="fa-solid fa-cake-candles text-base"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Quotes Ulang Tahun</h2>
                                <p class="text-xs text-slate-500">Ucapan saat pemagang berulang tahun</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-xl border border-amber-200">
                            {{ count($quotesultah) }} Data
                        </span>
                    </div>

                    <!-- Form Tambah Quote Ultah -->
                    <form action="{{ route('quotes.ultah.store') }}" method="POST" class="pt-4 pb-4 border-b border-slate-100 space-y-3">
                        @csrf
                        <input type="hidden" name="kategori" value="ultah">
                        <div class="relative">
                            <label for="input-quote-birthday" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-gift text-amber-600"></i>
                                    <span>Tulis Ucapan Ulang Tahun Baru</span>
                                </span>
                                <span id="char-count-birthday" class="text-[11px] font-normal text-slate-400">0/255</span>
                            </label>
                            <textarea id="input-quote-birthday" name="quote" rows="2" maxlength="255" required
                                oninput="document.getElementById('char-count-birthday').textContent = this.value.length + '/255'"
                                placeholder="Tuliskan ucapan hangat selamat ulang tahun..."
                                class="w-full px-4 py-2.5 bg-slate-50/60 hover:bg-slate-50 focus:bg-white border border-slate-200 focus:border-amber-500 rounded-2xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition resize-none"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-xl shadow-2xs hover:shadow-xs transition cursor-pointer">
                                <i class="fa-solid fa-plus text-[11px]"></i>
                                <span>Simpan Ucapan</span>
                            </button>
                        </div>
                    </form>

                    <!-- Search Bar Kutipan Ultah -->
                    <div class="pt-4 pb-3">
                        <div class="relative">
                            <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" id="search-birthday" onkeyup="filterQuotes('birthday')"
                                placeholder="Cari ucapan ulang tahun..."
                                class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-amber-500 transition">
                        </div>
                    </div>

                    <!-- Daftar Kutipan Ultah -->
                    <div class="max-h-[380px] overflow-y-auto pr-1 space-y-2.5 custom-scrollbar" id="list-birthday">
                        @forelse ($quotesultah as $index => $quote)
                        <div class="quote-item-birthday group flex items-start justify-between gap-3 p-3.5 bg-amber-50/30 hover:bg-amber-50/70 border border-amber-200/50 rounded-2xl transition">
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-800 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">
                                    #{{ $loop->iteration }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="quote-text text-xs sm:text-sm text-slate-800 font-medium leading-relaxed italic">
                                        "{{ $quote->quote }}"
                                    </p>
                                </div>
                            </div>
                            <form action="{{ route('quotes.delete', $quote->id) }}" method="POST" class="shrink-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kutipan ulang tahun ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Hapus Ucapan"
                                    class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition cursor-pointer">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                        @empty
                        <div class="py-10 text-center text-slate-400 text-xs empty-state">
                            <i class="fa-solid fa-birthday-cake text-3xl mb-2.5 block text-slate-300"></i>
                            <span>Belum ada kutipan ulang tahun yang ditambahkan</span>
                        </div>
                        @endforelse
                        <div id="no-search-birthday" class="py-8 text-center text-slate-400 text-xs hidden">
                            <i class="fa-solid fa-magnifying-glass text-2xl mb-2 block text-slate-300"></i>
                            <span>Tidak ditemukan ucapan yang cocok</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- Custom Style for subtle scrollbar -->
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #e2e8f0;
        border-radius: 9999px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background-color: #cbd5e1;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto fade success message
        const message = document.getElementById('success-message');
        if (message) {
            setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 500);
            }, 3500);
        }
    });

    // Tab Switcher Handler
    function switchQuoteTab(tab) {
        const panelDaily = document.getElementById('panel-daily');
        const panelBirthday = document.getElementById('panel-birthday');
        const btnAll = document.getElementById('tab-btn-all');
        const btnDaily = document.getElementById('tab-btn-daily');
        const btnBirthday = document.getElementById('tab-btn-birthday');

        // Reset button styles
        [btnAll, btnDaily, btnBirthday].forEach(btn => {
            btn.className = 'tab-btn px-4 py-2 rounded-xl text-xs font-semibold transition border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 cursor-pointer';
        });

        const activeClass = 'tab-btn px-4 py-2 rounded-xl text-xs font-bold transition shadow-2xs bg-slate-900 text-white cursor-pointer';

        if (tab === 'daily') {
            panelDaily.classList.remove('hidden');
            panelDaily.classList.add('xl:col-span-2');
            panelBirthday.classList.add('hidden');
            panelBirthday.classList.remove('xl:col-span-2');
            btnDaily.className = activeClass;
        } else if (tab === 'birthday') {
            panelDaily.classList.add('hidden');
            panelDaily.classList.remove('xl:col-span-2');
            panelBirthday.classList.remove('hidden');
            panelBirthday.classList.add('xl:col-span-2');
            btnBirthday.className = activeClass;
        } else {
            panelDaily.classList.remove('hidden', 'xl:col-span-2');
            panelBirthday.classList.remove('hidden', 'xl:col-span-2');
            btnAll.className = activeClass;
        }
    }

    // Live Search Filter for Quotes
    function filterQuotes(type) {
        const input = document.getElementById('search-' + type);
        const query = input ? input.value.toLowerCase().trim() : '';
        const items = document.querySelectorAll('.quote-item-' + type);
        const noResults = document.getElementById('no-search-' + type);
        let visibleCount = 0;

        items.forEach(item => {
            const textElem = item.querySelector('.quote-text');
            const text = textElem ? textElem.textContent.toLowerCase() : '';
            if (text.includes(query)) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (noResults) {
            noResults.style.display = (visibleCount === 0 && query !== '') ? 'block' : 'none';
        }
    }
</script>
@endsection