// #FITUR LOGIN

document.getElementById('togglePassword3').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    this.classList.toggle('fa-eye-slash');
});

document.addEventListener('DOMContentLoaded', function() {
    const savedUsername = localStorage.getItem('savedUsername');
    const savedPassword = localStorage.getItem('savedPassword');
    if (savedUsername) {
        document.getElementById('username').value = savedUsername;
    }
    if (savedPassword) {
        document.getElementById('password').value = savedPassword;
    }
});

document.querySelector('form').addEventListener('submit', function(event) {
    const rememberMe = document.getElementById('remember-me').checked;
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    if (rememberMe) {
        localStorage.setItem('savedUsername', username);
        localStorage.setItem('savedPassword', password);
    } else {
        localStorage.removeItem('savedUsername');
        localStorage.removeItem('savedPassword');
    }
});