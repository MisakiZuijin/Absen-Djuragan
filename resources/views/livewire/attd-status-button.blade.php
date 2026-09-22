<div wire:poll.5s>
    @php
    use App\Utils\AttendanceStatus;
    use App\Models\Attendance;
    use App\Models\PermitLog;
    use App\Models\PermitSetting;
    use App\Models\AdjustableAttd;
    use Carbon\Carbon;

    if (!isset($currentHandRaise) || !$currentHandRaise) {
    $currentHandRaise = \App\Models\HandRaise::where('user_id', auth()->id())
    ->where('is_raised', true)
    ->where('status', '!=', 'done')
    ->latest()
    ->first()
    ?? \App\Models\HandRaise::where('user_id', auth()->id())->latest()->first();
    }
    $isHandRaised = (bool) ($currentHandRaise?->is_raised && $currentHandRaise?->status !== 'done');
    $activePermit = null;
    $isPraying = false;

    // Hanya inisialisasi untuk prayer dan leave
    $hasReachedLeaveLimit = false;
    $hasReachedPrayerLimit = false;
    $hasCheckedIn = false;
    $hasActiveAdjustable = false;

    $prayerSetting = PermitSetting::where('type', 'prayer')->first();
    $leaveSetting = PermitSetting::where('type', 'leave')->first();

    $prayerCountToday = 0;
    $leaveCountToday = 0;

    if (auth()->user()->intern) {
    $internId = auth()->user()->intern->id;
    $todaysAttendanceId = Attendance::where('intern_id', $internId)
    ->whereDate('date', today())
    ->value('id');

    if ($todaysAttendanceId) {
    $activePermit = PermitLog::where('attendance_id', $todaysAttendanceId)
    ->whereNull('end_time')
    ->first();

    // Hitung hanya untuk prayer dan leave
    $prayerCountToday = PermitLog::where('attendance_id', $todaysAttendanceId)
    ->where('type', 'prayer')
    ->count();

    $leaveCountToday = PermitLog::where('attendance_id', $todaysAttendanceId)
    ->where('type', 'leave')
    ->count();

    $hasReachedPrayerLimit = $prayerCountToday >= ($prayerSetting?->max_daily_count ?? 5);
    $hasReachedLeaveLimit = $leaveCountToday >= ($leaveSetting?->max_daily_count ?? 1);
    }

    // Cek status masuk (presensi sudah dimulai) dan sesi ganti jam aktif
    $hasCheckedIn = false;
    $hasActiveAdjustable = false;
    if ($todaysAttendanceId) {
    $hasCheckedIn = !is_null(Attendance::where('id', $todaysAttendanceId)->value('start_time'));
    }
    $hasActiveAdjustable = AdjustableAttd::where('intern_id', $internId)
    ->whereDate('date', today())
    ->whereNotNull('start_time')
    ->whereNull('end_time')
    ->exists();
    }
    @endphp

    <!-- Notification Element -->
    <div id="permit-notification" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg hidden z-[10000] flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span>Izin selesai! Durasi: <span id="permit-duration" class="font-bold">00:00:00</span></span>
    </div>

    <!-- Permit Limit Alert Modal -->
    <div id="permitLimitModal" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg p-6 max-w-sm mx-4 shadow-xl">
            <div class="text-center">
                <div class="text-red-500 text-5xl mb-4">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2" id="permitLimitTitle">Batas Izin Tercapai</h3>
                <p class="text-sm text-gray-500 mb-6" id="permitLimitMessage"></p>
                <div class="flex justify-center">
                    <button onclick="closePermitLimitModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium rounded-md">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Shift Not Set Alert Modal -->
    <div id="shiftNotSetModal" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg p-6 max-w-sm mx-4 shadow-xl">
            <div class="text-center">
                <div class="text-orange-500 text-5xl mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Shift Belum Diatur</h3>
                <p class="text-sm text-gray-500 mb-6">Shift Anda belum diatur. Harap lakukan konfirmasi ke admin atau HR terkait pengaturan shift Anda.</p>
                <div class="flex justify-center">
                    <button onclick="closeShiftNotSetModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium rounded-md">
                        Mengerti
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Permit Modal --}}
    @if($activePermit)
    <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-75 backdrop-blur-sm">
        <div class="bg-white p-8 rounded-lg shadow-xl text-center w-80 max-w-sm mx-4 permit-modal">
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
            <div class="text-blue-500 text-6xl mb-6"><i class="fas fa-{{ $icon }} permit-icon"></i></div>
            <h2 class="text-2xl font-bold mb-3 text-gray-800">{{ $title }}</h2>
            <p class="text-gray-600 mb-8 text-sm leading-relaxed">{{ $description }}</p>

            <form id="end-permit-form" action="{{ route('user.permit.end') }}" method="POST">
                @csrf
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-lg transition-colors duration-200 shadow-lg permit-button">
                    <i class="fas fa-check mr-2"></i> Kembali dari Izin
                </button>
            </form>
        </div>
    </div>
    <script>
        if (typeof permitTimerInterval === 'undefined' || permitTimerInterval === null) {
            let permitStartTime = new Date("{{ \Carbon\Carbon::parse($activePermit->start_time)->toIso8601String() }}");
            let lastPermitDuration = '00:00:00';

            var permitTimerInterval = setInterval(function() {
                let now = new Date();
                let diff = now - permitStartTime;
                if (diff < 0) diff = 0;
                let hours = Math.floor(diff / (1000 * 60 * 60));
                let minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((diff % (1000 * 60)) / 1000);
                lastPermitDuration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }, 1000);

            document.getElementById('end-permit-form').addEventListener('submit', function() {
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
    </script>
    @endif

    {{-- Leave Permit Modal --}}
    <div id="leavePermitModal" class="fixed inset-0 z-[9998] flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
            <div class="flex justify-between items-center mb-4 pb-2 border-b">
                <h2 class="text-xl font-semibold text-gray-800">Form Izin Keluar</h2>
                <button type="button" onclick="closeLeavePermitModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold leading-none">&times;</button>
            </div>
            <form method="POST" action="{{ route('user.permit.start') }}">
                @csrf
                <input type="hidden" name="type" value="leave">
                <div class="mb-4">
                    <label for="keterangan" class="block text-gray-700 mb-2">Alasan Izin<span class="text-red-500">*</span></label>
                    <textarea id="keterangan" name="keterangan" rows="3" class="w-full border border-gray-300 rounded-lg p-2" placeholder="Contoh: Mengambil barang yang tertinggal" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="authorized_by" class="block text-gray-700 mb-2">Diizinkan oleh<span class="text-red-500">*</span></label>
                    <select id="authorized_by" name="authorized_by" class="w-full border border-gray-300 rounded-lg p-2" required>
                        <option value="" disabled selected>-- Pilih nama HR/Atasan --</option>
                        @foreach($hrUsers as $hr)
                        <option value="{{ $hr['name'] }}">{{ $hr['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeLeavePermitModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Kirim Izin</button>
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
        <div class="text-2xl font-bold text-center lg:mb-4 mt-5">Shift {{ $shift }}</div>
        <div class="hidden md:flex flex-col space-y-2">
            @switch($stage)
            @case(AttendanceStatus::AttendanceAndAdjustableTime)
            <!-- MASUK & GANTI JAM AWAL -->
            <a href="#" class="text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md"
                data-check-shift="1"
                data-has-shift="{{ $hasShift ? 1 : 0 }}"
                data-gps="{{ $user->is_gps_activate ? 1 : 0 }}"
                data-user-id="{{ $user->id }}"
                data-stage="{{ AttendanceStatus::AttendanceIn->value }}"
                data-adjustable="0"
                data-absence-id="{{ $absenceHistory->id ?? 0 }}"
                data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                onclick="handleAttendanceAction(event, this)">Masuk</a>
            <a href="#" class="text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md"
                data-gps="{{ $user->is_gps_activate ? 1 : 0 }}"
                data-user-id="{{ $user->id }}"
                data-stage="{{ AttendanceStatus::AdjustableIn->value }}"
                data-adjustable="1"
                data-absence-id="{{ $absenceHistory->id ?? 0 }}"
                data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                onclick="handleAttendanceAction(event, this)">Ganti Jam</a>
            <a href="#" class="text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md flex items-center justify-center gap-2 cursor-pointer" onclick="event.preventDefault(); if (typeof window.showModalIzin === 'function') { window.showModalIzin('sakit'); } else if (typeof showModalIzin === 'function') { showModalIzin('sakit'); } else { $('#modal2').removeClass('hidden').addClass('flex'); if(typeof switchPermitType === 'function') switchPermitType('sakit'); }">
                <i class="fa-solid fa-file-medical"></i> Izin Tidak Hadir / Sakit
            </a>
            @break

            @case(AttendanceStatus::AttendanceIn)
            <!-- SUDAH MASUK - TOMBOL GANTI JAM TETAP TERSEDIA -->
            <a href="#" class="text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md"
                data-gps="{{ $user->is_gps_activate ? 1 : 0 }}"
                data-user-id="{{ $user->id }}"
                data-stage="{{ AttendanceStatus::AdjustableIn->value }}"
                data-adjustable="1"
                data-absence-id="{{ $absenceHistory->id ?? 0 }}"
                data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                onclick="handleAttendanceAction(event, this)">Ganti Jam</a>
            @break

            @default
            <!-- TOMBOL GANTI JAM - SELALU TERSEDIA DI SEMUA STAGE KECEGUALI -->
            @if(!$hasActiveAdjustable && !in_array($stage, [AttendanceStatus::AllDone, AttendanceStatus::AdjustableOut]))
            <a href="#" class="text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md"
                data-gps="{{ $user->is_gps_activate ? 1 : 0 }}"
                data-user-id="{{ $user->id }}"
                data-stage="{{ AttendanceStatus::AdjustableIn->value }}"
                data-adjustable="1"
                data-absence-id="{{ $absenceHistory->id ?? 0 }}"
                data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                onclick="handleAttendanceAction(event, this)">Ganti Jam</a>
            @endif

            {{-- ISTIRAHAT BIASA (untuk attendance normal) --}}
            @if(in_array($stage, [AttendanceStatus::ShowBreakPermitChangeTime, AttendanceStatus::ShowBreakPermit, AttendanceStatus::ShowBreakChangeTime, AttendanceStatus::StartBreak]))
            <a href="#" class="text-center bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                data-check-shift="1"
                data-has-shift="{{ $hasShift ? 1 : 0 }}"
                data-active-permit="{{ $activePermit ? 1 : 0 }}"
                data-gps="{{ $user->is_gps_activate ? 1 : 0 }}"
                data-user-id="{{ $user->id }}"
                data-stage="{{ AttendanceStatus::StartBreak->value }}"
                data-adjustable="0"
                data-absence-id="0"
                data-adjustable-id="0"
                onclick="handleAttendanceAction(event, this)">Istirahat</a>
            @endif

            {{-- ISTIRAHAT GANTI JAM (untuk adjustable attendance) --}}
            @if($stage === AttendanceStatus::BreakOrBack)
            <a href="#" class="text-center bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                data-active-permit="{{ $activePermit ? 1 : 0 }}"
                data-gps="{{ $user->is_gps_activate ? 1 : 0 }}"
                data-user-id="{{ $user->id }}"
                data-stage="{{ AttendanceStatus::StartBreakAdjustable->value }}"
                data-adjustable="1"
                data-absence-id="0"
                data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                onclick="handleAttendanceAction(event, this)">Istirahat (Ganti Jam)</a>
            @endif

            {{-- KEMBALI DARI ISTIRAHAT BIASA --}}
            @if($stage === AttendanceStatus::EndBreak)
            <a href="#" class="text-center bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                data-check-shift="1"
                data-has-shift="{{ $hasShift ? 1 : 0 }}"
                data-active-permit="{{ $activePermit ? 1 : 0 }}"
                data-gps="{{ $user->is_gps_activate ? 1 : 0 }}"
                data-user-id="{{ $user->id }}"
                data-stage="{{ AttendanceStatus::EndBreak->value }}"
                data-adjustable="0"
                data-absence-id="0"
                data-adjustable-id="0"
                onclick="handleAttendanceAction(event, this)">Kembali dari Istirahat</a>
            @endif

            {{-- KEMBALI DARI ISTIRAHAT GANTI JAM --}}
            @if($stage === AttendanceStatus::EndBreakAdjustable)
            <a href="#" class="text-center bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                data-active-permit="{{ $activePermit ? 1 : 0 }}"
                data-gps="{{ $user->is_gps_activate ? 1 : 0 }}"
                data-user-id="{{ $user->id }}"
                data-stage="{{ AttendanceStatus::EndBreakAdjustable->value }}"
                data-adjustable="1"
                data-absence-id="0"
                data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                onclick="handleAttendanceAction(event, this)">Kembali dari Istirahat (Ganti Jam)</a>
            @endif

            {{-- PULANG BIASA (untuk attendance normal) --}}
            @if(in_array($stage, [AttendanceStatus::ShowBreakPermitChangeTime, AttendanceStatus::ShowBreakPermit, AttendanceStatus::ShowPermitChangeTime, AttendanceStatus::ShowBreakChangeTime, AttendanceStatus::StartBreak, AttendanceStatus::StartPermit, AttendanceStatus::AttendanceOut]))
            <a href="#" class="text-center bg-red-600 hover:bg-red-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                data-check-shift="1"
                data-has-shift="{{ $hasShift ? 1 : 0 }}"
                data-active-permit="{{ $activePermit ? 1 : 0 }}"
                data-gps="{{ $user->is_gps_activate ? 1 : 0 }}"
                data-user-id="{{ $user->id }}"
                data-stage="{{ AttendanceStatus::AttendanceOut->value }}"
                data-adjustable="0"
                data-absence-id="0"
                data-adjustable-id="0"
                onclick="handleAttendanceAction(event, this)">
                <i class="fas fa-home mr-2"></i>Pulang
            </a>
            @endif

            {{-- PULANG GANTI JAM (untuk adjustable attendance) --}}
            @if($stage === AttendanceStatus::AdjustableOut)
            <a href="#" class="text-center bg-red-600 hover:bg-red-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"
                data-active-permit="{{ $activePermit ? 1 : 0 }}"
                data-gps="{{ $user->is_gps_activate ? 1 : 0 }}"
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
            <a href="#" class="text-center bg-gray-400 text-white font-bold py-2 px-4 rounded-md cursor-not-allowed">Selesai</a>
            @endif
            @endswitch

            <!-- ACTION BUTTONS (SELALU TERSEDIA KECUALI ADA ACTIVE PERMIT) -->
            <!-- UNIFIED LOGBOOK HARIAN BUTTON -->
            <a href="{{ route('user.logbook.index') }}" onclick="handleLogbookClick(event)" class="w-full cursor-pointer text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                <i class="fa-regular fa-file-lines mr-1.5"></i>Logbook Harian
                @if($hasFilledLogToday)
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500 ml-1.5 align-middle" title="Sudah Diisi"></span>
                @else
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-yellow-400 ml-1.5 align-middle animate-pulse" title="Belum Diisi"></span>
                @endif
            </a>
            <a onclick="handleHolidayInfoClick(event)" class="w-full cursor-pointer text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"><i class="fa-solid fa-circle-info mr-1.5"></i> Info & Libur</a>
            <a href="{{ route('user.tasks.index') }}" class="w-full text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                <i class="fa-solid fa-folder-open mr-1.5"></i> Tugas & Akun Divisi
                @if(!empty($hasActiveTasks))
                <span class="inline-flex items-center ml-1.5 align-middle" title="{{ ($activeTasksCount ?? 0) > 0 ? ($activeTasksCount . ' Tugas Aktif') : 'Memiliki Tugas Aktif' }}">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                    </span>
                </span>
                @endif
            </a>
            <a onclick="handleBroadcastListClick(event)" class="w-full cursor-pointer text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"><i class="fa-regular fa-clipboard"></i> Pengumuman</a>
            @if($isHandRaised)
            <button type="button" onclick="handleLowerHandClick(event)" class="w-full cursor-pointer text-center bg-emerald-700 hover:bg-emerald-600 text-white font-bold py-2 px-4 rounded-md transition-all duration-200 {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $activePermit ? 'disabled' : '' }}>
                <i class="fas fa-hand-paper animate-bounce mr-1.5"></i> Tangan Diangkat
                @if($currentHandRaise && $currentHandRaise->status === 'urgent')
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-400 ml-1.5 align-middle animate-ping" title="Urgent / Prioritas Hari Ini"></span>
                @endif
            </button>
            @else
            <button type="button" onclick="handleRaiseHandClick(event)" class="w-full cursor-pointer text-center bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-md transition-all duration-200 {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $activePermit ? 'disabled' : '' }}>
                <i class="fas fa-hand-paper mr-1.5"></i> Angkat Tangan
            </button>
            @endif

            <!-- PERMIT BUTTONS (HANYA TERSEDIA JIKA SUDAH CHECKED IN DAN TIDAK ADA ACTIVE PERMIT) -->
            @if(!$activePermit && ($hasCheckedIn || $hasActiveAdjustable) && !in_array($stage, [AttendanceStatus::AttendanceIn, AttendanceStatus::AllDone]))
            <!-- Izin Keluar dengan limit -->
            <button type="button"
                onclick="handleLeavePermitClick(event)"
                class="w-full text-white bg-gray-700 hover:bg-gray-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}">
                <i class="fas fa-door-open mr-2"></i> Izin Keluar
            </button>

            <!-- Izin Shalat dengan limit -->
            <button type="button"
                onclick="handlePrayerPermitClick(event)"
                class="w-full text-white bg-gray-700 hover:bg-gray-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}">
                <i class="fas fa-mosque mr-2"></i> Izin Shalat
            </button>

            <!-- Izin Toilet TANPA limit -->
            <button type="button"
                onclick="event.preventDefault(); submitPermit('toilet')"
                class="w-full text-white bg-gray-700 hover:bg-gray-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}">
                <i class="fas fa-toilet mr-2"></i> Izin ke Toilet
            </button>
            @endif
        </div>

        <!-- MOBILE VIEW -->
        <div class="md:hidden floating-btn-container p-2 space-y-2">
            <!-- TOMBOL GANTI JAM UNTUK MOBILE -->
            @if(!in_array($stage, [AttendanceStatus::AllDone, AttendanceStatus::AdjustableOut]))
            <a href="#" class="block w-full text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-3 px-4 rounded-md"
                data-gps="{{ $user->is_gps_activate ? 1 : 0 }}"
                data-user-id="{{ $user->id }}"
                data-stage="{{ AttendanceStatus::AdjustableIn->value }}"
                data-adjustable="1"
                data-absence-id="{{ $absenceHistory->id ?? 0 }}"
                data-adjustable-id="{{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }}"
                onclick="handleAttendanceAction(event, this)">
                <i class="fas fa-clock mr-2"></i>Ganti Jam
            </a>

            @if($stage == AttendanceStatus::AttendanceAndAdjustableTime || (isset($stage->value) && $stage->value == 1))
            <a href="#" class="block w-full text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-3 px-4 rounded-md cursor-pointer" onclick="event.preventDefault(); if (typeof window.showModalIzin === 'function') { window.showModalIzin('sakit'); } else if (typeof showModalIzin === 'function') { showModalIzin('sakit'); } else { $('#modal2').removeClass('hidden').addClass('flex'); if(typeof switchPermitType === 'function') switchPermitType('sakit'); }">
                <i class="fa-solid fa-file-medical mr-2"></i>Izin Tidak Hadir / Sakit
            </a>
            @endif
            @endif

            <!-- TOMBOL LAINNYA UNTUK MOBILE -->
            <a href="{{ route('user.logbook.index') }}" onclick="handleLogbookClick(event)" class="block w-full text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-3 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                <i class="fa-regular fa-file-lines mr-2"></i>Logbook Harian
                @if($hasFilledLogToday)
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500 ml-1.5 align-middle" title="Sudah Diisi"></span>
                @else
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-yellow-400 ml-1.5 align-middle animate-pulse" title="Belum Diisi"></span>
                @endif
            </a>
            <a onclick="handleHolidayInfoClick(event)" class="block w-full text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-3 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"><i class="fa-solid fa-circle-info mr-2"></i> Info & Libur</a>
            <a href="{{ route('user.tasks.index') }}" class="block w-full text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-3 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                <i class="fa-solid fa-folder-open mr-2"></i> Tugas & Akun Divisi
                @if(!empty($hasActiveTasks))
                <span class="inline-flex items-center ml-1.5 align-middle" title="{{ ($activeTasksCount ?? 0) > 0 ? ($activeTasksCount . ' Tugas Aktif') : 'Memiliki Tugas Aktif' }}">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500 border border-green-200"></span>
                    </span>
                </span>
                @endif
            </a>
            @if($isHandRaised)
            <button type="button" onclick="handleLowerHandClick(event)" class="block w-full text-center bg-emerald-700 hover:bg-emerald-600 text-white font-bold py-3 px-4 rounded-md transition-all duration-200 {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}">
                <i class="fas fa-hand-paper animate-bounce mr-2"></i> Tangan Diangkat
                @if($currentHandRaise && $currentHandRaise->status === 'urgent')
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-400 ml-1.5 align-middle animate-ping"></span>
                @endif
            </button>
            @else
            <button type="button" onclick="handleRaiseHandClick(event)" class="block w-full text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-3 px-4 rounded-md transition-all duration-200 {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}">
                <i class="fas fa-hand-paper mr-2"></i> Angkat Tangan
            </button>
            @endif
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