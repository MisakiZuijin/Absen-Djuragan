@extends('layouts.main')

@section('contents')
<div class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 min-h-screen bg-slate-50 text-slate-800 min-w-0">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- HEADER SECTION -->
        <div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl shadow-xl p-6 md:p-8 text-white overflow-hidden border border-slate-800">
            <!-- Decorative Background Glow -->
            <div class="absolute -right-10 -top-10 w-48 h-48 bg-indigo-500/20 rounded-full blur-3xl"></div>
            <div class="absolute -left-10 -bottom-10 w-48 h-48 bg-blue-500/20 rounded-full blur-3xl"></div>

            <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded-full text-xs font-semibold uppercase tracking-wider">
                        <i class="fa-solid fa-crown text-amber-400"></i>
                        <span>Super Admin Area</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white flex items-center gap-2.5">
                        <i class="fa-solid fa-user-shield text-indigo-400"></i>
                        Manajemen Akun Admin
                    </h1>
                    <p class="text-slate-300 text-xs md:text-sm max-w-2xl">
                        Kelola akun Admin level 1 sistem: tambah admin baru, ubah kata sandi, aktifkan/nonaktifkan status akun, dan pantau wewenang sistem.
                    </p>
                </div>

                <div class="flex items-center gap-2.5 flex-wrap">
                    <a href="{{ route('super-admin.activity-logs.index') }}" class="px-4 py-2.5 bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-2xl text-xs font-medium transition shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-indigo-400"></i>
                        <span>Audit Log Sistem</span>
                    </a>
                    <button type="button" onclick="openCreateModal()" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white rounded-2xl text-xs font-semibold shadow-lg shadow-indigo-600/30 transition transform hover:-translate-y-0.5 flex items-center gap-2">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Tambah Admin</span>
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

        @if(session('error') || $errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl shadow-sm space-y-1 text-sm">
            <div class="flex items-center gap-2 font-bold">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                <span>Terjadi Kesalahan:</span>
            </div>
            <ul class="list-disc list-inside text-xs pl-2 space-y-0.5 text-rose-700">
                @if(session('error')) <li>{{ session('error') }}</li> @endif
                @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
        @endif

        <!-- STATS CARDS -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Total Admin -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold shrink-0">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-medium">Total Admin</div>
                    <div class="text-xl font-bold text-slate-800">{{ $totalAdmins }}</div>
                </div>
            </div>

            <!-- Admin Aktif -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold shrink-0">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-medium">Admin Aktif</div>
                    <div class="text-xl font-bold text-emerald-600">{{ $activeAdmins }}</div>
                </div>
            </div>

            <!-- Admin Nonaktif -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg font-bold shrink-0">
                    <i class="fa-solid fa-user-xmark"></i>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-medium">Nonaktif</div>
                    <div class="text-xl font-bold text-slate-600">{{ $inactiveAdmins }}</div>
                </div>
            </div>

            <!-- Super Admin -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold shrink-0">
                    <i class="fa-solid fa-crown"></i>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-medium">Super Admin</div>
                    <div class="text-xl font-bold text-amber-600">{{ $totalSuperAdmins }}</div>
                </div>
            </div>
        </div>

        <!-- TABLE & FILTER CONTAINER -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
            <!-- Filter Bar -->
            <div class="p-4 md:p-6 border-b border-slate-100 flex flex-col md:flex-row items-center justify-between gap-3 bg-slate-50/50">
                <form action="{{ route('super-admin.admins.index') }}" method="GET" class="w-full md:w-auto flex-1 flex flex-col sm:flex-row items-center gap-2.5">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-72">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, username, email..."
                            class="w-full pl-9 pr-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    </div>

                    <!-- Status Filter -->
                    <select name="status" onchange="this.form.submit()"
                        class="w-full sm:w-40 py-2 px-3 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>

                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition">
                        Filter
                    </button>

                    @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('super-admin.admins.index') }}" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-medium transition">
                        Reset
                    </a>
                    @endif
                </form>

                <div class="text-xs text-slate-500 font-medium">
                    Menampilkan <span class="font-bold text-slate-700">{{ $admins->count() }}</span> dari <span class="font-bold text-slate-700">{{ $admins->total() }}</span> admin
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                    <thead class="bg-slate-100/70 text-slate-700 uppercase font-semibold text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Admin</th>
                            <th class="px-5 py-3.5">Kontak & Username</th>
                            <th class="px-5 py-3.5">Status Akun</th>
                            <th class="px-5 py-3.5">Terdaftar</th>
                            <th class="px-5 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($admins as $admin)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Name & Avatar -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-blue-500 text-white font-bold text-xs flex items-center justify-center shadow-xs shrink-0 uppercase">
                                        {{ strtoupper(substr($admin->profile?->full_name ?? $admin->username, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 text-xs">
                                            {{ $admin->profile?->full_name ?? $admin->username }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.2 bg-indigo-50 text-indigo-700 rounded font-medium">
                                                <i class="fa-solid fa-shield-halved text-[8px]"></i> Role 1 - Admin
                                            </span>
                                            @if($admin->profile?->gender)
                                            <span>• {{ $admin->profile->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact & Username -->
                            <td class="px-5 py-3.5">
                                <div class="space-y-0.5">
                                    <div class="font-mono text-slate-700 font-semibold flex items-center gap-1">
                                        <i class="fa-solid fa-at text-[9px] text-slate-400"></i>{{ $admin->username }}
                                    </div>
                                    <div class="text-slate-500 text-[11px] flex items-center gap-1">
                                        <i class="fa-regular fa-envelope text-[9px] text-slate-400"></i>{{ $admin->email }}
                                    </div>
                                    @if($admin->profile?->phone)
                                    <div class="text-slate-500 text-[11px] flex items-center gap-1">
                                        <i class="fa-brands fa-whatsapp text-[10px] text-emerald-500"></i>{{ $admin->profile->phone }}
                                    </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Status Toggle -->
                            <td class="px-5 py-3.5">
                                <form action="{{ route('super-admin.admins.toggle-status', $admin->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="Klik untuk mengubah status"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold transition border {{ $admin->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $admin->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        <span>{{ $admin->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        <i class="fa-solid fa-rotate text-[8px] opacity-60 ml-0.5"></i>
                                    </button>
                                </form>
                            </td>

                            <!-- Created At -->
                            <td class="px-5 py-3.5 text-slate-500 text-[11px]">
                                <div>{{ $admin->created_at ? $admin->created_at->format('d M Y') : '-' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $admin->created_at ? $admin->created_at->format('H:i') . ' WIB' : '' }}</div>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <!-- Edit Button -->
                                    <button type="button"
                                        onclick="openEditModalFromButton(this)"
                                        data-admin="{{ json_encode([
                                            'id' => $admin->id,
                                            'full_name' => $admin->profile?->full_name ?? '',
                                            'username' => $admin->username,
                                            'email' => $admin->email,
                                            'phone' => $admin->profile?->phone ?? '',
                                            'gender' => $admin->profile?->gender ?? 'L',
                                            'is_active' => (bool)$admin->is_active,
                                            'update_url' => route('super-admin.admins.update', $admin->id)
                                        ]) }}"
                                        class="w-7 h-7 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 flex items-center justify-center transition shadow-2xs" title="Edit Admin">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button"
                                        onclick="openDeleteModalFromButton(this)"
                                        data-url="{{ route('super-admin.admins.destroy', $admin->id) }}"
                                        data-name="{{ $admin->profile?->full_name ?? $admin->username }}"
                                        class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition shadow-2xs" title="Hapus Admin">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-lg">
                                    <i class="fa-solid fa-user-slash"></i>
                                </div>
                                <p class="font-medium text-slate-600 text-xs">Tidak ada data Admin ditemukan</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">Silakan buat akun Admin baru atau ubah kata kunci pencarian</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($admins->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $admins->links() }}
            </div>
            @endif
        </div>

    </div>
</div>

<!-- ================= MODAL TAMBAH ADMIN ================= -->
<div id="createAdminModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="relative bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-lg w-full max-h-[92vh] flex flex-col p-4 sm:p-6 border border-slate-200 animate-in fade-in duration-200">
        <div class="flex items-center justify-between pb-3 sm:pb-4 border-b border-slate-100 mb-3 sm:mb-4 shrink-0">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs sm:text-sm font-bold shadow-2xs shrink-0">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-slate-800 text-sm sm:text-base truncate">Tambah Akun Admin Baru</h3>
                    <p class="text-slate-400 text-[10px] sm:text-[11px] truncate">Membuat akses akun administrator tingkat 1</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateModal()" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition shrink-0" aria-label="Tutup">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>

        <form action="{{ route('super-admin.admins.store') }}" method="POST" class="space-y-3 sm:space-y-3.5 flex-1 overflow-y-auto pr-1">
            @csrf
            <!-- Nama Lengkap -->
            <div>
                <label for="create_full_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input type="text" name="full_name" id="create_full_name" required placeholder="Contoh: Budi Santoso"
                    class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                <!-- Username -->
                <div>
                    <label for="create_username" class="block text-xs font-semibold text-slate-700 mb-1">Username <span class="text-rose-500">*</span></label>
                    <input type="text" name="username" id="create_username" required placeholder="budi_admin"
                        class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <!-- Email -->
                <div>
                    <label for="create_email" class="block text-xs font-semibold text-slate-700 mb-1">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="create_email" required placeholder="budi@example.com"
                        class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                <!-- Password -->
                <div>
                    <label for="create_password" class="block text-xs font-semibold text-slate-700 mb-1">Password <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" id="create_password" required placeholder="Minimal 6 karakter"
                        class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <!-- No WhatsApp -->
                <div>
                    <label for="create_phone" class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp / HP</label>
                    <input type="text" name="phone" id="create_phone" placeholder="08123456789"
                        class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3 pt-0.5">
                <!-- Gender -->
                <div>
                    <label for="create_gender" class="block text-xs font-semibold text-slate-700 mb-1">Jenis Kelamin</label>
                    <select name="gender" id="create_gender" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>

                <!-- Status Aktif -->
                <div class="flex items-center gap-2 pt-1 sm:pt-6">
                    <input type="checkbox" name="is_active" id="create_is_active" value="1" checked
                        class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                    <label for="create_is_active" class="text-xs font-semibold text-slate-700 select-none cursor-pointer">
                        Status Akun Aktif
                    </label>
                </div>
            </div>

            <div class="pt-3 sm:pt-4 border-t border-slate-100 flex items-center justify-end gap-2 shrink-0">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                    Simpan Admin
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL EDIT ADMIN ================= -->
<div id="editAdminModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="relative bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-lg w-full max-h-[92vh] flex flex-col p-4 sm:p-6 border border-slate-200 animate-in fade-in duration-200">
        <div class="flex items-center justify-between pb-3 sm:pb-4 border-b border-slate-100 mb-3 sm:mb-4 shrink-0">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs sm:text-sm font-bold shadow-2xs shrink-0">
                    <i class="fa-solid fa-user-pen"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-slate-800 text-sm sm:text-base truncate">Edit Akun Admin</h3>
                    <p class="text-slate-400 text-[10px] sm:text-[11px] truncate">Perbarui informasi profil atau reset kata sandi</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition shrink-0" aria-label="Tutup">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>

        <form id="editAdminForm" action="" method="POST" class="space-y-3 sm:space-y-3.5 flex-1 overflow-y-auto pr-1">
            @csrf
            @method('PUT')
            <!-- Nama Lengkap -->
            <div>
                <label for="edit_full_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input type="text" name="full_name" id="edit_full_name" required
                    class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                <!-- Username -->
                <div>
                    <label for="edit_username" class="block text-xs font-semibold text-slate-700 mb-1">Username <span class="text-rose-500">*</span></label>
                    <input type="text" name="username" id="edit_username" required
                        class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <!-- Email -->
                <div>
                    <label for="edit_email" class="block text-xs font-semibold text-slate-700 mb-1">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="edit_email" required
                        class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                <!-- Password (Opsional saat edit) -->
                <div>
                    <label for="edit_password" class="block text-xs font-semibold text-slate-700 mb-1">
                        Password Baru <span class="text-[10px] text-slate-400 font-normal">(Kosongkan jika tetap)</span>
                    </label>
                    <input type="password" name="password" id="edit_password" placeholder="Kosongkan jika tidak diubah"
                        class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>

                <!-- No WhatsApp -->
                <div>
                    <label for="edit_phone" class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp / HP</label>
                    <input type="text" name="phone" id="edit_phone" placeholder="08123456789"
                        class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3 pt-0.5">
                <!-- Gender -->
                <div>
                    <label for="edit_gender" class="block text-xs font-semibold text-slate-700 mb-1">Jenis Kelamin</label>
                    <select name="gender" id="edit_gender" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>

                <!-- Status Aktif -->
                <div class="flex items-center gap-2 pt-1 sm:pt-6">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                        class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                    <label for="edit_is_active" class="text-xs font-semibold text-slate-700 select-none cursor-pointer">
                        Status Akun Aktif
                    </label>
                </div>
            </div>

            <div class="pt-3 sm:pt-4 border-t border-slate-100 flex items-center justify-end gap-2 shrink-0">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                    Perbarui Admin
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL KONFIRMASI HAPUS ================= -->
<div id="deleteAdminModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 text-center border border-slate-200 animate-in fade-in duration-200">
        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3 text-xl shadow-xs">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="font-bold text-slate-800 text-base mb-1">Hapus Akun Admin?</h3>
        <p class="text-slate-500 text-xs mb-5">
            Apakah Anda yakin ingin menghapus admin <span id="deleteAdminName" class="font-bold text-slate-800"></span>? Aksi ini tidak dapat dibatalkan.
        </p>

        <form id="deleteAdminForm" action="" method="POST" class="flex items-center justify-center gap-2">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                Batal
            </button>
            <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-rose-600/20 transition">
                Ya, Hapus
            </button>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createAdminModal').classList.remove('hidden');
    }

    function closeCreateModal() {
        document.getElementById('createAdminModal').classList.add('hidden');
    }

    function openEditModalFromButton(btn) {
        try {
            const data = JSON.parse(btn.getAttribute('data-admin') || '{}');
            openEditModal(data);
        } catch (e) {
            console.error('Error parsing admin data:', e);
        }
    }

    function openDeleteModalFromButton(btn) {
        const actionUrl = btn.getAttribute('data-url') || '';
        const name = btn.getAttribute('data-name') || '';
        openDeleteModal(actionUrl, name);
    }

    function openEditModal(data) {
        document.getElementById('editAdminForm').action = data.update_url;
        document.getElementById('edit_full_name').value = data.full_name;
        document.getElementById('edit_username').value = data.username;
        document.getElementById('edit_email').value = data.email;
        document.getElementById('edit_phone').value = data.phone;
        document.getElementById('edit_gender').value = data.gender;
        document.getElementById('edit_is_active').checked = data.is_active;

        document.getElementById('editAdminModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editAdminModal').classList.add('hidden');
    }

    function openDeleteModal(actionUrl, name) {
        document.getElementById('deleteAdminForm').action = actionUrl;
        document.getElementById('deleteAdminName').textContent = name;
        document.getElementById('deleteAdminModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteAdminModal').classList.add('hidden');
    }
</script>
@endsection