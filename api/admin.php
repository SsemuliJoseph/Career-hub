<?php
// Path: api/admin.php
// Admin-only actions: ?action=users|jobs|applications
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php'; // $conn (mysqli)

if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'users') {
    $res = $conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
    echo json_encode(['users' => $res->fetch_all(MYSQLI_ASSOC)]);
    exit;
}

if ($action === 'jobs') {
    $res = $conn->query("SELECT id, title, company, created_at FROM jobs ORDER BY created_at DESC");
    echo json_encode(['jobs' => $res->fetch_all(MYSQLI_ASSOC)]);
    exit;
}

if ($action === 'applications') {
    $res = $conn->query("SELECT a.id, a.job_id, j.title, a.user_id, u.name as applicant_name, a.created_at FROM applications a JOIN jobs j ON a.job_id = j.id JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC");
    echo json_encode(['applications' => $res->fetch_all(MYSQLI_ASSOC)]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
exit;
