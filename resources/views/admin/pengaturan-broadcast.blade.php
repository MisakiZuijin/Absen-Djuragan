@extends('layouts.main')

@section('title', 'Pengaturan Pengumuman')

@section('contents')
    @include('layouts.sidebar-pengaturan')

    <!-- Main Content -->
    <main class="ml-0 lg:ml-[32rem] mt-2 lg:mt-20 p-3 sm:p-6 min-w-0 max-w-full overflow-x-hidden">
        <div class="w-full max-w-full min-w-0 space-y-6">

            <!-- Header Section -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-xs">
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Manage Pengumuman</h1>
                <p class="text-gray-500 text-sm">Pengaturan untuk menambahkan, mengedit, dan menargetkan pengumuman kepada divisi atau pemagang tertentu.</p>
            </div>

            <!-- Action Bar -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
                    <!-- Add Button -->
                    <button id="addbroadcastButton"
                        class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-xs font-semibold text-xs sm:text-sm transition">
                        <i class="fas fa-plus mr-2"></i> Tambahkan Pengumuman
                    </button>

                    <!-- Search Input -->
                    <div class="relative w-full sm:w-80">
                        <input type="text" id="searchInput" placeholder="Cari judul, divisi, atau pemagang..."
                            class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            @if (session('success'))
                <div id="success-message"
                    class="bg-emerald-50 border border-emerald-300 text-emerald-800 p-4 rounded-xl shadow-xs flex items-center justify-between transition-all duration-500"
                    role="alert">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle text-emerald-600"></i>
                        <div>
                            <strong class="font-bold">Berhasil!</strong>
                            <span class="ml-1 text-xs sm:text-sm">{{ session('success') }}</span>
                        </div>
                    </div>
                    <button class="text-emerald-600 hover:text-emerald-900 font-bold" onclick="this.closest('#success-message').style.display='none';">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <!-- Table Card -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto min-w-0">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-100 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4 text-center w-16">No</th>
                                <th class="py-3.5 px-4">Judul Pengumuman</th>
                                <th class="py-3.5 px-4">Target Penerima</th>
                                <th class="py-3.5 px-4 text-right pr-6">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs font-medium text-gray-700">
                            @forelse($broadcastlist as $index => $broadcast)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="py-3.5 px-4 text-center text-gray-400 font-bold">
                                        {{ $broadcastlist->firstItem() + $index }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-gray-900 text-xs sm:text-sm">{{ $broadcast->title }}</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($broadcast->divisions->isNotEmpty())
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-600 text-white shadow-xs">
                                                    Divisi
                                                </span>
                                                <span class="text-gray-700 font-medium">{{ $broadcast->divisions->pluck('name')->join(', ') }}</span>
                                            </div>
                                        @elseif($broadcast->shifts->isNotEmpty())
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-500 text-white shadow-xs">
                                                    <i class="fa-solid fa-business-time mr-1 text-[10px]"></i>
                                                    Shift
                                                </span>
                                                <span class="text-gray-700 font-medium">{{ $broadcast->shifts->pluck('name')->join(', ') }}</span>
                                            </div>
                                        @elseif($broadcast->users->isNotEmpty())
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-600 text-white shadow-xs">
                                                    Pemagang
                                                </span>
                                                <span class="text-gray-700 font-medium">{{ $broadcast->users->map(function ($user) {
                                                    return $user->profile->full_name ?? $user->name;
                                                })->join(', ') }}</span>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-purple-600 text-white shadow-xs">
                                                Semua Pengguna
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right pr-6">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                class="editbroadcastModal px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold transition shadow-xs"
                                                data-broadcast='@json($broadcast)'>
                                                <i class="fas fa-edit mr-1"></i>Edit
                                            </button>
                                            <button
                                                class="deletebroadcast px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition shadow-xs"
                                                data-id="{{ $broadcast->id }}">
                                                <i class="fas fa-trash mr-1"></i>Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-gray-400 text-xs">
                                        <i class="fas fa-bullhorn text-2xl mb-2 block text-gray-300"></i>
                                        Belum ada pengumuman yang dibuat
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-center">
                {{ $broadcastlist->links() }}
            </div>
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
                <div class="bg-gray-50/80 px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" id="closeAddbroadcastModal" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">Simpan Pengumuman</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Pengumuman -->
    <div id="editbroadcastModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4 transition-all">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden border border-gray-100 animate-in fade-in zoom-in duration-200">
            <form id="editbroadcastForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="broadcast_type" id="editBroadcastType" value="all">
                <input type="hidden" name="deleted_images" id="deletedImagesInput">

                <!-- Modal Header -->
                <div class="bg-amber-50/80 px-6 py-4 border-b border-amber-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-amber-950">Edit Pengumuman</h2>
                        <p class="text-xs text-amber-800">Perbarui informasi pengumuman yang sudah ada</p>
                    </div>
                    <button type="button" class="text-amber-800 hover:text-amber-950" onclick="$('#editbroadcastModal').addClass('hidden')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="overflow-y-auto max-h-[60vh] p-6 space-y-6">
                    <input type="hidden" id="editbroadcastId" name="id">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="editbroadcastTitle" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Judul Pengumuman <span class="text-rose-500 ml-0.5">*</span></label>
                            <input type="text" id="editbroadcastTitle" name="title" class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <div>
                            <label for="editbroadcastMessage" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Isi Pengumuman <span class="text-rose-500 ml-0.5">*</span></label>
                            <textarea name="message" id="editbroadcastMessage" rows="6" class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" required></textarea>
                        </div>

                        <!-- Current Images -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Gambar Saat Ini</label>
                            <div id="currentImagesContainer" class="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 border border-gray-200 rounded-xl p-4 bg-gray-50 min-h-[8rem]">
                                <p id="noCurrentImages" class="text-xs text-gray-500 col-span-full hidden">Tidak ada gambar saat ini.</p>
                            </div>
                        </div>

                        <!-- Add New Images -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Tambah Gambar Baru <span class="text-gray-400 font-normal normal-case">(opsional)</span></label>
                            <button type="button" id="editFilesButton" class="w-full px-4 py-2.5 bg-gray-800 hover:bg-gray-900 text-white rounded-xl font-semibold text-xs shadow-xs transition">
                                <i class="fas fa-upload mr-2"></i> Pilih atau Tambahkan File
                            </button>
                            <input type="file" id="editbroadcastImg" name="images[]" accept="image/*" multiple class="hidden">
                        </div>
                        
                        <!-- New Image Preview Container -->
                        <div id="editImagePreviewContainer" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"></div>
                    </div>

                    <!-- Target Selection -->
                    <div class="border-t border-gray-100 pt-6">
                        <h3 class="text-sm font-bold text-gray-800 mb-3">Target Penerima Pengumuman</h3>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
                            <p class="text-xs text-amber-800">
                                <i class="fas fa-info-circle mr-1.5"></i>
                                Pilih salah satu target: Divisi atau Pemagang. Jika tidak ada yang dipilih, pengumuman akan dikirim ke semua pengguna.
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="editDivisions" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Target Divisi</label>
                                <select name="divisions[]" id="editDivisions" multiple class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 select2">
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="editUsers" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Target Pemagang</label>
                                <select name="users[]" id="editUsers" multiple class="w-full px-3.5 py-2.5 text-xs sm:text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 select2">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->profile->full_name ?? $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50/80 px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" id="closeEditbroadcastModal" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div id="deletebroadcastModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4 transition-all">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 border border-gray-100 animate-in fade-in zoom-in duration-200 text-center">
            <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 mx-auto flex items-center justify-center text-xl mb-4">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="text-base font-bold text-gray-900 mb-2">Hapus Pengumuman</h2>
            <p class="text-xs text-gray-500 mb-6">Apakah Anda yakin ingin menghapus pengumuman ini? Tindakan ini tidak dapat dibatalkan.</p>
            <form id="deletebroadcastForm" action="" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-center gap-2">
                    <button type="button" id="closeDeletebroadcastModal" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">Ya, Hapus</button>
                </div>
            </form>
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

            // Fungsi untuk mengupdate broadcast_type berdasarkan input yang terisi
            function updateBroadcastType(form) {
                const divisionsSelect = form.querySelector('select[name="divisions[]"]');
                const shiftsSelect = form.querySelector('select[name="shifts[]"]');
                const usersSelect = form.querySelector('select[name="users[]"]');
                const broadcastTypeInput = form.querySelector('input[name="broadcast_type"]');
                
                if ($(divisionsSelect).val() && $(divisionsSelect).val().length > 0) {
                    broadcastTypeInput.value = 'division';
                } else if ($(shiftsSelect).val() && $(shiftsSelect).val().length > 0) {
                    broadcastTypeInput.value = 'shift';
                } else if ($(usersSelect).val() && $(usersSelect).val().length > 0) {
                    broadcastTypeInput.value = 'specific';
                } else {
                    broadcastTypeInput.value = 'all';
                }
            }
            
            // Event listener select (memastikan target tunggal dipilih)
            $('#addDivisions, #editDivisions').on('change', function () {
                const form = $(this).closest('form');
                if ($(this).val() && $(this).val().length > 0) {
                    form.find('.select2[name="users[]"]').val(null).trigger('change');
                    form.find('.select2[name="shifts[]"]').val(null).trigger('change');
                }
                updateBroadcastType(form[0]);
            });

            $('#addShifts, #editShifts').on('change', function () {
                const form = $(this).closest('form');
                if ($(this).val() && $(this).val().length > 0) {
                    form.find('.select2[name="divisions[]"]').val(null).trigger('change');
                    form.find('.select2[name="users[]"]').val(null).trigger('change');
                }
                updateBroadcastType(form[0]);
            });

            $('#addUsers, #editUsers').on('change', function () {
                const form = $(this).closest('form');
                if ($(this).val() && $(this).val().length > 0) {
                    form.find('.select2[name="divisions[]"]').val(null).trigger('change');
                    form.find('.select2[name="shifts[]"]').val(null).trigger('change');
                }
                updateBroadcastType(form[0]);
            });

            // Modal Tambah: buka dan reset form
            $('#addbroadcastButton').click(() => {
                $('#addbroadcastForm')[0].reset();
                $('#addDivisions').val(null).trigger('change');
                $('#addShifts').val(null).trigger('change');
                $('#addUsers').val(null).trigger('change');
                $('#addScheduledAt').val('');
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

                if (broadcast.scheduled_at) {
                    const dt = new Date(broadcast.scheduled_at);
                    const formattedDt = dt.toISOString().slice(0, 16);
                    $('#editScheduledAt').val(formattedDt);
                } else {
                    $('#editScheduledAt').val('');
                }

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
                $('#editShifts').val(null).trigger('change');
                $('#editUsers').val(null).trigger('change');

                if (broadcast.broadcast_type === 'division' && broadcast.divisions) {
                    const divisionIds = broadcast.divisions.map(d => d.id);
                    $('#editDivisions').val(divisionIds).trigger('change');
                } else if (broadcast.broadcast_type === 'shift' && broadcast.shifts) {
                    const shiftIds = broadcast.shifts.map(s => s.id);
                    $('#editShifts').val(shiftIds).trigger('change');
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