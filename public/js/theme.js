// public/js/theme.js
(function() {
    // Aplicăm clasa instant pe elementul html pentru a preveni flash-ul alb
    document.documentElement.classList.toggle('dark-theme', localStorage.getItem('theme') === 'dark');
})();

document.addEventListener('DOMContentLoaded', () => {
    const isDark = localStorage.getItem('theme') === 'dark';
    const toggleBtn = document.getElementById('toggle-theme');

    // Sincronizare body, html și text buton la încărcarea DOM-ului
    document.documentElement.classList.toggle('dark-theme', isDark);
    document.body.classList.toggle('dark-theme', isDark);
    document.body.classList.toggle('dark-mode', isDark);

    if (toggleBtn) {
        toggleBtn.textContent = isDark ? '☀️' : '🌙';
        toggleBtn.addEventListener('click', () => {
            const willBeDark = !document.documentElement.classList.contains('dark-theme');
            
            document.documentElement.classList.toggle('dark-theme', willBeDark);
            document.body.classList.toggle('dark-theme', willBeDark);
            document.body.classList.toggle('dark-mode', willBeDark);
            
            localStorage.setItem('theme', willBeDark ? 'dark' : 'light');
            toggleBtn.textContent = willBeDark ? '☀️' : '🌙';
        });
    }
});
