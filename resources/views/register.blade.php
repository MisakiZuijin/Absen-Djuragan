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
</head>

<body class="h-screen bg-gray-100">

    <div class="floating-box hidden" id="notification-box">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span id="notification-message">{{ session('failed') ?? ((isset($errors) ? $errors->first() : null) ?? 'Gagal Register') }}</span>
    </div>

    @if (session('failed') || (isset($errors) && $errors->any()))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var box = document.getElementById('notification-box');
            var message = "{{ session('failed') ?? (isset($errors) ? $errors->first() : '') }}";

            if (message) {
                document.getElementById('notification-message').innerText = message;
                box.classList.remove('hidden');
                box.classList.add('show');

                setTimeout(function() {
                    box.classList.remove('show');
                    box.classList.add('hidden');
                }, 3000);
            }
        });
    </script>
    @endif

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
                <form method="POST" action="{{ route('register.action') }}">
                    @csrf
                    <!-- Nama Lengkap -->
                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-semibold mb-1" for="full_name">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="shadow appearance-none border border-gray-300 rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1 focus:ring-gray-500"
                            id="full_name" name="full_name" type="text" placeholder="Masukkan nama lengkap"
                            value="{{ old('full_name') }}">
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-semibold mb-1" for="email">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="shadow appearance-none border border-gray-300 rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1 focus:ring-gray-500"
                            id="email" name="email" type="email" placeholder="Masukkan email"
                            value="{{ old('email') }}">
                    </div>

                    <!-- Username -->
                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-semibold mb-1" for="username">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="shadow appearance-none border border-gray-300 rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1 focus:ring-gray-500"
                            id="username" name="username" type="text" placeholder="Masukkan username"
                            value="{{ old('username') }}">
                    </div>

                    <!-- Tempat Tanggal Lahir - Responsive Grid Layout -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-1" for="birth_place">
                                Tempat Lahir <span class="text-red-500">*</span>
                            </label>
                            <input
                                class="shadow appearance-none border border-gray-300 rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1 focus:ring-gray-500"
                                id="birth_place" name="birth_place" type="text" placeholder="Masukkan tempat lahir"
                                value="{{ old('birth_place') }}">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-1" for="birth_date">
                                Tanggal Lahir <span class="text-red-500">*</span>
                            </label>
                            <input
                                class="shadow appearance-none border border-gray-300 rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1 focus:ring-gray-500"
                                id="birth_date" name="date_of_birth" type="date" value="{{ old('date_of_birth') }}">
                        </div>
                    </div>

                    <!-- Gender -->
                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-semibold mb-1" for="gender">
                            Jenis Kelamin <span class="text-red-500">*</span>
                        </label>
                        <select
                            class="shadow appearance-none border border-gray-300 rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1 focus:ring-gray-500"
                            id="gender" name="gender">
                            <option value="" disabled selected>Pilih Jenis Kelamin</option>
                            <option value="Laki-laki" {{ old('gender') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('gender') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
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
                                No Telp <span class="text-red-500">*</span>
                            </label>
                            <input
                                class="shadow appearance-none border border-gray-300 rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1 focus:ring-gray-500"
                                id="phone" name="phone" type="text" placeholder="08.. ... ...."
                                value="{{ old('phone') }}">
                        </div>
                    </div>

                    <!-- Asal Sekolah/Kampus -->
                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-semibold mb-1" for="school">
                            Asal Sekolah/Kampus <span class="text-red-500">*</span>
                        </label>
                        <select
                            class="shadow border border-gray-300 rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1 focus:ring-gray-500 select2"
                            id="school" name="school_origin_id">
                            <option value="" disabled selected>--Pilih asal sekolah/kampus--</option>
                            @foreach ($schoolList as $school)
                            <option value="{{ $school->id }}"
                                {{ old('school_origin_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                            @endforeach
                        </select>
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
                                    class="shadow appearance-none border border-gray-300 rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1 focus:ring-gray-500"
                                    id="password" name="password" type="password"
                                    placeholder="Masukkan password">
                                <i class="fa fa-eye absolute inset-y-0 right-0 mt-3 flex items-center pr-3 cursor-pointer text-gray-500"
                                    id="togglePassword3"></i>
                            </div>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-1" for="password_confirmation">
                                Konfirmasi Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    class="shadow appearance-none border border-gray-300 rounded w-full py-2 px-2 text-gray-700 leading-tight focus:outline-none focus:ring-1 focus:ring-gray-500"
                                    id="password_confirmation" name="password_confirmation" type="password"
                                    placeholder="Konfirmasi password">
                                <i class="fa fa-eye absolute inset-y-0 right-0 mt-2 flex items-center pr-3 cursor-pointer text-gray-500"
                                    id="togglePassword4"></i>
                            </div>
                        </div>
                    </div>

                    <input type="number" id="gps_support" name="is_gps_available" hidden>

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
            $('.select2').select2();
        });
    </script>
</body>

</html>