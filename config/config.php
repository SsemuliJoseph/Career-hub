<?php
/**
 * Application configuration (teaching notes)
 *
 * Purpose:
 * - Central place for environment-specific settings: DB credentials, API keys, application constants.
 * - In production you should NOT commit secrets to source control. Use environment variables or
 *   keep this file outside the webroot and out of version control (e.g. in a private config/ folder).
 *
 * Security notes for students:
 * - Never push real API keys or passwords to a public repository. Replace values with placeholders
 *   and inject real secrets via environment variables on the server.
 * - This file contains example credentials to make local development easy; treat them as unsafe
 *   for production.
 *
 * How to override at runtime (common patterns):
 * - Set environment variables (DB_HOST, DB_USER, DB_PASS, DB_NAME, etc.) in your hosting control panel
 *   or in a .env loader. The application first looks for env vars before using these values.
 */

return [
    // ==========================================
    // DATABASE CONFIGURATION
    // ==========================================
    
    // Local Development
    'DB_HOST_LOCAL' => 'localhost',
    'DB_USER_LOCAL' => 'root',
    'DB_PASS_LOCAL' => '',
    'DB_NAME_LOCAL' => 'uniconnect_db',
    
    // InfinityFree / production example (replace with env vars in real deployments)
    'DB_HOST' => 'sql113.infinityfree.com',
    'DB_USER' => 'if0_40185804',
    'DB_PASS' => 'careerhub12',  // Example only - do NOT commit real production secrets
    'DB_NAME' => 'if0_40185804_uniconnect_db',
    
    // ==========================================
    // EXTERNAL API KEYS
    // ==========================================
    
    // Adzuna Job Search API
    'ADZUNA_APP_ID' => 'a306aa16',
    'ADZUNA_APP_KEY' => '918d215fb40ce6f6d9c506be26c5dafd',
    
    // RapidAPI (JSearch)
    'RAPIDAPI_KEY' => '03dabda16dmsh846042236d69e8fp102ba6jsn2f1f9c31e700',
    
    // ==========================================
    // APPLICATION SETTINGS
    // ==========================================
    
    // Cache TTL in seconds (default: 1 hour)
    'CACHE_TTL' => 3600,
    
    // Base URL (automatically detect environment)
    'BASE_URL' => ($_SERVER['HTTP_HOST'] ?? '') === 'localhost' 
        ? '/career_hub' 
        : '',  // Empty for production (InfinityFree uses root)
];
?>
