<?php
session_start();
header('Content-Type: application/json');

$response = [
    'logged_in' => isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true,
    'username' => $_SESSION['username'] ?? null
];

echo json_encode($response);
?>
