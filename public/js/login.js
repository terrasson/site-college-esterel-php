document.addEventListener('DOMContentLoaded', function() {
    const errorMessage = document.querySelector('.error-message');
    
    // Cache le message d'erreur au démarrage
    if (errorMessage) {
        errorMessage.style.display = 'none';
    }
});

function logout() {
    fetch('/api/logout.php', {
        method: 'POST',
        credentials: 'same-origin'
    }).then(() => {
        window.location.href = '/index.php';
    });
} 