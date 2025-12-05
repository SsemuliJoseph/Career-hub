<?php
/**
 * Fetch External Jobs API Endpoint
 * GET /api/v1/fetch_external_jobs.php
 * 
 * Fetches jobs from external APIs (Adzuna, JSearch) and optionally imports them
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../classes/autoload.php';
require_once __DIR__ . '/../../includes/session.php';

// Require admin authentication
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Admin authentication required']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $apiService = new ExternalAPIService();
    
    $query = $_GET['query'] ?? 'software developer';
    $source = $_GET['source'] ?? 'adzuna'; // adzuna or jsearch
    $ttl    = isset($_GET['ttl']) ? (int)$_GET['ttl'] : null; // optional override cache ttl
    $import = isset($_GET['import']) && $_GET['import'] === 'true';
    
    $externalJobs = [];
    
    if ($ttl) { $apiService->setCacheTtl($ttl); }

    if ($source === 'adzuna') {
        $adzunaData = $apiService->fetchAdzunaJobs($query);
        if ($adzunaData) {
            $externalJobs = $apiService->parseAdzunaJobs($adzunaData);
        }
    } elseif ($source === 'jsearch') {
        $jSearchData = $apiService->fetchJSearchJobs($query);
        if ($jSearchData) {
            $externalJobs = $apiService->parseJSearchJobs($jSearchData);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid source. Use adzuna or jsearch']);
        exit;
    }
    
    $importSummary = ['imported' => 0, 'skipped' => 0];
    if ($import && !empty($externalJobs)) {
        $importSummary = $apiService->importJobs($externalJobs);
    }
    
    echo json_encode([
        'success' => true,
        'source' => $source,
        'query' => $query,
        'count' => count($externalJobs),
        'imported' => $importSummary['imported'],
        'skipped' => $importSummary['skipped'],
        'jobs' => $externalJobs,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
}
