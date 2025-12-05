<?php
/* Teaching: get_applicants.php
   Purpose: Return a list of applicants for the logged-in employer's jobs.
   Concepts shown:
   - Session-based authorization (ensure the requester is an employer)
   - Prepared statements to safely pass employer ID into SQL
   - Normalizing DB rows into a frontend-friendly JSON shape
   - Limits and pagination: this file limits results; for large datasets add paging
*/
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php'; // your mysqli connection as $conn

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'employer') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized', 'applicants' => []]);
    exit;
}

$employer_id = (int) ($_SESSION['user']['id'] ?? 0);
if ($employer_id <= 0) {
    echo json_encode(['applicants' => []]);
    exit;
}

/*
 expected tables:
 - jobs (id, employer_id, title, ...)
 - applications (id, job_id, student_id, cover_letter, applied_at)
 - students (id, full_name, email, education, skills, profile_pic, cv_file)
*/

$sql = "
SELECT 
  a.id AS application_id,
  j.id AS job_id,
  j.title,
  s.id AS student_id,
  s.full_name,
  s.email,
  s.education,
  s.skills,
  s.profile_pic,
  s.cv_file,
  a.cover_letter,
  a.applied_at
FROM jobs j
JOIN applications a ON a.job_id = j.id
JOIN students s ON s.id = a.student_id
WHERE j.employer_id = ?
ORDER BY a.applied_at DESC
LIMIT 1000
";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Query prepare failed', 'applicants' => []]);
    exit;
}
$stmt->bind_param('i', $employer_id);
$stmt->execute();
$res = $stmt->get_result();

$applicants = [];
while ($row = $res->fetch_assoc()) {
    // normalize field names to match frontend usage
    $applicants[] = [
        'application_id' => (int)$row['application_id'],
        'job_id' => (int)$row['job_id'],
        'title' => $row['title'],
        'student_id' => (int)$row['student_id'],
        'full_name' => $row['full_name'],
        'email' => $row['email'],
        'education' => $row['education'],
        'skills' => $row['skills'],
        'profile_pic' => $row['profile_pic'],
        'cv_file' => $row['cv_file'],
        'cover_letter' => $row['cover_letter'],
        'applied_at' => $row['applied_at']
    ];
}

echo json_encode(['applicants' => $applicants]);
exit;
