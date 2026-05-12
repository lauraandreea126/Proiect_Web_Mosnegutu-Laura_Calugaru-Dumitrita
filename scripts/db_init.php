<?php
$dbPath = __DIR__ . '/../data/awa.db';

try {
    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create nominations table
    $db->exec("CREATE TABLE IF NOT EXISTS nominations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        year INTEGER NOT NULL,
        category TEXT NOT NULL,
        nominee TEXT NOT NULL,
        production TEXT NOT NULL,
        is_winner INTEGER DEFAULT 0
    )");

    // Create actors table
    $db->exec("CREATE TABLE IF NOT EXISTS actors (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL,
        tmdb_id INTEGER,
        bio TEXT,
        image_url TEXT
    )");

    // Create news_sources table
    $db->exec("CREATE TABLE IF NOT EXISTS news_sources (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        url TEXT NOT NULL
    )");

    // Create users table for admin
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL
    )");

    echo "Baza de date și tabelele au fost create cu succes!\n";

} catch (PDOException $e) {
    echo "Eroare la crearea bazei de date: " . $e->getMessage() . "\n";
}
?>
