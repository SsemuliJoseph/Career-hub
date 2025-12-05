<?php
/**
 * Statistics API Endpoint
 * GET /api/v1/stats.php
 * 
 * Provides statistics data for external applications to process
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
    $jobModel = new Job();
    $applicationModel = new Application();
    $userModel = new User();
    
    // Get statistics
    $jobStats = $jobModel->getStatistics();
    $applicationStats = $applicationModel->getStatistics();
    
    // Get user counts by role
    $students = $userModel->getByRole('student');
    $employers = $userModel->getByRole('employer');
    
    $stats = [
        'jobs' => [
            'total' => $jobStats['total_jobs'] ?? 0,
            'full_time' => $jobStats['full_time_count'] ?? 0,
            'internships' => $jobStats['internship_count'] ?? 0,
            'part_time' => $jobStats['part_time_count'] ?? 0,
            'companies' => $jobStats['total_companies'] ?? 0
        ],
        'applications' => [
            'total' => $applicationStats['total_applications'] ?? 0,
            'pending' => $applicationStats['pending_count'] ?? 0,
            'reviewed' => $applicationStats['reviewed_count'] ?? 0,
            'accepted' => $applicationStats['accepted_count'] ?? 0,
            'rejected' => $applicationStats['rejected_count'] ?? 0
        ],
        'users' => [
            'students' => count($students),
            'employers' => count($employers),
            'total' => count($students) + count($employers)
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
}
