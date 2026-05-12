<?php
// Configurație bază de date
$dbPath = __DIR__ . '/../data/awa.db';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Eroare de conectare la baza de date: " . $e->getMessage());
}
?>
