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
                        value="{{ $team->intern->whatsappNumber->phone_number ?? '' }}"
                        class="w-full mt-1 p-2 border border-gray-300 rounded"
                        placeholder="Contoh: 6281234567890">
                </div>
                <div class="mb-4">
                    <label for="nip" class="block text-gray-700">
                        NIP<span class="text-red-500">*</span>
                    </label>
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

</main>

<script>
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