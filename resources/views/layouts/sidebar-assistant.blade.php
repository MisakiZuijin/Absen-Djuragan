<!-- Backdrop Overlay on Mobile -->
<div id="sidebar-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-40 hidden transition-opacity duration-300 md:hidden" onclick="toggleAdminSidebar()"></div>

<!-- Sidebar Assistant Admin -->
<aside id="admin-sidebar"
    class="fixed top-0 left-0 z-50 w-64 h-screen bg-gray-900 text-white flex flex-col shadow-2xl -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out sidebar-container">

    <!-- Logo Header -->
    <div class="flex items-center justify-between md:justify-center px-6 py-5 md:py-6 border-b border-gray-800">
        <a href="{{ route('assistant.dashboard') }}" class="transition-transform duration-300 hover:scale-105">
            <img src="{{ $appSetting->logo_url ?? asset('img/logo.svg') }}" alt="{{ $appSetting->app_name ?? 'Logo' }}" class="h-10 max-w-[180px] object-contain">
        </a>
        <button type="button" onclick="toggleAdminSidebar()" class="md:hidden text-gray-400 hover:text-white p-2 rounded-lg hover:bg-gray-800 focus:outline-none transition-colors" aria-label="Tutup Menu">
            <i class="fa-solid fa-times text-xl"></i>
        </button>
    </div>

    <!-- Navigasi Utama (Scrollable) -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-700 scrollbar-track-gray-800">
        <ul class="space-y-1.5 text-gray-200">
            <!-- Menu Item: Dashboard -->
            <li>
                <a href="{{ route('assistant.dashboard') }}"
                    class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::routeIs('assistant.dashboard') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center {{ Request::routeIs('assistant.dashboard') ? 'scale-110' : '' }}"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Menu Item: Presensi Offline (Jika route tersedia) -->
            @if(Route::has('assistant.absen-offline.index'))
            <li>
                <a href="{{ route('assistant.absen-offline.index') }}"
                    class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::is('assistant-admin/absen-offline*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                    <i class="fa-solid fa-user-check w-5 text-center {{ Request::is('assistant-admin/absen-offline*') ? 'scale-110' : '' }}"></i>
                    <span>Presensi Offline</span>
                </a>
            </li>
            @endif

            <!-- Menu Item: Raise Hand -->
            <li>
                <a href="{{ route('assistant.raisehand.list') }}"
                    class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::is('assistant-admin/raise-hand*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                    <i class="fa-solid fa-hand-point-up w-5 text-center {{ Request::is('assistant-admin/raise-hand*') ? 'scale-110' : '' }}"></i>
                    <span>Raise Hand</span>
                </a>
            </li>

            <!-- Menu Item: Persetujuan Log Aktivitas -->
            <li>
                <a href="{{ route('assistant.logactivity') }}"
                    class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::is('assistant-admin/log-activity*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                    <i class="fa-solid fa-clipboard-check w-5 text-center {{ Request::is('assistant-admin/log-activity*') ? 'scale-110' : '' }}"></i>
                    <span>Persetujuan Log</span>
                </a>
            </li>

            <!-- Dropdown: Monitoring Izin -->
            <li class="has-submenu {{ Request::is('assistant-admin/izin*') ? 'active' : '' }}">
                <a href="{{ route('assistant.izin.leave.index') }}"
                    class="flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-200 hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white">
                    <div class="flex items-center gap-x-4">
                        <i class="fa-solid fa-clipboard-list w-5 text-center"></i>
                        <span>Monitoring Izin</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300"></i>
                </a>
                <ul class="submenu pt-2 pl-8 space-y-2">
                    <li>
                        <a href="{{ route('assistant.izin.leave.index') }}"
                            class="flex items-center gap-x-3 py-2 rounded-xl transition-all duration-200 {{ Request::is('assistant-admin/izin/leave*') || Request::is('assistant-admin/izin/keluar*') ? 'text-white font-semibold bg-gray-700 px-2.5 shadow-xs' : 'text-gray-400 hover:text-white hover:translate-x-1' }}">
                            <i class="fa-solid fa-right-from-bracket fa-2xs {{ Request::is('assistant-admin/izin/leave*') || Request::is('assistant-admin/izin/keluar*') ? 'scale-125' : '' }}"></i>
                            Izin Keluar
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('assistant.izin.prayer.index') }}"
                            class="flex items-center gap-x-3 py-2 rounded-xl transition-all duration-200 {{ Request::is('assistant-admin/izin/prayer*') || Request::is('assistant-admin/izin/shalat*') ? 'text-white font-semibold bg-gray-700 px-2.5 shadow-xs' : 'text-gray-400 hover:text-white hover:translate-x-1' }}">
                            <i class="fa-solid fa-mosque fa-2xs {{ Request::is('assistant-admin/izin/prayer*') || Request::is('assistant-admin/izin/shalat*') ? 'scale-125' : '' }}"></i>
                            Izin Shalat
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('assistant.izin.toilet.index') }}"
                            class="flex items-center gap-x-3 py-2 rounded-xl transition-all duration-200 {{ Request::is('assistant-admin/izin/toilet*') ? 'text-white font-semibold bg-gray-700 px-2.5 shadow-xs' : 'text-gray-400 hover:text-white hover:translate-x-1' }}">
                            <i class="fa-solid fa-toilet fa-2xs {{ Request::is('assistant-admin/izin/toilet*') ? 'scale-125' : '' }}"></i>
                            Izin Toilet
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Pemisah sebelum Logout -->
            <hr class="my-3 border-gray-800">

            <!-- Menu Item: Logout -->
            <li>
                <button
                    class="logoutModal w-full flex items-center gap-x-4 px-4 py-2.5 rounded-xl text-left transition-all duration-200 text-gray-300 hover:bg-rose-600 hover:text-white hover:translate-x-1">
                    <i class="fa-solid fa-power-off w-5 text-center"></i>
                    <span>Log Out</span>
                </button>
            </li>
        </ul>
    </nav>
</aside>

<!-- CSS Modern -->
<style>
    .submenu {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .has-submenu.active > .submenu {
        max-height: 500px;
    }

    .has-submenu.active > a .fa-chevron-down {
        transform: rotate(180deg);
    }

    .scrollbar-thin::-webkit-scrollbar {
        width: 4px;
    }

    .scrollbar-thumb-gray-700::-webkit-scrollbar-thumb {
        background-color: #374151;
        border-radius: 4px;
    }

    .scrollbar-track-gray-800::-webkit-scrollbar-track {
        background-color: #111827;
    }

    .hover\:translate-x-1:hover {
        transform: translateX(0.25rem);
    }

    .scale-110 {
        transform: scale(1.1);
    }

    .scale-125 {
        transform: scale(1.25);
    }
</style>

<!-- JavaScript Interaktif & Responsif Mobile -->
<script>
    function toggleAdminSidebar() {
        const sidebar = document.getElementById('admin-sidebar') || document.querySelector('.sidebar-container');
        const backdrop = document.getElementById('sidebar-backdrop');
        if (!sidebar) return;

        const isClosed = sidebar.classList.contains('-translate-x-full');
        if (isClosed) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            if (backdrop) backdrop.classList.remove('hidden');
            document.body.classList.add('overflow-hidden', 'md:overflow-auto');
        } else {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            if (backdrop) backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden', 'md:overflow-auto');
        }
    }
    window.toggleAdminSidebar = toggleAdminSidebar;

    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            const backdrop = document.getElementById('sidebar-backdrop');
            if (backdrop) backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const sidebar = document.getElementById('admin-sidebar') || document.querySelector('.sidebar-container');
            if (sidebar && !sidebar.classList.contains('-translate-x-full') && window.innerWidth < 768) {
                toggleAdminSidebar();
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const submenuTriggers = document.querySelectorAll('.has-submenu > a');

        submenuTriggers.forEach(trigger => {
            trigger.addEventListener('click', function(event) {
                const parentLi = this.parentElement;
                const isAlreadyActive = parentLi.classList.contains('active');
                const isLink = this.getAttribute('href') && this.getAttribute('href') !== '#';

                if (isAlreadyActive && isLink) {
                    event.preventDefault();
                    parentLi.classList.remove('active');
                } else if (!isAlreadyActive && isLink) {
                    event.preventDefault();
                    parentLi.classList.add('active');
                    setTimeout(() => {
                        window.location.href = this.getAttribute('href');
                    }, 250);
                }
            });
        });
    });
</script>