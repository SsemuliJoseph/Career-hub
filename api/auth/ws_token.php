<?php
// Path: api/auth/ws_token.php
// Issues a short-lived WebSocket auth token bound to the current user session.
// GET only; returns { token, expiresAt }

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/session.php';

if (empty($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = (int)$_SESSION['user']['id'];
$token = bin2hex(random_bytes(16));
$expires = time() + 900; // 15 minutes

$dir = __DIR__ . '/../../cache/ws_tokens';
if (!is_dir($dir)) {
    @mkdir($dir, 0755, true);
}
$file = $dir . '/' . $token . '.json';
$meta = [
    'userId' => $userId,
    'issuedAt' => time(),
    'expiresAt' => $expires,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
];
file_put_contents($file, json_encode($meta, JSON_PRETTY_PRINT));

echo json_encode(['token' => $token, 'expiresAt' => $expires]);
exit;
?>