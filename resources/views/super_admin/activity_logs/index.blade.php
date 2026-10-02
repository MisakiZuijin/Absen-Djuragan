@extends('layouts.main')

@section('contents')
<div class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-h-screen bg-slate-50 text-slate-800 min-w-0">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- HEADER SECTION -->
        <div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl shadow-xl p-6 md:p-8 text-white overflow-hidden border border-slate-800">
            <!-- Decorative Glow -->
            <div class="absolute -right-10 -top-10 w-48 h-48 bg-indigo-500/20 rounded-full blur-3xl"></div>
            <div class="absolute -left-10 -bottom-10 w-48 h-48 bg-blue-500/20 rounded-full blur-3xl"></div>

            <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded-full text-xs font-semibold uppercase tracking-wider">
                        <i class="fa-solid fa-crown text-amber-400"></i>
                        <span>Audit Trail & Security</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white flex items-center gap-2.5">
                        <i class="fa-solid fa-list-check text-indigo-400"></i>
                        Audit Log Aktivitas Sistem
                    </h1>
                    <p class="text-slate-300 text-xs md:text-sm max-w-2xl">
                        Pantau seluruh rekam jejak aktivitas yang dilakukan oleh semua pengguna (Super Admin, Admin, Asisten Admin, Outsider, dan Pemagang) secara real-time.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <a href="{{ route('super-admin.admins.index') }}" class="px-4 py-2.5 bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-2xl text-xs font-medium transition shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-user-shield text-indigo-400"></i>
                        <span>Kelola Admin</span>
                    </a>
                    <button type="button" onclick="openClearModal()" class="px-4 py-2.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/30 rounded-2xl text-xs font-semibold transition flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-trash-can"></i>
                        <span>Bersihkan Log</span>
                    </button>
                    <button type="button" onclick="submitExportCsv(false)" class="px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-2xl text-xs font-semibold shadow-lg shadow-emerald-600/30 transition flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-file-excel"></i>
                        <span>Export CSV</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ALERTS -->
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl shadow-sm flex items-center gap-3 text-sm">
                <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div>
                    <span class="font-bold">Berhasil:</span> {{ session('success') }}
                </div>
            </div>
        @endif

        <!-- STATS CARDS -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Total Log Hari Ini -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold shrink-0">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-medium">Log Hari Ini</div>
                    <div class="text-xl font-bold text-slate-800">{{ $totalLogsToday }}</div>
                </div>
            </div>

            <!-- Total Login Hari Ini -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold shrink-0">
                    <i class="fa-solid fa-right-to-bracket"></i>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-medium">Login Hari Ini</div>
                    <div class="text-xl font-bold text-blue-600">{{ $totalLoginsToday }}</div>
                </div>
            </div>

            <!-- Aksi Admin Hari Ini -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold shrink-0">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-medium">Aksi Admin</div>
                    <div class="text-xl font-bold text-amber-600">{{ $totalAdminActionsToday }}</div>
                </div>
            </div>

            <!-- Aksi Pemagang Hari Ini -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold shrink-0">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-medium">Aksi Pemagang</div>
                    <div class="text-xl font-bold text-emerald-600">{{ $totalInternActionsToday }}</div>
                </div>
            </div>
        </div>

        <!-- ADVANCED FILTER CARD -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs space-y-4">
            <form id="filterLogsForm" action="{{ route('super-admin.activity-logs.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
                    <!-- Search Umum -->
                    <div class="lg:col-span-2">
                        <label for="filterSearchInput" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Pencarian Umum</label>
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" name="search" id="filterSearchInput" value="{{ request('search') }}" placeholder="Cari aktivitas, user, role, modul, IP..." 
                                class="w-full pl-8 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        </div>
                    </div>

                    <!-- Filter Aksi -->
                    <div>
                        <label for="filterActionInput" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Aksi</label>
                        <select name="action" id="filterActionInput" class="w-full py-2 px-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            <option value="">Semua Aksi</option>
                            @foreach($actions as $act)
                                <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Modul -->
                    <div>
                        <label for="filterModuleSelect" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Modul</label>
                        <select name="module" id="filterModuleSelect" class="w-full py-2 px-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            <option value="">Semua Modul</option>
                            @foreach($modules as $mod)
                                <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="filterRoleSelect" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Peran (Role)</label>
                        <select name="role" id="filterRoleSelect" class="w-full py-2 px-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            <option value="">Semua Role</option>
                            @foreach($roles as $r)
                                <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dari Tanggal -->
                    <div>
                        <label for="filterDateStart" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Dari Tanggal</label>
                        <input type="date" name="date_start" id="filterDateStart" value="{{ request('date_start') }}"
                            class="w-full py-2 px-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    </div>

                    <!-- Sampai Tanggal -->
                    <div>
                        <label for="filterDateEnd" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Sampai Tanggal</label>
                        <input type="date" name="date_end" id="filterDateEnd" value="{{ request('date_end') }}"
                            class="w-full py-2 px-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    </div>

                    <!-- Per Page -->
                    <div>
                        <label for="filterPerPageSelect" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Tampilkan</label>
                        <select name="per_page" id="filterPerPageSelect" class="w-full py-2 px-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            <option value="25" {{ request('per_page', $perPage ?? 50) == 25 ? 'selected' : '' }}>25 / Hal</option>
                            <option value="50" {{ request('per_page', $perPage ?? 50) == 50 ? 'selected' : '' }}>50 / Hal (Default)</option>
                            <option value="100" {{ request('per_page', $perPage ?? 50) == 100 ? 'selected' : '' }}>100 / Hal</option>
                            <option value="200" {{ request('per_page', $perPage ?? 50) == 200 ? 'selected' : '' }}>200 / Hal</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-100">
                    <div class="text-[11px] text-slate-500 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-info text-indigo-500"></i>
                        <span>Ditemukan: <strong class="text-slate-800 font-bold">{{ $logs->total() }}</strong> rekam log aktivitas (menampilkan 50 per halaman)</span>
                    </div>
                    <div class="flex items-center gap-2 self-end sm:self-auto">
                        @if(request()->hasAny(['search', 'module', 'action', 'role', 'date_start', 'date_end', 'per_page']))
                            <a href="{{ route('super-admin.activity-logs.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition flex items-center gap-1.5">
                                <i class="fa-solid fa-rotate-left text-[11px]"></i>
                                <span>Reset Filter</span>
                            </a>
                        @endif
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-filter text-[11px]"></i>
                            <span>Terapkan Filter</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- TABLE LOGS -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                    <thead class="bg-slate-100/70 text-slate-700 uppercase font-semibold text-[10px] tracking-wider">
                        <tr>
                            <th class="px-4 py-3.5 w-10 text-center">
                                <input type="checkbox" id="selectAllLogs" onchange="toggleSelectAllLogs(this)" title="Pilih Semua di Halaman Ini" 
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-4 h-4">
                            </th>
                            <th class="px-4 py-3.5 w-36">Waktu</th>
                            <th class="px-4 py-3.5">Pelaku (User)</th>
                            <th class="px-4 py-3.5">Aksi & Modul</th>
                            <th class="px-4 py-3.5">Deskripsi Aktivitas</th>
                            <th class="px-4 py-3.5">IP & Perangkat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($logs as $log)
                            @php
                                $actionUpper = strtoupper($log->action);
                                $badgeClass = match($actionUpper) {
                                    'CREATE', 'APPROVE', 'RESTORE' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'UPDATE', 'LOGIN' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'DELETE', 'REJECT', 'PENALTY', 'LOGIN_FAILED' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'LOGOUT', 'EXPORT' => 'bg-slate-100 text-slate-700 border-slate-200',
                                    default => 'bg-purple-50 text-purple-700 border-purple-200',
                                };

                                $roleBadgeClass = match($log->user_role) {
                                    'Super Admin' => 'bg-amber-100 text-amber-800 border-amber-300',
                                    'Admin' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'Asisten Admin' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                    'Outsider' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'Magang' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    default => 'bg-slate-100 text-slate-600 border-slate-200',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition log-row">
                                <!-- Checkbox -->
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" name="selected_log_ids[]" value="{{ $log->id }}" onchange="updateSelectedCount()" 
                                        class="log-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-4 h-4">
                                </td>

                                <!-- Waktu -->
                                <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                                    <div class="font-medium text-slate-800 text-[11px]">
                                        {{ $log->created_at ? $log->created_at->format('d M Y') : '-' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        {{ $log->created_at ? $log->created_at->format('H:i:s') . ' WIB' : '' }}
                                    </div>
                                    <div class="text-[9px] text-slate-400 mt-0.5">
                                        {{ $log->created_at ? $log->created_at->diffForHumans() : '' }}
                                    </div>
                                </td>

                                <!-- User -->
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5 min-w-[140px]">
                                        <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 font-bold text-[10px] flex items-center justify-center shrink-0 uppercase border border-slate-200">
                                            {{ strtoupper(substr($log->user_name ?? 'U', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-xs truncate max-w-[160px]">
                                                {{ $log->user_name ?? 'Sistem / Tamu' }}
                                            </div>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[9px] font-semibold border {{ $roleBadgeClass }} mt-0.5">
                                                {{ $log->user_role ?? 'Tamu' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Aksi & Modul -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeClass }}">
                                                {{ $log->action }}
                                            </span>
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-medium flex items-center gap-1">
                                            <i class="fa-solid fa-folder-open text-[9px] text-slate-400"></i>
                                            <span>{{ $log->module }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Deskripsi Aktivitas -->
                                <td class="px-4 py-3">
                                    <div class="text-slate-700 text-xs leading-relaxed max-w-md">
                                        {{ $log->description }}
                                    </div>
                                </td>

                                <!-- IP & Perangkat -->
                                <td class="px-4 py-3 text-slate-500 text-[11px] whitespace-nowrap">
                                    <div class="font-mono text-slate-700 font-medium flex items-center gap-1">
                                        <i class="fa-solid fa-network-wired text-[9px] text-slate-400"></i>
                                        <span>{{ $log->ip_address ?? '127.0.0.1' }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 truncate max-w-[140px] mt-0.5" title="{{ $log->user_agent }}">
                                        <i class="fa-solid fa-laptop text-[9px] mr-0.5"></i>
                                        {{ Str::limit($log->user_agent ?? 'Unknown Device', 25) }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-lg">
                                        <i class="fa-solid fa-clipboard-question"></i>
                                    </div>
                                    <p class="font-medium text-slate-600 text-xs">Belum ada catatan log aktivitas</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Catatan log akan muncul otomatis saat user melakukan aksi di dalam aplikasi</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($logs->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

<!-- ================= FLOATING SELECTION ACTION BAR ================= -->
<div id="selectionBar" class="fixed bottom-6 right-6 z-40 hidden transition-all duration-300 transform translate-y-4 opacity-0">
    <div class="bg-slate-900/95 backdrop-blur-md text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-slate-700/80 flex items-center gap-4">
        <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="text-xs font-bold text-slate-200"><span id="selectedCountBadge" class="text-emerald-400 text-sm font-black">0</span> log dipilih</span>
        </div>
        <div class="h-4 w-px bg-slate-700"></div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="submitExportCsv(true)" class="px-3.5 py-1.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-file-excel"></i>
                <span>Export CSV Terpilih</span>
            </button>
            <button type="button" onclick="clearSelectedLogs()" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-medium transition cursor-pointer">
                Batal
            </button>
        </div>
    </div>
</div>

<!-- ================= MODAL BERSIHKAN LOG LAMA ================= -->
<div id="clearLogModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 border border-slate-200 animate-in fade-in duration-200">
        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3 text-xl shadow-xs">
            <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 class="font-bold text-slate-800 text-base text-center mb-1">Bersihkan Log Lama</h3>
        <p class="text-slate-500 text-xs text-center mb-4">
            Pilih batas umur log aktivitas yang ingin dihapus untuk mengoptimalkan ruang database.
        </p>

        <form action="{{ route('super-admin.activity-logs.clear') }}" method="POST" class="space-y-4">
            @csrf
            @method('DELETE')

            <div>
                <label for="purge_days" class="block text-xs font-semibold text-slate-700 mb-1">Hapus Log yang Lebih Tua Dari:</label>
                <select name="days" id="purge_days" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition">
                    <option value="30">30 Hari yang lalu</option>
                    <option value="60">60 Hari yang lalu</option>
                    <option value="90" selected>90 Hari yang lalu (Disarankan)</option>
                    <option value="180">180 Hari (6 Bulan)</option>
                    <option value="365">365 Hari (1 Tahun)</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeClearModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-rose-600/20 transition">
                    Hapus Log
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openClearModal() {
        document.getElementById('clearLogModal').classList.remove('hidden');
    }
    function closeClearModal() {
        document.getElementById('clearLogModal').classList.add('hidden');
    }

    // Toggle select all checkboxes on current page
    function toggleSelectAllLogs(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.log-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = masterCheckbox.checked;
        });
        updateSelectedCount();
    }

    // Update count and show/hide floating action bar
    function updateSelectedCount() {
        const checkedBoxes = document.querySelectorAll('.log-checkbox:checked');
        const count = checkedBoxes.length;
        const countBadge = document.getElementById('selectedCountBadge');
        const selectionBar = document.getElementById('selectionBar');
        const selectAll = document.getElementById('selectAllLogs');

        if (countBadge) countBadge.textContent = count;

        if (selectionBar) {
            if (count > 0) {
                selectionBar.classList.remove('hidden');
                setTimeout(() => {
                    selectionBar.classList.remove('translate-y-4', 'opacity-0');
                }, 10);
            } else {
                selectionBar.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => {
                    selectionBar.classList.add('hidden');
                }, 200);
            }
        }

        const totalOnPage = document.querySelectorAll('.log-checkbox').length;
        if (selectAll) {
            selectAll.checked = totalOnPage > 0 && count === totalOnPage;
            selectAll.indeterminate = count > 0 && count < totalOnPage;
        }
    }

    // Deselect all
    function clearSelectedLogs() {
        document.querySelectorAll('.log-checkbox').forEach(cb => {
            cb.checked = false;
        });
        const selectAll = document.getElementById('selectAllLogs');
        if (selectAll) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
        updateSelectedCount();
    }

    // Submit Export CSV selectively (Priority 1: Checklist, Priority 2: Filter/Search)
    function submitExportCsv(isSelectiveOnly = false) {
        const selectedCheckboxes = Array.from(document.querySelectorAll('.log-checkbox:checked')).map(cb => cb.value);

        const form = document.createElement('form');
        form.method = 'GET';
        form.action = "{{ route('super-admin.activity-logs.export') }}";

        // Prioritas 1: Jika ada baris checklist yang dicentang
        if (selectedCheckboxes.length > 0) {
            const inputIds = document.createElement('input');
            inputIds.type = 'hidden';
            inputIds.name = 'ids';
            inputIds.value = selectedCheckboxes.join(',');
            form.appendChild(inputIds);
        } else {
            // Prioritas 2: Ekspor berdasarkan filter pencarian aktif saat ini
            const filterForm = document.getElementById('filterLogsForm');
            if (filterForm) {
                const formData = new FormData(filterForm);
                for (const [key, value] of formData.entries()) {
                    if (value && key !== 'per_page') {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = value;
                        form.appendChild(input);
                    }
                }
            }
        }

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
</script>
@endsection
