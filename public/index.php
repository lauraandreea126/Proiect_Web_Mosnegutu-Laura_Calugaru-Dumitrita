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
</head>
<body>
    <header class="main-header">
        <div class="container">
            <nav class="nav-wrapper">
                <a href="index.php" class="logo">AwA</a>
                <div style="display: flex; align-items: center;">
                    <ul style="display: flex; list-style: none; margin: 0; padding: 0; gap: 15px; align-items: center; color: var(--text-muted); font-size: 0.8em; font-weight: bold; letter-spacing: 1.5px;">
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
                        <li><a href="admin.php" style="color: inherit; text-decoration: none;">ADMIN</a></li>
                        <li style="margin-left: 20px;">
                            <button id="theme-toggle" style="background: none; border: 1px solid #c5a059; border-radius: 50%; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #c5a059; font-size: 1.1em; transition: all 0.3s;">🌙</button>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Actor Awards Visualizer</h1>
            <div class="search-container">
                <!-- câmp căutare actori -->
                <input type="text" id="search-actor" placeholder="caută un actor (ex: meryl streep)..." autocomplete="off">
                <button type="button" class="search-btn">caută</button>
                <div id="search-results" class="search-suggestions"></div>
            </div>
        </div>
    </section>

    <main class="main-content">
        <div class="container grid-layout">
            <section id="actor-profile" class="section-box">
                <h2>Profil actor</h2>
                <div class="empty-state-msg">
                    <p>Descoperă povestea din spatele succesului. Căutați un actor pentru a-i vedea biografia și galeria de imagini.</p>
                </div>
            </section>

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

            <section id="news-feed" class="section-box">
                <h2>Știri & noutăți</h2>
                <div class="empty-state-msg">
                    <p>Rămâneți la curent. Cele mai noi știri din sursele internaționale vor fi agregate aici în funcție de căutarea dumneavoastră.</p>
                </div>
                <div id="news-results" class="hidden"></div>
            </section>



            <section id="project-stats" class="section-box">
                <h2>Statistici Proiect (Global)</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 2rem;">
                    <div class="chart-wrapper" style="height: 350px;">
                        <h3>Top 5 Actori Nominalizați</h3>
                        <canvas id="actorsChart"></canvas>
                    </div>
                    <div class="chart-wrapper" style="height: 350px;">
                        <h3>Rata de Câștig (Total)</h3>
                        <canvas id="victoryChart"></canvas>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <footer class="main-footer">
        <div class="container">
            <span class="footer-logo">AwA</span>
            <p class="copyright">&copy; 2026 awa. proiect realizat de Laura & Dumitrița.</p>
        </div>
    </footer>

    <!-- buton sus -->
    <button id="back-to-top" title="înapoi sus">&#8593;</button>
    <script src="js/app.js?v=999"></script>
    <script src="js/charts.js?v=999"></script>
    <script src="js/charts_v2.js?v=999"></script>
    <script src="js/search_v2.js?v=999"></script>
    <script src="js/news.js?v=999"></script>
</body>
</html>


