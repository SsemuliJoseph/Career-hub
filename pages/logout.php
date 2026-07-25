<?php
/**
 * pages/logout.php - User Logout Handler
 * Teaching: This script destroys the user's session and redirects to login.
 * - session_start() or require session.php must be called before using session functions.
 * - session_unset() clears all session variables ($_SESSION becomes empty).
 * - session_destroy() completely destroys the session (deletes the session file).
 * - header("Location: ...") sends an HTTP redirect to the browser.
 * - exit stops script execution after the redirect (prevents further code from running).
 * 
 * Security note: Always call session_destroy() on logout to prevent session fixation attacks.
 */
// Include session management
require_once __DIR__ . '/../includes/session.php';
// Clear all session variables
session_unset();
// Destroy the session completely
session_destroy();
// Redirect user to login page
header("Location: /career_hub/pages/login.php");
exit; // Stop script execution
?>
