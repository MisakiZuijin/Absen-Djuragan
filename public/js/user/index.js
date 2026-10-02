// #FITUR DASHBOARD USER

const initialActivityElem = document.getElementById("activity");
if (initialActivityElem) {
    initialActivityElem.addEventListener("input", function () {
        this.value = this.value.replace(/\r/g, "");
    });
}

// ==============================================
// GLOBAL STATE & LOCATION HELPER
// ==============================================
var is_gps_available = false;
var choosen_stage = 0;
var GVAttendanceId = 0;
var GVAdjustableId = 0;

function getLocationWithRetry(retries) {
    return new Promise(function (resolve, reject) {
        if (!navigator.geolocation) {
            reject(
                new Error("Geolocation tidak didukung oleh browser ini."),
            );
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;
                resolve({ latitude: latitude, longitude: longitude });
            },
            function (error) {
                if (error.code === error.TIMEOUT && retries > 0) {
                    getLocationWithRetry(retries - 1)
                        .then(resolve)
                        .catch(reject);
                } else {
                    reject(error);
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0,
            },
        );
    });
}
window.getLocationWithRetry = getLocationWithRetry;


// ==============================================
// NOTIFIKASI SUCCESS / ERROR + LIVEWIRE LISTENER
// ==============================================
window.addEventListener("DOMContentLoaded", () => {
    const successBox = document.getElementById("success-notif");
    const errorBox = document.getElementById("error-notif");

    // Check if there's a success message
    const successMessage = sessionStorage.getItem("successMessage");
    if (successMessage) {
        document.getElementById("success-message").textContent = successMessage;
        successBox.classList.remove("hidden");

        // Hapus notifikasi setelah 5 detik
        setTimeout(() => {
            successBox.classList.add("hidden");
            sessionStorage.removeItem("successMessage"); // Clear after displaying
        }, 5000);
    }

    // Check if there's an error message
    const errorMessage = sessionStorage.getItem("errorMessage");
    if (errorMessage) {
        document.getElementById("error-message").textContent = errorMessage;
        errorBox.classList.remove("hidden");

        // Hapus notifikasi setelah 5 detik
        setTimeout(() => {
            errorBox.classList.add("hidden");
            sessionStorage.removeItem("errorMessage");
        }, 5000);
    }

    Livewire.on("post-created", ({ status, message }) => {
        const modal = document.getElementById("actionModal");
        if (modal) modal.classList.add("hidden");
        if (status) {
            document.getElementById("success-message").textContent = message;
            successBox.classList.remove("hidden");

            setTimeout(() => {
                successBox.classList.add("hidden");
                sessionStorage.removeItem("successMessage");
            }, 5000);
        } else {
            document.getElementById("error-message").textContent = message;
            errorBox.classList.remove("hidden");

            setTimeout(() => {
                errorBox.classList.add("hidden");
                sessionStorage.removeItem("errorMessage");
            }, 5000);
        }
    });

    Livewire.on("ganti-jam-started", ({ message }) => {
        const modal = document.getElementById("actionModal");
        if (modal) modal.classList.add("hidden");
    });

    Livewire.on("ganti-jam-ended", ({ message }) => {
        const modal = document.getElementById("actionModal");
        if (modal) modal.classList.add("hidden");
    });
});

// ==============================================
// GEO NOTIFICATION + SUBMIT HANDLER
// ==============================================
document.addEventListener("DOMContentLoaded", function () {
    const geoNotification = document.getElementById("geoNotification");
    const allowButton = document.getElementById("allowButton");
    const denyButton = document.getElementById("denyButton");

    if (allowButton) {
        allowButton.addEventListener("click", function () {
            geoNotification.classList.add("hidden");
            localStorage.setItem("locationPermission", "granted");
        });
    }

    if (denyButton) {
        denyButton.addEventListener("click", function () {
            geoNotification.classList.add("hidden");
            localStorage.setItem("locationPermission", "denied");
        });
    }

    // Trigger permission prompt di awal
    if (navigator.permissions) {
        navigator.permissions
            .query({ name: "geolocation" })
            .then(function (permissionStatus) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {},
                    function (error) {},
                );
            });
    }

    const submitButton = document.getElementById("save-attd");
    const circularLoading = document.getElementById("loading-spinner");
    const attdDescription = document.getElementById("modalTextarea");

    var todayRow = document.getElementById("today-row");
    if (todayRow) {
        todayRow.scrollIntoView({
            behavior: "smooth",
            block: "center",
        });
    }

    if (submitButton && circularLoading) {
        submitButton.addEventListener("click", function () {
            const text = attdDescription ? attdDescription.value : "";
            const isChangeTime = (choosen_stage === 5 || choosen_stage === 6);

            submitButton.classList.add("hidden");
            circularLoading.classList.remove("hidden");

            const stageNumberData = [3, 4, 5, 6];

            // Stage 3-6 (Masuk, Pulang, Ganti Jam)
            if (stageNumberData.includes(choosen_stage)) {
                if (is_gps_available) {
                    getLocationWithRetry(5)
                        .then(function ({ latitude, longitude }) {
                            Livewire.dispatch("actionAttd", {
                                stage: choosen_stage,
                                text: text,
                                latitude: latitude,
                                longitude: longitude,
                                attendanceId: GVAttendanceId,
                                adjustableId: GVAdjustableId,
                            });

                            if (attdDescription) attdDescription.value = "";
                        })
                        .catch(function (error) {
                            showErrorMessage(
                                "Gagal mendapatkan lokasi: " +
                                    (error.message || error),
                            );
                        })
                        .finally(function () {
                            circularLoading.classList.add("hidden");
                            submitButton.classList.remove("hidden");
                        });
                } else {
                    // WFH / GPS Non-Aktif: Langsung dispatch tanpa meminta akses GPS perangkat
                    Livewire.dispatch("actionAttd", {
                        stage: choosen_stage,
                        text: text,
                        latitude: null,
                        longitude: null,
                        attendanceId: GVAttendanceId,
                        adjustableId: GVAdjustableId,
                    });

                    if (attdDescription) attdDescription.value = "";
                    circularLoading.classList.add("hidden");
                    submitButton.classList.remove("hidden");
                }
            } else {
                // Stage non-GPS (mis. pengajuan izin) — dispatch tanpa koordinat
                Livewire.dispatch("actionAttd", {
                    stage: choosen_stage,
                    text: text,
                    attendanceId: GVAttendanceId,
                    adjustableId: GVAdjustableId,
                });

                if (attdDescription) attdDescription.value = "";
                circularLoading.classList.add("hidden");
                submitButton.classList.remove("hidden");
            }
        });
    }

    function getCookie(name) {
        let match = document.cookie.match(
            new RegExp("(^| )" + name + "=([^;]+)"),
        );
        if (match) return match[2];
    }
});

// Helper untuk menampilkan notifikasi error
function showErrorMessage(message) {
    const msgEl = document.getElementById("error-message");
    const boxEl = document.getElementById("error-notif");
    if (msgEl) msgEl.textContent = message;
    if (boxEl) {
        boxEl.classList.remove("hidden");
        setTimeout(() => {
            boxEl.classList.add("hidden");
        }, 5000);
    }
}

// Helper untuk menampilkan notifikasi sukses
function showSuccessMessage(message) {
    const msgEl = document.getElementById("success-message");
    const boxEl = document.getElementById("success-notif");
    if (msgEl) msgEl.textContent = message;
    if (boxEl) {
        boxEl.classList.remove("hidden");
        setTimeout(() => {
            boxEl.classList.add("hidden");
        }, 5000);
    }
}

// ==============================================
// ACTION MODAL (PRESENSI)
// ==============================================
function showModal(
    is_gps_permited,
    userId,
    stage,
    isAdjustable = false,
    attendanceId,
    adjustableId,
) {
    is_gps_available = !!is_gps_permited;
    choosen_stage = stage;
    GVAttendanceId = attendanceId || 0;
    GVAdjustableId = adjustableId || 0;

    const modal = document.getElementById("actionModal");
    const modalTitle = document.getElementById("modalTitle");
    const modalTextarea = document.getElementById("modalTextarea");

    const isChangeTime = (stage === 5 || stage === 6 || isAdjustable);

    if (modalTitle) {
        if (isChangeTime) {
            modalTitle.innerHTML = 'Keterangan Ganti Jam <span class="text-slate-400 font-normal text-xs">(opsional)</span>';
        } else {
            modalTitle.textContent = "Keterangan Presensi (opsional)";
        }
    }

    if (modalTextarea) {
        modalTextarea.value = "";
        if (isChangeTime) {
            modalTextarea.placeholder = "Tuliskan catatan / keterangan ganti jam (opsional)...";
            modalTextarea.classList.add("border-amber-300", "focus:ring-amber-500");
            modalTextarea.classList.remove("focus:ring-blue-500");
        } else {
            modalTextarea.placeholder = "Tuliskan keterangan (opsional)";
            modalTextarea.classList.remove("border-amber-300", "focus:ring-amber-500");
            modalTextarea.classList.add("focus:ring-blue-500");
        }
    }

    if (modal) {
        modal.classList.remove("hidden");
    }
}

function closeActionModal() {
    const modal = document.getElementById("actionModal");
    if (modal) {
        modal.classList.add("hidden");
    }
}

// Menutup modal sekaligus
function closeModal() {
    closeActionModal();
    closeHolidayModal();
    if (typeof closeChangeTimeModal === "function") {
        closeChangeTimeModal();
    }
}

function openChangeTimeModal() {
    const modal = document.getElementById("changeTimeModal");
    if (modal) {
        modal.classList.remove("hidden");
        modal.classList.add("flex");
    }
}

function closeChangeTimeModal() {
    const modal = document.getElementById("changeTimeModal");
    if (modal) {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    }
    const dropMenu = document.getElementById("shift-dropdown-menu");
    if (dropMenu) dropMenu.classList.add("hidden");
}

function selectChangeTimeDebtItem(hours, title, note, cardEl, scheduleId, scheduleDate) {
    const hoursInput = document.getElementById("change_time_hours");
    const labelSpan = document.getElementById("selected-duration-label");
    const notesInput = document.getElementById("change_time_notes");
    const targetScheduleInput = document.getElementById("change_time_target_schedule_id");
    const targetDateInput = document.getElementById("change_time_target_date");

    if (hoursInput) hoursInput.value = hours;
    if (labelSpan) labelSpan.textContent = `Dipilih: ${hours} Jam`;
    if (targetScheduleInput) targetScheduleInput.value = scheduleId || "";
    if (targetDateInput) targetDateInput.value = scheduleDate || "";

    // Reset all cards and chips
    document.querySelectorAll(".debt-option-card").forEach(function (el) {
        el.classList.remove("border-amber-500", "bg-amber-50", "ring-1", "ring-amber-500", "border-orange-500", "bg-orange-50/70", "ring-orange-500");
        const radio = el.querySelector("input[type=radio]");
        if (radio) radio.checked = false;
    });

    document.querySelectorAll(".debt-chip").forEach(function (el) {
        el.classList.remove("bg-amber-100", "border-amber-500", "text-amber-900", "ring-1", "ring-amber-500", "bg-orange-100", "border-orange-500", "text-orange-900", "ring-orange-500");
        el.classList.add("bg-slate-50", "border-slate-200", "text-slate-800");
    });

    // Highlight selected card
    if (cardEl) {
        if (cardEl.classList.contains("debt-option-card")) {
            cardEl.classList.add("border-amber-500", "bg-amber-50", "ring-1", "ring-amber-500");
            const radio = cardEl.querySelector("input[type=radio]");
            if (radio) radio.checked = true;
        } else if (cardEl.classList.contains("debt-chip")) {
            cardEl.classList.remove("bg-slate-50", "border-slate-200", "text-slate-800");
            cardEl.classList.add("bg-amber-100", "border-amber-500", "text-amber-900", "ring-1", "ring-amber-500");
        }
    }

    // NotesInput dibiarkan opsional dan manual oleh pemagang
}

function setChangeTimeSubmitBtnState(enabled, reason = "") {
    const btn = document.getElementById("btn-submit-change-time");
    if (!btn) return;
    if (enabled) {
        btn.disabled = false;
        btn.classList.remove("opacity-50", "cursor-not-allowed", "pointer-events-none");
        btn.classList.add("hover:bg-amber-700", "cursor-pointer");
        btn.removeAttribute("title");
    } else {
        btn.disabled = true;
        btn.classList.add("opacity-50", "cursor-not-allowed", "pointer-events-none");
        btn.classList.remove("hover:bg-amber-700", "cursor-pointer");
        if (reason) {
            btn.setAttribute("title", reason);
        } else {
            btn.setAttribute("title", "Pilihan tidak valid");
        }
    }
}

function switchDebtTab(tab) {
    const modeInput = document.getElementById("change_time_mode");
    if (modeInput) modeInput.value = tab;

    const btnNormal = document.getElementById("tab-btn-normal");
    const btnSmall = document.getElementById("tab-btn-small");
    const contentNormal = document.getElementById("tab-content-normal");
    const contentSmall = document.getElementById("tab-content-small");

    if (tab === "normal") {
        btnNormal?.classList.add("bg-white", "text-slate-800", "shadow-2xs");
        btnNormal?.classList.remove("text-slate-500");
        btnSmall?.classList.remove("bg-white", "text-slate-800", "shadow-2xs");
        btnSmall?.classList.add("text-slate-500");
        contentNormal?.classList.remove("hidden");
        contentSmall?.classList.add("hidden");

        const checkedRadio = document.querySelector('input[name="selected_normal_debt"]:checked');
        if (checkedRadio) {
            const card = checkedRadio.closest(".normal-debt-card");
            if (card) card.click();
        } else {
            const firstCard = document.querySelector(".normal-debt-card");
            if (firstCard) firstCard.click();
            else setChangeTimeSubmitBtnState(false, "Tidak ada data hutang yang tersedia.");
        }
    } else {
        btnSmall?.classList.add("bg-white", "text-slate-800", "shadow-2xs");
        btnSmall?.classList.remove("text-slate-500");
        btnNormal?.classList.remove("bg-white", "text-slate-800", "shadow-2xs");
        btnNormal?.classList.add("text-slate-500");
        contentSmall?.classList.remove("hidden");
        contentNormal?.classList.add("hidden");

        recalculateSmallDebtsTotal();
    }
}

function selectNormalDebtItem(scheduleIdOrEl, debtMinutes, hours, title, cardEl) {
    let scheduleId, dMinutes, dHours, dTitle, card;
    if (typeof scheduleIdOrEl === 'object' && scheduleIdOrEl !== null && scheduleIdOrEl.dataset) {
        scheduleId = scheduleIdOrEl.dataset.scheduleId;
        dMinutes = parseInt(scheduleIdOrEl.dataset.debtMinutes, 10);
        dHours = parseFloat(scheduleIdOrEl.dataset.debtHours);
        dTitle = scheduleIdOrEl.dataset.title;
        card = scheduleIdOrEl;
    } else {
        scheduleId = scheduleIdOrEl;
        dMinutes = debtMinutes;
        dHours = hours;
        dTitle = title;
        card = cardEl;
    }

    const hoursInput = document.getElementById("change_time_hours");
    const scheduleInput = document.getElementById("change_time_target_schedule_id");
    const durationLabel = document.getElementById("selected-duration-label");
    const notesInput = document.getElementById("change_time_notes");

    const h = Math.floor(dMinutes / 60);
    const m = dMinutes % 60;
    const formatted = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;

    if (hoursInput) hoursInput.value = dHours;
    if (scheduleInput) scheduleInput.value = scheduleId;
    if (durationLabel) {
        durationLabel.className = "text-[11px] font-bold text-amber-800 bg-amber-100 px-2.5 py-0.5 rounded-md border border-amber-300 font-mono";
        durationLabel.textContent = `Dipilih: ${formatted}`;
    }

    document.querySelectorAll(".normal-debt-card").forEach(c => {
        c.classList.remove("border-amber-500", "bg-amber-50/70", "ring-1", "ring-amber-500");
        c.classList.add("border-slate-200", "bg-white");
        const radio = c.querySelector('input[type="radio"]');
        if (radio) radio.checked = false;
    });

    if (card) {
        card.classList.remove("border-slate-200", "bg-white");
        card.classList.add("border-amber-500", "bg-amber-50/70", "ring-1", "ring-amber-500");
        const radio = card.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    }

    if (notesInput && (!notesInput.value || notesInput.value.startsWith("Ganti jam"))) {
        notesInput.value = `Ganti jam untuk ${dTitle}`;
    }

    if (scheduleId) {
        setChangeTimeSubmitBtnState(true);
    } else {
        setChangeTimeSubmitBtnState(false, "Harap pilih jadwal hutang.");
    }
}

function recalculateSmallDebtsTotal() {
    const checkboxes = document.querySelectorAll('input[name="selected_small_debts[]"]:checked');
    let totalMinutes = 0;
    const selectedIds = [];

    checkboxes.forEach(cb => {
        totalMinutes += parseInt(cb.getAttribute("data-minutes") || "0", 10);
        selectedIds.push(cb.value);
    });

    const hours = Math.floor(totalMinutes / 60);
    const mins = totalMinutes % 60;
    const hoursDecimal = (totalMinutes / 60).toFixed(1);
    const formatted = `${String(hours).padStart(2, '0')}:${String(mins).padStart(2, '0')}`;

    const hoursInput = document.getElementById("change_time_hours");
    const scheduleInput = document.getElementById("change_time_target_schedule_id");
    const durationLabel = document.getElementById("selected-duration-label");
    const sumLabel = document.getElementById("small-debt-sum-label");
    const notesInput = document.getElementById("change_time_notes");

    if (hoursInput) hoursInput.value = hoursDecimal;
    if (scheduleInput) scheduleInput.value = selectedIds.join(",");

    if (selectedIds.length === 0) {
        if (durationLabel) {
            durationLabel.className = "text-[11px] font-bold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-md border border-slate-300 font-mono";
            durationLabel.textContent = `Dipilih: 00:00 (Maks 07:00)`;
        }
        if (sumLabel) {
            sumLabel.className = "font-bold text-slate-600 font-mono";
            sumLabel.textContent = `Total: 00:00`;
        }
        setChangeTimeSubmitBtnState(false, "Pilih minimal 1 jadwal hutang untuk diganti.");
    } else if (totalMinutes > 420) {
        if (durationLabel) {
            durationLabel.className = "text-[11px] font-bold text-red-800 bg-red-100 px-2.5 py-0.5 rounded-md border border-red-300 font-mono animate-pulse";
            durationLabel.textContent = `Melebihi Batas: ${formatted} (Maks 07:00)`;
        }
        if (sumLabel) {
            sumLabel.className = "font-bold text-red-700 font-mono";
            sumLabel.textContent = `Total: ${formatted} (Melebihi Batas 07:00!)`;
        }
        setChangeTimeSubmitBtnState(false, "Total durasi melebihi batas maksimal 7 jam (07:00). Kurangi pilihan jadwal!");
    } else {
        if (durationLabel) {
            durationLabel.className = "text-[11px] font-bold text-blue-800 bg-blue-100 px-2.5 py-0.5 rounded-md border border-blue-300 font-mono";
            durationLabel.textContent = `Dipilih: ${formatted}`;
        }
        if (sumLabel) {
            sumLabel.className = "font-bold text-blue-800 font-mono";
            sumLabel.textContent = `Total: ${formatted} (Maks 07:00)`;
        }
        setChangeTimeSubmitBtnState(true);
    }

    // NotesInput dibiarkan manual oleh pemagang
}

function setChangeTimeHours(h) {
    const input = document.getElementById("change_time_hours");
    if (input) input.value = h;
}

function toggleShiftDropdown() {
    const menu = document.getElementById("shift-dropdown-menu");
    if (!menu) return;
    menu.classList.toggle("hidden");
    if (!menu.classList.contains("hidden")) {
        const searchInput = document.getElementById("shift-search-input");
        if (searchInput) {
            setTimeout(() => searchInput.focus(), 50);
        }
    }
}

function filterShiftList() {
    const searchInput = document.getElementById("shift-search-input");
    const query = searchInput ? searchInput.value.toLowerCase() : "";
    const items = document.querySelectorAll(".shift-option-item");
    items.forEach(function (item) {
        const name = item.getAttribute("data-name") || "";
        if (name.includes(query)) {
            item.style.display = "flex";
        } else {
            item.style.display = "none";
        }
    });
}

function selectShiftOption(idOrEl, label) {
    let id, shiftLabel;
    if (typeof idOrEl === 'object' && idOrEl !== null && idOrEl.dataset) {
        id = idOrEl.dataset.id;
        shiftLabel = idOrEl.dataset.label;
    } else {
        id = idOrEl;
        shiftLabel = label;
    }

    const idInput = document.getElementById("selected_change_time_shift_id");
    const labelSpan = document.getElementById("shift-dropdown-label");
    const menu = document.getElementById("shift-dropdown-menu");

    if (idInput) idInput.value = id;
    if (labelSpan) labelSpan.textContent = shiftLabel;
    if (menu) menu.classList.add("hidden");
}

function toggleOfficeDropdown() {
    const menu = document.getElementById("office-dropdown-menu");
    if (!menu) return;
    menu.classList.toggle("hidden");
    if (!menu.classList.contains("hidden")) {
        const searchInput = document.getElementById("office-search-input");
        if (searchInput) {
            setTimeout(() => searchInput.focus(), 50);
        }
    }
}

function filterOfficeList() {
    const searchInput = document.getElementById("office-search-input");
    const query = searchInput ? searchInput.value.toLowerCase() : "";
    const items = document.querySelectorAll(".office-option-item");
    items.forEach(function (item) {
        const name = item.getAttribute("data-name") || "";
        if (name.includes(query)) {
            item.style.display = "flex";
        } else {
            item.style.display = "none";
        }
    });
}

function selectOfficeOption(idOrEl, label) {
    let id, officeLabel;
    if (typeof idOrEl === 'object' && idOrEl !== null && idOrEl.dataset) {
        id = idOrEl.dataset.id;
        officeLabel = idOrEl.dataset.label;
    } else {
        id = idOrEl;
        officeLabel = label;
    }

    const idInput = document.getElementById("selected_change_time_office_id");
    const labelSpan = document.getElementById("office-dropdown-label");
    const menu = document.getElementById("office-dropdown-menu");

    if (idInput) idInput.value = id;
    if (labelSpan) labelSpan.textContent = officeLabel;
    if (menu) menu.classList.add("hidden");
}

// Close dropdowns when clicking outside
document.addEventListener("click", function (event) {
    const menu = document.getElementById("shift-dropdown-menu");
    const btn = document.getElementById("shift-dropdown-btn");
    if (menu && !menu.classList.contains("hidden")) {
        if (!menu.contains(event.target) && !btn?.contains(event.target)) {
            menu.classList.add("hidden");
        }
    }

    const officeMenu = document.getElementById("office-dropdown-menu");
    const officeBtn = document.getElementById("office-dropdown-btn");
    if (officeMenu && !officeMenu.classList.contains("hidden")) {
        if (!officeMenu.contains(event.target) && !officeBtn?.contains(event.target)) {
            officeMenu.classList.add("hidden");
        }
    }
});

function submitChangeTimeForm() {
    const form = document.getElementById("formDaftarGantiJam");
    if (!form) return;

    const dateInput = document.getElementById("reg_requested_date");
    if (dateInput && dateInput.min && dateInput.value < dateInput.min) {
        const msg = "Tanggal rencana ganti jam tidak dapat memilih hari ini atau tanggal lampau.";
        if (typeof showErrorMessage === "function") {
            showErrorMessage(msg);
        } else {
            alert(msg);
        }
        dateInput.focus();
        return;
    }

    const notesInput = document.getElementById("change_time_notes");
    if (!notesInput || !notesInput.value.trim()) {
        const msg = "Harap isi catatan / keterangan rencana ganti jam Anda.";
        if (typeof showErrorMessage === "function") {
            showErrorMessage(msg);
        } else {
            alert(msg);
        }
        if (notesInput) notesInput.focus();
        return;
    }

    const btn = document.getElementById("btn-submit-change-time");
    const spinner = document.getElementById("spinner-change-time");
    if (btn) btn.classList.add("hidden");
    if (spinner) spinner.classList.remove("hidden");

    form.submit();
}

// ==============================================
// GEOLOCATION UTILITIES
// ==============================================
function getLocation() {
    return new Promise(function (resolve, reject) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    resolve({
                        latitude,
                        longitude,
                    });
                },
                function (error) {
                    reject(error);
                },
                {
                    timeout: 5000,
                },
            );
        } else {
            reject(new Error("Geolocation is not supported by this browser."));
        }
    });
}

function setCoordinates(coords) {
    document.getElementById("lat").value = coords.latitude;
    document.getElementById("long").value = coords.longitude;
}

function showGeoNotification() {
    const geoNotification = document.getElementById("geoNotification");
    geoNotification.classList.remove("hidden");
}

function hideGeoNotification() {
    const geoNotification = document.getElementById("geoNotification");
    geoNotification.classList.add("hidden");
}

// ==============================================
// LOGBOOK MODAL
// ==============================================
function openLogbookModal() {
    const popup = document.getElementById("popup-form");
    if (popup) {
        popup.classList.remove("hidden");
        popup.classList.add("flex");
    }
}

function closeLogbookModal() {
    const popup = document.getElementById("popup-form");
    if (popup) {
        popup.classList.add("hidden");
        popup.classList.remove("flex");
    }
}

function togglePopup() {
    const popup = document.getElementById("popup-form");
    if (!popup) return;
    if (popup.classList.contains("flex")) {
        closeLogbookModal();
    } else {
        openLogbookModal();
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const textarea = document.getElementById("activity");
    const button = document.getElementById("submit-button");

    function updateButtonColor() {
        if (!textarea || !button) return;
        if (textarea.value.trim() === "") {
            button.disabled = true;
        } else {
            button.disabled = false;
        }
    }

    if (textarea && button) {
        updateButtonColor();
        textarea.addEventListener("input", updateButtonColor);
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("popup-form");
    const closeModalButton = document.getElementById("closeModal");

    if (closeModalButton) {
        closeModalButton.addEventListener("click", closeLogbookModal);
    }

    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            closeLogbookModal();
        }
    });
});

// ==============================================
// MODAL IZIN (SAKIT / KEPERLUAN)
// ==============================================
function switchPermitType(type) {
    const tabSakit = document.getElementById("tabBtnSakit");
    const tabKeperluan = document.getElementById("tabBtnKeperluan");
    const icon = document.getElementById("modalPermitIcon");
    const noticeSakit = document.getElementById("noticeSakit");
    const noticeKeperluan = document.getElementById("noticeKeperluan");
    const fieldKeperluanKategori = document.getElementById(
        "fieldKeperluanKategori",
    );
    const labelKeterangan = document.getElementById("labelKeterangan");
    const inputKeterangan = document.getElementById("form_keterangan");
    const labelProof = document.getElementById("labelProof");
    const inputProof = document.getElementById("form_proof_url");
    const btnSubmit = document.getElementById("btnSubmitPermit");
    const inputJamOption = document.getElementById("form_jam_option");
    const inputKategoriIzin = document.getElementById("form_kategori_izin");

    if (!tabSakit || !tabKeperluan) return;

    if (type === "sakit") {
        tabSakit.className =
            "py-2 rounded-lg transition-all bg-white text-emerald-700 shadow-xs flex items-center justify-center gap-1.5";
        tabKeperluan.className =
            "py-2 rounded-lg transition-all text-slate-600 hover:text-slate-900 flex items-center justify-center gap-1.5";

        if (icon) {
            icon.className =
                "p-2.5 bg-emerald-100 text-emerald-700 rounded-xl transition-colors";
            icon.innerHTML =
                '<i class="fa-solid fa-notes-medical text-lg"></i>';
        }

        noticeSakit?.classList.remove("hidden");
        noticeKeperluan?.classList.add("hidden");
        fieldKeperluanKategori?.classList.add("hidden");

        if (labelKeterangan)
            labelKeterangan.innerHTML =
                'Keluhan / Kondisi Sakit <span class="text-rose-500">*</span>';
        if (inputKeterangan) {
            inputKeterangan.placeholder =
                "Tuliskan keluhan atau diagnosis singkat sakit Anda...";
            inputKeterangan.className =
                "w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500";
        }

        if (labelProof)
            labelProof.innerHTML =
                'Link Google Drive Surat Dokter <span class="text-rose-500">*</span>';
        if (inputProof) {
            inputProof.required = true;
            inputProof.placeholder = "https://drive.google.com/file/d/...";
            inputProof.className =
                "w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500";
        }

        if (btnSubmit) {
            btnSubmit.className =
                "px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-xl text-xs shadow-sm transition";
            btnSubmit.innerText = "Kirim Izin Sakit";
        }

        if (inputJamOption) inputJamOption.value = "0";
        if (inputKategoriIzin) inputKategoriIzin.value = "1";
    } else {
        tabKeperluan.className =
            "py-2 rounded-lg transition-all bg-white text-amber-700 shadow-xs flex items-center justify-center gap-1.5";
        tabSakit.className =
            "py-2 rounded-lg transition-all text-slate-600 hover:text-slate-900 flex items-center justify-center gap-1.5";

        if (icon) {
            icon.className =
                "p-2.5 bg-amber-100 text-amber-700 rounded-xl transition-colors";
            icon.innerHTML = '<i class="fa-solid fa-user-clock text-lg"></i>';
        }

        noticeKeperluan?.classList.remove("hidden");
        noticeSakit?.classList.add("hidden");
        fieldKeperluanKategori?.classList.remove("hidden");

        if (labelKeterangan)
            labelKeterangan.innerHTML =
                'Alasan / Rincian Keperluan <span class="text-rose-500">*</span>';
        if (inputKeterangan) {
            inputKeterangan.placeholder =
                "Jelaskan alasan keperluan izin Anda...";
            inputKeterangan.className =
                "w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500";
        }

        if (labelProof)
            labelProof.innerHTML =
                'Link Google Drive Bukti Keperluan <span class="text-rose-500">*</span>';
        if (inputProof) {
            inputProof.required = true;
            inputProof.placeholder = "https://drive.google.com/file/d/...";
            inputProof.className =
                "w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500";
        }

        if (btnSubmit) {
            btnSubmit.className =
                "px-4 py-2 bg-amber-700 hover:bg-amber-800 text-white font-semibold rounded-xl text-xs shadow-sm transition";
            btnSubmit.innerText = "Kirim Izin Keperluan";
        }

        if (inputJamOption) inputJamOption.value = "2";
        const subSelect = document.getElementById("keperluan_sub_select");
        if (inputKategoriIzin)
            inputKategoriIzin.value = subSelect ? subSelect.value : "3";
    }
}

function showModalIzin(type = "sakit") {
    const m = document.getElementById("modal2");
    if (m) {
        m.classList.remove("hidden");
        m.classList.add("flex");
        if (typeof switchPermitType === "function") {
            switchPermitType(type);
        }
    }
}

function showModalIzinSakit() {
    showModalIzin("sakit");
}

function showModalIzinKeperluan() {
    showModalIzin("keperluan");
}

function closeModalIzin() {
    const m = document.getElementById("modal2");
    if (m) {
        m.classList.add("hidden");
        m.classList.remove("flex");
    }
}

window.addEventListener("click", function (event) {
    const m2 = document.getElementById("modal2");
    if (event.target === m2) {
        closeModalIzin();
    }
});

// ==============================================
// HOLIDAY MODAL
// ==============================================
function openHolidayModal() {
    const el = document.getElementById("holidayModal");
    if (el) el.classList.remove("hidden");
}

function closeHolidayModal() {
    const el = document.getElementById("holidayModal");
    if (el) el.classList.add("hidden");
}

function openModal() {
    openHolidayModal();
}

const holidayModalEl = document.getElementById("holidayModal");
if (holidayModalEl) {
    holidayModalEl.addEventListener("click", function (event) {
        if (event.target === this) {
            closeHolidayModal();
        }
    });
}

const actionModalEl = document.getElementById("actionModal");
if (actionModalEl) {
    actionModalEl.addEventListener("click", function (event) {
        if (event.target === this) {
            closeActionModal();
        }
    });
}

// ==============================================
// BROADCAST MODALS
// ==============================================
function openModalBroadcast() {
    const el = document.getElementById("broadcastModal");
    if (el) el.classList.remove("hidden");
}

function closeModalBroadcast() {
    const el = document.getElementById("broadcastModal");
    if (el) el.classList.add("hidden");
}

const broadcastModalEl = document.getElementById("broadcastModal");
if (broadcastModalEl) {
    broadcastModalEl.addEventListener("click", function (event) {
        if (event.target === this) {
            closeModalBroadcast();
        }
    });
}

function openModalBroadcastList() {
    const el = document.getElementById("broadcastModalList");
    if (el) el.classList.remove("hidden");
}

function closeModalBroadcastList() {
    const el = document.getElementById("broadcastModalList");
    if (el) el.classList.add("hidden");
}

const broadcastListEl = document.getElementById("broadcastModalList");
if (broadcastListEl) {
    broadcastListEl.addEventListener("click", function (event) {
        if (event.target === this) {
            closeModalBroadcastList();
        }
    });
}

function openModalBroadcastListbyId(id) {
    const el = document.getElementById(`broadcastModalbyId-${id}`);
    if (el) {
        el.classList.remove("hidden");
        el.classList.add("flex");
    }
}

function closeModalBroadcastListbyId(id) {
    const el = document.getElementById(`broadcastModalbyId-${id}`);
    if (el) {
        el.classList.add("hidden");
        el.classList.remove("flex");
    }
}

$(document).on("click", '[id^="broadcastModalbyId-"]', function (event) {
    if (event.target === this) {
        $(this).addClass("hidden").removeClass("flex");
    }
});

// ==============================================
// [BARU] CHECK-IN POPUP — listener event Livewire
// Di-dispatch oleh AttdStatusButton::actionAttd() saat stage = Masuk.
// ==============================================
document.addEventListener("DOMContentLoaded", () => {
    if (window.Livewire) {
        Livewire.on("show-checkin-popup", (data) => {
            const popup = data?.popup ?? data ?? null;
            showCheckinPopup(popup);
        });
    }
});

function showCheckinPopup(popup) {
    if (!popup) return;

    // Hindari popup ganda
    document.getElementById("checkinPopup")?.remove();

    const overlay = document.createElement("div");
    overlay.id = "checkinPopup";
    overlay.className =
        "fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-4";

    const isLate = popup.type === "late";

    const box = document.createElement("div");
    box.className =
        "bg-white rounded-2xl shadow-xl max-w-xs sm:max-w-sm w-full p-5 sm:p-6 text-center max-h-[90vh] flex flex-col justify-center animate-fadeIn";

    // Gambar (jika admin upload)
    if (popup.image) {
        const img = document.createElement("img");
        img.src = popup.image;
        img.alt = "Check-in";
        img.className = "w-28 h-28 sm:w-32 sm:h-32 mx-auto mb-3 sm:mb-4 rounded-xl object-cover";
        box.appendChild(img);
    }

    // Judul
    const title = document.createElement("div");
    title.className =
        "text-base sm:text-lg font-bold mb-2 " +
        (isLate ? "text-red-600" : "text-emerald-600");
    title.textContent = isLate ? "⚠️ Terlambat" : "✅ Tepat Waktu";
    box.appendChild(title);

    // Pesan (textContent = aman dari XSS)
    const msg = document.createElement("p");
    msg.className = "text-xs sm:text-sm text-slate-600 mb-4 whitespace-pre-line leading-relaxed max-h-48 overflow-y-auto";
    msg.textContent = popup.message;
    box.appendChild(msg);

    // Tombol tutup
    const btn = document.createElement("button");
    btn.type = "button";
    btn.className =
        "w-full sm:w-auto px-6 py-2.5 bg-slate-800 text-white rounded-xl text-xs sm:text-sm font-semibold hover:bg-slate-700 transition cursor-pointer mx-auto";
    btn.textContent = "Mengerti";
    btn.addEventListener("click", () => overlay.remove());
    box.appendChild(btn);

    overlay.appendChild(box);
    document.body.appendChild(overlay);
}

// ==============================================
// [PLAN #6] iOS Safari: keyboard virtual menutupi input yang
// sedang fokus → scroll input ke tengah layar setelah keyboard muncul.
// Deteksi mencakup iPadOS 13+ (userAgent "MacIntel" + touch).
// ==============================================
(function () {
    const isIOS =
        /iPhone|iPad|iPod/.test(navigator.userAgent) ||
        (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);
    if (!isIOS) return;

    document.addEventListener("focusin", function (e) {
        const tag = e.target.tagName;
        if (tag === "INPUT" || tag === "TEXTAREA" || tag === "SELECT") {
            setTimeout(() => {
                e.target.scrollIntoView({ behavior: "smooth", block: "center" });
            }, 300);
        }
    });
})();
