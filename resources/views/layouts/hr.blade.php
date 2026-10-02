<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ $appSetting->favicon_url ?? asset('favicon.ico') }}">
    <title>Halaman @yield('title') | {{ $appSetting->app_name ?? 'HR Monitoring' }}</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @livewireStyles
    @livewireScripts
</head>

<body class="min-h-screen bg-slate-50">

    <!-- Success Notification -->
    <div id="success-notif"
        class="fixed z-50 hidden items-center px-6 py-4 text-emerald-800 bg-white border border-emerald-200 rounded-xl shadow-lg top-6 right-6 transition-all duration-300 transform">
        <div class="flex items-center justify-center w-8 h-8 bg-emerald-100 rounded-full mr-4">
            <i class="fas fa-check text-emerald-600 text-sm"></i>
        </div>
        <div>
            <div class="font-semibold text-sm">Success</div>
            <span id="success-message" class="text-sm text-emerald-700"></span>
        </div>
        <button onclick="hideNotification('success')" class="ml-4 text-emerald-400 hover:text-emerald-600 transition-colors">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>

    <!-- Error Notification -->
    <div id="error-notif"
        class="fixed z-50 hidden items-center px-6 py-4 text-red-800 bg-white border border-red-200 rounded-xl shadow-lg top-6 right-6 transition-all duration-300 transform">
        <div class="flex items-center justify-center w-8 h-8 bg-red-100 rounded-full mr-4">
            <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
        </div>
        <div>
            <div class="font-semibold text-sm">Error</div>
            <span id="error-message" class="text-sm text-red-700">Something went wrong</span>
        </div>
        <button onclick="hideNotification('error')" class="ml-4 text-red-400 hover:text-red-600 transition-colors">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>

    <div class="min-h-screen flex flex-col">
        <!-- Header Section -->
        <header class="bg-white shadow-sm border-b border-slate-200">
            <div class="px-6 py-4">
                <div class="flex justify-between items-center">
                    <!-- Left Section -->
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-12 h-12 bg-blue-600 rounded-xl">
                            <i class="fas fa-user-check text-white text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-800">HR Monitoring</h1>
                            <p class="text-sm text-slate-500 font-medium">{{ $day_now ?? \App\Utils\DateNow::getCurrentDay() }}, {{ $date_now ?? \App\Utils\DateNow::getCurrentDate() }}</p>
                        </div>
                    </div>

                    <!-- Right Section -->
                    <div class="flex items-center space-x-6">
                        <!-- Live Clock -->
                        <div class="flex items-center space-x-2 px-4 py-2 bg-slate-100 rounded-lg">
                            <i class="fas fa-clock text-slate-600 text-sm"></i>
                            <span id="current-time" class="text-sm font-mono text-slate-700">{{ now()->format('H:i:s') }}</span>
                        </div>

                        <!-- User Info -->
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-slate-200 rounded-full">
                                <i class="fas fa-user text-slate-600 text-sm"></i>
                            </div>
                            <div class="hidden md:block">
                                <div class="font-semibold text-sm text-slate-800">{{ $user->profile->full_name ?? $user->name }}</div>
                                <div class="text-xs text-slate-500">HR Division</div>
                            </div>
                        </div>

                        <!-- Dashboard Button -->
                        <a href="{{ route('user.home') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                            <i class="fas fa-home mr-2 text-sm"></i>
                            <span class="hidden sm:inline">Dashboard</span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <div class="max-w-7xl mx-auto">
                @yield('contents')
            </div>
        </main>
    </div>

    <div id="flash-messages" class="hidden"
        data-success="{{ session('success') ?? '' }}"
        data-error="{{ session('error') ?? ($error ?? '') }}"></div>

    <script>
        // Clock update function
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, "0");
            const minutes = String(now.getMinutes()).padStart(2, "0");
            const seconds = String(now.getSeconds()).padStart(2, "0");

            const currentTime = `${hours}:${minutes}:${seconds}`;
            const timeElement = document.getElementById("current-time");

            if (timeElement) {
                timeElement.textContent = currentTime;
            }
        }

        setInterval(updateClock, 1000);
        updateClock();

        // Enhanced notification functions
        function showNotification(type, message) {
            const notif = document.getElementById(`${type}-notif`);
            const messageElement = document.getElementById(`${type}-message`);

            if (notif && messageElement) {
                messageElement.textContent = message;
                notif.classList.remove('hidden');
                notif.classList.add('flex');

                // Add entrance animation
                setTimeout(() => {
                    notif.style.transform = 'translateX(0)';
                }, 10);

                // Auto hide after 5 seconds
                setTimeout(() => {
                    hideNotification(type);
                }, 5000);
            }
        }

        function hideNotification(type) {
            const notif = document.getElementById(`${type}-notif`);
            if (notif) {
                notif.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    notif.classList.add('hidden');
                    notif.classList.remove('flex');
                    notif.style.transform = 'translateX(0)';
                }, 300);
            }
        }

        // Handle success/error messages from session
        document.addEventListener('DOMContentLoaded', function() {
            const flashEl = document.getElementById('flash-messages');
            if (flashEl) {
                const successMsg = flashEl.getAttribute('data-success');
                const errorMsg = flashEl.getAttribute('data-error');
                if (successMsg) {
                    showNotification('success', successMsg);
                }
                if (errorMsg) {
                    showNotification('error', errorMsg);
                }
            }
        });

        // Add smooth scrolling for better UX
        document.documentElement.style.scrollBehavior = 'smooth';

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
    </script>

    <style>
        /* Custom styles for better aesthetics */
        .transition-all {
            transition: all 0.2s ease-in-out;
        }

        #success-notif,
        #error-notif {
            transform: translateX(100%);
        }

        /* Responsive improvements */
        @media (max-width: 640px) {
            .px-6 {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }

        /* Subtle animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        header {
            animation: fadeIn 0.5s ease-out;
        }

        /* Focus states for accessibility */
        button:focus,
        a:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
    </style>
</body>

</html>