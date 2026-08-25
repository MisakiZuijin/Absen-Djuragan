document.addEventListener('DOMContentLoaded', function() {
    const message = document.getElementById('success-message');
    if (message) {
        setTimeout(() => {
            message.style.opacity = 0;
            setTimeout(() => message.remove(), 600); 
        }, 3000); 
    }
});