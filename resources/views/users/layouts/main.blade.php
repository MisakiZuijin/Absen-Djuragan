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
    <meta name="apple-mobile-web-app-title" content="{{ $appSetting->app_name ?? 'Absen Djuragan' }}" />
    <link rel="icon" type="image/x-icon" href="{{ $appSetting->favicon_url ?? asset('favicon.ico') }}">

    <title>Halaman @yield('title') | {{ $appSetting->app_name ?? 'User' }}</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <style>
        html, body, .no-scrollbar {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }
        html::-webkit-scrollbar, body::-webkit-scrollbar, .no-scrollbar::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    @livewireStyles
    @livewireScripts
</head>

<body class="min-h-screen bg-gray-100 overflow-x-hidden w-full min-w-0 no-scrollbar">

    {{-- Sidebar Pemagang --}}
    @include('users.layouts.sidebar')

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
        <!-- Background Image & Banner Carousel -->
        @php
        $currentUser = $user ?? auth()->user();
        $birth_date = $currentUser?->profile?->date_of_birth;
        $today = now()->format('m-d');
        $userBirth = $birth_date ? \Carbon\Carbon::parse($birth_date)->format('m-d') : null;
        $currentDay = $day_now ?? \App\Utils\DateNow::getCurrentDay();
        $currentDate = $date_now ?? \App\Utils\DateNow::getCurrentDate();
        $bannerSlidesList = $appSetting->banner_slides_urls ?? [asset('img/bg.jpg')];
        @endphp

        <div class="relative h-[250px] w-full flex-shrink-0 md:rounded-br-[40px] overflow-hidden bg-slate-900"
            @if ($today !== $userBirth)
            x-data='{
                activeSlide: 0,
                slides: @json($bannerSlidesList),
                timer: null,
                startAutoSlide() {
                    if (this.slides.length > 1) {
                        this.timer = setInterval(() => {
                            this.activeSlide = (this.activeSlide + 1) % this.slides.length;
                        }, 6000);
                    }
                },
                goToSlide(index) {
                    this.activeSlide = index;
                    clearInterval(this.timer);
                    this.startAutoSlide();
                }
            }'
            x-init="startAutoSlide()"
            @mouseenter="clearInterval(timer)"
            @mouseleave="startAutoSlide()"
            @endif
        >
            @if ($today === $userBirth)
            <img src="{{ asset('img/bg2.jpg') }}" alt="Birthday Banner"
                class="w-full h-full object-cover md:rounded-br-[40px] no-select">
            @else
            <!-- Slide Images -->
            <template x-for="(slide, index) in slides" :key="index">
                <div x-show="activeSlide === index"
                    x-transition:enter="transition ease-out duration-1000"
                    x-transition:enter-start="opacity-0 scale-105"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-1000"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute inset-0 w-full h-full">
                    <img :src="slide" alt="Banner Slide" class="w-full h-full object-cover md:rounded-br-[40px] no-select">
                </div>
            </template>

            <!-- Fallback Image for Initial Render / Non-JS -->
            <noscript>
                <img src="{{ $appSetting->intern_banner_url ?? asset('img/bg.jpg') }}" alt="Background Image"
                    class="w-full h-full object-cover md:rounded-br-[40px] no-select">
            </noscript>

            <!-- Dots Indicator & Controls -->
            <div x-show="slides && slides.length > 1" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-2 z-20">
                <template x-for="(slide, index) in slides" :key="index">
                    <button type="button" @click="goToSlide(index)"
                        :class="activeSlide === index ? 'w-6 bg-white shadow-md' : 'w-2 bg-white/60 hover:bg-white/90'"
                        class="h-2 rounded-full transition-all duration-300 cursor-pointer focus:outline-none"
                        :title="'Slide ' + (index + 1)"></button>
                </template>
            </div>
            @endif

            <!-- Dark Overlay for Readability -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent pointer-events-none z-10"></div>

            <!-- Welcome Message -->
            <div class="absolute inset-0 flex items-center justify-center z-10 p-2 md:p-4 pointer-events-none">
                <div class="typewriter text-xl md:text-3xl font-bold text-white text-center italic">
                    <h1 id="typewriter-text"></h1>
                </div>
            </div>

            <!-- Profile Info and Logout Button -->
            <div
                class="absolute bottom-4 left-4 md:left-10 flex items-center space-x-2 md:space-x-4 text-white z-20 bg-black/50 p-1.5 md:p-3 rounded-3xl max-w-[calc(100%-80px)] backdrop-blur-xs">
                <i class="fas fa-user-circle text-2xl md:text-3xl shrink-0"></i>
                <div class="text-xs md:text-sm min-w-0">
                    <div class="font-bold text-xs md:text-sm truncate">{{ $currentUser?->profile?->full_name ?? ($currentUser?->name ?? 'User') }}</div>
                    <div class="text-xs md:text-sm text-white/80 truncate">{{ $currentUser?->profile?->NIP ?? '-' }}</div>
                </div>
            </div>

            <div class="absolute bottom-4 right-4 md:right-10 text-white z-20 p-2 md:p-4">
                <button class="logoutModal">
                    <i class="fas fa-sign-out-alt text-xl md:text-3xl cursor-pointer"></i>
                </button>
            </div>

            <!-- Hamburger Menu Button & Date -->
            <div
                class="absolute top-4 left-4 md:left-10 flex items-center space-x-2 md:space-x-3 text-white z-20">
                <button type="button"
                    onclick="toggleUserSidebar()"
                    class="p-2 md:p-2.5 rounded-2xl bg-black/40 hover:bg-black/60 backdrop-blur-xs text-white border border-white/20 focus:outline-none transition-all duration-200 flex items-center justify-center cursor-pointer shadow-md hover:scale-105 active:scale-95 md:hidden"
                    aria-label="Buka Menu Navigasi"
                    title="Menu Navigasi">
                    <i class="fa-solid fa-bars text-sm md:text-xl"></i>
                </button>
                <div class="flex items-center space-x-1.5 md:space-x-2 text-xs md:text-2xl p-1 md:p-2 font-medium">
                    <i class="fas fa-calendar-day text-xs md:text-2xl"></i>
                    <span>{{ $currentDay }}, {{ $currentDate }}</span>
                </div>
            </div>

            <!-- Real-time Clock -->
            <div class="absolute top-4 right-4 md:right-10 text-white z-20 text-xs md:text-2xl p-1 md:p-3 rounded-xl">
                <span id="current-time" class="text-xs md:text-2xl">14:30:00</span>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 w-full min-w-0 relative z-30 no-scrollbar">
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

            // =========================================================================
            // GLOBAL BACKGROUND SCROLL LOCKER (UNTUK SEMUA POPUP / MODAL PEMAGANG)
            // =========================================================================
            (function() {
                let isLocked = false;
                function updateBodyScrollLock() {
                    const swalOpen = document.querySelector('.swal2-container.swal2-shown, body.swal2-shown');
                    if (swalOpen) {
                        if (!isLocked) {
                            document.body.classList.add('overflow-hidden');
                            document.documentElement.classList.add('overflow-hidden');
                            isLocked = true;
                        }
                        return;
                    }

                    const candidateModals = document.querySelectorAll(
                        '.fixed.inset-0:not(.hidden):not(#sidebar):not(#user-sidebar):not(#user-sidebar-backdrop):not(#main-navbar), ' +
                        '.modal-backdrop:not(.hidden)'
                    );

                    let hasVisibleModal = false;
                    const minWidth = window.innerWidth * 0.5;
                    const minHeight = window.innerHeight * 0.5;

                    for (let i = 0; i < candidateModals.length; i++) {
                        const el = candidateModals[i];
                        const tag = el.tagName.toLowerCase();
                        if (['button', 'a', 'input', 'select', 'textarea', 'nav', 'aside', 'header', 'footer', 'form'].includes(tag)) {
                            continue;
                        }

                        if (el.offsetWidth >= minWidth && el.offsetHeight >= minHeight) {
                            const style = window.getComputedStyle(el);
                            if (
                                style.position === 'fixed' &&
                                style.display !== 'none' &&
                                style.visibility !== 'hidden' &&
                                style.opacity !== '0' &&
                                style.pointerEvents !== 'none'
                            ) {
                                hasVisibleModal = true;
                                break;
                            }
                        }
                    }

                    if (hasVisibleModal && !isLocked) {
                        document.body.classList.add('overflow-hidden');
                        document.documentElement.classList.add('overflow-hidden');
                        isLocked = true;
                    } else if (!hasVisibleModal && isLocked) {
                        document.body.classList.remove('overflow-hidden');
                        document.documentElement.classList.remove('overflow-hidden');
                        isLocked = false;
                    }
                }

                const modalObserver = new MutationObserver(function() {
                    updateBodyScrollLock();
                });

                document.addEventListener('DOMContentLoaded', function() {
                    modalObserver.observe(document.body, {
                        attributes: true,
                        attributeFilter: ['class', 'style'],
                        childList: true,
                        subtree: true
                    });
                    updateBodyScrollLock();
                });

                window.updateBodyScrollLock = updateBodyScrollLock;
            })();
        </script>

        <!-- ========================================================================= -->
        <!-- SISTEM NOTIFIKASI SUARA & LOOP 5 DETIK CHAT GANTI JAM PEMAGANG           -->
        <!-- ========================================================================= -->
        <script>
            (function() {
                class InternChatSoundManager {
                    constructor() {
                        this.loopInterval = null;
                        this.loopIntervalMs = 5000; // Loop per 5 detik
                        this.isAudioUnlocked = false;
                        this.audioContextInstance = null;
                        this.hasUnreadReg = false;
                        this.hasUnreadSession = false;

                        this.initGestureUnlock();
                        this.initEventListeners();
                    }

                    initGestureUnlock() {
                        const unlock = () => {
                            this.isAudioUnlocked = true;
                            try {
                                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                                if (!this.audioContextInstance && AudioCtx) {
                                    this.audioContextInstance = new AudioCtx();
                                }
                                if (this.audioContextInstance && this.audioContextInstance.state === 'suspended') {
                                    this.audioContextInstance.resume().catch(() => {});
                                }
                            } catch (e) {}

                            ['click', 'touchstart', 'keydown'].forEach(evt => {
                                document.removeEventListener(evt, unlock);
                            });
                        };

                        ['click', 'touchstart', 'keydown'].forEach(evt => {
                            document.addEventListener(evt, unlock, { once: true, passive: true });
                        });
                    }

                    initEventListeners() {
                        // Event dari Livewire dispatch: intern-chat-status
                        window.addEventListener('intern-chat-status', (event) => {
                            const detail = event.detail || {};
                            let source = detail.source;
                            let hasUnread = detail.hasUnread;
                            if (Array.isArray(detail)) {
                                source = detail[0]?.source;
                                hasUnread = detail[0]?.hasUnread;
                            }
                            if (source) {
                                this.setUnreadStatus(source, hasUnread);
                            }
                        });

                        window.addEventListener('play-chat-sound', () => {
                            this.playChime();
                        });

                        window.addEventListener('stop-chat-sound', () => {
                            this.stopLoop();
                        });
                    }

                    playChime() {
                        try {
                            if (this.audioContextInstance && this.audioContextInstance.state === 'running') {
                                this.executeBubbleTones(this.audioContextInstance);
                                return;
                            }

                            if (this.audioContextInstance && this.audioContextInstance.state === 'suspended' && this.isAudioUnlocked) {
                                this.audioContextInstance.resume().then(() => {
                                    this.executeBubbleTones(this.audioContextInstance);
                                }).catch(() => {});
                                return;
                            }

                            const audio = new Audio('/sounds/chat-pop.wav');
                            audio.volume = 0.8;
                            audio.play().catch(() => {});
                        } catch (e) {}
                    }

                    executeBubbleTones(ctx) {
                        try {
                            const now = ctx.currentTime;
                            // Bubble Bloop sweep: 400Hz -> 1300Hz
                            const osc1 = ctx.createOscillator();
                            const gain1 = ctx.createGain();
                            osc1.type = 'sine';
                            osc1.frequency.setValueAtTime(400, now);
                            osc1.frequency.exponentialRampToValueAtTime(1300, now + 0.08);

                            gain1.gain.setValueAtTime(0.35, now);
                            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.15);

                            osc1.connect(gain1);
                            gain1.connect(ctx.destination);
                            osc1.start(now);
                            osc1.stop(now + 0.15);

                            // Bubble ripple 2: 750Hz -> 1600Hz
                            const osc2 = ctx.createOscillator();
                            const gain2 = ctx.createGain();
                            osc2.type = 'sine';
                            osc2.frequency.setValueAtTime(750, now + 0.05);
                            osc2.frequency.exponentialRampToValueAtTime(1600, now + 0.11);

                            gain2.gain.setValueAtTime(0.22, now + 0.05);
                            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.18);

                            osc2.connect(gain2);
                            gain2.connect(ctx.destination);
                            osc2.start(now + 0.05);
                            osc2.stop(now + 0.18);
                        } catch (e) {}
                    }

                    startLoop() {
                        if (this.loopInterval) return; // sudah aktif looping
                        this.playChime(); // Bunyikan 1x langsung saat terdeteksi
                        this.loopInterval = setInterval(() => {
                            if (this.hasUnreadReg || this.hasUnreadSession) {
                                this.playChime();
                            } else {
                                this.stopLoop();
                            }
                        }, this.loopIntervalMs);
                    }

                    stopLoop() {
                        if (this.loopInterval) {
                            clearInterval(this.loopInterval);
                            this.loopInterval = null;
                        }
                    }

                    setUnreadStatus(source, hasUnread) {
                        if (source === 'reg') {
                            this.hasUnreadReg = !!hasUnread;
                        } else if (source === 'session') {
                            this.hasUnreadSession = !!hasUnread;
                        }

                        if (this.hasUnreadReg || this.hasUnreadSession) {
                            this.startLoop();
                        } else {
                            this.stopLoop();
                        }
                    }
                }

                window.internChatSoundManager = new InternChatSoundManager();
                window.playChatNotificationSound = function() {
                    if (window.internChatSoundManager) {
                        window.internChatSoundManager.playChime();
                    }
                };
            })();
        </script>
        @stack('scripts')
</body>

</html>