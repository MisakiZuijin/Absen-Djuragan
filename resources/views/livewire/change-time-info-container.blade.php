<div @if(isset($pollInterval) && $pollInterval > 0) wire:poll.{{ $pollInterval }}s @endif class="w-full">
    @if($hasActiveSession || $hasPendingSession)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/90 overflow-hidden transition-all">

        <!-- 1. Card Header Banner (Clean White with Dark/Black Text) -->
        <div class="bg-white px-4 sm:px-6 py-3.5 border-b border-slate-200">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-orange-100 text-orange-700 flex items-center justify-center shrink-0 border border-orange-200 shadow-2xs">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Sesi Ganti Jam Kerja</h2>
                            @if($hasActiveSession)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-300 shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                                <span>Aktif Berjalan</span>
                            </span>
                            @elseif($hasPendingSession)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Pending</span>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tombol Buka Diskusi Admin -->
                <button type="button"
                    wire:click="openChatModal"
                    onclick="if(window.internChatSoundManager) window.internChatSoundManager.setUnreadStatus('session', false);"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-orange-50 hover:bg-orange-600 text-orange-700 hover:text-white border border-orange-200 hover:border-orange-600 text-xs font-bold transition shadow-2xs cursor-pointer relative shrink-0"
                    title="Buka Chat / Diskusi Ganti Jam dengan Admin">
                    <i class="fa-solid fa-comments text-xs"></i>
                    <span>Pesan Admin</span>
                    @if(!empty($unreadNotesCount) && $unreadNotesCount > 0)
                    <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-rose-500 rounded-full border-2 border-white animate-pulse"></span>
                    @endif
                </button>
            </div>
        </div>

        <!-- 3. Attendance Status Boxes -->
        <div class="p-4 sm:p-6 space-y-5">
            @if(!empty($internNoticeText))
            <div class="p-3 sm:p-3.5 rounded-xl bg-amber-50/80 border border-amber-200 text-amber-900 text-xs flex items-start gap-2.5">
                <i class="fa-solid fa-circle-info text-amber-600 mt-0.5 shrink-0 text-xs"></i>
                <div class="min-w-0 text-[11px] sm:text-xs text-amber-900 leading-relaxed">
                    <span class="font-bold text-amber-950">Catatan:</span> {{ $internNoticeText }}
                </div>
            </div>
            @endif

            <div class="grid {{ !empty($isBreakHidden) ? 'grid-cols-2' : 'grid-cols-2 lg:grid-cols-4' }} gap-3">
                <!-- 1. Masuk Ganti Jam -->
                <div class="p-3.5 sm:p-4 rounded-2xl border-2 bg-white transition-all flex flex-col items-center justify-center text-center {{ !empty($sessionData['start_time']) ? 'border-emerald-500 shadow-2xs' : 'border-slate-200' }}">
                    <div class="flex items-center justify-center gap-1.5 mb-1.5 w-full">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-900">Masuk</span>
                        @if(!empty($sessionData['start_time']))
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        @else
                        <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                        @endif
                    </div>
                    <div class="text-lg sm:text-xl font-bold font-mono tracking-tight text-slate-900">
                        {{ $sessionData['start_time'] ?? '--:--' }}
                    </div>
                </div>

                @if(empty($isBreakHidden))
                <!-- 2. Istirahat Ganti Jam -->
                <div class="p-3.5 sm:p-4 rounded-2xl border-2 bg-white transition-all flex flex-col items-center justify-center text-center {{ !empty($sessionData['break_time']) ? 'border-emerald-500 shadow-2xs' : 'border-slate-200' }}">
                    <div class="flex items-center justify-center gap-1.5 mb-1.5 w-full">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-900">Istirahat</span>
                        @if(!empty($sessionData['break_time']))
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        @else
                        <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                        @endif
                    </div>
                    <div class="text-lg sm:text-xl font-bold font-mono tracking-tight text-slate-900">
                        {{ $sessionData['break_time'] ?? '--:--' }}
                    </div>
                </div>

                <!-- 3. Kembali Istirahat -->
                <div class="p-3.5 sm:p-4 rounded-2xl border-2 bg-white transition-all flex flex-col items-center justify-center text-center {{ !empty($sessionData['back_time']) ? 'border-emerald-500 shadow-2xs' : 'border-slate-200' }}">
                    <div class="flex items-center justify-center gap-1.5 mb-1.5 w-full">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-900">Kembali</span>
                        @if(!empty($sessionData['back_time']))
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        @else
                        <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                        @endif
                    </div>
                    <div class="text-lg sm:text-xl font-bold font-mono tracking-tight text-slate-900">
                        {{ $sessionData['back_time'] ?? '--:--' }}
                    </div>
                </div>
                @endif

                <!-- 4. Pulang Ganti Jam -->
                <div class="p-3.5 sm:p-4 rounded-2xl border-2 bg-white transition-all flex flex-col items-center justify-center text-center {{ !empty($sessionData['end_time']) ? 'border-emerald-500 shadow-2xs' : 'border-slate-200' }}">
                    <div class="flex items-center justify-center gap-1.5 mb-1.5 w-full">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-900">Pulang</span>
                        @if(!empty($sessionData['end_time']))
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        @else
                        <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                        @endif
                    </div>
                    <div class="text-lg sm:text-xl font-bold font-mono tracking-tight text-slate-900">
                        {{ $sessionData['end_time'] ?? '--:--' }}
                    </div>
                </div>
            </div>

            <!-- 4. Progress Bar Durasi Kerja -->
            <div class="bg-slate-50 p-3.5 sm:p-4 rounded-2xl border border-slate-200 space-y-2.5 shadow-2xs">
                <div class="flex items-center justify-between gap-3 text-xs sm:text-sm">
                    <div class="flex items-center gap-2 flex-wrap min-w-0">
                        <svg class="w-4 h-4 text-orange-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-bold text-slate-900">Progres Durasi Kerja:</span>
                        <div class="inline-flex items-center gap-1 font-mono">
                            <span class="font-bold text-orange-700 text-sm sm:text-base">
                                {{ sprintf('%02d:%02d', floor($workedMinutes / 60), $workedMinutes % 60) }}
                            </span>
                            <span class="text-slate-600 font-bold text-xs">/ {{ sprintf('%02d:%02d', floor($targetDebtMinutes / 60), $targetDebtMinutes % 60) }}</span>
                        </div>
                        @if($isOnBreak ?? false)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                            <i class="fas fa-pause text-[9px]"></i>
                            <span>Sedang Istirahat (Dijeda)</span>
                        </span>
                        @endif
                    </div>
                    <div class="font-bold text-slate-900 font-mono text-sm sm:text-base shrink-0">
                        {{ $progressPercentage }}%
                    </div>
                </div>

                <!-- Progress Bar Track -->
                <div class="w-full bg-slate-200/90 rounded-full h-3.5 sm:h-4 overflow-hidden border border-slate-300 p-0.5 relative">
                    <div class="h-full rounded-full transition-all duration-500 block"
                        {!! $barStyleAttr ?? 'style="width: 0%;"' !!}>
                    </div>
                </div>

                @if($hasPendingSession)
                <div class="pt-1">
                    <div class="p-2.5 sm:p-3 rounded-xl bg-amber-50 border border-amber-300 text-slate-900 font-semibold text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Sesi selesai & diajukan. Jadwal dan absensi target akan otomatis dilunaskan setelah Admin menyetujui.</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- POPUP MODAL: TANYA JAWAB SESI GANTI JAM (PEMAGANG)           --}}
    {{-- ============================================================ --}}
    @if($showChatModal)
    <div class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-xs z-[10000] p-2.5 pb-7 sm:p-4 animate-in fade-in duration-150"
        wire:click.self="closeChatModal"
        onclick="if(window.internChatSoundManager) window.internChatSoundManager.setUnreadStatus('session', false);">
        <div class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-full max-w-lg mx-auto relative flex flex-col max-h-[85vh] sm:max-h-[88vh] mb-2 sm:mb-0 overflow-hidden border border-slate-100 animate-in zoom-in-95 duration-150">

            <!-- Header -->
            <div class="p-3 sm:p-4.5 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/70">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 pr-2">
                    <div class="p-1.5 sm:p-2.5 bg-orange-100 text-orange-600 rounded-xl shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-.856-.856 5.97 5.97 0 01.405-2.035C3.398 16.58 3 14.39 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 truncate">Tanya Jawab Sesi Ganti Jam</h3>
                        <p class="text-[11px] sm:text-xs text-slate-500 truncate">Komunikasi dua arah dengan Admin saat sesi berjalan.</p>
                    </div>
                </div>
                <button type="button" wire:click="closeChatModal"
                    onclick="if(window.internChatSoundManager) window.internChatSoundManager.setUnreadStatus('session', false);"
                    class="text-slate-400 hover:text-slate-600 transition p-1.5 rounded-lg hover:bg-slate-200/50 cursor-pointer shrink-0" title="Tutup">
                    <i class="fa-solid fa-xmark text-base sm:text-lg"></i>
                </button>
            </div>

            <!-- Info Sesi / Catatan Awal Pemagang -->
            @if(!empty($sessionData['start_time_message']))
            <div class="px-3.5 sm:px-5 py-2 sm:py-2.5 shrink-0 bg-orange-50/50 border-b border-orange-100">
                <div class="text-[11px] font-bold text-orange-950 flex items-center justify-between mb-1">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-regular fa-note-sticky text-orange-600"></i>
                        <span>Keterangan Awal Masuk Anda:</span>
                    </span>
                    <span class="text-[10px] text-orange-700 font-normal shrink-0 ml-2">{{ !empty($sessionData['start_time']) ? \Carbon\Carbon::parse($sessionData['start_time'])->format('H:i') : '-' }}</span>
                </div>
                <p class="text-xs text-slate-700 italic bg-white/90 p-2 sm:p-2.5 rounded-xl border border-orange-200/70 max-h-20 sm:max-h-28 overflow-y-auto whitespace-pre-line break-words leading-relaxed">"{{ trim($sessionData['start_time_message']) }}"</p>
            </div>
            @endif

            <!-- Chat History List -->
            <div id="intern-chat-messages"
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
                @chat-scroll-bottom.window="scrollToBottom()"
                class="flex-1 overflow-y-auto p-3.5 sm:p-5 space-y-2.5 sm:space-y-3 min-h-[120px] max-h-[40vh] sm:max-h-[320px] bg-slate-50/30">
                @forelse($chatNotesHistory as $note)
                @if($note['is_from_admin'])
                {{-- Admin bubble on the left --}}
                <div class="flex flex-col items-start animate-in fade-in duration-150">
                    <div class="bg-white text-slate-800 rounded-2xl rounded-tl-xs px-3 sm:px-3.5 py-1.5 sm:py-2 max-w-[88%] sm:max-w-[82%] text-xs border border-slate-200/80 shadow-2xs">
                        <div class="font-bold text-[10px] text-orange-600 mb-0.5">{{ $note['sender_name'] }} (Admin)</div>
                        <div class="whitespace-pre-wrap leading-relaxed break-words">{{ $note['message'] }}</div>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-0.5">{{ $note['time'] }}</span>
                </div>
                @else
                {{-- Intern bubble on the right (orange #ea580c) --}}
                <div class="flex flex-col items-end animate-in fade-in duration-150">
                    <div style="background-color: #ea580c !important; color: #ffffff !important;" class="bg-orange-600 text-white rounded-2xl rounded-tr-xs px-3 sm:px-3.5 py-1.5 sm:py-2 max-w-[88%] sm:max-w-[82%] text-xs shadow-2xs">
                        <div class="font-bold text-[10px] text-orange-100 mb-0.5">{{ $note['sender_name'] }}</div>
                        <div class="whitespace-pre-wrap leading-relaxed break-words">{{ $note['message'] }}</div>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-0.5">{{ $note['time'] }}</span>
                </div>
                @endif
                @empty
                <div class="py-8 text-center text-slate-400">
                    <i class="fa-regular fa-comments text-3xl mb-2 text-slate-300 block"></i>
                    <span class="text-xs font-medium">Belum ada riwayat diskusi. Tulis pesan di bawah untuk menghubungi Admin.</span>
                </div>
                @endforelse
            </div>

            <!-- Form Kirim Pesan -->
            <div class="p-3 sm:p-4.5 pb-4 sm:pb-4.5 border-t border-slate-200/80 bg-white shrink-0">
                <form wire:submit.prevent="sendChatMessage">
                    <div class="space-y-2 sm:space-y-2.5">
                        <label for="chat_intern_reply_input" class="block font-bold text-slate-700 text-[11px] sm:text-xs">
                            Kirim Pesan / Balasan ke Admin:
                        </label>
                        <textarea id="chat_intern_reply_input" wire:model="chatReplyInput" rows="1" required
                            @keydown.enter.exact.prevent="$wire.sendChatMessage()"
                            placeholder="Tulis pesan untuk Admin terkait ganti jam... (Enter untuk kirim)"
                            class="w-full px-2.5 py-1.5 sm:p-2.5 border border-slate-300 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-orange-500 focus:outline-none text-[11px] sm:text-xs transition leading-normal sm:leading-relaxed resize-none min-h-[36px] sm:min-h-[50px]"></textarea>

                        <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-between gap-2 pt-1 border-t border-slate-100">
                            <button type="button" wire:click="closeChatModal"
                                onclick="if(window.internChatSoundManager) window.internChatSoundManager.setUnreadStatus('session', false);"
                                class="w-full sm:w-auto px-3.5 py-1.5 sm:py-2 border border-slate-300 text-slate-600 rounded-lg sm:rounded-xl hover:bg-slate-100 text-[11px] sm:text-xs font-semibold transition cursor-pointer text-center">
                                Tutup
                            </button>
                            <div class="flex items-center gap-2 justify-end w-full sm:w-auto">
                                <button type="submit"
                                    style="background-color: #ea580c !important; color: #ffffff !important;"
                                    class="w-full sm:w-auto px-4 py-1.5 sm:py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-lg sm:rounded-xl text-[11px] sm:text-xs transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
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
    @endif

    {{-- ============================================================ --}}
    {{-- POPUP PEMBERITAHUAN: SESI GANTI JAM DIBERHENTIKAN / DITOLAK  --}}
    {{-- ============================================================ --}}
    @if(!empty($showRejectedModal))
    <div class="fixed inset-0 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs z-[10001] p-4 animate-in fade-in duration-150"
        @click.stop>
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md mx-auto p-5 sm:p-6 text-center relative border border-slate-100 animate-in zoom-in-95 duration-150">
            <!-- Icon Bulat -->
            <div class="mx-auto w-14 h-14 sm:w-16 sm:h-16 rounded-2xl flex items-center justify-center mb-3 sm:mb-4 shadow-sm bg-rose-100 text-rose-600">
                <i class="fa-solid fa-ban text-2xl sm:text-3xl"></i>
            </div>

            <!-- Judul & Subjudul -->
            <h3 class="text-base sm:text-lg font-bold text-slate-900 mb-1">
                Sesi Ganti Jam Diberhentikan
            </h3>
            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                Admin telah memberhentikan atau menolak sesi ganti jam Anda.
            </p>

            <!-- Kotak Alasan Admin -->
            <div class="bg-rose-50 border border-rose-200/80 rounded-2xl p-3.5 sm:p-4 text-left mb-4">
                <span class="text-[11px] font-bold text-rose-900 uppercase tracking-wider block mb-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-comment-dots text-rose-600"></i>
                    <span>Alasan dari Admin:</span>
                </span>
                <p class="text-xs text-rose-800 italic leading-relaxed break-words font-medium">
                    "{{ $rejectionReason }}"
                </p>
            </div>

            <!-- Catatan Tambahan -->
            <p class="text-[11px] text-slate-400 mb-5">
                Akumulasi jam kerja pada sesi ini tidak dihitung sebagai pelunasan hutang jam.
            </p>

            <!-- Tombol Saya Mengerti -->
            <button type="button"
                wire:click="dismissRejectedModal"
                class="w-full py-2.5 sm:py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs sm:text-sm transition shadow-sm cursor-pointer flex items-center justify-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Saya Mengerti</span>
            </button>
        </div>
    </div>
    @endif

    {{-- Script Sinkronisasi Notifikasi Chat Pemagang --}}
    <script>
        (function() {
            window.addEventListener('play-chat-sound', function() {
                if (window.internChatSoundManager) {
                    window.internChatSoundManager.playChime();
                } else if (typeof window.playChatNotificationSound === 'function') {
                    window.playChatNotificationSound();
                }
            });

            window.addEventListener('reload-page', function() {
                window.location.reload();
            });
        })();
    </script>
</div>