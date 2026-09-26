<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak | Absen Djuragan</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center bg-white p-8 md:p-10 rounded-3xl shadow-xl shadow-slate-100 border border-slate-100">
        <div class="w-24 h-24 bg-amber-50 text-amber-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner text-4xl">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="text-7xl font-extrabold text-slate-800 tracking-tight mb-2">403</div>
        <h1 class="text-xl font-bold text-slate-700 mb-2">Akses Tidak Diizinkan</h1>
        <p class="text-slate-500 text-sm mb-8 leading-relaxed">
            Anda tidak memiliki hak akses (permission) untuk membuka halaman atau melakukan tindakan ini.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="javascript:history.back()" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-slate-200 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
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
            <a href="{{ $homeUrl }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-slate-900 text-white font-semibold text-sm hover:bg-slate-800 shadow-md shadow-slate-200 transition">
                <i class="fa-solid fa-house"></i> Beranda
            </a>
        </div>
    </div>
</body>
</html>
