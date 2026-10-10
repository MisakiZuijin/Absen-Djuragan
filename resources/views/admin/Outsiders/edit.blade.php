@extends('layouts.main')

@section('title', 'Edit Outsider')

@section('contents')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Select2 Custom Tailwind Styling for Form Inputs */
    .select2-container--default .select2-selection--single {
        height: 42px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.375rem !important;
        padding: 6px 12px !important;
        display: flex !important;
        align-items: center !important;
        background-color: #ffffff !important;
        font-size: 0.875rem !important;
        transition: all 0.2s ease !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #374151 !important;
        line-height: normal !important;
        padding-left: 0 !important;
        padding-right: 20px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #9ca3af !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
        top: 1px !important;
    }
    .select2-container--default.select2-container--open .select2-selection--single,
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3) !important;
        outline: none !important;
    }
    .select2-dropdown {
        border: 1px solid #e5e7eb !important;
        border-radius: 0.375rem !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        font-size: 0.875rem !important;
        z-index: 9999 !important;
        overflow: hidden !important;
    }
    .select2-search--dropdown {
        padding: 8px !important;
    }
    .select2-search--dropdown .select2-search__field {
        border: 1px solid #d1d5db !important;
        border-radius: 0.375rem !important;
        padding: 6px 10px !important;
        font-size: 0.875rem !important;
        outline: none !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .select2-search--dropdown .select2-search__field:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
    }
    .select2-results__option {
        padding: 8px 12px !important;
        font-size: 0.875rem !important;
        color: #374151 !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #2563eb !important;
        color: #ffffff !important;
    }
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #eff6ff !important;
        color: #1d4ed8 !important;
        font-weight: 600 !important;
    }
    .select2-container {
        width: 100% !important;
    }
</style>

<!-- Main Content -->
<main class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-w-0">

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
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Edit Outsider</h1>
        <p class="text-gray-600">Update data outsider: {{ $user->profile->full_name ?? $user->username }}</p>
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
    <form action="{{ route('admin.outsiders.update', $user->id) }}" method="POST" class="max-w-4xl" id="outsiderForm">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <!-- Basic Information -->
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dasar</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="full_name" id="full_name"
                            value="{{ old('full_name', $user->profile->full_name ?? '') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan nama lengkap" required>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" id="email"
                            value="{{ old('email', $user->email) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="email@example.com" required>
                    </div>

                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                            Nomor Telepon
                        </label>
                        <input type="text" name="phone_number" id="phone_number"
                            value="{{ old('phone_number', $user->profile->phone ?? $user->outsider->phone_number ?? '') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="081234567890">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password Baru (Opsional)
                        </label>
                        <input type="password" name="password" id="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Kosongkan jika tidak ingin mengubah">
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
                            <option value="guru" {{ old('type', $user->outsider->type) == 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="ortu" {{ old('type', $user->outsider->type) == 'ortu' ? 'selected' : '' }}>Orang Tua</option>
                        </select>
                    </div>

                    <div id="school_field" @style(['display: ' . (old('type', $user->outsider->type) == 'guru' ? 'block' : 'none')])>
                        <label for="school_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Sekolah (Guru) <span class="text-red-500">*</span>
                        </label>
                        <select name="school_id" id="school_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value=""></option>
                            @foreach($schools as $school)
                            <option value="{{ $school->id }}"
                                {{ old('school_id', $user->outsider->interns->first()->school_id ?? '') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="intern_field" @style(['display: ' . (old('type', $user->outsider->type) == 'ortu' ? 'block' : 'none')])>
                        <label for="intern_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Siswa (Orang Tua) <span class="text-red-500">*</span>
                        </label>
                        <select name="intern_id" id="intern_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value=""></option>
                            @foreach ($interns as $intern)
                            <option value="{{ $intern->id }}"
                                {{ old('intern_id', $user->outsider->interns->first()->id ?? '') == $intern->id ? 'selected' : '' }}>
                                {{ $intern->user->profile->full_name ?? $intern->user->username }} - {{ $intern->school->name ?? '-' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Notification Settings (for ortu only) -->
            <div class="p-6 border-b border-gray-200" id="notification_section" @style(['display: ' . (old('type', $user->outsider->type) == 'ortu' ? 'block' : 'none')])>
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
                    <input type="checkbox" name="notif_enabled" id="edit_notif_enabled" value="1"
                        {{ old('notif_enabled', $user->outsider->notif_enabled) ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="edit_notif_enabled" class="ml-2 text-sm text-gray-700 cursor-pointer">Aktifkan notifikasi WhatsApp</label>
                </div>
            </div>

            <!-- Account Settings -->
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Pengaturan Akun</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="edit_outsider_is_active" value="1"
                            {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="edit_outsider_is_active" class="ml-2 text-sm text-gray-700 cursor-pointer">Akun Aktif</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_confirm" id="edit_outsider_is_confirm" value="1"
                            {{ old('is_confirm', $user->is_confirm) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="edit_outsider_is_confirm" class="ml-2 text-sm text-gray-700 cursor-pointer">Email Terkonfirmasi</label>
                    </div>

                    {{-- <div class="flex items-center">
                            <input type="checkbox" name="is_reset_token" value="1"
                                {{ old('is_reset_token', $user->is_reset_token) ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label class="ml-2 text-sm text-gray-700">Wajib Reset Password</label>
                </div> --}}
            </div>
        </div>


        <!-- Submit Button -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md transition-colors duration-200">
                <i class="fa fa-save mr-2"></i>Update Outsider
            </button>
        </div>
        </div>
    </form>
</main>

<!-- Select2 JS & Dynamic Fields Handler -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi Select2 Searchable Dropdown
        $('#school_id').select2({
            placeholder: 'Pilih atau cari sekolah...',
            allowClear: true,
            width: '100%'
        });

        $('#intern_id').select2({
            placeholder: 'Pilih atau cari nama pemagang...',
            allowClear: true,
            width: '100%'
        });

        const typeSelect = document.getElementById('type');
        const schoolField = document.getElementById('school_field');
        const internField = document.getElementById('intern_field');
        const notificationSection = document.getElementById('notification_section');

        function toggleFields() {
            const selected = typeSelect.value;
            schoolField.style.display = selected === 'guru' ? 'block' : 'none';
            internField.style.display = selected === 'ortu' ? 'block' : 'none';
            if (notificationSection) {
                notificationSection.style.display = selected === 'ortu' ? 'block' : 'none';
            }

            // Perbarui lebar Select2 saat kontainer yang sebelumnya disembunyikan ditampilkan
            if (selected === 'guru') {
                $('#school_id').next('.select2-container').css('width', '100%');
            } else if (selected === 'ortu') {
                $('#intern_id').next('.select2-container').css('width', '100%');
            }
        }

        typeSelect.addEventListener('change', toggleFields);
        toggleFields();

        // Validasi submit form agar tidak terjadi error browser non-focusable control
        $('#outsiderForm').on('submit', function(e) {
            const selected = typeSelect.value;
            if (selected === 'guru') {
                if (!$('#school_id').val()) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Sekolah Belum Dipilih',
                            text: 'Silakan pilih atau cari sekolah untuk outsider tipe Guru.',
                            confirmButtonColor: '#2563eb'
                        }).then(() => {
                            $('#school_id').select2('open');
                        });
                    } else {
                        alert('Silakan pilih atau cari sekolah untuk tipe Guru.');
                        $('#school_id').select2('open');
                    }
                    return false;
                }
            } else if (selected === 'ortu') {
                if (!$('#intern_id').val()) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Siswa Belum Dipilih',
                            text: 'Silakan pilih atau cari siswa/pemagang untuk outsider tipe Orang Tua.',
                            confirmButtonColor: '#2563eb'
                        }).then(() => {
                            $('#intern_id').select2('open');
                        });
                    } else {
                        alert('Silakan pilih atau cari siswa untuk tipe Orang Tua.');
                        $('#intern_id').select2('open');
                    }
                    return false;
                }
            }
        });
    });
</script>
@endsection