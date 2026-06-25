<?php
// public/client.php
// ============================================================
// client.php - spatiul personal al utilizatorului logat (client)
// afiseaza actorii favoriti si notificarile primite
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

// protectie simpla: daca nu e logat deloc, il trimitem pe prima pagina
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/../config/db.php';

$user_id = $_SESSION['user_id'];
$favorites = [];
$unreadCount = 0;
$allNotifications = [];

try {
    // luam actorii favoriti ai utilizatorului curent, folosind interogare pregatita (prepared statement)
    $stmt = $pdo->prepare("
        SELECT a.id, a.name AS nominee, a.bio, a.image_url 
        FROM favorite_actors f
        JOIN actors a ON f.actor_id = a.id
        WHERE f.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
    $favorites = $stmt->fetchAll();

    // numaram notificarile necitite ale utilizatorului
    $stmtNotif = $pdo->prepare("SELECT COUNT(*) FROM notificari WHERE user_id = ? AND citit = 0");
    $stmtNotif->execute([$user_id]);
    $unreadCount = $stmtNotif->fetchColumn();

    // luam ultimele 10 notificari (citite si necitite) pentru lista derulanta
    $stmtAllNotif = $pdo->prepare("SELECT * FROM notificari WHERE user_id = ? ORDER BY data_creare DESC LIMIT 10");
    $stmtAllNotif->execute([$user_id]);
    $allNotifications = $stmtAllNotif->fetchAll();
} catch (PDOException $e) {
    $favorites = [];
    $unreadCount = 0;
    $allNotifications = [];
}
?>
<!-- ============================================================ -->
<!-- structura html a paginii spatiul meu personal -->
<!-- ============================================================ -->
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spațiul Meu Personal - AwA</title>
    <link rel="stylesheet" href="css/style.css?v=999">
    <script src="js/theme.js?v=999"></script>
    <style>
        /* stiluri specifice acestei pagini (container, tabel de favorite, butoane) */
        .client-container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-color); box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .fav-list { margin-top: 2rem; border-collapse: collapse; width: 100%; }
        .fav-list th, .fav-list td { border: 1px solid var(--border-color); padding: 12px; text-align: left; color: var(--text-color); }
        .fav-list th { background-color: var(--bg-main); color: #7c5e28; font-family: Georgia, serif; }
        
        .btn-action {
            background: var(--bg-card);
            border: 1px solid #c5a059;
            color: #c5a059;
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
        .btn-action:hover { background: #c5a059; color: #fff; }
        .btn-delete { padding: 5px 12px; font-size: 0.65rem; }
    </style>
<!-- stiluri suplimentare pentru afisarea corecta pe mobil -->
<style>
@media (max-width: 768px) {
    /* rescrierea directa a containerului principal din Admin și Client pentru a elimina pixelii ficși */
    body, .admin-container, .client-container, div {
        max-width: 100%;
        width: 100%;
        box-sizing: border-box;
    }
    
    /* Reparare titlu tăiat CONTROL PANEL */
    h1, .brand, h2 {
        font-size: 20px;
        white-space: normal;
        text-align: center;
        display: block;
        width: 100%;
    }

    /* Forțare brutală: orice elemente din header care stau pe orizontală trec pe verticală */
    header, nav, .nav-links, .nav-wrapper, ul {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: auto;
        padding: 5px 0;
        margin: 0;
    }

    /* Transformă TOATE link-urile din zona de sus în butoane lătite pe tot ecranul */
    header a, nav a, ul li a {
        display: block;
        width: 90%;
        margin: 5px auto;
        padding: 12px;
        text-align: center;
        background-color: #1e1e1e;
        color: #c5a059; /* Text auriu */
        border: 1px solid #c5a059;
        border-radius: 5px;
        box-sizing: border-box;
    }

    /* Ascunderea completă a punctelor sau liniilor de separare dintre link-uri */
    header li, nav li {
        list-style: none;
        display: block;
        width: 100%;
    }
    
    /* Rezolvarea butoanelor de Export care ies din ecran */
    .section-box-admin, .io-card, form {
        width: 100%;
        padding: 10px;
        box-sizing: border-box;
    }

    #hamburger-btn {
        display: block; /* Afișăm butonul doar pe mobil */
    }
    #nav-menu {
        display: none; /* Ascundem implicit meniul pe mobil */
        flex-direction: column;
        width: 100%;
        background-color: #1a1a1a;
        padding: 10px 0;
        border-top: 1px solid #c5a059;
    }
    #nav-menu.active {
        display: flex; /* Când are clasa 'active', meniul se deschide vertical */
    }
    #nav-menu a {
        display: block;
        width: 90%;
        margin: 5px auto;
        padding: 12px;
        text-align: center;
        border: 1px solid #c5a059;
        color: #c5a059;
        background: #252525;
    }
}
</style>
<!-- stiluri pentru tabele si campuri de input, identice cu cele din index.php/admin.php -->
<style>
    /* regulile de tabele și inputuri rămân neschimbate pe desktop */
    table, .table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #cccccc; padding: 10px 12px; }
    body.dark-theme th, body.dark-theme td { border: 1px solid #333333; }

    /* Stil îmbunătățit pentru căsuțele de introducere date */
    input[type="text"], 
    input[type="email"], 
    input[type="password"], 
    input[type="url"],
    textarea, 
    select {
        border: 1px solid #b5b5b5; /* Bordură fină vizibilă pe fundal alb */
        background-color: #ffffff;
        color: #333333;
        padding: 10px 12px;
        border-radius: 5px;
        width: 100%;
        max-width: 400px; /* Limitează lățimea ca să nu se întindă pe tot ecranul pe desktop */
        box-sizing: border-box;
        transition: border-color 0.2s, box-shadow 0.2s;
        margin-top: 5px;
        margin-bottom: 15px;
        display: block;
    }

    /* Efect vizual când utilizatorul dă click în căsuță */
    input:focus, textarea:focus, select:focus {
        border-color: #c5a059; /* Schimbare în culoarea aurie a temei */
        box-shadow: 0 0 5px rgba(197, 160, 89, 0.3);
        outline: none;
    }

    /* Păstrăm adaptarea corectă și pentru modul întunecat */
    body.dark-theme input[type="text"], 
    body.dark-theme input[type="email"], 
    body.dark-theme input[type="password"], 
    body.dark-theme input[type="url"],
    body.dark-theme textarea, 
    body.dark-theme select {
        border: 1px solid #444444;
        background-color: #1e1e1e;
        color: #ffffff;
    }

    /* ==========================================================================
       ZONA RESPONSIVE - SE ACTIVEAZĂ STRICT PE MOBIL (MAX-WIDTH: 768px)
       ========================================================================== */
    @media screen and (max-width: 768px) {
        /* Afișăm butonul hamburger DOAR pe mobil */
        #hamburger-btn, #hamburger-index-btn, #hamburger-client-btn {
            display: block;
            background: none;
            border: none;
            font-size: 28px;
            color: #c5a059;
            cursor: pointer;
            float: right;
            margin: 10px;
        }

        /* Ascundem meniul orizontal implicit pe mobil ca să nu mai taie ecranul */
        #nav-menu, #nav-index-menu, #nav-client-menu, .nav-links, ul.nav-menu {
            display: none; 
            flex-direction: column;
            width: 100%;
            background-color: #1a1a1a;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        /* Când utilizatorul apasă pe hamburger, meniul se deschide vertical sub header */
        #nav-menu.open, #nav-index-menu.open, #nav-client-menu.open, 
        #nav-menu.active, #nav-index-menu.active, #nav-client-menu.active {
            display: flex;
        }

        /* Transformăm link-urile în butoane mari de mobil, ușor de apăsat */
        #nav-menu a, #nav-index-menu a, #nav-client-menu a, .nav-links a, ul.nav-menu li a {
            display: block;
            width: 100%;
            padding: 15px;
            text-align: center;
            color: #c5a059;
            border-bottom: 1px solid #222222;
            box-sizing: border-box;
        }

        /* Containerele mari (containere, tabele, formulare) nu mai au voie să iasă din ecran */
        .container, .main-content, table, .table, form, .section-box-admin {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }
    }

    /* Containerul principal care ține search-ul */
    .search-wrapper, form[action*="search"], form.search-form {
        display: flex; /* Păstrăm flex doar pentru aliniere */
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
    <!-- antetul paginii, cu clopotelul de notificari si butonul de tema -->
    <header class="main-header" style="background: var(--bg-card); border-bottom: 1px solid var(--border-color); padding: 15px 0;">
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center;">
            <a href="index.php" class="logo" style="font-size: 24px; color: #222; text-decoration: none; font-family: Georgia, serif; letter-spacing: 2px;">AwA Client</a>
            
            <div style="display: flex; align-items: center; gap: 15px;">
                <!-- clopotelul de notificari, cu numarul de notificari necitite si lista derulanta -->
                <div class="notifications-wrapper" style="position: relative; display: inline-block;">
                    <button id="notif-bell" style="background: none; border: none; font-size: 1.3em; cursor: pointer; position: relative; color: #c5a059; padding: 5px; margin: 0;">
                        🔔
                        <?php if ($unreadCount > 0): ?>
                            <span id="notif-badge" style="position: absolute; top: -5px; right: -5px; background: #ff6384; color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.65rem; font-weight: bold;"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </button>
                    <!-- lista derulanta cu ultimele notificari -->
                    <div id="notif-dropdown" style="display: none; position: absolute; top: 100%; right: 0; background: var(--bg-card); border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.15); border-radius: 4px; width: 280px; z-index: 1000; padding: 15px; text-align: left;">
                        <h4 style="margin: 0 0 10px 0; color: #7c5e28; font-family: Georgia, serif; border-bottom: 1px solid var(--border-color); padding-bottom: 5px;">Notificări</h4>
                        <ul id="notif-list" style="list-style: none; padding: 0; margin: 0; max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                            <?php if (empty($allNotifications)): ?>
                                <li style="color: #888; font-size: 0.85rem; text-align: center; padding: 10px 0;">Nu aveți notificări.</li>
                            <?php else: ?>
                                <?php foreach ($allNotifications as $n): ?>
                                    <li style="font-size: 0.8rem; border-bottom: 1px dashed var(--border-color); padding-bottom: 5px; color: <?php echo $n['citit'] ? '#888' : 'var(--text-color)'; ?>; font-weight: <?php echo $n['citit'] ? 'normal' : 'bold'; ?>;">
                                        <p style="margin: 0; line-height: 1.3; color: var(--text-color);"><?php echo htmlspecialchars($n['mesaj']); ?></p>
                                        <span style="font-size: 0.65rem; color: #aaa;"><?php echo $n['data_creare']; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <button id="hamburger-btn" style="display: none; background: none; border: none; font-size: 30px; color: #c5a059; cursor: pointer; padding: 10px;">☰</button>
            </div>
            
            <div id="nav-menu" class="nav-links" style="display: flex; align-items: center; gap: 20px;">
                <a href="index.php" style="color: #666; text-decoration: none; font-size: 0.85rem; font-weight: bold; letter-spacing: 1px;">ÎNAPOI LA SITE</a>
                <button id="toggle-theme" style="background: none; border: 1px solid #c5a059; border-radius: 50%; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #c5a059; font-size: 1.1em; transition: all 0.3s; margin: 0;">🌙</button>
            </div>
        </div>
    </header>

    <!-- continutul principal al paginii: titlu si tabelul cu actori favoriti -->
    <div class="client-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 1px solid #eee; padding-bottom: 1rem;">
            <h1 style="font-size: 1.6em; margin: 0; font-family: Georgia, serif; color: #7c5e28;">Spațiul Meu Personal</h1>
            <a href="logout.php" class="btn-action">Logout</a>
        </div>

        <!-- tabelul cu actorii favoriti ai utilizatorului, fiecare cu poza, biografie scurta si buton de stergere -->
        <section style="margin-bottom: 2rem;">
            <h2 style="font-family: Georgia, serif; color: #7c5e28;">Actorii Mei Favoriți ❤️</h2>
            <p style="font-size: 0.9rem; color: #666; font-family: Arial, sans-serif;">Mai jos găsești lista cu actorii pe care i-ai salvat în lista ta de preferințe.</p>
            
            <table class="fav-list">
                <thead>
                    <tr>
                        <th>Nume Actor</th>
                        <th>Biografie</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($favorites)): ?>
                        <tr><td colspan="3" style="text-align:center; color:#888;">Nu ai adăugat încă niciun actor la favorite.</td></tr>
                    <?php else: ?>
                        <?php foreach ($favorites as $actor): ?>
                            <tr id="fav-row-<?php echo $actor['id']; ?>">
                                <td data-label="Nume Actor" style="font-weight: bold; color: #7c5e28; vertical-align: middle;">
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <?php if (!empty($actor['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($actor['image_url']); ?>" alt="<?php echo htmlspecialchars($actor['nominee']); ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($actor['nominee']); ?></span>
                                    </div>
                                </td>
                                <td data-label="Biografie" style="color: #555; font-size: 0.9rem; vertical-align: middle;">
                                    <?php 
                                        // taiem biografia la 180 de caractere ca sa nu ocupe prea mult loc in tabel
                                        $bio = $actor['bio'] ?? 'Biografie indisponibilă';
                                        echo htmlspecialchars(strlen($bio) > 180 ? substr($bio, 0, 180) . '...' : $bio); 
                                    ?>
                                </td>
                                <td data-label="Acțiuni" style="vertical-align: middle;">
                                    <button class="btn-action btn-delete" onclick="eliminaFavorit(<?php echo $actor['id']; ?>, '<?php echo htmlspecialchars(addslashes($actor['nominee'])); ?>')" style="border-color: #ff6384; color: #ff6384;">Elimină</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>

    <!-- script pentru stergerea unui actor din lista de favorite -->
    <script>
    // elimina un actor din lista de favorite, fara sa reincarce pagina
    async function eliminaFavorit(actorId, actorName) {
        if (!confirm(`Sigur vrei să-l elimini pe "${actorName}" de la favorite?`)) return;

        try {
            // trimitem comanda de stergere prin delete catre api-ul securizat din users.php
            const response = await fetch(`api/users.php?action=remove_favorite&actor_id=${actorId}`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await response.json();

            if (response.ok) {
                // indepartam dinamic randul din dom, fara reincarcarea paginii
                const row = document.getElementById(`fav-row-${actorId}`);
                if (row) row.remove();
                
                // verificam daca tabelul a ramas complet gol, ca sa afisam mesajul de lista vida
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
    <!-- script pentru meniul hamburger si pentru deschiderea/inchiderea notificarilor -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // deschide/inchide meniul hamburger pe mobil
        const btn = document.getElementById('hamburger-btn');
        const menu = document.getElementById('nav-menu');
        if(btn && menu) {
            btn.addEventListener('click', function() {
                menu.classList.toggle('active');
            });
        }

        // deschide/inchide dropdown-ul de notificari si le marcheaza ca citite la prima deschidere
        const bell = document.getElementById('notif-bell');
        const dropdown = document.getElementById('notif-dropdown');
        const badge = document.getElementById('notif-badge');

        if (bell && dropdown) {
            bell.addEventListener('click', async function(e) {
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

        // inchidem dropdown-ul de notificari la click in afara lui
        window.addEventListener('click', function() {
            if (dropdown) dropdown.style.display = 'none';
        });
    });
    </script>
</body>
</html>