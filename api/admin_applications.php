
<?php
/**
 * Admin Applications Management API
 * GET /api/admin_applications.php
 * 
 * TEACHING NOTES:
 * - Admin-only endpoint to view all job applications across the platform
 * - Complex JOIN query combining applications, jobs, students, and employers
 * - Returns comprehensive applicant data (name, email, phone, CV, cover letter, status)
 * - Ordered by application date (newest first)
 * - Security: Admin authentication required
 * - Output: { applications: [{ id, studentId, jobId, applicant_name, email, phone, cover_letter, cv_file, applied_at, status, job_title, company }, ...] }
 */
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Check if user is admin
if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Fetch all applications with job and user details
$stmt = $conn->prepare("
    SELECT 
        a.id,
        a.studentId,
        a.jobId,
        s.name as applicant_name,
        s.email,
        s.contact as phone,
        a.coverLetter as cover_letter,
        s.cv_file,
        a.createdAt as applied_at,
        a.status,
        j.title as job_title,
        e.company_name as company
    FROM applications a
    LEFT JOIN jobs j ON a.jobId = j.id
    LEFT JOIN students s ON a.studentId = s.id
    LEFT JOIN employers e ON j.employer_id = e.id
    ORDER BY a.createdAt DESC
");
$stmt->execute();
$result = $stmt->get_result();

$applications = [];
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}

$stmt->close();

echo json_encode(['applications' => $applications]);
