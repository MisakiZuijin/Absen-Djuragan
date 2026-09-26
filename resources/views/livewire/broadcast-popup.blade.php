<div wire:poll.15s="checkForBroadcasts">
    @if(session('broadcast_success_msg'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
            class="fixed top-5 right-5 z-[100000] bg-emerald-600 text-white px-5 py-3 rounded-2xl shadow-xl flex items-center gap-3 text-sm animate-fadeIn">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span>{{ session('broadcast_success_msg') }}</span>
        </div>
    @endif

    @if($isOpen && $currentBroadcast)
        <div class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-4 overflow-y-auto animate-fadeIn">
            <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] shadow-2xl overflow-hidden flex flex-col my-auto border border-gray-100 transform transition-all">
                
                <!-- HEADER MODAL (Model Desain Lama / Klasik Pengumuman) -->
                <div class="flex justify-between items-center px-4 sm:px-6 py-3.5 sm:py-4 border-b bg-gradient-to-r from-blue-600 to-indigo-600 text-white shrink-0">
                    <div class="flex items-center gap-2.5 min-w-0 pr-2">
                        <i class="fa-solid fa-bullhorn text-base sm:text-lg shrink-0"></i>
                        <h3 class="font-bold text-sm sm:text-base truncate max-w-[180px] sm:max-w-md">{{ $currentBroadcast->title }}</h3>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[10px] sm:text-[11px] font-semibold bg-white/20 px-2 sm:px-2.5 py-1 rounded-full text-white shrink-0">
                        <i class="fa-solid fa-pen-nib text-[9px] sm:text-[10px]"></i> Wajib Diisi
                    </span>
                </div>

                <!-- BODY MODAL -->
                <div class="p-4 sm:p-6 overflow-y-auto space-y-3.5 sm:space-y-4 flex-1">
                    <!-- Lampiran Gambar (Jika ada) -->
                    @if($currentBroadcast->images && $currentBroadcast->images->isNotEmpty())
                        <div class="flex justify-center">
                            @foreach($currentBroadcast->images as $img)
                                <div class="w-full max-w-lg bg-slate-900/5 p-2 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-center overflow-hidden">
                                    <a href="{{ asset('broadcast-image/' . $img->image) }}" target="_blank" class="block w-full text-center group relative overflow-hidden rounded-xl" title="Klik untuk membuka ukuran penuh">
                                        <img src="{{ asset('broadcast-image/' . $img->image) }}" 
                                             alt="Lampiran Pengumuman" 
                                             class="max-h-64 sm:max-h-72 w-auto max-w-full mx-auto object-contain rounded-xl transition-transform duration-300 group-hover:scale-[1.02]">
                                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-semibold gap-1.5 backdrop-blur-[1px] rounded-xl">
                                            <i class="fa-solid fa-magnifying-glass-plus text-sm"></i>
                                            <span>Klik untuk Perbesar</span>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Isi Pengumuman / Pesan -->
                    <div class="text-gray-700 leading-relaxed text-justify whitespace-pre-line text-xs sm:text-sm bg-gray-50/70 p-3.5 sm:p-4 rounded-xl border border-gray-100">
                        {!! nl2br(e($currentBroadcast->message)) !!}
                    </div>

                    <!-- FORM TANGGAPAN WAJIB PEMAGANG -->
                    <div class="border-t border-gray-100 pt-3.5 sm:pt-4 space-y-2 sm:space-y-2.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                            <i class="fa-solid fa-clipboard-check text-blue-600 mr-1"></i>
                            {{ $currentBroadcast->report_question ?: 'Tuliskan tanggapan / konfirmasi Anda di sini:' }}
                            <span class="text-rose-500">*</span>
                        </label>
                        
                        <textarea wire:model="reportText" rows="3" 
                            class="w-full px-3.5 sm:px-4 py-2 sm:py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm outline-none resize-none leading-relaxed bg-white"
                            placeholder="Ketik tanggapan, konfirmasi, atau jawaban Anda di sini (wajib diisi)..."></textarea>
                        
                        @error('reportText')
                            <p class="text-xs text-rose-600 font-semibold flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- FOOTER MODAL (Model Desain Lama) -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-2.5 sm:gap-0 px-4 sm:px-6 py-3 sm:py-3.5 border-t bg-gray-50 shrink-0">
                    <span class="text-[11px] sm:text-xs text-gray-400 text-center sm:text-left">
                        @if($currentBroadcast->created_at)
                            Diterbitkan: {{ $currentBroadcast->created_at->translatedFormat('l, d F Y H:i') }}
                        @endif
                    </span>
                    <button type="button" wire:click="submitReport" wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-2 disabled:opacity-50">
                        <span wire:loading.remove wire:target="submitReport">
                            <i class="fa-solid fa-paper-plane mr-1"></i> Kirim Tanggapan
                        </span>
                        <span wire:loading wire:target="submitReport" class="flex items-center gap-2">
                            <i class="fa-solid fa-spinner fa-spin"></i> Mengirim...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
