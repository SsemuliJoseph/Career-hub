<?php
/**
 * api/applications.php - Application submission endpoint
 *
 * Teaching notes: RESTful API for job applications (POST only)
 * - Validates authentication and CSRF token
 * - Uses prepared statements for secure INSERT
 * - Returns JSON response with success/error status
 * - Demonstrates NOW() MySQL function for automatic timestamps
 *
 * Security layers:
 * 1. Session check: only authenticated students can apply
 * 2. CSRF validation: prevents cross-site request forgery attacks
 * 3. Prepared statements: prevents SQL injection via parameter binding
 * 4. Input sanitization: trim() and type casting for data integrity
 */

// Set response type to JSON for API consumers
header('Content-Type: application/json; charset=utf-8');

// Load session (provides $_SESSION with user data and LAST_ACTIVITY tracking)
require_once __DIR__ . '/../includes/session.php';

// Load database connection (provides $conn mysqli object)
require_once __DIR__ . '/../includes/db.php';

// Load CSRF helpers (verify_csrf_token function)
require_once __DIR__ . '/../includes/csrf.php';

// Only accept POST method for application submission
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    /**
     * Authentication guard: verify user is logged in
     * - empty() returns true for null, false, '', 0, '0', [], etc.
     * - Always check auth before processing mutations (create/update/delete)
     */
    if (empty($_SESSION['user'])) {
        http_response_code(401); // 401 Unauthorized
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    /**
     * CSRF token validation
     * - Tokens can come from POST body (form) or HTTP header (fetch API)
     * - ?? null coalescing operator returns first non-null value
     * - verify_csrf_token() uses hash_equals() for timing-safe comparison
     */
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!verify_csrf_token($token)) { 
        http_response_code(403); // 403 Forbidden
        echo json_encode(['error' => 'Invalid CSRF token']); 
        exit; 
    }

    /**
     * Extract and sanitize inputs
     * - (int) cast converts strings to integers safely (non-numeric becomes 0)
     * - trim() removes leading/trailing whitespace from strings
     * - isset() checks if key exists; ?? provides default value if not
     */
    $studentId   = (int) $_SESSION['user']['id'];
    $jobId       = isset($_POST['jobId']) ? (int) $_POST['jobId'] : 0;
    $coverLetter = isset($_POST['coverLetter']) ? trim($_POST['coverLetter']) : '';

    // Validate required field: jobId must be positive integer
    if ($jobId === 0) {
        http_response_code(400); // 400 Bad Request
        echo json_encode(['error' => 'Missing jobId']);
        exit;
    }

    /**
     * Prepared statement INSERT
     * - Placeholders (?) prevent SQL injection
     * - bind_param types: s=string, i=integer, d=double, b=blob
     * - NOW() is a MySQL function that returns current timestamp
     */
    $stmt = $conn->prepare("
        INSERT INTO applications (status, coverLetter, createdAt, updatedAt, studentId, jobId)
        VALUES ('Applied', ?, NOW(), NOW(), ?, ?)
    ");

    // Check if prepare() failed (returns false on SQL syntax errors)
    if (!$stmt) {
        http_response_code(500); // 500 Internal Server Error
        echo json_encode(['error' => 'Database prepare failed: ' . $conn->error]);
        exit;
    }

    // Bind parameters: s=coverLetter (string), i=studentId (int), i=jobId (int)
    $stmt->bind_param("sii", $coverLetter, $studentId, $jobId);

    /**
     * Execute the statement
     * - execute() returns true on success, false on failure
     * - insert_id property contains the auto-increment ID of the new row
     */
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'application_id' => $stmt->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save application: ' . $stmt->error]);
    }

    // Close statement to free resources
    $stmt->close();
    exit;
}

/**
 * Reject all non-POST requests
 * - HTTP 405 Method Not Allowed indicates the verb is not supported
 */
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;
