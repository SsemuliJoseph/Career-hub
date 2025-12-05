<?php
// Path: api/auth/login.php
// Handles POST login: expects email and password. Returns JSON with user profile.
// Optional "remember me" implementation is included but commented/configurable.

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php'; // provides $conn (mysqli)
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Enforce CSRF token
$token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!verify_csrf_token($token)) { http_response_code(403); echo json_encode(['error'=>'Invalid CSRF token']); exit; }

$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$remember = !empty($_POST['remember']) ? true : false;

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing credentials']);
    exit;
}

// Use mysqli prepared statement
$stmt = $conn->prepare("SELECT id, name, email, password_hash, profile_image, role, theme, phone FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid email or password']);
    exit;
}

// Load profile picture based on role
$profileImage = '/career_hub/uploads/profile/default-avatar.png';
$userRole = $user['role'] ?? 'student';

if ($userRole === 'student') {
    // Check student_profiles table for profilePic
    $profileStmt = $conn->prepare("SELECT profilePic FROM student_profiles WHERE id = ? LIMIT 1");
    $profileStmt->bind_param("i", $user['id']);
    $profileStmt->execute();
    $profileResult = $profileStmt->get_result();
    if ($profileData = $profileResult->fetch_assoc()) {
        $profileImage = $profileData['profilePic'] ?? $profileImage;
    }
    $profileStmt->close();
} elseif ($userRole === 'employer') {
    // Check employers table for logo
    $profileStmt = $conn->prepare("SELECT logo FROM employers WHERE id = ? LIMIT 1");
    $profileStmt->bind_param("i", $user['id']);
    $profileStmt->execute();
    $profileResult = $profileStmt->get_result();
    if ($profileData = $profileResult->fetch_assoc()) {
        $profileImage = $profileData['logo'] ?? $profileImage;
    }
    $profileStmt->close();
}

// If profile image is empty, use default
if (empty($profileImage)) {
    $profileImage = '/career_hub/uploads/profile/default-avatar.png';
}

// Successful login: regenerate session id for security
session_regenerate_id(true);

// Store user data in session for persistent access across pages
$_SESSION['user'] = [
    'id' => (int)$user['id'],
    'name' => $user['name'] ?? $user['email'],
    'email' => $user['email'],
    'phone' => $user['phone'] ?? '',
    'profile_image' => $profileImage,
    'role' => $userRole,
    'theme' => $user['theme'] ?? 'dark',
];

// Optional: Remember-me implementation (secure cookie + token stored in DB)
// 1) Create columns in users table: remember_token, remember_expires_at (we include remember_token in migration).
// 2) If $remember is true, generate a secure token, store hashed token in DB and set cookie with token id.
// Example implementation (UNCOMMENT to enable):
/*
if ($remember) {
    $rawToken = bin2hex(random_bytes(32)); // secure random token
    $tokenHash = password_hash($rawToken, PASSWORD_DEFAULT); // store hashed token
    $expires = (new DateTime('+30 days', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
    $update = $conn->prepare("UPDATE users SET remember_token = ?, remember_expires_at = ? WHERE id = ?");
    $update->bind_param("ssi", $tokenHash, $expires, $user['id']);
    $update->execute();
    $update->close();
    setcookie('remember_me', $user['id'] . ':' . $rawToken, [
        'expires' => time() + 60*60*24*30,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
}
*/

echo json_encode(['success' => true, 'user' => $_SESSION['user']]);
exit;
