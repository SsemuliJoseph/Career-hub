<?php
/* Teaching: employer_applicants.php
    Purpose: Return applicants scoped to an employer (or admin) with optional detail views.
    Key points:
    - Role-based access control: only employers or admins should view these records
    - Provide multiple query modes (by employerId or by applicationId) in a single endpoint
    - Use prepared statements to avoid SQL injection and to safely pass integers
    - Normalize database fields to predictable JSON keys for frontend consumption
    - Consider adding pagination and search filters for production readiness
*/
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/db.php'; // $conn (mysqli)

if (empty($_SESSION['user'])) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

$role = $_SESSION['user']['role'] ?? 'user';

// For simplicity we allow employer or admin to view
if (!in_array($role, ['employer', 'admin'])) { http_response_code(403); echo json_encode(['error'=>'Forbidden']); exit; }

if (!empty($_GET['employerId'])) {
    $employerId = (int)$_GET['employerId'];
    $stmt = $conn->prepare("SELECT a.id as application_id, a.job_id, j.title, a.user_id, u.name as applicant_name, a.cover_letter, a.created_at FROM applications a JOIN jobs j ON a.job_id = j.id JOIN users u ON a.user_id = u.id WHERE j.employer_id = ? ORDER BY a.created_at DESC");
    $stmt->bind_param("i", $employerId);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    echo json_encode(['applicants' => $rows]);
    exit;
}

if (!empty($_GET['applicationId'])) {
    $applicationId = (int)$_GET['applicationId'];
    $stmt = $conn->prepare("SELECT a.*, u.name as applicant_name, j.title FROM applications a JOIN users u ON a.user_id = u.id JOIN jobs j ON a.job_id = j.id WHERE a.id = ? LIMIT 1");
    $stmt->bind_param("i", $applicationId);
    $stmt->execute();
    $res = $stmt->get_result();
    $a = $res->fetch_assoc();
    $stmt->close();
    if (!$a) { http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }
    echo json_encode(['application' => $a]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Bad request']);
exit;
