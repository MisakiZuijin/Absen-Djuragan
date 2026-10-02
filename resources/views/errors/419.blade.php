<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ $appSetting->favicon_url ?? asset('favicon.ico') }}">
    <title>419 - Sesi Kedaluwarsa | {{ $appSetting->app_name ?? 'Absen Djuragan' }}</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center bg-white p-8 md:p-10 rounded-3xl shadow-xl shadow-slate-100 border border-slate-100">
        <div class="w-24 h-24 bg-blue-50 text-blue-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner text-4xl">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div class="text-7xl font-extrabold text-slate-800 tracking-tight mb-2">419</div>
        <h1 class="text-xl font-bold text-slate-700 mb-2">Sesi Anda Telah Berakhir</h1>
        <p class="text-slate-500 text-sm mb-6 leading-relaxed">
            Halaman telah terbuka terlalu lama atau token keamanan (CSRF) sudah kedaluwarsa. Sistem akan menyegarkan sesi Anda secara otomatis.
        </p>
        @php
            $homeUrl = url('/');
            if (auth()->check()) {
                $roleId = (int) auth()->user()->role_id;
                $homeUrl = match($roleId) {
                    1, 7 => route('admin.home'),
                    3 => route('user.home'),
                    5 => route('outsider.dashboard'),
                    6 => route('assistant.dashboard'),
                    default => url('/'),
                };
            }
        @endphp
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ $homeUrl }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 shadow-md shadow-blue-200 transition">
                <i class="fa-solid fa-rotate-right"></i> Segarkan Halaman Sekarang
            </a>
            <a href="{{ $homeUrl }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-slate-200 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition">
                <i class="fa-solid fa-house"></i> Beranda
            </a>
        </div>
        <p class="text-xs text-slate-400 mt-6">
            Otomatis menyegarkan dalam <span id="countdown" class="font-bold text-blue-600">3</span> detik...
        </p>
    </div>

    <script>
        let seconds = 3;
        const countdownEl = document.getElementById('countdown');
        const timer = setInterval(function() {
            seconds--;
            if (countdownEl) countdownEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = "{{ $homeUrl }}";
            }
        }, 1000);
    </script>
</body>
</html>
