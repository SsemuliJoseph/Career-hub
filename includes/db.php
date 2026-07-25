<?php
/**
 * Single mysqli connection file (teaching edition)
 *
 * Purpose:
 * - Provide a single place to initialize a mysqli connection used across the app.
 * - Show common patterns for configuration: prefer environment variables, fall back to a
 *   local config file, then to safe defaults.
 *
 * Important student notes:
 * - Never store real production credentials in source control. Use environment variables
 *   or a secret manager in production.
 * - mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT) makes mysqli throw exceptions
 *   so we can handle errors with try/catch.
 */

// Original reference values are left in comments above for history and debugging.

// Make mysqli throw exceptions on errors (mysqli_sql_exception). This simplifies error handling.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Read configuration from environment variables first. getenv() reads from the OS environment.
// The expression a ?: b returns a if a is truthy, otherwise b (short ternary). The ?? operator
// returns the first defined (non-null) operand. Combining them provides robust fallbacks.
$servername = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? '');
$username   = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? '');
$password   = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? '');
$dbname     = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? '');

// If not provided via env, try the project's config file. file_exists avoids warnings.
if (empty($servername) || empty($dbname)) {
    $configPath = __DIR__ . '/../config/config.php';
    if (file_exists($configPath)) {
        // The config file returns an associative array; require returns that array.
        $config = require $configPath;

        // Heuristic to detect local development: check SERVER_NAME for 'localhost' or '127.0.0.1'
        $isLocal = (isset($_SERVER['SERVER_NAME']) && 
                   (strpos($_SERVER['SERVER_NAME'], 'localhost') !== false || 
                    strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false));

        if ($isLocal) {
            // Use local-specific keys if available, otherwise generic DB_* keys.
            $servername = $config['DB_HOST_LOCAL'] ?? $config['DB_HOST'] ?? 'localhost';
            $username   = $config['DB_USER_LOCAL'] ?? $config['DB_USER'] ?? 'root';
            $password   = $config['DB_PASS_LOCAL'] ?? $config['DB_PASS'] ?? '';
            $dbname     = $config['DB_NAME_LOCAL'] ?? $config['DB_NAME'] ?? 'uniconnect_db';
        } else {
            // Production: support both newer generic names and legacy INFINITYFREE keys
            $servername = $config['DB_HOST_INFINITYFREE'] ?? $config['DB_HOST'] ?? '';
            $username   = $config['DB_USER_INFINITYFREE'] ?? $config['DB_USER'] ?? '';
            $password   = $config['DB_PASS_INFINITYFREE'] ?? $config['DB_PASS'] ?? '';
            $dbname     = $config['DB_NAME_INFINITYFREE'] ?? $config['DB_NAME'] ?? '';
        }
    }
}

// Final fallback defaults for local development. These ensure the app can run with minimal setup.
if (empty($servername)) $servername = 'localhost';
if (empty($username)) $username = 'root';
if (empty($password)) $password = '';
if (empty($dbname)) $dbname = 'uniconnect_db';

// Establish mysqli connection inside a try/catch because mysqli will throw exceptions.
try {
    // new mysqli(host, user, pass, dbname)
    $conn = new mysqli($servername, $username, $password, $dbname);
    // set_charset ensures we handle Unicode correctly (utf8mb4 supports emoji and multi-byte chars)
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Do not display raw DB errors to end users. Log them and return a generic error.
    error_log('DB connection error: ' . $e->getMessage());
    http_response_code(500);
    die('Database unavailable');
}

// $conn is now available globally for include-based code (legacy pattern used in this project)
?>