<?php
// ============================================================
// admin.php - panoul de administrare al aplicatiei awa
// vizibil doar pentru utilizatorii cu rol de admin, logati
// gestioneaza utilizatori, actori, surse de stiri, import/export
// ============================================================

// pornire sesiune (folosim folder local de sesiuni pentru proiect)
// creem un folder local pentru sesiuni daca nu exista
$sessionPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sessions';
if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0777, true);
}
// Forțăm PHP să salveze sesiunile în acest folder local din proiect
ini_set('session.save_path', $sessionPath);

session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();
require_once __DIR__ . '/../config/db.php';

// verificare autentificare administrator
$is_logged_in = (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

// initializam listele goale, pentru cazul in care nu e logat sau interogarea pica
$admins = [];
$clients = [];
$favorites = [];

// daca este admin logat, incarcam din baza de date toate datele necesare panoului
if ($is_logged_in) {
    try {
        // luam toti administratorii
        $stmtAdmin = $pdo->query("SELECT id, username, role FROM users WHERE username LIKE '%admin%' OR role = 'admin' ORDER BY id DESC");
        $admins = $stmtAdmin->fetchAll();

        // luam toti utilizatorii care nu sunt admin (clientii), inclusiv email-ul
        $stmtClient = $pdo->query("SELECT id, username, email, role FROM users WHERE username != 'admin' ORDER BY id DESC");
        $clients = $stmtClient->fetchAll();

        // luam toate actorii favoriti, asociati cu utilizatorul care i-a adaugat
        $stmtFav = $pdo->query("
            SELECT f.user_id, u.username, f.actor_id, a.name AS actor_name 
            FROM favorite_actors f 
            JOIN users u ON f.user_id = u.id 
            JOIN actors a ON f.actor_id = a.id 
            ORDER BY u.username ASC, a.name ASC
        ");
        $favorites = $stmtFav->fetchAll();
    } catch (PDOException $e) {
        $clients = [];
        $admins = [];
        $favorites = [];
    }
}
?>
<!-- ============================================================ -->
<!-- structura html a panoului de admin -->
<!-- ============================================================ -->
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panou Administrare Globală - AwA</title>
    <link rel="stylesheet" href="css/style.css?v=999">
    <script src="js/theme.js?v=999"></script>
    <style>
        /* stiluri specifice panoului de admin (containere, formulare, tabele, butoane) */
        .admin-container { max-width: 1000px; margin: 2rem auto; padding: 2rem; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-color); box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; color: #7c5e28; }
        .form-group input, .form-group textarea { width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-color); box-sizing: border-box; }
        .source-list { margin-top: 1rem; border-collapse: collapse; width: 100%; margin-bottom: 2rem; }
        .source-list th, .source-list td { border: 1px solid var(--border-color); padding: 10px; text-align: left; color: var(--text-color); }
        .source-list th { background-color: var(--bg-main); color: #7c5e28; font-family: Georgia, serif; }
        
        .btn-action {
            background: var(--bg-card); border: 1px solid #c5a059; color: #c5a059; padding: 8px 20px;
            cursor: pointer; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px;
            font-weight: 600; transition: all 0.3s ease; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;
        }
        .btn-action:hover { background: #c5a059; color: #fff; }
        .btn-delete { background: var(--bg-card); border: 1px solid #ff6384; color: #ff6384; padding: 5px 12px; font-size: 0.7rem; cursor: pointer; }
        .btn-delete:hover { background: #ff6384; color: #fff; }
        
        .section-box-admin { border: 1px solid var(--border-color); padding: 1.5rem; margin-bottom: 2.5rem; background: var(--bg-card); border-radius: 4px; }
        .section-box-admin h2 { font-family: Georgia, serif; color: #7c5e28; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-top: 0; }
        .search-row { display: flex; gap: 10px; margin-bottom: 1rem; }
        .search-row input { flex: 1; padding: 0.5rem; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-color); }
        
        .io-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-top: 1rem; }
        .io-card { border: 1px solid var(--border-color); padding: 1.5rem; background: var(--bg-card); border-radius: 4px; display: flex; flex-direction: column; justify-content: space-between; }
    </style>
<!-- stiluri suplimentare pentru afisarea corecta pe mobil a panoului de admin -->
<style>
@media (max-width: 768px) {
    /* rescrierea directa a containerului principal din Admin și Client pentru a elimina pixelii ficși */
    body, .admin-container, .client-container, div {
        max-width: 100% !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    
    /* Reparare titlu tăiat CONTROL PANEL */
    h1, .brand, h2 {
        font-size: 20px !important;
        white-space: normal !important;
        text-align: center !important;
        display: block !important;
        width: 100% !important;
    }

    /* Forțare brutală: orice elemente din header care stau pe orizontală trec pe verticală */
    header, nav, .nav-links, .nav-wrapper, ul {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        height: auto !important;
        padding: 5px 0 !important;
        margin: 0 !important;
    }

    /* Transformă TOATE link-urile din zona de sus în butoane lătite pe tot ecranul */
    header a, nav a, ul li a {
        display: block !important;
        width: 90% !important;
        margin: 5px auto !important;
        padding: 12px !important;
        text-align: center !important;
        background-color: #1e1e1e !important;
        color: #c5a059 !important; /* Text auriu */
        border: 1px solid #c5a059 !important;
        border-radius: 5px !important;
        box-sizing: border-box !important;
    }

    /* Ascunderea completă a punctelor sau liniilor de separare dintre link-uri */
    header li, nav li {
        list-style: none !important;
        display: block !important;
        width: 100% !important;
    }
    
    /* Rezolvarea butoanelor de Export care ies din ecran */
    .section-box-admin, .io-card, form {
        width: 100% !important;
        padding: 10px !important;
        box-sizing: border-box !important;
    }

    #hamburger-btn {
        display: block !important; /* Afișăm butonul doar pe mobil */
    }
    #nav-menu {
        display: none !important; /* Ascundem implicit meniul pe mobil */
        flex-direction: column !important;
        width: 100% !important;
        background-color: #1a1a1a !important;
        padding: 10px 0 !important;
        border-top: 1px solid #c5a059 !important;
    }
    #nav-menu.active {
        display: flex !important; /* Când are clasa 'active', meniul se deschide vertical */
    }
    #nav-menu a {
        display: block !important;
        width: 90% !important;
        margin: 5px auto !important;
        padding: 12px !important;
        text-align: center !important;
        border: 1px solid #c5a059 !important;
        color: #c5a059 !important;
        background: #252525 !important;
    }
}
</style>
<!-- stiluri pentru tabele si campuri de input, identice cu cele din index.php -->
<style>
    /* regulile de tabele și inputuri rămân neschimbate pe desktop */
    table, .table { border-collapse: collapse !important; width: 100% !important; }
    th, td { border: 1px solid #cccccc !important; padding: 10px 12px !important; }
    body.dark-theme th, body.dark-theme td { border: 1px solid #333333 !important; }

    /* Stil îmbunătățit pentru căsuțele de introducere date */
    input[type="text"], 
    input[type="email"], 
    input[type="password"], 
    input[type="url"],
    textarea, 
    select {
        border: 1px solid #b5b5b5 !important; /* Bordură fină vizibilă pe fundal alb */
        background-color: #ffffff !important;
        color: #333333 !important;
        padding: 10px 12px !important;
        border-radius: 5px !important;
        width: 100% !important;
        max-width: 400px !important; /* Limitează lățimea ca să nu se întindă pe tot ecranul pe desktop */
        box-sizing: border-box !important;
        transition: border-color 0.2s, box-shadow 0.2s !important;
        margin-top: 5px !important;
        margin-bottom: 15px !important;
        display: block !important;
    }

    /* Efect vizual când utilizatorul dă click în căsuță */
    input:focus, textarea:focus, select:focus {
        border-color: #c5a059 !important; /* Schimbare în culoarea aurie a temei */
        box-shadow: 0 0 5px rgba(197, 160, 89, 0.3) !important;
        outline: none !important;
    }

    /* Păstrăm adaptarea corectă și pentru modul întunecat */
    body.dark-theme input[type="text"], 
    body.dark-theme input[type="email"], 
    body.dark-theme input[type="password"], 
    body.dark-theme input[type="url"],
    body.dark-theme textarea, 
    body.dark-theme select {
        border: 1px solid #444444 !important;
        background-color: #1e1e1e !important;
        color: #ffffff !important;
    }

    /* ==========================================================================
       ZONA RESPONSIVE - SE ACTIVEAZĂ STRICT PE MOBIL (MAX-WIDTH: 768px)
       ========================================================================== */
    @media screen and (max-width: 768px) {
        /* Afișăm butonul hamburger DOAR pe mobil */
        #hamburger-btn, #hamburger-index-btn, #hamburger-client-btn {
            display: block !important;
            background: none !important;
            border: none !important;
            font-size: 28px !important;
            color: #c5a059 !important;
            cursor: pointer !important;
            float: right !important;
            margin: 10px !important;
        }

        /* Ascundem meniul orizontal implicit pe mobil ca să nu mai taie ecranul */
        #nav-menu, #nav-index-menu, #nav-client-menu, .nav-links, ul.nav-menu {
            display: none !important; 
            flex-direction: column !important;
            width: 100% !important;
            background-color: #1a1a1a !important;
            padding: 0 !important;
            margin: 0 !important;
            list-style: none !important;
        }

        /* Când utilizatorul apasă pe hamburger, meniul se deschide vertical sub header */
        #nav-menu.open, #nav-index-menu.open, #nav-client-menu.open, 
        #nav-menu.active, #nav-index-menu.active, #nav-client-menu.active {
            display: flex !important;
        }

        /* Transformăm link-urile în butoane mari de mobil, ușor de apăsat */
        #nav-menu a, #nav-index-menu a, #nav-client-menu a, .nav-links a, ul.nav-menu li a {
            display: block !important;
            width: 100% !important;
            padding: 15px !important;
            text-align: center !important;
            color: #c5a059 !important;
            border-bottom: 1px solid #222222 !important;
            box-sizing: border-box !important;
        }

        /* Containerele mari (containere, tabele, formulare) nu mai au voie să iasă din ecran */
        .container, .main-content, table, .table, form, .section-box-admin {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
    }

    /* Containerul principal care ține search-ul */
    .search-wrapper, form[action*="search"], form.search-form {
        display: flex !important; /* Păstrăm flex doar pentru aliniere */
        max-width: 650px; /* Lățime generoasă pe desktop */
        width: 100%;
        margin: 30px auto; /* Spațiere clară sus-jos */
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Umbră fină premium */
        border-radius: 6px;
        overflow: hidden;
    }

    /* Căsuța de text - o facem mai mare și mai înaltă */
    .search-wrapper input[type="text"], 
    form.search-form input[type="text"] {
        flex: 1;
        height: 50px; /* Înălțime mărită pentru impact vizual */
        padding: 0 20px; /* Spațiu generos pentru text în interior */
        font-size: 16px; /* Text mai mare, ușor de citit */
        border: 1px solid #c5a059;
        border-right: none; /* Să se lipească perfect de buton */
        border-radius: 6px 0 0 6px;
        outline: none;
        box-sizing: border-box;
    }

    /* Butonul - se potrivește la fix cu înălțimea inputului */
    .search-wrapper button, 
    form.search-form button,
    .search-wrapper input[type="submit"] {
        height: 50px; /* Aceeași înălțime ca inputul */
        padding: 0 30px; /* Buton mai lat, mai impunător */
        background-color: #c5a059; /* Culoarea aurie a temei */
        color: #ffffff;
        font-size: 15px;
        font-weight: bold;
        text-transform: uppercase;
        border: none;
        border-radius: 0 6px 6px 0;
        cursor: pointer;
        transition: background 0.2s;
        box-sizing: border-box;
    }

    .search-wrapper button:hover, 
    form.search-form button:hover {
        background-color: #b38f48; /* Efect discret la hover */
    }

</style>
</head>
<body>
    <!-- antetul panoului de admin, cu link inapoi la site si butonul de tema -->
    <header class="main-header" style="background: var(--bg-card); border-bottom: 1px solid var(--border-color); padding: 15px 0;">
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center;">
            <a href="index.php" class="logo" style="font-size: 24px; color: #222; text-decoration: none; font-family: Georgia, serif; letter-spacing: 2px;">AwA Control Panel</a>
            <button id="hamburger-btn" style="display: none; background: none; border: none; font-size: 30px; color: #c5a059; cursor: pointer; padding: 10px;">☰</button>
            <div id="nav-menu" class="nav-links" style="display: flex; align-items: center; gap: 20px;">
                <a href="index.php" style="color: #666; text-decoration: none; font-size: 0.85rem; font-weight: bold; letter-spacing: 1px;">ÎNAPOI LA SITE</a>
                <button id="toggle-theme" style="background: none; border: 1px solid #c5a059; border-radius: 50%; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #c5a059; font-size: 1.1em; transition: all 0.3s; margin: 0;">🌙</button>
            </div>
        </div>
    </header>

    <div class="admin-container">
        <!-- daca nu e logat, afisam formularele de login si inregistrare client -->
        <?php if (!$is_logged_in): ?>
            
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-family: Georgia, serif; color: #7c5e28; margin-bottom: 0.5rem;">Bun venit pe Platforma AwA</h1>
                <p style="color: #666; font-size: 0.95rem;">Selectează opțiunea dorită pentru a accesa serviciile noastre securizate.</p>
                
                <div style="margin-top: 1.5rem; display: flex; justify-content: center; gap: 10px;">
                    <button class="btn-action" onclick="comutaFormular('login')" id="btn-tab-login" style="background: #c5a059; color: #fff;">Autentificare (Admin / Client)</button>
                    <button class="btn-action" onclick="comutaFormular('register')" id="btn-tab-register">Creează Cont Nou Client</button>
                </div>
            </div>

            <div id="zona-login" class="section-box-admin" style="max-width: 500px; margin: 0 auto;">
                <h2 style="text-align: center; margin-bottom: 1.5rem;">Conectare în Cont</h2>
                <form method="POST" action="login.php">
                    <div class="form-group">
                        <label>Nume Utilizator (Username)</label>
                        <input type="text" name="username" required placeholder="ex: laura_admin sau client123">
                    </div>
                    <div class="form-group">
                        <label>Parolă</label>
                        <input type="password" name="password" required placeholder="••••••••">
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; gap: 8px; margin-top: 0.5rem;">
                        <input type="checkbox" id="show-pass-login" onclick="toggleVizibilitateParolaLogin()" style="width: auto; margin: 0;">
                        <label for="show-pass-login" style="margin: 0; font-weight: normal; font-size: 0.85rem; cursor: pointer; color: #666;">Afișează parola</label>
                    </div>
                    <button type="submit" class="btn-action" style="width: 100%; margin-top: 1rem;">Intră în cont</button>
                </form>
            </div>

            <div id="zona-register" class="section-box-admin" style="max-width: 500px; margin: 0 auto; display: none;">
                <h2 style="text-align: center; margin-bottom: 1.5rem; color: #c5a059;">Înregistrare Client Nou</h2>
                <form id="ajax-register-form" autocomplete="off">
                    <div class="form-group">
                        <label>Alege un Username nou</label>
                        <input type="text" id="reg-username" required placeholder="ex: ioana2026" autocomplete="none">
                    </div>
                    <div class="form-group">
                        <label>Adresă Email</label>
                        <input type="email" id="reg-email" required placeholder="ex: student@info.uaic.ro">
                    </div>
                    <div class="form-group">
                        <label>Setează o Parolă sigură</label>
                        <input type="password" id="reg-password" required placeholder="••••••••" autocomplete="new-password">
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; gap: 8px; margin-top: 0.5rem;">
                        <input type="checkbox" id="toggle-pw" onclick="toggleVizibilitateParola()" style="width: auto; margin: 0;">
                        <label for="toggle-pw" style="margin: 0; font-weight: normal; font-size: 0.85rem; cursor: pointer; color: #666;">Afișează parola</label>
                    </div>
                    <button type="submit" class="btn-action" style="width: 100%; margin-top: 1rem;">Finalizează Înregistrarea</button>
                </form>
                <p id="reg-msg" style="text-align: center; margin-top: 15px; font-weight: bold;"></p>
            </div>

            <!-- scripturi pentru formularele de login/inregistrare (cand nu e logat) -->
            <script>
            // arata/ascunde parola tastata la inregistrare
            function toggleVizibilitateParola() {
                const pwInput = document.getElementById('reg-password');
                if (pwInput) {
                    pwInput.type = pwInput.type === 'password' ? 'text' : 'password';
                }
            }

            // arata/ascunde parola tastata la login
            function toggleVizibilitateParolaLogin() {
                const p = document.querySelector('#zona-login input[name="password"]');
                if (p) {
                    p.type = p.type === 'password' ? 'text' : 'password';
                }
            }

            // comuta vizual intre formularul de login si cel de inregistrare
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

            // trimite datele de inregistrare prin ajax catre api si arata rezultatul
            document.getElementById('ajax-register-form').addEventListener('submit', async (e) => {
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
                        document.getElementById('ajax-register-form').reset();
                        // Reset input type back to password in case it was toggled
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
            </script>

        <?php else: ?>
            <!-- adminul este logat: afisam toate sectiunile de gestionare -->
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Panou de Control Global (Drepturi Depline)</h1>
                <a href="logout.php" class="btn-action">Deconectare Admin</a>
            </div>

            <!-- sectiunea de import/export date (csv pentru nominalizari, json/csv pentru utilizatori) -->
            <div class="section-box-admin">
                <h2>Gestionare Date (Import/Export)</h2>
                <div class="io-grid">
                    <div class="io-card">
                        <div>
                            <h3 style="margin-top:0; color:#7c5e28; font-family: Georgia, serif;">Export CSV</h3>
                            <p style="font-size: 0.85rem; color: #666; margin-bottom: 1.5rem;">Descarcă baza de date curentă pentru backup sau analiză externă.</p>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <a href="export_csv.php?target=nominations" class="btn-action" style="text-align:center;">Export Nominalizări</a>
                            <a href="export_csv.php?target=actors" class="btn-action" style="text-align:center;">Export Actori</a>
                        </div>
                    </div>
                    <div class="io-card">
                        <div>
                            <h3 style="margin-top:0; color:#7c5e28; font-family: Georgia, serif;">Import Nominalizări</h3>
                            <p style="font-size: 0.85rem; color: #666; margin-bottom: 1rem;">Încarcă un fișier CSV (format: year, category, nominee, production, won).</p>
                        </div>
                        <form action="import_data.php" method="POST" enctype="multipart/form-data">
                            <input type="file" name="csv_file" accept=".csv" required style="margin-bottom: 1rem; width: 100%; font-size: 0.85rem;">
                            <button type="submit" class="btn-action" style="width: 100%;">Apasă pentru Import</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- sectiunea cu listele de administratori, clienti si actorii lor favoriti -->
            <div class="section-box-admin">
                <h2>Grupuri de Utilizatori (Conturi Studenți)</h2>
                
                <h3>🛡️ Administratori Sistem</h3>
                <table class="source-list">
                    <thead><tr><th>ID Admin</th><th>Username</th><th>Rol</th></tr></thead>
                    <tbody>
                        <?php foreach ($admins as $a): ?>
                            <tr>
                                <td data-label="ID Admin"><?php echo $a['id']; ?></td>
                                <td data-label="Username" style="font-weight:bold;"><?php echo htmlspecialchars($a['username']); ?></td>
                                <td data-label="Rol"><span style="color: #c5a059; font-weight: bold;">ADMIN</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 10px;">
                    <h3 style="margin: 0;">👥 Toți Clienții Înregistrați</h3>
                    <div style="display: flex; gap: 10px;">
                        <a href="export_users.php?format=json" class="btn-action" target="_blank" style="padding: 6px 15px; font-size: 0.7rem;">Export JSON</a>
                        <a href="export_users.php?format=csv" class="btn-action" style="padding: 6px 15px; font-size: 0.7rem;">Export CSV</a>
                    </div>
                </div>
                <table class="source-list">
                    <thead><tr><th>ID Client</th><th>Username</th><th>Email</th><th>Rol în DB</th><th>Acțiuni</th></tr></thead>
                    <tbody>
                        <?php if (empty($clients)): ?>
                            <tr><td colspan="5" style="text-align:center; color:#888;">Nu există clienți înregistrați momentan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($clients as $c): ?>
                                <tr id="user-row-<?php echo $c['id']; ?>">
                                    <td data-label="ID Client"><?php echo $c['id']; ?></td>
                                    <td data-label="Username" style="font-weight:bold; color: #4bc0c0;"><?php echo htmlspecialchars($c['username']); ?></td>
                                    <td data-label="Email" style="font-size: 0.9rem; color: #555;"><?php echo htmlspecialchars($c['email'] ?? 'nespecificat'); ?></td>
                                    <td data-label="Rol în DB"><span style="color:#666; font-size:0.9rem;">client</span></td>
                                    <td data-label="Acțiuni"><button class="btn-delete" onclick="stergeClientDinAdmin(<?php echo $c['id']; ?>, '<?php echo htmlspecialchars($c['username']); ?>')">Șterge Cont</button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
<!-- mini-clasament cu cei mai populari 3 actori, calculat din lista de favorite -->
<div class="section-box-admin" style="margin-bottom: 30px;">
    <h3 style="color: #c5a059; margin-top: 0; text-align: center; font-family: serif; font-size: 1.6rem;">
        🥇 Top 3 Actori Preferați ai Clienților
    </h3>
    
    <div style="display: flex; gap: 15px; justify-content: space-between; margin-top: 20px; flex-wrap: wrap;">
        <?php
        // numaram aparitiile fiecarui actor din lista existenta de favorite
        $numaratoare_actori = [];
        if (!empty($favorites) && is_array($favorites)) {
            foreach ($favorites as $f) {
                $nume = $f['actor_name'];
                if (!isset($numaratoare_actori[$nume])) {
                    $numaratoare_actori[$nume] = 0;
                }
                $numaratoare_actori[$nume]++;
            }
            // sortam descrescator in functie de numarul de clienti care i-au adaugat
            arsort($numaratoare_actori);
        }

        // extragem primii 3 cei mai populari actori
        $top_3 = array_slice($numaratoare_actori, 0, 3, true);
        
        $badges = ['🥇 Locul 1', '🥈 Locul 2', '🥉 Locul 3'];
        $backgrounds = [
            'background: rgba(197, 160, 89, 0.12); border: 2px solid #c5a059;', // Locul 1 - Accent aurit AwA
            'background: rgba(255, 255, 255, 0.02); border: 1px solid #333333;', // Locul 2
            'background: rgba(255, 255, 255, 0.02); border: 1px solid #333333;'  // Locul 3
        ];

        // construim cate un card pentru fiecare dintre primii 3 actori
        $index = 0;
        if (!empty($top_3)) {
            foreach ($top_3 as $actor => $voturi) {
                ?>
                <div style="flex: 1; min-width: 200px; padding: 15px; border-radius: 6px; text-align: center; <?php echo $backgrounds[$index]; ?>">
                    <div style="font-size: 22px; margin-bottom: 5px;"><?php echo $badges[$index]; ?></div>
<h4 style="margin: 5px 0; font-size: 1.2rem; color: var(--text-color);"><?php echo htmlspecialchars($actor); ?></h4>
                    <small style="opacity: 0.8; color: #dfb76c;">Ales de <?php echo $voturi; ?> <?php echo ($voturi == 1) ? 'client' : 'clienți'; ?></small>
                </div>
                <?php
                $index++;
                if ($index >= 3) break;
            }
        }
        
        // daca nu avem destui actori (mai putin de 3), umplem restul podiumului elegant
        while ($index < 3) {
            ?>
            <div style="flex: 1; min-width: 200px; padding: 15px; border-radius: 6px; text-align: center; <?php echo $backgrounds[$index]; ?> opacity: 0.5;">
                <div style="font-size: 22px; margin-bottom: 5px;"><?php echo $badges[$index]; ?></div>
                <h4 style="margin: 5px 0; font-size: 1.2rem; color: #888888;">Nespecificat</h4>
                <small style="opacity: 0.5;">Fără selecții</small>
            </div>
            <?php
            $index++;
        }
        ?>
    </div>
</div>
                <!-- tabel detaliat cu toate asocierile client - actor favorit -->
                <h3 style="margin-top: 2.5rem;">❤️ Monitorizare Actori Favoriți</h3>
                <table class="source-list">
                    <thead>
                        <tr>
                            <th>ID Utilizator</th>
                            <th>Username Client</th>
                            <th>Nume Actor Favorit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($favorites)): ?>
                            <tr><td colspan="3" style="text-align:center; color:#888;">Nu există asocieri de favorite momentan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($favorites as $f): ?>
                                <tr>
                                    <td data-label="ID Utilizator"><?php echo htmlspecialchars($f['user_id']); ?></td>
                                    <td data-label="Username Client" style="font-weight:bold; color: #7c5e28;"><?php echo htmlspecialchars($f['username']); ?></td>
                                    <td data-label="Nume Actor Favorit" style="font-weight:bold; color: #c5a059;"><?php echo htmlspecialchars($f['actor_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- sectiunea de gestionare a catalogului de actori (adaugare/cautare/stergere) -->
            <div class="section-box-admin">
                <h2>Gestionare Catalog Actori</h2>
                <h3>➕ Adaugă un Actor Nou</h3>
                <form id="add-actor-form" style="margin-bottom: 2rem;">
                    <div class="form-group"><label>Nume Actor</label><input type="text" id="actor-name" required placeholder="ex: Cillian Murphy"></div>
                    <div class="form-group"><label>Biografie</label><textarea id="actor-bio" rows="3" required placeholder="Detalii biografice..."></textarea></div>
                    <div class="form-group"><label>URL Imagine</label><input type="url" id="actor-image-url" required placeholder="https://..."></div>
                    <button type="submit" class="btn-action">Salvează Actor</button>
                </form>
                <div id="actor-status-msg" style="font-weight:bold; margin-bottom: 1rem;"></div>

                <h3>🔍 Caută Actor pentru Eliminare</h3>
                <div class="search-row">
                    <input type="text" id="search-actor-admin" placeholder="Scrie numele actorului pe care vrei să-l ștergi...">
                    <button type="button" class="btn-action" onclick="cautaActorAdmin()">Caută</button>
                </div>
                <table class="source-list">
                    <thead><tr><th>ID</th><th>Nume</th><th>Biografie preview</th><th>Acțiuni</th></tr></thead>
                    <tbody id="actors-tbody-admin">
                        <tr><td colspan="4" style="text-align:center; color:#888;">Folosește căutarea de mai sus pentru a găsi și șterge un actor.</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- sectiunea de gestionare a surselor rss de stiri -->
            <div class="section-box-admin">
                <h2>Gestionare Știri & Surse RSS</h2>
                <h3>➕ Adaugă o Sursă Nouă de Știri (RSS)</h3>
                <form id="add-source-form" style="margin-bottom: 2rem;">
                    <div class="form-group"><label>Nume Sursă</label><input type="text" id="source-name" placeholder="ex: Hollywood Reporter" required></div>
                    <div class="form-group"><label>URL Flux RSS</label><input type="url" id="source-url" placeholder="https://..." required></div>
                    <button type="submit" class="btn-action">Adaugă Flux RSS</button>
                </form>
                <div id="source-status-msg" style="font-weight:bold; margin-bottom: 1rem;"></div>

                <h3>📋 Surse active de știri</h3>
                <table class="source-list">
                    <thead><tr><th>Nume Sursă</th><th>URL Flux</th><th>Acțiuni</th></tr></thead>
                    <tbody id="sources-tbody-admin"></tbody>
                </table>
            </div>

        <?php endif; ?>
    </div>

    <!-- ============================================================ -->
    <!-- scripturile panoului de admin: incarcare date, salvare, stergere -->
    <!-- ============================================================ -->
    <script>
    // la incarcarea paginii, daca e logat, pregatim listele si formularele
    document.addEventListener('DOMContentLoaded', () => {
        <?php if ($is_logged_in): ?>
        
        loadSourcesAdmin();
        
        // salvare sursa rss noua, trimisa prin formular
        document.getElementById('add-source-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const statusMsg = document.getElementById('source-status-msg');
            const name = document.getElementById('source-name').value;
            const url = document.getElementById('source-url').value;

            try {
                await fetch('manage_sources.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'create', name: name, url: url })
                });
                statusMsg.textContent = '✓ Sursa RSS a fost salvată!';
                statusMsg.style.color = '#4bc0c0';
                document.getElementById('add-source-form').reset();
                loadSourcesAdmin();
            } catch (err) {
                statusMsg.textContent = 'X Eroare salvat.';
            }
        });

        // salvare actor nou, trimis prin formular catre api
document.getElementById('add-actor-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const statusMsg = document.getElementById('actor-status-msg');
    const name = document.getElementById('actor-name').value;
    const bio = document.getElementById('actor-bio').value;
    const imageUrl = document.getElementById('actor-image-url').value; // luam valoarea url-ului imaginii

    try {
        const response = await fetch('api/actors.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            // trimitem image_url cu underscore, exact cum cere api/actors.php
            body: JSON.stringify({ name: name, bio: bio, image_url: imageUrl }) 
        });
        const data = await response.json();
        
        if(response.ok) {
            statusMsg.textContent = '✓ Actorul ' + name + ' a fost adăugat cu succes!';
            statusMsg.style.color = '#4bc0c0';
            document.getElementById('add-actor-form').reset();
        } else {
            statusMsg.textContent = 'X ' + (data.message || 'Eroare la salvare.');
            statusMsg.style.color = '#ff6384';
        }
    } catch (err) {
        statusMsg.textContent = 'X Eroare la comunicarea cu serverul.';
        statusMsg.style.color = '#ff6384';
    }
});
        <?php endif; ?>
    });

    // incarca si afiseaza in tabel toate sursele rss existente
    async function loadSourcesAdmin() {
        const tbody = document.getElementById('sources-tbody-admin');
        if(!tbody) return;
        try {
            const res = await fetch('manage_sources.php');
            const sources = await res.json();
            tbody.innerHTML = '';
            
            if(!sources || sources.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; color:#888;">Nu sunt surse active.</td></tr>';
                return;
            }

            sources.forEach(s => {
                tbody.innerHTML += `<tr><td data-label="Nume Sursă"><strong>${s.name}</strong></td><td data-label="URL Flux">${s.url}</td><td data-label="Acțiuni"><button class="btn-delete" onclick="stergeSursaAdmin(${s.id})">Elimină</button></td></tr>`;
            });
        } catch(e) { tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; color:red;">Eroare.</td></tr>'; }
    }

    // sterge o sursa rss, dupa confirmare
    async function stergeSursaAdmin(id) {
        if(!confirm("Elimini sursa?")) return;
        await fetch('manage_sources.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', id: id })
        });
        loadSourcesAdmin();
    }

    // cauta actori din catalog, in functie de textul introdus
    async function cautaActorAdmin() {
        const query = document.getElementById('search-actor-admin').value.trim().toLowerCase();
        const tbody = document.getElementById('actors-tbody-admin');
        if(!query) return;

        try {
            const response = await fetch('api/actors.php');
            const actors = await response.json();
            tbody.innerHTML = '';
            const rezultate = actors.filter(a => a.name.toLowerCase().includes(query));
            
            rezultate.forEach(a => {
                tbody.innerHTML += `<tr><td data-label="ID">${a.id}</td><td data-label="Nume"><strong>${a.name}</strong></td><td data-label="Biografie preview">${a.bio ? a.bio.substring(0,60)+'...' : ''}</td><td data-label="Acțiuni"><button class="btn-delete" onclick="stergeActorAdmin(${a.id})">Șterge</button></td></tr>`;
            });
        } catch (err) { }
    }

    // sterge un actor din catalog, dupa confirmare
    async function stergeActorAdmin(id) {
        if(!confirm("Sigur ștergi actorul?")) return;
        await fetch('api/actors.php?id=' + id, { method: 'DELETE' });
        cautaActorAdmin();
    }

    // sterge contul unui client, dupa confirmare, si elimina randul din tabel
    async function stergeClientDinAdmin(id, username) {
        if (!confirm(`Sigur vrei să ștergi contul lui "${username}"?`)) return;
        try {
            await fetch('api/users.php?action=delete_user&user_id=' + id, { method: 'DELETE' });
            const row = document.getElementById('user-row-' + id);
            if (row) row.remove();
        } catch (err) {
            alert("Eroare la eliminarea contului.");
        }
    }
    </script>
    <!-- fortare suplimentara a culorilor in dark mode, citite din localStorage -->
    <script>
    if(localStorage.getItem('theme') === 'dark') {
        document.querySelectorAll('.section-box-admin, .io-card, table, td, th, input').forEach(el => {
            el.style.setProperty('background-color', '#1e1e1e', 'important');
            el.style.setProperty('color', '#f5f5f5', 'important');
        });
    }
    </script>
    <!-- activeaza butonul hamburger pentru meniul mobil -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('hamburger-btn');
        const menu = document.getElementById('nav-menu');
        if(btn && menu) {
            btn.addEventListener('click', function() {
                menu.classList.toggle('active');
            });
        }
    });
    </script>
</body>
</html>