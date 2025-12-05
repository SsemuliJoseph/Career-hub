<?php
/* Teaching: get_applications.php
   Purpose: Return applications for jobs owned by the logged-in employer.
   Concepts:
   - Session-based authorization (ensure employer role)
   - Prepared statements with bound parameters to safely inject employer id
   - Aggregating application, student, and job data via JOINs
   - Returning a JSON object suitable for the employer dashboard
   Note: This is nearly identical to get_applicants.php; consider consolidating duplicates.
*/
require_once __DIR__ . '/../includes/db.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'employer') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$employer_id = $_SESSION['user']['id'];

$sql = "
SELECT a.id AS application_id, s.full_name, s.email, s.education, s.skills, s.profile_pic, s.cv_file, j.title
FROM applications a
JOIN students s ON a.student_id = s.id
JOIN jobs j ON a.job_id = j.id
WHERE j.employer_id = ?
ORDER BY a.applied_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$res = $stmt->get_result();

$applicants = [];
while ($row = $res->fetch_assoc()) {
    $applicants[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['applicants' => $applicants]);
?>
