@php
    $toiletMonitorIntervalSec = \App\Models\PopupSetting::getInterval('toilet_monitor', 15);
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const pollIntervalMs = {{ $toiletMonitorIntervalSec > 0 ? $toiletMonitorIntervalSec * 1000 : 0 }};

        function formatSeconds(totalSecs) {
            const h = String(Math.floor(totalSecs / 3600)).padStart(2, '0');
            const m = String(Math.floor((totalSecs % 3600) / 60)).padStart(2, '0');
            const s = String(totalSecs % 60).padStart(2, '0');
            return `${h}:${m}:${s}`;
        }

        function updateAllTimers() {
            const nowEpoch = Math.floor(Date.now() / 1000);
            const timerElements = document.querySelectorAll('.timer-value');
            timerElements.forEach(timerElement => {
                const startTimeEpoch = parseInt(timerElement.dataset.startTime, 10);
                if (startTimeEpoch && !isNaN(startTimeEpoch) && startTimeEpoch > 0) {
                    const elapsedSeconds = Math.max(0, nowEpoch - startTimeEpoch);
                    timerElement.textContent = formatSeconds(elapsedSeconds);
                    timerElement.classList.remove('text-gray-500');
                    if (!timerElement.classList.contains('text-amber-600') && !timerElement.classList.contains('text-amber-700')) {
                        timerElement.classList.add('text-gray-900');
                    }
                    timerElement.classList.add('font-mono');
                }
            });
        }

        // Jalankan kalkulasi durasi langsung & jalankan interval 1 detik berkelanjutan
        updateAllTimers();
        setInterval(updateAllTimers, 1000);

        // Background auto-refresh data tabel / kartu
        if (pollIntervalMs >= 2000) {
            setInterval(() => {
                const targetContainer = document.getElementById('internsTableBody') || document.getElementById('internsContainer');
                if (!targetContainer) return;

                fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTargetContainer = doc.getElementById('internsTableBody') || doc.getElementById('internsContainer');
                    if (newTargetContainer) {
                        targetContainer.innerHTML = newTargetContainer.innerHTML;
                        updateAllTimers();

                        const searchInput = document.getElementById('searchInput');
                        if (searchInput && searchInput.value.trim() !== '') {
                            const filter = searchInput.value.toLowerCase();
                            const rows = targetContainer.querySelectorAll('.intern-row');
                            rows.forEach(row => {
                                const name = (row.getAttribute('data-name') || '').toLowerCase();
                                row.style.display = name.includes(filter) ? '' : 'none';
                            });
                        }
                    }
                })
                .catch(() => {});
            }, pollIntervalMs);
        }
    });
</script>
