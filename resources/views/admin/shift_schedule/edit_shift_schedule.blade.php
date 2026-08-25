<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Schedule {{ $name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
            border-width: 10px 15px 10px 0;
            border-color: transparent black transparent transparent;
            cursor: pointer;
        }

        .triangle-right {
            border-width: 10px 0 10px 15px;
            border-color: transparent transparent transparent black;
            cursor: pointer;
        }
    </style>

</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <h1 class="absolute top-11 text-3xl font-bold text-gray-900">
        Edit Schedule {{ $name }}
    </h1>

    <div class="flex w-full max-w-screen-xl mt-10">

        <div class="w-2/4 p-4">
            <div class="flex items-center justify-center">
                <div class="flex items-center justify-center">
                    <button id="prev-month" class="triangle-left"></button>
                    <h2 id="current-month" class="text-xl font-bold mx-4 mb-4 inline-flex items-center">October</h2>
                    <button id="next-month" class="triangle-right"></button>
                </div>
            </div>

            <div class="grid grid-cols-7 gap-4" id="box-container">
                <!-- Loop untuk 30 kotak -->
                <template id="box-template">
                    <div
                        class="box bg-gray-500 h-16 flex items-center justify-center text-white font-bold rounded-lg cursor-pointer">
                    </div>

                </template>
            </div>
        </div>

        <div class="w-1/4 p-4">
            <h2 class="text-xl font-bold mb-4">Data Yang dipilih</h2>

            <input id="detail_schedule_id" type="number" hidden>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="day-selected" class="block text-sm font-medium text-gray-700">Hari</label>
                        <input type="text" id="day-selected" readonly disabled
                            class="mt-1 p-2 block w-full border rounded-md bg-gray-200 text-gray-900 shadow-none" />
                    </div>

                    <div>
                        <label for="date-selected" class="block text-sm font-medium text-gray-700">Tanggal</label>
                        <input type="text" id="date-selected" readonly disabled
                            class="mt-1 p-2 block w-full border rounded-md bg-gray-200 text-gray-900 shadow-none" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="shift" class="block text-sm font-medium text-gray-700">Shift</label>
                        <select id="shift"
                            class="mt-1 p-2 block w-full border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="" disabled selected>Pilih Shift</option>
                            @foreach ($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="work-type" class="block text-sm font-medium text-gray-700">Tipe Kerja</label>
                        <select id="work-type"
                            class="mt-1 p-2 block w-full border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="" disabled selected>Pilih Tipe Kerja</option>
                            @foreach ($workTypes as $type)
                                <option value="{{ $type }}">{{ Str::upper($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


                <div>
                    <label for="change_time" class="block text-sm font-medium text-gray-700">Tipe jadwal</label>
                    <select id="change_time"
                        class="mt-1 p-2 block w-full border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="" disabled selected>Pilih Tipe Jadwal</option>
                        <option value="0">Jadwal Biasa</option>
                        <option value="1">Ganti Jam</option>
                    </select>
                </div>
                <div>
                    <label for="back_earlier" class="block text-sm font-medium text-gray-700">Pulang Lebih Awal</label>
                    <select id="back_earlier"
                        class="mt-1 p-2 block w-full border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="" disabled selected>Pilih Tipe</option>
                        <option value="0">Tidak</option>
                        <option value="1">Ya</option>
                    </select>
                </div>

                <div class="mb-5">
                    <label for="office" class="block text-sm font-medium text-gray-700">Kantor</label>
                    <select id="office"
                        class="mt-1 p-2 block w-full border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="" disabled selected>Pilih Kantor</option>
                        @foreach ($offices as $office)
                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button id="submit_single" type="button"
                    class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600">Update</button>
            </div>
        </div>

        <!-- Form Input di Kanan -->
        <div class="w-1/4 p-4">

            <h2 class="text-xl font-bold mb-4">Data Jadwal Multi Update</h2>
            {{-- <form class="space-y-4"> --}}
            <div class="space-y-4">

                <div>
                    <label for="selected_dates" class="block text-sm font-medium text-gray-700">List Tanggal yang
                        dipilih</label>
                    <div id="selected_dates"
                        class="mt-1 p-2 block w-full border bg-gray-200 rounded-md overflow-x-auto whitespace-nowrap"
                        style="height: 2.5rem; line-height: 1.25rem; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                    </div>
                </div>

                <div>
                    <label for="shift_multi" class="block text-sm font-medium text-gray-700">Shift Multi</label>

                    <select id="shift_multi"
                        class="mt-1 p-2 block w-full border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        @foreach ($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                        @endforeach
                    </select>

                </div>
                <div>
                    <label for="work_type_multi" class="block text-sm font-medium text-gray-700">Tipe Kerja</label>
                    <select id="work_type_multi"
                        class="mt-1 p-2 block w-full border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        @foreach ($workTypes as $type)
                            <option value="{{ $type }}">{{ Str::upper($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="change_time_all" class="block text-sm font-medium text-gray-700">Tipe jadwal</label>
                    <select id="change_time_all"
                        class="mt-1 p-2 block w-full border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="0">Jadwal Biasa</option>
                        <option value="1">Ganti Jam</option>
                    </select>
                </div>
                <div class="mb-5">
                    <label for="office_multi" class="block text-sm font-medium text-gray-700">Kantor</label>
                    <select id="office_multi"
                        class="mt-1 p-2 block w-full border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        @foreach ($offices as $office)
                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex space-x-2">

                    <button id="submit_multiple" type="submit"
                        class="w-3/4 bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600">Bulk Update</button>
                    <button id="refresh-button" type="button"
                        class="w-1/4 bg-red-500 text-white py-2 rounded-md hover:bg-red-600">Refresh</button>
                </div>

            </div>
        </div>
    </div>

    <div id="success-message"
        class="hidden absolute top-10 right-10 mb-2 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded transition-opacity duration-5000 z-50"
        role="alert">
        <strong class="font-bold">Success! </strong>
        <span class="block sm:inline"><span id="notification-box"></span> </span>
      
    </div>

</body>

<script>
    const container = document.querySelector("#box-container");
    const template = document.querySelector("#box-template");
    const selectedDates = new Set();
    const internId = @json($intern_id);

    //single update input field
    const currentSelectedInput = document.getElementById("date-selected");
    const monthView = document.getElementById("current-month");
    const dayView = document.getElementById("day-selected");
    const shiftSelect = document.getElementById("shift");
    const workTypeSelect = document.getElementById("work-type");
    const changeTimeSelect = document.getElementById("change_time");
    const officeSelect = document.getElementById("office");
    const detailScheduleId = document.getElementById("detail_schedule_id")
    const changeTimeSelected = document.getElementById("change_time")
    const backEarlierSelected = document.getElementById("back_earlier")

    //multi update input field
    const selectedInput = document.getElementById("selected_dates");
    const shiftMultiInput = document.getElementById("shift_multi")
    const workTypeMulti = document.getElementById("work_type_multi")
    const officeMulti = document.getElementById("office_multi")
    const changeTimeMulti = document.getElementById("office_multi")

    let data = @json($schedule_data);
    let scheduleData = data != null ? data['schedule_data'] : [];
    let holidayData = @json($holiday_data);

    function checkIsDataAvailable(targetDate) {
        return scheduleData.find(schedule => schedule.date === targetDate) ?? null;
    }

    var currentMonth = new Date().getMonth();
    var currentYear = new Date().getFullYear();

    setMonthDate(currentMonth, currentYear);

    function showNotification(message, duration = 10000) {
        const notificationBox = document.getElementById('notification-box');
        notificationBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${message}`;
        notificationBox.classList.remove('hidden');
        notificationBox.classList.add('show');

        // Hide the notification box after the specified duration
        setTimeout(() => {
            notificationBox.classList.remove('show');
            notificationBox.classList.add('hidden');
        }, duration);
    }

    document.getElementById('submit_single').addEventListener('click', function() {
        if (!validateForm()) {
            return;
        }

        fetch("/api/schedule/update/single/" + internId, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json", // Set content type to JSON
                },
                body: JSON.stringify({ // Serialize the body as JSON
                    date: currentSelectedInput.value,
                    month: currentMonth + 1, //month in js start from 0
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
                return response.json(); // Handle the response data
            })
            .then(data => {
                if (data.message) {
                    showNotification(data.message); // Display the message from API
                }
                // location.reload(); // Optionally reload the page
            })
            .catch((error) => {
                showNotification("Something went wrong: " + error.message);
            });
    });

    function showNotification(message) {
        const notificationBox = document.getElementById('notification-box');
        const succesMessage = document.getElementById('success-message');

        notificationBox.classList.remove('hidden');
        succesMessage.classList.remove('hidden');
        notificationBox.innerHTML = `${message}`;

        setTimeout(function() {
            notificationBox.classList.add('hidden');
            succesMessage.classList.add('hidden');

        }, 5000); // 5000ms = 5 detik
    }

    document.getElementById("submit_multiple").addEventListener('click', function() {
        const data = JSON.stringify({
            date: selectedInput.textContent,
            month: currentMonth + 1, //month in js start from 0
            year: currentYear,
            shift_id: shiftMultiInput.value,
            work_type: workTypeMulti.value,
            office_id: officeMulti.value,
            schedule_type: changeTimeMulti.value
        })



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
                return response.json(); // Handle the response data
            })
            .then(data => {
                if (data.message) {
                    showNotification(data.message); // Display the message from API
                }
                // location.reload(); // Optionally reload the page
            })
            .catch((error) => {
                showNotification("Something went wrong: " + error.message);
            });

    });


    function validateForm() {
        const requiredFields = [shiftSelect, workTypeSelect, changeTimeSelect, officeSelect];

        for (const field of requiredFields) {
            if (!field.value) {
                alert("Semua pilihan harus diisi sebelum submit.");
                return false;
            }
        }
        return true;
    }

    function resetForm() {
        const requiredFields = [shiftSelect, workTypeSelect, changeTimeSelect, officeSelect];

        requiredFields.forEach(field => field.value = " ");
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
        monthView.textContent = "Bulan " + getMonthName(monthNumber) + " " + year;

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
                        box.classList.add("bg-yellow-500");
                    } else if (data) {
                        box.classList.add("bg-blue-500");
                    } else {
                        box.classList.add("bg-gray-500");
                    }
                } else {
                    selectedDates.add(date);
                    box.classList.remove("bg-gray-500", "bg-blue-500", "bg-yellow-500");
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
                selectedInput.textContent = Array.from(selectedDates).join(", ");
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

    // Get days in month
    function getDaysInMonth(year, month) {
        return new Date(year, month + 1, 0).getDate();
    }

    document.getElementById('refresh-button').addEventListener('click', function() {
        location.reload();
    });

    // Reset selection
    function resetSelection() {
        selectedDates.clear();
        document.querySelectorAll(".box").forEach(box => {
            box.classList.remove("bg-green-500");
            box.classList.add("bg-gray-500");
        });
        selectedInput.value = "";
    }

    // Event listener for refresh button
    document.getElementById("refresh-button").addEventListener("click", (event) => {
        event.preventDefault();
        resetSelection();
    });

    function isHoliday(date) {
        return holidayData.find(holiday => holiday.date === date) ?? null;
    }
</script>

</html>
