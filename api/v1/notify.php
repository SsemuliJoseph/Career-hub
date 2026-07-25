<?php
/**
 * WebSocket Notification Trigger
 * POST /api/v1/notify.php
 * 
 * Sends real-time notifications through WebSocket
 * This is a helper endpoint to trigger WebSocket notifications from PHP
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../classes/autoload.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';

// Require authentication
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON payload
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (!$data) { $data = $_POST; }

    // CSRF check (allow header or body field)
    $token = $data['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!verify_csrf_token($token)) { http_response_code(403); echo json_encode(['error'=>'Invalid CSRF token']); exit; }
    
    if (!$data || !isset($data['type'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payload. Type is required.']);
        exit;
    }
    
    // Send notification via WebSocket
    // Note: This requires the WebSocket server to be running
    // For now, we'll store it in a queue or database for the WebSocket server to pick up
    
    $notification = [
        'type' => $data['type'],
        'data' => $data['data'] ?? [],
        'userId' => $data['userId'] ?? null,
        'channel' => $data['channel'] ?? null,
        'timestamp' => date('Y-m-d H:i:s'),
        'sent' => false
    ];
    
    // Store notification in database or cache
    // For simplicity, we'll write to a file that the WebSocket server can read
    $notificationFile = __DIR__ . '/../../cache/notifications.json';
    $notifications = [];
    
    if (file_exists($notificationFile)) {
        $notifications = json_decode(file_get_contents($notificationFile), true) ?? [];
    }
    
    $notifications[] = $notification;
    file_put_contents($notificationFile, json_encode($notifications, JSON_PRETTY_PRINT));
    
    echo json_encode([
        'success' => true,
        'message' => 'Notification queued',
        'notification' => $notification
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
}
