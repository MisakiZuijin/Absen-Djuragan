<!-- Backdrop Overlay on Mobile -->
<div id="sidebar-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-40 hidden transition-opacity duration-300 md:hidden" onclick="toggleAdminSidebar()"></div>

<!-- Sidebar -->
<aside id="admin-sidebar"
    class="fixed top-0 left-0 z-50 w-64 h-screen bg-gray-900 text-white flex flex-col shadow-2xl -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out sidebar-container">

    <!-- Logo Header -->
    <div class="flex items-center justify-between md:justify-center px-6 py-5 md:py-6 border-b border-gray-800">
        <a href="{{ route('admin.home') }}" class="transition-transform duration-300 hover:scale-105">
            <img src="{{ asset('img/logo.svg') }}" alt="Logo" class="h-10">
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
                    <a href="{{ url('/admin/home') }}"
                        class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::is('admin/home*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie w-5 text-center {{ Request::is('admin/home*') ? 'scale-110' : '' }}"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Dropdown: Presensi -->
                <li class="has-submenu {{ Request::is(['admin/presence*', 'admin/late-absence*', 'admin/absen-offline*']) ? 'active' : '' }}">
                    <a href="{{ url('/admin/presence') }}"
                        class="flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-200 hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white">
                        <div class="flex items-center gap-x-4">
                            <i class="fa-solid fa-fingerprint w-5 text-center"></i>
                            <span>Presensi</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300"></i>
                    </a>
                    <ul class="submenu pt-2 pl-8 space-y-2">
                        <li>
                            <a href="{{ url('/admin/presence') }}"
                                class="flex items-center gap-x-3 py-2 rounded-xl transition-all duration-200 {{ Request::is('admin/presence') && !Request::is('admin/presence/late*') ? 'text-white font-semibold bg-gray-700 px-2.5 shadow-xs' : 'text-gray-400 hover:text-white hover:translate-x-1' }}">
                                <i class="fa-solid fa-check-circle fa-2xs {{ Request::is('admin/presence') && !Request::is('admin/presence/late*') ? 'scale-125' : '' }}"></i>
                                Presensi Reguler
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/admin/late-absence') }}"
                                class="flex items-center gap-x-3 py-2 rounded-xl transition-all duration-200 {{ Request::is('admin/late-absence*') ? 'text-white font-semibold bg-gray-700 px-2.5 shadow-xs' : 'text-gray-400 hover:text-white hover:translate-x-1' }}">
                                <i class="fa-solid fa-clock fa-2xs {{ Request::is('admin/late-absence*') ? 'scale-125' : '' }}"></i>
                                Telat Absen
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.absen-offline.index') }}"
                                class="flex items-center gap-x-3 py-2 rounded-xl transition-all duration-200 {{ Request::is('admin/absen-offline*') ? 'text-white font-semibold bg-gray-700 px-2.5 shadow-xs' : 'text-gray-400 hover:text-white hover:translate-x-1' }}">
                                <i class="fa-solid fa-user-check fa-2xs {{ Request::is('admin/absen-offline*') ? 'scale-125' : '' }}"></i>
                                Presensi Offline
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Menu Item: Presensi Otomatis -->
                <li>
                    <a href="{{ url('/admin/presensi-otomatis') }}"
                        class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::is('admin/presensi-otomatis*') || Request::is('admin/detail-auto-attendance*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                        <i class="fa-solid fa-robot w-5 text-center {{ Request::is('admin/presensi-otomatis*') || Request::is('admin/detail-auto-attendance*') ? 'scale-110' : '' }}"></i>
                        <span>Presensi Otomatis</span>
                    </a>
                </li>

                <!-- Menu Item: Divisi -->
                <li>
                    <a href="{{ url('/admin/division') }}"
                        class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::is('admin/division*') || Request::is('admin/interns*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                        <i class="fa-solid fa-sitemap w-5 text-center {{ Request::is('admin/division*') || Request::is('admin/interns*') ? 'scale-110' : '' }}"></i>
                        <span>Divisi</span>
                    </a>
                </li>

                <!-- Menu Item: Portofolio Project -->
                <li>
                    <a href="{{ route('admin.projects.completed') }}"
                        class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::is('admin/portofolio-project*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                        <i class="fa-solid fa-briefcase w-5 text-center {{ Request::is('admin/portofolio-project*') ? 'scale-110' : '' }}"></i>
                        <span>Portofolio Project</span>
                    </a>
                </li>

                <!-- Menu Item: Sekolah -->
                <li>
                    <a href="{{ url('/admin/sekolah') }}"
                        class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::is('admin/sekolah*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                        <i class="fa-solid fa-graduation-cap w-5 text-center {{ Request::is('admin/sekolah*') ? 'scale-110' : '' }}"></i>
                        <span>Sekolah / Kampus</span>
                    </a>
                </li>

                <!-- Menu Item: Laporan -->
                <li>
                    <a href="{{ url('/admin/report') }}"
                        class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::is('admin/report*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                        <i class="fa-solid fa-file-lines w-5 text-center {{ Request::is('admin/report*') ? 'scale-110' : '' }}"></i>
                        <span>Laporan</span>
                    </a>
                </li>

                <!-- Pemisah -->
                <hr class="my-3 border-gray-800">

                <!-- Dropdown: Izin -->
                <li class="has-submenu {{ Request::is('admin/izin-*') ? 'active' : '' }}">
                    <a href="{{ route('admin.izinKeluar.index') }}"
                        class="flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-200 hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white">
                        <div class="flex items-center gap-x-4">
                            <i class="fa-solid fa-clipboard-list w-5 text-center"></i>
                            <span>Manajemen Izin</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300"></i>
                    </a>
                    <ul class="submenu pt-2 pl-8 space-y-2">
                        <li>
                            <a href="{{ route('admin.permitSakit.index') }}"
                                class="flex items-center gap-x-3 py-2 rounded-xl transition-all duration-200 {{ Request::is('admin/izin-sakit*') ? 'text-white font-semibold bg-gray-700 px-2.5 shadow-xs' : 'text-gray-400 hover:text-white hover:translate-x-1' }}">
                                <i class="fa-solid fa-notes-medical fa-2xs {{ Request::is('admin/izin-sakit*') ? 'scale-125' : '' }}"></i>
                                Izin Sakit
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.permitKeperluan.index') }}"
                                class="flex items-center gap-x-3 py-2 rounded-xl transition-all duration-200 {{ Request::is('admin/izin-tidak-hadir*') ? 'text-white font-semibold bg-gray-700 px-2.5 shadow-xs' : 'text-gray-400 hover:text-white hover:translate-x-1' }}">
                                <i class="fa-solid fa-user-clock fa-2xs {{ Request::is('admin/izin-tidak-hadir*') ? 'scale-125' : '' }}"></i>
                                Izin Tidak Hadir
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.izinKeluar.index') }}"
                                class="flex items-center gap-x-3 py-2 rounded-xl transition-all duration-200 {{ Request::is('admin/izin-keluar*') ? 'text-white font-semibold bg-gray-700 px-2.5 shadow-xs' : 'text-gray-400 hover:text-white hover:translate-x-1' }}">
                                <i class="fa-solid fa-right-from-bracket fa-2xs {{ Request::is('admin/izin-keluar*') ? 'scale-125' : '' }}"></i>
                                Izin Keluar
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.izinShalat.index') }}"
                                class="flex items-center gap-x-3 py-2 rounded-xl transition-all duration-200 {{ Request::is('admin/izin-shalat*') ? 'text-white font-semibold bg-gray-700 px-2.5 shadow-xs' : 'text-gray-400 hover:text-white hover:translate-x-1' }}">
                                <i class="fa-solid fa-mosque fa-2xs {{ Request::is('admin/izin-shalat*') ? 'scale-125' : '' }}"></i>
                                Izin Shalat
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.izinToilet.index') }}"
                                class="flex items-center gap-x-3 py-2 rounded-xl transition-all duration-200 {{ Request::is('admin/izin-toilet*') ? 'text-white font-semibold bg-gray-700 px-2.5 shadow-xs' : 'text-gray-400 hover:text-white hover:translate-x-1' }}">
                                <i class="fa-solid fa-toilet fa-2xs {{ Request::is('admin/izin-toilet*') ? 'scale-125' : '' }}"></i>
                                Izin Toilet
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Dropdown: Shift -->
                <li class="has-submenu {{ Request::is('admin/shifts*') ? 'active' : '' }}">
                    <a href="{{ route('admin.shift.index') }}"
                        class="flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-200 hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white">
                        <div class="flex items-center gap-x-4">
                            <i class="fa-solid fa-business-time w-5 text-center"></i>
                            <span>Manajemen Shift</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300"></i>
                    </a>
                    <ul class="submenu pt-2 pl-8 space-y-2">
                        @php
                        $shiftNamesForSidebar = \Illuminate\Support\Facades\Cache::remember('sidebar_distinct_shift_names', 3600, function () {
                            return \App\Models\Shift::select('name')
                                ->distinct()
                                ->pluck('name')
                                ->filter(fn($name) => strtolower($name) !== 'none')
                                ->values();
                        });
                        @endphp

                        @forelse($shiftNamesForSidebar as $shiftName)
                        <li>
                            <a href="{{ route('admin.shift.index', ['name' => $shiftName]) }}"
                                class="flex items-center gap-x-3 py-2 rounded-xl transition-all duration-200 {{ request()->route('name') == $shiftName ? 'text-white font-semibold bg-gray-700 px-2.5 shadow-xs' : 'text-gray-400 hover:text-white hover:translate-x-1' }}">
                                <i class="fa-solid fa-clock fa-2xs {{ request()->route('name') == $shiftName ? 'scale-125' : '' }}"></i>
                                {{ ucwords(str_replace(['_', '-'], ' ', $shiftName)) }}
                            </a>
                        </li>
                        @empty
                        <li>
                            <span class="flex items-center gap-x-3 py-2 text-gray-500 text-xs">
                                <i class="fa-solid fa-times-circle fa-2xs"></i> Tidak ada shift
                            </span>
                        </li>
                        @endforelse

                        @if(Route::has('admin.shifts.bulk-update.form'))
                        <li>
                            <a href="{{ route('admin.shifts.bulk-update.form') }}"
                                class="flex items-center gap-x-3 py-2 rounded-xl transition-all duration-200 {{ Request::is('admin/shifts/bulk-update*') ? 'text-white font-semibold bg-gray-700 px-2.5 shadow-xs' : 'text-gray-400 hover:text-white hover:translate-x-1' }}">
                                <i class="fa-solid fa-users-gear fa-2xs {{ Request::is('admin/shifts/bulk-update*') ? 'scale-125' : '' }}"></i>
                                Update Massal
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>

                <!-- Menu Item: Raise Hand -->
                <li>
                    <a href="{{ route('admin.raiseHand.index') }}"
                        class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::is('admin/raise-hand*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                        <i class="fa-solid fa-hand-point-up w-5 text-center {{ Request::is('admin/raise-hand*') ? 'scale-110' : '' }}"></i>
                        <span>Raise Hand</span>
                    </a>
                </li>

                <!-- Menu Item: Broadcast Terjadwal -->
                <li>
                    <a href="{{ route('admin.scheduled-broadcasts.index') }}"
                        class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::is('admin/scheduled-broadcasts*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                        <i class="fa-solid fa-bullhorn w-5 text-center {{ Request::is('admin/scheduled-broadcasts*') ? 'scale-110' : '' }}"></i>
                        <span>Broadcast</span>
                    </a>
                </li>

                <!-- Pemisah -->
                <hr class="my-3 border-gray-800">

                <!-- Menu Item: Data Outsider -->
                <li>
                    <a href="{{ route('admin.outsiders.index') }}"
                        class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::is('admin/outsiders*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                        <i class="fa-solid fa-address-card w-5 text-center {{ Request::is('admin/outsiders*') ? 'scale-110' : '' }}"></i>
                        <span>Data Outsider</span>
                    </a>
                </li>

                <!-- Menu Item: Asisten Admin -->
                <li>
                    <a href="{{ route('admin.assistant-admins.index') }}"
                        class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::is('admin/assistant-admins*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                        <i class="fa-solid fa-user-tie w-5 text-center {{ Request::is('admin/assistant-admins*') ? 'scale-110' : '' }}"></i>
                        <span>Asisten Admin</span>
                    </a>
                </li>

                <!-- Menu Item: Pengaturan -->
                <li>
                    <a href="{{ url('/admin/setting') }}"
                        class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::is('admin/setting*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                        <i class="fa-solid fa-sliders w-5 text-center {{ Request::is('admin/setting*') ? 'scale-110' : '' }}"></i>
                        <span>Pengaturan</span>
                    </a>
                </li>

                @if(auth()->check() && (int) auth()->user()->role_id === 7)
                <!-- Pemisah Khusus Super Admin -->
                <hr class="my-3 border-gray-800">
                <div class="px-4 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-crown text-amber-400 text-xs"></i>
                    <span>Super Admin</span>
                </div>

                <!-- Menu Item: Kelola Akun Admin -->
                <li>
                    <a href="{{ route('super-admin.admins.index') }}"
                        class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::is('admin/super-admin/admins*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                        <i class="fa-solid fa-user-shield w-5 text-center {{ Request::is('admin/super-admin/admins*') ? 'scale-110' : '' }}"></i>
                        <span>Kelola Admin</span>
                    </a>
                </li>

                <!-- Menu Item: Audit Log Sistem -->
                <li>
                    <a href="{{ route('super-admin.activity-logs.index') }}"
                        class="flex items-center gap-x-4 px-4 py-2.5 rounded-xl transition-all duration-200 {{ Request::is('admin/super-admin/activity-logs*') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1 text-gray-300 hover:text-white' }}">
                        <i class="fa-solid fa-list-check w-5 text-center {{ Request::is('admin/super-admin/activity-logs*') ? 'scale-110' : '' }}"></i>
                        <span>Audit Log Sistem</span>
                    </a>
                </li>
                @endif

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
    /* Animasi submenu yang smooth */
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

    /* Scrollbar styling */
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

    /* Hover transitions */
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
            if (backdrop) {
                backdrop.classList.remove('hidden');
            }
            document.body.classList.add('overflow-hidden', 'md:overflow-auto');
        } else {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            if (backdrop) {
                backdrop.classList.add('hidden');
            }
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