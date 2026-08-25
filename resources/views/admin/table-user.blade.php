@extends('layouts.main')

@section('title', 'Presensi Otomatis')

@section('contents')
    @include('layouts.sidebar')

    @include('layouts.navbar')

    <!-- Main Content -->
    <main class="ml-64 mt-24 p-6 md:ml-48 lg:ml-64">
        <!-- Header -->
        <h1 class="text-2xl font-bold mb-2">Presensi Otomatis</h1>
        <p class=" text-gray-600">Berikut adalah daftar pemagang yang ditandai tidak melakukan presensi pulang pada jam
            yang ditentukan.</p>



        <div class="mb-4 flex items-center justify-between mt-4">
            <!-- Left: Total Masuk -->
            <div class="flex justify-start items-center p-2 border  rounded-md w-auto">
                <span>Total pulang Otomatis Hari ini </span>
                <span id="total_presence"
                    class="px-3 py-2 text-xs font-medium text-center text-white bg-green-700 rounded-lg ml-2">{{ $total_auto_end_today }}</span>
            </div>

            <!-- Right: Search and Date Fields -->
            <div class="flex items-center space-x-4">
                <!-- Search Field -->
                <div class="flex items-center border border-gray-800 rounded-md">
                    <div class="bg-white p-2 rounded-l-md">
                        <i class="fas fa-search text-gray-500"></i>
                    </div>
                    <input type="text" id="searchInput"
                        class="p-2 pl-2 w-full text-left text-gray-800 rounded-r-md focus:outline-none focus:border-blue-500"
                        placeholder="Cari Nama">
                </div>

                <!-- Date Field -->
                <div class="flex items-center border border-gray-800 rounded-md">
                    <div class="bg-white p-2 rounded-l-md">
                        <i class="fas fa-search text-gray-500"></i>
                    </div>
                    <input type="date" id="dateInput"
                        class="p-2 pl-2 w-full text-left text-gray-800 rounded-r-md focus:outline-none focus:border-blue-500">
                </div>
            </div>
        </div>




        <div class="overflow-x-auto">
            @livewire('auto-attendance-list-component', ['autoAttdData' => $auto_attd_data['data'], 'meta' => $auto_attd_data['meta']])
        </div>

        <!-- Pagination Controls -->
        <div class="mt-4 flex justify-center items-center space-x-2 border rounded-md p-2">
            <button id="prev-page"
                class="cursor-pointer bg-white text-blue-600 px-4 py-2 rounded-md border hover:bg-gray-100" disabled>
                Previous
            </button>

            <div id="page-numbers" class="flex space-x-2"></div>

            <button id="next-page" class="bg-white text-blue-600 px-4 py-2 rounded-md border hover:bg-gray-100">
                Next
            </button>
        </div>
        <script>
            const searchInput = document.getElementById('searchInput');
            const dateInput = document.getElementById('dateInput');
            let totalLoad = 0;

            let debounceTimeout;

            // Fungsi untuk mengirim data pencarian
            function dispatchSearch(currentPage = 1) {
                const searchTerm = searchInput.value.toLowerCase();
                const dateValue = dateInput.value;

                // Kondisi dinamis untuk pengiriman data
                const payload = {};
                if (searchTerm) payload.searchTerm = searchTerm;
                if (dateValue) payload.dateValue = dateValue;
                payload.currentPage = currentPage;

                Livewire.dispatch('searchAutoAttd', payload);
            }

            // Debounce handler untuk input pencarian
            function handleInputEvent() {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(() => {
                    dispatchSearch();
                }, 500);
            }

            // Event listener untuk input pencarian
            searchInput.addEventListener('input', handleInputEvent);
            dateInput.addEventListener('input', handleInputEvent);

            // Fungsi untuk memperbarui pagination
            function updatePagination(currentPage, totalPages, totalData) {
                const prevButton = $('#prev-page');
                const nextButton = $('#next-page');
                const totalPresence = $('#total_presence');
                if (searchInput.value.trim() === '' && dateInput.value.trim() === '') {
                    totalPresence.text("{{ $total_auto_end_today }}");
                } else {
                    totalPresence.text(totalData);
                }
                // totalLoad++;


                const pageNumbersContainer = $('#page-numbers');

                prevButton.prop('disabled', currentPage <= 1);
                nextButton.prop('disabled', currentPage >= totalPages);

                pageNumbersContainer.empty();

                for (let i = 1; i <= totalPages; i++) {
                    const pageNumber = $('<button></button>')
                        .text(i)
                        .addClass('cursor-pointer px-4 py-2 rounded-md border')
                        .toggleClass('bg-blue-600 text-white', i === currentPage)
                        .toggleClass('text-blue-600 bg-white hover:bg-gray-100', i !== currentPage)
                        .on('click', function() {

                            dispatchSearch(i);


                        });

                    pageNumbersContainer.append(pageNumber);
                }
            }

            // Event listener untuk update pagination
            document.addEventListener('DOMContentLoaded', function() {
                Livewire.on('updatePaginate', ({
                    meta
                }) => {

                    updatePagination(meta.current_page, meta.total_page, meta.total_data);
                });
            });
        </script>

    </main>
@endsection
