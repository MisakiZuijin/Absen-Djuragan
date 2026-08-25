<script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log('Universal permit timer script loaded');

        const permitTimers = {};

        function startAndRunTimer(timerElement) {
            const permitId = timerElement.dataset.permitId;
            const permitType = timerElement.dataset.permitType;

            console.log('Starting timer for:', permitId, 'type:', permitType);

            if (!permitId || !permitType || permitTimers[permitId]) {
                console.log('Timer not started - invalid data or already running');
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

            console.log('Fetch URL:', fetchUrl);

            function updateTimer() {
                fetch(fetchUrl)
                    .then(response => {
                        console.log('Response status:', response.status, 'for type:', permitType);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data for', permitType, ':', data);

                        if (data.is_active) {
                            // Update text content
                            timerElement.textContent = data.duration;

                            // Update class untuk styling
                            timerElement.classList.remove('text-gray-500');
                            timerElement.classList.add('text-gray-900', 'font-mono');

                            console.log('Updated', permitType, 'timer to:', data.duration);

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

                            console.log('Timer stopped for', permitType, 'permit:', permitId);
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

            console.log('Timer started for', permitType, 'permit:', permitId);
        }

        // Initialize semua timer
        const timerElements = document.querySelectorAll('.timer-value');
        console.log('Found', timerElements.length, 'timer elements');

        timerElements.forEach(element => {
            console.log('Timer element data:', {
                id: element.dataset.permitId,
                type: element.dataset.permitType,
                text: element.textContent
            });
            startAndRunTimer(element);
        });

        // Debug: Log semua element dengan data-permit-type
        const allPermitElements = document.querySelectorAll('[data-permit-type]');
        console.log('All permit elements:', allPermitElements.length);
        allPermitElements.forEach(el => {
            console.log('Permit element:', el.dataset.permitType, el.dataset.permitId);
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
