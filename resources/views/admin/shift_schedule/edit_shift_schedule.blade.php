<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>Edit Jadwal Shift {{ $name }} | Absen Djuragan</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Responsive calendar cell dimensions */
        .calendar-box {
            min-height: 3.25rem;
            padding: 0.25rem;
            transition: all 0.15s ease-in-out;
        }
        @media (min-width: 640px) {
            .calendar-box {
                min-height: 4.25rem;
                padding: 0.375rem;
            }
        }
        @media (min-width: 768px) {
            .calendar-box {
                min-height: 5rem;
                padding: 0.5rem;
            }
        }

        .chip-date {
            transition: all 0.15s ease;
        }
        .chip-date:hover {
            transform: translateY(-1px);
        }

        /* Custom scrollbar for horizontal chip / action scrolling */
        .custom-scrollbar::-webkit-scrollbar {
            height: 4px;
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen text-gray-800 antialiased p-2.5 sm:p-5 lg:p-6 pb-20 sm:pb-6">

    <div class="max-w-7xl mx-auto space-y-4 sm:space-y-5">

        <!-- Header Bar -->
        <div class="bg-white rounded-2xl p-3.5 sm:p-5 shadow-xs border border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4 min-w-0">
                <a href="{{ url()->previous() != url()->current() ? url()->previous() : route('admin.pengaturan.shift') }}"
                   class="p-2 sm:p-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors shrink-0"
                   title="Kembali ke halaman sebelumnya">
                    <i class="fa-solid fa-arrow-left text-sm sm:text-base"></i>
                </a>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                        <h1 class="text-base sm:text-2xl font-bold text-gray-900 truncate">
                            Edit Jadwal Shift
                        </h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 sm:py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                            <i class="fa-solid fa-user-graduate text-blue-600 text-[11px]"></i>
                            <span class="truncate max-w-[160px] sm:max-w-xs">{{ $name }}</span>
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5 line-clamp-1 sm:line-clamp-none">
                        Atur jadwal kerja harian pemagang secara satuan atau massal.
                    </p>
                </div>
            </div>

            <!-- Stats Ringkasan Bulan Aktif (Responsive Grid on Mobile) -->
            <div class="grid grid-cols-3 w-full md:w-auto gap-1.5 sm:gap-3 bg-gray-50 p-2 sm:p-2.5 rounded-xl border border-gray-200 text-xs shrink-0">
                <div class="text-center px-1 sm:px-2 border-r border-gray-200">
                    <span class="block text-gray-500 text-[10px] uppercase font-bold tracking-wider">Terjadwal</span>
                    <span id="stat-scheduled-count" class="font-bold text-blue-600 text-xs sm:text-sm">0 hari</span>
                </div>
                <div class="text-center px-1 sm:px-2 border-r border-gray-200">
                    <span class="block text-gray-500 text-[10px] uppercase font-bold tracking-wider">Hari Libur</span>
                    <span id="stat-holiday-count" class="font-bold text-pink-600 text-xs sm:text-sm">0 hari</span>
                </div>
                <div class="text-center px-1 sm:px-2">
                    <span class="block text-gray-500 text-[10px] uppercase font-bold tracking-wider">Terpilih</span>
                    <span id="stat-selected-count" class="font-bold text-emerald-600 text-xs sm:text-sm">0 tgl</span>
                </div>
            </div>
        </div>

        <!-- Main Layout Grid: Kalender (7 Kolom) & Panel Edit (5 Kolom) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5 items-start">

            <!-- Kolom 1: Kalender Interaktif (7 col on Desktop) -->
            <div class="lg:col-span-7 xl:col-span-7 bg-white rounded-2xl p-3 sm:p-5 shadow-xs border border-gray-200 flex flex-col space-y-3 sm:space-y-4">
                
                <!-- Month Navigator & Quick Actions -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 border-b border-gray-100 pb-3 sm:pb-4">
                    <!-- Navigasi Bulan -->
                    <div class="flex items-center justify-between sm:justify-start w-full sm:w-auto space-x-1 sm:space-x-2">
                        <button id="prev-month" type="button"
                            class="p-2 w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center justify-center transition-colors shrink-0"
                            title="Bulan Sebelumnya">
                            <i class="fa-solid fa-chevron-left text-xs"></i>
                        </button>
                        <h2 id="current-month" class="text-sm sm:text-base md:text-lg font-bold text-gray-900 flex-1 sm:flex-initial text-center sm:min-w-[160px] truncate px-1">
                            Memuat Bulan...
                        </h2>
                        <button id="next-month" type="button"
                            class="p-2 w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center justify-center transition-colors shrink-0"
                            title="Bulan Berikutnya">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </button>
                        <button id="today-btn" type="button"
                            class="px-2 sm:px-2.5 py-1 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border border-blue-200 shrink-0"
                            title="Kembali ke Bulan Sekarang">
                            Hari Ini
                        </button>
                    </div>

                    <!-- Quick Select Helpers (Horizontal Scroll on Mobile) -->
                    <div class="flex items-center gap-1.5 overflow-x-auto custom-scrollbar w-full sm:w-auto pb-1 sm:pb-0">
                        <button id="select-weekdays-btn" type="button"
                            class="px-2.5 py-1.5 bg-gray-100 hover:bg-blue-50 hover:text-blue-700 text-gray-700 rounded-lg transition-colors font-medium border border-gray-200 text-xs shrink-0 whitespace-nowrap"
                            title="Pilih semua hari Senin sampai Jumat di bulan ini">
                            <i class="fa-solid fa-calendar-week mr-1 text-blue-500"></i>Sen-Jum
                        </button>
                        <button id="select-saturdays-btn" type="button"
                            class="px-2.5 py-1.5 bg-gray-100 hover:bg-blue-50 hover:text-blue-700 text-gray-700 rounded-lg transition-colors font-medium border border-gray-200 text-xs shrink-0 whitespace-nowrap"
                            title="Pilih semua hari Sabtu di bulan ini">
                            <i class="fa-solid fa-calendar-day mr-1 text-indigo-500"></i>Sabtu
                        </button>
                        <button id="refresh-button" type="button"
                            class="px-2.5 py-1.5 bg-gray-100 hover:bg-red-50 hover:text-red-700 text-gray-600 rounded-lg transition-colors font-medium border border-gray-200 text-xs shrink-0 whitespace-nowrap"
                            title="Kosongkan pilihan tanggal">
                            <i class="fa-solid fa-rotate-left mr-1"></i>Reset
                        </button>
                    </div>
                </div>

                <!-- Calendar Weekday Headers -->
                <div class="grid grid-cols-7 gap-1 sm:gap-2 text-center text-[11px] sm:text-xs font-bold uppercase tracking-wider pb-0.5">
                    <div class="text-red-500 py-1 bg-red-50/60 rounded-md sm:rounded-lg">Min</div>
                    <div class="text-gray-700 py-1 bg-gray-50 rounded-md sm:rounded-lg">Sen</div>
                    <div class="text-gray-700 py-1 bg-gray-50 rounded-md sm:rounded-lg">Sel</div>
                    <div class="text-gray-700 py-1 bg-gray-50 rounded-md sm:rounded-lg">Rab</div>
                    <div class="text-gray-700 py-1 bg-gray-50 rounded-md sm:rounded-lg">Kam</div>
                    <div class="text-gray-700 py-1 bg-gray-50 rounded-md sm:rounded-lg">Jum</div>
                    <div class="text-blue-600 py-1 bg-blue-50/60 rounded-md sm:rounded-lg">Sab</div>
                </div>

                <!-- Calendar Boxes Grid -->
                <div class="grid grid-cols-7 gap-1 sm:gap-2" id="box-container">
                    <!-- Dynamic boxes generated via JS -->
                </div>

                <!-- Legenda Status & Petunjuk -->
                <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs">
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <span class="inline-flex items-center gap-1.5 text-gray-600">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Ada Jadwal
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-gray-600">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span> Libur Nasional
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-gray-600">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span> Minggu
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-gray-600">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Terpilih
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-gray-400">
                            <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span> Kosong
                        </span>
                    </div>
                    <div class="text-gray-400 text-[11px]">
                        <i class="fa-solid fa-circle-info mr-1"></i> Klik kotak tanggal untuk memilih
                    </div>
                </div>
            </div>

            <!-- Kolom 2: Panel Pengaturan Jadwal Terpadu (5 col on Desktop) -->
            <div id="panel-container" class="lg:col-span-5 xl:col-span-5 bg-white rounded-2xl p-3.5 sm:p-5 shadow-xs border border-gray-200 scroll-mt-4">
                
                <!-- Tab Mode Switcher -->
                <div class="flex items-center bg-gray-100 p-1 rounded-xl mb-3.5 sm:mb-4 text-xs font-semibold">
                    <button id="tab-single-btn" type="button"
                        class="flex-1 py-2 px-3 rounded-lg flex items-center justify-center gap-2 transition-all bg-white text-blue-700 shadow-xs font-semibold">
                        <i class="fa-solid fa-calendar-day"></i>
                        <span>Update Satuan</span>
                    </button>
                    <button id="tab-multi-btn" type="button"
                        class="flex-1 py-2 px-3 rounded-lg flex items-center justify-center gap-2 transition-all text-gray-600 hover:text-gray-900 font-semibold">
                        <i class="fa-solid fa-layer-group"></i>
                        <span>Update Massal (<span id="multi-count-badge">0</span>)</span>
                    </button>
                </div>

                <!-- MODE 1: FORM UPDATE SATUAN (SINGLE) -->
                <div id="panel-single-content" class="space-y-3.5 sm:space-y-4">
                    <div class="p-3 bg-blue-50/50 border border-blue-100 rounded-xl flex items-center justify-between gap-2.5">
                        <div class="min-w-0">
                            <span class="text-[10px] sm:text-[11px] font-semibold text-blue-600 uppercase tracking-wider block">Tanggal Dipilih:</span>
                            <h3 id="single-display-date" class="text-xs sm:text-sm md:text-base font-bold text-gray-900 truncate mt-0.5">
                                Pilih tanggal di kalender
                            </h3>
                        </div>
                        <span id="single-current-badge" class="shrink-0 px-2 py-0.5 sm:px-2.5 sm:py-1 text-[11px] sm:text-xs font-semibold rounded-lg bg-white text-gray-600 border border-blue-200">
                            Belum dipilih
                        </span>
                    </div>

                    <input id="detail_schedule_id" type="number" hidden>
                    <input id="date-selected" type="hidden">
                    <input id="day-selected" type="hidden">

                    <div class="space-y-3">
                        <div>
                            <label for="shift" class="block text-xs font-semibold text-gray-700 mb-1">
                                Shift Kerja <span class="text-red-500">*</span>
                            </label>
                            <select id="shift"
                                class="w-full px-3 py-2 sm:py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm text-gray-800 bg-white">
                                <option value="" disabled selected>-- Pilih Shift --</option>
                                @foreach ($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                            <div>
                                <label for="work-type" class="block text-xs font-semibold text-gray-700 mb-1">
                                    Tipe Kerja <span class="text-red-500">*</span>
                                </label>
                                <select id="work-type"
                                    class="w-full px-3 py-2 sm:py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm text-gray-800 bg-white">
                                    <option value="" disabled selected>-- Pilih Tipe --</option>
                                    @foreach ($workTypes as $type)
                                    <option value="{{ $type }}">{{ Str::upper($type) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="change_time" class="block text-xs font-semibold text-gray-700 mb-1">
                                    Tipe Jadwal <span class="text-red-500">*</span>
                                </label>
                                <select id="change_time"
                                    class="w-full px-3 py-2 sm:py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm text-gray-800 bg-white">
                                    <option value="0" selected>Jadwal Biasa</option>
                                    <option value="1">Ganti Jam</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                            <div>
                                <label for="office" class="block text-xs font-semibold text-gray-700 mb-1">
                                    Kantor <span class="text-red-500">*</span>
                                </label>
                                <select id="office"
                                    class="w-full px-3 py-2 sm:py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm text-gray-800 bg-white">
                                    <option value="" disabled selected>-- Pilih Kantor --</option>
                                    @foreach ($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="back_earlier" class="block text-xs font-semibold text-gray-700 mb-1">
                                    Pulang Cepat
                                </label>
                                <select id="back_earlier"
                                    class="w-full px-3 py-2 sm:py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm text-gray-800 bg-white">
                                    <option value="0" selected>Tidak</option>
                                    <option value="1">Ya</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-1.5 sm:pt-2">
                            <button id="submit_single" type="button"
                                class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-2.5 sm:py-3 px-4 rounded-xl text-xs sm:text-sm transition-colors shadow-xs flex items-center justify-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span>Simpan Jadwal Tanggal Ini</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- MODE 2: FORM UPDATE MASSAL (BULK) -->
                <div id="panel-multi-content" class="space-y-3.5 sm:space-y-4 hidden">
                    <div class="p-3 bg-indigo-50/50 border border-indigo-100 rounded-xl">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] sm:text-[11px] font-semibold text-indigo-700 uppercase tracking-wider block">
                                Tanggal Terpilih:
                            </span>
                            <span id="bulk-dates-counter" class="text-xs font-bold text-indigo-700 bg-white px-2 py-0.5 rounded-md border border-indigo-200">
                                0 tanggal
                            </span>
                        </div>
                        
                        <!-- List of selected date chips with remove button -->
                        <div id="selected_dates_chips" class="flex flex-wrap gap-1 sm:gap-1.5 max-h-24 sm:max-h-28 overflow-y-auto custom-scrollbar p-0.5">
                            <span class="text-xs text-gray-400 italic">Belum ada tanggal dipilih. Klik tanggal pada kalender.</span>
                        </div>
                        <!-- Hidden text field for backward compatibility -->
                        <div id="selected_dates" class="hidden"></div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label for="shift_multi" class="block text-xs font-semibold text-gray-700 mb-1">
                                Shift Baru <span class="text-red-500">*</span>
                            </label>
                            <select id="shift_multi"
                                class="w-full px-3 py-2 sm:py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs sm:text-sm text-gray-800 bg-white">
                                @foreach ($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                            <div>
                                <label for="work_type_multi" class="block text-xs font-semibold text-gray-700 mb-1">
                                    Tipe Kerja <span class="text-red-500">*</span>
                                </label>
                                <select id="work_type_multi"
                                    class="w-full px-3 py-2 sm:py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs sm:text-sm text-gray-800 bg-white">
                                    @foreach ($workTypes as $type)
                                    <option value="{{ $type }}">{{ Str::upper($type) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="change_time_all" class="block text-xs font-semibold text-gray-700 mb-1">
                                    Tipe Jadwal <span class="text-red-500">*</span>
                                </label>
                                <select id="change_time_all"
                                    class="w-full px-3 py-2 sm:py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs sm:text-sm text-gray-800 bg-white">
                                    <option value="0" selected>Jadwal Biasa</option>
                                    <option value="1">Ganti Jam</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="office_multi" class="block text-xs font-semibold text-gray-700 mb-1">
                                Kantor Penempatan <span class="text-red-500">*</span>
                            </label>
                            <select id="office_multi"
                                class="w-full px-3 py-2 sm:py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs sm:text-sm text-gray-800 bg-white">
                                @foreach ($offices as $office)
                                <option value="{{ $office->id }}">{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pt-1.5 sm:pt-2">
                            <button id="submit_multiple" type="button"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold py-2.5 sm:py-3 px-4 rounded-xl text-xs sm:text-sm transition-colors shadow-xs flex items-center justify-center gap-2">
                                <i class="fa-solid fa-layer-group"></i>
                                <span id="submit-multiple-text">Terapkan Perubahan Massal</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Floating Bottom Bar for Mobile when Dates are Selected -->
    <div id="mobile-selection-bar"
        class="lg:hidden fixed bottom-3 left-3 right-3 z-40 bg-gray-900/95 backdrop-blur-xs text-white p-3 rounded-2xl shadow-xl flex items-center justify-between border border-gray-700 transform translate-y-24 opacity-0 transition-all duration-300 pointer-events-none">
        <div class="flex items-center space-x-2 min-w-0">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shrink-0 animate-pulse"></span>
            <span id="mobile-bar-text" class="text-xs font-semibold truncate">1 tanggal terpilih</span>
        </div>
        <button id="mobile-scroll-to-panel-btn" type="button"
            class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-xs font-bold shadow-xs flex items-center space-x-1.5 transition-colors shrink-0">
            <span>Atur Jadwal</span>
            <i class="fa-solid fa-arrow-down text-[10px]"></i>
        </button>
    </div>

    <!-- Hidden Template for Calendar Box -->
    <template id="box-template">
        <div class="calendar-box box flex flex-col justify-between rounded-xl cursor-pointer select-none border transition-all">
            <div class="flex items-center justify-between">
                <span class="box-date font-bold">1</span>
                <span class="box-status-dot w-1.5 h-1.5 rounded-full hidden"></span>
            </div>
            <div class="box-badge-container mt-auto">
                <span class="box-badge leading-tight rounded-md truncate block max-w-full text-center"></span>
            </div>
        </div>
    </template>

    <!-- Init Data Script -->
    <script type="application/json" id="schedule-init-data">
        {
            "internId": @json($intern_id),
            "scheduleData": @json($schedule_data),
            "holidayData": @json($holiday_data)
        }
    </script>

    <script>
        const initData = JSON.parse(document.getElementById('schedule-init-data')?.textContent || '{}');
        const container = document.querySelector("#box-container");
        const template = document.querySelector("#box-template");
        const selectedDates = new Set();
        const internId = initData.internId;

        // Lookup maps for shifts and offices
        const shiftsMap = {
            @foreach ($shifts as $s)
                {{ $s->id }}: @json($s->name),
            @endforeach
        };

        const officesMap = {
            @foreach ($offices as $o)
                {{ $o->id }}: @json($o->name),
            @endforeach
        };

        // Inputs & elements - Single Update
        const currentSelectedInput = document.getElementById("date-selected");
        const monthView = document.getElementById("current-month");
        const dayView = document.getElementById("day-selected");
        const singleDisplayDate = document.getElementById("single-display-date");
        const singleCurrentBadge = document.getElementById("single-current-badge");
        const shiftSelect = document.getElementById("shift");
        const workTypeSelect = document.getElementById("work-type");
        const changeTimeSelect = document.getElementById("change_time");
        const officeSelect = document.getElementById("office");
        const detailScheduleId = document.getElementById("detail_schedule_id");
        const backEarlierSelected = document.getElementById("back_earlier");

        // Inputs & elements - Multi Update
        const selectedInput = document.getElementById("selected_dates");
        const selectedDatesChips = document.getElementById("selected_dates_chips");
        const bulkDatesCounter = document.getElementById("bulk-dates-counter");
        const shiftMultiInput = document.getElementById("shift_multi");
        const workTypeMulti = document.getElementById("work_type_multi");
        const officeMulti = document.getElementById("office_multi");
        const changeTimeMulti = document.getElementById("change_time_all");
        const multiCountBadge = document.getElementById("multi-count-badge");
        const submitMultipleText = document.getElementById("submit-multiple-text");

        // Tab buttons & panels
        const tabSingleBtn = document.getElementById("tab-single-btn");
        const tabMultiBtn = document.getElementById("tab-multi-btn");
        const panelSingleContent = document.getElementById("panel-single-content");
        const panelMultiContent = document.getElementById("panel-multi-content");

        // Stats elements
        const statScheduledCount = document.getElementById("stat-scheduled-count");
        const statHolidayCount = document.getElementById("stat-holiday-count");
        const statSelectedCount = document.getElementById("stat-selected-count");

        // Mobile floating bar elements
        const mobileBar = document.getElementById("mobile-selection-bar");
        const mobileBarText = document.getElementById("mobile-bar-text");
        const mobileScrollBtn = document.getElementById("mobile-scroll-to-panel-btn");

        let data = initData.scheduleData;
        let scheduleData = data != null ? data['schedule_data'] : [];
        let holidayData = initData.holidayData || [];

        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();
        let activeTab = 'single';

        function checkIsDataAvailable(targetDate) {
            return scheduleData.find(schedule => schedule.date === targetDate) ?? null;
        }

        function isHoliday(date) {
            return holidayData.find(holiday => holiday.date === date) ?? null;
        }

        // Tab Switching Logic
        function switchTab(mode) {
            activeTab = mode;
            if (mode === 'single') {
                tabSingleBtn.className = "flex-1 py-2 px-3 rounded-lg flex items-center justify-center gap-2 transition-all bg-white text-blue-700 shadow-xs font-semibold";
                tabMultiBtn.className = "flex-1 py-2 px-3 rounded-lg flex items-center justify-center gap-2 transition-all text-gray-600 hover:text-gray-900 font-semibold";
                panelSingleContent.classList.remove('hidden');
                panelMultiContent.classList.add('hidden');
            } else {
                tabMultiBtn.className = "flex-1 py-2 px-3 rounded-lg flex items-center justify-center gap-2 transition-all bg-white text-indigo-700 shadow-xs font-semibold";
                tabSingleBtn.className = "flex-1 py-2 px-3 rounded-lg flex items-center justify-center gap-2 transition-all text-gray-600 hover:text-gray-900 font-semibold";
                panelMultiContent.classList.remove('hidden');
                panelSingleContent.classList.add('hidden');
            }
        }

        tabSingleBtn.addEventListener('click', () => switchTab('single'));
        tabMultiBtn.addEventListener('click', () => switchTab('multi'));

        // Render Calendar
        function setMonthDate(monthNumber, year) {
            container.replaceChildren();

            const daysInMonth = getDaysInMonth(year, monthNumber);
            const firstDayIndex = new Date(year, monthNumber, 1).getDay(); // 0 = Minggu
            monthView.textContent = getMonthName(monthNumber) + " " + year;

            let scheduledDaysCount = 0;
            let holidaysDaysCount = 0;

            // Empty offset slots for days before 1st of month
            for (let e = 0; e < firstDayIndex; e++) {
                const emptySlot = document.createElement('div');
                emptySlot.className = "calendar-box rounded-xl bg-gray-50/50 border border-transparent";
                container.appendChild(emptySlot);
            }

            // Real month days (1 to daysInMonth)
            for (let i = 1; i <= daysInMonth; i++) {
                const boxClone = template.content.cloneNode(true);
                const box = boxClone.querySelector(".box");
                const dateNumber = box.querySelector(".box-date");
                const badge = box.querySelector(".box-badge");

                dateNumber.textContent = i;
                const dateStr = i.toString();
                const currentDate = `${year}-${(monthNumber + 1).toString().padStart(2, '0')}-${i.toString().padStart(2, '0')}`;

                const schedule = checkIsDataAvailable(currentDate);
                const holiday = isHoliday(currentDate);
                const isSunday = getDayIndex(year, monthNumber, i) === 0;
                const isSaturday = getDayIndex(year, monthNumber, i) === 6;
                const isSelected = selectedDates.has(dateStr);

                if (schedule) scheduledDaysCount++;
                if (holiday) holidaysDaysCount++;

                // Base styling based on state (Soft pastel & clean white tones)
                if (isSelected) {
                    box.className = "calendar-box box flex flex-col justify-between rounded-xl cursor-pointer select-none border-2 border-emerald-500 bg-emerald-50/40 text-gray-900 shadow-xs ring-2 ring-emerald-100";
                    dateNumber.className = "box-date text-xs sm:text-sm font-extrabold text-emerald-700";
                    badge.className = "box-badge text-[9px] sm:text-[10px] leading-tight px-1 sm:px-1.5 py-0.5 rounded truncate block max-w-full font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200 text-center";
                    badge.textContent = "✓ Terpilih";
                } else if (holiday) {
                    box.className = "calendar-box box flex flex-col justify-between rounded-xl cursor-pointer select-none border border-rose-200/80 bg-rose-50/30 text-gray-800 hover:bg-rose-50/60";
                    dateNumber.className = "box-date text-xs sm:text-sm font-bold text-rose-600";
                    badge.className = "box-badge text-[9px] sm:text-[10px] leading-tight px-1 sm:px-1.5 py-0.5 rounded truncate block max-w-full font-medium bg-rose-50 text-rose-700 border border-rose-200/60 text-center";
                    badge.textContent = holiday.name || "Libur";
                    box.title = "Hari Libur: " + holiday.name;
                } else if (schedule) {
                    const shiftName = shiftsMap[schedule.shift_id] || 'Shift';
                    const workType = schedule.work_type ? schedule.work_type.toUpperCase() : 'WFO';
                    const officeName = officesMap[schedule.office_id] || '';

                    box.className = "calendar-box box flex flex-col justify-between rounded-xl cursor-pointer select-none border border-blue-200/80 bg-blue-50/30 text-gray-800 hover:bg-blue-50/60";
                    dateNumber.className = "box-date text-xs sm:text-sm font-bold text-blue-700";
                    badge.className = "box-badge text-[9px] sm:text-[10px] leading-tight px-1 sm:px-1.5 py-0.5 rounded truncate block max-w-full font-semibold bg-blue-50 text-blue-700 border border-blue-200/70 text-center";
                    badge.textContent = shiftName;
                    box.title = `Shift: ${shiftName}` + (workType ? ` (${workType})` : '') + (officeName ? `\nKantor: ${officeName}` : '');
                } else if (isSunday) {
                    box.className = "calendar-box box flex flex-col justify-between rounded-xl cursor-pointer select-none border border-gray-200/70 bg-white text-gray-700 hover:bg-gray-50/50";
                    dateNumber.className = "box-date text-xs sm:text-sm font-bold text-red-500";
                    badge.className = "box-badge text-[9px] sm:text-[10px] leading-tight px-0.5 sm:px-1 py-0.5 rounded truncate block max-w-full text-red-400 font-medium text-center";
                    badge.textContent = "Minggu";
                } else {
                    box.className = "calendar-box box flex flex-col justify-between rounded-xl cursor-pointer select-none border border-gray-200/80 bg-white text-gray-700 hover:bg-gray-50/70 hover:border-gray-300";
                    dateNumber.className = "box-date text-xs sm:text-sm font-medium text-gray-700";
                    badge.className = "box-badge text-[9px] sm:text-[10px] leading-tight px-0.5 sm:px-1 py-0.5 rounded truncate block max-w-full text-gray-400 text-center";
                    badge.textContent = isSaturday ? "Sabtu" : "-";
                }

                // Click event on calendar box
                box.addEventListener("click", () => {
                    handleDateBoxClick(dateStr, i, monthNumber, year, schedule);
                });

                container.appendChild(boxClone);
            }

            // Update stats
            statScheduledCount.textContent = `${scheduledDaysCount} hari`;
            statHolidayCount.textContent = `${holidaysDaysCount} hari`;
            statSelectedCount.textContent = `${selectedDates.size} tgl`;
        }

        // Handle Date Click
        function handleDateBoxClick(dateStr, dayNum, monthNumber, year, schedule) {
            if (selectedDates.has(dateStr)) {
                selectedDates.delete(dateStr);
            } else {
                selectedDates.add(dateStr);
            }

            // Populate single view inputs
            currentSelectedInput.value = dateStr;
            const dayName = getDayName(year, monthNumber, dateStr);
            dayView.value = dayName;

            singleDisplayDate.textContent = `${dayName}, ${dayNum} ${getMonthName(monthNumber)} ${year}`;

            if (schedule) {
                detailScheduleId.value = schedule.id;
                shiftSelect.value = schedule.shift_id;
                workTypeSelect.value = schedule.work_type;
                officeSelect.value = schedule.office_id;
                changeTimeSelect.value = schedule.isChangeSchedule ? "1" : "0";
                backEarlierSelected.value = schedule.isBackFirst ? "1" : "0";
                
                const sName = shiftsMap[schedule.shift_id] || 'Shift';
                singleCurrentBadge.className = "shrink-0 px-2 py-0.5 sm:px-2.5 sm:py-1 text-[11px] sm:text-xs font-semibold rounded-lg bg-blue-50 text-blue-800 border border-blue-200";
                singleCurrentBadge.textContent = sName;
            } else {
                resetSingleForm();
                singleCurrentBadge.className = "shrink-0 px-2 py-0.5 sm:px-2.5 sm:py-1 text-[11px] sm:text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 border border-gray-200";
                singleCurrentBadge.textContent = "Belum Terjadwal";
            }

            // Update multi views & chips
            updateSelectedChips(monthNumber, year);

            // Auto-switch tab based on selection count
            if (selectedDates.size > 1) {
                switchTab('multi');
            } else if (selectedDates.size === 1) {
                switchTab('single');
            }

            // Re-render calendar styling cleanly
            setMonthDate(monthNumber, year);
        }

        // Update chips list in multi panel & mobile floating bar
        function updateSelectedChips(monthNumber, year) {
            const count = selectedDates.size;
            multiCountBadge.textContent = count;
            bulkDatesCounter.textContent = `${count} tanggal`;
            statSelectedCount.textContent = `${count} tgl`;
            submitMultipleText.textContent = count > 0 ? `Terapkan ke ${count} Tanggal Terpilih` : "Terapkan Perubahan Massal";

            // Mobile Floating Bar Toggle
            if (mobileBar && mobileBarText) {
                if (count > 0) {
                    mobileBarText.textContent = `${count} tanggal terpilih`;
                    mobileBar.classList.remove('translate-y-24', 'opacity-0', 'pointer-events-none');
                    mobileBar.classList.add('translate-y-0', 'opacity-100');
                } else {
                    mobileBar.classList.add('translate-y-24', 'opacity-0', 'pointer-events-none');
                    mobileBar.classList.remove('translate-y-0', 'opacity-100');
                }
            }

            selectedDatesChips.replaceChildren();

            if (count === 0) {
                const emptySpan = document.createElement('span');
                emptySpan.className = "text-xs text-gray-400 italic";
                emptySpan.textContent = "Belum ada tanggal dipilih. Klik tanggal pada kalender.";
                selectedDatesChips.appendChild(emptySpan);
                selectedInput.textContent = "Belum ada tanggal dipilih";
                return;
            }

            selectedInput.textContent = Array.from(selectedDates).join(", ");

            // Sort numbers ascending
            const sortedDates = Array.from(selectedDates).map(Number).sort((a, b) => a - b);
            const mName = getMonthName(monthNumber).slice(0, 3);

            sortedDates.forEach(d => {
                const chip = document.createElement('span');
                chip.className = "chip-date inline-flex items-center gap-1.5 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg text-[11px] sm:text-xs font-semibold bg-indigo-50 text-indigo-900 border border-indigo-200 shadow-2xs";
                chip.innerHTML = `<span>${d} ${mName}</span><button type="button" class="text-indigo-400 hover:text-indigo-700 focus:outline-hidden p-0.5" title="Hapus tanggal ini"><i class="fa-solid fa-xmark text-[10px]"></i></button>`;

                chip.querySelector('button').addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectedDates.delete(d.toString());
                    updateSelectedChips(monthNumber, year);
                    setMonthDate(monthNumber, year);
                });

                selectedDatesChips.appendChild(chip);
            });
        }

        // Mobile button scroll listener
        mobileScrollBtn?.addEventListener("click", () => {
            document.getElementById("panel-container")?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        // Quick Selection Handlers
        document.getElementById("select-weekdays-btn").addEventListener("click", () => {
            selectedDates.clear();
            const daysInMonth = getDaysInMonth(currentYear, currentMonth);

            for (let i = 1; i <= daysInMonth; i++) {
                const currentDate = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${i.toString().padStart(2, '0')}`;
                const isSun = getDayIndex(currentYear, currentMonth, i) === 0;
                const isSat = getDayIndex(currentYear, currentMonth, i) === 6;
                const holiday = isHoliday(currentDate);

                if (!isSun && !isSat && !holiday) {
                    selectedDates.add(i.toString());
                }
            }

            updateSelectedChips(currentMonth, currentYear);
            switchTab('multi');
            setMonthDate(currentMonth, currentYear);
        });

        document.getElementById("select-saturdays-btn").addEventListener("click", () => {
            selectedDates.clear();
            const daysInMonth = getDaysInMonth(currentYear, currentMonth);

            for (let i = 1; i <= daysInMonth; i++) {
                const currentDate = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${i.toString().padStart(2, '0')}`;
                const isSat = getDayIndex(currentYear, currentMonth, i) === 6;
                const holiday = isHoliday(currentDate);

                if (isSat && !holiday) {
                    selectedDates.add(i.toString());
                }
            }

            updateSelectedChips(currentMonth, currentYear);
            switchTab('multi');
            setMonthDate(currentMonth, currentYear);
        });

        document.getElementById("refresh-button").addEventListener("click", (e) => {
            e.preventDefault();
            resetSelection();
        });

        document.getElementById("today-btn").addEventListener("click", () => {
            currentMonth = new Date().getMonth();
            currentYear = new Date().getFullYear();
            setMonthDate(currentMonth, currentYear);
            updateSelectedChips(currentMonth, currentYear);
        });

        // Submit Single Update
        document.getElementById('submit_single').addEventListener('click', function() {
            if (!currentSelectedInput.value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Tanggal',
                    text: 'Silakan klik tanggal pada kalender terlebih dahulu untuk mengedit jadwal.',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }

            if (!validateSingleForm()) {
                return;
            }

            const payload = {
                date: currentSelectedInput.value,
                month: currentMonth + 1,
                year: currentYear,
                shift_id: parseInt(shiftSelect.value),
                work_type: workTypeSelect.value,
                office_id: parseInt(officeSelect.value),
                schedule_type: parseInt(changeTimeSelect.value),
                back_earlier: parseInt(backEarlierSelected.value)
            };

            const submitBtn = this;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...';

            fetch(`/api/schedule/update/single/${internId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify(payload)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(response.statusText || 'Gagal menyimpan perubahan');
                }
                return response.json();
            })
            .then(res => {
                // Update local memory data for instant live UI update
                const targetFullDate = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${currentSelectedInput.value.padStart(2, '0')}`;
                let existing = checkIsDataAvailable(targetFullDate);
                if (existing) {
                    existing.shift_id = payload.shift_id;
                    existing.work_type = payload.work_type;
                    existing.office_id = payload.office_id;
                    existing.isChangeSchedule = payload.schedule_type === 1;
                    existing.isBackFirst = payload.back_earlier === 1;
                } else {
                    scheduleData.push({
                        id: Date.now(),
                        date: targetFullDate,
                        shift_id: payload.shift_id,
                        work_type: payload.work_type,
                        office_id: payload.office_id,
                        isChangeSchedule: payload.schedule_type === 1,
                        isBackFirst: payload.back_earlier === 1
                    });
                }

                setMonthDate(currentMonth, currentYear);

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Disimpan!',
                    text: res.message || 'Jadwal shift berhasil diperbarui.',
                    timer: 2000,
                    showConfirmButton: false
                });
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: error.message,
                    confirmButtonColor: '#2563eb'
                });
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Jadwal Tanggal Ini';
            });
        });

        // Submit Multi Update
        document.getElementById("submit_multiple").addEventListener('click', function() {
            if (selectedDates.size === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum Ada Tanggal Dipilih',
                    text: 'Silakan pilih minimal 1 tanggal pada kalender terlebih dahulu untuk melakukan bulk update.',
                    confirmButtonColor: '#4f46e5'
                });
                return;
            }

            const datesArray = Array.from(selectedDates);
            const count = datesArray.length;
            const shiftName = shiftsMap[shiftMultiInput.value] || 'Shift Terpilih';

            Swal.fire({
                title: 'Konfirmasi Bulk Update',
                html: `Anda akan mengubah jadwal <strong>${count} tanggal terpilih</strong> menjadi <strong>${shiftName}</strong> (${workTypeMulti.value.toUpperCase()}). Lanjutkan?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Terapkan Sekarang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const payload = {
                        date: datesArray.join(", "),
                        month: currentMonth + 1,
                        year: currentYear,
                        shift_id: parseInt(shiftMultiInput.value),
                        work_type: workTypeMulti.value,
                        office_id: parseInt(officeMulti.value),
                        schedule_type: parseInt(changeTimeMulti.value)
                    };

                    const submitBtn = document.getElementById("submit_multiple");
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menerapkan...';

                    fetch(`/api/schedule/multi/update/${internId}`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(response.statusText || 'Gagal menerapkan update massal');
                        }
                        return response.json();
                    })
                    .then(res => {
                        // Live update local data
                        datesArray.forEach(d => {
                            const fullDate = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${d.padStart(2, '0')}`;
                            let existing = checkIsDataAvailable(fullDate);
                            if (existing) {
                                existing.shift_id = payload.shift_id;
                                existing.work_type = payload.work_type;
                                existing.office_id = payload.office_id;
                                existing.isChangeSchedule = payload.schedule_type === 1;
                            } else {
                                scheduleData.push({
                                    id: Date.now() + Math.random(),
                                    date: fullDate,
                                    shift_id: payload.shift_id,
                                    work_type: payload.work_type,
                                    office_id: payload.office_id,
                                    isChangeSchedule: payload.schedule_type === 1,
                                    isBackFirst: false
                                });
                            }
                        });

                        selectedDates.clear();
                        updateSelectedChips(currentMonth, currentYear);
                        setMonthDate(currentMonth, currentYear);
                        switchTab('single');

                        Swal.fire({
                            icon: 'success',
                            title: 'Bulk Update Berhasil!',
                            text: res.message || `${count} tanggal berhasil diperbarui.`,
                            timer: 2500,
                            showConfirmButton: false
                        });
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Bulk Update',
                            text: error.message,
                            confirmButtonColor: '#4f46e5'
                        });
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fa-solid fa-layer-group mr-1"></i> Terapkan Perubahan Massal';
                    });
                }
            });
        });

        function validateSingleForm() {
            if (!shiftSelect.value) {
                Swal.fire({ icon: 'warning', title: 'Shift Belum Dipilih', text: 'Silakan pilih shift kerja terlebih dahulu.' });
                return false;
            }
            if (!workTypeSelect.value) {
                Swal.fire({ icon: 'warning', title: 'Tipe Kerja Belum Dipilih', text: 'Silakan pilih tipe kerja (WFO/WFH) terlebih dahulu.' });
                return false;
            }
            if (!officeSelect.value) {
                Swal.fire({ icon: 'warning', title: 'Kantor Belum Dipilih', text: 'Silakan pilih kantor penempatan terlebih dahulu.' });
                return false;
            }
            return true;
        }

        function resetSingleForm() {
            shiftSelect.value = "";
            workTypeSelect.value = "";
            changeTimeSelect.value = "0";
            backEarlierSelected.value = "0";
            officeSelect.value = "";
        }

        function resetSelection() {
            selectedDates.clear();
            currentSelectedInput.value = "";
            dayView.value = "";
            singleDisplayDate.textContent = "Pilih tanggal di kalender";
            singleCurrentBadge.className = "shrink-0 px-2 py-0.5 sm:px-2.5 sm:py-1 text-[11px] sm:text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 border border-gray-200";
            singleCurrentBadge.textContent = "Belum dipilih";
            resetSingleForm();
            updateSelectedChips(currentMonth, currentYear);
            setMonthDate(currentMonth, currentYear);
            switchTab('single');
        }

        // Month Navigation Listeners
        document.getElementById("prev-month").addEventListener("click", () => {
            if (currentMonth === 0) {
                currentMonth = 11;
                currentYear -= 1;
            } else {
                currentMonth -= 1;
            }
            setMonthDate(currentMonth, currentYear);
            updateSelectedChips(currentMonth, currentYear);
        });

        document.getElementById("next-month").addEventListener("click", () => {
            if (currentMonth === 11) {
                currentMonth = 0;
                currentYear += 1;
            } else {
                currentMonth += 1;
            }
            setMonthDate(currentMonth, currentYear);
            updateSelectedChips(currentMonth, currentYear);
        });

        // Helper Date Functions
        function getMonthName(monthNumber) {
            const monthNames = [
                "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"
            ];
            return monthNames[monthNumber];
        }

        function getDayName(year, month, day) {
            const date = new Date(year, month, day);
            return date.toLocaleDateString('id-ID', { weekday: 'long' });
        }

        function getDayIndex(year, month, day) {
            return new Date(year, month, day).getDay();
        }

        function getDaysInMonth(year, month) {
            return new Date(year, month + 1, 0).getDate();
        }

        // Initial Load
        setMonthDate(currentMonth, currentYear);
        updateSelectedChips(currentMonth, currentYear);
    </script>
</body>

</html>