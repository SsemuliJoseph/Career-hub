<?php
/**
 * Import Jobs API Endpoint
 * POST /api/v1/import_jobs.php
 * 
 * Imports jobs from external sources or allows external applications to push job data
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../classes/autoload.php';
require_once __DIR__ . '/../../includes/session.php';

// API Authentication
$apiToken = $_POST['api_token'] ?? $_SERVER['HTTP_X_API_TOKEN'] ?? '';
$validTokens = ['sk_live_your_token_here', 'ext_token_456'];

if (!in_array($apiToken, $validTokens)) {
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
    $jobModel = new Job();
    
    // Get JSON payload
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON payload']);
        exit;
    }
    
    $imported = 0;
    $failed = 0;
    $errors = [];
    
    // Check if single job or array of jobs
    $jobs = isset($data['jobs']) ? $data['jobs'] : [$data];
    
    foreach ($jobs as $jobData) {
        // Validate required fields
        if (empty($jobData['title']) || empty($jobData['company'])) {
            $failed++;
            $errors[] = 'Missing required fields: title or company';
            continue;
        }
        
        // Prepare job data
        $job = [
            'title' => $jobData['title'],
            'company' => $jobData['company'],
            'description' => $jobData['description'] ?? '',
            'location' => $jobData['location'] ?? '',
            'type' => $jobData['type'] ?? 'full-time',
            'salary_min' => $jobData['salary_min'] ?? null,
            'salary_max' => $jobData['salary_max'] ?? null,
            'url' => $jobData['url'] ?? '',
            'source' => $jobData['source'] ?? 'external',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $jobModel->create($job);
            $imported++;
        } catch (Exception $e) {
            $failed++;
            $errors[] = $e->getMessage();
        }
    }
    
    echo json_encode([
        'success' => true,
        'imported' => $imported,
        'failed' => $failed,
        'errors' => $errors,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
}
