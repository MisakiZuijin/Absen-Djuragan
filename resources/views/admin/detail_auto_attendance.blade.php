@extends('layouts.main')

@section('title', 'Presensi Otomatis')

@section('contents')
    <!-- Main Content -->
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">
        <!-- Header -->
        <h1 class="text-xl sm:text-2xl font-bold mb-1 sm:mb-2">Presensi Otomatis {{ $id }}</h1>
        <p class="text-xs sm:text-sm text-gray-600">Daftar data presensi otomatis {{ $id }} selama magang tertandai tidak melakukan
            presensi pulang pada jam yang di tentukan</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-6 my-4 sm:my-6">
            <div class="bg-white p-4 sm:p-6 rounded-xl shadow-xs border border-gray-100">
                <h2 class="text-xs sm:text-sm font-semibold text-gray-600 mb-1">Total Tidak Presensi Pulang</h2>
                <p id="total_auto_end_all" class="text-gray-800 text-2xl sm:text-3xl font-bold">{{ $count['total_all'] }}</p>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-xl shadow-xs border border-gray-100">
                <h2 class="text-xs sm:text-sm font-semibold text-gray-600 mb-1">Tidak Presensi Pulang bulan ini</h2>
                <p class="text-gray-800 text-2xl sm:text-3xl font-bold">{{ $count['total_in_month'] }}</p>
            </div>
            <div class="bg-white p-4 sm:p-6 rounded-xl shadow-xs border border-gray-100">
                <h2 class="text-xs sm:text-sm font-semibold text-gray-600 mb-1">Tidak Presensi Pulang Minggu ini</h2>
                <p class="text-gray-800 text-2xl sm:text-3xl font-bold">{{ $count['total_in_week'] }}</p>
            </div>
        </div>

        <div class="mb-4 flex items-center justify-end">
            <!-- Date Field -->
            <div class="flex items-center border border-gray-800 rounded-md w-full sm:w-auto">
                <div class="bg-white p-2 rounded-l-md">
                    <i class="fas fa-search text-gray-500"></i>
                </div>

                <input type="date" id="dateInput"
                    class="p-2 pl-2 w-full text-left text-gray-800 rounded-r-md focus:outline-none focus:border-blue-500 text-xs sm:text-sm">
            </div>
        </div>

        <div class="overflow-x-auto">
            @livewire('auto-attendance-list-component', ['autoAttdData' => $auto_attd_data['data'], 'meta' => $auto_attd_data['meta']])
        </div>

        <!-- Pagination Controls -->
        <div class="mt-4 flex flex-wrap justify-center items-center gap-2 border rounded-md p-2">
            <button id="prev-page"
                class="cursor-pointer bg-white text-blue-600 px-4 py-2 rounded-md border hover:bg-gray-100 text-xs sm:text-sm" disabled>
                Previous
            </button>

            <div id="page-numbers" class="flex space-x-2 text-xs sm:text-sm"></div>

            <button id="next-page" class="bg-white text-blue-600 px-4 py-2 rounded-md border hover:bg-gray-100 text-xs sm:text-sm">
                Next
            </button>
        </div>

        <script>
            const dateInput = document.getElementById('dateInput');

            let debounceTimeout;

            // Fungsi untuk mengirim data pencarian
            function dispatchSearch(currentPage = 1) {
                const dateValue = dateInput.value;

                // Kondisi dinamis untuk pengiriman data
                const payload = {};
                payload.searchTerm = "{{ $id }}";
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

            dateInput.addEventListener('input', handleInputEvent);

            // Fungsi untuk memperbarui pagination
            function updatePagination(currentPage, totalPages, totalData) {
                const prevButton = $('#prev-page');
                const nextButton = $('#next-page');
                // const totalDataLabel = $('#total_auto_end_all');
                // totalDataLabel.text(totalData);
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
