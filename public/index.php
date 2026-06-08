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
                    <ul class="desktop-nav">
                        <li><a href="index.php#actor-profile">PROFIL</a></li>
                        <li class="nav-dot">•</li>
                        <li><a href="index.php#stats-container">STATISTICI</a></li>
                        <li class="nav-dot">•</li>
                        <li><a href="index.php#news-feed">ȘTIRI</a></li>
                        <li class="nav-dot">•</li>
                        <li><a href="raport_cerinte.html">RAPORT CERINȚE</a></li>
                        <li class="nav-dot">•</li>
                        <li><a href="raport_arhitectura.html">RAPORT ARHITECTURĂ</a></li>
                        <li class="nav-dot">•</li>
                        <li><a href="admin.php">ADMIN</a></li>
                    </ul>
                    <div style="display: flex; align-items: center; margin-left: 20px;">
                        <button id="theme-toggle" class="theme-btn" style="margin: 0;">🌙</button>
                        <button class="hamburger-btn" id="mobile-toggle" aria-label="Menu" style="margin-left: 15px;">
                            <span class="hamburger-line"></span>
                            <span class="hamburger-line"></span>
                            <span class="hamburger-line"></span>
                        </button>
                    </div>
                </div>
            </nav>
        </div>
        <div class="mobile-nav-drawer" id="mobile-drawer">
            <ul class="mobile-nav-list">
                <li><a href="index.php#actor-profile">PROFIL</a></li>
                <li><a href="index.php#stats-container">STATISTICI</a></li>
                <li><a href="index.php#news-feed">ȘTIRI</a></li>
                <li><a href="raport_cerinte.html">RAPORT CERINȚE</a></li>
                <li><a href="raport_arhitectura.html">RAPORT ARHITECTURĂ</a></li>
                <li><a href="admin.php">ADMIN</a></li>
            </ul>
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

    <!-- butoane plutitoare unificate -->
    <div style="position: fixed; bottom: 30px; right: 30px; display: flex; flex-direction: column; gap: 12px; z-index: 2000;">
        <button id="scroll-to-top" style="background: #c5a059; color: white; border: none; border-radius: 50%; width: 42px; height: 42px; font-size: 1.2em; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.25); transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.2s; animation: floatPulse 3s infinite ease-in-out;">▲</button>
        <button id="scroll-to-bottom" style="background: #c5a059; color: white; border: none; border-radius: 50%; width: 42px; height: 42px; font-size: 1.2em; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.25); transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.2s; animation: floatPulse 3s infinite ease-in-out;">▼</button>
    </div>
    
    <script src="js/app.js?v=999"></script>
    <script src="js/charts.js?v=999"></script>
    <script src="js/charts_v2.js?v=999"></script>
    <script src="js/search_v2.js?v=999"></script>
    <script src="js/news.js?v=999"></script>
    <script>
        document.getElementById('scroll-to-top').addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        document.getElementById('scroll-to-bottom').addEventListener('click', () => window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' }));
    </script>
</body>
</html>


