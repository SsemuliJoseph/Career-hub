<?php
echo "<h1>PHP & cURL Configuration Check</h1>";

// Check PHP version
echo "<h2>PHP Version</h2>";
echo "<p><strong>" . phpversion() . "</strong></p>";

// Check if cURL is enabled
echo "<h2>cURL Extension</h2>";
if (extension_loaded('curl')) {
    echo "<p>✅ cURL is <strong>ENABLED</strong></p>";
    
    $curl_version = curl_version();
    echo "<ul>";
    echo "<li>Version: " . $curl_version['version'] . "</li>";
    echo "<li>SSL Version: " . $curl_version['ssl_version'] . "</li>";
    echo "<li>Protocols: " . implode(', ', $curl_version['protocols']) . "</li>";
    echo "</ul>";
    
    // Test a simple cURL request
    echo "<h3>Test cURL Request to httpbin.org</h3>";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://httpbin.org/get',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'CareerHub/1.0'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "<p style='color: red;'>❌ cURL Error: " . htmlspecialchars($error) . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Test request successful (HTTP $httpCode)</p>";
        echo "<details><summary>Click to see response</summary>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "...</pre>";
        echo "</details>";
    }
} else {
    echo "<p style='color: red;'>❌ cURL is <strong>NOT ENABLED</strong></p>";
    echo "<p><strong>Fix:</strong> Edit php.ini and uncomment: <code>extension=curl</code></p>";
}

// Check other required extensions
echo "<h2>Other Extensions</h2>";
$required_extensions = ['mysqli', 'json', 'mbstring', 'openssl'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p>✅ <strong>$ext</strong> - Enabled</p>";
    } else {
        echo "<p>❌ <strong>$ext</strong> - Disabled</p>";
    }
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><a href='test_api_debug.php'>Run Full API Debug Test</a></li>";
echo "<li><a href='add_sample_jobs.php'>Add Sample Jobs to Database</a></li>";
echo "<li><a href='test_jobs.php'>Check Jobs in Database</a></li>";
echo "</ol>";
?>
