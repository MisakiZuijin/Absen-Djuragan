@extends('layouts.main')

@section('title', 'Manage Popup & Refresh Data')

@section('contents')
    @include('layouts.sidebar-pengaturan')

    <!-- Main Content -->
    <main class="ml-0 lg:ml-[32rem] mt-2 lg:mt-20 p-3 sm:p-6 pb-16 min-w-0 max-w-full overflow-x-hidden">
        <div class="w-full max-w-full min-w-0 space-y-6">

            <!-- Header Section -->
            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0 shadow-2xs">
                            <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-0.5">Pengaturan Refresh Otomatis</h1>
                            <p class="text-gray-500 text-xs sm:text-sm">
                                Atur seberapa sering data dan notifikasi diperbarui otomatis tanpa harus memuat ulang halaman.
                            </p>
                        </div>
                    </div>
                    <div>
                        <form action="{{ route('admin.pengaturan.popup.reset') }}" method="POST" onsubmit="return confirm('Kembalikan semua interval refresh ke setelan default?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 sm:px-4 sm:py-2.5 rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 font-semibold text-xs sm:text-sm shadow-2xs transition-colors cursor-pointer">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                </svg>
                                <span>Reset ke Default</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            @if (session('success'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 p-4 rounded-xl shadow-xs flex items-center justify-between text-xs sm:text-sm" role="alert">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <span class="font-bold">Sukses:</span> {{ session('success') }}
                        </div>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-950 font-bold text-lg leading-none cursor-pointer">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-rose-50 border border-rose-300 text-rose-800 p-4 rounded-xl shadow-xs flex items-center justify-between text-xs sm:text-sm" role="alert">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <div>
                            <span class="font-bold">Gagal:</span> {{ session('error') }}
                        </div>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-950 font-bold text-lg leading-none cursor-pointer">&times;</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-xl shadow-xs">
                    <p class="font-bold flex items-center gap-2 mb-1 text-xs sm:text-sm">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        Perhatian:
                    </p>
                    <ul class="list-disc list-inside text-xs space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Info Guide Card Ringkas -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-5 text-slate-800 shadow-xs flex items-start gap-3.5">
                <div class="w-9 h-9 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                    <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                </div>
                <div class="space-y-1 text-xs sm:text-sm leading-relaxed">
                    <div class="font-bold text-slate-900">Panduan Singkat:</div>
                    <p class="text-slate-600">
                        • <strong>Waktu Refresh (Detik)</strong>: Jeda waktu pengecekan data baru di latar belakang (minimal 2 detik).<br>
                        • <strong>Sakelar Aktif / Mati</strong>: Matikan sakelar jika fitur tersebut tidak perlu diperbarui otomatis.<br>
                        • <strong>Penerapan Interval</strong>: Perubahan interval refresh akan otomatis berlaku saat halaman terkait dimuat ulang (refresh) oleh pengguna.
                    </p>
                </div>
            </div>

            <!-- Form Form List -->
            <form action="{{ route('admin.pengaturan.popup.update') }}" method="POST">
                @csrf

                @php
                    $metaConfig = [
                        'attd_status_button' => [
                            'title' => 'Status Presensi Pemagang',
                            'badge' => 'Dashboard Pemagang',
                            'desc' => 'Memperbarui status tombol presensi, timer jam kerja, dan popup bantuan di akun pemagang.',
                            'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                        ],
                        'broadcast_popup' => [
                            'title' => 'Popup Pengumuman',
                            'badge' => 'Dashboard Pemagang',
                            'desc' => 'Mengecek dan menampilkan popup pengumuman baru untuk pemagang secara otomatis.',
                            'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.455a20.01 20.01 0 01-1.077-2.909m2.74-1.31c4.106 0 7.854 1.43 10.748 3.826.541.448 1.342.062 1.342-.642V5.216c0-.704-.8-1.09-1.342-.642C18.194 6.97 14.446 8.4 10.34 8.4m0 7.44V8.4" /></svg>',
                        ],
                        'change_time_info' => [
                            'title' => 'Status Sesi Ganti Jam',
                            'badge' => 'Dashboard Pemagang',
                            'desc' => 'Memperbarui status dan timer berjalan pada sesi ganti jam pemagang.',
                            'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.253M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>',
                        ],
                        'raise_hand_manager' => [
                            'title' => 'Tabel Antrean Bantuan (Raise Hand)',
                            'badge' => 'Panel Admin',
                            'desc' => 'Memperbarui daftar antrean permintaan bantuan pemagang di halaman Raise Hand Admin.',
                            'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" /></svg>',
                        ],
                        'toilet_monitor' => [
                            'title' => 'Monitor Izin (Toilet, Shalat, Keluar)',
                            'badge' => 'Panel Admin',
                            'desc' => 'Memperbarui daftar pemagang dan timer berjalan saat sedang izin toilet, shalat, atau izin keluar di panel admin.',
                            'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>',
                        ],
                        'raise_hand_notification' => [
                            'title' => 'Suara Notifikasi & Pesan Chat',
                            'badge' => 'Global Admin',
                            'desc' => 'Mengecek notifikasi suara lonceng dan kartu pemberitahuan chat/bantuan baru di panel admin.',
                            'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>',
                        ],
                    ];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @foreach($settings as $key => $setting)
                        @php
                            $meta = $metaConfig[$key] ?? [
                                'title' => $setting->label,
                                'badge' => 'Sistem',
                                'desc' => $setting->description,
                                'svg' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" /></svg>',
                            ];
                        @endphp
                        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-gray-200/80 shadow-xs hover:border-gray-300 transition-all flex flex-col justify-between gap-4 relative overflow-hidden group">
                            <div class="space-y-3">
                                <!-- Top Bar in Card -->
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition-transform">
                                            {!! $meta['svg'] !!}
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-sm sm:text-base text-gray-900 leading-tight">
                                                {{ $meta['title'] }}
                                            </h3>
                                            <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                {{ $meta['badge'] }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Switch Enable/Disable -->
                                    <div class="flex items-center gap-2">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox"
                                                   name="settings[{{ $key }}][is_enabled]"
                                                   value="1"
                                                   class="sr-only peer"
                                                   id="toggle_{{ $key }}"
                                                   {{ $setting->is_enabled ? 'checked' : '' }}
                                                   onchange="handleToggleChange('{{ $key }}')">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Description Ringkas & Mudah Dipahami -->
                                <p class="text-xs text-gray-600 leading-relaxed min-h-[32px]">
                                    {{ $meta['desc'] }}
                                </p>
                            </div>

                            <!-- Interval Input & Status Info -->
                            <div class="pt-3.5 border-t border-gray-100 flex items-center justify-between gap-3 bg-gray-50/60 -mx-5 sm:-mx-6 -mb-5 sm:-mb-6 p-3.5 sm:p-4 rounded-b-2xl">
                                <div class="flex items-center gap-2">
                                    <label for="interval_{{ $key }}" class="text-xs font-bold text-gray-700 whitespace-nowrap">
                                        Setiap:
                                    </label>
                                    <div class="relative flex items-center">
                                        <input type="number"
                                               name="settings[{{ $key }}][interval_seconds]"
                                               id="interval_{{ $key }}"
                                               min="2"
                                               max="300"
                                               value="{{ $setting->interval_seconds }}"
                                               required
                                               class="w-16 sm:w-20 px-2 py-1.5 text-xs sm:text-sm font-bold text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white shadow-2xs text-center">
                                        <span class="ml-1.5 text-xs font-semibold text-gray-500">detik</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <span id="badge_status_{{ $key }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold {{ $setting->is_enabled ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $setting->is_enabled ? 'bg-emerald-600 animate-pulse' : 'bg-rose-600' }}"></span>
                                        <span>{{ $setting->is_enabled ? 'Aktif' : 'Mati' }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Action Button Save -->
                <div class="mt-8 flex justify-end">
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span>Simpan Pengaturan</span>
                    </button>
                </div>
            </form>

        </div>
    </main>

    <script>
        function handleToggleChange(key) {
            const checkbox = document.getElementById('toggle_' + key);
            const badge = document.getElementById('badge_status_' + key);

            if (checkbox && badge) {
                if (checkbox.checked) {
                    badge.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-800';
                    badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span><span>Aktif</span>';
                } else {
                    badge.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-rose-100 text-rose-800';
                    badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span><span>Mati</span>';
                }
            }
        }
    </script>
@endsection
