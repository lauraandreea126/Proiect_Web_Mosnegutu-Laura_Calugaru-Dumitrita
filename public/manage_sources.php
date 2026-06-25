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
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// verificare autentificare admin - doar adminul gestioneaza sursele de stiri
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // ============================================================
            // returnam toate sursele de stiri existente
            // ============================================================
            $stmt    = $pdo->query("SELECT * FROM news_sources ORDER BY id DESC");
            $sources = $stmt->fetchAll();
            echo json_encode($sources);
            break;

        case 'POST':
            // ============================================================
            // creare sau stergere sursa (in functie de parametrul action)
            // ============================================================

            // preluare date din post sau din json
            $input  = json_decode(file_get_contents('php://input'), true);
            $action = $_POST['action'] ?? $input['action'] ?? 'create';

            if ($action === 'delete') {
                // stergere sursa pe baza id-ului
                $id = $_POST['id'] ?? $input['id'] ?? null;
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID-ul este obligatoriu pentru ștergere.']);
                    exit;
                }

                $stmt = $pdo->prepare("DELETE FROM news_sources WHERE id = :id");
                $stmt->execute(['id' => $id]);
                echo json_encode(['success' => true, 'message' => 'Sursă ștearsă cu succes.']);
            } else {
                // creare sursa noua
                $name = $_POST['name'] ?? $input['name'] ?? '';
                $url  = $_POST['url'] ?? $input['url'] ?? '';

                if (empty($name) || empty($url)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Numele și URL-ul sunt obligatorii.']);
                    exit;
                }

                // validam ca url-ul are un format corect
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'URL-ul furnizat nu este valid.']);
                    exit;
                }

                $stmt = $pdo->prepare("INSERT INTO news_sources (name, url) VALUES (:name, :url)");
                $stmt->execute(['name' => $name, 'url' => $url]);
                echo json_encode(['success' => true, 'message' => 'Sursă adăugată cu succes.', 'id' => $pdo->lastInsertId()]);
            }
            break;

        case 'DELETE':
            // ============================================================
            // suport pentru metoda http delete (alternativa la post+action=delete)
            // ============================================================
            parse_str(file_get_contents("php://input"), $deleteVars);
            $id = $deleteVars['id'] ?? null;

            if (!$id) {
                // verificare id si in query string
                $id = $_GET['id'] ?? null;
            }

            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID-ul este obligatoriu pentru ștergere.']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM news_sources WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['success' => true, 'message' => 'Sursă ștearsă cu succes.']);
            break;

        default:
            // orice alta metoda http nu este suportata
            http_response_code(405);
            echo json_encode(['error' => 'Metoda nepermisă.']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Eroare bază de date.', 'message' => $e->getMessage()]);
}
?>