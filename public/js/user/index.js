// #FITUR DASHBOARD USER

document.getElementById("activity").addEventListener("input", function () {
    this.value = this.value.replace(/\n/g, " ").replace(/\r/g, "");
});

var is_gps_available = false;
var choosen_stage = 0;
var GVAttendanceId = 0;
var GVAdjusatbleId = 0;

window.addEventListener("DOMContentLoaded", () => {
    // Check if there's a success message
    const successMessage = sessionStorage.getItem("successMessage");
    const successBox = document.getElementById("success-notif");
    const errorBox = document.getElementById("error-notif");
    if (successMessage) {
        document.getElementById("success-message").textContent = successMessage;
        successBox.classList.remove("hidden");

        // Hapus notifikasi setelah 5 detik
        setTimeout(() => {
            successBox.classList.add("hidden");
            sessionStorage.removeItem("successMessage"); // Clear after displaying
        }, 5000);
    }

    // // Check if there's an error message
    const errorMessage = sessionStorage.getItem("errorMessage");
    if (errorMessage) {
        document.getElementById("error-message").textContent = errorMessage;
        errorBox.classList.remove("hidden");

        // Hapus notifikasi setelah 5 detik
        setTimeout(() => {
            errorBox.classList.add("hidden");
            sessionStorage.removeItem("errorMessage"); // Clear after displaying
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

document.addEventListener("DOMContentLoaded", function () {
    const geoNotification = document.getElementById("geoNotification");
    const allowButton = document.getElementById("allowButton");
    const denyButton = document.getElementById("denyButton");

    allowButton.addEventListener("click", function () {
        geoNotification.classList.add("hidden");
        localStorage.setItem("locationPermission", "granted");
    });

    denyButton.addEventListener("click", function () {
        geoNotification.classList.add("hidden");
        localStorage.setItem("locationPermission", "denied");
    });

    // const permissionStatus = localStorage.getItem('locationPermission');

    // console.log("yups in here")
    // console.log('Location Permission:', permissionStatus);
    // if (permissionStatus !== 'granted') {
    navigator.permissions
        .query({ name: "geolocation" })
        .then(function (permissionStatus) {
            navigator.geolocation.getCurrentPosition(
                function (position) { },
                function (error) { }
            );
            // getLocationWithRetry(1);
            // console.log(permissionStatus);
            // geoNotification.classList.remove('hidden');
        });

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

    submitButton.addEventListener("click", function () {
        console.log('submit-clicked');

        submitButton.classList.add("hidden");
        circularLoading.classList.remove("hidden");
        const stageNumberData = [3, 4, 5, 6];
        const text = attdDescription.value;
        console.log('Before dispatch - GVAdjusatbleId:', GVAdjusatbleId);
        console.log('Before dispatch - GVAttendanceId:', GVAttendanceId);
        console.log('Before dispatch - choosen_stage:', choosen_stage);

        if (is_gps_available && stageNumberData.includes(choosen_stage)) {
            // Proses async: dapatkan lokasi, lalu submit
            getLocationWithRetry(5)
                .then(({ latitude, longitude }) => {
                    Livewire.dispatch("actionAttd", {
                        stage: choosen_stage,
                        text,
                        latitude,
                        longitude,
                        attendanceId: GVAttendanceId,
                        adjustableId: GVAdjusatbleId
                    });
                    attdDescription.value = "";
                })
                .catch((error) => {
                    // Tampilkan notifikasi error jika gagal dapat lokasi
                    document.getElementById("error-message").textContent = "Gagal mendapatkan lokasi: " + (error.message || error);
                    document.getElementById("error-notif").classList.remove("hidden");
                    setTimeout(() => {
                        document.getElementById("error-notif").classList.add("hidden");
                    }, 5000);
                })
                .finally(() => {
                    circularLoading.classList.add("hidden");
                    submitButton.classList.remove("hidden");
                });
        } else {
            Livewire.dispatch("actionAttd", {
                stage: choosen_stage,
                text: text,
                attendanceId: GVAttendanceId,
                adjustableId: GVAdjusatbleId
            });
            attdDescription.value = "";
            circularLoading.classList.add("hidden");
            submitButton.classList.remove("hidden");
        }
    });

    function getLocationWithRetry(retries) {
        return new Promise(function (resolve, reject) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    console.log("Got geolocation:", latitude, longitude);
                    resolve({ latitude, longitude });
                },
                function (error) {
                    if (error.code === error.TIMEOUT && retries > 0) {
                        console.warn(`Geolocation timeout. Retries left: ${retries}`);
                        // Retry
                        getLocationWithRetry(retries - 1).then(resolve).catch(reject);
                    } else {
                        console.error("Error getting geolocation:", error);
                        reject(error);
                    }
                },
                {
                    enableHighAccuracy: true,
                    maximumAge: 0,
                }
            );
        });
    }

    function getCookie(name) {
        let match = document.cookie.match(
            new RegExp("(^| )" + name + "=([^;]+)")
        );
        if (match) return match[2];
    }
});

function showModal(
    is_gps_permited,
    userId,
    stage,
    isAdjustable = false,
    attendanceId,
    adjustableId
) {
    console.log('open-modal');
    console.log('adjustableId received:', adjustableId);
    console.log('attendanceId received:', attendanceId);
    console.log('stage:', stage);
    console.log('isAdjustable:', isAdjustable);

    is_gps_available = is_gps_permited;
    choosen_stage = stage;
    GVAttendanceId = attendanceId || 0;
    GVAdjusatbleId = adjustableId || 0;

    console.log('GVAdjusatbleId set to:', GVAdjusatbleId);

    const modal = document.getElementById("actionModal");
    modal.classList.remove("hidden");

    if (navigator.permissions) {
        navigator.permissions
            .query({ name: "geolocation" })
            .then((result) => {
                if (result.state === "granted") {
                    console.log("Geolocation access granted");
                } else if (result.state === "denied") {
                    console.log("Geolocation access denied");
                } else {
                    console.log("Geolocation permission prompt will be shown");
                }
            })
            .catch((error) =>
                console.error("Error checking permission", error)
            );
    }
}

function closeModal() {
    const modal = document.getElementById("actionModal");

    modal.classList.add("hidden");
}

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
                }
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

function togglePopup() {
    const popup = document.getElementById("popup-form");
    if (popup.classList.contains("flex")) {
        popup.classList.remove("flex");
        popup.classList.add("hidden");
    } else {
        popup.classList.remove("hidden");
        popup.classList.add("flex");
    }
}
document.addEventListener("DOMContentLoaded", () => {
    const textarea = document.getElementById("activity");
    const button = document.getElementById("submit-button");

    function updateButtonColor() {
        if (textarea.value.trim() === "") {
            button.classList.remove("bg-red-700");
            button.classList.add("bg-red-200");
            button.disabled = true;
        } else {
            button.classList.remove("bg-red-200");
            button.classList.add("bg-red-600");
            button.disabled = false;
        }
    }

    updateButtonColor();

    textarea.addEventListener("input", updateButtonColor);
});

document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("popup-form");
    const closeModalButton = document.getElementById("closeModal");

    function closeModal() {
        modal.classList.add("hidden");
    }

    closeModalButton.addEventListener("click", closeModal);

    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });
});

function showModalIzin() {
    document.getElementById("modal2").classList.remove("hidden");
}

function closeModalIzin() {
    document.getElementById("modal2").classList.add("hidden");
}

window.addEventListener("click", function (event) {
    var modal = document.getElementById("modal2");
    if (event.target === modal) {
        closeModalIzin();
    }
});

function openModal() {
    document.getElementById("holidayModal").classList.remove("hidden");
}

function closeModal() {
    document.getElementById("holidayModal").classList.add("hidden");
}

document
    .getElementById("holidayModal")
    .addEventListener("click", function (event) {
        if (event.target === this) {
            closeModal();
        }
    });

function openModalBroadcast() {
    document.getElementById("broadcastModal").classList.remove("hidden");
}

function closeModalBroadcast() {
    document.getElementById("broadcastModal").classList.add("hidden");
}

document
    .getElementById("broadcastModal")
    .addEventListener("click", function (event) {
        if (event.target === this) {
            closeModalBroadcast();
        }
    });

function openModalBroadcastList() {
    document.getElementById("broadcastModalList").classList.remove("hidden");
}

function closeModalBroadcastList() {
    document.getElementById("broadcastModalList").classList.add("hidden");
}

document
    .getElementById("broadcastModalList")
    .addEventListener("click", function (event) {
        if (event.target === this) {
            closeModalBroadcastList();
        }
    });

function openModalBroadcastListbyId(id) {
    document
        .getElementById(`broadcastModalbyId-${id}`)
        .classList.remove('hidden');
}

function closeModalBroadcastListbyId(id) {
    document
        .getElementById(`broadcastModalbyId-${id}`)
        .classList.add('hidden');
}

document
    .getElementById(`broadcastModalbyId-${id}`)
    .addEventListener("click", function (event) {
        if (event.target === this) {
            closeModalBroadcastListbyId(id);
        }
    });
