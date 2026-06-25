<?php
// ============================================================
// pornire sesiune (folosim folder local de sesiuni pentru proiect)
// ============================================================

// creem un folder local pentru sesiuni daca nu exista
$sessionPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sessions';
if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0777, true);
}
// fortam php sa salveze sesiunile in acest folder local din proiect
ini_set('session.save_path', $sessionPath);

session_set_cookie_params([
    'lifetime' => 86400,
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();
require_once __DIR__ . '/../config/db.php';

// acceptam doar cereri post pentru login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Metoda nepermisă. Folosiți POST.']);
    exit;
}

// ============================================================
// rate limiting (protectie impotriva atacurilor cu forta bruta)
// ============================================================

if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
    // daca a trecut mai putin de 15 minute de la blocare, refuzam logarea
    if (isset($_SESSION['lockout_time']) && (time() - $_SESSION['lockout_time'] < 900)) {
        http_response_code(429);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Prea multe încercări eșuate. Blocat pentru 15 minute.']);
        exit;
    } else {
        // au trecut 15 minute, resetam contorul de incercari
        unset($_SESSION['login_attempts']);
        unset($_SESSION['lockout_time']);
    }
}

// ============================================================
// preluare date de logare (suporta atat formular cat si json)
// ============================================================

// preluare date din formular sau din json
$inputJSON = file_get_contents('php://input');
$input     = json_decode($inputJSON, true);

$username = $_POST['username'] ?? ($input['username'] ?? '');
$password = $_POST['password'] ?? ($input['password'] ?? '');

if (empty($username) || empty($password)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Username și parola sunt obligatorii.']);
    exit;
}

try {
    // cautam utilizatorul in baza de date locala sqlite
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    // verificam parola prin algoritmul nativ securizat
    if ($user && password_verify($password, $user['password_hash'])) {
        // ============================================================
        // logare cu succes
        // ============================================================

        // resetam incercarile la succes
        unset($_SESSION['login_attempts']);
        unset($_SESSION['lockout_time']);

        // setam variabilele de sesiune globale
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'] ?? 'client';

        // stabilim daca utilizatorul este admin (din rol sau din nume de utilizator)
        if ($_SESSION['role'] === 'admin' || strpos(strtolower($user['username']), 'admin') !== false) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['role']            = 'admin';
            $redirectUrl                 = 'admin.php';
        } else {
            $_SESSION['admin_logged_in'] = false;
            $_SESSION['role']            = 'client';
            $redirectUrl                 = 'index.php';
        }

        // verificam tipul cererii (ajax vs formular clasic)
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        $isJson = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;

        if ($isJson || $isAjax) {
            // raspuns json pentru cereri ajax/javascript
            header('Content-Type: application/json');
            echo json_encode([
                'success'  => true,
                'message'  => 'Autentificare reușită.',
                'redirect' => $redirectUrl
            ]);
            exit;
        } else {
            // daca trimiterea s-a facut prin formular normal, fortam redirectionarea de pe server
            header('Location: ' . $redirectUrl);
            exit;
        }
    } else {
        // ============================================================
        // logare nereusita - incrementam contorul de incercari
        // ============================================================

        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['lockout_time'] = time();
        }

        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        $isJson = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;

        if ($isAjax || $isJson) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Username sau parolă incorectă.']);
        } else {
            // fallback simplu pentru formular clasic fara javascript
            echo "<script>alert('Username sau parolă incorectă!'); window.location.href='admin.php';</script>";
        }
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Eroare de server.', 'message' => $e->getMessage()]);
    exit;
}
?>