<?php
session_start();
// verificare sesiune
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage News Sources</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/app.js"></script>
    <style>
        .admin-container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: var(--bg-card); border: 1px solid var(--border-soft); color: var(--text-main); }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: var(--text-main); }
        .form-group input { width: 100%; padding: 0.5rem; border: 1px solid var(--border-soft); background: var(--bg-card); color: var(--text-main); }
        .source-list { margin-top: 2rem; border-collapse: collapse; width: 100%; }
        .source-list th, .source-list td { border: 1px solid var(--border-soft); padding: 8px; text-align: left; color: var(--text-main); }
        
        /* Butoane stilizate: fundal alb, text/bordura aurie, auriu la hover */
        .btn-action {
            background: #fff;
            border: 1px solid var(--accent-gold);
            color: var(--accent-gold);
            padding: 8px 20px;
            cursor: pointer;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            font-weight: 600;
            transition: all 0.3s ease;
            border-radius: 2px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .btn-action:hover {
            background: var(--accent-gold);
            color: #fff;
        }

        .btn-delete {
            padding: 5px 12px;
            font-size: 0.65rem;
        }

        .import-btn {
            width: 100%;
            padding: 12px;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <nav class="nav-wrapper">
                <a href="index.php" class="logo">AwA Admin</a>
                <div style="display: flex; align-items: center;">
                    <a href="index.php" style="margin-right: 1rem;">Înapoi la site</a>
                    <button id="theme-toggle" class="theme-btn" title="Schimbă tema">🌙</button>
                </div>
            </nav>
        </div>
    </header>

    <div class="admin-container">
        <?php if (!$is_logged_in): ?>
            <h2>Autentificare Admin</h2>
            <form id="login-form" method="POST" action="login.php">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="admin-user" name="username" required>
                </div>
                <div class="form-group">
                    <label>Parolă</label>
                    <input type="password" id="admin-pass" name="password" required>
                </div>
                <button type="submit" class="search-btn">Login</button>
            </form>
            <p id="login-msg"></p>
        <?php else: ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>Panou Administrare</h2>
                <a href="logout.php" class="btn-action" style="height: 40px;">Logout</a>
            </div>

            <section style="margin-bottom: 3rem; padding-bottom: 2rem; border-bottom: 2px solid var(--border-soft);">
                <h2>Gestionare Date (Import/Export)</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 1.5rem;">
                    <div style="padding: 1.5rem; background: rgba(0,0,0,0.02); border: 1px solid var(--border-soft); border-radius: 4px;">
                        <h3 style="margin-bottom: 1rem;">Export CSV</h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem;">Descarcă baza de date curentă pentru backup sau analiză externă.</p>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <a href="export_csv.php?target=nominations" class="btn-action">Export Nominalizări</a>
                            <a href="export_csv.php?target=actors" class="btn-action">Export Actori</a>
                        </div>
                    </div>

                    <div style="padding: 1.5rem; background: rgba(0,0,0,0.02); border: 1px solid var(--border-soft); border-radius: 4px;">
                        <h3 style="margin-bottom: 1rem;">Import Nominalizări</h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">Încarcă un fișier CSV (format: year, category, nominee, production, won).</p>
                        <form action="import_data.php" method="POST" enctype="multipart/form-data">
                            <input type="file" name="csv_file" accept=".csv" required style="margin-bottom: 1rem; width: 100%; font-size: 0.8rem;">
                            <button type="submit" class="btn-action import-btn">Apasă pentru Import</button>
                        </form>
                        <?php if (isset($_GET['import']) && $_GET['import'] === 'success'): ?>
                            <p style="color: #4bc0c0; font-weight: bold; font-size: 0.85rem; margin-top: 10px;">✓ Import realizat cu succes!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

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

            <div id="source-status-msg" style="margin-top: 1rem; font-weight: bold; min-height: 1.2rem;"></div>

            <div style="margin-top: 2rem;">
                <h3>Surse Active</h3>
                <table class="source-list hidden" id="sources-table">
                    <thead>
                        <tr>
                            <th>Nume</th>
                            <th>URL</th>
                            <th>Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody id="sources-tbody">
                        <!-- dinamic via ajax -->
                    </tbody>
                </table>
                <p id="no-sources-msg" class="hidden" style="padding: 1rem; background: rgba(0,0,0,0.05); text-align: center; border-radius: 4px;">Nu există surse configurate.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function showStatus(msg, isError = false) {
        const el = document.getElementById('source-status-msg');
        if (!el) return;
        el.textContent = msg;
        el.style.color = isError ? '#ff6384' : '#4bc0c0';
        setTimeout(() => { el.textContent = ''; }, 3000);
    }

    async function loadSources() {
        const tbody = document.getElementById('sources-tbody');
        const noSourcesMsg = document.getElementById('no-sources-msg');
        const table = document.getElementById('sources-table');
        if (!tbody) return;

        try {
            const response = await fetch('manage_sources.php');
            if (!response.ok) throw new Error('Eroare server');
            
            const sources = await response.json();
            tbody.innerHTML = '';
            
            if (!sources || sources.length === 0) {
                noSourcesMsg.classList.remove('hidden');
                table.classList.add('hidden');
                return;
            }

            noSourcesMsg.classList.add('hidden');
            table.classList.remove('hidden');

            sources.forEach(s => {
                const tr = document.createElement('tr');
                const safeName = escapeHTML(s.name);
                const safeUrl = escapeHTML(s.url);
                const safeId = parseInt(s.id);
                
                tr.innerHTML = `
                    <td>${safeName}</td>
                    <td style="font-size: 0.8rem; color: #888; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${safeUrl}</td>
                    <td><button class="btn-action btn-delete" onclick="deleteSource(${safeId})">Șterge</button></td>
                `;
                tbody.appendChild(tr);
            });
        } catch (error) {
            console.error('Eroare loadSources:', error);
            showStatus('Nu am putut incarca sursele.', true);
        }
    }

    async function deleteSource(id) {
        if (!confirm('Sigur vrei să ștergi această sursă?')) return;
        try {
            const response = await fetch('manage_sources.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', id: id })
            });
            if (response.ok) {
                showStatus('Sursă ștearsă.');
                loadSources();
            } else {
                showStatus('Eroare la ștergere.', true);
            }
        } catch (err) {
            showStatus('Eroare rețea.', true);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const msgEl = document.getElementById('login-msg');
                try {
                    const response = await fetch('login.php', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            username: document.getElementById('admin-user').value,
                            password: document.getElementById('admin-pass').value
                        })
                    });
                    const res = await response.json();
                    if (res.success) {
                        location.href = 'admin.php';
                    } else {
                        msgEl.textContent = res.error || 'Eroare autentificare';
                    }
                } catch (err) {
                    msgEl.textContent = 'Eroare de conexiune.';
                }
            });
        }

        const addForm = document.getElementById('add-source-form');
        if (addForm) {
            loadSources();
            addForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const name = document.getElementById('source-name').value;
                const url = document.getElementById('source-url').value;

                try {
                    const response = await fetch('manage_sources.php', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            action: 'create',
                            name: name,
                            url: url
                        })
                    });
                    if (response.ok) {
                        showStatus('Sursă adăugată!');
                        addForm.reset();
                        loadSources();
                    } else {
                        const res = await response.json();
                        showStatus(res.error || 'Eroare la adăugare.', true);
                    }
                } catch (err) {
                    showStatus('Eroare rețea.', true);
                }
            });
        }
    });
    </script>
</body>
</html>
