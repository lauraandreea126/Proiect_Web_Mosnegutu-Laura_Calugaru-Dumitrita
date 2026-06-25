<?php
// public/api/register.php
header('Content-Type: application/json');

// Corectarea căii de import pentru fișierul de configurare a bazei de date
require_once __DIR__ . '/../../config/db.php';

// Asigurăm că primim exclusiv cereri de tip POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodă nepermisă. Folosește POST.']);
    exit;
}

// Preluarea și decodarea corpului cererii JSON
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

$username = isset($input['username']) ? trim($input['username']) : '';
$email = isset($input['email']) ? trim($input['email']) : '';
$password = isset($input['password']) ? trim($input['password']) : '';

// 1. Validări de bază
if (empty($username) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Toate câmpurile (Username, Email, Parolă) sunt obligatorii.']);
    exit;
}

// 2. Validare format email conform standardului RFC
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Adresa de email introdusă nu are un format valid.']);
    exit;
}

try {
    // 3. Prevenirea duplicării numelui de utilizator (Prepared Statement)
    $stmtCheckUsername = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmtCheckUsername->execute(['username' => $username]);
    if ($stmtCheckUsername->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Acest nume de utilizator este deja folosit.']);
        exit;
    }

    // 4. Prevenirea duplicării adresei de email (Prepared Statement)
    $stmtCheckEmail = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmtCheckEmail->execute(['email' => $email]);
    if ($stmtCheckEmail->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Această adresă de email este deja înregistrată.']);
        exit;
    }

    // 5. Criptarea parolei prin algoritmul recomandat BCRYPT (securing data storage)
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // 6. Inserarea noului cont de client în baza de date
    $stmtInsert = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, 'client')");
    $stmtInsert->execute([
        'username' => $username,
        'email' => $email,
        'password_hash' => $passwordHash
    ]);

    // Adăugăm notificare de bun venit
    $newUserId = $pdo->lastInsertId();
    $stmtNotif = $pdo->prepare("INSERT INTO notificari (user_id, mesaj) VALUES (?, ?)");
    $stmtNotif->execute([$newUserId, "Bun venit pe platforma AwA! Contul dumneavoastră a fost creat cu succes."]);

    // Răspuns de succes în format JSON standardizat
    echo json_encode([
        'success' => true,
        'message' => "Contul pentru '$username' a fost creat cu succes!"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Eroare internă la salvarea datelor în DB: ' . $e->getMessage()]);
}
?>