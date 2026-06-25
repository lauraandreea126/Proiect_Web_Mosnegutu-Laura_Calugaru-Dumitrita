<?php
// ============================================================
// index.php - pagina principala (publica) a aplicatiei awa
// aici utilizatorul cauta un actor, vede statistici si stiri
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
?>
<!-- ============================================================ -->
<!-- structura html a paginii principale -->
<!-- ============================================================ -->
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AwA - Actor Awards Visualizer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=999">
    <script src="js/theme.js?v=999"></script>
    <style>
        /* stiluri inline specifice acestei pagini (suprascriu css-ul general) */
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
    border: 1px solid #b5b5b5;
    background-color: #ffffff;
    color: #333333;
    padding: 10px 12px;
    border-radius: 5px;
    width: 100%;
    max-width: 400px;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s, color 0.2s;
    margin-top: 5px;
    margin-bottom: 15px;
    display: block;
}

        /* exceptie pentru bara de cautare principala */
        #search-actor {
            max-width: 100%;
            width: 100%;
            flex: 1;
            height: 56px;
            padding: 0 1.5rem;
            border: 1px solid var(--border-soft);
            border-right: none;
            border-radius: 0;
            margin: 0;
            font-size: 1rem;
            display: flex;
background-color: var(--input-bg, #fff);
        }

        /* efect vizual cand utilizatorul da click in casuta (focus) */
        input:focus, textarea:focus, select:focus {
            border-color: #c5a059; /* Schimbare în culoarea aurie a temei */
            box-shadow: 0 0 5px rgba(197, 160, 89, 0.3);
            outline: none;
        }

        /* pastram adaptarea corecta si pentru modul intunecat (dark theme) */
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
           zona responsive - regulile de mai jos sunt folosite doar pe mobil - SE ACTIVEAZĂ STRICT PE MOBIL (MAX-WIDTH: 768px)
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

        /* containerul principal care tine bara de cautare */
        .search-wrapper, form[action*="search"], form.search-form {
            display: flex; /* Păstrăm flex doar pentru aliniere */
            max-width: 650px; /* Lățime generoasă pe desktop */
            width: 100%;
            margin: 30px auto; /* Spațiere clară sus-jos */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Umbră fină premium */
            border-radius: 6px;
            overflow: hidden;
        }

        /* casuta de text - o facem mai mare si mai inalta */
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

        /* butonul - se potriveste la fix cu inaltimea inputului */
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
    <!-- antetul (header) cu logo, meniu de navigare si butonul de tema -->
    <header class="main-header">
        <div class="container">
            <nav class="nav-wrapper">
                <a href="index.php" class="logo">AwA</a>
                <div style="display: flex; align-items: center;">
                    <button id="hamburger-btn" style="display: none; background: none; border: none; font-size: 30px; color: #c5a059; cursor: pointer; padding: 10px;">☰</button>
                    <ul id="nav-menu" class="nav-links" style="display: flex; list-style: none; margin: 0; padding: 0; gap: 15px; align-items: center; color: var(--text-muted); font-size: 0.8em; font-weight: bold; letter-spacing: 1.5px;">
                        <li><a href="index.php#actor-profile" style="color: inherit; text-decoration: none;">PROFIL</a></li>
                        <li style="color: #c5a059; padding: 0 5px;">•</li>
                        <li><a href="index.php#stats-container" style="color: inherit; text-decoration: none;">STATISTICI</a></li>
                        <li style="color: #c5a059; padding: 0 5px;">•</li>
                        <li><a href="index.php#news-feed" style="color: inherit; text-decoration: none;">ȘTIRI</a></li>
                        <li style="color: #c5a059; padding: 0 5px;">•</li>
                        <li><a href="raport_cerinte.html" style="color: inherit; text-decoration: none;">RAPORT CERINȚE</a></li>
                        <li style="color: #c5a059; padding: 0 5px;">•</li>
                        <li><a href="raport_arhitectura.html" style="color: inherit; text-decoration: none;">RAPORT ARHITECTURĂ</a></li>
                        <li style="color: #c5a059; padding: 0 5px;">•</li>
                        
                        <!-- daca utilizatorul e logat, aratam meniul de cont, altfel link de conectare -->
                <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- doar adminul vede link-ul catre panoul de admin -->
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <li><a href="admin.php" style="color: var(--accent-gold); text-decoration: none;">PANOU ADMIN</a></li>
                                <li style="color: #c5a059; padding: 0 5px;">•</li>
                            <?php endif; ?>
                           <li style="position: relative; display: flex; align-items: center;">
                                <span id="user-menu-toggle" onclick="toggleUserDropdown(event)" style="color: inherit; cursor: pointer; text-transform: uppercase; font-weight: bold;">
                                    Salut, <?php echo htmlspecialchars($_SESSION['username'] ?? 'USER'); ?>! ▼
                                </span>
                                <ul id="user-dropdown-menu" style="position: absolute; top: 100%; right: 0; background: #fff; border: 1px solid #ccc; padding: 10px 0; min-width: 150px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 4px; display: none; flex-direction: column; gap: 5px; z-index: 1000;">
                                    <li><a href="client.php" style="color: #333; text-decoration: none; padding: 5px 10px; display: block; font-weight: bold; border-bottom: 1px solid #eee; margin-bottom: 2px;">Contul Meu</a></li>
                                    <li><a href="javascript:void(0)" onclick="promptChangePassword()" style="color: #333; text-decoration: none; padding: 5px 10px; display: block;">Schimbă Parolă</a></li>
                                    <li><a href="logout.php" style="color: #333; text-decoration: none; padding: 5px 10px; display: block;">Deconectare</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li><a href="admin.php" style="color: inherit; text-decoration: none; border: 1px solid var(--accent-gold); padding: 4px 10px; border-radius: 2px;">CONECTARE / ADMIN</a></li>
                        <?php endif; ?>
                        
                        <li style="margin-left: 20px;">
                            <button id="toggle-theme" style="background: none; border: 1px solid #c5a059; border-radius: 50%; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #c5a059; font-size: 1.1em; transition: all 0.3s;">🌙</button>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <!-- sectiunea hero - titlul mare al paginii si bara de cautare actori -->
    <section class="hero">
        <div class="hero-content">
            <h1>Actor Awards Visualizer</h1>
            <div class="search-container">
                <div class="search-wrapper">
                    <input type="text" id="search-actor" placeholder="caută un actor (ex: meryl streep)..." autocomplete="off">
                    <button type="button" class="search-btn">caută</button>
                </div>
                <div id="search-results" class="search-suggestions"></div>
            </div>
            
            <!-- doar utilizatorii logati pot adauga actorul cautat la favorite -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div style="margin-top: 20px;">
<button id="fav-button-fix" onclick="forteazaSalvareFavorite()" style="background: transparent; border: 2px solid #c5a059; color: #c5a059; padding: 10px 25px; cursor: pointer; font-weight: bold; font-size: 0.9em; letter-spacing: 1px; text-transform: uppercase; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            ❤️ Adaugă actorul de mai sus la Favorite
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- continutul principal: profil actor, statistici, stiri, statistici globale -->
    <main class="main-content">
        <div class="container grid-layout">
            <!-- profilul actorului cautat - se umple dinamic prin javascript -->
            <section id="actor-profile" class="section-box">
                <h2>Profil actor</h2>
                <div class="empty-state-msg">
                    <p>Descoperă povestea din spatele succesului. Căutați un actor pentru a-i vedea biografia și galeria de imagini.</p>
                </div>
            </section>

            <!-- statistici (grafice) pentru actorul cautat -->
            <section id="stats-container" class="section-box">
                <h2>Statistici</h2>
                <div class="empty-state-msg">
                    <p>Analiză și performanță. Diagramele de premii vor fi generate instantaneu după selectarea unui profil.</p>
                </div>
                <div class="chart-controls hidden">
                    <div id="bar-chart-container" class="chart-wrapper"></div>
                    <div class="export-actions">
                        <button class="export-btn" onclick="exportSVG('bar-chart-container')">Descarcă SVG</button>
                        <button class="export-btn" onclick="exportWebP('bar-chart-container')">Descarcă WebP</button>
                    </div>
                </div>
                <div class="chart-controls hidden">
                    <div id="pie-chart-container" class="chart-wrapper"></div>
                    <div class="export-actions">
                        <button class="export-btn" onclick="exportSVG('pie-chart-container')">Descarcă SVG</button>
                        <button class="export-btn" onclick="exportWebP('pie-chart-container')">Descarcă WebP</button>
                    </div>
                </div>
                <div class="chart-controls hidden">
                    <div id="donut-chart-container" class="chart-wrapper"></div>
                    <div class="export-actions">
                        <button class="export-btn" onclick="exportSVG('donut-chart-container')">Descarcă SVG</button>
                        <button class="export-btn" onclick="exportWebP('donut-chart-container')">Descarcă WebP</button>
                    </div>
                </div>
            </section>

            <!-- stiri legate de actorul cautat, agregate din mai multe surse rss -->
            <section id="news-feed" class="section-box">
                <h2>Știri & noutăți</h2>
                <div class="empty-state-msg">
                    <p>Rămâneți la curent. Cele mai noi știri din sursele internaționale vor fi agregate aici în funcție de căutarea dumneavoastră.</p>
                </div>
                <div id="news-results" class="hidden"></div>
            </section>

            <!-- statistici globale despre tot proiectul (nu despre un actor anume) -->
            <section id="project-stats" class="section-box">
                <h2>Statistici Proiect (Global)</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 2rem;">
                    <div class="chart-wrapper" style="height: 350px;">
                        <h3>Top 5 Actori Nominalizați</h3>
                        <!-- daca se face o cautare, afisam graficul dinamic (canvas); altfel afisam podiumul static demonstrativ -->
                        <?php if (isset($_GET['search']) || isset($_GET['actor_id'])): ?>
                            <canvas id="actorsChart"></canvas>
                        <?php else: ?>
                            <div class="podium-container">
                            <div class="podium-step step-2">
                                <div class="podium-actor">Julia Louis-Dreyfus</div>
                                <div class="podium-pillar">
                                    <span class="podium-rank">2</span>
                                    <span class="podium-count">21 ★</span>
                                </div>
                            </div>
                            
                            <div class="podium-step step-1">
                                <div class="podium-actor"><span style="color: #c5a059;">👑</span> Edie Falco</div>
                                <div class="podium-pillar">
                                    <span class="podium-rank">1</span>
                                    <span class="podium-count">22 ★</span>
                                </div>
                            </div>
                            
                            <div class="podium-step step-3">
                                <div class="podium-actor">Alec Baldwin</div>
                                <div class="podium-pillar">
                                    <span class="podium-rank">3</span>
                                    <span class="podium-count">20 ★</span>
                                </div>
                            </div>
                        </div>

                        <style>
                            /* stiluri css complet izolate, doar pentru componenta de podium de mai sus */
                            /* stiluri complet izolate doar pentru aceasta componenta */
                            .podium-container {
                                display: flex;
                                align-items: flex-end;
                                justify-content: center;
                                gap: 15px;
                                height: 250px;
                                width: 100%;
                                margin-top: 20px;
                                box-sizing: border-box;
                            }
                            .podium-step {
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                flex: 1;
                                max-width: 120px;
                            }
                            .podium-actor {
                                font-size: 0.9rem;
                                font-weight: bold;
                                text-align: center;
                                margin-bottom: 8px;
                                font-family: Georgia, serif;
                                min-height: 35px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            }
                            .podium-pillar {
                                width: 100%;
                                background: rgba(197, 160, 89, 0.05); /* Fundal discret, semi-transparent */
                                border: 1px solid #c5a059;
                                border-bottom: none;
                                border-radius: 6px 6px 0 0;
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                justify-content: space-between;
                                padding: 15px 5px;
                                box-sizing: border-box;
                                box-shadow: 0 4px 10px rgba(0,0,0,0.05);
                            }
                            
                            /* inaltimi diferite pentru efectul de podium (locul 1 cel mai inalt) */
                            .step-1 .podium-pillar { height: 160px; }
                            .step-2 .podium-pillar { height: 120px; }
                            .step-3 .podium-pillar { height: 90px; }
                            
                            .podium-rank {
                                font-size: 1.3rem;
                                font-weight: bold;
                                color: #7c5e28; /* Cifre elegante în auriu */
                                font-family: Georgia, serif;
                            }
                            .podium-count {
                                font-size: 0.85rem;
                                color: #7c5e28; /* Text curat și fin, fără casetă suplimentară */
                                font-weight: bold;
                                margin-top: 5px;
                            }

                            /* adaptare automata in dark mode pentru armonie vizuala */
                            body.dark-theme .podium-pillar {
                                border-color: #c5a059;
                                background: rgba(197, 160, 89, 0.1);
                            }
                            body.dark-theme .podium-rank {
                                color: #c5a059;
                            }
                            body.dark-theme .podium-count {
                                color: #c5a059;
                            }

                            /* responsive - pe mobil podiumul se transforma automat intr-o lista verticala */
                            @media screen and (max-width: 600px) {
                                .podium-container {
                                    display: flex;
                                    flex-direction: column;
                                    align-items: stretch;
                                    height: auto;
                                    gap: 10px;
                                }
                                .podium-step {
                                    flex-direction: row;
                                    max-width: 100%;
                                    justify-content: space-between;
                                    background: rgba(197, 160, 89, 0.05);
                                    padding: 8px 12px;
                                    border-radius: 6px;
                                    border-left: 4px solid #c5a059;
                                }
                                .step-1 { order: 1; border-left-color: #c5a059; } /* Forțăm ordinea 1, 2, 3 pe mobil */
                                .step-2 { order: 2; }
                                .step-3 { order: 3; }
                                
                                .podium-actor { margin-bottom: 0; text-align: left; justify-content: flex-start; }
                                .podium-pillar {
                                    width: auto;
                                    height: auto;
                                    flex-direction: row-reverse;
                                    gap: 10px;
                                    background: none;
                                    border: none;
                                    padding: 0;
                                    box-shadow: none;
                                }
                                .podium-rank { color: #7c5e28; font-size: 1.2rem; }
                                .podium-count { color: #7c5e28; background: none; }
                                body.dark-theme .podium-rank { color: #c5a059; }
                                body.dark-theme .podium-count { color: #c5a059; }
                            }
                        </style>
                        <?php endif; ?>
                    </div>
                    <!-- al doilea grafic global: raportul castigatori vs doar nominalizati -->
                    <div class="chart-wrapper" style="height: 350px;">
                        <h3>Rata de Câștig (Total)</h3>
<?php if (isset($_GET['search']) || isset($_GET['actor_id'])): ?>
                            <canvas id="victoryChart"></canvas>
                            <canvas id="rateChart" style="display:none;"></canvas>
                        <?php else: ?>
                            <div class="rate-minimalist-container">
                            <div class="rate-row">
                                <div class="rate-info">
                                    <span class="rate-label">🏆 Câștigători Trofee</span>
                                    <span class="rate-value">23.4%</span>
                                </div>
                                <div class="rate-bar-bg">
                                    <div class="rate-bar-fill winner-fill" style="width: 23.4%;"></div>
                                </div>
                            </div>

                            <div class="rate-row" style="margin-top: 25px;">
                                <div class="rate-info">
                                    <span class="rate-label">🎬 Doar Nominalizați</span>
                                    <span class="rate-value">76.6%</span>
                                </div>
                                <div class="rate-bar-bg">
                                    <div class="rate-bar-fill nominee-fill" style="width: 76.6%;"></div>
                                </div>
                            </div>
                        </div>

                        <style>
                            .rate-minimalist-container {
                                width: 100%;
                                max-width: 360px;
                                margin: 40px auto 0 auto;
                                box-sizing: border-box;
                            }
                            .rate-info {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                margin-bottom: 8px;
                                font-family: Georgia, serif;
                            }
                            .rate-label {
                                font-size: 0.95rem;
                                font-weight: bold;
                                color: #7c5e28;
                            }
                            .rate-value {
                                font-size: 1.1rem;
                                font-weight: bold;
                                color: #c5a059;
                            }
                            .rate-bar-bg {
                                width: 100%;
                                height: 8px;
                                background: rgba(197, 160, 89, 0.1);
                                border-radius: 4px;
                                overflow: hidden;
                            }
                            .rate-bar-fill {
                                height: 100%;
                                border-radius: 4px;
                            }
                            .winner-fill {
                                background: #c5a059; /* Auriul plin al temei */
                            }
                            .nominee-fill {
                                background: #e2d9cd; /* Rozul pal/crem de contrast */
                            }
                        </style>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- subsolul paginii -->
    <footer class="main-footer">
        <div class="container">
            <span class="footer-logo">AwA</span>
            <p class="copyright">&copy; 2026 awa. proiect realizat de Laura & Dumitrița.</p>
        </div>
    </footer>

    <!-- butoane flotante pentru a sari rapid sus/jos pe pagina -->
    <div style="position: fixed; bottom: 30px; right: 30px; display: flex; flex-direction: column; gap: 12px; z-index: 2000;">
        <button id="scroll-to-top" style="background: #c5a059; color: white; border: none; border-radius: 50%; width: 42px; height: 42px; font-size: 1.2em; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">▲</button>
        <button id="scroll-to-bottom" style="background: #c5a059; color: white; border: none; border-radius: 50%; width: 42px; height: 42px; font-size: 1.2em; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">▼</button>
    </div>
    
    <!-- ============================================================ -->
    <!-- scripturile paginii: tema, cautare, grafice, stiri si interactiuni -->
    <!-- ============================================================ -->
    <script src="js/app.js?v=999"></script>
    <script src="js/charts.js?v=999"></script>
    <script src="js/charts_v2.js?v=999"></script>
    <script src="js/search_v2.js?v=999"></script>
    <script src="js/news.js?v=999"></script>
    <script>
        // butoanele flotante de scroll sus/jos
        document.getElementById('scroll-to-top').addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        document.getElementById('scroll-to-bottom').addEventListener('click', () => window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' }));

        // cere user-ului parola veche si noua, apoi trimite cererea de schimbare la api
        async function promptChangePassword() {
            const oldPassword = prompt("Te rugăm să introduci parola veche:");
            if (!oldPassword) return;
            const newPassword = prompt("Te rugăm să introduci parola nouă:");
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

        // deschide/inchide meniul dropdown de cont al utilizatorului
        function toggleUserDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('user-dropdown-menu');
            if (dropdown) {
                dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
            }
        }
        // inchide dropdown-ul de cont daca utilizatorul face click in afara lui
        window.addEventListener('click', () => {
            const dropdown = document.getElementById('user-dropdown-menu');
            if (dropdown) dropdown.style.display = 'none';
        });

        // adauga actorul afisat in profil la lista de favorite a utilizatorului logat
        async function forteazaSalvareFavorite() {
            const profilActor = document.querySelector('#actor-profile [data-actor-id]');
            const actorId = profilActor ? profilActor.getAttribute('data-actor-id') : null;

            if (!actorId || actorId.trim() === '') {
                alert("Te rugăm să selectezi un actor valid");
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

        // activeaza butonul hamburger pentru meniul mobil
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