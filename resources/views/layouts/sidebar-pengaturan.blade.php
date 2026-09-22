<!-- Sidebar Pengaturan -->
<div class="fixed bottom-0 top-20 left-64">
    <aside class="w-64 bg-white shadow h-full">
        <div class="p-4 mt-3">
            <h1 class="text-2xl font-semibold mb-3 ml-5 text-gray-800">Pengaturan</h1>
            <ul>
                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.view') }}"
                        class="{{ Request::is('*setting') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Quotes</span>
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
                    <a href="{{ route('admin.pengaturan.meet') }}"
                        class="{{ Request::is('*setting/meet*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Link GMeet Presentasi</span>
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
                        class="{{ Request::is('*schooll*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
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
                        <span class="ml-3">Pengaturan Izin</span>
                    </a>
                </li>

                {{-- Setelah blok Pengaturan Izin yang sudah ada, tambahkan: --}}
                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.checkin-message') }}"
                        class="{{ Request::is('*setting/checkin-message*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Popup Check-in</span>
                    </a>
                </li>

                <li class="mb-3">
                    <a href="{{ route('admin.pengaturan.broadcast') }}"
                        class="{{ Request::is('*broadcast*') ? 'flex items-center p-2 rounded text-white bg-gray-700' : 'flex items-center p-2 rounded text-gray-800 hover:text-white hover:bg-gray-700' }}">
                        <span class="ml-3">Manage Pengumuman</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>
</div>