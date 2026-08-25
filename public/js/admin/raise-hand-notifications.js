class RaiseHandNotificationManager {
    constructor() {
        this.permission = 'default';
        this.lastCount = 0;
        this.pollInterval = null;
        this.notificationSound = null;
        this.isInitialized = false;
    }

    async init() {
        if (this.isInitialized) return;

        await this.requestPermission();

        this.loadNotificationSound();

        this.startPolling();
        
        this.isInitialized = true;
        console.log('✅ Raise Hand Notification System initialized');
    }

    async requestPermission() {
        if (!('Notification' in window)) {
            console.warn('Browser tidak mendukung notifikasi desktop');
            return;
        }

        if (Notification.permission === 'granted') {
            this.permission = 'granted';
            console.log('✅ Notifikasi sudah diizinkan');
        } else if (Notification.permission !== 'denied') {
            const permission = await Notification.requestPermission();
            this.permission = permission;
            
            if (permission === 'granted') {
                console.log('✅ Notifikasi berhasil diizinkan');
                this.showWelcomeNotification();
            } else {
                console.log('❌ Notifikasi ditolak');
            }
        }
    }

    loadNotificationSound() {
        this.notificationSound = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUKXh8LZjHAU5k9nyz3osBSl+zPLaizsKGGS56+mmVBELTKXh8bllHgU2jdTz0H0vBSh+zPDajDwJFmS56+mnVBEKTKXh8bllHgU2jdTz0H0vBSh+zPDajDwJFmS56+mnVBEKTKXh8bllHgU2jdTz0H0vBSh+zPDajDwJFmS56+mnVBEKTKXh8bllHgU2jdTz0H0vBSh+zPDajDwJFmS56+mnVBEKTKXh8bllHgU2jdTz0H0vBSh+zPDajDwJFmS56+mnVBEKTKXh8bllHgU2jdTz0H0vBSh+zPDajDwJFmS56+mnVBEKTKXh8bllHgU2jdTz0H0vBSh+zPDajDwJFmS56+mnVBEKTKXh8bllHgU2jdTz0H0vBSh+zPDajDwJFmS56+mnVBEKTKXh8bllHgU2jdTz0H0vBSh+zPDajDwJFmS56+mnVBEKTKXh8bllHgU2jdTz0H0vBSh+zPDajDwJFmS56+mnVBEKTKXh8bllHgU2jdTz0H0vBSh+zPDajDwJFmS56+mnVBEKTKXh8bllHgU2jdTz0H0vBSh+zPDajDwJFmS56+mnVBEKTKXh8bllHgU2jdTz0H0vBSh+zPDajDwJFmS56+mnVBEKTKXh8bllHgU2jdTz0H0vBSh+zPDajDwJFmS56+mnVBEKTKXh8bllHgU2jdTz0H0vBQ==');
        this.notificationSound.volume = 0.5;
    }

    showWelcomeNotification() {
        this.showNotification(
            'Sistem Notifikasi Aktif',
            'Anda akan menerima notifikasi saat ada permintaan raise hand baru',
            '/favicon.ico'
        );
    }

    startPolling(interval = 5000) {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
        }

        this.checkForUpdates();

        this.pollInterval = setInterval(() => {
            this.checkForUpdates();
        }, interval);

        console.log(`🔄 Polling started (every ${interval}ms)`);
    }

    async checkForUpdates() {
        try {
            const response = await fetch('/admin/raise-hand/count', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            const currentCount = data.count || 0;

            this.updateBadge(currentCount);

            if (currentCount > this.lastCount) {
                const newRequests = currentCount - this.lastCount;
                this.handleNewRequest(newRequests, currentCount);
            }

            this.lastCount = currentCount;

        } catch (error) {
            console.error('Error checking raise hand updates:', error);
        }
    }

    async handleNewRequest(newRequests, totalCount) {
        this.playNotificationSound();

        if (this.permission === 'granted') {
            const title = newRequests === 1 
                ? '🤚 Permintaan Bantuan Baru!' 
                : `🤚 ${newRequests} Permintaan Bantuan Baru!`;
            
            const body = totalCount === 1
                ? 'Ada 1 peserta yang membutuhkan bantuan'
                : `Total ${totalCount} peserta sedang menunggu bantuan`;

            this.showNotification(title, body, '/favicon.ico', {
                requireInteraction: true,
                actions: [
                    { action: 'view', title: '👀 Lihat' },
                    { action: 'dismiss', title: '✖️ Tutup' }
                ]
            });
        }

        this.flashTabTitle();
    }

    showNotification(title, body, icon, options = {}) {
        if (this.permission !== 'granted') return;

        const defaultOptions = {
            body: body,
            icon: icon,
            badge: icon,
            tag: 'raise-hand-notification',
            requireInteraction: false,
            silent: false,
            ...options
        };

        const notification = new Notification(title, defaultOptions);

        notification.onclick = (event) => {
            event.preventDefault();
            window.focus();

            if (event.action === 'view' || !event.action) {
                window.location.href = '/admin/raise-hand';
            }
            
            notification.close();
        };

        if (!defaultOptions.requireInteraction) {
            setTimeout(() => notification.close(), 10000);
        }

        return notification;
    }

    // Play notification sound
    playNotificationSound() {
        if (this.notificationSound) {
            this.notificationSound.currentTime = 0;
            this.notificationSound.play().catch(e => {
                console.warn('Could not play notification sound:', e);
            });
        }
    }

    flashTabTitle() {
        const originalTitle = document.title;
        let flashCount = 0;
        const maxFlashes = 6;

        const flashInterval = setInterval(() => {
            document.title = flashCount % 2 === 0 
                ? '🔴 PERMINTAAN BARU!' 
                : originalTitle;
            
            flashCount++;

            if (flashCount >= maxFlashes) {
                clearInterval(flashInterval);
                document.title = originalTitle;
            }
        }, 500);
    }

    updateBadge(count) {
        const badge = document.getElementById('raiseHandBadge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    }

    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
            console.log('⏹️ Polling stopped');
        }
    }

    destroy() {
        this.stopPolling();
        this.isInitialized = false;
    }
}

let raiseHandNotificationManager = null;

document.addEventListener('DOMContentLoaded', function() {
    const isAdminPage = window.location.pathname.includes('/admin');
    
    if (isAdminPage) {
        raiseHandNotificationManager = new RaiseHandNotificationManager();
        raiseHandNotificationManager.init();
        
        window.raiseHandNotificationManager = raiseHandNotificationManager;
    }
});

document.addEventListener('visibilitychange', function() {
    if (raiseHandNotificationManager) {
        if (document.hidden) {
            console.log('Tab hidden - continuing background polling');
        } else {
            console.log('Tab visible - checking for updates');
            raiseHandNotificationManager.checkForUpdates();
        }
    }
});

window.addEventListener('beforeunload', function() {
    if (raiseHandNotificationManager) {
        raiseHandNotificationManager.destroy();
    }
});