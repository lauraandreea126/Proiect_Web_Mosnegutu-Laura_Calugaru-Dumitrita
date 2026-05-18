<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// Verificăm dacă cererea este de tip POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metoda nepermisă. Folosiți POST.']);
    exit;
}

// Preluăm datele din POST (suport pentru form-data și json)
$input = json_decode(file_get_contents('php://input'), true);
$username = $_POST['username'] ?? $input['username'] ?? '';
$password = $_POST['password'] ?? $input['password'] ?? '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Username și parola sunt obligatorii.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Autentificare reușită
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_id'] = $user['id'];

        echo json_encode([
            'success' => true,
            'message' => 'Autentificare reușită.',
            'user' => [
                'username' => $user['username']
            ]
        ]);
    } else {
        // Autentificare eșuată
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Username sau parolă incorectă.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Eroare de server.', 'message' => $e->getMessage()]);
}
?>
