@extends('layouts.main')

@section('title', 'Daftar Outsider')

@section('contents')
    <!-- Main Content -->
    <main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Daftar Outsider</h1>
                    <p class="text-gray-600">Kelola data outsider (Guru & Orang Tua)</p>
                </div>
                <a href="{{ route('admin.outsiders.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center shadow-md hover:shadow-lg">
                    <i class="fa fa-plus mr-2"></i>
                    <span>Tambah Outsider</span>
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-sm transition-opacity duration-300"
                id="success-alert">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa fa-check-circle text-green-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="dismissAlert('success-alert')"
                        class="text-green-400 hover:text-green-600 transition-colors duration-200">
                        <i class="fa fa-times text-lg"></i>
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-sm transition-opacity duration-300"
                id="error-alert">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa fa-exclamation-circle text-red-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">
                                {{ session('error') }}
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="dismissAlert('error-alert')"
                        class="text-red-400 hover:text-red-600 transition-colors duration-200">
                        <i class="fa fa-times text-lg"></i>
                    </button>
                </div>
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Filter Data</h3>
            <form method="GET" action="{{ route('admin.outsiders.index') }}"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="filter-form">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                    <input type="text" name="search" id="search-input" value="{{ $filter_search }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                        placeholder="Nama/Email/Username">
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                    <select name="type" id="type-select"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                        <option value="">Semua Tipe</option>
                        <option value="guru" {{ $filter_type == 'guru' ? 'selected' : '' }}>Guru</option>
                        <option value="ortu" {{ $filter_type == 'ortu' ? 'selected' : '' }}>Orang Tua</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status-select"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                        <option value="">Semua Status</option>
                        <option value="active" {{ $filter_status == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ $filter_status == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center justify-center shadow-md hover:shadow-lg">
                        <i class="fa fa-search mr-2"></i>
                        <span>Filter</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="relative">
            {{-- PERBAIKAN 1: Tambahkan kelas 'pointer-events-none' agar spinner tidak menghalangi klik saat tidak terlihat --}}
            <div class="absolute inset-0 flex items-center justify-center z-10 opacity-0 transition-opacity duration-300 pointer-events-none"
                id="table-spinner">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
            </div>

            <div id="table-wrapper" class="transition-opacity duration-300">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipe
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Terkait
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Notifikasi
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($outsiders as $outsider)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-sm">
                                                    <span class="text-white font-medium text-sm">
                                                        {{ strtoupper(substr($outsider->profile->full_name ?? $outsider->username, 0, 2)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $outsider->profile->full_name ?? 'N/A' }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $outsider->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $outsider->outsider->type == 'guru' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ ucfirst($outsider->outsider->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $outsider->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $outsider->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($outsider->outsider->type == 'guru')
                                            @if($outsider->outsider->interns->isNotEmpty())
                                                <span class="text-blue-600 font-medium">{{ $outsider->outsider->interns->first()->school->name ?? 'N/A' }}</span>
                                            @else
                                                <span class="text-gray-500 italic">Belum terkait</span>
                                            @endif
                                        @else
                                            @if($outsider->outsider->interns->isNotEmpty())
                                                <span class="text-green-600 font-medium">{{ $outsider->outsider->interns->first()->user->profile->full_name ?? 'N/A' }}</span>
                                            @else
                                                <span class="text-gray-500 italic">Belum terkait</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($outsider->outsider->type == 'ortu')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $outsider->outsider->notif_enabled ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                                                <i class="fa fa-bell mr-1"></i>
                                                {{ $outsider->outsider->notif_enabled ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-sm">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <a href="{{ route('admin.outsiders.edit', $outsider->id) }}"
                                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200 shadow-sm hover:shadow"
                                                title="Edit Outsider">
                                                <i class="fa fa-edit mr-1"></i>
                                                <span>Edit</span>
                                            </a>
                                            <form id="delete-form-{{ $outsider->id }}"
                                                action="{{ route('admin.outsiders.destroy', $outsider->id) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" data-id="{{ $outsider->id }}"
                                                    data-name="{{ $outsider->profile->full_name ?? $outsider->username }}"
                                                    class="delete-outsider-btn inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors duration-200 shadow-sm hover:shadow"
                                                    title="Hapus Outsider">
                                                    <i class="fa fa-trash mr-1"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4">
                                                <i class="fa fa-users text-3xl text-gray-400"></i>
                                            </div>
                                            <p class="text-xl font-medium text-gray-900 mb-2">Tidak ada data outsider</p>
                                            <p class="text-sm text-gray-500 mb-4">Mulai dengan menambahkan outsider baru</p>
                                            <a href="{{ route('admin.outsiders.create') }}"
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg">
                                                <i class="fa fa-plus mr-2"></i>Tambah Outsider Pertama
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($outsiders->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 rounded-b-lg">
                        {{ $outsiders->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </main>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- PERBAIKAN 2: Ganti seluruh blok script dengan versi yang lebih baik dan efisien --}}
    <script>
        // Function to dismiss alerts
        function dismissAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }

        // Function to initialize delete button handlers
        function initializeDeleteHandlers() {
            const deleteButtons = document.querySelectorAll('.delete-outsider-btn');
            deleteButtons.forEach(button => {
                // Prevent adding multiple listeners to the same button
                if (button.dataset.listenerAttached) return;

                button.addEventListener('click', function() {
                    const outsiderId = this.dataset.id;
                    const outsiderName = this.dataset.name;
                    const form = document.getElementById(`delete-form-${outsiderId}`);

                    Swal.fire({
                        title: 'Anda Yakin?',
                        html: `Akan menghapus data outsider: <strong class="font-semibold text-blue-600">${outsiderName}</strong>.<br>Tindakan ini tidak dapat dibatalkan.`,
                        icon: 'warning',
                        iconColor: '#ef4444',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus Saja',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                        customClass: {
                            popup: 'rounded-xl shadow-lg',
                            title: 'text-2xl font-bold text-gray-800',
                            htmlContainer: 'text-gray-600',
                            confirmButton: 'px-4 py-2 text-sm font-semibold rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300 transition-all duration-200',
                            cancelButton: 'px-4 py-2 text-sm font-semibold rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-200 transition-all duration-200'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
                button.dataset.listenerAttached = 'true';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide success/error alerts
            setTimeout(() => dismissAlert('success-alert'), 5000);
            setTimeout(() => dismissAlert('error-alert'), 8000);

            // --- Live filter logic ---
            const filterForm = document.getElementById('filter-form');
            const searchInput = document.getElementById('search-input');
            const typeSelect = document.getElementById('type-select');
            const statusSelect = document.getElementById('status-select');
            const tableWrapper = document.getElementById('table-wrapper');
            const spinner = document.getElementById('table-spinner');

            let filterTimeout;

            function submitFilter() {
                const formData = new FormData(filterForm);
                const params = new URLSearchParams(formData).toString();
                
                // Show spinner and make it block clicks on the semi-transparent content
                tableWrapper.style.opacity = '0.5';
                spinner.classList.remove('opacity-0', 'pointer-events-none');
                spinner.classList.add('opacity-100');

                fetch(`{{ route('admin.outsiders.index') }}?${params}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        // Directly replace the content with the partial view's HTML
                        tableWrapper.innerHTML = html;
                        
                        // Re-initialize delete handlers for the new content
                        initializeDeleteHandlers();
                    })
                    .catch(error => console.error('Error during fetch:', error))
                    .finally(() => {
                        // Hide spinner and make it non-clickable again
                        setTimeout(() => {
                            tableWrapper.style.opacity = '1';
                            spinner.classList.remove('opacity-100');
                            spinner.classList.add('opacity-0', 'pointer-events-none');
                        }, 100);
                    });
            }

            // Debounce search input
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(filterTimeout);
                    filterTimeout = setTimeout(submitFilter, 300); // 300ms delay
                });
            }
            if (typeSelect) typeSelect.addEventListener('change', submitFilter);
            if (statusSelect) statusSelect.addEventListener('change', submitFilter);

            // Initial call to attach handlers to the first-load buttons
            initializeDeleteHandlers();
        });
    </script>
@endsection