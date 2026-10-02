<div @if(isset($pollInterval) && $pollInterval > 0) wire:poll.{{ $pollInterval }}s @endif>
    @php
    use App\Utils\AttendanceStatus;
    $isPraying = false;
    @endphp

    {{-- ============================================================ --}}
    {{-- [LIVEWIRE POPUP] MODAL TANGGAPAN ADMIN ATAS RAISE HAND --}}
    {{-- ============================================================ --}}
    @if($showAdminResponseModal && !empty($adminResponseData))
    <div class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/65 backdrop-blur-sm p-4" wire:key="admin-response-modal-{{ $adminResponseData['id'] }}">
        <div class="bg-white rounded-3xl p-6 sm:p-7 max-w-md w-full mx-auto shadow-2xl border border-slate-100 flex flex-col gap-4 relative animate-in fade-in zoom-in duration-200">

            {{-- Header Icon & Badge --}}
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    @if($adminResponseData['type'] === 'new_task')
                    <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl shadow-xs shrink-0">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    @elseif($adminResponseData['type'] === 'presentation')
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shadow-xs shrink-0">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    @else
                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl shadow-xs shrink-0">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    @endif
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200 inline-block mb-0.5">
                            {{ $adminResponseData['status_label'] }}
                        </span>
                        <h3 class="text-base sm:text-lg font-bold text-slate-800 leading-snug">
                            Tanggapan dari Admin
                        </h3>
                    </div>
                </div>

                <button type="button" wire:click="dismissAdminResponseModal({{ $adminResponseData['id'] }}, {{ $adminResponseData['timestamp'] }})" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600 flex items-center justify-center transition cursor-pointer shrink-0" title="Tutup">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Pesan / Balasan Admin --}}
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                <div class="flex items-center justify-between text-xs text-slate-500 font-medium">
                    <span class="flex items-center gap-1.5 text-slate-600 font-bold">
                        <i class="fa-solid fa-user-tie text-indigo-600"></i>
                        {{ $adminResponseData['resolver'] }}
                    </span>
                    <span class="text-[11px] text-slate-400">{{ $adminResponseData['type_label'] }}</span>
                </div>
                <div class="text-xs sm:text-sm text-slate-700 leading-relaxed font-normal whitespace-pre-line border-t border-slate-200/60 pt-2">
                    {{ $adminResponseData['response'] }}
                </div>
                @if(!empty($adminResponseData['presentation_date']) || !empty($adminResponseData['scheduled_time']))
                <div class="pt-2 border-t border-slate-200/60 flex items-center gap-2 text-xs font-bold text-emerald-800">
                    <i class="fa-regular fa-clock text-emerald-600"></i>
                    <span>Jadwal: {{ $adminResponseData['presentation_date'] ?? '-' }} pukul {{ $adminResponseData['scheduled_time'] ?? '-' }} WIB</span>
                </div>
                @endif
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-2 pt-1">
                @if($adminResponseData['type'] === 'new_task')
                <a href="{{ route('user.tasks.index') }}" wire:click="dismissAdminResponseModal({{ $adminResponseData['id'] }}, {{ $adminResponseData['timestamp'] }})" class="flex-1 py-2.5 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Buka Halaman Tugas</span>
                </a>
                @elseif($adminResponseData['type'] === 'presentation')
                <button type="button" wire:click="openStatusBantuanModalFromResponse({{ $adminResponseData['id'] }}, {{ $adminResponseData['timestamp'] }})" class="flex-1 py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    <span>Lihat Status Presentasi</span>
                </button>
                @endif
                <button type="button" wire:click="dismissAdminResponseModal({{ $adminResponseData['id'] }}, {{ $adminResponseData['timestamp'] }})" class="flex-1 py-2.5 px-4 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-check"></i>
                    <span>Mengerti</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- [LIVEWIRE MODAL] MODAL STATUS BANTUAN AKTIF (RAISE HAND)     --}}
    {{-- ============================================================ --}}
    {{-- ============================================================ --}}
    {{-- [LIVEWIRE POPUP] MODAL CHAT PERTANYAAN (DESAIN GANTI JAM)    --}}
    {{-- ============================================================ --}}
    @if($showStatusBantuanModal && $currentHandRaise && ($currentHandRaise->type === 'question' || is_null($currentHandRaise->type)))
    <div class="fixed inset-0 z-[10010] flex items-center justify-center bg-black/60 backdrop-blur-xs p-2.5 pb-7 sm:p-4 animate-in fade-in duration-150"
        wire:key="status-bantuan-modal-{{ $currentHandRaise->id }}"
        wire:click.self="closeStatusBantuanModal">
        <div class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-full max-w-lg mx-auto relative flex flex-col max-h-[85vh] sm:max-h-[88vh] mb-2 sm:mb-0 overflow-hidden border border-slate-100 animate-in zoom-in-95 duration-150">

            <!-- Header Modal -->
            <div class="p-3 sm:p-4.5 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/70">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 pr-2">
                    <div class="p-1.5 sm:p-2.5 bg-blue-100 text-blue-600 rounded-xl shrink-0">
                        <i class="fa-solid fa-comments text-base sm:text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 truncate">Diskusi Bantuan Kendala</h3>
                        <p class="text-[11px] sm:text-xs text-slate-500 truncate">
                            @if($currentHandRaise->status === 'pending')
                            Menunggu tanggapan atau arahan dari mentor.
                            @elseif($currentHandRaise->status === 'responded')
                            Mentor telah memberikan tanggapan / solusi.
                            @else
                            Komunikasi dua arah dengan Mentor mengenai kendala Anda.
                            @endif
                        </p>
                    </div>
                </div>
                <button type="button" wire:click="closeStatusBantuanModal" class="text-slate-400 hover:text-slate-600 transition p-1.5 rounded-lg hover:bg-slate-200/50 cursor-pointer shrink-0" title="Tutup">
                    <i class="fa-solid fa-xmark text-base sm:text-lg"></i>
                </button>
            </div>

            <!-- Info Pertanyaan / Kendala Awal Pemagang (Banner) -->
            @if(!empty($currentHandRaise->notes ?? $currentHandRaise->reason))
            <div class="px-3.5 sm:px-5 py-2 sm:py-2.5 shrink-0 bg-blue-50/50 border-b border-blue-100">
                <div class="text-[11px] font-bold text-blue-950 flex items-center justify-between mb-1">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-question text-blue-600"></i>
                        <span>Pertanyaan / Kendala Awal Anda:</span>
                    </span>
                    <span class="text-[10px] text-blue-700 font-normal shrink-0 ml-2">{{ $currentHandRaise->created_at?->format('H:i') }} WIB</span>
                </div>
                <p class="text-xs text-slate-800 bg-white/90 p-2 sm:p-2.5 rounded-xl border border-blue-200/70 whitespace-pre-line break-words leading-relaxed max-h-20 sm:max-h-28 overflow-y-auto">{{ trim($currentHandRaise->notes ?? $currentHandRaise->reason) }}</p>
            </div>
            @endif

            <!-- Chat History List -->
            <div id="intern-question-chat-messages"
                x-data="{
                    scrollToBottom() {
                        this.$nextTick(() => {
                            this.$el.scrollTop = this.$el.scrollHeight;
                        });
                    }
                }"
                x-init="
                    scrollToBottom();
                    const observer = new MutationObserver(() => scrollToBottom());
                    observer.observe($el, { childList: true, subtree: true });
                "
                class="flex-1 overflow-y-auto p-3.5 sm:p-5 space-y-2.5 sm:space-y-3 min-h-[120px] max-h-[40vh] sm:max-h-[320px] bg-slate-50/30">
                @php
                $thread = $currentHandRaise->conversation_thread;
                @endphp
                @forelse($thread as $idx => $note)
                @if(!empty($note['is_from_admin']))
                {{-- Mentor / Admin bubble on the left --}}
                <div wire:key="intern-chat-msg-{{ $note['id'] ?? $idx }}" class="flex flex-col items-start animate-in fade-in duration-150">
                    <div class="bg-white text-slate-800 rounded-2xl rounded-tl-xs px-3 sm:px-3.5 py-1.5 sm:py-2 max-w-[88%] sm:max-w-[82%] text-xs border border-slate-200/80 shadow-2xs">
                        <div class="font-bold text-[10px] text-blue-600 mb-0.5">{{ $note['sender_name'] ?? 'Mentor' }} (Mentor)</div>
                        <div class="whitespace-pre-wrap leading-relaxed break-words">{!! nl2br(e(trim($note['message']))) !!}</div>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-0.5">{{ $note['time'] }}</span>
                </div>
                @else
                {{-- Intern bubble on the right --}}
                <div wire:key="intern-chat-msg-{{ $note['id'] ?? $idx }}" class="flex flex-col items-end animate-in fade-in duration-150">
                    <div class="bg-blue-600 text-white rounded-2xl rounded-tr-xs px-3 sm:px-3.5 py-1.5 sm:py-2 max-w-[88%] sm:max-w-[82%] text-xs shadow-2xs">
                        <div class="font-bold text-[10px] text-blue-100 mb-0.5">{{ $note['sender_name'] ?? 'Anda' }}</div>
                        <div class="whitespace-pre-wrap leading-relaxed break-words">{!! nl2br(e(trim($note['message']))) !!}</div>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-0.5">{{ $note['time'] }}</span>
                </div>
                @endif
                @empty
                <div class="py-8 text-center text-slate-400">
                    <i class="fa-regular fa-comments text-3xl mb-2 text-slate-300 block"></i>
                    <span class="text-xs font-medium">Belum ada balasan dari mentor. Pertanyaan Anda sedang menunggu antrean mentor.</span>
                </div>
                @endforelse
            </div>

            <!-- Form Kirim Pesan / Balasan -->
            <div class="p-3 sm:p-4.5 pb-4 sm:pb-4.5 border-t border-slate-200/80 bg-white shrink-0">
                <form wire:submit.prevent="askFollowUp({{ $currentHandRaise->id }})">
                    <div class="space-y-2 sm:space-y-2.5">
                        <div>
                            <label for="chat_intern_q_input" class="block font-bold text-slate-700 text-[11px] sm:text-xs mb-1">
                                Kirim Pesan / Pertanyaan Lanjutan ke Mentor:
                            </label>
                            <textarea id="chat_intern_q_input" wire:model="followUpQuestionText" rows="1" required
                                @keydown.enter.exact.prevent="$wire.askFollowUp({{ $currentHandRaise->id }})"
                                placeholder="Tulis pesan atau pertanyaan untuk mentor... (Enter untuk kirim)"
                                class="w-full px-2.5 py-1.5 sm:p-2.5 border border-slate-300 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none text-[11px] sm:text-xs transition leading-normal sm:leading-relaxed resize-none min-h-[36px] sm:min-h-[50px]"></textarea>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-between gap-2 pt-1 border-t border-slate-100">
                            {{-- Tombol Tutup di sisi kiri (atau bawah di mobile) --}}
                            <button type="button" wire:click="closeStatusBantuanModal" class="w-full sm:w-auto px-3.5 py-1.5 sm:py-2 border border-slate-200 text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg sm:rounded-xl text-[11px] sm:text-xs font-semibold transition cursor-pointer text-center">
                                Tutup
                            </button>

                            {{-- Tombol Aksi di sisi kanan (atau atas di mobile) --}}
                            <div class="flex items-center gap-2 justify-end w-full sm:w-auto">
                                @if($currentHandRaise->status === 'pending' && count($thread) <= 1)
                                    <button type="button" wire:click="lowerHand({{ $currentHandRaise->id }})" class="flex-1 sm:flex-initial px-3.5 py-1.5 sm:py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg sm:rounded-xl text-[11px] sm:text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer" title="Batalkan pengajuan bantuan">
                                    <i class="fa-solid fa-hand-holding text-xs"></i>
                                    <span>Turunkan Tangan</span>
                                    </button>
                                    @else
                                    <button type="button" wire:click="completeQuestion({{ $currentHandRaise->id }})"
                                        class="flex-1 sm:flex-initial px-3.5 py-1.5 sm:py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg sm:rounded-xl text-[11px] sm:text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer"
                                        title="Selesaikan kendala dan turunkan tangan jika sudah paham">
                                        <i class="fa-solid fa-check text-xs"></i>
                                        <span>Sudah Paham</span>
                                    </button>
                                    @endif

                                    <button type="submit"
                                        class="flex-1 sm:flex-initial px-4 py-1.5 sm:py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg sm:rounded-xl text-[11px] sm:text-xs transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                                        <i class="fa-solid fa-paper-plane text-xs"></i>
                                        <span>Kirim Pesan</span>
                                    </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @elseif($showStatusBantuanModal && $currentHandRaise)
    {{-- ============================================================ --}}
    {{-- [LIVEWIRE POPUP] MODAL PRESENTASI & TUGAS BARU               --}}
    {{-- ============================================================ --}}
    <div class="fixed inset-0 z-[10010] flex items-center justify-center bg-black/65 backdrop-blur-sm p-2.5 sm:p-4" wire:key="status-bantuan-modal-{{ $currentHandRaise->id }}">
        <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-md max-h-[92vh] sm:max-h-[90vh] shadow-2xl overflow-hidden flex flex-col border border-slate-100 animate-in fade-in zoom-in duration-200">
            <!-- Header -->
            <div class="px-5 py-4 border-b {{ in_array($currentHandRaise->status, ['accepted', 'in_progress', 'ready']) ? 'bg-emerald-800' : ($currentHandRaise->status === 'rejected' ? 'bg-rose-800' : ($currentHandRaise->status === 'rescheduled' ? 'bg-blue-800' : 'bg-slate-800')) }} text-white flex items-center justify-between shrink-0">
                <div class="flex items-center space-x-2.5 min-w-0 pr-2">
                    <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center text-white shrink-0">
                        @if($currentHandRaise->type === 'presentation')
                        <i class="fa-solid fa-chalkboard-user text-base"></i>
                        @else
                        <i class="fa-solid fa-list-check text-base"></i>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm sm:text-base font-bold leading-tight truncate">
                            @if($currentHandRaise->type === 'presentation')
                            Status Presentasi
                            @else
                            Status Permintaan Tugas
                            @endif
                        </h2>
                        <p class="text-[11px] sm:text-xs text-white/80 truncate">
                            @if($currentHandRaise->status === 'pending')
                            Permintaan Anda sedang menunggu respon mentor
                            @elseif($currentHandRaise->status === 'accepted')
                            Jadwal presentasi diterima & siap dilaksanakan
                            @elseif($currentHandRaise->status === 'rescheduled')
                            Jadwal presentasi telah diatur ulang oleh mentor
                            @elseif($currentHandRaise->status === 'rejected')
                            Pengajuan bantuan / presentasi ditolak
                            @elseif($currentHandRaise->status === 'in_progress')
                            Tugas baru telah diberikan oleh mentor
                            @elseif($currentHandRaise->status === 'needs_revision')
                            Presentasi memiliki catatan revisi
                            @elseif($currentHandRaise->status === 'ready')
                            Presentasi telah disahkan lulus valid
                            @else
                            Tanggapan pengajuan bantuan
                            @endif
                        </p>
                    </div>
                </div>
                <button type="button" wire:click="closeStatusBantuanModal" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer shrink-0" title="Tutup">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-4 sm:p-6 space-y-3.5 overflow-y-auto flex-1 text-xs no-scrollbar">
                <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200 space-y-3">
                    {{-- Kategori & Status Ringkas --}}
                    <div class="flex items-center justify-between flex-wrap gap-2 pb-2.5 border-b border-slate-200/80">
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($currentHandRaise->type === 'presentation')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-900 border border-amber-200">
                                <i class="fa-solid fa-chalkboard-user text-amber-700"></i> Presentasi
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-50 text-purple-900 border border-purple-200">
                                <i class="fa-solid fa-list-check text-purple-700"></i> Tugas Baru
                            </span>
                            @endif

                            {{-- Status Badge --}}
                            @if($currentHandRaise->status === 'pending')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-900 border border-amber-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Menunggu Mentor
                            </span>
                            @elseif($currentHandRaise->status === 'accepted')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-900 border border-emerald-300">
                                <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i> Jadwal Disetujui
                            </span>
                            @elseif($currentHandRaise->status === 'rescheduled')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-900 border border-blue-300">
                                <i class="fa-solid fa-clock-rotate-left text-blue-600 text-xs"></i> Jadwal Diubah
                            </span>
                            @elseif($currentHandRaise->status === 'rejected')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-50 text-rose-900 border border-rose-300">
                                <i class="fa-solid fa-circle-xmark text-rose-600 text-xs"></i> Ditolak
                            </span>
                            @elseif($currentHandRaise->status === 'in_progress')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-900 border border-emerald-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span> Tugas Aktif
                            </span>
                            @elseif($currentHandRaise->status === 'needs_revision')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-orange-50 text-orange-900 border border-orange-300">
                                <i class="fa-solid fa-triangle-exclamation text-orange-600 text-xs"></i> Ada Revisi
                            </span>
                            @elseif($currentHandRaise->status === 'ready')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-900 border border-emerald-300">
                                <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i> Selesai
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                {{ $currentHandRaise->status }}
                            </span>
                            @endif
                        </div>

                        <span class="text-[11px] text-slate-400 font-medium">
                            {{ $currentHandRaise->created_at?->diffForHumans() }}
                        </span>
                    </div>

                    {{-- Detail Khusus Presentasi --}}
                    @if($currentHandRaise->type === 'presentation')
                    @if($currentHandRaise->presentation_date)
                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <span class="font-medium">Tanggal Presentasi:</span>
                        <span class="font-bold text-slate-900">{{ $currentHandRaise->presentation_date->format('d M Y') }}</span>
                    </div>
                    @endif

                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <span class="font-medium">Waktu / Jam Pelaksanaan:</span>
                        @if(!empty($currentHandRaise->scheduled_time))
                        <span class="font-bold text-indigo-800 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-lg text-xs">{{ date('H:i', strtotime($currentHandRaise->scheduled_time)) }} WIB</span>
                        @else
                        <span class="text-amber-800 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-lg text-xs italic font-medium">Menunggu penetapan jam</span>
                        @endif
                    </div>

                    @if($currentHandRaise->presentation_mode)
                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <span class="font-medium">Mode Presentasi:</span>
                        <span class="font-bold text-slate-900">{{ $currentHandRaise->presentation_mode === 'online' ? '💻 Online (Google Meet)' : '🏢 Tatap Muka' }}</span>
                    </div>
                    @endif

                    {{-- Google Meet Link if Online --}}
                    @if($currentHandRaise->presentation_mode === 'online')
                    @php
                    $currentUserModel = $user instanceof \App\Models\User ? $user : auth()->user();
                    $activeMeetUrl = $currentHandRaise->meet_url ?: ($currentUserModel?->intern?->division?->meet_url ?? null);
                    $isAccepted = in_array($currentHandRaise->status, ['accepted', 'rescheduled', 'in_progress', 'ready', 'needs_revision']);
                    @endphp
                    <div class="p-3 {{ $isAccepted ? 'bg-sky-50 border-sky-200' : 'bg-slate-50 border-slate-200' }} border rounded-xl space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold {{ $isAccepted ? 'text-sky-900' : 'text-slate-700' }} flex items-center gap-1.5">
                                <i class="fa-solid fa-video {{ $isAccepted ? 'text-sky-600' : 'text-slate-400' }}"></i> Link Google Meet Presentasi
                            </span>
                            <span class="text-[10px] {{ $isAccepted ? 'bg-sky-200/70 text-sky-800' : 'bg-slate-200 text-slate-700' }} font-bold px-2 py-0.5 rounded-full">Online</span>
                        </div>
                        @if($isAccepted)
                        @if($activeMeetUrl)
                        <div class="flex items-center gap-1.5">
                            <input type="text" readonly value="{{ $activeMeetUrl }}" class="w-full text-xs p-2 bg-white border border-sky-200 rounded-lg text-sky-900 font-mono select-all">
                            <a href="{{ $activeMeetUrl }}" target="_blank" rel="noopener noreferrer" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shrink-0 transition flex items-center gap-1">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i> Buka
                            </a>
                        </div>
                        @else
                        <p class="text-xs text-sky-800">Link Google Meet belum diatur oleh admin. Harap hubungi mentor.</p>
                        @endif
                        @else
                        <div class="p-2 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-900 flex items-start gap-1.5">
                            <i class="fa-solid fa-lock text-amber-600 mt-0.5 shrink-0"></i>
                            <span>Link Google Meet akan aktif setelah pengajuan disetujui mentor.</span>
                        </div>
                        @endif
                    </div>
                    @endif
                    @endif

                    {{-- Catatan / Keterangan Pengajuan Pemagang (Non-question) --}}
                    @if($currentHandRaise->notes || $currentHandRaise->reason)
                    <div class="pt-2 border-t border-slate-200">
                        <span class="text-[11px] font-bold text-slate-500 block mb-1">Catatan / Keterangan Pengajuan:</span>
                        <div class="text-xs text-slate-800 bg-white p-2.5 rounded-xl border border-slate-200 break-words leading-relaxed max-h-28 overflow-y-auto no-scrollbar font-normal">
                            {!! nl2br(e(trim($currentHandRaise->notes ?? $currentHandRaise->reason))) !!}
                        </div>
                    </div>
                    @endif

                    {{-- Tanggapan Mentor / Catatan Admin (Non-question) --}}
                    @if(!empty($currentHandRaise->admin_response))
                    <div class="pt-2 border-t border-slate-200">
                        <span class="text-[11px] font-bold block mb-1">
                            @if($currentHandRaise->status === 'rejected')
                            <span class="text-rose-800 flex items-center gap-1 font-bold">
                                <i class="fa-solid fa-circle-xmark text-rose-600"></i> Alasan Penolakan dari Mentor:
                            </span>
                            @elseif($currentHandRaise->status === 'needs_revision')
                            <span class="text-orange-800 flex items-center gap-1 font-bold">
                                <i class="fa-solid fa-triangle-exclamation text-orange-600"></i> Catatan Revisi dari Mentor:
                            </span>
                            @elseif($currentHandRaise->status === 'rescheduled')
                            <span class="text-blue-800 flex items-center gap-1 font-bold">
                                <i class="fa-solid fa-clock-rotate-left text-blue-600"></i> Catatan Reschedule dari Mentor:
                            </span>
                            @else
                            <span class="text-indigo-800 flex items-center gap-1 font-bold">
                                <i class="fa-solid fa-reply text-indigo-600"></i> Catatan / Tanggapan Mentor:
                            </span>
                            @endif
                        </span>
                        <div class="text-xs text-slate-800 bg-white p-2.5 rounded-xl border border-slate-200 break-words leading-relaxed max-h-36 overflow-y-auto no-scrollbar font-medium">
                            {!! nl2br(e(trim($currentHandRaise->admin_response))) !!}
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Hint & Notification Text --}}
                @if($currentHandRaise->status === 'pending')
                <p class="text-xs text-slate-500 leading-relaxed">
                    Jika Anda ingin membatalkan pengajuan ini, klik tombol <strong>Turunkan Tangan</strong> di bawah.
                </p>
                @elseif($currentHandRaise->type === 'presentation' && in_array($currentHandRaise->status, ['accepted', 'rescheduled']))
                @if(!$presentationDoneStep)
                <div class="p-2.5 bg-sky-50 border border-sky-200 rounded-xl space-y-0.5">
                    <div class="text-xs font-bold text-sky-950 flex items-center gap-1.5">
                        <i class="fa-solid fa-calendar-check text-sky-600"></i>
                        <span>Jadwal Disetujui</span>
                    </div>
                    <p class="text-xs text-sky-900 leading-normal">
                        Setelah selesai presentasi, klik <strong>Sudah Presentasi</strong> untuk validasi kelulusan.
                    </p>
                </div>
                @else
                <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl space-y-0.5">
                    <div class="text-xs font-bold text-emerald-950 flex items-center gap-1.5">
                        <i class="fa-solid fa-flag-checkered text-emerald-600"></i>
                        <span>Evaluasi Presentasi</span>
                    </div>
                    <p class="text-xs text-emerald-900 leading-normal">
                        Pilih hasil evaluasi dari mentor untuk menyelesaikan sesi:
                    </p>
                </div>
                @endif
                @elseif($currentHandRaise->status === 'rejected')
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-900 leading-relaxed">
                    Pengajuan Anda ditolak oleh mentor. Klik <strong>Tutup & Hapus Status</strong> di bawah agar dapat mengajukan kembali jika diperlukan.
                </div>
                @endif
            </div>

            <!-- Footer / Action Buttons -->
            <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-2 shrink-0">
                @if($currentHandRaise->type === 'presentation' && in_array($currentHandRaise->status, ['accepted', 'rescheduled']) && $presentationDoneStep)
                <button type="button" wire:click="resetPresentationDone" class="w-full sm:w-auto px-4 py-2.5 border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-100 transition cursor-pointer text-center flex items-center justify-center gap-1">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Kembali</span>
                </button>
                @else
                <button type="button" wire:click="closeStatusBantuanModal" class="w-full sm:w-auto px-4 py-2.5 border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-100 transition cursor-pointer text-center">
                    Tutup
                </button>
                @endif

                <div class="w-full sm:w-auto flex flex-col sm:flex-row items-center gap-2">
                    {{-- CASE 1: Presentasi yang sudah dijawab (Accepted / Rescheduled) --}}
                    @if($currentHandRaise->type === 'presentation' && in_array($currentHandRaise->status, ['accepted', 'rescheduled']))
                    @if(!$presentationDoneStep)
                    <button type="button"
                        wire:click="confirmPresentationDone"
                        class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                        <i class="fa-solid fa-chalkboard-user"></i>
                        <span>Sudah Presentasi</span>
                    </button>
                    @else
                    <button type="button"
                        wire:click="promptOutcomeConfirm({{ $currentHandRaise->id }}, 'revision')"
                        class="w-full sm:w-auto px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow-sm cursor-pointer"
                        title="Ada catatan revisi dari mentor">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>Ada Revisi</span>
                    </button>

                    <button type="button"
                        wire:click="promptOutcomeConfirm({{ $currentHandRaise->id }}, 'passed')"
                        class="w-full sm:w-auto px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow-sm cursor-pointer"
                        title="Lulus tanpa revisi (Tugas selesai valid)">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Lulus</span>
                    </button>
                    @endif

                    {{-- CASE 2: Status Ditolak --}}
                    @elseif($currentHandRaise->status === 'rejected')
                    <button type="button"
                        wire:click="lowerHand({{ $currentHandRaise->id }})"
                        class="w-full sm:w-auto px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                        <i class="fa-solid fa-trash-can"></i>
                        <span>Tutup & Hapus Status</span>
                    </button>

                    {{-- CASE 3: Status Pending --}}
                    @elseif($currentHandRaise->status === 'pending')
                    <button type="button"
                        wire:click="lowerHand({{ $currentHandRaise->id }})"
                        class="w-full sm:w-auto px-4 py-2.5 bg-slate-700 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                        <i class="fa-solid fa-hand-holding"></i>
                        <span>Turunkan Tangan</span>
                    </button>

                    {{-- CASE 5: Tugas baru diberikan --}}
                    @elseif($currentHandRaise->type === 'new_task' && in_array($currentHandRaise->status, ['in_progress', 'done']))
                    <a href="{{ route('user.tasks.index') }}"
                        wire:click="closeStatusBantuanModal"
                        class="w-full sm:w-auto px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                        <i class="fa-solid fa-list-check"></i>
                        <span>Buka Halaman Tugas</span>
                    </a>

                    <button type="button"
                        wire:click="completeNewTaskRaiseHand({{ $currentHandRaise->id }})"
                        class="w-full sm:w-auto px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                        <i class="fa-solid fa-check"></i>
                        <span>Selesai</span>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- [LIVEWIRE POPUP] MODAL KONFIRMASI HASIL EVALUASI PRESENTASI   --}}
    {{-- ============================================================ --}}
    @if($showOutcomeConfirmModal)
    <div class="fixed inset-0 z-[10020] flex items-center justify-center bg-black/65 backdrop-blur-xs p-4" wire:key="outcome-confirm-modal">
        <div class="bg-white rounded-3xl p-6 sm:p-7 {{ $pendingOutcome === 'passed' ? 'max-w-md' : 'max-w-sm' }} w-full mx-auto shadow-2xl border border-slate-100 flex flex-col items-center text-center animate-in fade-in zoom-in duration-150">
            @if($pendingOutcome === 'passed')
            {{-- Header Lulus --}}
            <div class="w-13 h-13 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl shadow-xs mb-3">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200 inline-block mb-1">
                Evaluasi Selesai
            </span>
            <h3 class="text-base sm:text-lg font-bold text-slate-800 leading-snug mb-1.5">
                Konfirmasi Kelulusan
            </h3>
            <p class="text-xs text-slate-600 leading-relaxed mb-3">
                Pastikan seluruh penugasan telah selesai. Masukkan tautan link tugas Anda di bawah ini sebelum menyelesaikan:
            </p>

            @if(!empty($pendingProjectName))
            <div class="w-full text-left p-3 bg-slate-50 border border-slate-200 rounded-xl mb-3.5 space-y-0.5">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 flex items-center gap-1">
                    <i class="fa-solid fa-list-check text-[10px]"></i>
                    <span>Tugas / Project:</span>
                </span>
                <span class="text-xs font-bold text-slate-800 leading-snug block truncate">{{ $pendingProjectName }}</span>
            </div>
            @endif

            {{-- Input Link Tugas / Repo / Figma / Media --}}
            <div class="space-y-1.5 w-full text-left mb-4">
                <label for="taskLinkInput" class="block text-xs font-bold text-slate-700">
                    {{ $taskLinkLabel }} <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        @if($taskLinkType === 'programmer')
                        <i class="fa-brands fa-github text-slate-700 text-sm"></i>
                        @elseif($taskLinkType === 'uiux')
                        <i class="fa-brands fa-figma text-purple-600 text-sm"></i>
                        @else
                        <i class="fa-solid fa-link text-indigo-500 text-xs"></i>
                        @endif
                    </div>
                    <input type="url"
                        id="taskLinkInput"
                        wire:model="taskLinkInput"
                        placeholder="{{ $taskLinkPlaceholder }}"
                        class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border @error('taskLinkInput') border-red-400 ring-2 ring-red-200 @else border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 @enderror rounded-xl text-xs text-slate-900 font-medium focus:outline-none transition">
                </div>
                @error('taskLinkInput')
                <p class="text-[11px] text-red-600 font-medium flex items-center gap-1 mt-1">
                    <i class="fa-solid fa-circle-exclamation text-xs shrink-0"></i>
                    <span>{{ $message }}</span>
                </p>
                @enderror
                <p class="text-[11px] text-slate-400 leading-tight pt-0.5">
                    @if($taskLinkType === 'programmer')
                    Tautkan URL repository GitHub khusus untuk tugas ini (pastikan bersifat publik atau mentor diberi akses).
                    @elseif($taskLinkType === 'uiux')
                    Tautkan URL file / prototype Figma khusus untuk tugas ini (pastikan diset view permission).
                    @else
                    Tautkan URL Google Drive / media folder pengumpulan tugas ini.
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-2 w-full pt-1">
                <button type="button" wire:click="cancelOutcomeConfirm" class="flex-1 py-2.5 px-3 border border-slate-300 text-slate-700 hover:bg-slate-100 font-bold text-xs rounded-xl transition cursor-pointer text-center">
                    Batal
                </button>
                <button type="button" wire:click="executeOutcomeConfirm" wire:loading.attr="disabled" class="flex-1 py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-check"></i>
                    <span>Simpan & Lulus</span>
                </button>
            </div>
            @elseif($pendingOutcome === 'revision')
            {{-- Icon Revisi --}}
            <div class="w-13 h-13 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-2xl shadow-xs mb-3">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200 inline-block mb-1">
                Perlu Perbaikan
            </span>
            <h3 class="text-base sm:text-lg font-bold text-slate-800 leading-snug mb-1.5">
                Konfirmasi Catatan Revisi
            </h3>
            <p class="text-xs text-slate-600 leading-relaxed mb-5">
                Apakah presentasi memiliki <strong>catatan revisi</strong> dari mentor? Status tugas akan tetap progress agar Anda dapat mengerjakan revisi.
            </p>
            <div class="flex items-center gap-2 w-full">
                <button type="button" wire:click="cancelOutcomeConfirm" class="flex-1 py-2.5 px-3 border border-slate-300 text-slate-700 hover:bg-slate-100 font-bold text-xs rounded-xl transition cursor-pointer text-center">
                    Batal
                </button>
                <button type="button" wire:click="executeOutcomeConfirm" wire:loading.attr="disabled" class="flex-1 py-2.5 px-3 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Ya, Ada Revisi</span>
                </button>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Notification Element -->
    <div id="permit-notification" wire:ignore class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg hidden z-[10000] flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span>Izin selesai! Durasi: <span id="permit-duration" class="font-bold">00:00:00</span></span>
    </div>

    <!-- Permit Limit Alert Modal -->
    <div id="permitLimitModal" wire:ignore class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/60 backdrop-blur-sm hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl p-5 sm:p-6 max-w-sm w-full mx-auto shadow-2xl">
            <div class="text-center">
                <div class="text-red-500 text-4xl sm:text-5xl mb-3 sm:mb-4">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-2" id="permitLimitTitle">Batas Izin Tercapai</h3>
                <p class="text-xs sm:text-sm text-gray-500 mb-5 sm:mb-6 leading-relaxed" id="permitLimitMessage"></p>
                <div class="flex justify-center">
                    <button onclick="closePermitLimitModal()" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs sm:text-sm font-semibold rounded-xl transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Shift Not Set Alert Modal -->
    <div id="shiftNotSetModal" wire:ignore class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/60 backdrop-blur-sm hidden p-3 sm:p-4">
        <div class="bg-white rounded-2xl p-5 sm:p-6 max-w-sm w-full mx-auto shadow-2xl">
            <div class="text-center">
                <div class="text-orange-500 text-4xl sm:text-5xl mb-3 sm:mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-2">Shift Belum Diatur</h3>
                <p class="text-xs sm:text-sm text-gray-500 mb-5 sm:mb-6 leading-relaxed">Shift Anda belum diatur. Harap lakukan konfirmasi ke admin atau HR terkait pengaturan shift Anda.</p>
                <div class="flex justify-center">
                    <button onclick="closeShiftNotSetModal()" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs sm:text-sm font-semibold rounded-xl transition">
                        Mengerti
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Permit Modal --}}
    @if($activePermit)
    @php
    $permitType = $activePermit->type;
    $icon = 'walking';
    $title = 'Sedang Izin';
    $description = $activePermit->description ?: 'Silakan kembali jika sudah selesai.';

    switch ($permitType) {
    case 'toilet':
    $icon = 'restroom';
    $title = 'Sedang Izin ke Toilet';
    break;
    case 'prayer':
    $icon = 'mosque';
    $title = 'Sedang Izin Shalat';
    break;
    case 'leave':
    $icon = 'door-open';
    $title = 'Sedang Izin Keluar';
    break;
    }

    // Ambil batas durasi dari PermitSetting jika agreed_duration_minutes <= 0
        $permitLimitSetting=\App\Models\PermitSetting::where('type', $permitType)->first();
        $targetLimitMinutes = (int) ($activePermit->agreed_duration_minutes > 0
        ? $activePermit->agreed_duration_minutes
        : ($permitLimitSetting?->max_duration_minutes ?? ($permitType === 'prayer' ? 20 : ($permitType === 'toilet' ? 25 : 0))));

        $startTimeIso = \Carbon\Carbon::parse($activePermit->start_time)->toIso8601String();
        @endphp
        <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/75 backdrop-blur-sm p-3 sm:p-4"
            wire:key="active-permit-modal-{{ $activePermit->id }}-{{ $startTimeIso }}"
            x-data="{
             startTime: new Date('{{ $startTimeIso }}'),
             elapsedStr: '00:00:00',
             timer: null,
             init() {
                 this.update();
                 this.timer = setInterval(() => { this.update(); }, 1000);
             },
             destroy() {
                 if (this.timer) clearInterval(this.timer);
             },
             update() {
                 const now = new Date();
                 let diffMs = now - this.startTime;
                 if (diffMs < 0) diffMs = 0;
                 const elapsedSec = Math.floor(diffMs / 1000);
                 
                 const h = Math.floor(elapsedSec / 3600);
                 const m = Math.floor((elapsedSec % 3600) / 60);
                 const s = elapsedSec % 60;
                 this.elapsedStr = `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
             }
         }">
            <div class="bg-white p-5 sm:p-7 rounded-2xl sm:rounded-3xl shadow-2xl text-center w-full max-w-xs sm:max-w-sm mx-auto permit-modal max-h-[92vh] flex flex-col justify-center animate-in fade-in zoom-in duration-200">

                @if($activePermit->authorized_by)
                <div class="mb-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                        <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <span>Diaktifkan oleh Admin</span>
                    </span>
                </div>
                @endif

                <div class="text-blue-500 text-5xl sm:text-6xl mb-3 sm:mb-4"><i class="fas fa-{{ $icon }} permit-icon"></i></div>
                <h2 class="text-lg sm:text-2xl font-bold mb-1.5 sm:mb-2 text-gray-800">{{ $title }}</h2>
                <p class="text-gray-600 mb-3 sm:mb-4 text-xs sm:text-sm leading-relaxed max-w-xs mx-auto">{{ $description }}</p>

                @if($targetLimitMinutes > 0)
                <div class="mb-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200/80 mx-auto">
                    <i class="fa-regular fa-clock text-amber-600 text-xs"></i>
                    <span>Batas Waktu Izin: {{ $targetLimitMinutes }} Menit</span>
                </div>
                @endif

                <div class="mb-4 sm:mb-6 py-2 sm:py-2.5 px-4 bg-slate-100 rounded-xl inline-flex items-center justify-center gap-2 text-slate-700 border border-slate-200/80 shadow-2xs mx-auto">
                    <i class="fa-regular fa-clock text-blue-600"></i>
                    <div class="flex flex-col items-center">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider">Durasi Berjalan</span>
                        <span class="font-mono text-xl sm:text-2xl font-bold tracking-wider" x-text="elapsedStr">00:00:00</span>
                    </div>
                </div>

                <form id="end-permit-form" action="{{ route('user.permit.end') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 sm:py-3.5 px-4 sm:px-6 rounded-xl transition-all duration-200 shadow-md hover:shadow-lg permit-button flex items-center justify-center gap-2 text-xs sm:text-sm cursor-pointer">
                        <i class="fas fa-check"></i>
                        <span>Selesai / Kembali dari Izin</span>
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Leave Permit Modal --}}
        <div id="leavePermitModal" wire:ignore class="fixed inset-0 z-[9998] flex items-center justify-center bg-black/60 backdrop-blur-sm hidden p-3 sm:p-4">
            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-2xl w-full max-w-md mx-auto max-h-[90vh] flex flex-col">
                <div class="flex justify-between items-center mb-3 sm:mb-4 pb-2.5 border-b border-gray-100 shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i class="fa-solid fa-door-open text-sm"></i>
                        </div>
                        <h2 class="text-base sm:text-lg font-bold text-gray-800">Form Izin Keluar</h2>
                    </div>
                    <button type="button" onclick="closeLeavePermitModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold leading-none p-1 transition">&times;</button>
                </div>
                <form method="POST" action="{{ route('user.permit.start') }}" class="flex flex-col flex-1 overflow-y-auto px-1.5 py-1 space-y-3.5">
                    @csrf
                    <input type="hidden" name="type" value="leave">
                    <div>
                        <label for="keterangan" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Alasan Izin <span class="text-red-500">*</span></label>
                        <textarea id="keterangan" name="keterangan" rows="3" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors" placeholder="Contoh: Mengambil barang yang tertinggal / keperluan kantor" required></textarea>
                    </div>
                    <div>
                        <label for="authorized_by" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Diizinkan oleh <span class="text-red-500">*</span></label>
                        <select id="authorized_by" name="authorized_by" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors bg-white" required>
                            <option value="" disabled selected>-- Pilih nama HR/Atasan --</option>
                            @foreach($hrUsers as $hr)
                            <option value="{{ $hr['name'] }}">{{ $hr['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100 shrink-0 mt-auto">
                        <button type="button" onclick="closeLeavePermitModal()" class="px-4 py-2 border border-gray-300 text-gray-700 font-semibold rounded-xl text-xs hover:bg-gray-50 transition">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs shadow-sm transition">Kirim Izin</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="flex flex-col {{ $isPraying ? 'prayer-mode' : '' }} [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden no-scrollbar"
            id="prayer-container"
            data-active-permit="{{ $activePermit ? '1' : '0' }}"
            data-has-shift="{{ $hasShift ? '1' : '0' }}"
            data-is-working="{{ $isWorkingShiftToday ? '1' : '0' }}"
            data-leave-limit-reached="{{ $hasReachedLeaveLimit ? '1' : '0' }}"
            data-prayer-limit-reached="{{ $hasReachedPrayerLimit ? '1' : '0' }}"
            data-permit-start-url="{{ route('user.permit.start') }}"
            data-csrf-token="{{ csrf_token() }}">

            {{-- ================================================================= --}}
            {{-- DESKTOP VIEW (md:flex) - Original Full Vertical Sidebar          --}}
            {{-- ================================================================= --}}
            <div class="hidden md:flex flex-col space-y-2.5 w-full [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden no-scrollbar">
                <div class="text-2xl font-bold text-center lg:mb-4 mt-5">Shift {{ $shift }}</div>
                @if($activeLeavePermit)
                <div class="flex justify-center mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        <i class="fas fa-sign-out-alt"></i>
                        Sedang izin keluar sejak {{ \Carbon\Carbon::parse($activeLeavePermit->start_time)->format('H:i') }}
                    </span>
                </div>
                @endif

                @switch($stage)
                @case(AttendanceStatus::AttendanceAndAdjustableTime)
                <!-- MASUK & GANTI JAM AWAL (DESKTOP) -->
                @if(!$isHolidayToday && !$hasCheckedIn)
                <a href="#" class="w-full text-center bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors"
                    data-check-shift="1"
                    data-has-shift="{{ $hasShift ? 1 : 0 }}"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::AttendanceIn->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::AttendanceIn->value }}"
                    data-adjustable="0"
                    data-absence-id="{{ $absenceHistory->id ?? 0 }}"
                    data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                </a>
                @elseif($isHolidayToday && !$hasCheckedIn)
                <div class="w-full p-3 rounded-xl bg-amber-50 border border-amber-200 text-center text-xs font-bold text-amber-900 shadow-2xs">
                    <i class="fa-solid fa-umbrella-beach mr-1.5 text-amber-600 text-sm"></i>
                    <span>Hari Libur (Tidak Ada Shift Reguler)</span>
                </div>
                @endif

                @if($this->isChangeTimeAllowed())
                <a href="#" class="w-full text-center bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-xl text-base py-2.5 px-4 flex items-center justify-center gap-2 transition-colors"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::AdjustableIn->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::AdjustableIn->value }}"
                    data-adjustable="1"
                    data-absence-id="{{ $absenceHistory->id ?? 0 }}"
                    data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-clock"></i>
                    <span>Ganti Jam</span>
                </a>
                @endif

                @if(!$isHolidayToday)
                <a href="#" class="w-full text-center bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-xl text-base py-2.5 px-4 flex items-center justify-center gap-2 cursor-pointer transition-colors" onclick="event.preventDefault(); if (typeof window.showModalIzin === 'function') { window.showModalIzin('sakit'); } else if (typeof showModalIzin === 'function') { showModalIzin('sakit'); } else { $('#modal2').removeClass('hidden').addClass('flex'); if(typeof switchPermitType === 'function') switchPermitType('sakit'); }">
                    <i class="fa-solid fa-file-medical"></i>
                    <span>Izin / Sakit</span>
                </a>
                @endif
                @break

                @case(AttendanceStatus::AttendanceIn)
                @break

                @default
                <!-- TOMBOL GANTI JAM - TERSEDIA JIKA TIDAK SEDANG AKTIF DALAM SHIFT KERJA -->
                @if($this->isChangeTimeAllowed() && !$hasActiveAdjustable && !$isWorkingShiftToday && !in_array($stage, [AttendanceStatus::AllDone, AttendanceStatus::AdjustableOut]))
                <a href="#" class="w-full text-center bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-xl text-base py-2.5 px-4 flex items-center justify-center gap-2 transition-colors"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::AdjustableIn->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::AdjustableIn->value }}"
                    data-adjustable="1"
                    data-absence-id="{{ $absenceHistory->id ?? 0 }}"
                    data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-clock"></i>
                    <span>Ganti Jam</span>
                </a>
                @endif

                {{-- ISTIRAHAT BIASA --}}
                @if(in_array($stage, [AttendanceStatus::ShowBreakPermitChangeTime, AttendanceStatus::ShowBreakPermit, AttendanceStatus::ShowBreakChangeTime, AttendanceStatus::StartBreak]) && empty($isRegularBreakMissed))
                <a href="#" class="w-full text-center bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                    data-check-shift="1"
                    data-has-shift="{{ $hasShift ? 1 : 0 }}"
                    data-active-permit="{{ $activePermit ? 1 : 0 }}"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::StartBreak->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::StartBreak->value }}"
                    data-adjustable="0"
                    data-absence-id="0"
                    data-adjustable-id="0"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-mug-hot mr-2"></i>Istirahat
                </a>
                @endif

                {{-- ISTIRAHAT GANTI JAM --}}
                @if(in_array($stage, [AttendanceStatus::BreakOrBack, AttendanceStatus::BreakOrBack->value, 13]) && empty($isGantiJamBreakMissed))
                <a href="#" class="w-full text-center bg-green-600 hover:bg-green-500 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                    data-active-permit="{{ $activePermit ? 1 : 0 }}"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::StartBreakAdjustable->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::StartBreakAdjustable->value }}"
                    data-adjustable="1"
                    data-absence-id="0"
                    data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-mug-hot mr-2"></i>Istirahat (Ganti Jam)
                </a>
                @endif

                {{-- KEMBALI DARI ISTIRAHAT BIASA --}}
                @if(in_array($stage, [AttendanceStatus::EndBreak, AttendanceStatus::EndBreak->value, 8]))
                <a href="#" class="w-full text-center bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                    data-check-shift="1"
                    data-has-shift="{{ $hasShift ? 1 : 0 }}"
                    data-active-permit="{{ $activePermit ? 1 : 0 }}"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::EndBreak->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::EndBreak->value }}"
                    data-adjustable="0"
                    data-absence-id="0"
                    data-adjustable-id="0"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-undo mr-2"></i>Kembali dari Istirahat
                </a>
                @endif

                {{-- KEMBALI DARI ISTIRAHAT GANTI JAM --}}
                @if(in_array($stage, [AttendanceStatus::EndBreakAdjustable, AttendanceStatus::EndBreakAdjustable->value, 12]))
                <a href="#" class="w-full text-center bg-green-600 hover:bg-green-500 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                    data-active-permit="{{ $activePermit ? 1 : 0 }}"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::EndBreakAdjustable->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::EndBreakAdjustable->value }}"
                    data-adjustable="1"
                    data-absence-id="0"
                    data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-undo mr-2"></i>Kembali dari Istirahat (Ganti Jam)
                </a>
                @endif

                {{-- PULANG BIASA --}}
                @if(in_array($stage, [AttendanceStatus::ShowBreakPermitChangeTime, AttendanceStatus::ShowBreakPermit, AttendanceStatus::ShowPermitChangeTime, AttendanceStatus::ShowBreakChangeTime, AttendanceStatus::StartBreak, AttendanceStatus::StartPermit, AttendanceStatus::AttendanceOut, AttendanceStatus::ShowBreakPermitChangeTime->value, AttendanceStatus::ShowBreakPermit->value, AttendanceStatus::ShowPermitChangeTime->value, AttendanceStatus::ShowBreakChangeTime->value, AttendanceStatus::StartBreak->value, AttendanceStatus::StartPermit->value, AttendanceStatus::AttendanceOut->value, 2, 17, 15, 16, 7, 9, 4]))
                <a href="#" class="w-full text-center bg-red-600 hover:bg-red-500 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                    data-check-shift="1"
                    data-has-shift="{{ $hasShift ? 1 : 0 }}"
                    data-active-permit="{{ $activePermit ? 1 : 0 }}"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::AttendanceOut->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::AttendanceOut->value }}"
                    data-adjustable="0"
                    data-absence-id="0"
                    data-adjustable-id="0"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-home mr-2"></i>Pulang
                </a>
                @endif

                {{-- PULANG GANTI JAM (DESKTOP) --}}
                @if(in_array($stage, [AttendanceStatus::BreakOrBack, AttendanceStatus::BreakOrBack->value, 13, AttendanceStatus::AdjustableOut, AttendanceStatus::AdjustableOut->value, 6]))
                <a href="#" class="w-full text-center bg-red-600 hover:bg-red-500 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                    data-active-permit="{{ $activePermit ? 1 : 0 }}"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::AdjustableOut->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::AdjustableOut->value }}"
                    data-adjustable="1"
                    data-absence-id="0"
                    data-adjustable-id="{{ $activeAdjustableId }}"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-clock mr-2"></i>Pulang (Ganti Jam)
                </a>
                @endif

                @if(in_array($stage, [AttendanceStatus::AllDone, AttendanceStatus::AllDone->value, 14]))
                <div class="w-full text-center bg-gray-400 text-white font-bold rounded-xl text-base py-3 px-4 block cursor-not-allowed">
                    <i class="fas fa-check-circle mr-2"></i>Selesai
                </div>
                @endif
                @endswitch

                <!-- ACTION BUTTONS (DESKTOP VERTICAL) -->
                <!-- Logbook Harian -->
                <a href="{{ route('user.logbook.index') }}" onclick="handleLogbookClick(event)" class="w-full cursor-pointer text-center bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-xl text-base py-2.5 px-4 flex items-center justify-center gap-2 transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                    <i class="fa-regular fa-file-lines shrink-0"></i>
                    <span class="truncate">Logbook Harian</span>
                    @if($hasFilledLogToday)
                    <span class="inline-block w-2 h-2 rounded-full bg-green-400 shrink-0" title="Sudah Diisi"></span>
                    @else
                    <span class="inline-block w-2 h-2 rounded-full bg-yellow-400 shrink-0 animate-pulse" title="Belum Diisi"></span>
                    @endif
                </a>

                <!-- Info & Libur -->
                <a onclick="handleHolidayInfoClick(event)" class="w-full cursor-pointer text-center bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-xl text-base py-2.5 px-4 flex items-center justify-center gap-2 transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <i class="fa-solid fa-circle-info shrink-0"></i>
                    <span class="truncate">Info & Libur</span>
                </a>

                <!-- Tugas & Akun Divisi -->
                <a href="{{ route('user.tasks.index') }}" class="w-full text-center bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-xl text-base py-2.5 px-4 flex items-center justify-center gap-2 transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                    <i class="fa-solid fa-folder-open shrink-0"></i>
                    <span class="truncate">Tugas & Akun</span>
                    @if(!empty($hasActiveTasks))
                    <span class="relative flex h-2 w-2 shrink-0" title="{{ ($activeTasksCount ?? 0) > 0 ? ($activeTasksCount . ' Tugas Aktif') : 'Memiliki Tugas Aktif' }}">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    @endif
                </a>

                <!-- Pengumuman -->
                <a onclick="handleBroadcastListClick(event)" class="w-full cursor-pointer text-center bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-xl text-base py-2.5 px-4 flex items-center justify-center gap-2 transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <i class="fa-regular fa-clipboard shrink-0"></i>
                    <span class="truncate">Pengumuman</span>
                </a>

                <!-- Angkat Tangan -->
                @if($isHandRaised)
                <button type="button" wire:click="openStatusBantuanModal" class="w-full cursor-pointer text-center bg-emerald-700 hover:bg-emerald-600 text-white font-semibold rounded-xl text-base py-2.5 px-4 transition-all duration-200 flex items-center justify-center gap-2 {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $activePermit ? 'disabled' : '' }}>
                    <i class="fas fa-hand-paper animate-bounce"></i>
                    <span>Tangan Diangkat</span>
                    @if($currentHandRaise && $currentHandRaise->status === 'urgent')
                    <span class="inline-block w-2 h-2 rounded-full bg-red-400 animate-ping"></span>
                    @endif
                </button>
                @else
                <button type="button" onclick="handleRaiseHandClick(event)" class="w-full cursor-pointer text-center bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-xl text-base py-2.5 px-4 transition-all duration-200 flex items-center justify-center gap-2 {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $activePermit ? 'disabled' : '' }}>
                    <i class="fas fa-hand-paper"></i>
                    <span>Angkat Tangan</span>
                </button>
                @endif

                <!-- PERMIT BUTTONS (DESKTOP) -->
                @php
                $stageVal = is_object($stage) ? $stage->value : $stage;
                $isStageBlockedForPermit = in_array((int)$stageVal, [
                \App\Utils\AttendanceStatus::AttendanceIn->value,
                \App\Utils\AttendanceStatus::AttendanceAndAdjustableTime->value,
                \App\Utils\AttendanceStatus::AdjustableIn->value,
                \App\Utils\AttendanceStatus::AllDone->value,
                ]);
                @endphp
                @if(!$activePermit && ($hasCheckedIn || $hasActiveAdjustable) && !$isStageBlockedForPermit)
                <div class="flex flex-col space-y-2.5">
                    <!-- Izin Keluar -->
                    <button type="button"
                        onclick="handleLeavePermitClick(event)"
                        class="w-full text-white bg-gray-700 hover:bg-gray-600 font-semibold rounded-xl text-base py-2.5 px-4 text-center transition-colors flex items-center justify-center gap-2 {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                        title="Izin Keluar Lingkungan Kantor">
                        <i class="fas fa-door-open"></i>
                        <span>Izin Keluar</span>
                    </button>

                    <!-- Izin Shalat -->
                    <button type="button"
                        onclick="handlePrayerPermitClick(event)"
                        class="w-full text-white bg-gray-700 hover:bg-gray-600 font-semibold rounded-xl text-base py-2.5 px-4 text-center transition-colors flex items-center justify-center gap-2 {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                        title="Izin Shalat">
                        <i class="fas fa-mosque"></i>
                        <span>Izin Shalat</span>
                    </button>

                    <!-- Izin Toilet -->
                    <button type="button"
                        onclick="event.preventDefault(); submitPermit('toilet')"
                        class="w-full text-white bg-gray-700 hover:bg-gray-600 font-semibold rounded-xl text-base py-2.5 px-4 text-center transition-colors flex items-center justify-center gap-2 {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                        title="Izin ke Toilet">
                        <i class="fas fa-toilet"></i>
                        <span>Izin Toilet</span>
                    </button>
                </div>
                @endif
            </div>

            {{-- ================================================================= --}}
            {{-- MOBILE VIEW (md:hidden) - Ultra Compact 4-Column Icon Grid        --}}
            {{-- ================================================================= --}}
            <div class="md:hidden flex flex-col space-y-2 w-full">
                <!-- Header Ringkas: Shift -->
                <div class="text-center font-bold text-base text-gray-800 py-0.5">
                    @if($isHolidayToday)
                    <span class="text-amber-800"><i class="fa-solid fa-umbrella-beach mr-1 text-amber-600"></i> Hari Libur</span>
                    @else
                    Shift {{ $shift }}
                    @endif
                </div>

                @if($activeLeavePermit)
                <div class="p-2 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-center justify-center gap-1.5 font-bold">
                    <i class="fas fa-sign-out-alt"></i> Sedang izin keluar sejak {{ \Carbon\Carbon::parse($activeLeavePermit->start_time)->format('H:i') }}
                </div>
                @endif

                <!-- PRIMARY ATTENDANCE BUTTON (MOBILE HERO) -->
                @switch($stage)
                @case(AttendanceStatus::AttendanceAndAdjustableTime)
                @if(!$isHolidayToday && !$hasCheckedIn)
                <a href="#" class="w-full text-center bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors"
                    data-check-shift="1"
                    data-has-shift="{{ $hasShift ? 1 : 0 }}"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::AttendanceIn->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::AttendanceIn->value }}"
                    data-adjustable="0"
                    data-absence-id="{{ $absenceHistory->id ?? 0 }}"
                    data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                </a>
                @if($this->isChangeTimeAllowed() && !$hasActiveAdjustable)
                <a href="#" class="w-full text-center bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-xl text-sm py-2.5 px-4 mt-2 flex items-center justify-center gap-2 transition-colors shadow-2xs"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::AdjustableIn->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::AdjustableIn->value }}"
                    data-adjustable="1"
                    data-absence-id="{{ $absenceHistory->id ?? 0 }}"
                    data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-clock"></i>
                    <span>Ganti Jam</span>
                </a>
                @endif
                @elseif($isHolidayToday && $this->isChangeTimeAllowed() && !$hasActiveAdjustable)
                <a href="#" class="w-full text-center bg-gray-800 hover:bg-gray-700 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::AdjustableIn->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::AdjustableIn->value }}"
                    data-adjustable="1"
                    data-absence-id="{{ $absenceHistory->id ?? 0 }}"
                    data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-clock mr-2"></i>Mulai Ganti Jam
                </a>
                @elseif($isHolidayToday && !$hasCheckedIn)
                <div class="w-full p-3 rounded-xl bg-amber-50 border border-amber-200 text-center text-xs font-bold text-amber-900 shadow-2xs">
                    <i class="fa-solid fa-umbrella-beach mr-1.5 text-amber-600 text-sm"></i>
                    <span>Hari Libur (Tidak Ada Shift Reguler)</span>
                </div>
                @endif
                @break

                @default
                {{-- ISTIRAHAT BIASA --}}
                @if(in_array($stage, [AttendanceStatus::ShowBreakPermitChangeTime, AttendanceStatus::ShowBreakPermit, AttendanceStatus::ShowBreakChangeTime, AttendanceStatus::StartBreak]) && empty($isRegularBreakMissed))
                <a href="#" class="w-full text-center bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                    data-check-shift="1"
                    data-has-shift="{{ $hasShift ? 1 : 0 }}"
                    data-active-permit="{{ $activePermit ? 1 : 0 }}"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::StartBreak->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::StartBreak->value }}"
                    data-adjustable="0"
                    data-absence-id="0"
                    data-adjustable-id="0"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-mug-hot mr-2"></i>Istirahat
                </a>
                @endif

                {{-- ISTIRAHAT GANTI JAM --}}
                @if(in_array($stage, [AttendanceStatus::BreakOrBack, AttendanceStatus::BreakOrBack->value, 13]) && empty($isGantiJamBreakMissed))
                <a href="#" class="w-full text-center bg-green-600 hover:bg-green-500 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                    data-active-permit="{{ $activePermit ? 1 : 0 }}"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::StartBreakAdjustable->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::StartBreakAdjustable->value }}"
                    data-adjustable="1"
                    data-absence-id="0"
                    data-adjustable-id="{{ $activeAdjustableId }}"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-mug-hot mr-2"></i>Istirahat (Ganti Jam)
                </a>
                @endif

                {{-- KEMBALI DARI ISTIRAHAT BIASA --}}
                @if(in_array($stage, [AttendanceStatus::EndBreak, AttendanceStatus::EndBreak->value, 8]))
                <a href="#" class="w-full text-center bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                    data-check-shift="1"
                    data-has-shift="{{ $hasShift ? 1 : 0 }}"
                    data-active-permit="{{ $activePermit ? 1 : 0 }}"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::EndBreak->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::EndBreak->value }}"
                    data-adjustable="0"
                    data-absence-id="0"
                    data-adjustable-id="0"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-undo mr-2"></i>Kembali dari Istirahat
                </a>
                @endif

                {{-- KEMBALI DARI ISTIRAHAT GANTI JAM --}}
                @if(in_array($stage, [AttendanceStatus::EndBreakAdjustable, AttendanceStatus::EndBreakAdjustable->value, 12]))
                <a href="#" class="w-full text-center bg-green-600 hover:bg-green-500 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                    data-active-permit="{{ $activePermit ? 1 : 0 }}"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::EndBreakAdjustable->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::EndBreakAdjustable->value }}"
                    data-adjustable="1"
                    data-absence-id="0"
                    data-adjustable-id="{{ $activeAdjustableId }}"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-undo mr-2"></i>Kembali dari Istirahat (Ganti Jam)
                </a>
                @endif

                {{-- PULANG BIASA --}}
                @if(in_array($stage, [AttendanceStatus::ShowBreakPermitChangeTime, AttendanceStatus::ShowBreakPermit, AttendanceStatus::ShowPermitChangeTime, AttendanceStatus::ShowBreakChangeTime, AttendanceStatus::StartBreak, AttendanceStatus::StartPermit, AttendanceStatus::AttendanceOut, AttendanceStatus::ShowBreakPermitChangeTime->value, AttendanceStatus::ShowBreakPermit->value, AttendanceStatus::ShowPermitChangeTime->value, AttendanceStatus::ShowBreakChangeTime->value, AttendanceStatus::StartBreak->value, AttendanceStatus::StartPermit->value, AttendanceStatus::AttendanceOut->value, 2, 17, 15, 16, 7, 9, 4]))
                <a href="#" class="w-full text-center bg-red-600 hover:bg-red-500 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                    data-check-shift="1"
                    data-has-shift="{{ $hasShift ? 1 : 0 }}"
                    data-active-permit="{{ $activePermit ? 1 : 0 }}"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::AttendanceOut->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::AttendanceOut->value }}"
                    data-adjustable="0"
                    data-absence-id="0"
                    data-adjustable-id="0"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-home mr-2"></i>Pulang
                </a>
                @endif

                {{-- PULANG GANTI JAM (MOBILE) --}}
                @if(in_array($stage, [AttendanceStatus::BreakOrBack, AttendanceStatus::BreakOrBack->value, 13, AttendanceStatus::AdjustableOut, AttendanceStatus::AdjustableOut->value, 6]))
                <a href="#" class="w-full text-center bg-red-600 hover:bg-red-500 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                    data-active-permit="{{ $activePermit ? 1 : 0 }}"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::AdjustableOut->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::AdjustableOut->value }}"
                    data-adjustable="1"
                    data-absence-id="0"
                    data-adjustable-id="{{ $activeAdjustableId }}"
                    onclick="handleAttendanceAction(event, this)">
                    <i class="fas fa-clock mr-2"></i>Pulang (Ganti Jam)
                </a>
                @endif

                @if(in_array($stage, [AttendanceStatus::AllDone, AttendanceStatus::AllDone->value, 14]))
                <div class="w-full text-center bg-gray-400 text-white font-bold rounded-xl text-base py-3 px-4 block cursor-not-allowed">
                    <i class="fas fa-check-circle mr-2"></i>Selesai
                </div>
                @endif
                @endswitch



                <!-- FITUR & PERIZINAN KHUSUS MOBILE (5 TOMBOL UTAMA) -->
                <div class="flex flex-col gap-2 bg-white p-2.5 rounded-2xl border border-slate-200/90 shadow-2xs">
                    @php
                    $stageVal = is_object($stage) ? $stage->value : $stage;
                    $isBeforeCheckIn = in_array((int)$stageVal, [
                    \App\Utils\AttendanceStatus::AttendanceAndAdjustableTime->value,
                    1
                    ]) && !$hasCheckedIn && !$hasActiveAdjustable;
                    @endphp

                    <!-- Baris 1: Izin Sakit & Keperluan (Hanya Sebelum Absen Masuk Pada Hari Kerja) & Raise Hand -->
                    @if($isBeforeCheckIn && !$isHolidayToday)
                    <div class="grid grid-cols-2 gap-2">
                        <!-- 1. Izin Sakit & Keperluan -->
                        <a href="#" onclick="event.preventDefault(); if (typeof window.showModalIzin === 'function') { window.showModalIzin('sakit'); } else if (typeof showModalIzin === 'function') { showModalIzin('sakit'); } else { $('#modal2').removeClass('hidden').addClass('flex'); if(typeof switchPermitType === 'function') switchPermitType('sakit'); }"
                            class="flex items-center justify-center gap-2 py-2.5 px-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white shadow-xs transition active:scale-95 text-center cursor-pointer" title="Izin Sakit & Keperluan">
                            <i class="fa-solid fa-notes-medical text-white text-base shrink-0"></i>
                            <span class="text-xs font-bold truncate text-white">Izin Sakit / Keperluan</span>
                        </a>

                        <!-- 2. Raise Hand / Butuh Bantuan -->
                        @if($isHandRaised)
                        <button type="button" wire:click="openStatusBantuanModal"
                            class="flex items-center justify-center gap-2 py-2.5 px-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition active:scale-95 text-center cursor-pointer" title="Bantuan Aktif (Klik untuk lihat status)">
                            <i class="fas fa-hand-paper text-white text-base shrink-0 animate-bounce"></i>
                            <span class="text-xs font-bold truncate text-white">Bantuan Aktif</span>
                        </button>
                        @else
                        <button type="button" onclick="handleRaiseHandClick(event)"
                            class="flex items-center justify-center gap-2 py-2.5 px-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition active:scale-95 text-center cursor-pointer {{ $activePermit ? 'opacity-50 pointer-events-none' : '' }}" title="Angkat Tangan / Butuh Bantuan">
                            <i class="fas fa-hand-paper text-white text-base shrink-0"></i>
                            <span class="text-xs font-bold truncate text-white">Butuh Bantuan</span>
                        </button>
                        @endif
                    </div>
                    @else
                    <!-- Saat Sudah Absen Masuk atau Hari Libur: Raise Hand Tampil Penuh -->
                    <div>
                        @if($isHandRaised)
                        <button type="button" wire:click="openStatusBantuanModal"
                            class="w-full flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition active:scale-95 text-center cursor-pointer" title="Bantuan Aktif (Klik untuk lihat status)">
                            <i class="fas fa-hand-paper text-white text-base shrink-0 animate-bounce"></i>
                            <span class="text-xs font-bold truncate text-white">Bantuan Aktif (Tangan Diangkat)</span>
                        </button>
                        @else
                        <button type="button" onclick="handleRaiseHandClick(event)"
                            class="w-full flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition active:scale-95 text-center cursor-pointer {{ $activePermit ? 'opacity-50 pointer-events-none' : '' }}" title="Angkat Tangan / Butuh Bantuan">
                            <i class="fas fa-hand-paper text-white text-base shrink-0"></i>
                            <span class="text-xs font-bold truncate text-white">Butuh Bantuan (Raise Hand)</span>
                        </button>
                        @endif
                    </div>
                    @endif

                    <!-- Baris 2: Routine In-Shift Permits: Izin Keluar, Shalat, Toilet (3 Kolom Seimbang) -->
                    @if(!$activePermit && ($hasCheckedIn || $hasActiveAdjustable) && !$isStageBlockedForPermit)
                    <div class="grid grid-cols-3 gap-1.5 pt-2 border-t border-slate-100">
                        <!-- 3. Izin Keluar -->
                        <button type="button" onclick="handleLeavePermitClick(event)"
                            class="py-2.5 px-1 bg-amber-500 hover:bg-amber-600 text-white shadow-xs rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition active:scale-95 text-center cursor-pointer" title="Izin Keluar Lingkungan Kantor">
                            <i class="fa-solid fa-door-open text-white shrink-0 text-sm"></i>
                            <span class="truncate text-white">Izin Keluar</span>
                        </button>

                        <!-- 4. Izin Shalat -->
                        <button type="button" onclick="handlePrayerPermitClick(event)"
                            class="py-2.5 px-1 bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition active:scale-95 text-center cursor-pointer" title="Izin Shalat">
                            <i class="fas fa-mosque text-white shrink-0 text-sm"></i>
                            <span class="truncate text-white">Shalat</span>
                        </button>

                        <!-- 5. Izin Toilet -->
                        <button type="button" onclick="event.preventDefault(); submitPermit('toilet')"
                            class="py-2.5 px-1 bg-blue-600 hover:bg-blue-700 text-white shadow-xs rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition active:scale-95 text-center cursor-pointer" title="Izin Toilet">
                            <i class="fas fa-restroom text-white shrink-0 text-sm"></i>
                            <span class="truncate text-white">Toilet</span>
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <style>
            #permit-notification {
                transition: all 0.3s ease;
                animation: slideIn 0.5s forwards;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }

                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            .no-scrollbar::-webkit-scrollbar {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
            }

            .no-scrollbar {
                -ms-overflow-style: none !important;
                scrollbar-width: none !important;
            }
        </style>

        <script>
            function handleAttendanceAction(event, el) {
                if (event) event.preventDefault();
                if (!el) return false;

                const container = document.getElementById('prayer-container');
                const isPermitActive = (container && container.getAttribute('data-active-permit') === '1') || el.getAttribute('data-active-permit') === '1';
                if (isPermitActive) {
                    return false;
                }

                const checkShift = el.getAttribute('data-check-shift') === '1';
                const hasShift = (container && container.getAttribute('data-has-shift') === '1') || el.getAttribute('data-has-shift') === '1';
                if (checkShift && !hasShift) {
                    openShiftNotSetModal();
                    return false;
                }

                const gps = el.getAttribute('data-gps') === '1';
                const userId = parseInt(el.getAttribute('data-user-id'), 10) || 0;
                const stageRaw = el.getAttribute('data-stage');
                const stage = !isNaN(stageRaw) ? parseInt(stageRaw, 10) : stageRaw;
                const isAdjustable = el.getAttribute('data-adjustable') === '1';

                if (stage === 5 || isAdjustable) {
                    const isWorking = container && container.getAttribute('data-is-working') === '1';
                    if (isWorking) {
                        if (typeof showErrorMessage === 'function') {
                            showErrorMessage('Tidak dapat melakukan ganti jam karena Anda sedang aktif dalam shift kerja hari ini. Anda harus belum absen masuk shift kerja atau menyelesaikan shift kerja terlebih dahulu.');
                        } else {
                            alert('Tidak dapat melakukan ganti jam karena Anda sedang aktif dalam shift kerja hari ini. Anda harus belum absen masuk shift kerja atau menyelesaikan shift kerja terlebih dahulu.');
                        }
                        return false;
                    }
                }

                const absenceId = parseInt(el.getAttribute('data-absence-id'), 10) || 0;
                const adjustableId = parseInt(el.getAttribute('data-adjustable-id'), 10) || 0;

                showModal(gps, userId, stage, isAdjustable, absenceId, adjustableId);
                return false;
            }

            function handleLeavePermitClick(event) {
                if (event) event.preventDefault();
                const container = document.getElementById('prayer-container');
                const isLimitReached = container ? container.getAttribute('data-leave-limit-reached') === '1' : false;
                if (isLimitReached) {
                    showPermitLimitAlert('leave');
                } else {
                    openLeavePermitModal();
                }
            }

            function handlePrayerPermitClick(event) {
                if (event) event.preventDefault();
                const container = document.getElementById('prayer-container');
                const isLimitReached = container ? container.getAttribute('data-prayer-limit-reached') === '1' : false;
                if (isLimitReached) {
                    showPermitLimitAlert('prayer');
                } else {
                    submitPermit('prayer');
                }
            }

            function handleLogbookClick(event) {
                if (event) event.preventDefault();
                const container = document.getElementById('prayer-container');
                if (container && container.getAttribute('data-active-permit') === '1') return false;
                window.location.href = "{{ route('user.logbook.index') }}";
            }

            function handleHolidayInfoClick(event) {
                if (event) event.preventDefault();
                const container = document.getElementById('prayer-container');
                if (container && container.getAttribute('data-active-permit') === '1') return false;
                openModal();
            }

            function handleDivisionAccountClick(event) {
                if (event) event.preventDefault();
                const container = document.getElementById('prayer-container');
                if (container && container.getAttribute('data-active-permit') === '1') return false;
                window.location.href = "{{ route('user.tasks.index') }}";
            }

            function handleBroadcastListClick(event) {
                if (event) event.preventDefault();
                const container = document.getElementById('prayer-container');
                if (container && container.getAttribute('data-active-permit') === '1') return false;
                openModalBroadcastList();
            }

            function handleRaiseHandClick(event) {
                if (event) event.preventDefault();
                const container = document.getElementById('prayer-container');
                if (container && container.getAttribute('data-active-permit') === '1') return false;
                if (typeof openRaiseHandModal === 'function') {
                    openRaiseHandModal();
                }
            }

            function handleLowerHandClick(event) {
                if (event) event.preventDefault();
                const container = document.getElementById('prayer-container');
                if (container && container.getAttribute('data-active-permit') === '1') return false;
                if (typeof window.Livewire !== 'undefined') {
                    window.Livewire.dispatch('open-status-bantuan-modal');
                } else if (typeof openLowerHandModal === 'function') {
                    openLowerHandModal();
                }
            }

            function openChangeTimeModal() {
                const m = document.getElementById('changeTimeModal');
                if (m) {
                    m.classList.remove('hidden');
                    m.classList.add('flex');
                }
            }

            function closeChangeTimeModal() {
                const m = document.getElementById('changeTimeModal');
                if (m) {
                    m.classList.add('hidden');
                    m.classList.remove('flex');
                }
                const dropMenu = document.getElementById('shift-dropdown-menu');
                if (dropMenu) dropMenu.classList.add('hidden');
            }

            function openLeavePermitModal() {
                document.getElementById('leavePermitModal').classList.remove('hidden');
            }

            function closeLeavePermitModal() {
                document.getElementById('leavePermitModal').classList.add('hidden');
            }

            function closePermitLimitModal() {
                document.getElementById('permitLimitModal').classList.add('hidden');
            }

            function openShiftNotSetModal() {
                document.getElementById('shiftNotSetModal').classList.remove('hidden');
            }

            function closeShiftNotSetModal() {
                document.getElementById('shiftNotSetModal').classList.add('hidden');
            }

            function showPermitLimitAlert(type) {
                const modal = document.getElementById('permitLimitModal');
                const title = document.getElementById('permitLimitTitle');
                const message = document.getElementById('permitLimitMessage');

                const permitTypes = {
                    'prayer': {
                        title: 'Batas Izin Shalat Tercapai',
                        message: 'Anda telah mencapai batas maksimum izin shalat untuk hari ini.'
                    },
                    'leave': {
                        title: 'Batas Izin Keluar Tercapai',
                        message: 'Anda telah mencapai batas maksimum izin keluar untuk hari ini.'
                    }
                };

                if (permitTypes[type]) {
                    title.textContent = permitTypes[type].title;
                    message.textContent = permitTypes[type].message;
                    modal.classList.remove('hidden');
                }
            }

            function submitPermit(type) {
                const container = document.getElementById('prayer-container');
                if (container && container.getAttribute('data-active-permit') === '1') {
                    return false;
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = (container && container.getAttribute('data-permit-start-url')) || '{{ route("user.permit.start") }}';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = (container && container.getAttribute('data-csrf-token')) || '{{ csrf_token() }}';

                const permitType = document.createElement('input');
                permitType.type = 'hidden';
                permitType.name = 'type';
                permitType.value = type;

                form.appendChild(csrfToken);
                form.appendChild(permitType);
                document.body.appendChild(form);
                form.submit();
            }

            function handleLockedGantiJamPulang(btn) {
                const msg = (btn && btn.dataset.msg) ? btn.dataset.msg : 'Belum dapat presensi pulang ganti jam.';
                if (typeof showErrorMessage === 'function') {
                    showErrorMessage(msg);
                } else {
                    alert(msg);
                }
            }

            // Pastikan modal tertutup saat halaman dimuat
            document.addEventListener('DOMContentLoaded', function() {
                closeLeavePermitModal();
                closePermitLimitModal();
                closeShiftNotSetModal();
            });

            // Bunyikan nada notifikasi halus saat ada tanggapan/chat baru dari mentor
            window.addEventListener('play-chat-notification', function() {
                try {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) return;
                    const ctx = new AudioCtx();
                    if (ctx.state === 'suspended') {
                        ctx.resume().catch(() => {});
                    }
                    const now = ctx.currentTime;
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(587.33, now); // D5
                    osc.frequency.setValueAtTime(880, now + 0.08); // A5
                    gain.gain.setValueAtTime(0.22, now);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.25);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(now);
                    osc.stop(now + 0.25);
                } catch (e) {}
            });
        </script>
</div>