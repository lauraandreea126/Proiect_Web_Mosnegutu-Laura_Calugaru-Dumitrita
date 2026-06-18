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

// Initial authentication check
if (!isset($_SESSION['user_id'])) {
    sendJsonResponse(['message' => 'Unauthorized: User must be logged in'], 401);
}

$user_id = $_SESSION['user_id'];

// Get data from JSON input or POST/GET depending on method
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'POST':
        // Determine the action
        $action = $_POST['action'] ?? $input['action'] ?? null;

        if ($action === 'change_password') {
            $old_password = $_POST['old_password'] ?? $input['old_password'] ?? null;
            $new_password = $_POST['new_password'] ?? $input['new_password'] ?? null;

            if (!$old_password || !$new_password) {
                sendJsonResponse(['message' => 'Bad Request: Both old_password and new_password are required'], 400);
            }

            try {
                // Fetch the current password hash for the user
                $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    sendJsonResponse(['message' => 'Not Found: User not found'], 404);
                }

                // Verify the old password
                if (password_verify($old_password, $user['password_hash'])) {
                    // Generate new hash and update
                    $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
                    $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $updateStmt->execute([$new_hash, $user_id]);
                    sendJsonResponse(['message' => 'Password changed successfully']);
                } else {
                    sendJsonResponse(['message' => 'Unauthorized: Incorrect old password'], 401);
                }
            } catch (PDOException $e) {
                sendJsonResponse(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
            }

        } elseif ($action === 'add_favorite') {
            $actor_id = $_POST['actor_id'] ?? $input['actor_id'] ?? null;

            if (!$actor_id || !is_numeric($actor_id)) {
                sendJsonResponse(['message' => 'Bad Request: Valid actor_id is required'], 400);
            }

            try {
                // Check if actor exists first
                $checkActor = $pdo->prepare("SELECT id FROM actors WHERE id = ?");
                $checkActor->execute([$actor_id]);
                if (!$checkActor->fetch()) {
                     sendJsonResponse(['message' => 'Not Found: Actor does not exist'], 404);
                }

                $stmt = $pdo->prepare("INSERT INTO favorite_actors (user_id, actor_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $actor_id]);
                sendJsonResponse(['message' => 'Actor added to favorites successfully'], 201);
            } catch (PDOException $e) {
                if ($e->getCode() == '23000') { // Constraint violation (e.g., already favorited)
                    sendJsonResponse(['message' => 'Bad Request: Actor is already in favorites'], 400);
                } else {
                    sendJsonResponse(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
                }
            }

        } else {
            sendJsonResponse(['message' => 'Bad Request: Invalid or missing action'], 400);
        }
        break;

    case 'DELETE':
        // For DELETE, action might be part of the query string or body
        $action = $_GET['action'] ?? $input['action'] ?? null;
        
        if ($action === 'remove_favorite') {
             $actor_id = $_GET['actor_id'] ?? $input['actor_id'] ?? null;

            if (!$actor_id || !is_numeric($actor_id)) {
                sendJsonResponse(['message' => 'Bad Request: Valid actor_id is required'], 400);
            }

            try {
                $stmt = $pdo->prepare("DELETE FROM favorite_actors WHERE user_id = ? AND actor_id = ?");
                $stmt->execute([$user_id, $actor_id]);

                if ($stmt->rowCount() > 0) {
                    sendJsonResponse(['message' => 'Actor removed from favorites successfully']);
                } else {
                    sendJsonResponse(['message' => 'Not Found: Actor was not in favorites'], 404);
                }
            } catch (PDOException $e) {
                sendJsonResponse(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
            }
        } else {
             sendJsonResponse(['message' => 'Bad Request: Invalid or missing action for DELETE'], 400);
        }
        break;

    default:
        sendJsonResponse(['message' => 'Method Not Allowed'], 405);
        break;
}
