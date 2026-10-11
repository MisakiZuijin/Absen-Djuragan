@extends('layouts.main')

@section('title', 'Sunting Anggota')

@section('contents')
<!-- Main Content -->
<main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">

    <div class="flex space-x-6 border-b border-gray-300 pb-3">
        <a href="{{ route('admin.division') }}" class="text-base sm:text-2xl font-semibold"><i class="fa-solid fa-chevron-left mr-2"></i> Kembali</a>
    </div>

    <div class="relative mt-2 mb-2 rounded-2xl overflow-hidden">
        <!-- Background Image -->
        <div class="absolute inset-0">
            <img src="{{ $appSetting->intern_banner_url ?? asset('img/bg.jpg') }}" alt="Background Image" class="w-full h-full object-cover">
        </div>

        <!-- Konten baru di atas gambar -->
        <div class="relative z-10 p-5 sm:p-10 ml-0 sm:ml-10">
            <div class="flex flex-col items-start">
                <h1 class="text-xl sm:text-4xl mb-2 text-white font-bold">Sunting Team "{{ $team->profile->full_name }}"</h1>
                <p class="text-lg text-white">{{ $team->profile->NIP }}</p>
            </div>
        </div>
    </div>

    @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" id="notification-box"
        role="alert">
        <strong class="font-bold">Whoops!</strong> Ada beberapa masalah dengan input Anda.
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if (session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" id="notification-box"
        role="alert">
        <strong class="font-bold">Whoops!</strong> {{ session('error') }}
    </div>
    @endif

    @if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-2 py-3 rounded relative"
        id="notification-box" role="alert">
        <strong class="font-bold">Success!</strong> {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('admin.update.intern.action') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 mt-2">

            <input name="user_id" type="number" hidden value="{{ $team->id }}">

            <!-- Form 1: Informasi Akun -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                    <i class="fa-solid fa-user mr-2 text-blue-600"></i>
                    Informasi Akun
                </h3>

                <div class="mb-4">
                    <label for="username" class="block text-gray-700">Username<span
                            class="text-red-500">*</span></label>
                    <input type="text" id="username" name="username" value="{{ $team->username }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-700">Email<span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ $team->email }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700">Password</label>
                    <input type="password" id="password" name="password" class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>
                <div class="mb-4">
                    <label for="confirm-password" class="block text-gray-700">Ulangi Password</label>
                    <input type="password" id="confirm-password" name="confirm_password"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>
            </div>

            <!-- Form 2: Informasi Pribadi -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                    <i class="fa-solid fa-id-card mr-2 text-green-600"></i>
                    Informasi Pribadi
                </h3>

                <div class="mb-4">
                    <label for="nama" class="block text-gray-700">Nama<span class="text-red-500">*</span></label>
                    <input type="text" id="nama" name="full_name" value="{{ $team->profile->full_name }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>
                <div class="mb-4">
                    <label for="school_id" class="block text-gray-700">Asal Sekolah/Kampus<span
                            class="text-red-500">*</span></label>
                    <select
                        class="shadow border border-gray-300 rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1 focus:ring-gray-500"
                        id="school_id" name="school_id">
                        <option value="" disabled>--Pilih asal sekolah/kampus--</option>
                        @foreach ($schoolList as $school)
                        <option value="{{ $school->id }}"
                            {{ $team->intern && $team->intern->school_id == $school->id ? 'selected' : '' }}>
                            {{ $school->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="tempat-lahir" class="block text-gray-700">Tempat Lahir<span
                            class="text-red-500">*</span></label>
                    <input type="text" id="tempat-lahir" name="birth_place"
                        value="{{ $team->profile->birth_place }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>
                <div class="mb-4">
                    <label for="tanggal-lahir" class="block text-gray-700">Tanggal Lahir<span
                            class="text-red-500">*</span></label>
                    <input type="date" id="tanggal-lahir" name="birth_date"
                        value="{{ $team->profile->date_of_birth }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>

                <!-- [START] Penambahan Kolom Gender -->
                <div class="mb-4">
                    <label for="gender" class="block text-gray-700">Jenis Kelamin<span
                            class="text-red-500">*</span></label>
                    @php
                    $rawGender = strtolower(trim($team->profile->gender ?? ''));
                    $isMaleSelected = in_array($rawGender, ['l', 'laki-laki', 'male', 'pria'], true);
                    $isFemaleSelected = in_array($rawGender, ['p', 'perempuan', 'female', 'wanita'], true);
                    @endphp
                    <select id="gender" name="gender" class="w-full mt-1 p-2 border border-gray-300 rounded">
                        <option value="" disabled {{ !$isMaleSelected && !$isFemaleSelected ? 'selected' : '' }}>--Pilih Jenis Kelamin--</option>
                        <option value="Laki-laki" {{ $isMaleSelected ? 'selected' : '' }}>
                            Laki-laki</option>
                        <option value="Perempuan" {{ $isFemaleSelected ? 'selected' : '' }}>
                            Perempuan</option>
                    </select>
                </div>
                <!-- [END] Penambahan Kolom Gender -->

                <div class="mb-4">
                    <label for="religion" class="block text-gray-700">Agama<span
                            class="text-red-500">*</span></label>
                    @php
                    $curReligion = $team->profile->religion ?? '';
                    $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'];
                    @endphp
                    <select id="religion" name="religion" class="w-full mt-1 p-2 border border-gray-300 rounded">
                        <option value="" disabled {{ empty($curReligion) ? 'selected' : '' }}>--Pilih Agama--</option>
                        @foreach($religions as $rel)
                            <option value="{{ $rel }}" {{ strcasecmp($curReligion, $rel) === 0 ? 'selected' : '' }}>{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" id="address" name="address" value="{{ $team->profile->address ?? '' }}">

                <div class="mb-4">
                    <label for="nomor-hp" class="block text-gray-700">Nomor HP<span
                            class="text-red-500">*</span></label>
                    <input type="tel" id="nomor-hp" name="phone" value="{{ $team->profile->phone }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>
                <div class="mb-4">
                    <label for="nim" class="block text-gray-700">NIM</label>
                    <input type="tel" id="nim" name="nim" value="{{ $team->intern->nim }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>
            </div>

            <!-- Form 3: Informasi Magang -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                    <i class="fa-solid fa-briefcase mr-2 text-purple-600"></i>
                    Informasi Magang
                </h3>

                <div class="mb-4">
                    <label for="tanggal-masuk" class="block text-gray-700">Tanggal Masuk Magang</label>
                    <input type="date" id="tanggal-masuk" name="in_date"
                        value="{{ $team->intern->start_date ?? '' }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>
                <div class="mb-4">
                    <label for="tanggal-keluar" class="block text-gray-700">Tanggal Keluar Magang</label>
                    <input type="date" id="tanggal-keluar" name="out_date"
                        value="{{ $team->intern->end_date ?? '' }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>
                <div class="mb-4">
                    <label for="parent_whatsapp_number" class="block text-gray-700">Nomor WA Orang Tua</label>
                    <input type="text" id="parent_whatsapp_number" name="parent_whatsapp_number"
                        value="{{ old('parent_whatsapp_number', $team->intern->whatsappNumber->phone_number ?? '') }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded"
                        placeholder="Contoh: 081234567890 atau 6281234567890">
                </div>
                <div class="mb-4">
                    <label for="nip" class="block text-gray-700">NIP</label>
                    <input type="text" id="nip" name="nip" value="{{ $team->profile->NIP }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                </div>
                <div class="mb-4">
                    <label for="divisi" class="block text-gray-700">Divisi<span
                            class="text-red-500">*</span></label>
                    <select
                        class="shadow border border-gray-300 rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1 focus:ring-gray-500"
                        id="divisi" name="division_id">
                        <option value="" {{ !isset($team->intern->division) ? 'selected' : '' }} disabled>
                            --Pilih Divisi--</option>
                        @foreach ($divisions as $division)
                        <option value="{{ $division->id }}"
                            data-name="{{ strtolower($division->name) }}"
                            {{ isset($team->intern->division) && $team->intern->division->id == $division->id ? 'selected' : '' }}>
                            {{ $division->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="brand_id" class="block text-gray-700">Brand / Tim Bisnis</label>
                    <select
                        class="shadow border border-gray-300 rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1 focus:ring-gray-500"
                        id="brand_id" name="brand_id">
                        <option value="" {{ !isset($team->intern->brand_id) ? 'selected' : '' }}>-- Tidak Memilih Brand --</option>
                        @if(isset($brands))
                        @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}"
                            {{ isset($team->intern->brand_id) && $team->intern->brand_id == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="mb-4">
                    <label for="project" class="block text-gray-700">Project</label>
                    <select
                        class="shadow border border-gray-300 rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1 focus:ring-gray-500"
                        id="project" name="project_id">
                        <option value="" selected disabled>
                            --{{ $projectVisibility ? 'Pilih Project' : 'Already in project' }}--</option>
                        @if ($projectVisibility)
                        @foreach ($projects as $project)
                        <option value="{{ $project->id }}"> {{ $project->name }} </option>
                        @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <!-- Form 4: Informasi Teknis -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                    <i class="fa-solid fa-cog mr-2 text-gray-600"></i>
                    Informasi Teknis
                </h3>

                <div class="mb-4">
                    <label for="os" class="block text-gray-700">OS<span class="text-red-500">*</span></label>
                    <select id="os" name="os" class="w-full mt-1 p-2 border border-gray-300 rounded">
                        <option value="" disabled>--Pilih OS--</option>
                        <option value="Windows" {{ $team->os == 'Windows' ? 'selected' : '' }}>Windows</option>
                        <option value="MacBook" {{ $team->os == 'MacBook' ? 'selected' : '' }}>MacBook</option>
                        <option value="Linux" {{ $team->os == 'Linux' ? 'selected' : '' }}>Linux</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="browser" class="block text-gray-700">Browser<span
                            class="text-red-500">*</span></label>
                    <select id="browser" name="browser" class="w-full mt-1 p-2 border border-gray-300 rounded">
                        <option value="" disabled>--Pilih Browser--</option>
                        <option value="Chrome" {{ $team->browser == 'Chrome' ? 'selected' : '' }}>Chrome</option>
                        <option value="Firefox" {{ $team->browser == 'Firefox' ? 'selected' : '' }}>Firefox</option>
                        <option value="Safari" {{ $team->browser == 'Safari' ? 'selected' : '' }}>Safari</option>
                        <option value="Edge" {{ $team->browser == 'Edge' ? 'selected' : '' }}>Edge</option>
                        <option value="Opera" {{ $team->browser == 'Opera' ? 'selected' : '' }}>Opera</option>
                        <option value="Internet-explorer"
                            {{ $team->browser == 'Internet-explorer' ? 'selected' : '' }}>Internet Explorer</option>
                        <option value="Vivaldi" {{ $team->browser == 'Vivaldi' ? 'selected' : '' }}>Vivaldi</option>
                        <option value="Uc-browser" {{ $team->browser == 'Uc-browser' ? 'selected' : '' }}>UC Browser
                        </option>
                        <option value="Samsung-internet" {{ $team->browser == 'Samsung-internet' ? 'selected' : '' }}>
                            Samsung Internet</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="status-akun" class="block text-gray-700">Status Akun<span
                            class="text-red-500">*</span></label>
                    <select id="status-akun" name="account_status"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                        <option value="" disabled>--Pilih Status Akun--</option>
                        <option value="1" {{ $team->is_active == 1 ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ $team->is_active == 0 ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="konfirmasi-email" class="block text-gray-700">Konfirmasi Email<span
                            class="text-red-500">*</span></label>
                    <select id="konfirmasi-email" name="email_confirm"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                        <option value="" disabled>--Pilih Konfirmasi Email--</option>
                        <option value="1" {{ $team->is_confirm == 1 ? 'selected' : '' }}>Terkonfirmasi</option>
                        <option value="0" {{ $team->is_confirm == 0 ? 'selected' : '' }}>Belum Terkonfirmasi
                        </option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="is-reset-device-token" class="block text-gray-700">Atur ulang device token<span
                            class="text-red-500">*</span></label>
                    <select id="is-reset-device-token" name="is_reset_device_token"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                        <option value="1" {{ $team->is_reset_token == 1 ? 'selected' : '' }}>Ya</option>
                        <option value="0" {{ $team->is_reset_token == 0 ? 'selected' : '' }}>Tidak</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="is_gps_active" class="block text-gray-700">GPS Status<span
                            class="text-red-500">*</span></label>
                    <select id="is_gps_active" name="is_gps_active"
                        class="w-full mt-1 p-2 border border-gray-300 rounded">
                        <option value="1" {{ (old('is_gps_active', $team->is_gps_activate) == 1) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ (old('is_gps_active', $team->is_gps_activate) == 0) ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>
            </div>

        </div>

        <!-- Form Penjadwalan Cerdas & Lokasi Kos Pemagang (Grid 2 Kolom) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mt-6">

            <!-- Card Lokasi Kos / Domisili Pemagang -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-200/80 flex flex-col justify-between">
                <div>
                    <!-- Header -->
                    <div class="pb-3 border-b border-gray-100 mb-4">
                        <h3 class="text-sm sm:text-base font-bold text-gray-800 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                            </span>
                            <span class="truncate">Titik Domisili / Kos Pemagang</span>
                        </h3>
                        <p class="text-[11px] sm:text-xs text-gray-500 mt-1 leading-relaxed">
                            Digunakan oleh Smart Auto-Scheduling untuk menghitung jarak terdekat ke kantor (Haversine).
                        </p>
                    </div>

                    <!-- Kolom Utama: Link Google Maps Domisili / Kos -->
                    <div class="mb-3.5 p-3.5 bg-blue-50/50 border border-blue-200/80 rounded-xl space-y-2">
                        <div>
                            <label for="gmaps_quick_paste" class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Link Google Maps Domisili / Kos Pemagang
                            </label>
                        </div>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            <div class="relative flex-1">
                                <input type="text" id="gmaps_quick_paste" name="gmaps_url" placeholder="Tempel link Google Maps (contoh: https://maps.app.goo.gl/...) atau koordinat..."
                                    class="w-full pl-3 pr-8 py-2 bg-white border border-gray-300 rounded-lg text-xs text-gray-800 focus:ring-2 focus:ring-blue-500 focus:outline-none transition font-medium">
                                <button type="button" onclick="pasteGmapsFromClipboard()" title="Paste dari clipboard" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600 text-xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                    </svg>
                                </button>
                            </div>
                            <button type="button" id="btnApplyGmapsQuickPaste" onclick="applyGmapsQuickPaste()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-lg text-xs font-bold shrink-0 transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                                <svg id="iconApplyGmaps" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                <svg id="spinnerApplyGmaps" class="w-3.5 h-3.5 text-white animate-spin hidden" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span id="textApplyGmaps">Terapkan</span>
                            </button>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-snug">
                            Cukup salin dan tempel link Google Maps atau format angka koordinat domisili/kos pemagang, lalu klik Terapkan. Koordinat di bawah akan terisi otomatis.
                        </p>
                    </div>

                    <!-- Tombol Aksi Tambahan: Pilih di Peta & Buka Google Maps -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-3.5">
                        <button type="button" onclick="openKosMapModal()"
                            class="w-full py-2 px-3 bg-white hover:bg-blue-50 active:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition cursor-pointer shadow-2xs">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span>Pilih di Peta</span>
                        </button>
                        <a id="btnOpenGmapsExternal" href="https://www.google.com/maps{{ !empty($team->intern->home_latitude) ? '?q=' . (float)$team->intern->home_latitude . ',' . (float)$team->intern->home_longitude : '' }}"
                            target="_blank" rel="noopener noreferrer"
                            class="w-full py-2 px-3 bg-white hover:bg-emerald-50 active:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition cursor-pointer shadow-2xs"
                            title="Buka titik koordinat di Google Maps">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                            <span>Buka Google Maps</span>
                            <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </a>
                    </div>

                    <!-- Koordinat Lat / Lng -->
                    <div class="mb-1.5 flex flex-wrap items-center justify-between gap-1">
                        <span class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span>Titik Koordinat Kos</span>
                        </span>
                        <span class="text-[10.5px] text-gray-400">
                            (Terisi otomatis dari link Google Maps atau peta)
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3.5">
                        <div>
                            <label for="home_latitude" class="block text-xs font-semibold text-gray-700 mb-1">
                                Latitude Kos
                            </label>
                            <div class="relative">
                                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zm0 0a8.949 8.949 0 004.951-1.488A3.987 3.987 0 0013 16h-2a3.987 3.987 0 00-3.951 3.512A8.949 8.949 0 0012 21zm4.5-9.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                                </svg>
                                <input type="number" step="any" id="home_latitude" name="home_latitude"
                                    value="{{ old('home_latitude', $team->intern->home_latitude ?? '') }}"
                                    placeholder="Contoh: -7.782812"
                                    class="w-full pl-8 pr-3 py-2 bg-gray-50/50 border border-gray-300 rounded-xl text-xs sm:text-sm font-mono focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            </div>
                        </div>
                        <div>
                            <label for="home_longitude" class="block text-xs font-semibold text-gray-700 mb-1">
                                Longitude Kos
                            </label>
                            <div class="relative">
                                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zm0 0a8.949 8.949 0 004.951-1.488A3.987 3.987 0 0013 16h-2a3.987 3.987 0 00-3.951 3.512A8.949 8.949 0 0012 21zm4.5-9.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                                </svg>
                                <input type="number" step="any" id="home_longitude" name="home_longitude"
                                    value="{{ old('home_longitude', $team->intern->home_longitude ?? '') }}"
                                    placeholder="Contoh: 110.367098"
                                    class="w-full pl-8 pr-3 py-2 bg-gray-50/50 border border-gray-300 rounded-xl text-xs sm:text-sm font-mono focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            </div>
                        </div>
                    </div>

                    <!-- Alamat / Keterangan Kos -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-1">
                            <label for="home_address_notes" class="block text-xs font-semibold text-gray-700">
                                Alamat Lengkap / Keterangan Kos <span class="text-xs text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <span class="text-[11px] text-gray-400">Nama kos, jalan, atau daerah</span>
                        </div>
                        <textarea id="home_address_notes" name="home_address_notes" rows="2"
                            placeholder="Contoh: Kos Putri Melati, Jl. Kaliurang KM 5 Gang Pandega Marta No. 10..."
                            class="w-full p-2.5 border border-gray-300 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none">{{ old('home_address_notes', $team->intern->home_address_notes ?? '') }}</textarea>
                    </div>

                </div>
            </div>


            <!-- Card Kebijakan Penempatan & HR Veto -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-200/80 flex flex-col justify-between">
                <div>
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-gray-100 mb-4 gap-2">
                        <div class="min-w-0">
                            <h3 class="text-sm sm:text-base font-bold text-gray-800 flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0M3.75 18H7.5m6-6h6m-6 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0M3.75 12H10.5" />
                                    </svg>
                                </span>
                                <span class="truncate">Kebijakan Penempatan & HR Veto</span>
                            </h3>
                            <p class="text-[11px] sm:text-xs text-gray-500 mt-1 leading-relaxed">
                                Pengaturan manual oleh HR untuk mengunci kantor, blacklist kantor, atau memisahkan pasangan konflik.
                            </p>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 shrink-0 w-fit">
                            <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                            <span>HR Override</span>
                        </span>
                    </div>

                    <div class="space-y-3.5">
                        <!-- Tipe Penempatan -->
                        <!-- Tipe Penempatan -->
                        <div>
                            <label for="work_placement_type" class="block text-xs font-semibold text-gray-700 mb-1">
                                Tipe Penempatan Kerja <span class="text-rose-500">*</span>
                            </label>
                            @php
                            $isRemoteSchool = (bool) ($team->intern?->school?->is_remote_school ?? false);
                            $currentPlacement = old('work_placement_type', $team->intern->work_placement_type ?? 'auto_office');
                            @endphp
                            @if($isRemoteSchool)
                            <div class="p-2.5 bg-purple-50 border border-purple-200 rounded-xl text-xs sm:text-sm text-purple-900 font-semibold flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                    </svg>
                                    <span>WFH Only (Terkunci Otomatis oleh Sekolah Remote / Luar Kota)</span>
                                </span>
                                <span class="text-[10px] bg-purple-200/80 text-purple-800 px-2 py-0.5 rounded-full font-bold">Locked</span>
                            </div>
                            <input type="hidden" name="work_placement_type" value="wfh_only">
                            @else
                            <select id="work_placement_type" name="work_placement_type"
                                class="w-full p-2.5 border border-gray-300 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white transition leading-normal">
                                <option value="auto_office" {{ $currentPlacement == 'auto_office' ? 'selected' : '' }}>
                                    Auto Office (Algoritma menentukan kantor terdekat & kapasitas terbaik)
                                </option>
                                <option value="wfo_only" {{ $currentPlacement == 'wfo_only' ? 'selected' : '' }}>
                                    WFO Only (Selalu wajib masuk kerja di kantor)
                                </option>
                                <option value="wfh_only" {{ $currentPlacement == 'wfh_only' ? 'selected' : '' }}>
                                    WFH Only (Remote penuh / luar kota / sekolah remote)
                                </option>
                                <option value="hybrid" {{ $currentPlacement == 'hybrid' ? 'selected' : '' }}>
                                    Hybrid Dinamis (Rotasi seimbang antara WFO dan WFH)
                                </option>
                            </select>
                            @endif
                        </div>

                        <!-- Kunci Kantor & Blacklist Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-200/80">
                                <label for="locked_office_id" class="block text-xs font-semibold text-amber-900 mb-1 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                    </svg>
                                    <span>Kunci Kantor Tertentu (Veto)</span>
                                </label>
                                @if($isRemoteSchool)
                                <div class="p-2 bg-gray-100 border border-gray-200 rounded-lg text-xs text-gray-500 italic">
                                    Tidak berlaku untuk pemagang remote luar kota
                                </div>
                                <input type="hidden" name="locked_office_id" value="">
                                @else
                                <select id="locked_office_id" name="locked_office_id"
                                    class="w-full p-2 bg-white border border-amber-300 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 transition">
                                    <option value="">-- Bebas (Rekomendasi Terdekat) --</option>
                                    @if(isset($offices))
                                    @foreach($offices as $office)
                                    <option value="{{ $office->id }}" {{ (old('locked_office_id', $team->intern->locked_office_id ?? '') == $office->id) ? 'selected' : '' }}>
                                        {{ $office->name }}
                                    </option>
                                    @endforeach
                                    @endif
                                </select>
                                @endif
                                <p class="text-[11px] text-amber-800/80 mt-1.5 flex items-start gap-1">
                                    <svg class="w-3 h-3 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                    </svg>
                                    <span>Pemagang diprioritaskan selalu di kantor ini saat WFO.</span>
                                </p>
                            </div>

                            <div class="p-3 bg-rose-50/50 rounded-xl border border-rose-200/80">
                                <label for="blacklisted_office_id" class="block text-xs font-semibold text-rose-900 mb-1 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                    <span>Blacklist / Hindari Kantor</span>
                                </label>
                                <select id="blacklisted_office_id" name="blacklisted_office_id"
                                    class="w-full p-2 bg-white border border-rose-300 rounded-lg text-xs focus:ring-2 focus:ring-rose-500 transition">
                                    <option value="">-- Tidak Ada Blacklist --</option>
                                    @if(isset($offices))
                                    @foreach($offices as $office)
                                    <option value="{{ $office->id }}" {{ (old('blacklisted_office_id', $team->intern->blacklisted_office_id ?? '') == $office->id) ? 'selected' : '' }}>
                                        {{ $office->name }}
                                    </option>
                                    @endforeach
                                    @endif
                                </select>
                                <p class="text-[11px] text-rose-800/80 mt-1.5 flex items-start gap-1">
                                    <svg class="w-3 h-3 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                    </svg>
                                    <span>Algoritma tidak akan menempatkan di kantor ini.</span>
                                </p>
                            </div>
                        </div>

                        <!-- Aturan Pasangan Konflik -->
                        <div class="p-3 bg-purple-50/50 rounded-xl border border-purple-200/80">
                            <label for="conflict_intern_id" class="block text-xs font-semibold text-purple-900 mb-1 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                <span>Aturan Pasangan Konflik (Anti-Satu Shift)</span>
                            </label>
                            <select id="conflict_intern_id" name="conflict_intern_id"
                                class="w-full p-2 bg-white border border-purple-300 rounded-lg text-xs focus:ring-2 focus:ring-purple-500 transition">
                                <option value="">-- Tidak Ada Aturan Konflik --</option>
                                @if(isset($otherInterns))
                                @foreach($otherInterns as $other)
                                <option value="{{ $other->id }}" {{ (old('conflict_intern_id', $existingConflict ?? '') == $other->id) ? 'selected' : '' }}>
                                    {{ $other->user?->profile?->full_name ?? $other->user?->username ?? 'ID #'.$other->id }}
                                    ({{ $other->division?->name ?? 'Tanpa Divisi' }} - {{ $other->school?->name ?? 'Umum' }})
                                </option>
                                @endforeach
                                @endif
                            </select>
                            <p class="text-[11px] text-purple-800/80 mt-1.5 flex items-start gap-1">
                                <svg class="w-3 h-3 text-purple-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                                <span>Kedua pemagang tidak akan dijadwalkan di shift & kantor yang sama secara bersamaan.</span>
                            </p>
                            <div class="mt-2 pt-2 border-t border-purple-200/60 flex items-center justify-between text-[11px]">
                                <span class="text-purple-700">Daftar lengkap semua konflik:</span>
                                <a href="{{ route('admin.automation-rules.index') }}" target="_blank" class="text-purple-700 hover:text-purple-900 font-bold hover:underline inline-flex items-center gap-1">
                                    <span>Kelola di Otomatisasi Jadwal</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- Catatan Khusus HR -->
                        <div>
                            <label for="hr_placement_notes" class="block text-xs font-semibold text-gray-700 mb-1">
                                Catatan Khusus HR / Alasan Penempatan (Opsional)
                            </label>
                            <textarea id="hr_placement_notes" name="hr_placement_notes" rows="2"
                                placeholder="Contoh: Dispensasi kuliah setiap hari Kamis, kendala kendaraan, rekomendasi pembimbing..."
                                class="w-full p-2.5 border border-gray-300 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition resize-none">{{ old('hr_placement_notes', $team->intern->hr_placement_notes ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        @php
        $account = $team->intern?->account;
        $oldPlatforms = old('enabled_platforms');
        if (is_array($oldPlatforms)) {
        $hasGdrive = in_array('gdrive', $oldPlatforms, true);
        $hasSpreadsheet = in_array('spreadsheet', $oldPlatforms, true);
        $hasGithub = in_array('github', $oldPlatforms, true);
        $hasFigma = in_array('figma', $oldPlatforms, true);
        $hasSosmed = in_array('sosmed', $oldPlatforms, true);
        } else {
        $hasGdrive = $account ? $account->isPlatformEnabled('gdrive') : false;
        $hasSpreadsheet = $account ? $account->isPlatformEnabled('spreadsheet') : false;
        $hasGithub = $account ? $account->isPlatformEnabled('github') : false;
        $hasFigma = $account ? $account->isPlatformEnabled('figma') : false;
        $hasSosmed = $account ? $account->isPlatformEnabled('sosmed') : false;
        }
        @endphp

        <!-- Form 5: Tautan Tugas & Akun Kredensial Divisi (Bebas untuk Semua Divisi) -->
        <div class="mt-6 bg-white p-6 rounded-lg shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b mb-6 gap-2">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fa-solid fa-folder-tree mr-2 text-indigo-600"></i>
                        Tautan Tugas & Kredensial Divisi
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Kelola tautan drive, spreadsheet monitoring, akun kerja, dan portofolio pemagang (bebas disesuaikan untuk divisi manapun).</p>
                </div>
                <div>
                    <span id="active-division-badge" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        <i class="fa-solid fa-briefcase mr-1.5"></i> <span id="active-division-text">{{ $team->intern->division->name ?? 'Belum Ditentukan' }}</span>
                    </span>
                </div>
            </div>

            <!-- Checklist Toggle Platform Resource (Kosong untuk akun baru, tercentang jika sudah ada data) -->
            <div class="mb-6 p-4 bg-slate-50/90 rounded-xl border border-slate-200 shadow-2xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-3 gap-1">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-list-check text-indigo-600"></i>
                        <span>Pilih Platform / Resource yang Ingin Ditautkan:</span>
                    </span>
                    <span class="text-[11px] text-slate-500 italic">Centang platform untuk memberikan akses dan menampilkan kolom input</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 text-xs font-medium text-slate-700">
                    <label class="flex items-center gap-2 p-2 bg-white rounded-lg border border-slate-200 hover:border-amber-400 cursor-pointer select-none transition">
                        <input type="checkbox" name="enabled_platforms[]" value="gdrive" id="toggle_gdrive" class="rounded text-amber-500 focus:ring-amber-500 cursor-pointer" {{ $hasGdrive ? 'checked' : '' }} onchange="document.getElementById('section_gdrive').classList.toggle('hidden', !this.checked)">
                        <span class="flex items-center gap-1.5"><i class="fa-brands fa-google-drive text-amber-500 text-sm"></i> Google Drive</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-white rounded-lg border border-slate-200 hover:border-emerald-400 cursor-pointer select-none transition">
                        <input type="checkbox" name="enabled_platforms[]" value="spreadsheet" id="toggle_spreadsheet" class="rounded text-emerald-600 focus:ring-emerald-500 cursor-pointer" {{ $hasSpreadsheet ? 'checked' : '' }} onchange="document.getElementById('section_spreadsheet').classList.toggle('hidden', !this.checked)">
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-file-excel text-emerald-600 text-sm"></i> Spreadsheet</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-white rounded-lg border border-slate-200 hover:border-blue-400 cursor-pointer select-none transition">
                        <input type="checkbox" name="enabled_platforms[]" value="github" id="toggle_github" class="rounded text-blue-600 focus:ring-blue-500 cursor-pointer" {{ $hasGithub ? 'checked' : '' }} onchange="document.getElementById('section_github').classList.toggle('hidden', !this.checked)">
                        <span class="flex items-center gap-1.5"><i class="fa-brands fa-github text-slate-800 text-sm"></i> GitHub & Gmail</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-white rounded-lg border border-slate-200 hover:border-purple-400 cursor-pointer select-none transition">
                        <input type="checkbox" name="enabled_platforms[]" value="figma" id="toggle_figma" class="rounded text-purple-600 focus:ring-purple-500 cursor-pointer" {{ $hasFigma ? 'checked' : '' }} onchange="document.getElementById('section_figma').classList.toggle('hidden', !this.checked)">
                        <span class="flex items-center gap-1.5"><i class="fa-brands fa-figma text-purple-600 text-sm"></i> Figma</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-white rounded-lg border border-slate-200 hover:border-pink-400 cursor-pointer select-none transition">
                        <input type="checkbox" name="enabled_platforms[]" value="sosmed" id="toggle_sosmed" class="rounded text-pink-600 focus:ring-pink-500 cursor-pointer" {{ $hasSosmed ? 'checked' : '' }} onchange="document.getElementById('section_sosmed').classList.toggle('hidden', !this.checked)">
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-share-nodes text-pink-500 text-sm"></i> Medsos</span>
                    </label>
                </div>
            </div>

            <!-- Bagian 1: Universal Google Drive Link -->
            <div id="section_gdrive" class="mb-6 p-4 rounded-xl bg-slate-50/80 border border-slate-200 {{ $hasGdrive ? '' : 'hidden' }}">
                <div class="flex items-center justify-between mb-2">
                    <label for="gdrive_url" class="block font-semibold text-gray-800 text-sm">
                        <i class="fa-brands fa-google-drive text-amber-500 mr-1.5 text-base align-middle"></i>
                        Link Folder Google Drive (Tugas Pemagang)
                    </label>
                    @if($team->intern?->account?->gdrive_url)
                    <a href="{{ $team->intern->account->gdrive_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-xs text-indigo-600 hover:text-indigo-800 hover:underline font-medium">
                        <span>Buka Folder Drive</span>
                        <i class="fa-solid fa-arrow-up-right-from-square ml-1 text-[11px]"></i>
                    </a>
                    @endif
                </div>
                <input type="url" id="gdrive_url" name="gdrive_url"
                    value="{{ old('gdrive_url', $team->intern?->account?->gdrive_url) }}"
                    placeholder="https://drive.google.com/drive/folders/..."
                    class="w-full p-2.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                <p class="text-xs text-gray-500 mt-1.5">
                    <i class="fa-solid fa-circle-info mr-1 text-slate-400"></i>
                    Link folder Google Drive pemagang untuk mengumpulkan file tugas harian / mingguan.
                </p>
            </div>

            <!-- Bagian 2: Google Spreadsheet Link -->
            <div id="section_spreadsheet" class="mb-6 p-4 rounded-xl bg-emerald-50/40 border border-emerald-200 {{ $hasSpreadsheet ? '' : 'hidden' }}">
                <div class="flex items-center justify-between mb-2">
                    <label for="spreadsheet_url" class="block font-semibold text-gray-800 text-sm">
                        <i class="fa-solid fa-file-excel text-emerald-600 mr-1.5 text-base align-middle"></i>
                        Link Google Spreadsheet (Monitoring & Rekap Tugas)
                    </label>
                    @if($team->intern?->account?->spreadsheet_url)
                    <a href="{{ $team->intern->account->spreadsheet_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-xs text-emerald-700 hover:text-emerald-900 hover:underline font-medium">
                        <span>Buka Spreadsheet</span>
                        <i class="fa-solid fa-arrow-up-right-from-square ml-1 text-[11px]"></i>
                    </a>
                    @endif
                </div>
                <input type="url" id="spreadsheet_url" name="spreadsheet_url"
                    value="{{ old('spreadsheet_url', $team->intern?->account?->spreadsheet_url) }}"
                    placeholder="https://docs.google.com/spreadsheets/d/..."
                    class="w-full p-2.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                <p class="text-xs text-gray-500 mt-1.5">
                    <i class="fa-solid fa-circle-info mr-1 text-slate-400"></i>
                    Tautan Google Spreadsheet untuk rekapitulasi data kerja, KPI, logbook, atau monitoring penugasan pemagang.
                </p>
            </div>

            <!-- Bagian 3: GitHub & Akun Gmail -->
            <div id="section_github" class="mb-6 p-4 rounded-xl border border-blue-200 bg-blue-50/20 {{ $hasGithub ? '' : 'hidden' }}">
                <h4 class="text-sm font-semibold text-blue-900 flex items-center mb-3 border-b border-blue-100 pb-2">
                    <i class="fa-brands fa-github mr-2 text-gray-800 text-base"></i>
                    Akun GitHub & Gmail Kantor
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                    <div>
                        <label for="github_url" class="block text-xs font-medium text-gray-700 mb-1">Link Profil / Repository GitHub</label>
                        <div class="flex items-center">
                            <input type="url" id="github_url" name="github_url"
                                value="{{ old('github_url', $team->intern?->account?->github_url) }}"
                                placeholder="https://github.com/username"
                                class="w-full p-2 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500">
                            @if($team->intern?->account?->github_url)
                            <a href="{{ $team->intern->account->github_url }}" target="_blank" rel="noopener noreferrer" class="ml-2 px-2.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-xs whitespace-nowrap" title="Buka GitHub">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label for="gmail_account" class="block text-xs font-medium text-gray-700 mb-1">Akun Gmail Kantor</label>
                        <input type="email" id="gmail_account" name="gmail_account"
                            value="{{ old('gmail_account', $team->intern?->account?->gmail_account) }}"
                            placeholder="nama.pemagang@gmail.com"
                            class="w-full p-2 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mb-2">
                    <label for="gmail_password" class="block text-xs font-medium text-gray-700 mb-1">
                        Password Akun Gmail (Terenkripsi Aman)
                    </label>
                    <div class="relative max-w-md">
                        <input type="password" id="gmail_password" name="gmail_password"
                            value="{{ old('gmail_password', $team->intern?->account?->gmail_password) }}"
                            placeholder="{{ $team->intern?->account?->gmail_password ? '•••••••• (Isi hanya jika ingin mengganti)' : 'Masukkan password akun' }}"
                            autocomplete="new-password"
                            class="w-full p-2 pr-10 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-blue-500 font-mono">
                        <button type="button" onclick="togglePasswordVisibility('gmail_password', this)" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" title="Lihat/Sembunyikan Password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">
                        <i class="fa-solid fa-lock mr-1"></i> Disimpan dengan enkripsi 2 arah (AES-256). Admin dapat melihat kredensial ini untuk setup repository & server.
                    </p>
                </div>
            </div>

            <!-- Bagian 4: Figma Workspace -->
            <div id="section_figma" class="mb-6 p-4 rounded-xl border border-purple-200 bg-purple-50/20 {{ $hasFigma ? '' : 'hidden' }}">
                <h4 class="text-sm font-semibold text-purple-900 flex items-center mb-3 border-b border-purple-100 pb-2">
                    <i class="fa-brands fa-figma mr-2 text-purple-600 text-base"></i>
                    Workspace Figma / Desain
                </h4>
                <div>
                    <label for="figma_url" class="block text-xs font-medium text-gray-700 mb-1">Link Profil / Workspace / File Figma</label>
                    <div class="flex items-center">
                        <input type="url" id="figma_url" name="figma_url"
                            value="{{ old('figma_url', $team->intern?->account?->figma_url) }}"
                            placeholder="https://www.figma.com/@username atau link file project"
                            class="w-full p-2 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-purple-500">
                        @if($team->intern?->account?->figma_url)
                        <a href="{{ $team->intern->account->figma_url }}" target="_blank" rel="noopener noreferrer" class="ml-2 px-2.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-xs whitespace-nowrap" title="Buka Figma">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">
                        <i class="fa-solid fa-circle-info mr-1"></i> Tautan profil Figma atau file canvas desain yang sedang dikerjakan.
                    </p>
                </div>
            </div>

            <!-- Bagian 5: Akun Media Sosial -->
            <div id="section_sosmed" class="mb-6 p-4 rounded-xl border border-pink-200 bg-pink-50/20 {{ $hasSosmed ? '' : 'hidden' }}">
                <div class="flex items-center justify-between mb-3 border-b border-pink-100 pb-2">
                    <div>
                        <h4 class="text-sm font-semibold text-pink-900 flex items-center">
                            <i class="fa-solid fa-share-nodes mr-2 text-pink-500 text-base"></i>
                            Daftar Akun Media Sosial
                        </h4>
                        <p class="text-xs text-gray-500 mt-0.5">Daftar akun media sosial yang dikelola oleh pemagang.</p>
                    </div>
                </div>

                <!-- Dynamic Social Media Links List -->
                <div id="social-links-container" class="space-y-3 mb-4">
                    @php
                    $existingSocialLinks = old('social_media_links', $team->intern?->account?->social_media_links ?? []);
                    @endphp

                    @forelse($existingSocialLinks as $idx => $link)
                    <div class="social-link-row p-3 bg-white border border-gray-200 rounded-md relative flex flex-col sm:flex-row gap-2 items-start sm:items-center shadow-sm">
                        <div class="w-full sm:w-1/4">
                            <select name="social_media_links[{{ $idx }}][platform]" class="w-full p-1.5 text-xs border border-gray-300 rounded bg-white">
                                <option value="Instagram" {{ ($link['platform'] ?? '') === 'Instagram' ? 'selected' : '' }}>Instagram</option>
                                <option value="TikTok" {{ ($link['platform'] ?? '') === 'TikTok' ? 'selected' : '' }}>TikTok</option>
                                <option value="LinkedIn" {{ ($link['platform'] ?? '') === 'LinkedIn' ? 'selected' : '' }}>LinkedIn</option>
                                <option value="YouTube" {{ ($link['platform'] ?? '') === 'YouTube' ? 'selected' : '' }}>YouTube</option>
                                <option value="Facebook" {{ ($link['platform'] ?? '') === 'Facebook' ? 'selected' : '' }}>Facebook</option>
                                <option value="Twitter/X" {{ ($link['platform'] ?? '') === 'Twitter/X' ? 'selected' : '' }}>Twitter / X</option>
                                <option value="Lainnya" {{ ($link['platform'] ?? '') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                        <div class="w-full sm:w-1/3">
                            <input type="text" name="social_media_links[{{ $idx }}][username]"
                                value="{{ $link['username'] ?? '' }}"
                                placeholder="@username"
                                class="w-full p-1.5 text-xs border border-gray-300 rounded">
                        </div>
                        <div class="w-full sm:w-2/5 flex items-center gap-1">
                            <input type="url" name="social_media_links[{{ $idx }}][url]"
                                value="{{ $link['url'] ?? '' }}"
                                placeholder="https://..."
                                class="w-full p-1.5 text-xs border border-gray-300 rounded">
                            @if(!empty($link['url']))
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="p-1.5 text-gray-500 hover:text-indigo-600" title="Buka Link">
                                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                            </a>
                            @endif
                            <button type="button" onclick="removeSocialRow(this)" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded" title="Hapus Baris">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div id="no-social-msg" class="text-center py-4 text-xs text-gray-400 bg-white rounded border border-dashed border-gray-200">
                        Belum ada akun media sosial yang ditambahkan.
                    </div>
                    @endforelse
                </div>

                <button type="button" onclick="addSocialRow()" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded bg-pink-50 text-pink-700 border border-pink-200 hover:bg-pink-100 transition">
                    <i class="fa-solid fa-plus mr-1.5"></i> Tambah Akun Sosmed
                </button>
            </div>

            <!-- Bagian 6: Catatan Akun / Kredensial Tambahan -->
            <div id="section_notes" class="mb-2 p-4 rounded-xl border border-amber-200 bg-amber-50/20">
                <label for="notes" class="block text-xs font-semibold text-gray-700 mb-1">
                    <i class="fa-solid fa-note-sticky mr-1 text-amber-500"></i>
                    Catatan Akun / Kredensial Tambahan (Opsional)
                </label>
                <textarea id="notes" name="notes" rows="2"
                    placeholder="Catatan tambahan kredensial tools (misal Canva, hosting, cPanel, portofolio, dll)..."
                    class="w-full p-2 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-gray-400">{{ old('notes', $team->intern?->account?->notes) }}</textarea>
            </div>
        </div>

        <div class="flex justify-center my-10">
            <button type="submit" class="center bg-red-500 text-white px-20 py-2 rounded hover:bg-red-700"> Simpan
            </button>
        </div>
    </form>

    <!-- MODAL PICKER PETA KOS PEMAGANG -->
    <div id="kosMapModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden p-2 sm:p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[92vh]">
            <!-- Header -->
            <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-200 flex items-center justify-between bg-slate-50 shrink-0">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm shrink-0">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-bold text-gray-800 text-xs sm:text-sm truncate">Pilih Titik Lokasi Kos / Tempat Tinggal</h3>
                        <p class="text-[11px] text-gray-500 truncate">Geser pin marker atau cari alamat kos pemagang</p>
                    </div>
                </div>
                <button type="button" onclick="closeKosMapModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition cursor-pointer shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Search Bar & GPS Button -->
            <div class="p-2.5 sm:p-3 bg-white border-b border-gray-100 flex flex-col sm:flex-row gap-2 shrink-0 relative z-[1050]">
                <div class="relative flex-1">
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input type="text" id="kosMapSearchInput" placeholder="Cari nama jalan, kos, atau daerah..."
                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div id="kosSearchResults" class="absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-2xl z-[2000] max-h-48 overflow-y-auto hidden text-xs divide-y divide-gray-100"></div>
                </div>
                <div class="flex gap-2 shrink-0">
                    <button type="button" onclick="searchKosLocation()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition cursor-pointer text-center flex items-center justify-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <span>Cari</span>
                    </button>
                </div>
            </div>

            <!-- Map Container -->
            <div class="relative flex-1 min-h-[250px] sm:min-h-[350px] z-10">
                <div id="kosLeafletMap" class="w-full h-[260px] sm:h-[380px]"></div>
                <div class="absolute bottom-2.5 right-2.5 z-[1000] bg-slate-900/80 backdrop-blur-md text-white px-2.5 py-1 rounded-lg text-[10px] sm:text-[11px] font-mono shadow-md border border-white/10">
                    <span id="kosModalCoordsText">Geser marker untuk memilih</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-4 sm:px-5 py-3 border-t border-gray-200 bg-slate-50 flex flex-col-reverse sm:flex-row items-center justify-between gap-2.5 shrink-0">
                <div class="text-[11px] text-gray-500 text-center sm:text-left flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <span class="hidden sm:inline">Klik di peta atau geser pin marker ke lokasi tempat tinggal pemagang.</span>
                    <span class="sm:hidden">Geser marker ke titik lokasi kos.</span>
                </div>
                <div class="flex w-full sm:w-auto gap-2">
                    <button type="button" onclick="closeKosMapModal()" class="flex-1 sm:flex-none px-4 py-2 border border-gray-300 text-gray-700 rounded-xl text-xs font-medium hover:bg-gray-100 transition cursor-pointer text-center">
                        Batal
                    </button>
                    <button type="button" onclick="applyKosCoordinates()" class="flex-1 sm:flex-none px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow transition cursor-pointer text-center">
                        Gunakan Titik Ini
                    </button>
                </div>
            </div>
        </div>
    </div>

</main>

<script>
    let kosMap = null;
    let kosMarker = null;
    let currentKosLat = Number("{{ (float)($team->intern->home_latitude ?? ($offices->first()->latitude ?? -7.7828)) }}");
    let currentKosLng = Number("{{ (float)($team->intern->home_longitude ?? ($offices->first()->longitude ?? 110.3670)) }}");

    function openKosMapModal() {
        const modal = document.getElementById('kosMapModal');
        if (!modal) return;
        modal.classList.remove('hidden');

        const inputLat = parseFloat(document.getElementById('home_latitude').value);
        const inputLng = parseFloat(document.getElementById('home_longitude').value);
        if (!isNaN(inputLat) && !isNaN(inputLng)) {
            currentKosLat = inputLat;
            currentKosLng = inputLng;
        }

        // Prefill kolom pencarian modal jika masih kosong
        const searchInput = document.getElementById('kosMapSearchInput');
        if (searchInput && !searchInput.value.trim()) {
            const addrNotes = document.getElementById('home_address_notes')?.value.trim();
            const generalAddr = document.getElementById('address')?.value.trim();
            if (addrNotes) {
                searchInput.value = addrNotes;
            } else if (generalAddr) {
                searchInput.value = generalAddr;
            }
        }

        setTimeout(() => {
            if (!kosMap) {
                kosMap = L.map('kosLeafletMap', {
                    zoomControl: false
                }).setView([currentKosLat, currentKosLng], 16);
                L.control.zoom({
                    position: 'topright'
                }).addTo(kosMap);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(kosMap);

                kosMarker = L.marker([currentKosLat, currentKosLng], {
                    draggable: true
                }).addTo(kosMap);
                kosMarker.on('dragend', function() {
                    const pos = kosMarker.getLatLng();
                    setKosMarkerPos(pos.lat, pos.lng);
                });

                kosMap.on('click', function(e) {
                    setKosMarkerPos(e.latlng.lat, e.latlng.lng);
                });
            } else {
                kosMap.invalidateSize();
                setKosMarkerPos(currentKosLat, currentKosLng);
                kosMap.setView([currentKosLat, currentKosLng], 16);
            }
            updateKosCoordsText(currentKosLat, currentKosLng);
        }, 200);
    }

    function setKosMarkerPos(lat, lng) {
        currentKosLat = lat;
        currentKosLng = lng;
        if (kosMarker) {
            kosMarker.setLatLng([lat, lng]);
        }
        updateKosCoordsText(lat, lng);
    }

    function updateKosCoordsText(lat, lng) {
        const el = document.getElementById('kosModalCoordsText');
        if (el) el.textContent = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
    }

    function closeKosMapModal() {
        const modal = document.getElementById('kosMapModal');
        if (modal) modal.classList.add('hidden');
        const results = document.getElementById('kosSearchResults');
        if (results) results.classList.add('hidden');
    }

    function locateKosUserGPS() {
        if (!navigator.geolocation) {
            alert('Browser tidak mendukung GPS Geolocation.');
            return;
        }
        navigator.geolocation.getCurrentPosition(
            function(pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                if (kosMap) {
                    kosMap.setView([lat, lng], 17);
                    setKosMarkerPos(lat, lng);
                }
            },
            function(err) {
                alert('Gagal mengambil titik GPS: ' + err.message);
            }, {
                enableHighAccuracy: true,
                timeout: 10000
            }
        );
    }

    function searchKosLocation() {
        const input = document.getElementById('kosMapSearchInput');
        const query = input ? input.value.trim() : '';
        if (!query) return;

        const container = document.getElementById('kosSearchResults');
        container.innerHTML = '<div class="p-3 text-center text-slate-500 flex items-center justify-center gap-2"><svg class="w-4 h-4 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span>Mencari titik koordinat di Google Maps...</span></div>';
        container.classList.remove('hidden');

        fetch("{{ route('admin.geocode-address') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ query: query })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success || (!data.results && !data.lat)) {
                container.innerHTML = `<div class="p-3 text-center text-rose-500">${data.message || 'Alamat tidak ditemukan di sistem peta.'}</div>`;
                return;
            }

            const results = data.results || [{ lat: data.lat, lng: data.lng, display_name: data.display_name || query }];

            // Jika hanya 1 hasil atau langsung dari Google Maps, set titik dan marker peta langsung!
            if (results.length === 1 || (data.source && data.source.startsWith('gmaps'))) {
                const targetLat = parseFloat(results[0].lat);
                const targetLng = parseFloat(results[0].lng);
                if (kosMap) {
                    kosMap.setView([targetLat, targetLng], 17);
                    setKosMarkerPos(targetLat, targetLng);
                }
                const srcLabel = (data.source && data.source.startsWith('gmaps')) ? 'Google Maps' : 'Peta';
                container.innerHTML = `
                    <div class="p-3 bg-emerald-50 text-emerald-800 text-xs flex items-center justify-between border-b border-emerald-100">
                        <span class="truncate"><i class="fa-solid fa-circle-check text-emerald-600 mr-1.5"></i>Ditemukan via ${srcLabel}: <b>${results[0].display_name}</b></span>
                        <button type="button" onclick="document.getElementById('kosSearchResults').classList.add('hidden')" class="text-emerald-700 hover:text-emerald-900 font-bold ml-2 text-sm cursor-pointer">×</button>
                    </div>
                `;
                setTimeout(() => {
                    container.classList.add('hidden');
                }, 4000);
                return;
            }

            // Jika ada beberapa alternatif hasil (dari fallback)
            container.innerHTML = '';
            results.forEach(item => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'w-full text-left p-2.5 hover:bg-blue-50 border-b border-gray-100 last:border-0 text-slate-700 flex items-start gap-2 text-xs transition cursor-pointer';
                btn.innerHTML = `<i class="fa-solid fa-map-pin text-blue-600 mt-0.5 shrink-0"></i><span class="truncate block">${item.display_name}</span>`;
                btn.onclick = () => {
                    const lat = parseFloat(item.lat);
                    const lng = parseFloat(item.lng || item.lon);
                    if (kosMap) {
                        kosMap.setView([lat, lng], 17);
                        setKosMarkerPos(lat, lng);
                    }
                    container.classList.add('hidden');
                };
                container.appendChild(btn);
            });
        })
        .catch(err => {
            container.innerHTML = '<div class="p-3 text-center text-rose-500">Gagal mencari alamat: ' + err.message + '</div>';
        });
    }

    // Enter key trigger for search
    document.getElementById('kosMapSearchInput')?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchKosLocation();
        }
    });

    function applyKosCoordinates() {
        const latInput = document.getElementById('home_latitude');
        const lngInput = document.getElementById('home_longitude');
        if (latInput) latInput.value = currentKosLat.toFixed(7);
        if (lngInput) lngInput.value = currentKosLng.toFixed(7);

        syncGmapsLinks(currentKosLat, currentKosLng);
        closeKosMapModal();
    }

    function openKosModalInGmaps() {
        if (!isNaN(currentKosLat) && !isNaN(currentKosLng)) {
            window.open(`https://www.google.com/maps?q=${currentKosLat},${currentKosLng}`, '_blank');
        } else {
            window.open('https://www.google.com/maps', '_blank');
        }
    }

    function syncGmapsLinks(lat, lng) {
        if (!lat || !lng || isNaN(lat) || isNaN(lng)) return;
        lat = parseFloat(lat);
        lng = parseFloat(lng);
        const gmapsUrl = `https://www.google.com/maps?q=${lat},${lng}`;

        const headerBtn = document.getElementById('btnOpenGmapsExternal');
        if (headerBtn) headerBtn.href = gmapsUrl;
    }

    function applyResolvedCoordinates(lat, lng, address = null) {
        lat = parseFloat(lat);
        lng = parseFloat(lng);
        if (isNaN(lat) || isNaN(lng)) return;

        const latInput = document.getElementById('home_latitude');
        const lngInput = document.getElementById('home_longitude');
        if (latInput) latInput.value = lat.toFixed(7);
        if (lngInput) lngInput.value = lng.toFixed(7);

        if (address) {
            const notesInput = document.getElementById('home_address_notes');
            if (notesInput) notesInput.value = address;
            const addrInput = document.getElementById('address');
            if (addrInput) addrInput.value = address;
        }

        currentKosLat = lat;
        currentKosLng = lng;
        if (kosMarker) {
            kosMarker.setLatLng([lat, lng]);
        }
        if (kosMap) {
            kosMap.setView([lat, lng], 16);
        }

        syncGmapsLinks(lat, lng);
    }

    async function applyGmapsQuickPaste() {
        const input = document.getElementById('gmaps_quick_paste');
        if (!input) return;
        const val = input.value.trim();
        if (!val) {
            alert('Silakan paste link Google Maps atau teks koordinat terlebih dahulu.');
            return;
        }

        // 1. Preview cepat jika koordinat langsung terbaca di client
        const directCoords = parseGmapsCoordinates(val);
        if (directCoords) {
            applyResolvedCoordinates(directCoords.lat, directCoords.lng);
        }

        // 2. Resolve via server untuk memvalidasi dan mengambil alamat teks dari Google Maps
        const btn = document.getElementById('btnApplyGmapsQuickPaste');
        const icon = document.getElementById('iconApplyGmaps');
        const spinner = document.getElementById('spinnerApplyGmaps');
        const text = document.getElementById('textApplyGmaps');

        if (btn) btn.disabled = true;
        if (icon) icon.classList.add('hidden');
        if (spinner) spinner.classList.remove('hidden');
        if (text) text.textContent = 'Mengecek...';

        try {
            const response = await fetch("{{ route('admin.resolve-gmaps-url') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ url: val })
            });

            const data = await response.json();
            if (data.success && data.lat && data.lng) {
                applyResolvedCoordinates(data.lat, data.lng, data.address || null);
                input.value = '';
            } else if (directCoords) {
                input.value = '';
            } else {
                alert(data.message || 'Format tidak dikenali. Pastikan mem-paste link Google Maps atau angka koordinat (contoh: -7.79579, 110.40823).');
            }
        } catch (err) {
            if (!directCoords) {
                alert('Gagal menghubungi server untuk memproses link: ' + err.message);
            } else {
                input.value = '';
            }
        } finally {
            if (btn) btn.disabled = false;
            if (icon) icon.classList.remove('hidden');
            if (spinner) spinner.classList.add('hidden');
            if (text) text.textContent = 'Terapkan';
        }
    }

    function pasteGmapsFromClipboard() {
        if (navigator.clipboard && navigator.clipboard.readText) {
            navigator.clipboard.readText().then(text => {
                const input = document.getElementById('gmaps_quick_paste');
                if (input) {
                    input.value = text;
                    applyGmapsQuickPaste();
                }
            }).catch(err => {
                alert('Gagal membaca clipboard: ' + err.message);
            });
        } else {
            alert('Gunakan Ctrl+V untuk mem-paste pada kolom input.');
        }
    }

    async function autoGeocodeKosAddress() {
        const notesInput = document.getElementById('home_address_notes');
        const generalAddrInput = document.getElementById('address');
        const query = (notesInput?.value || '').trim() || (generalAddrInput?.value || '').trim();

        if (!query) {
            alert('Silakan tuliskan alamat, nama jalan, atau nama kos pemagang terlebih dahulu pada kolom Alamat / Keterangan Kos.');
            if (notesInput) notesInput.focus();
            return;
        }

        const btn = document.getElementById('btnAutoGeocodeAddress');
        const icon = document.getElementById('iconAutoGeocode');
        const spinner = document.getElementById('spinnerAutoGeocode');
        const text = document.getElementById('textAutoGeocode');

        if (btn) btn.disabled = true;
        if (icon) icon.classList.add('hidden');
        if (spinner) spinner.classList.remove('hidden');
        if (text) text.textContent = 'Mencari di Google Maps...';

        try {
            const response = await fetch("{{ route('admin.geocode-address') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ query: query })
            });

            const data = await response.json();
            if (data.success && data.lat && data.lng) {
                applyResolvedCoordinates(data.lat, data.lng);
            } else {
                alert(data.message || 'Alamat tidak ditemukan di sistem peta. Mohon periksa kembali ejaan atau gunakan nama jalan/daerah yang lebih dikenal.');
            }
        } catch (err) {
            alert('Gagal menghubungi server untuk mencari koordinat: ' + err.message);
        } finally {
            if (btn) btn.disabled = false;
            if (icon) icon.classList.remove('hidden');
            if (spinner) spinner.classList.add('hidden');
            if (text) text.textContent = 'Ambil Titik Koordinat dari Alamat Ini (Google Maps)';
        }
    }

    function parseGmapsCoordinates(text) {
        if (!text) return null;
        text = text.trim();

        // 1. URL with place pin marker !3dlat!4dlng
        let match = text.match(/!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/);
        if (match) {
            return { lat: parseFloat(match[1]), lng: parseFloat(match[2]) };
        }
        // 2. URL with @lat,lng
        match = text.match(/@(-?\d+\.?\d*),(-?\d+\.?\d*)/);
        if (match) {
            return { lat: parseFloat(match[1]), lng: parseFloat(match[2]) };
        }
        // 3. URL with ?q=lat,lng or query=lat,lng or ll=lat,lng
        match = text.match(/[?&](?:q|query|ll)=(-?\d+\.?\d*),(-?\d+\.?\d*)/);
        if (match) {
            return { lat: parseFloat(match[1]), lng: parseFloat(match[2]) };
        }
        // 4. Coordinate string with dot decimals: "-7.79579, 110.40823"
        match = text.match(/(-?\d+\.\d+)\s*,\s*(-?\d+\.\d+)/);
        if (match) {
            return { lat: parseFloat(match[1]), lng: parseFloat(match[2]) };
        }
        // 5. Coordinate string with comma decimals: "-7,79579, 110,40823"
        match = text.match(/(-?\d+,\d+)\s*,\s*(-?\d+,\d+)/);
        if (match) {
            return { lat: parseFloat(match[1].replace(',', '.')), lng: parseFloat(match[2].replace(',', '.')) };
        }
        // 6. Space separated: "-7.79579 110.40823"
        match = text.match(/(-?\d+[\.,]\d+)\s+([0-9]+[\.,]\d+)/);
        if (match) {
            return { lat: parseFloat(match[1].replace(',', '.')), lng: parseFloat(match[2].replace(',', '.')) };
        }

        return null;
    }


    // Input listeners for direct typing
    document.getElementById('home_latitude')?.addEventListener('input', function() {
        const lat = parseFloat(this.value.replace(',', '.'));
        const lng = parseFloat(document.getElementById('home_longitude')?.value.replace(',', '.'));
        if (!isNaN(lat) && !isNaN(lng)) {
            currentKosLat = lat;
            currentKosLng = lng;
            syncGmapsLinks(lat, lng);
        }
    });

    document.getElementById('home_longitude')?.addEventListener('input', function() {
        const lng = parseFloat(this.value.replace(',', '.'));
        const lat = parseFloat(document.getElementById('home_latitude')?.value.replace(',', '.'));
        if (!isNaN(lat) && !isNaN(lng)) {
            currentKosLat = lat;
            currentKosLng = lng;
            syncGmapsLinks(lat, lng);
        }
    });

    document.getElementById('gmaps_quick_paste')?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyGmapsQuickPaste();
        }
    });

    document.getElementById('home_address_notes')?.addEventListener('input', function() {
        const addr = document.getElementById('address');
        if (addr) addr.value = this.value;
    });

    function togglePasswordVisibility(fieldId, btn) {
        const input = document.getElementById(fieldId);
        if (!input) return;
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    }

    let socialLinkIndex = document.querySelectorAll('.social-link-row').length;

    function addSocialRow() {
        const container = document.getElementById('social-links-container');
        const noMsg = document.getElementById('no-social-msg');
        if (noMsg) {
            noMsg.style.display = 'none';
        }

        const row = document.createElement('div');
        row.className = 'social-link-row p-3 bg-white border border-gray-200 rounded-md relative flex flex-col sm:flex-row gap-2 items-start sm:items-center shadow-sm';
        row.innerHTML = `
                <div class="w-full sm:w-1/4">
                    <select name="social_media_links[${socialLinkIndex}][platform]" class="w-full p-1.5 text-xs border border-gray-300 rounded bg-white">
                        <option value="Instagram">Instagram</option>
                        <option value="TikTok">TikTok</option>
                        <option value="LinkedIn">LinkedIn</option>
                        <option value="YouTube">YouTube</option>
                        <option value="Facebook">Facebook</option>
                        <option value="Twitter/X">Twitter / X</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="w-full sm:w-1/3">
                    <input type="text" name="social_media_links[${socialLinkIndex}][username]" placeholder="@username" class="w-full p-1.5 text-xs border border-gray-300 rounded">
                </div>
                <div class="w-full sm:w-2/5 flex items-center gap-1">
                    <input type="url" name="social_media_links[${socialLinkIndex}][url]" placeholder="https://..." class="w-full p-1.5 text-xs border border-gray-300 rounded">
                    <button type="button" onclick="removeSocialRow(this)" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded" title="Hapus Baris">
                        <i class="fa-regular fa-trash-can text-xs"></i>
                    </button>
                </div>
            `;
        container.appendChild(row);
        socialLinkIndex++;
    }

    function removeSocialRow(btn) {
        const row = btn.closest('.social-link-row');
        if (row) {
            row.remove();
        }
        const container = document.getElementById('social-links-container');
        if (container && container.querySelectorAll('.social-link-row').length === 0) {
            const noMsg = document.getElementById('no-social-msg');
            if (noMsg) {
                noMsg.style.display = 'block';
            }
        }
    }

    function updateDivisionForm(selectedDivisionName) {
        const badgeText = document.getElementById('active-division-text');
        if (badgeText && selectedDivisionName) {
            badgeText.textContent = selectedDivisionName;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const message = document.getElementById('notification-box');
        if (message) {
            setTimeout(() => {
                message.style.opacity = 0;
                setTimeout(() => message.remove(), 600);
            }, 3000);
        }

        // Bind change event to Divisi dropdown
        const divisiSelect = document.getElementById('divisi');
        if (divisiSelect) {
            divisiSelect.addEventListener('change', function() {
                const selectedOpt = this.options[this.selectedIndex];
                const divName = selectedOpt ? (selectedOpt.getAttribute('data-name') || selectedOpt.text) : '';
                updateDivisionForm(divName);
            });
        }
    });
</script>
@endsection