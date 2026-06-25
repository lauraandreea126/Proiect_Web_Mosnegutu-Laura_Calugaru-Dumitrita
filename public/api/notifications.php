<?php
// Creăm un folder local pentru sesiuni dacă nu există
$sessionPath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'sessions';
if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0777, true);
}
// Forțăm PHP să salveze sesiunile în acest folder local din proiect
ini_set('session.save_path', $sessionPath);

require_once __DIR__ . '/../../config/db.php';
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        // Mark all as read
        $stmt = $pdo->prepare("UPDATE notificari SET citit = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
        echo json_encode(['success' => true]);
        exit();
    } else {
        // GET: Fetch all notifications from database
        $stmt = $pdo->prepare("SELECT * FROM notificari WHERE user_id = ? ORDER BY data_creare DESC LIMIT 20");
        $stmt->execute([$user_id]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($notifications);
        exit();
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
}
