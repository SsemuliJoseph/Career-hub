<?php
/**
 * Export Applications API Endpoint
 * GET /api/v1/export_applications.php
 * 
 * Exports application data for external applications
 * Requires authentication and proper permissions
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../classes/autoload.php';
require_once __DIR__ . '/../../includes/session.php';

// API Authentication
$apiToken = $_GET['api_token'] ?? $_SERVER['HTTP_X_API_TOKEN'] ?? '';
$validTokens = ['sk_live_your_token_here', 'ext_token_456'];

if (!in_array($apiToken, $validTokens)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $applicationModel = new Application();
    
    $format = $_GET['format'] ?? 'json';
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
    $jobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : null;
    $status = $_GET['status'] ?? null;
    
    // Fetch applications based on filters
    if ($userId) {
        $applications = $applicationModel->getByUser($userId);
    } elseif ($jobId) {
        $applications = $applicationModel->getByJob($jobId);
    } else {
        $applications = $applicationModel->all(100, 0);
    }
    
    // Filter by status if provided
    if ($status && !empty($applications)) {
        $applications = array_filter($applications, function($app) use ($status) {
            return $app['status'] === $status;
        });
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($applications),
        'data' => array_values($applications),
        'meta' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'filters' => [
                'user_id' => $userId,
                'job_id' => $jobId,
                'status' => $status
            ]
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
}
