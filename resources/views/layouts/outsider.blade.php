<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ $appSetting->favicon_url ?? asset('favicon.ico') }}">
    <title>@yield('title') - {{ $appSetting->app_name ?? 'Outsider Panel' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

        <img src="{{ $today === $userBirth ? asset('img/bg2.jpg') : ($appSetting->intern_banner_url ?? asset('img/bg.jpg')) }}"
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
    <div id="logout-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-50 p-4 transition-all">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm border border-slate-100">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3 text-xl shadow-xs">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 text-center mb-1">Konfirmasi Keluar</h2>
            <p class="text-xs text-slate-500 text-center mb-5">Apakah Anda yakin ingin keluar dari akun Outsider ini?</p>
            <div class="flex items-center justify-end gap-2">
                <button type="button" id="closeLogout" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition">Batal</button>
                <a href="{{ url('/logout') }}" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-xs transition shadow-md shadow-rose-600/20">Keluar</a>
            </div>
        </div>
    </div>

    {{-- Global Notification Toasts (Outsider Minimalist) --}}
    <div id="outsider-toast-container" class="fixed top-5 right-5 z-[100] flex flex-col items-end gap-2 max-w-sm pointer-events-none px-4 sm:px-0">
        @if(session('success'))
            <div class="outsider-toast pointer-events-auto bg-emerald-600 text-white rounded-xl px-3.5 py-2.5 sm:px-4 sm:py-3 shadow-lg shadow-emerald-950/20 flex items-center gap-2.5 transition-all duration-300 transform translate-y-0 opacity-100 max-w-xs sm:max-w-sm w-auto">
                <svg class="w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <div class="text-xs sm:text-sm font-medium leading-snug min-w-0 flex-1">
                    {{ session('success') }}
                </div>
                <button type="button" onclick="dismissOutsiderToast(this)" class="text-white/80 hover:text-white p-0.5 rounded-md transition-colors cursor-pointer shrink-0 ml-1.5" title="Tutup">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @php
            $flashError = session('error') ?? session('failed') ?? ($error ?? null);
            if (!$flashError && isset($errors) && $errors->any()) {
                $flashError = $errors->first();
            }
        @endphp
        @if($flashError)
            <div class="outsider-toast pointer-events-auto bg-rose-600 text-white rounded-xl px-3.5 py-2.5 sm:px-4 sm:py-3 shadow-lg shadow-rose-950/20 flex items-center gap-2.5 transition-all duration-300 transform translate-y-0 opacity-100 max-w-xs sm:max-w-sm w-auto">
                <svg class="w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="text-xs sm:text-sm font-medium leading-snug min-w-0 flex-1">
                    {{ $flashError }}
                </div>
                <button type="button" onclick="dismissOutsiderToast(this)" class="text-white/80 hover:text-white p-0.5 rounded-md transition-colors cursor-pointer shrink-0 ml-1.5" title="Tutup">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if(session('info'))
            <div class="outsider-toast pointer-events-auto bg-sky-600 text-white rounded-xl px-3.5 py-2.5 sm:px-4 sm:py-3 shadow-lg shadow-sky-950/20 flex items-center gap-2.5 transition-all duration-300 transform translate-y-0 opacity-100 max-w-xs sm:max-w-sm w-auto">
                <svg class="w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-xs sm:text-sm font-medium leading-snug min-w-0 flex-1">
                    {{ session('info') }}
                </div>
                <button type="button" onclick="dismissOutsiderToast(this)" class="text-white/80 hover:text-white p-0.5 rounded-md transition-colors cursor-pointer shrink-0 ml-1.5" title="Tutup">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
    </div>

    {{-- Quotes Data Payload --}}
    <script type="application/json" id="quotes-data">@json($quotes ?? [])</script>

    {{-- Livewire Scripts --}}
    @livewireScripts

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Clock + Typewriter + Toast Script --}}
    <script>
        function updateClock() {
            const now = new Date();
            const time = now.toTimeString().split(' ')[0];
            document.getElementById('current-time').textContent = time;
        }
        setInterval(updateClock, 1000);
        updateClock();

        function dismissOutsiderToast(btnOrEl) {
            const toast = btnOrEl.closest('.outsider-toast');
            if (!toast) return;
            toast.classList.add('opacity-0', '-translate-y-2', 'scale-95');
            setTimeout(() => {
                if (toast && toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }

        window.showOutsiderToast = function(message, type = 'success') {
            let container = document.getElementById('outsider-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'outsider-toast-container';
                container.className = 'fixed top-5 right-5 z-[100] flex flex-col items-end gap-2 max-w-sm pointer-events-none px-4 sm:px-0';
                document.body.appendChild(container);
            }

            const typeConfig = {
                success: {
                    bg: 'bg-emerald-600',
                    shadow: 'shadow-emerald-950/20',
                    svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />'
                },
                error: {
                    bg: 'bg-rose-600',
                    shadow: 'shadow-rose-950/20',
                    svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />'
                },
                warning: {
                    bg: 'bg-amber-600',
                    shadow: 'shadow-amber-950/20',
                    svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />'
                },
                info: {
                    bg: 'bg-sky-600',
                    shadow: 'shadow-sky-950/20',
                    svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
                }
            };

            const config = typeConfig[type] || typeConfig.success;

            const toast = document.createElement('div');
            toast.className = `outsider-toast pointer-events-auto ${config.bg} text-white rounded-xl px-3.5 py-2.5 sm:px-4 sm:py-3 shadow-lg ${config.shadow} flex items-center gap-2.5 transition-all duration-300 transform -translate-y-2 opacity-0 max-w-xs sm:max-w-sm w-auto`;
            toast.innerHTML = `
                <svg class="w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    ${config.svg}
                </svg>
                <div class="text-xs sm:text-sm font-medium leading-snug min-w-0 flex-1">
                    ${message}
                </div>
                <button type="button" onclick="dismissOutsiderToast(this)" class="text-white/80 hover:text-white p-0.5 rounded-md transition-colors cursor-pointer shrink-0 ml-1.5" title="Tutup">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;

            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('-translate-y-2', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
            });

            setTimeout(() => {
                if (document.body.contains(toast)) {
                    dismissOutsiderToast(toast);
                }
            }, 5000);
        };

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

            // Auto dismiss existing server-rendered toasts after 5 seconds
            document.querySelectorAll('.outsider-toast').forEach(toast => {
                setTimeout(() => {
                    if (document.body.contains(toast)) {
                        dismissOutsiderToast(toast);
                    }
                }, 5000);
            });

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