<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>Edit Schedule {{ $name }} | Absen Djuragan</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        .triangle-left,
        .triangle-right {
            width: 0;
            height: 0;
            border-style: solid;
            vertical-align: middle;
            display: inline-block;
        }

        .triangle-left {
            border-width: 8px 12px 8px 0;
            border-color: transparent #374151 transparent transparent;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .triangle-right {
            border-width: 8px 0 8px 12px;
            border-color: transparent transparent transparent #374151;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .triangle-left:hover {
            transform: scale(1.15);
            border-color: transparent #1e40af transparent transparent;
        }

        .triangle-right:hover {
            transform: scale(1.15);
            border-color: transparent transparent transparent #1e40af;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen text-gray-800 antialiased p-3 sm:p-6 lg:p-8">

    <div class="max-w-7xl mx-auto space-y-6">

        <!-- Header Bar -->
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4 min-w-0">
                <a href="{{ url()->previous() != url()->current() ? url()->previous() : route('admin.pengaturan.shift') }}"
                   class="p-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors shrink-0"
                   title="Kembali">
                    <i class="fa-solid fa-arrow-left text-sm sm:text-base"></i>
                </a>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">
                            Edit Jadwal Shift
                        </h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                            {{ $name }}
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Atur jadwal kerja harian pemagang secara satuan atau massal</p>
                </div>
            </div>

            <!-- Legend Status -->
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 font-medium">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Ada Jadwal
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-pink-50 text-pink-700 border border-pink-200 font-medium">
                    <span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span> Libur
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-red-50 text-red-700 border border-red-200 font-medium">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Minggu
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-green-50 text-green-700 border border-green-200 font-medium">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span> Terpilih
                </span>
            </div>
        </div>

        <!-- Main Responsive Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">

            <!-- Column 1: Kalender (6 col on Desktop) -->
            <div class="lg:col-span-6 bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200">
                <!-- Month Navigator -->
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
                    <button id="prev-month" type="button" class="p-2 rounded-lg hover:bg-gray-100 flex items-center justify-center" aria-label="Bulan Sebelumnya">
                        <span class="triangle-left"></span>
                    </button>
                    <h2 id="current-month" class="text-base sm:text-lg font-bold text-gray-900 text-center">
                        Memuat Bulan...
                    </h2>
                    <button id="next-month" type="button" class="p-2 rounded-lg hover:bg-gray-100 flex items-center justify-center" aria-label="Bulan Berikutnya">
                        <span class="triangle-right"></span>
                    </button>
                </div>

                <!-- Calendar Boxes -->
                <div class="grid grid-cols-7 gap-1.5 sm:gap-2.5" id="box-container">
                    <!-- Dynamic boxes generated via JS -->
                </div>

                <template id="box-template">
                    <div class="box bg-gray-500 h-10 sm:h-12 md:h-14 flex items-center justify-center text-white text-xs sm:text-sm font-bold rounded-xl cursor-pointer hover:opacity-90 transition-all select-none shadow-xs">
                    </div>
                </template>

                <p class="text-[11px] sm:text-xs text-gray-400 mt-4 text-center">
                    <i class="fa-solid fa-circle-info mr-1"></i> Klik tanggal untuk memilih satu/banyak tanggal sekaligus.
                </p>
            </div>

            <!-- Column 2: Data Single Update (3 col on Desktop) -->
            <div class="lg:col-span-3 bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-3 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>
                    <h2 class="text-sm sm:text-base font-bold text-gray-900">Update Single</h2>
                </div>

                <input id="detail_schedule_id" type="number" hidden>
                <div class="space-y-3 sm:space-y-4">
                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label for="day-selected" class="block text-xs font-semibold text-gray-600 mb-1">Hari</label>
                            <input type="text" id="day-selected" readonly disabled
                                class="p-2 block w-full border border-gray-200 rounded-xl bg-gray-100 text-gray-700 text-xs font-medium" />
                        </div>

                        <div>
                            <label for="date-selected" class="block text-xs font-semibold text-gray-600 mb-1">Tanggal</label>
                            <input type="text" id="date-selected" readonly disabled
                                class="p-2 block w-full border border-gray-200 rounded-xl bg-gray-100 text-gray-700 text-xs font-medium" />
                        </div>
                    </div>

                    <div>
                        <label for="shift" class="block text-xs font-semibold text-gray-600 mb-1">Shift</label>
                        <select id="shift"
                            class="p-2 block w-full border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs text-gray-800 bg-white">
                            <option value="" disabled selected>Pilih Shift</option>
                            @foreach ($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="work-type" class="block text-xs font-semibold text-gray-600 mb-1">Tipe Kerja</label>
                        <select id="work-type"
                            class="p-2 block w-full border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs text-gray-800 bg-white">
                            <option value="" disabled selected>Pilih Tipe Kerja</option>
                            @foreach ($workTypes as $type)
                            <option value="{{ $type }}">{{ Str::upper($type) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="change_time" class="block text-xs font-semibold text-gray-600 mb-1">Tipe Jadwal</label>
                        <select id="change_time"
                            class="p-2 block w-full border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs text-gray-800 bg-white">
                            <option value="" disabled selected>Pilih Tipe Jadwal</option>
                            <option value="0">Jadwal Biasa</option>
                            <option value="1">Ganti Jam</option>
                        </select>
                    </div>

                    <div>
                        <label for="back_earlier" class="block text-xs font-semibold text-gray-600 mb-1">Pulang Lebih Awal</label>
                        <select id="back_earlier"
                            class="p-2 block w-full border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs text-gray-800 bg-white">
                            <option value="" disabled selected>Pilih Opsi</option>
                            <option value="0">Tidak</option>
                            <option value="1">Ya</option>
                        </select>
                    </div>

                    <div>
                        <label for="office" class="block text-xs font-semibold text-gray-600 mb-1">Kantor</label>
                        <select id="office"
                            class="p-2 block w-full border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs text-gray-800 bg-white">
                            <option value="" disabled selected>Pilih Kantor</option>
                            @foreach ($offices as $office)
                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button id="submit_single" type="button"
                        class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition-colors shadow-xs">
                        Update Tanggal
                    </button>
                </div>
            </div>

            <!-- Column 3: Data Multi Update (3 col on Desktop) -->
            <div class="lg:col-span-3 bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-3 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <h2 class="text-sm sm:text-base font-bold text-gray-900">Bulk Multi Update</h2>
                </div>

                <div class="space-y-3 sm:space-y-4">
                    <div>
                        <label for="selected_dates" class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Terpilih</label>
                        <div id="selected_dates"
                            class="p-2 block w-full border border-gray-200 bg-gray-50 rounded-xl overflow-x-auto whitespace-nowrap text-xs text-gray-700 min-h-[38px] flex items-center font-mono">
                            Belum ada tanggal dipilih
                        </div>
                    </div>

                    <div>
                        <label for="shift_multi" class="block text-xs font-semibold text-gray-600 mb-1">Shift</label>
                        <select id="shift_multi"
                            class="p-2 block w-full border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs text-gray-800 bg-white">
                            @foreach ($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="work_type_multi" class="block text-xs font-semibold text-gray-600 mb-1">Tipe Kerja</label>
                        <select id="work_type_multi"
                            class="p-2 block w-full border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs text-gray-800 bg-white">
                            @foreach ($workTypes as $type)
                            <option value="{{ $type }}">{{ Str::upper($type) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="change_time_all" class="block text-xs font-semibold text-gray-600 mb-1">Tipe Jadwal</label>
                        <select id="change_time_all"
                            class="p-2 block w-full border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs text-gray-800 bg-white">
                            <option value="0">Jadwal Biasa</option>
                            <option value="1">Ganti Jam</option>
                        </select>
                    </div>

                    <div>
                        <label for="office_multi" class="block text-xs font-semibold text-gray-600 mb-1">Kantor</label>
                        <select id="office_multi"
                            class="p-2 block w-full border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs text-gray-800 bg-white">
                            @foreach ($offices as $office)
                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button id="submit_multiple" type="submit"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold py-2.5 px-3 rounded-xl text-xs transition-colors shadow-xs">
                            Bulk Update
                        </button>
                        <button id="refresh-button" type="button"
                            class="px-3.5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition-colors"
                            title="Reset Pilihan">
                            <i class="fa-solid fa-rotate-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="success-message"
        class="hidden fixed bottom-5 right-5 z-50 p-4 bg-gray-900 text-white rounded-2xl shadow-2xl border border-gray-700 flex items-center space-x-3 text-xs sm:text-sm max-w-sm"
        role="alert">
        <i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
        <div id="notification-box" class="flex-1 font-medium"></div>
    </div>

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

        // Single update input fields
        const currentSelectedInput = document.getElementById("date-selected");
        const monthView = document.getElementById("current-month");
        const dayView = document.getElementById("day-selected");
        const shiftSelect = document.getElementById("shift");
        const workTypeSelect = document.getElementById("work-type");
        const changeTimeSelect = document.getElementById("change_time");
        const officeSelect = document.getElementById("office");
        const detailScheduleId = document.getElementById("detail_schedule_id");
        const changeTimeSelected = document.getElementById("change_time");
        const backEarlierSelected = document.getElementById("back_earlier");

        // Multi update input fields
        const selectedInput = document.getElementById("selected_dates");
        const shiftMultiInput = document.getElementById("shift_multi");
        const workTypeMulti = document.getElementById("work_type_multi");
        const officeMulti = document.getElementById("office_multi");
        const changeTimeMulti = document.getElementById("change_time_all");

        let data = initData.scheduleData;
        let scheduleData = data != null ? data['schedule_data'] : [];
        let holidayData = initData.holidayData || [];

        function checkIsDataAvailable(targetDate) {
            return scheduleData.find(schedule => schedule.date === targetDate) ?? null;
        }

        var currentMonth = new Date().getMonth();
        var currentYear = new Date().getFullYear();

        setMonthDate(currentMonth, currentYear);

        function showNotification(message, duration = 5000) {
            const notificationBox = document.getElementById('notification-box');
            const successMessage = document.getElementById('success-message');

            notificationBox.innerHTML = message;
            successMessage.classList.remove('hidden');

            setTimeout(() => {
                successMessage.classList.add('hidden');
            }, duration);
        }

        document.getElementById('submit_single').addEventListener('click', function() {
            if (!validateForm()) {
                return;
            }

            fetch("/api/schedule/update/single/" + internId, {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        date: currentSelectedInput.value,
                        month: currentMonth + 1,
                        year: currentYear,
                        shift_id: shiftSelect.value,
                        work_type: workTypeSelect.value,
                        office_id: officeSelect.value,
                        schedule_type: changeTimeSelected.value,
                        back_earlier: backEarlierSelected.value
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        showNotification("Network response was not ok: " + response.statusText);
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.message) {
                        showNotification(data.message);
                    }
                })
                .catch((error) => {
                    showNotification("Something went wrong: " + error.message);
                });
        });

        document.getElementById("submit_multiple").addEventListener('click', function() {
            if (selectedDates.size === 0) {
                alert("Silakan pilih minimal 1 tanggal pada kalender terlebih dahulu.");
                return;
            }

            const data = JSON.stringify({
                date: Array.from(selectedDates).join(", "),
                month: currentMonth + 1,
                year: currentYear,
                shift_id: shiftMultiInput.value,
                work_type: workTypeMulti.value,
                office_id: officeMulti.value,
                schedule_type: changeTimeMulti.value
            });

            fetch("/api/schedule/multi/update/" + internId, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: data
                })
                .then(response => {
                    if (!response.ok) {
                        showNotification("Network response was not ok: " + response.statusText);
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.message) {
                        showNotification(data.message);
                    }
                })
                .catch((error) => {
                    showNotification("Something went wrong: " + error.message);
                });
        });

        function validateForm() {
            const requiredFields = [shiftSelect, workTypeSelect, changeTimeSelect, officeSelect];

            for (const field of requiredFields) {
                if (!field.value || field.value.trim() === "") {
                    alert("Semua pilihan harus diisi sebelum submit.");
                    return false;
                }
            }
            return true;
        }

        function resetForm() {
            const requiredFields = [shiftSelect, workTypeSelect, changeTimeSelect, officeSelect];
            requiredFields.forEach(field => field.value = "");
        }

        document.getElementById("prev-month").addEventListener("click", () => {
            if (currentMonth === 0) {
                currentMonth = 11;
                currentYear -= 1;
            } else {
                currentMonth -= 1;
            }
            setMonthDate(currentMonth, currentYear);
        });

        document.getElementById("next-month").addEventListener("click", () => {
            if (currentMonth === 11) {
                currentMonth = 0;
                currentYear += 1;
            } else {
                currentMonth += 1;
            }
            setMonthDate(currentMonth, currentYear);
        });

        function setMonthDate(monthNumber, year) {
            container.replaceChildren();

            const daysInMonth = getDaysInMonth(year, monthNumber);
            monthView.textContent = getMonthName(monthNumber) + " " + year;

            for (let i = 1; i <= daysInMonth; i++) {
                const boxClone = template.content.cloneNode(true);
                const box = boxClone.querySelector(".box");
                box.textContent = i;
                const currentDate =
                    `${year}-${(monthNumber + 1).toString().padStart(2, '0')}-${i.toString().padStart(2, '0')}`;

                let data = checkIsDataAvailable(currentDate);
                let holiday = isHoliday(currentDate);
                let isSunday = getDayIndex(year, monthNumber, i) === 0;

                if (holiday) {
                    box.classList.remove("bg-gray-500");
                    box.classList.add("bg-pink-500");
                    box.title = holiday.name;
                } else if (data) {
                    box.classList.remove("bg-gray-500");
                    box.classList.add("bg-blue-500");
                } else if (isSunday) {
                    box.classList.remove("bg-gray-500");
                    box.classList.add("bg-red-500");
                } else {
                    box.classList.add("bg-gray-500");
                }

                box.addEventListener("click", () => {
                    const date = i.toString();

                    if (selectedDates.has(date)) {
                        selectedDates.delete(date);
                        box.classList.remove("bg-green-500");
                        if (holiday) {
                            box.classList.add("bg-pink-500");
                        } else if (data) {
                            box.classList.add("bg-blue-500");
                        } else if (isSunday) {
                            box.classList.add("bg-red-500");
                        } else {
                            box.classList.add("bg-gray-500");
                        }
                    } else {
                        selectedDates.add(date);
                        box.classList.remove("bg-gray-500", "bg-blue-500", "bg-pink-500", "bg-red-500");
                        box.classList.add("bg-green-500");
                    }

                    currentSelectedInput.value = date;
                    dayView.value = getDayName(year, monthNumber, date);
                    if (data) {
                        detailScheduleId.value = data.id;
                        shiftSelect.value = data.shift_id;
                        workTypeSelect.value = data.work_type;
                        officeSelect.value = data.office_id;
                        changeTimeSelected.value = data.isChangeSchedule;
                        backEarlierSelected.value = data.isBackFirst;
                    } else {
                        resetForm();
                    }
                    selectedInput.textContent = selectedDates.size > 0 ? Array.from(selectedDates).join(", ") : "Belum ada tanggal dipilih";
                });

                container.appendChild(boxClone);
            }
        }

        function getMonthName(monthNumber) {
            const monthNames = [
                "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"
            ];
            return monthNames[monthNumber];
        }

        function getDayName(year, month, day) {
            const date = new Date(year, month, day);
            const options = {
                weekday: 'long'
            };
            return date.toLocaleDateString('id-ID', options);
        }

        function getDayIndex(year, month, day) {
            const date = new Date(year, month, day);
            return date.getDay();
        }

        function getDaysInMonth(year, month) {
            return new Date(year, month + 1, 0).getDate();
        }

        function resetSelection() {
            selectedDates.clear();
            setMonthDate(currentMonth, currentYear);
            selectedInput.textContent = "Belum ada tanggal dipilih";
            resetForm();
            currentSelectedInput.value = "";
            dayView.value = "";
        }

        document.getElementById("refresh-button").addEventListener("click", (event) => {
            event.preventDefault();
            resetSelection();
        });

        function isHoliday(date) {
            return holidayData.find(holiday => holiday.date === date) ?? null;
        }
    </script>
</body>

</html>