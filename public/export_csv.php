<?php
// permitem accesul de pe orice domeniu (cors deschis pentru acest endpoint)
header('Access-Control-Allow-Origin: *');

// citim parametrul target din url - ce tabel vrem sa exportam
$target = isset($_GET['target']) ? trim($_GET['target']) : '';

// validare: acceptam doar nominations sau actors, altfel respingem cererea
if ($target !== 'nominations' && $target !== 'actors') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Target invalid. Folositi nominations sau actors.']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

try {
    // pregatim headerele pentru descarcare fisier csv
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=export_' . $target . '_' . date('Y-m-d') . '.csv');

    // deschidem fluxul de output direct catre raspunsul http
    $output = fopen('php://output', 'w');

    if ($target === 'nominations') {
        // scriem randul de antet pentru nominalizari
        fputcsv($output, ['ID', 'Year', 'Category', 'Nominee', 'Production', 'Is Winner']);

        // luam toate nominalizarile din baza de date
        $stmt = $pdo->prepare("SELECT id, year, category, nominee, production, is_winner FROM nominations");
        $stmt->execute();
        // scriem fiecare rand in csv pe rand, fara sa incarcam tot in memorie
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            fputcsv($output, $row);
        }
    } else {
        // scriem randul de antet pentru actori
        fputcsv($output, ['ID', 'Name', 'TMDb ID', 'Biography', 'Image URL']);

        // luam toti actorii din baza de date
        $stmt = $pdo->prepare("SELECT id, name, tmdb_id, bio, image_url FROM actors");
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            fputcsv($output, $row);
        }
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    // daca pica baza de date, raspundem cu eroare json (nu mai trimitem csv)
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Eroare la export: ' . $e->getMessage()]);
}
?>