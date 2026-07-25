<?php
// Path: api/auth/session.php
// Returns current session user data as JSON (or null if not logged in).
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/session.php';

$user = $_SESSION['user'] ?? null;

echo json_encode(['user' => $user]);
exit;
