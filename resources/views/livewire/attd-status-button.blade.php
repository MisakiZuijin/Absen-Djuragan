<div>
    @php
        use App\Utils\AttendanceStatus;
        use App\Models\Attendance;
        use App\Models\PermitLog;
        use App\Models\PermitSetting;
        use App\Models\AdjustableAttd;
        use Carbon\Carbon;

        $isHandRaised = \App\Models\HandRaise::where('user_id', auth()->id())->value('is_raised') ?? false;
        $activePermit = null;
        $isPraying = false;

        // Hanya inisialisasi untuk prayer dan leave
        $hasReachedLeaveLimit = false;
        $hasReachedPrayerLimit = false;

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
            <h2 class="text-xl font-semibold mb-4">Form Izin Keluar</h2>
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
    <div class="flex flex-col {{ $isPraying ? 'prayer-mode' : '' }}" id="prayer-container">
        <div class="text-2xl font-bold text-center lg:mb-4 mt-5">Shift {{ $shift }}</div>
        <div class="hidden md:flex flex-col space-y-2">
            @switch($stage)
                @case(AttendanceStatus::AttendanceAndAdjustableTime)
                    <!-- MASUK & GANTI JAM AWAL -->
                    <a href="#" class="text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md" onclick="event.preventDefault(); {{ !$hasShift ? 'openShiftNotSetModal()' : 'showModal( ' . $user->is_gps_activate . ', ' . $user->id . ', ' . AttendanceStatus::AttendanceIn->value . ', false, ' . ($absenceHistory->id ?? 0) . ', ' . ($adjustableTimeHistory ? $adjustableTimeHistory->id : 0) . ');' }}">Masuk</a>
                    <a href="#" class="text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md" onclick="event.preventDefault(); showModal( {{ $user->is_gps_activate }}, {{ $user->id }}, {{ AttendanceStatus::AdjustableIn->value }}, true, {{ $absenceHistory->id ?? 0 }}, {{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }} );">Ganti Jam</a>
                    <a href="#" class="text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md" onclick="event.preventDefault(); showModalIzin();">Izin Tidak Masuk</a>
                    @break

                @case(AttendanceStatus::AttendanceIn)
                    <!-- SUDAH MASUK - TOMBOL GANTI JAM TETAP TERSEDIA -->
                    <a href="#" class="text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md" onclick="event.preventDefault(); showModal( {{ $user->is_gps_activate }}, {{ $user->id }}, {{ AttendanceStatus::AdjustableIn->value }}, true, {{ $absenceHistory->id ?? 0 }}, {{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }} );">Ganti Jam</a>
                    @break

                @default
                    <!-- TOMBOL GANTI JAM - SELALU TERSEDIA DI SEMUA STAGE KECEGUALI -->
                    @if(!$hasActiveAdjustable && !in_array($stage, [AttendanceStatus::AllDone, AttendanceStatus::AdjustableOut]))
                    <a href="#" class="text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md" onclick="event.preventDefault(); showModal( {{ $user->is_gps_activate }}, {{ $user->id }}, {{ AttendanceStatus::AdjustableIn->value }}, true, {{ $absenceHistory->id ?? 0 }}, {{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }} );">Ganti Jam</a>
                    @endif

                    {{-- ISTIRAHAT BIASA (untuk attendance normal) --}}
                    @if(in_array($stage, [AttendanceStatus::ShowBreakPermitChangeTime, AttendanceStatus::ShowBreakPermit, AttendanceStatus::ShowBreakChangeTime, AttendanceStatus::StartBreak]))
                        <a href="#" class="text-center bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}" onclick="{{ $activePermit ? 'return false;' : 'event.preventDefault(); ' }}{{ !$hasShift ? 'openShiftNotSetModal()' : 'showModal( ' . $user->is_gps_activate . ', ' . $user->id . ', ' . AttendanceStatus::StartBreak->value . ');' }}">Istirahat</a>
                    @endif

                    {{-- ISTIRAHAT GANTI JAM (untuk adjustable attendance) --}}
                    @if($stage === AttendanceStatus::BreakOrBack)
                        <a href="#" class="text-center bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}" onclick="{{ $activePermit ? 'return false;' : '' }}event.preventDefault(); showModal( {{ $user->is_gps_activate }}, {{ $user->id }}, {{ AttendanceStatus::StartBreakAdjustable->value }}, true, 0, {{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }} );">Istirahat (Ganti Jam)</a>
                    @endif

                    {{-- KEMBALI DARI ISTIRAHAT BIASA --}}
                    @if($stage === AttendanceStatus::EndBreak)
                        <a href="#" class="text-center bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}" onclick="{{ $activePermit ? 'return false;' : 'event.preventDefault(); ' }}{{ !$hasShift ? 'openShiftNotSetModal()' : 'showModal( ' . $user->is_gps_activate . ', ' . $user->id . ', ' . AttendanceStatus::EndBreak->value . ');' }}">Kembali dari Istirahat</a>
                    @endif

                    {{-- KEMBALI DARI ISTIRAHAT GANTI JAM --}}
                    @if($stage === AttendanceStatus::EndBreakAdjustable)
                        <a href="#" class="text-center bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}" onclick="{{ $activePermit ? 'return false;' : '' }}event.preventDefault(); showModal( {{ $user->is_gps_activate }}, {{ $user->id }}, {{ AttendanceStatus::EndBreakAdjustable->value }}, true, 0, {{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }} );">Kembali dari Istirahat (Ganti Jam)</a>
                    @endif

                    {{-- PULANG BIASA (untuk attendance normal) --}}
                    @if(in_array($stage, [AttendanceStatus::ShowBreakPermitChangeTime, AttendanceStatus::ShowBreakPermit, AttendanceStatus::ShowPermitChangeTime, AttendanceStatus::ShowBreakChangeTime, AttendanceStatus::StartBreak, AttendanceStatus::StartPermit, AttendanceStatus::AttendanceOut]))
                        <a href="#" class="text-center bg-red-600 hover:bg-red-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}" onclick="{{ $activePermit ? 'return false;' : 'event.preventDefault(); ' }}{{ !$hasShift ? 'openShiftNotSetModal()' : 'showModal( ' . $user->is_gps_activate . ', ' . $user->id . ', ' . AttendanceStatus::AttendanceOut->value . ', false);' }}">
                            <i class="fas fa-home mr-2"></i>Pulang
                        </a>
                    @endif

                    {{-- PULANG GANTI JAM (untuk adjustable attendance) --}}
                    @if($stage === AttendanceStatus::AdjustableOut)
                        <a href="#" class="text-center bg-red-600 hover:bg-red-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}" onclick="{{ $activePermit ? 'return false;' : '' }}event.preventDefault(); showModal( {{ $user->is_gps_activate }}, {{ $user->id }}, {{ AttendanceStatus::AdjustableOut->value }}, true, 0, {{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }} );">
                            <i class="fas fa-clock mr-2"></i>Pulang (Ganti Jam)
                        </a>
                    @endif

                    @if($stage === AttendanceStatus::AllDone)
                        <a href="#" class="text-center bg-gray-400 text-white font-bold py-2 px-4 rounded-md cursor-not-allowed">Selesai</a>
                    @endif
            @endswitch

            <!-- ACTION BUTTONS (SELALU TERSEDIA KECUALI ADA ACTIVE PERMIT) -->
            <a onclick="{{ $activePermit ? 'return false;' : 'togglePopup()' }}" class="w-full cursor-pointer text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"><i class="fa-solid fa-circle-plus"></i> Log Activity</a>
            <a href="{{ $activePermit ? '#' : route('home.historyActivity') }}" class="w-full text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"><i class="fa-regular fa-file-lines"></i> History Log Activity</a>
            <a onclick="{{ $activePermit ? 'return false;' : 'openModal()' }}" class="w-full cursor-pointer text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"><i class="fa-regular fa-calendar-days"></i> Hari Libur</a>
            <a onclick="{{ $activePermit ? 'return false;' : 'openModalBroadcastList()' }}" class="w-full cursor-pointer text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"><i class="fa-regular fa-clipboard"></i> Pengumuman</a>
            <form action="{{ route('intern.raisehand.toggle') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="w-full cursor-pointer text-center {{ $isHandRaised ? 'bg-green-700 hover:bg-green-600' : 'bg-gray-700 hover:bg-gray-600' }} text-white font-bold py-2 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $activePermit ? 'disabled' : '' }}>
                    <i class="fas fa-hand-paper"></i> {{ $isHandRaised ? 'Tangan Diangkat' : 'Angkat Tangan' }}
                </button>
            </form>

            <!-- PERMIT BUTTONS (HANYA TERSEDIA JIKA SUDAH CHECKED IN DAN TIDAK ADA ACTIVE PERMIT) -->
            @if(!$activePermit && ($hasCheckedIn || $hasActiveAdjustable) && !in_array($stage, [AttendanceStatus::AttendanceIn, AttendanceStatus::AllDone]))
                <!-- Izin Keluar dengan limit -->
                <button type="button"
                    onclick="event.preventDefault(); {{ $hasReachedLeaveLimit ? 'showPermitLimitAlert(\'leave\')' : 'openLeavePermitModal()' }}"
                    class="w-full text-white bg-gray-700 hover:bg-gray-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <i class="fas fa-door-open mr-2"></i> Izin Keluar
                </button>

                <!-- Izin Shalat dengan limit -->
                <button type="button"
                    onclick="event.preventDefault(); {{ $hasReachedPrayerLimit ? 'showPermitLimitAlert(\'prayer\')' : 'submitPermit(\'prayer\')' }}"
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
            <a href="#" class="block w-full text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-3 px-4 rounded-md" onclick="event.preventDefault(); showModal( {{ $user->is_gps_activate }}, {{ $user->id }}, {{ AttendanceStatus::AdjustableIn->value }}, true, {{ $absenceHistory->id ?? 0 }}, {{ $adjustableTimeHistory ? $adjustableTimeHistory->id : 0 }} );">
                <i class="fas fa-clock mr-2"></i>Ganti Jam
            </a>
            @endif

            <!-- TOMBOL LAINNYA UNTUK MOBILE -->
            <a onclick="{{ $activePermit ? 'return false;' : 'togglePopup()' }}" class="block w-full text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-3 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"><i class="fa-solid fa-circle-plus mr-2"></i> Log Activity</a>
            <a href="{{ $activePermit ? '#' : route('home.historyActivity') }}" class="block w-full text-center bg-gray-700 hover:bg-gray-500 text-white font-bold py-3 px-4 rounded-md {{ $activePermit ? 'opacity-50 cursor-not-allowed' : '' }}"><i class="fa-regular fa-file-lines mr-2"></i> History Log</a>
        </div>
    </div>

    <style>
        #permit-notification {
            transition: all 0.3s ease;
            animation: slideIn 0.5s forwards;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>

    <script>
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
            if ({{ $activePermit ? 'true' : 'false' }}) {
                return false;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("user.permit.start") }}';

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';

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
