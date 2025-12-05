<?php
/**
 * classes/autoload.php - Class autoloader and environment bootstrap
 *
 * Teaching notes: This file sets up automatic class loading and environment configuration.
 * Key PHP concepts:
 * - spl_autoload_register(): Registers a custom class loader function
 * - Composer autoload: PHP package manager's autoloader for vendor libraries
 * - .env file loading: Pulls configuration from environment variables (12-factor app pattern)
 * - require/require_once: Includes PHP files (once = prevent duplicate includes)
 *
 * Purpose:
 * 1. Load Composer dependencies (phpdotenv, Ratchet WebSocket, etc.)
 * 2. Parse .env file and set environment variables
 * 3. Auto-load project classes (User, Job, Application, etc.) on demand
 * 4. Ensure database connection is available globally
 *
 * Benefits of autoloading:
 * - No need to manually require_once every class file
 * - Classes are loaded only when first used (lazy loading)
 * - PSR-4 compliant structure (PHP Standards Recommendation)
 * - Reduces boilerplate at top of files
 */

/**
 * Step 1: Load Composer's autoloader
 * - Composer generates vendor/autoload.php which handles all vendor dependencies
 * - This includes phpdotenv (for .env parsing), Ratchet (for WebSocket), etc.
 */
$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

/**
 * Step 2: Load .env file for environment variables
 * - phpdotenv library reads .env file and populates $_ENV, $_SERVER, getenv()
 * - createMutable() allows .env to override existing environment vars (useful on Windows)
 * - safeLoad() doesn't throw if .env is missing (graceful degradation)
 *
 * Security note: Never commit .env to version control. Use .env.example as template.
 */
if (class_exists('Dotenv\\Dotenv')) {
    try {
        // createMutable allows .env to override existing environment vars (helpful on Windows)
        $dotenv = Dotenv\Dotenv::createMutable(__DIR__ . '/..');
        // safeLoad() don't throw if .env not found (production may use real env vars)
        $dotenv->safeLoad();
    } catch (Throwable $e) {
        // Log error but don't crash app (production servers may not have .env)
        error_log('Dotenv load warning: ' . $e->getMessage());
    }
} else {
    /**
     * Fallback .env parser if phpdotenv isn't installed
     * - Manually parse .env file line by line
     * - Supports # comments, KEY=VALUE format, quoted values
     * - Sets variables in $_ENV, $_SERVER, and via putenv()
     */
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath) && is_readable($envPath)) {
        // file() reads entire file into array (one element per line)
        // FILE_IGNORE_NEW_LINES: strip \n from each line
        // FILE_SKIP_EMPTY_LINES: skip blank lines
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $trim = trim($line);
            
            // Skip comments (# or ;) and empty lines
            if ($trim === '' || $trim[0] === '#' || str_starts_with($trim, ';')) { 
                continue; 
            }
            
            // Find = separator position
            $pos = strpos($trim, '=');
            if ($pos === false) { 
                continue; // Skip lines without = 
            }
            
            // Extract key and value
            $key = rtrim(substr($trim, 0, $pos)); // Key is left side (trim trailing space)
            $val = ltrim(substr($trim, $pos + 1)); // Value is right side (trim leading space)
            
            // Strip quotes from value if present: "value" or 'value'
            if ((str_starts_with($val, '"') && str_ends_with($val, '"')) || 
                (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                $val = substr($val, 1, -1); // Remove first and last character
            }
            
            // Set environment variable in all three locations for maximum compatibility
            if ($key !== '') {
                putenv($key . '=' . $val);    // getenv() will read this
                $_ENV[$key] = $val;            // $_ENV superglobal
                $_SERVER[$key] = $_SERVER[$key] ?? $val; // $_SERVER (don't override existing)
            }
        }
    }
}

/**
 * Step 3: Register custom class autoloader
 * - spl_autoload_register() accepts a function that loads classes on demand
 * - When PHP encounters `new User()`, it calls this function with $className = 'User'
 * - We construct the file path and require it if it exists
 *
 * Example:
 * - Code: $user = new User();
 * - PHP can't find User class, calls autoloader with $className = 'User'
 * - Autoloader checks: __DIR__ . '/User.php' = 'classes/User.php'
 * - If file exists, requires it once
 * - Class is now available, object instantiation succeeds
 */
spl_autoload_register(function ($className) {
    // Build expected file path: classes/ClassName.php
    $classFile = __DIR__ . '/' . $className . '.php';
    
    // Check if file exists before requiring (prevents fatal errors)
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

/**
 * Step 4: Ensure global database connection is available
 * - Legacy pattern: $conn is stored in $GLOBALS['conn'] for include-based code
 * - Modern apps use dependency injection instead, but this project uses globals
 * - includes/db.php creates the mysqli connection and stores it in $GLOBALS['conn']
 */
if (!isset($GLOBALS['conn'])) {
    require_once __DIR__ . '/../includes/db.php';
}
