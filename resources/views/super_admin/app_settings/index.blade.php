@extends('layouts.main')

@section('contents')
<div class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-h-screen bg-slate-50 text-slate-800 min-w-0">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- HEADER SECTION -->
        <div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl shadow-xl p-6 md:p-8 text-white overflow-hidden border border-slate-800">
            <!-- Decorative Background Glow -->
            <div class="absolute -right-10 -top-10 w-48 h-48 bg-indigo-500/20 rounded-full blur-3xl"></div>
            <div class="absolute -left-10 -bottom-10 w-48 h-48 bg-blue-500/20 rounded-full blur-3xl"></div>

            <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded-full text-xs font-semibold uppercase tracking-wider">
                        <i class="fa-solid fa-crown text-amber-400"></i>
                        <span>Super Admin Area</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white flex items-center gap-2.5">
                        <i class="fa-solid fa-palette text-indigo-400"></i>
                        Pengaturan Tampilan Aplikasi & Website
                    </h1>
                    <p class="text-slate-300 text-xs md:text-sm max-w-2xl">
                        Kustomisasi identitas visual aplikasi mulai dari Logo, Favicon browser, Banner beranda pemagang, hingga Background halaman login.
                    </p>
                </div>
            </div>
        </div>

        <!-- ALERTS -->
        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl shadow-sm flex items-center gap-3 text-sm animate-fade-in">
            <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                <i class="fa-solid fa-check"></i>
            </div>
            <div>
                <span class="font-bold">Berhasil:</span> {{ session('success') }}
            </div>
        </div>
        @endif

        @if(session('error') || (isset($errors) && $errors->any()))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl shadow-sm space-y-1 text-sm animate-fade-in">
            <div class="flex items-center gap-2 font-bold">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                <span>Terjadi Kesalahan:</span>
            </div>
            <ul class="list-disc list-inside text-xs pl-2 space-y-0.5 text-rose-700">
                @if(session('error')) <li>{{ session('error') }}</li> @endif
                @if(isset($errors))
                    @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                @endif
            </ul>
        </div>
        @endif

        <!-- MAIN FORM -->
        <form action="{{ route('super-admin.app-settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- CARD: NAMA APLIKASI -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 md:p-8 space-y-4">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold">
                            <i class="fa-solid fa-font"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Nama & Judul Aplikasi</h2>
                            <p class="text-xs text-slate-500">Nama brand atau sistem yang muncul pada judul browser dan metadata sistem.</p>
                        </div>
                    </div>
                </div>

                <div class="max-w-xl">
                    <label for="app_name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Nama Aplikasi
                    </label>
                    <input type="text" id="app_name" name="app_name" value="{{ old('app_name', $appSetting->app_name ?? 'Absen Djuragan') }}" placeholder="Contoh: Absen Djuragan" class="w-full bg-slate-50 border border-slate-300 rounded-2xl px-4 py-3 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>
            </div>

            <!-- GRID 2 KOLOM: LOGO & FAVICON -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- 1. PENGATURAN LOGO -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 space-y-4 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-800">Logo Aplikasi</h2>
                                    <p class="text-xs text-slate-500">Ditampilkan pada Sidebar Admin & Pemagang, serta Halaman Login.</p>
                                </div>
                            </div>
                            @if($appSetting->logo)
                            <button type="button" onclick="confirmReset('logo', 'Logo Aplikasi')" class="px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl transition flex items-center gap-1.5" title="Kembalikan ke Logo Bawaan">
                                <i class="fa-solid fa-rotate-left"></i>
                                <span>Reset Default</span>
                            </button>
                            @endif
                        </div>

                        <!-- Preview Container -->
                        <div class="space-y-2">
                            <div class="text-xs font-bold text-slate-600">Pratinjau Saat Ini:</div>
                            <div class="grid grid-cols-2 gap-3">
                                <!-- Latar Gelap (Simulasi Sidebar) -->
                                <div class="bg-gray-900 rounded-2xl p-4 flex flex-col items-center justify-center text-center border border-gray-800 min-h-[110px] relative group">
                                    <span class="absolute top-2 left-3 text-[10px] uppercase font-bold text-gray-400">Sidebar (Gelap)</span>
                                    <img id="logo-preview-dark" src="{{ $appSetting->logo_url }}" alt="Logo Dark Preview" class="max-h-12 max-w-full object-contain transition-transform group-hover:scale-105">
                                </div>
                                <!-- Latar Terang -->
                                <div class="bg-slate-100 rounded-2xl p-4 flex flex-col items-center justify-center text-center border border-slate-200 min-h-[110px] relative group">
                                    <span class="absolute top-2 left-3 text-[10px] uppercase font-bold text-slate-400">Latar Terang</span>
                                    <img id="logo-preview-light" src="{{ $appSetting->logo_url }}" alt="Logo Light Preview" class="max-h-12 max-w-full object-contain transition-transform group-hover:scale-105">
                                </div>
                            </div>
                        </div>

                        <!-- Upload Input -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                                Unggah File Logo Baru
                            </label>
                            <div class="relative border-2 border-dashed border-slate-300 hover:border-indigo-500 rounded-2xl p-4 text-center cursor-pointer transition bg-slate-50/60 hover:bg-indigo-50/20" onclick="document.getElementById('input_logo').click()">
                                <input type="file" id="input_logo" name="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp,image/x-icon" class="hidden" onchange="previewImage(this, ['logo-preview-dark', 'logo-preview-light'], 'logo-filename')">
                                <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-400 mb-1 block"></i>
                                <span id="logo-filename" class="text-xs font-semibold text-slate-700 block">Pilih file logo atau klik di sini</span>
                                <span class="text-[11px] text-slate-400 block mt-0.5">Format: SVG, PNG transparan, JPG, WEBP (Maks: 4 MB)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. PENGATURAN FAVICON -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 space-y-4 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold">
                                    <i class="fa-solid fa-globe"></i>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-800">Favicon Browser</h2>
                                    <p class="text-xs text-slate-500">Ikon kecil yang muncul di tab browser pengguna.</p>
                                </div>
                            </div>
                            @if($appSetting->favicon)
                            <button type="button" onclick="confirmReset('favicon', 'Favicon Tab Browser')" class="px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl transition flex items-center gap-1.5" title="Kembalikan ke Favicon Bawaan">
                                <i class="fa-solid fa-rotate-left"></i>
                                <span>Reset Default</span>
                            </button>
                            @endif
                        </div>

                        <!-- Preview Mockup Tab Browser -->
                        <div class="space-y-2">
                            <div class="text-xs font-bold text-slate-600">Simulasi Tab Browser:</div>
                            <div class="bg-slate-200 rounded-2xl p-2.5 border border-slate-300/80 shadow-inner">
                                <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 bg-white rounded-xl shadow-xs border border-slate-200 max-w-full">
                                    <img id="favicon-preview" src="{{ $appSetting->favicon_url }}" alt="Favicon Preview" class="w-4 h-4 object-contain rounded-xs shrink-0">
                                    <span class="text-xs font-semibold text-slate-800 truncate max-w-[180px]">{{ $appSetting->app_name ?? 'Absen Djuragan' }} - Presensi</span>
                                    <i class="fa-solid fa-xmark text-[10px] text-slate-400 hover:text-slate-600 cursor-pointer ml-2"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Input -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                                Unggah File Favicon Baru
                            </label>
                            <div class="relative border-2 border-dashed border-slate-300 hover:border-amber-500 rounded-2xl p-4 text-center cursor-pointer transition bg-slate-50/60 hover:bg-amber-50/20" onclick="document.getElementById('input_favicon').click()">
                                <input type="file" id="input_favicon" name="favicon" accept=".ico,image/png,image/jpeg,image/svg+xml" class="hidden" onchange="previewImage(this, ['favicon-preview'], 'favicon-filename')">
                                <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-400 mb-1 block"></i>
                                <span id="favicon-filename" class="text-xs font-semibold text-slate-700 block">Pilih file favicon (.ico / .png) atau klik di sini</span>
                                <span class="text-[11px] text-slate-400 block mt-0.5">Format: ICO, PNG, SVG (Ukuran optimal 32x32 atau 64x64 px, Maks: 2 MB)</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- GRID 2 KOLOM: BACKGROUND LOGIN & INFORMASI TAMBAHAN -->
            <div class="grid grid-cols-1 gap-6">

                <!-- 3. PENGATURAN BACKGROUND HALAMAN LOGIN -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 space-y-4">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg font-bold">
                                <i class="fa-solid fa-right-to-bracket"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-800">Background Halaman Login</h2>
                                <p class="text-xs text-slate-500">Gambar latar belakang pada sisi panel login aplikasi.</p>
                            </div>
                        </div>
                        @if($appSetting->login_background)
                        <button type="button" onclick="confirmReset('login_background', 'Background Login')" class="px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl transition flex items-center gap-1.5" title="Kembalikan ke Background Default">
                            <i class="fa-solid fa-rotate-left"></i>
                            <span>Reset Default</span>
                        </button>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                        <!-- Preview Mockup Login Background -->
                        <div class="space-y-2">
                            <div class="text-xs font-bold text-slate-600">Pratinjau Panel Login:</div>
                            <div class="relative h-36 w-full rounded-2xl overflow-hidden border border-slate-200 shadow-xs bg-gray-900 flex items-center justify-center">
                                @if($appSetting->login_background_url)
                                <img id="login-bg-preview" src="{{ $appSetting->login_background_url }}" alt="Login BG Preview" class="absolute inset-0 w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]"></div>
                                @else
                                <div id="login-bg-preview-placeholder" class="absolute inset-0 bg-gradient-to-br from-gray-900 via-slate-800 to-indigo-950"></div>
                                <img id="login-bg-preview" src="" alt="Login BG Preview" class="hidden absolute inset-0 w-full h-full object-cover">
                                @endif
                                <div class="relative z-10 flex flex-col items-center gap-1.5 p-3 text-center">
                                    <img src="{{ $appSetting->logo_url }}" alt="Logo" class="max-h-8 object-contain drop-shadow">
                                    <span class="text-[11px] font-medium text-white/90">Panel Kiri Halaman Login</span>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Input -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                                Unggah Background Baru
                            </label>
                            <div class="relative border-2 border-dashed border-slate-300 hover:border-purple-500 rounded-2xl p-5 text-center cursor-pointer transition bg-slate-50/60 hover:bg-purple-50/20" onclick="document.getElementById('input_login_bg').click()">
                                <input type="file" id="input_login_bg" name="login_background" accept="image/png,image/jpeg,image/webp" class="hidden" onchange="previewImage(this, ['login-bg-preview'], 'login-bg-filename', true)">
                                <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-400 mb-1 block"></i>
                                <span id="login-bg-filename" class="text-xs font-semibold text-slate-700 block">Pilih gambar background login atau klik di sini</span>
                                <span class="text-[11px] text-slate-400 block mt-0.5">Format: JPG, PNG, WEBP (Resolusi rekomendasi: 1200x1200 px atau lanskap, Maks: 6 MB)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. PENGATURAN BANNER HEADER PEMAGANG & SLIDES CAROUSEL -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 md:p-8 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">
                                <i class="fa-solid fa-panorama"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-800">Banner Header Pemagang & Carousel Slide</h2>
                                <p class="text-xs text-slate-500">Banner beranda pemagang yang bergeser secara otomatis (slide bergantian).</p>
                            </div>
                        </div>
                        @if($appSetting->intern_banner)
                        <button type="button" onclick="confirmReset('intern_banner', 'Banner Header Pemagang')" class="self-start sm:self-center px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl transition flex items-center gap-1.5" title="Kembalikan ke Banner Bawaan">
                            <i class="fa-solid fa-rotate-left"></i>
                            <span>Reset Banner Utama</span>
                        </button>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                        <!-- Kolom Kiri: Banner Utama -->
                        <div class="space-y-4 p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                    <i class="fa-solid fa-star text-amber-500"></i> Banner Utama
                                </span>
                                <span class="text-[10px] text-slate-400">Default Slide #1</span>
                            </div>

                            <!-- Preview Mockup Banner Utama -->
                            <div class="relative h-32 w-full rounded-2xl overflow-hidden border border-slate-200 shadow-xs bg-slate-900">
                                <img id="banner-preview" src="{{ $appSetting->intern_banner_url }}" alt="Banner Preview" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent flex items-end p-3">
                                    <div class="flex items-center gap-2 text-white">
                                        <div class="w-6 h-6 rounded-full bg-white/20 backdrop-blur-xs flex items-center justify-center text-xs">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <div class="text-[11px] font-semibold">Beranda Pemagang (Header Area)</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload Banner Utama -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                                    Ganti Banner Utama
                                </label>
                                <div class="relative border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-2xl p-4 text-center cursor-pointer transition bg-white hover:bg-emerald-50/20" onclick="document.getElementById('input_banner').click()">
                                    <input type="file" id="input_banner" name="intern_banner" accept="image/png,image/jpeg,image/webp" class="hidden" onchange="previewImage(this, ['banner-preview'], 'banner-filename')">
                                    <i class="fa-solid fa-cloud-arrow-up text-xl text-slate-400 mb-1 block"></i>
                                    <span id="banner-filename" class="text-xs font-semibold text-slate-700 block">Pilih gambar banner utama atau klik di sini</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">Format: JPG, PNG, WEBP (Maks: 6 MB)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Slide Carousel Tambahan -->
                        <div class="space-y-4 p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                    <i class="fa-solid fa-images text-emerald-600"></i> Slide Tambahan (Carousel)
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                                    {{ !empty($appSetting->banner_slides) ? count($appSetting->banner_slides) : 0 }} Slide
                                </span>
                            </div>

                            @if(!empty($appSetting->banner_slides) && is_array($appSetting->banner_slides) && count($appSetting->banner_slides) > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-48 overflow-y-auto p-1">
                                @foreach($appSetting->banner_slides as $slideIdx => $slideItem)
                                @if($slideItem && file_exists(public_path('uploads/app-settings/' . $slideItem)))
                                <div class="relative group rounded-xl overflow-hidden border border-slate-200 shadow-2xs aspect-video bg-slate-900">
                                    <img src="{{ asset('uploads/app-settings/' . $slideItem) }}" alt="Slide {{ $slideIdx + 1 }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                        <button type="button" onclick="confirmDeleteSlide('{{ $slideIdx }}')" class="p-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs shadow-sm transition" title="Hapus Slide Ini">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                    <span class="absolute bottom-1 left-1.5 px-1.5 py-0.5 bg-black/70 text-white text-[9px] font-bold rounded">
                                        Slide #{{ $slideIdx + 2 }}
                                    </span>
                                </div>
                                @endif
                                @endforeach
                            </div>
                            @else
                            <div class="p-3.5 bg-white rounded-xl border border-dashed border-slate-200 text-center text-xs text-slate-400">
                                <i class="fa-solid fa-image text-slate-300 text-base mb-1 block"></i>
                                Belum ada slide tambahan. Unggah di bawah untuk mengaktifkan slider carousel.
                            </div>
                            @endif

                            <!-- Upload Multi Slide Input -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                                    + Tambah Slide Baru
                                </label>
                                <div class="relative border-2 border-dashed border-emerald-300 hover:border-emerald-500 rounded-2xl p-4 text-center cursor-pointer transition bg-white hover:bg-emerald-50/30" onclick="document.getElementById('input_banner_slides').click()">
                                    <input type="file" id="input_banner_slides" name="banner_slides[]" accept="image/png,image/jpeg,image/webp" multiple class="hidden" onchange="handleMultipleSlidesPreview(this)">
                                    <i class="fa-solid fa-layer-group text-xl text-emerald-600 mb-1 block"></i>
                                    <span id="banner-slides-filename" class="text-xs font-semibold text-emerald-800 block">Klik untuk memilih 1 atau beberapa gambar slide</span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">Format: JPG, PNG, WEBP (Maks: 6 MB per file)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ACTION BAR BUTTON -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <i class="fa-solid fa-circle-info text-indigo-500 text-sm"></i>
                    <span>Perubahan akan langsung diterapkan ke seluruh sistem setelah Anda menekan tombol simpan.</span>
                </div>
                <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold text-sm rounded-2xl shadow-lg shadow-indigo-600/30 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Seluruh Pengaturan</span>
                </button>
            </div>
        </form>

        <!-- HIDDEN FORM FOR RESET & DELETE SLIDE -->
        <form id="reset-form" action="" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        <form id="delete-slide-form" action="" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>

    </div>
</div>

<!-- JAVASCRIPT FOR INTERACTIVE PREVIEW & SWEETALERT -->
<script>
    function handleMultipleSlidesPreview(input) {
        const fnEl = document.getElementById('banner-slides-filename');
        if (input.files && input.files.length > 0) {
            const count = input.files.length;
            if (fnEl) {
                fnEl.textContent = count + ' file slide dipilih untuk diunggah';
                fnEl.classList.add('text-emerald-700', 'font-bold');
            }
        }
    }

    function confirmDeleteSlide(slideIndex) {
        const deleteUrl = `{{ url('admin/super-admin/app-settings/banner-slides') }}/${slideIndex}`;
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Slide Banner?',
                text: 'Apakah Anda yakin ingin menghapus slide banner ini dari carousel?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus Slide',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-3xl shadow-2xl border border-slate-100',
                    confirmButton: 'rounded-xl px-4 py-2 font-bold text-sm',
                    cancelButton: 'rounded-xl px-4 py-2 font-semibold text-sm'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('delete-slide-form');
                    form.action = deleteUrl;
                    form.submit();
                }
            });
        } else {
            if (confirm('Apakah Anda yakin ingin menghapus slide banner ini dari carousel?')) {
                const form = document.getElementById('delete-slide-form');
                form.action = deleteUrl;
                form.submit();
            }
        }
    }

    function previewImage(input, previewElementIds, filenameElementId, isBackground = false) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                previewElementIds.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.src = e.target.result;
                        el.classList.remove('hidden');
                    }
                });

                if (isBackground) {
                    const placeholder = document.getElementById('login-bg-preview-placeholder');
                    if (placeholder) placeholder.classList.add('hidden');
                }
            };

            reader.readAsDataURL(file);

            const fnEl = document.getElementById(filenameElementId);
            if (fnEl) {
                fnEl.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
                fnEl.classList.add('text-indigo-600', 'font-bold');
            }
        }
    }

    function confirmReset(type, typeLabel) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Reset',
                text: `Apakah Anda yakin ingin mereset ${typeLabel} kembali ke aset bawaan (default)?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Kembalikan ke Default',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-3xl shadow-2xl border border-slate-100',
                    confirmButton: 'rounded-xl px-4 py-2 font-bold text-sm',
                    cancelButton: 'rounded-xl px-4 py-2 font-semibold text-sm'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const resetForm = document.getElementById('reset-form');
                    resetForm.action = `{{ url('admin/super-admin/app-settings/reset') }}/${type}`;
                    resetForm.submit();
                }
            });
        } else {
            if (confirm(`Apakah Anda yakin ingin mereset ${typeLabel} kembali ke bawaan (default)?`)) {
                const resetForm = document.getElementById('reset-form');
                resetForm.action = `{{ url('admin/super-admin/app-settings/reset') }}/${type}`;
                resetForm.submit();
            }
        }
    }
</script>
@endsection
