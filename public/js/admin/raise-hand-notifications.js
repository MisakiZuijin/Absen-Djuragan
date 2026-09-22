/**
 * Unified Raise Hand Notification System for Admin & Assistant Admin
 * Uses HTML5 Audio (/sounds/notification.wav) for reliable, clean notifications.
 */
class RaiseHandNotificationManager {
    constructor() {
        this.role = null; // 'admin' | 'assistant'
        this.badgeElement = null;
        this.pollUrl = null;
        this.targetUrl = null;
        this.currentCount = 0;
        this.urgentCount = 0;
        this.isFirstRun = true;
        this.pollInterval = null;
        this.pollIntervalMs = 4000;
        this.isFetching = false;
        this.titleFlashInterval = null;
        this.originalDocumentTitle = document.title;
        this.notificationPermission = 'default';
        this.repeatInterval = null;
        this.repeatIntervalMs = 12000; // Pengulangan bunyi setiap 12 detik khusus untuk permintaan kondisi URGENT
        this.isAudioUnlocked = false;
        this.audioNoticeElement = null;
        this.isInitialized = false;
    }

    getSoundUrl() {
        return window.__raiseHandSoundUrl || '/sounds/notification.wav';
    }

    init() {
        if (this.isInitialized) return;

        // Detect if admin or assistant admin badge exists in the DOM
        const adminBadge = document.getElementById('raiseHandBadge');
        const assistantBadge = document.getElementById('assistantRaiseHandBadge');

        if (adminBadge) {
            this.role = 'admin';
            this.badgeElement = adminBadge;
            this.pollUrl = '/admin/raise-hand/count';
            this.targetUrl = '/admin/raise-hand';
        } else if (assistantBadge) {
            this.role = 'assistant';
            this.badgeElement = assistantBadge;
            this.pollUrl = '/raise-hand/count';
            this.targetUrl = '/assistant-admin/raise-hand/list';
        } else {
            // Neither badge found on this page
            return;
        }

        // Read initial count from HTML badge rendered by server
        const initialText = this.badgeElement.textContent.trim();
        this.currentCount = parseInt(initialText, 10) || 0;

        // Setup audio & permission
        this.initAudio();
        this.setupNotificationPermission();

        // Start polling (akan memeriksa kondisi urgent pada poll pertama)
        this.isInitialized = true;
        this.startPolling();

        console.log(`🔔 Raise Hand Notification System initialized for [${this.role}] (Initial count: ${this.currentCount})`);
    }

    initAudio() {
        this.isAudioUnlocked = false;

        // Listener satu kali saat pengguna berinteraksi pertama kali dengan halaman
        const unlockOnUserGesture = () => {
            if (this.isAudioUnlocked) return;
            this.isAudioUnlocked = true;
            this.hideAudioNotice();
            console.log('🔊 Audio browser telah di-unlock melalui interaksi pengguna');

            // Hapus listener setelah sekali terpicu
            ['click', 'keydown', 'touchstart'].forEach(evt => {
                document.removeEventListener(evt, unlockOnUserGesture);
            });
        };

        ['click', 'keydown', 'touchstart'].forEach(evt => {
            document.addEventListener(evt, unlockOnUserGesture, { once: true, passive: true });
        });
    }

    async setupNotificationPermission() {
        if (!('Notification' in window)) return;

        this.notificationPermission = Notification.permission;
        if (this.notificationPermission === 'default') {
            const requestOnGesture = async () => {
                try {
                    const permission = await Notification.requestPermission();
                    this.notificationPermission = permission;
                } catch (e) {}
                document.removeEventListener('click', requestOnGesture);
            };
            document.addEventListener('click', requestOnGesture, { once: true, passive: true });
        }
    }

    startPolling() {
        if (this.pollInterval) clearInterval(this.pollInterval);

        // First poll check after 1.5 seconds
        setTimeout(() => {
            this.checkForUpdates();
        }, 1500);

        this.pollInterval = setInterval(() => {
            this.checkForUpdates();
        }, this.pollIntervalMs);
    }

    async checkForUpdates() {
        if (this.isFetching || !this.pollUrl) return;

        this.isFetching = true;
        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };
            if (csrfMeta) {
                headers['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');
            }

            const response = await fetch(this.pollUrl, {
                method: 'GET',
                headers: headers,
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            const newCount = typeof data.count === 'number' ? data.count : (parseInt(data.count, 10) || 0);
            const urgentCount = typeof data.urgent_count === 'number' ? data.urgent_count : 0;

            this.handleCountUpdate(newCount, urgentCount);
        } catch (error) {
            // Silently handle ordinary polling network glitches
        } finally {
            this.isFetching = false;
        }
    }

    handleCountUpdate(newCount, urgentCount = 0) {
        if (!this.badgeElement) {
            this.badgeElement = document.getElementById(this.role === 'admin' ? 'raiseHandBadge' : 'assistantRaiseHandBadge');
            if (!this.badgeElement) return;
        }

        const isNewArrival = !this.isFirstRun && (newCount > this.currentCount);
        const diff = newCount - this.currentCount;

        // Update badge text and visibility (menampilkan total seluruh antrean aktif)
        if (newCount > 0) {
            this.badgeElement.textContent = newCount > 99 ? '99+' : newCount;
            this.badgeElement.classList.remove('hidden');
        } else {
            this.badgeElement.textContent = '0';
            this.badgeElement.classList.add('hidden');
        }

        // PENGULANGAN BUNYI: HANYA untuk permintaan yang masuk KONDISI URGENT.
        // Permintaan presentasi besok / masa mendatang (urgentCount == 0) TIDAK diberi notifikasi ulang.
        if (urgentCount > 0) {
            this.startSoundRepeat(urgentCount);
        } else {
            this.stopSoundRepeat();
        }

        // Trigger alert effects on NEW requests
        if (isNewArrival) {
            console.log(`🔔 Permintaan raise hand baru (+${diff}, total: ${newCount}, urgent: ${urgentCount})`);
            this.playNotificationSound();
            this.showDesktopNotification(diff, newCount);
            this.flashTabTitle(diff, newCount);
            this.animateBadge();
        }

        this.currentCount = newCount;
        this.urgentCount = urgentCount;
        this.isFirstRun = false;
    }

    playNotificationSound() {
        try {
            const soundUrl = this.getSoundUrl();
            const audio = new Audio(soundUrl);
            audio.volume = 0.85;

            const playPromise = audio.play();
            if (playPromise !== undefined) {
                playPromise.then(() => {
                    console.log('🔊 Audio notifikasi WAV berhasil berbunyi:', soundUrl);
                    this.isAudioUnlocked = true;
                    this.hideAudioNotice();
                }).catch(err => {
                    console.warn('⚠️ Pemutaran audio dicegah oleh browser autoplay policy:', err.name);
                    // Tampilkan notifikasi ramah kepada admin jika browser memblokir audio otomatis
                    this.showAudioNotice();
                });
            }
        } catch (e) {
            console.error('❌ Gagal memutar suara notifikasi:', e);
        }
    }

    showAudioNotice() {
        if (this.audioNoticeElement || this.isAudioUnlocked) return;

        const notice = document.createElement('div');
        notice.id = 'raiseHandAudioNotice';
        notice.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-50 bg-amber-500 hover:bg-amber-600 text-white font-medium px-4 py-2.5 rounded-full shadow-xl flex items-center gap-2 cursor-pointer transition-all duration-300 text-sm animate-bounce';
        notice.innerHTML = `
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M12 6v12m-3.5-3.5L5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L8.5 14.5z"/>
            </svg>
            <span>Ada antrean Raise Hand! Klik di sini untuk mengaktifkan suara alarm</span>
        `;

        notice.addEventListener('click', (e) => {
            e.stopPropagation();
            this.isAudioUnlocked = true;
            this.hideAudioNotice();
            this.playNotificationSound();
        });

        document.body.appendChild(notice);
        this.audioNoticeElement = notice;
    }

    hideAudioNotice() {
        if (this.audioNoticeElement) {
            try {
                this.audioNoticeElement.remove();
            } catch (e) {}
            this.audioNoticeElement = null;
        }
    }

    showDesktopNotification(diff, total) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;

        const roleTitle = this.role === 'assistant' ? 'Asisten Admin' : 'Admin';
        const title = diff === 1 ? '🤚 Permintaan Bantuan Baru!' : `🤚 ${diff} Permintaan Bantuan Baru!`;
        const body = total === 1 ? 'Ada 1 peserta magang yang membutuhkan bantuan' : `Total ${total} peserta sedang menunggu bantuan`;

        try {
            const notif = new Notification(title, {
                body: body,
                icon: '/favicon.ico',
                badge: '/favicon.ico',
                tag: 'raise-hand-notification',
                renotify: true,
                requireInteraction: false
            });

            notif.onclick = (event) => {
                event.preventDefault();
                window.focus();
                if (this.targetUrl) {
                    window.location.href = this.targetUrl;
                }
                notif.close();
            };

            setTimeout(() => notif.close(), 8000);
        } catch (e) {}
    }

    flashTabTitle(diff, total) {
        if (this.titleFlashInterval) {
            clearInterval(this.titleFlashInterval);
        }

        this.originalDocumentTitle = document.title.replace(/^🔴\s*\(\d+\)\s*/, '');
        let flashCount = 0;
        const maxFlashes = 8;
        const alertTitle = `🔴 (${total}) BANTUAN BARU!`;

        this.titleFlashInterval = setInterval(() => {
            document.title = (flashCount % 2 === 0) ? alertTitle : this.originalDocumentTitle;
            flashCount++;

            if (flashCount >= maxFlashes) {
                clearInterval(this.titleFlashInterval);
                this.titleFlashInterval = null;
                document.title = this.originalDocumentTitle;
            }
        }, 500);
    }

    animateBadge() {
        if (!this.badgeElement) return;
        this.badgeElement.classList.add('scale-125', 'ring-4', 'ring-red-300');
        setTimeout(() => {
            if (this.badgeElement) {
                this.badgeElement.classList.remove('scale-125', 'ring-4', 'ring-red-300');
            }
        }, 600);
    }

    startSoundRepeat(urgentCount = 1) {
        if (this.repeatInterval) return;

        console.log(`🔁 Pengulangan bunyi aktif: berbunyi berkala setiap ${this.repeatIntervalMs / 1000}s khusus untuk ${urgentCount} permintaan kondisi URGENT`);
        this.repeatInterval = setInterval(() => {
            if (this.urgentCount > 0) {
                console.log(`🔁 Mengulang bunyi notifikasi untuk ${this.urgentCount} permintaan kondisi URGENT`);
                this.playNotificationSound();
                this.animateBadge();
            } else {
                this.stopSoundRepeat();
            }
        }, this.repeatIntervalMs);
    }

    stopSoundRepeat() {
        if (this.repeatInterval) {
            clearInterval(this.repeatInterval);
            this.repeatInterval = null;
            console.log('⏹️ Pengulangan bunyi dinonaktifkan');
        }
    }

    destroy() {
        this.stopSoundRepeat();
        this.hideAudioNotice();
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
        if (this.titleFlashInterval) {
            clearInterval(this.titleFlashInterval);
            this.titleFlashInterval = null;
            document.title = this.originalDocumentTitle;
        }
        this.isInitialized = false;
    }
}

// Global Helper to Test Sound on Demand
window.testNotificationSound = function(e) {
    if (e) {
        if (e.preventDefault) e.preventDefault();
        if (e.stopPropagation) e.stopPropagation();
    }

    const manager = window.raiseHandNotificationManager;
    const soundUrl = (manager && manager.getSoundUrl)
        ? manager.getSoundUrl()
        : (window.__raiseHandSoundUrl || '/sounds/notification.wav');

    const audio = new Audio(soundUrl);
    audio.volume = 0.85;

    audio.play().then(() => {
        console.log('🔊 Test suara notifikasi WAV berhasil berbunyi!');
        if (manager) {
            manager.isAudioUnlocked = true;
            manager.hideAudioNotice();
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '🔊 Suara notifikasi aktif & berfungsi!',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        }
    }).catch(err => {
        console.error('❌ Gagal memutar suara test:', err);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: '⚠️ Browser membatasi audio: silakan klik halaman sekali',
                showConfirmButton: false,
                timer: 3000
            });
        }
    });
};

// Single Global Instance Protection
(function() {
    if (window.__raiseHandNotificationManagerInstance) {
        return;
    }

    function initManager() {
        if (window.__raiseHandNotificationManagerInstance) return;
        const manager = new RaiseHandNotificationManager();
        manager.init();
        window.__raiseHandNotificationManagerInstance = manager;
        window.raiseHandNotificationManager = manager;
        // Backward-compatible global references
        window.adminRaiseHandNotifier = manager;
        window.assistantAdminNotifier = manager;
    }

    // Class alias on window for backward compatibility
    window.RaiseHandNotifications = RaiseHandNotificationManager;
    window.AssistantAdminNotifications = RaiseHandNotificationManager;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initManager);
    } else {
        initManager();
    }

    document.addEventListener('visibilitychange', function() {
        if (window.__raiseHandNotificationManagerInstance && !document.hidden) {
            window.__raiseHandNotificationManagerInstance.checkForUpdates();
        }
    });

    window.addEventListener('beforeunload', function() {
        if (window.__raiseHandNotificationManagerInstance) {
            window.__raiseHandNotificationManagerInstance.destroy();
        }
    });
})();