<?php
/**
 * Admin-only environment check for API keys
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../classes/autoload.php';
require_once __DIR__ . '/../../includes/session.php';

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Admin authentication required']);
    exit;
}

$response = [
    'ADZUNA_APP_ID' => ($v = getenv('ADZUNA_APP_ID') ?: $_ENV['ADZUNA_APP_ID'] ?? $_SERVER['ADZUNA_APP_ID'] ?? '') ? 'set' : 'missing',
    'ADZUNA_APP_KEY' => ($v = getenv('ADZUNA_APP_KEY') ?: $_ENV['ADZUNA_APP_KEY'] ?? $_SERVER['ADZUNA_APP_KEY'] ?? '') ? 'set' : 'missing',
    'RAPIDAPI_KEY' => ($v = getenv('RAPIDAPI_KEY') ?: $_ENV['RAPIDAPI_KEY'] ?? $_SERVER['RAPIDAPI_KEY'] ?? '') ? 'set' : 'missing',
];

// If present, show first 4 characters for sanity check (not full value)
foreach (['ADZUNA_APP_ID','ADZUNA_APP_KEY','RAPIDAPI_KEY'] as $k) {
    $v = getenv($k) ?: $_ENV[$k] ?? $_SERVER[$k] ?? '';
    if ($v) {
        $response[$k . '_preview'] = substr($v, 0, 4) . '…';
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);
