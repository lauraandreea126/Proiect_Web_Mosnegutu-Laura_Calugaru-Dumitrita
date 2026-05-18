<?php
/**
 * Utilitar CLI pentru crearea unui cont de administrator.
 * Utilizare: php scripts/create_admin.php [username] [password]
 */

require_once __DIR__ . '/../config/db.php';

if (php_sapi_name() !== 'cli') {
    die("Acest script poate fi rulat doar din linia de comandă.\n");
}

$username = $argv[1] ?? 'admin';
$password = $argv[2] ?? 'secret123';

$passwordHash = password_hash($password, PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)");
    $stmt->execute([
        'username' => $username,
        'password_hash' => $passwordHash
    ]);
    echo "Administrator creat cu succes!\n";
    echo "Username: $username\n";
    echo "Parola: $password\n";
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        echo "Eroare: Utilizatorul '$username' există deja.\n";
    } else {
        echo "Eroare la crearea administratorului: " . $e->getMessage() . "\n";
    }
}
?>
