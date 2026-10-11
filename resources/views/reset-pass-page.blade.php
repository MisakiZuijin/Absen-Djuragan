<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Include Font Awesome -->
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-8 md:p-10 border border-gray-200">
        <!-- Logo Web & Heading -->
        <div class="text-center mb-6">
            <div class="mb-3 flex justify-center">
                <img src="{{ $appSetting->logo_url ?? asset('img/logo.svg') }}" alt="{{ $appSetting->app_name ?? 'Logo' }}" class="h-16 object-contain drop-shadow">
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Buat Password Baru</h1>
            <p class="text-gray-500 text-sm mt-1">Masukkan password baru untuk akun Anda (minimal 8 karakter).</p>
        </div>

        {{-- Pesan error jika ada --}}
        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded-r text-sm font-medium" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @error('password')
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded-r text-sm font-medium" role="alert">
                {{ $message }}
            </div>
        @enderror

        <!-- Reset Password Form -->
        <form action="{{ route('change-password.action', ['jwt' => $key]) }}" method="POST" id="passwordForm">
            @csrf
            <!-- Password Baru -->
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password Baru</label>
                <div class="relative flex items-center">
                    <input
                        class="w-full shadow-sm appearance-none border border-gray-300 rounded-lg py-2.5 pl-3.5 pr-11 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-red-400 text-sm"
                        id="password" name="password" type="password" placeholder="Minimal 8 karakter" required minlength="8">
                    <button type="button" id="btnTogglePassword1" tabindex="-1" class="absolute right-0 inset-y-0 pr-3.5 pl-2 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer">
                        <i class="fa fa-eye text-base" id="togglePassword1"></i>
                    </button>
                </div>
            </div>

            <!-- Konfirmasi Password -->
            <div class="mb-3">
                <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password Baru</label>
                <div class="relative flex items-center">
                    <input
                        class="w-full shadow-sm appearance-none border border-gray-300 rounded-lg py-2.5 pl-3.5 pr-11 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-red-400 text-sm"
                        id="confirmPassword" name="password_confirmation" type="password" placeholder="Ulangi password baru" required minlength="8">
                    <button type="button" id="btnTogglePassword2" tabindex="-1" class="absolute right-0 inset-y-0 pr-3.5 pl-2 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer">
                        <i class="fa fa-eye text-base" id="togglePassword2"></i>
                    </button>
                </div>
            </div>

            <p id="error-message" class="text-red-500 text-xs mt-1 mb-2 hidden"></p>

            <!-- Submit Button -->
            <div class="mt-6">
                <button
                    class="w-full bg-red-800 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-lg shadow transition focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 cursor-pointer"
                    type="submit">Simpan Password Baru</button>
            </div>

            <div class="text-center mt-5">
                <a href="{{ route('login.view') }}" class="text-sm font-medium text-gray-600 hover:text-red-700 transition">
                    Kembali ke Login
                </a>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/index/reset-password.js') }}"></script>
</body>

</html>
