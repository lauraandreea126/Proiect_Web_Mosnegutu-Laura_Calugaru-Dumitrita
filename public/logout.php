<?php
// public/logout.php

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

// ============================================================
// deconectare completa a utilizatorului
// ============================================================

// 1. stergerea tuturor variabilelor de sesiune din memorie
$_SESSION = array();

// 2. expirarea cookie-ului de sesiune din browserul clientului
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. distrugerea fizica a sesiunii de pe server
session_destroy();

// 4. redirectionare curata spre pagina principala
header('Location: index.php');
exit;
?>