<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php'; // $conn (mysqli)
require_once __DIR__ . '/../../includes/csrf.php';

// Simple endpoint to save the logged-in user's theme preference
// POST body: { theme: 'light'|'dark' }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (empty($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// CSRF token (support JSON or form header)
$tokenPayload = null;
if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $raw = json_decode(file_get_contents('php://input'), true);
    $tokenPayload = $raw['csrf_token'] ?? null;
}
$token = $tokenPayload ?? ($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
if (!verify_csrf_token($token)) { http_response_code(403); echo json_encode(['error'=>'Invalid CSRF token']); exit; }

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$theme = $input['theme'] ?? null;
if (!in_array($theme, ['light', 'dark'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid theme']);
    exit;
}

try {
    $stmt = $conn->prepare('UPDATE users SET theme = ?, updatedAt = NOW() WHERE id = ?');
    $stmt->bind_param('si', $theme, $_SESSION['user']['id']);
    $stmt->execute();
    $stmt->close();

    // Update session copy
    $_SESSION['user']['theme'] = $theme;

    echo json_encode(['success' => true, 'theme' => $theme]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
    exit;
}
?>