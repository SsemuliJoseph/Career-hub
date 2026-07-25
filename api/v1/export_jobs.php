<?php
/**
 * Export Jobs API Endpoint
 * GET /api/v1/export_jobs.php
 * 
 * Exports job data for external applications to consume
 * Supports JSON, CSV, and XML formats
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../classes/autoload.php';
require_once __DIR__ . '/../../includes/session.php';

// API Authentication (simple token-based)
$apiToken = $_GET['api_token'] ?? $_SERVER['HTTP_X_API_TOKEN'] ?? '';
$validTokens = ['sk_live_your_token_here', 'ext_token_456']; // Configure your API tokens

if (!in_array($apiToken, $validTokens)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized. Valid API token required.']);
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
    
    // Get query parameters
    $format = $_GET['format'] ?? 'json';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $type = $_GET['type'] ?? null;
    $company = $_GET['company'] ?? null;
    
    // Fetch jobs based on filters
    if ($type) {
        $jobs = $jobModel->getByType($type);
    } elseif ($company) {
        $jobs = $jobModel->getByCompany($company);
    } else {
        $jobs = $jobModel->all($limit, $offset);
    }
    
    // Format response
    switch ($format) {
        case 'csv':
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="jobs_export.csv"');
            
            $output = fopen('php://output', 'w');
            
            // CSV headers
            if (!empty($jobs)) {
                fputcsv($output, array_keys($jobs[0]));
                
                foreach ($jobs as $job) {
                    fputcsv($output, $job);
                }
            }
            
            fclose($output);
            break;
            
        case 'xml':
            header('Content-Type: application/xml');
            
            $xml = new SimpleXMLElement('<jobs/>');
            
            foreach ($jobs as $jobData) {
                $job = $xml->addChild('job');
                foreach ($jobData as $key => $value) {
                    $job->addChild($key, htmlspecialchars($value ?? ''));
                }
            }
            
            echo $xml->asXML();
            break;
            
        case 'json':
        default:
            echo json_encode([
                'success' => true,
                'count' => count($jobs),
                'data' => $jobs,
                'meta' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ], JSON_PRETTY_PRINT);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
}
