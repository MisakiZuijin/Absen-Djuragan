@extends('layouts.main')

@section('title', 'Pengaturan Divisi')

@section('contents')
    @include('layouts.sidebar')

    @include('layouts.sidebar-pengaturan')

    @include('layouts.navbar')

    <!-- Main Content -->
    <main class="ml-[32rem] mt-24 p-6">

        <!-- Header -->
        <h1 class="text-2xl font-bold mb-2">Manage Divisi</h1>
        <p class="mb-6 text-gray-600">Membuat Divisi untuk anak magang</p>

        <div class="flex items-center mb-6">
            <!-- Add Shift Button -->
            <button id="addDivisiButton"
                class="flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg shadow-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                <i class="fas fa-plus mr-2"></i> Tambahkan Divisi
            </button>

            <!-- Search Input -->
            <div class="flex items-center ml-auto">
                <input type="text" id="searchInput" placeholder="Cari Divisi"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500">
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

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
                        <th class="py-3 px-6 text-left">Icon</th>
                        <th class="py-3 px-6 text-left">Nama Divisi</th>
                        <th class="py-3 px-6 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody id="division-table-body">
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

    <!-- Modal Tambah Divisi -->
    <div id="addDivisiModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            <h2 class="text-xl font-bold mb-4">Tambahkan Divisi</h2>
            <form id="addDivisiForm" action="{{ route('divisions.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="namaDivisi" class="block text-gray-700">Nama Divisi<span
                        class="text-red-500">*</span></label>
                    <input type="text" id="namaDivisi" name="namaDivisi"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="mb-4">
                    <label for="iconDivisi" class="block text-gray-700">Icon Divisi<span
                        class="text-red-500">*</span></label>
                    <input type="file" id="iconDivisi" name="iconDivisi"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        accept="image/*" required>
                </div>
                <div class="flex justify-end">
                    <button type="button" id="closeAddModal"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg mr-2">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Divisi -->
    <div id="editDivisiModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            <h2 class="text-xl font-bold mb-4">Update Divisi</h2>
            <form id="editDivisiForm" action="{{ route('divisions.update', ['id' => '$division->id']) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="editDivisiId" name="divisiId">
                <div class="mb-4">
                    <label for="editNamaDivisi" class="block text-gray-700">Nama Divisi<span
                        class="text-red-500">*</span></label>
                    <input type="text" id="editNamaDivisi" name="namaDivisi"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required>
                </div>
                <div class="mb-4">
                    <label for="editIconDivisi" class="block text-gray-700">Icon Divisi<span
                        class="text-red-500">*</span></label>
                    <input type="file" id="editIconDivisi" name="iconDivisi"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500" accept="image/*">
                </div>
                <div class="flex justify-end">
                    <button type="button" id="closeEditDivisiModal"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg mr-2">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus Divisi -->
    <div id="deleteDivisiModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
            <h2 class="text-xl font-bold mb-4">Hapus Divisi</h2>
            <p>Apakah Anda yakin ingin menghapus divisi ini?</p>
            <form id="deleteDivisiForm" action="" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" id="deleteDivisiId" name="divisiId">
                <div class="flex justify-end mt-4">
                    <button type="button" id="closeDeleteDivisiModal"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg mr-2">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const itemsPerPage = 5;
            let currentPage = 1;
            let divisions = @json($division);

            function renderTable(page, filteredDivisions) {
                const startIndex = (page - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;

                $('#division-table-body').empty();

                const currentPageData = filteredDivisions.slice(startIndex, endIndex);
                currentPageData.forEach((division, index) => {
                    $('#division-table-body').append(`
                    <tr class="border-b border-gray-200">
                        <td class="py-4 px-6">${startIndex + index + 1}</td>
                        <td class="py-4 px-6">
                            <img src="{{ asset('img/${division.icon}') }}" alt="Icon" class="w-6 h-6">
                        </td>
                        <td class="py-4 px-6">${division.name}</td>
                        <td class="py-4 px-6">
                            <button class="editDivisi px-4 py-2 bg-blue-500 text-white rounded-lg shadow-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500" data-id="${division.id}" data-nama="${division.name}" data-icon="${division.icon}">Edit</button>
                            <button data-action="delete" class="deleteDivisi px-4 py-2 bg-red-500 text-white rounded-lg shadow-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-blue-500" data-id="${division.id}">Hapus</button>
                        </td>
                    </tr>
                `);
                });

                $('#prev-page').prop('disabled', currentPage === 1);
                $('#next-page').prop('disabled', currentPage === Math.ceil(filteredDivisions.length /
                    itemsPerPage));

                $('#page-numbers').empty();
                for (let i = 1; i <= Math.ceil(filteredDivisions.length / itemsPerPage); i++) {
                    $('#page-numbers').append(`
                    <button class="page-number ${currentPage === i ? 'bg-blue-600 text-white' : 'bg-white text-blue-600'} px-4 py-2 rounded-md border hover:bg-gray-100" data-page="${i}">${i}</button>
                `);
                }
            }

            function filterDivisions(searchTerm) {
                return divisions.filter(division =>
                    division.name.toLowerCase().includes(searchTerm.toLowerCase())
                );
            }

            function updateTable() {
                const searchTerm = $('#searchInput').val();
                const filteredDivisions = filterDivisions(searchTerm);
                currentPage = 1; // Reset to the first page after filtering
                renderTable(currentPage, filteredDivisions);
            }

            // Initial render
            renderTable(currentPage, divisions);

            // Search input event listener
            $('#searchInput').on('input', function() {
                updateTable();
            });

            $(document).on('click', '.page-number', function() {
                currentPage = $(this).data('page');
                const searchTerm = $('#searchInput').val();
                const filteredDivisions = filterDivisions(searchTerm);
                renderTable(currentPage, filteredDivisions);
            });

            $('#prev-page').on('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    const searchTerm = $('#searchInput').val();
                    const filteredDivisions = filterDivisions(searchTerm);
                    renderTable(currentPage, filteredDivisions);
                }
            });

            $('#next-page').on('click', function() {
                const searchTerm = $('#searchInput').val();
                const filteredDivisions = filterDivisions(searchTerm);
                if (currentPage < Math.ceil(filteredDivisions.length / itemsPerPage)) {
                    currentPage++;
                    renderTable(currentPage, filteredDivisions);
                }
            });

            $('#addDivisiButton').on('click', function() {
                $('#addDivisiModal').removeClass('hidden');
            });

            $('#closeAddModal, #closeEditDivisiModal, #closeDeleteDivisiModal').on('click', function() {
                $(this).closest('.modal').addClass('hidden');
            });

            $(window).on('click', function(event) {
                if ($(event.target).hasClass('modal')) {
                    $(event.target).addClass('hidden');
                }
            });

            $(document).on('click', '.editDivisi', function() {
                const divisiId = $(this).data('id');
                const namaDivisi = $(this).data('nama');

                $('#editDivisiId').val(divisiId);
                $('#editDivisiForm').attr('action', `{{ route('divisions.update', '') }}/${divisiId}`);
                $('#editNamaDivisi').val(namaDivisi);
                $('#editDivisiModal').removeClass('hidden');
            });

            $(document).on('click', '.deleteDivisi', function() {
                const divisiId = $(this).data('id');
                $('#deleteDivisiId').val(divisiId);
                $('#deleteDivisiModal').removeClass('hidden');
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

            $(document).ready(function() {
                // Tampilkan modal tambah divisi
                $('#addDivisiButton').on('click', function() {
                    $('#addDivisiModal').removeClass('hidden');
                });

                // Tutup modal tambah divisi
                $('#closeAddModal').on('click', function() {
                    $('#addDivisiModal').addClass('hidden');
                });

                // Tampilkan modal edit divisi
                $('.editDivisi').on('click', function() {
                    var divisiId = $(this).data('id');
                    var namaDivisi = $(this).data('nama');

                    // Set value pada formulir
                    $('#editDivisiId').val(divisiId);
                    var actionUrl = '{{ route('divisions.update', ':id') }}';
                    actionUrl = actionUrl.replace(':id', divisiId);
                    $('#editDivisiForm').attr('action', actionUrl);
                    $('#editNamaDivisi').val(namaDivisi);

                    // Tampilkan modal
                    $('#editDivisiModal').removeClass('hidden');
                });

                // Tutup modal edit divisi
                $('#closeEditDivisiModal').on('click', function() {
                    $('#editDivisiModal').addClass('hidden');
                });

                $(document).on('click', '.deleteDivisi', function() {
                    const divisiId = $(this).data('id');
                    $('#deleteDivisiId').val(divisiId);

                    // Set the action URL dynamically
                    const actionUrl = '{{ route('divisions.delete', ':id') }}'.replace(':id',
                        divisiId);
                    $('#deleteDivisiForm').attr('action', actionUrl);

                    $('#deleteDivisiModal').removeClass('hidden');
                });

                // Tutup modal hapus divisi
                $('#closeDeleteDivisiModal').on('click', function() {
                    $('#deleteDivisiModal').addClass('hidden');
                });

                $(window).on('click', function(event) {
                    if ($(event.target).is('#addDivisiModal') || $(event.target).is(
                            '#editDivisiModal') || $(event.target).is('#deleteDivisiModal')) {
                        $(event.target).addClass('hidden'); 
                    }
                });
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
