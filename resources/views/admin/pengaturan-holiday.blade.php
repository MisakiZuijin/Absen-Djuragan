@extends('layouts.main')

@section('title', 'Pengaturan Hari Libur')

@section('contents')
    @include('layouts.sidebar')

    @include('layouts.sidebar-pengaturan')

    @include('layouts.navbar')

    <!-- Main Content -->
    <main class="ml-[32rem] mt-24 p-6">

        <!-- Header -->
        <h1 class="text-2xl font-bold mb-2">Manage Hari Libur</h1>
        <p class="mb-6 text-gray-600">Pengaturan untuk menambahkan hari libur</p>
        <div class="flex items-center mb-6">
            <!-- Add Sekolah Button -->
            <button id="addHolidayButton"
                class="flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg shadow-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                <i class="fas fa-plus mr-2"></i> Tambahkan Hari Libur
            </button>

            <!-- Search Input -->
            <div class="flex items-center ml-auto">
                <input type="text" id="searchInput" placeholder="Cari Hari Libur"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500">
            </div>
        </div>

        @if (session('success'))
            <div id="success-message"
                class="mb-2 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative transition-opacity duration-500"
                role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="removeMessage()">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20">
                        <title>Close</title>
                        <path
                            d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                    </svg>
                </span>
            </div>
        @endif

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-gray-200 text-gray-700">
                        <th class="py-3 px-6 text-left">No</th>
                        <th class="py-3 px-6 text-left">Tanggal</th>
                        <th class="py-3 px-6 text-left">Nama Hari Libur</th>
                        <th class="py-3 px-6 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody id="holidayTableBody">
                    <!-- Rows will be dynamically inserted here -->
                </tbody>
            </table>
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
    </main>

    <!-- Modal Tambah Hari Libur -->
    <div id="addHolidayModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            <h2 class="text-xl font-bold mb-4">Tambahkan Hari Libur</h2>
            <form action="{{ route('holidays.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="holidayDate" class="block text-gray-700">Tanggal<span class="text-red-500">*</span></label>
                    <input type="date" id="holidayDate" name="date"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="mb-4">
                    <label for="holidayName" class="block text-gray-700">Nama Hari Libur<span
                            class="text-red-500">*</span></label>
                    <input type="text" id="holidayName" name="name"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="flex justify-end">
                    <button type="button" id="closeAddHolidayModal"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg mr-2">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Hari Libur -->
    <div id="editHolidayModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            <h2 class="text-xl font-bold mb-4">Update Hari Libur</h2>
            <form id="editHolidayForm" action="" method="POST">
                @csrf
                {{-- @method('PUT') --}}
                <input type="hidden" id="editHolidayId" name="holidayId">
                <div class="mb-4">
                    <label for="editHolidayDate" class="block text-gray-700">Tanggal<span
                            class="text-red-500">*</span></label>
                    <input type="date" id="editHolidayDate" name="date"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="mb-4">
                    <label for="editHolidayName" class="block text-gray-700">Nama Hari Libur<span
                            class="text-red-500">*</span></label>
                    <input type="text" id="editHolidayName" name="name"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="flex justify-end">
                    <button type="button" id="closeEditHolidayModal"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg mr-2">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteHolidayModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            <h2 class="text-xl font-bold mb-4">Hapus Hari Libur</h2>
            <p>Apakah Anda yakin ingin menghapus hari libur ini?</p>
            <form id="deleteHolidayForm" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" id="deleteHolidayId" name="holidayId">
                <div class="flex justify-end mt-4">
                    <button type="button" id="closeDeleteHolidayModal"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg mr-2">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Assuming `HolidayList` is passed as a JavaScript array from backend for demonstration
            const HolidayList = @json($holidaylist);
            const itemsPerPage = 5;
            let currentPage = 1;
            let filteredData = HolidayList; // Array to hold filtered results

            const totalPages = () => Math.ceil(filteredData.length / itemsPerPage);

            function displayPage(page) {
                const start = (page - 1) * itemsPerPage;
                const end = start + itemsPerPage;
                const paginatedData = filteredData.slice(start, end);

                $('#holidayTableBody').html(''); // Clear table body
                paginatedData.forEach((holiday, index) => {

                    $('#holidayTableBody').append(`
                        <tr class="border-b border-gray-200">
                            <td class="py-4 px-6">${start + index + 1}</td>
                            <td class="py-4 px-6">${holiday.date}</td>
                            <td class="py-4 px-6">${holiday.name}</td>
                            <td class="py-4 px-6">
                                <button class="editHolidayModal px-4 py-2 bg-blue-500 text-white rounded-lg shadow-md hover:bg-blue-600" data-id="${holiday.id}" data-name="${holiday.name}" data-date="${holiday.date}">Edit</button>
                                <button class="deleteHoliday px-4 py-2 bg-red-500 text-white rounded-lg shadow-md hover:bg-red-600" data-id="${holiday.id}">Hapus</button>
                            </td>
                        </tr>
                    `);
                });
            }

            function updatePagination() {
                $('#page-numbers').empty();

                for (let i = 1; i <= totalPages(); i++) {
                    const isActive = i === currentPage ? 'bg-blue-500 text-white' : 'bg-white text-blue-600';
                    const pageButton = $(`
                        <button class="page-number ${isActive} px-4 py-2 rounded-md border hover:bg-gray-100" data-page="${i}">${i}</button>
                    `);

                    pageButton.on('click', function() {
                        currentPage = i;
                        displayPage(currentPage);
                        updatePagination();
                    });

                    $('#page-numbers').append(pageButton);
                }

                $('#prev-page').prop('disabled', currentPage === 1);
                $('#next-page').prop('disabled', currentPage === totalPages());
            }

            $('#prev-page').on('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    displayPage(currentPage);
                    updatePagination();
                }
            });

            $('#next-page').on('click', function() {
                if (currentPage < totalPages()) {
                    currentPage++;
                    displayPage(currentPage);
                    updatePagination();
                }
            });

            // Search functionality
            $('#searchInput').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                filteredData = HolidayList.filter(holiday =>
                    holiday.name.toLowerCase().includes(searchTerm) ||
                    holiday.date.toLowerCase().includes(searchTerm)
                );
                currentPage = 1;
                displayPage(currentPage);
                updatePagination();
            });

            // Initial display
            displayPage(currentPage);
            updatePagination();

            $(document).on('click', '.editHolidayModal', function() {
                const holidayId = $(this).data('id');
                const holidayName = $(this).data('name');
                let holidayDate = $(this).data('date'); 

                if (holidayDate) {
                    const parts = holidayDate.split('-'); 
                    holidayDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
                }

                // Set values in the form
                $('#editHolidayId').val(holidayId);
                $('#editHolidayName').val(holidayName);
                $('#editHolidayDate').val(holidayDate);

                console.log('Holiday Date:', holidayDate);

                // Update form action URL
                let actionUrl = '/admin/setting/update-holiday/:id';
                actionUrl = actionUrl.replace(':id', holidayId);
                $('#editHolidayForm').attr('action', actionUrl);

                // Show the edit modal
                $('#editHolidayModal').removeClass('hidden');
            });

            // Close edit modal
            $('#closeEditHolidayModal').on('click', function() {
                $('#editHolidayModal').addClass('hidden');
            });

            $(document).on('click', '.deleteHoliday', function() {
                const holidayId = $(this).data('id');

                let actionUrl = '/admin/setting/delete-holiday/:id';
                actionUrl = actionUrl.replace(':id', holidayId);
                $('#deleteHolidayForm').attr('action', actionUrl);

                $('#deleteHolidayModal').removeClass('hidden');
            });

            $('#closeDeleteHolidayModal').on('click', function() {
                $('#deleteHolidayModal').addClass('hidden');
            });

            $(window).on('click', function(event) {
                if ($(event.target).is('#addHolidayModal')) {
                    $('#addHolidayModal').addClass('hidden');
                }
                if ($(event.target).is('#editHolidayModal')) {
                    $('#editHolidayModal').addClass('hidden');
                }
                if ($(event.target).is('#deleteHolidayModal')) {
                    $('#deleteHolidayModal').addClass('hidden');
                }
            });

            // Modal for adding a new holiday
            $('#addHolidayButton').on('click', function() {
                $('#addHolidayModal').removeClass('hidden');
            });

            $('#closeAddHolidayModal').on('click', function() {
                $('#addHolidayModal').addClass('hidden');
            });

        });

        // Display success message temporarily
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
