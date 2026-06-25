<?php
require_once __DIR__ . '/../config/db.php';

echo "Seeding users...\n";

$users = [
    [
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => 'admin123',
        'role' => 'admin'
    ],
    [
        'username' => 'client',
        'email' => 'client@example.com',
        'password' => 'client123',
        'role' => 'user'
    ]
];

try {
    foreach ($users as $u) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$u['username']]);
        if ($stmt->fetch()) {
            echo "User '{$u['username']}' already exists. Skipping.\n";
            continue;
        }

        $hash = password_hash($u['password'], PASSWORD_BCRYPT);
        $insert = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $insert->execute([$u['username'], $u['email'], $hash, $u['role']]);
        echo "Created user '{$u['username']}' with role '{$u['role']}'.\n";
    }
    echo "Seeding complete.\n";
} catch (PDOException $e) {
    echo "Error seeding users: " . $e->getMessage() . "\n";
}
