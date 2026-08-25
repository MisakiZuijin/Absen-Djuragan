@extends('layouts.main')

@section('title', 'Pengaturan Pengumuman')

@section('contents')
    @include('layouts.sidebar')
    @include('layouts.sidebar-pengaturan')
    @include('layouts.navbar')

    <!-- Main Content -->
    <main class="ml-[32rem] mt-24 p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-3">Kelola Pengumuman</h1>
            <p class="text-gray-600 leading-relaxed">Pengaturan untuk menambahkan, mengedit, dan menargetkan pengumuman kepada divisi atau pemagang tertentu.</p>
        </div>

        <!-- Action Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <!-- Add Button -->
                <button id="addbroadcastButton"
    class="flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg shadow-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
    <i class="fas fa-plus mr-2"></i> Tambahkan Pengumuman
</button>

                <!-- Search Input -->
                <div class="relative w-full sm:w-80">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="searchInput" placeholder="Cari berdasarkan judul, divisi, atau pemagang..."
                        class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 hover:bg-white transition-colors duration-200">
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div id="success-message"
                class="mb-6 bg-green-50 border-l-4 border-green-400 text-green-700 p-4 rounded-lg shadow-sm transition-all duration-500"
                role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <strong class="font-semibold">Berhasil!</strong>
                        <span class="ml-1">{{ session('success') }}</span>
                    </div>
                    <button class="ml-auto text-green-400 hover:text-green-600" onclick="this.closest('#success-message').style.display='none';">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <!-- Table Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">No</th>
                            <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Judul Pengumuman</th>
                            <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Target Penerima</th>
                            <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($broadcastlist as $index => $broadcast)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="py-4 px-6 text-sm text-gray-900">
                                    <span class="bg-gray-100 text-gray-700 py-1 px-3 rounded-full text-xs font-medium">
                                        {{ $broadcastlist->firstItem() + $index }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="text-sm font-medium text-gray-900">{{ $broadcast->title }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    @if($broadcast->divisions->isNotEmpty())
                                        <div class="flex items-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Divisi
                                            </span>
                                            <span class="text-sm text-gray-600">{{ $broadcast->divisions->pluck('name')->join(', ') }}</span>
                                        </div>
                                    @elseif($broadcast->users->isNotEmpty())
                                        <div class="flex items-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                                                </svg>
                                                Pemagang
                                            </span>
                                            <span class="text-sm text-gray-600">{{ $broadcast->users->map(function ($user) {
                                                return $user->profile->full_name ?? $user->name;
                                            })->join(', ') }}</span>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                            </svg>
                                            Semua Pengguna
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex space-x-2">
                                        <button
                                            class="editbroadcastModal inline-flex items-center px-3 py-2 border border-blue-300 text-blue-700 bg-blue-50 rounded-lg text-sm font-medium hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                                            data-broadcast='@json($broadcast)'>
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </button>
                                        <button
                                            class="deletebroadcast inline-flex items-center px-3 py-2 border border-red-300 text-red-700 bg-red-50 rounded-lg text-sm font-medium hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200"
                                            data-id="{{ $broadcast->id }}">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center">
            {{ $broadcastlist->links() }}
        </div>
    </main>

    <!-- Modal Tambah Pengumuman -->
    <div id="addbroadcastModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
            <form id="addbroadcastForm" action="{{ route('broadcast.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="broadcast_type" value="all">

                <!-- Modal Header -->
                <div class="bg-blue-50 px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">Tambahkan Pengumuman Baru</h2>
                    <p class="text-sm text-gray-600 mt-1">Isi form di bawah untuk membuat pengumuman baru</p>
                </div>

                <!-- Modal Body -->
                <div class="overflow-y-auto max-h-[60vh] p-6 space-y-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <div>
                            <label for="broadcastTitle" class="block text-sm font-medium text-gray-700 mb-2">
                                Judul Pengumuman <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="broadcastTitle" name="title" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Masukkan judul pengumuman..." required>
                        </div>

                        <div>
                            <label for="broadcastMessage" class="block text-sm font-medium text-gray-700 mb-2">
                                Isi Pengumuman <span class="text-red-500">*</span>
                            </label>
                            <textarea name="message" id="broadcastMessage" rows="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none" placeholder="Tulis isi pengumuman di sini..." required></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Gambar Pendukung <span class="text-sm text-gray-500">(opsional)</span>
                            </label>
                            <!-- [MODIFIKASI] Tombol terlihat & Input file tersembunyi -->
                            <button type="button" id="addFilesButton" class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-blue-500 hover:text-blue-500 transition">
                                <i class="fas fa-upload mr-2"></i> Pilih atau Tambahkan File
                            </button>
                            <input type="file" id="broadcastimg" name="images[]" accept="image/*" multiple class="hidden">
                            <p class="text-xs text-gray-500 mt-1">Format yang didukung: JPG, PNG, GIF (Maks. 2MB per file)</p>

                             <!-- Image Preview Container -->
                            <div id="addImagePreviewContainer" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"></div>
                        </div>
                    </div>

                    <!-- Target Selection -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Target Penerima Pengumuman</h3>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-yellow-800">
                                <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Pilih salah satu target: Divisi atau Pemagang. Jika tidak ada yang dipilih, pengumuman akan dikirim ke semua pengguna.
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="addDivisions" class="block text-sm font-medium text-gray-700 mb-2">Target Divisi</label>
                                <select name="divisions[]" id="addDivisions" multiple class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 select2">
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="addUsers" class="block text-sm font-medium text-gray-700 mb-2">Target Pemagang</label>
                                <select name="users[]" id="addUsers" multiple class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 select2">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->profile->full_name ?? $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" id="closeAddbroadcastModal" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors duration-200">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">Simpan Pengumuman</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Pengumuman -->
    <div id="editbroadcastModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
            <form id="editbroadcastForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="broadcast_type" id="editBroadcastType" value="all">
                <input type="hidden" name="deleted_images" id="deletedImagesInput">

                <!-- Modal Header -->
                <div class="bg-orange-50 px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">Edit Pengumuman</h2>
                    <p class="text-sm text-gray-600 mt-1">Perbarui informasi pengumuman yang sudah ada</p>
                </div>

                <!-- Modal Body -->
                <div class="overflow-y-auto max-h-[60vh] p-6 space-y-6">
                    <input type="hidden" id="editbroadcastId" name="id">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="editbroadcastTitle" class="block text-sm font-medium text-gray-700 mb-2">Judul Pengumuman <span class="text-red-500">*</span></label>
                            <input type="text" id="editbroadcastTitle" name="title" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" required>
                        </div>

                        <div>
                            <label for="editbroadcastMessage" class="block text-sm font-medium text-gray-700 mb-2">Isi Pengumuman <span class="text-red-500">*</span></label>
                            <textarea name="message" id="editbroadcastMessage" rows="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none" required></textarea>
                        </div>

                        <!-- Current Images -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Saat Ini</label>
                            <div id="currentImagesContainer" class="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 border border-gray-200 rounded-lg p-4 bg-gray-50 min-h-[8rem]">
                                <p id="noCurrentImages" class="text-sm text-gray-500 col-span-full hidden">Tidak ada gambar saat ini.</p>
                            </div>
                        </div>

                        <!-- Add New Images -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tambah Gambar Baru <span class="text-sm text-gray-500">(opsional)</span></label>
                            <!-- [MODIFIKASI] Tombol terlihat & Input file tersembunyi -->
                            <button type="button" id="editFilesButton" class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-orange-500 hover:text-orange-500 transition">
                                <i class="fas fa-upload mr-2"></i> Pilih atau Tambahkan File
                            </button>
                            <input type="file" id="editbroadcastImg" name="images[]" accept="image/*" multiple class="hidden">
                        </div>
                        
                        <!-- New Image Preview Container -->
                        <div id="editImagePreviewContainer" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"></div>
                    </div>

                    <!-- Target Selection -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Target Penerima Pengumuman</h3>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-yellow-800">
                                <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                Pilih salah satu target: Divisi atau Pemagang. Jika tidak ada yang dipilih, pengumuman akan dikirim ke semua pengguna.
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editDivisions" class="block text-sm font-medium text-gray-700 mb-2">Target Divisi</label>
                                <select name="divisions[]" id="editDivisions" multiple class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 select2">
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="editUsers" class="block text-sm font-medium text-gray-700 mb-2">Target Pemagang</label>
                                <select name="users[]" id="editUsers" multiple class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 select2">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->profile->full_name ?? $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" id="closeEditbroadcastModal" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors duration-200">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div id="deletebroadcastModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                </div>
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Konfirmasi Penghapusan</h3>
                    <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin menghapus pengumuman ini? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <form id="deletebroadcastForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex space-x-3 justify-end">
                        <button type="button" id="closeDeletebroadcastModal" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">Ya, Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- CSS Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove { color: white !important; }
        .select2-container--default .select2-selection--multiple:focus { border: 2px solid #3b82f6 !important; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important; }
        .select2-dropdown { border-radius: 0.5rem !important; border: 1px solid #d1d5db !important; }
        .overflow-y-auto::-webkit-scrollbar { width: 6px; }
        .overflow-y-auto::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
        .overflow-y-auto::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .overflow-y-auto::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            // ===================================================================
            // [MODIFIKASI UTAMA] Logika Baru untuk Manajemen File
            // ===================================================================
            let addModalFiles = [];
            let editModalFiles = [];

            // Fungsi untuk merender preview gambar
            function renderPreviews(stagedFiles, containerSelector) {
                const container = $(containerSelector);
                container.empty();

                stagedFiles.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = `
                            <div class="relative group">
                                <img src="${e.target.result}" class="h-24 w-full object-cover rounded-lg shadow-sm">
                                <button type="button" class="remove-preview absolute top-1 right-1 bg-red-600 text-white rounded-full h-6 w-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity" data-index="${index}" data-modal-type="${containerSelector.includes('add') ? 'add' : 'edit'}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        `;
                        container.append(preview);
                    };
                    reader.readAsDataURL(file);
                });
            }

            // Pemicu untuk input file di modal Tambah
            $('#addFilesButton').on('click', function() {
                $('#broadcastimg').click();
            });

            // Pemicu untuk input file di modal Edit
            $('#editFilesButton').on('click', function() {
                $('#editbroadcastImg').click();
            });

            // Event handler saat file dipilih di modal Tambah
            $('#broadcastimg').on('change', function(e) {
                const newFiles = Array.from(e.target.files);
                addModalFiles.push(...newFiles);
                renderPreviews(addModalFiles, '#addImagePreviewContainer');
                $(this).val(''); // Reset input agar bisa memilih file yang sama lagi
            });

            // Event handler saat file dipilih di modal Edit
            $('#editbroadcastImg').on('change', function(e) {
                const newFiles = Array.from(e.target.files);
                editModalFiles.push(...newFiles);
                renderPreviews(editModalFiles, '#editImagePreviewContainer');
                $(this).val(''); // Reset input
            });

            // Event handler untuk tombol hapus preview
            $(document).on('click', '.remove-preview', function() {
                const index = $(this).data('index');
                const modalType = $(this).data('modal-type');
                
                if (modalType === 'add') {
                    addModalFiles.splice(index, 1);
                    renderPreviews(addModalFiles, '#addImagePreviewContainer');
                } else if (modalType === 'edit') {
                    editModalFiles.splice(index, 1);
                    renderPreviews(editModalFiles, '#editImagePreviewContainer');
                }
            });

            // Sinkronisasi file ke input sebelum form disubmit
            function syncFilesToInput(stagedFiles, inputSelector) {
                const dataTransfer = new DataTransfer();
                stagedFiles.forEach(file => {
                    dataTransfer.items.add(file);
                });
                $(inputSelector)[0].files = dataTransfer.files;
            }

            $('#addbroadcastForm').on('submit', function() {
                syncFilesToInput(addModalFiles, '#broadcastimg');
            });

            $('#editbroadcastForm').on('submit', function() {
                syncFilesToInput(editModalFiles, '#editbroadcastImg');
            });
            // ===================================================================
            // AKHIR MODIFIKASI UTAMA
            // ===================================================================


            // Inisialisasi Select2
            $('.select2').select2({
                placeholder: "Pilih target penerima...",
                allowClear: true,
                width: '100%',
                dropdownParent: $(document.body)
            });

            // Fungsi update tipe broadcast
            function updateBroadcastType(form) {
                const broadcastTypeInput = form.querySelector('input[name="broadcast_type"]');
                const divisionsSelect = form.querySelector('.select2[name="divisions[]"]');
                const usersSelect = form.querySelector('.select2[name="users[]"]');
                if ($(divisionsSelect).val() && $(divisionsSelect).val().length > 0) {
                    broadcastTypeInput.value = 'division';
                } else if ($(usersSelect).val() && $(usersSelect).val().length > 0) {
                    broadcastTypeInput.value = 'specific';
                } else {
                    broadcastTypeInput.value = 'all';
                }
            }
            
            // Event listener select (memastikan hanya satu target dipilih)
            $('#addDivisions, #editDivisions').on('change', function () {
                const form = $(this).closest('form');
                if ($(this).val() && $(this).val().length > 0) {
                    form.find('.select2[name="users[]"]').val(null).trigger('change');
                }
                updateBroadcastType(form[0]);
            });

            $('#addUsers, #editUsers').on('change', function () {
                const form = $(this).closest('form');
                if ($(this).val() && $(this).val().length > 0) {
                    form.find('.select2[name="divisions[]"]').val(null).trigger('change');
                }
                updateBroadcastType(form[0]);
            });

            // Modal Tambah: buka dan reset form
            $('#addbroadcastButton').click(() => {
                $('#addbroadcastForm')[0].reset();
                $('#addDivisions').val(null).trigger('change');
                $('#addUsers').val(null).trigger('change');
                addModalFiles = []; // Reset array file
                renderPreviews(addModalFiles, '#addImagePreviewContainer'); // Kosongkan preview
                $('#addbroadcastModal').removeClass('hidden');
                setTimeout(() => $('#broadcastTitle').focus(), 100);
            });

            $('#closeAddbroadcastModal').click(() => {
                $('#addbroadcastModal').addClass('hidden');
            });

            // Modal Edit: buka dan isi data
            $(document).on('click', '.editbroadcastModal', function () {
                const broadcast = $(this).data('broadcast');
                if (!broadcast) {
                    alert('Gagal memuat data pengumuman. Silakan coba lagi.');
                    return;
                }
                
                // Reset form dan state
                $('#editbroadcastForm')[0].reset();
                editModalFiles = []; // Reset array file baru
                renderPreviews(editModalFiles, '#editImagePreviewContainer'); // Kosongkan preview file baru
                $('#deletedImagesInput').val('');

                // Isi field dasar
                $('#editbroadcastTitle').val(broadcast.title);
                $('#editbroadcastMessage').val(broadcast.message);
                $('#editBroadcastType').val(broadcast.broadcast_type);

                const currentImagesContainer = $('#currentImagesContainer');
                const noCurrentImagesText = $('#noCurrentImages');
                currentImagesContainer.find('.image-wrapper').remove();

                if (broadcast.images && broadcast.images.length > 0) {
                    noCurrentImagesText.addClass('hidden');
                    broadcast.images.forEach(img => {
                        const imageUrl = '{{ asset("broadcast-image") }}/' + img.image;
                        const imageWrapper = `
                            <div class="relative group image-wrapper">
                                <img src="${imageUrl}" class="h-24 w-full object-cover rounded-lg shadow-sm">
                                <button type="button" class="delete-existing-image absolute top-1 right-1 bg-red-600 text-white rounded-full h-6 w-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity" data-image-id="${img.id}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        `;
                        currentImagesContainer.append(imageWrapper);
                    });
                } else {
                    noCurrentImagesText.removeClass('hidden');
                }

                $('#editDivisions').val(null).trigger('change');
                $('#editUsers').val(null).trigger('change');

                if (broadcast.broadcast_type === 'division' && broadcast.divisions) {
                    const divisionIds = broadcast.divisions.map(d => d.id);
                    $('#editDivisions').val(divisionIds).trigger('change');
                } else if (broadcast.broadcast_type === 'specific' && broadcast.users) {
                    const userIds = broadcast.users.map(u => u.id);
                    $('#editUsers').val(userIds).trigger('change');
                }
                
                $('#editbroadcastForm').attr('action', '{{ url("admin/broadcasts") }}/' + broadcast.id);
                $('#editbroadcastModal').removeClass('hidden');
                
                setTimeout(() => $('#editbroadcastTitle').focus(), 100);
            });

            $('#closeEditbroadcastModal').click(() => {
                $('#editbroadcastModal').addClass('hidden');
            });

            // Event listener untuk menghapus gambar yang sudah ada
            $(document).on('click', '.delete-existing-image', function() {
                const imageId = $(this).data('image-id');
                const deletedImagesInput = $('#deletedImagesInput');
                let deletedIds = deletedImagesInput.val() ? deletedImagesInput.val().split(',') : [];
                if (!deletedIds.includes(imageId.toString())) {
                    deletedIds.push(imageId);
                }
                deletedImagesInput.val(deletedIds.join(','));
                $(this).closest('.image-wrapper').fadeOut(300, function() { 
                    $(this).remove();
                    if ($('#currentImagesContainer .image-wrapper').length === 0) {
                        $('#noCurrentImages').removeClass('hidden');
                    }
                });
            });

            // Modal Hapus
            $(document).on('click', '.deletebroadcast', function () {
                const id = $(this).data('id');
                $('#deletebroadcastForm').attr('action', '{{ url("admin/broadcasts") }}/' + id);
                $('#deletebroadcastModal').removeClass('hidden');
            });

            $('#closeDeletebroadcastModal').click(() => {
                $('#deletebroadcastModal').addClass('hidden');
            });

            // Tutup modal jika klik di luar
            $(window).click(function (event) {
                if ($(event.target).is('#addbroadcastModal')) $('#addbroadcastModal').addClass('hidden');
                if ($(event.target).is('#editbroadcastModal')) $('#editbroadcastModal').addClass('hidden');
                if ($(event.target).is('#deletebroadcastModal')) $('#deletebroadcastModal').addClass('hidden');
            });

            // Sembunyikan notifikasi sukses
            setTimeout(() => $('#success-message').fadeOut('slow'), 5000);

            // Search functionality
            $('#searchInput').on('input', function () {
                const searchTerm = $(this).val().toLowerCase();
                $('tbody tr').each(function () {
                    const rowText = $(this).text().toLowerCase();
                    $(this).toggle(rowText.includes(searchTerm));
                });
            });

            // Form validation enhancement
            $('#addbroadcastForm, #editbroadcastForm').on('submit', function(e) {
                const title = $(this).find('input[name="title"]').val().trim();
                const message = $(this).find('textarea[name="message"]').val().trim();
                if (!title || !message) {
                    e.preventDefault();
                    alert('Mohon lengkapi semua field yang wajib diisi.');
                    return false;
                }
            });

            // Keyboard shortcuts
            $(document).keydown(function(e) {
                if (e.key === 'Escape') $('.fixed.inset-0').addClass('hidden');
                if (e.ctrlKey && e.key === 'n') {
                    e.preventDefault();
                    $('#addbroadcastButton').click();
                }
            });
        });
    </script>
@endsection