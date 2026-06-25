<?php
// config/db.php
$dbPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'awa.db';

// Verificare chirurgicală a permisiunilor de scriere pe disc (Task 10)
$dbDir = dirname($dbPath);
if (!is_writable($dbDir) || (file_exists($dbPath) && !is_writable($dbPath))) {
    $errMsg = "Eroare critică de permisiuni: Serverul local Apache/PHP nu are drepturi de scriere în folderul '" . realpath($dbDir) . "' sau pe fișierul bazei de date. SQLite are nevoie de drepturi de scriere în ambele pentru a efectua tranzacții.";
    
    // Verificăm tipul cererii pentru a returna JSON în caz de AJAX sau HTML în caz de pagină clasică
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'
           || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
           || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
           
    if ($isAjax) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => $errMsg]);
    } else {
        echo "<div style='padding: 20px; background: #fff5f5; color: #cc0000; border: 1px solid #cc0000; font-family: Arial, sans-serif; margin: 20px; border-radius: 4px; line-height: 1.5;'>";
        echo "<h3 style='margin-top:0;'>❌ " . $errMsg . "</h3>";
        echo "<p><strong>Cum rezolvi această eroare în Apache/XAMPP:</strong></p>";
        echo "<ol>";
        echo "<li>Deschide folderul proiectului în Explorer.</li>";
        echo "<li>Dă click dreapta pe folderul <code>data</code> -> <strong>Properties</strong>.</li>";
        echo "<li>Debifează opțiunea <strong>Read-only (Numai citire)</strong> și apasă Apply.</li>";
        echo "<li>Mergi la tab-ul <strong>Security</strong>, apasă <strong>Edit</strong>, selectează utilizatorii (ex: <code>Users</code> sau <code>SYSTEM</code>) și bifează permisiunea <strong>Full Control / Write</strong>.</li>";
        echo "</ol>";
        echo "</div>";
    }
    exit;
}

try {
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // 1. Asigurăm existența tabelei 'users' (Corectat tiparul: AUTOINCREMENT)
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        role TEXT DEFAULT 'client',
        email TEXT
    )");

    // 2. Verificăm și forțăm adăugarea coloanei 'role' în caz că ai o tabelă users mai veche
    try {
        @$pdo->exec("ALTER TABLE users ADD COLUMN role TEXT DEFAULT 'client'");
    } catch (PDOException $e) {
        // Ignorăm eroarea dacă coloana există deja în DB
    }

    // 2.5 Verificăm și adăugăm coloana 'email' în caz că tabela users exista deja
    try {
        @$pdo->exec("ALTER TABLE users ADD COLUMN email TEXT");
    } catch (PDOException $e) {
        // Ignorăm eroarea dacă coloana există deja în DB
    }

    // 3. 🆕 REPARĂ IMAGINEA 1: Creăm tabela pentru sursele de știri RSS
    $pdo->exec("CREATE TABLE IF NOT EXISTS news_sources (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        url TEXT NOT NULL
    )");

    // 4. Creăm tabela pentru Actori Favoriți
    $pdo->exec("CREATE TABLE IF NOT EXISTS favorite_actors (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        actor_id INTEGER NOT NULL,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY(actor_id) REFERENCES actors(id) ON DELETE CASCADE,
        UNIQUE(user_id, actor_id)
    )");

    // 4.5 Creăm tabela pentru Notificări
    $pdo->exec("CREATE TABLE IF NOT EXISTS notificari (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        mesaj TEXT,
        data_creare DATETIME DEFAULT CURRENT_TIMESTAMP,
        citit INTEGER DEFAULT 0
    )");

    // 5. 👥 REPARĂ IMAGINEA 2: Inserăm automat utilizatorii de test în DB dacă nu există deja
    $checkUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($checkUsers == 0) {
        // Generăm hash-uri sigure pe server
        $adminHash = password_hash('admin123', PASSWORD_BCRYPT);
        $clientHash = password_hash('client123', PASSWORD_BCRYPT);

        $insertUser = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
        $insertUser->execute(['admin', $adminHash, 'admin']);
        $insertUser->execute(['client', $clientHash, 'client']);
    }

} catch (PDOException $e) {
    die("Eroare de conectare la baza de date: " . $e->getMessage());
}
?>