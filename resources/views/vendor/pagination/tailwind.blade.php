@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row items-center justify-between gap-3 w-full">
        <!-- Info Ringkasan -->
        <div class="text-xs text-gray-500">
            Menampilkan
            <span class="font-bold text-gray-900">{{ $paginator->firstItem() ?? 0 }}</span>
            sampai
            <span class="font-bold text-gray-900">{{ $paginator->lastItem() ?? 0 }}</span>
            dari
            <span class="font-bold text-gray-900">{{ $paginator->total() }}</span>
            data pemagang
        </div>

        <!-- Tombol Halaman dengan Jarak (Spacious Controls) -->
        <div class="flex items-center gap-1.5 flex-wrap">
            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed flex items-center gap-1.5 select-none">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    <span>Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-gray-200 bg-white hover:bg-gray-100 text-gray-700 hover:text-blue-600 transition shadow-2xs flex items-center gap-1.5">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    <span>Sebelumnya</span>
                </a>
            @endif

            {{-- Elemen Nomor Halaman --}}
            @foreach ($elements as $element)
                {{-- Separator Titik Tiga --}}
                @if (is_string($element))
                    <span class="w-8 h-8 flex items-center justify-center text-xs text-gray-400 font-bold select-none">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Link Halaman --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="w-8 h-8 flex items-center justify-center text-xs font-bold rounded-xl bg-blue-600 text-white shadow-xs select-none">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                                class="w-8 h-8 flex items-center justify-center text-xs font-semibold rounded-xl border border-gray-200 bg-white hover:bg-gray-100 text-gray-700 hover:text-blue-600 hover:border-blue-300 transition shadow-2xs">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Selanjutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                    class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-gray-200 bg-white hover:bg-gray-100 text-gray-700 hover:text-blue-600 transition shadow-2xs flex items-center gap-1.5">
                    <span>Selanjutnya</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </a>
            @else
                <span class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed flex items-center gap-1.5 select-none">
                    <span>Selanjutnya</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
