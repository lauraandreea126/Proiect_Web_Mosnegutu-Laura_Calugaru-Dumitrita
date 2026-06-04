<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

try {
    // SQL1: Top 5 actori după numărul de nominalizări
    $stmt1 = $pdo->query("SELECT nominee, COUNT(*) as count FROM nominations GROUP BY nominee ORDER BY count DESC LIMIT 5");
    $top_actors = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // SQL2: Proporție câștigători vs nominalizați (global)
    $stmt2 = $pdo->query("SELECT 
        SUM(CASE WHEN is_winner = 1 THEN 1 ELSE 0 END) as winners,
        SUM(CASE WHEN is_winner = 0 THEN 1 ELSE 0 END) as non_winners
        FROM nominations");
    $win_loss = $stmt2->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'top_actors' => $top_actors,
        'win_loss' => [
            ['label' => 'Câștigători', 'count' => (int)$win_loss['winners']],
            ['label' => 'Doar nominalizați', 'count' => (int)$win_loss['non_winners']]
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Eroare la preluarea statisticilor: ' . $e->getMessage()]);
}
?>