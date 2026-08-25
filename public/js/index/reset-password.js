// #FITUR RESET PASSWORD
document.getElementById('togglePassword1').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    this.classList.toggle('fa-eye-slash');
});
document.getElementById('togglePassword2').addEventListener('click', function() {
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
    confirmPasswordInput.type = type;
    this.classList.toggle('fa-eye-slash');
});
document.getElementById('passwordForm').addEventListener('submit', function(event) {
    var password = document.getElementById('password').value;
    var confirmPassword = document.getElementById('confirmPassword').value;
    var errorMessage = document.getElementById('error-message');

    if (password !== confirmPassword) {
        event.preventDefault(); 
        errorMessage.classList.remove('hidden'); 
    } else {
        errorMessage.classList.add('hidden'); 
    }
});