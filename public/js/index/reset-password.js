// #FITUR RESET PASSWORD
function attachPasswordToggle(triggerId, inputId, iconId) {
    const trigger = document.getElementById(triggerId);
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (!trigger || !input) return;

    trigger.addEventListener('click', function(e) {
        e.preventDefault();
        const type = input.type === 'password' ? 'text' : 'password';
        input.type = type;
        if (icon) {
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }
    });
}

attachPasswordToggle('btnTogglePassword1', 'password', 'togglePassword1');
attachPasswordToggle('btnTogglePassword2', 'confirmPassword', 'togglePassword2');

document.getElementById('passwordForm')?.addEventListener('submit', function(event) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const errorMessage = document.getElementById('error-message');

    if (password.length < 8) {
        event.preventDefault();
        if (errorMessage) {
            errorMessage.textContent = 'Password baru minimal harus 8 karakter.';
            errorMessage.classList.remove('hidden');
        }
        return;
    }

    if (password !== confirmPassword) {
        event.preventDefault(); 
        if (errorMessage) {
            errorMessage.textContent = 'Password dan Konfirmasi Password tidak sama.';
            errorMessage.classList.remove('hidden');
        }
        return;
    }

    if (errorMessage) {
        errorMessage.classList.add('hidden'); 
    }
});