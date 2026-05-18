<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/tmdb_helper.php';

$queryParam = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($queryParam) < 2) {
    echo json_encode([]);
    exit;
}

try {
    // cautare nume in baza locala
    $sql = "SELECT DISTINCT n.nominee FROM nominations n 
            WHERE n.nominee LIKE :query 
            LIMIT 8";
    
    $stmt = $pdo->prepare($sql);
    $searchTerm = "%$queryParam%";
    $stmt->execute(['query' => $searchTerm]);
    $results = $stmt->fetchAll();

    // daca nu sunt destule rezultate, cautam si pe tmdb
    if (count($results) < 5) {
        $tmdbResults = searchActorsOnTMDb($queryParam);
        foreach ($tmdbResults as $actor) {
            $name = $actor['name'];
            $exists = false;
            foreach ($results as $res) {
                if ($res['nominee'] === $name) { $exists = true; break; }
            }
            if (!$exists) {
                $results[] = ['nominee' => $name];
                if (count($results) >= 10) break;
            }
        }
    }
    
    echo json_encode($results);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>