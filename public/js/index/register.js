// #FITUR REGISTER

document.getElementById('togglePassword3').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    this.classList.toggle('fa-eye-slash');
});
document.getElementById('togglePassword4').addEventListener('click', function() {
    const passwordInput = document.getElementById('password_confirmation');
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    this.classList.toggle('fa-eye-slash');
});

var gpsSupport = document.getElementById("gps_support");
if ("geolocation" in navigator) {
    gpsSupport.value = 1;
} else {
    gpsSupport.value = 0;
}
