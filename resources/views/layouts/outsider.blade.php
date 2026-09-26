<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Outsider Panel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @livewireStyles
</head>

<body class="bg-gray-100 text-gray-800">


    {{-- Header Visual --}}
    <div class="relative h-[250px]">
        @php
        $birth_date = $user->profile->date_of_birth ?? null;
        $today = now()->format('m-d');
        $userBirth = $birth_date ? \Carbon\Carbon::parse($birth_date)->format('m-d') : null;
        @endphp

        <img src="{{ asset($today === $userBirth ? 'img/bg2.jpg' : 'img/bg.jpg') }}"
            alt="Background Image"
            class="w-full h-full object-cover md:rounded-br-[40px] no-select">

        {{-- Welcome Message --}}
        <div class="absolute inset-0 flex items-center justify-center z-10 p-2 md:p-4">
            <div class="typewriter text-xl md:text-3xl font-bold text-white text-center italic">
                <h1 id="typewriter-text"></h1>
            </div>

            {{-- Profile Info --}}
            <div class="absolute bottom-4 left-4 md:left-10 flex items-center space-x-2 md:space-x-4 text-white z-20 bg-black p-1 md:p-3 bg-opacity-50 rounded-3xl">
                <i class="fas fa-user-circle text-2xl md:text-3xl"></i>
                <div class="text-xs md:text-sm">
                    <div class="font-bold text-xs md:text-sm">{{ $user->profile->full_name ?? 'Outsider' }}</div>
                    <div class="text-xs md:text-sm">{{ $user->profile->NIP ?? '-' }}</div>
                </div>
            </div>

            {{-- Logout Button --}}
            <div class="absolute bottom-4 right-4 md:right-10 text-white z-20">
                <button class="logoutModal bg-black bg-opacity-50 p-3 rounded-full hover:bg-opacity-75 transition-colors" title="Logout">
                    {{-- Gunakan SVG untuk ikon yang lebih tajam dan modern --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Date Display --}}
        <div class="absolute top-4 left-4 md:left-10 flex items-center space-x-1 md:space-x-2 text-white z-20 text-xs md:text-2xl p-1 md:p-3">
            <i class="fas fa-calendar-day text-xs md:text-2xl"></i>
            <span class="text-xs md:text-2xl">{{ now()->translatedFormat('l') }}, {{ now()->format('d F Y') }}</span>
        </div>

        {{-- Real-time Clock --}}
        <div class="absolute top-4 right-4 md:right-10 text-white z-20 text-xs md:text-2xl p-1 md:p-3 rounded-xl">
            <span id="current-time" class="text-xs md:text-2xl">--:--:--</span>
        </div>
    </div>

    {{-- Main Content --}}
    <main class="min-h-screen p-4">
        @yield('contents')
    </main>

    {{-- Logout Modal --}}
    <div id="logout-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-4/5 md:w-1/3">
            <h2 class="text-xl font-bold mb-4">Konfirmasi Keluar</h2>
            <p>Apakah Anda yakin ingin keluar dari halaman ini?</p>
            <div class="flex justify-end mt-4">
                <button type="button" id="closeLogout" class="px-4 py-2 bg-gray-600 text-white rounded-lg mr-2">Batal</button>
                <a href="{{ url('/logout') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg">Keluar</a>
            </div>
        </div>
    </div>

    {{-- Notifikasi --}}
    <div id="success-box" class="floating-box {{ session('success') ? '' : 'hidden' }} bg-green-500 text-white p-4 rounded">
        <i class="fa-solid fa-check-circle"></i> {{ session('success') ?? '' }}
    </div>

    <div id="error-box" class="floating-box {{ isset($error) ? '' : 'hidden' }} bg-red-500 text-white p-4 rounded">
        <i class="fa-solid fa-triangle-exclamation"></i> {{ $error ?? '' }}
    </div>

    {{-- Quotes Data Payload --}}
    <script type="application/json" id="quotes-data">@json($quotes ?? [])</script>

    {{-- Livewire Scripts --}}
    @livewireScripts

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Font Awesome --}}
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    {{-- Clock + Typewriter Script --}}
    <script>
        function updateClock() {
            const now = new Date();
            const time = now.toTimeString().split(' ')[0];
            document.getElementById('current-time').textContent = time;
        }
        setInterval(updateClock, 1000);
        updateClock();

        document.addEventListener('DOMContentLoaded', () => {
            const quotesEl = document.getElementById('quotes-data');
            const texts = quotesEl ? JSON.parse(quotesEl.textContent || '[]') : [];
            const element = document.getElementById('typewriter-text');

            function changeQuote() {
                if (!texts.length || !element) return;
                const random = texts[Math.floor(Math.random() * texts.length)];
                element.innerHTML = random.split(' ').map((word, i) => ((i + 1) % 4 === 0 ? word + '<br>' : word)).join(' ');
            }

            changeQuote();
            setInterval(changeQuote, 10000);

            // Logout Modal Handler
            $('.logoutModal').click(() => $('#logout-modal').removeClass('hidden'));
            $('#closeLogout').click(() => $('#logout-modal').addClass('hidden'));
            $(window).click(e => {
                if (e.target.id === 'logout-modal') $('#logout-modal').addClass('hidden');
            });

            // Notification Boxes Auto Hide
            const successBox = document.getElementById('success-box');
            if (successBox && !successBox.classList.contains('hidden')) {
                setTimeout(() => successBox.classList.add('hidden'), 3000);
            }
            const errorBox = document.getElementById('error-box');
            if (errorBox && !errorBox.classList.contains('hidden')) {
                setTimeout(() => errorBox.classList.add('hidden'), 3000);
            }

            if (typeof $ !== 'undefined') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    statusCode: {
                        419: function() {
                            alert('Sesi Anda telah berakhir. Halaman akan dimuat ulang.');
                            window.location.reload();
                        },
                        403: function() {
                            alert('Anda tidak memiliki izin untuk melakukan tindakan ini.');
                        }
                    }
                });
            }
            window.addEventListener('unhandledrejection', function(event) {
                if (event.reason && (event.reason.status === 419 || event.reason.status === 401)) {
                    window.location.reload();
                }
            });
        });
    </script>

    {{-- Script tambahan dari halaman menggunakan @push('scripts') --}}
    @stack('scripts')
</body>

</html>