<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ $appSetting->favicon_url ?? asset('favicon.ico') }}">
    <title>Register | {{ $appSetting->app_name ?? 'Absen Djuragan' }}</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Select2 Custom Tailwind Styling for Register Page */
        .select2-container {
            width: 100% !important;
            display: block !important;
        }
        .select2-container--default .select2-selection--single {
            height: 42px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.375rem !important;
            padding: 0 10px !important;
            display: flex !important;
            align-items: center !important;
            background-color: #ffffff !important;
            font-size: 0.875rem !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            transition: all 0.15s ease-in-out !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #374151 !important;
            line-height: 40px !important;
            padding-left: 2px !important;
            padding-right: 24px !important;
            font-size: 0.875rem !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #9ca3af !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 8px !important;
        }
        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #6b7280 !important;
            box-shadow: 0 0 0 1px #6b7280 !important;
            outline: none !important;
        }
        select.border-rose-500 + .select2-container .select2-selection--single {
            border-color: #f43f5e !important;
        }
        .select2-dropdown {
            border: 1px solid #e5e7eb !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
            font-size: 0.875rem !important;
            z-index: 99999 !important;
            overflow: hidden !important;
            margin-top: 4px !important;
            background-color: #ffffff !important;
        }
        .select2-search--dropdown {
            padding: 8px 10px !important;
            background-color: #f9fafb !important;
            border-bottom: 1px solid #f3f4f6 !important;
            position: relative !important;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1px solid #d1d5db !important;
            border-radius: 0.375rem !important;
            padding: 7px 10px 7px 30px !important;
            font-size: 16px !important; /* Mencegah auto-zoom di iOS Safari */
            outline: none !important;
            width: 100% !important;
            box-sizing: border-box !important;
            background-color: #ffffff !important;
        }
        .select2-search--dropdown::before {
            content: "\f002";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px;
            color: #9ca3af;
            pointer-events: none;
            z-index: 10;
        }
        .select2-search--dropdown .select2-search__field:focus {
            border-color: #6b7280 !important;
            box-shadow: 0 0 0 1px #6b7280 !important;
        }
        .select2-results__options {
            max-height: 240px !important;
            -webkit-overflow-scrolling: touch !important;
        }
        .select2-results__option {
            padding: 9px 12px !important;
            font-size: 0.875rem !important;
            color: #374151 !important;
            cursor: pointer !important;
            touch-action: pan-y !important;
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0.05) !important;
            min-height: 38px !important;
            display: flex !important;
            align-items: center !important;
            border-bottom: 1px solid #f8fafc !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #111827 !important;
            color: #ffffff !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #f3f4f6 !important;
            color: #111827 !important;
            font-weight: 600 !important;
        }

        /* Mode Mobile Touch Optimization */
        @media (max-width: 767px) {
            .select2-container {
                width: 100% !important;
            }
            .select2-dropdown {
                width: 100% !important;
            }
            .select2-results__option {
                padding: 12px 14px !important;
                font-size: 0.875rem !important;
                min-height: 44px !important;
            }
        }
    </style>
</head>

<body class="h-screen bg-gray-100">

    <!-- Slide Popup Notifikasi Atas (Hanya untuk Kegagalan Fatal / Sistem) -->
    <div id="notification-box"
        data-fatal-error="{{ session('fatal_error') ?? session('error') ?? '' }}"
        class="fixed top-5 left-1/2 -translate-x-1/2 z-[9999] transition-all duration-300 ease-out transform -translate-y-16 opacity-0 pointer-events-none max-w-[92vw] sm:max-w-md w-auto"
        role="alert">
        <div class="flex items-center gap-2.5 px-4 py-3 rounded-2xl bg-rose-600 text-white shadow-2xl text-xs sm:text-sm font-medium border border-rose-500/50 backdrop-blur-sm">
            <i class="fa-solid fa-circle-exclamation text-base shrink-0 text-white"></i>
            <span id="notification-message" class="leading-snug break-words"></span>
        </div>
    </div>

    <script>
        function showToast(message) {
            const notifBox = document.getElementById('notification-box');
            const notifMsg = document.getElementById('notification-message');
            if (!notifBox || !notifMsg || !message) return;

            notifMsg.textContent = message;
            notifBox.classList.remove('-translate-y-16', 'opacity-0', 'pointer-events-none');
            notifBox.classList.add('translate-y-0', 'opacity-100', 'pointer-events-auto');

            clearTimeout(window.__notifTimer);
            window.__notifTimer = setTimeout(() => {
                notifBox.classList.remove('translate-y-0', 'opacity-100', 'pointer-events-auto');
                notifBox.classList.add('-translate-y-16', 'opacity-0', 'pointer-events-none');
            }, 5000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            var box = document.getElementById('notification-box');
            if (box && box.dataset.fatalError) {
                showToast(box.dataset.fatalError);
            }
        });
    </script>

    <div class="grid grid-cols-1 md:grid-cols-2 h-full">

        <!-- Left side with image / background -->
        <!-- Desktop Version -->
        <div class="relative bg-gray-900 flex items-center justify-center rounded-br-[80px] hidden md:flex overflow-hidden"
            @if(!empty($appSetting->login_background_url)) style="background-image: url('{{ $appSetting->login_background_url }}'); background-size: cover; background-position: center;" @endif>
            @if($appSetting->login_background_url)
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-[1px]"></div>
            @endif
            <div class="relative z-10 flex flex-col items-center">
                <img src="{{ $appSetting->logo_url ?? asset('img/logo.svg') }}" alt="{{ $appSetting->app_name ?? 'Logo' }}" class="w-[268px] h-[266.95px] object-contain drop-shadow-xl">
            </div>
            <div class="absolute inset-0 -z-10 bg-white"></div>
        </div>

        <!-- Mobile Version -->
        <div class="pt-10 bg-white md:hidden">
            <div class="relative bg-gray-900 flex items-center justify-center mx-auto rounded-full w-[150px] h-[150px] overflow-hidden shadow-lg"
                @if(!empty($appSetting->login_background_url)) style="background-image: url('{{ $appSetting->login_background_url }}'); background-size: cover; background-position: center;" @endif>
                @if($appSetting->login_background_url)
                <div class="absolute inset-0 bg-slate-950/50"></div>
                @endif
                <img src="{{ $appSetting->logo_url ?? asset('img/logo.svg') }}" alt="{{ $appSetting->app_name ?? 'Logo' }}" class="relative z-10 w-[100px] object-contain drop-shadow">
            </div>
        </div>

        <!-- Right side with login form -->
        <div class="relative bg-white flex items-center justify-center p-8 rounded-tl-[80px]">
            <!-- Background for rounded-tl with gray color -->
            <div class="absolute inset-0 -z-10 bg-white md:bg-gray-900"></div>

            <div class="w-full max-w-md">

                <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Sign Up</h2>

                <form id="registerForm" method="POST" action="{{ route('register.action') }}">
                    @csrf
                    <!-- Nama Lengkap -->
                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-semibold mb-1" for="full_name">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="shadow appearance-none border @error('full_name') border-rose-500 focus:ring-rose-500 @else border-gray-300 focus:ring-gray-500 @enderror rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1"
                            id="full_name" name="full_name" type="text" placeholder="Masukkan nama lengkap"
                            required
                            value="{{ old('full_name') }}">
                        @error('full_name')
                            <p class="text-xs text-rose-600 mt-1 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-semibold mb-1" for="email">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="shadow appearance-none border @error('email') border-rose-500 focus:ring-rose-500 @else border-gray-300 focus:ring-gray-500 @enderror rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1"
                            id="email" name="email" type="text" placeholder="contoh: nama@gmail.com atau nama@yahoo.com"
                            required
                            value="{{ old('email') }}">
                        @error('email')
                            <p class="server-error text-xs text-rose-600 mt-1 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                        @enderror
                        <p id="email_error_msg" class="text-xs text-rose-600 mt-1 font-medium hidden"></p>
                    </div>

                    <!-- Username -->
                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-semibold mb-1" for="username">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="shadow appearance-none border @error('username') border-rose-500 focus:ring-rose-500 @else border-gray-300 focus:ring-gray-500 @enderror rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1"
                            id="username" name="username" type="text" placeholder="Masukkan username"
                            value="{{ old('username') }}">
                        @error('username')
                            <p class="text-xs text-rose-600 mt-1 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tempat Tanggal Lahir - Responsive Grid Layout -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-1" for="birth_place">
                                Tempat Lahir <span class="text-red-500">*</span>
                            </label>
                            <input
                                class="shadow appearance-none border @error('birth_place') border-rose-500 focus:ring-rose-500 @else border-gray-300 focus:ring-gray-500 @enderror rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1"
                                id="birth_place" name="birth_place" type="text" placeholder="Masukkan tempat lahir"
                                value="{{ old('birth_place') }}">
                            @error('birth_place')
                                <p class="text-xs text-rose-600 mt-1 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-1" for="birth_date">
                                Tanggal Lahir <span class="text-red-500">*</span>
                            </label>
                            <input
                                class="shadow appearance-none border @error('date_of_birth') border-rose-500 focus:ring-rose-500 @else border-gray-300 focus:ring-gray-500 @enderror rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1"
                                id="birth_date" name="date_of_birth" type="date" value="{{ old('date_of_birth') }}">
                            @error('date_of_birth')
                                <p class="text-xs text-rose-600 mt-1 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Gender & Agama - 2 Grid Layout -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-1" for="gender">
                                Jenis Kelamin <span class="text-red-500">*</span>
                            </label>
                            <select
                                class="shadow appearance-none border @error('gender') border-rose-500 focus:ring-rose-500 @else border-gray-300 focus:ring-gray-500 @enderror rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1"
                                id="gender" name="gender" required>
                                <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Pilih Jenis Kelamin</option>
                                <option value="Laki-laki" {{ old('gender') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('gender') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('gender')
                                <p class="text-xs text-rose-600 mt-1 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-1" for="religion">
                                Agama <span class="text-red-500">*</span>
                            </label>
                            <select
                                class="shadow appearance-none border @error('religion') border-rose-500 focus:ring-rose-500 @else border-gray-300 focus:ring-gray-500 @enderror rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1"
                                id="religion" name="religion" required>
                                <option value="" disabled {{ old('religion') ? '' : 'selected' }}>Pilih Agama</option>
                                <option value="Islam" {{ old('religion') == 'Islam' ? 'selected' : '' }}>Islam</option>
                                <option value="Kristen" {{ old('religion') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                <option value="Katolik" {{ old('religion') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                <option value="Hindu" {{ old('religion') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                <option value="Buddha" {{ old('religion') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                <option value="Khonghucu" {{ old('religion') == 'Khonghucu' ? 'selected' : '' }}>Khonghucu</option>
                            </select>
                            @error('religion')
                                <p class="text-xs text-rose-600 mt-1 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Link Google Maps Domisili / Kos (Wajib) -->
                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-semibold mb-1" for="gmaps_url">
                            Link Google Maps Domisili / Kos <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                class="shadow appearance-none border @error('gmaps_url') border-rose-500 focus:ring-rose-500 @else border-gray-300 focus:ring-gray-500 @enderror rounded w-full py-2 pl-3 pr-9 text-gray-700 leading-tight focus:outline-none focus:ring-1 text-sm"
                                id="gmaps_url" name="gmaps_url" type="text" required
                                placeholder="Contoh: https://maps.app.goo.gl/... atau link Google Maps"
                                value="{{ old('gmaps_url') }}">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-rose-500 pointer-events-none">
                                <i class="fa-solid fa-location-dot"></i>
                            </span>
                        </div>
                        @error('gmaps_url')
                            <p class="text-xs text-rose-600 mt-1 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                        @enderror
                        <input type="hidden" id="home_latitude" name="home_latitude" value="{{ old('home_latitude') }}">
                        <input type="hidden" id="home_longitude" name="home_longitude" value="{{ old('home_longitude') }}">
                        <div id="gmaps_status_msg" class="text-[11px] mt-1"></div>
                    </div>

                    <!-- Keterangan Alamat / Nama Kos (Opsional) -->
                    <div class="mb-3">
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-gray-700 text-sm font-semibold" for="address">
                                Alamat / Keterangan Kos <span class="text-xs text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <span id="address_counter" class="text-xs text-gray-400 font-medium">0/255</span>
                        </div>
                        <input
                            class="shadow appearance-none border @error('address') border-rose-500 focus:ring-rose-500 @else border-gray-300 focus:ring-gray-500 @enderror rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-1 text-sm"
                            id="address" name="address" type="text" maxlength="255" placeholder="Contoh: Kos Putri Melati, Jl. Kaliurang KM 5"
                            value="{{ old('address') }}">
                        @error('address')
                            <p class="server-error text-xs text-rose-600 mt-1 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                        @enderror
                        <p id="address_error_msg" class="text-xs text-rose-600 mt-1 font-medium hidden"></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                        <!-- NIM (Optional) -->
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-1" for="nim">
                                NIM
                            </label>
                            <input
                                class="shadow appearance-none border border-gray-300 rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1 focus:ring-gray-500"
                                id="nim" name="nim" type="text" placeholder="Masukkan nim (optional)"
                                value="{{ old('nim') }}">
                        </div>
                        <!-- No Telp -->
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-1" for="phone">
                                No Telp / WhatsApp <span class="text-red-500">*</span>
                            </label>
                            <input
                                class="shadow appearance-none border @error('phone') border-rose-500 focus:ring-rose-500 @else border-gray-300 focus:ring-gray-500 @enderror rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1"
                                id="phone" name="phone" type="text"
                                placeholder="081234567890"
                                required
                                value="{{ old('phone') }}">
                            @error('phone')
                                <p class="server-error text-xs text-rose-600 mt-1 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror
                            <p id="phone_error_msg" class="text-xs text-rose-600 mt-1 font-medium hidden"></p>
                        </div>
                    </div>

                    <!-- Asal Sekolah/Kampus -->
                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-semibold mb-1" for="school">
                            Asal Sekolah/Kampus <span class="text-red-500">*</span>
                        </label>
                        <select
                            class="shadow border @error('school_origin_id') border-rose-500 focus:ring-rose-500 @else border-gray-300 focus:ring-gray-500 @enderror rounded w-full py-2.5 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-1 select2 bg-white text-sm"
                            id="school" name="school_origin_id" required>
                            <option value="" disabled {{ old('school_origin_id') ? '' : 'selected' }}>--Pilih asal sekolah/kampus--</option>
                            @foreach ($schoolList as $school)
                            <option value="{{ $school->id }}"
                                {{ old('school_origin_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('school_origin_id')
                            <p class="server-error text-xs text-rose-600 mt-1 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                        @enderror
                        <p id="school_error_msg" class="text-xs text-rose-600 mt-1 font-medium hidden"></p>
                    </div>

                    <!-- Password and Konfirmasi Password - 2 Grid Layout for Desktop, 1 Grid Layout for Mobile -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                        <!-- Password -->
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-1" for="password">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    class="shadow appearance-none border @error('password') border-rose-500 focus:ring-rose-500 @else border-gray-300 focus:ring-gray-500 @enderror rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1"
                                    id="password" name="password" type="password"
                                    placeholder="Masukkan password">
                                <i class="fa fa-eye absolute inset-y-0 right-0 mt-3 flex items-center pr-3 cursor-pointer text-gray-500"
                                    id="togglePassword3"></i>
                            </div>
                            @error('password')
                                <p class="text-xs text-rose-600 mt-1 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Konfirmasi Password -->
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-1" for="password_confirmation">
                                Konfirmasi Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    class="shadow appearance-none border @error('password_confirmation') border-rose-500 focus:ring-rose-500 @else border-gray-300 focus:ring-gray-500 @enderror rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1"
                                    id="password_confirmation" name="password_confirmation" type="password"
                                    placeholder="Konfirmasi password">
                                <i class="fa fa-eye absolute inset-y-0 right-0 mt-2 flex items-center pr-3 cursor-pointer text-gray-500"
                                    id="togglePassword4"></i>
                            </div>
                            @error('password_confirmation')
                                <p class="text-xs text-rose-600 mt-1 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <input type="number" id="gps_support" name="is_gps_available" value="1" hidden>

                    <div class="flex items-center justify-center">
                        <button
                            class="bg-gray-900 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-32"
                            type="submit">
                            Daftar
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/index/register.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const $school = $('#school');

            // Inisialisasi Select2 Searchable Dropdown untuk Semua Layar (Mobile & Desktop)
            if ($school.length) {
                $school.select2({
                    width: '100%',
                    placeholder: '--Pilih asal sekolah/kampus--',
                    allowClear: false,
                    language: {
                        noResults: function() {
                            return 'Tidak ada sekolah/kampus ditemukan';
                        }
                    }
                });
            }

            // Atur placeholder pencarian dan fokus otomatis (hanya di desktop non-touch)
            $(document).on('select2:open', function() {
                const isTouch = ('ontouchstart' in window) || (window.innerWidth < 768) || (navigator.maxTouchPoints > 0);
                const searchInput = document.querySelector('.select2-container--open .select2-search__field');
                if (searchInput) {
                    searchInput.setAttribute('placeholder', 'Ketik nama sekolah atau kampus...');
                    if (!isTouch) {
                        setTimeout(() => { searchInput.focus(); }, 50);
                    }
                }
            });

            // FIX BUG SENTUHAN DI MODE MOBILE (iOS & Android):
            // Di perangkat sentuh, blur keyboard virtual sering membatalkan klik opsi.
            // Handler ini mendeteksi ketukan (tap) presisi pada opsi dan langsung memilihnya tanpa tertutup prematur.
            let touchStartY = 0;
            let touchStartX = 0;
            let isScrollingResults = false;

            $(document).on('touchstart', '.select2-results__options', function(e) {
                const touch = e.originalEvent.touches ? e.originalEvent.touches[0] : e;
                touchStartY = touch.clientY;
                touchStartX = touch.clientX;
                isScrollingResults = false;
            });

            $(document).on('touchmove', '.select2-results__options', function(e) {
                const touch = e.originalEvent.touches ? e.originalEvent.touches[0] : e;
                if (Math.abs(touch.clientY - touchStartY) > 8 || Math.abs(touch.clientX - touchStartX) > 8) {
                    isScrollingResults = true; // Pengguna sedang menggeser daftar (scroll), bukan mengetuk
                }
            });

            $(document).on('touchend', '.select2-results__option', function(e) {
                if (isScrollingResults) {
                    return; // Abaikan saat selesai scroll
                }

                const $option = $(this);
                if ($option.hasClass('select2-results__option--load-more') || $option.attr('aria-disabled') === 'true') {
                    return;
                }

                const data = $option.data('data');
                if (data && data.id !== undefined && data.id !== '') {
                    e.preventDefault();
                    e.stopPropagation();

                    $school.val(data.id).trigger('change');
                    $school.trigger({
                        type: 'select2:select',
                        params: { data: data }
                    });
                    $school.select2('close');
                }
            });

            // Helper untuk menampilkan error inline dan border merah tanpa menghapus input
            function setFieldError(inputEl, errorEl, message) {
                if (inputEl) {
                    inputEl.classList.add('border-rose-500', 'focus:ring-rose-500');
                    inputEl.classList.remove('border-gray-300', 'focus:ring-gray-500');
                }
                if (errorEl) {
                    // Sembunyikan error dari server jika ada agar tidak dobel
                    const parent = errorEl.parentElement;
                    if (parent) {
                        const serverError = parent.querySelector('.server-error');
                        if (serverError) serverError.style.display = 'none';
                    }
                    errorEl.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>' + message;
                    errorEl.classList.remove('hidden');
                }
            }

            function clearFieldError(inputEl, errorEl) {
                if (inputEl) {
                    inputEl.classList.remove('border-rose-500', 'focus:ring-rose-500');
                    inputEl.classList.add('border-gray-300', 'focus:ring-gray-500');
                }
                if (errorEl) {
                    const parent = errorEl.parentElement;
                    if (parent) {
                        const serverError = parent.querySelector('.server-error');
                        if (serverError) serverError.style.display = 'none';
                    }
                    errorEl.innerHTML = '';
                    errorEl.classList.add('hidden');
                }
            }

            // Validasi Input Nomor Telepon (Hanya Angka, Tanpa Huruf, Tanpa Tanda Minus)
            const phoneInput = document.getElementById('phone');
            const phoneError = document.getElementById('phone_error_msg');

            function validatePhone(showRequired = false) {
                if (!phoneInput) return true;
                const rawVal = phoneInput.value;
                const val = rawVal.trim();

                if (!val) {
                    if (showRequired) {
                        setFieldError(phoneInput, phoneError, 'Nomor telepon/WhatsApp wajib diisi.');
                        return false;
                    }
                    clearFieldError(phoneInput, phoneError);
                    return true;
                }

                // Cek apakah ada tanda minus (-)
                if (val.includes('-')) {
                    setFieldError(phoneInput, phoneError, 'Nomor telepon tidak boleh ada tanda minus (-). Hanya boleh angka.');
                    return false;
                }

                // Cek apakah ada huruf
                if (/[a-zA-Z]/.test(val)) {
                    setFieldError(phoneInput, phoneError, 'Nomor telepon tidak boleh ada huruf. Hanya boleh angka.');
                    return false;
                }

                // Cek karakter lain selain angka (spasi, simbol, dll)
                if (/[^0-9]/.test(val)) {
                    setFieldError(phoneInput, phoneError, 'Nomor telepon hanya boleh berisi angka (tanpa spasi atau simbol).');
                    return false;
                }

                // Cek panjang digit
                if (val.length < 10) {
                    setFieldError(phoneInput, phoneError, 'Nomor telepon minimal 10 digit angka (saat ini ' + val.length + ' digit).');
                    return false;
                }

                if (val.length > 15) {
                    setFieldError(phoneInput, phoneError, 'Nomor telepon maksimal 15 digit angka (saat ini ' + val.length + ' digit).');
                    return false;
                }

                clearFieldError(phoneInput, phoneError);
                return true;
            }

            if (phoneInput) {
                // Jangan cegah pengetikan dan jangan hapus input! Berikan peringatan langsung jika ada karakter salah
                phoneInput.addEventListener('input', function() {
                    const val = this.value;
                    // Jika terdeteksi minus atau huruf, langsung beri tahu tanpa menghapus
                    if (val.includes('-') || /[a-zA-Z]/.test(val) || /[^0-9]/.test(val.trim())) {
                        validatePhone(false);
                    } else if (!phoneError.classList.contains('hidden')) {
                        // Jika sebelumnya ada error, re-validasi saat pengguna memperbaiki
                        validatePhone(false);
                    }
                });

                phoneInput.addEventListener('blur', function() {
                    validatePhone(false);
                });
            }

            // Validasi Input Email (@gmail.com, @yahoo.com, @email.com)
            const emailInput = document.getElementById('email');
            const emailError = document.getElementById('email_error_msg');
            const allowedEmailRegex = /^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com|yahoo\.co\.id|email\.com)$/i;

            function validateEmail(showRequired = false) {
                if (!emailInput) return true;
                const rawVal = emailInput.value;
                const val = rawVal.trim().toLowerCase();

                if (!val) {
                    if (showRequired) {
                        setFieldError(emailInput, emailError, 'Email wajib diisi.');
                        return false;
                    }
                    clearFieldError(emailInput, emailError);
                    return true;
                }

                if (!val.includes('@')) {
                    setFieldError(emailInput, emailError, "Email harus memiliki tanda '@' (contoh: nama@gmail.com, nama@yahoo.com, atau nama@email.com).");
                    return false;
                }

                if (!allowedEmailRegex.test(val)) {
                    setFieldError(emailInput, emailError, 'Email harus menggunakan domain @gmail.com, @yahoo.com, atau @email.com.');
                    return false;
                }

                clearFieldError(emailInput, emailError);
                return true;
            }

            if (emailInput) {
                emailInput.addEventListener('input', function() {
                    if (!emailError.classList.contains('hidden')) {
                        validateEmail(false);
                    }
                });

                emailInput.addEventListener('blur', function() {
                    validateEmail(false);
                });
            }

            // Validasi & Counter Karakter Alamat (Maksimal 255 Karakter)
            const addressInput = document.getElementById('address');
            const addressError = document.getElementById('address_error_msg');
            const addressCounter = document.getElementById('address_counter');

            function updateAddressCounter() {
                if (!addressInput || !addressCounter) return true;
                const len = addressInput.value.length;
                addressCounter.textContent = `${len}/255`;

                if (len > 255) {
                    addressCounter.classList.remove('text-gray-400', 'text-amber-600');
                    addressCounter.classList.add('text-rose-600', 'font-bold');
                    setFieldError(addressInput, addressError, `Alamat / keterangan kos melebihi batas maksimal 255 karakter (saat ini ${len} karakter). Mohon persingkat isian.`);
                    return false;
                } else if (len === 255) {
                    addressCounter.classList.remove('text-gray-400', 'text-rose-600');
                    addressCounter.classList.add('text-amber-600', 'font-bold');
                    if (addressError && !addressError.classList.contains('hidden')) {
                        clearFieldError(addressInput, addressError);
                    }
                    return true;
                } else {
                    addressCounter.classList.remove('text-rose-600', 'text-amber-600', 'font-bold');
                    addressCounter.classList.add('text-gray-400');
                    if (addressError && !addressError.classList.contains('hidden')) {
                        clearFieldError(addressInput, addressError);
                    }
                    return true;
                }
            }

            if (addressInput) {
                updateAddressCounter();
                addressInput.addEventListener('input', updateAddressCounter);
                addressInput.addEventListener('keyup', updateAddressCounter);
                addressInput.addEventListener('paste', function() {
                    setTimeout(updateAddressCounter, 50);
                });
            }

            // Validasi Input Sekolah / Kampus
            const schoolInput = document.getElementById('school');
            const schoolError = document.getElementById('school_error_msg');

            function validateSchool(showRequired = false) {
                if (!schoolInput) return true;
                const val = schoolInput.value;
                if (!val) {
                    if (showRequired) {
                        setFieldError(schoolInput, schoolError, 'Asal sekolah/kampus wajib dipilih.');
                        return false;
                    }
                    return true;
                }
                clearFieldError(schoolInput, schoolError);
                return true;
            }

            if (schoolInput) {
                schoolInput.addEventListener('change', function() {
                    validateSchool(false);
                });
                $('#school').on('select2:select', function() {
                    validateSchool(false);
                });
            }

            // Validasi saat form di-submit
            const regForm = document.getElementById('registerForm');
            if (regForm) {
                regForm.addEventListener('submit', function(e) {
                    const isEmailValid = validateEmail(true);
                    const isPhoneValid = validatePhone(true);
                    const isAddressValid = updateAddressCounter();
                    const isSchoolValid = validateSchool(true);

                    if (!isEmailValid || !isPhoneValid || !isAddressValid || !isSchoolValid) {
                        e.preventDefault();

                        // Fokuskan ke input yang pertama kali error tanpa menghapus input apapun
                        if (!isEmailValid && emailInput) {
                            emailInput.focus();
                        } else if (!isPhoneValid && phoneInput) {
                            phoneInput.focus();
                        } else if (!isAddressValid && addressInput) {
                            addressInput.focus();
                        } else if (!isSchoolValid && schoolInput) {
                            if (window.innerWidth >= 768 && $('#school').data('select2')) {
                                $('#school').select2('open');
                            } else {
                                schoolInput.focus();
                            }
                        }
                    }
                });
            }

            const gmapsInput = document.getElementById('gmaps_url');
            const gmapsStatus = document.getElementById('gmaps_status_msg');
            let gmapsTimer = null;

            if (gmapsInput) {
                gmapsInput.addEventListener('input', function() {
                    clearTimeout(gmapsTimer);
                    const val = this.value.trim();
                    if (!val) {
                        if (gmapsStatus) gmapsStatus.innerHTML = '';
                        return;
                    }
                    gmapsTimer = setTimeout(async function() {
                        if (gmapsStatus) gmapsStatus.innerHTML = '<span class="text-blue-600"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Memeriksa link Google Maps...</span>';
                        try {
                            const res = await fetch("{{ route('public.resolve-gmaps-url') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ url: val })
                            });
                            const data = await res.json();
                            if (data.success && data.lat && data.lng) {
                                document.getElementById('home_latitude').value = data.lat;
                                document.getElementById('home_longitude').value = data.lng;
                                if (data.address) {
                                    const addrField = document.getElementById('address');
                                    if (addrField && (!addrField.value || addrField.dataset.autoFilled === '1')) {
                                        addrField.value = data.address.slice(0, 255);
                                        addrField.dataset.autoFilled = '1';
                                        updateAddressCounter();
                                    }
                                }
                                if (gmapsStatus) {
                                    gmapsStatus.innerHTML = '<span class="text-emerald-600 font-semibold"><i class="fa-solid fa-circle-check mr-1"></i> Titik lokasi valid ditemukan!</span>';
                                }
                            } else {
                                if (gmapsStatus) {
                                    gmapsStatus.innerHTML = '<span class="text-rose-600"><i class="fa-solid fa-circle-exclamation mr-1"></i> ' + (data.message || 'Link tidak dikenali.') + '</span>';
                                }
                            }
                        } catch (e) {
                            if (gmapsStatus) gmapsStatus.innerHTML = '';
                        }
                    }, 600);
                });
            }
        });
    </script>
</body>

</html>