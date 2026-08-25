@extends('layouts.main')

@section('title', 'Sunting Anggota')

@section('contents')
    @include('layouts.sidebar')
    @include('layouts.navbar')

    <!-- Main Content -->
    <main class="ml-64 mt-24 p-6 md:ml-48 lg:ml-64">

        <div class="flex space-x-6 border-b border-gray-300">
            <a href="{{ route('admin.division') }}" class="text-2xl"><i class="fa-solid fa-chevron-left"></i> Kembali</a>
        </div>

        <div class="relative mt-2 mb-2 -z-10">
            <!-- Background Image -->
            <div class="absolute inset-0">
                <img src="{{ asset('img/bg.jpg') }}" alt="Background Image" class="w-full h-full object-cover">
            </div>

            <!-- Konten baru di atas gambar -->
            <div class="relative z-10 p-10 ml-10">
                <div class="flex flex-col items-start">
                    <h1 class="text-4xl mb-2 text-white">Sunting Team "{{ $team->profile->full_name }}"</h1>
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
                        <label for="asal-sekolah" class="block text-gray-700">Asal Sekolah/Kampus<span
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
                        <select id="gender" name="gender" class="w-full mt-1 p-2 border border-gray-300 rounded">
                            <option value="" disabled>--Pilih Jenis Kelamin--</option>
                            <option value="Laki-laki" {{ $team->profile->gender == 'Laki-laki' ? 'selected' : '' }}>
                                Laki-laki</option>
                            <option value="Perempuan" {{ $team->profile->gender == 'Perempuan' ? 'selected' : '' }}>
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
                        <label for="devisi" class="block text-gray-700">Divisi<span
                                class="text-red-500">*</span></label>
                        <select
                            class="shadow border border-gray-300 rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1 focus:ring-gray-500"
                            id="divisi" name="division_id">
                            <option value="" {{ !isset($team->intern->division) ? 'selected' : '' }} disabled>
                                --Pilih Divisi--</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}"
                                    {{ isset($team->intern->division) && $team->intern->division->id == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="Project" class="block text-gray-700">Project</label>
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
                            <option value="1" {{ $team->is_gps_activate == 1 ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ $team->is_gps_activate == 0 ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>
                </div>

            </div>
            <div class="flex justify-center my-10">
                <button type="submit" class="center bg-red-500 text-white px-20 py-2 rounded hover:bg-red-700"> Simpan
                </button>
            </div>
        </form>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const message = document.getElementById('notification-box');
            if (message) {
                setTimeout(() => {
                    message.style.opacity = 0;
                    setTimeout(() => message.remove(), 600);
                }, 3000);
            }
        });
    </script>
@endsection
