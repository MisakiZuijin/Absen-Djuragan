<script>
    document.addEventListener('DOMContentLoaded', function () {
        const permitTimers = {};

        function startAndRunTimer(timerElement) {
            const permitId = timerElement.dataset.permitId;
            const permitType = timerElement.dataset.permitType;

            if (!permitId || !permitType || permitTimers[permitId]) {
                return;
            }

            // URL endpoint - PASTIKAN SEMUA TIPE IZIN DITANGANI
            let fetchUrl = '';
            switch (permitType) {
                case 'prayer':
                    fetchUrl = `/admin/izin-shalat/duration/${permitId}`;
                    break;
                case 'leave':
                    fetchUrl = `/admin/izin-keluar/duration/${permitId}`;
                    break;
                case 'toilet':
                    fetchUrl = `/admin/izin-toilet/duration/${permitId}`;
                    break;
                default:
                    console.error('Unknown permit type:', permitType);
                    return;
            }

            function updateTimer() {
                fetch(fetchUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.is_active) {
                            // Update text content
                            timerElement.textContent = data.duration;

                            // Update class untuk styling
                            timerElement.classList.remove('text-gray-500');
                            timerElement.classList.add('text-gray-900', 'font-mono');

                            // Update status cell berdasarkan tipe izin
                            const statusCell = document.getElementById(`status-cell-${permitId}`);
                            if (statusCell) {
                                switch (permitType) {
                                    case 'prayer':
                                        statusCell.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-mosque mr-1"></i> Sedang Shalat</span>`;
                                        break;
                                    case 'leave':
                                        statusCell.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-sign-out-alt mr-1"></i> Sedang Izin</span>`;
                                        break;
                                    case 'toilet':
                                        statusCell.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class="fas fa-toilet mr-1"></i> Sedang Ke Toilet</span>`;
                                        break;
                                }
                            }
                        } else {
                            // Izin selesai
                            clearInterval(permitTimers[permitId]);
                            delete permitTimers[permitId];

                            timerElement.textContent = '-';
                            timerElement.classList.remove('text-gray-900', 'font-mono');
                            timerElement.classList.add('text-gray-500');

                            // Update status menjadi "Normal"
                            const statusCell = document.getElementById(`status-cell-${permitId}`);
                            if (statusCell) {
                                statusCell.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><i class="fas fa-check mr-1"></i> Normal</span>`;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching', permitType, 'duration:', error);
                        clearInterval(permitTimers[permitId]);
                        delete permitTimers[permitId];
                    });
            }

            // Jalankan pertama kali
            updateTimer();

            // Set interval untuk update berkala
            permitTimers[permitId] = setInterval(updateTimer, 1000);
        }

        // Initialize semua timer
        const timerElements = document.querySelectorAll('.timer-value');
        timerElements.forEach(element => {
            startAndRunTimer(element);
        });

        const allPermitElements = document.querySelectorAll('[data-permit-type]');
        allPermitElements.forEach(el => {
            if (el.classList.contains('timer-value')) {
                startAndRunTimer(el);
            }
        });

        // Observer untuk element baru
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.addedNodes.length) {
                    mutation.addedNodes.forEach(function (node) {
                        if (node.nodeType === 1) {
                            if (node.classList && node.classList.contains('timer-value')) {
                                startAndRunTimer(node);
                            }

                            const newTimers = node.querySelectorAll('.timer-value');
                            newTimers.forEach(timer => {
                                if (!permitTimers[timer.dataset.permitId]) {
                                    startAndRunTimer(timer);
                                }
                            });
                        }
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });
</script>
