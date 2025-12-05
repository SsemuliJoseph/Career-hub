<?php
// api/session/ping.php
// Endpoint to keep session alive during user activity

require_once __DIR__ . '/../../includes/session.php';

header('Content-Type: application/json');

// Update last activity timestamp
if (isset($_SESSION['user'])) {
    $_SESSION['LAST_ACTIVITY'] = time();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Session refreshed',
        'timestamp' => $_SESSION['LAST_ACTIVITY']
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Not authenticated'
    ]);
}
