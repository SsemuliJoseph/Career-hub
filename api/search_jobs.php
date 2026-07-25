<?php
/**
 * api/search_jobs.php - Job search endpoint with wildcard matching
 *
 * Teaching notes: Demonstrates LIKE queries with CONCAT for wildcard search
 * - Uses prepared statements even for LIKE queries to prevent SQL injection
 * - Searches across multiple columns (title, location, industry)
 * - Filters by status to only show active jobs
 * - Limits results to prevent performance issues on large datasets
 *
 * Security:
 * - CONCAT('%', ?, '%') builds LIKE pattern server-side (safe)
 * - bind_param with multiple identical parameters (query repeated 3 times)
 * - Direct user input NEVER interpolated into SQL string
 *
 * Performance notes:
 * - LIKE '%term%' requires full table scan (slow on large tables)
 * - Consider full-text indexes (FULLTEXT) or external search (Elasticsearch) for production
 * - LIMIT 10 caps result set size for fast response times
 */

// Load database connection
require_once __DIR__ . '/../includes/db.php';

/**
 * Get search query from URL parameter
 * - $_GET['q'] contains the query string from ?q=developer
 * - isset() checks if key exists to avoid undefined index warnings
 * - trim() removes leading/trailing whitespace
 */
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Set Content-Type header for JSON API response
header('Content-Type: application/json');

/**
 * Early return empty array if no query provided
 * - Prevents unnecessary database query
 * - === checks for exact match (type and value)
 */
if ($q === '') {
    echo json_encode([]);
    exit;
}

/**
 * Prepared statement with LIKE wildcard search
 * - CONCAT('%', ?, '%') builds '%developer%' pattern at SQL level (safe from injection)
 * - OR clauses search multiple columns
 * - status = 'Open' filters to active jobs only
 * - ORDER BY createdAt DESC sorts newest first
 * - LIMIT 10 caps results for performance
 */
$stmt = $conn->prepare("
    SELECT id, title, location, industry, type
    FROM jobs
    WHERE title LIKE CONCAT('%', ?, '%')
       OR location LIKE CONCAT('%', ?, '%')
       OR industry LIKE CONCAT('%', ?, '%')
    AND status = 'Open'
    ORDER BY createdAt DESC
    LIMIT 10
");

/**
 * Bind same parameter to all 3 placeholders
 * - bind_param types: 'sss' = 3 strings
 * - $q is repeated 3 times (one for each LIKE clause)
 * - This is safe because bind_param escapes each value independently
 */
$stmt->bind_param('sss', $q, $q, $q);

// Execute the query
$stmt->execute();

// Get result set
$result = $stmt->get_result();

// Initialize empty array to collect matching jobs
$jobs = [];

/**
 * Fetch all matching rows into array
 * - while loop iterates until fetch_assoc() returns null
 * - Each row is appended to $jobs array
 */
while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}

/**
 * Return JSON array directly (not wrapped in object)
 * - Frontend expects array of job objects
 * - json_encode() converts PHP array to JSON
 */
echo json_encode($jobs);
?>
