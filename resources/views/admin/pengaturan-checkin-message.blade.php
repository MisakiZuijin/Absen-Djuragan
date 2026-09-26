@extends('layouts.main')

@section('title', 'Pengaturan Popup Check-in')

@section('contents')
@include('layouts.sidebar-pengaturan')

<!-- Main Content -->
<main class="ml-0 lg:ml-[32rem] mt-2 lg:mt-20 p-3 sm:p-6 min-w-0 max-w-full overflow-x-hidden">
    <div class="w-full max-w-full min-w-0 space-y-6">

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-xs border border-gray-100">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="p-2.5 bg-emerald-600 text-white rounded-xl shadow-xs">
                        <i class="fa-solid fa-message-check text-lg"></i>
                    </span>
                    <h1 class="text-2xl font-bold text-gray-900">Pengaturan Popup Check-in</h1>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Atur teks pesan motivasi dan gambar popup yang muncul di dashboard pemagang saat menekan tombol check-in.
                </p>
            </div>
        </div>

        <!-- Alert Notifikasi -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    <span class="text-xs sm:text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-xl flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                    <span class="text-xs sm:text-sm font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-xl shadow-xs">
                <p class="font-bold flex items-center gap-2 mb-1 text-xs sm:text-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Perhatian:
                </p>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.pengaturan.checkin-message.update') }}" method="POST" enctype="multipart/form-data" id="checkinMessageForm">
            @csrf

            <!-- 2 TAB NAVIGATION -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <div class="flex border-b border-gray-100 bg-gray-50/70 p-2 gap-2">
                    <!-- Tab Button 1: Hadir Tepat Waktu -->
                    <button type="button" id="tabBtnOnTime" onclick="switchTab('on_time')" 
                        class="flex-1 flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl font-bold text-xs sm:text-sm transition-all duration-200 bg-emerald-600 text-white shadow-xs">
                        <span class="w-6 h-6 rounded-full bg-white/20 text-white flex items-center justify-center text-xs">
                            <i class="fa-solid fa-check"></i>
                        </span>
                        <span>Hadir Tepat Waktu</span>
                        @if(isset($messages['on_time']) && $messages['on_time']->is_active)
                            <span class="w-2 h-2 rounded-full bg-white animate-pulse" title="Popup Aktif"></span>
                        @endif
                    </button>

                    <!-- Tab Button 2: Terlambat -->
                    <button type="button" id="tabBtnLate" onclick="switchTab('late')" 
                        class="flex-1 flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl font-semibold text-xs sm:text-sm transition-all duration-200 text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                        <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-clock"></i>
                        </span>
                        <span>Terlambat</span>
                        @if(isset($messages['late']) && $messages['late']->is_active)
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse" title="Popup Aktif"></span>
                        @endif
                    </button>
                </div>

                <!-- TAB PANE 1: TEPAT WAKTU -->
                <div id="tabPaneOnTime" class="p-6 space-y-6">
                    <!-- Header Info & Toggle Switch -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl bg-emerald-50/50 border border-emerald-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-emerald-950">Popup Hadir Tepat Waktu</h3>
                                <p class="text-xs text-emerald-700">Tampil otomatis di dashboard saat pemagang check-in tepat waktu atau dalam toleransi jadwal shift.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-semibold text-gray-600">Status Popup:</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="on_time_is_active" value="1" class="sr-only peer"
                                    {{ isset($messages['on_time']) && $messages['on_time']->is_active ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-400 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Input Teks Pesan -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="on_time_message" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                                Teks Pesan Motivasi <span class="text-rose-500">*</span>
                            </label>
                            <span id="charCountOnTime" class="text-xs text-gray-400">0 / 500</span>
                        </div>
                        <textarea name="on_time_message" id="on_time_message" rows="3" maxlength="500" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm leading-relaxed"
                            placeholder="Tuliskan pesan motivasi atau ucapan selamat datang...">{{ $messages['on_time']->message ?? 'Selamat datang! Anda tepat waktu hari ini 🎉' }}</textarea>
                        <p class="text-[11px] text-gray-400 mt-1">Dukung emoji (🎉, 🚀, Semangat!). Maksimal 500 karakter.</p>
                    </div>

                    <!-- AREA KELOLA GAMBAR TEPAT WAKTU -->
                    <div class="border-t border-gray-100 pt-5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">
                            Kelola Gambar Popup <span class="text-gray-400 font-normal normal-case">(Opsional)</span>
                        </label>

                        @php
                            $onTimeImage = $messages['on_time']->image ?? null;
                            $hasOnTimeImage = $onTimeImage && file_exists(public_path('checkin-images/' . $onTimeImage));
                        @endphp

                        <!-- Hidden input untuk indikasi hapus gambar -->
                        <input type="hidden" name="remove_on_time_image" id="remove_on_time_image" value="0">

                        <!-- Section: Gambar Saat Ini (jika ada) -->
                        <div id="existingOnTimeImageWrapper" class="{{ $hasOnTimeImage ? '' : 'hidden' }} mb-4 p-4 rounded-xl border border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="relative group">
                                    <img id="imgOnTimeCurrent" src="{{ $hasOnTimeImage ? asset('checkin-images/' . $onTimeImage) : '' }}" 
                                        alt="Gambar Tepat Waktu Saat Ini" 
                                        class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-xl border border-gray-200 shadow-sm bg-white">
                                    <span class="absolute bottom-1 right-1 bg-emerald-600 text-white text-[10px] px-1.5 py-0.5 rounded font-bold">Aktif</span>
                                </div>
                                <div class="space-y-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check text-[10px]"></i> Gambar Saat Ini
                                    </span>
                                    <p class="text-xs text-gray-600 font-medium truncate max-w-xs">{{ $onTimeImage }}</p>
                                    <p class="text-[11px] text-gray-400">Gambar ini tampil di popup pemagang saat ini.</p>
                                </div>
                            </div>
                            <div>
                                <button type="button" onclick="markImageForRemoval('on_time')" 
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition">
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span>Hapus Gambar Ini</span>
                                </button>
                            </div>
                        </div>

                        <!-- Banner Peringatan Hapus Gambar (muncul saat admin klik hapus) -->
                        <div id="removalAlertOnTime" class="hidden mb-4 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-trash-can text-rose-600"></i>
                                <span><strong>Gambar ditandai untuk dihapus.</strong> Perubahan akan diterapkan permanen saat Anda menekan tombol <strong>Simpan Perubahan</strong> di bawah.</span>
                            </div>
                            <button type="button" onclick="cancelImageRemoval('on_time')" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-xs font-semibold shadow-xs transition ml-3 flex-shrink-0">
                                Batalkan Hapus
                            </button>
                        </div>

                        <!-- Dropzone Upload Gambar Baru -->
                        <div class="space-y-2">
                            <div class="relative border-2 border-dashed border-gray-300 hover:border-emerald-500 rounded-2xl p-5 text-center transition bg-white cursor-pointer group">
                                <input type="file" name="on_time_image" id="input_on_time_image" accept="image/jpeg,image/png,image/jpg,image/gif"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                    onchange="handleNewFileSelected(this, 'on_time')">
                                <div class="flex flex-col items-center justify-center space-y-1.5 pointer-events-none">
                                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-700">Klik untuk memilih gambar atau seret file ke sini</p>
                                    <p class="text-xs text-gray-400">Format: JPG, PNG, GIF (Maks. 2MB). Disarankan foto persegi (1:1).</p>
                                </div>
                            </div>

                            <!-- Preview File Baru yang Dipilih -->
                            <div id="newFilePreviewOnTime" class="hidden p-3 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img id="newImgThumbOnTime" src="" alt="Thumbnail Baru" class="w-12 h-12 rounded-lg object-cover border border-emerald-300 bg-white">
                                    <div>
                                        <p class="text-xs font-bold text-emerald-900" id="newFileNameOnTime">file.jpg</p>
                                        <p class="text-[11px] text-emerald-700" id="newFileSizeOnTime">0 KB (Siap diunggah)</p>
                                    </div>
                                </div>
                                <button type="button" onclick="cancelNewFileSelection('on_time')" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-xs transition">
                                    <i class="fa-solid fa-xmark mr-1"></i>Batal Pilih
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- LIVE PREVIEW POPUP HADIR TEPAT WAKTU -->
                    <div class="border-t border-gray-100 pt-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-700 mb-3 flex items-center gap-1.5">
                            <i class="fa-solid fa-eye text-emerald-600"></i>
                            Simulasi Tampilan Popup di Pemagang:
                        </p>
                        <div class="bg-gray-100/80 p-6 rounded-2xl flex items-center justify-center">
                            <!-- Card Popup Mockup -->
                            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center space-y-3.5 border border-gray-100">
                                <!-- Image Preview Container -->
                                <div id="previewCardImageOnTime">
                                    @if($hasOnTimeImage)
                                        <img src="{{ asset('checkin-images/' . $onTimeImage) }}" alt="Preview" 
                                            class="w-32 h-32 mx-auto rounded-2xl object-cover shadow-sm border border-gray-100 mb-2">
                                    @endif
                                </div>
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                    <span>Hadir Tepat Waktu</span>
                                </div>
                                <p id="previewTextOnTime" class="text-sm text-gray-700 leading-relaxed font-medium">
                                    {{ $messages['on_time']->message ?? 'Selamat datang! Anda tepat waktu hari ini 🎉' }}
                                </p>
                                <button type="button" class="w-full py-2.5 px-4 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition shadow-sm pointer-events-none">
                                    Oke, Siap Bekerja
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB PANE 2: TERLAMBAT -->
                <div id="tabPaneLate" class="hidden p-6 space-y-6">
                    <!-- Header Info & Toggle Switch -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl bg-amber-50/60 border border-amber-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-amber-950">Popup Hadir Terlambat</h3>
                                <p class="text-xs text-amber-800">Tampil otomatis saat pemagang check-in melewati toleransi jam masuk shift.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-semibold text-gray-600">Status Popup:</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="late_is_active" value="1" class="sr-only peer"
                                    {{ isset($messages['late']) && $messages['late']->is_active ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-amber-400 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Input Teks Pesan -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="late_message" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                                Teks Pesan Pengingat <span class="text-rose-500">*</span>
                            </label>
                            <span id="charCountLate" class="text-xs text-gray-400">0 / 500</span>
                        </div>
                        <textarea name="late_message" id="late_message" rows="3" maxlength="500" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none text-sm leading-relaxed"
                            placeholder="Tuliskan pesan pengingat keterlambatan...">{{ $messages['late']->message ?? 'Anda terlambat hari ini. Harap datang tepat waktu!' }}</textarea>
                        <p class="text-[11px] text-gray-400 mt-1">Dukung emoji (⚠️, ⏰, Semangat!). Maksimal 500 karakter.</p>
                    </div>

                    <!-- AREA KELOLA GAMBAR TERLAMBAT -->
                    <div class="border-t border-gray-100 pt-5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">
                            Kelola Gambar Popup <span class="text-gray-400 font-normal normal-case">(Opsional)</span>
                        </label>

                        @php
                            $lateImage = $messages['late']->image ?? null;
                            $hasLateImage = $lateImage && file_exists(public_path('checkin-images/' . $lateImage));
                        @endphp

                        <!-- Hidden input untuk indikasi hapus gambar -->
                        <input type="hidden" name="remove_late_image" id="remove_late_image" value="0">

                        <!-- Section: Gambar Saat Ini (jika ada) -->
                        <div id="existingLateImageWrapper" class="{{ $hasLateImage ? '' : 'hidden' }} mb-4 p-4 rounded-xl border border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="relative group">
                                    <img id="imgLateCurrent" src="{{ $hasLateImage ? asset('checkin-images/' . $lateImage) : '' }}" 
                                        alt="Gambar Terlambat Saat Ini" 
                                        class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-xl border border-gray-200 shadow-sm bg-white">
                                    <span class="absolute bottom-1 right-1 bg-amber-500 text-white text-[10px] px-1.5 py-0.5 rounded font-bold">Aktif</span>
                                </div>
                                <div class="space-y-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-clock text-[10px]"></i> Gambar Saat Ini
                                    </span>
                                    <p class="text-xs text-gray-600 font-medium truncate max-w-xs">{{ $lateImage }}</p>
                                    <p class="text-[11px] text-gray-400">Gambar ini tampil di popup pemagang terlambat saat ini.</p>
                                </div>
                            </div>
                            <div>
                                <button type="button" onclick="markImageForRemoval('late')" 
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition">
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span>Hapus Gambar Ini</span>
                                </button>
                            </div>
                        </div>

                        <!-- Banner Peringatan Hapus Gambar -->
                        <div id="removalAlertLate" class="hidden mb-4 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-trash-can text-rose-600"></i>
                                <span><strong>Gambar ditandai untuk dihapus.</strong> Perubahan akan diterapkan permanen saat Anda menekan tombol <strong>Simpan Perubahan</strong> di bawah.</span>
                            </div>
                            <button type="button" onclick="cancelImageRemoval('late')" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-xs font-semibold shadow-xs transition ml-3 flex-shrink-0">
                                Batalkan Hapus
                            </button>
                        </div>

                        <!-- Dropzone Upload Gambar Baru -->
                        <div class="space-y-2">
                            <div class="relative border-2 border-dashed border-gray-300 hover:border-amber-500 rounded-2xl p-5 text-center transition bg-white cursor-pointer group">
                                <input type="file" name="late_image" id="input_late_image" accept="image/jpeg,image/png,image/jpg,image/gif"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                    onchange="handleNewFileSelected(this, 'late')">
                                <div class="flex flex-col items-center justify-center space-y-1.5 pointer-events-none">
                                    <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-700">Klik untuk memilih gambar atau seret file ke sini</p>
                                    <p class="text-xs text-gray-400">Format: JPG, PNG, GIF (Maks. 2MB). Disarankan foto persegi (1:1).</p>
                                </div>
                            </div>

                            <!-- Preview File Baru yang Dipilih -->
                            <div id="newFilePreviewLate" class="hidden p-3 bg-amber-50 rounded-xl border border-amber-200 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img id="newImgThumbLate" src="" alt="Thumbnail Baru" class="w-12 h-12 rounded-lg object-cover border border-amber-300 bg-white">
                                    <div>
                                        <p class="text-xs font-bold text-amber-900" id="newFileNameLate">file.jpg</p>
                                        <p class="text-[11px] text-amber-700" id="newFileSizeLate">0 KB (Siap diunggah)</p>
                                    </div>
                                </div>
                                <button type="button" onclick="cancelNewFileSelection('late')" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-xs transition">
                                    <i class="fa-solid fa-xmark mr-1"></i>Batal Pilih
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- LIVE PREVIEW POPUP TERLAMBAT -->
                    <div class="border-t border-gray-100 pt-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-700 mb-3 flex items-center gap-1.5">
                            <i class="fa-solid fa-eye text-amber-600"></i>
                            Simulasi Tampilan Popup di Pemagang:
                        </p>
                        <div class="bg-gray-100/80 p-6 rounded-2xl flex items-center justify-center">
                            <!-- Card Popup Mockup -->
                            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center space-y-3.5 border border-gray-100">
                                <!-- Image Preview Container -->
                                <div id="previewCardImageLate">
                                    @if($hasLateImage)
                                        <img src="{{ asset('checkin-images/' . $lateImage) }}" alt="Preview" 
                                            class="w-32 h-32 mx-auto rounded-2xl object-cover shadow-sm border border-gray-100 mb-2">
                                    @endif
                                </div>
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                    <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                                    <span>Hadir Terlambat</span>
                                </div>
                                <p id="previewTextLate" class="text-sm text-gray-700 leading-relaxed font-medium">
                                    {{ $messages['late']->message ?? 'Anda terlambat hari ini. Harap datang tepat waktu!' }}
                                </p>
                                <button type="button" class="w-full py-2.5 px-4 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 transition shadow-sm pointer-events-none">
                                    Saya Mengerti
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM SAVE ACTION BAR -->
                <div class="p-5 border-t border-gray-100 bg-gray-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <p class="text-xs text-gray-500 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-info text-blue-500"></i>
                        Perubahan pada kedua tab akan disimpan secara bersamaan.
                    </p>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-7 py-2.5 rounded-xl shadow-xs transition">
                        <i class="fa-solid fa-floppy-disk text-sm"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
    // Tab switching logic
    function switchTab(tab) {
        const tabBtnOnTime = document.getElementById('tabBtnOnTime');
        const tabBtnLate = document.getElementById('tabBtnLate');
        const tabPaneOnTime = document.getElementById('tabPaneOnTime');
        const tabPaneLate = document.getElementById('tabPaneLate');

        if (tab === 'on_time') {
            tabPaneOnTime.classList.remove('hidden');
            tabPaneLate.classList.add('hidden');

            tabBtnOnTime.className = "flex-1 flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl font-bold text-xs sm:text-sm transition-all duration-200 bg-emerald-600 text-white shadow-xs";
            tabBtnLate.className = "flex-1 flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl font-semibold text-xs sm:text-sm transition-all duration-200 text-gray-600 hover:text-gray-900 hover:bg-gray-100";
        } else {
            tabPaneOnTime.classList.add('hidden');
            tabPaneLate.classList.remove('hidden');

            tabBtnOnTime.className = "flex-1 flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl font-semibold text-xs sm:text-sm transition-all duration-200 text-gray-600 hover:text-gray-900 hover:bg-gray-100";
            tabBtnLate.className = "flex-1 flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl font-bold text-xs sm:text-sm transition-all duration-200 bg-amber-500 text-white shadow-xs";
        }
    }

    // Logic Hapus Gambar
    function markImageForRemoval(type) {
        document.getElementById(`remove_${type}_image`).value = '1';
        document.getElementById(`existing${capitalize(type)}ImageWrapper`).classList.add('opacity-40', 'pointer-events-none');
        document.getElementById(`removalAlert${capitalize(type)}`).classList.remove('hidden');

        // Kosongkan preview gambar di simulasi
        const previewContainer = document.getElementById(`previewCardImage${capitalize(type)}`);
        previewContainer.innerHTML = '';
    }

    function cancelImageRemoval(type) {
        document.getElementById(`remove_${type}_image`).value = '0';
        document.getElementById(`existing${capitalize(type)}ImageWrapper`).classList.remove('opacity-40', 'pointer-events-none');
        document.getElementById(`removalAlert${capitalize(type)}`).classList.add('hidden');

        // Kembalikan gambar ke simulasi
        const currentImg = document.getElementById(`img${capitalize(type)}Current`);
        if (currentImg && currentImg.src) {
            const previewContainer = document.getElementById(`previewCardImage${capitalize(type)}`);
            previewContainer.innerHTML = `<img src="${currentImg.src}" alt="Preview" class="w-32 h-32 mx-auto rounded-2xl object-cover shadow-sm border border-gray-100 mb-2">`;
        }
    }

    // Logic Pemilihan File Baru
    function handleNewFileSelected(input, type) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                // Tampilkan thumbnail & info file baru
                document.getElementById(`newImgThumb${capitalize(type)}`).src = e.target.result;
                document.getElementById(`newFileName${capitalize(type)}`).textContent = file.name;
                document.getElementById(`newFileSize${capitalize(type)}`).textContent = `${Math.round(file.size / 1024)} KB (Siap diunggah)`;
                document.getElementById(`newFilePreview${capitalize(type)}`).classList.remove('hidden');

                // Update gambar simulasi mockup
                const previewContainer = document.getElementById(`previewCardImage${capitalize(type)}`);
                previewContainer.innerHTML = `<img src="${e.target.result}" alt="Preview" class="w-32 h-32 mx-auto rounded-2xl object-cover shadow-sm border border-gray-100 mb-2 animate-fadeIn">`;
            };

            reader.readAsDataURL(file);
        }
    }

    function cancelNewFileSelection(type) {
        const input = document.getElementById(`input_${type}_image`);
        input.value = '';
        document.getElementById(`newFilePreview${capitalize(type)}`).classList.add('hidden');

        // Kembalikan ke gambar saat ini jika ada dan belum dihapus
        const removeFlag = document.getElementById(`remove_${type}_image`).value;
        const currentImg = document.getElementById(`img${capitalize(type)}Current`);
        const previewContainer = document.getElementById(`previewCardImage${capitalize(type)}`);

        if (removeFlag === '0' && currentImg && currentImg.src) {
            previewContainer.innerHTML = `<img src="${currentImg.src}" alt="Preview" class="w-32 h-32 mx-auto rounded-2xl object-cover shadow-sm border border-gray-100 mb-2">`;
        } else {
            previewContainer.innerHTML = '';
        }
    }

    function capitalize(str) {
        if (str === 'on_time') return 'OnTime';
        if (str === 'late') return 'Late';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Inisialisasi Karakter Counter & Live Text Preview
    document.addEventListener('DOMContentLoaded', function() {
        const onTimeInput = document.getElementById('on_time_message');
        const lateInput = document.getElementById('late_message');
        const countOnTime = document.getElementById('charCountOnTime');
        const countLate = document.getElementById('charCountLate');
        const previewTextOnTime = document.getElementById('previewTextOnTime');
        const previewTextLate = document.getElementById('previewTextLate');

        function updateCounter(input, countEl, previewEl) {
            if (!input) return;
            const len = input.value.length;
            if (countEl) countEl.textContent = `${len} / 500`;
            if (previewEl) previewEl.textContent = input.value || '-';
        }

        if (onTimeInput) {
            updateCounter(onTimeInput, countOnTime, previewTextOnTime);
            onTimeInput.addEventListener('input', () => updateCounter(onTimeInput, countOnTime, previewTextOnTime));
        }

        if (lateInput) {
            updateCounter(lateInput, countLate, previewTextLate);
            lateInput.addEventListener('input', () => updateCounter(lateInput, countLate, previewTextLate));
        }
    });
</script>
@endsection