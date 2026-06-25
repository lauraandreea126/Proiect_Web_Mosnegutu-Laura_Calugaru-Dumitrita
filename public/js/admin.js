// js/admin.js — logica paginii de administrare (admin.php)

document.addEventListener('DOMContentLoaded', () => {
    // Hamburger menu
    const btn = document.getElementById('hamburger-btn');
    const menu = document.getElementById('nav-menu');
    if (btn && menu) {
        btn.addEventListener('click', function () {
            menu.classList.toggle('active');
        });
    }

    // Dark mode quick patch pentru tabele
    if (localStorage.getItem('theme') === 'dark') {
        document.querySelectorAll('.section-box-admin, .io-card, table, td, th, input').forEach(el => {
            el.style.setProperty('background-color', '#1e1e1e', 'important');
            el.style.setProperty('color', '#f5f5f5', 'important');
        });
    }
});

function toggleVizibilitateParola() {
    const pwInput = document.getElementById('reg-password');
    if (pwInput) {
        pwInput.type = pwInput.type === 'password' ? 'text' : 'password';
    }
}

function toggleVizibilitateParolaLogin() {
    const p = document.querySelector('#zona-login input[name="password"]');
    if (p) {
        p.type = p.type === 'password' ? 'text' : 'password';
    }
}

function comutaFormular(tip) {
    const fLogin = document.getElementById('zona-login');
    const fRegister = document.getElementById('zona-register');
    const btnLogin = document.getElementById('btn-tab-login');
    const btnRegister = document.getElementById('btn-tab-register');

    if (tip === 'login') {
        fLogin.style.display = 'block';
        fRegister.style.display = 'none';
        btnLogin.style.backgroundColor = '#c5a059';
        btnLogin.style.color = '#fff';
        btnRegister.style.backgroundColor = '';
        btnRegister.style.color = '#c5a059';
    } else {
        fLogin.style.display = 'none';
        fRegister.style.display = 'block';
        btnLogin.style.backgroundColor = '';
        btnLogin.style.color = '#c5a059';
        btnRegister.style.backgroundColor = '#c5a059';
        btnRegister.style.color = '#fff';
    }
}

// Înregistrare AJAX
const ajaxForm = document.getElementById('ajax-register-form');
if (ajaxForm) {
    ajaxForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const msg = document.getElementById('reg-msg');
        const username = document.getElementById('reg-username').value;
        const email = document.getElementById('reg-email').value;
        const password = document.getElementById('reg-password').value;

        try {
            const res = await fetch('api/register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, email, password })
            });
            const data = await res.json();
            if (data.success) {
                msg.style.color = '#4bc0c0';
                msg.textContent = '✓ ' + data.message;
                ajaxForm.reset();
                document.getElementById('reg-password').type = 'password';
                document.getElementById('toggle-pw').checked = false;
                setTimeout(() => comutaFormular('login'), 2000);
            } else {
                msg.style.color = '#ff6384';
                msg.textContent = 'X ' + (data.error || 'Eroare la înregistrare.');
            }
        } catch (err) {
            msg.style.color = '#ff6384';
            msg.textContent = 'X Eroare de rețea.';
        }
    });
}

// Adăugare Actor
const addActorForm = document.getElementById('add-actor-form');
if (addActorForm) {
    addActorForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const statusMsg = document.getElementById('actor-status-msg');
        const name = document.getElementById('actor-name').value;
        const bio = document.getElementById('actor-bio').value;
        const imageUrl = document.getElementById('actor-image-url').value;

        try {
            const response = await fetch('api/actors.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, bio, image_url: imageUrl })
            });
            const data = await response.json();
            if (response.ok) {
                statusMsg.textContent = '✓ Actorul ' + name + ' a fost adăugat cu succes!';
                statusMsg.style.color = '#4bc0c0';
                addActorForm.reset();
            } else {
                statusMsg.textContent = 'X ' + (data.message || 'Eroare la salvare.');
                statusMsg.style.color = '#ff6384';
            }
        } catch (err) {
            statusMsg.textContent = 'X Eroare la comunicarea cu serverul.';
            statusMsg.style.color = '#ff6384';
        }
    });
}

// Adăugare Sursă RSS
const addSourceForm = document.getElementById('add-source-form');
if (addSourceForm) {
    addSourceForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const statusMsg = document.getElementById('source-status-msg');
        const name = document.getElementById('source-name').value;
        const url = document.getElementById('source-url').value;

        try {
            await fetch('manage_sources.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'create', name, url })
            });
            statusMsg.textContent = '✓ Sursa RSS a fost salvată!';
            statusMsg.style.color = '#4bc0c0';
            addSourceForm.reset();
            loadSourcesAdmin();
        } catch (err) {
            statusMsg.textContent = 'X Eroare salvat.';
        }
    });

    loadSourcesAdmin();
}

async function loadSourcesAdmin() {
    const tbody = document.getElementById('sources-tbody-admin');
    if (!tbody) return;
    try {
        const res = await fetch('manage_sources.php');
        const sources = await res.json();
        tbody.innerHTML = '';
        if (!sources || sources.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; color:#888;">Nu sunt surse active.</td></tr>';
            return;
        }
        sources.forEach(s => {
            tbody.innerHTML += `<tr><td data-label="Nume Sursă"><strong>${s.name}</strong></td><td data-label="URL Flux">${s.url}</td><td data-label="Acțiuni"><button class="btn-delete" onclick="stergeSursaAdmin(${s.id})">Elimină</button></td></tr>`;
        });
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; color:red;">Eroare.</td></tr>';
    }
}

async function stergeSursaAdmin(id) {
    if (!confirm('Elimini sursa?')) return;
    await fetch('manage_sources.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id })
    });
    loadSourcesAdmin();
}

async function cautaActorAdmin() {
    const query = document.getElementById('search-actor-admin').value.trim().toLowerCase();
    const tbody = document.getElementById('actors-tbody-admin');
    if (!query) return;
    try {
        const response = await fetch('api/actors.php');
        const actors = await response.json();
        tbody.innerHTML = '';
        actors.filter(a => a.name.toLowerCase().includes(query)).forEach(a => {
            tbody.innerHTML += `<tr><td data-label="ID">${a.id}</td><td data-label="Nume"><strong>${a.name}</strong></td><td data-label="Biografie preview">${a.bio ? a.bio.substring(0, 60) + '...' : ''}</td><td data-label="Acțiuni"><button class="btn-delete" onclick="stergeActorAdmin(${a.id})">Șterge</button></td></tr>`;
        });
    } catch (err) {}
}

async function stergeActorAdmin(id) {
    if (!confirm('Sigur ștergi actorul?')) return;
    await fetch('api/actors.php?id=' + id, { method: 'DELETE' });
    cautaActorAdmin();
}

async function stergeClientDinAdmin(id, username) {
    if (!confirm(`Sigur vrei să ștergi contul lui "${username}"?`)) return;
    try {
        await fetch('api/users.php?action=delete_user&user_id=' + id, { method: 'DELETE' });
        const row = document.getElementById('user-row-' + id);
        if (row) row.remove();
    } catch (err) {
        alert('Eroare la eliminarea contului.');
    }
}
