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
                <ul class="nav-menu">
                    <li><a href="#actor-profile">profil</a></li>
                    <li><a href="#stats-container">statistici</a></li>
                    <li><a href="#news-feed">știri</a></li>
                    <li><a href="admin.php">admin</a></li>
                </ul>
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
                <h2>Profil Actor</h2>
                <div class="placeholder-content">datele vor fi preluate din tmdb api.</div>
            </section>

            <section id="stats-container" class="section-box">
                <h2>Statistici</h2>
                <div id="bar-chart-container" class="chart-wrapper"></div>
                <div id="pie-chart-container" class="chart-wrapper"></div>
                <div id="donut-chart-container" class="chart-wrapper"></div>
            </section>

            <section id="news-feed" class="section-box">
                <h2>Știri & Noutăți</h2>
                <div class="placeholder-content">fluxul de știri externe va apărea aici.</div>
            </section>
        </div>
    </main>

    <footer class="main-footer">
        <div class="container">
            <span class="footer-logo">AwA</span>
            <p class="copyright">&copy; 2026 awa. proiect realizat de Laura & Dumitrița.</p>
        </div>
    </footer>

    <!-- buton scroll sus -->
    <button id="back-to-top" title="înapoi sus">&#8593;</button>
    <script src="js/app.js?v=999"></script>
    <script src="js/charts.js?v=999"></script>
    <script src="js/search_v2.js?v=999"></script>
    <script src="js/news.js?v=999"></script>
</body>
</html>
