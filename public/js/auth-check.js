function checkAuth() {
    // If we're already on the login page, no need to check
    if (window.location.pathname === '/index.php' || window.location.pathname === '/') {
        return;
    }

    fetch('/api/check-auth.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(response => {
            if (!response.ok) {
                window.location.href = '/index.php';
            }
        })
        .catch(() => {
            window.location.href = '/index.php';
        });
}

// Check on page load
checkAuth();

// Check when visibility changes (tab focus)
document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        checkAuth();
    }
});