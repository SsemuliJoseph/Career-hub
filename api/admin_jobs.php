<?php
/**
 * Admin Jobs Management API
 * GET /api/admin_jobs.php
 * 
 * TEACHING NOTES:
 * - Admin-only endpoint to list all jobs in the system
 * - Joins jobs table with employers to get company name
 * - Returns jobs ordered by posting date (newest first)
 * - Security: Admin role required
 * - Output: { jobs: [{ id, title, company, location, type, posted_date }, ...] }
 */
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Check if user is admin
if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Fetch all jobs
$stmt = $conn->prepare("
    SELECT j.id, j.title, e.company_name as company, j.location, j.type, j.createdAt as posted_date 
    FROM jobs j 
    LEFT JOIN employers e ON j.employer_id = e.id 
    ORDER BY j.createdAt DESC
");
$stmt->execute();
$result = $stmt->get_result();

$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}

$stmt->close();

echo json_encode(['jobs' => $jobs]);
