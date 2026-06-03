<?php
header('Access-Control-Allow-Origin: *');

$target = isset($_GET['target']) ? trim($_GET['target']) : '';

if ($target !== 'nominations' && $target !== 'actors') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Target invalid. Folositi nominations sau actors.']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

try {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=export_' . $target . '_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    if ($target === 'nominations') {
        fputcsv($output, ['ID', 'Year', 'Category', 'Nominee', 'Production', 'Is Winner']);
        
        $stmt = $pdo->prepare("SELECT id, year, category, nominee, production, is_winner FROM nominations");
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            fputcsv($output, $row);
        }
    } else {
        fputcsv($output, ['ID', 'Name', 'TMDb ID', 'Biography', 'Image URL']);
        
        $stmt = $pdo->prepare("SELECT id, name, tmdb_id, bio, image_url FROM actors");
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Eroare la export: ' . $e->getMessage()]);
}
?>