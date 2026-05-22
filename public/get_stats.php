<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$actor = isset($_GET['actor']) ? trim($_GET['actor']) : null;

try {
    if ($actor) {
        // 1. Statistici pe categorii pentru actor
        $stmt1 = $pdo->prepare("SELECT category, COUNT(*) as count FROM nominations WHERE nominee = :actor GROUP BY category");
        $stmt1->execute(['actor' => $actor]);
        $byCategory = $stmt1->fetchAll();

        // 2. Statistici pe ani pentru actor
        $stmt2 = $pdo->prepare("SELECT year, COUNT(*) as count FROM nominations WHERE nominee = :actor GROUP BY year ORDER BY year ASC");
        $stmt2->execute(['actor' => $actor]);
        $byYear = $stmt2->fetchAll();

        // 3. Raport Câștigători vs Nominalizați pentru actor
        $stmt3 = $pdo->prepare("SELECT 
            SUM(CASE WHEN is_winner = 1 THEN 1 ELSE 0 END) as winners,
            SUM(CASE WHEN is_winner = 0 THEN 1 ELSE 0 END) as nominees
            FROM nominations WHERE nominee = :actor");
        $stmt3->execute(['actor' => $actor]);
        $winLoss = $stmt3->fetch();
    } else {
        // Statistici globale (default)
        $stmt1 = $pdo->query("SELECT category, COUNT(*) as count FROM nominations GROUP BY category");
        $byCategory = $stmt1->fetchAll();

        $stmt2 = $pdo->query("SELECT year, COUNT(*) as count FROM nominations GROUP BY year ORDER BY year ASC");
        $byYear = $stmt2->fetchAll();

        $stmt3 = $pdo->query("SELECT 
            SUM(CASE WHEN is_winner = 1 THEN 1 ELSE 0 END) as winners,
            SUM(CASE WHEN is_winner = 0 THEN 1 ELSE 0 END) as nominees
            FROM nominations");
        $winLoss = $stmt3->fetch();
    }

    echo json_encode([
        'byCategory' => $byCategory,
        'byYear' => $byYear,
        'winLoss' => $winLoss,
        'actor' => $actor
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>