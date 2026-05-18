<?php
session_start();
// O simplă verificare pentru demo. În producție, login.php setează această sesiune.
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage News Sources</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: #fff; border: 1px solid var(--border-soft); }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; }
        .form-group input { width: 100%; padding: 0.5rem; border: 1px solid #ccc; }
        .source-list { margin-top: 2rem; border-collapse: collapse; width: 100%; }
        .source-list th, .source-list td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .btn-delete { background: #ff6384; color: #fff; border: none; padding: 5px 10px; cursor: pointer; }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <nav class="nav-wrapper">
                <a href="index.php" class="logo">AwA Admin</a>
                <a href="index.php">Înapoi la site</a>
            </nav>
        </div>
    </header>

    <div class="admin-container">
        <?php if (!$is_logged_in): ?>
            <h2>Autentificare Admin</h2>
            <form id="login-form">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="admin-user" required>
                </div>
                <div class="form-group">
                    <label>Parolă</label>
                    <input type="password" id="admin-pass" required>
                </div>
                <button type="submit" class="search-btn">Login</button>
            </form>
            <p id="login-msg"></p>
        <?php else: ?>
            <h2>Gestionare Surse Știri (RSS)</h2>
            <form id="add-source-form">
                <div class="form-group">
                    <label>Nume Sursă</label>
                    <input type="text" id="source-name" placeholder="ex: Hollywood Reporter" required>
                </div>
                <div class="form-group">
                    <label>URL RSS/Atom</label>
                    <input type="url" id="source-url" placeholder="https://..." required>
                </div>
                <button type="submit" class="search-btn">Adaugă Sursă</button>
            </form>

            <table class="source-list">
                <thead>
                    <tr>
                        <th>Nume</th>
                        <th>URL</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody id="sources-tbody">
                    <!-- Dinamic via AJAX -->
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
    if (document.getElementById('login-form')) {
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const response = await fetch('login.php', {
                method: 'POST',
                headers: { 'Content-Type: application/json' },
                body: JSON.stringify({
                    username: document.getElementById('admin-user').value,
                    password: document.getElementById('admin-pass').value
                })
            });
            const res = await response.json();
            if (res.success) {
                location.reload();
            } else {
                document.getElementById('login-msg').textContent = res.error;
            }
        });
    }

    if (document.getElementById('add-source-form')) {
        loadSources();

        document.getElementById('add-source-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const response = await fetch('manage_sources.php', {
                method: 'POST',
                headers: { 'Content-Type: application/json' },
                body: JSON.stringify({
                    action: 'create',
                    name: document.getElementById('source-name').value,
                    url: document.getElementById('source-url').value
                })
            });
            if (response.ok) {
                document.getElementById('add-source-form').reset();
                loadSources();
            }
        });
    }

    async function loadSources() {
        const response = await fetch('manage_sources.php');
        const sources = await response.json();
        const tbody = document.getElementById('sources-tbody');
        tbody.innerHTML = '';
        sources.forEach(s => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${s.name}</td>
                <td>${s.url}</td>
                <td><button class="btn-delete" onclick="deleteSource(${s.id})">Șterge</button></td>
            `;
            tbody.appendChild(tr);
        });
    }

    async function deleteSource(id) {
        if (!confirm('Sigur vrei să ștergi această sursă?')) return;
        await fetch('manage_sources.php', {
            method: 'POST',
            headers: { 'Content-Type: application/json' },
            body: JSON.stringify({ action: 'delete', id: id })
        });
        loadSources();
    }
    </script>
</body>
</html>
