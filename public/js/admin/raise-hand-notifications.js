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
        this.lastLatestId = 0;
        this.lastLatestMessageId = 0;
        this.isFirstRun = true;
        this.pollInterval = null;
        this.pollIntervalMs = (typeof window !== 'undefined' && window.__raiseHandPollIntervalMs && window.__raiseHandPollIntervalMs >= 2000)
            ? window.__raiseHandPollIntervalMs
            : 5000; // Polling status bantuan setiap 5 detik (responsif)
        this.isFetching = false;
        this.titleFlashInterval = null;
        this.originalDocumentTitle = document.title;
        this.notificationPermission = 'default';
        this.repeatInterval = null;
        this.repeatIntervalMs = 10000; // Pengulangan bunyi setiap 10 detik khusus untuk permintaan kondisi URGENT
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
        } else if (window.location.pathname.startsWith('/admin')) {
            this.role = 'admin';
            this.badgeElement = null;
            this.pollUrl = '/admin/raise-hand/count';
            this.targetUrl = '/admin/raise-hand';
        } else if (window.location.pathname.startsWith('/assistant-admin')) {
            this.role = 'assistant';
            this.badgeElement = null;
            this.pollUrl = '/raise-hand/count';
            this.targetUrl = '/assistant-admin/raise-hand/list';
        } else {
            // Neither badge nor admin prefix found on this page
            return;
        }

        // Read initial count from HTML badge rendered by server if available
        if (this.badgeElement) {
            const initialText = this.badgeElement.textContent.trim();
            this.currentCount = parseInt(initialText, 10) || 0;
        }

        // Setup audio & permission
        this.initAudio();
        this.setupNotificationPermission();

        // Check if disabled by PopupSetting
        if (window.__raiseHandNotificationsEnabled === false) {
            this.isInitialized = true;
            return;
        }

        if (window.__raiseHandPollIntervalMs && window.__raiseHandPollIntervalMs >= 2000) {
            this.pollIntervalMs = window.__raiseHandPollIntervalMs;
        }

        // Start polling (akan memeriksa kondisi urgent pada poll pertama)
        this.isInitialized = true;
        this.startPolling();
    }

    hasActiveRaiseHand() {
        return (this.currentCount > 0) || (this.urgentCount > 0);
    }

    initAudio() {
        this.isAudioUnlocked = false;

        // Listener satu kali saat pengguna berinteraksi pertama kali dengan halaman
        const unlockOnUserGesture = () => {
            if (this.isAudioUnlocked) return;
            this.isAudioUnlocked = true;
            this.hideAudioNotice();

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
            const latestId = typeof data.latest_id === 'number' ? data.latest_id : 0;
            const latestMessageId = typeof data.latest_message_id === 'number' ? data.latest_message_id : 0;
            const latestRequest = data.latest_request || null;

            this.handleCountUpdate(newCount, urgentCount, latestId, latestMessageId, latestRequest);
        } catch (error) {
            // Silently handle ordinary polling network glitches
        } finally {
            this.isFetching = false;
        }
    }

    handleCountUpdate(newCount, urgentCount = 0, latestId = 0, latestMessageId = 0, latestRequest = null) {
        if (!this.badgeElement) {
            this.badgeElement = document.getElementById(this.role === 'admin' ? 'raiseHandBadge' : 'assistantRaiseHandBadge');
        }

        const isNewArrival = !this.isFirstRun && (
            (newCount > this.currentCount) ||
            (latestId > 0 && latestId > this.lastLatestId) ||
            (latestMessageId > 0 && latestMessageId > this.lastLatestMessageId)
        );
        const diff = Math.max(1, newCount - this.currentCount);

        // Update badge text and visibility (menampilkan total seluruh antrean aktif)
        if (this.badgeElement) {
            if (newCount > 0) {
                this.badgeElement.textContent = newCount > 99 ? '99+' : newCount;
                this.badgeElement.classList.remove('hidden');
            } else {
                this.badgeElement.textContent = '0';
                this.badgeElement.classList.add('hidden');
            }
        }

        // PENGULANGAN BUNYI: HANYA untuk permintaan yang masuk KONDISI URGENT.
        if (urgentCount > 0) {
            this.startSoundRepeat(urgentCount);
        } else {
            this.stopSoundRepeat();
        }

        // Trigger alert effects on NEW requests
        if (isNewArrival) {
            this.playNotificationSound();
            this.showDesktopNotification(diff, newCount, latestRequest);
            this.flashTabTitle(diff, newCount);
            this.animateBadge();

            // Tampilkan in-app popup toast kartu jika tidak sedang berada di halaman antrean
            if (!window.location.pathname.includes('/raise-hand') && !window.location.pathname.includes('/raisehand')) {
                this.showRaiseHandToast(latestRequest, diff, newCount);
            }
        }

        this.currentCount = newCount;
        this.urgentCount = urgentCount;
        if (latestId > 0) this.lastLatestId = Math.max(this.lastLatestId, latestId);
        if (latestMessageId > 0) this.lastLatestMessageId = Math.max(this.lastLatestMessageId, latestMessageId);
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
        notice.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-50 rounded-full shadow-2xl flex items-center gap-2 cursor-pointer transition-all duration-300 text-sm animate-bounce font-sans';
        notice.style.cssText = 'background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff; padding: 10px 18px; box-shadow: 0 10px 25px rgba(217, 119, 6, 0.4); font-weight: 600;';
        notice.innerHTML = `
            <svg style="width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 2;" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072M12 6v12m-3.5-3.5L5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L8.5 14.5z"/>
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

    showDesktopNotification(diff, total, latestRequest = null) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;

        const roleTitle = this.role === 'assistant' ? 'Asisten Admin' : 'Admin';
        const sender = latestRequest?.sender_name ? ` dari ${latestRequest.sender_name}` : '';
        const title = diff === 1 ? `🤚 Permintaan Bantuan Baru${sender}!` : `🤚 ${diff} Permintaan Bantuan Baru!`;
        const body = latestRequest?.reason ? latestRequest.reason : (total === 1 ? 'Ada 1 peserta magang yang membutuhkan bantuan' : `Total ${total} peserta sedang menunggu bantuan`);

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

    showRaiseHandToast(latestRequest, diff, total) {
        const sender = latestRequest ? (latestRequest.sender_name || 'Peserta Magang') : 'Peserta Magang';
        const school = latestRequest ? (latestRequest.school || '') : '';
        const reason = latestRequest ? (latestRequest.reason || 'Membutuhkan bantuan/tanggapan') : 'Membutuhkan bantuan/tanggapan';
        const type = latestRequest ? (latestRequest.type || 'question') : 'question';
        const typeLabel = type === 'presentation' ? 'Presentasi' : (type === 'new_task' ? 'Tugas Baru' : 'Bantuan');
        const truncated = reason.length > 75 ? reason.substring(0, 72) + '...' : reason;

        let container = document.getElementById('admin-global-floating-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'admin-global-floating-toast-container';
            container.className = 'fixed top-5 right-5 flex flex-col gap-2.5 max-w-sm w-[92vw] sm:w-[380px] pointer-events-none';
            container.style.cssText = 'z-index: 99999999 !important;';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto rounded-2xl p-3.5 transform transition-all duration-300 translate-y-[-10px] opacity-0 flex flex-col gap-2.5 font-sans';
        toast.style.cssText = 'background: #ffffff; border: 1px solid #bfdbfe; box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.1); color: #1e293b;';

        toast.innerHTML = `
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); display: flex; align-items: center; justify-content: center; color: #ffffff; flex-shrink: 0; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);">
                        <svg style="width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 2;" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3.5M3 16.5h18" />
                        </svg>
                    </div>
                    <div style="min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #1d4ed8; background-color: #dbeafe; padding: 1px 7px; border-radius: 9999px; border: 1px solid #bfdbfe;">🤚 ${this.escapeHtml(typeLabel)}</span>
                            <span style="font-size: 11px; color: #94a3b8;">Baru saja</span>
                        </div>
                        <h4 style="font-size: 13px; font-weight: 700; color: #0f172a; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 220px;">${this.escapeHtml(sender)}${school ? ` <span style="font-size: 11px; font-weight: normal; color: #64748b;">• ${this.escapeHtml(school)}</span>` : ''}</h4>
                    </div>
                </div>
                <button type="button" class="btn-close-toast" style="color: #94a3b8; background: transparent; border: none; padding: 4px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" title="Tutup" onmouseover="this.style.color='#475569'; this.style.backgroundColor='#f1f5f9';" onmouseout="this.style.color='#94a3b8'; this.style.backgroundColor='transparent';">
                    <svg style="width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2;" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div style="font-size: 12px; color: #334155; background-color: #f8fafc; padding: 8px 12px; border-radius: 8px; border: 1px solid #f1f5f9; word-break: break-word; line-height: 1.5;">
                "${this.escapeHtml(truncated)}"
            </div>
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; padding-top: 6px; border-top: 1px solid #f1f5f9;">
                <button type="button" class="btn-close-toast" style="font-size: 12px; font-weight: 600; color: #64748b; background-color: #f1f5f9; border: 1px solid #e2e8f0; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#e2e8f0'; this.style.color='#334155';" onmouseout="this.style.backgroundColor='#f1f5f9'; this.style.color='#64748b';">
                    Tutup
                </button>
                <a href="${this.targetUrl || '/admin/raise-hand'}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #ffffff; background: linear-gradient(135deg, #2563eb, #1d4ed8); padding: 6px 14px; border-radius: 8px; text-decoration: none; box-shadow: 0 4px 8px rgba(37, 99, 235, 0.25); transition: all 0.2s;" onmouseover="this.style.opacity='0.9'; this.style.transform='scale(1.02)';" onmouseout="this.style.opacity='1'; this.style.transform='scale(1)';">
                    <span>Lihat Antrean</span>
                    <svg style="width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2.5;" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
            </div>
        `;

        container.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-[-10px]', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        });

        const removeToast = () => {
            toast.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                toast.remove();
                if (container.children.length === 0) {
                    container.remove();
                }
            }, 300);
        };

        toast.querySelectorAll('.btn-close-toast').forEach(btn => {
            btn.addEventListener('click', removeToast);
        });

        let dismissTimeout = setTimeout(removeToast, 8000);
        toast.addEventListener('mouseenter', () => clearTimeout(dismissTimeout));
        toast.addEventListener('mouseleave', () => {
            dismissTimeout = setTimeout(removeToast, 3500);
        });
    }

    escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
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

        this.repeatInterval = setInterval(() => {
            if (this.urgentCount > 0) {
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

// =========================================================================
// SISTEM NOTIFIKASI SUARA & CHAT GANTI JAM REAL-TIME (GLOBAL SEMUA HALAMAN)
// =========================================================================
class GantiJamChatNotificationManager {
    constructor() {
        this.pollUrl = window.__unreadGantiJamChatUrl || '/admin/persetujuan-ganti-jam/unread-chats';
        this.pollInterval = null;
        this.pollIntervalMs = (typeof window !== 'undefined' && window.__raiseHandPollIntervalMs && window.__raiseHandPollIntervalMs >= 2000)
            ? window.__raiseHandPollIntervalMs
            : 5000; // Polling chat ganti jam
        this.isFetching = false;
        this.lastUnreadCount = 0;
        this.lastNoteId = 0;
        this.isFirstRun = true;
        this.audioContextInstance = null;
        this.isInitialized = false;
        this.sessionChatSoundLoopInterval = null;
        this.sessionLoopIntervalMs = 5000; // Loop per 5 detik sekali untuk chat ganti jam (sesi maupun pendaftaran)
        this.activeUnreadSessionIds = [];
        this.activeUnreadRegIds = [];
    }

    init() {
        if (this.isInitialized) return;

        // Check if disabled by PopupSetting
        if (window.__raiseHandNotificationsEnabled === false) {
            this.isInitialized = true;
            return;
        }

        if (window.__raiseHandPollIntervalMs && window.__raiseHandPollIntervalMs >= 2000) {
            this.pollIntervalMs = window.__raiseHandPollIntervalMs;
        }

        // Hanya aktif untuk Admin / Super Admin (memiliki URL polling atau di prefix /admin)
        const isAdminAccess = !!window.__unreadGantiJamChatUrl || window.location.pathname.startsWith('/admin');
        if (!isAdminAccess) return;

        this.initAudioContext();
        this.isInitialized = true;
        this.startPolling();
    }

    initAudioContext() {
        this.isAudioUnlocked = false;

        const unlock = () => {
            this.isAudioUnlocked = true;
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!this.audioContextInstance && AudioCtx) {
                    this.audioContextInstance = new AudioCtx();
                }
                if (this.audioContextInstance && this.audioContextInstance.state === 'suspended') {
                    this.audioContextInstance.resume().catch(() => {});
                }
            } catch (e) {}

            ['click', 'touchstart', 'keydown'].forEach(evt => {
                document.removeEventListener(evt, unlock);
            });
        };

        ['click', 'touchstart', 'keydown'].forEach(evt => {
            document.addEventListener(evt, unlock, { once: true, passive: true });
        });
    }

    startPolling() {
        if (this.pollInterval) clearInterval(this.pollInterval);

        // Polling pertama 2 detik setelah halaman dimuat
        setTimeout(() => {
            this.checkForUpdates();
        }, 2000);

        this.pollInterval = setInterval(() => {
            this.checkForUpdates();
        }, this.pollIntervalMs);
    }

    async checkForUpdates() {
        if (this.isFetching) return;
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

            const url = window.__unreadGantiJamChatUrl || this.pollUrl;
            const res = await fetch(url, {
                method: 'GET',
                headers: headers,
                credentials: 'same-origin'
            });

            if (!res.ok) return;

            const data = await res.json();
            this.handleChatUpdate(data);
        } catch (e) {
            // Silently ignore background polling network errors
        } finally {
            this.isFetching = false;
        }
    }

    handleChatUpdate(data) {
        const newCount = typeof data.unread_count === 'number' ? data.unread_count : 0;
        const latestNote = data.latest_note;
        const newLatestId = latestNote ? latestNote.id : 0;

        const isNewArrival = !this.isFirstRun && (
            newCount > this.lastUnreadCount || (newLatestId > 0 && newLatestId > this.lastNoteId)
        );

        // Update dot indikator chat di sidebar menu
        this.updateSidebarDot(newCount);

        // Update pending approval badge di sidebar (total pendaftaran pending + sesi pending)
        if (typeof data.total_pending_count === 'number') {
            this.updateSidebarPendingBadge(data.total_pending_count);
        }

        // Siarkan custom event untuk halaman spesifik seperti admin/ganti-jam/index.blade.php
        window.dispatchEvent(new CustomEvent('ganti-jam-chat-update', { detail: data }));

        // Identifikasi pesan belum dibaca dari SESI GANTI JAM maupun PENDAFTARAN GANTI JAM
        const sessionIds = Array.isArray(data.session_ids) ? data.session_ids : [];
        const registrationIds = Array.isArray(data.registration_ids) ? data.registration_ids : [];
        const unreadSessionCount = typeof data.unread_session_count === 'number'
            ? data.unread_session_count
            : sessionIds.length;
        const unreadRegCount = typeof data.unread_reg_count === 'number'
            ? data.unread_reg_count
            : registrationIds.length;

        this.activeUnreadSessionIds = sessionIds;
        this.activeUnreadRegIds = registrationIds;

        // Jika ada chat belum dibaca dari sesi ganti jam yang sudah dimulai ATAU pendaftaran, aktifkan loop suara per 5 detik
        const hasUnreadChat = (unreadSessionCount > 0 && sessionIds.length > 0) || (unreadRegCount > 0 && registrationIds.length > 0);
        if (hasUnreadChat) {
            this.startSessionChatSoundLoop();
        } else {
            this.stopSessionChatSoundLoop();
        }

        if (isNewArrival) {
            // Jika loop suara belum aktif, bunyikan 1x
            if (!hasUnreadChat) {
                this.playChatChimeIfAllowed();
            }

            // Tampilkan toast notifikasi jika admin sedang berada di luar halaman ganti jam
            if (!window.location.pathname.includes('/persetujuan-ganti-jam')) {
                this.showChatToast(latestNote, newCount);
            }
        }

        this.lastUnreadCount = newCount;
        if (newLatestId > 0) {
            this.lastNoteId = Math.max(this.lastNoteId, newLatestId);
        }
        this.isFirstRun = false;
    }

    startSessionChatSoundLoop() {
        if (this.sessionChatSoundLoopInterval) {
            return; // Loop sudah aktif
        }

        // Bunyikan langsung 1x saat terdeteksi
        if (!this.isFirstRun) {
            this.playChatChimeIfAllowed();
        }

        // Ulangi bunyi setiap 5 detik sekali (loop per 5 detik) sampai dibaca/dibuka
        this.sessionChatSoundLoopInterval = setInterval(() => {
            const hasUnreadSession = this.activeUnreadSessionIds && this.activeUnreadSessionIds.length > 0;
            const hasUnreadReg = this.activeUnreadRegIds && this.activeUnreadRegIds.length > 0;
            if (hasUnreadSession || hasUnreadReg) {
                this.playChatChimeIfAllowed();
            } else {
                this.stopSessionChatSoundLoop();
            }
        }, this.sessionLoopIntervalMs);
    }

    stopSessionChatSoundLoop() {
        if (this.sessionChatSoundLoopInterval) {
            clearInterval(this.sessionChatSoundLoopInterval);
            this.sessionChatSoundLoopInterval = null;
        }
    }

    playChatChimeIfAllowed() {
        // =========================================================================
        // ATURAN PRIORITAS: JIKA ADA RAISE HAND AKTIF, NOTIF SUARA CHAT DI-DISABLE!
        // "namun yang di utamakan adalah suara dari raisehand, jika ada raisehand
        // maka notif dari chat ganti jam akan di disable atau di nonaktifkan sampai
        // raisehand selesai"
        // =========================================================================
        const raiseHandMgr = window.raiseHandNotificationManager;
        const isRaiseHandActive = raiseHandMgr && (
            (typeof raiseHandMgr.hasActiveRaiseHand === 'function' && raiseHandMgr.hasActiveRaiseHand()) ||
            (raiseHandMgr.currentCount > 0) ||
            (raiseHandMgr.urgentCount > 0)
        );

        if (!isRaiseHandActive) {
            // Tidak ada raise hand aktif -> bunyikan suara notif chat ganti jam
            this.playChatChime();
        }
    }

    playChatChime() {
        try {
            // 1. Prioritaskan Web Audio Context jika sudah aktif/running dari gesture pengguna
            if (this.audioContextInstance && this.audioContextInstance.state === 'running') {
                this.executeBubbleTones(this.audioContextInstance);
                return;
            }

            // Jika AudioContext ada tapi suspended dan sudah ada user gesture, coba resume
            if (this.audioContextInstance && this.audioContextInstance.state === 'suspended' && this.isAudioUnlocked) {
                this.audioContextInstance.resume().then(() => {
                    this.executeBubbleTones(this.audioContextInstance);
                }).catch(() => {});
                return;
            }

            // 2. Fallback aman ke HTML5 Audio element (/sounds/chat-pop.wav)
            // HTML5 Audio tidak memicu error merah AudioContext di console Chrome
            const audio = new Audio('/sounds/chat-pop.wav');
            audio.volume = 0.75;
            const playPromise = audio.play();
            if (playPromise !== undefined) {
                playPromise.then(() => {
                    this.isAudioUnlocked = true;
                }).catch(() => {
                    // Autoplay dicegah oleh browser sampai interaksi pertama user di halaman (silent catch)
                });
            }
        } catch (e) {
            // Silent catch agar browser tetap bersih dan tidak error
        }
    }

    executeBubbleTones(ctx) {
        try {
            const now = ctx.currentTime;
            // Bubble Pop Nada 1: Sweep pitch 400Hz -> 1300Hz
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(400, now);
            osc1.frequency.exponentialRampToValueAtTime(1300, now + 0.08);

            gain1.gain.setValueAtTime(0.35, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.15);

            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.15);

            // Bubble Ripple Nada 2: Sweep pitch 750Hz -> 1600Hz
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(750, now + 0.05);
            osc2.frequency.exponentialRampToValueAtTime(1600, now + 0.11);

            gain2.gain.setValueAtTime(0.22, now + 0.05);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.18);

            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.05);
            osc2.stop(now + 0.18);
        } catch (e) {}
    }

    escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    showChatToast(latestNote, totalUnread) {
        const sender = latestNote ? (latestNote.sender_name || 'Pemagang') : 'Pemagang';
        const msg = latestNote ? (latestNote.message || 'Pesan baru dalam sesi ganti jam') : 'Pesan baru dalam sesi ganti jam';
        const truncated = msg.length > 75 ? msg.substring(0, 72) + '...' : msg;

        let container = document.getElementById('admin-global-floating-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'admin-global-floating-toast-container';
            container.className = 'fixed top-5 right-5 flex flex-col gap-2.5 max-w-sm w-[92vw] sm:w-[380px] pointer-events-none';
            container.style.cssText = 'z-index: 99999999 !important;';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto rounded-2xl p-3.5 transform transition-all duration-300 translate-y-[-10px] opacity-0 flex flex-col gap-2.5 font-sans';
        toast.style.cssText = 'background: #ffffff; border: 1px solid #fed7aa; box-shadow: 0 10px 25px -5px rgba(234, 88, 12, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.1); color: #1e293b;';
        
        toast.innerHTML = `
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #f97316, #ea580c); display: flex; align-items: center; justify-content: center; color: #ffffff; flex-shrink: 0; box-shadow: 0 4px 10px rgba(234, 88, 12, 0.3);">
                        <svg style="width: 18px; height: 18px; fill: currentColor;" viewBox="0 0 24 24">
                            <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12z"/>
                            <path d="M7 9h10v2H7zm0-3h10v2H7z"/>
                        </svg>
                    </div>
                    <div style="min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #c2410c; background-color: #ffedd5; padding: 1px 7px; border-radius: 9999px; border: 1px solid #fed7aa;">Ganti Jam</span>
                            <span style="font-size: 11px; color: #94a3b8;">Baru saja</span>
                        </div>
                        <h4 style="font-size: 13px; font-weight: 700; color: #0f172a; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 220px;">${this.escapeHtml(sender)}</h4>
                    </div>
                </div>
                <button type="button" class="btn-close-toast" style="color: #94a3b8; background: transparent; border: none; padding: 4px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" title="Tutup" onmouseover="this.style.color='#475569'; this.style.backgroundColor='#f1f5f9';" onmouseout="this.style.color='#94a3b8'; this.style.backgroundColor='transparent';">
                    <svg style="width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2;" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div style="font-size: 12px; color: #334155; background-color: #f8fafc; padding: 8px 12px; border-radius: 8px; border: 1px solid #f1f5f9; word-break: break-word; line-height: 1.5;">
                "${this.escapeHtml(truncated)}"
            </div>
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; padding-top: 6px; border-top: 1px solid #f1f5f9;">
                <button type="button" class="btn-close-toast" style="font-size: 12px; font-weight: 600; color: #64748b; background-color: #f1f5f9; border: 1px solid #e2e8f0; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#e2e8f0'; this.style.color='#334155';" onmouseout="this.style.backgroundColor='#f1f5f9'; this.style.color='#64748b';">
                    Tutup
                </button>
                <a href="/admin/persetujuan-ganti-jam" style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #ffffff; background: linear-gradient(135deg, #f97316, #ea580c); padding: 6px 14px; border-radius: 8px; text-decoration: none; box-shadow: 0 4px 8px rgba(234, 88, 12, 0.25); transition: all 0.2s;" onmouseover="this.style.opacity='0.9'; this.style.transform='scale(1.02)';" onmouseout="this.style.opacity='1'; this.style.transform='scale(1)';">
                    <span>Buka Chat</span>
                    <svg style="width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2.5;" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
            </div>
        `;

        container.appendChild(toast);

        // Animasi masuk
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-[-10px]', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        });

        const removeToast = () => {
            toast.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                toast.remove();
                if (container.children.length === 0) {
                    container.remove();
                }
            }, 300);
        };

        toast.querySelectorAll('.btn-close-toast').forEach(btn => {
            btn.addEventListener('click', removeToast);
        });

        // Auto dismiss setelah 7.5 detik (pause bila kursor di atasnya)
        let dismissTimeout = setTimeout(removeToast, 7500);
        toast.addEventListener('mouseenter', () => clearTimeout(dismissTimeout));
        toast.addEventListener('mouseleave', () => {
            dismissTimeout = setTimeout(removeToast, 3500);
        });
    }

    updateSidebarDot(unreadCount) {
        const dot = document.getElementById('sidebar-ganti-jam-chat-dot');
        if (dot) {
            if (unreadCount > 0) {
                dot.classList.remove('hidden');
            } else {
                dot.classList.add('hidden');
            }
        }
    }

    updateSidebarPendingBadge(totalPending) {
        const badge = document.getElementById('sidebar-ganti-jam-pending-badge');
        if (badge) {
            if (totalPending > 0) {
                badge.textContent = totalPending > 99 ? '99+' : totalPending;
                badge.classList.remove('hidden');
            } else {
                badge.textContent = '0';
                badge.classList.add('hidden');
            }
        }
    }

    destroy() {
        this.stopSessionChatSoundLoop();
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
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

    function initManagers() {
        if (!window.__raiseHandNotificationManagerInstance) {
            const manager = new RaiseHandNotificationManager();
            manager.init();
            window.__raiseHandNotificationManagerInstance = manager;
            window.raiseHandNotificationManager = manager;
            // Backward-compatible global references
            window.adminRaiseHandNotifier = manager;
            window.assistantAdminNotifier = manager;
        }

        if (!window.__gantiJamChatNotificationManagerInstance) {
            const chatManager = new GantiJamChatNotificationManager();
            chatManager.init();
            window.__gantiJamChatNotificationManagerInstance = chatManager;
            window.gantiJamChatNotifier = chatManager;
            window.playChatNotificationSound = function() {
                chatManager.playChatChime();
            };
        }
    }

    // Class aliases on window for backward compatibility
    window.RaiseHandNotifications = RaiseHandNotificationManager;
    window.AssistantAdminNotifications = RaiseHandNotificationManager;
    window.GantiJamChatNotifications = GantiJamChatNotificationManager;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initManagers);
    } else {
        initManagers();
    }

    document.addEventListener('visibilitychange', function() {
        if (window.__raiseHandNotificationManagerInstance && !document.hidden) {
            window.__raiseHandNotificationManagerInstance.checkForUpdates();
        }
        if (window.__gantiJamChatNotificationManagerInstance && !document.hidden) {
            window.__gantiJamChatNotificationManagerInstance.checkForUpdates();
        }
    });

    window.addEventListener('beforeunload', function() {
        if (window.__raiseHandNotificationManagerInstance) {
            window.__raiseHandNotificationManagerInstance.destroy();
        }
        if (window.__gantiJamChatNotificationManagerInstance) {
            window.__gantiJamChatNotificationManagerInstance.destroy();
        }
    });
})();