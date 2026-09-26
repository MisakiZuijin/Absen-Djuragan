// #FITUR DASHBOARD USER

const initialActivityElem = document.getElementById("activity");
if (initialActivityElem) {
    initialActivityElem.addEventListener("input", function () {
        this.value = this.value.replace(/\r/g, "");
    });
}

// ==============================================
// GLOBAL STATE
// ==============================================
var is_gps_available = false;
var choosen_stage = 0;
var GVAttendanceId = 0;
var GVAdjustableId = 0;

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
        modal.classList.add("hidden");
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
            submitButton.classList.add("hidden");
            circularLoading.classList.remove("hidden");

            const stageNumberData = [3, 4, 5, 6];
            const text = attdDescription ? attdDescription.value : "";

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
                        // Retry
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
    modal.classList.remove("hidden");
}

function closeActionModal() {
    const modal = document.getElementById("actionModal");
    if (modal) {
        modal.classList.add("hidden");
    }
}

// Menutup kedua modal sekaligus (aman untuk semua pemanggil)
function closeModal() {
    closeActionModal();
    closeHolidayModal();
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
