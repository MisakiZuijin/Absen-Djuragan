@extends('layouts.main')

@section('title', 'Sekolah')

@section('contents')
    <!-- Main Content -->
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">

        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4 sm:mb-6">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Daftar Sekolah / Universitas</h1>
                        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Daftar institusi sekolah dan kampus yang sudah terdaftar dalam sistem magang</p>
                    </div>
                    <div class="text-xs text-gray-500 font-medium">
                        Total: <span class="font-bold text-gray-800">{{ $schoolList->count() }}</span> Institusi
                    </div>
                </div>

                <!-- Search Field (Mendukung Instant Client-Side & Server-Side Fallback) -->
                <div class="mb-5 sm:mb-6">
                    <form method="GET" action="{{ route('admin.school.view') }}" id="searchSchoolForm">
                        <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden bg-white focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 shadow-2xs transition">
                            <div class="p-2.5 sm:p-3 text-gray-400">
                                <i class="ml-2 fa-solid fa-search text-sm"></i>
                            </div>

                            <input type="text" id="searchInput" name="search" value="{{ request('search') }}"
                                class="w-full px-3 sm:px-4 py-2 sm:py-3 pl-2 sm:pl-3 text-gray-800 focus:outline-none text-xs sm:text-base"
                                placeholder="Cari nama sekolah atau universitas..."
                                autocomplete="off">

                            @if (request('search'))
                                <a href="{{ route('admin.school.view') }}"
                                    class="px-3.5 py-2 text-xs font-semibold text-gray-500 hover:text-gray-800 transition whitespace-nowrap">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6" id="schoolListContainer">
                    @forelse ($schoolList as $item)
                        <a href="{{ route('admin.school.team.view', ['schoolId' => $item->id]) }}"
                            data-school-name="{{ strtolower($item->name) }}"
                            class="division-item bg-gray-700 text-white rounded-xl overflow-hidden shadow hover:shadow-lg flex flex-col justify-between h-full transition duration-150">
                            <div class="p-3 sm:p-4 flex-grow">
                                <div class="text-xs sm:text-lg font-semibold text-center line-clamp-2 school-title">{{ $item->name }}</div>
                                <div class="flex items-center justify-between mt-4 sm:mt-8">
                                    <i class="fa-solid fa-users text-2xl sm:text-4xl text-gray-300"></i>
                                    <span class="ml-auto text-right text-lg sm:text-2xl font-bold">{{ $item->intern_total }}</span>
                                </div>
                            </div>
                            <div class="bg-white text-gray-800 px-3 sm:px-4 py-2 flex justify-between items-center">
                                <span class="text-xs sm:text-sm font-medium">View Detail</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full py-12 text-center">
                            <div class="max-w-xs mx-auto text-center">
                                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3 text-gray-400">
                                    <i class="fa-solid fa-school-flag text-xl"></i>
                                </div>
                                <h3 class="text-sm font-bold text-gray-900">Tidak ada sekolah ditemukan</h3>
                                <p class="text-xs text-gray-500 mt-1">Coba sesuaikan kata kunci pencarian Anda.</p>
                                @if (request('search'))
                                    <a href="{{ route('admin.school.view') }}"
                                        class="inline-block mt-3 px-3.5 py-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                                        Tampilkan Semua Sekolah
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforelse

                    <!-- Client-side No Results Placeholder -->
                    <div id="noSchoolFoundClient" class="hidden col-span-full py-12 text-center">
                        <div class="max-w-xs mx-auto text-center">
                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3 text-gray-400">
                                <i class="fa-solid fa-school-flag text-xl"></i>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900">Tidak ada sekolah yang cocok</h3>
                            <p class="text-xs text-gray-500 mt-1">Tidak ditemukan sekolah yang sesuai dengan pencarian tersebut.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const schoolCards = document.querySelectorAll('#schoolListContainer .division-item');
                const noResultEl = document.getElementById('noSchoolFoundClient');

                if (searchInput && schoolCards.length > 0) {
                    searchInput.addEventListener('input', function() {
                        const filter = this.value.toLowerCase().trim();
                        let visibleCount = 0;

                        schoolCards.forEach(function(card) {
                            const nameAttr = card.getAttribute('data-school-name') || '';
                            const titleEl = card.querySelector('.school-title');
                            const titleText = titleEl ? titleEl.textContent.toLowerCase() : '';
                            const fullCardText = card.textContent.toLowerCase();

                            const isMatch = filter === '' ||
                                            nameAttr.includes(filter) ||
                                            titleText.includes(filter) ||
                                            fullCardText.includes(filter);

                            if (isMatch) {
                                card.style.display = '';
                                card.classList.remove('hidden');
                                visibleCount++;
                            } else {
                                card.style.display = 'none';
                                card.classList.add('hidden');
                            }
                        });

                        if (noResultEl) {
                            if (visibleCount === 0 && filter !== '') {
                                noResultEl.classList.remove('hidden');
                                noResultEl.style.display = '';
                            } else {
                                noResultEl.classList.add('hidden');
                                noResultEl.style.display = 'none';
                            }
                        }
                    });
                }
            });
        </script>
        <script src="{{ asset('js/admin/schooll.js') }}"></script>

    </main>
@endsection
