<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Halaman @yield('title') | Admin</title>

    {{-- Styles --}}
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />

    {{-- Scripts --}}
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

{{-- 
    ========================================================================
    == PENDEKATAN TERBAIK UNTUK LAYOUT ADMIN DENGAN SIDEBAR FIXED
    ========================================================================
--}}
<body class="bg-gray-100 min-h-screen">

    {{-- 1. Sidebar Anda (yang 'position: fixed') akan dimuat di sini --}}
    {{-- Karena 'fixed', ia mengambang di atas halaman --}}
    @include($sidebarView ?? 'layouts.sidebar')

    {{-- 2. Wrapper untuk konten utama DIBERI MARGIN KIRI --}}
    {{-- Margin ini mendorong konten ke kanan, memberi ruang untuk sidebar --}}
    {{-- Ini adalah cara yang benar untuk mengatasi konten yang "tenggelam" --}}
    <div class="ml-0 md:ml-64">

        {{-- 3. Semua konten lainnya berada di dalam wrapper ini --}}
        <div class="flex-1 flex flex-col min-h-screen">

            {{-- Navbar --}}
            @include('layouts.navbar', ['user' => $user ?? null])

            {{-- Page Contents --}}
            <main class="flex-1 p-3 sm:p-6">
                {{-- Nama section kita standarkan menjadi 'contents' agar konsisten --}}
                @yield('contents')
            </main>
        </div>
    </div>


    {{-- Modal Logout & Notifikasi (Tidak ada perubahan) --}}
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

    <aside id="success-notif" class="hidden fixed top-5 right-5 z-[100] flex items-center w-full max-w-xs p-4 space-x-4 text-gray-500 bg-white divide-x divide-gray-200 rounded-lg shadow dark:text-gray-400 dark:divide-gray-700 space-x dark:bg-gray-800" role="alert">
        <div class="text-green-500 bg-green-100 p-2 rounded-lg">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="pl-4 text-sm font-normal" id="success-message">Aksi berhasil.</div>
    </aside>

    <aside id="error-notif" class="hidden fixed top-5 right-5 z-[100] flex items-center w-full max-w-xs p-4 space-x-4 text-gray-500 bg-white divide-x divide-gray-200 rounded-lg shadow dark:text-gray-400 dark:divide-gray-700 space-x dark:bg-gray-800" role="alert">
        <div class="text-red-500 bg-red-100 p-2 rounded-lg">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="pl-4 text-sm font-normal" id="error-message">Aksi gagal.</div>
    </aside>

    {{-- Scripts --}}
    <script src="{{ asset('js/admin/index.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/admin/raise-hand-notifications.js') }}"></script>
    <script>
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                statusCode: {
                    419: function () {
                        alert('Sesi Anda telah berakhir. Halaman akan dimuat ulang.');
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
    @livewireScripts
</body>

</html>