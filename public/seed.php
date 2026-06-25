<?php
require_once __DIR__ . '/../config/db.php'; // ajustam calea daca e nevoie

header('Content-Type: text/plain'); // raspunsul este text simplu, nu json

try {
    // ============================================================
    // 1. stergem tabelele existente (resetare completa a bazei)
    // ============================================================
    $pdo->exec("DROP TABLE IF EXISTS favorite_actors;");
    $pdo->exec("DROP TABLE IF EXISTS users;");
    $pdo->exec("DROP TABLE IF EXISTS nominations;");
    $pdo->exec("DROP TABLE IF EXISTS actors;");
    $pdo->exec("DROP TABLE IF EXISTS news_sources;");

    // ============================================================
    // 2. citim continutul fisierului schema.sql
    // ============================================================
    $schema_sql = file_get_contents(__DIR__ . '/../data/schema.sql');
    if ($schema_sql === false) {
        throw new Exception("Could not read schema.sql file.");
    }

    // ============================================================
    // 3. executam schema completa pentru a recrea tabelele
    // ============================================================
    $pdo->exec($schema_sql);

    // ============================================================
    // 4. inseram utilizatorii de test (admin si client)
    // ============================================================
    $admin_password_hash  = password_hash('admin123', PASSWORD_BCRYPT);
    $client_password_hash = password_hash('client123', PASSWORD_BCRYPT);

    $users_to_insert = [
        ['admin', 'admin@test.com', $admin_password_hash, 'admin'],
        ['client', 'client@test.com', $client_password_hash, 'user'],
    ];

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");

    foreach ($users_to_insert as $user_data) {
        $stmt->execute($user_data);
    }

    echo "Baza de date a fost populată cu succes!
";

} catch (PDOException $e) {
    // eroare specifica de baza de date (de exemplu sintaxa sql gresita)
    echo "Eroare la popularea bazei de date (PDOException): " . $e->getMessage() . "
";
} catch (Exception $e) {
    // orice alta eroare generala (de exemplu fisier schema.sql lipsa)
    echo "Eroare la popularea bazei de date (General Exception): " . $e->getMessage() . "
";
}