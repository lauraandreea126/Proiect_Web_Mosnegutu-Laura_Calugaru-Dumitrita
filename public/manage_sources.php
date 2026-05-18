<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// Verificare autentificare admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Returnează toate sursele
            $stmt = $pdo->query("SELECT * FROM news_sources ORDER BY id DESC");
            $sources = $stmt->fetchAll();
            echo json_encode($sources);
            break;

        case 'POST':
            // Preluare date (suport JSON și form-data)
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $_POST['action'] ?? $input['action'] ?? 'create';
            
            if ($action === 'delete') {
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
                // Creare sursă nouă
                $name = $_POST['name'] ?? $input['name'] ?? '';
                $url = $_POST['url'] ?? $input['url'] ?? '';

                if (empty($name) || empty($url)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Numele și URL-ul sunt obligatorii.']);
                    exit;
                }

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
            // Suport opțional pentru metoda DELETE pură
            parse_str(file_get_contents("php://input"), $deleteVars);
            $id = $deleteVars['id'] ?? null;
            
            if (!$id) {
                // Încercăm să luăm din query string dacă nu e în body
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
            http_response_code(405);
            echo json_encode(['error' => 'Metoda nepermisă.']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Eroare bază de date.', 'message' => $e->getMessage()]);
}
?>
