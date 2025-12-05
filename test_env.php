<?php
// Quick diagnostic to confirm env loading
require_once __DIR__ . '/classes/autoload.php';

echo "=== Environment Variables Check ===\n\n";

$keys = ['ADZUNA_APP_ID', 'ADZUNA_APP_KEY', 'RAPIDAPI_KEY'];
foreach ($keys as $key) {
    $val = getenv($key);
    echo "$key: ";
    if ($val === false || $val === '') {
        echo "NOT SET\n";
    } else {
        echo "SET (preview: " . substr($val, 0, 6) . "...)\n";
    }
}

echo "\n_ENV check:\n";
foreach ($keys as $key) {
    echo "$key in \$_ENV: " . (isset($_ENV[$key]) ? 'YES' : 'NO') . "\n";
}

echo "\n_SERVER check:\n";
foreach ($keys as $key) {
    echo "$key in \$_SERVER: " . (isset($_SERVER[$key]) ? 'YES' : 'NO') . "\n";
}

echo "\n.env file exists: " . (file_exists(__DIR__ . '/.env') ? 'YES' : 'NO') . "\n";
echo "Dotenv class available: " . (class_exists('Dotenv\\Dotenv') ? 'YES' : 'NO') . "\n";
