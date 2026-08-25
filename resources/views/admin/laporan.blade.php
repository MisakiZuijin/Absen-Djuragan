@extends('layouts.main')

@section('title', 'Laporan')

@section('contents')
    @include('layouts.sidebar')
        <!-- Main Content -->
        @include('components.admin-raise-hand-notification')

    @include('layouts.navbar')

    <!-- Main Content -->
    <main class="ml-64 mt-24 p-6 md:ml-48 lg:ml-64">
        <div class="bg-gray-700 text-white p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="flex flex-col space-y-4">
                    <div class="text-4xl font-bold">Laporan Data Presensi</div>
                    <div class="text-lg" id="date-range-display">Data per tanggal {{ $dateNow ?? '' }}</div>
                </div>

                <!-- Right Column -->
                <div class="flex flex-col space-y-2">
                    <label for="search-student" class="text-lg font-medium">Cari Mahasiswa</label>
                    <div class="flex items-center border border-gray-300 rounded">
                        <div class="bg-white p-2 rounded-l">
                            <i class="ml-2 fa-solid fa-search text-gray-500"></i>
                        </div>

                        <input type="text" id="search-student"
                            class="p-2 pl-3 pr-3 rounded-r text-gray-800 border-gray-300 focus:outline-none focus:border-blue-500 w-full"
                            placeholder="Masukkan nama mahasiswa">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white grid grid-cols-1 lg:grid-cols-2 gap-6 w-full">
            <!-- Right Side: Date Input dan Filter -->
            <div class="px-6 py-4 rounded-lg flex items-center gap-4 lg:justify-end lg:col-start-2 w-full">
                <!-- Date Inputs for Range -->
                <div class="flex items-center gap-4">
                    <input type="date"
                        class="p-3 w-full border border-gray-800 rounded-md focus:outline-none focus:border-blue-500"
                        id="start-date" placeholder="Start Date">
                    <span class="text-gray-700">s/d</span>
                    <input type="date"
                        class="p-3 w-full border border-gray-800 rounded-md focus:outline-none focus:border-blue-500"
                        id="end-date" placeholder="End Date">
                </div>


                <!-- Search Button -->
                <button id="search-button"
                    class="bg-gray-800 text-white p-3 rounded-md hover:bg-gray-600 flex items-center gap-2">
                    <i class="fas fa-search"></i>
                    Search
                </button>
            </div>
        </div>

        <table class="min-w-full bg-white shadow-md rounded-lg mt-2">
            <thead>
                <tr class="bg-gray-200 text-gray-700 text-sm uppercase leading-normal text-center">
                    <th rowspan="2" class="px-4 py-2 border-r">No</th>
                    <th rowspan="2" class="px-4 py-2 border-r">Nama</th>
                    <th rowspan="2" class="px-4 py-2 border-r">NIP</th>
                    <th rowspan="2" class="px-4 py-2 border-r">Total Kehadiran</th>
                    <th rowspan="2" class="px-4 py-2 border-r">Total Izin</th>
                    <th rowspan="2" class="px-4 py-2 border-r">Total Ketidakhadiran</th>
                </tr>
            </thead>

            <tbody class="text-sm font-normal text-gray-800" id="report-tbody">

            </tbody>
        </table>
        <div id="loading-spinner" class="flex justify-center items-center py-4 hidden">
            <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-10 w-10"></div>
        </div>

        <div id="no-data-message" class="text-center py-4 text-gray-600 hidden">
            No data available
        </div>


        <!-- Pagination Controls -->
        <div class="mt-4 flex justify-center items-center space-x-2 border rounded-md p-2">
            <button id="prev-page"
                class="cursor-pointer bg-white text-blue-600 px-4 py-2 rounded-md border hover:bg-gray-100" disabled>
                Previous
            </button>

            <!-- Page numbers will be dynamically added here -->
            <div id="page-numbers" class="flex space-x-2"></div>

            <button id="next-page" class="bg-white text-blue-600 px-4 py-2 rounded-md border hover:bg-gray-100">
                Next
            </button>
        </div>

        <div class="mt-4 flex justify-end">
            <button id="download-pdf"
                class="px-3 py-2 text-sm font-medium text-center text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                <i class="fa-solid fa-download"></i>
                <span>Download PDF</span>
            </button>
        </div>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentPage = 1;
            const perPage = 15;
            const pageInfo = document.querySelector('#page-info');
            const prevPageButton = document.querySelector('#prev-page');
            const nextPageButton = document.querySelector('#next-page');
            const pageNumbersContainer = document.querySelector('#page-numbers');
            const tbody = document.querySelector('#report-tbody');
            const downloadButton = document.querySelector('#download-pdf');
            let totalPages = 1;

            const fetchData = (page, startDate = '', endDate = '', internName = null, filter = '') => {
                const params = {
                    page: page,
                    perPage: perPage,
                    startDate: startDate,
                    endDate: endDate
                };
                if (internName != null) {
                    params.internName = internName;
                }

                axios.get('/api/report-attendance', {
                        params
                    })
                    .then(response => {
                        totalPages = response.data.totalPages;
                        currentPage = response.data.currentPage;
                        tbody.innerHTML = '';

                        response.data.reportData.forEach((item, key) => {
                            const row = document.createElement('tr');
                            row.className = 'border-b border-gray-300 text-center';

                            const rowNumber = (currentPage - 1) * perPage + (key + 1);

                            row.innerHTML = `
                        <td class="px-4 py-2">${rowNumber}</td>
                        <td class="px-4 py-2 text-left hover:underline cursor-pointer text-blue-600 hover:text-blue-800">
                            <a href="/admin/presence/detail/${item.id}">${item.name}</a>
                        </td>
                        <td class="px-4 py-2">${item.nip}</td>
                        <td class="px-4 py-2">
                            <a href="#" class="inline-block ml-2">${item.submitted}
                            </a>
                        </td>
                        <td class="px-4 py-2 text-yellow-600">
                            <a href="#" class="inline-block ml-2">${item.permits}
                            </a>
                        </td>
                        <td class="px-4 py-2 text-red-600">
                            <a href="#" class="inline-block ml-2">${item.absence}
                            </a>
                        </td>
                    `;
                            tbody.appendChild(row);
                            updatePaginationButtons();
                            renderPageNumbers();
                        });

                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            };

            const updatePaginationButtons = () => {
                prevPageButton.disabled = currentPage <= 1;
                nextPageButton.disabled = currentPage >= totalPages;
            };

            const renderPageNumbers = () => {
                pageNumbersContainer.innerHTML = '';

                for (let i = 1; i <= totalPages; i++) {
                    const pageButton = document.createElement('button');
                    pageButton.textContent = i;
                    pageButton.className = 'cursor-pointer px-4 py-2 rounded-md border';

                    if (i === currentPage) {
                        pageButton.classList.add('bg-blue-600', 'text-white');
                    } else {
                        pageButton.classList.add('text-blue-600', 'bg-white', 'hover:bg-gray-100');
                    }

                    pageButton.addEventListener('click', () => {
                        if (i !== currentPage) {
                            currentPage = i;
                            fetchData(currentPage);
                        }
                    });

                    pageNumbersContainer.appendChild(pageButton);
                }
            };

            fetchData(currentPage);

            prevPageButton.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    fetchData(currentPage);
                }
            });

            nextPageButton.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    fetchData(currentPage);
                }
            });

            document.querySelector('#search-student').addEventListener('input', function() {
                const query = this.value;
                fetchData(1, query);
            });

            document.querySelector('#search-button').addEventListener('click', () => {
                const startDate = document.querySelector('#start-date').value;
                const endDate = document.querySelector('#end-date').value;
                const query = document.querySelector('#search-student').value;
                fetchData(1, startDate, endDate, query);
            });

        });

        document.addEventListener('DOMContentLoaded', () => {
        const startDateInput = document.getElementById('start-date');
        const endDateInput = document.getElementById('end-date');
        const dateRangeDisplay = document.getElementById('date-range-display');

        const updateDateRange = () => {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;

            if (startDate && endDate) {
                const [startYear, startMonth, startDay] = startDate.split('-');
                const [endYear, endMonth, endDay] = endDate.split('-');

                const formattedStartDate = `${startDay}-${startMonth}-${startYear}`;
                const formattedEndDate = `${endDay}-${endMonth}-${endYear}`;

                dateRangeDisplay.textContent = `Data per tanggal ${formattedStartDate} s/d ${formattedEndDate}`;
            } else {
                dateRangeDisplay.textContent = `Data per tanggal {{ $dateNow ?? '' }}`;
            }
        };

        startDateInput.addEventListener('change', updateDateRange);
        endDateInput.addEventListener('change', updateDateRange);
    });

    document.getElementById('download-pdf').addEventListener('click', function () {
        const searchStudent = document.getElementById('search-student').value;
        let startDate = document.getElementById('start-date').value;
        let endDate = document.getElementById('end-date').value;

        const today = new Date().toISOString().split('T')[0];
        if (!startDate) startDate = today;
        if (!endDate) endDate = today;

        const url = `./report/download?search=${encodeURIComponent(searchStudent)}&start_date=${startDate}&end_date=${endDate}`;

        window.location.href = url;
    });
    </script>
@endsection
