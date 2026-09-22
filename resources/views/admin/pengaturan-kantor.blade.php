@extends('layouts.main')

@section('title', 'Pengaturan Kantor')

@section('contents')
    @include('layouts.sidebar')

    @include('layouts.sidebar-pengaturan')

    @include('layouts.navbar')

    <main class="ml-[32rem] mt-24 p-6">

        <h1 class="text-2xl font-bold mb-2">Manage Kantor</h1>
        <p class="mb-6 text-gray-600">Pengaturan untuk manage kantor</p>

        <div class="flex items-center mb-6">
            <!-- Add Kantor Button -->
            <a href="{{ route('office.maps.view') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg shadow-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                <i class="fas fa-plus mr-2"></i> Tambahkan Kantor
            </a>

            <!-- Search Input -->
            <div class="flex items-center ml-auto">
                <input type="text" id="search-office" placeholder="Cari Kantor"
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
                        <th class="py-3 px-6 text-left">Nama Lokasi</th>
                        <th class="py-3 px-6 text-left">Alamat</th>
                        <th class="py-3 px-6 text-left">Kapasitas</th>
                        <th class="py-3 px-6 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody id="office-table-body">
                </tbody>
            </table>
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

    </main>

    <!-- Modal Hapus Kantor -->
    <div id="deleteOfficeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            <h2 class="text-xl font-bold mb-4">Hapus Kantor</h2>
            <p>Apakah Anda yakin ingin menghapus kantor ini?</p>
            <form id="deleteOfficeForm" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" id="deleteOfficeId" name="officeId">
                <div class="flex justify-end mt-4">
                    <button type="button" id="closeDeleteOfficeModal"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg mr-2">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script id="office-data" type="application/json">@json($office)</script>
    <script>
        $(document).ready(function() {
            let offices = JSON.parse(document.getElementById('office-data')?.textContent || '[]'); 
            let filteredOffices = offices; 
            let itemsPerPage = 5;
            let currentPage = 1;
            let totalPages = Math.ceil(filteredOffices.length / itemsPerPage);
    
            // Display the current page
            function displayPage(page) {
                const start = (page - 1) * itemsPerPage;
                const end = page * itemsPerPage;
                $('#office-table-body').empty();
    
                // Display only the items for the current page
                filteredOffices.slice(start, end).forEach((office, index) => {
                    $('#office-table-body').append(`
                        <tr class="border-b border-gray-200">
                            <td class="py-4 px-6">${start + index + 1}</td>
                            <td class="py-4 px-6">${office.name}</td>
                            <td class="py-4 px-6">${office.address}</td>
                            <td class="py-4 px-6">${office.capacity}</td>
                            <td class="py-4 px-6">
                                <a href="office/edit/${office.id}"
                                    class="editOffice px-4 py-2 bg-blue-500 text-white rounded-lg shadow-md hover:bg-blue-600 mr-2">
                                    Edit
                                </a>
                                <button
                                    class="deleteOffice px-4 py-2 bg-red-500 text-white rounded-lg shadow-md hover:bg-red-600"
                                    data-id="${office.id}">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }
    
            // Update pagination controls
            function updatePagination() {
                $('#page-numbers').empty();
                totalPages = Math.ceil(filteredOffices.length / itemsPerPage);
                for (let i = 1; i <= totalPages; i++) {
                    const isActive = i === currentPage ? 'bg-blue-500 text-white' : 'bg-white text-blue-600';
                    const pageButton = $(`
                        <button class="page-number ${isActive} px-4 py-2 rounded-md border">${i}</button>
                    `);
    
                    // Page click event
                    pageButton.on('click', () => {
                        currentPage = i;
                        displayPage(currentPage);
                        updatePagination();
                    });
    
                    $('#page-numbers').append(pageButton);
                }
                $('#prev-page').prop('disabled', currentPage === 1);
                $('#next-page').prop('disabled', currentPage === totalPages);
            }
    
            // Filter function
            function filterOffices() {
                const searchTerm = $('#search-office').val().toLowerCase();
                filteredOffices = offices.filter(office =>
                    office.name.toLowerCase().includes(searchTerm) ||
                    office.address.toLowerCase().includes(searchTerm)
                );
                currentPage = 1;
                displayPage(currentPage);
                updatePagination();
            }
    
            // Search input event
            $('#search-office').on('input', filterOffices);
    
            // Initialize the page
            displayPage(currentPage);
            updatePagination();
    
            $('#office-table-body').on('click', '.deleteOffice', function() {
                var officeId = $(this).data('id');
                $('#deleteOfficeId').val(officeId);
    
                var actionUrl = `delete-office/${officeId}`;
                $('#deleteOfficeForm').attr('action', actionUrl);
    
                $('#deleteOfficeModal').removeClass('hidden');
            });
    
            // Close modal when clicking the close button
            $('#closeDeleteOfficeModal').on('click', function() {
                $('#deleteOfficeModal').addClass('hidden');
            });
    
            // Close modal when clicking outside of it
            $(window).on('click', function(event) {
                if ($(event.target).is('#deleteOfficeModal')) {
                    $('#deleteOfficeModal').addClass('hidden');
                }
            });

        });

        // Success message fade-out
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
