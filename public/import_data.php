<?php
/**
 * AwA - Data Import Handler (Admin Task)
 * Procesează fișierele CSV încărcate și le introduce în baza de date SQLite.
 */

session_start();
require_once __DIR__ . '/../config/db.php';

// Verificare securitate: doar adminul are acces
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    die("Acces neautorizat. Vă rugăm să vă autentificați.");
}

// Verificăm dacă a fost trimis un fișier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Eroare la încărcarea fișierului pe server.");
    }

    // Verificăm extensia (doar CSV)
    $fileInfo = pathinfo($file['name']);
    if (strtolower($fileInfo['extension']) !== 'csv') {
        die("Format invalid. Vă rugăm să încărcați doar fișiere .csv");
    }

    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        die("Nu s-a putut deschide fișierul pentru citire.");
    }

    // Sarim peste header (prima linie)
    fgetcsv($handle);

    try {
        $pdo->beginTransaction();

        // Pregătim statement-urile pentru viteză și securitate (SQL Injection)
        $stmt = $pdo->prepare("INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (?, ?, ?, ?, ?)");
        $actorStmt = $pdo->prepare("INSERT OR IGNORE INTO actors (name) VALUES (?)");

        $count = 0;
        while (($row = fgetcsv($handle)) !== FALSE) {
            // Structura așteptată: year, category, nominee, production, won
            if (count($row) < 5) continue;

            $yearRaw = $row[0];
            $category = $row[1];
            $nominee = $row[2];
            $production = $row[3];
            $wonRaw = strtolower(trim($row[4]));

            // Curățare și conversie date
            $year = (int)substr($yearRaw, 0, 4);
            $isWinner = ($wonRaw === 'true' || $wonRaw === '1' || $wonRaw === 'yes' || $wonRaw === 'won') ? 1 : 0;
            
            // Dacă nominee este gol, folosim producția (ex: pentru premii de distribuție/ansamblu)
            $effectiveNominee = !empty($nominee) ? trim($nominee) : trim($production);

            // Inserare în baza de date
            $stmt->execute([$year, trim($category), $effectiveNominee, trim($production), $isWinner]);

            // Adăugăm automat și în tabela de actori dacă avem un nume valid
            if (!empty($nominee)) {
                $actorStmt->execute([trim($nominee)]);
            }

            $count++;
        }

        $pdo->commit();
        fclose($handle);

        // Redirect cu succes
        header("Location: admin.php?import=success&items=" . $count);
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Eroare critică la import: " . $e->getMessage());
    }
} else {
    // Dacă accesăm fișierul direct, trimitem înapoi în admin
    header("Location: admin.php");
    exit;
}
?>