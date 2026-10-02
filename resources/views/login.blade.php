<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="apple-mobile-web-app-title" content="{{ $appSetting->app_name ?? 'Absen Djuragan' }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ $appSetting->favicon_url ?? asset('favicon.ico') }}">
    <title>Login | {{ $appSetting->app_name ?? 'Absen Djuragan' }}</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="h-screen bg-gray-100">

    <!-- Notification Box -->
    @if (session('success'))
    <div class="floating-box hidden" id="notification-box">
        <i class="fa-solid fa-check-circle"></i>
        {{ session('success') }}
    </div>
    @elseif (session('warning'))
    <div class="floating-box hidden" id="notification-box">
        <i class="fa-solid fa-clock-rotate-left"></i>
        {{ session('warning') }}
    </div>
    @else
    <div class="floating-box hidden" id="notification-box">
        <i class="fa-solid fa-triangle-exclamation"></i>
        {{ session('error') ?? ((isset($errors) ? $errors->first() : null) ?? 'Gagal Login') }}
    </div>
    @endif

    <!-- Success & Error Notification Handling -->
    @if (session('success') || session('warning') || session('error') || (isset($errors) && $errors->any() && $errors->first() != null))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function showBox() {
                var box = document.getElementById('notification-box');
                if (!box) return;
                box.classList.remove('hidden');
                box.classList.add('show');

                if ("{{ session('success') }}") {
                    box.classList.add('bg-green-500');
                } else if ("{{ session('warning') }}") {
                    box.classList.add('bg-amber-500');
                } else {
                    box.classList.add('bg-red-500');
                }
            }

            function hideBox() {
                var box = document.getElementById('notification-box');
                if (!box) return;
                box.classList.remove('show');
                box.classList.add('hidden');
            }

            showBox();
            setTimeout(hideBox, 4000);
        });
    </script>
    @endif

    <div class="relative grid grid-cols-1 md:grid-cols-2 h-full">

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
            <div class="absolute inset-0 -z-10 md:bg-white"></div>
        </div>

        <!-- Mobile Version -->
        <div class="pt-10 bg-white md:hidden">
            <div class="relative bg-gray-900 flex items-center justify-center mx-auto rounded-full w-[150px] h-[150px] mt-20 overflow-hidden shadow-lg"
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

                <!-- Padlock Icon -->
                <div class="text-center">
                    <i class="fas fa-lock text-gray-800 text-1xl"></i>
                </div>

                <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Log In</h2>
                <form method="POST" action="{{ route('login.action') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="username">Username/Email</label>
                        <input
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="username" name="username" type="text" placeholder="Masukkan username / email"
                            value="{{ old('username') }}">
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                        <div class="relative">
                            <input
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline pr-10"
                                id="password" name="password" type="password" placeholder="Masukkan password">
                            <i class="fa fa-eye absolute inset-y-0 right-0 mt-3 flex items-center pr-3 cursor-pointer text-gray-500"
                                id="togglePassword3"></i>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <input class="p-2 leading-tight mr-2" type="checkbox" id="remember-me">
                            <label class="text-sm text-gray-700" for="remember-me">Ingat Saya</label>
                        </div>
                        <div class="inline-block align-baseline text-sm text-gray-700">
                            Lupa kata sandi? <a href="{{ route('forget-password.view') }}"><span
                                    class="text-red-500 font-bold hover:underline">Reset</span></a>
                        </div>
                    </div>

                    <div class="flex items-center justify-center">
                        <button
                            class="bg-gray-900 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-40"
                            type="submit">
                            Login
                        </button>
                    </div>

                    <div class="text-center mt-4">
                        <p class="text-sm text-gray-700">
                            Belum punya akun? <a href="{{ route('register.view') }}"
                                class="text-red-500 hover:underline font-bold">Daftar</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/index/login.js') }}"></script>
</body>

</html>