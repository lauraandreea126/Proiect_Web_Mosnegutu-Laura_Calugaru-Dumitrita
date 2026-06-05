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
                    <ul class="nav-menu">
                        <li><a href="#actor-profile">profil</a></li>
                        <li><a href="#stats-container">statistici</a></li>
                        <li><a href="#news-feed">știri</a></li>
                        <li><a href="admin.php">admin</a></li>
                    </ul>
                    <button id="theme-toggle" class="theme-btn" title="Schimbă tema">🌙</button>
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
                        <button class="export-btn" onclick="exportSVG('bar-chart-container')">Download SVG</button>
                        <button class="export-btn" onclick="exportWebP('bar-chart-container')">Download WebP</button>
                    </div>
                </div>
                <div class="chart-controls hidden">
                    <div id="pie-chart-container" class="chart-wrapper"></div>
                    <div class="export-actions">
                        <button class="export-btn" onclick="exportSVG('pie-chart-container')">Download SVG</button>
                        <button class="export-btn" onclick="exportWebP('pie-chart-container')">Download WebP</button>
                    </div>
                </div>
                <div class="chart-controls hidden">
                    <div id="donut-chart-container" class="chart-wrapper"></div>
                    <div class="export-actions">
                        <button class="export-btn" onclick="exportSVG('donut-chart-container')">Download SVG</button>
                        <button class="export-btn" onclick="exportWebP('donut-chart-container')">Download WebP</button>
                    </div>
                </div>
            </section>

            <section id="news-feed" class="section-box">
                <h2>Stiri & noutati</h2>
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


