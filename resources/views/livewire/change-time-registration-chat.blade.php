<div @if(isset($pollInterval) && $pollInterval > 0) wire:poll.{{ $pollInterval }}s @endif class="w-full">
    @if(isset($registrationData) && $registrationData)
    <!-- ==================================================================== -->
    <!-- BANNER STATUS PRA-PENDAFTARAN GANTI JAM AKTIF                        -->
    <!-- ==================================================================== -->
    <div class="w-full bg-white border-2 border-orange-500 rounded-2xl p-3.5 sm:p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-start sm:items-center gap-3 min-w-0">
            <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center shrink-0 border border-orange-200">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h4 class="font-bold text-slate-900 text-xs sm:text-sm">Rencana Ganti Jam</h4>
                    @if($registrationData['status'] === 'approved')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i>
                        <span>Disetujui — Siap Dilaksanakan</span>
                    </span>
                    @elseif($registrationData['status'] === 'rejected')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs">
                        <i class="fa-solid fa-circle-xmark text-rose-600 text-xs"></i>
                        <span>Ditolak Admin</span>
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-900 border border-amber-200 shadow-2xs">
                        <i class="fa-solid fa-clock text-amber-600 text-xs"></i>
                        <span>Menunggu Persetujuan Admin</span>
                    </span>
                    @endif

                    @if(!empty($unreadCount) && $unreadCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500 text-white animate-pulse">
                        {{ $unreadCount }} Pesan Baru
                    </span>
                    @endif
                </div>
                <div class="text-xs text-slate-700 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                    @if(!empty($registrationData['requested_date_formatted']))
                    <span><i class="fa-regular fa-calendar text-orange-600 mr-1"></i><strong>Hari/Tanggal:</strong> {{ $registrationData['requested_date_formatted'] }}</span>
                    @endif
                    @if(!empty($registrationData['shift_name']))
                    <span><i class="fa-regular fa-clock text-orange-600 mr-1"></i><strong>Shift:</strong> {{ $registrationData['shift_name'] }} ({{ $registrationData['shift_time'] }})</span>
                    @endif
                    @if(!empty($registrationData['office_name']))
                    <span><i class="fa-regular fa-building text-orange-600 mr-1"></i><strong>Kantor:</strong> {{ $registrationData['office_name'] }}</span>
                    @endif
                </div>
                @if(!empty($registrationData['reason']))
                <p class="text-xs text-slate-600 mt-0.5 truncate max-w-xl">
                    Catatan: <em>"{{ $registrationData['reason'] }}"</em>
                </p>
                @endif
                @if(!empty($registrationData['admin_notes']) && $registrationData['status'] === 'rejected')
                <p class="text-xs text-rose-700 font-medium mt-0.5">
                    Alasan Penolakan: <em>"{{ $registrationData['admin_notes'] }}"</em>
                </p>
                @endif
            </div>
        </div>

        @if($registrationData['status'] !== 'approved')
        <div class="flex items-center gap-2 shrink-0">
            <button type="button" wire:click="openModal"
                onclick="if(window.internChatSoundManager) window.internChatSoundManager.setUnreadStatus('reg', false);"
                class="px-3.5 py-1.5 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-xl text-xs transition shadow-2xs cursor-pointer flex items-center gap-1.5"
                style="background-color: #ea580c !important;">
                <i class="fa-solid fa-comments text-xs"></i>
                <span>Diskusi / Detail</span>
                @if(!empty($unreadCount) && $unreadCount > 0)
                <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                @endif
            </button>
            @if($registrationData['status'] === 'pending')
            <button type="button" wire:click="cancelRegistration"
                wire:confirm="Batalkan pendaftaran rencana ganti jam ini?"
                class="px-3.5 py-1.5 bg-white hover:bg-rose-50 text-rose-600 font-bold border border-rose-200 rounded-xl text-xs transition shadow-2xs cursor-pointer">
                <i class="fa-solid fa-xmark mr-1"></i> Batalkan
            </button>
            @endif
        </div>
        @endif
    </div>

    <!-- ==================================================================== -->
    <!-- MODAL POPUP DISKUSI GANTI JAM REGISTRASI                             -->
    <!-- ==================================================================== -->
    @if($isOpen)
    <div class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm z-[9999] p-3 sm:p-4"
        wire:click.self="closeModal"
        onclick="if(window.internChatSoundManager) window.internChatSoundManager.setUnreadStatus('reg', false);">
        <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-2xl w-full max-w-lg mx-auto relative animate-fade-in max-h-[92vh] flex flex-col"
            @click.stop>
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between gap-3 mb-3.5 sm:mb-4 shrink-0 pr-8 border-b border-gray-100 pb-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="p-2 sm:p-2.5 bg-amber-100 text-amber-700 rounded-xl shrink-0">
                        <i class="fa-solid fa-clock-rotate-left text-base sm:text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-base sm:text-lg font-bold text-slate-800 truncate">Pendaftaran Ganti Jam Aktif</h2>
                        <p class="text-xs text-slate-500 truncate">Komunikasi dua arah dengan Admin.</p>
                    </div>
                </div>
            </div>

            <!-- Tombol Close (X) -->
            <button type="button" wire:click="closeModal" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer">
                <i class="fas fa-times text-base sm:text-lg"></i>
            </button>

            <!-- Scrollable Body -->
            <div class="overflow-y-auto flex-1 px-1 py-1 space-y-3 text-xs no-scrollbar">
                <!-- Rincian Jadwal & Catatan Pemagang -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                        <div class="flex items-center gap-1.5 font-bold text-slate-800">
                            <i class="fa-regular fa-calendar-days text-amber-600"></i>
                            <span>{{ $registrationData['requested_date_formatted'] ?? 'Belum ditentukan' }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 font-semibold text-blue-800">
                            <i class="fa-solid fa-business-time text-blue-600"></i>
                            <span>{{ $registrationData['shift_name'] ?? 'Bebas Shift' }} ({{ $registrationData['shift_time'] }})</span>
                        </div>
                        @if(!empty($registrationData['office_name']))
                        <div class="flex items-center gap-1.5 font-semibold text-slate-700">
                            <i class="fa-regular fa-building text-slate-500"></i>
                            <span>{{ $registrationData['office_name'] }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="pt-2 border-t border-slate-200/80">
                        <div class="flex items-center justify-between text-slate-500 text-[11px] mb-1">
                            <span class="font-semibold text-slate-700">Catatan Pengajuan:</span>
                            <span>{{ $registrationData['created_at_formatted'] }}</span>
                        </div>
                        <p class="text-slate-800 text-xs italic bg-white p-2.5 rounded-lg border border-slate-200">
                            "{{ $registrationData['reason'] ?: 'Tidak ada catatan.' }}"
                        </p>
                    </div>
                </div>

                <!-- Thread Percakapan / Pesan -->
                <div class="space-y-2 pt-1">
                    <div class="flex items-center justify-between text-slate-700 font-bold text-xs">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-comments text-amber-600"></i>
                            Percakapan / Catatan Admin
                        </span>
                        <span class="text-[11px] font-normal text-slate-400">
                            {{ count($notes) }} pesan
                        </span>
                    </div>

                    <div id="chatRegThread" class="space-y-2 max-h-52 overflow-y-auto p-2.5 bg-slate-100/70 rounded-xl border border-slate-200/80 scroll-smooth">
                        @forelse($notes as $n)
                            @if($n['is_from_admin'])
                            <!-- Pesan dari Admin (Kiri) -->
                            <div class="flex items-start gap-2 max-w-[85%]">
                                <div class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[9px] shrink-0 font-bold uppercase tracking-wider" title="{{ $n['sender_name'] }}">
                                    {{ $n['initials'] ?? 'AD' }}
                                </div>
                                <div class="bg-white p-2.5 rounded-2xl rounded-tl-none border border-slate-200 shadow-2xs space-y-1">
                                    <div class="flex items-center justify-between gap-2 text-[10px] text-slate-500 font-medium">
                                        <span class="font-bold text-indigo-700">{{ $n['sender_name'] }}</span>
                                        <span>{{ $n['time'] }}</span>
                                    </div>
                                    <p class="text-xs text-slate-800 whitespace-pre-line leading-relaxed">{{ $n['message'] }}</p>
                                </div>
                            </div>
                            @else
                            <!-- Pesan dari Pemagang (Kanan) -->
                            <div class="flex items-start gap-2 max-w-[85%] ml-auto flex-row-reverse">
                                <div class="w-6 h-6 rounded-full bg-orange-600 text-white flex items-center justify-center text-[9px] shrink-0 font-bold uppercase tracking-wider" title="Anda">
                                    {{ $n['initials'] ?? 'ME' }}
                                </div>
                                <div class="bg-orange-500 text-white p-2.5 rounded-2xl rounded-tr-none shadow-2xs space-y-1" style="background-color: #ea580c !important;">
                                    <div class="flex items-center justify-between gap-2 text-[10px] text-orange-100 font-medium">
                                        <span class="font-bold">{{ $n['sender_name'] }}</span>
                                        <span>{{ $n['time'] }}</span>
                                    </div>
                                    <p class="text-xs text-white whitespace-pre-line leading-relaxed">{{ $n['message'] }}</p>
                                </div>
                            </div>
                            @endif
                        @empty
                            <div class="text-center py-4 text-slate-400 text-xs italic">
                                Belum ada percakapan. Jika Admin mengirim pertanyaan atau catatan, pesan akan muncul di sini.
                            </div>
                        @endforelse
                    </div>

                    <!-- Form Balasan Pemagang -->
                    <div class="space-y-2 pt-1.5">
                        <div class="relative">
                            <textarea wire:model="message"
                                wire:keydown.enter.prevent="sendReply"
                                rows="2"
                                placeholder="Tulis balasan atau pertanyaan untuk Admin..."
                                class="w-full p-2.5 text-xs text-slate-800 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white leading-relaxed resize-none"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" wire:click="sendReply"
                                style="background-color: #ea580c !important;"
                                class="px-4 py-1.5 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-xl text-xs transition shadow-2xs flex items-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-paper-plane text-[10px]"></i>
                                <span>Kirim Pesan</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between gap-2 pt-3 sm:pt-4 border-t border-gray-100 mt-2 shrink-0">
                @if($registrationData['status'] === 'pending')
                <button type="button" wire:click="cancelRegistration"
                    wire:confirm="Apakah Anda yakin ingin membatalkan pengajuan ganti jam ini?"
                    class="px-3.5 py-2 border border-rose-300 text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                    <span>Batalkan Pendaftaran</span>
                </button>
                @else
                <div></div>
                @endif
                <button type="button" wire:click="closeModal"
                    onclick="if(window.internChatSoundManager) window.internChatSoundManager.setUnreadStatus('reg', false);"
                    class="px-4 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-100 rounded-xl text-xs font-semibold transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endif
    @endif

    <!-- ==================================================================== -->
    <!-- POPUP PEMBERITAHUAN: PENDAFTARAN DITOLAK / SESI DIBERHENTIKAN        -->
    <!-- ==================================================================== -->
    @if(!empty($showNotificationModal))
    <div class="fixed inset-0 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs z-[10001] p-4 animate-in fade-in duration-150"
        @click.stop>
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md mx-auto p-5 sm:p-6 text-center relative border border-slate-100 animate-in zoom-in-95 duration-150">
            <!-- Icon Bulat -->
            <div class="mx-auto w-14 h-14 sm:w-16 sm:h-16 rounded-2xl flex items-center justify-center mb-3 sm:mb-4 shadow-sm {{ $notificationModalType === 'session_stopped' ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-700' }}">
                @if($notificationModalType === 'session_stopped')
                    <i class="fa-solid fa-ban text-2xl sm:text-3xl"></i>
                @else
                    <i class="fa-solid fa-circle-xmark text-2xl sm:text-3xl"></i>
                @endif
            </div>

            <!-- Judul & Subjudul -->
            <h3 class="text-base sm:text-lg font-bold text-slate-900 mb-1">
                {{ $notificationModalTitle }}
            </h3>
            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                {{ $notificationModalSubtitle }}
            </p>

            <!-- Kotak Alasan Admin -->
            <div class="bg-rose-50 border border-rose-200/80 rounded-2xl p-3.5 sm:p-4 text-left mb-4">
                <span class="text-[11px] font-bold text-rose-900 uppercase tracking-wider block mb-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-comment-dots text-rose-600"></i>
                    <span>Alasan dari Admin:</span>
                </span>
                <p class="text-xs text-rose-800 italic leading-relaxed break-words font-medium">
                    "{{ $notificationModalReason }}"
                </p>
            </div>

            <!-- Catatan Tambahan -->
            <p class="text-[11px] text-slate-400 mb-5">
                {{ $notificationModalNote }}
            </p>

            <!-- Tombol Saya Mengerti -->
            <button type="button"
                wire:click="dismissNotificationModal"
                class="w-full py-2.5 sm:py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs sm:text-sm transition shadow-sm cursor-pointer flex items-center justify-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Saya Mengerti</span>
            </button>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('chat-reg-scroll-bottom', () => {
                setTimeout(() => {
                    const el = document.getElementById('chatRegThread');
                    if (el) el.scrollTop = el.scrollHeight;
                }, 80);
            });

            Livewire.on('reload-page', () => {
                window.location.reload();
            });
        });
    </script>
</div>
