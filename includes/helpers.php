<?php
// includes/helpers.php
// Small, focused helper functions used across templates. These are intentionally
// simple so students can see best practices for escaping and session usage.

// Ensure session is started so $_SESSION is available. session_status() returns
// PHP_SESSION_NONE when no session exists yet.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * getUserName
 * - Returns the current user's display name escaped for HTML output.
 * - $_SESSION is a superglobal associative array. The null coalescing operator ??
 *   provides a default value if the expected key doesn't exist.
 * - (string) cast ensures we pass a string to htmlspecialchars even if session data is tampered with.
 * - htmlspecialchars flags used:
 *   ENT_QUOTES - escape both single and double quotes
 *   ENT_SUBSTITUTE - substitute invalid code unit sequences
 */
function getUserName(): string {
    $name = $_SESSION['user']['name'] ?? 'Student';
    return htmlspecialchars((string)$name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * isLoggedIn
 * - A tiny boolean helper that checks whether a user object exists in the session.
 * - wrap common checks in helpers to make templates clearer and easier to test.
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

/**
 * getUserEmail
 * - Return an escaped email suitable for HTML output. Always escape output that
 *   comes from user-controlled sources.
 */
function getUserEmail(): string {
    return htmlspecialchars((string)($_SESSION['user']['email'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
