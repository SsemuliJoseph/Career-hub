<?php
/* Teaching: track_external_application.php
    Purpose: Record that a student has applied via an external site (external_link).
    Important concepts:
    - Uses server session for authentication (student-only endpoint)
    - Validates inputs and checks for duplicate records before inserting
    - Uses prepared statements to avoid SQL injection
    - Responds with clear JSON shapes and relevant HTTP status codes
    - Keep server-side validations authoritative (never trust client-side checks)
*/
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/csrf.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized. Only students can track applications.']);
    exit;
}

// Verify CSRF token
$token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!verify_csrf_token($token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Get input data
$studentId = (int) $_SESSION['user']['id'];
$jobId = isset($_POST['jobId']) ? (int) $_POST['jobId'] : 0;
$coverLetter = isset($_POST['coverLetter']) ? trim($_POST['coverLetter']) : '';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
$appliedDate = isset($_POST['appliedDate']) ? trim($_POST['appliedDate']) : date('Y-m-d H:i:s');

// Validate job ID
if ($jobId === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing jobId']);
    exit;
}

// Check if job exists and is external
$checkJobStmt = $conn->prepare("SELECT id, title, application_method, external_link FROM jobs WHERE id = ? LIMIT 1");
$checkJobStmt->bind_param("i", $jobId);
$checkJobStmt->execute();
$jobResult = $checkJobStmt->get_result();

if ($jobResult->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Job not found']);
    $checkJobStmt->close();
    exit;
}

$job = $jobResult->fetch_assoc();
$checkJobStmt->close();

if ($job['application_method'] !== 'External') {
    http_response_code(400);
    echo json_encode(['error' => 'This endpoint is only for external jobs. Use /api/applications.php for direct applications.']);
    exit;
}

// Check if user already tracked this application
$checkStmt = $conn->prepare("SELECT id FROM applications WHERE studentId = ? AND jobId = ? LIMIT 1");
$checkStmt->bind_param("ii", $studentId, $jobId);
$checkStmt->execute();
$existingResult = $checkStmt->get_result();

if ($existingResult->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'You have already tracked an application for this job']);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Insert application tracking record
$insertStmt = $conn->prepare("
    INSERT INTO applications (status, coverLetter, createdAt, updatedAt, studentId, jobId)
    VALUES ('Applied', ?, ?, NOW(), ?, ?)
");

if (!$insertStmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database prepare failed: ' . $conn->error]);
    exit;
}

// Combine cover letter and notes
$fullCoverLetter = $coverLetter;
if (!empty($notes)) {
    $fullCoverLetter .= "\n\n--- Application Notes ---\n" . $notes;
}

$insertStmt->bind_param("ssii", $fullCoverLetter, $appliedDate, $studentId, $jobId);

if ($insertStmt->execute()) {
    $applicationId = $insertStmt->insert_id;
    
    echo json_encode([
        'success' => true,
        'application_id' => $applicationId,
        'message' => 'External application tracked successfully',
        'job_title' => $job['title'],
        'external_link' => $job['external_link']
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to track application: ' . $insertStmt->error]);
}

$insertStmt->close();
$conn->close();
?>
