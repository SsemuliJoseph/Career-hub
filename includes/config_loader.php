<?php
/**
 * Configuration Loader
 * 
 * This file loads the configuration from config.php
 * Use this in your files instead of accessing config.php directly
 */

// Prevent direct access
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);
    
    // Determine config file path
    // Try multiple possible locations
    $configPaths = [
        __DIR__ . '/../../config/config.php',           // Outside htdocs (InfinityFree)
        __DIR__ . '/../config/config.php',              // Same level as includes
        __DIR__ . '/config.php',                        // In includes folder
    ];
    
    $configFile = null;
    foreach ($configPaths as $path) {
        if (file_exists($path)) {
            $configFile = $path;
            break;
        }
    }
    
    if (!$configFile) {
        die('Configuration file not found. Please create config/config.php');
    }
    
    // Load configuration
    $GLOBALS['APP_CONFIG'] = require $configFile;
}

/**
 * Get configuration value
 * 
 * @param string $key Configuration key
 * @param mixed $default Default value if key not found
 * @return mixed Configuration value
 */
function config($key, $default = null) {
    return $GLOBALS['APP_CONFIG'][$key] ?? $default;
}

/**
 * Get database configuration based on environment
 * 
 * @return array Database configuration
 */
function getDbConfig() {
    $isLocal = ($_SERVER['HTTP_HOST'] ?? '') === 'localhost' || 
               strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false;
    
    if ($isLocal) {
        return [
            'host' => config('DB_HOST_LOCAL', 'localhost'),
            'user' => config('DB_USER_LOCAL', 'root'),
            'pass' => config('DB_PASS_LOCAL', ''),
            'name' => config('DB_NAME_LOCAL', 'uniconnect_db'),
        ];
    } else {
        return [
            'host' => config('DB_HOST'),
            'user' => config('DB_USER'),
            'pass' => config('DB_PASS'),
            'name' => config('DB_NAME'),
        ];
    }
}

/**
 * Check if running on localhost
 * 
 * @return bool
 */
function isLocalEnvironment() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return $host === 'localhost' || 
           strpos($host, '127.0.0.1') !== false ||
           strpos($host, 'localhost:') === 0;
}
?>
