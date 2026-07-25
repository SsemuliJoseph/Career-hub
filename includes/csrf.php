<?php
/**
 * includes/csrf.php - Cross-Site Request Forgery (CSRF) protection
 *
 * Teaching notes: CSRF attacks trick authenticated users into submitting malicious requests.
 * Example attack:
 * - User logs into careerHub.com (gets session cookie)
 * - User visits evil.com which contains:
 *   <form action="careerHub.com/api/delete_job.php" method="POST">
 *     <input name="job_id" value="123">
 *   </form>
 *   <script>document.forms[0].submit()</script>
 * - Browser automatically sends session cookie to careerHub.com
 * - Without CSRF protection, the job gets deleted
 *
 * CSRF tokens prevent this by:
 * 1. Server generates random token stored in session
 * 2. Token embedded in forms/AJAX requests
 * 3. Server validates token matches session before processing mutations
 * 4. Evil.com can't read the token (Same-Origin Policy blocks cross-site access)
 *
 * Key PHP functions:
 * - random_bytes(n): generates cryptographically secure random bytes
 * - bin2hex(): converts binary to hexadecimal string (safe for HTML)
 * - hash_equals(): timing-safe string comparison (prevents timing attacks)
 */

// Ensure session is started so $_SESSION is available
require_once __DIR__ . '/session.php';

/**
 * csrf_token - Generate or retrieve CSRF token from session
 *
 * @return string 64-character hexadecimal token
 *
 * Usage in forms:
 * <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
 *
 * Usage in AJAX (fetch API):
 * headers: { 'X-CSRF-Token': sessionStorage.getItem('csrf_token') }
 */
function csrf_token(): string {
    // Check if token already exists in session
    if (empty($_SESSION['csrf_token'])) {
        /**
         * Generate new token:
         * - random_bytes(32) creates 32 bytes of cryptographically secure random data
         * - bin2hex() converts to 64-character hex string (2 hex chars per byte)
         * - Store in session for validation in subsequent requests
         */
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * verify_csrf_token - Validate submitted token against session token
 *
 * @param string|null $token Token from POST/header
 * @return bool True if valid, false otherwise
 *
 * Security notes:
 * - hash_equals() prevents timing attacks (constant-time comparison)
 * - Regular === comparison leaks info via execution time (slower with more matching chars)
 * - (string) cast prevents type confusion attacks
 */
function verify_csrf_token(?string $token): bool {
    // Fail if no token in session (shouldn't happen in normal flow)
    if (empty($_SESSION['csrf_token'])) return false;
    
    /**
     * hash_equals() is timing-safe string comparison
     * - Always takes same time regardless of how many characters match
     * - Prevents attackers from using timing data to guess tokens character-by-character
     * - (string) cast ensures $token is string type (prevents null/array injection)
     */
    return hash_equals($_SESSION['csrf_token'], (string)$token);
}

/**
 * Implementation guide:
 * 
 * 1. In HTML forms:
 *    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
 * 
 * 2. In API endpoints (server-side):
 *    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
 *    if (!verify_csrf_token($token)) {
 *        http_response_code(403);
 *        echo json_encode(['error' => 'Invalid CSRF token']);
 *        exit;
 *    }
 * 
 * 3. In JavaScript (fetch API):
 *    fetch('/api/jobs.php', {
 *        method: 'POST',
 *        headers: {
 *            'Content-Type': 'application/json',
 *            'X-CSRF-Token': document.querySelector('[name=csrf_token]').value
 *        },
 *        body: JSON.stringify({...})
 *    });
 */
?>
