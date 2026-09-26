@extends('layouts.main')

@section('title', 'Pengaturan Sekolah')

@section('contents')
    @include('layouts.sidebar-pengaturan')

    <!-- Main Content -->
    <main class="ml-0 lg:ml-[32rem] mt-2 lg:mt-20 p-3 sm:p-6 min-w-0 max-w-full overflow-x-hidden">
        <div class="w-full max-w-full min-w-0 space-y-6">

        <!-- Header & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2.5">
                    <i class="fa-solid fa-graduation-cap text-blue-600"></i>
                    Manage Sekolah / Universitas
                </h1>
                <p class="text-sm text-gray-600 mt-1">Pengaturan master data asal sekolah atau universitas pemagang</p>
            </div>
            <button id="addSchoolButton"
                class="px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm hover:shadow self-start sm:self-auto shrink-0">
                <i class="fas fa-plus"></i>
                <span>Tambah Sekolah / Univ</span>
            </button>
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

        @if ($errors->any())
            <div class="mb-5 bg-red-50 border border-red-400 text-red-800 px-4 py-3 rounded-xl shadow-xs" role="alert">
                <div class="font-bold text-sm mb-1 flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-red-600"></i>
                    Terjadi Kesalahan:
                </div>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Filter & Search Toolbar -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            <div class="relative w-full sm:max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" id="searchInput" placeholder="Cari nama sekolah atau alamat..."
                    class="w-full text-xs pl-9 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            <div class="text-xs text-gray-500 font-medium shrink-0" id="schoolCountInfo"></div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200 font-bold tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 sm:px-6 text-center w-16">No</th>
                            <th class="py-3.5 px-4 sm:px-6">Nama Sekolah / Universitas</th>
                            <th class="py-3.5 px-4 sm:px-6">Alamat</th>
                            <th class="py-3.5 px-4 sm:px-6 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="schoolTableBody" class="divide-y divide-gray-100">
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

    <!-- Modal Tambah Sekolah / Universitas -->
    <div id="addSchoolModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-blue-600"></i>
                    Tambahkan Sekolah / Universitas
                </h2>
                <button type="button" id="closeAddSchoolModal" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form action="{{ route('schools.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="namaSekolah" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Nama Sekolah / Universitas <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="namaSekolah" name="namaSekolah"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Contoh: Universitas Gadjah Mada" required>
                </div>
                <div class="mb-4">
                    <label for="schoolType1" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Tipe Tingkat Pendidikan <span class="text-red-500">*</span>
                    </label>
                    <select id="schoolType1" name="schoolType1"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        required>
                        <option value="" disabled selected>-- Pilih Tingkat --</option>
                        @foreach ($educationalLevels as $level)
                            <option value="{{ $level->id }}">{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-5">
                    <label for="alamatSekolah" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Alamat <span class="text-red-500">*</span>
                    </label>
                    <textarea id="alamatSekolah" name="alamatSekolah" rows="3"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Alamat lengkap institusi..." required></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="$('#addSchoolModal').addClass('hidden')"
                        class="px-4 py-2 text-xs font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-xl shadow-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition">
                        Simpan Sekolah
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Sekolah -->
    <div id="editSchoolModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-amber-600"></i>
                    Update Sekolah / Universitas
                </h2>
                <button type="button" id="closeEditSchoolModal" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form id="editSchoolForm" action="" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="editSchoolId" name="schoolId">
                <div class="mb-4">
                    <label for="editNamaSekolah" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Nama Sekolah / Universitas <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="editNamaSekolah" name="namaSekolah"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        required>
                </div>
                <div class="mb-4">
                    <label for="schoolType" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Tipe Tingkat Pendidikan <span class="text-red-500">*</span>
                    </label>
                    <select id="schoolType" name="schoolType"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        required>
                        <option value="" disabled selected>-- Pilih Tingkat --</option>
                        @foreach ($educationalLevels as $level)
                            <option value="{{ $level->id }}">{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-5">
                    <label for="editAlamatSekolah" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Alamat <span class="text-red-500">*</span>
                    </label>
                    <textarea id="editAlamatSekolah" name="alamatSekolah" rows="3"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        required></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="$('#editSchoolModal').addClass('hidden')"
                        class="px-4 py-2 text-xs font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-xl shadow-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-xs transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus Sekolah / Universitas -->
    <div id="deleteSchoolModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
            <div class="flex items-center gap-3 mb-4 text-red-600">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Hapus Sekolah / Universitas</h2>
                    <p class="text-xs text-gray-500">Konfirmasi tindakan penghapusan</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-5">Apakah Anda yakin ingin menghapus data sekolah/universitas ini?</p>
            <form id="deleteSchoolForm" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" id="deleteSchoolId" name="schoolId">
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" id="closeDeleteSchoolModal"
                        class="px-4 py-2 text-xs font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-xl shadow-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-xs transition">
                        Hapus Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script id="school-data" type="application/json">@json($schoolList)</script>
    <script>
        $(document).ready(function() {
            const schoolList = JSON.parse(document.getElementById('school-data')?.textContent || '[]');
            const itemsPerPage = 8;
            let currentPage = 1;
            let filteredData = schoolList;
            const totalPages = () => Math.max(1, Math.ceil(filteredData.length / itemsPerPage));

            function displayPage(page) {
                const start = (page - 1) * itemsPerPage;
                const end = start + itemsPerPage;
                const paginatedData = filteredData.slice(start, end);

                if (document.getElementById('schoolCountInfo')) {
                    document.getElementById('schoolCountInfo').innerHTML = `Total: <strong class="text-gray-800">${filteredData.length}</strong> Sekolah`;
                }

                $('#schoolTableBody').html('');
                if (paginatedData.length === 0) {
                    $('#schoolTableBody').append(`
                        <tr>
                            <td colspan="4" class="py-8 text-center text-gray-400 text-xs">
                                <i class="fa-solid fa-folder-open text-2xl mb-2 block"></i>
                                Tidak ada data sekolah ditemukan
                            </td>
                        </tr>
                    `);
                    $('#pagination-info').text('Menampilkan 0 data');
                    updatePagination();
                    return;
                }

                const startEntry = start + 1;
                const endEntry = Math.min(end, filteredData.length);
                $('#pagination-info').text(`Menampilkan ${startEntry}-${endEntry} dari ${filteredData.length} sekolah`);

                paginatedData.forEach((school, index) => {
                    $('#schoolTableBody').append(`
                        <tr class="hover:bg-blue-50/30 transition-colors border-b border-gray-100">
                            <td class="py-3.5 px-4 sm:px-6 text-center text-gray-500 font-mono text-xs">${start + index + 1}</td>
                            <td class="py-3.5 px-4 sm:px-6 whitespace-nowrap">
                                <div class="font-bold text-gray-800">${school.name}</div>
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-gray-600 text-xs max-w-sm truncate">${school.address || '-'}</td>
                            <td class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button class="editSchoolModal px-2.5 py-1.5 bg-amber-500 text-white hover:bg-amber-600 rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1"
                                        data-id="${school.id}" data-name="${school.name}" data-address="${school.address || ''}" data-type="${school.educational_level_id}"
                                        title="Edit Sekolah">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button class="deleteSchool px-2.5 py-1.5 bg-rose-600 text-white hover:bg-rose-700 rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1"
                                        data-id="${school.id}"
                                        title="Hapus Sekolah">
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

                for (let i = 1; i <= totalPages(); i++) {
                    const isActive = i === currentPage ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300';
                    const pageButton = $(`
                        <button class="page-number ${isActive} px-3 py-1.5 rounded-lg text-xs font-bold shadow-xs transition" data-page="${i}">${i}</button>
                    `);

                    pageButton.on('click', function() {
                        currentPage = i;
                        displayPage(currentPage);
                        updatePagination();
                    });

                    $('#page-numbers').append(pageButton);
                }

                $('#prev-page').prop('disabled', currentPage <= 1);
                $('#next-page').prop('disabled', currentPage >= totalPages());
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

            $('#searchInput').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                filteredData = schoolList.filter(school =>
                    (school.name && school.name.toLowerCase().includes(searchTerm)) ||
                    (school.address && school.address.toLowerCase().includes(searchTerm))
                );
                currentPage = 1;
                displayPage(currentPage);
                updatePagination();
            });

            displayPage(currentPage);
            updatePagination();

            $(document).on('click', '.editSchoolModal', function() {
                var schoolId = $(this).data('id');
                var namaSekolah = $(this).data('name');
                var alamatSekolah = $(this).data('address');
                var schoolType = $(this).data('type');

                $('#editSchoolId').val(schoolId);
                $('#editNamaSekolah').val(namaSekolah);
                $('#editAlamatSekolah').val(alamatSekolah);
                $('#schoolType').val(schoolType);

                var actionUrl = "{{ route('schools.update', ':id') }}";
                actionUrl = actionUrl.replace(':id', schoolId);
                $('#editSchoolForm').attr('action', actionUrl);

                $('#editSchoolModal').removeClass('hidden');
            });

            $('#closeEditSchoolModal').on('click', function() {
                $('#editSchoolModal').addClass('hidden');
            });

            $(document).on('click', '.deleteSchool', function() {
                var schoolId = $(this).data('id');

                var actionUrl = "{{ route('schools.delete', ':id') }}";
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
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 400);
                }, 3000);
            }
        });
    </script>
@endsection
