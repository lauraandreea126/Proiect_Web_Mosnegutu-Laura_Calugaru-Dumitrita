// js/client.js — logica paginii clientului (client.php)

document.addEventListener('DOMContentLoaded', function () {
    // Hamburger menu
    const btn = document.getElementById('hamburger-btn');
    const menu = document.getElementById('nav-menu');
    if (btn && menu) {
        btn.addEventListener('click', function () {
            menu.classList.toggle('active');
        });
    }

    // Toggle notificări
    const bell = document.getElementById('notif-bell');
    const dropdown = document.getElementById('notif-dropdown');
    const badge = document.getElementById('notif-badge');

    if (bell && dropdown) {
        bell.addEventListener('click', async function (e) {
            e.stopPropagation();
            const isHidden = dropdown.style.display === 'none' || dropdown.style.display === '';
            dropdown.style.display = isHidden ? 'block' : 'none';

            if (isHidden && badge) {
                try {
                    await fetch('api/notifications.php', { method: 'POST' });
                    badge.remove();
                } catch (error) {
                    console.error('Eroare la marcarea notificărilor ca citite:', error);
                }
            }
        });
    }

    // Închide dropdown la click în afară
    window.addEventListener('click', function () {
        if (dropdown) dropdown.style.display = 'none';
    });

    // Dark mode quick patch
    if (localStorage.getItem('theme') === 'dark') {
        document.querySelectorAll('.section-box-admin, .io-card, table, td, th, input').forEach(el => {
            el.style.setProperty('background-color', '#1e1e1e', 'important');
            el.style.setProperty('color', '#f5f5f5', 'important');
        });
    }
});

async function eliminaFavorit(actorId, actorName) {
    if (!confirm(`Sigur vrei să-l elimini pe "${actorName}" de la favorite?`)) return;

    try {
        const response = await fetch(`api/users.php?action=remove_favorite&actor_id=${actorId}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await response.json();

        if (response.ok) {
            const row = document.getElementById(`fav-row-${actorId}`);
            if (row) row.remove();

            const tbody = document.querySelector('.fav-list tbody');
            if (tbody && tbody.querySelectorAll('tr').length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; color:#888;">Nu ai adăugat încă niciun actor la favorite.</td></tr>';
            }
        } else {
            alert(data.message || 'Eroare la eliminarea actorului.');
        }
    } catch (error) {
        alert('A apărut o eroare de rețea la salvare.');
    }
}
