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

// Preluăm datele (JS trimite raw JSON body, nu form-data standard)
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

$username = $_POST['username'] ?? ($input['username'] ?? '');
$password = $_POST['password'] ?? ($input['password'] ?? '');

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

        // Verificăm dacă cererea a venit prin AJAX/Fetch
        // JS fetch modern adesea nu trimite X-Requested-With by default, ne bazăm pe Content-Type sau body JSON valid
        $isJson = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;

        if ($isJson || !empty($inputJSON)) {
            echo json_encode([
                'success' => true,
                'message' => 'Autentificare reușită.',
                'user' => [
                    'username' => $user['username']
                ]
            ]);
        } else {
            // Fallback pentru submit normal de formular
            header('Location: admin.php');
            exit;
        }
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
