<?php
// Path: includes/session.php
// Deep teaching comments: this file centralizes session management. Include it
// at the top of pages or API endpoints before sending any output. PHP sessions
// let you persist small amounts of state (like user identity) between HTTP requests.

/*
 * session_status()
 * - Returns the current session status (PHP_SESSION_DISABLED, PHP_SESSION_NONE, PHP_SESSION_ACTIVE)
 * - We compare to PHP_SESSION_NONE to check if a session hasn't been started yet.
 */
if (session_status() === PHP_SESSION_NONE) {
    // Determine if we should mark the cookie as 'secure' (only sent over HTTPS).
    // $_SERVER['HTTPS'] is commonly set to 'on' or '1' when using HTTPS.
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? true : false;

    // session_get_cookie_params() returns the current cookie settings as an array.
    $cookieParams = session_get_cookie_params();

    /*
     * session_set_cookie_params accepts an array (PHP 7.3+) to set options for the
     * session cookie. Options used here:
     * - lifetime: 0 means "until browser close" (session cookie)
     * - path/domain: control cookie scope
     * - secure: if true the cookie is only sent over HTTPS
     * - httponly: true prevents JavaScript from reading the cookie (mitigates XSS)
     * - samesite: controls cross-site sending of cookies (Lax is a reasonable default)
     */
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $cookieParams['path'] ?? '/',
        'domain' => $cookieParams['domain'] ?? '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    // Start the session. This creates $_SESSION superglobal and sends a Set-Cookie header
    // if a session cookie doesn't already exist. Always call session_start() before output.
    session_start();
}

// SESSION_TIMEOUT demonstrates a common pattern: expire sessions after a period of inactivity.
// define() creates a constant named SESSION_TIMEOUT with an integer value (seconds).
define('SESSION_TIMEOUT', 1800); // 1800 seconds = 30 minutes

// Check for last activity to implement inactivity logout.
// isset() checks if a key exists in the $_SESSION array.
if (isset($_SESSION['LAST_ACTIVITY'])) {
    // time() returns current Unix timestamp. Subtract stored timestamp to get inactivity period.
    $inactive = time() - $_SESSION['LAST_ACTIVITY'];

    if ($inactive > SESSION_TIMEOUT) {
        // Destroy the session safely
        session_unset(); // clear $_SESSION
        session_destroy(); // remove session data on server and expire cookie

        // Redirect to login unless we're already on a public auth page.
        // basename($_SERVER['PHP_SELF']) gives the current script filename.
        $current_page = basename($_SERVER['PHP_SELF']);
        $public_pages = ['index.php', 'login.php', 'signup.php', 'admin-login.php'];

        // in_array checks membership; if current page isn't public, redirect.
        if (!in_array($current_page, $public_pages)) {
            // header('Location: ...') sends an HTTP redirect. Always exit after to stop execution.
            header("Location: /career_hub/pages/login.php?timeout=1");
            exit;
        }
    }
}

// Update LAST_ACTIVITY on every request (sliding expiration)
$_SESSION['LAST_ACTIVITY'] = time();

/**
 * getCurrentUserFromSession
 * - Small helper that returns the user array from the session or null.
 * - Return type ?array means "array or null" in PHP 7.1+ type hints.
 */
function getCurrentUserFromSession(): ?array {
    return $_SESSION['user'] ?? null;
}
