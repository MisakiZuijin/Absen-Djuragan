@extends('layouts.main')

@section('title', 'Pengaturan Popup Check-in')

@section('contents')
@include('layouts.sidebar')
@include('layouts.sidebar-pengaturan')
@include('layouts.navbar')

<!-- Main Content -->
<main class="ml-[32rem] mt-24 p-6 bg-gray-50 min-h-screen">

    <!-- Header Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Pengaturan Popup Check-in</h1>
        <p class="text-gray-600 leading-relaxed">
            Atur pesan dan gambar popup yang tampil saat pemagang melakukan check-in.
            Popup berbeda akan ditampilkan untuk yang tepat waktu dan yang terlambat.
        </p>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg" role="alert">
        <p class="font-bold">Sukses!</p>
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg" role="alert">
        <p class="font-bold">Gagal!</p>
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <form action="{{ route('admin.pengaturan.checkin-message.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="space-y-8">

            {{-- ============================================================ --}}
            {{-- SECTION: TEPAT WAKTU --}}
            {{-- ============================================================ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-emerald-50 border-b border-emerald-200 px-6 py-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center text-lg font-bold">
                        ✅
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-emerald-900">Popup Tepat Waktu</h3>
                        <p class="text-xs text-emerald-700">Tampil saat pemagang check-in tepat waktu atau dalam toleransi</p>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    {{-- Toggle Aktif --}}
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700">Tampilkan popup tepat waktu?</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="on_time_is_active" value="1"
                                class="sr-only peer"
                                {{ isset($messages['on_time']) && $messages['on_time']->is_active ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    {{-- Teks Pesan --}}
                    <div>
                        <label for="on_time_message" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Teks Pesan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="on_time_message" id="on_time_message" rows="3" maxlength="500"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm"
                            required>{{ $messages['on_time']->message ?? 'Selamat datang! Anda tepat waktu hari ini 🎉' }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">Maksimal 500 karakter. Emoji didukung.</p>
                    </div>

                    {{-- Gambar --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Gambar Popup <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>

                        @php
                        $onTimeImage = $messages['on_time']->image ?? null;
                        @endphp

                        @if($onTimeImage && file_exists(public_path('checkin-images/' . $onTimeImage)))
                        <div class="mb-3 relative inline-block">
                            <img src="{{ asset('checkin-images/' . $onTimeImage) }}" alt="Preview Tepat Waktu"
                                class="w-48 h-48 object-cover rounded-lg border border-gray-200 shadow-sm">
                            <label class="absolute -top-2 -right-2 flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" name="remove_on_time_image" value="1" class="sr-only peer">
                                <div class="w-7 h-7 bg-red-100 text-red-600 rounded-full flex items-center justify-center peer-checked:bg-red-600 peer-checked:text-white transition hover:bg-red-200">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </div>
                            </label>
                        </div>
                        <p class="text-xs text-amber-600 mb-2">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            Centang ikon hapus untuk menghapus gambar ini, lalu simpan.
                        </p>
                        @endif

                        <input type="file" name="on_time_image" accept="image/jpeg,image/png,image/jpg,image/gif"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, GIF. Maks 2MB.</p>
                    </div>

                    {{-- Preview --}}
                    <div class="border border-dashed border-emerald-300 rounded-xl p-4 bg-emerald-50/50">
                        <p class="text-xs font-semibold text-emerald-700 mb-2">
                            <i class="fa-solid fa-eye"></i> Preview Popup:
                        </p>
                        <div class="bg-white rounded-xl p-4 shadow-sm max-w-xs mx-auto text-center">
                            <div id="preview_on_time_image" class="mb-2">
                                @if($onTimeImage && file_exists(public_path('checkin-images/' . $onTimeImage)))
                                <img src="{{ asset('checkin-images/' . $onTimeImage) }}" alt="Preview" class="w-24 h-24 mx-auto rounded-xl object-cover">
                                @endif
                            </div>
                            <div class="text-sm font-bold text-emerald-600 mb-1">✅ Tepat Waktu</div>
                            <p id="preview_on_time_text" class="text-xs text-gray-600">{{ $messages['on_time']->message ?? 'Selamat datang! Anda tepat waktu hari ini 🎉' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- SECTION: TERLAMBAT --}}
            {{-- ============================================================ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-red-50 border-b border-red-200 px-6 py-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-600 text-white flex items-center justify-center text-lg font-bold">
                        ⚠️
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-red-900">Popup Terlambat</h3>
                        <p class="text-xs text-red-700">Tampil saat pemagang check-in melewati batas toleransi keterlambatan</p>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    {{-- Toggle Aktif --}}
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700">Tampilkan popup terlambat?</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="late_is_active" value="1"
                                class="sr-only peer"
                                {{ isset($messages['late']) && $messages['late']->is_active ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                        </label>
                    </div>

                    {{-- Teks Pesan --}}
                    <div>
                        <label for="late_message" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Teks Pesan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="late_message" id="late_message" rows="3" maxlength="500"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none text-sm"
                            required>{{ $messages['late']->message ?? 'Anda terlambat hari ini. Harap datang tepat waktu!' }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">Maksimal 500 karakter. Emoji didukung.</p>
                    </div>

                    {{-- Gambar --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Gambar Popup <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>

                        @php
                        $lateImage = $messages['late']->image ?? null;
                        @endphp

                        @if($lateImage && file_exists(public_path('checkin-images/' . $lateImage)))
                        <div class="mb-3 relative inline-block">
                            <img src="{{ asset('checkin-images/' . $lateImage) }}" alt="Preview Terlambat"
                                class="w-48 h-48 object-cover rounded-lg border border-gray-200 shadow-sm">
                            <label class="absolute -top-2 -right-2 flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" name="remove_late_image" value="1" class="sr-only peer">
                                <div class="w-7 h-7 bg-red-100 text-red-600 rounded-full flex items-center justify-center peer-checked:bg-red-600 peer-checked:text-white transition hover:bg-red-200">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </div>
                            </label>
                        </div>
                        <p class="text-xs text-amber-600 mb-2">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            Centang ikon hapus untuk menghapus gambar ini, lalu simpan.
                        </p>
                        @endif

                        <input type="file" name="late_image" accept="image/jpeg,image/png,image/jpg,image/gif"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                        <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, GIF. Maks 2MB.</p>
                    </div>

                    {{-- Preview --}}
                    <div class="border border-dashed border-red-300 rounded-xl p-4 bg-red-50/50">
                        <p class="text-xs font-semibold text-red-700 mb-2">
                            <i class="fa-solid fa-eye"></i> Preview Popup:
                        </p>
                        <div class="bg-white rounded-xl p-4 shadow-sm max-w-xs mx-auto text-center">
                            <div id="preview_late_image" class="mb-2">
                                @if($lateImage && file_exists(public_path('checkin-images/' . $lateImage)))
                                <img src="{{ asset('checkin-images/' . $lateImage) }}" alt="Preview" class="w-24 h-24 mx-auto rounded-xl object-cover">
                                @endif
                            </div>
                            <div class="text-sm font-bold text-red-600 mb-1">⚠️ Terlambat</div>
                            <p id="preview_late_text" class="text-xs text-gray-600">{{ $messages['late']->message ?? 'Anda terlambat hari ini. Harap datang tepat waktu!' }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Tombol Simpan --}}
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                <i class="fa-solid fa-circle-info"></i>
                Perubahan akan langsung berlaku untuk check-in berikutnya.
            </p>
            <button type="submit" class="bg-blue-600 text-white px-8 py-2.5 rounded-lg hover:bg-blue-700 transition font-medium shadow-sm">
                <i class="fa-solid fa-floppy-disk mr-2"></i>Simpan Perubahan
            </button>
        </div>
    </form>
</main>

{{-- JavaScript untuk live preview --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
            // Live preview untuk teks
            const onTimeTextarea = document.getElementById('on_time_message');
            const lateTextarea = document.getElementById('late_message');
            const previewOnTimeText = document.getElementById('preview_on_time_text');
            const previewLateText = document.getElementById('preview_late_text');

            if (onTimeTextarea && previewOnTimeText) {
                onTimeTextarea.addEventListener('input', function() {
                    previewOnTimeText.textContent = this.value;
                });
            }

            if (lateTextarea && previewLateText) {
                lateTextarea.addEventListener('input', function() {
                    previewLateText.textContent = this.value;
                });
            }

            // Preview untuk upload gambar baru
            const onTimeImageInput = document.querySelector('input[name="on_time_image"]');
            const lateImageInput = document.querySelector('input[name="late_image"]');
            const previewOnTimeImage = document.getElementById('preview_on_time_image');
            const previewLateImage = document.getElementById('preview_late_image');

            if (onTimeImageInput) {
                on < parameter name = "TimeImageInput" > < /parameter>.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        if (previewOnTimeImage) {
                            previewOnTimeImage.innerHTML = '<img src="' + ev.target.result + '" alt="Preview" class="w-24 h-24 mx-auto rounded-xl object-cover">';
                        }
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
    }

    if (lateImageInput) {
        lateImageInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    if (previewLateImage) {
                        previewLateImage.innerHTML = '<img src="' + ev.target.result + '" alt="Preview" class="w-24 h-24 mx-auto rounded-xl object-cover">';
                    }
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }
    });
</script>
@endsection