@extends('users.layouts.main', [])

@section('title', 'Dashboard')

@section('contents')
<!-- Main Content -->
<div class="w-full h-full flex flex-col md:flex-row gap-2 p-4 md:p-10">

    <!-- Left Side Buttons (Attendance Actions) -->
    <div class="flex flex-col w-full md:w-1/5 gap-4">

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
    <div class="flex flex-col w-full md:w-4/5 gap-4 md:ml-4">
        @if(isset($currentHandRaise) && $currentHandRaise && $currentHandRaise->is_raised && $currentHandRaise->status !== 'done')
        @if($currentHandRaise->type === 'new_task' && $currentHandRaise->status === 'in_progress')
        <div class="p-4 bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                    <i class="fa-solid fa-clipboard-list text-base"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-purple-950 flex items-center gap-2">
                        <span>Tugas Baru Telah Diberikan Pembimbing!</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Sedang Dikerjakan
                        </span>
                    </div>
                    <p class="text-[11px] text-purple-700 mt-0.5">
                        Pembimbing telah menanggapi permintaan Anda dan memberikan instruksi tugas. Silakan cek detail tugas.
                    </p>
                </div>
            </div>
            <a href="{{ route('user.tasks.index') }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5 flex-shrink-0">
                <span>Buka Halaman Tugas</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
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
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-200 text-orange-900">
                            Perlu Perbaikan
                        </span>
                    </div>
                    <p class="text-[11px] text-orange-800 mt-0.5 line-clamp-1">
                        {{ $currentHandRaise->admin_response ?: 'Mentor memberikan catatan revisi sebelum jadwal presentasi dimulai.' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('user.tasks.index') }}" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5">
                    <span>Lihat di Halaman Tugas</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
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
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-200 text-emerald-900">
                        Sudah Presentasi
                    </span>
                </div>
                <p class="text-[11px] text-emerald-800 mt-0.5">
                    Presentasi projek Anda telah dikonfirmasi selesai oleh mentor.
                </p>
            </div>
        </div>
        @endif
        @endif

        @livewire('attd-info-container', [
        'attdData' => isset($absenceHistory) ? $absenceHistory : null,
        'isWithoutBreak' => isset($shift) && is_object($shift) && isset($shift->break_time_in_minute) && (int) $shift->break_time_in_minute === 0 ? true : false,
        'isAdjustable' => isset($all_adjustable) && sizeOf($all_adjustable) > 0,
        ])

        @livewire('adjst-info-container', [
        'adjstData' => isset($all_adjustable) ? $all_adjustable : [],
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

        <!-- Schedule and Attention Section -->
        <div class="flex flex-col md:flex-row items-stretch mt-4 space-y-4 md:space-y-0 md:space-x-4">
            <!-- Weekly Schedule -->
            <div class="w-full md:w-1/2 flex flex-col">
                <p class="text-xl font-bold mb-3 text-gray-800 text-center flex items-center justify-center gap-2">
                    <i class="fa-solid fa-calendar-week text-blue-600"></i> Jadwalmu Minggu Ini
                </p>
                <div class="border border-gray-200 shadow-sm rounded-xl overflow-hidden bg-white flex-1 flex flex-col">
                    <div class="overflow-x-auto overflow-y-auto max-h-60">
                        <table class="min-w-full text-xs text-center border-collapse">
                            <thead class="sticky top-0 bg-slate-800 text-white z-10">
                                <tr>
                                    <th class="py-2.5 px-2 font-semibold">Tanggal</th>
                                    <th class="py-2.5 px-2 font-semibold">Hari</th>
                                    <th class="py-2.5 px-2 font-semibold">Shift</th>
                                    <th class="py-2.5 px-2 font-semibold">Jam</th>
                                    <th class="py-2.5 px-2 font-semibold">Tipe</th>
                                    <th class="py-2.5 px-2 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($schedules as $schedule)
                                @php
                                $isToday = \Carbon\Carbon::parse($schedule->date)->isToday();
                                $isPast = \Carbon\Carbon::parse($schedule->date)->isPast();
                                $attdStatusId = $schedule->attdStatus->id ?? $schedule->attd_status_id ?? 1;
                                $attdStatusName = $schedule->attdStatus->name ?? 'Dijadwalkan';

                                $rowBg = $isToday ? 'bg-amber-50/80 font-medium' : ($loop->even ? 'bg-gray-50/50' : 'bg-white');

                                // Badge warna status
                                $statusBadge = match((int)$attdStatusId) {
                                2 => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                3 => 'bg-blue-100 text-blue-800 border-blue-200',
                                4 => 'bg-purple-100 text-purple-800 border-purple-200',
                                5 => 'bg-rose-100 text-rose-800 border-rose-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };

                                $workTypeBadge = strtoupper($schedule->work_type ?? 'WFO') === 'WFH'
                                ? 'bg-indigo-50 text-indigo-700 border-indigo-200'
                                : 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                @endphp
                                <tr class="{{ $rowBg }} hover:bg-blue-50/40 transition-colors">
                                    <td class="py-2 px-2 text-gray-700 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($schedule->date)->format('d-m-Y') }}
                                        @if($isToday)
                                        <span class="ml-1 inline-block w-2 h-2 rounded-full bg-amber-500" title="Hari Ini"></span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-2 text-gray-700 whitespace-nowrap font-medium">
                                        {{ \Carbon\Carbon::parse($schedule->date)->locale('id')->translatedFormat('l') }}
                                    </td>
                                    <td class="py-2 px-2 text-gray-800 font-semibold whitespace-nowrap">
                                        {{ $schedule->shift->name ?? '-' }}
                                    </td>
                                    <td class="py-2 px-2 text-gray-600 whitespace-nowrap font-mono text-[11px]">
                                        @if(isset($schedule->shift))
                                        {{ substr($schedule->shift->start_time, 0, 5) }} - {{ substr($schedule->shift->end_time, 0, 5) }}
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td class="py-2 px-2 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $workTypeBadge }}">
                                            {{ strtoupper($schedule->work_type ?? 'WFO') }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-2 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $statusBadge }}">
                                            {{ $attdStatusName }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="py-8 px-4 text-center text-gray-400">
                                        <i class="fa-regular fa-calendar-xmark text-2xl mb-1 text-gray-300 block"></i>
                                        <span>Belum ada jadwal untuk minggu ini</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Attention Section -->
            <div class="attention-section w-full md:w-1/2 text-red-600 text-center">
                <p class="font-bold animate-pulse text-2xl">Attention !</p>
                <div class="flex flex-col md:flex-row gap-2 mt-4 attention-section">
                    <div class="border border-red-600 py-5 px-3 w-full md:w-3/5">
                        <ul class="text-xs list-disc list-inside">
                            <li>{{ $user->intern->attention_message ?? 'Tidak ada catatan' }}</li>
                        </ul>
                    </div>
                    <div class="border {{ $lack['isLess'] ? 'border-red-600' : 'border-green-600' }} p-3 w-full md:w-2/5">
                        <p class="text-xs mb-2 {{ $lack['isLess'] ? 'text-red-600' : 'text-green-600' }}">{{ $lack['isLess'] ? 'Anda memiliki kekurangan jam kerja' : 'Total jam kerja anda' }}</p>
                        <p id="timer" class="mx-auto {{ $lack['isLess'] ? 'bg-red-100' : 'bg-green-100' }} w-3/4 border {{ $lack['isLess'] ? 'border-red-600' : 'border-green-600' }} p-2 text-xs font-bold text-center {{ $lack['isLess'] ? 'text-red-600' : 'text-green-600' }}">{{ $intern_target['change_time_total'] ?? '00:00:00' }}</p>
                        <div class="text-center mt-2">
                            <a href="{{ route('user.attendance.change.view') }}" class="text-xs text-blue-800 font-bold underline hover:text-black">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Broadcast List Modal -->
<div id="broadcastModalList" class="fixed inset-0 flex items-center justify-center z-[999] bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg w-3/4 lg:w-1/2 p-4 shadow-lg">
        <div class="flex justify-between items-center border-b pb-2">
            <h2 class="text-xl font-bold">Daftar Pengumuman</h2>
            <button onclick="closeModalBroadcastList()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <div class="overflow-y-auto max-h-60 mt-4">
            <table class="w-full table-fixed border-collapse">
                <thead>
                    <tr>
                        <th class="w-1/12 border bg-gray-100 px-4 py-2 text-center font-semibold">No</th>
                        <th class="w-8/12 border bg-gray-100 px-4 py-2 text-left font-semibold">Judul</th>
                        <th class="w-3/12 border bg-gray-100 px-4 py-2 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($broadcast_list as $index => $broadcast)
                    <tr>
                        <td class="border px-4 py-2 text-center">{{ $index + 1 }}</td>
                        <td class="border px-4 py-2 text-left break-words">{{ $broadcast->title }}</td>
                        <td class="border px-4 py-2 text-center">
                            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold px-4 py-1 rounded text-sm" onclick="openModalBroadcastListbyId('{{ $broadcast->id }}')">Detail</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="border px-4 py-2 text-center">Tidak ada pengumuman.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex justify-end pt-4">
            <button onclick="closeModalBroadcastList()" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md">Tutup</button>
        </div>
    </div>
</div>

<!-- First Broadcast Modal -->
@if (session('firstBroadcast') && $broadcast_list->contains('id', session('firstBroadcast')->id))
<div id="broadcastModal" class="fixed inset-0 flex items-center justify-center z-[9999] bg-black/50 backdrop-blur-sm animate-fadeIn">
    <div class="bg-white rounded-2xl w-full max-w-3xl shadow-2xl overflow-hidden">
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h2 class="text-2xl font-bold text-gray-800">📢 Pengumuman</h2>
            <button onclick="closeModalBroadcast()" class="text-gray-500 hover:text-gray-800 transition">✕</button>
        </div>
        <div class="p-6 max-h-[70vh] overflow-y-auto space-y-4">
            <h3 class="text-xl font-semibold text-center">{{ session('firstBroadcast')->title }}</h3>

            {{-- [MODIFIKASI] Menampilkan multiple gambar --}}
            @if(session('firstBroadcast')->images && session('firstBroadcast')->images->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach(session('firstBroadcast')->images as $image)
                <img src="{{ asset('broadcast-image/' . $image->image) }}"
                    alt="Gambar Pengumuman"
                    class="rounded-xl shadow-lg w-full h-auto object-cover">
                @endforeach
            </div>
            @endif

            <div class="text-gray-700 leading-relaxed text-justify whitespace-pre-line">
                {!! nl2br(e(session('firstBroadcast')->message)) !!}
            </div>
        </div>
        <div class="flex justify-end px-6 py-4 border-t">
            <button onclick="closeModalBroadcast()" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-600 transition">Tutup</button>
        </div>
    </div>
</div>
@endif

<!-- Broadcast Detail Modals -->
@foreach ($broadcast_list as $item)
<div id="broadcastModalbyId-{{ $item->id }}" class="hidden fixed inset-0 flex items-center justify-center z-[999] bg-black/50 backdrop-blur-sm animate-fadeIn">
    <div class="bg-white rounded-2xl w-full max-w-3xl shadow-2xl overflow-hidden">
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h2 class="text-2xl font-bold text-gray-800">📢 {{ $item->title }}</h2>
            <button onclick="closeModalBroadcastListbyId('{{ $item->id }}')" class="text-gray-500 hover:text-gray-800 transition">✕</button>
        </div>
        <div class="p-6 max-h-[70vh] overflow-y-auto space-y-4">

            {{-- [MODIFIKASI] Menampilkan multiple gambar --}}
            @if($item->images && $item->images->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($item->images as $image)
                <img src="{{ asset('broadcast-image/' . $image->image) }}"
                    alt="Gambar Pengumuman"
                    class="rounded-xl shadow-lg w-full h-auto object-cover">
                @endforeach
            </div>
            @endif

            <div class="text-gray-700 leading-relaxed text-justify whitespace-pre-line">
                {!! nl2br(e($item->message)) !!}
            </div>
        </div>
        <div class="flex justify-between items-center px-6 py-4 border-t bg-gray-50">
            <span class="text-xs text-gray-500">Diterbitkan: {{ $item->created_at->translatedFormat('l, d F Y H:i') }}</span>
            <button type="button" onclick="closeModalBroadcastListbyId('{{ $item->id }}')" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 text-sm transition">Tutup</button>
        </div>
    </div>
</div>
@endforeach

<!-- ==================================================================== -->
<!-- UNIFIED LOGBOOK HARIAN MODAL (2 TAB: LAPORAN HARI INI & RIWAYAT) -->
<!-- Catatan: Logbook Harian kini berada di halaman khusus (route: user.logbook.index) -->

<!-- Information & Holiday Modal (3 Tabs: Hari Libur, SOP Magang, Peraturan Kantor) -->
<div id="holidayModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden z-[9999] p-4">
    <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-fadeIn">
        <!-- Modal Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b bg-gray-800 text-white">
            <div class="flex items-center space-x-2">
                <i class="fas fa-info-circle text-blue-400 text-xl"></i>
                <h2 class="text-lg md:text-xl font-bold">Informasi & Tata Tertib Magang</h2>
            </div>
            <button type="button" onclick="closeHolidayModal()" class="text-gray-300 hover:text-white text-2xl font-bold leading-none">&times;</button>
        </div>

        <!-- Tabs Navigation -->
        <div class="flex border-b border-gray-200 bg-gray-50 text-xs sm:text-sm font-medium overflow-x-auto">
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
        <div class="p-6 overflow-y-auto flex-1 space-y-4">

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
                <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 flex items-start space-x-3">
                    <div class="text-indigo-600 text-xl mt-0.5"><i class="fas fa-building"></i></div>
                    <div>
                        <h4 class="font-bold text-indigo-900 text-sm">
                            Peraturan Penempatan: {{ $userOffice->name ?? 'Kantor Djuragan' }}
                        </h4>
                        <p class="text-xs text-indigo-700 mt-0.5">
                            <i class="fas fa-location-dot mr-1"></i> {{ $userOffice->address ?? 'Alamat kantor terdaftar' }}
                            @if(isset($userOffice->capacity))
                            &bull; Kapasitas: {{ $userOffice->capacity }} orang
                            @endif
                        </p>
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
        <div class="flex justify-end px-6 py-4 border-t bg-stone-50">
            <button type="button" onclick="closeHolidayModal()" class="px-5 py-2 bg-gray-800 hover:bg-gray-700 text-white font-medium text-sm rounded-xl transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Action Modal -->
<div id="actionModal" class="fixed inset-0 bg-gray-900 bg-opacity-60 flex justify-center items-center hidden z-50">
    <div class="bg-white rounded-xl overflow-hidden shadow-2xl transform transition-all w-11/12 sm:w-full sm:max-w-lg">
        <div class="bg-slate-800 text-white p-4 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-semibold">Keterangan Presensi</h3>
            <button type="button" onclick="closeActionModal()" class="text-gray-300 hover:text-white text-2xl font-bold leading-none">&times;</button>
        </div>
        <div id="modalContent" class="p-6">
            <textarea id="modalTextarea" class="w-full border border-gray-300 rounded-lg shadow-sm bg-slate-50 p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" rows="4"
                placeholder="Tuliskan keterangan (opsional)"></textarea>
            <div class="flex justify-end items-center gap-3 mt-4">
                <button type="button" onclick="closeActionModal()" class="px-5 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition">
                    Batal
                </button>
                <button type="button" id="save-attd" class="bg-slate-800 hover:bg-slate-700 text-white font-semibold py-2 px-8 rounded-lg shadow transition">
                    Simpan
                </button>
                <svg aria-hidden="true" id="loading-spinner" class="w-8 h-8 ml-2 text-gray-200 animate-spin hidden fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor" />
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill" />
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Geo Notification Modal -->
<div id="geoNotification" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex justify-center items-center z-50 hidden">
    <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-11/12 sm:w-full sm:max-w-lg">
        <div class="bg-red-700 text-white p-4">
            <h3 class="text-lg leading-6 font-medium">Izin Lokasi Diperlukan</h3>
        </div>
        <div class="p-4">
            <p class="text-gray-700">
                Aplikasi ini memerlukan akses ke lokasi Anda untuk menyediakan layanan yang lebih baik. Silakan
                berikan izin untuk melanjutkan.
            </p>
        </div>
        <div class="bg-gray-100 px-4 py-3 flex justify-end">
            <button id="allowButton" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none">
                Izinkan
            </button>
            <button id="denyButton" class="ml-2 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none">
                Tolak
            </button>
        </div>
    </div>
</div>

<!-- UNIFIED POPUP MODAL: FORM IZIN TIDAK MASUK (SAKIT & KEPERLUAN) -->
<div id="modal2" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden z-[9999] p-4" onclick="if(event.target === this) closeModalIzin();">
    <div class="bg-white p-6 m-3 rounded-2xl shadow-xl w-full max-w-md relative animate-fade-in" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div class="flex items-center gap-3 mb-4">
            <div id="modalPermitIcon" class="p-2.5 bg-emerald-100 text-emerald-700 rounded-xl transition-colors">
                <i class="fa-solid fa-notes-medical text-lg"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-800">Form Izin Tidak Masuk</h2>
                <p class="text-xs text-slate-500">Pilih jenis izin sakit atau izin keperluan Anda.</p>
            </div>
        </div>

        <!-- Segmented Control / Type Selector -->
        <div class="grid grid-cols-2 p-1 bg-slate-100 rounded-xl mb-4 text-xs font-bold">
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
        <div id="noticeSakit" class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl mb-4 text-xs text-emerald-800 space-y-1">
            <div class="font-semibold flex items-center gap-1.5">
                <i class="fa-solid fa-circle-check text-emerald-600"></i> Ketentuan Izin Sakit:
            </div>
            <p>Wajib melampirkan link Google Drive bukti foto surat dokter resmi agar disetujui <strong>Bebas Ganti Jam (Lunas)</strong>.</p>
        </div>

        <div id="noticeKeperluan" class="p-3 bg-amber-50 border border-amber-200 rounded-xl mb-4 text-xs text-amber-800 space-y-1 hidden">
            <div class="font-semibold flex items-center gap-1.5">
                <i class="fa-solid fa-clock-rotate-left text-amber-600"></i> Ketentuan Ganti Jam:
            </div>
            <p>Izin keperluan biasa <strong>wajib mengganti jam kerja</strong> sesuai durasi shift yang ditinggalkan.</p>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('attendance.addPermitPresenceUser') }}">
            @csrf
            <input type="hidden" id="form_jam_option" name="jam-option" value="0" />
            <input type="hidden" name="id" value="{{ $detail_schedule_id ?? 0 }}" />
            <input type="hidden" id="form_kategori_izin" name="kategori-izin" value="1" />

            <!-- Category Sub-select (Only for Keperluan) -->
            <div id="fieldKeperluanKategori" class="mb-4 hidden">
                <label for="keperluan_sub_select" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Jenis Keperluan <span class="text-rose-500">*</span>
                </label>
                <select id="keperluan_sub_select" onchange="document.getElementById('form_kategori_izin').value = this.value;"
                    class="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    <option value="3">📋 Keperluan Sekolah / Kampus (Ujian, Dispensasi, dsb)</option>
                    <option value="4">📁 Keperluan Pribadi / Keluarga / Lainnya</option>
                </select>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label id="labelKeterangan" for="form_keterangan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Keluhan / Kondisi Sakit <span class="text-rose-500">*</span>
                </label>
                <textarea id="form_keterangan" name="keterangan" rows="3"
                    placeholder="Tuliskan keluhan atau diagnosis singkat sakit Anda..."
                    class="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required></textarea>
            </div>

            <!-- Link GDrive -->
            <div class="mb-4">
                <label id="labelProof" for="form_proof_url" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Link Google Drive Surat Dokter <span id="proofRequiredStar" class="text-rose-500">*</span>
                </label>
                <input type="url" id="form_proof_url" name="link-google-drive"
                    placeholder="https://drive.google.com/file/d/..."
                    class="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required />
                <p id="proofHelpText" class="text-[10px] text-slate-400 mt-1">Pastikan akses link Google Drive diset ke 'Anyone with link / Siapa saja memiliki link'.</p>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end items-center gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModalIzin();" class="px-4 py-2 border border-slate-300 text-slate-700 font-semibold rounded-xl text-xs hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" id="btnSubmitPermit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-xl text-xs shadow-sm transition">
                    Kirim Izin Sakit
                </button>
            </div>
        </form>
        <button class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition" onclick="closeModalIzin();">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<!-- ==================================================================== -->
<!-- [BARU] MODAL UNTUK IZIN KE TOILET (TANPA TIMER) -->
<!-- ==================================================================== -->
<div id="toiletPermitModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center hidden z-[999]">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-sm mx-auto text-center">

        <div class="mb-4">
            <svg class="mx-auto h-12 w-12 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m-7.5-2.962a3.75 3.75 0 1 0-7.5 0v9.75a3.75 3.75 0 0 0 7.5 0v-9.75Zm4.5 0a3.75 3.75 0 1 0-7.5 0v9.75a3.75 3.75 0 0 0 7.5 0v-9.75Z" />
            </svg>
        </div>

        <h3 class="text-2xl font-bold text-gray-800">Sedang Izin ke Toilet</h3>

        <p class="text-gray-600 mt-2 mb-6">
            Silakan kembali jika sudah selesai.
        </p>

        <button id="returnFromToiletBtn" class="w-full px-4 py-3 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            <i class="fas fa-check mr-2"></i> Kembali dari Izin
        </button>
    </div>
</div>

{{-- ============================================================ --}}
{{-- POPUP CHECK-IN MESSAGE --}}
{{-- ============================================================ --}}
@if(session('checkin_popup'))
<div
    id="checkinPopup"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div
        class="bg-white rounded-2xl shadow-xl max-w-sm w-full mx-4 p-6 text-center">
        @php
        $popup = session('checkin_popup');
        @endphp

        @if(!empty($popup['image']))
        <img
            src="{{ $popup['image'] }}"
            alt="Checkin"
            class="w-32 h-32 mx-auto mb-4 rounded-xl object-cover" />
        @endif

        <div
            class="text-lg font-bold mb-2
            {{ $popup['type'] === 'late'
                ? 'text-red-600'
                : 'text-emerald-600' }}">
            {{ $popup['type'] === 'late'
                ? '⚠️ Terlambat'
                : '✅ Tepat Waktu' }}
        </div>

        <p class="text-sm text-slate-600 mb-4">
            {{ $popup['message'] }}
        </p>

        <button
            type="button"
            onclick="document.getElementById('checkinPopup').remove()"
            class="px-6 py-2 bg-slate-800 text-white rounded-xl text-sm font-semibold hover:bg-slate-700">
            Mengerti
        </button>
    </div>
</div>
@endif


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
<div id="raiseHandModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden z-[9999] p-4">
    <div class="bg-white rounded-2xl w-full max-w-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-fadeIn">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b bg-gray-800 text-white">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center">
                    <i class="fas fa-hand-paper text-base"></i>
                </div>
                <div>
                    <h2 class="text-base md:text-lg font-bold">Angkat Tangan / Butuh Bantuan</h2>
                    <p class="text-xs text-gray-300">Pilih kategori bantuan yang ingin diajukan ke mentor/admin</p>
                </div>
            </div>
            <button type="button" onclick="closeRaiseHandModal()" class="text-gray-300 hover:text-white text-2xl font-bold leading-none">&times;</button>
        </div>

        <!-- Form Body -->
        <form action="{{ route('intern.raisehand.toggle') }}" method="POST" class="flex flex-col flex-1 overflow-y-auto">
            @csrf
            <div class="p-6 space-y-5 flex-1">
                <!-- Mode Selection Tabs / Radio Cards -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2.5">
                        Pilih Kategori Bantuan <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <!-- Card 1: Tanya Jawab -->
                        <label class="raise-mode-card relative flex flex-col p-3 rounded-xl border-2 cursor-pointer transition-all border-blue-600 bg-blue-50/50" id="card-mode-question">
                            <input type="radio" name="type" value="question" class="sr-only" checked onchange="switchRaiseMode('question')">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fa-solid fa-comments text-blue-600 text-sm"></i>
                                <span class="text-xs font-bold text-gray-900">Bertanya</span>
                            </div>
                            <span class="text-[11px] text-gray-500 leading-tight">Konsultasi kendala teknis / materi</span>
                        </label>

                        <!-- Card 2: Tugas Baru -->
                        <label class="raise-mode-card relative flex flex-col p-3 rounded-xl border-2 border-gray-200 hover:border-gray-300 cursor-pointer transition-all" id="card-mode-new_task">
                            <input type="radio" name="type" value="new_task" class="sr-only" onchange="switchRaiseMode('new_task')">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fa-solid fa-list-check text-purple-600 text-sm"></i>
                                <span class="text-xs font-bold text-gray-900">Tugas Baru</span>
                            </div>
                            <span class="text-[11px] text-gray-500 leading-tight">Minta modul / tugas berikutnya</span>
                        </label>

                        <!-- Card 3: Presentasi -->
                        <label class="raise-mode-card relative flex flex-col p-3 rounded-xl border-2 border-gray-200 hover:border-gray-300 cursor-pointer transition-all" id="card-mode-presentation">
                            <input type="radio" name="type" value="presentation" class="sr-only" onchange="switchRaiseMode('presentation')">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fa-solid fa-chalkboard-user text-amber-600 text-sm"></i>
                                <span class="text-xs font-bold text-gray-900">Presentasi</span>
                            </div>
                            <span class="text-[11px] text-gray-500 leading-tight">Jadwal uji hasil modul/project</span>
                        </label>
                    </div>
                </div>

                <!-- Panel 1: Mode Bertanya -->
                <div id="panel-raise-question" class="space-y-3">
                    <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-800 flex items-start gap-2">
                        <i class="fa-solid fa-circle-info text-blue-600 mt-0.5"></i>
                        <span>Jelaskan kendala atau pertanyaan Anda secara spesifik agar mentor dapat membantu dengan cepat.</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Detail Pertanyaan / Kendala <span class="text-red-500">*</span>
                        </label>
                        <textarea name="notes" id="notes-question" rows="4" class="w-full text-xs p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Mengalami error koneksi database saat menjalankan migration Laravel..."></textarea>
                    </div>
                </div>

                <!-- Panel 2: Mode Tugas Baru -->
                <div id="panel-raise-new_task" class="space-y-3 hidden">
                    <div class="p-3 bg-purple-50 border border-purple-100 rounded-xl text-xs text-purple-800 flex items-start gap-2">
                        <i class="fa-solid fa-circle-info text-purple-600 mt-0.5"></i>
                        <span>Gunakan opsi ini jika tugas Anda sebelumnya sudah selesai dan membutuhkan arahan pengerjaan tugas berikutnya.</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Keterangan Tugas Selesai & Permintaan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="notes" id="notes-new_task" rows="4" disabled class="w-full text-xs p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Contoh: Modul desain UI/UX sudah selesai dan diserahkan ke repo/GDrive. Mohon arahan modul selanjutnya..."></textarea>
                    </div>
                </div>

                <!-- Panel 3: Mode Presentasi -->
                <div id="panel-raise-presentation" class="space-y-3 hidden">
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 flex items-start gap-2">
                        <i class="fa-solid fa-lightbulb text-amber-600 mt-0.5"></i>
                        <span>Mentor akan mereview materi presentasi Anda dan memberikan evaluasi performa setelah presentasi selesai.</span>
                    </div>

                    @if(isset($activeProjects) && $activeProjects->count() > 0)
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Project yang Sedang Dikerjakan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="project_id" id="select-project-presentation" onchange="onPresentationProjectChange(this)" disabled class="w-full text-xs p-2.5 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 appearance-none pr-8">
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
                        <p class="text-[11px] text-gray-500 mt-1">Otomatis diarahkan ke project aktif Anda agar tidak perlu mengisi manual secara keseluruhan.</p>
                    </div>
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Judul / Materi Presentasi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="notes" id="notes-presentation" disabled
                            value="{{ isset($activeProjects) && $activeProjects->count() > 0 ? ($activeProjects->first()->nameProject->name ?? '') : '' }}"
                            class="w-full text-xs p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            placeholder="Contoh: Presentasi Modul Autentikasi dan API Resource">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Tanggal Presentasi <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="presentation_date" id="presentation_date_input" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" onchange="checkPresentationUrgency(this.value)" class="w-full text-xs p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Mode Presentasi <span class="text-red-500">*</span>
                            </label>
                            <select name="presentation_mode" id="presentation_mode_select" class="w-full text-xs p-2.5 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <option value="offline">🏢 Tatap Muka / Di Kantor</option>
                                <option value="online">💻 Online (Google Meet)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Schedule Notice Badge -->
                    <div id="urgency-notice-badge" class="p-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center gap-2.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-600 text-white">
                            TERJADWAL
                        </span>
                        <span class="text-xs text-blue-800 font-medium">
                            Jadwal diajukan untuk <strong>Hari Ini</strong>.
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex justify-end gap-2">
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

<!-- Modal Status Raise Hand Aktif (Lower Hand / Batalkan) -->
<div id="lowerHandModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden z-[9999] p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden flex flex-col animate-fadeIn">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b bg-emerald-800 text-white">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-700 flex items-center justify-center text-emerald-200">
                    <i class="fas fa-hand-paper animate-bounce text-sm"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold">Status Bantuan Aktif</h2>
                    <p class="text-xs text-emerald-200">Permintaan Anda sedang menunggu respon</p>
                </div>
            </div>
            <button type="button" onclick="closeLowerHandModal()" class="text-gray-300 hover:text-white text-2xl font-bold leading-none">&times;</button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4">
            @if(isset($currentHandRaise) && $currentHandRaise && $currentHandRaise->is_raised && $currentHandRaise->status !== 'done')
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</span>
                    @if($currentHandRaise->type === 'presentation')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                        <i class="fa-solid fa-chalkboard-user"></i> Presentasi
                    </span>
                    @elseif($currentHandRaise->type === 'new_task')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                        <i class="fa-solid fa-list-check"></i> Tugas Baru
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                        <i class="fa-solid fa-comments"></i> Tanya Jawab
                    </span>
                    @endif
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</span>
                    @if($currentHandRaise->status === 'urgent')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span> Menunggu Konfirmasi Jadwal (Hari Ini)
                    </span>
                    @elseif($currentHandRaise->status === 'in_progress')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span> Tugas Diberikan (Sedang Dikerjakan)
                    </span>
                    @elseif($currentHandRaise->status === 'needs_revision')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-800 border border-orange-200">
                        <i class="fa-solid fa-triangle-exclamation text-orange-600"></i> Perlu Perbaikan Pra-Presentasi
                    </span>
                    @elseif($currentHandRaise->status === 'ready')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i> Sudah Presentasi
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Menunggu Respon Mentor
                    </span>
                    @endif
                </div>

                @if($currentHandRaise->type === 'presentation')
                @if($currentHandRaise->presentation_date)
                <div class="flex items-center justify-between text-xs text-gray-600">
                    <span class="font-medium">Jadwal:</span>
                    <span class="font-semibold text-gray-800">{{ $currentHandRaise->presentation_date->format('d M Y') }}</span>
                </div>
                @endif
                @if($currentHandRaise->presentation_mode)
                <div class="flex items-center justify-between text-xs text-gray-600">
                    <span class="font-medium">Mode:</span>
                    <span class="font-semibold text-gray-800">{{ ucfirst($currentHandRaise->presentation_mode) }}</span>
                </div>
                @endif
                @if($currentHandRaise->presentation_mode === 'online')
                @php
                $activeMeetUrl = $currentHandRaise->meet_url ?: ($user->intern?->division?->meet_url ?? null);
                @endphp
                <div class="p-3 bg-sky-50 border border-sky-200 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-sky-900 flex items-center gap-1.5">
                            <i class="fa-solid fa-video text-sky-600"></i> Link Google Meet Presentasi
                        </span>
                        <span class="text-[10px] bg-sky-200/70 text-sky-800 font-semibold px-2 py-0.5 rounded-full">Online</span>
                    </div>
                    @if($activeMeetUrl)
                    <div class="flex items-center gap-1.5">
                        <input type="text" readonly value="{{ $activeMeetUrl }}"
                            class="w-full text-xs p-2 bg-white border border-sky-200 rounded-lg text-sky-900 font-mono select-all">
                        <button type="button" onclick="copyMeetLink('{{ $activeMeetUrl }}', this)"
                            class="px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shrink-0 transition flex items-center gap-1"
                            title="Salin Link Google Meet">
                            <i class="fa-regular fa-copy"></i>
                            <span>Salin</span>
                        </button>
                        <a href="{{ $activeMeetUrl }}" target="_blank" rel="noopener noreferrer"
                            class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shrink-0 transition flex items-center gap-1"
                            title="Buka Google Meet">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            <span>Buka</span>
                        </a>
                    </div>
                    @else
                    <p class="text-xs text-sky-800">Link Google Meet untuk divisi Anda belum diatur oleh admin. Harap hubungi admin/mentor.</p>
                    @endif
                </div>
                @endif
                @endif

                @if($currentHandRaise->notes || $currentHandRaise->reason)
                <div class="pt-2 border-t border-gray-200">
                    <span class="text-xs font-semibold text-gray-500 block mb-1">Catatan / Materi:</span>
                    <p class="text-xs text-gray-700 bg-white p-2.5 rounded-lg border border-gray-200 whitespace-pre-line">{{ $currentHandRaise->notes ?? $currentHandRaise->reason }}</p>
                </div>
                @endif

                @if(!empty($currentHandRaise->admin_response))
                <div class="pt-2 border-t border-gray-200">
                    <span class="text-xs font-semibold block mb-1">
                        @if($currentHandRaise->status === 'needs_revision')
                        <span class="text-orange-800 font-bold flex items-center gap-1">
                            <i class="fa-solid fa-triangle-exclamation"></i> Catatan Perbaikan Pra-Presentasi:
                        </span>
                        @elseif($currentHandRaise->type === 'new_task')
                        <span class="text-emerald-800 font-bold flex items-center gap-1">
                            <i class="fa-solid fa-clipboard-check"></i> Instruksi Tugas dari Pembimbing:
                        </span>
                        @else
                        <span class="text-blue-800 font-bold flex items-center gap-1">
                            <i class="fa-solid fa-reply"></i> Tanggapan dari Pembimbing:
                        </span>
                        @endif
                    </span>
                    <div class="text-xs text-gray-800 bg-white p-2.5 rounded-lg border border-gray-200 whitespace-pre-line leading-relaxed">
                        {{ $currentHandRaise->admin_response }}
                    </div>
                    @if($currentHandRaise->type === 'new_task')
                    <div class="mt-2 text-right">
                        <a href="{{ route('user.tasks.index') }}" class="inline-flex items-center gap-1 text-xs text-purple-700 hover:text-purple-900 font-bold underline">
                            <span>Buka Detail di Halaman Tugas</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                    @endif
                </div>
                @endif

                <div class="text-[11px] text-gray-400 text-right">
                    Diajukan: {{ $currentHandRaise->created_at?->diffForHumans() }}
                </div>
            </div>
            @else
            <p class="text-xs text-gray-500 text-center py-4">Tidak ada permintaan bantuan aktif saat ini.</p>
            @endif

            <p class="text-xs text-gray-500 leading-relaxed">
                Jika Anda sudah selesai berkonsultasi atau ingin membatalkan bantuan, klik tombol <strong>Turunkan Tangan</strong> di bawah.
            </p>
        </div>

        <!-- Footer -->
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
            <button type="button" onclick="closeLowerHandModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl text-xs font-medium hover:bg-gray-100 transition">
                Tutup
            </button>
            <form action="{{ route('intern.raisehand.toggle') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="lower">
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-hand-holding"></i> Turunkan Tangan
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Popup Link Google Meet (Otomatis Muncul Setelah Ajukan Presentasi Online) -->
<div id="onlineMeetModal" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden z-[99999] p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden flex flex-col animate-fadeIn border border-gray-100">
        <!-- Header -->
        <div class="bg-gradient-to-r from-sky-600 to-blue-700 text-white p-5 text-center relative">
            <button type="button" onclick="closeOnlineMeetModal()" class="absolute top-3 right-3 text-white/80 hover:text-white text-2xl font-bold leading-none">&times;</button>
            <div class="w-14 h-14 mx-auto rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-2xl mb-2 shadow-inner">
                <i class="fa-solid fa-video"></i>
            </div>
            <h3 class="text-lg font-bold">Link Google Meet Presentasi</h3>
            <p class="text-xs text-sky-100 mt-0.5">
                Divisi: {{ session('division_name') ?? ($user->intern?->division?->name ?? 'Divisi') }}
            </p>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4">
            @php
            $sessionMeetUrl = session('meet_url') ?? ($user->intern?->division?->meet_url ?? null);
            @endphp

            @if($sessionMeetUrl)
            <div class="p-3.5 bg-sky-50 border border-sky-200 rounded-xl text-xs text-sky-900 leading-relaxed flex items-start gap-2.5">
                <i class="fa-solid fa-circle-info text-sky-600 mt-0.5 text-sm shrink-0"></i>
                <span>Jadwal presentasi online berhasil diajukan! Gunakan link Google Meet di bawah ini untuk bergabung saat sesi presentasi Anda dimulai.</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Tautan Google Meet
                </label>
                <div class="flex items-center gap-2">
                    <input type="text" id="popupMeetUrlInput" readonly value="{{ $sessionMeetUrl }}"
                        class="w-full text-xs font-mono p-3 bg-gray-50 border border-gray-300 rounded-xl text-gray-800 select-all focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <!-- Tombol Aksi Utama -->
            <div class="grid grid-cols-2 gap-2.5 pt-1">
                <button type="button" id="btnCopyPopupMeet" onclick="copyPopupMeetLink()"
                    class="w-full py-2.5 px-4 bg-gray-800 hover:bg-gray-900 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                    <i class="fa-regular fa-copy text-sm" id="iconCopyPopupMeet"></i>
                    <span id="textCopyPopupMeet">Salin Link</span>
                </button>

                <a href="{{ $sessionMeetUrl }}" target="_blank" rel="noopener noreferrer"
                    class="w-full py-2.5 px-4 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    <span>Buka GMeet</span>
                </a>
            </div>
            @else
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-center">
                <div class="w-10 h-10 mx-auto rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-lg mb-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h4 class="text-xs font-bold text-amber-900 mb-1">Link Meet Belum Tersedia</h4>
                <p class="text-xs text-amber-800 leading-relaxed">
                    Admin belum mengatur tautan Google Meet untuk divisi <strong>{{ session('division_name') ?? ($user->intern?->division?->name ?? 'Anda') }}</strong>. Mohon hubungi admin atau pembimbing untuk mendapatkan tautan meet.
                </p>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button type="button" onclick="closeOnlineMeetModal()"
                class="px-5 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-xs font-semibold transition">
                Tutup
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

            if (labelProof) labelProof.innerHTML = 'Link Dokumen Pendukung <span class="text-rose-500">*</span>';
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
        $('#lowerHandModal').removeClass('hidden').addClass('flex');
    }

    function closeLowerHandModal() {
        $('#lowerHandModal').addClass('hidden').removeClass('flex');
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

    @if(session('show_online_meet_modal'))
    $(document).ready(function() {
        openOnlineMeetModal();
    });
    @endif

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
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-600 text-white">
                        TERJADWAL
                    </span>
                    <span class="text-xs text-blue-800 font-medium">
                        Jadwal diajukan untuk <strong>Hari Ini</strong>.
                    </span>
                `;
        } else {
            badge.innerHTML = `
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-600 text-white">
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

    console.log('stage: {{ $stage ?? "not set" }}');

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
</script>
@endsection