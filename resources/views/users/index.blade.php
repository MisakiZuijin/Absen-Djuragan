@extends('users.layouts.main', [])

@section('title', 'Dashboard')

@section('contents')
<!-- Main Content -->
<div class="w-full h-full flex flex-col md:flex-row gap-4 p-3 sm:p-4 md:p-6 lg:p-8 min-w-0">

    <!-- Left Side Buttons (Attendance Actions) -->
    <div class="flex flex-col w-full md:w-64 lg:w-72 md:shrink-0 gap-4 min-w-0 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden no-scrollbar">

        <!-- Pemanggilan Komponen Livewire (Tombol Izin akan ada di dalam sini) -->
        @livewire('attd-status-button', [
        'hrUsers' => $hrUsers,
        'shift' => is_object($shift) ? $shift->name : 'belum ada',
        'hasShift' => is_object($shift),
        'user' => $user,
        'stage' => $stage ?? App\Utils\AttendanceStatus::AllDone,
        'scheduleId' => $schedule_id ?? null,
        'detailScheduleId' => $detail_schedule_id ?? null,
        'absenceHistory' => $absenceHistory ?? null,
        'adjustableTimeHistory' => isset($all_adjustable) && count($all_adjustable) > 0 ? collect($all_adjustable)->where('end_time', null)->first() : null,
        'isHandRaised' => $isHandRaised ?? false,
        'currentHandRaise' => $currentHandRaise ?? null,
        'hasFilledLogToday' => $hasFilledLogToday ?? false,
        'hasActiveTasks' => $hasActiveTasks ?? false,
        'activeTasksCount' => $activeTasksCount ?? 0,
        ])

    </div>

    <!-- Right Side Content -->
    <div class="flex flex-col w-full flex-1 min-w-0 gap-4 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden no-scrollbar">
        @if(isset($currentHandRaise) && $currentHandRaise && $currentHandRaise->is_raised && !in_array($currentHandRaise->status, ['done', 'rejected']))
        @if($currentHandRaise->type === 'new_task' && $currentHandRaise->status === 'in_progress')
        <div class="p-4 bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                    <i class="fa-solid fa-clipboard-list text-base"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-purple-950 flex items-center gap-2">
                        <span>Tugas Baru Telah Diberikan Pembimbing!</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Sedang Dikerjakan
                        </span>
                    </div>
                    <p class="text-xs text-purple-700 mt-0.5">
                        Pembimbing telah menanggapi permintaan Anda dan memberikan instruksi tugas. Silakan cek detail tugas.
                    </p>
                </div>
            </div>
            <a href="{{ route('user.tasks.index') }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5 flex-shrink-0">
                <span>Buka Halaman Tugas</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>
        @elseif($currentHandRaise->type === 'presentation' && $currentHandRaise->status === 'needs_revision')
        <div class="p-4 bg-orange-50 border border-orange-200 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-base"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-orange-950 flex items-center gap-2">
                        <span>Catatan Perbaikan Projek Pra-Presentasi</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-orange-200 text-orange-900">
                            Perlu Perbaikan
                        </span>
                    </div>
                    <p class="text-xs text-orange-800 mt-0.5 line-clamp-1">
                        {{ $currentHandRaise->admin_response ?: 'Mentor memberikan catatan revisi sebelum jadwal presentasi dimulai.' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('user.tasks.index') }}" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5">
                    <span>Lihat di Halaman Tugas</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
        @elseif($currentHandRaise->type === 'presentation' && $currentHandRaise->status === 'ready')
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                <i class="fa-solid fa-circle-check text-base"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-emerald-950 flex items-center gap-2">
                    <span>Presentasi Dikonfirmasi: Sudah Presentasi</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-200 text-emerald-900">
                        Sudah Presentasi
                    </span>
                </div>
                <p class="text-xs text-emerald-800 mt-0.5">
                    Presentasi projek Anda telah dikonfirmasi selesai oleh mentor.
                </p>
            </div>
        </div>
        @endif
        @elseif(isset($currentHandRaise) && $currentHandRaise && $currentHandRaise->status === 'rejected' && $currentHandRaise->resolved_at && $currentHandRaise->resolved_at->isToday())
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                    <i class="fa-solid fa-xmark text-base"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-rose-950 flex items-center gap-2">
                        <span>Pengajuan Presentasi Ditolak Pembimbing</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-rose-200 text-rose-900">
                            Ditolak
                        </span>
                    </div>
                    <p class="text-xs text-rose-800 mt-0.5">
                        {{ $currentHandRaise->admin_response ?: 'Pengajuan presentasi ditolak oleh pembimbing. Harap lengkapi materi sebelum mengajukan kembali.' }}
                    </p>
                </div>
            </div>
            <button type="button" onclick="openRaiseHandModal()" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5 flex-shrink-0 cursor-pointer">
                <span>Ajukan Ulang</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </div>
        @endif

        @if(isset($todaysDetailSchedule) && (int) $todaysDetailSchedule->attd_status_id === 5)
        <!-- Minimalist Alert: Status Alpha & Presensi Ditutup -->
        <div class="flex items-center justify-between px-3.5 py-2 bg-red-50/90 border border-red-200/80 rounded-xl text-xs text-red-800 shadow-2xs">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                <span class="truncate">Status hari ini: <strong class="text-red-900 font-bold">Alpha</strong> (Presensi reguler ditutup oleh Admin).</span>
            </div>
        </div>
        @endif

        {{-- SESI GANTI JAM CONTAINER (V2) --}}
        @livewire('change-time-info-container', [
        'session' => $activeChangeTimeSession
        ])

        {{-- ABSENSI REGULER CONTAINER --}}
        @livewire('attd-info-container', [
        'attdData' => isset($absenceHistory) ? $absenceHistory : null,
        'isWithoutBreak' => isset($shift) && is_object($shift) && isset($shift->break_time_in_minute) && (int) $shift->break_time_in_minute === 0 ? true : false,
        'isAdjustable' => $isAdjustable,
        ])

        {{-- ... bagian lain yang tidak berubah ... --}}
        @if($user->intern && $user->intern->division && $user->intern->division->name === 'Human Resource')
        <!-- HR Monitoring Section -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-check text-lg text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Monitoring Izin</h3>
                        <p class="text-sm text-gray-500 mt-1">Pantau izin toilet dan sholat pemagang</p>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('hr.monitor.toilet') }}"
                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                        <i class="fas fa-toilet mr-1.5"></i>Izin Toilet
                    </a>
                    <a href="{{ route('hr.monitor.prayer') }}"
                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 transition-colors">
                        <i class="fas fa-pray mr-1.5"></i>Izin Sholat
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Top Section: Attention / Pesan Mentor (3/4 Lebar) & Status Jam Kerja (1/4 Lebar) -->
        <div class="flex flex-col lg:flex-row gap-3.5 items-stretch">

            <!-- Left: Catatan / Pesan Perhatian dari Mentor (3/4 Lebar) -->
            <div class="w-full lg:w-3/4 flex flex-col">
                <div class="rounded-2xl border border-gray-200 bg-white p-3.5 sm:p-4 shadow-xs flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-2 border-b border-gray-100 mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-bullhorn text-xs"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 text-xs md:text-sm">Perhatian & Pesan Mentor</h3>
                                    <p class="text-xs text-gray-500">Catatan & instruksi khusus dari pembimbing magang</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                <i class="fa-solid fa-user-tie text-xs"></i> Mentor
                            </span>
                        </div>

                        @php
                        $msg = $user->intern->attention_message;
                        @endphp
                        @if($msg && $msg !== 'Tidak ada catatan')
                        <div class="p-2.5 bg-amber-50/50 rounded-xl border border-amber-100 text-xs text-slate-800 break-words [overflow-wrap:anywhere] leading-relaxed">
                            <p class="font-medium text-amber-950 whitespace-pre-line">{{ $msg }}</p>
                        </div>
                        @else
                        <div class="p-2.5 bg-gray-50 rounded-xl border border-gray-100 text-xs text-gray-500 flex items-center gap-2">
                            <i class="fa-regular fa-circle-check text-emerald-600 text-sm shrink-0"></i>
                            <span>Tidak ada catatan khusus dari mentor saat ini. Tetap semangat dan fokus mengerjakan tugas!</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right: Status Jam Kerja & Ganti Jam (1/4 Lebar) -->
            <div class="w-full lg:w-1/4 flex flex-col">
                <div class="rounded-2xl border border-gray-200 bg-white p-3.5 sm:p-4 shadow-xs flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-2 border-b border-gray-100 mb-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ $lack['isLess'] ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }}">
                                    <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-gray-900 text-xs md:text-sm truncate">Jam Kerja</h3>
                                    <p class="text-xs text-gray-500 truncate">Status akumulasi</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold shrink-0 {{ $lack['isLess'] ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                {{ $lack['isLess'] ? 'Kurang' : 'Aman' }}
                            </span>
                        </div>

                        <div class="text-center py-1.5 bg-slate-50 rounded-xl border border-slate-100 my-1">
                            <div class="text-xs font-medium text-gray-500">
                                {{ $lack['isLess'] ? 'Hutang jam kerja:' : 'Total jam kerja:' }}
                            </div>
                            <div id="timer" class="text-lg font-bold font-mono tracking-tight mt-0.5 {{ $lack['isLess'] ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $intern_target['change_time_total'] ?? '00:00:00' }}
                            </div>
                        </div>
                    </div>

                    <div class="pt-2.5 mt-2 border-t border-gray-100 flex items-center gap-2">
                        @if(!isset($activeRegistration) || !$activeRegistration)
                        <button type="button" onclick="openChangeTimeModal();"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold transition shadow-2xs text-white cursor-pointer hover:opacity-90"
                            style="background-color: #ea580c !important;"
                            title="Daftar Ganti Jam">
                            <i class="fa-solid fa-calendar-plus text-xs"></i>
                            <span class="truncate">Daftar Ganti</span>
                        </button>
                        @endif
                        <a href="{{ route('user.attendance.change.view') }}"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold transition shadow-2xs text-white text-center {{ $lack['isLess'] ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700' }}"
                            title="Lihat Detail Jam Kerja">
                            <span>Detail</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Banner & Modal Chat Pra-Pendaftaran Ganti Jam (Livewire 5s Polling) -->
        @livewire('change-time-registration-chat')

        <!-- Weekly Schedule (Full Width) -->
        <div id="weekly-schedule-container" class="w-full flex flex-col">
            <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden flex-1 flex flex-col">
                <!-- Schedule Header -->
                <div class="px-4 py-3.5 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2 bg-gradient-to-r from-gray-50/80 to-white">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-calendar-week"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm md:text-base">Jadwal Minggu Ini</h3>
                            <p class="text-xs text-gray-500">Daftar shift & kehadiranmu minggu ini</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                        <i class="fa-regular fa-calendar-check text-xs"></i>
                        <span>{{ count($schedules) }} Hari Kerja</span>
                    </span>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto overflow-y-auto max-h-[250px] flex-1">
                    <table class="min-w-full text-sm text-center border-collapse">
                        <thead class="sticky top-0 bg-slate-800 text-white z-10 text-xs uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="py-3 px-3.5">Tanggal</th>
                                <th class="py-3 px-3.5">Hari</th>
                                <th class="py-3 px-3.5">Shift</th>
                                <th class="py-3 px-3.5">Jam Kerja</th>
                                <th class="py-3 px-3.5">Jam Istirahat</th>
                                <th class="py-3 px-3.5">Tipe</th>
                                <th class="py-3 px-3.5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($schedules as $schedule)
                            @php
                            $isToday = \Carbon\Carbon::parse($schedule->date)->isToday();
                            $isPast = \Carbon\Carbon::parse($schedule->date)->isPast();
                            $attdStatusId = $schedule->attdStatus->id ?? $schedule->attd_status_id ?? 1;
                            $attdStatusName = $schedule->attdStatus->name ?? 'Dijadwalkan';

                            $rowBg = $isToday ? 'bg-amber-50/90 font-medium ring-1 ring-inset ring-amber-300' : ($loop->even ? 'bg-gray-50/50' : 'bg-white');

                            // Badge warna status
                            $statusBadge = match((int)$attdStatusId) {
                            2 => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            3 => 'bg-blue-100 text-blue-800 border-blue-200',
                            4 => 'bg-purple-100 text-purple-800 border-purple-200',
                            5 => 'bg-rose-100 text-rose-800 border-rose-200',
                            default => 'bg-slate-100 text-slate-700 border-slate-200'
                            };

                            $workTypeBadge = match(strtoupper($schedule->work_type ?? 'WFO')) {
                            'WFH' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                            default => 'bg-emerald-50 text-emerald-700 border-emerald-200'
                            };
                            @endphp
                            <tr class="{{ $rowBg }} hover:bg-blue-50/40 transition-colors">
                                <td class="py-3 px-3.5 text-gray-700 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($schedule->date)->format('d-m-Y') }}
                                </td>
                                <td class="py-3 px-3.5 text-gray-800 whitespace-nowrap font-semibold">
                                    {{ \Carbon\Carbon::parse($schedule->date)->locale('id')->translatedFormat('l') }}
                                </td>
                                <td class="py-3 px-3.5 text-gray-900 font-bold whitespace-nowrap">
                                    {{ $schedule->shift->name ?? '-' }}
                                </td>
                                <td class="py-3 px-3.5 text-gray-600 whitespace-nowrap font-mono text-xs md:text-sm">
                                    @if(isset($schedule->shift) && !empty($schedule->shift->start_time) && !empty($schedule->shift->end_time))
                                    {{ substr($schedule->shift->start_time, 0, 5) }} - {{ substr($schedule->shift->end_time, 0, 5) }}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 text-gray-600 whitespace-nowrap font-mono text-xs md:text-sm">
                                    @if(isset($schedule->shift))
                                    @php
                                    $effectiveStartBreak = $schedule->shift->getEffectiveStartBreakTime($schedule->date, $user);
                                    $effectiveEndBreak = $schedule->shift->getEffectiveEndBreakTime($schedule->date, $user);
                                    @endphp
                                    @if(!empty($effectiveStartBreak) && !empty($effectiveEndBreak))
                                    {{ substr($effectiveStartBreak, 0, 5) }} - {{ substr($effectiveEndBreak, 0, 5) }}
                                    @else
                                    -
                                    @endif
                                    @else
                                    -
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $workTypeBadge }}">
                                        {{ strtoupper($schedule->work_type ?? 'WFO') }}
                                    </span>
                                </td>
                                <td class="py-3 px-3.5 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusBadge }}">
                                        {{ $attdStatusName }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-10 px-4 text-center text-gray-400">
                                    <i class="fa-regular fa-calendar-xmark text-3xl mb-1 text-gray-300 block"></i>
                                    <span class="font-medium text-sm">Belum ada jadwal untuk minggu ini</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Broadcast List Modal -->
<div id="broadcastModalList" class="fixed inset-0 flex items-center justify-center z-[999] bg-black/60 backdrop-blur-sm hidden p-3 sm:p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl p-4 sm:p-5 flex flex-col max-h-[85vh]">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h2 class="text-base sm:text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-bullhorn text-blue-600"></i> Daftar Pengumuman
            </h2>
            <button onclick="closeModalBroadcastList()" class="text-gray-400 hover:text-gray-700 text-2xl font-bold leading-none">&times;</button>
        </div>
        <div class="overflow-y-auto flex-1 mt-3 divide-y divide-gray-100 pr-1">
            @forelse ($broadcast_list as $index => $broadcast)
            <div class="py-3 flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="text-xs sm:text-sm font-semibold text-gray-800 truncate">{{ $broadcast->title }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">{{ $broadcast->created_at->translatedFormat('d M Y H:i') }}</div>
                </div>
                <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-3 py-1.5 rounded-xl text-xs shrink-0 shadow-2xs transition" onclick="openModalBroadcastListbyId('{{ $broadcast->id }}')">
                    Detail
                </button>
            </div>
            @empty
            <div class="py-8 text-center text-xs text-gray-400">Tidak ada pengumuman.</div>
            @endforelse
        </div>
        <div class="flex justify-end pt-3 border-t border-gray-100 mt-2">
            <button onclick="closeModalBroadcastList()" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-xl text-xs transition">Tutup</button>
        </div>
    </div>
</div>

<!-- Popup Pengumuman Biasa (Non-Livewire, Berbasis Flash Session) -->
@if (session('firstAnnouncement'))
<div id="broadcastModal" class="fixed inset-0 flex items-center justify-center z-[999] bg-black/60 backdrop-blur-sm p-3 sm:p-4 animate-fadeIn">
    <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[88vh] shadow-2xl overflow-hidden flex flex-col my-auto border border-gray-100">
        <div class="flex justify-between items-center px-4 sm:px-6 py-3.5 sm:py-4 border-b bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex-shrink-0">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-bullhorn text-lg"></i>
                <h3 class="font-bold text-base">{{ session('firstAnnouncement')->title }}</h3>
            </div>
            <button type="button" onclick="closeModalBroadcast()" class="text-white/80 hover:text-white text-2xl font-bold leading-none p-1">&times;</button>
        </div>
        <div class="p-4 sm:p-6 overflow-y-auto space-y-3.5 sm:space-y-4 flex-1">
            @if (session('firstAnnouncement')->images && session('firstAnnouncement')->images->isNotEmpty())
            <div class="flex justify-center">
                @foreach(session('firstAnnouncement')->images as $img)
                <div class="w-full max-w-lg bg-slate-900/5 p-2 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-center overflow-hidden">
                    <a href="{{ asset('broadcast-image/' . $img->image) }}" target="_blank" class="block w-full text-center group relative overflow-hidden rounded-xl" title="Klik untuk membuka ukuran penuh">
                        <img src="{{ asset('broadcast-image/' . $img->image) }}" alt="Gambar Pengumuman" class="max-h-64 sm:max-h-72 w-auto max-w-full mx-auto object-contain rounded-xl transition-transform duration-300 group-hover:scale-[1.02]">
                    </a>
                </div>
                @endforeach
            </div>
            @endif
            <div class="text-gray-700 leading-relaxed text-justify whitespace-pre-line text-sm">
                {!! nl2br(e(session('firstAnnouncement')->message)) !!}
            </div>
        </div>
        <div class="flex justify-between items-center px-4 sm:px-6 py-3 sm:py-3.5 border-t bg-gray-50 flex-shrink-0">
            <span class="text-xs text-gray-400">Diterbitkan: {{ session('firstAnnouncement')->created_at->translatedFormat('l, d F Y H:i') }}</span>
            <button type="button" onclick="closeModalBroadcast()" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition shadow-sm">
                Saya Mengerti
            </button>
        </div>
    </div>
</div>
@endif

<!-- Livewire Real-time Broadcast & Question Popup (Hanya untuk Broadcast Terjadwal) -->
@livewire('broadcast-popup', [
'shiftId' => $todaysDetailSchedule?->shift_id,
'officeId' => $todaysDetailSchedule?->office_id,
'scheduleChecked' => true,
])

<!-- Broadcast Detail Modals -->
@foreach ($broadcast_list as $item)
<div id="broadcastModalbyId-{{ $item->id }}" class="hidden fixed inset-0 flex items-center justify-center z-[999] bg-black/60 backdrop-blur-sm p-3 sm:p-4 animate-fadeIn">
    <div class="bg-white rounded-2xl w-full max-w-3xl max-h-[88vh] shadow-2xl overflow-hidden flex flex-col my-auto border border-gray-100">
        <div class="flex justify-between items-center px-4 sm:px-6 py-3.5 sm:py-4 border-b">
            <h2 class="text-base sm:text-xl font-bold text-gray-800 truncate pr-2">📢 {{ $item->title }}</h2>
            <button onclick="closeModalBroadcastListbyId('{{ $item->id }}')" class="text-gray-400 hover:text-gray-800 text-2xl font-bold leading-none p-1 transition">&times;</button>
        </div>
        <div class="p-4 sm:p-6 max-h-[70vh] overflow-y-auto space-y-3.5 sm:space-y-4">

            {{-- [MODIFIKASI] Menampilkan gambar pengumuman --}}
            @if($item->images && $item->images->isNotEmpty())
            <div class="flex justify-center">
                @foreach($item->images as $image)
                <div class="w-full max-w-lg bg-slate-900/5 p-2 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-center overflow-hidden">
                    <a href="{{ asset('broadcast-image/' . $image->image) }}" target="_blank" class="block w-full text-center group relative overflow-hidden rounded-xl" title="Klik untuk membuka ukuran penuh">
                        <img src="{{ asset('broadcast-image/' . $image->image) }}"
                            alt="Gambar Pengumuman"
                            class="max-h-64 sm:max-h-72 w-auto max-w-full mx-auto object-contain rounded-xl transition-transform duration-300 group-hover:scale-[1.02]">
                    </a>
                </div>
                @endforeach
            </div>
            @endif

            <div class="text-gray-700 leading-relaxed text-justify whitespace-pre-line text-xs sm:text-sm">
                {!! nl2br(e($item->message)) !!}
            </div>
        </div>
        <div class="flex justify-between items-center px-4 sm:px-6 py-3 sm:py-4 border-t bg-gray-50">
            <span class="text-xs text-gray-500">Diterbitkan: {{ $item->created_at->translatedFormat('l, d F Y H:i') }}</span>
            <button type="button" onclick="closeModalBroadcastListbyId('{{ $item->id }}')" class="px-4 py-2 bg-gray-800 text-white rounded-xl hover:bg-gray-700 text-xs sm:text-sm transition">Tutup</button>
        </div>
    </div>
</div>
@endforeach

<!-- ==================================================================== -->
<!-- UNIFIED LOGBOOK HARIAN MODAL (2 TAB: LAPORAN HARI INI & RIWAYAT) -->
<!-- Catatan: Logbook Harian kini berada di halaman khusus (route: user.logbook.index) -->

<!-- Information & Holiday Modal (3 Tabs: Hari Libur, SOP Magang, Peraturan Kantor) -->
<div id="holidayModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden z-[9999] p-3 sm:p-4">
    <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-fadeIn">
        <!-- Modal Header -->
        <div class="flex justify-between items-center px-4 sm:px-6 py-3 sm:py-4 border-b bg-gray-800 text-white shrink-0">
            <div class="flex items-center space-x-2 min-w-0 pr-2">
                <i class="fas fa-info-circle text-blue-400 text-lg sm:text-xl shrink-0"></i>
                <h2 class="text-base sm:text-lg md:text-xl font-bold truncate">Informasi & Tata Tertib Magang</h2>
            </div>
            <button type="button" onclick="closeHolidayModal()" class="text-gray-300 hover:text-white text-2xl font-bold leading-none p-1 shrink-0">&times;</button>
        </div>

        <!-- Tabs Navigation -->
        <div class="flex border-b border-gray-200 bg-gray-50 text-xs sm:text-sm font-medium overflow-x-auto shrink-0">
            <button type="button" onclick="switchInfoTab('tab-holiday')" id="btn-tab-holiday"
                class="info-tab-btn flex-1 py-3 px-2 sm:px-4 text-center border-b-2 border-blue-600 text-blue-600 font-semibold focus:outline-none flex items-center justify-center gap-1.5 whitespace-nowrap">
                <i class="fa-regular fa-calendar-days"></i>
                <span>Hari Libur</span>
            </button>
            <button type="button" onclick="switchInfoTab('tab-sop')" id="btn-tab-sop"
                class="info-tab-btn flex-1 py-3 px-2 sm:px-4 text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none flex items-center justify-center gap-1.5 whitespace-nowrap">
                <i class="fa-regular fa-file-lines"></i>
                <span>SOP Magang</span>
            </button>
            <button type="button" onclick="switchInfoTab('tab-rules')" id="btn-tab-rules"
                class="info-tab-btn flex-1 py-3 px-2 sm:px-4 text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none flex items-center justify-center gap-1.5 whitespace-nowrap">
                <i class="fas fa-building"></i>
                <span>Peraturan Kantor</span>
            </button>
            <button type="button" onclick="switchInfoTab('tab-piket')" id="btn-tab-piket"
                class="info-tab-btn flex-1 py-3 px-2 sm:px-4 text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none flex items-center justify-center gap-1.5 whitespace-nowrap">
                <i class="fa-solid fa-broom"></i>
                <span>Jadwal Piket</span>
            </button>
        </div>

        <!-- Modal Body (Tab Contents) -->
        <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-3.5 sm:space-y-4">

            <!-- TAB 1: HARI LIBUR -->
            <div id="content-tab-holiday" class="info-tab-content space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-stone-800 text-base flex items-center gap-2">
                        <i class="fas fa-calendar-check text-green-600"></i> Kalender Hari Libur & Cuti Bersama
                    </h3>
                    <span class="text-xs text-gray-500">Tahun {{ now()->year }}</span>
                </div>
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <div class="overflow-x-auto max-h-72">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="sticky top-0 bg-gray-100 text-gray-700 font-semibold">
                                <tr>
                                    <th class="py-2.5 px-3 w-12 text-center">No</th>
                                    <th class="py-2.5 px-4 w-44">Tanggal</th>
                                    <th class="py-2.5 px-4">Nama Hari Libur</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($holiday_data as $index => $holiday)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="py-2 px-3 text-center text-gray-500 font-medium">{{ $index + 1 }}</td>
                                    <td class="py-2 px-4 whitespace-nowrap text-gray-800 font-medium">
                                        {{ \Carbon\Carbon::parse($holiday->date)->locale('id')->isoFormat('dddd, DD MMMM YYYY') }}
                                    </td>
                                    <td class="py-2 px-4 text-gray-700">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                            {{ $holiday->name }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-gray-400">
                                        <i class="far fa-calendar-times text-2xl mb-1 block"></i>
                                        Tidak ada data hari libur yang tercatat.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: SOP MAGANG -->
            <div id="content-tab-sop" class="info-tab-content hidden space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start space-x-3">
                    <div class="text-blue-600 text-xl mt-0.5"><i class="fas fa-file-contract"></i></div>
                    <div>
                        <h4 class="font-bold text-blue-900 text-sm">Standar Operasional Prosedur (SOP) Magang</h4>
                        <p class="text-xs text-blue-700 mt-0.5">Tata tertib dan pedoman pelaksanaan magang di lingkungan Djuragan.</p>
                    </div>
                </div>

                <div class="space-y-3 text-sm text-gray-700">
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <h5 class="font-semibold text-gray-900 flex items-center gap-2 mb-1">
                            <i class="fas fa-clock text-blue-600 text-xs"></i> 1. Kehadiran & Jam Kerja
                        </h5>
                        <ul class="list-disc list-inside text-xs text-gray-600 space-y-1 ml-1">
                            <li>Pemagang wajib melakukan presensi masuk dan pulang sesuai shift yang berlaku.</li>
                            <li>Keterlambatan atau kekurangan jam kerja wajib diganti melalui menu Ganti Jam.</li>
                            <li>Waktu istirahat maksimal sesuai durasi shift yang ditentukan.</li>
                        </ul>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <h5 class="font-semibold text-gray-900 flex items-center gap-2 mb-1">
                            <i class="fas fa-user-check text-green-600 text-xs"></i> 2. Ketentuan Perizinan
                        </h5>
                        <ul class="list-disc list-inside text-xs text-gray-600 space-y-1 ml-1">
                            <li>Izin tidak hadir (sakit/keperluan lain) wajib mengajukan form izin dengan melampirkan surat bukti.</li>
                            <li>Izin keluar lingkungan kantor harus mendapatkan persetujuan dari HR / Atasan.</li>
                            <li>Izin sholat dan toilet wajib dicatat melalui sistem presensi.</li>
                        </ul>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <h5 class="font-semibold text-gray-900 flex items-center gap-2 mb-1">
                            <i class="fas fa-tasks text-purple-600 text-xs"></i> 3. Tugas & Laporan Harian
                        </h5>
                        <ul class="list-disc list-inside text-xs text-gray-600 space-y-1 ml-1">
                            <li>Pemagang wajib mengisi <strong>Log Activity</strong> harian sebelum mengklik tombol pulang.</li>
                            <li>Menjaga kerahasiaan data perusahaan, klien, dan project yang sedang dikerjakan.</li>
                        </ul>
                    </div>
                </div>

                @php
                $sopLink = $userOffice->sop_url ?? 'https://docs.google.com/document/d/sop-magang-djuragan';
                @endphp
                <div class="pt-2 flex justify-center">
                    <a href="{{ $sopLink }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium text-sm transition shadow-sm hover:shadow">
                        <i class="fas fa-file-pdf mr-2 text-base"></i>
                        <span>Buka Dokumen SOP Lengkap</span>
                        <i class="fas fa-arrow-up-right-from-square ml-2 text-xs"></i>
                    </a>
                </div>
            </div>

            <!-- TAB 3: PERATURAN KANTOR -->
            <div id="content-tab-rules" class="info-tab-content hidden space-y-4">
                <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 flex items-center space-x-3">
                    <div class="text-indigo-600 text-xl"><i class="fas fa-building"></i></div>
                    <div>
                        <h4 class="font-bold text-indigo-900 text-sm">
                            Peraturan Penempatan: {{ $userOffice->name ?? 'Kantor Djuragan' }}
                        </h4>
                    </div>
                </div>

                <!-- Detail Poin Aturan Kantor -->
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-sm text-gray-700 space-y-3">
                    <h5 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-clipboard-list text-indigo-600"></i> Tata Tertib Khusus Kantor Ini:
                    </h5>
                    @if(!empty($userOffice->rules_description))
                    <div class="text-xs text-gray-600 whitespace-pre-line leading-relaxed pl-1">
                        {!! nl2br(e($userOffice->rules_description)) !!}
                    </div>
                    @else
                    <ul class="list-disc list-inside text-xs text-gray-600 space-y-1.5 pl-1">
                        <li>Wajib hadir dan absen tepat waktu sesuai shift.</li>
                        <li>Berpakaian rapi, sopan, dan mengenakan tanda pengenal/ID Card magang.</li>
                        <li>Menjaga ketertiban, kebersihan ruang kerja, dan kenyamanan lingkungan kantor.</li>
                        <li>Mematuhi batas waktu istirahat dan konfirmasi ke atasan jika ada keperluan izin.</li>
                        <li>Dilarang merokok di area tertutup dan menjaga fasilitas kerja dengan baik.</li>
                    </ul>
                    @endif
                </div>

                @php
                $rulesLink = $userOffice->rules_url ?? 'https://docs.google.com/document/d/peraturan-kantor-djuragan';
                @endphp
                <div class="pt-2 flex justify-center">
                    <a href="{{ $rulesLink }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium text-sm transition shadow-sm hover:shadow">
                        <i class="fas fa-book-open mr-2 text-base"></i>
                        <span>Buka Dokumen Peraturan Kantor</span>
                        <i class="fas fa-arrow-up-right-from-square ml-2 text-xs"></i>
                    </a>
                </div>
            </div>

            <!-- TAB 4: JADWAL PIKET -->
            <div id="content-tab-piket" class="info-tab-content hidden space-y-4">
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start space-x-3">
                    <div class="text-amber-600 text-xl mt-0.5"><i class="fa-solid fa-broom"></i></div>
                    <div>
                        <h4 class="font-bold text-amber-900 text-sm">
                            Jadwal & Ketentuan Piket: {{ $userOffice->name ?? 'Kantor Djuragan' }}
                        </h4>
                        <p class="text-xs text-amber-700 mt-0.5">
                            Pembagian tugas kebersihan dan ketertiban ruang kerja harian bagi pemagang.
                        </p>
                    </div>
                </div>

                <!-- Detail Poin Aturan & Tugas Piket -->
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-sm text-gray-700 space-y-3">
                    <h5 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-check text-amber-600"></i> Ketentuan & Tugas Piket Harian:
                    </h5>
                    @if(!empty($userOffice->piket_description))
                    <div class="text-xs text-gray-600 whitespace-pre-line leading-relaxed pl-1">
                        {!! nl2br(e($userOffice->piket_description)) !!}
                    </div>
                    @else
                    <ul class="list-disc list-inside text-xs text-gray-600 space-y-1.5 pl-1">
                        <li>Petugas piket hadir 15 menit lebih awal untuk mempersiapkan ruang kerja.</li>
                        <li>Menyapu lantai, merapikan meja kerja bersama, dan memastikan ruang kerja bersih.</li>
                        <li>Membuang sampah harian ke tempat pembuangan luar kantor di akhir jam kerja.</li>
                        <li>Memastikan seluruh AC, dispenser, dan lampu dimatikan sebelum meninggalkan ruangan.</li>
                    </ul>
                    @endif
                </div>

                @php
                $piketLink = $userOffice->piket_url ?? 'https://docs.google.com/spreadsheets/d/jadwal-piket-djuragan';
                @endphp
                <div class="pt-2 flex justify-center">
                    <a href="{{ $piketLink }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-medium text-sm transition shadow-sm hover:shadow">
                        <i class="fa-solid fa-table-cells mr-2 text-base"></i>
                        <span>Buka Dokumen Jadwal Piket</span>
                        <i class="fas fa-arrow-up-right-from-square ml-2 text-xs"></i>
                    </a>
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end px-4 sm:px-6 py-3 border-t bg-stone-50 shrink-0">
            <button type="button" onclick="closeHolidayModal()" class="px-5 py-2 bg-gray-800 hover:bg-gray-700 text-white font-medium text-xs sm:text-sm rounded-xl transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Action Modal -->
<div id="actionModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center hidden z-50 p-3 sm:p-4">
    <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all w-full max-w-md mx-auto max-h-[90vh] flex flex-col">
        <div class="bg-slate-800 text-white px-4 sm:px-5 py-3 sm:py-3.5 flex justify-between items-center shrink-0">
            <h3 id="modalTitle" class="text-base sm:text-lg leading-6 font-semibold">Keterangan Presensi</h3>
            <button type="button" onclick="closeActionModal()" class="text-gray-300 hover:text-white text-2xl font-bold leading-none p-1">&times;</button>
        </div>
        <div id="modalContent" class="p-4 sm:p-5 flex-1 overflow-y-auto">
            <textarea id="modalTextarea" class="w-full border border-gray-300 rounded-xl shadow-xs bg-slate-50 p-3 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" rows="4"
                placeholder="Tuliskan keterangan (opsional)"></textarea>
            <div class="flex justify-end items-center gap-2 mt-4 shrink-0">
                <button type="button" onclick="closeActionModal()" class="px-4 py-2 border border-gray-300 text-gray-700 text-xs sm:text-sm font-medium rounded-xl hover:bg-gray-100 transition">
                    Batal
                </button>
                <button type="button" id="save-attd" class="bg-slate-800 hover:bg-slate-700 text-white font-semibold py-2 px-5 sm:px-6 rounded-xl text-xs sm:text-sm shadow transition">
                    Simpan
                </button>
                <svg aria-hidden="true" id="loading-spinner" class="w-7 h-7 ml-2 text-gray-200 animate-spin hidden fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor" />
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill" />
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Geo Notification Modal -->
<div id="geoNotification" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50 hidden p-3 sm:p-4">
    <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all w-full max-w-md mx-auto">
        <div class="bg-red-700 text-white p-4">
            <h3 class="text-base sm:text-lg leading-6 font-bold">Izin Lokasi Diperlukan</h3>
        </div>
        <div class="p-4 sm:p-5">
            <p class="text-gray-700 text-xs sm:text-sm leading-relaxed">
                Aplikasi ini memerlukan akses ke lokasi Anda untuk memvalidasi presensi. Silakan berikan izin GPS pada peramban Anda untuk melanjutkan.
            </p>
        </div>
        <div class="bg-gray-50 px-4 py-3 flex justify-end gap-2 border-t border-gray-100">
            <button id="denyButton" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-xs font-semibold hover:bg-gray-300 transition">
                Tolak
            </button>
            <button id="allowButton" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-xs font-semibold hover:bg-blue-700 transition">
                Izinkan
            </button>
        </div>
    </div>
</div>

<!-- UNIFIED POPUP MODAL: FORM IZIN TIDAK MASUK (SAKIT & KEPERLUAN) -->
<div id="modal2" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden z-[9999] p-3 sm:p-4" onclick="if(event.target === this) closeModalIzin();">
    <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-2xl w-full max-w-md mx-auto relative animate-fade-in max-h-[92vh] flex flex-col" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div class="flex items-center gap-3 mb-3.5 sm:mb-4 shrink-0 pr-8">
            <div id="modalPermitIcon" class="p-2 sm:p-2.5 bg-emerald-100 text-emerald-700 rounded-xl transition-colors shrink-0">
                <i class="fa-solid fa-notes-medical text-base sm:text-lg"></i>
            </div>
            <div class="min-w-0">
                <h2 class="text-base sm:text-lg font-bold text-slate-800 truncate">Form Izin Tidak Masuk</h2>
                <p class="text-xs sm:text-sm text-slate-500 truncate">Pilih jenis izin sakit atau izin keperluan Anda.</p>
            </div>
        </div>

        <!-- Scrollable Form Area -->
        <div class="overflow-y-auto flex-1 pr-0.5 space-y-3.5">
            <!-- Segmented Control / Type Selector -->
            <div class="grid grid-cols-2 p-1 bg-slate-100 rounded-xl text-xs font-bold shrink-0">
                <button type="button" id="tabBtnSakit" onclick="switchPermitType('sakit')"
                    class="py-2 rounded-lg transition-all bg-white text-emerald-700 shadow-xs flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-notes-medical"></i> Izin Sakit
                </button>
                <button type="button" id="tabBtnKeperluan" onclick="switchPermitType('keperluan')"
                    class="py-2 rounded-lg transition-all text-slate-600 hover:text-slate-900 flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-user-clock"></i> Izin Keperluan
                </button>
            </div>

            <!-- Notice Box -->
            <div id="noticeSakit" class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 space-y-1">
                <div class="font-semibold flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i> Ketentuan Izin Sakit:
                </div>
                <p class="leading-relaxed">Wajib melampirkan link Google Drive bukti foto surat dokter resmi agar disetujui <strong>Bebas Ganti Jam (Lunas)</strong>.</p>
            </div>

            <div id="noticeKeperluan" class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 space-y-1 hidden">
                <div class="font-semibold flex items-center gap-1.5">
                    <i class="fa-solid fa-clock-rotate-left text-amber-600"></i> Ketentuan Ganti Jam:
                </div>
                <p class="leading-relaxed">Wajib melampirkan <strong>link Google Drive bukti keperluan</strong>. Izin keperluan biasa juga <strong>wajib mengganti jam kerja</strong> sesuai durasi shift yang ditinggalkan.</p>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('attendance.addPermitPresenceUser') }}" class="space-y-3.5">
                @csrf
                <input type="hidden" id="form_jam_option" name="jam-option" value="0" />
                <input type="hidden" name="id" value="{{ $detail_schedule_id ?? 0 }}" />
                <input type="hidden" id="form_kategori_izin" name="kategori-izin" value="{{ old('kategori-izin', 1) }}" />

                <!-- Category Sub-select (Only for Keperluan) -->
                <div id="fieldKeperluanKategori" class="hidden">
                    <label for="keperluan_sub_select" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Jenis Keperluan <span class="text-rose-500">*</span>
                    </label>
                    <select id="keperluan_sub_select" onchange="document.getElementById('form_kategori_izin').value = this.value;"
                        class="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        <option value="3" @selected((int) old('kategori-izin')===3)>📋 Keperluan Sekolah / Kampus (Ujian, Dispensasi, dsb)</option>
                        <option value="4" @selected((int) old('kategori-izin')===4)>📁 Keperluan Pribadi / Keluarga / Lainnya</option>
                    </select>
                </div>

                <!-- Description -->
                <div>
                    <label id="labelKeterangan" for="form_keterangan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Keluhan / Kondisi Sakit <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="form_keterangan" name="keterangan" rows="3"
                        placeholder="Tuliskan keluhan atau diagnosis singkat sakit Anda..."
                        class="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>{{ old('keterangan') }}</textarea>
                </div>

                <!-- Link GDrive -->
                <div>
                    <label id="labelProof" for="form_proof_url" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Link Google Drive Surat Dokter <span id="proofRequiredStar" class="text-rose-500">*</span>
                    </label>
                    <input type="url" id="form_proof_url" name="link-google-drive"
                        placeholder="https://drive.google.com/file/d/..."
                        value="{{ old('link-google-drive') }}"
                        class="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required />
                    <p id="proofHelpText" class="text-xs text-slate-400 mt-1">Pastikan akses link Google Drive diset ke 'Anyone with link / Siapa saja memiliki link'.</p>
                    @error('link-google-drive')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex justify-end items-center gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeModalIzin();" class="px-4 py-2 border border-slate-300 text-slate-700 font-semibold rounded-xl text-xs hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitPermit" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-xl text-xs shadow-sm transition">
                        Kirim Izin Sakit
                    </button>
                </div>
            </form>
        </div>
        <button class="absolute top-3.5 sm:top-4 right-3.5 sm:right-4 text-slate-400 hover:text-slate-600 transition p-1" onclick="closeModalIzin();">
            <i class="fas fa-times text-base sm:text-lg"></i>
        </button>
    </div>
</div>

<!-- ==================================================================== -->
<!-- POPUP MODAL: FORMULIR SESI GANTI JAM KERJA (PEMAGANG)                -->
<!-- ==================================================================== -->
<div id="changeTimeModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden z-[9999] p-3 sm:p-4" onclick="if(event.target === this) closeChangeTimeModal();">
    <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-2xl w-full max-w-lg mx-auto relative animate-fade-in max-h-[92vh] flex flex-col" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div class="flex items-center gap-3 mb-3.5 sm:mb-4 shrink-0 pr-8 border-b border-gray-100 pb-3">
            <div class="p-2 sm:p-2.5 bg-amber-100 text-amber-700 rounded-xl shrink-0">
                <i class="fa-solid fa-calendar-plus text-base sm:text-lg"></i>
            </div>
            <div class="min-w-0">
                <h2 class="text-base sm:text-lg font-bold text-slate-800 truncate">Pendaftaran Rencana Ganti Jam</h2>
                <p class="text-xs text-slate-500 truncate">Pilih tanggal dan shift yang direncanakan untuk disetujui Admin.</p>
            </div>
        </div>

        <form id="formDaftarGantiJam" action="{{ route('user.change-time.register') }}" method="POST" class="flex flex-col flex-1 overflow-hidden" onsubmit="event.preventDefault(); submitChangeTimeForm();">
            @csrf

            <!-- Scrollable Body -->
            <div class="overflow-y-auto flex-1 px-1 py-1 space-y-4 text-xs no-scrollbar">

                <!-- Note Otomatis dari Admin jika diset (Fitur B) -->
                @if(!empty($changeTimeSetting?->intern_notice_text))
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2.5 text-amber-900">
                    <i class="fa-solid fa-circle-info text-amber-600 mt-0.5 shrink-0 text-xs"></i>
                    <div class="text-xs text-amber-900 leading-relaxed">
                        <span class="font-bold text-amber-950">Catatan:</span> {{ $changeTimeSetting->intern_notice_text }}
                    </div>
                </div>
                @else
                <!-- Info Box Ketentuan -->
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl flex items-start gap-2.5 text-blue-900">
                    <i class="fa-solid fa-circle-info text-blue-600 mt-0.5 shrink-0 text-xs"></i>
                    <div class="text-xs text-blue-900 leading-relaxed space-y-1">
                        <p><strong>Ketentuan Ganti Jam:</strong> Pilih hari/tanggal dan shift yang Anda rencanakan. Admin dapat menyesuaikan jam/shift dan menyetujui pendaftaran Anda.</p>
                    </div>
                </div>
                @endif

                <!-- Input 1: Hari / Tanggal Rencana (Wajib) -->
                <div>
                    <label for="reg_requested_date" class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                        Hari / Tanggal Rencana Ganti Jam <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="reg_requested_date" name="requested_date" required
                        min="{{ \Carbon\Carbon::tomorrow('Asia/Jakarta')->toDateString() }}" value="{{ \Carbon\Carbon::tomorrow('Asia/Jakarta')->toDateString() }}"
                        class="w-full p-2.5 text-xs text-slate-800 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white">
                </div>

                <!-- Input 2: Pilihan Shift (Wajib) -->
                <div>
                    <label for="reg_shift_id" class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                        Shift yang Dipilih <span class="text-rose-500">*</span>
                    </label>
                    <select id="reg_shift_id" name="shift_id" required
                        class="w-full p-2.5 text-xs text-slate-800 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white cursor-pointer">
                        <option value="" disabled selected>-- Pilih Shift Kerja --</option>
                        @if(isset($shiftsForChangeTime) && $shiftsForChangeTime->isNotEmpty())
                        @foreach($shiftsForChangeTime as $sh)
                        <option value="{{ $sh->id }}">
                            {{ $sh->name }} ({{ substr($sh->start_time, 0, 5) }} - {{ substr($sh->end_time, 0, 5) }})
                        </option>
                        @endforeach
                        @endif
                    </select>
                </div>

                <!-- Input 3: Keterangan Ganti Jam -->
                <div>
                    <label for="change_time_notes" class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                        Keterangan Ganti Jam <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="change_time_notes" name="reason" rows="3" required
                        placeholder="Contoh: Mengganti kekurangan jam kerja minggu lalu..."
                        class="w-full p-3 text-xs text-slate-800 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white leading-relaxed"></textarea>
                    <p class="text-[10px] text-slate-400 mt-1">Admin dapat membalas via chat untuk menyesuaikan jam atau memberikan arahan.</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-2 pt-3 sm:pt-4 border-t border-gray-100 mt-2 shrink-0">
                <button type="button" onclick="closeChangeTimeModal();" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-100 text-xs font-semibold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btn-submit-change-time"
                    class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs shadow-md transition flex items-center gap-1.5 cursor-pointer"
                    style="background-color: #ea580c !important;">
                    <i class="fa-solid fa-paper-plane text-[11px] text-white"></i>
                    <span class="text-white font-bold tracking-wide">Ajukan Pendaftaran Ganti Jam</span>
                </button>
                <div id="spinner-change-time"
                    class="hidden items-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-xl text-xs font-bold"
                    style="background-color: #ea580c !important;">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-white font-semibold">Memproses Pendaftaran...</span>
                </div>
            </div>
        </form>

        <!-- Close button top right -->
        <button type="button" class="absolute top-3.5 sm:top-4 right-3.5 sm:right-4 text-slate-400 hover:text-slate-600 transition p-1 focus:outline-none" onclick="closeChangeTimeModal();">
            <i class="fas fa-times text-base sm:text-lg"></i>
        </button>
    </div>
</div>

<!-- ==================================================================== -->
<!-- [BARU] MODAL UNTUK IZIN KE TOILET (TANPA TIMER) -->
<!-- ==================================================================== -->
<div id="toiletPermitModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center hidden z-[999] p-3 sm:p-4">
    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl p-5 sm:p-7 w-full max-w-xs sm:max-w-sm mx-auto text-center border border-gray-100 max-h-[90vh] flex flex-col justify-center">

        <div class="mb-3 sm:mb-4">
            <div class="w-12 h-12 sm:w-14 sm:h-14 mx-auto rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i class="fa-solid fa-restroom text-xl sm:text-2xl"></i>
            </div>
        </div>

        <h3 class="text-lg sm:text-2xl font-bold text-gray-800">Sedang Izin ke Toilet</h3>

        <p class="text-gray-600 text-xs sm:text-sm mt-1.5 mb-4 sm:mb-6 leading-relaxed">
            Silakan kembali jika sudah selesai.
        </p>

        <button id="returnFromToiletBtn" class="w-full px-4 py-2.5 sm:py-3 bg-green-600 text-white font-semibold rounded-xl shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 text-xs sm:text-sm transition">
            <i class="fas fa-check mr-2"></i> Kembali dari Izin
        </button>
    </div>
</div>

{{-- ============================================================ --}}
{{-- POPUP CHECK-IN: dibangun via JS (showCheckinPopup di          --}}
{{-- public/js/user/index.js) dari event Livewire                  --}}
{{-- 'show-checkin-popup' yang di-dispatch AttdStatusButton.       --}}
{{-- ============================================================ --}}


<!-- ==================================================================== -->
<!-- [BARU] WADAH UNTUK NOTIFIKASI DARI SAMPING (TOAST) -->
<!-- ==================================================================== -->
<div id="toast-notification" class="hidden fixed top-5 right-5 w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg z-[9999]" role="alert">
    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg">
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
        </svg>
    </div>
    <div class="ms-3 text-sm font-normal" id="toast-message">Pesan notifikasi.</div>
    <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg p-1.5 inline-flex items-center justify-center h-8 w-8" onclick="document.getElementById('toast-notification').classList.add('hidden')">
        <span class="sr-only">Close</span>
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
    </button>
</div>



<!-- Modal Raise Hand (3 Mode: Bertanya, Permintaan Tugas Baru, Penjadwalan Presentasi) -->
<div id="raiseHandModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden z-[9999] p-3 sm:p-4">
    <div class="bg-white rounded-2xl w-full max-w-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-fadeIn">
        <!-- Header -->
        <div class="flex justify-between items-center px-4 sm:px-6 py-3.5 sm:py-4 border-b bg-gray-800 text-white shrink-0">
            <div class="flex items-center space-x-2.5 min-w-0 pr-2">
                <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center shrink-0">
                    <i class="fas fa-hand-paper text-base"></i>
                </div>
                <div class="min-w-0">
                    <h2 class="text-base sm:text-lg font-bold truncate">Angkat Tangan / Butuh Bantuan</h2>
                    <p class="text-xs sm:text-sm text-gray-300 truncate">Pilih kategori bantuan yang ingin diajukan ke mentor/admin</p>
                </div>
            </div>
            <button type="button" onclick="closeRaiseHandModal()" class="text-gray-300 hover:text-white text-2xl font-bold leading-none p-1 shrink-0">&times;</button>
        </div>

        <!-- Form Body -->
        <form action="{{ route('intern.raisehand.toggle') }}" method="POST" class="flex flex-col flex-1 overflow-y-auto no-scrollbar">
            @csrf
            <div class="p-4 sm:p-6 space-y-3.5 sm:space-y-4 flex-1">
                <!-- Mode Selection Tabs / Radio Cards -->
                <div>
                    <span class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Pilih Kategori Bantuan <span class="text-red-500">*</span>
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <!-- Card 1: Tanya Jawab -->
                        <label class="raise-mode-card relative flex flex-col p-2.5 sm:p-3 rounded-xl border-2 cursor-pointer transition-all border-blue-600 bg-blue-50/50" id="card-mode-question">
                            <input type="radio" name="type" value="question" class="sr-only" checked onchange="switchRaiseMode('question')">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fa-solid fa-comments text-blue-600 text-sm"></i>
                                <span class="text-xs font-bold text-gray-900">Bertanya</span>
                            </div>
                            <span class="text-xs text-gray-500 leading-tight">Konsultasi kendala teknis / materi</span>
                        </label>

                        <!-- Card 2: Tugas Baru -->
                        <label class="raise-mode-card relative flex flex-col p-2.5 sm:p-3 rounded-xl border-2 border-gray-200 hover:border-gray-300 cursor-pointer transition-all" id="card-mode-new_task">
                            <input type="radio" name="type" value="new_task" class="sr-only" onchange="switchRaiseMode('new_task')">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fa-solid fa-list-check text-purple-600 text-sm"></i>
                                <span class="text-xs font-bold text-gray-900">Tugas Baru</span>
                            </div>
                            <span class="text-xs text-gray-500 leading-tight">Minta modul / tugas berikutnya</span>
                        </label>

                        <!-- Card 3: Presentasi -->
                        <label class="raise-mode-card relative flex flex-col p-2.5 sm:p-3 rounded-xl border-2 border-gray-200 hover:border-gray-300 cursor-pointer transition-all" id="card-mode-presentation">
                            <input type="radio" name="type" value="presentation" class="sr-only" onchange="switchRaiseMode('presentation')">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fa-solid fa-chalkboard-user text-amber-600 text-sm"></i>
                                <span class="text-xs font-bold text-gray-900">Presentasi</span>
                            </div>
                            <span class="text-xs text-gray-500 leading-tight">Jadwal uji hasil modul/project</span>
                        </label>
                    </div>
                </div>

                <!-- Panel 1: Mode Bertanya -->
                <div id="panel-raise-question" class="space-y-3">
                    <div class="text-xs text-gray-500 flex items-start gap-2">
                        <i class="fa-solid fa-circle-info text-blue-500 mt-0.5 shrink-0 text-xs"></i>
                        <span class="leading-relaxed">Jelaskan kendala atau pertanyaan Anda secara spesifik agar mentor dapat membantu dengan cepat.</span>
                    </div>
                    <div>
                        <label for="notes-question" class="block text-xs font-semibold text-gray-700 mb-1">
                            Detail Pertanyaan / Kendala <span class="text-red-500">*</span>
                        </label>
                        <textarea name="notes" id="notes-question" rows="3" class="w-full text-xs p-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Mengalami error koneksi database saat menjalankan migration Laravel..."></textarea>
                    </div>
                </div>

                <!-- Panel 2: Mode Tugas Baru -->
                <div id="panel-raise-new_task" class="space-y-3 hidden">
                    <div class="text-xs text-gray-500 flex items-start gap-2">
                        <i class="fa-solid fa-circle-info text-purple-500 mt-0.5 shrink-0 text-xs"></i>
                        <span class="leading-relaxed">Gunakan opsi ini jika tugas Anda sebelumnya sudah selesai dan membutuhkan arahan pengerjaan tugas berikutnya.</span>
                    </div>
                    <div>
                        <label for="notes-new_task" class="block text-xs font-semibold text-gray-700 mb-1">
                            Keterangan Tugas Selesai & Permintaan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="notes" id="notes-new_task" rows="3" disabled class="w-full text-xs p-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Contoh: Modul desain UI/UX sudah selesai dan diserahkan ke repo/GDrive. Mohon arahan modul selanjutnya..."></textarea>
                    </div>
                </div>

                <!-- Panel 3: Mode Presentasi -->
                <div id="panel-raise-presentation" class="space-y-3 hidden">
                    <div class="text-xs text-gray-500 flex items-start gap-2">
                        <i class="fa-solid fa-lightbulb text-amber-500 mt-0.5 shrink-0 text-xs"></i>
                        <span class="leading-relaxed">Mentor akan mereview materi presentasi Anda dan memberikan evaluasi performa setelah presentasi selesai.</span>
                    </div>

                    @if(isset($activeProjects) && $activeProjects->count() > 0)
                    <div>
                        <label for="select-project-presentation" class="block text-xs font-semibold text-gray-700 mb-1">
                            Project yang Sedang Dikerjakan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="project_id" id="select-project-presentation" onchange="onPresentationProjectChange(this)" disabled class="w-full text-xs p-2.5 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 appearance-none pr-8">
                                @foreach($activeProjects as $actProject)
                                @php
                                $projTitle = $actProject->nameProject->name ?? ('Project #' . $actProject->id);
                                @endphp
                                <option value="{{ $actProject->id }}" data-title="{{ $projTitle }}" {{ $loop->first ? 'selected' : '' }}>
                                    📁 {{ $projTitle }} (Sedang Dikerjakan)
                                </option>
                                @endforeach
                                <option value="" data-title="">✏️ Lainnya / Judul Manual</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-gray-500">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Otomatis diarahkan ke project aktif Anda agar tidak perlu mengisi manual secara keseluruhan.</p>
                    </div>
                    @endif

                    <div>
                        <label for="notes-presentation" class="block text-xs font-semibold text-gray-700 mb-1">
                            Judul / Materi Presentasi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="notes" id="notes-presentation" disabled
                            value="{{ isset($activeProjects) && $activeProjects->count() > 0 ? ($activeProjects->first()->nameProject->name ?? '') : '' }}"
                            class="w-full text-xs p-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            placeholder="Contoh: Presentasi Modul Autentikasi dan API Resource">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                        <div>
                            <label for="presentation_date_input" class="block text-xs font-semibold text-gray-700 mb-1">
                                Tanggal Presentasi <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="presentation_date" id="presentation_date_input" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" onchange="checkPresentationUrgency(this.value)" class="w-full text-xs p-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        </div>
                        <div>
                            <label for="presentation_mode_select" class="block text-xs font-semibold text-gray-700 mb-1">
                                Mode Presentasi <span class="text-red-500">*</span>
                            </label>
                            <select name="presentation_mode" id="presentation_mode_select" class="w-full text-xs p-2.5 border border-gray-300 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <option value="offline">🏢 Tatap Muka</option>
                                <option value="online">💻 Online (GMeet)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Schedule Notice Badge -->
                    <div id="urgency-notice-badge" class="p-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center gap-2.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-600 text-white shrink-0">
                            TERJADWAL
                        </span>
                        <span class="text-xs text-blue-800 font-medium">
                            Jadwal diajukan untuk <strong>Hari Ini</strong>.
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-4 sm:px-6 py-3 bg-gray-50 border-t border-gray-200 flex justify-end gap-2 shrink-0">
                <button type="button" onclick="closeRaiseHandModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl text-xs font-medium hover:bg-gray-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-paper-plane text-xs"></i> Kirim Angkat Tangan
                </button>
            </div>
        </form>
    </div>
</div>



<!-- Modal Popup Detail Pengajuan Presentasi (Muncul Otomatis Setelah Mengajukan Presentasi) -->
<div id="onlineMeetModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden z-[99999] p-3 sm:p-4 transition-all duration-200">
    <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-md max-h-[90vh] shadow-2xl overflow-hidden flex flex-col border border-slate-100 transform transition-all duration-200">
        <!-- Header -->
        <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-blue-700 text-white p-4 sm:p-5 text-center relative shadow-sm shrink-0">
            <button type="button" onclick="closeOnlineMeetModal()" class="absolute top-3.5 right-3.5 w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white/90 hover:text-white flex items-center justify-center transition cursor-pointer" title="Tutup">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
            <div class="w-11 h-11 sm:w-12 sm:h-12 mx-auto rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-lg sm:text-xl mb-2 sm:mb-2.5 shadow-inner border border-white/20">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
            <h3 class="text-sm sm:text-base font-bold tracking-tight">Pengajuan Presentasi Terkirim</h3>
            <p class="text-xs sm:text-sm text-indigo-100 mt-0.5 font-medium">
                Divisi: {{ session('presentation_detail.division_name') ?? (session('division_name') ?? ($user->intern?->division?->name ?? 'Divisi')) }}
            </p>
        </div>

        <!-- Body -->
        <div class="p-4 sm:p-5 space-y-3 sm:space-y-3.5 overflow-y-auto flex-1 no-scrollbar">
            <!-- Ringkasan Pengajuan -->
            <div class="p-3.5 sm:p-4 bg-slate-50/90 border border-slate-200 rounded-2xl text-xs space-y-2.5">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-slate-500 font-medium shrink-0">Materi / Judul:</span>
                    <strong class="text-slate-900 text-right truncate max-w-[220px]">{{ session('presentation_detail.title') ?? 'Presentasi Modul' }}</strong>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-slate-500 font-medium shrink-0">Tanggal Diajukan:</span>
                    <span class="font-semibold text-slate-800">{{ session('presentation_detail.date') ?? date('d M Y') }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-slate-500 font-medium shrink-0">Waktu / Jam:</span>
                    <span class="inline-flex items-center gap-1 font-semibold text-amber-800 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-lg text-xs">
                        <i class="fa-regular fa-clock text-amber-600 text-xs"></i>
                        <span>Menunggu konfirmasi mentor</span>
                    </span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-slate-500 font-medium shrink-0">Mode Presentasi:</span>
                    <span class="font-bold uppercase {{ (session('presentation_detail.mode') ?? (session('show_online_meet_modal') ? 'online' : 'offline')) === 'online' ? 'text-sky-700 bg-sky-50 border-sky-200' : 'text-slate-700 bg-slate-100 border-slate-200' }} border px-2 py-0.5 rounded-lg text-xs">
                        {{ (session('presentation_detail.mode') ?? (session('show_online_meet_modal') ? 'online' : 'offline')) === 'online' ? '💻 Online (Google Meet)' : '🏢 Tatap Muka' }}
                    </span>
                </div>
                <div class="flex items-center justify-between pt-2.5 border-t border-slate-200 gap-3">
                    <span class="text-slate-500 font-medium shrink-0">Status Pengajuan:</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                        <i class="fa-solid fa-hourglass-start text-amber-600 text-xs"></i>
                        <span>Menunggu Respon Mentor</span>
                    </span>
                </div>
            </div>

            @php
            $sessionMode = session('presentation_detail.mode') ?? (session('show_online_meet_modal') ? 'online' : 'offline');
            @endphp

            @if($sessionMode === 'online')
            <!-- Keterangan Mode Online -->
            <div class="p-3.5 bg-amber-50/80 border border-amber-200 rounded-2xl text-xs space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-amber-950">
                    <div class="w-6 h-6 rounded-lg bg-amber-200/80 text-amber-800 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-lock text-xs"></i>
                    </div>
                    <span>Tautan Google Meet</span>
                </div>
                <p class="text-xs leading-relaxed text-amber-900 pl-8">
                    Link Google Meet akan <strong>otomatis aktif</strong> setelah pengajuan jadwal Anda disetujui oleh mentor. Pantau penetapan jam pada kartu Bantuan Aktif Anda.
                </p>
            </div>
            @else
            <!-- Keterangan Mode Offline -->
            <div class="p-3.5 bg-blue-50/80 border border-blue-200 rounded-2xl text-xs space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-blue-950">
                    <div class="w-6 h-6 rounded-lg bg-blue-200/80 text-blue-800 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-circle-info text-xs"></i>
                    </div>
                    <span>Presentasi Tatap Muka</span>
                </div>
                <p class="text-xs leading-relaxed text-blue-900 pl-8">
                    Pengajuan presentasi tatap muka telah berhasil dikirim ke mentor. Harap menunggu konfirmasi penetapan jam presentasi langsung di kantor.
                </p>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="px-4 sm:px-5 py-3 sm:py-3.5 bg-slate-50 border-t border-slate-100 flex justify-end shrink-0">
            <button type="button" onclick="closeOnlineMeetModal()"
                class="w-full sm:w-auto px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer text-center">
                Mengerti & Tutup
            </button>
        </div>
    </div>
</div>

<script src="{{ asset('js/user/index.js') }}?v={{ time() }}"></script>
<script>
    // ==========================================================
    // == LOGIKA UNIFIED MODAL IZIN (SAKIT & KEPERLUAN) ==
    // ==========================================================
    window.showModalIzin = function(type = 'sakit') {
        $('#modal2').removeClass('hidden').addClass('flex');
        window.switchPermitType(type);
    };

    window.closeModalIzin = function() {
        $('#modal2').addClass('hidden').removeClass('flex');
    };

    window.showModalIzinSakit = function() {
        window.showModalIzin('sakit');
    };

    window.showModalIzinKeperluan = function() {
        window.showModalIzin('keperluan');
    };

    window.switchPermitType = function(type) {
        const tabSakit = document.getElementById('tabBtnSakit');
        const tabKeperluan = document.getElementById('tabBtnKeperluan');
        const icon = document.getElementById('modalPermitIcon');
        const noticeSakit = document.getElementById('noticeSakit');
        const noticeKeperluan = document.getElementById('noticeKeperluan');
        const fieldKeperluanKategori = document.getElementById('fieldKeperluanKategori');
        const labelKeterangan = document.getElementById('labelKeterangan');
        const inputKeterangan = document.getElementById('form_keterangan');
        const labelProof = document.getElementById('labelProof');
        const inputProof = document.getElementById('form_proof_url');
        const btnSubmit = document.getElementById('btnSubmitPermit');
        const inputJamOption = document.getElementById('form_jam_option');
        const inputKategoriIzin = document.getElementById('form_kategori_izin');

        if (!tabSakit || !tabKeperluan) return;

        if (type === 'sakit') {
            tabSakit.className = 'py-2 rounded-lg transition-all bg-white text-emerald-700 shadow-xs flex items-center justify-center gap-1.5';
            tabKeperluan.className = 'py-2 rounded-lg transition-all text-slate-600 hover:text-slate-900 flex items-center justify-center gap-1.5';

            if (icon) {
                icon.className = 'p-2.5 bg-emerald-100 text-emerald-700 rounded-xl transition-colors';
                icon.innerHTML = '<i class="fa-solid fa-notes-medical text-lg"></i>';
            }

            if (noticeSakit) noticeSakit.classList.remove('hidden');
            if (noticeKeperluan) noticeKeperluan.classList.add('hidden');
            if (fieldKeperluanKategori) fieldKeperluanKategori.classList.add('hidden');

            if (labelKeterangan) labelKeterangan.innerHTML = 'Keluhan / Kondisi Sakit <span class="text-rose-500">*</span>';
            if (inputKeterangan) {
                inputKeterangan.placeholder = 'Tuliskan keluhan atau diagnosis singkat sakit Anda...';
                inputKeterangan.className = 'w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500';
            }

            if (labelProof) labelProof.innerHTML = 'Link Google Drive Surat Dokter <span class="text-rose-500">*</span>';
            if (inputProof) {
                inputProof.required = true;
                inputProof.placeholder = 'https://drive.google.com/file/d/...';
                inputProof.className = 'w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500';
            }

            if (btnSubmit) {
                btnSubmit.className = 'px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-xl text-xs shadow-sm transition';
                btnSubmit.innerText = 'Kirim Izin Sakit';
            }

            if (inputJamOption) inputJamOption.value = '0';
            if (inputKategoriIzin) inputKategoriIzin.value = '1';
        } else {
            tabKeperluan.className = 'py-2 rounded-lg transition-all bg-white text-amber-700 shadow-xs flex items-center justify-center gap-1.5';
            tabSakit.className = 'py-2 rounded-lg transition-all text-slate-600 hover:text-slate-900 flex items-center justify-center gap-1.5';

            if (icon) {
                icon.className = 'p-2.5 bg-amber-100 text-amber-700 rounded-xl transition-colors';
                icon.innerHTML = '<i class="fa-solid fa-user-clock text-lg"></i>';
            }

            if (noticeKeperluan) noticeKeperluan.classList.remove('hidden');
            if (noticeSakit) noticeSakit.classList.add('hidden');
            if (fieldKeperluanKategori) fieldKeperluanKategori.classList.remove('hidden');

            if (labelKeterangan) labelKeterangan.innerHTML = 'Alasan / Rincian Keperluan <span class="text-rose-500">*</span>';
            if (inputKeterangan) {
                inputKeterangan.placeholder = 'Jelaskan alasan keperluan izin Anda...';
                inputKeterangan.className = 'w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500';
            }

            if (labelProof) labelProof.innerHTML = 'Link Google Drive Bukti Keperluan <span class="text-rose-500">*</span>';
            if (inputProof) {
                inputProof.required = true;
                inputProof.placeholder = 'https://drive.google.com/file/d/...';
                inputProof.className = 'w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500';
            }

            if (btnSubmit) {
                btnSubmit.className = 'px-4 py-2 bg-amber-700 hover:bg-amber-800 text-white font-semibold rounded-xl text-xs shadow-sm transition';
                btnSubmit.innerText = 'Kirim Izin Keperluan';
            }

            if (inputJamOption) inputJamOption.value = '2';
            const subSelect = document.getElementById('keperluan_sub_select');
            if (inputKategoriIzin) inputKategoriIzin.value = subSelect ? subSelect.value : '3';
        }
    };

    $(document).on('click', '#modal2', function(e) {
        if (e.target === this) {
            window.closeModalIzin();
        }
    });

    function openRaiseHandModal() {
        $('#raiseHandModal').removeClass('hidden').addClass('flex');
    }

    function closeRaiseHandModal() {
        $('#raiseHandModal').addClass('hidden').removeClass('flex');
    }

    function openLowerHandModal() {
        if (typeof window.Livewire !== 'undefined') {
            window.Livewire.dispatch('open-status-bantuan-modal');
        }
    }

    function closeLowerHandModal() {
        if (typeof window.Livewire !== 'undefined') {
            window.Livewire.dispatch('close-status-bantuan-modal');
        }
    }

    function openOnlineMeetModal() {
        $('#onlineMeetModal').removeClass('hidden').addClass('flex');
    }

    function closeOnlineMeetModal() {
        $('#onlineMeetModal').addClass('hidden').removeClass('flex');
    }



    function copyPopupMeetLink() {
        const input = document.getElementById('popupMeetUrlInput');
        if (!input) return;

        const url = input.value;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(() => {
                handleCopyPopupFeedback();
            }).catch(() => {
                input.select();
                document.execCommand('copy');
                handleCopyPopupFeedback();
            });
        } else {
            input.select();
            document.execCommand('copy');
            handleCopyPopupFeedback();
        }
    }

    function handleCopyPopupFeedback() {
        const textEl = document.getElementById('textCopyPopupMeet');
        const iconEl = document.getElementById('iconCopyPopupMeet');
        const btn = document.getElementById('btnCopyPopupMeet');

        if (textEl && iconEl && btn) {
            textEl.innerText = 'Link Tersalin!';
            iconEl.className = 'fa-solid fa-check text-emerald-400 text-sm';
            btn.classList.remove('bg-gray-800', 'hover:bg-gray-900');
            btn.classList.add('bg-emerald-700');

            setTimeout(() => {
                textEl.innerText = 'Salin Link';
                iconEl.className = 'fa-regular fa-copy text-sm';
                btn.classList.remove('bg-emerald-700');
                btn.classList.add('bg-gray-800', 'hover:bg-gray-900');
            }, 2000);
        }
    }

    function copyMeetLink(url, btnEl) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url);
        } else {
            const temp = document.createElement('textarea');
            temp.value = url;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
        }

        if (btnEl) {
            const orig = btnEl.innerHTML;
            btnEl.innerHTML = '<i class="fa-solid fa-check text-xs"></i> <span>Tersalin!</span>';
            btnEl.classList.remove('bg-sky-600', 'hover:bg-sky-700');
            btnEl.classList.add('bg-emerald-600');
            setTimeout(() => {
                btnEl.innerHTML = orig;
                btnEl.classList.remove('bg-emerald-600');
                btnEl.classList.add('bg-sky-600', 'hover:bg-sky-700');
            }, 1500);
        }
    }

    $(document).on('click', '#onlineMeetModal', function(e) {
        if (e.target === this) {
            closeOnlineMeetModal();
        }
    });

    $(document).ready(function() {
        if ("{{ (session('show_presentation_submitted_modal') || session('show_online_meet_modal')) ? '1' : '' }}" === "1") {
            openOnlineMeetModal();
        }
    });

    function switchRaiseMode(mode) {
        $('.raise-mode-card').removeClass('border-blue-600 bg-blue-50/50 border-purple-600 bg-purple-50/50 border-amber-600 bg-amber-50/50').addClass('border-gray-200');
        $('#panel-raise-question, #panel-raise-new_task, #panel-raise-presentation').addClass('hidden');
        $('#notes-question, #notes-new_task, #notes-presentation, #select-project-presentation').prop('disabled', true);

        if (mode === 'question') {
            $('#card-mode-question').addClass('border-blue-600 bg-blue-50/50').removeClass('border-gray-200');
            $('#panel-raise-question').removeClass('hidden');
            $('#notes-question').prop('disabled', false).focus();
        } else if (mode === 'new_task') {
            $('#card-mode-new_task').addClass('border-purple-600 bg-purple-50/50').removeClass('border-gray-200');
            $('#panel-raise-new_task').removeClass('hidden');
            $('#notes-new_task').prop('disabled', false).focus();
        } else if (mode === 'presentation') {
            $('#card-mode-presentation').addClass('border-amber-600 bg-amber-50/50').removeClass('border-gray-200');
            $('#panel-raise-presentation').removeClass('hidden');
            $('#notes-presentation, #select-project-presentation').prop('disabled', false);

            // Auto-fill judul project aktif jika input judul masih kosong
            const sel = document.getElementById('select-project-presentation');
            const inputNotes = document.getElementById('notes-presentation');
            if (sel && inputNotes && (!inputNotes.value || inputNotes.value.trim() === '')) {
                const opt = sel.options[sel.selectedIndex];
                const title = opt ? (opt.getAttribute('data-title') || '') : '';
                if (title) {
                    inputNotes.value = title;
                }
            }
            $('#notes-presentation').focus();
        }
    }

    function onPresentationProjectChange(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        const projectTitle = opt ? (opt.getAttribute('data-title') || '') : '';
        const inputNotes = document.getElementById('notes-presentation');
        if (inputNotes) {
            if (projectTitle) {
                inputNotes.value = projectTitle;
            } else {
                inputNotes.value = '';
                inputNotes.focus();
            }
        }
    }

    function checkPresentationUrgency(dateValue) {
        const today = new Date().toISOString().split('T')[0];
        const badge = document.getElementById('urgency-notice-badge');
        if (!badge) return;

        badge.className = 'p-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center gap-2.5';
        if (dateValue === today) {
            badge.innerHTML = `
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-600 text-white">
                        TERJADWAL
                    </span>
                    <span class="text-xs text-blue-800 font-medium">
                        Jadwal diajukan untuk <strong>Hari Ini</strong>.
                    </span>
                `;
        } else {
            badge.innerHTML = `
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-600 text-white">
                        TERJADWAL
                    </span>
                    <span class="text-xs text-blue-800 font-medium">
                        Jadwal diajukan untuk tanggal <strong>${dateValue}</strong>.
                    </span>
                `;
        }
    }


    function switchInfoTab(tabId) {
        $('.info-tab-content').addClass('hidden');
        $(`#content-${tabId}`).removeClass('hidden');

        $('.info-tab-btn').removeClass('border-blue-600 text-blue-600 font-semibold')
            .addClass('border-transparent text-gray-500');

        $(`#btn-${tabId}`).removeClass('border-transparent text-gray-500')
            .addClass('border-blue-600 text-blue-600 font-semibold');
    }

    function openModalBroadcastList() {
        $('#broadcastModalList').removeClass('hidden').addClass('flex');
    }

    function closeModalBroadcastList() {
        $('#broadcastModalList').addClass('hidden').removeClass('flex');
    }

    function closeModalBroadcast() {
        $('#broadcastModal').addClass('hidden').removeClass('flex');
    }

    function openModalBroadcastListbyId(id) {
        $(`#broadcastModalbyId-${id}`).removeClass('hidden').addClass('flex');
    }

    function closeModalBroadcastListbyId(id) {
        $(`#broadcastModalbyId-${id}`).addClass('hidden').removeClass('flex');
    }

    // ==========================================================
    // == LOGBOOK HARIAN NAVIGATION ==
    // ==========================================================
    function openLogbookModal() {
        window.location.href = "{{ route('user.logbook.index') }}";
    }

    function closeLogbookModal() {
        // Logbook berada di halaman tersendiri
    }

    function togglePopup() {
        openLogbookModal();
    }

    function showNotification(message, type) {
        // ... (kode notifikasi Anda yang sudah ada)
    }

    // ==========================================================
    // == [BARU & DIPERBAIKI] LOGIKA UNTUK IZIN TOILET ==
    // ==========================================================
    $(document).ready(function() {

        // --- FUNGSI UNTUK MENAMPILKAN NOTIFIKASI DARI SAMPING ---
        function showToast(message) {
            const toast = $('#toast-notification');
            const toastMessage = $('#toast-message');

            toastMessage.html(message); // Set pesan notifikasi
            toast.removeClass('hidden'); // Tampilkan notifikasi

            // Sembunyikan notifikasi setelah 5 detik
            setTimeout(() => {
                toast.addClass('hidden');
            }, 5000);
        }

        // --- VALIDASI UNTUK MODAL LAINNYA ---
        // Validasi untuk modal izin tidak hadir
        $('#keterangan').on('input', function() {
            validateInput($(this), /^[a-zA-Z0-9\s.,!?():;'-]+$/);
        });

        // Validasi untuk modal action
        $('#modalTextarea').on('input', function() {
            validateInput($(this), /^[a-zA-Z0-9\s.,!?():;'-]+$/);
        });

        // Fungsi validasi umum
        function validateInput(inputElement, regexPattern) {
            const value = inputElement.val();
            if (!regexPattern.test(value) && value !== '') {
                inputElement.addClass('border-red-500');
                // Tampilkan pesan error jika diperlukan
            } else {
                inputElement.removeClass('border-red-500');
            }
        }

        // --- LISTENER UNTUK EVENT DARI LIVEWIRE UNTUK MEMBUKA MODAL ---
        Livewire.on('start-toilet-permit', () => {
            $('#toiletPermitModal').removeClass('hidden').addClass('flex');
        });

        // --- LOGIKA SAAT TOMBOL "KEMBALI DARI IZIN" DITEKAN ---
        $('#returnFromToiletBtn').on('click', function() {
            const button = $(this);
            button.prop('disabled', true).html('Memproses...');

            $.ajax({
                url: '{{ route("attendance.toilet.return") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#toiletPermitModal').addClass('hidden').removeClass('flex');
                    showToast(`Anda telah izin ke toilet selama <strong>${response.duration}</strong>`);
                    Livewire.dispatch('refreshAttendanceStatus');
                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                    showToast(`<strong>Error:</strong> ${errorMessage}`);
                },
                complete: function() {
                    button.prop('disabled', false).html('<i class="fas fa-check mr-2"></i> Kembali dari Izin');
                }
            });
        });

    });

    if ("{{ ($errors->has('link-google-drive') || $errors->has('keterangan')) ? '1' : '' }}" === "1") {
        window.showModalIzin("{{ in_array((int) old('kategori-izin'), [3, 4], true) ? 'keperluan' : 'sakit' }}");
    }
</script>

@if(isset($autoEndPopup) && $autoEndPopup)
<!-- Modal Notifikasi Pulang Otomatis (Muncul 1x Saat Lupa Absen Pulang) -->
<div id="auto-end-popup-modal" class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-[999] p-4 animate-in fade-in duration-300">
    <div class="bg-white rounded-3xl shadow-2xl p-6 sm:p-8 max-w-md w-full border border-amber-200 text-center transform transition-all duration-300 scale-100">
        <!-- Icon -->
        <div class="w-16 h-16 bg-gradient-to-tr from-amber-500 to-amber-400 text-white rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4 shadow-lg shadow-amber-200">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>

        <!-- Title & Subtitle -->
        <h3 class="text-xl sm:text-2xl font-black text-gray-900 mb-1">
            Pemberitahuan Pulang Otomatis
        </h3>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 mb-4">
            <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
            <span>Kamu Lupa Melakukan Presensi Pulang</span>
        </div>

        <!-- Body Message -->
        <div class="text-xs sm:text-sm text-gray-600 leading-relaxed space-y-3 mb-4 text-left">
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-1.5 text-xs">
                <div class="flex justify-between items-center text-slate-700">
                    <span class="text-slate-500 font-medium">Tanggal Presensi:</span>
                    <span class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($autoEndPopup->date)->translatedFormat('l, d F Y') }}</span>
                </div>
                <div class="flex justify-between items-center text-slate-700">
                    <span class="text-slate-500 font-medium">Jam Masuk:</span>
                    <span class="font-semibold text-emerald-700">{{ $autoEndPopup->start_time ? \Carbon\Carbon::parse($autoEndPopup->start_time)->format('H:i') : '-' }}</span>
                </div>
                <div class="flex justify-between items-center text-slate-700">
                    <span class="text-slate-500 font-medium">Dipulangkan Otomatis:</span>
                    <span class="font-semibold text-red-600">{{ $autoEndPopup->end_time ? \Carbon\Carbon::parse($autoEndPopup->end_time)->format('H:i') : '-' }}</span>
                </div>
            </div>

            <p class="text-gray-600 text-center text-xs">
                Karena kamu belum melakukan presensi pulang hingga shift berakhir, sistem atau Admin telah menyelesaikan presensimu secara otomatis.
            </p>

            @if(!empty($autoEndPopup->auto_end_note))
            <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-2xl text-xs text-blue-900">
                <span class="font-bold block mb-1 flex items-center gap-1.5 text-blue-800">
                    <i class="fa-solid fa-message text-blue-600"></i> Catatan dari Admin:
                </span>
                <span class="italic text-blue-950 block">"{{ $autoEndPopup->auto_end_note }}"</span>
            </div>
            @endif

            <p class="text-xs text-gray-400 text-center">
                Harap selalu ingat untuk melakukan presensi pulang sebelum meninggalkan area kantor atau mengakhiri jam kerja.
            </p>
        </div>

        <!-- Action Button -->
        <button type="button"
            data-id="{{ $autoEndPopup->id }}"
            onclick="dismissAutoEndPopupModal(this)"
            id="btn-dismiss-auto-end"
            class="w-full py-3 px-5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition transform hover:scale-[1.01] active:scale-[0.99] text-xs sm:text-sm flex items-center justify-center gap-2">
            <i class="fa-solid fa-check"></i>
            <span>Saya Mengerti</span>
        </button>
    </div>
</div>

<script>
    function dismissAutoEndPopupModal(attendanceIdOrBtn) {
        const attendanceId = typeof attendanceIdOrBtn === 'object' && attendanceIdOrBtn !== null ?
            attendanceIdOrBtn.dataset.id :
            attendanceIdOrBtn;
        const btn = document.getElementById('btn-dismiss-auto-end');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...';
        }

        $.ajax({
            url: '{{ route("user.autoEnd.dismiss", ":id") }}'.replace(':id', attendanceId),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                const modal = document.getElementById('auto-end-popup-modal');
                if (modal) {
                    modal.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                    setTimeout(() => modal.remove(), 300);
                }
            },
            error: function(xhr) {
                const modal = document.getElementById('auto-end-popup-modal');
                if (modal) {
                    modal.remove();
                }
            }
        });
    }
</script>
@endif
<script>
    function openChangeTimeModal() {
        const modal = document.getElementById('changeTimeModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeChangeTimeModal() {
        const modal = document.getElementById('changeTimeModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function submitChangeTimeForm() {
        const form = document.getElementById('formDaftarGantiJam');
        const dateInput = document.getElementById('reg_requested_date');
        if (dateInput && dateInput.min && dateInput.value < dateInput.min) {
            alert('Tanggal rencana ganti jam tidak dapat memilih hari ini atau tanggal lampau.');
            dateInput.focus();
            return;
        }
        const btn = document.getElementById('btn-submit-change-time');
        const spinner = document.getElementById('spinner-change-time');
        if (btn) btn.classList.add('hidden');
        if (spinner) {
            spinner.classList.remove('hidden');
            spinner.classList.add('flex');
        }
        if (form) form.submit();
    }

    function openPraDaftarGantiJamModal() {
        openChangeTimeModal();
    }

    function closePraDaftarGantiJamModal() {
        closeChangeTimeModal();
    }

    // Sembunyikan / Tampilkan Jadwal Minggu Ini secara langsung saat ganti jam dimulai/selesai tanpa refresh
    function toggleWeeklySchedule(hide) {
        const el = document.getElementById('weekly-schedule-container');
        if (el) {
            if (hide) {
                el.classList.add('hidden');
            } else {
                el.classList.remove('hidden');
            }
        }
    }

    window.addEventListener('attd-info-ajdst', function(event) {
        const isAdj = event.detail?.isAdjustable ?? event.detail?.[0]?.isAdjustable ?? false;
        toggleWeeklySchedule(isAdj);
    });

    window.addEventListener('change-time-refresh', function(event) {
        const sessionData = event.detail?.sessionData ?? event.detail?.[0]?.sessionData;
        if (sessionData) {
            toggleWeeklySchedule(true);
        }
    });
</script>

<style>
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
@endsection