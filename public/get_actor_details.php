<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/tmdb_helper.php';

$name = isset($_GET['name']) ? trim($_GET['name']) : '';

if (empty($name)) {
    echo json_encode(['error' => 'nume lipsa']);
    exit;
}

try {
    // cautam in baza locala
    $stmt = $pdo->prepare("SELECT tmdb_id, bio, image_url FROM actors WHERE name = :name");
    $stmt->execute(['name' => $name]);
    $actor = $stmt->fetch();

    if (!$actor || empty($actor['bio'])) {
        // daca nu exista, luam de pe tmdb
        $tmdbData = fetchActorFromTMDb($name);
        if ($tmdbData) {
            $actor = $tmdbData;
            updateLocalActor($pdo, $name, $tmdbData);
        }
    }

    echo json_encode($actor ?: ['nominee' => $name, 'bio' => 'biografie indisponibila', 'image_url' => null]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>