<?php
/* Teaching: update_application_status.php
    Purpose: Allow a student to update the status of their own application.
    Concepts to note:
    - Input methods: accepts JSON payload (php://input) with POST/PUT/PATCH compatibility
    - Authentication: uses server-side session ($_SESSION['user']) to identify student
    - CSRF protection: verifies CSRF token for state-changing requests
    - Prepared statements (mysqli) to prevent SQL injection
    - Proper HTTP response codes and JSON responses for frontend fetch()/api.js
*/
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/csrf.php';

// Only allow PUT/PATCH requests
if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'PATCH', 'POST'])) {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if user is logged in as student
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized. Only students can update their applications.']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST; // Fallback to POST data
}

// Verify CSRF token
$token = $input['csrf_token'] ?? $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!verify_csrf_token($token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

$studentId = (int) $_SESSION['user']['id'];
$applicationId = isset($input['application_id']) ? (int) $input['application_id'] : 0;
$newStatus = isset($input['status']) ? trim($input['status']) : '';
$notes = isset($input['notes']) ? trim($input['notes']) : null;

// Validate application ID
if ($applicationId === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing application_id']);
    exit;
}

// Validate status
$validStatuses = ['Applied', 'Reviewed', 'Interview', 'Hired', 'Rejected'];
if (!in_array($newStatus, $validStatuses)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status. Must be one of: ' . implode(', ', $validStatuses)]);
    exit;
}

// Check if application exists and belongs to this student
$checkStmt = $conn->prepare("
    SELECT a.id, a.status, j.title, j.application_method 
    FROM applications a
    INNER JOIN jobs j ON a.jobId = j.id
    WHERE a.id = ? AND a.studentId = ?
    LIMIT 1
");
$checkStmt->bind_param("ii", $applicationId, $studentId);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Application not found or does not belong to you']);
    $checkStmt->close();
    exit;
}

$application = $result->fetch_assoc();
$oldStatus = $application['status'];
$checkStmt->close();

// Update application status
$updateStmt = $conn->prepare("UPDATE applications SET status = ?, updatedAt = NOW() WHERE id = ?");
$updateStmt->bind_param("si", $newStatus, $applicationId);

if ($updateStmt->execute()) {
    // If notes provided, append to cover letter
    if (!empty($notes)) {
        $appendStmt = $conn->prepare("
            UPDATE applications 
            SET coverLetter = CONCAT(COALESCE(coverLetter, ''), '\n\n--- Status Update (', NOW(), ') ---\n', ?)
            WHERE id = ?
        ");
        $appendStmt->bind_param("si", $notes, $applicationId);
        $appendStmt->execute();
        $appendStmt->close();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Application status updated successfully',
        'application_id' => $applicationId,
        'job_title' => $application['title'],
        'old_status' => $oldStatus,
        'new_status' => $newStatus,
        'is_external' => $application['application_method'] === 'External',
        'updated_at' => date('Y-m-d H:i:s')
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update application: ' . $updateStmt->error]);
}

$updateStmt->close();
$conn->close();
?>
