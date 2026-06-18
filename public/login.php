<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// verificare post
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metoda nepermisă. Folosiți POST.']);
    exit;
}

// Rate Limiting (Brute Force Protection)
if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
    if (isset($_SESSION['lockout_time']) && (time() - $_SESSION['lockout_time'] < 900)) { // 900 secunde = 15 minute
        http_response_code(429);
        echo json_encode(['error' => 'Prea multe încercări eșuate. Contul este blocat pentru 15 minute.']);
        exit;
    } else {
        // Au trecut 15 minute, resetăm încercările
        unset($_SESSION['login_attempts']);
        unset($_SESSION['lockout_time']);
    }
}

// preluare date
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
    // Am adăugat și coloana 'role' în selecție pentru a o putea seta în sesiune
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Resetăm încercările de login la succes
        unset($_SESSION['login_attempts']);
        unset($_SESSION['lockout_time']);

        // autentificare reusita
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_id'] = $user['id'];
        
        if (isset($user['role'])) {
            $_SESSION['role'] = $user['role'];
        }

        // verificare daca cererea este AJAX/JSON
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        $isJson = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;

        if ($isJson || $isAjax) {
            echo json_encode([
                'success' => true,
                'message' => 'Autentificare reușită.',
                'user' => [
                    'username' => $user['username'],
                    'role' => $user['role'] ?? 'user'
                ]
            ]);
        } else {
            // fallback pentru formular normal (navigare directa)
            header('Location: admin.php');
            exit;
        }
    } else {
        // autentificare esuata - incrementam numărul de încercări
        $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] + 1 : 1;
        
        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['lockout_time'] = time();
        }

        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Username sau parolă incorectă.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Eroare de server.', 'message' => $e->getMessage()]);
}
?>