<div wire:poll.15s>
    @php
    use App\Utils\AttendanceStatus;
    $isPraying = false;
    @endphp

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
    <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/75 backdrop-blur-sm p-3 sm:p-4">
        <div class="bg-white p-5 sm:p-7 rounded-2xl sm:rounded-3xl shadow-2xl text-center w-full max-w-xs sm:max-w-sm mx-auto permit-modal max-h-[92vh] flex flex-col justify-center">
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
            @endphp
            <div class="text-blue-500 text-5xl sm:text-6xl mb-3 sm:mb-4"><i class="fas fa-{{ $icon }} permit-icon"></i></div>
            <h2 class="text-lg sm:text-2xl font-bold mb-1.5 sm:mb-2 text-gray-800">{{ $title }}</h2>
            <p class="text-gray-600 mb-3 sm:mb-4 text-xs sm:text-sm leading-relaxed max-w-xs mx-auto">{{ $description }}</p>

            <div class="mb-4 sm:mb-6 py-2 sm:py-2.5 px-4 bg-slate-100 rounded-xl inline-flex items-center justify-center gap-2 text-slate-700 border border-slate-200/80 shadow-2xs mx-auto">
                <i class="fa-regular fa-clock text-blue-600"></i>
                <span id="active-permit-live-timer" class="font-mono text-xl sm:text-2xl font-bold tracking-wider">00:00:00</span>
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
    <script>
        if (typeof permitTimerInterval === 'undefined' || permitTimerInterval === null) {
            let permitStartTime = new Date("{{ \Carbon\Carbon::parse($activePermit->start_time)->toIso8601String() }}");
            let lastPermitDuration = '00:00:00';

            function updateLivePermitTimer() {
                let now = new Date();
                let diff = now - permitStartTime;
                if (diff < 0) diff = 0;
                let hours = Math.floor(diff / (1000 * 60 * 60));
                let minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((diff % (1000 * 60)) / 1000);
                lastPermitDuration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                let timerEl = document.getElementById('active-permit-live-timer');
                if (timerEl) {
                    timerEl.textContent = lastPermitDuration;
                }
            }

            updateLivePermitTimer();
            var permitTimerInterval = setInterval(updateLivePermitTimer, 1000);

            const endForm = document.getElementById('end-permit-form');
            if (endForm) {
                endForm.addEventListener('submit', function() {
                    const notification = document.getElementById('permit-notification');
                    const durationDisplay = document.getElementById('permit-duration');
                    clearInterval(permitTimerInterval);
                    permitTimerInterval = null;
                    if (notification && durationDisplay) {
                        durationDisplay.textContent = lastPermitDuration;
                        notification.classList.remove('hidden');
                        setTimeout(() => {
                            notification.classList.add('hidden');
                        }, 5000);
                    }
                });
            }
        }
    </script>
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
            <form method="POST" action="{{ route('user.permit.start') }}" class="flex flex-col flex-1 overflow-y-auto pr-0.5">
                @csrf
                <input type="hidden" name="type" value="leave">
                <div class="mb-3.5">
                    <label for="keterangan" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Alasan Izin <span class="text-red-500">*</span></label>
                    <textarea id="keterangan" name="keterangan" rows="3" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Contoh: Mengambil barang yang tertinggal / keperluan kantor" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="authorized_by" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Diizinkan oleh <span class="text-red-500">*</span></label>
                    <select id="authorized_by" name="authorized_by" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
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
    <div class="flex flex-col {{ $isPraying ? 'prayer-mode' : '' }}"
        id="prayer-container"
        data-active-permit="{{ $activePermit ? '1' : '0' }}"
        data-has-shift="{{ $hasShift ? '1' : '0' }}"
        data-leave-limit-reached="{{ $hasReachedLeaveLimit ? '1' : '0' }}"
        data-prayer-limit-reached="{{ $hasReachedPrayerLimit ? '1' : '0' }}"
        data-permit-start-url="{{ route('user.permit.start') }}"
        data-csrf-token="{{ csrf_token() }}">

        {{-- ================================================================= --}}
        {{-- DESKTOP VIEW (md:flex) - Original Full Vertical Sidebar          --}}
        {{-- ================================================================= --}}
        <div class="hidden md:flex flex-col space-y-2.5 w-full">
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
            
            <a href="#" class="w-full text-center bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-xl text-sm py-2.5 px-3 flex items-center justify-center gap-1.5 transition-colors"
                data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::AdjustableIn->value) ? 1 : 0 }}"
                data-user-id="{{ $user->id }}"
                data-stage="{{ AttendanceStatus::AdjustableIn->value }}"
                data-adjustable="1"
                data-absence-id="{{ $absenceHistory->id ?? 0 }}"
                data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                onclick="handleAttendanceAction(event, this)">
                <i class="fas fa-clock mr-1"></i>Ganti Jam
            </a>
            <a href="#" class="w-full text-center bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-xl text-sm py-2.5 px-3 flex items-center justify-center gap-1.5 cursor-pointer transition-colors" onclick="event.preventDefault(); if (typeof window.showModalIzin === 'function') { window.showModalIzin('sakit'); } else if (typeof showModalIzin === 'function') { showModalIzin('sakit'); } else { $('#modal2').removeClass('hidden').addClass('flex'); if(typeof switchPermitType === 'function') switchPermitType('sakit'); }">
                <i class="fa-solid fa-file-medical mr-1"></i>Izin / Sakit
            </a>
            @break

            @case(AttendanceStatus::AttendanceIn)
            <!-- SUDAH MASUK - TOMBOL GANTI JAM TETAP TERSEDIA -->
            <a href="#" class="w-full text-center bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-xl text-sm py-2.5 px-4 block transition-colors"
                data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::AdjustableIn->value) ? 1 : 0 }}"
                data-user-id="{{ $user->id }}"
                data-stage="{{ AttendanceStatus::AdjustableIn->value }}"
                data-adjustable="1"
                data-absence-id="{{ $absenceHistory->id ?? 0 }}"
                data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                onclick="handleAttendanceAction(event, this)">
                <i class="fas fa-clock mr-2"></i>Ganti Jam
            </a>
            @break

            @default
            <!-- TOMBOL GANTI JAM - SELALU TERSEDIA DI SEMUA STAGE KECUALI -->
            @if(!$hasActiveAdjustable && !in_array($stage, [AttendanceStatus::AllDone, AttendanceStatus::AdjustableOut]))
            <a href="#" class="w-full text-center bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-xl text-sm py-2.5 px-4 block transition-colors"
                data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::AdjustableIn->value) ? 1 : 0 }}"
                data-user-id="{{ $user->id }}"
                data-stage="{{ AttendanceStatus::AdjustableIn->value }}"
                data-adjustable="1"
                data-absence-id="{{ $absenceHistory->id ?? 0 }}"
                data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                onclick="handleAttendanceAction(event, this)">
                <i class="fas fa-clock mr-2"></i>Ganti Jam
            </a>
            @endif

            {{-- ISTIRAHAT BIASA --}}
            @if(in_array($stage, [AttendanceStatus::ShowBreakPermitChangeTime, AttendanceStatus::ShowBreakPermit, AttendanceStatus::ShowBreakChangeTime, AttendanceStatus::StartBreak]))
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
            @if($stage === AttendanceStatus::BreakOrBack)
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
            @if($stage === AttendanceStatus::EndBreak)
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
            @if($stage === AttendanceStatus::EndBreakAdjustable)
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
            @if(in_array($stage, [AttendanceStatus::ShowBreakPermitChangeTime, AttendanceStatus::ShowBreakPermit, AttendanceStatus::ShowPermitChangeTime, AttendanceStatus::ShowBreakChangeTime, AttendanceStatus::StartBreak, AttendanceStatus::StartPermit, AttendanceStatus::AttendanceOut]))
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

            {{-- PULANG GANTI JAM --}}
            @if($stage === AttendanceStatus::AdjustableOut)
            <a href="#" class="w-full text-center bg-red-600 hover:bg-red-500 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                data-active-permit="{{ $activePermit ? 1 : 0 }}"
                data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::AdjustableOut->value) ? 1 : 0 }}"
                data-user-id="{{ $user->id }}"
                data-stage="{{ AttendanceStatus::AdjustableOut->value }}"
                data-adjustable="1"
                data-absence-id="0"
                data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                onclick="handleAttendanceAction(event, this)">
                <i class="fas fa-clock mr-2"></i>Pulang (Ganti Jam)
            </a>
            @endif

            @if($stage === AttendanceStatus::AllDone)
            <div class="w-full text-center bg-gray-400 text-white font-bold rounded-xl text-base py-3 px-4 block cursor-not-allowed">
                <i class="fas fa-check-circle mr-2"></i>Selesai
            </div>
            @endif
            @endswitch

            <!-- ACTION BUTTONS (DESKTOP VERTICAL) -->
            <!-- Logbook Harian -->
            <a href="{{ route('user.logbook.index') }}" onclick="handleLogbookClick(event)" class="w-full cursor-pointer text-center bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-xl text-sm py-2.5 px-3 flex items-center justify-center gap-1.5 transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                <i class="fa-regular fa-file-lines shrink-0"></i>
                <span class="truncate">Logbook Harian</span>
                @if($hasFilledLogToday)
                <span class="inline-block w-2 h-2 rounded-full bg-green-400 shrink-0" title="Sudah Diisi"></span>
                @else
                <span class="inline-block w-2 h-2 rounded-full bg-yellow-400 shrink-0 animate-pulse" title="Belum Diisi"></span>
                @endif
            </a>

            <!-- Info & Libur -->
            <a onclick="handleHolidayInfoClick(event)" class="w-full cursor-pointer text-center bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-xl text-sm py-2.5 px-3 flex items-center justify-center gap-1.5 transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}">
                <i class="fa-solid fa-circle-info shrink-0"></i>
                <span class="truncate">Info & Libur</span>
            </a>

            <!-- Tugas & Akun Divisi -->
            <a href="{{ route('user.tasks.index') }}" class="w-full text-center bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-xl text-sm py-2.5 px-3 flex items-center justify-center gap-1.5 transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
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
            <a onclick="handleBroadcastListClick(event)" class="w-full cursor-pointer text-center bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-xl text-sm py-2.5 px-3 flex items-center justify-center gap-1.5 transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}">
                <i class="fa-regular fa-clipboard shrink-0"></i>
                <span class="truncate">Pengumuman</span>
            </a>

            <!-- Angkat Tangan -->
            @if($isHandRaised)
            <button type="button" onclick="handleLowerHandClick(event)" class="w-full cursor-pointer text-center bg-emerald-700 hover:bg-emerald-600 text-white font-medium rounded-xl text-sm py-2.5 px-4 transition-all duration-200 flex items-center justify-center gap-1.5 {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $activePermit ? 'disabled' : '' }}>
                <i class="fas fa-hand-paper animate-bounce"></i>
                <span>Tangan Diangkat</span>
                @if($currentHandRaise && $currentHandRaise->status === 'urgent')
                <span class="inline-block w-2 h-2 rounded-full bg-red-400 animate-ping"></span>
                @endif
            </button>
            @else
            <button type="button" onclick="handleRaiseHandClick(event)" class="w-full cursor-pointer text-center bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-xl text-sm py-2.5 px-4 transition-all duration-200 flex items-center justify-center gap-1.5 {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $activePermit ? 'disabled' : '' }}>
                <i class="fas fa-hand-paper"></i>
                <span>Angkat Tangan</span>
            </button>
            @endif

            <!-- PERMIT BUTTONS (DESKTOP) -->
            @if(!$activePermit && ($hasCheckedIn || $hasActiveAdjustable) && !in_array($stage, [AttendanceStatus::AttendanceIn, AttendanceStatus::AllDone]))
            <div class="flex flex-col space-y-2 pt-2 border-t border-slate-200/80 mt-1">
                <!-- Izin Keluar -->
                <button type="button"
                    onclick="handleLeavePermitClick(event)"
                    class="w-full text-white bg-gray-700 hover:bg-gray-600 font-medium rounded-xl text-sm py-2.5 px-3 text-center transition-colors flex items-center justify-center gap-1.5 {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                    title="Izin Keluar Lingkungan Kantor">
                    <i class="fas fa-door-open text-xs"></i>
                    <span>Izin Keluar</span>
                </button>

                <!-- Izin Shalat -->
                <button type="button"
                    onclick="handlePrayerPermitClick(event)"
                    class="w-full text-white bg-gray-700 hover:bg-gray-600 font-medium rounded-xl text-sm py-2.5 px-3 text-center transition-colors flex items-center justify-center gap-1.5 {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                    title="Izin Shalat">
                    <i class="fas fa-mosque text-xs"></i>
                    <span>Izin Shalat</span>
                </button>

                <!-- Izin Toilet -->
                <button type="button"
                    onclick="event.preventDefault(); submitPermit('toilet')"
                    class="w-full text-white bg-gray-700 hover:bg-gray-600 font-medium rounded-xl text-sm py-2.5 px-3 text-center transition-colors flex items-center justify-center gap-1.5 {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                    title="Izin ke Toilet">
                    <i class="fas fa-toilet text-xs"></i>
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
                Shift {{ $shift }}
            </div>

            @if($activeLeavePermit)
            <div class="p-2 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-center justify-center gap-1.5 font-bold">
                <i class="fas fa-sign-out-alt"></i> Sedang izin keluar sejak {{ \Carbon\Carbon::parse($activeLeavePermit->start_time)->format('H:i') }}
            </div>
            @endif

            <!-- PRIMARY ATTENDANCE BUTTON (MOBILE HERO) -->
            @switch($stage)
            @case(AttendanceStatus::AttendanceAndAdjustableTime)
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
            @break

            @default
            {{-- ISTIRAHAT BIASA --}}
            @if(in_array($stage, [AttendanceStatus::ShowBreakPermitChangeTime, AttendanceStatus::ShowBreakPermit, AttendanceStatus::ShowBreakChangeTime, AttendanceStatus::StartBreak]))
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
            @if($stage === AttendanceStatus::BreakOrBack)
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
            @if($stage === AttendanceStatus::EndBreak)
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
            @if($stage === AttendanceStatus::EndBreakAdjustable)
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
            @if(in_array($stage, [AttendanceStatus::ShowBreakPermitChangeTime, AttendanceStatus::ShowBreakPermit, AttendanceStatus::ShowPermitChangeTime, AttendanceStatus::ShowBreakChangeTime, AttendanceStatus::StartBreak, AttendanceStatus::StartPermit, AttendanceStatus::AttendanceOut]))
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

            {{-- PULANG GANTI JAM --}}
            @if($stage === AttendanceStatus::AdjustableOut)
            <a href="#" class="w-full text-center bg-red-600 hover:bg-red-500 text-white font-bold rounded-xl text-base py-3 px-4 block shadow-xs transition-colors {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                data-active-permit="{{ $activePermit ? 1 : 0 }}"
                data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::AdjustableOut->value) ? 1 : 0 }}"
                data-user-id="{{ $user->id }}"
                data-stage="{{ AttendanceStatus::AdjustableOut->value }}"
                data-adjustable="1"
                data-absence-id="0"
                data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                onclick="handleAttendanceAction(event, this)">
                <i class="fas fa-clock mr-2"></i>Pulang (Ganti Jam)
            </a>
            @endif

            @if($stage === AttendanceStatus::AllDone)
            <div class="w-full text-center bg-gray-400 text-white font-bold rounded-xl text-base py-3 px-4 block cursor-not-allowed">
                <i class="fas fa-check-circle mr-2"></i>Selesai
            </div>
            @endif
            @endswitch

            <!-- GRID IKON 4 KOLOM: FITUR & PERIZINAN (HEMAT RUANG) -->
            <div class="grid grid-cols-4 gap-1.5 bg-white p-2 rounded-2xl border border-slate-200/90 shadow-2xs">
                <!-- 1. Logbook -->
                <a href="{{ route('user.logbook.index') }}" onclick="handleLogbookClick(event)"
                    class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-100 hover:border-blue-200 transition active:scale-95 text-center relative {{ $activePermit ? 'opacity-50 pointer-events-none' : '' }}" title="Logbook Harian">
                    <div class="relative">
                        <i class="fa-regular fa-file-lines text-blue-600 text-lg"></i>
                        @if($hasFilledLogToday)
                        <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-green-500 ring-2 ring-white"></span>
                        @else
                        <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-amber-400 ring-2 ring-white animate-pulse"></span>
                        @endif
                    </div>
                    <span class="text-[10px] font-semibold mt-1 truncate w-full">Logbook</span>
                </a>

                <!-- 2. Tugas -->
                <a href="{{ route('user.tasks.index') }}" onclick="handleDivisionAccountClick(event)"
                    class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-slate-50 hover:bg-purple-50 text-slate-700 hover:text-purple-700 border border-slate-100 hover:border-purple-200 transition active:scale-95 text-center relative {{ $activePermit ? 'opacity-50 pointer-events-none' : '' }}" title="Tugas Divisi">
                    <div class="relative">
                        <i class="fa-solid fa-folder-open text-purple-600 text-lg"></i>
                        @if(!empty($hasActiveTasks))
                        <span class="absolute -top-1 -right-1 flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500 ring-2 ring-white"></span>
                        </span>
                        @endif
                    </div>
                    <span class="text-[10px] font-semibold mt-1 truncate w-full">Tugas</span>
                </a>

                <!-- 3. Pengumuman -->
                <a onclick="handleBroadcastListClick(event)"
                    class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-slate-50 hover:bg-sky-50 text-slate-700 hover:text-sky-700 border border-slate-100 hover:border-sky-200 transition active:scale-95 text-center cursor-pointer {{ $activePermit ? 'opacity-50 pointer-events-none' : '' }}" title="Pengumuman">
                    <i class="fa-solid fa-bullhorn text-sky-600 text-lg"></i>
                    <span class="text-[10px] font-semibold mt-1 truncate w-full">Pengumuman</span>
                </a>

                <!-- 4. Info & SOP -->
                <a onclick="handleHolidayInfoClick(event)"
                    class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-slate-50 hover:bg-amber-50 text-slate-700 hover:text-amber-700 border border-slate-100 hover:border-amber-200 transition active:scale-95 text-center cursor-pointer {{ $activePermit ? 'opacity-50 pointer-events-none' : '' }}" title="Info & SOP Magang">
                    <i class="fa-solid fa-circle-info text-amber-600 text-lg"></i>
                    <span class="text-[10px] font-semibold mt-1 truncate w-full">Info SOP</span>
                </a>

                <!-- 5. Ganti Jam -->
                <a href="#"
                    data-gps="{{ $this->isGpsRequiredForStage(AttendanceStatus::AdjustableIn->value) ? 1 : 0 }}"
                    data-user-id="{{ $user->id }}"
                    data-stage="{{ AttendanceStatus::AdjustableIn->value }}"
                    data-adjustable="1"
                    data-absence-id="{{ $absenceHistory->id ?? 0 }}"
                    data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                    onclick="handleAttendanceAction(event, this)"
                    class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-slate-50 hover:bg-orange-50 text-slate-700 hover:text-orange-700 border border-slate-100 hover:border-orange-200 transition active:scale-95 text-center {{ $hasActiveAdjustable || in_array($stage, [AttendanceStatus::AllDone, AttendanceStatus::AdjustableOut]) ? 'opacity-50 pointer-events-none' : '' }}" title="Ganti Jam">
                    <i class="fa-solid fa-clock text-orange-600 text-lg"></i>
                    <span class="text-[10px] font-semibold mt-1 truncate w-full">Ganti Jam</span>
                </a>

                <!-- 6. Izin (Izin Tidak Masuk / Sakit) -->
                <a href="#" onclick="event.preventDefault(); if (typeof window.showModalIzin === 'function') { window.showModalIzin('izin'); } else if (typeof showModalIzin === 'function') { showModalIzin('izin'); } else { $('#modal2').removeClass('hidden').addClass('flex'); if(typeof switchPermitType === 'function') switchPermitType('izin'); }"
                    class="flex flex-col items-center justify-center py-2 px-1 rounded-xl bg-slate-50 hover:bg-rose-50 text-slate-700 hover:text-rose-700 border border-slate-100 hover:border-rose-200 transition active:scale-95 text-center cursor-pointer" title="Izin Tidak Masuk / Sakit">
                    <i class="fa-solid fa-file-medical text-rose-600 text-lg"></i>
                    <span class="text-[10px] font-semibold mt-1 truncate w-full">Izin</span>
                </a>

                <!-- 7. Bantuan / Angkat Tangan (Spans 2 columns to fill row 2) -->
                @if($isHandRaised)
                <button type="button" onclick="handleLowerHandClick(event)"
                    class="col-span-2 flex flex-row items-center justify-center gap-2 py-2 px-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 transition active:scale-95 text-center w-full shadow-2xs" title="Bantuan Aktif (Klik untuk turunkan)">
                    <i class="fas fa-hand-paper text-emerald-600 text-base animate-bounce"></i>
                    <span class="text-[11px] font-bold truncate text-emerald-800">Bantuan Aktif</span>
                </button>
                @else
                <button type="button" onclick="handleRaiseHandClick(event)"
                    class="col-span-2 flex flex-row items-center justify-center gap-2 py-2 px-2 rounded-xl bg-slate-50 hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 border border-slate-100 hover:border-indigo-200 transition active:scale-95 text-center w-full shadow-2xs {{ $activePermit ? 'opacity-50 pointer-events-none' : '' }}" title="Angkat Tangan / Butuh Bantuan">
                    <i class="fas fa-hand-paper text-indigo-600 text-base"></i>
                    <span class="text-[11px] font-semibold truncate">Butuh Bantuan</span>
                </button>
                @endif

                <!-- Row 3: Routine Permits: Izin Keluar, Toilet, Sholat (Khusus Mobile: 3 Berjejer) -->
                @if(!$activePermit && ($hasCheckedIn || $hasActiveAdjustable) && !in_array($stage, [AttendanceStatus::AttendanceIn, AttendanceStatus::AllDone]))
                <div class="col-span-4 grid grid-cols-3 gap-1.5 pt-1.5 border-t border-slate-100 mt-0.5">
                    <!-- 1. Izin Keluar -->
                    <button type="button" onclick="handleLeavePermitClick(event)"
                        class="py-2 px-1 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 rounded-xl text-[11px] sm:text-xs font-bold flex items-center justify-center gap-1 transition active:scale-95 text-center shadow-2xs" title="Izin Keluar Kantor">
                        <i class="fa-solid fa-door-open text-amber-600 shrink-0 text-xs"></i>
                        <span class="truncate">Izin Keluar</span>
                    </button>

                    <!-- 2. Izin Toilet -->
                    <button type="button" onclick="event.preventDefault(); submitPermit('toilet')"
                        class="py-2 px-1 bg-blue-50 hover:bg-blue-100 text-blue-900 border border-blue-200 rounded-xl text-[11px] sm:text-xs font-bold flex items-center justify-center gap-1 transition active:scale-95 text-center shadow-2xs" title="Izin Toilet">
                        <i class="fas fa-restroom text-blue-600 shrink-0 text-xs"></i>
                        <span class="truncate">Toilet</span>
                    </button>

                    <!-- 3. Izin Shalat -->
                    <button type="button" onclick="handlePrayerPermitClick(event)"
                        class="py-2 px-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-900 border border-emerald-200 rounded-xl text-[11px] sm:text-xs font-bold flex items-center justify-center gap-1 transition active:scale-95 text-center shadow-2xs" title="Izin Shalat">
                        <i class="fas fa-mosque text-emerald-600 shrink-0 text-xs"></i>
                        <span class="truncate">Sholat</span>
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
            if (typeof openLowerHandModal === 'function') {
                openLowerHandModal();
            }
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

        // Pastikan modal tertutup saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            closeLeavePermitModal();
            closePermitLimitModal();
            closeShiftNotSetModal();
        });
    </script>
</div>