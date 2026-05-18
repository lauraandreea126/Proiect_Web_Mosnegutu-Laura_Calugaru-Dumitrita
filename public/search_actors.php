<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$queryParam = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($queryParam) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $sql = "SELECT n.*, a.tmdb_id, a.bio, a.image_url 
            FROM nominations n 
            LEFT JOIN actors a ON n.nominee = a.name 
            WHERE n.nominee LIKE :query";
    
    $stmt = $pdo->prepare($sql);
    $searchTerm = "%$queryParam%";
    $stmt->execute(['query' => $searchTerm]);
    
    $results = $stmt->fetchAll();
    
    echo json_encode($results);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Eroare la interogarea bazei de date',
        'message' => $e->getMessage()
    ]);
}
?>
