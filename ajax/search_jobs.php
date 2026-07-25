<?php
/**
 * ajax/search_jobs.php - Legacy AJAX job search endpoint
 *
 * Teaching notes: This is a simple AJAX handler for autocomplete/search suggestions.
 * - Returns only job titles (not full job objects) for autocomplete dropdowns
 * - Uses CONCAT for wildcard LIKE pattern (same as api/search_jobs.php)
 * - LIMIT 10 caps results for performance
 *
 * Legacy vs Modern:
 * - AJAX endpoints like this were common before REST APIs
 * - Modern approach: consolidate into api/search_jobs.php with full job data
 * - This file may be redundant if api/search_jobs.php serves same purpose
 *
 * Security: Prepared statements prevent SQL injection
 */

// Load database connection (mysqli $conn global)
require_once('../includes/db.php');

// Set Content-Type header for JSON response
header('Content-Type: application/json; charset=utf-8');

/**
 * Get search query from URL parameter
 * - $_GET['q'] contains query string from ?q=developer
 * - trim() removes leading/trailing whitespace
 */
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

/**
 * Early return if no query provided
 * - Prevents unnecessary DB query
 * - Returns empty JSON array
 */
if ($q === '') {
    echo json_encode([]);
    exit;
}

/**
 * Prepared statement with LIKE wildcard search
 * - CONCAT('%', ?, '%') builds wildcard pattern server-side (safe)
 * - Only returns title column (minimal data for autocomplete)
 * - LIMIT 10 caps suggestions for UI responsiveness
 */
$stmt = $conn->prepare("SELECT title FROM jobs WHERE title LIKE CONCAT('%', ?, '%') LIMIT 10");

// Bind search query as string parameter
$stmt->bind_param("s", $q);

// Execute query
$stmt->execute();

// Get result set
$result = $stmt->get_result();

// Accumulate suggestions into array
$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row; // Each element is ['title' => '...']
}

// Return JSON array of suggestions
echo json_encode($suggestions);

// Clean up resources
$stmt->close();
$conn->close();
?>
