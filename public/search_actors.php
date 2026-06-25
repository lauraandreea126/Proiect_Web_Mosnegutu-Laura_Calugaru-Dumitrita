<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/tmdb_helper.php';

// textul cautat de utilizator (minim 2 caractere pentru a evita interogari inutile)
$queryParam = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($queryParam) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $results    = [];
    $searchTerm = "%$queryParam%";

    // ============================================================
    // 1. cautam mai intai in tabela locala gestionata de admin
    //    (actorii din catalogul oficial al site-ului)
    // ============================================================
    $sqlLocal = "SELECT DISTINCT id, name AS nominee FROM actors 
                 WHERE name LIKE :query 
                 LIMIT 5";
    $stmtLocal = $pdo->prepare($sqlLocal);
    $stmtLocal->execute(['query' => $searchTerm]);
    $localActors = $stmtLocal->fetchAll();

    // adaugam actorii locali gasiti in lista finala de rezultate (cu id-ul lor local)
    foreach ($localActors as $la) {
        $results[] = [
            'id'      => (int)$la['id'],
            'nominee' => $la['nominee']
        ];
    }

    // ============================================================
    // 2. cautare in tabela istorica de nominalizari (daca mai avem loc)
    // ============================================================
    $limitaRamasa = 8 - count($results);
    if ($limitaRamasa > 0) {
        // sqlite are nevoie de bind explicit sau concatenare securizata pentru limit
        $stmtNom = $pdo->prepare("SELECT DISTINCT nominee FROM nominations WHERE nominee LIKE :query LIMIT " . intval($limitaRamasa));
        $stmtNom->execute(['query' => $searchTerm]);
        $nomResults = $stmtNom->fetchAll();

        foreach ($nomResults as $nr) {
            // verificam sa nu fie deja adaugat din tabela actors
            $exists = false;
            foreach ($results as $res) {
                if ($res['nominee'] === $nr['nominee']) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $results[] = [
                    'id'      => null,
                    'nominee' => $nr['nominee']
                ];
            }
        }
    }

    // ============================================================
    // 3. daca tot nu sunt destule rezultate (mai puțin de 5), cautam si pe tmdb
    // ============================================================
    if (count($results) < 5) {
        $tmdbResults = searchActorsOnTMDb($queryParam);
        foreach ($tmdbResults as $actor) {
            $name   = $actor['name'];
            $exists = false;
            foreach ($results as $res) {
                if ($res['nominee'] === $name) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $results[] = [
                    'id'      => null,
                    'nominee' => $name
                ];
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