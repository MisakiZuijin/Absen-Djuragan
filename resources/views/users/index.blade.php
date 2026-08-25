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
            ])

        </div>

        <!-- Right Side Content -->
        <div class="flex flex-col w-full md:w-4/5 gap-4 md:ml-4">
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
            <div class="flex flex-col md:flex-row items-center mt-4 space-y-2 md:space-y-0 md:space-x-2">
                <!-- Weekly Schedule -->
                <div class="w-full md:w-1/2 mx-auto">
                    <p class="text-2xl font-bold mb-4 text-gray-800 text-center">Jadwalmu Minggu Ini</p>
                    <div class="overflow-y-auto h-40 shadow-lg rounded-lg">
                        <table class="min-w-full border-collapse border border-gray-300 rounded-lg shadow-md text-xs">
                            <thead class="sticky top-0 bg-gray-500">
                                <tr>
                                    <th class="border border-gray-300 p-2 text-gray-100 font-semibold text-center">Tanggal</th>
                                    <th class="border border-gray-300 p-2 text-gray-100 font-semibold text-center">Hari</th>
                                    <th class="border border-gray-300 p-2 text-gray-100 font-semibold text-center">Status</th>
                                    <th class="border border-gray-300 p-2 text-gray-100 font-semibold text-center">Shift</th>
                                    <th class="border border-gray-300 p-2 text-gray-100 font-semibold text-center">Masuk</th>
                                    <th class="border border-gray-300 p-2 text-gray-100 font-semibold text-center">Pulang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($schedules as $schedule)
                                    @php
                                        $isToday = \Carbon\Carbon::parse($schedule->date)->isToday();
                                        $isPast = \Carbon\Carbon::parse($schedule->date)->isPast();
                                        $attdStatusId = $schedule->attdStatus->id;
                                        $bgColorClass = '';

                                        if ($isToday) {
                                            $bgColorClass = 'bg-yellow-100';
                                        } elseif ($isPast) {
                                            switch ($attdStatusId) {
                                                case 1:
                                                    $bgColorClass = '';
                                                    break;
                                                case 2:
                                                    $bgColorClass = 'bg-green-100';
                                                    break;
                                                case 5:
                                                    $bgColorClass = 'bg-red-100';
                                                    break;
                                                default:
                                                    $bgColorClass = '';
                                                    break;
                                            }
                                        }
                                    @endphp
                                    <tr class="{{ $bgColorClass }}">
                                        <td class="border border-gray-300 p-2 text-gray-600 text-center">{{ \Carbon\Carbon::parse($schedule->date)->format('d-m-Y') }}</td>
                                        <td class="border border-gray-300 p-2 text-gray-600 text-center">{{ \Carbon\Carbon::parse($schedule->date)->locale('id')->translatedFormat('l') }}</td>
                                        <td class="border border-gray-300 p-2 text-gray-600 text-center">{{ strtoupper($schedule->work_type) }}</td>
                                        <td class="border border-gray-300 p-2 text-gray-600 text-center">{{ $schedule->shift->name }}</td>
                                        <td class="border border-gray-300 p-2 text-gray-600 text-center">{{ $schedule->shift->start_time }}</td>
                                        <td class="border border-gray-300 p-2 text-gray-600 text-center">{{ $schedule->shift->end_time }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold px-4 py-1 rounded text-sm" onclick="openModalBroadcastListbyId({{ $broadcast->id }})">Detail</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="border px-4 py-2 text-center">Tidak ada pengumuman.</td></tr>
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
                    <button onclick="closeModalBroadcastListbyId({{ $item->id }})" class="text-gray-500 hover:text-gray-800 transition">✕</button>
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
                <div class="px-6 py-4 border-t text-xs text-gray-500">
                    Diterbitkan: {{ $item->created_at->translatedFormat('l, d F Y H:i') }}
                </div>
            </div>
        </div>
    @endforeach

    <!-- Activity Log Popup -->
    <div id="popup-form" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white px-8 py-5 rounded-lg shadow-lg w-full h-full md:w-1/2 md:h-auto relative">
            <div class="flex items-center justify-between relative">
                <h2 class="text-2xl font-bold">Activity Log</h2>
            </div>
            <form id="activity-form" action="{{ route('home.logActivity.action') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <input type="number" hidden name="user_id" value="{{ $user->id }}">
                    <label class="block font-bold mb-2" for="activity">Keterangan</label>
                    <textarea class="border border-gray-300 p-2 w-full h-40" id="activity" name="activity"
                        placeholder="Apa yang telah anda kerjakan hari ini" required></textarea>
                    <div id="activity-error" class="text-red-500 text-sm mt-1 hidden"></div>
                    <div class="text-red-500 mt-2">Note: Jangan lupa submit sebelum klik tombol pulang</div>
                </div>
                <div class="flex justify-end">
                    <button type="button" id="closeModal" class="px-4 py-2 border border-gray-600 rounded-lg mr-2">Batal</button>
                    <button id="submit-button" type="submit" class="bg-gray-800 text-white px-4 py-2 rounded focus:outline-none hover:bg-gray-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Holiday Modal -->
    <div id="holidayModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg w-3/4 lg:w-1/2 p-4 shadow-lg">
            <div class="flex justify-between items-center border-b pb-2">
                <h2 class="text-xl font-bold">Daftar Hari Libur</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="overflow-y-auto max-h-60 mt-4">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="border px-4 py-2 bg-gray-100">No</th>
                            <th class="border px-4 py-2 bg-gray-100">Tanggal</th>
                            <th class="border px-4 py-2 bg-gray-100">Hari Libur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($holiday_data as $index => $holiday)
                            <tr>
                                <td class="border px-4 py-2 text-center">{{ $index + 1 }}</td>
                                <td class="border px-4 py-2">
                                    {{ \Carbon\Carbon::parse($holiday->date)->locale('id')->isoFormat('dddd, DD-MM-YYYY') }}
                                </td>
                                <td class="border px-4 py-2">{{ $holiday->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="border px-4 py-2 text-center">Information not available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end pt-4">
                <button onclick="closeModal()" class="bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Action Modal -->
    <div id="actionModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex justify-center items-center hidden z-50">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-11/12 sm:w-full sm:max-w-lg">
            <div class="bg-gray-700 text-white p-4">
                <h3 class="text-lg leading-6 font-medium">Keterangan</h3>
            </div>
            <div id="modalContent" class="p-4">
                <textarea id="modalTextarea" class="w-full border-gray-300 shadow-sm bg-slate-100 p-2" rows="4"
                    placeholder="Tuliskan keterangan (opsional)"></textarea>
                <div class="flex justify-end items-center">
                    <button type="button" id="save-attd" class="bg-gray-700 hover:bg-gray-500 text-white font-bold py-2 px-8 rounded-md">
                        Simpan
                    </button>
                    <svg aria-hidden="true" id="loading-spinner" class="w-8 h-8 ml-4 text-gray-200 animate-spin hidden fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
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

    <!-- Absence Permit Modal -->
    <div id="modal2" class="fixed inset-0 flex items-center z-50 justify-center bg-gray-900 bg-opacity-50 hidden">
        <div class="bg-white p-6 m-3 rounded-lg shadow-lg w-full max-w-md relative" onclick="event.stopPropagation();">
            <h2 class="text-xl font-semibold mb-4">Form Izin Tidak Hadir</h2>
            <form method="POST" action="{{ route('attendance.addPermitPresenceUser') }}">
                @csrf
                <input type="hidden" id="jam-option" name="jam-option" class="w-full border border-gray-300 rounded-lg p-2" value="0" />
                <input type="hidden" id="id" name="id" class="w-full border border-gray-300 rounded-lg p-2" value="{{ $detail_schedule_id ?? 0 }}" />

                <div class="mb-4">
                    <label for="keterangan" class="block text-gray-700 mb-2">Keterangan<span class="text-red-500">*</span></label>
                    <textarea id="keterangan" name="keterangan" rows="4" class="w-full h-20 border border-gray-300 rounded-lg p-2" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="link-google-drive" class="block text-gray-700 mb-2">Link Google Drive<span class="text-red-500">*</span></label>
                    <input type="url" id="link-google-drive" name="link-google-drive" placeholder="https://drive.google.com/file/..." class="w-full border border-gray-300 rounded-lg p-2" required />
                </div>
                <div class="mb-4">
                    <label for="kategori-izin" class="block text-gray-700 mb-2">Kategori Izin<span class="text-red-500">*</span></label>
                    <select id="kategori-izin" name="kategori-izin" class="w-full border border-gray-300 rounded-lg p-2" required>
                        @foreach ($listPermitCategory as $category)
                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded">Simpan</button>
                </div>
            </form>
            <button id="close-modal" class="absolute top-2 right-2 text-gray-700 hover:text-gray-900" onclick="closeModalIzin();">
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

    <!-- ==================================================================== -->
    <!-- [BARU] WADAH UNTUK NOTIFIKASI DARI SAMPING (TOAST) -->
    <!-- ==================================================================== -->
    <div id="toast-notification" class="hidden fixed top-5 right-5 w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg z-[9999]" role="alert">
        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg">
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
            </svg>
        </div>
        <div class="ms-3 text-sm font-normal" id="toast-message">Pesan notifikasi.</div>
        <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg p-1.5 inline-flex items-center justify-center h-8 w-8" onclick="document.getElementById('toast-notification').classList.add('hidden')">
            <span class="sr-only">Close</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
        </button>
    </div>


    <script src="{{ asset('js/user/index.js') }}"></script>
    <script>
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

            // --- VALIDASI INPUT ACTIVITY LOG ---
            function validateActivityInput(input) {
                // Regex untuk mencegah karakter khusus yang bisa merusak kode PHP/JS
                // Hanya menerima huruf (a-z, A-Z), angka (0-9), spasi, dan tanda baca dasar
                const regex = /^[a-zA-Z0-9\s.,!?():;'-]+$/;

                // Periksa setiap baris input
                const lines = input.split('\n');
                for (let i = 0; i < lines.length; i++) {
                    if (!regex.test(lines[i].trim()) && lines[i].trim() !== '') {
                        return false;
                    }
                }
                return true;
            }

            // --- EVENT LISTENER UNTUK FORM ACTIVITY LOG ---
            $('#activity-form').on('submit', function(e) {
                const activityInput = $('#activity').val().trim();
                const errorDiv = $('#activity-error');

                // Validasi input kosong
                if (!activityInput) {
                    errorDiv.text('Keterangan aktivitas tidak boleh kosong.').removeClass('hidden');
                    e.preventDefault();
                    return;
                }

                // Validasi dengan regex
                if (!validateActivityInput(activityInput)) {
                    errorDiv.text('Input mengandung karakter yang tidak diizinkan. Hanya huruf, angka, dan tanda baca standar yang diperbolehkan.').removeClass('hidden');
                    e.preventDefault();
                    return;
                }

                // Jika validasi berhasil, sembunyikan pesan error
                errorDiv.addClass('hidden');
            });

            // --- EVENT LISTENER UNTUK INPUT ACTIVITY (REAL-TIME VALIDATION) ---
            $('#activity').on('input', function() {
                const errorDiv = $('#activity-error');
                const input = $(this).val();

                if (!validateActivityInput(input)) {
                    errorDiv.text('Input mengandung karakter yang tidak diizinkan. Hanya huruf, angka, dan tanda baca standar yang diperbolehkan.').removeClass('hidden');
                } else {
                    errorDiv.addClass('hidden');
                }
            });

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
