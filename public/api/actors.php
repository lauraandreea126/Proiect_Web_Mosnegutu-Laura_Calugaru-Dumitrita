<?php
require_once __DIR__ . '/../../config/db.php';
session_start();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Helper function for sending JSON responses
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

// Check for admin authorization
function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true ||
           isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

switch ($method) {
    case 'GET':
        // Select all actors
        $stmt = $pdo->query("SELECT id, name, tmdb_id, bio, image_url FROM actors");
        $actors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendJsonResponse($actors);
        break;

    case 'POST':
        if (!isAdmin()) {
            sendJsonResponse(['message' => 'Unauthorized: Admin access required'], 401);
        }

        // Get data from POST or JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $_POST['name'] ?? $input['name'] ?? null;
        $bio = $_POST['bio'] ?? $input['bio'] ?? null;
        $image_url = $_POST['image_url'] ?? $input['image_url'] ?? null;
        $tmdb_id = $_POST['tmdb_id'] ?? $input['tmdb_id'] ?? null; // Assuming tmdb_id can be sent via POST

        if (!$name || !$bio || !$image_url) {
            sendJsonResponse(['message' => 'Bad Request: Name, bio, and image_url are required'], 400);
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO actors (name, tmdb_id, bio, image_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $tmdb_id, $bio, $image_url]);
            sendJsonResponse(['message' => 'Actor created successfully', 'id' => $pdo->lastInsertId()], 201);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { // SQLite constraint violation for UNIQUE (e.g., name)
                 sendJsonResponse(['message' => 'Bad Request: Actor with this name already exists.'], 400);
            } else {
                 sendJsonResponse(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
            }
        }
        break;

    case 'DELETE':
        if (!isAdmin()) {
            sendJsonResponse(['message' => 'Unauthorized: Admin access required'], 401);
        }

        // Get actor ID from query string or JSON input
        $id = $_GET['id'] ?? null;
        if (is_null($id)) {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;
        }

        if (!$id || !is_numeric($id)) {
            sendJsonResponse(['message' => 'Bad Request: Valid actor ID is required'], 400);
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM actors WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                sendJsonResponse(['message' => 'Actor deleted successfully']);
            } else {
                sendJsonResponse(['message' => 'Not Found: Actor with the specified ID does not exist'], 404);
            }
        } catch (PDOException $e) {
            sendJsonResponse(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
        break;

    default:
        sendJsonResponse(['message' => 'Method Not Allowed'], 405);
        break;
}
