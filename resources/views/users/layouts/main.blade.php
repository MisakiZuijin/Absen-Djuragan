<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- iOS / macOS Safari Meta Tags -->
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="apple-mobile-web-app-title" content="Absen Djuragan" />

    <title>Halaman @yield('title') | User</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    @livewireStyles
    @livewireScripts
</head>

<body class="min-h-screen bg-gray-100 overflow-x-hidden w-full min-w-0">


    <aside id="success-notif"
        class="fixed z-50 hidden flex items-center justify-center px-5 py-2 text-white bg-green-500 rounded-lg top-4 right-4">
        <i class="fa-solid fa-check-circle"></i>
        <span id="success-message" class="ml-2 text-xl font-medium hover:opacity-75">

        </span>
    </aside>

    <aside id="error-notif"
        class="fixed z-50 hidden flex items-center justify-center px-5 py-2 text-white bg-red-500 rounded-lg top-4 right-4">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span id="error-message" class="ml-2 text-xl font-medium hover:opacity-75">
            failed
        </span>
    </aside>


    <!-- Success Notification Box -->
    <div class="floating-box hidden bg-green-500" id="success-box" data-show="{{ session('success') ? '1' : '0' }}">
        <i class="fa-solid fa-check-circle"></i>
        {{ session('success') ?? '' }}
    </div>

    @php
        $flashError = $error ?? session('error');
        if (!$flashError && isset($errors) && $errors->any()) {
            $flashError = $errors->first();
        }
    @endphp
    <!-- Error Notification Box -->
    <div class="floating-box hidden" id="error-box" data-show="{{ $flashError ? '1' : '0' }}">
        <i class="fa-solid fa-triangle-exclamation"></i>
        {{ $flashError ?? '' }}
    </div>

    <div class="relative min-h-screen flex flex-col pb-16 w-full min-w-0">
        <!-- Background Image -->
        <div class="relative h-[250px] w-full flex-shrink-0">
            @php
            $currentUser = $user ?? auth()->user();
            $birth_date = $currentUser?->profile?->date_of_birth;
            $today = now()->format('m-d');
            $userBirth = $birth_date ? \Carbon\Carbon::parse($birth_date)->format('m-d') : null;
            $currentDay = $day_now ?? \App\Utils\DateNow::getCurrentDay();
            $currentDate = $date_now ?? \App\Utils\DateNow::getCurrentDate();
            @endphp

            @if ($today === $userBirth)
            <img src="{{ asset('img/bg2.jpg') }}" alt="Background Image"
                class="w-full h-full object-cover md:rounded-br-[40px] no-select">
            @else
            <img src="{{ asset('img/bg.jpg') }}" alt="Background Image"
                class="w-full h-full object-cover md:rounded-br-[40px] no-select">
            @endif

            <!-- Welcome Message -->
            <div class="absolute inset-0 flex items-center justify-center z-10 p-2 md:p-4">
                <div class="typewriter text-xl md:text-3xl font-bold text-white text-center italic">
                    <h1 id="typewriter-text"></h1>
                </div>

                <!-- Profile Info and Logout Button -->
                <div
                    class="absolute bottom-4 left-4 md:left-10 flex items-center space-x-2 md:space-x-4 text-white z-20 bg-black/50 p-1.5 md:p-3 rounded-3xl max-w-[calc(100%-80px)] backdrop-blur-xs">
                    <i class="fas fa-user-circle text-2xl md:text-3xl shrink-0"></i>
                    <div class="text-xs md:text-sm min-w-0">
                        <div class="font-bold text-xs md:text-sm truncate">{{ $currentUser?->profile?->full_name ?? ($currentUser?->name ?? 'User') }}</div>
                        <div class="text-[11px] md:text-xs text-white/80 truncate">{{ $currentUser?->profile?->NIP ?? '-' }}</div>
                    </div>
                </div>

                <div class="absolute bottom-4 right-4 md:right-10 text-white z-20 p-2 md:p-4">
                    <button class="logoutModal">
                        <i class="fas fa-sign-out-alt text-xl md:text-3xl cursor-pointer"></i>
                    </button>
                </div>
            </div>

            <!-- Date Icon and Date -->
            <div
                class="absolute top-4 left-4 md:left-10 flex items-center space-x-1 md:space-x-2 text-white z-20 text-xs md:text-2xl p-1 md:p-3">
                <i class="fas fa-calendar-day text-xs md:text-2xl"></i>
                <span class="text-xs md:text-2xl">{{ $currentDay }}, {{ $currentDate }}</span>
            </div>

            <!-- Real-time Clock -->
            <div class="absolute top-4 right-4 md:right-10 text-white z-20 text-xs md:text-2xl p-1 md:p-3 rounded-xl">
                <span id="current-time" class="text-xs md:text-2xl">14:30:00</span>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 w-full min-w-0">
            @yield('contents')
        </main>
        <!-- Modal Logout -->
        <div id="logout-modal"
            class="hidden fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-auto">
                <h2 class="text-xl font-bold mb-2 text-gray-800">Konfirmasi Keluar</h2>
                <p class="text-sm text-gray-600 mb-6">Apakah Anda yakin ingin keluar dari halaman ini?</p>
                <div class="flex justify-end gap-2">
                    <button type="button" id="closeLogout"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-sm transition">Batal</button>
                    <a href="{{ url('/logout') }}" id="logoutConfirm"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl text-sm transition shadow-sm">Keluar</a>
                </div>
            </div>
        </div>

        <script id="quotes-data" type="application/json">
            @json($quotes ?? [])
        </script>
        <script>
            function updateClock() {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, "0");
                const minutes = String(now.getMinutes()).padStart(2, "0");
                const seconds = String(now.getSeconds()).padStart(2, "0");

                const currentTime = `${hours}:${minutes}:${seconds}`;
                const timeElement = document.getElementById("current-time");

                if (timeElement) {
                    timeElement.textContent = currentTime;
                } else {
                    console.error("Element with ID 'current-time' not found.");
                }
            }

            setInterval(updateClock, 1000);

            updateClock();

            document.addEventListener('DOMContentLoaded', () => {

                const typewriterTextElement = document.getElementById('typewriter-text');
                if (!typewriterTextElement) return;

                const quotesDataEl = document.getElementById('quotes-data');
                let parsedTexts = [];
                try {
                    const raw = quotesDataEl ? JSON.parse(quotesDataEl.textContent || '[]') : [];
                    if (Array.isArray(raw)) {
                        parsedTexts = raw;
                    } else if (raw && Array.isArray(raw.data)) {
                        parsedTexts = raw.data;
                    }
                } catch (e) {
                    parsedTexts = [];
                }

                const texts = parsedTexts.map(item => {
                    if (typeof item === 'string') return item;
                    if (item && typeof item === 'object' && item.quote) return item.quote;
                    return '';
                }).filter(text => text.trim().length > 0);

                const delayBeforeChange = 10000;

                function getRandomIndex(max) {
                    return Math.floor(Math.random() * max);
                }

                function formatTextWithLineBreaks(text) {
                    if (!text || typeof text !== 'string') return '';
                    const words = text.split(' ');
                    let formattedText = '';
                    for (let i = 0; i < words.length; i++) {
                        formattedText += words[i];
                        if ((i + 1) % 4 === 0 && i !== words.length - 1) {
                            formattedText += '<br>';
                        } else {
                            formattedText += ' ';
                        }
                    }
                    return formattedText.trim();
                }

                function changeText() {
                    if (!typewriterTextElement || !texts || texts.length === 0) return;
                    const randomIndex = getRandomIndex(texts.length);
                    const selectedText = texts[randomIndex];
                    if (!selectedText) return;
                    const formattedText = formatTextWithLineBreaks(selectedText);

                    // Remove animation class, trigger reflow, and then add it back to reset the animation
                    typewriterTextElement.innerHTML = formattedText;
                    typewriterTextElement.classList.remove('typing-animation'); // Remove animation class
                    void typewriterTextElement.offsetWidth; // Trigger reflow
                    typewriterTextElement.classList.add('typing-animation'); // Add animation class back
                }

                if (texts.length > 0) {
                    changeText();

                    setInterval(() => {
                        changeText();
                    }, delayBeforeChange);
                }
            });


            $(document).ready(function() {

                $('.logoutModal').on('click', function(event) {
                    event.preventDefault();

                    $('#logout-modal').removeClass('hidden');
                });

                $('#closeLogout').on('click', function() {
                    $('#logout-modal').addClass('hidden');
                });

                $(window).on('click', function(event) {
                    if ($(event.target).is('#logout-modal')) {
                        $('#logout-modal').addClass('hidden');
                    }
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                function showBox(id) {
                    var box = document.getElementById(id);
                    if (!box) return;
                    box.classList.remove('hidden');
                    box.classList.add('show');
                }

                function hideBox(id) {
                    var box = document.getElementById(id);
                    if (!box) return;
                    box.classList.remove('show');
                    box.classList.add('hidden');
                }

                var errorBox = document.getElementById('error-box');
                if (errorBox && errorBox.getAttribute('data-show') === '1') {
                    showBox('error-box');
                    setTimeout(function() {
                        hideBox('error-box');
                    }, 3000);
                }

                var successBox = document.getElementById('success-box');
                if (successBox && successBox.getAttribute('data-show') === '1') {
                    showBox('success-box');
                    setTimeout(function() {
                        hideBox('success-box');
                    }, 3000);
                }
            });

            if (typeof $ !== 'undefined') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    statusCode: {
                        419: function () {
                            alert('Sesi Anda telah berakhir karena tidak ada aktivitas. Halaman akan dimuat ulang.');
                            window.location.reload();
                        },
                        403: function () {
                            alert('Anda tidak memiliki izin untuk melakukan tindakan ini.');
                        }
                    }
                });
            }
            window.addEventListener('unhandledrejection', function (event) {
                if (event.reason && (event.reason.status === 419 || event.reason.status === 401)) {
                    window.location.reload();
                }
            });
        </script>
        @stack('scripts')
</body>

</html>