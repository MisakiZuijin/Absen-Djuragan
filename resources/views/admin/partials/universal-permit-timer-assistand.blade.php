{{-- File: resources/views/admin/partials/universal-permit-timer-script.blade.php --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const runningTimers = {};

        function initializeTimers() {
            const timerElements = document.querySelectorAll('.timer-value');
            timerElements.forEach(timerElement => {
                const permitId = timerElement.dataset.permitId;
                if (!permitId || runningTimers[permitId]) {
                    return; // Lewati jika tidak ada ID atau timer sudah berjalan
                }
                startTimerFor(timerElement);
            });
        }

        function startTimerFor(timerElement) {
            const permitId = timerElement.dataset.permitId;
            const baseUrl = timerElement.dataset.baseUrl; // Ambil URL template dari atribut HTML

            if (!permitId || !baseUrl) {
                console.error('Timer Gagal: Atribut data-permit-id atau data-base-url tidak ada.', timerElement.dataset);
                timerElement.textContent = 'Error';
                return;
            }
            
            // Buat URL yang valid dengan mengganti placeholder :id
            const fetchUrl = baseUrl.replace(':id', permitId);

            const updateTimer = () => {
                fetch(fetchUrl)
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.is_active) {
                            timerElement.textContent = data.duration;
                        } else {
                            stopTimer(permitId, timerElement);
                        }
                    })
                    .catch(error => {
                        console.error('Gagal mengambil durasi:', error);
                        stopTimer(permitId, timerElement);
                    });
            };

            // Jalankan sekali saat dimulai
            updateTimer();
            // Simpan interval agar bisa dihentikan nanti
            runningTimers[permitId] = setInterval(updateTimer, 1000);
        }

        function stopTimer(permitId, timerElement) {
            if (runningTimers[permitId]) {
                clearInterval(runningTimers[permitId]);
                delete runningTimers[permitId];
            }
            timerElement.textContent = '00:00:00';
        }

        // Mulai semua timer saat halaman dimuat
        initializeTimers();
    });
</script>