<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/tmdb_helper.php';

// numele actorului cautat
$name = isset($_GET['name']) ? trim($_GET['name']) : '';

if (empty($name)) {
    echo json_encode(['error' => 'nume lipsa']);
    exit;
}

try {
    // cautam mai intai in baza locala
    $stmt = $pdo->prepare("SELECT id, tmdb_id, bio, image_url FROM actors WHERE name = :name");
    $stmt->execute(['name' => $name]);
    $actor = $stmt->fetch();

    if (!$actor || empty($actor['bio'])) {
        // daca nu exista local sau nu are biografie, luam de pe tmdb
        $tmdbData = fetchActorFromTMDb($name);
        if ($tmdbData) {
            updateLocalActor($pdo, $name, $tmdbData);
            // re-interogam pentru a obtine randul complet salvat local, inclusiv id-ul autogenerat
            $stmt->execute(['name' => $name]);
            $actor = $stmt->fetch();
        }
    }

    // raspuns final: actorul gasit, sau un fallback minimal daca nu am gasit nimic
    echo json_encode($actor ?: ['nominee' => $name, 'bio' => 'biografie indisponibila', 'image_url' => null]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>