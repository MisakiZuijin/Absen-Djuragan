@extends('layouts.main')

@section('title', 'Broadcast Terjadwal')

@section('contents')
<div class="ml-0 md:ml-64 mt-16 md:mt-20 p-3 sm:p-6 bg-slate-50/50 min-h-screen min-w-0">
    <div class="max-w-6xl mx-auto space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                        <i class="fa-solid fa-bullhorn text-lg"></i>
                    </span>
                    <h1 class="text-2xl font-bold text-gray-800">Broadcast Terjadwal</h1>
                </div>
                <p class="text-sm text-gray-500 mt-1">Kirim pesan teks atau pertanyaan interaktif kepada pemagang dengan penjadwalan & targeting presisi.</p>
            </div>
            <div>
                <button type="button" onclick="toggleFormModal()" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-plus text-sm"></i>
                    <span>Buat Broadcast Baru</span>
                </button>
            </div>
        </div>

        <!-- Alert Notifikasi -->
        @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold">&times;</button>
        </div>
        @endif

        @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold">&times;</button>
        </div>
        @endif

        @if ($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-sm">
            <p class="font-bold flex items-center gap-2 mb-1">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Perhatian:
            </p>
            <ul class="list-disc list-inside text-sm space-y-0.5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Tabel Daftar Broadcast Terjadwal -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-gray-400"></i>
                    Riwayat Broadcast Terjadwal
                </h2>
                <span class="text-xs text-gray-400">Total: {{ $broadcastlist->total() }} Pesan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50/75 text-xs uppercase font-semibold text-gray-500 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Judul & Pesan</th>
                            <th class="px-6 py-4">Target Penerima</th>
                            <th class="px-6 py-4">Target Shift</th>
                            <th class="px-6 py-4">Jadwal / Pengiriman</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Balasan Pemagang</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($broadcastlist as $item)
                        <tr class="hover:bg-gray-50/50 transition">
                            <!-- Judul & Pesan -->
                            <td class="px-6 py-4 max-w-sm">
                                <p class="font-bold text-gray-900 line-clamp-1">{{ $item->title }}</p>
                                <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $item->message }}</p>
                                @if($item->images->count() > 0)
                                <div class="flex items-center gap-1 text-[11px] text-blue-600 mt-1 font-medium">
                                    <i class="fa-solid fa-image"></i>
                                    <span>{{ $item->images->count() }} Lampiran Gambar</span>
                                </div>
                                @endif
                            </td>

                            <!-- Target Penerima -->
                            <td class="px-6 py-4">
                                @if($item->broadcast_type === 'all')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                    <i class="fa-solid fa-globe text-[10px]"></i> Semua Pemagang
                                </span>
                                @elseif($item->broadcast_type === 'division')
                                <div>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i class="fa-solid fa-sitemap text-[10px]"></i> Per Divisi
                                    </span>
                                    <p class="text-[11px] text-gray-500 mt-1 font-medium">{{ $item->divisions->pluck('name')->implode(', ') ?: '-' }}</p>
                                </div>
                                @elseif($item->broadcast_type === 'shift')
                                <div>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fa-solid fa-business-time text-[10px]"></i> Khusus Shift Tertentu
                                    </span>
                                </div>
                                @elseif($item->broadcast_type === 'office')
                                <div>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                        <i class="fa-solid fa-building text-[10px]"></i> Per Kantor/Brand
                                    </span>
                                    <p class="text-[11px] text-gray-500 mt-1 font-medium">{{ $item->offices->pluck('name')->implode(', ') ?: '-' }}</p>
                                </div>
                                @elseif($item->broadcast_type === 'specific')
                                <div>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-teal-50 text-teal-700 border border-teal-200">
                                        <i class="fa-solid fa-user-tag text-[10px]"></i> Spesifik User ({{ $item->users->count() }})
                                    </span>
                                </div>
                                @endif
                            </td>

                            <!-- Target Shift -->
                            <td class="px-6 py-4">
                                @if($item->shifts && $item->shifts->isNotEmpty())
                                    <div class="flex flex-col gap-1.5 min-w-[100px] max-w-[160px]">
                                        @foreach($item->shifts as $shift)
                                        <div class="px-2.5 py-1.5 rounded-lg bg-amber-50/90 text-amber-900 border border-amber-200/80 shadow-2xs">
                                            <div class="flex items-center gap-1.5 font-bold text-xs leading-tight">
                                                <i class="fa-solid fa-business-time text-amber-600 text-[10px] shrink-0"></i>
                                                <span class="truncate">{{ $shift->name }}</span>
                                            </div>
                                            @if($shift->start_time && $shift->end_time)
                                            <div class="text-[10px] text-amber-700/90 font-medium pl-4 mt-0.5 leading-tight">
                                                {{ substr($shift->start_time, 0, 5) }} - {{ substr($shift->end_time, 0, 5) }} WIB
                                            </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                        <i class="fa-solid fa-clock text-[10px]"></i> Semua Shift
                                    </span>
                                @endif
                            </td>

                            <!-- Jadwal / Waktu Kirim -->
                            <td class="px-6 py-4">
                                @if($item->scheduled_at)
                                <div class="space-y-0.5">
                                    <span class="text-xs text-gray-500">Jadwal:</span>
                                    <p class="font-semibold text-gray-800 text-xs">{{ $item->scheduled_at->format('d M Y, H:i') }}</p>
                                </div>
                                @else
                                <div class="space-y-0.5">
                                    <span class="text-xs text-emerald-600 font-semibold flex items-center gap-1">
                                        <i class="fa-solid fa-paper-plane text-[10px]"></i> Langsung
                                    </span>
                                    <p class="text-[11px] text-gray-400">{{ $item->created_at->format('d M Y, H:i') }}</p>
                                </div>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                @if($item->isDue())
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    <i class="fa-solid fa-check text-[10px]"></i> Terkirim
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    <i class="fa-solid fa-clock text-[10px]"></i> Menunggu Jadwal
                                </span>
                                @endif
                            </td>

                            <!-- Balasan Pemagang -->
                            <td class="px-6 py-4">
                                @if($item->requires_report)
                                <span class="inline-flex items-center gap-1.5 text-xs text-slate-700 font-semibold bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200/70">
                                    <i class="fa-regular fa-comment-dots text-blue-600"></i>
                                    <span>{{ $item->reports->count() }} Balasan</span>
                                </span>
                                @else
                                <span class="text-xs text-gray-400 italic">Tanpa Balasan</span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if($item->requires_report)
                                    <button type="button"
                                        data-id="{{ $item->id }}"
                                        onclick="openReportsModal(this.dataset.id)"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                        title="Lihat Balasan / Laporan">
                                        <i class="fa-solid fa-comments"></i>
                                    </button>
                                    @endif

                                    <form method="POST" action="{{ route('admin.scheduled-broadcasts.destroy', $item->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus broadcast ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                            title="Hapus Broadcast">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <i class="fa-solid fa-envelope-open-text text-4xl mb-3 text-gray-300 block"></i>
                                Belum ada broadcast terjadwal yang dibuat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($broadcastlist->hasPages())
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 border-t border-gray-100 bg-white">
                <div class="text-xs text-gray-500 font-medium">
                    Menampilkan <span class="font-bold text-gray-800">{{ $broadcastlist->firstItem() ?? 0 }}</span> - <span class="font-bold text-gray-800">{{ $broadcastlist->lastItem() ?? 0 }}</span> dari <span class="font-bold text-gray-800">{{ $broadcastlist->total() }}</span> broadcast
                </div>
                <div class="flex items-center space-x-1.5">
                    {{-- Prev Button --}}
                    @if ($broadcastlist->onFirstPage())
                        <button class="bg-gray-200 text-gray-400 px-3 py-1.5 rounded-lg text-xs font-semibold cursor-not-allowed shadow-2xs flex items-center gap-1" disabled>
                            <i class="fas fa-chevron-left text-[10px]"></i>
                            <span>Prev</span>
                        </button>
                    @else
                        <a href="{{ $broadcastlist->previousPageUrl() }}" class="bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold shadow-2xs transition flex items-center gap-1">
                            <i class="fas fa-chevron-left text-[10px]"></i>
                            <span>Prev</span>
                        </a>
                    @endif

                    {{-- Page Numbers --}}
                    <div class="flex space-x-1">
                        @foreach (range(1, $broadcastlist->lastPage()) as $page)
                            @if ($page == $broadcastlist->currentPage())
                                <span class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold shadow-2xs transition">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $broadcastlist->url($page) }}" class="px-3 py-1.5 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-xs font-bold shadow-2xs transition">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    </div>

                    {{-- Next Button --}}
                    @if ($broadcastlist->hasMorePages())
                        <a href="{{ $broadcastlist->nextPageUrl() }}" class="bg-gray-800 text-white hover:bg-gray-900 px-3 py-1.5 rounded-lg text-xs font-semibold shadow-2xs transition flex items-center gap-1">
                            <span>Next</span>
                            <i class="fas fa-chevron-right text-[10px]"></i>
                        </a>
                    @else
                        <button class="bg-gray-200 text-gray-400 px-3 py-1.5 rounded-lg text-xs font-semibold cursor-not-allowed shadow-2xs flex items-center gap-1" disabled>
                            <span>Next</span>
                            <i class="fas fa-chevron-right text-[10px]"></i>
                        </button>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Form Buat Broadcast Baru -->
<div id="formBroadcastModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden p-3 sm:p-4 overflow-y-auto" onclick="if(event.target === this) toggleFormModal()">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg my-auto max-h-[90vh] flex flex-col overflow-hidden transform transition-all">
        <!-- Modal Header -->
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex-shrink-0">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-paper-plane text-sm"></i>
                <h3 class="font-bold text-sm sm:text-base">Buat Pesan / Pertanyaan Broadcast</h3>
            </div>
            <button type="button" onclick="toggleFormModal()" class="text-white/80 hover:text-white text-xl font-bold leading-none p-1">&times;</button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <form method="POST" action="{{ route('admin.scheduled-broadcasts.store') }}" enctype="multipart/form-data" class="p-4 sm:p-5 space-y-3.5 overflow-y-auto flex-1 text-xs">
            @csrf

            <!-- Judul -->
            <div>
                <label for="sb_title" class="block font-bold text-gray-700 mb-1">
                    Judul / Subjek Pesan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" id="sb_title" required value="{{ old('title') }}"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
                    placeholder="Contoh: Evaluasi Progres Harian">
            </div>

            <!-- Teks Pesan / Pertanyaan -->
            <div>
                <label for="sb_message" class="block font-bold text-gray-700 mb-1">
                    Teks Pesan / Pertanyaan <span class="text-rose-500">*</span>
                </label>
                <textarea name="message" id="sb_message" rows="3" required
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
                    placeholder="Tuliskan isi pesan atau pertanyaan untuk pemagang...">{{ old('message') }}</textarea>
            </div>

            <!-- Penjadwalan Waktu & Target Shift Kerja (Bersandingan) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <!-- Kolom Kiri: Waktu Pengiriman -->
                <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200 space-y-1.5">
                    <label for="sb_scheduled_at" class="block font-bold text-gray-800 text-xs">
                        <i class="fa-regular fa-clock mr-1 text-blue-600"></i>
                        Waktu Pengiriman <span class="text-rose-500">*</span>
                    </label>
                    <p class="text-[11px] text-gray-500">Pilih tanggal dan jam pengiriman broadcast.</p>
                    <input type="datetime-local" name="scheduled_at" id="sb_scheduled_at" required value="{{ old('scheduled_at') }}"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 text-xs bg-white mt-1 font-medium text-gray-700">
                </div>

                <!-- Kolom Kanan: Target Shift Kerja (Wajib Diisi) -->
                <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200 space-y-1.5">
                    <span class="block font-bold text-gray-800 text-xs">
                        <i class="fa-solid fa-business-time mr-1 text-amber-600"></i>
                        Target Shift Kerja <span class="text-rose-500">*</span>
                    </span>
                    <p class="text-[11px] text-gray-500">Pilih shift kerja pemagang penerima.</p>
                    <div class="space-y-1 mt-1 max-h-32 overflow-y-auto pr-1">
                        @foreach($shifts as $shift)
                        <label class="flex items-center gap-2 text-[11px] text-gray-700 bg-white p-1.5 rounded-lg border border-gray-200 hover:border-amber-400 cursor-pointer transition">
                            <input type="checkbox" name="shifts[]" value="{{ $shift->id }}" class="rounded text-amber-600 h-3.5 w-3.5 focus:ring-amber-500" {{ (is_array(old('shifts')) && in_array($shift->id, old('shifts'))) || (!old('shifts') && $loop->first) ? 'checked' : '' }}>
                            <span class="font-medium truncate">{{ $shift->name }} <span class="text-gray-400 text-[10px]">({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})</span></span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Filter Target Tambahan (Opsional) -->
            <div class="space-y-2 pt-1 border-t border-gray-100">
                <label for="broadcast_type" class="block font-bold text-gray-700">
                    <i class="fa-solid fa-filter mr-1 text-indigo-600"></i>
                    Filter Target Tambahan
                </label>
                <select name="broadcast_type" id="broadcast_type" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 text-xs bg-white" onchange="handleTargetChange(this.value)">
                    <option value="all" {{ old('broadcast_type') == 'all' ? 'selected' : '' }}>Semua Pemagang pada Shift Tersebut</option>
                    <option value="division" {{ old('broadcast_type') == 'division' ? 'selected' : '' }}>Filter Berdasarkan Divisi</option>
                    <option value="office" {{ old('broadcast_type') == 'office' ? 'selected' : '' }}>Filter Berdasarkan Kantor / Brand</option>
                    <option value="specific" {{ old('broadcast_type') == 'specific' ? 'selected' : '' }}>Filter Pemagang Tertentu (Spesifik)</option>
                </select>

                <!-- Target: Divisi -->
                <div id="target_division_wrapper" class="hidden p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <span class="block font-semibold text-gray-700 mb-1.5 text-[11px]">Pilih Divisi:</span>
                    <div class="grid grid-cols-2 gap-1.5 max-h-36 overflow-y-auto">
                        @foreach($divisions as $div)
                        <label class="flex items-center gap-1.5 text-[11px] text-gray-700 bg-white p-1.5 rounded border border-gray-200 hover:border-blue-400 cursor-pointer">
                            <input type="checkbox" name="divisions[]" value="{{ $div->id }}" class="rounded text-blue-600 h-3.5 w-3.5">
                            <span class="truncate">{{ $div->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Target: Kantor/Brand -->
                <div id="target_office_wrapper" class="hidden p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <span class="block font-semibold text-gray-700 mb-1.5 text-[11px]">Pilih Kantor / Brand:</span>
                    <div class="grid grid-cols-2 gap-1.5 max-h-36 overflow-y-auto">
                        @foreach($offices as $office)
                        <label class="flex items-center gap-1.5 text-[11px] text-gray-700 bg-white p-1.5 rounded border border-gray-200 hover:border-blue-400 cursor-pointer">
                            <input type="checkbox" name="offices[]" value="{{ $office->id }}" class="rounded text-blue-600 h-3.5 w-3.5">
                            <span class="truncate">{{ $office->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Target: User Spesifik -->
                <div id="target_specific_wrapper" class="hidden p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <span class="block font-semibold text-gray-700 mb-1.5 text-[11px]">Pilih Pemagang:</span>
                    <div class="max-h-36 overflow-y-auto space-y-1 p-1 bg-white rounded border border-gray-200">
                        @foreach($users as $u)
                        <label class="flex items-center gap-2 text-[11px] text-gray-700 p-1 rounded hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="users[]" value="{{ $u->id }}" class="rounded text-blue-600 h-3.5 w-3.5">
                            <span class="truncate">
                                {{ $u->profile->full_name ?? $u->name }} ({{ $u->username }})
                                @if($u->intern?->brand)
                                    <span class="text-[10px] text-purple-700 bg-purple-50 border border-purple-200 px-1 py-0.5 rounded ml-1">{{ $u->intern->brand->name }}</span>
                                @endif
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Info Status Wajib Diisi -->
            <div class="bg-blue-50/70 p-3 rounded-xl border border-blue-200 flex items-center gap-2.5 text-blue-900">
                <i class="fa-solid fa-circle-info text-blue-600 text-sm flex-shrink-0"></i>
                <p class="text-[11px] leading-relaxed">
                    <span class="font-bold">Wajib Diisi:</span> Broadcast ini akan otomatis berstatus wajib dijawab / dikonfirmasi oleh pemagang sebelum dapat ditutup di dashboard.
                </p>
            </div>

            <!-- Lampiran Gambar (Single Image Upload & Clean Preview) -->
            <div>
                <span class="block font-bold text-gray-700 mb-1 text-xs">
                    <i class="fa-regular fa-image text-blue-600 mr-1"></i>
                    Lampiran Gambar <span class="text-gray-400 font-normal text-[11px]">(Opsional, Maks. 1 Gambar)</span>
                </span>

                <!-- Dropzone Area (Tampil jika belum ada gambar) -->
                <div id="imageDropzone"
                    class="border-2 border-dashed border-gray-300 hover:border-blue-500 rounded-xl p-4 text-center cursor-pointer bg-gray-50/70 hover:bg-blue-50/40 transition-all duration-200 group">
                    <div class="flex flex-col items-center justify-center gap-1.5 pointer-events-none">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-cloud-arrow-up text-base"></i>
                        </div>
                        <p class="text-xs font-semibold text-gray-700 group-hover:text-blue-600">
                            Klik untuk memilih gambar atau seret file ke sini
                        </p>
                        <p class="text-[10px] text-gray-400">
                            Format: JPG, PNG, GIF, WebP (Maksimal 2MB)
                        </p>
                    </div>
                </div>
                <input type="file" id="scheduledBroadcastImagesInput" name="images[]" accept="image/*" class="hidden">

                <!-- Single Image Selected Card (Tampil saat gambar berhasil dipilih) -->
                <div id="singleImagePreviewCard" class="hidden bg-white p-3 rounded-xl border border-gray-200 shadow-xs flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Image Thumbnail -->
                        <div class="w-16 h-16 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0">
                            <img id="previewImageElement" src="" alt="Pratinjau Gambar" class="w-full h-full object-cover">
                        </div>
                        <!-- Info File -->
                        <div class="min-w-0 space-y-0.5">
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">
                                    <i class="fa-solid fa-circle-check text-[9px]"></i> Gambar Terpilih
                                </span>
                            </div>
                            <p id="previewImageName" class="text-xs font-bold text-gray-800 truncate" title=""></p>
                            <p id="previewImageSize" class="text-[11px] text-gray-400 font-medium"></p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <button type="button" onclick="document.getElementById('scheduledBroadcastImagesInput').click()"
                            class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-medium transition flex items-center gap-1"
                            title="Ganti gambar">
                            <i class="fa-solid fa-arrows-rotate text-[10px]"></i>
                            <span>Ganti</span>
                        </button>
                        <button type="button" onclick="removeSingleImage()"
                            class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs font-semibold transition"
                            title="Hapus gambar">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="toggleFormModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition">
                    Simpan & Kirim
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Lihat Laporan / Balasan Pemagang -->
<div id="reportsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden p-3 sm:p-4" onclick="if(event.target === this) closeReportsModal()">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[85vh] flex flex-col overflow-hidden my-auto">
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between bg-gray-50 flex-shrink-0">
            <div>
                <h3 id="modalReportTitle" class="font-bold text-gray-800 text-sm sm:text-base">Balasan Pemagang</h3>
                <p id="modalReportQuestion" class="text-xs text-gray-500 mt-0.5 italic"></p>
                <div id="modalReportShiftBadge" class="mt-1 hidden">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-amber-50 text-amber-900 border border-amber-200">
                        <i class="fa-solid fa-business-time text-amber-600 text-[10px]"></i>
                        <span id="modalReportShiftText"></span>
                    </span>
                </div>
            </div>
            <button type="button" onclick="closeReportsModal()" class="text-gray-400 hover:text-gray-700 text-xl font-bold leading-none p-1">&times;</button>
        </div>

        <div id="reportsListContainer" class="p-4 sm:p-5 overflow-y-auto space-y-2.5 flex-1 text-xs">
            <div class="text-center py-8 text-gray-400">
                <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 block"></i>
                Memuat data balasan pemagang...
            </div>
        </div>

        <div class="p-3 border-t border-gray-100 bg-gray-50 flex justify-end flex-shrink-0">
            <button type="button" onclick="closeReportsModal()" class="px-4 py-1.5 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-xs font-semibold">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    let selectedImageFile = null;

    function toggleFormModal() {
        const modal = document.getElementById('formBroadcastModal');
        if (modal) {
            modal.classList.toggle('hidden');
        }
    }

    function initImageUpload() {
        const dropzone = document.getElementById('imageDropzone');
        const fileInput = document.getElementById('scheduledBroadcastImagesInput');

        if (!dropzone || !fileInput) return;

        dropzone.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files.length > 0) {
                handleSingleFile(e.target.files[0]);
            }
            fileInput.value = ''; // Reset input agar event change terpicu walau file sama
        });

        // Drag & drop handlers
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('border-blue-500', 'bg-blue-50/70');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('border-blue-500', 'bg-blue-50/70');
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length > 0) {
                handleSingleFile(dt.files[0]);
            }
        });
    }

    function handleSingleFile(file) {
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            alert('File yang dipilih bukan berkas gambar. Harap pilih gambar (JPG, PNG, GIF, WebP).');
            return;
        }

        if (file.size > 2 * 1024 * 1024) { // 2MB
            alert(`Ukuran gambar (${formatFileSize(file.size)}) melebihi batas maksimal 2MB.`);
            return;
        }

        selectedImageFile = file;
        renderSingleImagePreview();
    }

    function renderSingleImagePreview() {
        const dropzone = document.getElementById('imageDropzone');
        const previewCard = document.getElementById('singleImagePreviewCard');
        const imgEl = document.getElementById('previewImageElement');
        const nameEl = document.getElementById('previewImageName');
        const sizeEl = document.getElementById('previewImageSize');

        if (!selectedImageFile) {
            if (dropzone) dropzone.classList.remove('hidden');
            if (previewCard) previewCard.classList.add('hidden');
            if (imgEl) imgEl.src = '';
            return;
        }

        if (dropzone) dropzone.classList.add('hidden');
        if (previewCard) previewCard.classList.remove('hidden');

        if (imgEl) {
            imgEl.src = URL.createObjectURL(selectedImageFile);
        }
        if (nameEl) {
            nameEl.textContent = selectedImageFile.name;
            nameEl.title = selectedImageFile.name;
        }
        if (sizeEl) {
            sizeEl.textContent = formatFileSize(selectedImageFile.size);
        }
    }

    function removeSingleImage() {
        selectedImageFile = null;
        renderSingleImagePreview();
        const fileInput = document.getElementById('scheduledBroadcastImagesInput');
        if (fileInput) fileInput.value = '';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    function handleTargetChange(value) {
        const divWrap = document.getElementById('target_division_wrapper');
        const offWrap = document.getElementById('target_office_wrapper');
        const specWrap = document.getElementById('target_specific_wrapper');

        if (divWrap) divWrap.classList.add('hidden');
        if (offWrap) offWrap.classList.add('hidden');
        if (specWrap) specWrap.classList.add('hidden');

        if (value === 'division' && divWrap) {
            divWrap.classList.remove('hidden');
        } else if (value === 'office' && offWrap) {
            offWrap.classList.remove('hidden');
        } else if (value === 'specific' && specWrap) {
            specWrap.classList.remove('hidden');
        }
    }

    function openReportsModal(broadcastId) {
        const modal = document.getElementById('reportsModal');
        const container = document.getElementById('reportsListContainer');
        const titleEl = document.getElementById('modalReportTitle');
        const questionEl = document.getElementById('modalReportQuestion');
        const shiftBadgeEl = document.getElementById('modalReportShiftBadge');
        const shiftTextEl = document.getElementById('modalReportShiftText');

        if (shiftBadgeEl) shiftBadgeEl.classList.add('hidden');
        modal.classList.remove('hidden');
        container.innerHTML = `
            <div class="text-center py-8 text-gray-400">
                <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 block"></i>
                Memuat data balasan pemagang...
            </div>`;

        fetch(`{{ url('/admin/scheduled-broadcasts') }}/${broadcastId}/reports`)
            .then(res => res.json())
            .then(data => {
                titleEl.textContent = data.title || 'Balasan Pemagang';
                questionEl.textContent = data.question ? `Pertanyaan: "${data.question}"` : '';

                if (data.shifts && shiftBadgeEl && shiftTextEl) {
                    shiftTextEl.textContent = 'Target Shift: ' + data.shifts;
                    shiftBadgeEl.classList.remove('hidden');
                }

                if (!data.reports || data.reports.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-10 text-gray-400">
                            <i class="fa-regular fa-comment-slash text-3xl mb-2 block text-gray-300"></i>
                            Belum ada pemagang yang mengirim balasan/laporan.
                        </div>`;
                    return;
                }

                let html = '';
                data.reports.forEach(r => {
                    const hasChats = r.chats && r.chats.length > 0;
                    const unreadBadge = (r.unread_intern_chats && r.unread_intern_chats > 0) 
                        ? `<span id="unread-badge-${r.id}" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500 text-white animate-pulse">${r.unread_intern_chats} Pesan Baru</span>` 
                        : '';

                    let chatBubblesHtml = '';
                    if (hasChats) {
                        r.chats.forEach(c => {
                            if (c.is_from_admin) {
                                chatBubblesHtml += `
                                    <div class="flex items-start gap-2 max-w-[85%] ml-auto justify-end">
                                        <div class="bg-indigo-600 text-white p-2.5 rounded-2xl rounded-tr-xs text-xs shadow-2xs leading-relaxed space-y-0.5">
                                            <span class="font-bold text-[10px] text-indigo-200 block">${escapeHtml(c.sender_name)} (Admin)</span>
                                            <p class="whitespace-pre-line">${escapeHtml(c.message)}</p>
                                            <span class="text-[9px] text-indigo-300 block text-right font-mono">${escapeHtml(c.time)}</span>
                                        </div>
                                    </div>`;
                            } else {
                                chatBubblesHtml += `
                                    <div class="flex items-start gap-2 max-w-[85%]">
                                        <div class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <div class="bg-white text-slate-800 p-2.5 rounded-2xl rounded-tl-xs text-xs border border-slate-200 shadow-2xs leading-relaxed space-y-0.5">
                                            <span class="font-bold text-[10px] text-indigo-700 block">${escapeHtml(c.sender_name)}</span>
                                            <p class="whitespace-pre-line">${escapeHtml(c.message)}</p>
                                            <span class="text-[9px] text-slate-400 block text-right font-mono">${escapeHtml(c.time)}</span>
                                        </div>
                                    </div>`;
                            }
                        });
                    }

                    html += `
                        <div class="p-3 bg-slate-50 hover:bg-slate-100/70 transition rounded-xl border border-slate-200/80 space-y-2" id="report-card-${r.id}">
                            <!-- Baris Atas: Profil Pemagang, Waktu, Badge Unread & Tombol Icon Chat -->
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-[10px] font-bold shrink-0">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <span class="font-bold text-gray-800 text-xs truncate">${escapeHtml(r.name)}</span>
                                    ${unreadBadge}
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="text-gray-400 text-[10px] font-mono">${escapeHtml(r.submitted_at)}</span>
                                    <!-- Tombol Chat Personal (Hanya Icon SVG) -->
                                    <button type="button" onclick="toggleFollowUpThread(${r.id})" 
                                        class="p-1.5 px-2 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 transition cursor-pointer flex items-center justify-center gap-1"
                                        title="${hasChats ? 'Lihat Diskusi & Balas (' + r.chats.length + ')' : 'Kirim Chat / Tanya Pemagang'}">
                                        <i class="fa-solid fa-comments text-xs"></i>
                                        ${hasChats ? `<span class="text-[10px] font-bold text-indigo-700">${r.chats.length}</span>` : ''}
                                    </button>
                                </div>
                            </div>

                            <!-- Jawaban/Laporan Pemagang -->
                            <div class="bg-white px-3 py-2 rounded-lg border border-slate-200/70 text-xs text-slate-700 whitespace-pre-wrap leading-relaxed">${escapeHtml(r.report ? r.report.trim() : '')}</div>

                            <!-- Follow-up Chat Container (Accordion - Tersembunyi secara Default) -->
                            <div id="follow-up-container-${r.id}" class="hidden space-y-2 pt-2 border-t border-slate-200">
                                <div class="space-y-1.5 max-h-40 overflow-y-auto p-2 bg-slate-100/70 rounded-lg border border-slate-200" id="chat-thread-${r.id}">
                                    ${chatBubblesHtml || '<p class="text-[11px] text-gray-400 italic text-center py-1">Belum ada percakapan lanjutan. Tulis pertanyaan di bawah untuk menanyakan pemagang.</p>'}
                                </div>

                                <!-- Form Kirim Chat Personal ke Pemagang -->
                                <div class="flex items-center gap-1.5">
                                    <input type="text" id="follow-up-input-${r.id}" 
                                        placeholder="Ketik pertanyaan untuk ${escapeHtml(r.name)}..."
                                        class="flex-1 px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white"
                                        onkeydown="if(event.key === 'Enter') sendAdminFollowUp(${r.id});">
                                    <button type="button" onclick="sendAdminFollowUp(${r.id})"
                                        id="btn-send-followup-${r.id}"
                                        class="p-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-2xs flex items-center justify-center shrink-0 cursor-pointer"
                                        title="Kirim Pesan">
                                        <i class="fa-solid fa-paper-plane text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                        </div>`;
                });
                container.innerHTML = html;
            })
            .catch(err => {
                container.innerHTML = `
                    <div class="text-center py-8 text-rose-500 text-xs">
                        <i class="fa-solid fa-triangle-exclamation text-2xl mb-1 block"></i>
                        Gagal memuat balasan pemagang. Silakan coba lagi.
                    </div>`;
            });
    }

    function toggleFollowUpThread(reportId) {
        const el = document.getElementById(`follow-up-container-${reportId}`);
        if (el) {
            const wasHidden = el.classList.contains('hidden');
            el.classList.toggle('hidden');
            if (wasHidden) {
                // Tandai chat dibaca oleh admin ke server dan hapus badge notifikasi
                fetch(`{{ url('/admin/scheduled-broadcasts/reports') }}/${reportId}/chats`)
                    .then(res => res.json())
                    .then(() => {
                        const badge = document.getElementById(`unread-badge-${reportId}`);
                        if (badge) badge.remove();
                    })
                    .catch(() => {});
            }
        }
    }

    function sendAdminFollowUp(reportId) {
        const input = document.getElementById(`follow-up-input-${reportId}`);
        const btn = document.getElementById(`btn-send-followup-${reportId}`);
        const thread = document.getElementById(`chat-thread-${reportId}`);
        if (!input || !input.value.trim()) return;

        const message = input.value.trim();
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[10px]"></i>';
        }

        fetch(`{{ url('/admin/scheduled-broadcasts/reports') }}/${reportId}/follow-up`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: message })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.chat) {
                input.value = '';
                const badge = document.getElementById(`unread-badge-${reportId}`);
                if (badge) badge.remove();
                const bubble = `
                    <div class="flex items-start gap-2 max-w-[85%] ml-auto justify-end">
                        <div class="bg-indigo-600 text-white p-2.5 rounded-2xl rounded-tr-xs text-xs shadow-2xs leading-relaxed space-y-0.5">
                            <span class="font-bold text-[10px] text-indigo-200 block">${escapeHtml(data.chat.sender_name)} (Admin)</span>
                            <p class="whitespace-pre-line">${escapeHtml(data.chat.message)}</p>
                            <span class="text-[9px] text-indigo-300 block text-right font-mono">${escapeHtml(data.chat.time)}</span>
                        </div>
                    </div>`;
                if (thread.innerHTML.includes('Belum ada percakapan lanjutan')) {
                    thread.innerHTML = bubble;
                } else {
                    thread.innerHTML += bubble;
                }
                thread.scrollTop = thread.scrollHeight;
            } else {
                alert(data.message || 'Gagal mengirim pesan.');
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan saat mengirim pesan follow-up.');
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane text-[10px]"></i> <span>Kirim</span>';
            }
        });
    }

    function closeReportsModal() {
        document.getElementById('reportsModal').classList.add('hidden');
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    document.addEventListener('DOMContentLoaded', function() {
        initImageUpload();

        const form = document.querySelector('#formBroadcastModal form');
        if (form) {
            form.addEventListener('submit', function() {
                const dataTransfer = new DataTransfer();
                if (selectedImageFile) {
                    dataTransfer.items.add(selectedImageFile);
                }
                const fileInput = document.getElementById('scheduledBroadcastImagesInput');
                if (fileInput) {
                    fileInput.files = dataTransfer.files;
                }
            });
        }

        const typeSelect = document.getElementById('broadcast_type');
        if (typeSelect) {
            handleTargetChange(typeSelect.value);
        }

        // Tutup modal jika tombol Escape ditekan
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const formModal = document.getElementById('formBroadcastModal');
                const reportsModal = document.getElementById('reportsModal');
                if (formModal && !formModal.classList.contains('hidden')) formModal.classList.add('hidden');
                if (reportsModal && !reportsModal.classList.contains('hidden')) reportsModal.classList.add('hidden');
            }
        });
    });
</script>
@endsection