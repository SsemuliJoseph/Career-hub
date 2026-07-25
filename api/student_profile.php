<?php
// Path: api/student_profile.php
// POST: update profile (requires login). Accepts name, optional profile_image upload.
// GET: ?email= returns profile info by email.

/* Teaching: student_profile.php
    Purpose: Handle reading/updating student profile data and file uploads.
    Topics demonstrated:
    - Multi-method endpoints (GET for read, POST for update)
    - Handling file uploads via $_FILES and helper functions
    - Session-based authentication and server-side validation
    - CSRF token verification for state changes
    - Returning JSON responses for AJAX clients
    Best practice: perform server-side validation on uploaded files (type/size)
*/
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php'; // $conn (mysqli)
require_once __DIR__ . '/../includes/upload_helpers.php';
require_once __DIR__ . '/../includes/csrf.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    if (empty($_SESSION['user'])) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!verify_csrf_token($token)) { http_response_code(403); echo json_encode(['error'=>'Invalid CSRF token']); exit; }

    $userId = (int)$_SESSION['user']['id'];
    $name = trim($_POST['name'] ?? '');

    if (!$name) { http_response_code(400); echo json_encode(['error'=>'Name required']); exit; }

    $profileImageFilename = $_SESSION['user']['profile_image'] ?? null;
    if (!empty($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $stored = store_profile_image($_FILES['profile_image']);
        if ($stored === null) { http_response_code(400); echo json_encode(['error'=>'Invalid image']); exit; }
        $profileImageFilename = $stored;
    }

    $stmt = $conn->prepare("UPDATE users SET name = ?, profile_image = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $profileImageFilename, $userId);
    $stmt->execute();
    $stmt->close();

    // update session copy
    $_SESSION['user']['name'] = $name;
    $_SESSION['user']['profile_image'] = $profileImageFilename;

    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'GET') {
    $email = strtolower(trim($_GET['email'] ?? ''));
    if (!$email) { http_response_code(400); echo json_encode(['error'=>'email required']); exit; }

    $stmt = $conn->prepare("SELECT id, name, email, profile_image, role, created_at FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();
    if (!$user) { http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }
    echo json_encode(['user' => $user]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;
