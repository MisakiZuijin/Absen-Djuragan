<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- iOS / macOS Safari Meta Tags -->
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="apple-mobile-web-app-title" content="{{ $appSetting->app_name ?? 'Absen Djuragan' }} Admin" />
    <link rel="icon" type="image/x-icon" href="{{ $appSetting->favicon_url ?? asset('favicon.ico') }}">

    <title>Halaman @yield('title') | {{ $appSetting->app_name ?? 'Admin' }}</title>

    {{-- Styles --}}
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />

    {{-- Scripts --}}
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @livewireStyles
</head>

<body class="bg-gray-100 min-h-screen text-gray-800 antialiased w-full overflow-x-hidden min-w-0">

    {{-- Sidebar Global --}}
    @include($sidebarView ?? (auth()->check() && (int) auth()->user()->role_id === 6 ? 'layouts.sidebar-assistant' : 'layouts.sidebar'))

    {{-- Navbar Global --}}
    @include('layouts.navbar', ['user' => $user ?? null])

    {{-- Konten Utama --}}
    @yield('contents')
    

    {{-- Modal Logout (Tidak ada perubahan) --}}
    <div id="logout-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Konfirmasi Keluar</h2>
            <p>Apakah Anda yakin ingin keluar halaman ini?</p>
            <div class="flex justify-end mt-4">
                <button type="button" id="closeLogout"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg mr-2">Batal</button>
                <a href="{{ url('/logout') }}" id="logoutConfirm"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg">Keluar</a>
            </div>
        </div>
    </div>

    {{-- Notifikasi (Tidak ada perubahan) --}}
    <aside id="success-notif" class="hidden fixed top-5 right-5 z-[100] flex items-center w-full max-w-xs p-4 space-x-4 text-gray-500 bg-white divide-x divide-gray-200 rounded-lg shadow dark:text-gray-400 dark:divide-gray-700 space-x dark:bg-gray-800" role="alert">
        {{-- ... --}}
    </aside>
    <aside id="error-notif" class="hidden fixed top-5 right-5 z-[100] flex items-center w-full max-w-xs p-4 space-x-4 text-gray-500 bg-white divide-x divide-gray-200 rounded-lg shadow dark:text-gray-400 dark:divide-gray-700 space-x dark:bg-gray-800" role="alert">
        {{-- ... --}}
    </aside>

    <script src="{{ asset('js/admin/index.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    @php
        $raiseHandNotifInterval = \App\Models\PopupSetting::getInterval('raise_hand_notification', 5);
        $raiseHandNotifEnabled = \App\Models\PopupSetting::isEnabled('raise_hand_notification', true);
    @endphp
    <script>
        window.__raiseHandPollIntervalMs = {{ $raiseHandNotifInterval * 1000 }};
        window.__raiseHandNotificationsEnabled = {{ $raiseHandNotifEnabled && $raiseHandNotifInterval > 0 ? 'true' : 'false' }};
    </script>
    <script src="{{ asset('js/admin/raise-hand-notifications.js') }}?v={{ file_exists(public_path('js/admin/raise-hand-notifications.js')) ? filemtime(public_path('js/admin/raise-hand-notifications.js')) : '1.0' }}"></script>
    <script>
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                statusCode: {
                    419: function () {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Sesi Telah Berakhir',
                                text: 'Sesi Anda telah kedaluwarsa. Halaman akan dimuat ulang.',
                                confirmButtonText: 'Muat Ulang'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            alert('Sesi Anda telah berakhir. Halaman akan dimuat ulang.');
                            window.location.reload();
                        }
                    },
                    403: function () {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Akses Ditolak',
                                text: 'Anda tidak memiliki izin untuk melakukan tindakan ini.'
                            });
                        } else {
                            alert('Anda tidak memiliki izin untuk melakukan tindakan ini.');
                        }
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
        // GLOBAL BACKGROUND SCROLL LOCKER (UNTUK SEMUA POPUP / MODAL DI SISTEM)
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
                    '.fixed.inset-0:not(.hidden):not(#sidebar):not(#user-sidebar):not(#user-sidebar-backdrop):not(#settings-sidebar-wrapper):not(#main-navbar), ' +
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
    @stack('scripts')
    @livewireScripts
</body>

</html>