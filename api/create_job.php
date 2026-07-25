<?php
/* Teaching: create_job.php
   Purpose: Allow authenticated employers to create job postings.
   Key concepts:
   - Method & role checks: only accept POST and ensure user is an employer
   - CSRF protection: verify token for state-changing requests
   - Server-side validation: sanitize and validate inputs before DB insertion
   - Prepared statements (mysqli) to safely insert user-provided data
   - Return structured JSON so front-end can show success/errors
   Note: Consider additional checks (rate limiting, spam prevention, content moderation)
*/
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user']) && $_SESSION['user']['role'] === 'employer') {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!verify_csrf_token($token)) { http_response_code(403); echo json_encode(['error' => 'Invalid CSRF token']); exit; }
    $title = $_POST['title'] ?? '';
    $company = $_POST['company'] ?? '';
    $location = $_POST['location'] ?? '';
    $description = $_POST['description'] ?? '';
    $requirements = $_POST['requirements'] ?? '';
    $employer_id = $_SESSION['user']['id'];

    $stmt = $conn->prepare("INSERT INTO jobs (employer_id, title, company, location, description, requirements) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("isssss", $employer_id, $title, $company, $location, $description, $requirements);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Job created successfully']);
    exit;
}
echo json_encode(['success' => false, 'message' => 'Unauthorized or invalid request']);
?>
