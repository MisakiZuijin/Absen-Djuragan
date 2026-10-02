<!-- Backdrop Overlay on Mobile & Desktop -->
<div id="user-sidebar-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-40 hidden transition-opacity duration-300" onclick="toggleUserSidebar()"></div>

@php
$sidebarUser = $user ?? auth()->user();
$sidebarProfile = $sidebarUser?->profile;
$sidebarIntern = $sidebarUser?->intern;
$sidebarDivision = $sidebarIntern?->division;
$sidebarSchool = $sidebarIntern?->school;
@endphp

<!-- Sidebar Pemagang (Off-Canvas Drawer) -->
<aside id="user-sidebar"
    class="fixed top-0 left-0 z-50 w-72 sm:w-80 h-screen bg-gray-900 text-white flex flex-col shadow-2xl -translate-x-full transition-transform duration-300 ease-in-out sidebar-container">

    <!-- Header Logo & Close Button -->
    <div class="flex items-center justify-between px-5 py-4 sm:py-5 border-b border-gray-800 shrink-0">
        <a href="{{ route('user.home') }}" class="flex items-center gap-2 transition-transform duration-200 hover:scale-105">
            <img src="{{ $appSetting->logo_url ?? asset('img/logo.svg') }}" alt="{{ $appSetting->app_name ?? 'Absen Djuragan' }}" class="h-8 sm:h-9 max-w-[150px] object-contain">
        </a>
        <button type="button" onclick="toggleUserSidebar()"
            class="text-gray-400 hover:text-white p-2 rounded-xl hover:bg-gray-800 focus:outline-none transition-colors cursor-pointer"
            aria-label="Tutup Menu">
            <i class="fa-solid fa-times text-lg"></i>
        </button>
    </div>

    <!-- User Profile Card -->
    <div class="px-5 py-4 border-b border-gray-800 bg-gray-950/40 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-blue-600/20 border border-blue-500/30 text-blue-400 flex items-center justify-center text-xl shrink-0 shadow-inner">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="font-bold text-sm text-white truncate">
                    {{ $sidebarProfile?->full_name ?? ($sidebarUser?->name ?? 'Pemagang') }}
                </div>
                <div class="text-xs text-gray-400 truncate">
                    NIP: {{ $sidebarProfile?->NIP ?? '-' }}
                </div>
                @if($sidebarDivision)
                <div class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-500/10 border border-blue-500/20 text-[11px] font-semibold text-blue-300 truncate max-w-full">
                    <i class="fa-solid fa-layer-group text-[10px]"></i>
                    <span class="truncate">{{ $sidebarDivision->name }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Navigation Links (Scrollable) -->
    <nav class="flex-1 px-3.5 py-4 space-y-1.5 overflow-y-auto no-scrollbar">
        <div class="px-3 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">
            Menu Utama
        </div>

        <!-- 1. Dashboard / Presensi -->
        <a href="{{ route('user.home') }}"
            class="flex items-center gap-x-3.5 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ Request::routeIs('user.home') || Request::is('home') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md shadow-blue-950/50' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
            <i class="fa-solid fa-fingerprint w-5 text-center text-base {{ Request::routeIs('user.home') || Request::is('home') ? 'text-white' : 'text-blue-400' }}"></i>
            <span class="text-sm">Presensi & Dashboard</span>
        </a>

        <!-- 2. Logbook Harian -->
        <a href="{{ route('user.logbook.index') }}"
            class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ Request::routeIs('user.logbook.*') || Request::is('logbook*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md shadow-blue-950/50' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
            <div class="flex items-center gap-x-3.5 min-w-0">
                <i class="fa-regular fa-file-lines w-5 text-center text-base {{ Request::routeIs('user.logbook.*') || Request::is('logbook*') ? 'text-white' : 'text-emerald-400' }}"></i>
                <span class="text-sm truncate">Logbook Harian</span>
            </div>
            <i class="fa-solid fa-chevron-right text-[10px] text-gray-500"></i>
        </a>

        <!-- 3. Tugas & Akun Divisi -->
        <a href="{{ route('user.tasks.index') }}"
            class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ Request::routeIs('user.tasks.*') || Request::is('tasks*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md shadow-blue-950/50' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
            <div class="flex items-center gap-x-3.5 min-w-0">
                <i class="fa-solid fa-folder-open w-5 text-center text-base {{ Request::routeIs('user.tasks.*') || Request::is('tasks*') ? 'text-white' : 'text-purple-400' }}"></i>
                <span class="text-sm truncate">Tugas & Akun Divisi</span>
            </div>
            <i class="fa-solid fa-chevron-right text-[10px] text-gray-500"></i>
        </a>

        <!-- 4. Data Ganti Jam -->
        <a href="{{ route('user.change-time.index') }}"
            class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ Request::routeIs('user.change-time.*') || Request::is('ganti-jam*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md shadow-blue-950/50' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
            <div class="flex items-center gap-x-3.5 min-w-0">
                <i class="fa-solid fa-clock-rotate-left w-5 text-center text-base {{ Request::routeIs('user.change-time.*') || Request::is('ganti-jam*') ? 'text-white' : 'text-amber-400' }}"></i>
                <span class="text-sm truncate">Data Ganti Jam</span>
            </div>
            <i class="fa-solid fa-chevron-right text-[10px] text-gray-500"></i>
        </a>

        <div class="pt-3 px-3 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400">
            Informasi & SOP
        </div>

        <!-- 5. Pengumuman -->
        <a href="javascript:void(0)"
            data-home-url="{{ route('user.home') }}"
            onclick="handleSidebarBroadcastClick(this)"
            class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-200 hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white cursor-pointer">
            <div class="flex items-center gap-x-3.5 min-w-0">
                <i class="fa-solid fa-bullhorn w-5 text-center text-base text-sky-400"></i>
                <span class="text-sm truncate">Pengumuman</span>
            </div>
            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-sky-500/20 text-sky-300 border border-sky-500/30">Info</span>
        </a>

        <!-- 6. Info & SOP Magang / Libur -->
        <a href="javascript:void(0)"
            data-home-url="{{ route('user.home') }}"
            onclick="handleSidebarHolidayClick(this)"
            class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-200 hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white cursor-pointer">
            <div class="flex items-center gap-x-3.5 min-w-0">
                <i class="fa-solid fa-circle-info w-5 text-center text-base text-rose-400"></i>
                <span class="text-sm truncate">Info & SOP Magang</span>
            </div>
            <i class="fa-solid fa-chevron-right text-[10px] text-gray-500"></i>
        </a>

        <!-- 7. Menu Khusus Divisi Human Resource -->
        @if(isset($sidebarDivision) && $sidebarDivision->name === 'Human Resource')
        <div class="pt-3 px-3 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-purple-400 flex items-center gap-1.5">
            <i class="fa-solid fa-shield-halved text-xs"></i>
            <span>Monitoring HR</span>
        </div>
        <a href="{{ route('hr.monitor.toilet') }}"
            class="flex items-center gap-x-3.5 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ Request::routeIs('hr.monitor.toilet') ? 'bg-gradient-to-r from-purple-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
            <i class="fa-solid fa-toilet w-5 text-center text-base text-purple-400"></i>
            <span class="text-sm truncate">Monitoring Izin Toilet</span>
        </a>
        <a href="{{ route('hr.monitor.prayer') }}"
            class="flex items-center gap-x-3.5 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ Request::routeIs('hr.monitor.prayer') ? 'bg-gradient-to-r from-purple-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
            <i class="fa-solid fa-mosque w-5 text-center text-base text-emerald-400"></i>
            <span class="text-sm truncate">Monitoring Izin Shalat</span>
        </a>
        @endif
    </nav>

    <!-- Footer: Logout Button -->
    <div class="p-4 border-t border-gray-800 bg-gray-950/60 shrink-0">
        <button type="button"
            onclick="toggleUserSidebar(); $('#logout-modal').removeClass('hidden');"
            class="w-full flex items-center justify-center gap-2.5 px-4 py-2.5 rounded-xl bg-red-600/15 hover:bg-red-600 text-red-400 hover:text-white border border-red-500/20 hover:border-red-600 font-semibold text-sm transition-all duration-200 cursor-pointer shadow-xs active:scale-95">
            <i class="fas fa-sign-out-alt"></i>
            <span>Keluar Akun</span>
        </button>
    </div>
</aside>

<script>
    function toggleUserSidebar() {
        const sidebar = document.getElementById('user-sidebar');
        const backdrop = document.getElementById('user-sidebar-backdrop');
        if (!sidebar || !backdrop) return;

        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    function handleSidebarBroadcastClick(el) {
        if (typeof toggleUserSidebar === 'function') toggleUserSidebar();
        if (typeof handleBroadcastListClick === 'function') {
            handleBroadcastListClick(event);
        } else {
            window.location.href = el?.dataset?.homeUrl || '/user';
        }
    }

    function handleSidebarHolidayClick(el) {
        if (typeof toggleUserSidebar === 'function') toggleUserSidebar();
        if (typeof handleHolidayInfoClick === 'function') {
            handleHolidayInfoClick(event);
        } else {
            window.location.href = el?.dataset?.homeUrl || '/user';
        }
    }

    window.toggleUserSidebar = toggleUserSidebar;
    window.handleSidebarBroadcastClick = handleSidebarBroadcastClick;
    window.handleSidebarHolidayClick = handleSidebarHolidayClick;

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const sidebar = document.getElementById('user-sidebar');
            if (sidebar && !sidebar.classList.contains('-translate-x-full')) {
                toggleUserSidebar();
            }
        }
    });
</script>