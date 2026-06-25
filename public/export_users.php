<?php
// public/export_users.php

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

// ============================================================
// verificare drepturi de acces (doar admin poate exporta clientii)
// ============================================================

// verificare autentificare administrator (pentru protectia datelor clientilor)
$is_logged_in = (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

if (!$is_logged_in) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Acces interzis. Doar administratorii pot exporta datele utilizatorilor.']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

// ============================================================
// validare format export (json sau csv)
// ============================================================

$format = isset($_GET['format']) ? trim(strtolower($_GET['format'])) : '';

if ($format !== 'json' && $format !== 'csv') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Format invalid. Parametri acceptați: ?format=json sau ?format=csv']);
    exit;
}

try {
    if ($format === 'json') {
        // ============================================================
        // export in format deschis json
        // ============================================================
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="clienti_awa_' . date('Y-m-d') . '.json"');

        $stmt = $pdo->prepare("SELECT id, username, email, role FROM users ORDER BY id DESC");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        // ============================================================
        // export in format deschis csv
        // ============================================================
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="clienti_awa_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // trimiterea marcajului byte order mark (bom) pentru a deschide corect diacriticele in excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // capul de tabel conform specificatiilor
        fputcsv($output, ['ID', 'Username', 'Email', 'Rol']);

        $stmt = $pdo->prepare("SELECT id, username, email, role FROM users ORDER BY id DESC");
        $stmt->execute();

        // scriem fiecare utilizator pe un rand nou in csv
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
} catch (PDOException $e) {
    // eroare de baza de date, raspundem cu json deoarece headerul de csv nu a fost inca trimis cu succes
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Eroare la exportul datelor: ' . $e->getMessage()]);
}
?>