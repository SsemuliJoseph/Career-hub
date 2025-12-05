<?php
/**
 * Admin Users Management API
 * GET /api/admin_users.php
 * 
 * TEACHING NOTES:
 * - Admin-only endpoint to list all registered users
 * - Returns users ordered by registration date (newest first)
 * - Security: Checks $_SESSION['user']['role'] must be 'admin'
 * - Output: { users: [{ id, username, email, role, createdAt }, ...] }
 */
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Check if user is admin
if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Fetch all users
$stmt = $conn->prepare("SELECT id, username, email, role, createdAt FROM users ORDER BY createdAt DESC");
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();

echo json_encode(['users' => $users]);
