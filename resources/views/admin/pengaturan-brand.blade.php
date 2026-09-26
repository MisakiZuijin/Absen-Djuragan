@extends('layouts.main')

@section('title', 'Pengaturan Brand')

@section('contents')
    @include('layouts.sidebar-pengaturan')

    <!-- Main Content -->
    <main class="ml-0 lg:ml-[32rem] mt-2 lg:mt-20 p-3 sm:p-6 min-w-0 max-w-full overflow-x-hidden">
        <div class="w-full max-w-full min-w-0 space-y-6">

        <!-- Header & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2.5">
                    <i class="fa-solid fa-tags text-blue-600"></i>
                    Manage Brand
                </h1>
                <p class="text-sm text-gray-600 mt-1">Kelola master data Brand atau Unit Tim Bisnis untuk pemagang</p>
            </div>
            <button id="addBrandButton"
                class="px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm hover:shadow self-start sm:self-auto shrink-0">
                <i class="fas fa-plus"></i>
                <span>Tambah Brand</span>
            </button>
        </div>

        @if (session('status'))
            <div class="mb-5 bg-blue-50 border border-blue-400 text-blue-800 px-4 py-3 rounded-xl relative flex items-center gap-2 shadow-xs" role="alert">
                <i class="fa-solid fa-circle-info text-blue-600 text-lg"></i>
                <span class="font-medium text-sm">{{ session('status') }}</span>
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
                <input type="text" id="searchInput" placeholder="Cari brand berdasarkan nama, slug, atau deskripsi..."
                    class="w-full text-xs pl-9 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            <div class="text-xs text-gray-500 font-medium shrink-0" id="brandCountInfo"></div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200 font-bold tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 sm:px-6 text-center w-16">No</th>
                            <th class="py-3.5 px-4 sm:px-6 w-20">Logo</th>
                            <th class="py-3.5 px-4 sm:px-6">Nama Brand</th>
                            <th class="py-3.5 px-4 sm:px-6">Deskripsi</th>
                            <th class="py-3.5 px-4 sm:px-6 text-center">Pemagang</th>
                            <th class="py-3.5 px-4 sm:px-6 text-center">Status</th>
                            <th class="py-3.5 px-4 sm:px-6 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="brand-table-body" class="divide-y divide-gray-100">
                        <!-- Rows dynamically rendered here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-3.5 bg-white rounded-xl border border-gray-200 shadow-xs">
            <div id="pagination-info" class="text-xs text-gray-500 font-medium"></div>
            <div class="flex items-center space-x-1.5">
                <button id="prev-page"
                    class="cursor-pointer bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed shadow-xs transition">
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

    <!-- Modal Tambah Brand -->
    <div id="addBrandModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-blue-600"></i>
                    Tambahkan Brand
                </h2>
                <button type="button" class="closeModal text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form id="addBrandForm" action="{{ route('brands.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="addName" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Nama Brand <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="addName" name="name"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Contoh: Djuragan Kreatif" required>
                </div>
                <div class="mb-4">
                    <label for="addSlug" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Slug / Identifier
                    </label>
                    <input type="text" id="addSlug" name="slug"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Kosongkan untuk generate otomatis">
                </div>
                <div class="mb-4">
                    <label for="addLogo" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Logo Brand
                    </label>
                    <input type="file" id="addLogo" name="logo"
                        class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs"
                        accept="image/*">
                    <p class="text-[11px] text-gray-400 mt-1">Format: JPG, PNG, GIF, SVG, WEBP (Max 2MB)</p>
                </div>
                <div class="mb-4">
                    <label for="addDescription" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Deskripsi
                    </label>
                    <textarea id="addDescription" name="description" rows="3"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Keterangan mengenai brand..."></textarea>
                </div>
                <div class="mb-5 flex items-center">
                    <input type="checkbox" id="addIsActive" name="is_active" value="1" checked
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="addIsActive" class="ml-2 text-xs font-bold text-gray-700 uppercase tracking-wider">Brand Aktif</label>
                </div>
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" class="closeModal px-4 py-2 text-xs font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-xl shadow-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition">
                        Simpan Brand
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Brand -->
    <div id="editBrandModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-amber-600"></i>
                    Edit Brand
                </h2>
                <button type="button" class="closeModal text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form id="editBrandForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="editBrandId" name="brandId">
                <div class="mb-4">
                    <label for="editName" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Nama Brand <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="editName" name="name"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        required>
                </div>
                <div class="mb-4">
                    <label for="editSlug" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Slug / Identifier
                    </label>
                    <input type="text" id="editSlug" name="slug"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                </div>
                <div class="mb-4">
                    <label for="editLogo" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Ganti Logo (Opsional)
                    </label>
                    <input type="file" id="editLogo" name="logo"
                        class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs"
                        accept="image/*">
                    <div id="currentLogoPreview" class="mt-2 hidden flex items-center space-x-2">
                        <span class="text-xs text-gray-500">Logo saat ini:</span>
                        <img id="currentLogoImg" src="" alt="Logo" class="w-8 h-8 rounded object-contain border p-0.5 bg-gray-50">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="editDescription" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Deskripsi
                    </label>
                    <textarea id="editDescription" name="description" rows="3"
                        class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"></textarea>
                </div>
                <div class="mb-5 flex items-center">
                    <input type="checkbox" id="editIsActive" name="is_active" value="1"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="editIsActive" class="ml-2 text-xs font-bold text-gray-700 uppercase tracking-wider">Brand Aktif</label>
                </div>
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" class="closeModal px-4 py-2 text-xs font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-xl shadow-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-xs transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus Brand -->
    <div id="deleteBrandModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
            <div class="flex items-center gap-3 mb-4 text-red-600">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Hapus Brand</h2>
                    <p class="text-xs text-gray-500">Konfirmasi tindakan penghapusan</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-5">Apakah Anda yakin ingin menghapus brand <strong id="deleteBrandName" class="text-gray-800"></strong>? Pemagang yang terhubung dengan brand ini akan diset tanpa brand.</p>
            <form id="deleteBrandForm" action="" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" class="closeModal px-4 py-2 text-xs font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-xl shadow-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-xs transition">
                        Hapus Brand
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Form for Toggle Status -->
    <form id="toggleStatusForm" action="" method="POST" class="hidden">
        @csrf
    </form>

    <script id="brands-data" type="application/json">@json($brands)</script>
    <script>
        $(document).ready(function() {
            const itemsPerPage = 8;
            let currentPage = 1;
            let brands = JSON.parse(document.getElementById('brands-data')?.textContent || '[]');

            function renderTable(page, filteredBrands) {
                const startIndex = (page - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                const totalItems = filteredBrands.length;

                $('#brandCountInfo').html(`Total: <strong class="text-gray-800">${totalItems}</strong> Brand`);
                $('#brand-table-body').empty();

                if (totalItems === 0) {
                    $('#brand-table-body').append(`
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400 text-xs">
                                <i class="fa-solid fa-folder-open text-2xl mb-2 block"></i>
                                Belum ada data brand ditemukan.
                            </td>
                        </tr>
                    `);
                    $('#pagination-info').text('Menampilkan 0 data');
                    $('#prev-page').prop('disabled', true);
                    $('#next-page').prop('disabled', true);
                    $('#page-numbers').empty();
                    return;
                }

                const currentPageData = filteredBrands.slice(startIndex, endIndex);
                currentPageData.forEach((brand, index) => {
                    const rowNumber = startIndex + index + 1;
                    const logoHtml = brand.logo
                        ? `<img src="{{ asset('img/brands') }}/${brand.logo}" alt="${brand.name}" class="w-8 h-8 rounded-lg object-contain bg-gray-50 border border-gray-200 p-0.5">`
                        : `<div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-400 flex items-center justify-center text-xs"><i class="fa-solid fa-tag"></i></div>`;

                    const statusBadge = brand.is_active
                        ? `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-600 text-white shadow-xs">
                             <span class="w-1.5 h-1.5 bg-white rounded-full"></span> Aktif
                           </span>`
                        : `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-gray-500 text-white shadow-xs">
                             <span class="w-1.5 h-1.5 bg-white rounded-full"></span> Nonaktif
                           </span>`;

                    const toggleTitle = brand.is_active ? 'Nonaktifkan' : 'Aktifkan';
                    const toggleIcon = brand.is_active ? 'fa-toggle-on text-emerald-600' : 'fa-toggle-off text-gray-400';

                    $('#brand-table-body').append(`
                        <tr class="hover:bg-blue-50/30 transition-colors border-b border-gray-100">
                            <td class="py-3.5 px-4 sm:px-6 text-center text-gray-500 font-mono text-xs">${rowNumber}</td>
                            <td class="py-3.5 px-4 sm:px-6">${logoHtml}</td>
                            <td class="py-3.5 px-4 sm:px-6 whitespace-nowrap">
                                <div class="font-bold text-gray-800">${brand.name}</div>
                                <div class="text-xs text-gray-400 font-mono">${brand.slug || '-'}</div>
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-gray-600 max-w-xs truncate text-xs">${brand.description || '-'}</td>
                            <td class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-600 text-white shadow-xs">
                                    <i class="fa-solid fa-users text-[10px]"></i> ${brand.interns_count ?? 0}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">
                                <button type="button" class="toggleStatusBtn text-xl focus:outline-none hover:scale-110 transition"
                                    data-id="${brand.id}" title="${toggleTitle}">
                                    <i class="fa-solid ${toggleIcon}"></i>
                                </button>
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button class="editBrandBtn px-2.5 py-1.5 bg-amber-500 text-white hover:bg-amber-600 rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1"
                                        data-id="${brand.id}"
                                        data-name="${brand.name}"
                                        data-slug="${brand.slug || ''}"
                                        data-logo="${brand.logo || ''}"
                                        data-description="${brand.description || ''}"
                                        data-active="${brand.is_active ? 1 : 0}"
                                        title="Edit Brand">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button class="deleteBrandBtn px-2.5 py-1.5 bg-rose-600 text-white hover:bg-rose-700 rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1"
                                        data-id="${brand.id}"
                                        data-name="${brand.name}"
                                        title="Hapus Brand">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
                $('#pagination-info').text(`Menampilkan ${Math.min(startIndex + 1, totalItems)}-${Math.min(endIndex, totalItems)} dari ${totalItems} brand`);
                $('#prev-page').prop('disabled', currentPage === 1);
                $('#next-page').prop('disabled', currentPage === totalPages || totalPages === 0);

                $('#page-numbers').empty();
                for (let i = 1; i <= totalPages; i++) {
                    const activeCls = currentPage === i ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300';
                    $('#page-numbers').append(`
                        <button class="page-number ${activeCls} px-3 py-1.5 rounded-lg text-xs font-bold shadow-xs transition" data-page="${i}">${i}</button>
                    `);
                }
            }

            function filterBrands(searchTerm) {
                return brands.filter(brand =>
                    (brand.name && brand.name.toLowerCase().includes(searchTerm.toLowerCase())) ||
                    (brand.slug && brand.slug.toLowerCase().includes(searchTerm.toLowerCase())) ||
                    (brand.description && brand.description.toLowerCase().includes(searchTerm.toLowerCase()))
                );
            }

            function updateTable() {
                const searchTerm = $('#searchInput').val();
                const filtered = filterBrands(searchTerm);
                currentPage = 1;
                renderTable(currentPage, filtered);
            }

            renderTable(currentPage, brands);

            $('#searchInput').on('input', function() {
                updateTable();
            });

            $(document).on('click', '.page-number', function() {
                currentPage = $(this).data('page');
                const searchTerm = $('#searchInput').val();
                const filtered = filterBrands(searchTerm);
                renderTable(currentPage, filtered);
            });

            $('#prev-page').on('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    const searchTerm = $('#searchInput').val();
                    const filtered = filterBrands(searchTerm);
                    renderTable(currentPage, filtered);
                }
            });

            $('#next-page').on('click', function() {
                const searchTerm = $('#searchInput').val();
                const filtered = filterBrands(searchTerm);
                const totalPages = Math.ceil(filtered.length / itemsPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    renderTable(currentPage, filtered);
                }
            });

            // Modal Handlers
            $('#addBrandButton').on('click', function() {
                $('#addBrandModal').removeClass('hidden');
            });

            $('.closeModal').on('click', function() {
                $('#addBrandModal, #editBrandModal, #deleteBrandModal').addClass('hidden');
            });

            $(window).on('click', function(event) {
                if ($(event.target).is('#addBrandModal') || $(event.target).is('#editBrandModal') || $(event.target).is('#deleteBrandModal')) {
                    $('#addBrandModal, #editBrandModal, #deleteBrandModal').addClass('hidden');
                }
            });

            // Edit Brand Click
            $(document).on('click', '.editBrandBtn', function() {
                const brandId = $(this).data('id');
                const name = $(this).data('name');
                const slug = $(this).data('slug');
                const logo = $(this).data('logo');
                const description = $(this).data('description');
                const active = $(this).data('active');

                $('#editBrandId').val(brandId);
                let actionUrl = "{{ route('brands.update', ':id') }}".replace(':id', brandId);
                $('#editBrandForm').attr('action', actionUrl);
                $('#editName').val(name);
                $('#editSlug').val(slug);
                $('#editDescription').val(description);
                $('#editIsActive').prop('checked', active == 1);

                if (logo) {
                    $('#currentLogoImg').attr('src', `{{ asset('img/brands') }}/${logo}`);
                    $('#currentLogoPreview').removeClass('hidden');
                } else {
                    $('#currentLogoPreview').addClass('hidden');
                }

                $('#editBrandModal').removeClass('hidden');
            });

            // Delete Brand Click
            $(document).on('click', '.deleteBrandBtn', function() {
                const brandId = $(this).data('id');
                const name = $(this).data('name');

                $('#deleteBrandName').text(name);
                let actionUrl = "{{ route('brands.delete', ':id') }}".replace(':id', brandId);
                $('#deleteBrandForm').attr('action', actionUrl);
                $('#deleteBrandModal').removeClass('hidden');
            });

            // Toggle Status Click
            $(document).on('click', '.toggleStatusBtn', function() {
                const brandId = $(this).data('id');
                let actionUrl = "{{ route('brands.toggleStatus', ':id') }}".replace(':id', brandId);
                $('#toggleStatusForm').attr('action', actionUrl);
                $('#toggleStatusForm').submit();
            });

            const message = document.getElementById('success-message');
            if (message) {
                setTimeout(() => {
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 400);
                }, 3500);
            }
        });
    </script>
@endsection
