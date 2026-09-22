<!-- Sidebar -->
<aside
    class="fixed top-0 left-0 z-50 w-64 h-screen bg-gray-900 text-white flex flex-col shadow-lg transform transition-transform duration-300 ease-in-out sidebar-container">

    <!-- Logo Header -->
    <div class="flex items-center justify-center p-6 border-b border-gray-700">
        <a href="{{ route('admin.home') }}" class="transition-transform duration-300 hover:scale-105">
            <img src="{{ asset('img/logo.svg') }}" alt="Logo" class="h-10">
        </a>
    </div>

    <!-- Navigasi Utama (Scrollable) -->
    <nav
        class="flex-1 px-4 py-6 space-y-2 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-700 scrollbar-track-gray-800">
        <ul class="space-y-2 text-gray-200">
            <!-- Menu Item: Dashboard -->
            <li>
                <a href="{{ url('/admin/home') }}"
                    class="flex items-center gap-x-4 px-4 py-2.5 rounded-lg transition-all duration-300 {{ Request::is('admin/home*') ? 'bg-gradient-to-r font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1' }}">
                    <i
                        class="fa-solid fa-chart-pie w-5 text-center transition-transform duration-300 {{ Request::is('admin/home*') ? 'scale-110' : '' }}"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Dropdown: Presensi -->
            <li class="has-submenu {{ Request::is(['admin/presence*', 'admin/late-absence*']) ? 'active' : '' }}">
    <a href="{{ url('/admin/presence') }}"
        class="flex items-center justify-between px-4 py-2.5 rounded-lg transition-all duration-300 hover:bg-gray-800 hover:translate-x-1">
        <div class="flex items-center gap-x-4">
            {{-- Menggunakan ikon dan class yang konsisten --}}
            <i class="fa-solid fa-fingerprint w-5 text-center"></i>
            <span>Presensi</span>
        </div>
        <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300"></i>
    </a>
    <ul class="submenu pt-2 pl-8 space-y-2">
        {{-- Menggunakan kode submenu asli Anda --}}
        <li>
            <a href="{{ url('/admin/presence') }}"
                class="flex items-center gap-x-3 py-2 rounded-lg transition-all duration-300 {{ Request::is('admin/presence') && !Request::is('admin/presence/late*') ? 'text-white font-semibold bg-gray-700 px-2' : 'hover:text-white hover:translate-x-1' }}">
                <i
                    class="fa-solid fa-check-circle fa-2xs transition-transform duration-300 {{ Request::is('admin/presence') && !Request::is('admin/presence/late*') ? 'scale-125' : '' }}"></i>
                Presensi Reguler
            </a>
        </li>
        <li>
            <a href="{{ url('/admin/late-absence') }}"
                class="flex items-center gap-x-3 py-2 rounded-lg transition-all duration-300 {{ Request::is('admin/late-absence*') ? 'text-white font-semibold bg-gray-700 px-2' : 'hover:text-white hover:translate-x-1' }}">
                <i
                    class="fa-solid fa-clock fa-2xs transition-transform duration-300 {{ Request::is('admin/late-absence*') ? 'scale-125' : '' }}"></i>
                Telat Absen
            </a>
        </li>
    </ul>
</li>

            <!-- Menu Item: Presensi Otomatis -->
            <li>
                <a href="{{ url('/admin/presensi-otomatis') }}"
                    class="flex items-center gap-x-4 px-4 py-2.5 rounded-lg transition-all duration-300 {{ Request::is('admin/presensi-otomatis*') || Request::is('admin/detail-auto-attendance*') ? 'bg-gradient-to-r font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1' }}">
                    <i
                        class="fa-solid fa-robot w-5 text-center transition-transform duration-300 {{ Request::is('admin/presensi-otomatis*') || Request::is('admin/detail-auto-attendance*') ? 'scale-110' : '' }}"></i>
                    <span>Presensi Otomatis</span>
                </a>
            </li>

            <!-- Menu Item: Divisi -->
            <li>
                <a href="{{ url('/admin/division') }}"
                    class="flex items-center gap-x-4 px-4 py-2.5 rounded-lg transition-all duration-300 {{ Request::is('admin/division*') ? 'bg-gradient-to-r font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1' }}">
                    <i
                        class="fa-solid fa-sitemap w-5 text-center transition-transform duration-300 {{ Request::is('admin/division*') ? 'scale-110' : '' }}"></i>
                    <span>Divisi</span>
                </a>
            </li>

            <!-- Menu Item: Portofolio Project -->
            <li>
                <a href="{{ route('admin.projects.completed') }}"
                    class="flex items-center gap-x-4 px-4 py-2.5 rounded-lg transition-all duration-300 {{ Request::is('admin/portofolio-project*') ? 'bg-gradient-to-r font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1' }}">
                    <i
                        class="fa-solid fa-briefcase w-5 text-center transition-transform duration-300 {{ Request::is('admin/portofolio-project*') ? 'scale-110' : '' }}"></i>
                    <span>Portofolio Project</span>
                </a>
            </li>

            <!-- Menu Item: Sekolah -->
            <li>
                <a href="{{ url('/admin/sekolah') }}"
                    class="flex items-center gap-x-4 px-4 py-2.5 rounded-lg transition-all duration-300 {{ Request::is('admin/sekolah*') ? 'bg-gradient-to-r font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1' }}">
                    <i
                        class="fa-solid fa-graduation-cap w-5 text-center transition-transform duration-300 {{ Request::is('admin/sekolah*') ? 'scale-110' : '' }}"></i>
                    <span>Sekolah / Kampus</span>
                </a>
            </li>

            <!-- Menu Item: Laporan -->
            <li>
                <a href="{{ url('/admin/report') }}"
                    class="flex items-center gap-x-4 px-4 py-2.5 rounded-lg transition-all duration-300 {{ Request::is('admin/report*') ? 'bg-gradient-to-r font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1' }}">
                    <i
                        class="fa-solid fa-file-lines w-5 text-center transition-transform duration-300 {{ Request::is('admin/report*') ? 'scale-110' : '' }}"></i>
                    <span>Laporan</span>
                </a>
            </li>

            <!-- Pemisah -->
            <hr class="my-4 border-gray-700">

            <!-- Dropdown: Izin -->
            <li class="has-submenu {{ Request::is('admin/izin-*') ? 'active' : '' }}">
                <a href="{{ route('admin.izinKeluar.index') }}"
                    class="flex items-center justify-between px-4 py-2.5 rounded-lg transition-all duration-300 hover:bg-gray-800 hover:translate-x-1">
                    <div class="flex items-center gap-x-4">
                        <i class="fa-solid fa-clipboard-list w-5 text-center"></i>
                        <span>Manajemen Izin</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300"></i>
                </a>
                <ul class="submenu pt-2 pl-8 space-y-2">
                    <li>
                        <a href="{{ route('admin.permitSakit.index') }}"
                            class="flex items-center gap-x-3 py-2 rounded-lg transition-all duration-300 {{ Request::is('admin/izin-sakit*') ? 'text-white font-semibold bg-gray-700 px-2' : 'hover:text-white hover:translate-x-1' }}">
                            <i
                                class="fa-solid fa-notes-medical fa-2xs transition-transform duration-300 {{ Request::is('admin/izin-sakit*') ? 'scale-125' : '' }}"></i>
                            Izin Sakit
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.permitKeperluan.index') }}"
                            class="flex items-center gap-x-3 py-2 rounded-lg transition-all duration-300 {{ Request::is('admin/izin-tidak-hadir*') ? 'text-white font-semibold bg-gray-700 px-2' : 'hover:text-white hover:translate-x-1' }}">
                            <i
                                class="fa-solid fa-user-clock fa-2xs transition-transform duration-300 {{ Request::is('admin/izin-tidak-hadir*') ? 'scale-125' : '' }}"></i>
                            Izin Tidak Hadir
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.izinKeluar.index') }}"
                            class="flex items-center gap-x-3 py-2 rounded-lg transition-all duration-300 {{ Request::is('admin/izin-keluar*') ? 'text-white font-semibold bg-gray-700 px-2' : 'hover:text-white hover:translate-x-1' }}">
                            <i
                                class="fa-solid fa-right-from-bracket fa-2xs transition-transform duration-300 {{ Request::is('admin/izin-keluar*') ? 'scale-125' : '' }}"></i>
                            Izin Keluar
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.izinShalat.index') }}"
                            class="flex items-center gap-x-3 py-2 rounded-lg transition-all duration-300 {{ Request::is('admin/izin-shalat*') ? 'text-white font-semibold bg-gray-700 px-2' : 'hover:text-white hover:translate-x-1' }}">
                            <i
                                class="fa-solid fa-mosque fa-2xs transition-transform duration-300 {{ Request::is('admin/izin-shalat*') ? 'scale-125' : '' }}"></i>
                            Izin Shalat
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.izinToilet.index') }}"
                            class="flex items-center gap-x-3 py-2 rounded-lg transition-all duration-300 {{ Request::is('admin/izin-toilet*') ? 'text-white font-semibold bg-gray-700 px-2' : 'hover:text-white hover:translate-x-1' }}">
                            <i
                                class="fa-solid fa-toilet fa-2xs transition-transform duration-300 {{ Request::is('admin/izin-toilet*') ? 'scale-125' : '' }}"></i>
                            Izin Toilet
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Dropdown: Shift -->
            <li class="has-submenu {{ Request::is('admin/shifts*') ? 'active' : '' }}">
                <a href="{{ route('admin.shift.index') }}"
                    class="flex items-center justify-between px-4 py-2.5 rounded-lg transition-all duration-300 hover:bg-gray-800 hover:translate-x-1">
                    <div class="flex items-center gap-x-4">
                        <i class="fa-solid fa-business-time w-5 text-center"></i>
                        <span>Manajemen Shift</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300"></i>
                </a>
                <ul class="submenu pt-2 pl-8 space-y-2">
                    @php
                        $shiftNamesForSidebar = \App\Models\Shift::select('name')
                            ->distinct()
                            ->pluck('name')
                            ->filter(fn($name) => strtolower($name) !== 'none');
                    @endphp

                    @forelse($shiftNamesForSidebar as $shiftName)
                        <li>
                            <a href="{{ route('admin.shift.index', ['name' => $shiftName]) }}"
                                class="flex items-center gap-x-3 py-2 rounded-lg transition-all duration-300 {{ request()->route('name') == $shiftName ? 'text-white font-semibold bg-gray-700 px-2' : 'hover:text-white hover:translate-x-1' }}">
                                <i
                                    class="fa-solid fa-clock fa-2xs transition-transform duration-300 {{ request()->route('name') == $shiftName ? 'scale-125' : '' }}"></i>
                                {{ ucwords(str_replace(['_', '-'], ' ', $shiftName)) }}
                            </a>
                        </li>
                    @empty
                        <li>
                            <span class="flex items-center gap-x-3 py-2 text-gray-500">
                                <i class="fa-solid fa-times-circle fa-2xs"></i> Tidak ada shift
                            </span>
                        </li>
                    @endforelse

                    @if(Route::has('admin.shifts.bulk-update.form'))
                        <li>
                            <a href="{{ route('admin.shifts.bulk-update.form') }}"
                                class="flex items-center gap-x-3 py-2 rounded-lg transition-all duration-300 {{ Request::is('admin/shifts/bulk-update*') ? 'text-white font-semibold bg-gray-700 px-2' : 'hover:text-white hover:translate-x-1' }}">
                                <i
                                    class="fa-solid fa-users-gear fa-2xs transition-transform duration-300 {{ Request::is('admin/shifts/bulk-update*') ? 'scale-125' : '' }}"></i>
                                Update Massal
                            </a>
                        </li>
                    @endif
                </ul>
            </li>

            <!-- Menu Item: Raise Hand -->
            <li>
                <a href="{{ route('admin.raiseHand.index') }}"
                    class="flex items-center gap-x-4 px-4 py-2.5 rounded-lg transition-all duration-300 {{ Request::is('admin/raise-hand*') ? 'bg-gradient-to-r font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1' }}">
                    <i
                        class="fa-solid fa-hand-point-up w-5 text-center transition-transform duration-300 {{ Request::is('admin/raise-hand*') ? 'scale-110' : '' }}"></i>
                    <span>Raise Hand</span>
                </a>
            </li>

            <!-- Pemisah -->
            <hr class="my-4 border-gray-700">

            <!-- Menu Item: Data Outsider -->
            <li>
                <a href="{{ route('admin.outsiders.index') }}"
                    class="flex items-center gap-x-4 px-4 py-2.5 rounded-lg transition-all duration-300 {{ Request::is('admin/outsiders*') ? 'bg-gradient-to-r font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1' }}">
                    <i
                        class="fa-solid fa-address-card w-5 text-center transition-transform duration-300 {{ Request::is('admin/outsiders*') ? 'scale-110' : '' }}"></i>
                    <span>Data Outsider</span>
                </a>
            </li>

            <!-- Menu Item: Asisten Admin -->
            <li>
                <a href="{{ route('admin.assistant-admins.index') }}"
                    class="flex items-center gap-x-4 px-4 py-2.5 rounded-lg transition-all duration-300 {{ Request::is('admin/assistant-admins*') ? 'bg-gradient-to-r font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1' }}">
                    <i
                        class="fa-solid fa-user-tie w-5 text-center transition-transform duration-300 {{ Request::is('admin/assistant-admins*') ? 'scale-110' : '' }}"></i>
                    <span>Asisten Admin</span>
                </a>
            </li>

            <!-- Menu Item: Pengaturan -->
            <li>
                <a href="{{ url('/admin/setting') }}"
                    class="flex items-center gap-x-4 px-4 py-2.5 rounded-lg transition-all duration-300 {{ Request::is('admin/setting*') ? 'bg-gradient-to-r font-bold text-white shadow-md' : 'hover:bg-gray-800 hover:translate-x-1' }}">
                    <i
                        class="fa-solid fa-sliders w-5 text-center transition-transform duration-300 {{ Request::is('admin/setting*') ? 'scale-110' : '' }}"></i>
                    <span>Pengaturan</span>
                </a>
            </li>

            <!-- Pemisah sebelum Logout -->
            <hr class="my-4 border-gray-700">

            <!-- Menu Item: Logout -->
            <li>
                <button
                    class="logoutModal w-full flex items-center gap-x-4 px-4 py-2.5 rounded-lg text-left transition-all duration-300 hover:bg-red-700 hover:text-white hover:translate-x-1">
                    <i class="fa-solid fa-power-off w-5 text-center"></i>
                    <span>Log Out</span>
                </button>
            </li>
        </ul>
    </nav>
</aside>

<!-- CSS Modern -->
<style>
    /* Animasi submenu yang lebih smooth */
    .submenu {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .has-submenu.active>.submenu {
        max-height: 500px;
    }

    .has-submenu.active>a .fa-chevron-down {
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
        background-color: #1f2937;
    }

    /* Efek hover yang lebih halus */
    .hover\:translate-x-1:hover {
        transform: translateX(0.25rem);
    }

    /* Animasi untuk ikon aktif */
    .scale-110 {
        transform: scale(1.1);
    }

    .scale-125 {
        transform: scale(1.25);
    }

    /* Efek glow untuk item aktif */
    .bg-gradient-to-r {
        background-size: 200% 100%;
        background-position: 100% 0;
        transition: background-position 0.5s ease;
    }

    .bg-gradient-to-r:hover {
        background-position: 0 0;
    }
</style>

<!-- JavaScript yang Lebih Interaktif -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const submenuTriggers = document.querySelectorAll('.has-submenu > a');
        const sidebar = document.querySelector('.sidebar-container');

        // Animasi saat sidebar dimuat
        setTimeout(() => {
            sidebar.classList.remove('transform');
        }, 100);

        submenuTriggers.forEach(trigger => {
            trigger.addEventListener('click', function (event) {
                const parentLi = this.parentElement;
                const isAlreadyActive = parentLi.classList.contains('active');
                const isLink = this.getAttribute('href') && this.getAttribute('href') !== '#';

                // Jika submenu SUDAH TERBUKA dan diklik lagi,
                // maka cegah link agar tidak pindah halaman, lalu tutup submenunya.
                if (isAlreadyActive && isLink) {
                    event.preventDefault();
                    parentLi.classList.remove('active');
                } else if (!isAlreadyActive && isLink) {
                    // Jika submenu belum terbuka, biarkan link bekerja normal
                    // tapi beri animasi sebelum navigasi
                    event.preventDefault();
                    parentLi.classList.add('active');

                    // Navigasi setelah animasi selesai
                    setTimeout(() => {
                        window.location.href = this.getAttribute('href');
                    }, 300);
                }
            });
        });

        // Efek hover yang lebih dinamis
        const menuItems = document.querySelectorAll('nav a');
        menuItems.forEach(item => {
            item.addEventListener('mouseenter', function () {
                this.classList.add('transition-all', 'duration-300');
            });
        });
    });
</script>
