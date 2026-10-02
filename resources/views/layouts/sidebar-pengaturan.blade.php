<!-- Mobile Settings Navigation -->
<div class="lg:hidden mt-16 md:mt-20 px-3 sm:px-6 pt-3 pb-0 max-w-full min-w-0">
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl shadow-xs border border-slate-200">
        <div class="flex items-center justify-between mb-2">
            <label for="mobile-settings-nav" class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                <i class="fa-solid fa-sliders text-blue-600"></i>
                <span>Menu Pengaturan</span>
            </label>
            <span class="text-[11px] text-slate-400 font-medium">Pilih Halaman</span>
        </div>
        <div class="relative">
            <select id="mobile-settings-nav" onchange="if(this.value) window.location.href=this.value" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:outline-none appearance-none transition-colors cursor-pointer">
                <option value="{{ route('admin.pengaturan.view') }}" {{ Request::is('*setting') ? 'selected' : '' }}>Manage Quotes</option>
                <option value="{{ route('admin.pengaturan.shift') }}" {{ Request::is('*shift*') ? 'selected' : '' }}>Manage Shift</option>
                <option value="{{ route('admin.pengaturan.divisi') }}" {{ Request::is('*ng/divis*') ? 'selected' : '' }}>Manage Divisi</option>
                <option value="{{ route('admin.pengaturan.brand') }}" {{ Request::is('*setting/brand*') ? 'selected' : '' }}>Manage Brand</option>
                <option value="{{ route('admin.pengaturan.meet') }}" {{ Request::is('*setting/meet*') ? 'selected' : '' }}>Manage Link GMeet</option>
                <option value="{{ route('admin.pengaturan.project') }}" {{ Request::is('*project*') ? 'selected' : '' }}>Manage Project</option>
                <option value="{{ route('admin.pengaturan.sekolah') }}" {{ Request::is('*school*') ? 'selected' : '' }}>Manage Sekolah</option>
                <option value="{{ route('admin.pengaturan.kantor') }}" {{ Request::is('*office*') ? 'selected' : '' }}>Manage Kantor</option>
                <option value="{{ route('admin.pengaturan.holiday') }}" {{ Request::is('*holiday*') ? 'selected' : '' }}>Manage Info & Libur</option>
                <option value="{{ route('admin.pengaturan.izin.view') }}" {{ Request::is('*setting/izin*') ? 'selected' : '' }}>Manage Izin</option>
                <option value="{{ route('admin.pengaturan.ganti-jam.view') }}" {{ Request::is('*setting/ganti-jam*') ? 'selected' : '' }}>Manage Ganti Jam</option>
                <option value="{{ route('admin.pengaturan.checkin-message') }}" {{ Request::is('*setting/checkin-message*') ? 'selected' : '' }}>Manage Popup Check-in</option>
                <option value="{{ route('admin.pengaturan.popup') }}" {{ Request::is('*manage-popup*') ? 'selected' : '' }}>Manage Popup</option>
                <option value="{{ route('admin.pengaturan.broadcast') }}" {{ Request::is('*broadcast*') ? 'selected' : '' }}>Manage Pengumuman</option>
                @if(auth()->check() && (int) auth()->user()->role_id === 7)
                <option value="{{ route('super-admin.app-settings.index') }}" {{ Request::is('*app-settings*') ? 'selected' : '' }}>Manage Web</option>
                @endif
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                <i class="fa-solid fa-chevron-down text-xs"></i>
            </div>
        </div>
    </div>
</div>

<!-- Desktop Sidebar Pengaturan -->
<div id="settings-sidebar-wrapper" class="hidden lg:block fixed bottom-0 top-20 left-64 transition-all duration-300 ease-in-out">
    <aside class="w-64 bg-white shadow h-full overflow-y-auto hide-scrollbar">
        <style>
            /* Sembunyikan scrollbar untuk Chrome, Safari, Opera */
            .hide-scrollbar::-webkit-scrollbar {
                display: none;
            }

            /* Sembunyikan scrollbar untuk IE, Edge, dan Firefox */
            .hide-scrollbar {
                -ms-overflow-style: none;
                /* IE and Edge */
                scrollbar-width: none;
                /* Firefox */
            }
        </style>
        <div class="p-4 mt-3 pb-16">
            <h1 class="text-2xl font-semibold mb-3 ml-5 text-gray-800">Pengaturan</h1>
            <ul>
                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.view') }}"
                        class="{{ Request::is('*setting') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Manage Quotes</span>
                    </a>
                </li>
                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.shift') }}"
                        class="{{ Request::is('*shift*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Manage Shift</span>
                    </a>
                </li>
                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.divisi') }}"
                        class="{{ Request::is('*ng/divis*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Manage Divisi</span>
                    </a>
                </li>
                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.brand') }}"
                        class="{{ Request::is('*setting/brand*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Manage Brand</span>
                    </a>
                </li>
                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.meet') }}"
                        class="{{ Request::is('*setting/meet*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Manage Link GMeet</span>
                    </a>
                </li>
                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.project') }}"
                        class="{{ Request::is('*project*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Manage Project</span>
                    </a>
                </li>
                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.sekolah') }}"
                        class="{{ Request::is('*school*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Manage Sekolah</span>
                    </a>
                </li>
                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.kantor') }}"
                        class="{{ Request::is('*office*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Manage Kantor</span>
                    </a>
                </li>
                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.holiday') }}"
                        class="{{ Request::is('*holiday*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Manage Info & Libur</span>
                    </a>
                </li>

                {{-- ========================================================== --}}
                {{-- [PERBAIKAN] Mengganti route, kondisi aktif, dan teks menu --}}
                {{-- ========================================================== --}}
                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.izin.view') }}"
                        class="{{ Request::is('*setting/izin*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Manage Izin</span>
                    </a>
                </li>

                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.ganti-jam.view') }}"
                        class="{{ Request::is('*setting/ganti-jam*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Manage Ganti Jam</span>
                    </a>
                </li>

                {{-- Setelah blok Pengaturan Izin yang sudah ada, tambahkan: --}}
                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.checkin-message') }}"
                        class="{{ Request::is('*setting/checkin-message*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Manage Popup Check-in</span>
                    </a>
                </li>

                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.popup') }}"
                        class="{{ Request::is('*manage-popup*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Manage Popup</span>
                    </a>
                </li>

                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.broadcast') }}"
                        class="{{ Request::is('*broadcast*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Manage Pengumuman</span>
                    </a>
                </li>

                @if(auth()->check() && (int) auth()->user()->role_id === 7)
                <li class="mb-3 pt-2 border-t border-gray-200">
                    <a href="{{ route('super-admin.app-settings.index') }}"
                        class="{{ Request::is('*app-settings*') ? 'flex items-center p-2 rounded text-white bg-gradient-to-r from-indigo-600 to-blue-600 font-bold shadow-sm' : 'flex items-center p-2 rounded text-indigo-700 hover:text-white hover:bg-indigo-600 bg-indigo-50/50' }}">
                        <i class="fa-solid fa-crown text-amber-500 text-xs ml-2"></i>
                        <span class="ml-2 font-semibold">Manage Web</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </aside>
</div>