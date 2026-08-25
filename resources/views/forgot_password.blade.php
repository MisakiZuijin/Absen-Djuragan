<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    @vite('resources/css/app.css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="flex w-full max-w-4xl mx-auto bg-white rounded-3xl shadow-2xl overflow-hidden border-2 border-cyan-400">
        <!-- Panel Kiri dengan Logo -->
        <div class="hidden md:flex w-1/2 bg-gray-800 items-center justify-center p-12">
            <svg class="w-48 h-48" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_1_2)">
                    <path d="M20 35 L50 20 L80 35 L80 65 L50 80 L20 65 Z" fill="#FFFFFF"/>
                    <path d="M20 35 L50 50 L80 35" stroke="#1F2937" stroke-width="5"/>
                    <path d="M50 50 L50 80" stroke="#1F2937" stroke-width="5"/>
                    <path d="M25 40 L50 55 L75 40" fill="#FFFFFF"/>
                    <path d="M25 40 L50 55 L75 40" stroke="#EF4444" stroke-width="5"/>
                    <path d="M50 55 L50 75" stroke="#EF4444" stroke-width="5"/>
                </g>
                <defs>
                    <clipPath id="clip0_1_2">
                        <rect width="100" height="100" fill="white"/>
                    </clipPath>
                </defs>
            </svg>
        </div>

        <!-- Panel Kanan dengan Form -->
        <div class="w-full md:w-1/2 p-8 md:p-12">
            <div class="flex flex-col h-full justify-center">
                <div class="text-center mb-8">
                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    <h1 class="text-2xl font-bold text-gray-800">Reset Password</h1>
                    <p class="text-gray-500 mt-2 text-sm">Masukkan email Anda. Kami akan mengirimkan link konfirmasi untuk mengubah kata sandi Anda.</p>
                </div>
                
                {{-- Menampilkan pesan sukses jika ada --}}
                @if (session('status'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p>{{ session('status') }}</p>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('forget-password.action') }}">
                    @csrf
                    <div>
                        <label for="email" class="text-sm font-medium text-gray-700">Email</label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-cyan-400 transition"
                                   placeholder="anda@email.com">
                            
                            {{-- Menampilkan pesan error validasi jika ada --}}
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="w-full bg-gray-800 text-white font-semibold py-3 px-4 rounded-lg hover:bg-gray-700 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            Continue
                        </button>
                    </div>
                </form>

                <div class="text-center mt-6">
                    <a href="{{ route('login.view') }}" class="text-sm font-medium text-gray-600 hover:text-cyan-500">
                        Kembali ke Login
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>