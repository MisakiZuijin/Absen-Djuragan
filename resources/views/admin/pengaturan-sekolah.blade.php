@extends('layouts.main')

@section('title', 'Pengaturan Sekolah')

@section('contents')
    @include('layouts.sidebar')

    @include('layouts.sidebar-pengaturan')

    @include('layouts.navbar')

    <!-- Main Content -->
    <main class="ml-[32rem] mt-24 p-6">

        <!-- Header -->
        <h1 class="text-2xl font-bold mb-2">Manage Sekolah/Universitas</h1>
        <p class="mb-6 text-gray-600">Pengaturan untuk menambahkan sekolah/universitas</p>
        <div class="flex items-center mb-6">
            <!-- Add Sekolah Button -->
            <button id="addSchoolButton"
                class="flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg shadow-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                <i class="fas fa-plus mr-2"></i> Tambahkan Sekolah / Universitas
            </button>

            <!-- Search Input -->
            <div class="flex items-center ml-auto">
                <input type="text" id="searchInput" placeholder="Cari Sekolah"
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
                        <th class="py-3 px-6 text-left">Nama Sekolah / Universitas</th>
                        <th class="py-3 px-6 text-left">Alamat</th>
                        <th class="py-3 px-6 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody id="schoolTableBody">
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

    <!-- Modal Tambah Sekolah / Universitas -->
    <div id="addSchoolModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            <h2 class="text-xl font-bold mb-4">Tambahkan Sekolah / Universitas</h2>
            <form action="{{ route('schools.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="namaSekolah" class="block text-gray-700">Nama Sekolah / Universitas<span
                                class="text-red-500">*</span></label>
                    <input type="text" id="namaSekolah" name="namaSekolah"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="mb-4">
                    <label for="schoolType1" class="block text-gray-700">Tipe Sekolah / Universitas<span
                        class="text-red-500">*</span></label>
                    <select id="schoolType1" name="schoolType1"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                        <option value="" disabled selected>--Pilih tipe--</option>
                        @foreach ($educationalLevels as $level)
                            <option value="{{ $level->id }}">{{ $level->name }}</option>
                        @endforeach
                    </select>

                </div>
                <div class="mb-4">
                    <label for="alamatSekolah" class="block text-gray-700">Alamat<span
                        class="text-red-500">*</span></label>
                    <textarea id="alamatSekolah" name="alamatSekolah"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500" required></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="button" id="closeAddSchoolModal"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg mr-2">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Sekolah -->
    <div id="editSchoolModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            <h2 class="text-xl font-bold mb-4">Update Sekolah / Universitas</h2>
            <form id="editSchoolForm" action="" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="editSchoolId" name="schoolId">
                <div class="mb-4">
                    <label for="editNamaSekolah" class="block text-gray-700">Nama Sekolah / Universitas<span
                        class="text-red-500">*</span></label>
                    <input type="text" id="editNamaSekolah" name="namaSekolah"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="mb-4">
                    <label for="schoolType" class="block text-gray-700">Tipe Sekolah / Universitas<span
                        class="text-red-500">*</span></label>
                    <select id="schoolType" name="schoolType"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                        <option value="" disabled selected>--Pilih tipe--</option>
                        @foreach ($educationalLevels as $level)
                            <option value="{{ $level->id }}">{{ $level->name }}</option>
                        @endforeach
                    </select>

                </div>
                <div class="mb-4">
                    <label for="editAlamatSekolah" class="block text-gray-700">Alamat<span
                        class="text-red-500">*</span></label>
                    <textarea type="text" id="editAlamatSekolah" name="alamatSekolah"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500" required></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="button" id="closeEditSchoolModal"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg mr-2">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus Sekolah / Universitas -->
    <div id="deleteSchoolModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            <h2 class="text-xl font-bold mb-4">Hapus Sekolah / Universitas</h2>
            <p>Apakah Anda yakin ingin menghapus sekolah/universitas ini?</p>
            <form id="deleteSchoolForm" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" id="deleteSchoolId" name="schoolId">
                <div class="flex justify-end mt-4">
                    <button type="button" id="closeDeleteSchoolModal"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg mr-2">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const schoolList = @json($schoolList);
            const itemsPerPage = 5;
            let currentPage = 1;
            let filteredData = schoolList; 
            const totalPages = () => Math.ceil(filteredData.length /
                itemsPerPage); 

            function displayPage(page) {
                const start = (page - 1) * itemsPerPage;
                const end = start + itemsPerPage;
                const paginatedData = filteredData.slice(start, end);

                $('#schoolTableBody').html(''); 
                paginatedData.forEach((school, index) => {
                    $('#schoolTableBody').append(`
                <tr class="border-b border-gray-200">
                    <td class="py-4 px-6">${start + index + 1}</td>
                    <td class="py-4 px-6">${school.name}</td>
                    <td class="py-4 px-6">${school.address}</td>
                    <td class="py-4 px-6">
                        <button class="editSchoolModal px-4 py-2 bg-blue-500 text-white rounded-lg shadow-md hover:bg-blue-600" data-id="${school.id}" data-name="${school.name}" data-address="${school.address}" data-type="${school.educational_level_id}">Edit</button>
                        <button class="deleteSchool px-4 py-2 bg-red-500 text-white rounded-lg shadow-md hover:bg-red-600" data-id="${school.id}">Hapus</button>
                    </td>
                </tr>
            `);
                });
            }

            function updatePagination() {
                $('#page-numbers').empty(); // Clear page numbers

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
                filteredData = schoolList.filter(school =>
                    school.name.toLowerCase().includes(searchTerm) ||
                    school.address.toLowerCase().includes(searchTerm)
                );
                currentPage = 1;
                displayPage(currentPage);
                updatePagination();
            });

            // Initial display
            displayPage(currentPage);
            updatePagination();

            $(document).on('click', '.editSchoolModal', function() {
                var schoolId = $(this).data('id');
                var namaSekolah = $(this).data('name');
                var alamatSekolah = $(this).data('address');
                var schoolType = $(this).data('type');

                // Set values in the form
                $('#editSchoolId').val(schoolId);
                $('#editNamaSekolah').val(namaSekolah);
                $('#editAlamatSekolah').val(alamatSekolah);
                $('#schoolType').val(schoolType);

                // Update form action URL
                var actionUrl = '{{ route('schools.update', ':id') }}';
                actionUrl = actionUrl.replace(':id', schoolId);
                $('#editSchoolForm').attr('action', actionUrl);

                // Show the edit modal
                $('#editSchoolModal').removeClass('hidden');
            });

            // Close edit modal
            $('#closeEditSchoolModal').on('click', function() {
                $('#editSchoolModal').addClass('hidden');
            });

            $(document).on('click', '.deleteSchool', function() {
                var schoolId = $(this).data('id');

                var actionUrl = '{{ route('schools.delete', ':id') }}';
                actionUrl = actionUrl.replace(':id', schoolId);
                $('#deleteSchoolForm').attr('action', actionUrl);

                $('#deleteSchoolModal').removeClass('hidden');
            });

            $('#closeDeleteSchoolModal').on('click', function() {
                $('#deleteSchoolModal').addClass('hidden');
            });

            $(window).on('click', function(event) {
                if ($(event.target).is('#addSchoolModal')) {
                    $('#addSchoolModal').addClass('hidden');
                }
                if ($(event.target).is('#editSchoolModal')) {
                    $('#editSchoolModal').addClass('hidden');
                }
                if ($(event.target).is('#deleteSchoolModal')) {
                    $('#deleteSchoolModal').addClass('hidden');
                }
            });

            $('#addSchoolButton').on('click', function() {
                $('#addSchoolModal').removeClass('hidden');
            });

            $('#closeAddSchoolModal').on('click', function() {
                $('#addSchoolModal').addClass('hidden');
            });
        });

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
