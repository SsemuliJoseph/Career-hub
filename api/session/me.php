<?php
// Path: api/session/me.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/session.php';

// Return the current session user directly (not wrapped) so frontend can read fields with:
//   const user = await (await fetch('/api/session/me')).json();
// This avoids confusion between { user: {...} } vs {...}
$user = $_SESSION['user'] ?? null;
echo json_encode($user);
exit;
?>
