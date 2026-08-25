<nav class="fixed top-0 right-0 left-0 md:left-64 z-30 bg-white/80 backdrop-blur-sm border-b border-slate-200 transition-all duration-300 ease-in-out">
    <div class="flex justify-between items-center p-4">
        <div class="flex-1"></div>

        @auth
            <div class="flex items-center gap-4">
                {{-- Notification Area untuk Admin --}}
                @if(auth()->user()->role_id == 1)
                <div class="flex items-center gap-3 mr-4">
                    <a href="{{ route('admin.raiseHand.index') }}"
                       class="relative p-2 rounded-full hover:bg-gray-100 transition-colors duration-200"
                       title="Permintaan Raise Hand">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3.5M3 16.5h18"/>
                        </svg>
                        @php
                            $raiseHandCount = \App\Models\HandRaise::where('is_raised', true)->count();
                        @endphp
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center {{ $raiseHandCount > 0 ? '' : 'hidden' }}"
                              id="raiseHandBadge">
                            {{ $raiseHandCount > 0 ? $raiseHandCount : '0' }}
                        </span>
                    </a>
                    <div class="w-px h-6 bg-gray-300"></div>
                </div>
                @elseif(auth()->user()->role_id == 6)
                {{-- Notification Area untuk Assistant Admin --}}
                <div class="flex items-center gap-3 mr-4">
                    <a href="{{ route('assistant.raisehand.list') }}"
                       class="relative p-2 rounded-full hover:bg-gray-100 transition-colors duration-200"
                       title="Permintaan Raise Hand">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3.5M3 16.5h18"/>
                        </svg>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden"
                              id="assistantRaiseHandBadge">
                            0
                        </span>
                    </a>
                    <div class="w-px h-6 bg-gray-300"></div>
                </div>
                @endif

                <a href="{{ route('profile.pengaturan.view') }}" class="flex items-center space-x-3 text-black no-underline hover:bg-gray-50 p-2 rounded-lg transition-colors duration-200">
                    <div class="flex flex-col items-end">
                        <span class="text-right font-semibold">{{ optional(auth()->user()->profile)->full_name ?? auth()->user()->name ?? 'Nama Pengguna' }}</span>
                        <span class="text-right text-sm text-gray-500">
                            @switch(auth()->user()->role_id)
                                @case(1) Admin @break
                                @case(2) HR @break
                                @case(3) Intern @break
                                @case(5) Outsider @break
                                @case(6) Assistant Admin @break
                                @default User
                            @endswitch
                        </span>
                    </div>
                    <img src="{{ optional(auth()->user()->profile)->photo ? asset('storage/' . auth()->user()->profile->photo) : asset('img/profile.jpg') }}"
                         alt="Foto Profil"
                         class="w-12 h-12 rounded-full object-cover border-2 border-slate-200 ring-2 ring-white shadow-sm">
                </a>
            </div>
        @endauth

        @guest
            <div class="flex items-center gap-4">
                <a href="{{ route('login.view') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">Login</a>
            </div>
        @endguest
    </div>
</nav>

{{-- Admin Notifications Script --}}
@if(auth()->check() && auth()->user()->role_id == 1)
<script>
class RaiseHandNotifications {
    constructor() {
        this.isInitialized = false;
        this.pollInterval = null;
        this.currentCount = 0;
        this.badgeElement = null;
        this.notificationPermission = 'default';
        this.audioContext = null;
        
        console.log('🔔 Admin RaiseHandNotifications initialized');
        this.init();
    }

    async init() {
        if (this.isInitialized) return;

        this.badgeElement = document.getElementById('raiseHandBadge');
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        
        if (!this.badgeElement) {
            console.error('❌ Badge element not found, retrying...');
            setTimeout(() => this.init(), 2000);
            return;
        }
        
        if (!csrfToken) {
            console.error('❌ CSRF token not found');
            return;
        }

        const initialCountText = this.badgeElement.textContent.trim();
        this.currentCount = parseInt(initialCountText) || 0;

        this.initNotificationSound();
        await this.requestNotificationPermission();

        this.isInitialized = true;
        this.startPolling();
        
        console.log('✅ Admin notifications initialized with count:', this.currentCount);
    }

    initNotificationSound() {
        try {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        } catch (e) {
            console.log('⚠️ Web Audio API not supported');
        }
    }

    async requestNotificationPermission() {
        if (!('Notification' in window)) {
            console.log('⚠️ Browser does not support notifications');
            return;
        }

        if (Notification.permission === 'granted') {
            this.notificationPermission = 'granted';
        } else if (Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            this.notificationPermission = permission;
            
            if (permission === 'granted') {
                this.showWelcomeNotification();
            }
        }
    }

    showWelcomeNotification() {
        if (this.notificationPermission === 'granted') {
            new Notification('🔔 Sistem Notifikasi Admin Aktif', {
                body: 'Anda akan menerima notifikasi saat ada permintaan raise hand baru',
                icon: '/favicon.ico',
                tag: 'admin-welcome'
            });
        }
    }

    startPolling() {
        if (this.pollInterval) clearInterval(this.pollInterval);

        this.checkNotifications();
        this.pollInterval = setInterval(() => this.checkNotifications(), 3000);
    }

    async checkNotifications() {
        try {
            const response = await fetch('/admin/raise-hand/count', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            this.updateBadge(data.count || 0);

        } catch (error) {
            console.error('❌ Error checking notifications:', error);
        }
    }

    updateBadge(newCount) {
        if (!this.badgeElement) return;

        const isNewRequest = newCount > this.currentCount && this.currentCount >= 0;

        if (newCount > 0) {
            this.badgeElement.textContent = newCount;
            this.badgeElement.classList.remove('hidden');

            if (isNewRequest) {
                this.showBrowserNotification(newCount);
                this.playNotificationSound();
                this.flashTabTitle(newCount);
            }
        } else {
            this.badgeElement.classList.add('hidden');
        }

        this.currentCount = newCount;
    }

    showBrowserNotification(count) {
        if (this.notificationPermission !== 'granted') return;

        const title = count === 1 ? '🤚 Permintaan Bantuan Baru!' : `🤚 ${count} Permintaan Bantuan!`;
        const body = count === 1 ? 'Ada 1 intern yang membutuhkan bantuan' : `Total ${count} intern sedang menunggu bantuan`;

        const notification = new Notification(title, {
            body: body,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: 'admin-raise-hand',
            requireInteraction: true,
            vibrate: [200, 100, 200]
        });

        notification.onclick = function(event) {
            event.preventDefault();
            window.focus();
            window.location.href = '/admin/raise-hand';
            notification.close();
        };

        setTimeout(() => notification.close(), 10000);
    }

    playNotificationSound() {
        if (!this.audioContext) return;

        try {
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);
            
            oscillator.frequency.setValueAtTime(800, this.audioContext.currentTime);
            oscillator.frequency.setValueAtTime(600, this.audioContext.currentTime + 0.1);
            
            gainNode.gain.setValueAtTime(0.15, this.audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.3);
            
            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + 0.3);
        } catch (error) {
            console.log('⚠️ Could not play sound:', error);
        }
    }

    flashTabTitle(count) {
        const originalTitle = document.title;
        let flashCount = 0;

        const flashInterval = setInterval(() => {
            document.title = flashCount % 2 === 0 ? `🔴 ${count} PERMINTAAN BARU!` : originalTitle;
            flashCount++;

            if (flashCount >= 6) {
                clearInterval(flashInterval);
                document.title = originalTitle;
            }
        }, 500);
    }

    destroy() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
        if (this.audioContext) {
            this.audioContext.close();
        }
        this.isInitialized = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    window.adminRaiseHandNotifier = new RaiseHandNotifications();
});

document.addEventListener('visibilitychange', function() {
    if (window.adminRaiseHandNotifier && !document.hidden) {
        window.adminRaiseHandNotifier.checkNotifications();
    }
});

window.addEventListener('beforeunload', function() {
    if (window.adminRaiseHandNotifier) {
        window.adminRaiseHandNotifier.destroy();
    }
});
</script>
@endif

{{-- Assistant Admin Notifications Script --}}
@if(auth()->check() && auth()->user()->role_id == 6)
<script>
class AssistantAdminNotifications {
    constructor() {
        this.isInitialized = false;
        this.pollInterval = null;
        this.currentCount = 0;
        this.badgeElement = null;
        this.notificationPermission = 'default';
        this.audioContext = null;
        
        console.log('🔔 Assistant Admin Notifications initialized');
        this.init();
    }

    async init() {
        if (this.isInitialized) return;

        this.badgeElement = document.getElementById('assistantRaiseHandBadge');
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        
        if (!this.badgeElement) {
            console.error('[ASSISTANT] Badge element not found, retrying...');
            setTimeout(() => this.init(), 2000);
            return;
        }
        
        if (!csrfToken) {
            console.error('[ASSISTANT] CSRF token not found');
            return;
        }

        const initialCountText = this.badgeElement.textContent.trim();
        this.currentCount = parseInt(initialCountText) || 0;

        this.initNotificationSound();
        await this.requestNotificationPermission();

        this.isInitialized = true;
        this.startPolling();
        
        console.log('[ASSISTANT] Initialization complete with count:', this.currentCount);
    }

    initNotificationSound() {
        try {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        } catch (e) {
            console.log('[ASSISTANT] Web Audio API not supported');
        }
    }

    async requestNotificationPermission() {
        if (!('Notification' in window)) return;

        if (Notification.permission === 'granted') {
            this.notificationPermission = 'granted';
        } else if (Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            this.notificationPermission = permission;
            
            if (permission === 'granted') {
                this.showWelcomeNotification();
            }
        }
    }

    showWelcomeNotification() {
        if (this.notificationPermission === 'granted') {
            new Notification('🔔 Sistem Notifikasi Asisten Admin Aktif', {
                body: 'Anda akan menerima notifikasi saat ada permintaan raise hand baru',
                icon: '/favicon.ico',
                tag: 'assistant-welcome'
            });
        }
    }

    startPolling() {
        if (this.pollInterval) clearInterval(this.pollInterval);

        this.checkNotifications();
        this.pollInterval = setInterval(() => this.checkNotifications(), 3000);
    }

    async checkNotifications() {
        try {
            const response = await fetch('/raise-hand/count', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            this.updateBadge(data.count || 0);

        } catch (error) {
            console.error('[ASSISTANT] Error checking notifications:', error);
        }
    }

    updateBadge(newCount) {
        if (!this.badgeElement) return;

        const isNewRequest = newCount > this.currentCount && this.currentCount >= 0;

        if (newCount > 0) {
            this.badgeElement.textContent = newCount;
            this.badgeElement.classList.remove('hidden');

            if (isNewRequest) {
                this.showBrowserNotification(newCount);
                this.playNotificationSound();
                this.flashTabTitle(newCount);
            }
        } else {
            this.badgeElement.classList.add('hidden');
        }

        this.currentCount = newCount;
    }

    showBrowserNotification(count) {
        if (this.notificationPermission !== 'granted') return;

        const title = count === 1 ? '🤚 Permintaan Bantuan Baru!' : `🤚 ${count} Permintaan Bantuan!`;
        const body = count === 1 ? 'Ada 1 intern yang membutuhkan bantuan' : `Total ${count} intern sedang menunggu bantuan`;

        const notification = new Notification(title, {
            body: body,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: 'assistant-raise-hand',
            requireInteraction: true,
            vibrate: [200, 100, 200]
        });

        notification.onclick = function(event) {
            event.preventDefault();
            window.focus();
            window.location.href = '{{ route("assistant.raisehand.list") }}';
            notification.close();
        };

        setTimeout(() => notification.close(), 10000);
    }

    playNotificationSound() {
        if (!this.audioContext) return;

        try {
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);
            
            oscillator.frequency.setValueAtTime(800, this.audioContext.currentTime);
            oscillator.frequency.setValueAtTime(600, this.audioContext.currentTime + 0.1);
            
            gainNode.gain.setValueAtTime(0.15, this.audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.3);
            
            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + 0.3);
        } catch (error) {
            console.log('[ASSISTANT] Could not play sound:', error);
        }
    }

    flashTabTitle(count) {
        const originalTitle = document.title;
        let flashCount = 0;

        const flashInterval = setInterval(() => {
            document.title = flashCount % 2 === 0 ? `🔴 ${count} PERMINTAAN BARU!` : originalTitle;
            flashCount++;

            if (flashCount >= 6) {
                clearInterval(flashInterval);
                document.title = originalTitle;
            }
        }, 500);
    }

    destroy() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
        if (this.audioContext) {
            this.audioContext.close();
        }
        this.isInitialized = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    window.assistantAdminNotifier = new AssistantAdminNotifications();
});

document.addEventListener('visibilitychange', function() {
    if (window.assistantAdminNotifier && !document.hidden) {
        window.assistantAdminNotifier.checkNotifications();
    }
});

window.addEventListener('beforeunload', function() {
    if (window.assistantAdminNotifier) {
        window.assistantAdminNotifier.destroy();
    }
});
</script>
@endif