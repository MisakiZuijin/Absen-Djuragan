{{-- Pastikan nama layout ini sudah benar sesuai proyek Anda --}}
@extends('layouts.main')

{{-- Pastikan nama section ini 'contents' agar konsisten dengan halaman lain --}}
@section('contents')

<main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 bg-gray-50 min-h-screen min-w-0">
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="relative bg-white rounded-2xl shadow-lg border border-gray-200 p-4 sm:p-8 mb-6 sm:mb-8 overflow-hidden">
            <!-- Decorative background elements -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -translate-y-16 translate-x-16"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-indigo-50 rounded-full translate-y-12 -translate-x-12"></div>

            <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                Manajemen Asisten Admin
                            </h1>
                            <p class="text-gray-600 font-medium">Kelola data asisten admin sistem dengan mudah</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="hidden md:flex items-center space-x-2 px-4 py-2 bg-gray-100 rounded-xl border border-gray-200">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <span class="text-sm font-medium text-gray-700">{{ $assistantAdmins->count() }} Admin Aktif</span>
                    </div>
                    <a href="{{ route('admin.assistant-admins.create') }}"
                        class="group relative inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-blue-500/25">
                        <svg class="w-5 h-5 mr-2 transition-transform group-hover:rotate-90 duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span>Tambah Asisten Admin</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Notifikasi Sukses --}}
        @if (session('success'))
        <div class="relative bg-green-50 border border-green-200 rounded-2xl p-5 mb-8 shadow-lg overflow-hidden" role="alert">
            <!-- Decorative elements -->
            <div class="absolute top-0 right-0 w-20 h-20 bg-green-100 rounded-full -translate-y-10 translate-x-10"></div>
            <div class="relative flex items-center">
                <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="font-semibold text-green-800">Berhasil!</p>
                    <p class="text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Table Section -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
            <!-- Table Header -->
            <div class="px-8 py-6 bg-gray-50 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-slate-700 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">Daftar Asisten Admin</h3>
                    </div>
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Total: {{ $assistantAdmins->total() ?? $assistantAdmins->count() }} admin</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th scope="col" class="px-8 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                <div class="flex items-center space-x-2">
                                    <div class="w-5 h-5 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <span>Admin</span>
                                </div>
                            </th>
                            <th scope="col" class="px-8 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                <div class="flex items-center space-x-2">
                                    <div class="w-5 h-5 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                        </svg>
                                    </div>
                                    <span>Kontak</span>
                                </div>
                            </th>
                            <th scope="col" class="px-8 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                <div class="flex items-center space-x-2">
                                    <div class="w-5 h-5 bg-orange-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-3 h-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                        </svg>
                                    </div>
                                    <span>Tindakan</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($assistantAdmins as $assistant)
                        <tr class="group hover:bg-blue-50 transition-all duration-300">
                            <td class="px-8 py-6 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 relative">
                                        <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                                            <span class="text-sm font-bold text-white">
                                                {{ strtoupper(substr($assistant->name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 rounded-full border-2 border-white shadow-md"></div>
                                    </div>
                                    <div class="ml-5">
                                        <div class="text-base font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors duration-200">
                                            {{ $assistant->name }}
                                        </div>
                                        <div class="text-sm text-gray-500 font-medium">Admin Assistant</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $assistant->email }}</div>
                                        <div class="text-xs text-gray-500">Email Utama</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('admin.assistant-admins.edit', $assistant->id) }}"
                                        class="group/edit relative inline-flex items-center px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 hover:text-amber-800 text-sm font-semibold rounded-xl border border-amber-200 hover:border-amber-300 transition-all duration-200 shadow-sm hover:shadow-md transform hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-amber-500/25">
                                        <svg class="w-4 h-4 mr-2 transition-transform group-hover/edit:rotate-12 duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        <span>Edit</span>
                                    </a>

                                    {{-- === BAGIAN YANG DIUBAH: START === --}}
                                    <form id="delete-form-{{ $assistant->id }}" action="{{ route('admin.assistant-admins.destroy', $assistant->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            data-id="{{ $assistant->id }}"
                                            data-name="{{ $assistant->name }}"
                                            class="delete-btn group/delete relative inline-flex items-center px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 hover:text-red-800 text-sm font-semibold rounded-xl border border-red-200 hover:border-red-300 transition-all duration-200 shadow-sm hover:shadow-md transform hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-red-500/25">
                                            <svg class="w-4 h-4 mr-2 transition-transform group-hover/delete:scale-110 duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                    {{-- === BAGIAN YANG DIUBAH: END === --}}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center space-y-4">
                                    <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center">
                                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                    </div>
                                    <div class="text-center space-y-2">
                                        <h3 class="text-lg font-semibold text-gray-900">Belum Ada Data</h3>
                                        <p class="text-gray-600 max-w-sm">Belum ada asisten admin yang terdaftar dalam sistem. Mulai dengan menambahkan admin pertama.</p>
                                    </div>
                                    <a href="{{ route('admin.assistant-admins.create') }}"
                                        class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Tambah Admin Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($assistantAdmins->hasPages())
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <div class="pagination-mobile">
                            {{ $assistantAdmins->simplePaginate() }}
                        </div>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-700">
                                Menampilkan
                                <span class="font-bold text-blue-600">{{ $assistantAdmins->firstItem() }}</span>
                                -
                                <span class="font-bold text-blue-600">{{ $assistantAdmins->lastItem() }}</span>
                                dari
                                <span class="font-bold text-blue-600">{{ $assistantAdmins->total() }}</span>
                                admin
                            </p>
                        </div>
                        <div class="pagination-links">
                            {{ $assistantAdmins->links() }}
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</main>

<style>
    /* Custom pagination styling */
    .pagination-links .pagination {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .pagination-links .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        font-weight: 500;
        color: #374151;
        background-color: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
    }

    .pagination-links .page-link:hover {
        background-color: #f9fafb;
        color: #2563eb;
    }

    .pagination-links .page-link:focus {
        outline: 2px solid transparent;
        outline-offset: 2px;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
    }

    .pagination-links .page-item.active .page-link {
        background-color: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
    }

    .pagination-links .page-item.disabled .page-link {
        color: #9ca3af;
        cursor: not-allowed;
    }

    .pagination-links .page-item.disabled .page-link:hover {
        background-color: #ffffff;
        color: #9ca3af;
    }

    /* Mobile pagination */
    .pagination-mobile .pagination {
        display: flex;
        justify-content: space-between;
        width: 100%;
        gap: 0.5rem;
    }

    .pagination-mobile .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        font-weight: 500;
        color: #374151;
        background-color: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
    }

    .pagination-mobile .page-link:hover {
        background-color: #f9fafb;
    }
</style>

{{-- === BAGIAN BARU: SCRIPT UNTUK SWEETALERT === --}}
{{-- Anda bisa memindahkan ini ke dalam @push('scripts') jika layout Anda menggunakan @stack('scripts') --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-btn');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const assistantId = this.dataset.id;
                const assistantName = this.dataset.name;
                const form = document.getElementById(`delete-form-${assistantId}`);

                Swal.fire({
                    title: 'Konfirmasi Penghapusan',
                    html: `Anda yakin ingin menghapus asisten <strong class="font-semibold text-indigo-600">${assistantName}</strong>?<br>Tindakan ini tidak dapat dibatalkan.`,
                    icon: 'warning',
                    iconColor: '#ef4444', // red-500
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true, // Tombol konfirmasi di kanan
                    customClass: {
                        popup: 'p-4 sm:p-6 rounded-2xl shadow-xl border border-gray-200',
                        title: 'text-2xl font-bold text-gray-800 mb-2',
                        htmlContainer: 'text-base text-gray-600',
                        confirmButton: 'px-5 py-2.5 text-sm font-semibold rounded-xl text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300 transition-all duration-300 transform hover:-translate-y-0.5',
                        cancelButton: 'px-5 py-2.5 text-sm font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-200 transition-all duration-300'
                    },
                    buttonsStyling: false // Penting untuk menerapkan kelas kustom
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Tampilkan notifikasi loading saat form disubmit
                        Swal.fire({
                            title: 'Menghapus...',
                            text: 'Mohon tunggu sebentar.',
                            imageUrl: 'https://media.tenor.com/On7kvXhzml4AAAAj/loading-gif.gif', // Ganti dengan URL loading GIF yang Anda suka
                            imageWidth: 100,
                            imageHeight: 100,
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            customClass: {
                                popup: 'p-4 sm:p-6 rounded-2xl shadow-xl border border-gray-200',
                            }
                        });
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endsection