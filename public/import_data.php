<?php
// import date din csv in sqlite

session_start();
require_once __DIR__ . '/../config/db.php';

// doar adminul are acces
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    die("acces neautorizat.");
}

// verificare fisier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("eroare la incarcare.");
    }

    // doar csv
    $fileInfo = pathinfo($file['name']);
    if (strtolower($fileInfo['extension']) !== 'csv') {
        die("format invalid. folositi csv.");
    }

    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        die("nu s-a putut deschide fisierul.");
    }

    // sarim peste header
    fgetcsv($handle);

    try {
        $pdo->beginTransaction();

        // pregatire interogari
        $stmt = $pdo->prepare("INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (?, ?, ?, ?, ?)");
        $actorStmt = $pdo->prepare("INSERT OR IGNORE INTO actors (name) VALUES (?)");

        $count = 0;
        while (($row = fgetcsv($handle)) !== FALSE) {
            // structura: year, category, nominee, production, won
            if (count($row) < 5) continue;

            $yearRaw = $row[0];
            $category = $row[1];
            $nominee = $row[2];
            $production = $row[3];
            $wonRaw = strtolower(trim($row[4]));

            // curatare date
            $year = (int)substr($yearRaw, 0, 4);
            $isWinner = ($wonRaw === 'true' || $wonRaw === '1' || $wonRaw === 'yes' || $wonRaw === 'won') ? 1 : 0;
            
            // daca nominee e gol folosim productia
            $effectiveNominee = !empty($nominee) ? trim($nominee) : trim($production);

            // inserare
            $stmt->execute([$year, trim($category), $effectiveNominee, trim($production), $isWinner]);

            // adaugare in tabela actori
            if (!empty($nominee)) {
                $actorStmt->execute([trim($nominee)]);
            }

            $count++;
        }

        $pdo->commit();
        fclose($handle);

        // redirect succes
        header("Location: admin.php?import=success&items=" . $count);
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("eroare la import: " . $e->getMessage());
    }
} else {
    // inapoi in admin
    header("Location: admin.php");
    exit;
}
?>