// js/index.js — logica paginii principale (index.php)

document.addEventListener('DOMContentLoaded', function () {
    // Scroll butoane
    document.getElementById('scroll-to-top').addEventListener('click', () =>
        window.scrollTo({ top: 0, behavior: 'smooth' })
    );
    document.getElementById('scroll-to-bottom').addEventListener('click', () =>
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' })
    );

    // Hamburger menu
    const btn = document.getElementById('hamburger-btn');
    const menu = document.getElementById('nav-menu');
    if (btn && menu) {
        btn.addEventListener('click', function () {
            menu.classList.toggle('active');
        });
    }

    // Închide dropdown user la click în afară
    window.addEventListener('click', () => {
        const dropdown = document.getElementById('user-dropdown-menu');
        if (dropdown) dropdown.style.display = 'none';
    });
});

async function promptChangePassword() {
    const oldPassword = prompt('Te rugăm să introduci parola veche:');
    if (!oldPassword) return;
    const newPassword = prompt('Te rugăm să introduci parola nouă:');
    if (!newPassword) return;

    try {
        const response = await fetch('api/users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'change_password', old_password: oldPassword, new_password: newPassword })
        });
        const data = await response.json();
        if (response.ok) {
            alert(data.message || 'Parola a fost schimbată cu succes!');
        } else {
            alert('Eroare: ' + (data.message || 'A apărut o problemă la schimbarea parolei.'));
        }
    } catch (error) {
        alert('A apărut o eroare de rețea.');
    }
}

function toggleUserDropdown(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('user-dropdown-menu');
    if (dropdown) {
        dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
    }
}

async function forteazaSalvareFavorite() {
    const profilActor = document.querySelector('#actor-profile [data-actor-id]');
    const actorId = profilActor ? profilActor.getAttribute('data-actor-id') : null;

    if (!actorId || actorId.trim() === '') {
        alert('Te rugăm să selectezi un actor valid');
        return;
    }

    try {
        const response = await fetch('api/users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add_favorite', actor_id: parseInt(actorId) })
        });
        const data = await response.json();
        if (response.ok || response.status === 201) {
            alert('Actorul a fost adăugat la favorite cu succes!');
        } else {
            alert(data.message || 'Eroare la adăugarea în favorite.');
        }
    } catch (error) {
        alert('A apărut o eroare de rețea la salvare.');
    }
}
