<?php
/* Teaching: student_applications.php
   Purpose: Return the currently authenticated student's applications with job and employer details.
   Concepts:
   - Session-based authentication: require $_SESSION['user'] and role checks
   - Joining tables (applications -> jobs -> employers) to return aggregated data
   - Prepared statements and result fetching with mysqli
   - Returning structured JSON with lists and simple statistics
*/
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Check if user is logged in as student
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized. Only students can view their applications.']);
    exit;
}

$studentId = (int) $_SESSION['user']['id'];

// Fetch all applications for this student with job and employer details
$stmt = $conn->prepare("
    SELECT 
        a.id,
        a.status,
        a.coverLetter,
        a.createdAt as applied_at,
        a.updatedAt,
        j.id as job_id,
        j.title as job_title,
        j.location,
        j.type as job_type,
        j.application_method,
        j.external_link,
        j.status as job_status,
        e.company_name,
        e.logo as company_logo,
        e.industry as company_industry
    FROM applications a
    INNER JOIN jobs j ON a.jobId = j.id
    LEFT JOIN employers e ON j.employer_id = e.id
    WHERE a.studentId = ?
    ORDER BY a.createdAt DESC
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

$applications = [];
while ($row = $result->fetch_assoc()) {
    // Determine if this is an external application
    $isExternal = $row['application_method'] === 'External';
    
    $applications[] = [
        'id' => $row['id'],
        'status' => $row['status'],
        'applied_at' => $row['applied_at'],
        'updated_at' => $row['updatedAt'],
        'is_external' => $isExternal,
        'job' => [
            'id' => $row['job_id'],
            'title' => $row['job_title'],
            'location' => $row['location'],
            'type' => $row['job_type'],
            'status' => $row['job_status'],
            'external_link' => $row['external_link']
        ],
        'company' => [
            'name' => $row['company_name'] ?? 'Unknown Company',
            'logo' => $row['company_logo'],
            'industry' => $row['company_industry']
        ],
        'cover_letter' => $row['coverLetter']
    ];
}

$stmt->close();
$conn->close();

// Group applications by type
$internalApps = array_filter($applications, fn($app) => !$app['is_external']);
$externalApps = array_filter($applications, fn($app) => $app['is_external']);

echo json_encode([
    'success' => true,
    'total_applications' => count($applications),
    'internal_applications' => array_values($internalApps),
    'external_applications' => array_values($externalApps),
    'all_applications' => $applications,
    'statistics' => [
        'total' => count($applications),
        'internal' => count($internalApps),
        'external' => count($externalApps),
        'by_status' => [
            'Applied' => count(array_filter($applications, fn($app) => $app['status'] === 'Applied')),
            'Reviewed' => count(array_filter($applications, fn($app) => $app['status'] === 'Reviewed')),
            'Interview' => count(array_filter($applications, fn($app) => $app['status'] === 'Interview')),
            'Hired' => count(array_filter($applications, fn($app) => $app['status'] === 'Hired')),
            'Rejected' => count(array_filter($applications, fn($app) => $app['status'] === 'Rejected'))
        ]
    ]
]);
?>
