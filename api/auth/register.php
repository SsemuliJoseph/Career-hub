<?php
// Path: api/auth/register.php
// Handles POST registration with optional profile image upload.
// Expected POST form fields: name, email, password, (profile_image file input name: profile_image)
// This endpoint returns JSON { success: true, user_id: ... } or { error: "..." }

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php'; // provides $conn (mysqli)
require_once __DIR__ . '/../../includes/upload_helpers.php';
require_once __DIR__ . '/../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Enforce CSRF protection
$token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!verify_csrf_token($token)) { http_response_code(403); echo json_encode(['error'=>'Invalid CSRF token']); exit; }

$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if (!$name || !$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email']);
    exit;
}

// check for existing user
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$check = $stmt->get_result();
if ($check->fetch_assoc()) {
    http_response_code(409);
    echo json_encode(['error' => 'Email already registered']);
    exit;
}
$stmt->close();

// handle profile image if provided
$profileImageFilename = null;
if (!empty($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $stored = store_profile_image($_FILES['profile_image']);
    if ($stored === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid profile image']);
        exit;
    }
    $profileImageFilename = $stored;
}

// Insert user
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$now = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');

$insert = $conn->prepare("INSERT INTO users (name, email, password_hash, profile_image, created_at) VALUES (?, ?, ?, ?, ?)");
$insert->bind_param("sssss", $name, $email, $passwordHash, $profileImageFilename, $now);
$insert->execute();
$userId = (int)$conn->insert_id;
$insert->close();

echo json_encode(['success' => true, 'user_id' => $userId]);
exit;
