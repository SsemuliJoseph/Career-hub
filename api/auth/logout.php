<?php
// Path: api/logout.php
// Destroys session and clears remember-me cookie if set. Call via GET or POST.

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php'; // $conn (mysqli)

// If remember cookie exists, clear token from DB
if (!empty($_COOKIE['remember_me'])) {
    // cookie format: "<userId>:<token>"
    [$uid, $token] = explode(':', $_COOKIE['remember_me']) + [null, null];
    if ($uid) {
        // remove token stored in DB for that user (optional)
    $stmt = $conn->prepare("UPDATE users SET remember_token = NULL, remember_expires_at = NULL WHERE id = ?");
    $uidInt = (int)$uid;
    $stmt->bind_param("i", $uidInt);
    $stmt->execute();
    $stmt->close();
    }
    setcookie('remember_me', '', time() - 3600, '/');
}

// Destroy session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();

echo json_encode(['success' => true]);
exit;
