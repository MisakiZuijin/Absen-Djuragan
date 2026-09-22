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
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @livewireStyles
</head>

<body class="flex bg-gray-100 min-h-screen">

    {{-- Sidebar Anda (Tidak ada perubahan) --}}
    @include($sidebarView ?? 'layouts.sidebar')

    {{-- ====================================================================== --}}
    {{-- PERBAIKAN UTAMA DI SINI --}}
    {{-- ====================================================================== --}}
    
    {{-- Pembungkus utama untuk Navbar dan Konten --}}
    <div class="flex-1 flex flex-col h-screen">
        
        {{-- Navbar Anda, sekarang tidak 'fixed' di dalam sini --}}
        {{-- Kita bungkus agar bisa diberi style jika perlu --}}
        <div>
            @include('layouts.navbar', ['user' => $user ?? null])
        </div>

        {{-- Konten Utama dengan scrolling internal --}}
        <main class="flex-1 overflow-y-auto p-6">
            @yield('contents')
        </main>
    </div>
    {{-- ====================================================================== --}}
    {{-- AKHIR DARI PERBAIKAN --}}
    {{-- ====================================================================== --}}
    

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
    <script src="{{ asset('js/admin/raise-hand-notifications.js') }}"></script>
    @stack('scripts')
    @livewireScripts
</body>

</html>