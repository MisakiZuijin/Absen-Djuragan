@extends('layouts.main')

@section('title', 'Pengaturan Quotes')

@section('contents')

    @include('layouts.sidebar-pengaturan')

    <!-- Main Content -->
    <main class="ml-0 lg:ml-[32rem] mt-2 lg:mt-20 p-3 sm:p-6 min-w-0 max-w-full overflow-x-hidden">
        <div class="w-full max-w-full min-w-0 space-y-6">

            <!-- Header -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs">
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Manage Quotes</h1>
                <p class="text-gray-500 text-sm">Pengaturan kutipan motivasi harian dan ucapan selamat ulang tahun</p>
            </div>

            @if (session('success'))
                <div id="success-message"
                    class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl relative transition-opacity duration-500 shadow-xs flex items-center justify-between"
                    role="alert">
                    <div>
                        <strong class="font-bold">Sukses!</strong>
                        <span class="block sm:inline ml-1">{{ session('success') }}</span>
                    </div>
                    <button type="button" class="text-emerald-600 hover:text-emerald-900" onclick="removeMessage()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Quotes List -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs">
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                            <div>
                                <h2 class="text-base font-bold text-gray-900">Daftar Kutipan Motivasi Harian</h2>
                                <p class="text-xs text-gray-500">Kutipan yang ditampilkan secara acak di beranda pemagang</p>
                            </div>
                            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg border border-blue-200">
                                {{ count($quotes) }} Quotes
                            </span>
                        </div>

                        <ul class="divide-y divide-gray-100 max-h-96 overflow-y-auto pr-1">
                            @forelse ($quotes as $quote)
                                <li class="flex justify-between items-center py-3 gap-3 hover:bg-gray-50/60 px-2 rounded-xl transition">
                                    <div class="flex items-start gap-2.5 min-w-0">
                                        <i class="fas fa-quote-left text-gray-300 text-xs mt-1 shrink-0"></i>
                                        <span class="text-xs sm:text-sm text-gray-700 font-medium leading-relaxed">{{ $quote->quote }}</span>
                                    </div>
                                    <form action="{{ route('quotes.delete', $quote->id) }}" method="POST" class="shrink-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kutipan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Quote" class="p-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition shadow-xs flex items-center justify-center w-7 h-7">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </li>
                            @empty
                                <li class="py-8 text-center text-gray-400 text-xs">
                                    <i class="fas fa-quote-right text-2xl mb-2 block text-gray-300"></i>
                                    Belum ada kutipan harian yang ditambahkan
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs">
                        <form action="{{ route('quotes.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-plus text-blue-600"></i> Tambah Kutipan Harian
                            </h3>
                            <div>
                                <textarea name="quote" placeholder="Tuliskan kata-kata mutiara atau motivasi baru..." rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                            </div>
                            <input type="hidden" name="kategori" value="quote">
                            <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm rounded-xl shadow-xs transition">
                                <i class="fas fa-plus mr-1.5"></i> Tambahkan Kutipan
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Quotes Ulang Tahun -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs">
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                            <div>
                                <h2 class="text-base font-bold text-gray-900">Quotes Ulang Tahun</h2>
                                <p class="text-xs text-gray-500">Ucapan saat pemagang berulang tahun</p>
                            </div>
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-lg border border-amber-200">
                                {{ count($quotesultah) }}
                            </span>
                        </div>

                        <ul class="divide-y divide-gray-100 max-h-96 overflow-y-auto pr-1">
                            @forelse ($quotesultah as $quote)
                                <li class="flex justify-between items-center py-3 gap-3 hover:bg-gray-50/60 px-2 rounded-xl transition">
                                    <div class="flex items-start gap-2 min-w-0">
                                        <i class="fas fa-cake-candles text-amber-500 text-xs mt-1 shrink-0"></i>
                                        <span class="text-xs sm:text-sm text-gray-700 font-medium leading-relaxed">{{ $quote->quote }}</span>
                                    </div>
                                    <form action="{{ route('quotes.delete', $quote->id) }}" method="POST" class="shrink-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kutipan ulang tahun ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Quote Ultah" class="p-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition shadow-xs flex items-center justify-center w-7 h-7">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </li>
                            @empty
                                <li class="py-8 text-center text-gray-400 text-xs">
                                    <i class="fas fa-birthday-cake text-2xl mb-2 block text-gray-300"></i>
                                    Belum ada kutipan ulang tahun
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs">
                        <form action="{{ route('quotes.ultah.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-plus text-amber-600"></i> Tambah Quote Ulang Tahun
                            </h3>
                            <div>
                                <textarea name="quote" placeholder="Masukkan ucapan selamat ulang tahun..." rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-amber-500" required></textarea>
                            </div>
                            <input type="hidden" name="kategori" value="ultah">
                            <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs sm:text-sm rounded-xl shadow-xs transition">
                                <i class="fas fa-plus mr-1.5"></i> Tambahkan Ucapan
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </main>
    <script src="{{ asset('js/admin/setting.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const message = document.getElementById('success-message');
            if (message) {
                setTimeout(() => {
                    message.style.opacity = 0;
                    setTimeout(() => message.remove(), 600);
                }, 3000);
            }
        });
    </script>
@endsection
