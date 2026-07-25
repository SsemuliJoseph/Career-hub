<?php
/**
 * api/get_jobs.php - Simple jobs list endpoint
 *
 * Teaching notes: This is a minimal API endpoint that returns all jobs in JSON format.
 * It demonstrates:
 * - Direct SQL query (acceptable when no user input is involved)
 * - while loop to accumulate results into an array
 * - JSON response with proper Content-Type header
 *
 * Comparison to api/jobs.php:
 * - This file only supports GET and returns all data
 * - api/jobs.php supports GET (single/all) and POST (create) with auth/CSRF
 * - In a real app, consolidate endpoints to reduce duplication
 */

// Load database connection ($conn is mysqli object)
require_once __DIR__ . '/../includes/db.php';

/**
 * Query all jobs ordered by most recent first
 * - query() is used here because there are no user inputs to parameterize
 * - For queries with user input, always use prepare() + bind_param() to prevent SQL injection
 */
$result = $conn->query("SELECT * FROM jobs ORDER BY created_at DESC");

// Initialize empty array to collect job rows
$jobs = [];

/**
 * fetch_assoc() returns next row as associative array or null when exhausted
 * - while loop continues until fetch_assoc returns null
 * - Each iteration appends one row to $jobs array
 */
while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}

/**
 * Set Content-Type header to application/json
 * - Tells clients (browsers, fetch API) that response body is JSON
 * - Should be sent before echo to avoid "headers already sent" warnings
 */
header('Content-Type: application/json');

/**
 * json_encode() converts PHP array to JSON string
 * - Wrap jobs array in an object with 'jobs' key for consistent API structure
 */
echo json_encode(['jobs' => $jobs]);
?>
