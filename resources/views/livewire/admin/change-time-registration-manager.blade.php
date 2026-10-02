<div @if(isset($pollInterval) && $pollInterval > 0) wire:poll.{{ $pollInterval }}s @endif class="space-y-4 sm:space-y-6 min-w-0 max-w-full">

    <!-- Auto-refresh Livewire Indicator -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs bg-white border border-slate-200/80 px-3 py-1.5 rounded-xl shadow-2xs">
            <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></div>
            <span class="text-slate-600 font-medium">Auto-refresh aktif ({{ $pollInterval }} detik)</span>
        </div>
        <div class="text-[11px] text-slate-400">
            Terakhir diperbarui: <span class="font-mono">{{ now()->format('H:i:s') }}</span> WIB
        </div>
    </div>

    <!-- Quick Stats Cards Pra-Pendaftaran (3 Status: Pending, Approved, Rejected) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-4">
        <!-- Menunggu / Pending -->
        <div wire:click="filterStatus('pending')"
            class="p-3 sm:p-4 rounded-2xl border transition flex flex-col justify-between cursor-pointer {{ $regStatus === 'pending' ? 'border-amber-500 bg-amber-50/70 ring-2 ring-amber-500/20 shadow-xs' : 'border-slate-200/80 bg-white hover:border-amber-300 hover:shadow-2xs' }}">
            <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                <span class="text-[11px] sm:text-xs font-semibold text-slate-500 line-clamp-1">Pending</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-hourglass-half text-xs"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-bold text-amber-700 font-mono">{{ $regPendingCount }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">Dapat mulai sesi ganti jam</div>
        </div>

        <!-- Disetujui -->
        <div wire:click="filterStatus('approved')"
            class="p-3 sm:p-4 rounded-2xl border transition flex flex-col justify-between cursor-pointer {{ $regStatus === 'approved' ? 'border-emerald-500 bg-emerald-50/70 ring-2 ring-emerald-500/20 shadow-xs' : 'border-slate-200/80 bg-white hover:border-emerald-300 hover:shadow-2xs' }}">
            <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                <span class="text-[11px] sm:text-xs font-semibold text-slate-500 line-clamp-1">Disetujui</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-check text-xs"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-bold text-emerald-700 font-mono">{{ $regApprovedCount }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">Disetujui admin</div>
        </div>

        <!-- Ditolak -->
        <div wire:click="filterStatus('rejected')"
            class="p-3 sm:p-4 rounded-2xl border transition flex flex-col justify-between cursor-pointer {{ $regStatus === 'rejected' ? 'border-rose-500 bg-rose-50/70 ring-2 ring-rose-500/20 shadow-xs' : 'border-slate-200/80 bg-white hover:border-rose-300 hover:shadow-2xs' }}">
            <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                <span class="text-[11px] sm:text-xs font-semibold text-slate-500 line-clamp-1">Ditolak</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-xmark text-xs"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-bold text-rose-700 font-mono">{{ $regRejectedCount }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">Pengajuan ditolak</div>
        </div>
    </div>

    <!-- Filters & Search Form Pra-Pendaftaran (Livewire Real-time) -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-2xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs">
            <!-- Search Intern Name -->
            <div>
                <label for="search_reg_lw" class="block font-semibold text-slate-700 mb-1 text-[11px]">Cari Pemagang</label>
                <input type="text" id="search_reg_lw" wire:model.live.debounce.300ms="search" placeholder="Nama pemagang..."
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none text-xs bg-slate-50/50 focus:bg-white transition">
            </div>

            <!-- Division Filter -->
            <div>
                <label for="division_id_reg_lw" class="block font-semibold text-slate-700 mb-1 text-[11px]">Divisi</label>
                <select id="division_id_reg_lw" wire:model.live="divisionId"
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none bg-slate-50/50 focus:bg-white text-xs transition">
                    <option value="">Semua Divisi</option>
                    @foreach($divisions as $div)
                    <option value="{{ $div->id }}">{{ $div->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="reg_status_lw" class="block font-semibold text-slate-700 mb-1 text-[11px]">Status Pendaftaran</label>
                <select id="reg_status_lw" wire:model.live="regStatus"
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none bg-slate-50/50 focus:bg-white text-xs transition">
                    <option value="all">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Disetujui</option>
                    <option value="completed">Selesai</option>
                    <option value="rejected">Ditolak</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label for="date_from_reg_lw" class="block font-semibold text-slate-700 mb-1 text-[11px]">Dari Tanggal</label>
                <input type="date" id="date_from_reg_lw" wire:model.live="dateFrom"
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none bg-slate-50/50 focus:bg-white text-xs transition">
            </div>

            <!-- Date To -->
            <div>
                <label for="date_to_reg_lw" class="block font-semibold text-slate-700 mb-1 text-[11px]">Sampai Tanggal</label>
                <input type="date" id="date_to_reg_lw" wire:model.live="dateTo"
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none bg-slate-50/50 focus:bg-white text-xs transition">
            </div>

            <!-- Reset Button -->
            <div class="flex items-end gap-2 pt-1 sm:pt-0">
                <button type="button" wire:click="resetFilters" class="w-full py-2 px-3 border border-slate-300 text-slate-600 hover:bg-slate-100 rounded-xl transition text-center flex items-center justify-center gap-1.5 cursor-pointer" title="Reset Filter ke Hari Ini">
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                    <span>Reset</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Table Pra-Pendaftaran Ganti Jam -->
    <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 overflow-hidden">
        <div class="px-4 py-3 sm:px-5 sm:py-3.5 bg-slate-50/70 border-b border-slate-200/80 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-calendar-check text-orange-600"></i>
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Daftar Pra-Pendaftaran Ganti Jam</span>
            </div>
            <div class="flex items-center gap-2">
                <span wire:loading.inline-flex class="text-orange-600 text-xs items-center gap-1 font-semibold">
                    <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                    <span>Memperbarui...</span>
                </span>
                <span class="text-[11px] font-medium text-slate-500">Total: <strong>{{ $registrations->total() }}</strong> data</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-3.5 text-center w-10">No</th>
                        <th class="py-3 px-3.5 text-left w-48">Pemagang</th>
                        <th class="py-3 px-3.5 text-left w-52">Rencana Ganti Jam</th>
                        <th class="py-3 px-3.5 text-left min-w-[200px]">Catatan / Alasan</th>
                        <th class="py-3 px-3.5 text-center w-28">Status</th>
                        <th class="py-3 px-3.5 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($registrations as $index => $reg)
                    @php
                    $internProfile = $reg->intern?->user?->profile;
                    $internName = $internProfile?->full_name ?? $reg->intern?->user?->username ?? 'Unknown';
                    $divisionName = $reg->intern?->division?->name ?? '-';
                    $schoolName = $reg->intern?->school?->name ?? '-';
                    $unreadCount = $reg->unreadInternNotesCount();
                    $notesCount = $reg->notes->count();
                    $lastNote = $reg->notes->last();
                    $notesJson = $reg->notes->map(fn($n) => [
                        'id' => $n->id,
                        'message' => $n->message,
                        'is_from_admin' => (bool)$n->is_from_admin,
                        'sender_name' => $n->user?->profile?->full_name ?? $n->user?->username ?? ($n->is_from_admin ? 'Admin' : 'Pemagang'),
                        'time' => $n->created_at->locale('id')->isoFormat('D MMM Y, HH:mm'),
                    ])->values();
                    @endphp
                    <tr wire:key="reg-row-{{ $reg->id }}" class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-3.5 text-center align-top font-bold text-slate-400">
                            {{ ($registrations->currentPage() - 1) * $registrations->perPage() + $loop->iteration }}
                        </td>

                        <!-- Pemagang -->
                        <td class="py-3.5 px-3.5 align-top">
                            <div class="font-bold text-slate-900 text-sm leading-tight">{{ $internName }}</div>
                            <div class="text-slate-500 text-[11px] mt-0.5 truncate max-w-[180px]" title="{{ $schoolName }}">{{ $schoolName }}</div>
                            <div class="mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-[10px] font-semibold border border-slate-200">
                                    {{ $divisionName }}
                                </span>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-1">
                                <i class="fa-regular fa-clock text-[9px]"></i>
                                <span>Diajukan: {{ $reg->created_at->locale('id')->isoFormat('D MMM Y, HH:mm') }}</span>
                            </div>
                        </td>

                        <!-- Rencana Ganti Jam (Tanggal, Shift & Kantor) -->
                        <td class="py-3.5 px-3.5 align-top">
                            <div class="p-2.5 bg-slate-50/90 rounded-xl border border-slate-200/80 space-y-2 text-xs shadow-2xs">
                                <!-- Tanggal Rencana -->
                                <div class="flex items-center gap-1.5 font-bold text-slate-800">
                                    <div class="w-5 h-5 rounded-md bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                        <i class="fa-regular fa-calendar-days text-[10px]"></i>
                                    </div>
                                    @if($reg->requested_date)
                                        <span>{{ \Carbon\Carbon::parse($reg->requested_date)->locale('id')->isoFormat('D MMM Y') }}</span>
                                    @else
                                        <span class="text-slate-400 font-normal italic">Belum ditentukan</span>
                                    @endif
                                </div>

                                <!-- Shift & Jam Kerja -->
                                <div class="flex items-start gap-1.5">
                                    <div class="w-5 h-5 rounded-md bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 mt-0.5">
                                        <i class="fa-solid fa-business-time text-[10px]"></i>
                                    </div>
                                    <div class="min-w-0">
                                        @if($reg->shift)
                                            <div class="font-bold text-blue-900 leading-tight">{{ $reg->shift->name }}</div>
                                            <div class="text-[10px] text-slate-500 font-mono">
                                                {{ \Carbon\Carbon::parse($reg->shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($reg->shift->end_time)->format('H:i') }} WIB
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic text-[11px]">Bebas Shift</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Kantor -->
                                <div class="flex items-center gap-1.5 pt-1.5 border-t border-slate-200/60 text-slate-600">
                                    <div class="w-5 h-5 rounded-md bg-slate-200 text-slate-600 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-building text-[10px]"></i>
                                    </div>
                                    <span class="font-medium text-[11px] truncate">{{ $reg->office->name ?? ($reg->intern?->office?->name ?? 'Kantor Utama') }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Catatan / Alasan Pemagang -->
                        <td class="py-3.5 px-3.5 align-top">
                            <div class="p-2.5 bg-slate-50/90 rounded-xl border border-slate-200/80">
                                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 flex items-center gap-1">
                                    <i class="fa-regular fa-note-sticky text-amber-600"></i>
                                    <span>Alasan Pemagang</span>
                                </div>
                                <p class="text-slate-800 text-xs italic leading-relaxed line-clamp-3" title="{{ $reg->reason }}">
                                    "{{ $reg->reason }}"
                                </p>
                            </div>
                            @if($reg->admin_notes)
                            <div class="mt-1.5 p-2 bg-blue-50/70 rounded-lg border border-blue-200 text-[10px] text-blue-900">
                                <strong class="text-blue-800">Catatan Persetujuan:</strong> {{ $reg->admin_notes }}
                            </div>
                            @endif
                        </td>

                        <!-- Status -->
                        <td class="py-3.5 px-3.5 text-center align-top whitespace-nowrap">
                            <div class="flex flex-col items-center justify-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $reg->status_badge }}">
                                    {{ $reg->status_label }}
                                </span>
                                @if($reg->approved_at)
                                <div class="text-[10px] text-slate-400 mt-1 text-center">
                                    Oleh: {{ $reg->approver?->profile?->full_name ?? $reg->approver?->username ?? 'Admin' }}
                                </div>
                                @endif
                            </div>
                        </td>

                        <!-- Aksi -->
                        <td class="py-3.5 px-3.5 text-center align-top whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-1.5 bg-slate-50 p-1.5 rounded-2xl border border-slate-200 shadow-2xs">
                                @if($reg->status === 'pending')
                                <!-- Tombol Buka Diskusi (Hanya saat status Pending) -->
                                <button type="button"
                                    id="btn-chat-reg-{{ $reg->id }}"
                                    data-id="{{ $reg->id }}"
                                    data-name="{{ $internName }}"
                                    data-date="{{ $reg->requested_date ? \Carbon\Carbon::parse($reg->requested_date)->locale('id')->isoFormat('D MMMM Y') : 'Belum ditentukan' }}"
                                    data-shift="{{ $reg->shift ? $reg->shift->name . ' (' . \Carbon\Carbon::parse($reg->shift->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($reg->shift->end_time)->format('H:i') . ' WIB)' : 'Bebas Shift' }}"
                                    data-office="{{ $reg->office->name ?? ($reg->intern?->office?->name ?? 'Kantor Utama') }}"
                                    data-reason="{{ e($reg->reason) }}"
                                    data-created="{{ $reg->created_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}"
                                    data-has-unread="{{ $unreadCount > 0 ? 'true' : 'false' }}"
                                    data-notes='@json($notesJson)'
                                    onclick="openChatRegModal(this)"
                                    style="background-color: #ea580c !important; color: #ffffff !important;"
                                    class="p-2 rounded-xl text-white hover:opacity-90 transition shadow-2xs cursor-pointer relative flex items-center justify-center"
                                    title="Buka Diskusi / Kirim Catatan">
                                    <i class="fa-solid fa-comments text-xs"></i>
                                    <span id="chat-dot-reg-{{ $reg->id }}" class="chat-dot-reg absolute -top-1 -right-1 w-2.5 h-2.5 bg-rose-500 rounded-full border-2 border-white animate-pulse {{ $unreadCount > 0 ? '' : 'hidden' }}"></span>
                                </button>
                                <!-- Setujui Pendaftaran (ACC) -->
                                <button type="button"
                                    data-id="{{ $reg->id }}"
                                    data-name="{{ $internName }}"
                                    data-date="{{ $reg->requested_date ? \Carbon\Carbon::parse($reg->requested_date)->format('Y-m-d') : '' }}"
                                    data-shift-id="{{ $reg->shift_id ?? '' }}"
                                    data-office-id="{{ $reg->office_id ?? ($reg->intern?->office_id ?? '') }}"
                                    onclick="openApproveRegModal(this)"
                                    class="p-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-2xs cursor-pointer flex items-center justify-center"
                                    title="Setujui Pendaftaran (ACC)">
                                    <i class="fa-solid fa-check text-xs"></i>
                                </button>
                                @endif

                                @if(in_array($reg->status, ['pending', 'approved']))
                                <!-- Tolak / Batalkan Pendaftaran -->
                                <button type="button"
                                    data-id="{{ $reg->id }}"
                                    data-name="{{ $internName }}"
                                    data-date="{{ $reg->requested_date ? \Carbon\Carbon::parse($reg->requested_date)->locale('id')->isoFormat('D MMM Y') : '-' }}"
                                    onclick="openRejectRegModal(this)"
                                    class="p-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white transition shadow-2xs cursor-pointer flex items-center justify-center"
                                    title="{{ $reg->status === 'approved' ? 'Batalkan Persetujuan / Tolak' : 'Tolak Pendaftaran' }}">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                                @endif

                                <!-- Hapus Data -->
                                <button type="button"
                                    data-id="{{ $reg->id }}"
                                    data-name="{{ $internName }}"
                                    data-date="{{ $reg->created_at->locale('id')->isoFormat('dddd, D MMMM YYYY') }}"
                                    data-shift="{{ $reg->shift?->name ?? 'Bebas Sesi' }}"
                                    data-office="{{ $reg->office?->name ?? 'Semua Kantor' }}"
                                    data-status="{{ $reg->status }}"
                                    data-status-label="{{ $reg->status_label }}"
                                    onclick="openDeleteRegModal(this)"
                                    class="p-2 rounded-xl bg-white hover:bg-rose-50 text-slate-400 hover:text-rose-600 border border-slate-200 transition shadow-2xs cursor-pointer flex items-center justify-center"
                                    title="Hapus Data">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <i class="fa-regular fa-folder-open text-3xl mb-2 text-slate-300 block"></i>
                            <span class="font-medium text-sm">Tidak ada data pra-pendaftaran ganti jam.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($registrations->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $registrations->links() }}
        </div>
        @endif
    </div>

</div>
