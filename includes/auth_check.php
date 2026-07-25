<?php
// includes/auth_check.php
// Teaching: This file is a lightweight authorization guard. Include it at the
// top of any page that requires an authenticated user. It relies on
// includes/session.php to start and validate the session.

// Start session if not already started (with timeout handling)
require_once __DIR__ . '/session.php';

// If no authenticated user is present, remember where the user wanted to go
// and redirect them to the login page. After login the app can read
// $_SESSION['redirect_after_login'] and send the user back.
if (!isset($_SESSION['user'])) {
    // Save the originally requested URI so user can return after auth.
    $requested_page = $_SERVER['REQUEST_URI'];
    $_SESSION['redirect_after_login'] = $requested_page;
    
    // Redirect to login page. Note: using absolute paths helps avoid
    // accidental relative redirects when this include is used from different folders.
    header("Location: /career_hub/pages/login.php");
    exit;
}

// Provide a convenient local variable for templates
$currentUser = $_SESSION['user'];

// Ensure required keys exist to avoid undefined index warnings in templates.
// We don't mutate the underlying DB here; this is only defensive for rendering.
if (!isset($currentUser['name'])) {
    $currentUser['name'] = 'Student';
}
if (!isset($currentUser['email'])) {
    $currentUser['email'] = '';
}
