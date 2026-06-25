<?php
// import date din csv in sqlite

// ============================================================
// pornire sesiune (folosim folder local de sesiuni pentru proiect)
// ============================================================

// creem un folder local pentru sesiuni daca nu exista
$sessionPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sessions';
if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0777, true);
}
// fortam php sa salveze sesiunile in acest folder local din proiect
ini_set('session.save_path', $sessionPath);

session_set_cookie_params([
    'lifetime' => 86400,
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();
require_once __DIR__ . '/../config/db.php';

// doar adminul are acces la import
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    die("acces neautorizat.");
}

// ============================================================
// procesare fisier csv trimis prin formular
// ============================================================

// verificare fisier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("eroare la incarcare.");
    }

    // acceptam doar fisiere cu extensia csv
    $fileInfo = pathinfo($file['name']);
    if (strtolower($fileInfo['extension']) !== 'csv') {
        die("format invalid. folositi csv.");
    }

    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        die("nu s-a putut deschide fisierul.");
    }

    // sarim peste randul de antet (header) din csv
    fgetcsv($handle);

    try {
        $pdo->beginTransaction();

        // pregatire interogari de inserare
        $stmt      = $pdo->prepare("INSERT INTO nominations (year, category, nominee, production, is_winner) VALUES (?, ?, ?, ?, ?)");
        $actorStmt = $pdo->prepare("INSERT OR IGNORE INTO actors (name) VALUES (?)");

        $count = 0;
        // citim randurile din csv pe rand, pana la final
        while (($row = fgetcsv($handle)) !== FALSE) {
            // structura asteptata: year, category, nominee, production, won
            if (count($row) < 5) continue;

            $yearRaw    = $row[0];
            $category   = $row[1];
            $nominee    = $row[2];
            $production = $row[3];
            $wonRaw     = strtolower(trim($row[4]));

            // curatare si normalizare date din csv
            $year     = (int)substr($yearRaw, 0, 4);
            $isWinner = ($wonRaw === 'true' || $wonRaw === '1' || $wonRaw === 'yes' || $wonRaw === 'won') ? 1 : 0;

            // daca nominee e gol folosim numele productiei in locul lui
            $effectiveNominee = !empty($nominee) ? trim($nominee) : trim($production);

            // inserare in tabela de nominalizari
            $stmt->execute([$year, trim($category), $effectiveNominee, trim($production), $isWinner]);

            // adaugare in tabela de actori (doar daca avem un nume valid de nominee)
            if (!empty($nominee)) {
                $actorStmt->execute([trim($nominee)]);
            }

            $count++;
        }

        $pdo->commit();
        fclose($handle);

        // redirect catre admin cu mesaj de succes si numarul de randuri importate
        header("Location: admin.php?import=success&items=" . $count);
        exit;

    } catch (Exception $e) {
        // daca a aparut o eroare, anulam toate modificarile din tranzactie
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("eroare la import: " . $e->getMessage());
    }
} else {
    // nu a fost trimis fisier, ne intoarcem in pagina de admin
    header("Location: admin.php");
    exit;
}
?>