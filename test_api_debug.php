<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/classes/autoload.php';
require_once __DIR__ . '/includes/db.php';

echo "<h1>External API Debugging</h1>";

// Test environment variables
echo "<h2>1. Environment Variables Check</h2>";
$envVars = ['ADZUNA_APP_ID', 'ADZUNA_APP_KEY', 'RAPIDAPI_KEY'];
foreach ($envVars as $var) {
    $value = getenv($var) ?: ($_ENV[$var] ?? ($_SERVER[$var] ?? ''));
    if ($value) {
        echo "<p>✅ <strong>$var:</strong> " . substr($value, 0, 8) . "...</p>";
    } else {
        echo "<p>❌ <strong>$var:</strong> NOT SET</p>";
    }
}

echo "<hr>";

// Test API Service
echo "<h2>2. Testing External API Service</h2>";
try {
    $apiService = new ExternalAPIService();
    echo "<p>✅ ExternalAPIService initialized successfully</p>";
    
    // Test JSearch API
    echo "<h3>Testing JSearch API (RapidAPI)</h3>";
    echo "<p>Query: 'software developer'</p>";
    
    try {
        $jSearchData = $apiService->fetchJSearchJobs('software developer', 'Kenya');
        
        if ($jSearchData) {
            echo "<p>✅ API Response received</p>";
            
            // Check if data key exists
            if (isset($jSearchData['data'])) {
                echo "<p>Found <strong>" . count($jSearchData['data']) . "</strong> jobs in response</p>";
                
                // Parse jobs
                $parsedJobs = $apiService->parseJSearchJobs($jSearchData);
                echo "<p>Parsed <strong>" . count($parsedJobs) . "</strong> jobs</p>";
                
                // Show first job
                if (count($parsedJobs) > 0) {
                    echo "<h4>Sample Job:</h4>";
                    echo "<pre>" . htmlspecialchars(json_encode($parsedJobs[0], JSON_PRETTY_PRINT)) . "</pre>";
                } else {
                    echo "<p>⚠️ No jobs could be parsed from response</p>";
                }
            } else {
                echo "<p>⚠️ Response structure unexpected. Keys found: " . implode(', ', array_keys($jSearchData)) . "</p>";
                echo "<details><summary>Full response (click to expand)</summary>";
                echo "<pre>" . htmlspecialchars(json_encode($jSearchData, JSON_PRETTY_PRINT)) . "</pre>";
                echo "</details>";
            }
        } else {
            echo "<p>❌ No response from JSearch API (returned null)</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><small>Check error_log for more details</small></p>";
    }
    
    echo "<hr>";
    
    // Test Adzuna API
    echo "<h3>Testing Adzuna API</h3>";
    echo "<p>Query: 'developer' in US</p>";
    
    try {
        $adzunaData = $apiService->fetchAdzunaJobs('developer', 'us', 1);
        
        if ($adzunaData) {
            echo "<p>✅ API Response received</p>";
            
            if (isset($adzunaData['results'])) {
                echo "<p>Found <strong>" . count($adzunaData['results']) . "</strong> jobs in response</p>";
                
                // Parse jobs
                $parsedJobs = $apiService->parseAdzunaJobs($adzunaData);
                echo "<p>Parsed <strong>" . count($parsedJobs) . "</strong> jobs</p>";
                
                // Show first job
                if (count($parsedJobs) > 0) {
                    echo "<h4>Sample Job:</h4>";
                    echo "<pre>" . htmlspecialchars(json_encode($parsedJobs[0], JSON_PRETTY_PRINT)) . "</pre>";
                }
            } else {
                echo "<p>⚠️ Response structure unexpected. Keys found: " . implode(', ', array_keys($adzunaData)) . "</p>";
                echo "<details><summary>Full response (click to expand)</summary>";
                echo "<pre>" . htmlspecialchars(json_encode($adzunaData, JSON_PRETTY_PRINT)) . "</pre>";
                echo "</details>";
            }
        } else {
            echo "<p>❌ No response from Adzuna API (returned null)</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><small>Adzuna might not have data for this region. Try 'us', 'gb', or other supported countries.</small></p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Failed to initialize API Service: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<h2>3. Cache Directory Check</h2>";
$cacheDir = __DIR__ . '/cache/api/';
if (is_dir($cacheDir)) {
    echo "<p>✅ Cache directory exists: <code>$cacheDir</code></p>";
    $files = glob($cacheDir . '*.json');
    echo "<p>Cached files: <strong>" . count($files) . "</strong></p>";
    if (count($files) > 0) {
        echo "<ul>";
        foreach (array_slice($files, 0, 5) as $file) {
            $age = time() - filemtime($file);
            echo "<li>" . basename($file) . " (age: {$age}s)</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p>❌ Cache directory does not exist</p>";
}

echo "<hr>";
echo "<h2>Tips:</h2>";
echo "<ul>";
echo "<li>If APIs return 0 results, try different search terms (e.g., 'developer', 'engineer', 'analyst')</li>";
echo "<li>Some APIs have geographic limitations (try 'us', 'gb', 'ke' for location)</li>";
echo "<li>Check your API quotas on RapidAPI and Adzuna dashboards</li>";
echo "<li>Cache is stored for 1 hour by default - delete cache files to force fresh API calls</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='add_sample_jobs.php'>Add Sample Jobs to Database</a></p>";
echo "<p><a href='test_jobs.php'>Check Jobs in Database</a></p>";
?>
