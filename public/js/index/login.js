// #FITUR LOGIN & SMART CSRF RECOVERY

// 1. Toggle Password Visibility
const togglePasswordBtn = document.getElementById('togglePassword3');
if (togglePasswordBtn) {
    togglePasswordBtn.addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            this.classList.toggle('fa-eye-slash');
        }
    });
}

// 2. Load Saved Credentials
document.addEventListener('DOMContentLoaded', function() {
    const savedUsername = localStorage.getItem('savedUsername');
    const savedPassword = localStorage.getItem('savedPassword');
    const usernameEl = document.getElementById('username');
    const passwordEl = document.getElementById('password');
    const rememberMeEl = document.getElementById('remember-me');

    if (savedUsername && usernameEl) {
        usernameEl.value = savedUsername;
        if (rememberMeEl) rememberMeEl.checked = true;
    }
    if (savedPassword && passwordEl) {
        passwordEl.value = savedPassword;
    }
});

// 3. Smart CSRF Token Auto-Refresh & Wakeup Recovery
let lastCsrfRefreshTime = Date.now();
let isRefreshingCsrf = false;

async function refreshCsrfToken() {
    if (isRefreshingCsrf) return null;
    isRefreshingCsrf = true;

    try {
        const response = await fetch('/csrf-token', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            cache: 'no-store'
        });

        if (response.ok) {
            const data = await response.json();
            if (data && data.csrf_token) {
                // Perbarui seluruh input _token pada form
                document.querySelectorAll('input[name="_token"]').forEach(input => {
                    input.value = data.csrf_token;
                });

                // Perbarui meta tag jika ada
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.setAttribute('content', data.csrf_token);
                }

                lastCsrfRefreshTime = Date.now();
                return data.csrf_token;
            }
        }
    } catch (err) {
        console.warn('Gagal memperbarui CSRF token otomatis:', err);
    } finally {
        isRefreshingCsrf = false;
    }
    return null;
}

// Deteksi saat laptop dihidupkan kembali / tab aktif setelah lama ditinggal (sleep / idle)
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        const idleDuration = Date.now() - lastCsrfRefreshTime;
        // Jika tab tidak aktif lebih dari 5 menit, perbarui token secara senyap
        if (idleDuration > 5 * 60 * 1000) {
            refreshCsrfToken();
        }
    }
});

window.addEventListener('focus', function() {
    const idleDuration = Date.now() - lastCsrfRefreshTime;
    if (idleDuration > 5 * 60 * 1000) {
        refreshCsrfToken();
    }
});

// Heartbeat berkala setiap 15 menit agar sesi tetap segar saat halaman terbuka
setInterval(function() {
    refreshCsrfToken();
}, 15 * 60 * 1000);

// 4. Form Submit Interceptor dengan Validasi Kesegaran Token
const loginForm = document.querySelector('form');
if (loginForm) {
    loginForm.addEventListener('submit', async function(event) {
        const rememberMe = document.getElementById('remember-me')?.checked;
        const username = document.getElementById('username')?.value;
        const password = document.getElementById('password')?.value;

        if (rememberMe) {
            localStorage.setItem('savedUsername', username || '');
            localStorage.setItem('savedPassword', password || '');
        } else {
            localStorage.removeItem('savedUsername');
            localStorage.removeItem('savedPassword');
        }

        // Jika halaman sudah terbuka > 10 menit sejak refresh terakhir, segarkan token dulu sebelum submit
        const idleDuration = Date.now() - lastCsrfRefreshTime;
        if (idleDuration > 10 * 60 * 1000) {
            event.preventDefault();
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Memproses...';
            }

            try {
                await refreshCsrfToken();
            } finally {
                // Submit form dengan token segar
                loginForm.submit();
            }
        }
    });
}