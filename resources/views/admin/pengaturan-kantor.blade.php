@extends('layouts.main')

@section('title', 'Pengaturan Kantor')

@section('contents')
    @include('layouts.sidebar-pengaturan')

    <!-- Main Content -->
    <main class="ml-0 lg:ml-[32rem] mt-2 lg:mt-20 p-3 sm:p-6 min-w-0 max-w-full overflow-x-hidden">
        <div class="w-full max-w-full min-w-0 space-y-6">

        <!-- Header & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2.5">
                    <i class="fa-solid fa-building text-blue-600"></i>
                    Manage Kantor
                </h1>
                <p class="text-sm text-gray-600 mt-1">Kelola master data lokasi kantor dan batas wilayah presensi GPS</p>
            </div>
            <!-- Add Kantor Button -->
            <a href="{{ route('office.maps.view') }}"
                class="px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm hover:shadow self-start sm:self-auto shrink-0">
                <i class="fas fa-plus"></i>
                <span>Tambahkan Kantor</span>
            </a>
        </div>

        @if (session('success'))
            <div id="success-message"
                class="mb-5 bg-emerald-50 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-xl relative flex items-center justify-between shadow-xs transition-opacity duration-300"
                role="alert">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        @endif

        <!-- Filter & Search Toolbar -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            <div class="relative w-full sm:max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" id="search-office" placeholder="Cari kantor berdasarkan nama atau alamat..."
                    class="w-full text-xs pl-9 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            <div class="text-xs text-gray-500 font-medium shrink-0" id="officeCountInfo"></div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200 font-bold tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 sm:px-6 text-center w-16">No</th>
                            <th class="py-3.5 px-4 sm:px-6">Nama Lokasi</th>
                            <th class="py-3.5 px-4 sm:px-6">Alamat</th>
                            <th class="py-3.5 px-4 sm:px-6 text-center">Kapasitas</th>
                            <th class="py-3.5 px-4 sm:px-6 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="office-table-body" class="divide-y divide-gray-100">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-3.5 bg-white rounded-xl border border-gray-200 shadow-xs">
            <div id="pagination-info" class="text-xs text-gray-500 font-medium"></div>
            <div class="flex items-center space-x-1.5">
                <button id="prev-page"
                    class="cursor-pointer bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed shadow-xs transition" disabled>
                    <i class="fas fa-chevron-left mr-1"></i> Prev
                </button>
                <div id="page-numbers" class="flex space-x-1"></div>
                <button id="next-page"
                    class="cursor-pointer bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed shadow-xs transition">
                    Next <i class="fas fa-chevron-right ml-1"></i>
                </button>
            </div>
        </div>

        </div>
    </main>

    <!-- Modal Hapus Kantor -->
    <div id="deleteOfficeModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
            <div class="flex items-center gap-3 mb-4 text-red-600">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Hapus Kantor</h2>
                    <p class="text-xs text-gray-500">Konfirmasi tindakan penghapusan</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-5">Apakah Anda yakin ingin menghapus data kantor ini? Pengaturan wilayah absen akan terhapus.</p>
            <form id="deleteOfficeForm" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" id="deleteOfficeId" name="officeId">
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" id="closeDeleteOfficeModal"
                        class="px-4 py-2 text-xs font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-xl shadow-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-xs transition">
                        Hapus Kantor
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script id="office-data" type="application/json">@json($office)</script>
    <script>
        $(document).ready(function() {
            let offices = JSON.parse(document.getElementById('office-data')?.textContent || '[]'); 
            let filteredOffices = offices; 
            let itemsPerPage = 8;
            let currentPage = 1;
            let totalPages = Math.ceil(filteredOffices.length / itemsPerPage);
    
            function displayPage(page) {
                const start = (page - 1) * itemsPerPage;
                const end = page * itemsPerPage;

                if (document.getElementById('officeCountInfo')) {
                    document.getElementById('officeCountInfo').innerHTML = `Total: <strong class="text-gray-800">${filteredOffices.length}</strong> Kantor`;
                }

                $('#office-table-body').empty();
    
                if (filteredOffices.length === 0) {
                    $('#office-table-body').append(`
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-400 text-xs">
                                <i class="fa-solid fa-folder-open text-2xl mb-2 block"></i>
                                Tidak ada data kantor ditemukan.
                            </td>
                        </tr>
                    `);
                    $('#pagination-info').text('Menampilkan 0 data');
                    $('#prev-page').prop('disabled', true);
                    $('#next-page').prop('disabled', true);
                    $('#page-numbers').empty();
                    return;
                }

                const startEntry = start + 1;
                const endEntry = Math.min(end, filteredOffices.length);
                $('#pagination-info').text(`Menampilkan ${startEntry}-${endEntry} dari ${filteredOffices.length} kantor`);

                filteredOffices.slice(start, end).forEach((office, index) => {
                    $('#office-table-body').append(`
                        <tr class="hover:bg-blue-50/30 transition-colors border-b border-gray-100">
                            <td class="py-3.5 px-4 sm:px-6 text-center text-gray-500 font-mono text-xs">${start + index + 1}</td>
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-gray-800 whitespace-nowrap">${office.name}</td>
                            <td class="py-3.5 px-4 sm:px-6 text-gray-600 text-xs max-w-sm truncate">${office.address || '-'}</td>
                            <td class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-600 text-white shadow-xs">
                                    <i class="fa-solid fa-users text-[10px]"></i> ${office.capacity || 0}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ url('admin/setting/office/edit') }}/${office.id}"
                                        class="editOffice px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1"
                                        title="Edit Kantor">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button
                                        class="deleteOffice px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1"
                                        data-id="${office.id}"
                                        title="Hapus Kantor">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `);
                });
            }
    
            function updatePagination() {
                $('#page-numbers').empty();
                totalPages = Math.max(1, Math.ceil(filteredOffices.length / itemsPerPage));
                for (let i = 1; i <= totalPages; i++) {
                    const activeCls = currentPage === i ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300';
                    const pageButton = $(`
                        <button class="page-number ${activeCls} px-3 py-1.5 rounded-lg text-xs font-bold shadow-xs transition" data-page="${i}">${i}</button>
                    `);
    
                    pageButton.on('click', () => {
                        currentPage = i;
                        displayPage(currentPage);
                        updatePagination();
                    });
    
                    $('#page-numbers').append(pageButton);
                }
                $('#prev-page').prop('disabled', currentPage <= 1);
                $('#next-page').prop('disabled', currentPage >= totalPages || totalPages === 0);
            }
    
            function filterOffices() {
                const searchTerm = $('#search-office').val().toLowerCase();
                filteredOffices = offices.filter(office =>
                    (office.name && office.name.toLowerCase().includes(searchTerm)) ||
                    (office.address && office.address.toLowerCase().includes(searchTerm))
                );
                currentPage = 1;
                displayPage(currentPage);
                updatePagination();
            }
    
            $('#search-office').on('input', filterOffices);
    
            $('#prev-page').on('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    displayPage(currentPage);
                    updatePagination();
                }
            });

            $('#next-page').on('click', function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    displayPage(currentPage);
                    updatePagination();
                }
            });

            displayPage(currentPage);
            updatePagination();
    
            $('#office-table-body').on('click', '.deleteOffice', function() {
                var officeId = $(this).data('id');
                $('#deleteOfficeId').val(officeId);
                var actionUrl = `delete-office/${officeId}`;
                $('#deleteOfficeForm').attr('action', actionUrl);
                $('#deleteOfficeModal').removeClass('hidden');
            });
    
            $('#closeDeleteOfficeModal').on('click', function() {
                $('#deleteOfficeModal').addClass('hidden');
            });
    
            $(window).on('click', function(event) {
                if ($(event.target).is('#deleteOfficeModal')) {
                    $('#deleteOfficeModal').addClass('hidden');
                }
            });
        });
    </script>
@endsection
