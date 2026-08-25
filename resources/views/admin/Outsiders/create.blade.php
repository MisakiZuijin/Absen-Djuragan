@extends('layouts.main')

@section('title', 'Tambah Outsider')

@section('contents')
    @include('layouts.sidebar')
    @include('layouts.navbar')

    <main class="ml-64 mt-24 p-6 md:ml-48 lg:ml-64">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('admin.outsiders.index') }}"
                class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors duration-200">
                <i class="fa-solid fa-chevron-left mr-2"></i>
                <span>Kembali ke Daftar Outsider</span>
            </a>
        </div>

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Tambah Outsider Baru</h1>
            <p class="text-gray-600">Lengkapi data outsider untuk akses sistem</p>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex">
                    <i class="fa fa-exclamation-circle mr-2 mt-0.5"></i>
                    <div>
                        <strong class="font-medium">Terdapat kesalahan:</strong>
                        <ul class="mt-1 text-sm list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('admin.outsiders.store') }}" method="POST" class="max-w-4xl">
            @csrf

            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <!-- Basic Information -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dasar</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Masukkan nama lengkap" required>
                        </div>

                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                Username <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="username" id="username" value="{{ old('username') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Username unik" required>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="email@example.com" required>
                        </div>

                        <div>
                            <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Telepon
                            </label>
                            <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="081234567890">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password" id="password"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Minimal 8 karakter" required>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Konfirmasi Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Ketik ulang password" required>
                        </div>
                    </div>
                </div>

                <!-- Type and Connection -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tipe & Koneksi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipe Outsider <span class="text-red-500">*</span>
                            </label>
                            <select name="type" id="type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                                <option value="" disabled selected>Pilih tipe</option>
                                <option value="guru" {{ old('type') == 'guru' ? 'selected' : '' }}>Guru</option>
                                <option value="ortu" {{ old('type') == 'ortu' ? 'selected' : '' }}>Orang Tua</option>
                            </select>
                        </div>

                        <div id="school_field" style="display: none;">
                            <label for="school_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Sekolah (Guru) <span class="text-red-500">*</span>
                            </label>
                            <select name="school_id" id="school_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih sekolah</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="intern_field" style="display: none;">
                            <label for="intern_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Siswa (Orang Tua) <span class="text-red-500">*</span>
                            </label>
                            <select name="intern_id" id="intern_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih siswa</option>
                                @foreach ($interns as $intern)
                                    <option value="{{ $intern->id }}" {{ old('intern_id') == $intern->id ? 'selected' : '' }}>
                                        {{ $intern->user->profile->full_name ?? $intern->user->username }} - {{ $intern->school->name ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- <!-- Notification Settings (for ortu only) -->
                <div class="p-6 border-b border-gray-200" id="notification_section" style="display: none;">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Pengaturan Notifikasi</h3>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                        <div class="flex">
                            <i class="fa fa-bell text-yellow-600 mr-3 mt-0.5"></i>
                            <div>
                                <h4 class="text-sm font-medium text-yellow-800">Notifikasi WhatsApp</h4>
                                <p class="text-sm text-yellow-700 mt-1">
                                    Aktifkan untuk menerima notifikasi presensi anak melalui WhatsApp
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="notif_enabled" value="1" {{ old('notif_enabled', '1') ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label class="ml-2 text-sm text-gray-700">Aktifkan notifikasi WhatsApp</label>
                    </div>
                </div> --}}

                <!-- Account Settings -->
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Pengaturan Akun</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label class="ml-2 text-sm text-gray-700">Akun Aktif</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_confirm" value="1" {{ old('is_confirm', '1') ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label class="ml-2 text-sm text-gray-700">Email Terkonfirmasi</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_reset_token" value="1" {{ old('is_reset_token') ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label class="ml-2 text-sm text-gray-700">Wajib Reset Password</label>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md transition-colors duration-200">
                        <i class="fa fa-save mr-2"></i>Simpan Outsider
                    </button>
                </div>
            </div>
        </form>
    </main>

    <script>
        const typeSelect = document.getElementById('type');
        const schoolField = document.getElementById('school_field');
        const internField = document.getElementById('intern_field');
        const notificationSection = document.getElementById('notification_section');
        const schoolInput = document.getElementById('school_id');
        const internInput = document.getElementById('intern_id');

        function toggleFields() {
            const selected = typeSelect.value;
            schoolField.style.display = selected === 'guru' ? 'block' : 'none';
            internField.style.display = selected === 'ortu' ? 'block' : 'none';
            notificationSection.style.display = selected === 'ortu' ? 'block' : 'none';

            if(selected === 'guru') {
                schoolInput.required = true;
                internInput.required = false;
            } else if(selected === 'ortu') {
                schoolInput.required = false;
                internInput.required = true;
            } else {
                schoolInput.required = false;
                internInput.required = false;
            }
        }

        typeSelect.addEventListener('change', toggleFields);
        document.addEventListener('DOMContentLoaded', toggleFields);
    </script>
@endsection
