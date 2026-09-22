@extends('layouts.main')

@section('title', 'Manajemen Izin Tidak Hadir & Sakit')

@section('contents')
    @include('layouts.sidebar')
    @include('layouts.navbar')

    <main class="ml-64 mt-24 p-4 md:p-6 lg:ml-64 bg-slate-50 min-h-screen">
        <!-- Header Section -->
        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-gradient-to-tr from-rose-600 to-indigo-600 rounded-2xl shadow-md text-white">
                        <i class="fa-solid fa-file-medical text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Izin Tidak Hadir & Sakit</h1>
                        <p class="text-xs md:text-sm text-slate-500 mt-0.5">Kelola verifikasi pengajuan izin sakit, izin keperluan biasa, dan penetapan status alpha pemagang.</p>
                    </div>
                </div>

                <!-- Quick Help / Information Badge -->
                <div class="flex items-center gap-2 px-3 py-2 bg-white rounded-xl border border-slate-200 shadow-sm text-xs text-slate-600">
                    <i class="fa-solid fa-circle-info text-blue-500"></i>
                    <span><strong>Sakit (Lunas):</strong> Bebas hutang jam | <strong>Alpha:</strong> Otomatis hutang penuh</span>
                </div>
            </div>
        </div>

        <!-- Alert Notification -->
        @if (session('success'))
            <div class="mb-6 flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 flex items-center justify-between p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
                    <p class="text-sm font-medium">{{ session('error') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        <!-- Summary Metric Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Card 1: Hari Ini -->
            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Izin Hari Ini</span>
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                        <i class="fa-solid fa-calendar-day text-sm"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl md:text-3xl font-bold text-slate-800">{{ $todayCount }}</span>
                    <span class="text-xs text-slate-400">pemagang</span>
                </div>
            </div>

            <!-- Card 2: Sakit -->
            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Izin Sakit</span>
                    <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                        <i class="fa-solid fa-notes-medical text-sm"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl md:text-3xl font-bold text-emerald-600">{{ $sakitCount }}</span>
                    <span class="text-xs text-slate-400">lunas/bebas jam</span>
                </div>
            </div>

            <!-- Card 3: Izin Biasa -->
            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Izin Keperluan</span>
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                        <i class="fa-solid fa-user-clock text-sm"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl md:text-3xl font-bold text-amber-600">{{ $izinBiasaCount }}</span>
                    <span class="text-xs text-slate-400">wajib ganti jam</span>
                </div>
            </div>

            <!-- Card 4: Alpha -->
            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Alpha (Tidak Hadir)</span>
                    <span class="p-2 bg-rose-50 text-rose-600 rounded-xl">
                        <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl md:text-3xl font-bold text-rose-600">{{ $alphaCount }}</span>
                    <span class="text-xs text-slate-400">hutang penuh</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200 shadow-sm mb-6 space-y-4">
            <!-- Tabs Quick Filter -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div class="flex flex-wrap items-center gap-1.5">
                    <a href="{{ route('admin.permitAbsence.index', array_merge(request()->query(), ['category' => 'all'])) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $categoryFilter === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Semua
                    </a>
                    <a href="{{ route('admin.permitAbsence.index', array_merge(request()->query(), ['category' => 'sakit'])) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $categoryFilter === 'sakit' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <i class="fa-solid fa-heart-pulse mr-1"></i> Izin Sakit
                    </a>
                    <a href="{{ route('admin.permitAbsence.index', array_merge(request()->query(), ['category' => 'izin'])) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $categoryFilter === 'izin' ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <i class="fa-solid fa-clipboard-question mr-1"></i> Izin Biasa
                    </a>
                    <a href="{{ route('admin.permitAbsence.index', array_merge(request()->query(), ['category' => 'alpha'])) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors {{ $categoryFilter === 'alpha' ? 'bg-rose-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <i class="fa-solid fa-ban mr-1"></i> Alpha
                    </a>
                </div>

                <!-- Reset Filter Button -->
                @if(request()->hasAny(['category', 'status', 'date', 'search']))
                    <a href="{{ route('admin.permitAbsence.index') }}"
                       class="text-xs font-medium text-slate-500 hover:text-rose-600 inline-flex items-center gap-1 transition-colors">
                        <i class="fa-solid fa-rotate-left"></i> Reset Filter
                    </a>
                @endif
            </div>

            <!-- Form Search & Date Filter -->
            <form method="GET" action="{{ route('admin.permitAbsence.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <input type="hidden" name="category" value="{{ $categoryFilter }}">

                <!-- Search Input -->
                <div class="md:col-span-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama pemagang, divisi, sekolah..."
                           class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                </div>

                <!-- Date Filter -->
                <div class="md:col-span-3">
                    <input type="date" name="date" value="{{ $dateFilter }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                </div>

                <!-- Status Ganti Jam Filter -->
                <div class="md:col-span-3">
                    <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status Jam</option>
                        <option value="lunas" {{ $statusFilter === 'lunas' ? 'selected' : '' }}>Lunas / Bebas Jam</option>
                        <option value="ganti_jam" {{ $statusFilter === 'ganti_jam' ? 'selected' : '' }}>Wajib Ganti Jam</option>
                        <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Belum Dikonfirmasi</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="md:col-span-1">
                    <button type="submit" class="w-full h-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Table of Absence & Permits -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50/80 text-slate-600 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3.5 text-center w-12">No</th>
                            <th class="px-4 py-3.5 text-left">Pemagang</th>
                            <th class="px-4 py-3.5 text-left">Tanggal & Shift</th>
                            <th class="px-4 py-3.5 text-center">Kategori</th>
                            <th class="px-4 py-3.5 text-left">Alasan / Keterangan</th>
                            <th class="px-4 py-3.5 text-center">Bukti Surat</th>
                            <th class="px-4 py-3.5 text-center">Status Hutang Jam</th>
                            <th class="px-4 py-3.5 text-center">Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($permits as $index => $item)
                            @php
                                $intern = $item->schedule?->intern;
                                $user = $intern?->user;
                                $profile = $user?->profile;
                                $name = $profile?->full_name ?? $user?->name ?? 'Pemagang';
                                $divisionName = $intern?->division?->name ?? 'Umum';
                                $schoolName = $intern?->school?->name ?? '-';

                                $isAlpha = ($item->attd_status_id == 5);
                                $isSakit = false;
                                if (!$isAlpha && $item->permitReason) {
                                    $catId = $item->permitReason->permit_category_id;
                                    $isSakit = in_array($catId, [1, 2]) || str_contains(strtolower($item->permitReason->description ?? ''), 'sakit');
                                }

                                $isLunas = ($item->isChangeSchedule == 1);
                                $isGantiJam = ($item->isChangeSchedule == 2);
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <!-- Number -->
                                <td class="px-4 py-3.5 text-center text-slate-400 font-medium">
                                    {{ $permits->firstItem() + $index }}
                                </td>

                                <!-- Intern Info -->
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800">{{ $name }}</div>
                                            <div class="text-[11px] text-slate-500 flex flex-col items-start gap-0.5 mt-0.5">
                                                <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-medium text-[10px]">{{ $divisionName }}</span>
                                                <span class="truncate max-w-[170px] text-slate-500" title="{{ $schoolName }}">{{ $schoolName }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Date & Shift -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="font-semibold text-slate-800">
                                        {{ \Carbon\Carbon::parse($item->date)->translatedFormat('d F Y') }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        <i class="fa-regular fa-clock text-slate-400 mr-1"></i>
                                        {{ $item->shift?->name ?? 'Shift' }} ({{ $item->shift?->start_time ?? '-' }} - {{ $item->shift?->end_time ?? '-' }})
                                    </div>
                                </td>

                                <!-- Category Badge -->
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    @if ($isAlpha)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                            <i class="fa-solid fa-ban"></i> Alpha
                                        </span>
                                    @elseif ($isSakit)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <i class="fa-solid fa-heart-pulse"></i> Izin Sakit
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <i class="fa-solid fa-clipboard-question"></i> Izin Biasa
                                        </span>
                                    @endif
                                </td>

                                <!-- Description -->
                                <td class="px-4 py-3.5 max-w-xs">
                                    <p class="text-slate-700 line-clamp-2" title="{{ $item->permitReason?->description ?? '-' }}">
                                        {{ $item->permitReason?->description ?? ($isAlpha ? 'Tidak hadir tanpa keterangan' : 'Tidak ada catatan') }}
                                    </p>
                                </td>

                                <!-- Proof File / Google Drive -->
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    @if (!empty($item->permitReason?->proof_url))
                                        <a href="{{ $item->permitReason->proof_url }}" target="_blank"
                                           class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-semibold transition">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Tanpa lampiran</span>
                                    @endif
                                </td>

                                <!-- Status Ganti Jam -->
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    @if ($isAlpha)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i class="fa-solid fa-circle-exclamation"></i> Hutang Penuh
                                        </span>
                                    @elseif ($isLunas)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-check-circle"></i> Lunas (Bebas Jam)
                                        </span>
                                    @elseif ($isGantiJam)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <i class="fa-solid fa-clock-rotate-left"></i> Wajib Ganti Jam
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">
                                            <i class="fa-solid fa-hourglass-half"></i> Menunggu Konfirmasi
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <!-- ACC Lunas (Bebas Jam) -->
                                        <form method="POST" action="{{ route('admin.permitAbsence.approveLunas', $item->id) }}" class="inline"
                                              onsubmit="return confirm('Setujui izin ini sebagai Bebas Ganti Jam (Lunas)? Pemagang tidak akan dibebani hutang jam.');">
                                            @csrf
                                            <button type="submit" title="ACC Lunas (Bebas Hutang Jam)"
                                                    class="p-1.5 text-emerald-600 hover:text-white hover:bg-emerald-600 rounded-lg border border-emerald-300 hover:border-emerald-600 transition shadow-xs">
                                                <i class="fa-solid fa-check text-xs"></i>
                                            </button>
                                        </form>

                                        <!-- ACC Ganti Jam (Wajib Ganti) -->
                                        <form method="POST" action="{{ route('admin.permitAbsence.approveGantiJam', $item->id) }}" class="inline"
                                              onsubmit="return confirm('Setujui izin ini dengan status Wajib Ganti Jam?');">
                                            @csrf
                                            <button type="submit" title="ACC Wajib Ganti Jam"
                                                    class="p-1.5 text-amber-600 hover:text-white hover:bg-amber-600 rounded-lg border border-amber-300 hover:border-amber-600 transition shadow-xs">
                                                <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                                            </button>
                                        </form>

                                        <!-- Set Alpha (Hutang Penuh) -->
                                        <form method="POST" action="{{ route('admin.permitAbsence.setAlpha', $item->id) }}" class="inline"
                                              onsubmit="return confirm('Ubah status menjadi Alpha (Tidak Hadir)? Shift akan otomatis dihitung penuh sebagai hutang jam kerja.');">
                                            @csrf
                                            <button type="submit" title="Jadikan Alpha (Hutang Penuh)"
                                                    class="p-1.5 text-rose-600 hover:text-white hover:bg-rose-600 rounded-lg border border-rose-300 hover:border-rose-600 transition shadow-xs">
                                                <i class="fa-solid fa-ban text-xs"></i>
                                            </button>
                                        </form>

                                        <!-- Edit Modal Trigger -->
                                        <button type="button" title="Edit Detail"
                                                onclick="openEditModal({{ $item->id }}, '{{ addslashes($item->permitReason?->description ?? '') }}', '{{ $item->permitReason?->proof_url ?? '' }}', {{ $item->permitReason?->permit_category_id ?? 1 }}, {{ $item->isChangeSchedule ?? 1 }})"
                                                class="p-1.5 text-slate-600 hover:text-white hover:bg-slate-700 rounded-lg border border-slate-300 hover:border-slate-700 transition shadow-xs">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-solid fa-file-circle-check text-4xl text-slate-300 mb-3"></i>
                                        <p class="font-semibold text-slate-600">Tidak ada data izin atau ketidakhadiran</p>
                                        <p class="text-xs text-slate-400 mt-1">Belum ada pengajuan izin atau catatan alpha sesuai filter yang dipilih.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($permits->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $permits->links() }}
                </div>
            @endif
        </div>
    </main>

    <!-- Modal Edit Detail Izin -->
    <div id="modalEditDetail" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs hidden animate-fade-in">
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg mx-4 overflow-hidden" onclick="event.stopPropagation();">
            <!-- Modal Header -->
            <div class="p-5 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/10 rounded-xl">
                        <i class="fa-solid fa-pen-nib text-indigo-300"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base">Edit Detail Perizinan</h3>
                        <p class="text-xs text-slate-300">Ubah rincian kategori, bukti drive, dan status ganti jam.</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal();" class="text-slate-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form id="editDetailForm" method="POST" action="" class="p-6 space-y-4">
                @csrf
                <!-- Category Select -->
                <div>
                    <label for="edit_permit_category_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kategori Izin <span class="text-rose-500">*</span>
                    </label>
                    <select id="edit_permit_category_id" name="permit_category_id" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="1">Izin Sakit (dengan surat dokter)</option>
                        <option value="2">Izin Sakit (tanpa surat dokter)</option>
                        <option value="3">Izin Biasa (Keperluan sekolah / kampus)</option>
                        <option value="4">Izin Biasa (Keperluan lain / pribadi)</option>
                    </select>
                </div>

                <!-- Jam Option Select -->
                <div>
                    <label for="edit_jam_option" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Status Penggantian Jam <span class="text-rose-500">*</span>
                    </label>
                    <select id="edit_jam_option" name="jam_option" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="1">Bebas Ganti Jam (Lunas - Tidak Berhutang Jam)</option>
                        <option value="2">Wajib Ganti Jam (Masuk Hutang Jam Kerja)</option>
                    </select>
                </div>

                <!-- Description -->
                <div>
                    <label for="edit_description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Alasan / Keterangan <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="edit_description" name="description" rows="3" required
                              class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Rincian alasan izin..."></textarea>
                </div>

                <!-- Proof Google Drive -->
                <div>
                    <label for="edit_proof_url" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Link Google Drive Bukti Surat
                    </label>
                    <input type="url" id="edit_proof_url" name="proof_url"
                           placeholder="https://drive.google.com/file/..."
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeEditModal();"
                            class="px-4 py-2 border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openEditModal(id, description, proofUrl, categoryId, jamOption) {
            const form = document.getElementById('editDetailForm');
            form.action = `/admin/izin-tidak-hadir/${id}/update-detail`;

            document.getElementById('edit_description').value = description;
            document.getElementById('edit_proof_url').value = proofUrl;
            document.getElementById('edit_permit_category_id').value = categoryId;
            document.getElementById('edit_jam_option').value = jamOption || 1;

            document.getElementById('modalEditDetail').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('modalEditDetail').classList.add('hidden');
        }

        document.getElementById('modalEditDetail')?.addEventListener('click', function (e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
    @endpush
@endsection
