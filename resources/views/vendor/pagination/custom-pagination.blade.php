@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $maxPages = 5;

        $start = max(1, $currentPage - 2);
        $end = min($lastPage, $start + ($maxPages - 1));

        if ($end - $start + 1 < $maxPages) {
            $start = max(1, $end - ($maxPages - 1));
        }
    @endphp

    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center gap-1.5 flex-wrap justify-center sm:justify-end">
        {{-- Tombol Sebelumnya --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed flex items-center gap-1.5 select-none shadow-2xs">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Sebelumnya</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-gray-200 bg-white hover:bg-gray-100 text-gray-700 hover:text-blue-600 transition shadow-2xs flex items-center gap-1.5 select-none">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Sebelumnya</span>
            </a>
        @endif

        {{-- First Page jika start > 1 --}}
        @if ($start > 1)
            <a href="{{ $paginator->url(1) }}"
                class="w-8 h-8 flex items-center justify-center text-xs font-semibold rounded-xl border border-gray-200 bg-white hover:bg-gray-100 text-gray-700 hover:text-blue-600 hover:border-blue-300 transition shadow-2xs select-none">
                1
            </a>
            @if ($start > 2)
                <span class="w-6 h-8 flex items-center justify-center text-xs text-gray-400 font-bold select-none">
                    ...
                </span>
            @endif
        @endif

        {{-- Loop Angka Halaman (Maksimal 5 Halaman) --}}
        @for ($page = $start; $page <= $end; $page++)
            @if ($page == $currentPage)
                <span aria-current="page"
                    class="w-8 h-8 flex items-center justify-center text-xs font-bold rounded-xl bg-blue-600 text-white shadow-xs select-none">
                    {{ $page }}
                </span>
            @else
                <a href="{{ $paginator->url($page) }}"
                    class="w-8 h-8 flex items-center justify-center text-xs font-semibold rounded-xl border border-gray-200 bg-white hover:bg-gray-100 text-gray-700 hover:text-blue-600 hover:border-blue-300 transition shadow-2xs select-none">
                    {{ $page }}
                </a>
            @endif
        @endfor

        {{-- Last Page jika end < lastPage --}}
        @if ($end < $lastPage)
            @if ($end < $lastPage - 1)
                <span class="w-6 h-8 flex items-center justify-center text-xs text-gray-400 font-bold select-none">
                    ...
                </span>
            @endif
            <a href="{{ $paginator->url($lastPage) }}"
                class="w-8 h-8 flex items-center justify-center text-xs font-semibold rounded-xl border border-gray-200 bg-white hover:bg-gray-100 text-gray-700 hover:text-blue-600 hover:border-blue-300 transition shadow-2xs select-none">
                {{ $lastPage }}
            </a>
        @endif

        {{-- Tombol Selanjutnya --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-gray-200 bg-white hover:bg-gray-100 text-gray-700 hover:text-blue-600 transition shadow-2xs flex items-center gap-1.5 select-none">
                <span>Selanjutnya</span>
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @else
            <span class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed flex items-center gap-1.5 select-none shadow-2xs">
                <span>Selanjutnya</span>
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </span>
        @endif
    </nav>
@endif