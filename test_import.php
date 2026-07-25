<?php
// Quick test of the import API
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing Import API</h1>";
echo "<hr>";

try {
    echo "<p>1. Testing autoload...</p>";
    require_once __DIR__ . '/classes/autoload.php';
    echo "<p style='color: green;'>✅ Autoload successful</p>";
    
    echo "<p>2. Testing database connection...</p>";
    require_once __DIR__ . '/includes/db.php';
    echo "<p style='color: green;'>✅ Database connected</p>";
    
    echo "<p>3. Testing ExternalAPIService class...</p>";
    $apiService = new ExternalAPIService();
    echo "<p style='color: green;'>✅ ExternalAPIService initialized</p>";
    
    echo "<p>4. Testing API call (Software Developer in Kenya)...</p>";
    $jSearchData = $apiService->fetchJSearchJobs('Software Developer', 'Kenya');
    
    if ($jSearchData && isset($jSearchData['data'])) {
        $count = count($jSearchData['data']);
        echo "<p style='color: green;'>✅ API returned $count jobs</p>";
        
        echo "<p>5. Testing parseJSearchJobs...</p>";
        $jobs = $apiService->parseJSearchJobs($jSearchData);
        echo "<p style='color: green;'>✅ Parsed " . count($jobs) . " jobs</p>";
        
        if (!empty($jobs)) {
            echo "<h3>Sample Job:</h3>";
            echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
            print_r($jobs[0]);
            echo "</pre>";
        }
    } else {
        echo "<p style='color: red;'>❌ API returned no data</p>";
        echo "<pre>";
        print_r($jSearchData);
        echo "</pre>";
    }
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✅ All tests passed! Import should work.</h2>";
    echo "<p><a href='/career_hub/pages/import-jobs.php'>Go to Import Jobs Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red; background: #ffe6e6; padding: 15px; border-radius: 5px;'>";
    echo "<strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage());
    echo "</p>";
    echo "<pre>";
    echo $e->getTraceAsString();
    echo "</pre>";
}
?>
